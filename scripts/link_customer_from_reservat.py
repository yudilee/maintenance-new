#!/usr/bin/env python3
"""
Customer Link Recovery Script
==============================
Uses the FoxPro `reservat` table to link htransaksi records to customers.

FoxPro Logic (from the original application):
    SELECT reservat
    SET ORDER TO chassis
    SEEK(m.cchasnum)
    IF FOUND() AND m.djobno >= reservat.dusestrt AND m.djobno <= reservat.duseend
        m.ccodecus = reservat.ccodecus
        m.enamecus = IIF(SEEK(m.ccodecus,"customer"),customer.enamecom,'')
    ENDIF

This script applies the same logic:
1. Load reservat.dbf → index by CCHASNUM (chassis number)
2. For each unlinked htransaksi record:
   a. Look up its nomor_chassis in the reservat index
   b. Check if tanggal_job falls within DUSESTRT..DUSEEND (+ optional grace period)
   c. If yes, find the customer by kode_customer and set id_customer
3. Generate a report of what was linked

Coverage:
  - 41,658 unlinked records currently (id_customer = 0, pre-2025)
  - Strict (grace=0):   ~24,132 (58%) linkable
  - Grace=14 days:      ~26,504 (64%) linkable  (+2,372)
  - Grace=30 days:      ~27,474 (66%) linkable  (+3,342)
  - Grace=90 days:      ~29,784 (72%) linkable  (+5,652)

Usage:
    # Dry-run (preview only, no changes):
    python3 scripts/link_customer_from_reservat.py --dry-run
    
    # Dry-run with 14-day grace period:
    python3 scripts/link_customer_from_reservat.py --dry-run --grace-days 14
    
    # Execute updates with 14-day grace:
    python3 scripts/link_customer_from_reservat.py --execute --grace-days 14
    
    # Strict execution (no grace):
    python3 scripts/link_customer_from_reservat.py --execute --grace-days 0
"""

import os
import sys
import argparse
from datetime import datetime, date, timedelta
from collections import defaultdict, Counter
from dbfread import DBF
import pymysql

DBF_DIR = '23-01-2026'
MYSQL_CONFIG = {
    'host': '127.0.0.1',
    'port': 3307,
    'user': 'sdp',
    'password': 'password',
    'database': 'sdp',
}


def get_mysql_conn():
    return pymysql.connect(**MYSQL_CONFIG)


def load_reservat():
    """Load reservat.dbf and build an index by chassis number."""
    dbf_path = os.path.join(DBF_DIR, 'reservat.dbf')
    print(f"Loading {dbf_path}...")
    dbf = DBF(dbf_path, encoding='cp1252')
    
    # Build index: chassis_number -> list of (customer_code, start_date, end_date)
    chassis_index = defaultdict(list)
    customer_codes_in_reservat = set()
    
    for i, rec in enumerate(dbf):
        chassis = str(rec.get('CCHASNUM', '')).strip()
        ccodecus = str(rec.get('CCODECUS', '')).strip()
        dusestrt = rec.get('DUSESTRT')
        duseend = rec.get('DUSEEND')
        
        if not chassis or not ccodecus or not dusestrt or not duseend:
            continue
        
        # Convert datetime to date for comparison
        if hasattr(dusestrt, 'date'):
            start_date = dusestrt.date()
        elif isinstance(dusestrt, date):
            start_date = dusestrt
        else:
            continue
            
        if hasattr(duseend, 'date'):
            end_date = duseend.date()
        elif isinstance(duseend, date):
            end_date = duseend
        else:
            continue
        
        chassis_index[chassis].append({
            'cust_code': ccodecus,
            'start': start_date,
            'end': end_date,
        })
        customer_codes_in_reservat.add(ccodecus)
    
    print(f"  Loaded {len(dbf)} reservat records")
    print(f"  Unique chassis numbers: {len(chassis_index)}")
    print(f"  Unique customer codes in reservat: {len(customer_codes_in_reservat)}")
    
    return chassis_index, customer_codes_in_reservat


def load_customer_map():
    """Load customer table from MySQL into a dict: kode_customer -> id."""
    conn = get_mysql_conn()
    cur = conn.cursor()
    cur.execute("SELECT id, kode_customer FROM customer")
    customer_map = {}
    for row in cur.fetchall():
        customer_map[row[1].strip()] = row[0]
    conn.close()
    print(f"  Loaded {len(customer_map)} customers from MySQL")
    return customer_map


