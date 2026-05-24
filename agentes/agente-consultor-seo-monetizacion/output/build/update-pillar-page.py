#!/usr/bin/env python3
"""Update pillar page (recorridos-de-micros) with links to new line pages"""

import requests
import json

WP_API = "https://visitandopuntaarenas.cl/wp-json/wp/v2"
AUTH = ("cvieille", "EpAe QMwP G12X H6nJ KZXo WgdM")
PAGE_ID = 15043

BASE_URL = "https://visitandopuntaarenas.cl/transporte/recorridos-de-micros"

lines_links = {
    "1": f"{BASE_URL}/linea-1/",
    "2": f"{BASE_URL}/linea-2/",
    "5": f"{BASE_URL}/linea-5/",
    "6": f"{BASE_URL}/linea-6/",
    "8": f"{BASE_URL}/linea-8/",
    "9": f"{BASE_URL}/linea-9/",
}

# Get current content
resp = requests.get(f"{WP_API}/pages/{PAGE_ID}?context=edit&_fields=id,content", auth=AUTH, timeout=30)
data = resp.json()
content = data["content"]["raw"]
original = content

print(f"Original content length: {len(content)}")

# --- UPDATE TABLE ROWS ---
# For lines 1, 2, 5, 6, 8: add anchor links around the line name in the <strong> tags

# Line 1: <strong>Linea 1</strong> -> <strong><a href="...">Linea 1</a></strong>
content = content.replace(
    '<strong>Linea 1</strong>',
    f'<strong><a href="{lines_links["1"]}">Linea 1</a></strong>',
    1
)

# Line 2: <strong>Linea 2</strong> -> linked
content = content.replace(
    '<strong>Linea 2</strong>',
    f'<strong><a href="{lines_links["2"]}">Linea 2</a></strong>',
    1
)

# Line 5: has weird nesting <strong><strong>Linea </strong>5</strong>
content = content.replace(
    '<strong><strong>Linea </strong>5</strong>',
    f'<strong><a href="{lines_links["5"]}">Linea 5</a></strong>',
    1
)

# Line 6: <strong><strong>Linea </strong></strong> <strong>6</strong>
content = content.replace(
    '<strong><strong>Linea </strong></strong> <strong>6</strong>',
    f'<strong><a href="{lines_links["6"]}">Linea 6</a></strong>',
    1
)

# Line 8: <strong><strong>Linea </strong>8</strong>
content = content.replace(
    '<strong><strong>Linea </strong>8</strong>',
    f'<strong><a href="{lines_links["8"]}">Linea 8</a></strong>',
    1
)

# --- UPDATE H3 SECTIONS: add "Ver recorrido completo" links ---
# After each H3 paragraph, add a link to the dedicated page

