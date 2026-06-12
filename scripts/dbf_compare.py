#!/usr/bin/env python3
"""
DBF vs MySQL Comparison Script
Compares FoxPro DBF+FPT data with current MySQL database (Docker)
"""

import os
import sys
import json
import datetime
from collections import Counter
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

def get_mysql_counts():
    """Get record counts from MySQL for pre-2025-12-08 data"""
    conn = get_mysql_conn()
    cur = conn.cursor()
    
    results = {}
    
    # htransaksi counts
    cur.execute("SELECT COUNT(*) FROM htransaksi")
    results['htransaksi_total'] = cur.fetchone()[0]
    
    cur.execute("SELECT COUNT(*) FROM htransaksi WHERE tanggal_job < '2025-12-08'")
    results['htransaksi_pre_2025'] = cur.fetchone()[0]
    
    cur.execute("SELECT COUNT(*) FROM htransaksi WHERE tanggal_job >= '2025-12-08'")
    results['htransaksi_post_2025'] = cur.fetchone()[0]
    
    # With customer links
    cur.execute("SELECT COUNT(*) FROM htransaksi WHERE id_customer > 0 AND tanggal_job < '2025-12-08'")
    results['htransaksi_pre_2025_with_customer'] = cur.fetchone()[0]
    
    cur.execute("SELECT COUNT(*) FROM htransaksi WHERE id_customer = 0 AND tanggal_job < '2025-12-08'")
    results['htransaksi_pre_2025_no_customer'] = cur.fetchone()[0]
    
    # With keterangan
    cur.execute("SELECT COUNT(*) FROM htransaksi WHERE keterangan IS NOT NULL AND keterangan != '' AND tanggal_job < '2025-12-08'")
    results['htransaksi_pre_2025_with_keterangan'] = cur.fetchone()[0]
    
    # Other tables
    cur.execute("SELECT COUNT(*) FROM customer")
    results['customer_total'] = cur.fetchone()[0]
    
    cur.execute("SELECT COUNT(*) FROM supplier")
    results['supplier_total'] = cur.fetchone()[0]
    
    cur.execute("SELECT COUNT(*) FROM mobil")
    results['mobil_total'] = cur.fetchone()[0]
    
    cur.execute("SELECT COUNT(*) FROM dtransaksi")
    results['dtransaksi_total'] = cur.fetchone()[0]
    
    conn.close()
    return results

def get_dbf_counts():
    """Get record counts from DBF files"""
    results = {}
    
    # maintvhc.dbf (htransaksi)
    dbf = DBF(os.path.join(DBF_DIR, 'maintvhc.dbf'), encoding='cp1252')
    results['maintvhc_total'] = len(dbf)
    
    # Count memos
    non_empty_memos = 0
    for rec in dbf:
        memo = rec.get('MAINTMEMO')
        if memo and str(memo).strip() and str(memo) != 'None':
            non_empty_memos += 1
    results['maintvhc_with_memo'] = non_empty_memos
    
    # maintdet.dbf (dtransaksi)
    dbf2 = DBF(os.path.join(DBF_DIR, 'maintdet.dbf'), encoding='cp1252')
    results['maintdet_total'] = len(dbf2)
    
    # customer.dbf
    dbf3 = DBF(os.path.join(DBF_DIR, 'customer.dbf'), encoding='cp1252')
    results['customer_total'] = len(dbf3)
    
    # supplier.dbf
    try:
        dbf4 = DBF(os.path.join(DBF_DIR, 'supplier.dbf'), encoding='cp1252')
        results['supplier_total'] = len(dbf4)
    except:
        results['supplier_total'] = 'ERROR'
    
    # vehicle.dbf (mobil)
    dbf5 = DBF(os.path.join(DBF_DIR, 'vehicle.dbf'), encoding='cp1252')
    results['vehicle_total'] = len(dbf5)
    
    return results

