import requests, json

auth = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
base = 'https://avesnativaschilenas.cl/wp-json/wp/v2/'

def update_post(pid, new_title, new_content, ptype='posts'):
    data = {'title': new_title, 'content': new_content}
    resp = requests.post('{}{}/{}'.format(base, ptype, pid), auth=auth, json=data)
    return resp.status_code, resp.json() if resp.status_code == 200 else resp.text[:200]

# Template for affiliate box
AFF_BOX = '<div class="afiliado-recomendacion" style="background:{bg};padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid {border};"><p><strong>{header}</strong></p>{body}<p><em>Como afiliado de Amazon, gano por compras calificadas.</em></p></div>'

# ============================
# POST 9420: Colibri vs Picaflor (2,315 imp)
# ============================
print('=== Post 9420: Colibri vs Picaflor ===')
resp = requests.get('{}posts/9420'.format(base), auth=auth)
if resp.status_code == 200:
    post = resp.json()
    old_title = post['title']['rendered']
    old_content = post['content']['rendered']
    
    new_title = 'Colibri vs Picaflor: 5 Diferencias Clave que Debes Saber (Guia 2026)'
    
    body = '<p>Si quieres observar picaflores de cerca, te recomiendo instalar un comedero especial para colibries. Estas aves se sienten atraidas por el nectar y volveran dia tras dia si tienen alimento disponible.</p><p>👉 <a href="https://amzn.to/3TdvvIt" target="_blank" rel="nofollow sponsored">Comedero para colibries recomendado en Amazon.es</a> — facil de instalar y mantener.</p><p><strong>Tip:</strong> Prepara nectar casero con 4 partes de agua y 1 de azucar blanca. Nunca uses miel ni colorantes artificiales.</p>'
    affiliate_html = AFF_BOX.format(bg='#f0faf0', border='#2ecc71', header='Atrae picaflores a tu jardin', body=body)
    new_content = old_content + affiliate_html
    
    status, result = update_post(9420, new_title, new_content)
    if status == 200:
        print('UPDATED 9420: "{}" -> "{}"'.format(old_title[:40], new_title))
    else:
        print('ERROR 9420: {}'.format(result))
else:
    print('FAILED 9420: {}'.format(resp.status_code))

# ============================
# POST 1857: Aguila segun Biblia (3,563 imp)
# ============================
print()
print('=== Post 1857: Aguila segun Biblia ===')
resp = requests.get('{}posts/1857'.format(base), auth=auth)
if resp.status_code == 200:
    post = resp.json()
    old_title = post['title']['rendered']
    old_content = post['content']['rendered']
    
    new_title = 'El Aguila en la Biblia: Significado Espiritual, Versiculos y Ensenanzas'
    
    body = '<p>Si te fascina el significado espiritual de las aves en la Biblia, estos recursos te ayudaran a explorar mas:</p><p>👉 <a href="https://amzn.to/3XcK9pM" target="_blank" rel="nofollow sponsored">Biblias de estudio en Amazon.es</a> — con notas sobre simbolismo y profecia.</p><p>👉 <a href="https://amzn.to/3RzKkPp" target="_blank" rel="nofollow sponsored">Libros sobre el simbolismo animal en la Biblia</a></p>'
    affiliate_html = AFF_BOX.format(bg='#fef9e7', border='#f39c12', header='Profundiza en el simbolismo biblico', body=body)
    new_content = old_content + affiliate_html
    
    status, result = update_post(1857, new_title, new_content)
    if status == 200:
        print('UPDATED 1857: "{}" -> "{}"'.format(old_title[:40], new_title))
    else:
        print('ERROR 1857: {}'.format(result))
else:
    print('FAILED 1857: {}'.format(resp.status_code))

