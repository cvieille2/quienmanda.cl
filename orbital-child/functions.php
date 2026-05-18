<?php

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('ficha-hero', 1200, 600, true);
    add_image_size('ficha-card', 600, 360, true);
});

add_action('init', function () {
    add_rewrite_tag('%ciudad%', '([^/]+)');

    if (post_type_exists('ficha_motel')) {
        return;
    }

    register_post_type('ficha_motel', array(
        'labels' => array(
            'name' => 'Fichas de Motel',
            'singular_name' => 'Ficha de Motel',
            'add_new' => 'Nueva ficha',
            'add_new_item' => 'Anadir nueva ficha',
            'edit_item' => 'Editar ficha',
            'view_item' => 'Ver ficha',
            'search_items' => 'Buscar fichas',
            'not_found' => 'No se encontraron fichas',
        ),
        'public' => true,
        'menu_icon' => 'dashicons-building',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'comments'),
        'has_archive' => false,
        'rewrite' => array('slug' => '%ciudad%', 'with_front' => false),
        'show_in_rest' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'capability_type' => 'post',
    ));
});

add_filter('post_type_link', function ($post_link, $post) {
    if ($post->post_type === 'ficha_motel') {
        $ciudad = get_post_meta($post->ID, 'ciudad', true);
        $post_link = str_replace('%ciudad%', $ciudad ? sanitize_title($ciudad) : 'sin-ciudad', $post_link);
    }

    return $post_link;
}, 10, 2);

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

add_filter('document_title_parts', function ($parts) {
    if (is_front_page()) {
        $parts['title'] = 'Infomoteles | Moteles por ciudad, fichas y precios';
        return $parts;
    }

    if (is_page('para-moteles')) {
        $parts['title'] = 'Para moteles | Publica tu motel en InfoMoteles';
        return $parts;
    }

    if (is_singular('ficha_motel')) {
        $ciudad = get_post_meta(get_the_ID(), 'ciudad', true);
        $parts['title'] = get_the_title() . ($ciudad ? ' en ' . $ciudad : '') . ' | InfoMoteles';
        return $parts;
    }

    if (is_single()) {
        $parts['title'] = get_the_title() . ' | Guia de moteles en Chile';
    }

    return $parts;
});

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    $description = '';

    if (is_front_page()) {
        $description = 'Encuentra moteles por ciudad, rescata fichas con traccion y navega una arquitectura SEO limpia en InfoMoteles.';
    } elseif (is_page('para-moteles')) {
        $description = 'Publica tu motel en InfoMoteles con una ficha pensada para captar demanda, mejorar CTR y convertir visitas en contactos.';
    } elseif (is_singular('ficha_motel')) {
        $description = get_the_excerpt();
        if (!$description) {
            $description = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 26, '...');
        }
    } elseif (is_single()) {
        $description = get_the_excerpt();
        if (!$description) {
            $description = wp_trim_words(wp_strip_all_tags(get_post_field('post_content', get_the_ID())), 26, '...');
        }
    }

    if ($description) {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
});

add_filter('body_class', function ($classes) {
    if (is_front_page()) {
        $classes[] = 'infomoteles-front';
    }

    if (is_page('para-moteles')) {
        $classes[] = 'infomoteles-para-moteles';
    }

    if (is_singular('ficha_motel')) {
        $classes[] = 'infomoteles-ficha';
    }

    if (is_single()) {
        $classes[] = 'infomoteles-post';
    }

    return $classes;
});
