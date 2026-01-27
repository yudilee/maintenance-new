<?php

namespace App\Services;

use App\Constants\Location;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;

class InventoryService
{
    /**
     * Determine if an item is an Original unit in a rental pair.
     * Criteria: Lot number matches the Reserved Lot number.
     */
    public function isOriginal(string $lotNumber, ?string $reservedLot): bool
    {
        return !empty($reservedLot) && $lotNumber === $reservedLot;
    }

    /**
     * Determine if an item is a Replacement unit in a rental pair.
     * Criteria: Has Rental ID but Lot Number does NOT match Reserved Lot.
     */
    public function isReplacement(string $lotNumber, ?string $reservedLot, ?string $rentalId, bool $isVendorRent): bool
    {
        return !empty($rentalId) && !$isVendorRent && !$this->isOriginal($lotNumber, $reservedLot);
    }

    /**
     * Determine if a rental is currently active based on dates.
     */
    public function isActiveRental(?string $start, ?string $end, string $today): bool
    {
        // If dates are missing, assume active (legacy logic)
        if (empty($start) && empty($end)) return true;
        
        // Use string comparison for Y-m-d format
        $started = empty($start) || $start <= $today;
        $notEnded = empty($end) || $end >= $today;
        
        return $started && $notEnded;
    }

    /**
     * Check if item is in specific location via string matching
     */
    public function isInLocation(string $itemLocation, string $targetLocation): bool
    {
        return stripos($itemLocation, $targetLocation) !== false;
    }

    /**
     * Scope: In Stock items
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('in_stock', true);
    }

    /**
     * Scope: Rented items (Partners/Customers/Rental)
     */
    public function scopeRented(Builder $query): Builder
    {
        return $query->where('location', Location::RENTAL_CUSTOMER);
    }
    
    /**
     * Scope: External Service
     */
    public function scopeExternalService(Builder $query): Builder
    {
        return $query->where('location', 'like', Location::SERVICE_EXTERNAL . '%');
    }

    /**
     * Scope: Internal Service
     */
    public function scopeInternalService(Builder $query): Builder
    {
        return $query->where('location', Location::SERVICE_INTERNAL);
    }
    
    /**
     * Scope: Insurance
     */
    public function scopeInsurance(Builder $query): Builder
    {
        return $query->where('location', 'like', Location::INSURANCE . '%');
    }

    /**
     * Scope: All Service (Internal + External + Insurance)
     */
    public function scopeInService(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('location', Location::SERVICE_INTERNAL)
              ->orWhere('location', 'like', Location::SERVICE_EXTERNAL . '%')
              ->orWhere('location', 'like', Location::INSURANCE . '%');
        });
    }

    /**
     * Scope: SDP Owned (Not Vendor Rent)
     */
    public function scopeSdpOwned(Builder $query): Builder
    {
        return $query->where('is_vendor_rent', false);
    }
}
