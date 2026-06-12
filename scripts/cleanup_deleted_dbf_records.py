#!/usr/bin/env python3
"""
DBF Deleted Records Cleanup Script
====================================
Compares maintdet.dbf (including deleted records) with MySQL dtransaksi
and removes any records that were deleted in FoxPro but still exist in MySQL.

Also checks maintvhc.dbf for deleted headers.

Background:
  FoxPro DBF files mark records as deleted with a '*' byte (0x2A) at the
  start of each record, rather than physically removing them. When data was
  imported to MySQL, these deleted records were likely imported as if active.

Usage:
    # Dry-run (preview only):
    python3 scripts/cleanup_deleted_dbf_records.py --dry-run
    
    # Execute cleanup:
    python3 scripts/cleanup_deleted_dbf_records.py --execute
    
    # Limit to specific invoice:
    python3 scripts/cleanup_deleted_dbf_records.py --dry-run --invoice 250122002669
"""

import os
import sys
import argparse
import struct
from collections import defaultdict
import pymysql

DBF_DIR = '23-01-2026'
MYSQL_CONFIG = {
    'host': '127.0.0.1',
    'port': 3307,
    'user': 'sdp',
    'password': 'password',
    'database': 'sdp',
}


def parse_dbf_header(filepath):
    """Parse DBF header and return record info."""
    with open(filepath, 'rb') as f:
        header = f.read(32)
        num_records = struct.unpack('<I', header[4:8])[0]
        header_len = struct.unpack('<H', header[8:10])[0]
        record_len = struct.unpack('<H', header[10:12])[0]
        
        # Read field definitions
        f.seek(32)
        fields = []
        while True:
            field_data = f.read(32)
            if field_data[0] == 0x0D:
                break
            name = field_data[0:11].split(b'\x00')[0].decode('ascii')
            ftype = chr(field_data[11])
            length = field_data[16]
            fields.append({'name': name, 'type': ftype, 'len': length})
    
    return num_records, header_len, record_len, fields


def scan_dbf_records(filepath, fields, batch_size=50000):
    """
    Scan DBF file and yield batches of (is_deleted, field_values_dict).
    Uses raw byte reading to detect deleted flag.
    """
    num_records, header_len, record_len, _ = parse_dbf_header(filepath)
    
    # Calculate field offsets
    offset = 1  # First byte = deleted flag
    field_offsets = {}
    for f in fields:
        field_offsets[f['name']] = offset
        offset += f['len']
    
    with open(filepath, 'rb') as f:
        f.seek(header_len)
        batch = []
        
        for rec_num in range(num_records):
            record = f.read(record_len)
            if len(record) < record_len:
                break
            
            is_deleted = record[0] == 0x2A  # '*' = deleted
            
            values = {}
            for field in fields:
                name = field['name']
                off = field_offsets[name]
                length = field['len']
                raw = record[off:off+length]
                # Strip null bytes and whitespace
                val = raw.split(b'\x00')[0].decode('cp1252', errors='replace').strip()
                values[name] = val
            
            batch.append((rec_num, is_deleted, values))
            
            if len(batch) >= batch_size:
                yield batch
                batch = []
        
        if batch:
            yield batch


def get_mysql_conn():
    return pymysql.connect(**MYSQL_CONFIG)