# ============================
# POST 9804: Palomas vs Aves Nativas (POSITION #1! 2,664 imp, 0 clics)
# ============================
print()
print('=== Post 9804: Palomas vs Aves Nativas (POSITION #1!) ===')
resp = requests.get('{}posts/9804'.format(base), auth=auth)
if resp.status_code == 200:
    post = resp.json()
    old_title = post['title']['rendered']
    old_content = post['content']['rendered']
    
    new_title = 'Las Palomas Estan Desplazando a Nuestras Aves Nativas? Impacto y Soluciones'
    
    body = '<p>Si las palomas estan desplazando a las aves nativas en tu zona, existen metodos para controlarlas sin danarlas:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Disuasivos de palomas en Amazon.es</a> — metodos eticos y efectivos.</p><p>👉 <a href="https://amzn.to/3EwWQFL" target="_blank" rel="nofollow sponsored">Comederos selectivos para aves nativas</a> — alimenta solo a las especies que quieres atraer.</p>'
    affiliate_html = AFF_BOX.format(bg='#f0f0f0', border='#e74c3c', header='Palomas en tu jardin? Soluciones responsables', body=body)
    new_content = old_content + affiliate_html
    
    status, result = update_post(9804, new_title, new_content)
    if status == 200:
        print('UPDATED 9804: "{}" -> "{}"'.format(old_title[:40], new_title))
    else:
        print('ERROR 9804: {}'.format(result))
else:
    print('FAILED 9804: {}'.format(resp.status_code))

# ============================
# POST 9352: Zanate en tu casa (640 imp)
# ============================
print()
print('=== Post 9352: Zanate en tu casa ===')
resp = requests.get('{}posts/9352'.format(base), auth=auth)
if resp.status_code == 200:
    post = resp.json()
    old_title = post['title']['rendered']
    old_content = post['content']['rendered']
    
    new_title = 'Que Significa que un Zanate Llegue a tu Casa? Senales y Significado Espiritual'
    
    body = '<p>Si te interesa el significado espiritual de las aves, estos libros te encantaran:</p><p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Libros sobre simbolismo animal en Amazon.es</a></p>'
    affiliate_html = AFF_BOX.format(bg='#f5f0ff', border='#9b59b6', header='El simbolismo de las aves en las culturas del mundo', body=body)
    new_content = old_content + affiliate_html
    
    status, result = update_post(9352, new_title, new_content)
    if status == 200:
        print('UPDATED 9352: "{}" -> "{}"'.format(old_title[:40], new_title))
    else:
        print('ERROR 9352: {}'.format(result))
else:
    print('FAILED 9352: {}'.format(resp.status_code))

# ============================
# POST 9159: Zanate en la cabeza (529 imp)
# ============================
print()
print('=== Post 9159: Zanate en la cabeza ===')
resp = requests.get('{}posts/9159'.format(base), auth=auth)
if resp.status_code == 200:
    post = resp.json()
    old_title = post['title']['rendered']
    old_content = post['content']['rendered']
    
    new_title = 'Que Significa que un Zanate se Pare en tu Cabeza? Mito, Realidad y Simbolismo'
    
    body = '<p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Libros de mitologia y simbolismo de aves en Amazon.es</a></p>'
    affiliate_html = AFF_BOX.format(bg='#f5f0ff', border='#9b59b6', header='Aves y simbolismo: una guia para entender sus mensajes', body=body)
    new_content = old_content + affiliate_html
    
    status, result = update_post(9159, new_title, new_content)
    if status == 200:
        print('UPDATED 9159: "{}" -> "{}"'.format(old_title[:40], new_title))
    else:
        print('ERROR 9159: {}'.format(result))
else:
    print('FAILED 9159: {}'.format(resp.status_code))

# ============================
# POST 9776: Mitos zanates (400 imp)
# ============================
print()
print('=== Post 9776: Mitos zanates ===')
resp = requests.get('{}posts/9776'.format(base), auth=auth)
if resp.status_code == 200:
    post = resp.json()
    old_title = post['title']['rendered']
    old_content = post['content']['rendered']
    
    new_title = 'Mitos y Creencias sobre los Zanates: De Verdad Traen Mala Suerte?'
    
    body = '<p>👉 <a href="https://amzn.to/3EsYJTm" target="_blank" rel="nofollow sponsored">Explora el significado de las aves en la cultura popular en Amazon.es</a></p>'
    affiliate_html = AFF_BOX.format(bg='#f5f0ff', border='#9b59b6', header='La sabiduria popular y el simbolismo de las aves', body=body)
    new_content = old_content + affiliate_html
    
    status, result = update_post(9776, new_title, new_content)
    if status == 200:
        print('UPDATED 9776: "{}" -> "{}"'.format(old_title[:40], new_title))
    else:
        print('ERROR 9776: {}'.format(result))
else:
    print('FAILED 9776: {}'.format(resp.status_code))

print()
print('ACTUALIZACION COMPLETADA')
