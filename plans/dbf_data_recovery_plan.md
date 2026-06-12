# DBF Data Recovery & Comparison Plan

## Objective
Properly extract ALL data from the FoxPro DBF+FPT+CDX files, compare with current MySQL data (pre-2025-12), identify discrepancies, and generate a report.

---

## Phase 1: Investigation & Tooling

### Step 1.1: Check Available Tools
- [ ] Check if PHP `dbase` extension is installed (`php -m | grep dbase`)
- [ ] Check for available CLI tools: `dbf2sql`, `dbview`, `dbfdump`
- [ ] Check if Python `dbfread` library is available (`pip list | grep dbfread`)
- [ ] **Decision:** Choose the best tool for DBF+FPT parsing. Preferred options:
  - **Option A:** PHP `dbase` extension (native, but may not support FPT/memo)
  - **Option B:** Python `dbfread` (best for FoxPro DBF+FPT, handles memo fields well)
  - **Option C:** Node.js `dbf` package (via npm)
  - **Option D:** Pure PHP custom parser for FPT memo blocks

### Step 1.2: Understand the FPT File Format
- [ ] Research FoxPro FPT (memo) file structure:
  - FPT files store memo blocks in 512-byte chunks
  - The DBF field of type `M` (Memo) stores a pointer (block number) to the FPT
  - Block 0 = FPT header, Block 1+ = actual memo data
  - Each memo block: 4 bytes (next block pointer / 0xFFFFFFFF = last block) + 4 bytes (length) + data
