import requests, re, json, html

AUTH = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'

POSTS = {
    14138: 'piquero-blanco',
    14139: 'piquero-cafe',
    14140: 'piquero-de-patas-coloradas',
    14141: 'piquero-peruano',
}

def extract_faq(raw_html):
    """Extract FAQ pairs from h3 + p after 'Preguntas frecuentes' heading."""
    # Find the FAQ section
    faq_start = raw_html.find('<h2>Preguntas frecuentes</h2>')
    if faq_start == -1:
        # Try with different spacing
        faq_start = raw_html.find('Preguntas frecuentes')
    if faq_start == -1:
        print('  WARN: No FAQ section found')
        return []
    
    section = raw_html[faq_start:]
    
    # Find all h3 (questions) followed by p (answers)
    pairs = []
    pattern = re.compile(r'<h3>(.*?)</h3>\s*<p>(.*?)</p>', re.DOTALL)
    matches = pattern.findall(section)
    
    for q, a in matches:
        # Strip HTML tags from question and answer
        q_clean = re.sub(r'<[^>]+>', '', q).strip()
        a_clean = re.sub(r'<[^>]+>', '', a).strip()
        # Decode HTML entities
        q_clean = html.unescape(q_clean)
        a_clean = html.unescape(a_clean)
        pairs.append((q_clean, a_clean))
    
    return pairs

def build_faq_ld_json(faq_pairs, post_url):
    """Build FAQPage JSON-LD."""
    main_entity = []
    for q, a in faq_pairs:
        main_entity.append({
            "@type": "Question",
            "name": q,
            "acceptedAnswer": {
                "@type": "Answer",
                "text": a
            }
        })
    
    return {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": main_entity
    }

def content_has_schema(raw_html):
    """Check if FAQPage schema already exists in content."""
    return 'FAQPage' in raw_html

for pid, slug in POSTS.items():
    print(f'\n=== {slug} (ID {pid}) ===')
    
    r = requests.get(f'{BASE}/posts/{pid}?context=edit', auth=AUTH)
    if r.status_code != 200:
        print(f'  ERROR: {r.status_code} - {r.text[:200]}')
        continue
    
    p = r.json()
    raw = p['content']['raw']
    post_url = p.get('link', '')
    
    if content_has_schema(raw):
        print('  FAQPage schema already present, skipping')
        continue
    
    faq_pairs = extract_faq(raw)
    if not faq_pairs:
        print('  No FAQ pairs found, skipping')
        continue
    
    print(f'  Found {len(faq_pairs)} FAQ pairs:')
    for i, (q, a) in enumerate(faq_pairs):
        print(f'    {i+1}. Q: {q[:60]}')
        print(f'       A: {a[:60]}')
    
    ld_json = build_faq_ld_json(faq_pairs, post_url)
    script_tag = f'\n<script type="application/ld+json">\n{json.dumps(ld_json, ensure_ascii=False, indent=2)}\n</script>'
    
    new_raw = raw.strip() + '\n\n' + script_tag
    
    # PUT updated content
    update = {'content': new_raw}
    r2 = requests.put(f'{BASE}/posts/{pid}?context=edit', json=update, auth=AUTH)
    
    if r2.status_code == 200:
        print(f'  OK - FAQ schema added')
    else:
        print(f'  ERROR: {r2.status_code} - {r2.text[:300]}')
        # Try with different approach - maybe script is stripped
        # Check if script tag exists in updated content
        updated = r2.json() if r2.status_code == 200 else None
        if updated:
            new_raw_check = updated['content']['raw']
            if 'FAQPage' in new_raw_check:
                print('  (script was preserved)')
            else:
                print('  (script tag was stripped)')

print('\nDone.')
