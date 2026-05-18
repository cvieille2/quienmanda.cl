import json, subprocess, re, csv, os, sys, time, tempfile
from urllib.parse import urlparse

AUTH = 'cvieille:QCZg BNQs mcnf HMXE OvBm MXpz'
BASE = 'https://infomoteles.cl/wp-json/wp/v2'

CITY_MAP = {
    'antofagasta':'Antofagasta','arica':'Arica','calama':'Calama',
    'copiapo':'Copiap\u00f3','coquimbo':'Coquimbo','talca':'Talca',
    'curico':'Curic\u00f3','concepcion':'Concepci\u00f3n',
    'valparaiso':'Valpara\u00edso','vina-del-mar':'Vi\u00f1a del Mar',
    'santiago':'Santiago','maipu':'Maip\u00fa','valdivia':'Valdivia',
    'osorno':'Osorno','temuco':'Temuco','iquique':'Iquique',
    'punta-arenas':'Punta Arenas','puerto-montt':'Puerto Montt',
    'rancagua':'Rancagua','quinta-normal':'Quinta Normal',
    'recoleta':'Recoleta','la-cisterna':'La Cisterna',
    'la-florida':'La Florida','san-bernardo':'San Bernardo',
    'san-miguel':'San Miguel','providencia':'Providencia',
    'la-reina':'La Reina','melipilla':'Melipilla',
    'lo-barnechea':'Lo Barnechea','san-joaquin':'San Joaqu\u00edn',
    'lampa':'Lampa','colina':'Colina','buin':'Buin',
    'villa-alemana':'Villa Alemana','coronel':'Coronel',
    'los-angeles':'Los \u00c1ngeles','nunoa':'\u00d1u\u00f1oa',
    'penalolen':'Pe\u00f1alol\u00e9n','pudahuel':'Pudahuel',
    'san-vicente':'San Vicente','talcahuano':'Talcahuano',
    'tome':'Tom\u00e9','maule':'Maule','el-monte':'El Monte',
    'conchali':'Conchal\u00ed','huechuraba':'Huechuraba',
    'san-ramon':'San Ram\u00f3n','la-serena':'La Serena',
    'san-antonio':'San Antonio','victoria':'Victoria',
    'san-felipe':'San Felipe','los-andes':'Los Andes',
    'quillota':'Quillota','limache':'Limache',
    'pirque':'Pirque','isla-de-maipo':'Isla de Maipo',
    'talagante':'Talagante','penaflor':'Pe\u00f1aflor',
    'colbun':'Colb\u00fan','linares':'Linares','parral':'Parral',
    'constitucion':'Constituci\u00f3n','cauquenes':'Cauquenes',
    'chanco':'Chanco','san-fernando':'San Fernando',
    'chimbarongo':'Chimbarongo','san-javier':'San Javier',
    'santa-cruz':'Santa Cruz','santo-domingo':'Santo Domingo',
    'quilpue':'Quilpu\u00e9',
}

def extract_phone(c):
    phones = re.findall(r'\+56[\s\d\-]{7,15}', c)
    if not phones:
        phones = re.findall(r'tel[eé]fono[:=:\s]*\+?(\d[\d\s\-]{7,15})', c, re.I)
    return phones[0].strip() if phones else ''

def api_cmd(method, endpoint, data=None):
    url = f'{BASE}/{endpoint}'
    if data:
        tmp = tempfile.NamedTemporaryFile(mode='w', suffix='.json', delete=False, encoding='utf-8')
        json.dump(data, tmp, ensure_ascii=False)
        tmp.close()
        cmd = f'curl -s -X {method} "{url}" -u "{AUTH}" -H "Content-Type: application/json" -d @{tmp.name}'
        r = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=30)
        os.unlink(tmp.name)
    else:
        cmd = f'curl -s -X {method} "{url}" -u "{AUTH}"'
        r = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=30)
    try:
        return json.loads(r.stdout)
    except:
        return {'error': r.stdout[:200]}

def clean_name(title, city_name):
    name = title
    for sep in [f', {city_name}', f',{city_name}', f' en {city_name}']:
        if sep in name:
            name = name.split(sep)[0].strip()
            name = re.sub(r'[,\\-:]+$', '', name).strip()
            break
    for pref in ['Motel ', 'Hotel Motel ', 'Hotel ', 'Hostal o Pensi\u00f3n ', 'Hostal ', 'Caba\u00f1as ', 'Caba\u00f1a ']:
        if name.startswith(pref):
            name = name[len(pref):]
            break
    return name