- [ ] Verify all FPT files exist and are readable:
  - `maintvhc.fpt` ✅ EXISTS (contradicts original document claiming it's missing)
  - `maintdet.fpt` ✅ EXISTS
  - `customer.fpt` ✅ EXISTS
  - `supplier.fpt` ✅ EXISTS
  - `vehicle.fpt` ✅ EXISTS

---

## Phase 2: Extract Data from DBF+FPT Properly

### Step 2.1: Parse Each DBF File with Memo Recovery
For each table (`maintvhc`, `maintdet`, `customer`, `supplier`, `vehicle`):

- [ ] Read the DBF header to get field definitions (name, type, length)
- [ ] Read the FPT file to build a memo block index (block_number => content)
- [ ] Parse each record in the DBF, replacing `M` type fields with actual memo content from FPT
- [ ] Output to structured format (JSON or CSV with proper field mapping)

### Step 2.2: Critical Field Mapping

#### `maintvhc.dbf` (Transaction Headers → `htransaksi`)
| DBF Field | Type | Maps To | Notes |
|-----------|------|---------|-------|
| Field 1 (id?) | N | `id` | Auto-increment |
| Field 2 (id_customer?) | N | `id_customer` | ⚠️ Always 0 in CSV, may have data in FPT memo |
| Field 3 (nomor_job) | C | `nomor_job` | |
| Field 4 (tanggal_job) | D | `tanggal_job` | |
| Field 5 (nomor_chassis) | C | `nomor_chassis` | |
| Field 6 (nomor_invoice) | C | `nomor_invoice` | |
| Field 7 (sup_invoice) | N | `sup_invoice` | |
| Field 8 (tanggal_invoice) | D | `tanggal_invoice` | |
| Field 9 (pajak) | C | `pajak` | |
| Field 10 (kode_sup) | C | `kode_sup` | |
| Field 11-14 (harga_*) | N | `harga_part/oli/lbr/oth` | |
| Field 15 (**MAINTMEMO**) | **M** | `keterangan` | **🔑 This is the critical memo field!** |
| Field 16+ (other fields) | - | `posisi_km`, `nomor_sv`, `tanggal_close` | |

#### `maintdet.dbf` (Transaction Details → `dtransaksi`)
| DBF Field | Type | Maps To | Notes |
|-----------|------|---------|-------|
| id | N | `id` | |
| nomor_invoice | C | `nomor_invoice` | ⚠️ **Leading zero issue:** CSV has `30122000077` vs MySQL `030122000077` |
| mnt_grp | C | `mnt_grp` | P=Part, G=Service/Grease, L=Labor, O=Other |
| deskripsi | C | `deskripsi` | Service description |
| jumlah | N | `jumlah` | Quantity |
| harga | N | `harga` | Unit price |
| value | N | `value` | Total |

### Step 2.3: Memo Field Parsing Strategy (MAINTMEMO)

**🔑 USER-CONFIRMED FORMAT:**
```
First line:  Customer code (e.g., "BDANAM", "SIEMENS", "HALLIBURTO")
Remaining:   Keterangan/service description (e.g., "Pergantian Ban Luar 2Pcs...")
```

**Extraction logic:**
```php
function parseMemo(string $memoText): array {
    $lines = explode("\n", trim($memoText));
    $customerCode = trim($lines[0] ?? '');  // First line = customer code
    $keterangan = trim(implode("\n", array_slice($lines, 1)));  // Rest = keterangan
    return [$customerCode, $keterangan];
}
```

**Mapping:**
- Customer code (first line) ➔ match against `customer.kode_customer` ➔ set `htransaksi.id_customer`
- Remainder (rest of lines) ➔ set `htransaksi.keterangan`

### Step 2.4: Handle the `nomor_invoice` Leading Zero Issue
- **Critical finding:** `dtransaksi` in CSV/SQL dump has `nomor_invoice` WITHOUT leading zero (e.g., `30122000077`)
- `htransaksi` has `nomor_invoice` WITH leading zero (e.g., `030122000077`)
- **Fix:** Join using `CONCAT('0', dtransaksi.nomor_invoice)` OR strip leading zero from htransaksi
- Verify this pattern is consistent across ALL records

---

## Phase 3: Compare with MySQL Data

### Step 3.1: Record Count Comparison
For each table, compare:

| Table | DBF Source Count | MySQL Count (pre-2025) | Match? |
|-------|-----------------|----------------------|--------|
| `htransaksi` | From DBF | `WHERE tanggal_job < '2025-12-08'` | |
| `dtransaksi` | From DBF | (via htransaksi join) | |
| `customer` | 127 | 127 | Should match |
| `supplier` | ~4,385 | Imported from SQL | Should match |
| `mobil` | ~14,700 | Imported from SQL | Should match |

### Step 3.2: Content Comparison (Sampling)
- [ ] For each table, compare field-by-field for a sample of 100-500 records
- [ ] Identify specific differences in field values
- [ ] Pay special attention to:
  - **`keterangan` field:** Currently empty in MySQL → should contain MAINTMEMO content
  - **`id_customer` field:** Currently 0 in MySQL → check if memo contains customer references
  - **`nomor_invoice` format differences** between htransaksi and dtransaksi

### Step 3.3: Memo Content Analysis
- [ ] Extract all non-empty memo content from `maintvhc.fpt`
- [ ] Analyze memo text patterns:
  - Does it contain customer codes/names? (e.g., "BDANAM", "PT. BANK DANAMON")
  - Does it contain service descriptions? (document says ~22% are service descriptions)
  - Does it contain any structured data that could identify customers?
- [ ] Generate a memo content sample report (first 100 non-empty memos)

---

## Phase 4: Generate Discrepancy Report

### Step 4.1: Report Structure
Generate a markdown report (`dbf_comparison_report.md`) containing:

1. **File Inventory Status**
   - Which DBF/FPT/CDX files were processed
   - File sizes and record counts
   - Any corruption or parsing issues found

2. **Record Count Comparison**
   | Table | DBF Records | MySQL Records (pre-2025) | Difference |
   |-------|-------------|------------------------|------------|
   | htransaksi | X | Y | +/-Z |
   | dtransaksi | X | Y | +/-Z |
   | customer | 127 | 127 | 0 |
   | supplier | X | Y | +/-Z |
   | mobil | X | Y | +/-Z |

3. **Memo Field Status**
   - Total memo fields in DBF: X
   - Non-empty memos: X (Y%)
   - Memos containing potential customer references: X (Y%)
   - Sample content (first 20 non-empty memos)

4. **Data Integrity Issues Found**
   - Leading zero issue in nomor_invoice
   - Trailing whitespace in character fields
   - Any field truncation or corruption

5. **Customer Link Analysis**
   - Can any customer info be recovered from memos?
   - Pattern analysis results

### Step 4.2: Validation Queries
Generate SQL queries to run against MySQL:
```sql
-- 1. Total pre-2025 records
SELECT COUNT(*) FROM htransaksi WHERE tanggal_job < '2025-12-08';

-- 2. Records with non-empty keterangan
SELECT COUNT(*) FROM htransaksi 
WHERE keterangan IS NOT NULL AND keterangan != '' 
AND tanggal_job < '2025-12-08';

-- 3. Records with non-zero id_customer
SELECT COUNT(*) FROM htransaksi 
WHERE id_customer != 0 
AND tanggal_job < '2025-12-08';

-- 4. Customer table check
SELECT COUNT(*) FROM customer;

-- 5. dtransaksi orphan check (records with no matching htransaksi)
SELECT COUNT(*) FROM dtransaksi d
LEFT JOIN htransaksi h ON CONCAT('0', d.nomor_invoice) = h.nomor_invoice
WHERE h.id IS NULL;
```

---

## Phase 5: 🔑 Reservat-Based Customer Link Recovery (NEW — Highest Priority)

### Background
The FoxPro programmer revealed that the original application uses a **separate `reservat` (reservation/contract) table** to determine which customer a vehicle belongs to at any given time. This is a **completely different approach** from the MAINTMEMO parsing previously assumed.

### FoxPro Logic
```foxpro
SELECT reservat
SET ORDER TO chassis
SEEK(m.cchasnum)
IF FOUND() AND m.djobno >= reservat.dusestrt AND m.djobno <= reservat.duseend
    m.ccodecus = reservat.ccodecus
    m.enamecus = IIF(SEEK(m.ccodecus,"customer"),customer.enamecom,'')
ENDIF
```

### Reservat Table Structure
- **File:** [`23-01-2026/reservat.dbf`](23-01-2026/reservat.dbf) (41,782 records)
- **Key fields:** `CCHASNUM` (chassis), `CCODECUS` (customer code), `DUSESTRT` (start date), `DUSEEND` (end date)
- **Not imported to MySQL** — needs to be processed directly from DBF

### Step 5.1: Run Customer Link Recovery Script

**Script:** [`scripts/link_customer_from_reservat.py`](scripts/link_customer_from_reservat.py)

```bash
# Step 1: Preview changes (dry-run)
python3 scripts/link_customer_from_reservat.py --dry-run

# Step 2: Apply updates
python3 scripts/link_customer_from_reservat.py --execute
```

**Expected coverage:** **24,132 records** (57.9% of the 41,658 unlinked)

- [ ] Run dry-run to preview changes
- [ ] Verify results are correct
- [ ] Execute to apply updates
- [ ] Verify updated counts in MySQL

### Step 5.2: Investigate Remaining 17,526 Unlinked Records

These fall into categories:
1. **Bad/invalid dates** (e.g., `0000-00-00`, year 18, 214, 220) — fix dates first
2. **Gaps between rental contracts** — maintenance during vehicle idle periods
3. **Owned fleet vehicles** — company-owned vehicles not in reservat
4. **Chassis not in reservat** — only 437 records (0.1%)

- [ ] Identify records with invalid dates
- [ ] Check if MAINTMEMO can help with remaining records
- [ ] For truly unlinkable, consider labeling as "OWN FLEET" or "UNKNOWN"

### Step 5.3: Fix Odoo Sync (Post-2025 Data)

- [ ] Add `partner_id` → `id_customer` mapping in [`OdooSyncService.php`](app/Services/OdooSyncService.php#L281)
- [ ] This would fix **7,672 Odoo-era records** that currently have `id_customer = 0`

### Step 5.4: Fix `nomor_invoice` Leading Zero (If Still Needed)
- [ ] Verify the leading zero issue is resolved (current report shows ✅ No issue)
- [ ] If needed, run migration to normalize

### Step 5.5: Re-import Missing Records
- [ ] For any records found in DBF but missing in MySQL, generate INSERT statements
- [ ] For records with different data (e.g., now with memo content), generate UPDATE statements
- [ ] Create a Laravel command: `php artisan dbf:recover-missing`

---

## Phase 6: Update DBF_DATA_ANALYSIS.md

### Step 6.1: Corrections to Current Document
The current [`DBF_DATA_ANALYSIS.md`](DBF_DATA_ANALYSIS.md) has several inaccuracies that should be corrected:

1. **`maintvhc.fpt` status:** Document says **MISSING** → **EXISTS** (user added it)
2. **Memo content lost:** Update to reflect that we CAN now attempt recovery
3. **Recommendation 1:** Update from "URGENT: Extract MAINTMEMO" to "Now possible with existing FPT file"

### Step 6.2: Append Comparison Results
Add the comparison findings and final status to the document.

---

## File Reference Summary

| Item | Path |
|------|------|
| DBF/FPT/CDX Files | [`23-01-2026/`](23-01-2026/) |
| DBF Data Analysis | [`DBF_DATA_ANALYSIS.md`](DBF_DATA_ANALYSIS.md) |
| Htransaksi Model | [`app/Models/Htransaksi.php`](app/Models/Htransaksi.php) |
| Customer Model | [`app/Models/Customer.php`](app/Models/Customer.php) |
| **Reservat DBF (NEW)** | [`23-01-2026/reservat.dbf`](23-01-2026/reservat.dbf) — 41,782 records |
| **Customer Link Script (NEW)** | [`scripts/link_customer_from_reservat.py`](scripts/link_customer_from_reservat.py) |
| Mobil Model | [`app/Models/Mobil.php`](app/Models/Mobil.php) |
| Supplier Model | [`app/Models/Supplier.php`](app/Models/Supplier.php) |
| Odoo Sync Service | [`app/Services/OdooSyncService.php`](app/Services/OdooSyncService.php) |
| MainController | [`app/Http/Controllers/MainController.php`](app/Http/Controllers/MainController.php) |
| VehicleTransactionController | [`app/Http/Controllers/VehicleTransactionController.php`](app/Http/Controllers/VehicleTransactionController.php) |

## Key Findings So Far

1. **`reservat.dbf` is the KEY to customer linking** — 41,782 rental contract records mapping chassis + date range → customer code
2. **FoxPro app does NOT use `htransaksi.id_customer`** — it looks up customer via `reservat` table using chassis number + job date
3. **24,132 of 41,658 unlinked records (58%) can be linked via reservat** — all matched customer codes exist in MySQL ✅
4. **MAINTMEMO first-line parsing was ALREADY applied** — 53,059 records already linked via that method
5. **17,526 records remain unlinkable** — bad dates, owned fleet vehicles, or gaps between rental contracts
6. **`maintvhc.fpt` EXISTS** — memo file for transaction headers is available
7. **`keterangan` field** in MySQL — still needs to be populated from MAINTMEMO if desired
8. **Leading zero issue** — ✅ No longer an issue (report shows resolved)
9. **Odoo sync skips `partner_id` mapping** — affects **7,672 post-2025 records** with `id_customer = 0`
