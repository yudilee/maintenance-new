#!/usr/bin/env python3
"""
Populate missing vehicle models from DBF vehicle.dbf file.

Reads CMODEL from vehicle.dbf and updates the mobil table in MySQL
for vehicles that have an empty model field but exist in the DBF.

Usage:
    python3 scripts/populate_model_from_dbf.py --dry-run   # Preview only
    python3 scripts/populate_model_from_dbf.py --execute   # Apply updates
"""

import os
import sys
import argparse
import pymysql
from dbfread import DBF

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


def load_dbf_models():
    """Load chassis -> model mapping from vehicle.dbf."""
    dbf_path = os.path.join(DBF_DIR, 'vehicle.dbf')
    if not os.path.exists(dbf_path):
        print(f"ERROR: {dbf_path} not found!")
        sys.exit(1)

    dbf = DBF(dbf_path, encoding='cp1252')
    models = {}
    for rec in dbf:
        chassis = str(rec.get('CCHASNUM', '')).strip()
        model = str(rec.get('CMODEL', '')).strip()
        if chassis and model:
            models[chassis] = model

    return models


def main():
    parser = argparse.ArgumentParser(description='Populate vehicle models from DBF')
    parser.add_argument('--dry-run', action='store_true', default=True,
                        help='Preview changes without applying (default)')
    parser.add_argument('--execute', action='store_true',
                        help='Actually update the database')
    args = parser.parse_args()

    dry_run = not args.execute

    print("=" * 60)
    print("POPULATE VEHICLE MODELS FROM DBF")
    print("=" * 60)
    print(f"Mode: {'DRY RUN' if dry_run else 'EXECUTE'}")
    print()

    # Step 1: Load DBF models
    print("Loading vehicle.dbf...")
    dbf_models = load_dbf_models()
    print(f"Loaded {len(dbf_models)} chassis->model mappings from DBF")
    print()

    # Step 2: Find MySQL vehicles with empty model
    conn = get_mysql_conn()
    cur = conn.cursor()

    cur.execute("""
        SELECT id, TRIM(nomor_chassis) as chassis, nomor_polisi
        FROM mobil
        WHERE (model IS NULL OR model = '')
          AND nomor_chassis IS NOT NULL
          AND TRIM(nomor_chassis) != ''
    """)
    empty_vehicles = cur.fetchall()
    print(f"Vehicles with empty model in MySQL: {len(empty_vehicles)}")
    print()

    # Step 3: Cross-reference
    updates = []
    no_match = []
    for vid, chassis, plate in empty_vehicles:
        if chassis in dbf_models:
            updates.append((vid, chassis, plate, dbf_models[chassis]))
        else:
            no_match.append((vid, chassis, plate))

    print(f"Can update from DBF: {len(updates)}")
    print(f"No match in DBF:     {len(no_match)}")
    print()

    if updates:
        print("=== Sample updates ===")
        for vid, chassis, plate, model in updates[:10]:
            print(f"  ID={vid}  {plate:20s}  ->  {model}")
        if len(updates) > 10:
            print(f"  ... and {len(updates) - 10} more")
        print()

    if not dry_run and updates:
        print("Updating database...")
        batch_size = 500
        updated_count = 0
        for i in range(0, len(updates), batch_size):
            batch = updates[i:i + batch_size]
            for vid, chassis, plate, model in batch:
                cur.execute(
                    "UPDATE mobil SET model = %s WHERE id = %s",
                    (model, vid)
                )
            conn.commit()
            updated_count += len(batch)
            print(f"  Updated {updated_count}/{len(updates)}...")

        print(f"\n✅ Successfully updated {len(updates)} vehicles with model from DBF")
    elif dry_run:
        print(f"Run with --execute to apply these {len(updates)} updates")

    if no_match:
        print(f"\n⚠️  {len(no_match)} vehicles still have no model source:")
        for vid, chassis, plate in no_match[:10]:
            print(f"  ID={vid}  plate={plate}  chassis={chassis}")
        if len(no_match) > 10:
            print(f"  ... and {len(no_match) - 10} more")

    conn.close()
    print("\nDone.")


if __name__ == '__main__':
    main()
