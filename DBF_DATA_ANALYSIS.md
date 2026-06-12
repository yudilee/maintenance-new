# DBF Data vs Current Database Analysis

**Generated:** May 28, 2026
**Updated:** May 28, 2026 (reservat table analysis added)

## Executive Summary

**Critical Finding:** The DBF-era data (pre-2025) does **NOT have a direct customer link** in the `htransaksi` table. However, **two alternative methods** can recover customer links:

| Method | Records Linkable | Status |
|--------|-----------------|--------|
| **MAINTMEMO first-line parsing** | Already done for **53,059 records** | ✅ 53,059 already linked |
| **Reservat table (chassis + date range)** | **24,132 more records** linkable | 🔧 Script ready — not yet executed |
| **Still unlinkable** | **17,526 records** | ❌ Need investigation |

The original FoxPro application does NOT store customer ID directly in `htransaksi`. Instead, it uses a **separate `reservat` (reservation/contract) table** that maps chassis numbers + date ranges to customers.

---

## File Structure Comparison

### Source Files (DBF Export)
Located in `/home/yudi/dev/maintenance_new/23-01-2026/`

| File | Format | Purpose |
|------|--------|---------|
| `customer.csv` / `.dbf` | Customer Master | 127 records (id, kode_customer, nama_customer) |
| `supplier.csv` / `.dbf` | Supplier Master | 30+ records (kode_supplier, nama_supplier) |
| `vehicle.csv` / `.dbf` | Vehicle Master | 200+ records (chassis, polisi, color, year) |
| `maintdet.csv` / `.dbf` | Transaction Details | Part/labor line items |
| `maintvhc.csv` / `.dbf` | Transaction Headers | Job records (htransaksi) |

### Current Database Schema

```
customer (Laravel model)
├── id (PK)
├── kode_customer (e.g., BDANAM, SIEMENS)
└── nama_customer (e.g., PT. BANK DANAMON INDONESIA)

htransaksi (Transaction Headers)
├── id (PK)
├── id_customer (FK) → customer.id ⚠️ **ALWAYS 0 FOR DBF DATA**
├── nomor_job (e.g., 030121000077)
├── nomor_chassis (vehicle reference)
├── kode_sup (supplier code)
├── tanggal_job, tanggal_invoice, tanggal_close (dates)
├── harga_part, harga_oli, harga_lbr, harga_oth (cost breakdown)
└── ... (other fields)

dtransaksi (Transaction Details)
├── nomor_invoice (FK to htransaksi)
├── deskripsi (description)
├── jumlah (quantity)
├── harga (unit price)
├── value (total)
└── ...
```

---

## Key Findings

### 1. Customer Link Status

#### ⚠️ DBF Data (2003-2024) - **HAS MEMO FIELD BUT LOST IN EXPORT**

**Critical Discovery:** The original DBF structure has:
```
Field 15: MAINTMEMO (Type=M, Memo field, 4 bytes)
```

However, the **CSV/SQL export lost the memo content!**

**Current exported data shows:**
- **`id_customer` field:** Always `0` in exported SQL
- **`keterangan` field:** Empty in exported data (originally mapped from MAINTMEMO)
- **Sample records from htransaksi(3).sql:**
  ```
  (1, 0, '030121000077', '2003-10-27', 'S76U15930', ...)
  (2, 0, '030121000098', '2003-10-09', 'S76U15930', ...)
  (3, 0, '030121000104', '2003-10-27', '31032423009095', ...)
  ```

**Original DBF Fields (40 total):**
- Field 15: **MAINTMEMO** (contains customer/transaction memo)
- Field 3: CCHASNUM (chassis number)
- Field 7: CCODESUP (supplier code)
- (+ 36 other fields)

**Conclusion:** Customer link IS embedded in the MAINTMEMO field of the original DBF, but was NOT extracted in the CSV conversion

**UPDATE - Memo Field Analysis (CSV Export):**
- Checked keterangan column (field 17) in maintvhc.csv
- Found **221+ non-empty memo rows in first 1000 rows** (~22% populated)
- **CRITICAL:** Memo content is SERVICE DESCRIPTIONS, NOT customer codes
- Sample content:
  - "Pergantian Ban Luar 2Pcs BRIGSTONE 235/75 R 15 D 689 T (Price List: Rp. 741.000)"
  - "Service Rutin 10000 / Pergantian Oli Mesin + Filter"
  - "Check Rem 4 Roda / Rem ada bunyi"
  - "Engine Tune Up / Mesin Terasa Berat Tariknya"

