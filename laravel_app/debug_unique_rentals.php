<?php

require __DIR__ . '/vendor/autoload.php';

use App\Repositories\JsonItemRepository;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$repo = new JsonItemRepository();
$items = $repo->all();

echo "=== UNIQUE RENTAL COUNT ANALYSIS ===\n\n";

$subscriptionVehicles = 0;
$regularVehicles = 0;
$subscriptionRentalIds = [];
$regularRentalIds = [];

foreach ($items as $item) {
    if ($item['is_sold']) continue;
    
    $rentalType = $item['rental_type'] ?? '';
    $rentalId = $item['rental_id'] ?? '';
    
    if ($rentalType === 'Subscription') {
        $subscriptionVehicles++;
        if (!empty($rentalId)) {
            $subscriptionRentalIds[$rentalId] = true;
        }
    } elseif ($rentalType === 'Regular') {
        $regularVehicles++;
        if (!empty($rentalId)) {
            $regularRentalIds[$rentalId] = true;
        }
    }
}

$uniqueSubscriptionRentals = count($subscriptionRentalIds);
$uniqueRegularRentals = count($regularRentalIds);

echo "SUBSCRIPTION:\n";
echo "  Total Vehicles: $subscriptionVehicles\n";
echo "  Unique Rentals (by rental_id): $uniqueSubscriptionRentals\n";
echo "  Difference (Replacements): " . ($subscriptionVehicles - $uniqueSubscriptionRentals) . "\n\n";

echo "REGULAR:\n";
echo "  Total Vehicles: $regularVehicles\n";
echo "  Unique Rentals (by rental_id): $uniqueRegularRentals\n";
echo "  Difference (Replacements): " . ($regularVehicles - $uniqueRegularRentals) . "\n\n";

echo "INTERPRETATION:\n";
echo "The 'Unique Rentals' number represents actual active rental contracts.\n";
echo "The difference shows how many replacement vehicles are currently in use.\n";
