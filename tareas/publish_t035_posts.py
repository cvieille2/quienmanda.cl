#!/usr/bin/env python3
"""Publish T-035 content posts to avesnativaschilenas.cl via WordPress REST API"""

import json, base64, urllib.request, urllib.error, os, re, sys, time

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
CATEGORY_JAULAS = 163

def api(method, endpoint, data=None):
    req = urllib.request.Request(f'{BASE}{endpoint}', method=method)
    req.add_header('Authorization', AUTH)
    req.add_header('User-Agent', 'opencode/1.0')
    if data:
        req.add_header('Content-Type', 'application/json')
        req.data = json.dumps(data).encode('utf-8')
    for attempt in range(3):
        try:
            with urllib.request.urlopen(req, timeout=60) as resp:
                return json.loads(resp.read())
        except urllib.error.HTTPError as e:
            body = e.read().decode()
            print(f'  HTTP {e.code}: {body[:300]}')
            if attempt < 2:
                time.sleep(3)
            else:
                raise
        except Exception as e:
            print(f'  Error: {e}')
            if attempt < 2:
                time.sleep(3)
            else:
                raise

def md_to_html(text):
    """Convert markdown text to HTML"""
    # Convert headers first (must be at start of line)
    text = re.sub(r'^### (.+)$', r'<h3>\1</h3>', text, flags=re.MULTILINE)
    text = re.sub(r'^## (.+)$', r'<h2>\1</h2>', text, flags=re.MULTILINE)
    text = re.sub(r'^# (.+)$', r'<h1>\1</h1>', text, flags=re.MULTILINE)
    
    # Convert bold and italic
    text = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', text)
    text = re.sub(r'\*(.+?)\*', r'<em>\1</em>', text)
    
    # Convert inline links [text](url)
    text = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', r'<a href="\2">\1</a>', text)
    
    # Convert horizontal rules
    text = re.sub(r'^---+\s*$', '<hr/>', text, flags=re.MULTILINE)
    
    # Process lines into paragraphs and lists
    lines = text.split('\n')
    result = []
    in_ul = False
    in_ol = False
    in_p = False
    
    for line in lines:
        stripped = line.strip()
        
        # Skip empty lines after closing tags
        if not stripped:
            if in_ul:
                result.append('</ul>')
                in_ul = False
            if in_ol:
                result.append('</ol>')
                in_ol = False
            if in_p:
                result.append('</p>')
                in_p = False
            continue
        
        # If line is a block-level HTML tag, just pass through
        if stripped.startswith('<h') or stripped.startswith('<hr') or stripped.startswith('<p'):
            if in_ul:
                result.append('</ul>')
                in_ul = False
            if in_ol:
                result.append('</ol>')
                in_ol = False
            if in_p:
                result.append('</p>')
                in_p = False
            result.append(stripped)
            continue
        
        # Unordered list
        if stripped.startswith('- '):
            if in_ol:
                result.append('</ol>')
                in_ol = False
            if in_p:
                result.append('</p>')
                in_p = False
            if not in_ul:
                result.append('<ul>')
                in_ul = True
            result.append(f'<li>{stripped[2:]}</li>')
            continue
        
        # Ordered list
        if re.match(r'^\d+[.)]\s', stripped):
            if in_ul:
                result.append('</ul>')
                in_ul = False
            if in_p:
                result.append('</p>')
                in_p = False
            if not in_ol:
                result.append('<ol>')
                in_ol = True
            item = re.sub(r'^\d+[.)]\s', '', stripped)
            result.append(f'<li>{item}</li>')
            continue
        
        # Paragraph text
        if in_ul:
            result.append('</ul>')
            in_ul = False
        if in_ol:
            result.append('</ol>')
            in_ol = False
        if not in_p:
            result.append('<p>')
            in_p = True
            result.append(stripped)
        else:
            result.append('<br/>' + stripped)
    
    if in_ul:
        result.append('</ul>')
    if in_ol:
        result.append('</ol>')
    if in_p:
        result.append('</p>')
    
    return '\n'.join(result)

def parse_md(filepath):
    """Parse markdown file to extract metadata and content"""
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()
    
    # Extract title (first # line)
    title_match = re.search(r'^# (.+)$', text, re.MULTILINE)
    title = title_match.group(1).strip() if title_match else ''
    
    # Extract meta description
    meta_match = re.search(r'\*\*Meta description:\*\* (.+)', text)
    meta_desc = meta_match.group(1).strip() if meta_match else ''
    
    # Extract slug
    slug_match = re.search(r'\*\*Slug:\*\* /?(.+?)$', text, re.MULTILINE)
    slug = slug_match.group(1).strip().strip('/') if slug_match else ''
    
    # Build content: remove metadata header (everything before first ---)
    parts = text.split('---', 2)
    content_md = parts[-1].strip() if len(parts) > 2 else text.strip()
    
    # Remove metadata lines that may remain
    for pat in [
        r'^\*\*Keywords objetivo:\*\*.*?\n',
        r'^\*\*Slug:\*\*.*?\n',
        r'^\*\*Cluster:\*\*.*?\n',
        r'^\*\*Productos Amazon:\*\*.*?\n(?:- .*?\n)*',
        r'^\*\*Meta description:\*\*.*?\n',
        r'^\*\*Tipo:\*\*.*?\n',
    ]:
        content_md = re.sub(pat, '', content_md, flags=re.MULTILINE)
    
    content_md = content_md.strip()
    content_html = md_to_html(content_md)
    
    # Add disclaimer
    disclaimer = '<p><em>Como afiliado de Amazon, gano por compras calificadas. Los precios y la disponibilidad pueden variar.</em></p>'
    content_html += '\n' + disclaimer
    
    return {
        'title': title,
        'slug': slug,
        'content': content_html,
        'meta_desc': meta_desc,
    }

