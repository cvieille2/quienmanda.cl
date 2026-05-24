#!/usr/bin/env python3
"""Fix mojibake (double UTF-8 encoding) in the 5 line pages"""

import requests
import json

WP_API = "https://visitandopuntaarenas.cl/wp-json/wp/v2"
AUTH = ("cvieille", "EpAe QMwP G12X H6nJ KZXo WgdM")

POST_IDS = [20852, 20855, 20856, 20857, 20858]

def fix_mojibake(text):
    """Fix double-encoded UTF-8 characters.
    E.g., 'L\xc3\xadnea' (which is 'LÃ\xadnea' = L + Ã + soft hyphen + nea)
    -> 'L\xc3\xadnea' (L + í + nea = 'Línea')
    
    The fix: encode as Latin-1 (to get raw bytes), then decode as UTF-8.
    ASCII chars are 1:1 the same in both encodings.
    """
    try:
        # Step 1: encode the garbled string as latin-1 to get the raw bytes
        raw_bytes = text.encode('latin-1')
        # Step 2: decode those bytes as UTF-8 (they were originally UTF-8 bytes 
        # that got misinterpreted as Latin-1 chars)
        return raw_bytes.decode('utf-8')
    except (UnicodeEncodeError, UnicodeDecodeError) as e:
        print(f"  Encoding error: {e}")
        return text  # Return as-is if can't fix

for post_id in POST_IDS:
    # Get the raw content (with context=edit)
    resp = requests.get(
        f"{WP_API}/posts/{post_id}?context=edit&_fields=id,slug,title,content",
        auth=AUTH,
        timeout=15
    )
    data = resp.json()
    slug = data.get("slug", "?")
    original_content = data["content"]["raw"]
    original_title = data["title"]["raw"] if isinstance(data["title"], dict) else data["title"]
    
    print(f"\n[{slug}] (ID={post_id})")
    
    # Fix title
    fixed_title = fix_mojibake(original_title)
    title_changed = fixed_title != original_title
    if title_changed:
        print(f"  Title: '{original_title[:50]}...'")
        print(f"    ->  '{fixed_title[:50]}...'")
    
    # Fix content
    fixed_content = fix_mojibake(original_content)
    content_changed = fixed_content != original_content
    
    if content_changed:
        print(f"  Content: {len(original_content)} -> {len(fixed_content)} chars")
        
        # Check if the fix actually worked by verifying specific patterns
        for good_pattern in ['Línea', 'España', 'Día', 'Miércoles', 'Duración', 'Población']:
            if good_pattern in fixed_content:
                pass  # Good, fix worked
            elif good_pattern in original_content:
                print(f"  WARNING: '{good_pattern}' was already correct, might have broken")
        
        # Send update
        update_data = {"content": fixed_content}
        if title_changed:
            update_data["title"] = fixed_title
        
        update_resp = requests.put(
            f"{WP_API}/posts/{post_id}",
            json=update_data,
            auth=AUTH,
            headers={"Content-Type": "application/json"},
            timeout=30
        )
        
        if update_resp.status_code in (200, 201):
            print(f"  [OK] Updated")
        else:
            print(f"  [ERR] {update_resp.status_code}: {update_resp.text[:200]}")
    else:
        print(f"  Content unchanged (same length)")
        # Try partial fix: check if the raw bytes suggest double encoding
        sample = original_content[:500].encode('utf-8')
        if b'\xc3\x83\xc2' in sample:
            print(f"  Double-encoding detected but fix didn't change content!")
            # Maybe the fix isn't working. Let's check one specific char
            idx = sample.find(b'\xc3\x83\xc2')
            print(f"  At position {idx}: bytes={sample[idx:idx+6].hex()}")

print("\nDone!")
