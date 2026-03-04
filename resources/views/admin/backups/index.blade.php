@extends('layouts.app')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4m0 5c0 2.21-3.582 4-8 4s-4-1.79-4-4"></path></svg>
            Database Backups
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Manage database backups, view audit logs, and perform restoration.</p>
    </div>
    <div class="flex gap-3">
        <button type="button" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-indigo-600 dark:text-indigo-400 font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-2" onclick="document.getElementById('restoreFromFileModal').classList.remove('hidden')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            Restore from File
        </button>
        <button type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center gap-2 shadow-sm shadow-indigo-200 dark:shadow-none" onclick="document.getElementById('createBackupModal').classList.remove('hidden')">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Create New Backup
        </button>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden mb-8">
    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h3 class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            Backup Files
        </h3>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.backups.prune') }}" method="POST" class="inline" onsubmit="return confirm('Run automatic pruning based on retention policy?');">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path></svg>
                    Auto Prune
                </button>
            </form>
            <button type="button" class="hidden px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 rounded-lg text-sm font-medium transition items-center gap-2" id="deleteSelectedBtn" onclick="deleteSelected()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Delete Selected (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>
    
    <form id="batchDeleteForm" action="{{ route('admin.backups.delete-batch') }}" method="POST">
        @csrf
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4 w-12 text-center">
                            <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300">Filename</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300">Remark</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300">Size</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300">Created By</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300">Created At</th>
                        <th class="py-3 px-4 font-semibold text-sm text-slate-600 dark:text-slate-300 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($backups as $backup)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 text-center align-middle">
                                <input type="checkbox" class="backup-checkbox w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500" name="filenames[]" value="{{ $backup->filename }}" onchange="updateSelectedCount()">
                            </td>
                            <td class="py-3 px-4 align-middle">
                                <div class="flex items-center gap-2 font-medium text-slate-800 dark:text-slate-200">
                                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                    {{ $backup->filename }}
                                </div>
                            </td>
                            <td class="py-3 px-4 align-middle">
                                @if($backup->remark)
                                    <span class="text-slate-500 dark:text-slate-400 italic text-sm">{{ $backup->remark }}</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-middle text-sm text-slate-600 dark:text-slate-400">
                                @if($backup->size > 0)
                                    <span class="font-mono bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">{{ number_format($backup->size / 1048576, 2) }} MB</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-middle">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 rounded-full text-xs font-semibold">{{ $backup->created_by ?? 'System' }}</span>
                            </td>
                            <td class="py-3 px-4 align-middle text-sm">
                                <span class="text-slate-800 dark:text-slate-200 block">{{ $backup->created_at->format('d M Y H:i:s') }}</span>
                                <span class="text-slate-500 dark:text-slate-400 text-xs">{{ $backup->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="py-3 px-4 align-middle text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.backups.download', $backup->filename) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded transition" title="Download">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.backups.restore', $backup->filename) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded transition" title="Restore" onclick="return confirmRestore('{{ $backup->filename }}');">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.backups.destroy', $backup->filename) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition" title="Delete" onclick="return confirm('Delete this backup file?');">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-lg font-medium mb-1">No backups found</p>
                                <p class="text-sm">Create one manually to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Schedule & Pruning Configuration Card -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm mb-8">
    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
        <h3 class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Schedule & Retention
        </h3>
        <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $schedule->enabled ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
            {{ $schedule->enabled ? 'Enabled' : 'Disabled' }}
        </span>
    </div>
    
    <div class="p-6">
        <form action="{{ route('admin.backups.schedule') }}" method="POST">
            @csrf
            
            <div class="mb-8">
                <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Schedule Settings
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-2 lg:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                        <label class="flex items-center gap-3 cursor-pointer py-2">
                            <input type="checkbox" name="enabled" value="1" class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500" id="scheduleEnabled" {{ $schedule->enabled ? 'checked' : '' }}>
                            <span class="text-slate-700 dark:text-slate-300 text-sm">Enable</span>
                        </label>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Frequency</label>
                        <select name="frequency" id="frequency" onchange="toggleDayFields()" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="daily" {{ $schedule->frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $schedule->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $schedule->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Time</label>
                        <input type="time" name="time" id="time" value="{{ $schedule->time }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2" id="dayOfWeekGroup" style="{{ $schedule->frequency == 'weekly' ? '' : 'display:none' }}">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                            <option value="{{ $i }}" {{ ($schedule->day_of_week ?? 0) == $i ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2" id="dayOfMonthGroup" style="{{ $schedule->frequency == 'monthly' ? '' : 'display:none' }}">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Day of Month</label>
                        <input type="number" name="day_of_month" id="day_of_month" min="1" max="31" value="{{ $schedule->day_of_month ?? 1 }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div class="md:col-span-4 lg:col-span-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Remark</label>
                        <input type="text" name="remark" value="{{ $schedule->remark }}" placeholder="e.g. Daily backup" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-200 dark:border-slate-800 my-6"></div>
            
            <div>
                <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path></svg>
                    Retention Policy (like borgbackup)
                </h4>
                
                <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-4 items-start">
                    <div class="md:col-span-2 lg:col-span-2 space-y-2 mt-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="prune_enabled" value="1" class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500" id="pruneEnabled" {{ ($schedule->prune_enabled ?? true) ? 'checked' : '' }}>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Auto Prune</span>
                        </label>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Keep Daily</label>
                        <input type="number" name="keep_daily" min="0" max="365" value="{{ $schedule->keep_daily ?? 7 }}" class="w-full px-4 py-2 mb-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-xs text-slate-500">Last N days</span>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Keep Weekly</label>
                        <input type="number" name="keep_weekly" min="0" max="52" value="{{ $schedule->keep_weekly ?? 4 }}" class="w-full px-4 py-2 mb-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-xs text-slate-500">Last N weeks</span>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Keep Monthly</label>
                        <input type="number" name="keep_monthly" min="0" max="24" value="{{ $schedule->keep_monthly ?? 6 }}" class="w-full px-4 py-2 mb-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <span class="text-xs text-slate-500">Last N months</span>
                    </div>
                    
                    <div class="md:col-span-4 lg:col-span-4 md:mt-[22px]">
                        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Save Configuration
                        </button>
                    </div>
                </div>
                
                <div class="mt-4 flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Retention limits keep the most recent backup for each period. Default: 7 daily + 4 weekly + 6 monthly = up to 17 total backups stored locally.</p>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 flex flex-col items-center max-w-sm w-full mx-4">
        <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-lg font-medium text-slate-800 dark:text-slate-100" id="loadingText">Processing...</p>
        <p class="text-sm text-slate-500 mt-1">Please wait, do not close your browser window.</p>
    </div>
</div>

<!-- Modals Native HTML Implementation -->
<!-- Create Backup Modal -->
<div id="createBackupModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all mx-4">
        <form action="{{ route('admin.backups.create') }}" method="POST" onsubmit="document.getElementById('createBackupModal').classList.add('hidden'); showLoading('Creating backup...');">
            @csrf
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Create Database Backup</h3>
                <button type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none" onclick="document.getElementById('createBackupModal').classList.add('hidden')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Remark (Optional)</label>
                    <input type="text" name="remark" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white" placeholder="e.g. Before manual cleanup">
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">This will create a full snapshot of the current database state.</p>
            </div>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition" onclick="document.getElementById('createBackupModal').classList.add('hidden')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">Create Backup</button>
            </div>
        </form>
    </div>
</div>

<!-- Restore from File Modal -->
<div id="restoreFromFileModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm" onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all mx-4">
        <form action="{{ route('admin.backups.restore-file') }}" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('restoreFromFileModal').classList.add('hidden'); showLoading('Restoring database...');">
            @csrf
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-red-50 dark:bg-red-900/20">
                <h3 class="text-lg font-semibold text-red-800 dark:text-red-400 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Critical Action: Restore from File
                </h3>
                <button type="button" class="text-red-400 hover:text-red-600 focus:outline-none" onclick="document.getElementById('restoreFromFileModal').classList.add('hidden')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="px-6 py-5">
                <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-5 flex gap-3">
                    <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <h4 class="text-sm font-bold text-amber-800 dark:text-amber-400">Warning</h4>
                        <p class="text-sm text-amber-700 dark:text-amber-500 mt-1">This will instantly OVERWRITE all current database data. Make sure you select the correct backup file.</p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Backup File (.sql.gz, .sql)</label>
                    <input type="file" name="backup_file" accept=".sql.gz,.gz,.sql" required class="w-full text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-400 dark:hover:file:bg-indigo-900/50">
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition" onclick="document.getElementById('restoreFromFileModal').classList.add('hidden')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition shadow-sm shadow-red-200 dark:shadow-none">Restore DB Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDayFields() {
    const freq = document.getElementById('frequency').value;
    document.getElementById('dayOfWeekGroup').style.display = freq === 'weekly' ? 'block' : 'none';
    document.getElementById('dayOfMonthGroup').style.display = freq === 'monthly' ? 'block' : 'none';
}

function showLoading(text) {
    document.getElementById('loadingText').innerText = text;
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function confirmRestore(filename) {
    if (confirm('⚠️ WARNING: Restore database from ' + filename + '?\n\nThis will OVERWRITE all current data. This action cannot be undone!\n\nAre you absolutely sure?')) {
        showLoading('Restoring database...');
        return true;
    }
    return false;
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.backup-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.backup-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
    
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (selected > 0) {
        deleteBtn.classList.remove('hidden');
        deleteBtn.classList.add('inline-flex');
    } else {
        deleteBtn.classList.add('hidden');
        deleteBtn.classList.remove('inline-flex');
    }
}

function deleteSelected() {
    const count = document.querySelectorAll('.backup-checkbox:checked').length;
    if (confirm('Delete ' + count + ' selected backup(s)?')) {
        showLoading('Deleting backups...');
        document.getElementById('batchDeleteForm').submit();
    }
}
</script>
@endsection
