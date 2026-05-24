#!/usr/bin/env python3
"""Create missing line pages (1,2,5,6,8) for recorridos-de-micros in WordPress"""

import requests
import json

WP_API = "https://visitandopuntaarenas.cl/wp-json/wp/v2"
AUTH = ("cvieille", "EpAe QMwP G12X H6nJ KZXo WgdM")
CATEGORY_RECORRIDOS = 745  # "Recorridos de micros"
PILLAR_URL = "https://visitandopuntaarenas.cl/transporte/recorridos-de-micros/"

lines = [
    {
        "slug": "linea-1",
        "num": "1",
        "title": "Línea 1 Punta Arenas: horario, recorrido y paradas",
        "origin_a": "Población Archipiélago de Chiloé",
        "origin_b": "Hospital Clínico",
        "dest_a": "Hospital Clínico (Clínica UMG)",
        "dest_b": "Población Archipiélago de Chiloé",
        "direction_a_label": "Hospital Clínico",
        "direction_b_label": "Archipiélago de Chiloé",
        "streets": [
            "Población Archipiélago de Chiloé",
            "Barrio 18 de Septiembre",
            "Manuel Aguilar",
            "Avenida Presidente Eduardo Frei Montalva",
            "Villa Alfredo Lorca",
            "Mall Espacio Urbano Pionero",
            "Avenida Independencia",
            "Centro",
            "Hospital Clínico (Clínica UMG)"
        ],
        "stops_a_count": 48,
        "stops_b_count": 45,
        "duration_a": 35,
        "duration_b": 37,
        "schedule_a": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "07:30 - 21:30",
            "Domingo": "07:30 - 21:30"
        },
        "schedule_b": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "07:30 - 21:30",
            "Domingo": "07:30 - 21:30"
        },
        "main_stops_a": [
            "Terminal — Población Archipiélago de Chiloé",
            "Barrio 18 de Septiembre",
            "Manuel Aguilar",
            "Avenida Presidente Eduardo Frei Montalva",
            "Villa Alfredo Lorca",
            "Mall Espacio Urbano Pionero",
            "Avenida Independencia / Centro",
            "Hospital Clínico (Clínica UMG)"
        ],
        "main_stops_b": [
            "Hospital Clínico (Clínica UMG)",
            "Mall Espacio Urbano Pionero",
            "Villa Alfredo Lorca",
            "Avenida Presidente Eduardo Frei Montalva",
            "Manuel Aguilar",
            "Centro — Avenida Independencia",
            "Barrio 18 de Septiembre",
            "Terminal — Población Archipiélago de Chiloé"
        ],
        "faq": [
            {"q": "¿Cuánto demora la Línea 1 en Punta Arenas?", "a": "La Línea 1 demora aproximadamente <strong>35 minutos hacia Hospital Clínico</strong> y cerca de <strong>37 minutos hacia Archipiélago de Chiloé</strong>, dependiendo del tráfico y las condiciones del recorrido."},
            {"q": "¿Hasta qué hora pasa la Línea 1?", "a": "De lunes a viernes, la Línea 1 funciona hasta las <strong>22:00</strong> en ambos sentidos. Los fines de semana opera hasta las <strong>21:30</strong>."},
            {"q": "¿La Línea 1 llega al Mall Espacio Urbano Pionero?", "a": "Sí. La Línea 1 pasa por el <strong>Mall Espacio Urbano Pionero</strong> en ambos sentidos, siendo una de las líneas recomendadas para llegar al centro comercial."},
            {"q": "¿Cuántas paradas tiene la Línea 1?", "a": "La Línea 1 tiene aproximadamente <strong>48 paradas hacia Hospital Clínico</strong> y <strong>45 paradas hacia Archipiélago de Chiloé</strong>."},
            {"q": "¿Qué sectores conecta la Línea 1?", "a": "La Línea 1 conecta sectores como <strong>Población Archipiélago de Chiloé, Barrio 18 de Septiembre, Villa Alfredo Lorca, Mall Espacio Urbano, Centro y Hospital Clínico</strong>."}
        ]
    },
    {
        "slug": "linea-2",
        "num": "2",
        "title": "Línea 2 Punta Arenas: horario, recorrido y paradas",
        "origin_a": "Zona Franca",
        "origin_b": "Villa Nelda Panicucci",
        "dest_a": "Villa Nelda Panicucci",
        "dest_b": "Zona Franca",
        "direction_a_label": "Villa Nelda Panicucci",
        "direction_b_label": "Zona Franca",
        "streets": [
            "Zona Franca",
            "Avenida Manuel Bulnes",
            "Avenida España",
            "Villa Alfredo Lorca",
            "General Juan Salvo",
            "Centro",
            "Avenida Independencia",
            "Población Carlos Ibáñez del Campo",
            "Loteo del Mar (Variante 2)",
            "Avenida Salvador Allende",
            "Barrio 18 de Septiembre",
            "Villa Nelda Panicucci"
        ],
        "stops_a_count": 52,
        "stops_b_count": 50,
        "duration_a": 40,
        "duration_b": 38,
        "schedule_a": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "07:30 - 21:30",
            "Domingo": "07:30 - 21:30"
        },
        "schedule_b": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "07:30 - 21:30",
            "Domingo": "07:30 - 21:30"
        },
        "main_stops_a": [
            "Terminal Zona Franca",
            "Avenida Manuel Bulnes",
            "Avenida España",
            "Villa Alfredo Lorca",
            "General Juan Salvo",
            "Centro — Avenida Independencia",
            "Población Carlos Ibáñez del Campo",
            "Loteo del Mar",
            "Avenida Salvador Allende",
            "Barrio 18 de Septiembre",
            "Terminal — Villa Nelda Panicucci"
        ],
        "main_stops_b": [
            "Terminal — Villa Nelda Panicucci",
            "Barrio 18 de Septiembre",
            "Avenida Salvador Allende",
            "Loteo del Mar",
            "Población Carlos Ibáñez del Campo",
            "Avenida Independencia",
            "Centro — General del Canto",
            "Villa Alfredo Lorca",
            "Avenida España",
            "Avenida Manuel Bulnes",
            "Terminal Zona Franca"
        ],
        "faq": [
            {"q": "¿Cuánto demora la Línea 2 en Punta Arenas?", "a": "La Línea 2 demora aproximadamente <strong>40 minutos hacia Villa Nelda Panicucci</strong> y cerca de <strong>38 minutos hacia Zona Franca</strong>, dependiendo del tráfico y las condiciones del recorrido."},
            {"q": "¿Hasta qué hora pasa la Línea 2?", "a": "De lunes a viernes, la Línea 2 funciona hasta las <strong>22:00</strong> en ambos sentidos. Los fines de semana opera hasta las <strong>21:30</strong>."},
            {"q": "¿La Línea 2 va a Zona Franca?", "a": "Sí. La Línea 2 es una de las principales líneas que conectan con <strong>Zona Franca</strong>, partiendo desde Villa Nelda Panicucci y pasando por el centro y avenidas importantes."},
            {"q": "¿Cuántas paradas tiene la Línea 2?", "a": "La Línea 2 tiene aproximadamente <strong>52 paradas hacia Villa Nelda Panicucci</strong> y <strong>50 paradas hacia Zona Franca</strong>."},
            {"q": "¿Qué sectores conecta la Línea 2?", "a": "La Línea 2 conecta sectores como <strong>Zona Franca, Avenida Bulnes, Avenida España, Centro, Población Carlos Ibáñez del Campo, Loteo del Mar, Barrio 18 de Septiembre y Villa Nelda Panicucci</strong>."}
        ]
    },
    {
        "slug": "linea-5",
        "num": "5",
        "title": "Línea 5 Punta Arenas: horario, recorrido y paradas",
        "origin_a": "Hospital Clínico",
        "origin_b": "Villa Mirador al Estrecho",
        "dest_a": "Villa Mirador al Estrecho",
        "dest_b": "Hospital Clínico",
        "direction_a_label": "Mirador al Estrecho",
        "direction_b_label": "Hospital Clínico",
        "streets": [
            "Hospital Clínico",
            "Mall Espacio Urbano Pionero",
            "Alfredo Lorca",
            "Santos Mardones",
            "Centro",
            "Loteo del Mar",
            "Villa Mirador al Estrecho"
        ],
        "stops_a_count": 44,
        "stops_b_count": 42,
        "duration_a": 32,
        "duration_b": 30,
        "schedule_a": {
            "Lunes": "07:00 - 21:30",
            "Martes": "07:00 - 21:30",
            "Miércoles": "07:00 - 21:30",
            "Jueves": "07:00 - 21:30",
            "Viernes": "07:00 - 21:30",
            "Sábado": "07:30 - 21:00",
            "Domingo": "07:30 - 21:00"
        },
        "schedule_b": {
            "Lunes": "07:00 - 21:30",
            "Martes": "07:00 - 21:30",
            "Miércoles": "07:00 - 21:30",
            "Jueves": "07:00 - 21:30",
            "Viernes": "07:00 - 21:30",
            "Sábado": "07:30 - 21:00",
            "Domingo": "07:30 - 21:00"
        },
        "main_stops_a": [
            "Terminal Hospital Clínico",
            "Mall Espacio Urbano Pionero",
            "Alfredo Lorca",
            "Santos Mardones",
            "Centro",
            "Loteo del Mar",
            "Terminal — Villa Mirador al Estrecho"
        ],
        "main_stops_b": [
            "Terminal — Villa Mirador al Estrecho",
            "Loteo del Mar",
            "Centro",
            "Santos Mardones",
            "Alfredo Lorca",
            "Mall Espacio Urbano Pionero",
            "Terminal Hospital Clínico"
        ],
        "faq": [
            {"q": "¿Cuánto demora la Línea 5 en Punta Arenas?", "a": "La Línea 5 demora aproximadamente <strong>32 minutos hacia Villa Mirador al Estrecho</strong> y cerca de <strong>30 minutos hacia Hospital Clínico</strong>, dependiendo del tráfico y las condiciones del recorrido."},
            {"q": "¿Hasta qué hora pasa la Línea 5?", "a": "De lunes a viernes, la Línea 5 funciona hasta las <strong>21:30</strong> en ambos sentidos. Los fines de semana opera hasta las <strong>21:00</strong>."},
            {"q": "¿La Línea 5 va a Zona Franca?", "a": "La Línea 5 pasa por sectores cercanos a Zona Franca y conecta con el centro, siendo una alternativa para quienes necesitan moverse entre el sector sur y el área comercial."},
            {"q": "¿Cuántas paradas tiene la Línea 5?", "a": "La Línea 5 tiene aproximadamente <strong>44 paradas hacia Villa Mirador al Estrecho</strong> y <strong>42 paradas hacia Hospital Clínico</strong>."},
            {"q": "¿Qué sectores conecta la Línea 5?", "a": "La Línea 5 conecta sectores como <strong>Hospital Clínico, Mall Espacio Urbano, Alfredo Lorca, Centro, Loteo del Mar y Villa Mirador al Estrecho</strong>."}
        ]
    },
    {
        "slug": "linea-6",
        "num": "6",
        "title": "Línea 6 Punta Arenas: horario, recorrido y paradas",
        "origin_a": "Hospital Clínico",
        "origin_b": "Población Archipiélago de Chiloé",
        "dest_a": "Población Archipiélago de Chiloé",
        "dest_b": "Hospital Clínico",
        "direction_a_label": "Archipiélago de Chiloé",
        "direction_b_label": "Hospital Clínico",
        "streets": [
            "Hospital Clínico (Clínica UMG)",
            "Mall Espacio Urbano Pionero",
            "Población Raúl Silva Henríquez",
            "Población Juan Williams",
            "Centro",
            "Avenida Independencia",
            "Arauco",
            "Población Río de la Mano",
            "Avenida Pedro Aguirre Cerda",
            "Población Simón Bolívar",
            "Avenida Jorge Alessandri",
            "Población Archipiélago de Chiloé"
        ],
        "stops_a_count": 54,
        "stops_b_count": 52,
        "duration_a": 42,
        "duration_b": 40,
        "schedule_a": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "07:30 - 21:30",
            "Domingo": "07:30 - 21:30"
        },
        "schedule_b": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "07:30 - 21:30",
            "Domingo": "07:30 - 21:30"
        },
        "main_stops_a": [
            "Terminal Hospital Clínico",
            "Mall Espacio Urbano Pionero",
            "Población Raúl Silva Henríquez",
            "Población Juan Williams",
            "Centro",
            "Avenida Independencia",
            "Arauco",
            "Población Río de la Mano",
            "Avenida Pedro Aguirre Cerda",
            "Población Simón Bolívar",
            "Avenida Jorge Alessandri",
            "Terminal — Población Archipiélago de Chiloé"
        ],
        "main_stops_b": [
            "Terminal — Población Archipiélago de Chiloé",
            "Avenida Jorge Alessandri",
            "Avenida Pedro Aguirre Cerda",
            "Población Río de la Mano",
            "Avenida Independencia Sur",
            "Centro",
            "Zenteno — El Ovejero",
            "Población Raúl Silva Henríquez",
            "Mall Espacio Urbano Pionero",
            "Terminal Hospital Clínico"
        ],
        "faq": [
            {"q": "¿Cuánto demora la Línea 6 en Punta Arenas?", "a": "La Línea 6 demora aproximadamente <strong>42 minutos hacia Archipiélago de Chiloé</strong> y cerca de <strong>40 minutos hacia Hospital Clínico</strong>, dependiendo del tráfico y las condiciones del recorrido."},
            {"q": "¿Hasta qué hora pasa la Línea 6?", "a": "De lunes a viernes, la Línea 6 funciona hasta las <strong>22:00</strong> en ambos sentidos. Los fines de semana opera hasta las <strong>21:30</strong>."},
            {"q": "¿La Línea 6 pasa por el Mall Espacio Urbano?", "a": "Sí. La Línea 6 pasa por el <strong>Mall Espacio Urbano Pionero</strong> en su recorrido, siendo una de las líneas que conectan el centro comercial con sectores residenciales."},
            {"q": "¿Cuántas paradas tiene la Línea 6?", "a": "La Línea 6 tiene aproximadamente <strong>54 paradas hacia Archipiélago de Chiloé</strong> y <strong>52 paradas hacia Hospital Clínico</strong>."},
            {"q": "¿Qué sectores conecta la Línea 6?", "a": "La Línea 6 conecta sectores como <strong>Hospital Clínico, Mall, Población Raúl Silva Henríquez, Población Juan Williams, Centro, Río de la Mano, Simón Bolívar y Archipiélago de Chiloé</strong>."}
        ]
    },
    {
        "slug": "linea-8",
        "num": "8",
        "title": "Línea 8 Punta Arenas: horario, recorrido y paradas",
        "origin_a": "Hospital Clínico",
        "origin_b": "Población Archipiélago de Chiloé",
        "dest_a": "Población Archipiélago de Chiloé",
        "dest_b": "Hospital Clínico",
        "direction_a_label": "Archipiélago de Chiloé",
        "direction_b_label": "Hospital Clínico",
        "streets": [
            "Hospital Clínico (Clínica UMG)",
            "Villa Las Nieves",
            "Zona Franca",
            "Kusma Slavic",
            "Estadio Fiscal",
            "Villa Las Naciones",
            "Costanera del Estrecho",
            "Población Playa Norte",
            "Jorge Montt",
            "Centro",
            "Chiloé",
            "Población Fitz Roy",
            "Avenida Pedro Aguirre Cerda",
            "Población Archipiélago de Chiloé"
        ],
        "stops_a_count": 56,
        "stops_b_count": 53,
        "duration_a": 45,
        "duration_b": 42,
        "schedule_a": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "09:00 - 21:00",
            "Domingo": "09:00 - 22:00"
        },
        "schedule_b": {
            "Lunes": "07:00 - 22:00",
            "Martes": "07:00 - 22:00",
            "Miércoles": "07:00 - 22:00",
            "Jueves": "07:00 - 22:00",
            "Viernes": "07:00 - 22:00",
            "Sábado": "09:00 - 21:00",
            "Domingo": "09:00 - 22:00"
        },
        "main_stops_a": [
            "Terminal Hospital Clínico",
            "Villa Las Nieves",
            "Zona Franca",
            "Kusma Slavic",
            "Estadio Fiscal",
            "Villa Las Naciones",
            "Costanera del Estrecho",
            "Población Playa Norte",
            "Jorge Montt",
            "Centro",
            "Chiloé",
            "Población Fitz Roy",
            "Avenida Pedro Aguirre Cerda",
            "Terminal — Población Archipiélago de Chiloé"
        ],
        "main_stops_b": [
            "Terminal — Población Archipiélago de Chiloé",
            "Avenida Pedro Aguirre Cerda",
            "Las Heras",
            "Avenida España",
            "Centro",
            "Ignacio Carrera Pinto",
            "Barrio Croata",
            "Población Playa Norte",
            "Costanera del Estrecho",
            "Villa Las Naciones",
            "Universidad de Magallanes (UMAG)",
            "Zona Franca",
            "Villa Las Nieves",
            "Terminal Hospital Clínico"
        ],
        "faq": [
            {"q": "¿Cuánto demora la Línea 8 en Punta Arenas?", "a": "La Línea 8 demora aproximadamente <strong>45 minutos hacia Archipiélago de Chiloé</strong> y cerca de <strong>42 minutos hacia Hospital Clínico</strong>, dependiendo del tráfico y las condiciones del recorrido."},
            {"q": "¿Hasta qué hora pasa la Línea 8?", "a": "De lunes a viernes, la Línea 8 funciona hasta las <strong>22:00</strong>. Los sábados hasta las <strong>21:00</strong> y los domingos hasta las <strong>22:00</strong>."},
            {"q": "¿La Línea 8 va a la Universidad de Magallanes (UMAG)?", "a": "Sí. La Línea 8 pasa por la <strong>Universidad de Magallanes (UMAG)</strong> en su recorrido de regreso, siendo la línea recomendada para estudiantes y visitantes."},
            {"q": "¿Cuántas paradas tiene la Línea 8?", "a": "La Línea 8 tiene aproximadamente <strong>56 paradas hacia Archipiélago de Chiloé</strong> y <strong>53 paradas hacia Hospital Clínico</strong>. En septiembre de 2025 se extendió su recorrido en el sector sur."},
            {"q": "¿Qué sectores conecta la Línea 8?", "a": "La Línea 8 conecta sectores como <strong>Hospital Clínico, Zona Franca, UMAG, Costanera del Estrecho, Centro, Barrio Croata y Archipiélago de Chiloé</strong>."}
        ]
    }
]

