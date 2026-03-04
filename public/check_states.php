<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OdooSyncService;
use App\Models\OdooSetting;
use Illuminate\Support\Facades\Http;

$setting = OdooSetting::first();
if (!$setting) die('No settings');

$authData = Http::post("{$setting->odoo_url}/jsonrpc", [
    'jsonrpc' => '2.0',
    'method' => 'call',
    'params' => [
        'service' => 'common',
        'method' => 'authenticate',
        'args' => [$setting->database, $setting->user_email, $setting->api_key, (object)[]],
    ],
    'id' => uniqid(),
])->json();

$uid = $authData['result'];

$statesData = Http::post("{$setting->odoo_url}/jsonrpc", [
    'jsonrpc' => '2.0',
    'method' => 'call',
    'params' => [
        'service' => 'object',
        'method' => 'execute_kw',
        'args' => [
            $setting->database, $uid, $setting->api_key,
            'repair.order', 'search_read',
            [[]],
            ['fields' => ['state'], 'limit' => 1000]
        ],
    ],
    'id' => uniqid(),
])->json();

$states = array_unique(array_column($statesData['result'], 'state'));
print_r($states);