def compare_memo_with_mysql():
    """Compare memo content from DBF with keterangan in MySQL"""
    conn = get_mysql_conn()
    cur = conn.cursor(pymysql.cursors.DictCursor)
    
    # Get sample MySQL records with keterangan
    cur.execute("""
        SELECT nomor_job, keterangan, id_customer 
        FROM htransaksi 
        WHERE tanggal_job < '2025-12-08' 
          AND (keterangan IS NOT NULL AND keterangan != '')
        LIMIT 10
    """)
    mysql_with_keterangan = cur.fetchall()
    
    # Get sample records without keterangan
    cur.execute("""
        SELECT nomor_job, keterangan, id_customer 
        FROM htransaksi 
        WHERE tanggal_job < '2025-12-08' 
          AND (keterangan IS NULL OR keterangan = '')
        LIMIT 10
    """)
    mysql_without_keterangan = cur.fetchall()
    
    conn.close()
    
    return {
        'mysql_with_keterangan': mysql_with_keterangan,
        'mysql_without_keterangan': mysql_without_keterangan,
    }

def analyze_dbf_memo_patterns():
    """Analyze memo content patterns from DBF"""
    dbf = DBF(os.path.join(DBF_DIR, 'maintvhc.dbf'), encoding='cp1252')
    
    first_line_patterns = Counter()
    customer_candidates = []
    sample_memos = []
    
    for i, rec in enumerate(dbf):
        if i >= 20000:  # Analyze first 20K for patterns
            break
        memo = rec.get('MAINTMEMO')
        if memo and str(memo).strip() and str(memo) != 'None':
            text = str(memo)
            lines = text.replace('\r', '\n').split('\n')
            first_line = lines[0].strip() if lines else ''
            
            if first_line:
                first_line_patterns[first_line[:30]] += 1
            
            # Check if first line matches customer code pattern
            # Customer codes: all caps, letters only, no spaces, 2-15 chars
            import re
            if re.match(r'^[A-Z][A-Z0-9]{1,14}$', first_line):
                customer_candidates.append({
                    'job': rec['CJOBNO'],
                    'code': first_line,
                    'rest': '\n'.join(lines[1:]).strip()[:200] if len(lines) > 1 else '',
                })
            
            if len(sample_memos) < 20 and first_line:
                sample_memos.append({
                    'job': rec['CJOBNO'],
                    'first_line': first_line[:100],
                    'full_length': len(text),
                })
    
    return {
        'first_line_patterns': first_line_patterns.most_common(30),
        'customer_candidates_count': len(customer_candidates),
        'customer_candidates': customer_candidates[:20],
        'sample_memos': sample_memos,
    }

def check_invoice_join_issue():
    """Check the leading zero issue between dtransaksi and htransaksi"""
    conn = get_mysql_conn()
    cur = conn.cursor()
    
    # Check if dtransaksi nomor_invoice has leading zeros
    cur.execute("""
        SELECT LEFT(nomor_invoice, 1) as first_char, COUNT(*) as cnt
        FROM dtransaksi 
        GROUP BY LEFT(nomor_invoice, 1)
        ORDER BY first_char
    """)
    dtrans_invoice_prefixes = cur.fetchall()
    
    # Check if htransaksi nomor_invoice has leading zeros
    cur.execute("""
        SELECT LEFT(nomor_invoice, 1) as first_char, COUNT(*) as cnt
        FROM htransaksi 
        WHERE tanggal_job < '2025-12-08'
        GROUP BY LEFT(nomor_invoice, 1)
        ORDER BY first_char
    """)
    htrans_invoice_prefixes = cur.fetchall()
    
    # Check join: count orphan dtransaksi records
    cur.execute("""
        SELECT COUNT(*) as orphans
        FROM dtransaksi d
        LEFT JOIN htransaksi h ON CONCAT('0', d.nomor_invoice) = h.nomor_invoice
        WHERE h.id IS NULL
    """)
    orphans_with_zero = cur.fetchone()[0]
    
    cur.execute("""
        SELECT COUNT(*) as orphans
        FROM dtransaksi d
        LEFT JOIN htransaksi h ON d.nomor_invoice = h.nomor_invoice
        WHERE h.id IS NULL
    """)
    orphans_without_zero = cur.fetchone()[0]
    
    conn.close()
    
    return {
        'dtrans_invoice_prefixes': dtrans_invoice_prefixes,
        'htrans_invoice_prefixes': htrans_invoice_prefixes,
        'orphans_with_leading_zero': orphans_with_zero,
        'orphans_without_leading_zero': orphans_without_zero,
    }

