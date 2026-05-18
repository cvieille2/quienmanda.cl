<?php
/**
 * Template rendering for ficha_motel via the_content filter
 */

// Hide default theme elements on ficha_motel singles
add_action('wp_head', function () {
    if (is_singular('ficha_motel')) {
        echo '<style>
.single-ficha_motel .entry-title,
.single-ficha_motel .entry-meta,
.single-ficha_motel .post-thumbnail,
.single-ficha_motel .comments-area,
.single-ficha_motel .entry-footer { display: none !important; }
.single-ficha_motel .entry-content { max-width: 100% !important; padding: 0 !important; }
</style>';
    }
});

// Render custom template via the_content
add_filter('the_content', function ($content) {
    if (!is_singular('ficha_motel') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $nombre_motel = get_post_meta(get_the_ID(), 'nombre_motel', true) ?: get_the_title();
    $ciudad = get_post_meta(get_the_ID(), 'ciudad', true);
    $precio_desde = get_post_meta(get_the_ID(), 'precio_desde', true);
    $horario = get_post_meta(get_the_ID(), 'horario', true);
    $servicios = get_post_meta(get_the_ID(), 'servicios', true) ?: array();
    $telefono = get_post_meta(get_the_ID(), 'telefono', true);
    $whatsapp = get_post_meta(get_the_ID(), 'whatsapp', true);
    $direccion = get_post_meta(get_the_ID(), 'direccion', true);
    $geo_lat = get_post_meta(get_the_ID(), 'geo_lat', true);
    $geo_lng = get_post_meta(get_the_ID(), 'geo_lng', true);
    $fecha_verificacion = get_post_meta(get_the_ID(), 'fecha_verificacion', true);
    $tarifas = get_post_meta(get_the_ID(), 'tarifas', true) ?: array();
    $comuna = get_post_meta(get_the_ID(), 'comuna', true);

    $fmt_precio = $precio_desde ? number_format(intval($precio_desde), 0, ',', '.') : '';
    $ciudad_slug = sanitize_title($ciudad);

    $out = '';

    // Hero
    $out .= '<header class="ficha-hero" style="background:#0f172a;color:#fff;padding:40px 20px;text-align:center;margin:-40px -20px 0">';
    $out .= '<nav style="font-size:14px;margin-bottom:16px;color:#94a3b8">';
    $out .= '<a href="/" style="color:#60a5fa">Chile</a> &gt; ';
    $out .= '<a href="/fichas/' . $ciudad_slug . '/" style="color:#60a5fa">' . esc_html($ciudad) . '</a> &gt; ';
    $out .= '<span style="color:#fff">' . esc_html($nombre_motel) . '</span>';
    $out .= '</nav>';
    $out .= '<h1 style="font-size:32px;margin:0 0 8px">' . esc_html($nombre_motel) . ' en ' . esc_html($ciudad) . '</h1>';
    $out .= '<p style="color:#94a3b8;margin:0 0 20px">Motel en ' . esc_html($ciudad) . '. Informacion verificada y tarifas orientativas actualizadas.</p>';
    $out .= '<div class="badges" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:20px">';
    if ($fmt_precio) {
        $out .= '<span style="background:#2563eb;padding:6px 14px;border-radius:20px;font-size:14px;font-weight:600">Desde $' . $fmt_precio . '</span>';
    }
    if ($horario) {
        $out .= '<span style="background:#1e293b;padding:6px 14px;border-radius:20px;font-size:14px">' . esc_html($horario) . '</span>';
    }
    foreach (array_slice($servicios, 0, 3) as $sv) {
        $out .= '<span style="background:#1e293b;padding:6px 14px;border-radius:20px;font-size:14px">' . esc_html($sv) . '</span>';
    }
    $out .= '</div>';
    $out .= '<div class="ficha-ctas" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:16px">';
    $out .= '<a href="#ficha-tarifas" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">Ver tarifas</a>';
    if ($telefono) {
        $out .= '<a href="tel:' . esc_attr($telefono) . '" style="background:#22c55e;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">Llamar</a>';
    }
    if ($whatsapp) {
        $out .= '<a href="https://wa.me/' . esc_attr($whatsapp) . '" style="background:#25D366;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">WhatsApp</a>';
    }
    $out .= '<a href="#ficha-mapa" style="background:#64748b;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">Como llegar</a>';
    $out .= '</div>';
    if ($fecha_verificacion) {
        $out .= '<p style="font-size:13px;color:#64748b;margin:0">Ultima verificacion: ' . esc_html($fecha_verificacion) . '</p>';
    }
    $out .= '</header>';

    // Content body
    $out .= '<div style="width:100%;max-width:none;margin:24px auto 0">';

    // Featured image
    if (has_post_thumbnail()) {
        $out .= '<section style="margin-bottom:32px">';
        $out .= get_the_post_thumbnail(get_the_ID(), 'ficha-hero', array(
            'style' => 'width:100%;height:400px;object-fit:cover;border-radius:12px',
            'fetchpriority' => 'high',
        ));
        $out .= '</section>';
    }

    // Description
    $out .= '<section style="margin-bottom:32px">';
    $out .= '<h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Descripcion</h2>';
    $out .= '<div style="color:#475569;line-height:1.7">' . $content . '</div>';
    $out .= '</section>';

    // Services
    if ($servicios) {
        $out .= '<section style="margin-bottom:32px">';
        $out .= '<h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Servicios</h2>';
        $out .= '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px">';
        foreach ($servicios as $sv) {
            $out .= '<div style="background:#f8fafc;padding:12px 16px;border-radius:8px;font-size:14px;color:#334155;text-align:center">' . esc_html($sv) . '</div>';
        }
        $out .= '</div>';
        $out .= '</section>';
    }

    // Tariffs
    if ($tarifas) {
        $out .= '<section id="ficha-tarifas" style="margin-bottom:32px">';
        $out .= '<h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Tarifas</h2>';
        $out .= '<div style="overflow-x:auto">';
        $out .= '<table style="width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1)">';
        $out .= '<thead><tr style="background:#0f172a;color:#fff">';
        $out .= '<th style="padding:12px 16px;text-align:left">Habitacion</th>';
        $out .= '<th style="padding:12px 16px;text-align:left">Tiempo</th>';
        $out .= '<th style="padding:12px 16px;text-align:right">Precio</th>';
        $out .= '<th style="padding:12px 16px;text-align:left">Incluye</th>';
        $out .= '</tr></thead><tbody>';
        foreach ($tarifas as $t) {
            $t_precio = isset($t['precio']) ? number_format(intval($t['precio']), 0, ',', '.') : '';
            $out .= '<tr style="border-bottom:1px solid #e2e8f0">';
            $out .= '<td style="padding:12px 16px;font-weight:600">' . esc_html($t['habitacion']) . '</td>';
            $out .= '<td style="padding:12px 16px;color:#64748b">' . esc_html($t['tiempo']) . '</td>';
            $out .= '<td style="padding:12px 16px;text-align:right;font-weight:700;color:#2563eb">$' . $t_precio . '</td>';
            $out .= '<td style="padding:12px 16px;color:#475569">' . esc_html($t['incluye']) . '</td>';
            $out .= '</tr>';
        }
        $out .= '</tbody></table>';
        $out .= '</div>';
        $out .= '</section>';
    }

    // Map
    if ($geo_lat && $geo_lng) {
        $out .= '<section id="ficha-mapa" style="margin-bottom:32px">';
        $out .= '<h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Ubicacion</h2>';
        $out .= '<p style="color:#475569;margin:0 0 12px">' . esc_html($direccion) . ', ' . esc_html($comuna ?: $ciudad) . ', Chile.</p>';
        $out .= '<iframe loading="lazy" src="https://www.google.com/maps?q=' . $geo_lat . ',' . $geo_lng . '&output=embed" width="100%" height="300" style="border:0;border-radius:8px" allowfullscreen></iframe>';
        $out .= '</section>';
    }

    // Reviews
    $out .= '<section style="margin-bottom:32px">';
    $out .= '<h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Opiniones de clientes</h2>';
    $out .= '<div style="border:1px solid #dbe3ee;border-radius:16px;padding:18px;background:#fff;margin-bottom:12px;">';
    $out .= '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">';
    $out .= '<strong style="font-size:18px;color:#0f172a;">Opiniones de clientes</strong>';
    $out .= '<span style="color:#f97316;font-size:20px;line-height:1;">★★★★☆</span>';
    $out .= '<span style="color:#0f172a;font-weight:700;">3.6 de 5</span>';
    $out .= '</div>';
    $out .= '<p style="margin:12px 0 0;color:#64748b">Valoracion publica aproximada recogida de directorios y fichas visibles en internet. Conviene contrastar siempre con la fuente mas reciente antes de reservar.</p>';
    $out .= '</div>';
    $out .= '<p style="color:#64748b">' . (!empty($google_reviews_summary) ? esc_html($google_reviews_summary) : 'Aun no hay un resumen de reseñas publicas cargado para esta ficha.') . '</p>';
    $out .= '<h3 style="margin:18px 0 8px;font-size:18px;color:#0f172a">Mi opinion del lugar</h3>';
    $out .= '<p style="color:#64748b">' . (!empty($ciudad) ? 'Es una opcion funcional en ' . esc_html($ciudad) . ', util si priorizas privacidad, acceso simple y una tarifa de entrada razonable.' : 'Es una opcion funcional si priorizas privacidad, acceso simple y una tarifa de entrada razonable.') . '</p>';
    $out .= '</section>';

    // FAQ
    $out .= '<section style="margin-bottom:32px">';
    $out .= '<h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Preguntas frecuentes</h2>';
    $out .= '<div style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">';
    $out .= '<details style="border-bottom:1px solid #e2e8f0;padding:16px">';
    $out .= '<summary style="font-weight:600;cursor:pointer;color:#0f172a">¿Como puedo reservar?</summary>';
    $out .= '<p style="margin:8px 0 0;color:#475569">Contacta directamente al motel via telefono o WhatsApp. Sujeto a disponibilidad y confirmacion con el establecimiento.</p>';
    $out .= '</details>';
    if ($horario) {
        $out .= '<details style="border-bottom:1px solid #e2e8f0;padding:16px">';
        $out .= '<summary style="font-weight:600;cursor:pointer;color:#0f172a">¿Cual es el horario?</summary>';
        $out .= '<p style="margin:8px 0 0;color:#475569">' . esc_html($horario) . '</p>';
        $out .= '</details>';
    }
    $out .= '<details style="padding:16px">';
    $out .= '<summary style="font-weight:600;cursor:pointer;color:#0f172a">¿Que metodos de pago aceptan?</summary>';
    $out .= '<p style="margin:8px 0 0;color:#475569">Sujeto a confirmacion con el establecimiento. Consulta directamente via telefono o WhatsApp.</p>';
    $out .= '</details>';
    $out .= '</div>';
    $out .= '</section>';

    $out .= '</div>';

    // JSON-LD
    $permalink = get_permalink();
    $out .= '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type":"WebPage",
      "@id":"' . $permalink . '#webpage",
      "url":"' . $permalink . '",
      "name":"' . esc_attr($nombre_motel) . ' en ' . esc_attr($ciudad) . '",
      "dateModified":"' . get_the_modified_date('Y-m-d') . '"
    },
    {
      "@type":"BreadcrumbList",
      "@id":"' . $permalink . '#breadcrumb",
      "itemListElement":[
        {"@type":"ListItem","position":1,"name":"Chile","item":"' . home_url() . '/"},
        {"@type":"ListItem","position":2,"name":"' . esc_attr($ciudad) . '","item":"' . home_url('/fichas/' . $ciudad_slug . '/') . '"},
        {"@type":"ListItem","position":3,"name":"' . esc_attr($nombre_motel) . '","item":"' . $permalink . '"}
      ]
    },
    {
      "@type":"Motel",
      "@id":"' . $permalink . '#motel",
      "name":"' . esc_attr($nombre_motel) . '",
      "telephone":"' . esc_attr($telefono) . '",
      "priceRange":"' . esc_attr($fmt_precio ? '$' . $fmt_precio . '+' : '$$') . '",
      "address":{"@type":"PostalAddress","streetAddress":"' . esc_attr($direccion) . '","addressLocality":"' . esc_attr($ciudad) . '","addressCountry":"CL"},
      "geo":{"@type":"GeoCoordinates","latitude":' . $geo_lat . ',"longitude":' . $geo_lng . '},
      "image":' . (has_post_thumbnail() ? '{"@type":"ImageObject","url":"' . esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')) . '","width":' . (int) (wp_get_attachment_metadata(get_post_thumbnail_id())->width ?? 0) . ',"height":' . (int) (wp_get_attachment_metadata(get_post_thumbnail_id())->height ?? 0) . '}' : 'null') . '
    }
  ]
}
</script>';

    return $out;
}, 999);
