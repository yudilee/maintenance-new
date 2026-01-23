<?php

require __DIR__ . '/vendor/autoload.php';

use App\Repositories\JsonItemRepository;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$repo = new JsonItemRepository();
$items = $repo->all();
$summary = $repo->getSummary();

echo "=== DASHBOARD SUMMARY ===\n";
echo "Subscription Count: " . ($summary['rental_type_summary']['Subscription'] ?? 'N/A') . "\n";
echo "Regular Count: " . ($summary['rental_type_summary']['Regular'] ?? 'N/A') . "\n";
echo "Pending Rental Count: " . ($summary['pending_rental'] ?? 'N/A') . "\n";
echo "\n";

echo "=== ITEM ANALYSIS ===\n";
$subRows = 0;
$subQty = 0;
$subPendingRows = 0;
$subActiveRows = 0;
$soldRows = 0;

$byQty = [];

foreach ($items as $item) {
    if ($item['is_sold']) {
        $soldRows++;
        continue;
    }
    
    if (($item['rental_type'] ?? '') === 'Subscription') {
        $subRows++;
        $qty = $item['on_hand_quantity'];
        $subQty += $qty;
        
        if ($qty != 1) {
            $byQty[$qty] = ($byQty[$qty] ?? 0) + 1;
        }
        
        if ($item['is_active_rental'] ?? true) { // Default to true if missing
            $subActiveRows++;
        } else {
            $subPendingRows++;
        }
    }
}

echo "Filtered Table Logic (Not Sold, Type=Subscription):\n";
echo "Total Rows: $subRows\n";
echo "Total Qty: $subQty\n";
echo "Active Rows: $subActiveRows\n";
echo "Pending Rows: $subPendingRows\n";

if (!empty($byQty)) {
    echo "\nItems with Qty != 1:\n";
    print_r($byQty);
}

echo "\n";
echo "Difference (Summary - Rows): " . ($summary['rental_type_summary']['Subscription'] - $subRows) . "\n";
echo "Difference (Summary - Active Rows): " . ($summary['rental_type_summary']['Subscription'] - $subActiveRows) . "\n";

echo "\n=== LOCATIONS OF SUBSCRIPTIONS ===\n";
// Let's see if there are subscriptions in weird locations that might be filtered out?
// Controller only filters by is_sold. 
// So locations shouldn't matter for the 'rental_type' filter.

