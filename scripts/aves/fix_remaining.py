import json, urllib.request, base64, re

CREDS = ('cvieille', 'u0wM 1VRi v9wL Z71R 7XCx mnEq')
AUTH = 'Basic ' + base64.b64encode(f'{CREDS[0]}:{CREDS[1]}'.encode()).decode()
BASE = 'https://avesnativaschilenas.cl/wp-json/wp/v2'
PILLAR_URL = 'https://avesnativaschilenas.cl/jaulas/'
PILLAR_ID = 6492

def api(method, endpoint, data=None):
    req = urllib.request.Request(f'{BASE}{endpoint}', method=method)
    req.add_header('Authorization', AUTH)
    req.add_header('User-Agent', 'opencode/1.0')
    if data:
        req.add_header('Content-Type', 'application/json')
        req.data = json.dumps(data).encode('utf-8')
    with urllib.request.urlopen(req, timeout=30) as resp:
        return json.loads(resp.read())

# 1. Fix 5 missing posts
missing_slugs = [
    'descubre-la-jaula-perfecta-para-tus-pajaros-comodidad-y-estilo-en-ferplast-ibiza-open',
    'descubre-la-jaula-perfecta-para-tus-pajaros-ferplast-regina-comodidad-y-estilo-en-un-solo-lugar',
    'descubre-la-jaula-ideal-para-tus-pajaros-espacio-comodidad-y-estilo-en-uno-no-te-lo-pierdas',
    'descubre-la-jaula-perfecta-para-tus-pajaros-spacio-comodidad-y-estilo-en-un-solo-lugar',
    'descubre-la-increible-imac-jaula-pajaros-wilma-el-hogar-perfecto-para-tu-ave-favorita',
]
for slug in missing_slugs:
    data = api('GET', f'/posts?slug={slug}&_fields=id,slug,content&context=edit')
    if not data:
        print(f'Not found: {slug}')
        continue
    p = data[0]
    raw = p.get('content', {}).get('raw', '')
    sub_url = f'{PILLAR_URL}modelos/'
    if sub_url in raw:
        print(f'Already has link: {slug}')
        continue
    link_p = '<p>\U0001f449 Si quieres ver m\u00e1s opciones y comparar caracter\u00edsticas, visita nuestra <a href="' + sub_url + '">modelos de jaulas para aves</a>.</p>'
    last_p = raw.rfind('</p>')
    if last_p > 0:
        new_raw = raw[:last_p] + '</p>\n\n' + link_p + raw[last_p + 4:]
    else:
        new_raw = raw.rstrip() + '\n\n' + link_p
    api('PUT', f'/posts/{p["id"]}', {'content': new_raw})
    print(f'Fixed: {slug}')

