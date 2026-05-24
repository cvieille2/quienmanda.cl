#!/usr/bin/env python3
"""Fix direction labels properly - first occurrence keeps original dest_a, second gets dest_b"""

import requests
import re

WP_API = "https://visitandopuntaarenas.cl/wp-json/wp/v2"
AUTH = ("cvieille", "EpAe QMwP G12X H6nJ KZXo WgdM")

# Each page: post_id, first_should_be, second_should_be
fixes = [
    (20855, "Villa Nelda Panicucci", "Zona Franca"),           # linea-2
    (20856, "Villa Mirador al Estrecho", "Hospital Cl\u00ednico"),  # linea-5
    (20857, "Poblaci\u00f3n Archipi\u00e9lago de Chilo\u00e9", "Hospital Cl\u00ednico"),  # linea-6
    (20858, "Poblaci\u00f3n Archipi\u00e9lago de Chilo\u00e9", "Hospital Cl\u00ednico"),  # linea-8
]

for post_id, first_dest, second_dest in fixes:
    # Get raw content
    resp = requests.get(f"{WP_API}/posts/{post_id}?context=edit&_fields=id,slug,content", auth=AUTH, timeout=15)
    data = resp.json()
    raw = data["content"]["raw"]
    slug = data.get("slug", "?")
    
    # Find all "sentido hacia <strong>X</strong>" occurrences
    matches = list(re.finditer(r'En sentido hacia <strong>([^<]+)</strong>', raw))
    
    if len(matches) >= 2:
        current_first = matches[0].group(1)
        current_second = matches[1].group(1)
        
        print(f"[{slug}] Current: 1st='{current_first}', 2nd='{current_second}'")
        print(f"  Target:  1st='{first_dest}', 2nd='{second_dest}'")
        
        # Fix both occurrences by replacing the full matched text
        # Process in reverse order so indices don't shift
        new_content = raw
        
        # Fix second occurrence first (to avoid position shift)
        if current_second != second_dest:
            old_second = matches[1].group(0)
            new_second = f"En sentido hacia <strong>{second_dest}</strong>"
            # Use string replace with count=1 for each unique old text
            # But since both might be the same, we need position-based replace
            # Let's use a different approach: replace from the right (second occurrence is the second one)
            
            # Find position of second match
            pos2 = matches[1].start()
            pos1 = matches[0].start()
            
            # Build new content by splicing
            new_content = raw[:pos2] + new_second + raw[pos2 + len(matches[1].group(0)):]
            print(f"  Fixed 2nd: '{current_second}' -> '{second_dest}'")
        
        # Fix first occurrence (re-get matches since content changed)
        matches_new = list(re.finditer(r'En sentido hacia <strong>([^<]+)</strong>', new_content))
        if matches_new and matches_new[0].group(1) != first_dest:
            pos1_new = matches_new[0].start()
            old_first = matches_new[0].group(0)
            new_first = f"En sentido hacia <strong>{first_dest}</strong>"
            new_content = new_content[:pos1_new] + new_first + new_content[pos1_new + len(old_first):]
            print(f"  Fixed 1st: '{matches_new[0].group(1)}' -> '{first_dest}'")
        
        if new_content != raw:
            update_resp = requests.put(
                f"{WP_API}/posts/{post_id}",
                json={"content": new_content},
                auth=AUTH,
                headers={"Content-Type": "application/json"},
                timeout=30
            )
            if update_resp.status_code in (200, 201):
                print(f"  [OK] Updated")
            else:
                print(f"  [ERR] {update_resp.status_code}: {update_resp.text[:200]}")
        else:
            print(f"  [SKIP] No changes needed")
    else:
        print(f"[{slug}] WARNING: Found only {len(matches)} matches")

print("\nDone!")
