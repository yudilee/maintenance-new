<?php

namespace App\Constants;

class Location
{
    const RENTAL_CUSTOMER = 'Partners/Customers/Rental';
    const SERVICE_INTERNAL = 'Physical Locations/Service';
    const SERVICE_EXTERNAL = 'Partners/Vendors/Service';
    const INSURANCE = 'Partners/Vendors/Insurance';
    const SOLD = 'SDP/SOLD';
    const SOLD_STOCK = 'SDP/STOCK SOLD';
    const OPERATION = 'SDP/OPERATION';
    const TRANSIT = 'Transit';
    
    // City Codes
    const JAKARTA = ['JKT', 'Jakarta'];
    const SURABAYA = ['SUB', 'SBY', 'Surabaya'];
    const SEMARANG = ['SMG', 'Semarang'];
    const BANDUNG = ['BDG', 'Bandung'];
    const CIREBON = ['CRB', 'CBN', 'Cirebon'];
    const CILEGON = ['CLG', 'Cilegon'];
}