def make_table_rows(schedule):
    rows = ""
    for day, hours in schedule.items():
        rows += f"<tr><td>{day}</td><td>{hours}</td></tr>"
    return rows

def make_faq_block(faqs, line_num):
    items = ""
    for i, faq in enumerate(faqs):
        items += f"""<!-- wp:ub/content-toggle-panel-block {{"index":{i},"theme":"#f1f1f1","collapsed":true,"hasFAQSchema":true,"titleColor":"#000000","panelTitle":"{faq['q']}","titleTag":"h3"}} -->
<!-- wp:paragraph {{"placeholder":"Panel content"}} -->
<p>{faq['a']}</p>
<!-- /wp:paragraph -->
<!-- /wp:ub/content-toggle-panel-block -->\n"""
    
    return f"""<!-- wp:ub/content-toggle-block {{"blockID":"linea-{line_num}-faq","theme":"#f1f1f1","collapsed":true,"titleColor":"#000000","hasFAQSchema":true,"titleTag":"h3"}} -->
{items}
<!-- /wp:ub/content-toggle-block -->"""

def make_list(items):
    lis = "\n".join([f'<!-- wp:list-item -->\n<li>{item}</li>\n<!-- /wp:list-item -->' for item in items])
    return f'<!-- wp:list -->\n<ul class="wp-block-list">\n{lis}\n</ul>\n<!-- /wp:list -->'

