<?php
add_shortcode('fichas_motel', function ($atts) {
    $atts = shortcode_atts(array(
        'ciudad' => '',
        'limite' => '10',
    ), $atts);

    $args = array(
        'post_type' => 'ficha_motel',
        'posts_per_page' => intval($atts['limite']),
        'meta_key' => 'precio_desde',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'post_status' => 'publish',
    );

    if (!empty($atts['ciudad'])) {
        $args['meta_query'] = array(array(
            'key' => 'ciudad',
            'value' => $atts['ciudad'],
            'compare' => 'LIKE',
            'type' => 'CHAR',
        ));
    }

    $fichas = new WP_Query($args);
    if (!$fichas->have_posts()) {
        return '<p style="color:#64748b">No se encontraron moteles en esta ciudad.</p>';
    }

    $html = '<div class="grid-fichas" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px">';

    while ($fichas->have_posts()) {
        $fichas->the_post();
        $id = get_the_ID();
        $precio = get_post_meta($id, 'precio_desde', true);
        $whatsapp = get_post_meta($id, 'whatsapp', true);
        $telefono = get_post_meta($id, 'telefono', true);
        $servicios = get_post_meta($id, 'servicios', true) ?: array();
        $ciudad = get_post_meta($id, 'ciudad', true);
        $badges = array_slice($servicios, 0, 3);

        $html .= '<article style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.06)">';

        if (has_post_thumbnail()) {
            $html .= get_the_post_thumbnail($id, 'ficha-card', array(
                'style' => 'width:100%;height:180px;object-fit:cover',
                'loading' => 'lazy',
            ));
        }

        $html .= '<div style="padding:16px">';
        $html .= '<h3 style="margin:0 0 6px;font-size:18px"><a href="' . get_permalink() . '" style="color:#0f172a;text-decoration:none">' . get_the_title() . '</a></h3>';
        $html .= '<div style="color:#64748b;font-size:14px;margin-bottom:8px">' . esc_html($ciudad) . '</div>';

        if ($precio) {
            $html .= '<div style="font-size:22px;color:#2563eb;font-weight:700;margin:0 0 8px">Desde $' . number_format(intval($precio), 0, ',', '.') . '</div>';
        }

        if ($badges) {
            foreach ($badges as $b) {
                $html .= '<span style="background:#e8f0fe;color:#2563eb;padding:3px 10px;border-radius:12px;font-size:12px;margin-right:4px;display:inline-block;margin-bottom:4px">' . esc_html($b) . '</span>';
            }
        }

        if ($whatsapp) {
            $html .= '<a href="https://wa.me/' . esc_attr($whatsapp) . '" style="display:block;background:#25D366;color:#fff;text-align:center;padding:10px;border-radius:6px;text-decoration:none;margin-top:10px;font-weight:600;font-size:14px">WhatsApp</a>';
        } elseif ($telefono) {
            $html .= '<a href="tel:' . esc_attr($telefono) . '" style="display:block;background:#22c55e;color:#fff;text-align:center;padding:10px;border-radius:6px;text-decoration:none;margin-top:10px;font-weight:600;font-size:14px">Llamar</a>';
        }

        $html .= '</div></article>';
    }

    $html .= '</div>';
    wp_reset_postdata();
    return $html;
});
