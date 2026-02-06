<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooService;

class DiscoverOdooFields extends Command
{
    protected $signature = 'odoo:discover-fields';
    protected $description = 'Discover available fields in Odoo for export_data';

    public function handle(OdooService $odoo)
    {
        $this->info("Discovering Odoo field paths for export_data...\n");

        // All potential field paths based on Excel columns
        $fieldsToTest = [
            // Basic stock.lot fields
            'name',
            'product_id/display_name',
            'ref',
            'location_id/display_name',
            'product_qty',
            'is_vendor_rent',
            'vehicle_year',
            'x_studio_partnercust',
            
            // Rental ID related
            'rental_id',
            'rental_id/display_name',
            'rental_id/name',
            
            // Rental type variations
            'rental_id/x_tipe_rental',
            'rental_id/x_studio_tipe_rental',
            'rental_id/rental_type',
            
            // Dates
            'rental_id/actual_start_rental',
            'rental_id/actual_end_rental',
            'rental_id/rental_start_date',
            'rental_id/rental_return_date',
            
            // Customer/Warehouse
            'rental_id/partner_id/display_name',
            'rental_id/partner_id/name',
            'rental_id/warehouse_id/display_name',
            'rental_id/warehouse_id/name',
            
            // Reserved lot variations
            'rental_id/order_line/reserved_lot_ids',
            'rental_id/order_line/reserved_lot_ids/name',
            'original_reserved',
            
            // In Stock flag
            'x_studio_in_stock',
        ];

        // Get a sample ID
        $result = $odoo->testConnection();
        if (!$result['success']) {
            $this->error("Connection failed: " . $result['message']);
            return 1;
        }

        // Test each field individually
        $workingFields = [];
        $failedFields = [];

        $this->info("Testing " . count($fieldsToTest) . " field paths...\n");
        
        $bar = $this->output->createProgressBar(count($fieldsToTest));

        foreach ($fieldsToTest as $field) {
            try {
                $testResult = $this->testField($odoo, $field);
                if ($testResult['success']) {
                    $workingFields[$field] = $testResult['sample'];
                } else {
                    $failedFields[] = $field;
                }
            } catch (\Exception $e) {
                $failedFields[] = $field;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info("✓ Working Fields (" . count($workingFields) . "):");
        foreach ($workingFields as $field => $sample) {
            $sampleStr = is_array($sample) ? json_encode($sample) : (string)$sample;
            $sampleStr = mb_substr($sampleStr, 0, 50);
            $this->line("  <fg=green>$field</> → $sampleStr");
        }

        $this->newLine();
        $this->warn("✗ Failed Fields (" . count($failedFields) . "):");
        foreach ($failedFields as $field) {
            $this->line("  <fg=red>$field</>");
        }

        // Save results
        $outputPath = storage_path('app/odoo_fields.json');
        file_put_contents($outputPath, json_encode([
            'working' => array_keys($workingFields),
            'failed' => $failedFields,
            'samples' => $workingFields
        ], JSON_PRETTY_PRINT));
        
        $this->newLine();
        $this->info("Results saved to: $outputPath");

        return 0;
    }

    protected function testField(OdooService $odoo, string $field): array
    {
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($odoo);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        // Get one sample ID
        $ids = $method->invoke($odoo, 'stock.lot', 'search', [
            [['product_qty', '>', 0], ['location_id', '!=', 5]]
        ], ['limit' => 1]);

        if (empty($ids)) {
            return ['success' => false, 'sample' => null];
        }

        // Try export_data with this field
        $result = $method->invoke($odoo, 'stock.lot', 'export_data', [$ids, [$field]]);

        if (isset($result['datas'][0][0])) {
            return ['success' => true, 'sample' => $result['datas'][0][0]];
        }

        return ['success' => false, 'sample' => null];
    }
}