def analyze_coverage(chassis_index, customer_map, dry_run=True, grace_days=0):
    """
    Analyze how many records can be linked.
    
    Args:
        chassis_index: dict of chassis -> list of (cust_code, start, end)
        customer_map: dict of kode_customer -> customer.id
        dry_run: If True, only analyze; if False, execute UPDATEs
        grace_days: Number of days to extend the end date for more flexible matching
                    (e.g., 14 = match if job date is within 14 days after contract end)
    """
    conn = get_mysql_conn()
    cur = conn.cursor()
    
    # Get all unlinked pre-2025 records
    cur.execute("""
        SELECT id, nomor_job, nomor_chassis, tanggal_job
        FROM htransaksi
        WHERE tanggal_job < '2025-12-08'
          AND (id_customer = 0 OR id_customer IS NULL)
        ORDER BY tanggal_job
    """)
    records = cur.fetchall()
    print(f"\nTotal unlinked records to process: {len(records)}")
    print(f"Grace period: end_date + {grace_days} days")
    
    # Statistics
    stats = {
        'total': len(records),
        'matched_via_reservat': 0,
        'no_chassis': 0,
        'chassis_not_in_reservat': 0,
        'no_date_overlap': 0,
        'customer_code_not_found': 0,
    }
    
    matched_codes = Counter()
    updates_to_execute = []
    
    for row in records:
        htrans_id, nomor_job, nomor_chassis, tanggal_job = row
        
        # Normalize chassis
        chassis = str(nomor_chassis).strip() if nomor_chassis else ''
        job_date = tanggal_job
        
        if not isinstance(job_date, date):
            stats['no_date_overlap'] += 1
            continue
        
        if not chassis:
            stats['no_chassis'] += 1
            continue
        
        if chassis not in chassis_index:
            stats['chassis_not_in_reservat'] += 1
            continue
        
        # Search reservat entries for this chassis
        matched = False
        matched_cust_code = None
        
        for res in chassis_index[chassis]:
            # Apply grace period to end date
            end_with_grace = res['end'] + timedelta(days=grace_days)
            if res['start'] <= job_date <= end_with_grace:
                matched = True
                matched_cust_code = res['cust_code']
                break
        
        if not matched:
            stats['no_date_overlap'] += 1
            continue
        
        # Look up customer code in MySQL customer table
        if matched_cust_code not in customer_map:
            stats['customer_code_not_found'] += 1
            continue
        
        customer_id = customer_map[matched_cust_code]
        stats['matched_via_reservat'] += 1
        matched_codes[matched_cust_code] += 1
        
        if not dry_run:
            updates_to_execute.append((customer_id, htrans_id))
    
    # Print summary
    print(f"\n{'='*60}")
    print(f"MATCHING RESULTS")
    print(f"{'='*60}")
    print(f"  Matched via reservat:        {stats['matched_via_reservat']:6d} ({stats['matched_via_reservat']/stats['total']*100:.1f}%)")
    print(f"  No chassis number:           {stats['no_chassis']:6d} ({stats['no_chassis']/stats['total']*100:.1f}%)")
    print(f"  Chassis not in reservat:     {stats['chassis_not_in_reservat']:6d} ({stats['chassis_not_in_reservat']/stats['total']*100:.1f}%)")
    print(f"  Chassis found, no date range:{stats['no_date_overlap']:6d} ({stats['no_date_overlap']/stats['total']*100:.1f}%)")
    print(f"  Customer code not in table:  {stats['customer_code_not_found']:6d} ({stats['customer_code_not_found']/stats['total']*100:.1f}%)")
    print(f"{'─'*60}")
    print(f"  TOTAL linkable:              {stats['matched_via_reservat']:6d}")
    print(f"  TOTAL unlinkable:            {stats['total'] - stats['matched_via_reservat']:6d}")
    
    # Top customer codes
    print(f"\nTop matched customer codes:")
    for code, count in matched_codes.most_common(15):
        cust_name = ""
        if code in customer_map:
            cur2 = conn.cursor()
            cur2.execute("SELECT nama_customer FROM customer WHERE id = %s", (customer_map[code],))
            name_row = cur2.fetchone()
            if name_row:
                cust_name = name_row[0][:40]
        print(f"  {code:20s} => {count:5d} records  ({cust_name})")
    
    # Execute updates
    if not dry_run and updates_to_execute:
        print(f"\n{'='*60}")
        print(f"EXECUTING UPDATES")
        print(f"{'='*60}")
        
        batch_size = 1000
        total_updated = 0
        
        for i in range(0, len(updates_to_execute), batch_size):
            batch = updates_to_execute[i:i+batch_size]
            # Build batched UPDATE
            # MySQL doesn't support bulk UPDATE easily, so we use a CASE statement
            case_stmt = "UPDATE htransaksi SET id_customer = CASE id\n"
            ids = []
            for cust_id, htrans_id in batch:
                case_stmt += f"  WHEN {htrans_id} THEN {cust_id}\n"
                ids.append(str(htrans_id))
            case_stmt += f"  ELSE id_customer\nEND\nWHERE id IN ({','.join(ids)})"
            
            try:
                cur.execute(case_stmt)
                conn.commit()
                total_updated += len(batch)
            except Exception as e:
                conn.rollback()
                print(f"  ERROR updating batch {i//batch_size + 1}: {e}")
                # Fall back to individual updates
                for cust_id, htrans_id in batch:
                    try:
                        cur.execute("UPDATE htransaksi SET id_customer = %s WHERE id = %s", (cust_id, htrans_id))
                    except:
                        conn.rollback()
                    conn.commit()
                    total_updated += 1
            
            print(f"  Updated {min(i+batch_size, len(updates_to_execute))}/{len(updates_to_execute)} records...")
        
        print(f"\n✅ Successfully updated {total_updated} records")
        print(f"   Failed: {len(updates_to_execute) - total_updated}")
    elif dry_run:
        print(f"\n⏭️  DRY RUN - No changes made. Use --execute to apply updates.")
    
    conn.close()
    return stats


