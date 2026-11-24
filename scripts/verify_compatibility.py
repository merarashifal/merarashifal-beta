#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Verification Script for zodiac_compatibility
"""
import sys
import json
import re
import pymysql

if sys.stdout.encoding != 'utf-8':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

conn = pymysql.connect(
    host='localhost',
    user='root',
    password='',
    database='merarashifal',
    charset='utf8mb4',
    cursorclass=pymysql.cursors.DictCursor
)

try:
    with conn.cursor() as cur:
        cur.execute("SELECT * FROM zodiac_compatibility")
        rows = cur.fetchall()

    print(f"Total rows fetched: {len(rows)}")

    null_checks = {
        'slug': 0,
        'element_pair': 0,
        'meta_title': 0,
        'meta_description': 0,
        'verdict_summary': 0,
        'key_strength': 0,
        'key_challenge': 0,
        'faq_json': 0,
        'general_text': 0,
        'love_text': 0,
        'friendship_text': 0,
        'trust_text': 0,
        'dependability_text': 0,
        'longevity_text': 0,
        'marriage_text': 0,
        'kids_text': 0,
        'finance_text': 0
    }

    invalid_json = 0
    escaped_slashes = 0
    hindi_with_english = 0

    english_word_pattern = re.compile(r'\b[a-zA-Z]{3,}\b')
    # Known English words that shouldn't appear in Hindi fields (excluding sign names if any)
    forbidden_en_keywords = ['love', 'language', 'quality', 'communication', 'partner', 'overview']

    for r in rows:
        for col in null_checks:
            val = r.get(col)
            if val is None or str(val).strip() == '':
                null_checks[col] += 1

        # Check JSON
        f_json = r.get('faq_json')
        try:
            parsed = json.loads(f_json)
            if not isinstance(parsed, list) or len(parsed) != 3:
                invalid_json += 1
            if '\\/' in f_json:
                escaped_slashes += 1
        except Exception:
            invalid_json += 1

        # Check Hindi purity
        if r['language'] == 'hi':
            for col in ['general_text', 'love_text', 'verdict_summary', 'key_strength', 'key_challenge']:
                text = r.get(col, '').lower()
                for kw in forbidden_en_keywords:
                    if kw in text:
                        hindi_with_english += 1
                        print(f"Row {r['id']} ({col}) contains English word '{kw}': {r[col][:80]}")

    print("\n--- Field Integrity Check ---")
    for col, count in null_checks.items():
        status = "PASSED (0 null/empty)" if count == 0 else f"FAILED ({count} null/empty)"
        print(f"  {col}: {status}")

    print(f"\nInvalid JSON count: {invalid_json} (Expected: 0)")
    print(f"Escaped slashes in JSON: {escaped_slashes} (Expected: 0)")
    print(f"Hindi fields containing forbidden English boilerplate: {hindi_with_english} (Expected: 0)")

    # Test an index lookup simulation
    with conn.cursor() as cur:
        cur.execute("SELECT id, slug, element_pair, meta_title FROM zodiac_compatibility WHERE slug='aries-man-leo-woman' AND language='en'")
        sample_en = cur.fetchone()
        cur.execute("SELECT id, slug, element_pair, meta_title FROM zodiac_compatibility WHERE slug='aries-man-leo-woman' AND language='hi'")
        sample_hi = cur.fetchone()

    print("\n--- Slug & Language Index Lookup Verification ---")
    print(f"EN Lookup: {sample_en}")
    print(f"HI Lookup: {sample_hi}")

    if all(c == 0 for c in null_checks.values()) and invalid_json == 0 and hindi_with_english == 0:
        print("\nALL VERIFICATION CHECKS PASSED PERFECTLY!")
    else:
        print("\nSOME CHECKS FAILED! Please inspect above.")

finally:
    conn.close()