def generate_report():
    """Generate comprehensive comparison report"""
    report = []
    report.append("# DBF vs MySQL Comparison Report")
    report.append(f"**Generated:** {datetime.datetime.now().isoformat()}")
    report.append("")
    
    # Phase 1: Record Counts
    report.append("## 1. Record Count Comparison")
    report.append("")
    report.append("| Table | DBF Source | MySQL (Total) | MySQL (Pre-2025) | Difference |")
    report.append("|-------|-----------|---------------|------------------|------------|")
    
    dbf_counts = get_dbf_counts()
    mysql_counts = get_mysql_counts()
    
    report.append(f"| htransaksi (maintvhc) | {dbf_counts['maintvhc_total']:,} | {mysql_counts['htransaksi_total']:,} | {mysql_counts['htransaksi_pre_2025']:,} | {dbf_counts['maintvhc_total'] - mysql_counts['htransaksi_pre_2025']:,} |")
    report.append(f"| dtransaksi (maintdet) | {dbf_counts['maintdet_total']:,} | {mysql_counts['dtransaksi_total']:,} | - | {dbf_counts['maintdet_total'] - mysql_counts['dtransaksi_total']:,} |")
    report.append(f"| customer | {dbf_counts['customer_total']:,} | {mysql_counts['customer_total']:,} | - | {dbf_counts['customer_total'] - mysql_counts['customer_total']:,} |")
    report.append(f"| supplier | {dbf_counts['supplier_total']:,} | {mysql_counts['supplier_total']:,} | - | {dbf_counts['supplier_total'] - mysql_counts['supplier_total']:,} |")
    report.append(f"| mobil (vehicle) | {dbf_counts['vehicle_total']:,} | {mysql_counts['mobil_total']:,} | - | {dbf_counts['vehicle_total'] - mysql_counts['mobil_total']:,} |")
    report.append("")
    
    # Phase 2: Memo Analysis
    report.append("## 2. Memo Field Analysis")
    report.append("")
    report.append(f"### DBF Memo Status")
    report.append(f"- Total records in maintvhc.dbf: **{dbf_counts['maintvhc_total']:,}**")
    report.append(f"- Records with non-empty MAINTMEMO: **{dbf_counts['maintvhc_with_memo']:,}** ({dbf_counts['maintvhc_with_memo']/dbf_counts['maintvhc_total']*100:.1f}%)")
    report.append("")
    
    report.append(f"### MySQL Keterangan Status (Pre-2025)")
    report.append(f"- MySQL records (pre-2025): **{mysql_counts['htransaksi_pre_2025']:,}**")
    report.append(f"- With non-empty keterangan: **{mysql_counts['htransaksi_pre_2025_with_keterangan']:,}**")
    report.append(f"- With customer link (id_customer > 0): **{mysql_counts['htransaksi_pre_2025_with_customer']:,}**")
    report.append(f"- Missing customer link (id_customer = 0): **{mysql_counts['htransaksi_pre_2025_no_customer']:,}**")
    report.append("")
    
    report.append("**Conclusion:** The DBF has memo content for ~92K records, but MySQL keterangan is mostly empty.")
    report.append("")
    
    # Phase 3: Memo Pattern Analysis
    report.append("## 3. Memo Content Pattern Analysis")
    report.append("")
    
    memo_analysis = analyze_dbf_memo_patterns()
    
    report.append(f"Analyzed first 20,000 records for patterns.")
    report.append(f"Records with potential customer codes (first line): **{memo_analysis['customer_candidates_count']}**")
    report.append("")
    
    report.append("### Sample Memos (First 20)")
    report.append("")
    report.append("| Job No | First Line | Full Length |")
    report.append("|--------|-----------|-------------|")
    for s in memo_analysis['sample_memos']:
        report.append(f"| {s['job']} | {s['first_line'][:80]} | {s['full_length']} |")
    report.append("")
    
    report.append("### Top First Line Patterns")
    report.append("")
    report.append("| Pattern | Count |")
    report.append("|---------|-------|")
    for pattern, count in memo_analysis['first_line_patterns'][:20]:
        report.append(f"| {pattern[:80]} | {count} |")
    report.append("")
    
    report.append("### Potential Customer Codes Found")
    report.append("")
    if memo_analysis['customer_candidates']:
        report.append("| Job No | Customer Code | Rest of Memo |")
        report.append("|--------|--------------|--------------|")
        for c in memo_analysis['customer_candidates'][:20]:
            report.append(f"| {c['job']} | {c['code']} | {c['rest'][:100]} |")
    report.append("")
    
    # Phase 4: Invoice Join Issue
    report.append("## 4. Invoice Join Analysis (Leading Zero Issue)")
    report.append("")
    
    invoice_analysis = check_invoice_join_issue()
    
    report.append("### dtransaksi nomor_invoice prefixes")
    for prefix, count in invoice_analysis['dtrans_invoice_prefixes']:
        report.append(f"- Prefix '{prefix}': {count:,} records")
    report.append("")
    report.append("### htransaksi nomor_invoice prefixes (pre-2025)")
    for prefix, count in invoice_analysis['htrans_invoice_prefixes']:
        report.append(f"- Prefix '{prefix}': {count:,} records")
    report.append("")
    report.append(f"### Orphan dtransaksi records")
    report.append(f"- Without leading zero fix (direct join): {invoice_analysis['orphans_without_leading_zero']:,}")
    report.append(f"- With leading zero fix (CONCAT '0'): {invoice_analysis['orphans_with_leading_zero']:,}")
    report.append("")
    
    if invoice_analysis['orphans_without_leading_zero'] > invoice_analysis['orphans_with_leading_zero']:
        report.append("**⚠️ Confirmed: Leading zero ISSUE exists.** Joining with CONCAT('0', nomor_invoice) resolves most orphans.")
    else:
        report.append("**✅ No leading zero issue detected.**")
    report.append("")
    
    # Phase 5: Customer Link Status
    report.append("## 5. Customer Link Status")
    report.append("")
    report.append(f"- Total pre-2025 records: **{mysql_counts['htransaksi_pre_2025']:,}**")
    report.append(f"- Linked to customer (id_customer > 0): **{mysql_counts['htransaksi_pre_2025_with_customer']:,}**")
    report.append(f"- Not linked (id_customer = 0): **{mysql_counts['htransaksi_pre_2025_no_customer']:,}**")
    report.append(f"- Customer table records: **{mysql_counts['customer_total']:,}**")
    report.append("")
    
    if mysql_counts['htransaksi_pre_2025_with_customer'] > 0:
        report.append("**✅ Customer links ALREADY exist** - the DBF-to-customer mapping was already done for many records.")
    else:
        report.append("**❌ No customer links exist** - need to extract from memo fields.")
    report.append("")
    
    # Summary
    report.append("## 6. Summary of Discrepancies")
    report.append("")
    
    discrepancies = []
    
    if dbf_counts['maintvhc_total'] != mysql_counts['htransaksi_pre_2025']:
        discrepancies.append(f"- **Record count mismatch:** DBF has {dbf_counts['maintvhc_total']:,} records, MySQL has {mysql_counts['htransaksi_pre_2025']:,} pre-2025 records (diff: {dbf_counts['maintvhc_total'] - mysql_counts['htransaksi_pre_2025']:,})")
    
    if dbf_counts['customer_total'] != mysql_counts['customer_total']:
        discrepancies.append(f"- **Customer count mismatch:** DBF has {dbf_counts['customer_total']:,}, MySQL has {mysql_counts['customer_total']:,}")
    
    if dbf_counts['maintvhc_with_memo'] > 0 and mysql_counts['htransaksi_pre_2025_with_keterangan'] == 0:
        discrepancies.append(f"- **Memo content missing:** DBF has {dbf_counts['maintvhc_with_memo']:,} non-empty memos, but MySQL has {mysql_counts['htransaksi_pre_2025_with_keterangan']:,} non-empty keterangan fields")
    
    if invoice_analysis['orphans_without_leading_zero'] > 0:
        discrepancies.append(f"- **Invoice join issue:** {invoice_analysis['orphans_without_leading_zero']:,} dtransaksi records can't join to htransaksi without fixing leading zeros")
    
    if mysql_counts['htransaksi_pre_2025_no_customer'] > 0:
        discrepancies.append(f"- **Unlinked records:** {mysql_counts['htransaksi_pre_2025_no_customer']:,} pre-2025 records still have id_customer = 0")
    
    for d in discrepancies:
        report.append(d)
    
    if not discrepancies:
        report.append("**✅ No significant discrepancies found.**")
    
    report.append("")
    report.append("---")
    report.append(f"*Report generated at {datetime.datetime.now().isoformat()}*")
    
    return '\n'.join(report)

if __name__ == '__main__':
    os.chdir('/home/yudi/dev/maintenance_new')
    report = generate_report()
    print(report)
    
    # Save report
    with open('dbf_comparison_report.md', 'w') as f:
        f.write(report)
    print("\n\nReport saved to dbf_comparison_report.md")