**FPT File Status:**
- `maintvhc.fpt` (memo blocks file) **NOW EXISTS** (was previously reported missing)
- Other FPT files exist: customer.fpt, maintdet.fpt, supplier.fpt, vehicle.fpt
- MAINTMEMO content can now be recovered from the FPT file

#### ❌ Odoo Data (2025-12-08+)
- **Current code:** `'id_customer' => 0` in [OdooSyncService.php](app/Services/OdooSyncService.php#L281)
- **Reason:** Odoo uses `partner_id` for customer, not directly mapped to legacy `customer` table
- **Conclusion:** Odoo sync also skips customer mapping
- **Odoo records (post 2025-12-08):** 7,787 total, only 115 linked, **7,672 unlinked**

### 2. 🔑 New Discovery: Reservat Table (Reservation/Contract)

The FoxPro programmer revealed that the original application uses a **separate `reservat` (reservation) table** to look up customer information. This is the PRIMARY method used in the FoxPro app.

#### FoxPro Logic
```foxpro
SELECT reservat
SET ORDER TO chassis          && Index on chassis number
SEEK(m.cchasnum)              && Search by chassis number
IF FOUND() AND m.djobno >= reservat.dusestrt AND m.djobno <= reservat.duseend
    m.ccodecus = reservat.ccodecus
    m.enamecus = IIF(SEEK(m.ccodecus,"customer"),customer.enamecom,'')
ENDIF
```

**Translation to application logic:**
1. Open `reservat` table, ordered by chassis number index
2. Search for the current job's chassis number
3. If found AND the job date falls within the reservation's start-to-end date range
4. Then use the customer code from the reservation
5. Look up the customer name from the `customer` table

#### Reservat DBF Structure
File: [`23-01-2026/reservat.dbf`](23-01-2026/reservat.dbf) (41,782 records)

| Field | Type | Description | Maps To |
|-------|------|-------------|---------|
| `CCODECON` | C(12) | Contract code | — |
| `CCODERES` | C(12) | Reservation code | — |
| `DDATERES` | D | Reservation date | — |
| `CCODECUS` | C(10) | **Customer code** | `customer.kode_customer` |
| `EUSENAME` | C(80) | Customer/User name | — |
| `EUSEADDR` | C(60) | Address | — |
| `DUSESTRT` | T | **Usage start date** | Compare with `htransaksi.tanggal_job` |
| `DUSEEND` | T | **Usage end date** | Compare with `htransaksi.tanggal_job` |
| `FTYPECON` | C(1) | Contract type ('Y'=Rental, 'H', 'D', 'M') | — |
| `CPOLINUM` | C(11) | Police number | — |
| `CCHASNUM` | C(20) | **Chassis number** | `htransaksi.nomor_chassis` |
| `CMODEL` | C(15) | Vehicle model | — |
| `PRICENET` | N(9) | Net price | — |
| `PRICERENT` | N(9) | Rental price | — |

#### How the Customer Link Works

The `reservat` table represents **rental contracts** — each record says "Customer X rented Vehicle Y from Date A to Date B". A vehicle can have multiple sequential contracts with different customers.

```
Example:
  Chassis:  WDC1631132X727333
  ┌──────────┬──────────────┬────────────┬──────────────┐
  │ Customer │ From         │ To         │              │
  ├──────────┼──────────────┼────────────┼──────────────┤
  │ BSCB     │ 2003-12-02   │ 2003-12-02 │ (1 day)      │
  │ BSCB     │ 2003-12-08   │ 2005-12-07 │ (~2 years)   │
  │ BSCB     │ 2005-12-08   │ 2006-01-07 │              │
  │ SANTOS   │ 2006-09-30   │ 2006-10-03 │              │
  │ CGT      │ 2006-11-13   │ 2007-01-12 │              │
  │ OTHER    │ 2007-02-13   │ 2007-05-12 │              │
  └──────────┴──────────────┴────────────┴──────────────┘
  
  If a job's tanggal_job = 2004-06-15, customer = BSCB
  If a job's tanggal_job = 2006-10-01, customer = SANTOS
  If a job's tanggal_job = 2003-12-05 → NO MATCH (gap between contracts)
```

**Key insight:** This is the FoxPro application's actual method for determining which customer a maintenance job belongs to. It was **never exported** in the CSV/SQL dump because only specific tables were migrated.

#### Customer Link Recovery Statistics

| Category | Count | % of Unlinked |
|----------|-------|--------------|
| **Can be linked via reservat** (chassis match + date overlap) | **24,132** | **57.9%** |
| Chassis found but NO date overlap | 17,062 | 41.0% |
| Chassis NOT in reservat at all | 437 | 1.0% |
| No chassis number | 27 | 0.1% |
| **Total unlinked pre-2025 records** | **41,658** | 100% |

**All matched customer codes exist in the MySQL `customer` table** ✅ (329 unique codes, all found)

#### Top Customers Identifiable via Reservat

| Customer Code | Customer Name | Records |
|---------------|--------------|---------|
| `PERMIEPSNG` | PT. PERTAMINA EP ASSET 5 - FIELD SANGASA | 7,342 |
| `BOBBSPHULU` | BOB PT. BSP - PERTAMINA HULU | 4,082 |
| `BMANDR` | PT. BANK MANDIRI (PERSERO) TBK | 2,288 |
| `INBISCO` | PT. INBISCO NIAGATAMA SEMESTA | 2,010 |
| `SHARPELECT` | PT. SHARP ELECTRONICS INDONESIA | 577 |
| `JAKARTAINT` | PT. JAKARTA INTERNATIONAL CONTAINER TERMINAL | 447 |
| `TIRTAFRESI` | PT. TIRTA FRESINDO JAYA | 379 |
| `HALLIBURTO` | PT. HALLIBURTON INDONESIA | 378 |

#### Why 17,062 Records Have No Date Overlap

The "no overlap" cases occur when:
1. **Job was done before/after rental period** — e.g., vehicle serviced 5 days before a rental contract starts
2. **Gap between contracts** — e.g., vehicle returned Dec 2, next contract starts Dec 8, maintenance on Dec 5 falls in between
3. **Vehicle was owned (non-rental)** — the company's own fleet vehicles would not be in the reservat table
4. **Bad/incomplete data** — some dates may be zero (`0000-00-00`) or invalid

**Note:** The MAINTMEMO first-line customer code parsing (already applied to 53,059 records) may help recover some of these. But for the remaining unlinked records, the MAINTMEMO approach was already exhausted — the unlinked records either lack MAINTMEMO entries in the DBF or the memo first-line doesn't match a known customer code.

### 3. Alternative Link Methods

### 2. Alternative Link Methods

#### Via `kode_sup` (Supplier Code)
- **DBF** uses `kode_sup` to reference supplier
- **Current app** tries to match this to `supplier` table
- **Status:** ⚠️ Partial - only links to supplier, not customer

#### Via Vehicle (`nomor_chassis`)
- **DBF** stores vehicle chassis number
- **Current app** has `Mobil` model with `nomor_chassis` as PK
- **Link path:** `htransaksi` → `nomor_chassis` → `mobil` → (no customer link in Mobil)
- **Status:** ⚠️ Weak - vehicle doesn't have customer reference in DBF era

#### Via `customer.csv` Reference
- **Available data:** 127 customers in customer.csv
- **Mapping field:** None! The CSV customers are standalone
- **Status:** ❌ No transaction-to-customer mapping exists

### 3. Data Integrity Issues

#### Missing Links
```
htransaksi records:    ~8,000+ (2003-2004 visible)
customer records:      127
Link field (id_customer): ALL ZEROS
```

#### Orphaned Customer Data
- Customer table has 127 records
- None are referenced by any htransaksi record
- These appear to be unused/reference data only

---

## Data Volume Summary

### From DBF Files (Actual Data)
| DBF File | Row Count | Maps To | Notes |
|----------|-----------|---------|-------|
| `maintvhc.dbf` | **93,539** | `htransaksi` | Transaction headers (2003-2025) |
| `maintdet.dbf` | **309,451** | `dtransaksi` | Transaction details |
| `customer.dbf` | **1,839** | `customer` | Customer master |
| `supplier.dbf` | **4,353** | `supplier` | Supplier codes |
| `vehicle.dbf` | **14,703** | `mobil` | Vehicle fleet |
| `reservat.dbf` | **41,782** | *Not imported* | 🔑 Rental contracts (customer links) |

### Current Database Status
```sql
SELECT COUNT(*) FROM customer;                → 1,133
SELECT COUNT(*) FROM htransaksi;              → 102,249 (total)
SELECT COUNT(*) FROM htransaksi
  WHERE tanggal_job < '2025-12-08';           → 94,717 (pre-Odoo)
SELECT COUNT(*) FROM htransaksi
  WHERE tanggal_job < '2025-12-08'
    AND id_customer > 0;                      → 53,059 (already linked)
SELECT COUNT(*) FROM htransaksi
  WHERE tanggal_job < '2025-12-08'
    AND id_customer = 0;                      → 41,658 (unlinked — target for recovery)
SELECT COUNT(*) FROM htransaksi
  WHERE tanggal_job >= '2025-12-08';          → 7,787 (Odoo era)
SELECT COUNT(*) FROM htransaksi
  WHERE tanggal_job >= '2025-12-08'
    AND id_customer = 0;                      → 7,672 (Odoo unlinked — needs Odoo fix)
```

---

## Implications

### For Data Analysis

| Question | Answer | Source |
|----------|--------|--------|
| Can we link DBF transactions to customers? | **YES — 77,191/94,717 (81.5%)** | 53,059 via MAINTMEMO + **24,132 via reservat** |
| Can we trace which customer used which vehicle? | **YES** | Via reservat table (chassis + date range) |
| Is customer master data populated? | **YES** | 1,133 records present |
| Are customers in transactions? | **MOSTLY** | 77,191 linkable via reservat + MAINTMEMO |
| Can we still recover the remaining 17,526? | **PARTIALLY** | Some may be owned fleet vehicles, some have bad dates |

### For Integration

**Problem:** Odoo data (post-2025) also doesn't populate `id_customer`
```php
// From OdooSyncService.php line 281
'id_customer' => 0,  ← ❌ Always zero, no mapping logic
```

**Suggestion:** Odoo may store customer info differently:
- `partner_id` (Odoo customer) vs `vendor_id` (supplier/workshop)
- Current code maps `vendor_id` → `kode_sup` only
- Should add logic to map `partner_id` → `id_customer`

---

## Recommendations

### ✅ COMPLETED: MAINTMEMO First-Line Parsing (53,059 linked)

The MAINTMEMO field parsing (first-line customer codes) has **already been applied** to the database — 53,059 records have `id_customer > 0`. This method is now considered complete.

### 1. 🔥 **PRIORITY: Execute Reservat Customer Link Recovery**

The `reservat` table provides the **FoxPro application's actual customer linking logic** (chassis + date range lookup). A Python script is ready:

**Script:** [`scripts/link_customer_from_reservat.py`](scripts/link_customer_from_reservat.py)

```bash
# Step 1: Dry-run to preview changes
python3 scripts/link_customer_from_reservat.py --dry-run

# Step 2: Execute to apply updates
python3 scripts/link_customer_from_reservat.py --execute
```

**Expected results:**
- **24,132 records** will be linked to customers
- **17,526 records** will remain unlinked (chassis not in reservat or no date overlap)
- All matched customer codes (329 unique) exist in MySQL ✅

### 2. **Investigate Remaining 17,526 Unlinked Records**

For the records that cannot be linked via reservat:
- **Some have invalid/bad dates** (`0000-00-00`, year 18, 214, etc.) — fix dates first, then retry
- **Some are owned fleet vehicles** — the company's own vehicles won't have reservat entries
- **Some have date gaps** — maintenance happened between rental contracts

**Recommended approach:**
```sql
-- Find records with bad dates
SELECT COUNT(*) FROM htransaksi
WHERE tanggal_job < '2025-12-08'
  AND id_customer = 0
  AND (YEAR(tanggal_job) < 2000 OR YEAR(tanggal_job) > 2025);

-- Check if MAINTMEMO has customer info for remaining unlinked
-- (from DBF, not MySQL, since MySQL may not have the memo data)
```

### 3. **Fix Odoo Sync to Populate `id_customer` (7,672 records)**

Current code sets `'id_customer' => 0` for all Odoo-imported records. This affects **7,672 post-2025 records**.

**File:** [`app/Services/OdooSyncService.php`](app/Services/OdooSyncService.php#L281)
```php
// Before:
'id_customer' => 0,

// After: Try to match Odoo partner to customer
'id_customer' => function() {
    $customerId = $this->matchCustomerFromOdoo($ro['partner_id']) ?? 0;
    return $customerId;
}()
```

---

## File Locations

| Item | Path |
|------|------|
| DBF Exports | `/home/yudi/dev/maintenance_new/23-01-2026/` |
| Models | `app/Models/` (Htransaksi, Customer, Mobil, Supplier) |
| Import Logic | `app/Services/OdooSyncService.php` |
| Controllers | `app/Http/Controllers/` (MainController, VehicleTransactionController) |

---

## Next Steps

1. **Confirm** whether customer mapping was lost in DBF → CSV conversion
2. **Check** FoxPro original schema for hidden relationship
3. **Implement** customer matching if mapping is recoverable
4. **Update** Odoo sync to populate customer data correctly
5. **Document** the customer relationship rules for future imports

---

**Status:** ⚠️ No active customer links found in legacy data. All `id_customer` values are zero.
