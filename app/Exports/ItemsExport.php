<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Lot Number',
            'Product',
            'Location',
            'Quantity',
            'Type',
            'Rental Status',
            'Rental ID',
            'Vehicle Role',
            'Linked Vehicle',
            'Actual Start',
            'Actual End',
            'In Stock',
            'Internal Reference',
        ];
    }

    public function map($item): array
    {
        $type = $item->is_vendor_rent ? 'Vendor' : 'Owned';
        
        $status = '-';
        if ($item->rental_id) {
             $status = $item->rental_type; // e.g., Subscription, Regular
        } elseif ($item->in_stock) {
             $status = 'In Stock';
        }

        return [
            $item->lot_number,
            $item->product,
            $item->location,
            $item->on_hand_quantity,
            $type,
            $status,
            $item->rental_id,
            $item->vehicle_role,
            $item->linked_vehicle,
            $item->actual_start_rental ? $item->actual_start_rental->format('Y-m-d') : '',
            $item->actual_end_rental ? $item->actual_end_rental->format('Y-m-d') : '',
            $item->in_stock ? 'Yes' : 'No',
            $item->internal_reference,
        ];
    }
}