# 2. Create /jaulas/marcas/ page
marcas_content = '''<!-- wp:paragraph -->
<p>El mercado de jaulas para aves tiene marcas que destacan por su calidad, durabilidad y dise\u00f1o. En esta gu\u00eda reunimos las marcas m\u00e1s relevantes: Yaheetech, Vision, Ferplast, Arquivet, BPS Buena Pet Shop, Flamingo, PH Prevue Hendryx y VIVOHOME. Cada una tiene su especialidad, desde jaulas econ\u00f3micas hasta modelos premium para especies exigentes.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Las mejores marcas de jaulas para aves</h2>
<!-- /wp:heading -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Yaheetech</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Yaheetech es una de las marcas m\u00e1s populares en Amazon. Ofrece jaulas para loros, periquitos y aves peque\u00f1as con una excelente relaci\u00f3n calidad-precio. Sus modelos destacan por ser robustos, con barrotes de metal resistente y bandejas extra\u00edbles. Adem\u00e1s de jaulas, comercializan accesorios como perchas, comederos y fundas protectoras.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Vision</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Vision es reconocida por su sistema de bandeja inferior que permite una limpieza r\u00e1pida y sin esfuerzo. Sus jaulas tienen un dise\u00f1o funcional pensado para la higiene diaria, con bases profundas que evitan que los restos de comida caigan fuera. Ideales para propietarios que buscan pr\u00e1cticidad sin renunciar a la calidad.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Ferplast</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Ferplast es un fabricante italiano con d\u00e9cadas de experiencia. Sus jaulas abarcan desde modelos b\u00e1sicos para canarios hasta jaulones para loros grandes. Destacan por su acabado cuidado, barrotes de acero con recubrimiento y accesorios incluidos. La l\u00ednea Ferplast Ibiza, Regina y Bali son las m\u00e1s vendidas.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Arquivet</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Arquivet es una marca espa\u00f1ola especializada en productos para mascotas. Sus jaulas para aves combinan dise\u00f1o funcional con materiales de calidad. Ofrecen modelos para peque\u00f1as y medianas aves, con barrotes de metal y bandejas extra\u00edbles. Tambi\u00e9n comercializan accesorios y complementos para el cuidado aviar.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">BPS Buena Pet Shop</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>BPS (Buena Pet Shop) es conocida por sus jaulas plegables y transportadores. Sus productos est\u00e1n dise\u00f1ados para la movilidad: jaulas de viaje, transportadores para loros y accesorios port\u00e1tiles. Una opci\u00f3n pr\u00e1ctica para quienes necesitan una jaula funcional para desplazamientos.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Flamingo</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Flamingo es una marca con amplia trayectoria en el cuidado de mascotas. Sus jaulas para aves se caracterizan por un dise\u00f1o cl\u00e1sico y colores vibrantes. Ofrecen modelos para canarios, periquitos y loros peque\u00f1os, con barrotes de alambre resistente y bandejas de limpieza.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">PH Prevue Hendryx</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>PH Prevue Hendryx es una marca estadounidense con m\u00e1s de 60 a\u00f1os de historia. Fabricantes de las emblem\u00e1ticas jaulas estilo vuelo (flight cages), ideales para que las aves puedan volar horizontalmente. Sus jaulas son espaciosas, con barrotes horizontales que facilitan el ejercicio y la exploraci\u00f3n.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">VIVOHOME</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>VIVOHOME se especializa en jaulas grandes y aviarios. Sus productos est\u00e1n pensados para aves que necesitan espacio: jaulas con ruedas, aviarios de exterior y jaulones para m\u00faltiples aves. Destacan por su estructura reforzada y acabados en acero resistente a la intemperie.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Caracter\u00edsticas clave al elegir una marca de jaulas</h2>
<!-- /wp:heading -->

<!-- wp:table -->
<figure class="wp-block-table"><table><tbody>
<tr><td><strong>Calidad del material</strong></td><td>Acero inoxidable o metal con recubrimiento electrost\u00e1tico anticorrosi\u00f3n</td></tr>
<tr><td><strong>Tipo de cierre</strong></td><td>Cierres bloqueables con pestillo de seguridad antiescape</td></tr>
<tr><td><strong>Bandeja extra\u00edble</strong></td><td>Facilita la limpieza diaria y mantiene la higiene del ave</td></tr>
<tr><td><strong>Garant\u00eda</strong></td><td>Las mejores marcas ofrecen 1\u20133 a\u00f1os de garant\u00eda contra defectos</td></tr>
<tr><td><strong>Disponibilidad de repuestos</strong></td><td>Bandejas, rejillas y accesorios disponibles por separado</td></tr>
<tr><td><strong>Relaci\u00f3n calidad-precio</strong></td><td>Equilibrio entre durabilidad, prestaciones y coste</td></tr>
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Productos destacados por marca</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Explora nuestra selecci\u00f3n de productos organizados por marca. Cada enlace te lleva a la ficha completa con caracter\u00edsticas, ventajas y valoraciones.</p>
<!-- /wp:paragraph -->

<!-- wp:asap/cluster {"display":"category","display_setting":[163],"edit_mode":false} /-->

<!-- wp:heading -->
<h2 class="wp-block-heading">Preguntas frecuentes sobre marcas de jaulas para aves</h2>
<!-- /wp:heading -->

<script type="application/ld+json">{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"\u00bfCu\u00e1l es la mejor marca de jaulas para aves?","acceptedAnswer":{"@type":"Answer","text":"No hay una \u00fanica mejor marca, depende del tipo de ave y presupuesto. Yaheetech ofrece buena relaci\u00f3n calidad-precio, Ferplast es ideal para principiantes, PH Prevue Hendryx destaca en jaulas de vuelo, y VIVOHOME en aviarios grandes."}},{"@type":"Question","name":"\u00bfLas jaulas Yaheetech son seguras?","acceptedAnswer":{"@type":"Answer","text":"S\u00ed, Yaheetech fabrica jaulas con barrotes de metal resistente y cierres seguros. Sus modelos para loros incluyen doble pestillo y bandejas extra\u00edbles."}},{"@type":"Question","name":"\u00bfD\u00f3nde puedo comprar jaulas Ferplast en Chile?","acceptedAnswer":{"@type":"Answer","text":"Ferplast se encuentra disponible en Amazon con env\u00edo internacional a Chile. Los modelos Ibiza, Regina y Bali son los m\u00e1s populares."}},{"@type":"Question","name":"\u00bfQu\u00e9 marca recomiendas para jaulas de vuelo?","acceptedAnswer":{"@type":"Answer","text":"PH Prevue Hendryx es la marca l\u00edder en jaulas de vuelo. Sus modelos son alargados horizontalmente, permitiendo que las aves vuelen de un extremo a otro."}},{"@type":"Question","name":"\u00bfVIVOHOME vende aviarios para exterior?","acceptedAnswer":{"@type":"Answer","text":"S\u00ed, VIVOHOME ofrece aviarios de exterior con estructura de acero resistente a la intemperie y techo impermeable."}}]}</script>

<p><strong>\u00bfVenden algo m\u00e1s que jaulas?</strong> Muchas de estas marcas tambi\u00e9n comercializan accesorios, perchas, comederos, bebederos y juguetes. Explora nuestra secci\u00f3n de <a href="''' + PILLAR_URL + '''accesorios/">accesorios para jaulas</a>.</p>

<p><strong>\u00bfBuscas un tipo concreto de jaula?</strong> Visita nuestra <a href="''' + PILLAR_URL + '''">Gu\u00eda Principal de Jaulas para Aves</a>.</p>

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"width":100,"className":"cta"} -->
<div class="wp-block-button has-custom-width wp-block-button__width-100 cta"><a class="wp-block-button__link wp-element-button" href="https://amzn.to/3TdvvIt" target="_blank" rel="noreferrer noopener nofollow">\ud83d\udc49\ud83c\udffb Ver ofertas de jaulas en Amazon</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->'''

