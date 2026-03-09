<?php

namespace App\Services;

use App\Models\OdooSetting;
use App\Models\ImportHistory;
use App\Models\Htransaksi;
use App\Models\Dtransaksi;
use App\Models\Mobil;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OdooSyncService
{
    protected $setting;
    protected $url;
    protected $db;
    protected $user;
    protected $password;
    protected $uid = null;

    public function __construct()
    {
        $this->setting = OdooSetting::first();
        if ($this->setting) {
            $this->url = $this->setting->odoo_url;
            $this->db = $this->setting->database;
            $this->user = $this->setting->user_email;
            $this->password = $this->setting->api_key;
        }
    }

    public function sync($sourceType = 'Manual', $targetJo = null)
    {
        if (!$this->setting) {
            return ['success' => false, 'message' => 'Odoo settings not configured.'];
        }

        try {
            // 1. Authenticate with Odoo
            $authData = $this->odooCall('common', 'authenticate', [$this->db, $this->user, $this->password, (object)[]]);
            
            if (!$authData['success'] || empty($authData['result'])) {
                $errorMsg = $authData['error'] ?? 'Authentication failed';
                $this->logHistory($sourceType, 'Failed', 0, "Auth Error: " . $errorMsg);
                return ['success' => false, 'message' => "Odoo Auth Failed: $errorMsg"];
            }

            $this->uid = $authData['result'];
            $lastSyncUTC = $this->setting->last_sync ? Carbon::parse($this->setting->last_sync)->setTimezone('UTC')->format('Y-m-d H:i:s') : null;

            $ros = [];
            $moves = [];

            // 2. Fetch Repair Orders (Job Orders) in Batches
            $roDomain = [];
            if ($targetJo) {
                $roDomain[] = ['name', '=', $targetJo];
            } elseif ($lastSyncUTC) {
                // If it synced previously, only grab things modified since then
                $roDomain[] = ['write_date', '>=', $lastSyncUTC];
            } else {
                // If first time syncing, pull EVERYTHING created since Jan 1 2026
                $roDomain[] = ['create_date', '>=', '2025-01-01 00:00:00'];
            }

            $offset = 0;
            $limit = 500;
            while(true) {
                $rosData = $this->odooCall('object', 'execute_kw', [
                    $this->db, $this->uid, $this->password,
                    'repair.order', 'search_read',
                    [$roDomain],
                    [
                        'fields' => ['name', 'lot_id', 'lot_vehicle_ref', 'service_type', 'km_pickup', 'compute_job_card_repair_notes', 'product_model_type_combined', 'partner_id', 'order_line_ids', 'repair_service_ids', 'state', 'create_date', 'move_id', 'vendor_bill_ids', 'schedule_date', 'repair_end_datetime'],
                        'limit' => $limit,
                        'offset' => $offset,
                        'order' => 'create_date asc' // Fetch oldest first so updates replace correctly
                    ]
                ]);
                $batchRos = $rosData['result'] ?? [];
                if(empty($batchRos)) break;
                
                $ros = array_merge($ros, $batchRos);
                if(count($batchRos) < $limit) break;
                $offset += $limit;
            }
 
            // 3. Fetch Linked Vendor Bills (Costs only, where repair_id is set)
            $billDomain = [['repair_id', '!=', false], ['state', '!=', 'cancel'], ['name', 'like', 'BILLS/']];
            if ($lastSyncUTC) {
                $billDomain[] = ['write_date', '>=', $lastSyncUTC];
            } else {
                $billDomain[] = ['create_date', '>=', '2025-01-01 00:00:00'];
            }

            $offset = 0;
            $limit = 500;
            while(true) {
                $movesData = $this->odooCall('object', 'execute_kw', [
                    $this->db, $this->uid, $this->password,
                    'account.move', 'search_read',
                    [$billDomain],
                    [
                        'fields' => ['name', 'partner_id', 'invoice_date', 'ref', 'line_ids', 'state', 'repair_id', 'create_date', 'amount_tax', 'amount_untaxed'], 
                        'limit' => $limit,
                        'offset' => $offset,
                        'order' => 'create_date asc'
                    ]
                ]);
                
                $batchMoves = $movesData['result'] ?? [];
                if(empty($batchMoves)) break;
                
                $moves = array_merge($moves, $batchMoves);
                if(count($batchMoves) < $limit) break;
                $offset += $limit;
            }
            
            if (empty($ros) && empty($moves)) {
                $this->setting->update(['last_sync' => now()]);
                $this->logHistory($sourceType, 'Success', 0, 'No new records to sync.');
                return ['success' => true, 'message' => 'Everything up to date.', 'items' => 0];
            }

            // --- Pre-fetch Related Data in Batch ---
            $allLotIds = [];
            $allLineIds = []; // account.move.line
            $allRoLineIds = []; // repair.order.line
            $allRoServiceIds = []; // repair.service
            $allRoMoveIds = []; // account.move (Sales Invoices)
            $allVendorBillIds = []; // vendor bills from ROs
            
            foreach ($ros as $ro) {
                if (!empty($ro['lot_id'])) $allLotIds[] = is_array($ro['lot_id']) ? $ro['lot_id'][0] : $ro['lot_id'];
                if (!empty($ro['order_line_ids'])) $allRoLineIds = array_merge($allRoLineIds, $ro['order_line_ids']);
                if (!empty($ro['repair_service_ids'])) $allRoServiceIds = array_merge($allRoServiceIds, $ro['repair_service_ids']);
                if (!empty($ro['move_id'])) $allRoMoveIds[] = is_array($ro['move_id']) ? $ro['move_id'][0] : $ro['move_id'];
                if (!empty($ro['vendor_bill_ids'])) $allVendorBillIds = array_merge($allVendorBillIds, $ro['vendor_bill_ids']);
            }
            foreach ($moves as $move) {
                if (!empty($move['line_ids'])) $allLineIds = array_merge($allLineIds, $move['line_ids']);
            }

            $allLotIds = array_values(array_unique($allLotIds));
            $allLineIds = array_values(array_unique($allLineIds));
            $allRoLineIds = array_values(array_unique($allRoLineIds));
            $allRoServiceIds = array_values(array_unique($allRoServiceIds));
            $allVendorBillIds = array_values(array_unique($allVendorBillIds));

            // Fetch Lots (Vehicles)
            $lotsMap = [];
            if (!empty($allLotIds)) {
                $res = $this->odooCall('object', 'execute_kw', [$this->db, $this->uid, $this->password, 'stock.lot', 'read', [$allLotIds], ['fields' => ['name', 'ref', 'color_id']]]);
                if ($res['success']) foreach ($res['result'] as $l) $lotsMap[$l['id']] = $l;
            }

            // Fetch RO Lines
            $roLinesMap = [];
            if (!empty($allRoLineIds)) {
                $res = $this->odooCall('object', 'execute_kw', [$this->db, $this->uid, $this->password, 'repair.order.line', 'read', [$allRoLineIds], ['fields' => ['name', 'quantity', 'price_unit', 'price_subtotal']]]);
                if ($res['success']) foreach ($res['result'] as $rl) $roLinesMap[$rl['id']] = $rl;
            }

            // Fetch Service Lines
            $roServicesMap = [];
            if (!empty($allRoServiceIds)) {
                $res = $this->odooCall('object', 'execute_kw', [$this->db, $this->uid, $this->password, 'repair.service', 'read', [$allRoServiceIds], ['fields' => ['name', 'quantity', 'price_unit', 'price_subtotal']]]);
                if ($res['success']) foreach ($res['result'] as $rs) $roServicesMap[$rs['id']] = $rs;
            }

            // Fetch Bill Lines
            $billLinesMap = [];
            if (!empty($allLineIds)) {
                $res = $this->odooCall('object', 'execute_kw', [$this->db, $this->uid, $this->password, 'account.move.line', 'read', [$allLineIds], ['fields' => ['move_id', 'name', 'quantity', 'price_unit', 'price_subtotal', 'display_type', 'debit', 'credit', 'balance']]]);
                if ($res['success']) {
                    foreach ($res['result'] as $bl) {
                        $mId = is_array($bl['move_id']) ? $bl['move_id'][0] : $bl['move_id'];
                        $billLinesMap[$mId][] = $bl;
                    }
                }
            }
            
            // Fetch RO Linked Moves (Sales Invoices for Revenue Tax)
            $roMovesMap = [];
            if (!empty($allRoMoveIds)) {
                $res = $this->odooCall('object', 'execute_kw', [$this->db, $this->uid, $this->password, 'account.move', 'read', [$allRoMoveIds], ['fields' => ['amount_tax']]]);
                if ($res['success']) foreach ($res['result'] as $rm) $roMovesMap[$rm['id']] = $rm;
            }

            // Fetch Vendor Bills linked to ROs (for tax amounts)
            // Map: repair_order_id => bill data
            $vendorBillsByRo = [];
            if (!empty($allVendorBillIds)) {
                $res = $this->odooCall('object', 'execute_kw', [$this->db, $this->uid, $this->password, 'account.move', 'read', [$allVendorBillIds], ['fields' => ['repair_id', 'amount_tax', 'amount_untaxed', 'amount_total', 'state']]]);
                if ($res['success']) {
                    foreach ($res['result'] as $bill) {
                        $roId = is_array($bill['repair_id']) ? $bill['repair_id'][0] : $bill['repair_id'];
                        if ($roId) {
                            // Keep the bill with highest amount_total if multiple
                            if (!isset($vendorBillsByRo[$roId]) || $bill['amount_total'] > $vendorBillsByRo[$roId]['amount_total']) {
                                $vendorBillsByRo[$roId] = $bill;
                            }
                        }
                    }
                }
            }
            // --- End Batch Pre-fetch ---

            DB::beginTransaction();
            $itemsSynced = 0;

            // Step A: Process Job Orders (Repair Orders)
            foreach ($ros as $ro) {
                try {
                $jobNo = $ro['name'];
                $chassisNo = $ro['lot_vehicle_ref'];
                $orderLotId = is_array($ro['lot_id']) ? $ro['lot_id'][0] : null;
                $plateNo = is_array($ro['lot_id']) ? $ro['lot_id'][1] : null;

                if ($orderLotId && isset($lotsMap[$orderLotId])) {
                    $lot = $lotsMap[$orderLotId];
                    if (!$chassisNo) $chassisNo = $lot['name'];
                    if (!$plateNo || preg_match('/^\d+$/', $plateNo)) $plateNo = $lot['ref'];
                }

                if (!$chassisNo) continue;

                // Sync Vehicle
                $mobil = Mobil::where('nomor_chassis', $chassisNo)->first();
                if ($mobil) {
                    $mobil->update(['nomor_polisi' => $plateNo ?: $mobil->nomor_polisi]);
                } else {
                    $mobil = Mobil::create([
                        'nomor_chassis' => $chassisNo,
                        'nomor_polisi' => substr($plateNo ?? '', 0, 25),
                        'nopol' => substr($plateNo ?? '', 0, 25),
                        'warna' => '',
                        'tanggal_pembelian' => now()->format('Y-m-d'),
                        'nomor_kk' => '',
                        'model' => '',
                        'tahun_pembuatan' => now()->format('Y'),
                        'nomor_mesin' => '',
                        'kode_sup' => '',
                    ]);
                }

                // Sync Vendor/Supplier from Repair Order (Step A fallback)
                $supplierId = '0';
                if (!empty($ro['partner_id']) && is_array($ro['partner_id'])) {
                    $supplierId = 'O-' . $ro['partner_id'][0];
                    \App\Models\Supplier::updateOrCreate(
                        ['kode_supplier' => $supplierId],
                        ['nama_supplier' => substr($ro['partner_id'][1], 0, 100)]
                    );
                }

                // Sync Head
                $headerData = [
                    'nomor_job' => $jobNo,
                    'nomor_invoice' => $jobNo, // Default to jobNo if no bill yet
                    'tanggal_job' => $ro['schedule_date'] ? Carbon::parse($ro['schedule_date'])->format('Y-m-d') : Carbon::parse($ro['create_date'])->format('Y-m-d'),
                    'tanggal_invoice' => $ro['schedule_date'] ? Carbon::parse($ro['schedule_date'])->format('Y-m-d') : Carbon::parse($ro['create_date'])->format('Y-m-d'),
                    'tanggal_close' => $ro['repair_end_datetime'] ? Carbon::parse($ro['repair_end_datetime'])->format('Y-m-d') : ($ro['schedule_date'] ? Carbon::parse($ro['schedule_date'])->format('Y-m-d') : Carbon::parse($ro['create_date'])->format('Y-m-d')),
                    'nomor_chassis' => substr($chassisNo, 0, 50),
                    'posisi_km' => $ro['km_pickup'] ?? 0,
                    'mtrs' => $ro['km_pickup'] ?? 0,
                    'keterangan' => substr($this->sanitizeText(strip_tags($ro['compute_job_card_repair_notes'] ?? '')), 0, 255),
                    'nomor_sv' => substr($ro['service_type'] ?? '', 0, 50),
                    'id_customer' => 0,
                    'sup_invoice' => 0,
                    'pajak' => '0',
                    'kode_sup' => $supplierId,
                    'kode_servis' => 0,
                    'nomor_req' => '',
                    'harga_part' => 0,
                    'harga_oli' => 0,
                    'harga_lbr' => 0,
                    'harga_oth' => 0,
                    'harga_total' => 0,
                    'harga_pajak' => 0,
                    'harga_jual' => 0,
                    'harga_pajak_jual' => 0,
                    'state' => $ro['state'] ?? null,
                ];

                $htransaksi = Htransaksi::updateOrCreate(['nomor_job' => $jobNo], $headerData);

                // Sync Detail Lines (Draft from JO)
                Dtransaksi::where('nomor_invoice', $htransaksi->nomor_invoice)->delete();
                $totalJO = 0;
                
                // Process Parts (order_line_ids) from roLinesMap
                foreach ($ro['order_line_ids'] as $lid) {
                    $ld = $roLinesMap[$lid] ?? null;
                    if (!$ld) continue;

                    $subtotal = $ld['price_subtotal'];
                    if ($subtotal == 0 && $ld['price_unit'] > 0) {
                        $subtotal = $ld['price_unit'] * $ld['quantity'];
                    }

                    $totalJO += $subtotal;
                    Dtransaksi::create([
                        'nomor_invoice' => $htransaksi->nomor_invoice,
                        'deskripsi' => substr($this->sanitizeText($ld['name']), 0, 255),
                        'jumlah' => $ld['quantity'],
                        'harga' => $ld['price_unit'],
                        'value' => $subtotal,
                        'discount' => 0,
                        'mnt_grp' => '',
                        'lbr_grp' => '',
                        'note' => ''
                    ]);
                }

                // Process Services (repair_service_ids) from roServicesMap
                foreach ($ro['repair_service_ids'] as $lid) {
                    $ld = $roServicesMap[$lid] ?? null;
                    if (!$ld) continue;

                    $subtotal = $ld['price_subtotal'];
                    if ($subtotal == 0 && $ld['price_unit'] > 0) {
                        $subtotal = $ld['price_unit'] * $ld['quantity'];
                    }

                    $totalJO += $subtotal;
                    Dtransaksi::create([
                        'nomor_invoice' => $htransaksi->nomor_invoice,
                        'deskripsi' => substr($this->sanitizeText($ld['name']), 0, 255),
                        'jumlah' => $ld['quantity'],
                        'harga' => $ld['price_unit'],
                        'value' => $subtotal,
                        'discount' => 0,
                        'mnt_grp' => '',
                        'lbr_grp' => '',
                        'note' => ''
                    ]);
                }
                $roMoveId = is_array($ro['move_id']) ? $ro['move_id'][0] : $ro['move_id'];
                $roId = $ro['id'];
                $roTax = 0;
                $roHargaTotal = $totalJO;
                // Prefer vendor bill (most accurate, includes tax)
                if (isset($vendorBillsByRo[$roId])) {
                    $roTax = $vendorBillsByRo[$roId]['amount_tax'];
                    $roHargaTotal = $vendorBillsByRo[$roId]['amount_untaxed'] ?: $totalJO;
                } elseif ($roMoveId && isset($roMovesMap[$roMoveId])) {
                    $roTax = $roMovesMap[$roMoveId]['amount_tax'];
                }

                $htransaksi->update([
                    'harga_part' => $roHargaTotal,
                    'harga_total' => $roHargaTotal,
                    'harga_jual' => $roHargaTotal,
                    'harga_pajak' => $roTax,
                    'harga_pajak_jual' => $roTax,
                ]);
                $itemsSynced++;
                } catch (\Exception $roEx) {
                    Log::warning('Skipping RO due to error: ' . ($ro['name'] ?? 'unknown') . ' - ' . $roEx->getMessage());
                }
            }

            // Step B: Overwrite with Bills (More accurate financials)
            foreach ($moves as $move) {
                $rId = is_array($move['repair_id']) ? $move['repair_id'][0] : null;
                $jobNo = is_array($move['repair_id']) ? $move['repair_id'][1] : null;
                $invoiceNo = $move['name'];

                if (!$jobNo) continue;

                $htransaksi = Htransaksi::where('nomor_job', $jobNo)->first();
                if (!$htransaksi) continue;

                $calculatedTotal = $move['amount_untaxed'] ?? 0;
                $calculatedTax = $move['amount_tax'] ?? 0;

                // Sync Vendor/Supplier
                $supplierId = '0';
                if (!empty($move['partner_id']) && is_array($move['partner_id'])) {
                    $supplierId = 'O-' . $move['partner_id'][0];
                    \App\Models\Supplier::updateOrCreate(
                        ['kode_supplier' => $supplierId],
                        ['nama_supplier' => substr($move['partner_id'][1], 0, 100)]
                    );
                }

                Dtransaksi::where('nomor_invoice', $htransaksi->nomor_invoice)->delete();
                
                if (isset($billLinesMap[$move['id']])) {
                    foreach ($billLinesMap[$move['id']] as $line) {
                        if (in_array($line['display_type'], ['payment_term', 'tax'])) continue;

                        $amt = $line['price_subtotal'];

                        Dtransaksi::create([
                            'nomor_invoice' => $htransaksi->nomor_invoice,
                            'deskripsi' => substr($line['name'], 0, 255),
                            'jumlah' => $line['quantity'],
                            'harga' => $line['price_unit'] ?: ($line['quantity'] ? abs($amt/$line['quantity']) : 0),
                            'value' => $amt,
                            'discount' => 0,
                            'mnt_grp' => '',
                            'lbr_grp' => '',
                            'note' => $line['display_type'] ?? ''
                        ]);
                    }
                }

                $htransaksi->update([
                    'nomor_invoice' => $invoiceNo,
                    'kode_sup' => $supplierId,
                    'harga_part' => $calculatedTotal,
                    'harga_pajak' => $calculatedTax,
                    'harga_total' => $move['amount_total'] ?? ($calculatedTotal + $calculatedTax),
                    'tanggal_invoice' => $move['invoice_date'] ?: $htransaksi->tanggal_job
                ]);
            }

            DB::commit();
            $this->setting->update(['last_sync' => now()]);
            $this->logHistory($sourceType, 'Success', $itemsSynced, "Processed $itemsSynced JOs and matched Bills.");
            return ['success' => true, 'message' => "Synced $itemsSynced items successfully.", 'items' => $itemsSynced];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Odoo Sync Error: ' . $e->getMessage());
            $this->logHistory($sourceType, 'Failed', 0, 'Exception: ' . $e->getMessage() . " line " . $e->getLine());
            return ['success' => false, 'message' => "Error: " . $e->getMessage()];
        }
    }

    private function odooCall($service, $method, $args)
    {
        try {
            $response = Http::post("{$this->url}/jsonrpc", [
                'jsonrpc' => '2.0',
                'method' => 'call',
                'params' => [
                    'service' => $service,
                    'method' => $method,
                    'args' => $args,
                ],
                'id' => uniqid(),
            ]);

            $json = $response->json();
            
            if (isset($json['error'])) {
                return ['success' => false, 'error' => $json['error']['data']['message'] ?? $json['error']['message']];
            }

            return ['success' => true, 'result' => $json['result'] ?? null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function logHistory($source, $status, $items, $details)
    {
        ImportHistory::create([
            'source' => "Odoo ($source)",
            'status' => $status,
            'items' => $items,
            'details' => $details
        ]);
    }

    private function sanitizeText(string $text): string
    {
        // Remove invalid UTF-8 sequences
        $clean = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        // Remove non-printable and invalid XML/JSON characters
        $clean = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $clean ?? '');
        return $clean ?? '';
    }
}
