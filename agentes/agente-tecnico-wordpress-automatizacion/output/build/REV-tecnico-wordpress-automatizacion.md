# Build Tecnico - Automatizar sistema de fichas destacadas
## Sesion: 2026-05-17

## URL / foco
`infomoteles.cl`

## Estado
Listo para implementar y validar con fichas reales.

## Evidencia
- El CPT `ficha_destacada` cubre el flujo base.
- El shortcode ordena fichas por precio y acepta filtro por categoria.
- Falta validar el flujo final en mobile.

## Custom Post Type: ficha_destacada
```php
<?php
function registrar_ficha_destacada() {
    register_post_type('ficha_destacada', array(
        'labels' => array('name' => 'Fichas Destacadas', 'singular_name' => 'Ficha Destacada'),
        'public' => true,
        'menu_icon' => 'dashicons-star-filled',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'has_archive' => true,
    ));
}
add_action('init', 'registrar_ficha_destacada');

function shortcode_fichas_destacadas($atts) {
    $atts = shortcode_atts(array(), $atts);
    $args = array(
        'post_type' => 'ficha_destacada',
        'posts_per_page' => isset($atts['limite']) ? intval($atts['limite']) : 10,
        'meta_key' => 'precio',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    );
    if (!empty($atts['categoria'])) {
        $args['tax_query'] = array(array('taxonomy' => 'categoria_ficha', 'field' => 'slug', 'terms' => $atts['categoria']));
    }
    $fichas = new WP_Query($args);
    $html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;padding:20px">';
    while ($fichas->have_posts()) { $fichas->the_post();
        $precio = get_post_meta(get_the_ID(), 'precio', true);
        $whatsapp = get_post_meta(get_the_ID(), 'whatsapp', true);
        $html .= '<div style="border:1px solid #ddd;border-radius:8px;padding:15px">';
        $html .= get_the_post_thumbnail(get_the_ID(), 'medium', array('style'=>'width:100%;height:200px;object-fit:cover;border-radius:4px'));
        $html .= '<h3 style="margin:10px 0">'.get_the_title().'</h3>';
        $html .= '<p>'.get_the_excerpt().'</p>';
        $html .= '<div style="font-size:24px;color:#1a73e8;font-weight:bold">$'.number_format(intval($precio)).'/mes</div>';
        if ($whatsapp) { $html .= '<a href="https://wa.me/'.$whatsapp.'" style="display:block;background:#25D366;color:white;text-align:center;padding:10px;border-radius:6px;text-decoration:none;margin-top:10px">Contactar por WhatsApp</a>'; }
        $html .= '</div>';
    }
    $html .= '</div>';
    wp_reset_postdata();
    return $html;
}
add_shortcode('fichas_destacadas', 'shortcode_fichas_destacadas');
?>
```

## Accion
1. Probar el shortcode con 3 fichas reales en una ciudad piloto.
2. Crear la taxonomia `categoria_ficha`.
3. Agregar campos: precio, whatsapp, telefono, horario y direccion.
4. Publicar la pagina "Fichas Destacadas" con el shortcode.
5. Validar en mobile.

## Formulario de contacto a WhatsApp
```html
<form action="https://wa.me/569XXXXXXXX" method="get" target="_blank" style="max-width:400px">
  <input type="text" name="text" placeholder="Hola, quiero info sobre la ficha destacada" style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:4px" />
  <button type="submit" style="width:100%;padding:12px;background:#25D366;color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer">Enviar por WhatsApp</button>
</form>
```

## Checklist de implementacion
- [ ] Agregar CPT a functions.php (tema hijo si existe)
- [ ] Crear taxonomy categoria_ficha
- [ ] Agregar campos personalizados: precio, whatsapp, telefono, horario, direccion
- [ ] Probar shortcode [fichas_destacadas]
- [ ] Crear pagina "Fichas Destacadas" con el shortcode
- [ ] Probar en mobile

## Criterio de cierre
Queda cerrado cuando el shortcode muestre 3 fichas reales sin romper el layout y el CTA a WhatsApp funcione en mobile.
