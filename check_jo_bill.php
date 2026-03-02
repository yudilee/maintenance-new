<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\OdooSetting;

$setting = OdooSetting::first();
$url = rtrim($setting->odoo_url, '/') . '/jsonrpc';
$db = $setting->database;
$user = $setting->user_email;
$pass = $setting->api_key;

$call = function($service, $method, $args) use ($url, $db, $user, $pass) {
    $params = ['service' => $service, 'method' => $method, 'args' => $args];
    $resp = Http::post($url, ['jsonrpc' => '2.0', 'method' => 'call', 'id' => uniqid(), 'params' => $params]);
    return $resp->json();
};

// Authenticate
$auth = $call('common', 'authenticate', [$db, $user, $pass, (object)[]]);
$uid = $auth['result'];
echo "UID: $uid\n";

// Find repair order
$ro = $call('object', 'execute_kw', [$db, $uid, $pass, 'repair.order', 'search_read',
    [[['name', '=', 'JO-SUB/2026/00320']]],
    ['fields' => ['name', 'move_id', 'state', 'partner_invoice_id']]
]);
echo "\nREPAIR ORDER:\n";
print_r($ro['result'] ?? $ro['error'] ?? 'none');

// Find linked bills
$bills = $call('object', 'execute_kw', [$db, $uid, $pass, 'account.move', 'search_read',
    [[['repair_id.name', '=', 'JO-SUB/2026/00320']]],
    ['fields' => ['name', 'state', 'amount_untaxed', 'amount_tax', 'invoice_date']]
]);
echo "\nLINKED BILLS:\n";
print_r($bills['result'] ?? $bills['error'] ?? 'none');