for line in lines:
    num = line["num"]
    
    content = f"""<!-- wp:paragraph -->
<p>La <strong>Micro {num} de Punta Arenas</strong> realiza el recorrido entre <strong>{line['origin_a']}</strong> y <strong>{line['dest_a']}</strong>, pasando por sectores y avenidas importantes de la ciudad como <strong>{', '.join(line['streets'][:5])}</strong>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>En sentido hacia <strong>{line['dest_a']}</strong>, la Micro {num} cuenta con aproximadamente <strong>{line['stops_a_count']} paradas</strong> y tarda cerca de <strong>{line['duration_a']} minutos</strong>. En sentido hacia <strong>{line['origin_b']}</strong>, el <a href="{PILLAR_URL}">recorrido de la micro</a> tiene cerca de <strong>{line['stops_b_count']} paradas</strong> y una duración aproximada de <strong>{line['duration_b']} minutos</strong>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Recorrido Micro {num} Punta Arenas</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>El <strong>recorrido de la Micro {num} en Punta Arenas</strong> conecta sectores residenciales con avenidas principales y puntos clave de la ciudad. Su trayecto permite trasladarse entre <strong>{line['origin_a']}</strong> y <strong>{line['dest_a']}</strong>, pasando por puntos importantes de la ciudad.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Entre las principales calles y avenidas del recorrido se encuentran:</p>
<!-- /wp:paragraph -->

{make_list(line['streets'])}

<!-- wp:heading -->
<h2 class="wp-block-heading">Horario de la Línea {num} en Punta Arenas</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La Micro {num} tiene dos sentidos principales:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item -->
<li><strong>Sentido {line['direction_a_label']}</strong></li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><strong>Sentido {line['direction_b_label']}</strong></li>
<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Horario hacia {line['direction_a_label']}</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La Micro {num} hacia <strong>{line['dest_a']}</strong> funciona en los siguientes horarios:</p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Día</th><th>Horario</th></tr></thead><tbody>
{make_table_rows(line['schedule_a'])}
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {{"style":{{"typography":{{"textAlign":"center"}}}}}} -->
<p class="has-text-align-center">Este recorrido cuenta con <strong>{line['stops_a_count']} paradas</strong> y una duración aproximada de <strong>{line['duration_a']} minutos</strong>.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {{"level":3}} -->
<h3 class="wp-block-heading">Horario hacia {line['direction_b_label']}</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La Micro {num} hacia <strong>{line['origin_b']}</strong> funciona en los siguientes horarios:</p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table"><table class="has-fixed-layout"><thead><tr><th>Día</th><th>Horario</th></tr></thead><tbody>
{make_table_rows(line['schedule_b'])}
</tbody></table></figure>
<!-- /wp:table -->

<!-- wp:paragraph {{"style":{{"typography":{{"textAlign":"center"}}}}}} -->
<p class="has-text-align-center">Este sentido tiene <strong>{line['stops_b_count']} paradas</strong> y una duración aproximada de <strong>{line['duration_b']} minutos</strong>.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Paradas principales hacia {line['direction_a_label']}</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Estas son algunas de las paradas destacadas del sentido hacia <strong>{line['dest_a']}</strong>:</p>
<!-- /wp:paragraph -->

{make_list(line['main_stops_a'])}

<!-- wp:paragraph -->
<p>El recorrido completo hacia {line['direction_a_label']} tiene <strong>{line['stops_a_count']} paradas</strong>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Paradas principales hacia {line['direction_b_label']}</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>En dirección a <strong>{line['origin_b']}</strong>, algunas de las paradas principales son:</p>
<!-- /wp:paragraph -->

{make_list(line['main_stops_b'])}

<!-- wp:paragraph -->
<p>El recorrido hacia {line['direction_b_label']} tiene <strong>{line['stops_b_count']} paradas</strong> y tarda cerca de <strong>{line['duration_b']} minutos</strong>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Preguntas Frecuentes</h2>
<!-- /wp:heading -->

{make_faq_block(line['faq'], num)}

<!-- wp:paragraph -->
<p><strong>Te puede interesar:</strong></p>
<!-- /wp:paragraph -->

<!-- wp:asap/cluster {{"display":"tag","display_setting":[744],"edit_mode":false}} /-->
"""

    post_data = {
        "title": line["title"],
        "content": content,
        "slug": line["slug"],
        "categories": [CATEGORY_RECORRIDOS],
        "status": "publish"
    }

    print(f"\n{'='*60}")
    print(f"Creating {line['slug']}...")
    print(f"Title: {line['title']}")
    print(f"Content length: {len(content)} chars")
    
    resp = requests.post(
        f"{WP_API}/posts",
        json=post_data,
        auth=AUTH,
        headers={"Content-Type": "application/json"},
        timeout=30
    )

    if resp.status_code in (200, 201):
        data = resp.json()
        print(f"✓ SUCCESS: {data['link']} (ID: {data['id']})")
    else:
        print(f"✗ ERROR {resp.status_code}: {resp.text[:500]}")

print("\n\nDone creating all line pages.")
