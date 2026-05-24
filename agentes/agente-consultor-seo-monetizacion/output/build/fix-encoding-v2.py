#!/usr/bin/env python3
"""Fix mojibake using targeted character-by-character encoding fix"""

import requests
import json

WP_API = "https://visitandopuntaarenas.cl/wp-json/wp/v2"
AUTH = ("cvieille", "EpAe QMwP G12X H6nJ KZXo WgdM")

POST_IDS = [20852, 20855, 20856, 20857, 20858]

def fix_mixed_encoding(text):
    """
    Fix text that has a mix of correct and double-encoded UTF-8 characters.
    
    Strategy: try to encode the entire string as latin-1 and decode as utf-8.
    If it fails (because some chars are > U+00FF), we process character by character.
    
    For each character:
    - If it's U+00C0-U+00FF (À-ÿ), it might be a garbled UTF-8 byte. Try to see if
      combining it with the next character forms a valid UTF-8 sequence when 
      encoded as Latin-1.
    - Otherwise keep it as-is.
    """
    result = []
    i = 0
    while i < len(text):
        ch = text[i]
        code = ord(ch)
        
        # Check if this looks like part of a mojibake sequence
        # Double-encoded chars produce sequences in U+0080-U+00FF range
        # The mojibake for accented vowels starts with U+00C3 (Ã) followed by 
        # U+0080-U+00BF range chars
        
        if code == 0xC3 and i + 1 < len(text):
            next_code = ord(text[i + 1])
            # Ã + [80-BF] is the mojibake pattern for accented chars
            if 0x80 <= next_code <= 0xBF:
                # Try to fix: encode as latin-1, decode as utf-8
                pair = ch + text[i + 1]
                try:
                    bytes_pair = pair.encode('latin-1')
                    fixed = bytes_pair.decode('utf-8')
                    result.append(fixed)
                    i += 2
                    continue
                except:
                    pass
        
        if code == 0xC2 and i + 1 < len(text):
            next_code = ord(text[i + 1])
            # Â + [80-BF] - this is the mojibake for ¿, ¡, etc.
            if 0x80 <= next_code <= 0xBF:
                pair = ch + text[i + 1]
                try:
                    bytes_pair = pair.encode('latin-1')
                    fixed = bytes_pair.decode('utf-8')
                    result.append(fixed)
                    i += 2
                    continue
                except:
                    pass
        
        # Handle longer mojibake sequences (3-byte UTF-8 characters)
        # e.g., em dash — (U+2014) -> â€” (U+00E2 U+0080 U+0094)
        if code == 0xE2 and i + 2 < len(text):
            next_code = ord(text[i + 1])
            next_next_code = ord(text[i + 2])
            if 0x80 <= next_code <= 0xBF and 0x80 <= next_next_code <= 0xBF:
                triple = ch + text[i + 1] + text[i + 2]
                try:
                    bytes_triple = triple.encode('latin-1')
                    fixed = bytes_triple.decode('utf-8')
                    result.append(fixed)
                    i += 3
                    continue
                except:
                    pass
        
        # Not a mojibake sequence, keep as-is
        result.append(ch)
        i += 1
    
    return ''.join(result)


for post_id in POST_IDS:
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
    
    # Fix content
    fixed_content = fix_mixed_encoding(original_content)
    content_changed = fixed_content != original_content
    
    # Fix title
    fixed_title = fix_mixed_encoding(original_title)
    title_changed = fixed_title != original_title
    
    if title_changed:
        print(f"  Title: '{original_title[:60]}'")
        print(f"    ->  '{fixed_title[:60]}'")
    
    if content_changed:
        # Verify the fix: check for garbled patterns
        garbled_found = 0
        for garbled in ['Ã¡', 'Ã©', 'Ã\xad', 'Ã³', 'Ãº', 'Ã±', 'Ã‘', 'Â¿', 'â€”', 'â€™', 'â€œ', 'â€']:
            if garbled in fixed_content:
                garbled_found += 1
        
        print(f"  Content: {len(original_content)} -> {len(fixed_content)} chars")
        print(f"  Garbled patterns remaining: {garbled_found}")
        
        # Check for correct patterns
        for good in ['Línea', 'España', 'Día', 'Miércoles', 'Duración', 'Población']:
            if good in fixed_content:
                pass
            elif good in original_content:
                print(f"  WARNING: '{good}' was in original but lost in fix!")
        
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
        print(f"  No content changes needed")
        # Check if it's actually already correct
        sample = original_content[:500]
        for check in ['Ã', 'Â']:
            if check in sample:
                print(f"  But '{check}' found in content!")
                break

print("\nDone!")