marcas_data = {
    'title': 'Marcas de Jaulas para Aves: Yaheetech, Vision, Ferplast y M\u00e1s',
    'content': marcas_content,
    'slug': 'marcas',
    'parent': PILLAR_ID,
    'status': 'publish',
    'excerpt': 'Gu\u00eda completa de las mejores marcas de jaulas para aves: Yaheetech, Vision, Ferplast, Arquivet, BPS, Flamingo, PH Prevue Hendryx y VIVOHOME.'
}
existing = api('GET', f'/pages?slug=marcas&parent={PILLAR_ID}&_fields=id')
if existing:
    api('PUT', f'/pages/{existing[0]["id"]}', marcas_data)
    print(f'Updated /jaulas/marcas/ (ID {existing[0]["id"]})')
else:
    result = api('POST', '/pages', marcas_data)
    print(f'Created /jaulas/marcas/ (ID {result["id"]})')

# 3. Update main pillar to add marcas link
main = api('GET', f'/pages/{PILLAR_ID}?context=edit')
main_raw = main.get('content', {}).get('raw', '')

marcas_li = '<li><a href="' + PILLAR_URL + 'marcas/"><strong>Marcas de jaulas</strong></a> — Yaheetech, Ferplast, Vision, Arquivet, Flamingo, PH Prevue Hendryx, VIVOHOME y m\u00e1s...</li>'

if '<li><a href="' + PILLAR_URL + 'marcas/' not in main_raw:
    pattern = r'(<ul>\s*<li><a href="' + re.escape(PILLAR_URL) + r'portatiles/)'
    m = re.search(pattern, main_raw)
    if m:
        ul_end = main_raw.find('</ul>', m.start())
        if ul_end > 0:
            new_main_raw = main_raw[:ul_end] + '\n' + marcas_li + '\n' + main_raw[ul_end:]
            api('PUT', f'/pages/{PILLAR_ID}', {'content': new_main_raw})
            print('Added marcas link to main pillar')
        else:
            print('Could not find ul end')
    else:
        # fallback: append to end
        new_main_raw = main_raw.rstrip() + '\n\n<!-- wp:list -->\n<ul>\n' + marcas_li + '\n</ul>\n<!-- /wp:list -->'
        api('PUT', f'/pages/{PILLAR_ID}', {'content': new_main_raw})
        print('Fallback: appended marcas link to main pillar')
else:
    print('Main pillar already has marcas link')

# 4. Verify everything
print('\n=== VERIFICATION ===')
all_posts = []
page = 1
while True:
    batch = api('GET', f'/posts?categories=163&per_page=100&page={page}&_fields=id,slug,content')
    if not batch:
        break
    all_posts.extend(batch)
    page += 1
    if len(batch) < 100:
        break
print(f'Total posts: {len(all_posts)}')
linked = 0
for p in all_posts:
    r = p.get('content', {}).get('rendered', '')
    if 'Si quieres ver m' in r or '/jaulas/' in r.replace(PILLAR_URL + 'jaulas/', ''):
        linked += 1
print(f'With sub-pillar link: {linked}')
print(f'Missing: {len(all_posts) - linked}')

print('\nSub-pillar pages:')
pages = api('GET', f'/pages?parent={PILLAR_ID}&per_page=50&_fields=id,slug')
for p in pages:
    print(f'  /jaulas/{p["slug"]}/ (ID {p["id"]})')