h3_replacements = {
    "1": (
        """<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Recorrido micro 1 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 1</strong> es una de las l\u00edneas \u00fatiles para conectar sectores residenciales con el centro, Mall Espacio Urbano, Barrio 18 de Septiembre y Hospital Cl\u00ednico. Confirma el sentido si vas desde o hacia el hospital.</p>
<!-- /wp:paragraph -->""",
        f"""<!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Recorrido micro 1 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 1</strong> es una de las l\u00edneas \u00fatiles para conectar sectores residenciales con el centro, Mall Espacio Urbano, Barrio 18 de Septiembre y Hospital Cl\u00ednico. Confirma el sentido si vas desde o hacia el hospital.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong><a href=\"{lines_links["1"]}\">Ver recorrido completo de la L\u00ednea 1 &raquo;</a></strong></p>
<!-- /wp:paragraph -->"""
    ),
    "2": (
        """<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Recorrido micro 2 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 2</strong> es una de las referencias principales para moverse entre <strong>Zona Franca</strong>, Avenida Bulnes, Avenida Espa\u00f1a, centro y Villa Nelda Panicucci. Es una de las primeras l\u00edneas que conviene revisar si tu destino es Zona Franca.</p>
<!-- /wp:paragraph -->""",
        f"""<!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Recorrido micro 2 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 2</strong> es una de las referencias principales para moverse entre <strong>Zona Franca</strong>, Avenida Bulnes, Avenida Espa\u00f1a, centro y Villa Nelda Panicucci. Es una de las primeras l\u00edneas que conviene revisar si tu destino es Zona Franca.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong><a href=\"{lines_links["2"]}\">Ver recorrido completo de la L\u00ednea 2 &raquo;</a></strong></p>
<!-- /wp:paragraph -->"""
    ),
    "5": (
        """<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Recorrido micro 5 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 5</strong> aparece asociada a viajes hacia <strong>Zona Franca</strong> y sectores urbanos conectados con el centro. Revisa horarios y sentido actualizado antes de subir, especialmente si buscas llegar a Zona Franca.</p>
<!-- /wp:paragraph -->""",
        f"""<!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Recorrido micro 5 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 5</strong> aparece asociada a viajes hacia <strong>Zona Franca</strong> y sectores urbanos conectados con el centro. Revisa horarios y sentido actualizado antes de subir, especialmente si buscas llegar a Zona Franca.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong><a href=\"{lines_links["5"]}\">Ver recorrido completo de la L\u00ednea 5 &raquo;</a></strong></p>
<!-- /wp:paragraph -->"""
    ),
    "6": (
        """<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Recorrido micro 6 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 6</strong> sirve como referencia para conectar sectores sur, centro, R\u00edo de la Mano, Mall Espacio Urbano y Zona Franca. Puede ser \u00fatil si quieres combinar entre centro, mall y sectores residenciales.</p>
<!-- /wp:paragraph -->""",
        f"""<!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Recorrido micro 6 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 6</strong> sirve como referencia para conectar sectores sur, centro, R\u00edo de la Mano, Mall Espacio Urbano y Zona Franca. Puede ser \u00fatil si quieres combinar entre centro, mall y sectores residenciales.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong><a href=\"{lines_links["6"]}\">Ver recorrido completo de la L\u00ednea 6 &raquo;</a></strong></p>
<!-- /wp:paragraph -->"""
    ),
    "8": (
        """<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Recorrido micro 8 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 8</strong> es una de las l\u00edneas m\u00e1s \u00fatiles para viajes hacia <strong>UMAG</strong>, Costanera, Zona Franca, centro y Hospital Cl\u00ednico. Si buscas c\u00f3mo llegar a la Universidad de Magallanes, revisa primero esta l\u00ednea.</p>
<!-- /wp:paragraph -->""",
        f"""<!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Recorrido micro 8 Punta Arenas</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La <strong>micro 8</strong> es una de las l\u00edneas m\u00e1s \u00fatiles para viajes hacia <strong>UMAG</strong>, Costanera, Zona Franca, centro y Hospital Cl\u00ednico. Si buscas c\u00f3mo llegar a la Universidad de Magallanes, revisa primero esta l\u00ednea.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong><a href=\"{lines_links["8"]}\">Ver recorrido completo de la L\u00ednea 8 &raquo;</a></strong></p>
<!-- /wp:paragraph -->"""
    ),
}

for line_num, (old, new) in h3_replacements.items():
    if old in content:
        content = content.replace(old, new, 1)
        print(f"Line {line_num} H3 section: UPDATED")
    else:
        print(f"Line {line_num} H3 section: NOT FOUND (maybe encoding diff)")
        # Try to find it by searching for partial match
        search_term = f"Recorrido micro {line_num} Punta Arenas"
        if search_term in content:
            print(f"  But '{search_term}' IS in content")
        else:
            print(f"  '{search_term}' NOT in content either")

print(f"\nNew content length: {len(content)}")
print(f"Content changed: {content != original}")

if content != original:
    # Update the page
    update_resp = requests.put(
        f"{WP_API}/pages/{PAGE_ID}",
        json={"content": content},
        auth=AUTH,
        headers={"Content-Type": "application/json"},
        timeout=30
    )
    
    if update_resp.status_code in (200, 201):
        updated = update_resp.json()
        print(f"\n[OK] Pillar page updated: {updated['link']}")
    else:
        print(f"\n[ERROR] {update_resp.status_code}: {update_resp.text[:500]}")
else:
    print("\nNo changes needed!")

# Verify
print("\n--- VERIFICATION ---")
for line_num, url in sorted(lines_links.items()):
    r = requests.get(url, timeout=15, allow_redirects=True)
    print(f"Linea {line_num}: {r.status_code} -> {r.url}")
