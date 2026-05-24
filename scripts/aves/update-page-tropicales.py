import json, requests

WP_URL = 'https://avesnativaschilenas.cl'
USER = 'cvieille'
APP_PASSWORD = 'u0wM 1VRi v9wL Z71R 7XCx mnEq'
PAGE_ID = 6286

new_title = 'Aves Tropicales Chilenas: Especies, H\u00e1bitats y Conservaci\u00f3n [Gu\u00eda 2026]'

new_content = """<h2>Introducci\u00f3n</h2>
<p>Chile, conocido por su geograf\u00eda extrema que va desde el desierto m\u00e1s \u00e1rido del mundo hasta los glaciares patag\u00f3nicos, alberga una sorprendente riqueza de <strong>aves tropicales y subtropicales</strong>. Aunque el pa\u00eds tiene clima mayormente templado-fr\u00edo, sus zonas norte\u00f1as, islas oce\u00e1nicas y archipi\u00e9lagos albergan especies de origen tropical que convierten a Chile en un destino de observaci\u00f3n \u00fanico.</p>
<p>Este art\u00edculo es la gu\u00eda central sobre las <strong>aves tropicales chilenas</strong>. Aqu\u00ed encontrar\u00e1s el mapa completo de especies, sus h\u00e1bitats, estado de conservaci\u00f3n, y enlaces a gu\u00edas detalladas de cada una.</p>

<h2>\u00bfQu\u00e9 son las aves tropicales chilenas?</h2>
<p>Cuando hablamos de "aves tropicales chilenas" nos referimos a especies que tienen su origen evolutivo en zonas tropicales y que habitan en Chile principalmente en tres regiones:</p>
<ul>
<li><strong>Norte Grande (Arica-Parinacota, Tarapac\u00e1, Antofagasta):</strong> valles y oasis costeros donde llegan especies tropicales en su l\u00edmite sur de distribuci\u00f3n. Destacan los picaflores, la corbatita, el cazamoscas picochato y el mart\u00edn pescador verde.</li>
<li><strong>Isla de Pascua (Rapa Nui):</strong> isla oce\u00e1nica polin\u00e9sica con aves marinas tropicales como las aves del tr\u00f3pico, piqueros, fragatas y gaviotines, muchas con nombres tradicionales rapa nui.</li>
<li><strong>Archipi\u00e9lago Juan Fern\u00e1ndez e Islas Desventuradas:</strong> ecosistemas insulares subtropicales con alto endemismo, como el picaflor de Juan Fern\u00e1ndez, el cachudito y la fardela blanca.</li>
</ul>

<h2>Especies de aves tropicales en Chile</h2>

<h3>Aves del tr\u00f3pico (Phaethontidae) — Los rabijuncos</h3>
<p>Las aves del tr\u00f3pico son el grupo m\u00e1s emblem\u00e1tico de aves tropicales en Chile. Se caracterizan por sus largas plumas centrales en la cola y su vuelo gr\u00e1cile. Tres especies habitan aguas chilenas:</p>
<ul>
<li><strong>Ave del tr\u00f3pico de cola roja</strong> (<em>Phaethon rubricauda</em>) — Isla de Pascua y Sala y G\u00f3mez. Conocida en rapa nui como <em>Tavake</em>. Es la m\u00e1s com\u00fan.</li>
<li><strong>Ave del tr\u00f3pico de cola blanca</strong> (<em>Phaethon lepturus</em>) — Isla de Pascua, Juan Fern\u00e1ndez, ocasional en costa norte.</li>
<li><strong>Ave del tr\u00f3pico de pico rojo</strong> (<em>Phaethon aethereus</em>) — Archipi\u00e9lago Juan Fern\u00e1ndez e islas Cha\u00f1aral.</li>
</ul>

<h3>Piqueros (Sulidae)</h3>
<p>Los piqueros son aves marinas de origen tropical que se zambullen desde gran altura para pescar. En Chile se encuentran:</p>
<ul>
<li><strong>Piquero blanco</strong> (<em>Sula dactylatra</em>) — Isla de Pascua (<em>kena</em> en rapa nui), Desventuradas.</li>
<li><strong>Piquero caf\u00e9</strong> (<em>Sula leucogaster</em>) — Isla de Pascua, registro reciente.</li>
<li><strong>Piquero de patas coloradas</strong> (<em>Sula sula</em>) — Accidental en Isla de Pascua.</li>
<li><strong>Piquero peruano</strong> (<em>Sula variegata</em>) — Costa norte de Chile.</li>
</ul>

<h3>Ave fragata (Fregatidae)</h3>
<p>El <strong>ave fragata grande</strong> (<em>Fregata minor</em>) es un ave tropical por excelencia, conocida por su enorme bolsa roja en el cuello que infla durante el cortejo. En rapa nui se le llama <em>Makohe</em>. Habita en Isla de Pascua.</p>

<h3>Fardelas tropicales (Procellariidae)</h3>
<p>Varias especies de fardelas (petreles) de origen tropical anidan en las islas chilenas:</p>
<ul>
<li><strong>Fardela blanca de Juan Fern\u00e1ndez</strong> (<em>Pterodroma externa</em>) — End\u00e9mica reproductora del archipi\u00e9lago.</li>
<li><strong>Fardela negra de Juan Fern\u00e1ndez</strong> (<em>Pterodroma neglecta</em>) — Anida en Juan Fern\u00e1ndez e Isla de Pascua.</li>
<li><strong>Fardela de Pascua</strong> (<em>Puffinus nativitatis</em>) — Conocida como <em>Kima</em> en rapa nui.</li>
<li><strong>Fardela her\u00e1ldica</strong> (<em>Pterodroma arminjoniana</em>) — Isla de Pascua.</li>
<li><strong>Fardela de Phoenix</strong> (<em>Pterodroma alba</em>) — Isla de Pascua.</li>
</ul>

<h3>Colibr\u00edes y picaflores (Trochilidae)</h3>
<p>Los colibr\u00edes son una familia de origen tropical. Chile alberga varias especies, algunas end\u00e9micas y en peligro cr\u00edtico:</p>
<ul>
<li><strong>Picaflor de Arica</strong> (<em>Eulidia yarrellii</em>) — End\u00e9mico de los valles de Arica. Es el ave m\u00e1s peque\u00f1a de Chile. <strong>En peligro cr\u00edtico.</strong></li>
<li><strong>Picaflor del norte</strong> (<em>Rhodopis vesper</em>) — Desde Arica hasta La Serena, en oasis y valles.</li>
<li><strong>Picaflor de Juan Fern\u00e1ndez</strong> (<em>Sephanoides fernandensis</em>) — End\u00e9mico del archipi\u00e9lago. <strong>En peligro cr\u00edtico.</strong></li>
<li><strong>Picaflor gigante</strong> (<em>Patagona gigas</em>) — El colibr\u00ed m\u00e1s grande del mundo. Habita en el norte y centro de Chile.</li>
<li><strong>Picaflor de Cora</strong> (<em>Thaumastura cora</em>) — Visitante raro en el extremo norte.</li>
<li><strong>Picaflor de la Puna</strong> (<em>Oreotrochilus estella</em>) — Altos Andes del norte.</li>
</ul>
<p>\U0001f4d6 <a href=\"/trochilidae/\">Ver gu\u00eda completa de la familia Trochilidae (colibr\u00edes de Chile)</a></p>

<h3>Loros nativos de Chile (Psittacidae)</h3>
<p>Los loros son un grupo de origen tropical. Chile tiene tres especies nativas y una introducida:</p>
<ul>
<li><strong>Loro Tricahue</strong> (<em>Cyanoliseus patagonus bloxami</em>) — Habita en la zona central. Es la especie m\u00e1s emblem\u00e1tica. Estado: vulnerable.</li>
<li><strong>Choroy</strong> (<em>Enicognathus leptorhynchus</em>) — End\u00e9mico de los bosques del sur de Chile. Se reconoce por su pico delgado y curvado.</li>
<li><strong>Cacha\u00f1a</strong> (<em>Enicognathus ferrugineus</em>) — El loro m\u00e1s austral del mundo. Habita desde el centro-sur hasta Magallanes.</li>
<li><strong>Cotorra argentina</strong> (<em>Myiopsitta monachus</em>) — Especie introducida que se ha establecido en Chile central.</li>
</ul>

<h3>Aves tropicales del norte de Chile</h3>
<p>El extremo norte chileno alberga especies que llegan desde los tr\u00f3picos sudamericanos:</p>
<ul>
<li><strong>Corbatita</strong> (<em>Sporophila telasco</em>) — Peque\u00f1a ave gran\u00edvora de los valles de Arica.</li>
<li><strong>Cazamoscas picochato</strong> (<em>Myiophobus rufescens</em>) — Especie tropical que solo se encuentra en Chile en la regi\u00f3n de Arica.</li>
<li><strong>Mart\u00edn pescador verde</strong> (<em>Chloroceryle americana</em>) — Habita en cursos de agua del extremo norte.</li>
<li><strong>Bandurrilla de Arica</strong> — Ave de los valles del norte.</li>
</ul>

<h3>Otras aves de afiliaci\u00f3n tropical</h3>
<ul>
<li><strong>Mart\u00edn pescador</strong> (<em>Megaceryle torquata</em>) — Habita en cuerpos de agua del centro y sur de Chile.</li>
<li><strong>Flamenco chileno</strong> (<em>Phoenicopterus chilensis</em>) — Ave de origen tropical que habita salares del norte y humedales del centro-sur.</li>
<li><strong>Garza boyera</strong> (<em>Bubulcus ibis</em>) — Originaria de \u00c1frica y los tr\u00f3picos, hoy establecida en Chile.</li>
<li><strong>Pid\u00e9n del norte</strong> (<em>Pardirallus sanguinolentus</em>) — Rasc\u00f3n de humedales tropicales.</li>
</ul>

<h2>Aves de Isla de Pascua (Rapa Nui) — Nombres tradicionales</h2>
<p>La avifauna de Isla de Pascua es mayoritariamente tropical y tiene una rica nomenclatura tradicional en idioma rapa nui:</p>
<table>
<thead>
<tr><th>Nombre rapa nui</th><th>Nombre cient\u00edfico</th><th>Nombre en espa\u00f1ol</th></tr>
</thead>
<tbody>
<tr><td>Tavake</td><td><em>Phaethon rubricauda</em></td><td>Ave del tr\u00f3pico cola roja</td></tr>
<tr><td>Manutara</td><td><em>Sterna lunata / fuscata</em></td><td>Gaviot\u00edn pascuense / apizarrado</td></tr>
<tr><td>Makohe</td><td><em>Fregata minor</em></td><td>Ave fragata grande</td></tr>
<tr><td>Kena</td><td><em>Sula dactylatra</em></td><td>Piquero blanco</td></tr>
<tr><td>Kima</td><td><em>Puffinus nativitatis</em></td><td>Fardela de Pascua</td></tr>
<tr><td>Kia Kia</td><td><em>Gygis alba</em></td><td>Gaviot\u00edn albo</td></tr>
<tr><td>Tuao</td><td><em>Anous stolidus</em></td><td>Gaviot\u00edn de San F\u00e9lix</td></tr>
<tr><td>Tavi</td><td><em>Procelsterna ceruleana</em></td><td>Gaviot\u00edn de San Ambrosio</td></tr>
</tbody>
</table>

<h2>H\u00e1bitats y distribuci\u00f3n</h2>
<p>Las aves tropicales chilenas se distribuyen en ecosistemas muy diversos:</p>
<ul>
<li><strong>Valles y oasis del norte:</strong> Azapa, Vitor, Camarones, Lluta, Codpa. Albergan picaflores, corbatitas y cazamoscas.</li>
<li><strong>Islas oce\u00e1nicas:</strong> Rapa Nui, Juan Fern\u00e1ndez, Desventuradas. Concentran la mayor diversidad de aves tropicales marinas.</li>
<li><strong>Bosque escler\u00f3filo central:</strong> H\u00e1bitat del loro Tricahue y la cacha\u00f1a.</li>
<li><strong>Bosques templados del sur:</strong> Hogar del choroy y la cacha\u00f1a (el loro m\u00e1s austral del mundo).</li>
<li><strong>Salares y humedales altoandinos:</strong> Flamencos, picaflor de la Puna.</li>
<li><strong>Costa norte:</strong> Piqueros, gaviotines, mart\u00edn pescador.</li>
</ul>

<h2>Estado de conservaci\u00f3n</h2>
<p>Varias de estas especies enfrentan amenazas graves:</p>
<ul>
<li><strong>En peligro cr\u00edtico (CR):</strong> Picaflor de Arica, Picaflor de Juan Fern\u00e1ndez, Rayadito de Masafuera.</li>
<li><strong>Vulnerable (VU):</strong> Loro Tricahue.</li>
<li><strong>Presi\u00f3n por especies invasoras:</strong> Las aves de Isla de Pascua enfrentan depredaci\u00f3n por gatos y ratas introducidos.</li>
<li><strong>P\u00e9rdida de h\u00e1bitat:</strong> Los valles de Arica pierden cobertura vegetal nativa por agricultura y urbanizaci\u00f3n.</li>
<li><strong>Cambio clim\u00e1tico:</strong> Afecta la disponibilidad de n\u00e9ctar para picaflores y las corrientes marinas de las que dependen las aves oce\u00e1nicas.</li>
</ul>

<h2>Gu\u00edas de especies relacionadas</h2>
<ul>
<li><a href=\"/picaflor-de-arica/\">Picaflor de Arica (Eulidia yarrellii)</a></li>
<li><a href=\"/picaflor-de-juan-fernandez/\">Picaflor de Juan Fern\u00e1ndez</a></li>
<li><a href=\"/trochilidae/\">Familia Trochilidae — Colibr\u00edes de Chile</a></li>
<li><a href=\"/tricahue/\">Loro Tricahue</a></li>
<li><a href=\"/fardela-tropical/\">Fardela tropical</a></li>
<li><a href=\"/fardela-blanca-de-juan-fernandez/\">Fardela blanca de Juan Fern\u00e1ndez</a></li>
<li><a href=\"/fardela/\">La Fardela en Chile</a></li>
<li><a href=\"/fregatidae/\">Familia Fregatidae — Fragatas</a></li>
<li><a href=\"/piqueros-de-patas-azules/\">Piqueros de patas azules</a></li>
<li><a href=\"/el-cachudito-de-juan-fernandez/\">Cachudito de Juan Fern\u00e1ndez</a></li>
<li><a href=\"/bandurrilla-de-arica/\">Bandurrilla de Arica</a></li>
<li><a href=\"/flamenco-chileno/\">Flamenco chileno</a></li>
<li><a href=\"/aves-de-la-primera-region/\">Aves de la Primera Regi\u00f3n</a></li>
<li><a href=\"/aves-de-la-segunda-region/\">Aves de la Segunda Regi\u00f3n</a></li>
<li><a href=\"/aves-de-la-quinta-region-2/\">Aves de la Quinta Regi\u00f3n</a></li>
</ul>

<h2>Lecturas adicionales</h2>
<ul>
<li><a href=\"/diferencia-entre-colibri-y-picaflor-5-claves-para-identificarlos/\">Diferencia entre colibr\u00ed y picaflor</a></li>
<li><a href=\"/aves-en-peligro-de-extincion-en-chile-lista-2026-y-como-ayudarlas/\">Aves en peligro de extinci\u00f3n en Chile 2026</a></li>
<li><a href=\"/loros-que-se-pueden-tener-en-casa/\">Loros que se pueden tener en casa</a></li>
</ul>

<h2>Preguntas frecuentes</h2>
<h3>\u00bfExisten aves tropicales en Chile?</h3>
<p>S\u00ed, Chile alberga aproximadamente 50-60 especies de origen tropical o subtropical, concentradas en el norte grande, Isla de Pascua, Archipi\u00e9lago Juan Fern\u00e1ndez e Islas Desventuradas.</p>
<h3>\u00bfCu\u00e1l es el ave tropical m\u00e1s emblem\u00e1tica de Chile?</h3>
<p>El ave del tr\u00f3pico de cola roja (<em>Phaethon rubricauda</em>), conocida como <em>Tavake</em> en rapa nui, es probablemente la m\u00e1s representativa de las aves tropicales chilenas.</p>
<h3>\u00bfD\u00f3nde ver aves tropicales en Chile?</h3>
<p>Los mejores lugares son: valles de Arica (Azapa, Vitor) para picaflores y corbatitas; Isla de Pascua para aves del tr\u00f3pico, piqueros y fragatas; Archipi\u00e9lago Juan Fern\u00e1ndez para especies end\u00e9micas; y los salares altoandinos para flamencos.</p>
<h3>\u00bfCu\u00e1ntas especies de colibr\u00edes hay en Chile?</h3>
<p>Chile tiene registradas alrededor de 7 especies de colibr\u00edes (familia Trochilidae), incluyendo dos end\u00e9micas en peligro cr\u00edtico: el picaflor de Arica y el picaflor de Juan Fern\u00e1ndez.</p>
<h3>\u00bfQu\u00e9 loros son nativos de Chile?</h3>
<p>Tres especies de loros son nativas de Chile: el loro Tricahue (zona central), el Choroy (bosques del sur) y la Cacha\u00f1a (sur-austral), siendo esta \u00faltima el loro m\u00e1s austral del mundo.</p>"""

# Update the page
r = requests.post(f'{WP_URL}/wp-json/wp/v2/pages/{PAGE_ID}', auth=(USER, APP_PASSWORD),
    json={'title': new_title, 'content': new_content})
print(f'Update page status: {r.status_code}')
if r.status_code == 200:
    d = r.json()
    print(f'✅ Page updated successfully!')
    print(f'   New title: {d.get("title",{}).get("rendered","")}')
    print(f'   URL: {d.get("link","")}')
    print(f'   Content length: {len(new_content)} chars')
else:
    print(f'❌ Error: {r.text[:500]}')

# Delete the duplicate post (14131)
r2 = requests.delete(f'{WP_URL}/wp-json/wp/v2/posts/14131?force=true', auth=(USER, APP_PASSWORD))
print(f'Delete duplicate post: {r2.status_code} {r2.text[:200]}')
