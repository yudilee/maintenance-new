<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use App\Models\BackupLog;
use Illuminate\Support\Facades\Auth;

class BackupService
{
    protected $disk = 'local';
    protected $backupFolder = 'backups';

    public function create($remark = null)
    {
        $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql.gz';
        $path = Storage::disk($this->disk)->path($this->backupFolder . '/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $config = config('database.connections.mysql');
        
        // Pipe mysqldump directly to gzip file (avoids loading entire dump into PHP memory)
        $command = sprintf(
            'mysqldump --skip-ssl --no-tablespaces --user=%s --password=%s --host=%s --port=%s %s 2>/dev/null | gzip > %s',
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['database']),
            escapeshellarg($path)
        );

        // Execute the piped command
        $returnCode = 0;
        $output = [];
        exec($command, $output, $returnCode);

        // Check if file was created and has content
        clearstatcache(true, $path);
        $fileSize = file_exists($path) ? filesize($path) : 0;
        
        if ($fileSize < 100) {
            // Try to get the actual error
            $errorCommand = sprintf(
                'mysqldump --skip-ssl --no-tablespaces --user=%s --password=%s --host=%s --port=%s %s 2>&1 | head -5',
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['database'])
            );
            $errorOutput = shell_exec($errorCommand);
            
            // Clean up failed file
            if (file_exists($path)) {
                unlink($path);
            }
            
            throw new \Exception('Backup failed: ' . ($errorOutput ?: 'mysqldump returned empty output'));
        }

        // Create BackupLog record
        BackupLog::create([
            'filename' => $filename,
            'path' => $this->backupFolder . '/' . $filename,
            'disk' => $this->disk,
            'size' => $fileSize,
            'remark' => $remark,
            'created_by' => Auth::check() ? Auth::user()->name : 'System/Scheduler',
        ]);

        return $filename;
    }

    public function list()
    {
        // Return BackupLogs ordered by latest
        return BackupLog::latest()->get();
    }

    public function restore($filename)
    {
        $path = Storage::disk($this->disk)->path($this->backupFolder . '/' . $filename);
        
        if (!file_exists($path)) {
            throw new \Exception('Backup file not found.');
        }

        $this->restoreFromPath($path, $filename);
        return true;
    }

    /**
     * Restore from uploaded file
     */
    public function restoreFromFile(UploadedFile $file)
    {
        // Save uploaded file temporarily
        $tempPath = Storage::disk($this->disk)->path('temp_restore_' . time() . '.sql.gz');
        $file->move(dirname($tempPath), basename($tempPath));

        $this->restoreFromPath($tempPath, $file->getClientOriginalName(), true);

        return true;
    }

    /**
     * Common restore logic
     */
    protected function restoreFromPath($path, $filename, $deleteAfter = false)
    {
        $config = config('database.connections.mysql');
        
        $artisan = base_path('artisan');
        
        $cleanupCommand = $deleteAfter ? sprintf('; rm -f %s', escapeshellarg($path)) : '';

        // Wrap command to put app in maintenance mode, run restore, then bring it back up, all in background
        if ($isGzipped) {
            $command = sprintf(
                '(php %s down --refresh=15 --secret="restore"; gunzip < %s | mysql --skip-ssl --user=%s --password=%s --host=%s --port=%s %s; php %s up%s) > /dev/null 2>&1 &',
                escapeshellarg($artisan),
                escapeshellarg($path),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['database']),
                escapeshellarg($artisan),
                $cleanupCommand
            );
        } else {
            $command = sprintf(
                '(php %s down --refresh=15 --secret="restore"; mysql --skip-ssl --user=%s --password=%s --host=%s --port=%s %s < %s; php %s up%s) > /dev/null 2>&1 &',
                escapeshellarg($artisan),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['database']),
                escapeshellarg($path),
                escapeshellarg($artisan),
                $cleanupCommand
            );
        }

        exec($command);
    }

    public function delete($filename)
    {
        $path = $this->backupFolder . '/' . $filename;
        
        // Delete file
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
        
        // Delete log record
        BackupLog::where('filename', $filename)->delete();
        
        return true;
    }
    
    public function download($filename)
    {
         $path = $this->backupFolder . '/' . $filename;
         if (Storage::disk($this->disk)->exists($path)) {
             return Storage::disk($this->disk)->download($path);
         }
         return null;
    }

    /**
     * Delete multiple backups
     */
    public function deleteBatch(array $filenames): int
    {
        $deleted = 0;
        foreach ($filenames as $filename) {
            try {
                $this->delete($filename);
                $deleted++;
            } catch (\Exception $e) {
                // Continue with other files
            }
        }
        return $deleted;
    }

    /**
     * Prune old backups based on retention policy (like borgbackup)
     */
    public function prune(int $keepDaily = 7, int $keepWeekly = 4, int $keepMonthly = 6): array
    {
        $backups = BackupLog::orderByDesc('created_at')->get();
        
        $keepSet = [];
        $dailyCounts = [];
        $weeklyCounts = [];
        $monthlyCounts = [];

        foreach ($backups as $backup) {
            $date = $backup->created_at;
            $dayKey = $date->format('Y-m-d');
            $weekKey = $date->format('Y-W');
            $monthKey = $date->format('Y-m');

            $keep = false;

            // Keep daily
            if (!isset($dailyCounts[$dayKey])) {
                $dailyCounts[$dayKey] = 0;
            }
            if ($dailyCounts[$dayKey] < 1 && count($dailyCounts) <= $keepDaily) {
                $keep = true;
                $dailyCounts[$dayKey]++;
            }

            // Keep weekly (first of each week)
            if (!isset($weeklyCounts[$weekKey])) {
                $weeklyCounts[$weekKey] = 0;
            }
            if ($weeklyCounts[$weekKey] < 1 && count($weeklyCounts) <= $keepWeekly) {
                $keep = true;
                $weeklyCounts[$weekKey]++;
            }

            // Keep monthly (first of each month)
            if (!isset($monthlyCounts[$monthKey])) {
                $monthlyCounts[$monthKey] = 0;
            }
            if ($monthlyCounts[$monthKey] < 1 && count($monthlyCounts) <= $keepMonthly) {
                $keep = true;
                $monthlyCounts[$monthKey]++;
            }

            if ($keep) {
                $keepSet[$backup->filename] = true;
            }
        }

        // Delete backups not in keep set
        $deleted = [];
        foreach ($backups as $backup) {
            if (!isset($keepSet[$backup->filename])) {
                try {
                    $this->delete($backup->filename);
                    $deleted[] = $backup->filename;
                } catch (\Exception $e) {
                    // Continue
                }
            }
        }

        return [
            'kept' => count($keepSet),
            'deleted' => count($deleted),
            'deleted_files' => $deleted,
        ];
    }
}