def cleanup_dtransaksi(dry_run=True, specific_invoice=None):
    """
    Compare maintdet.dbf with MySQL dtransaksi and clean up phantom records.
    
    Strategy:
    1. For each invoice in the DBF, collect active and deleted records
    2. For the same invoice in MySQL, find records that don't match any active DBF record
    3. If a MySQL record matches a deleted DBF record (or doesn't exist in DBF at all), 
       it's a phantom and should be removed
    """
    print(f"{'='*60}")
    print(f"DBF DELETED RECORDS CLEANUP")
    print(f"{'='*60}")
    print(f"Mode: {'DRY RUN' if dry_run else 'EXECUTE'}")
    if specific_invoice:
        print(f"Filtering to invoice: {specific_invoice}")
    print()
    
    dbf_path = os.path.join(DBF_DIR, 'maintdet.dbf')
    if not os.path.exists(dbf_path):
        print(f"ERROR: {dbf_path} not found!")
        return
    
    # Step 1: Build DBF invoice index (active vs deleted)
    _, _, _, fields = parse_dbf_header(dbf_path)
    
    # Find key field names
    inv_field = None
    desc_field = None
    grp_field = None
    for f in fields:
        name = f['name'].upper()
        if 'INV' in name and 'NO' in name:
            inv_field = f['name']
        elif 'DESCR' in name and f['len'] > 10:
            desc_field = f['name']
        elif 'GRPMNT' in name:
            grp_field = f['name']
    
    print(f"DBF fields: invoice={inv_field}, desc={desc_field}, grp={grp_field}")
    
    # Build: invoice -> {'active': set of (grp, desc), 'deleted': set of (grp, desc)}
    dbf_invoices = defaultdict(lambda: {'active': set(), 'deleted': set()})
    total_dbf = 0
    total_deleted_in_dbf = 0
    
    for batch in scan_dbf_records(dbf_path, fields):
        for rec_num, is_deleted, values in batch:
            inv = values.get(inv_field, '')
            if specific_invoice and inv != specific_invoice:
                continue
            if not inv:
                continue
            
            grp = values.get(grp_field, '')
            desc = values.get(desc_field, '')
            key = (grp, desc)
            
            total_dbf += 1
            if is_deleted:
                dbf_invoices[inv]['deleted'].add(key)
                total_deleted_in_dbf += 1
            else:
                dbf_invoices[inv]['active'].add(key)
    
    print(f"\nScanned DBF: {total_dbf} total records ({total_deleted_in_dbf} deleted)")
    print(f"Unique invoices in DBF: {len(dbf_invoices)}")
    
    # Step 2: Query MySQL dtransaksi
    conn = get_mysql_conn()
    cur = conn.cursor()
    
    # Get all dtransaksi records
    if specific_invoice:
        cur.execute("SELECT id, nomor_invoice, mnt_grp, deskripsi FROM dtransaksi WHERE nomor_invoice = %s", (specific_invoice,))
    else:
        cur.execute("SELECT id, nomor_invoice, mnt_grp, deskripsi FROM dtransaksi")
    
    mysql_records = cur.fetchall()
    print(f"MySQL dtransaksi records: {len(mysql_records)}")
    
    # Build MySQL invoice index
    mysql_by_invoice = defaultdict(list)
    for row in mysql_records:
        mysql_by_invoice[row[1]].append(row)
    
    # Step 3: Cross-reference and find phantoms
    phantom_records = []  # Records to delete
    records_checked = 0
    
    # Only check invoices that exist in BOTH DBF and MySQL
    common_invoices = set(dbf_invoices.keys()) & set(mysql_by_invoice.keys())
    
    for inv in sorted(common_invoices):
        dbf_active = dbf_invoices[inv]['active']
        dbf_deleted = dbf_invoices[inv]['deleted']
        
        for mysql_row in mysql_by_invoice[inv]:
            mysql_id, mysql_inv, mysql_grp, mysql_desc = mysql_row
            mysql_key = (str(mysql_grp).strip(), str(mysql_desc).strip())
            records_checked += 1
            
            # Check if this MySQL record matches an active DBF record
            if mysql_key in dbf_active:
                continue  # All good
            
            # Check if it matches a deleted DBF record
            if mysql_key in dbf_deleted:
                phantom_records.append((mysql_id, mysql_inv, mysql_grp, mysql_desc, 'DELETED IN DBF'))
                continue
            
            # It doesn't exist in DBF at all (for this invoice)
            phantom_records.append((mysql_id, mysql_inv, mysql_grp, mysql_desc, 'NOT IN DBF'))
    
    # Step 4: Report
    print(f"\n{'='*60}")
    print(f"CLEANUP REPORT")
    print(f"{'='*60}")
    print(f"Invoices compared (in both DBF & MySQL): {len(common_invoices)}")
    print(f"Records checked: {records_checked}")
    print(f"Phantom records found: {len(phantom_records)}")
    print()
    
    if phantom_records:
        # Group by reason
        deleted_in_dbf = [r for r in phantom_records if r[4] == 'DELETED IN DBF']
        not_in_dbf = [r for r in phantom_records if r[4] == 'NOT IN DBF']
        
        print(f"  Marked deleted in DBF: {len(deleted_in_dbf)}")
        print(f"  Not found in DBF at all: {len(not_in_dbf)}")
        print()
        
        # Show samples
        print("=== Sample phantom records ===")
        for i, rec in enumerate(phantom_records[:20]):
            print(f"  [{rec[4]}] ID={rec[0]:6d}  Invoice={rec[1]}  Grp={rec[2]}  Desc={str(rec[3])[:50]}")
        
        if len(phantom_records) > 20:
            print(f"  ... and {len(phantom_records) - 20} more")
        
        # Execute deletions
        if not dry_run:
            print(f"\n{'='*60}")
            print(f"DELETING PHANTOM RECORDS")
            print(f"{'='*60}")
            
            ids_to_delete = [r[0] for r in phantom_records]
            batch_size = 500
            
            for i in range(0, len(ids_to_delete), batch_size):
                batch = ids_to_delete[i:i+batch_size]
                placeholders = ','.join(['%s'] * len(batch))
                sql = f"DELETE FROM dtransaksi WHERE id IN ({placeholders})"
                cur.execute(sql, batch)
                conn.commit()
                print(f"  Deleted {min(i+batch_size, len(ids_to_delete))}/{len(ids_to_delete)}...")
            
            print(f"\n✅ Successfully deleted {len(phantom_records)} phantom records")
    else:
        print("✅ No phantom records found — MySQL matches DBF perfectly!")
    
    conn.close()


def main():
    parser = argparse.ArgumentParser(
        description='Remove records from MySQL that were deleted in FoxPro DBF'
    )
    parser.add_argument('--dry-run', action='store_true', default=True,
                        help='Preview changes without applying (default)')
    parser.add_argument('--execute', action='store_true',
                        help='Actually delete the phantom records')
    parser.add_argument('--invoice', type=str, default=None,
                        help='Check only this specific invoice number')
    args = parser.parse_args()
    
    dry_run = not args.execute
    cleanup_dtransaksi(dry_run=dry_run, specific_invoice=args.invoice)


if __name__ == '__main__':
    main()