def create_post(post_data, category_id=None):
    wp_data = {
        'title': post_data['title'],
        'content': post_data['content'],
        'slug': post_data['slug'],
        'status': 'publish',
    }
    if post_data['meta_desc']:
        wp_data['meta'] = {'_yoast_wpseo_metadesc': post_data['meta_desc'][:160]}
    if category_id:
        wp_data['categories'] = [category_id]
    
    result = api('POST', '/posts', wp_data)
    link = result.get('link', '?')
    post_id = result.get('id', '?')
    print(f'  -> Created: {link} (ID {post_id})')
    return result

def get_post_id_by_slug(slug):
    """Find existing post ID by slug"""
    clean_slug = slug.strip('/').split('/')[-1]
    try:
        result = api('GET', f'/posts?slug={clean_slug}&_fields=id,link')
        if result:
            return result[0]['id']
    except:
        pass
    return None

# ============================================================
if __name__ == '__main__':
    base_dir = 'C:/Users/Usuario/Desktop/multiplicar-dinero/agentes/agente-copywriter-conversion/input'
    
    create_files = [
        ('T-035-ST-14-jaulas-para-guacamayos.md', CATEGORY_JAULAS),
        ('T-035-ST-14-jaulas-para-cacatuas.md', CATEGORY_JAULAS),
        ('T-035-ST-14-jaulas-para-agapornis.md', CATEGORY_JAULAS),
        ('T-035-ST-14-jaulas-para-cotorras.md', CATEGORY_JAULAS),
        ('T-035-ST-14-jaulas-para-yacos.md', CATEGORY_JAULAS),
        ('T-035-ST-14-aviarios.md', CATEGORY_JAULAS),
        ('T-035-ST-14-voladeras.md', CATEGORY_JAULAS),
        ('T-035-ST-14-pajareras.md', CATEGORY_JAULAS),
        ('T-035-ST-14-jaulas-para-canarios.md', CATEGORY_JAULAS),
        ('T-035-ST-15-jaulas-para-gallinas-ponedoras.md', CATEGORY_JAULAS),
        ('T-035-ST-15-jaulas-para-codornices.md', CATEGORY_JAULAS),
        ('T-035-ST-15-jaulas-marcas.md', CATEGORY_JAULAS),
        ('T-035-ST-16-jaulas-para-periquitos.md', CATEGORY_JAULAS),
        ('T-035-ST-16-jaulas-para-ninfas.md', CATEGORY_JAULAS),
        ('T-035-ST-16-transportines-para-aves.md', CATEGORY_JAULAS),
    ]
    
    update_files = [
        'T-035-ST-15-comederos.md',
        'T-035-ST-15-nidos.md',
        'T-035-ST-15-accesorios.md',
        'T-035-ST-16-jaulas.md',
        'T-035-ST-16-migratorias-paloma.md',
    ]
    
    sys.stdout.reconfigure(encoding='utf-8')
    
    print('=' * 60)
    print('PUBLISHING T-035 POSTS TO avesnativaschilenas.cl')
    print('=' * 60)
    
    # Phase 1: Create new posts
    print('\n--- PHASE 1: CREATING NEW POSTS ---')
    created = 0
    errors = 0
    for fname, cat_id in create_files:
        fpath = os.path.join(base_dir, fname)
        if not os.path.exists(fpath):
            print(f'\n[SKIP] {fname} - not found')
            continue
        print(f'\n>>> {fname}')
        try:
            post_data = parse_md(fpath)
            print(f'  Title: {post_data["title"][:80]}')
            print(f'  Slug: {post_data["slug"]}')
            print(f'  Content: {len(post_data["content"])} chars')
            create_post(post_data, cat_id)
            created += 1
            time.sleep(1.5)
        except Exception as e:
            print(f'  FAILED: {e}')
            errors += 1
    
    print(f'\n--- PHASE 1 DONE: {created} created, {errors} errors ---')
    
    # Phase 2: Update existing posts
    print('\n--- PHASE 2: UPDATING EXISTING POSTS ---')
    updated = 0
    for fname in update_files:
        fpath = os.path.join(base_dir, fname)
        if not os.path.exists(fpath):
            print(f'\n[SKIP] {fname} - not found')
            continue
        print(f'\n>>> {fname}')
        try:
            post_data = parse_md(fpath)
            slug = post_data['slug']
            post_id = get_post_id_by_slug(slug)
            if post_id:
                print(f'  Found existing post ID {post_id} for slug "{slug}"')
                # Get existing content and append
                existing = api('GET', f'/posts/{post_id}?_fields=content')
                existing_html = existing.get('content', {}).get('rendered', '')
                new_content = post_data['content']
                # Append to existing
                combined = existing_html + '\n' + new_content
                upd = api('POST', f'/posts/{post_id}', {
                    'content': combined,
                })
                print(f'  -> Updated: {upd.get("link", "?")}')
                updated += 1
                time.sleep(1)
            else:
                print(f'  [WARN] No post found with slug "{slug}" - creating instead')
                create_post(post_data, CATEGORY_JAULAS)
                created += 1
                time.sleep(1.5)
        except Exception as e:
            print(f'  FAILED: {e}')
            errors += 1
    
    print(f'\n{"=" * 60}')
    print(f'FINAL: {created} created, {updated} updated, {errors} errors')
    print(f'{"=" * 60}')