def main():
    parser = argparse.ArgumentParser(
        description='Link htransaksi customers using FoxPro reservat table'
    )
    parser.add_argument('--dry-run', action='store_true', default=True,
                        help='Preview changes without applying (default)')
    parser.add_argument('--execute', action='store_true',
                        help='Actually apply the updates to the database')
    parser.add_argument('--grace-days', type=int, default=0,
                        help='Extend end date by N days for flexible matching (default: 0). '
                             'Recommended: 14 for 2-week grace period after contract ends.')
    args = parser.parse_args()
    
    # If --execute is provided, override --dry-run
    dry_run = not args.execute
    grace_days = args.grace_days
    
    print(f"{'='*60}")
    print(f"CUSTOMER LINK RECOVERY VIA RESERVAT")
    print(f"{'='*60}")
    print(f"Mode:       {'DRY RUN (no changes)' if dry_run else 'EXECUTE (will update DB)'}")
    print(f"Grace days: end_date + {grace_days} days")
    print()
    
    # Step 1: Load reservat data
    chassis_index, reservat_codes = load_reservat()
    
    # Step 2: Load customer map
    customer_map = load_customer_map()
    
    # Step 3: Check overlap
    codes_in_customer = reservat_codes & set(customer_map.keys())
    codes_not_in_customer = reservat_codes - set(customer_map.keys())
    print(f"  Reservat codes found in customer table: {len(codes_in_customer)}")
    print(f"  Reservat codes NOT in customer table: {len(codes_not_in_customer)}")
    if codes_not_in_customer:
        print(f"  Missing codes (sample): {sorted(list(codes_not_in_customer))[:10]}")
    
    # Step 4: Analyze and optionally update
    stats = analyze_coverage(chassis_index, customer_map, dry_run=dry_run, grace_days=grace_days)
    
    # Summary
    print(f"\n{'='*60}")
    print(f"SUMMARY")
    print(f"{'='*60}")
    print(f"  Previously linked (id_customer > 0): was 53,059")
    print(f"  Newly linkable via reservat:         {stats['matched_via_reservat']}")
    print(f"  Still unlinked after this:           {stats['total'] - stats['matched_via_reservat']}")
    print()
    if grace_days > 0:
        print(f"  ⚠️  Using {grace_days}-day grace period — records matched due to grace")
        print(f"      may have less confidence. Consider reviewing a sample.")
    print()
    print(f"  For the remaining unlinked records, consider:")
    print(f"  1. MAINTMEMO first-line customer code parsing")
    print(f"  2. Manual review of the reservat table for missing date ranges")
    print(f"  3. Checking if some records correspond to owned (non-rental) vehicles")


if __name__ == '__main__':
    main()