def build_top(display_name, city_name, phone, services):
    svc_html = ''
    if services:
        svc_html = '<ul>\n'
        for svc in services:
            svc_html += f'  <li><strong>{svc}</strong></li>\n'
        svc_html += '</ul>\n'
    whatsapp = ''
    if phone:
        pc = re.sub(r'[\s\-]', '', phone)
        whatsapp = f'<p><a href="https://wa.me/{pc}" class="button" target="_blank" rel="noopener">Contactar por WhatsApp</a></p>\n'
    tel = phone if phone else 'Disponible bajo solicitud'
    return ('<p><strong>'+display_name+'</strong> es un motel en <strong>'+city_name+'</strong>, Chile. Informaci\u00f3n actualizada: tel\u00e9fono, direcci\u00f3n, horarios y rese\u00f1as.</p>\n'
            +whatsapp+'\n<h2>Informaci\u00f3n de '+display_name+' en '+city_name+'</h2>\n<p><strong>Tel\u00e9fono:</strong> '+tel+'</p>\n<p><strong>Horario:</strong> Abierto 24 horas, todos los d\u00edas.</p>\n<h2>Servicios</h2>\n'
            +(svc_html if svc_html else '<p>Habitaciones privadas con estacionamiento.</p>\n')
            +'\n<h2>Precios</h2>\n<p>Los precios var\u00edan seg\u00fan la habitaci\u00f3n y horario. Contacta directamente para tarifas actualizadas.</p>\n<hr />\n<!-- OLD CONTENT PRESERVED BELOW -->\n')

with open('tareas/infomoteles-posts.json') as f:
    all_posts = json.load(f)

sc_pages = {}
with open('input/https___infomoteles.cl_-Performance-on-Search-2026-05-17/P\u00e1ginas.csv', encoding='utf-8') as f:
    for row in csv.DictReader(f):
        sc_pages[row['P\u00e1ginas principales'].rstrip('/')] = row

matched_posts = []
for p in all_posts:
    path = urlparse(p['link']).path.rstrip('/')
    sc = sc_pages.get(p['link'].rstrip('/')) or sc_pages.get(path)
    if sc:
        matched_posts.append((int(sc['Clics']), p, sc))

matched_posts.sort(key=lambda x: x[0], reverse=True)
START_FROM = 60

remaining = matched_posts[START_FROM:]
print(f'Resuming from post {START_FROM+1}, {len(remaining)} remaining\n')

ok = 0
fail = 0
for idx, (clics, p_full, sc) in enumerate(remaining):
    progress = idx + 1
    
    # Delay between requests to avoid rate limiting
    if progress > 1:
        time.sleep(2)
    # Longer delay every 20 posts
    if progress % 20 == 0:
        print(f'  [{progress}/{len(remaining)}] {ok} ok, {fail} fail - rate limiting pause...')
        time.sleep(5)
    
    try:
        p = api_cmd('GET', f'posts/{p_full["id"]}?_fields=id,title,content,slug,link,date')
        if not p or 'content' not in p:
            fail += 1
            continue
        
        title = p['title']['rendered']
        content = p['content']['rendered']
        path = urlparse(p['link']).path
        city_name = CITY_MAP.get(path.strip('/').split('/')[0], '')
        
        display_name = clean_name(title, city_name)
        phone = extract_phone(content)
        
        svcs = []
        for pat, name in [('jacuzzi','Jacuzzi'),('wifi','Wi-Fi'),('estacionamiento','Estacionamiento'),
                          ('televisi','TV'),('aire acondicionado','Aire Acondicionado'),
                          ('calefacci','Calefacci\u00f3n'),('hidromasaje','Hidromasaje'),('sauna','Sauna')]:
            if re.search(pat, content.lower()):
                svcs.append(name)
        services = list(set(svcs))
        
        new_top = build_top(display_name, city_name, phone, services)
        new_content = new_top + content
        new_title = display_name + ' en ' + city_name + ' | Tel\u00e9fono y Precios'
        
        result = api_cmd('PUT', f'posts/{p["id"]}', {'title': new_title, 'content': new_content})
        
        if result and 'id' in result:
            ok += 1
            if ok % 20 == 0:
                print(f'  [{progress}/{len(remaining)}] {ok} ok, {fail} fail')
        else:
            fail += 1
            if fail <= 5:
                print(f'  FAIL {p_full["id"]}: {str(result)[:60]}')
    except Exception as e:
        fail += 1
        if fail <= 5:
            print(f'  ERROR {p_full["id"]}: {str(e)[:60]}')

print(f'\nDone: {ok} rewritten, {fail} failed')
