# DBF vs MySQL Comparison Report
**Generated:** 2026-05-28T14:15:29.643136

## 1. Record Count Comparison

| Table | DBF Source | MySQL (Total) | MySQL (Pre-2025) | Difference |
|-------|-----------|---------------|------------------|------------|
| htransaksi (maintvhc) | 93,539 | 102,249 | 94,717 | -1,178 |
| dtransaksi (maintdet) | 309,451 | 373,300 | - | -63,849 |
| customer | 1,839 | 1,133 | - | 706 |
| supplier | 4,353 | 5,587 | - | -1,234 |
| mobil (vehicle) | 14,703 | 14,786 | - | -83 |

## 2. Memo Field Analysis

### DBF Memo Status
- Total records in maintvhc.dbf: **93,539**
- Records with non-empty MAINTMEMO: **91,977** (98.3%)

### MySQL Keterangan Status (Pre-2025)
- MySQL records (pre-2025): **94,717**
- With non-empty keterangan: **93,148**
- With customer link (id_customer > 0): **53,059**
- Missing customer link (id_customer = 0): **41,658**

**Conclusion:** The DBF has memo content for ~92K records, but MySQL keterangan is mostly empty.

## 3. Memo Content Pattern Analysis

Analyzed first 20,000 records for patterns.
Records with potential customer codes (first line): **11143**

### Sample Memos (First 20)

| Job No | First Line | Full Length |
|--------|-----------|-------------|
| 091821000209 | PETRINTSRG | 11 |
| 081821000187 | PETRINTSRG | 11 |
| 091821000213 | PETRINTSRG | 11 |
| 091821000217 | PETRINTSRG | 11 |
| 081821000189 | PETRINTSRG | 11 |
| 081821000190 | PETRINTSRG | 11 |
| 090121006218 | Stock For Rent | 15 |
| 091821000230 | PETRINTSRG | 11 |
| 090121006232 | PGNSUMJA | 15 |
| 090121006246 | AELINDON | 185 |
| 091221005440 | Stock For Rent | 26 |
| 091821000232 | PETRINTSRG | 11 |
| 091821000233 | PETRINTSRG | 11 |
| 091821000234 | PETRINTSRG | 11 |
| 091021000744 | Stock For Rent | 15 |
| 090321000225 | PERMIEPCBN | 26 |
| 091821000236 | PETRINTSRG | 11 |
| 091821000240 | PETRINTSRG | 11 |
| 091821000247 | PETRINTSRG | 11 |
| 091821000249 | PETRINTSRG | 11 |

### Top First Line Patterns

| Pattern | Count |
|---------|-------|
| Stock For Rent | 1366 |
| PERMIEPSGT | 1329 |
| PERMIEPTJG | 629 |
| BOBBSPHULU | 599 |
| BMANDR | 467 |
| PGNSUMJA | 400 |
| PERTAGEKMG | 387 |
| SHARPELECT | 385 |
| PERMIRUBAL | 338 |
| PERMIEPPAP | 320 |
| KSTEELENGC | 291 |
| PERTAMIDMI | 283 |
| PGASSOLUTI | 265 |
| PERMIEPCPU | 261 |
| Operation | 254 |
| KISELKOPER | 252 |
| PERTAMIGEN | 245 |
| PERMIEPSNG | 219 |
| BPBERAU | 167 |
| KSTEELKONS | 157 |

### Potential Customer Codes Found

| Job No | Customer Code | Rest of Memo |
|--------|--------------|--------------|
| 091821000209 | PETRINTSRG |  |
| 081821000187 | PETRINTSRG |  |
| 091821000213 | PETRINTSRG |  |
| 091821000217 | PETRINTSRG |  |
| 081821000189 | PETRINTSRG |  |
| 081821000190 | PETRINTSRG |  |
| 091821000230 | PETRINTSRG |  |
| 090121006232 | PGNSUMJA |  |
| 090121006246 | AELINDON | LAst SR km 10.097 tgl 01/11/08 



Note : mohon buatkan estimasi jika ada penggantian part diluar se |
| 091821000232 | PETRINTSRG |  |
| 091821000233 | PETRINTSRG |  |
| 091821000234 | PETRINTSRG |  |
| 090321000225 | PERMIEPCBN | Service Rutin |
| 091821000236 | PETRINTSRG |  |
| 091821000240 | PETRINTSRG |  |
| 091821000247 | PETRINTSRG |  |
| 091821000249 | PETRINTSRG |  |
| 090121006348 | BPWESTJAVA | Gt Ban Dlp 225/55 R16 SP2000 2 Pcs ( Tipis )
P. List 2 Rp. 1.293.000
Disc 30% @ Rp. 905.100
Spooring |
| 091021000749 | COPHILRAM |  |
| 091021000750 | COPHILRAM |  |

## 4. Invoice Join Analysis (Leading Zero Issue)

### dtransaksi nomor_invoice prefixes
- Prefix '1': 85,045 records
- Prefix '2': 224,894 records
- Prefix '3': 7 records
- Prefix '4': 312 records
- Prefix '5': 399 records
- Prefix '6': 706 records
- Prefix '7': 624 records
- Prefix '8': 536 records
- Prefix '9': 1,276 records
- Prefix 'B': 12,378 records
- Prefix 'J': 47,121 records
- Prefix 'R': 2 records

### htransaksi nomor_invoice prefixes (pre-2025)
- Prefix ' ': 4,300 records
- Prefix '0': 1,387 records
- Prefix '1': 24,598 records
- Prefix '2': 64,429 records
- Prefix 'B': 2 records
- Prefix 'J': 1 records

### Orphan dtransaksi records
- Without leading zero fix (direct join): 41,353
- With leading zero fix (CONCAT '0'): 369,463

**✅ No leading zero issue detected.**

## 5. Customer Link Status

- Total pre-2025 records: **94,717**
- Linked to customer (id_customer > 0): **53,059**
- Not linked (id_customer = 0): **41,658**
- Customer table records: **1,133**

**✅ Customer links ALREADY exist** - the DBF-to-customer mapping was already done for many records.

## 6. Summary of Discrepancies

- **Record count mismatch:** DBF has 93,539 records, MySQL has 94,717 pre-2025 records (diff: -1,178)
- **Customer count mismatch:** DBF has 1,839, MySQL has 1,133
- **Invoice join issue:** 41,353 dtransaksi records can't join to htransaksi without fixing leading zeros
- **Unlinked records:** 41,658 pre-2025 records still have id_customer = 0

---
*Report generated at 2026-05-28T14:15:33.809644*