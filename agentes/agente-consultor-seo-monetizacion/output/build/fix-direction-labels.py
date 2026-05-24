#!/usr/bin/env python3
"""Fix direction labels in the second 'sentido hacia' paragraph"""

import requests
import json
import re

WP_API = "https://visitandopuntaarenas.cl/wp-json/wp/v2"
AUTH = ("cvieille", "EpAe QMwP G12X H6nJ KZXo WgdM")

# Page IDs for line pages
# We need to get the correct IDs
pages_to_fix = {
    "linea-1": {
        "slug": "linea-1",
        "old_second_dest": "Hospital Cl\u00ednico",  # should be Archipi\u00e9lago de Chilo\u00e9
        "new_second_dest": "Poblaci\u00f3n Archipi\u00e9lago de Chilo\u00e9",
        "new_second_dest_short": "Archipi\u00e9lago de Chilo\u00e9"
    },
    "linea-2": {
        "slug": "linea-2",
        "old_second_dest": "Zona Franca",
        "new_second_dest": "Zona Franca",  # actually correct already
        "new_second_dest_short": "Zona Franca"
    },
    "linea-5": {
        "slug": "linea-5",
        "old_second_dest": "Hospital Cl\u00ednico",
        "new_second_dest": "Hospital Cl\u00ednico",  # correct already
        "new_second_dest_short": "Hospital Cl\u00ednico"
    },
    "linea-6": {
        "slug": "linea-6",
        "old_second_dest": "Hospital Cl\u00ednico",
        "new_second_dest": "Hospital Cl\u00ednico",  # correct already
        "new_second_dest_short": "Hospital Cl\u00ednico"
    },
    "linea-8": {
        "slug": "linea-8",
        "old_second_dest": "Poblaci\u00f3n Archipi\u00e9lago de Chilo\u00e9",
        "new_second_dest": "Hospital Cl\u00ednico",
        "new_second_dest_short": "Hospital Cl\u00ednico"
    }
}

for line_key, info in pages_to_fix.items():
    slug = info["slug"]
    old_dest = info["old_second_dest"]
    new_dest = info["new_second_dest"]
    new_short = info["new_second_dest_short"]
    
    # Find the post by slug
    resp = requests.get(f"{WP_API}/posts?slug={slug}&_fields=id,slug,link", auth=AUTH, timeout=15)
    data = resp.json()
    
    if not data:
        print(f"[{slug}] NOT FOUND")
        continue
    
    post = data[0]
    post_id = post["id"]
    print(f"[{slug}] Found ID={post_id}, URL={post['link']}")
    
    # Get raw content
    content_resp = requests.get(f"{WP_API}/posts/{post_id}?context=edit&_fields=content", auth=AUTH, timeout=15)
    content_data = content_resp.json()
    raw_content = content_data["content"]["raw"]
    
    # Find the second paragraph with "sentido hacia"  
    # Pattern: first paragraph has dest, second has origin_b
    # We need to check if it needs fixing
    
    old_text = f"En sentido hacia <strong>{old_dest}</strong>, el <a href=\"https://visitandopuntaarenas.cl/transporte/recorridos-de-micros/\">recorrido de la micro</a> tiene"
    
    if old_text in raw_content:
        # Check if this is actually incorrect (same as first direction)
        first_dest_text = None
        # Extract first "sentido hacia"
        first_match = re.search(r'En sentido hacia <strong>([^<]+)</strong>', raw_content)
        if first_match:
            first_dest = first_match.group(1)
            print(f"  First direction: towards '{first_dest}'")
            print(f"  Second direction: towards '{old_dest}'")
            
            if first_dest == old_dest:
                # Same as first - needs fix
                new_text = f"En sentido hacia <strong>{new_dest}</strong>, el <a href=\"https://visitandopuntaarenas.cl/transporte/recorridos-de-micros/\">recorrido de la micro</a> tiene"
                new_html = raw_content.replace(old_text, new_text)
                
                if new_html != raw_content:
                    update_resp = requests.put(
                        f"{WP_API}/posts/{post_id}",
                        json={"content": new_html},
                        auth=AUTH,
                        headers={"Content-Type": "application/json"},
                        timeout=30
                    )
                    
                    if update_resp.status_code in (200, 201):
                        print(f"  [OK] Fixed: 'hacia {old_dest}' -> 'hacia {new_dest}'")
                    else:
                        print(f"  [ERR] {update_resp.status_code}: {update_resp.text[:200]}")
                else:
                    print(f"  [SKIP] Content unchanged")
            else:
                print(f"  [OK] Already different from first direction, no fix needed")
    else:
        # Check what the actual second dest is
        print(f"  Pattern not found. Searching for alternatives...")
        # Look for the second "sentido hacia"
        matches = list(re.finditer(r'En sentido hacia <strong>([^<]+)</strong>', raw_content))
        for i, m in enumerate(matches):
            print(f"  Match {i}: '{m.group(1)}'")
        if len(matches) >= 2:
            second_dest = matches[1].group(1)
            if second_dest != new_dest:
                # Need to fix
                old_markup = matches[1].group(0)
                new_markup = f"En sentido hacia <strong>{new_dest}</strong>"
                new_html = raw_content.replace(old_markup, new_markup)
                
                update_resp = requests.put(
                    f"{WP_API}/posts/{post_id}",
                    json={"content": new_html},
                    auth=AUTH,
                    headers={"Content-Type": "application/json"},
                    timeout=30
                )
                
                if update_resp.status_code in (200, 201):
                    print(f"  [OK] Fixed second direction: '{second_dest}' -> '{new_dest}'")
                else:
                    print(f"  [ERR] {update_resp.status_code}")

print("\nDone fixing direction labels.")
