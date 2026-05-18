<?php
add_action('wp_head', function () {
    if (!is_singular('ficha_motel')) {
        return;
    }
    $id = get_the_ID();
    $nombre = get_post_meta($id, 'nombre_motel', true) ?: get_the_title();
    $ciudad = get_post_meta($id, 'ciudad', true);
    $telefono = get_post_meta($id, 'telefono', true);
    $direccion = get_post_meta($id, 'direccion', true);
    $geo_lat = get_post_meta($id, 'geo_lat', true);
    $geo_lng = get_post_meta($id, 'geo_lng', true);
    $permalink = get_permalink($id);
    $ciudad_slug = sanitize_title($ciudad);
    $modified = get_the_modified_date('Y-m-d');
    $lat = $geo_lat ?: 0;
    $lng = $geo_lng ?: 0;
    ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "<?php echo $permalink; ?>#webpage",
      "url": "<?php echo $permalink; ?>",
      "name": "<?php echo esc_attr($nombre); ?> en <?php echo esc_attr($ciudad); ?>",
      "dateModified": "<?php echo $modified; ?>"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "<?php echo $permalink; ?>#breadcrumb",
      "itemListElement": [
        {"@type":"ListItem","position":1,"name":"Chile","item":"<?php echo home_url(); ?>/"},
        {"@type":"ListItem","position":2,"name":"<?php echo esc_attr($ciudad); ?>","item":"<?php echo home_url('/fichas/' . $ciudad_slug . '/'); ?>"},
        {"@type":"ListItem","position":3,"name":"<?php echo esc_attr($nombre); ?>","item":"<?php echo $permalink; ?>"}
      ]
    },
    {
      "@type": "Motel",
      "@id": "<?php echo $permalink; ?>#motel",
      "name": "<?php echo esc_attr($nombre); ?>",
      "telephone": "<?php echo esc_attr($telefono); ?>",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "<?php echo esc_attr($direccion); ?>",
        "addressLocality": "<?php echo esc_attr($ciudad); ?>",
        "addressCountry": "CL"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": <?php echo $lat; ?>,
        "longitude": <?php echo $lng; ?>
      }
    }
  ]
}
</script>
    <?php
});
