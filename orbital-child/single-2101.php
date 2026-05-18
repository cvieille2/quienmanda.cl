<?php
/**
 * Template for post ID 2101 - Motel El Refugio de Alsino
 * Single-page test for ficha_motel new design
 */
?>
<style>
.single-post-2101 .default-header,
.single-post-2101 .entry-title,
.single-post-2101 .entry-meta,
.single-post-2101 .post-thumbnail,
.single-post-2101 .comments-area,
.single-post-2101 .entry-footer { display: none !important; }
.single-post-2101 .entry-content { max-width: 100% !important; padding: 0 !important; }
.single-post-2101 .site-main { padding-top: 0 !important; }

.ficha-hero { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #fff; border-radius: 16px; padding: 40px; margin-bottom: 32px; }
.ficha-breadcrumbs { font-size: 14px; color: rgba(255,255,255,.6); margin-bottom: 20px; }
.ficha-breadcrumbs a { color: rgba(255,255,255,.8); text-decoration: none; }
.ficha-breadcrumbs a:hover { color: #fff; text-decoration: underline; }
.ficha-breadcrumbs .sep { margin: 0 8px; }
.ficha-title { font-size: 32px; margin: 0 0 12px; color: #fff; line-height: 1.2; }
.ficha-resumen { font-size: 16px; line-height: 1.5; color: rgba(255,255,255,.75); max-width: 700px; margin: 0 0 20px; }
.ficha-badges { display: flex; gap: 8px; flex-wrap: wrap; margin: 16px 0; }
.badge { padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; }
.badge-price { background: #e94560; color: #fff; }
.badge-hours { background: rgba(255,255,255,.15); color: #fff; }
.badge-location { background: rgba(255,255,255,.15); color: #fff; }
.badge-service { background: rgba(255,255,255,.15); color: #fff; }
.ficha-ctas { display: flex; gap: 10px; flex-wrap: wrap; margin: 20px 0; }
.btn { padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; display: inline-block; transition: all .2s; border: none; cursor: pointer; font-size: 14px; }
.btn-primary { background: #e94560; color: #fff; }
.btn-primary:hover { background: #d6384e; transform: translateY(-1px); }
.btn-secondary { background: #fff; color: #1a1a2e; }
.btn-secondary:hover { background: #f0f0f0; }
.btn-whatsapp { background: #25D366; color: #fff; }
.btn-whatsapp:hover { background: #20bd5a; }
.btn-outline { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.4); }
.btn-outline:hover { border-color: #fff; }
.ficha-freshness { font-size: 13px; color: rgba(255,255,255,.5); margin-top: 16px; }
.ficha-section { background: #fff; border-radius: 12px; padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.ficha-section h2 { font-size: 22px; color: #111; margin: 0 0 16px; }

.tarifas-table { width: 100%; border-collapse: collapse; }
.tarifas-table th { background: #1a1a2e; color: #fff; padding: 12px; text-align: left; }
.tarifas-table td { padding: 12px; border-bottom: 1px solid #eee; }
.tarifas-table tr:nth-child(even) { background: #f8f9fa; }
.table-wrapper { overflow-x: auto; }

.galeria-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
.galeria-grid img { width: 100%; height: 220px; object-fit: cover; border-radius: 8px; }

.faq-item { border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; margin-bottom: 8px; }
.faq-item summary { font-weight: bold; cursor: pointer; color: #1a73e8; }
.faq-item p { margin: 8px 0 0; color: #555; font-size: 14px; }

.relacionados-list { list-style: none; padding: 0; }
.relacionados-list li { margin-bottom: 8px; }
.relacionados-list a { color: #1a73e8; text-decoration: none; }
.relacionados-list a:hover { text-decoration: underline; }

.map-embed { border-radius: 12px; overflow: hidden; margin: 12px 0; }
.map-embed iframe { display: block; }
@media (max-width: 600px) {
  .ficha-hero { padding: 24px 16px; }
  .ficha-section { padding: 20px 16px; }
  .ficha-title { font-size: 24px; }
  .tarifas-table td:before { content: attr(data-label); font-weight: bold; display: inline-block; width: 100px; }
}
</style>

<?php get_header(); ?>

<main id="main" class="site-main">

<?php while (have_posts()) : the_post();

  $cats = get_the_category();
  $ciudad = !empty($cats) ? $cats[0]->name : 'Chile';
  $ciudad_slug = !empty($cats) ? $cats[0]->slug : '';
  $nombre_motel = preg_replace('/ en .*$/', '', get_the_title());
  $excerpt = get_the_excerpt();
  $precio_match = '';
  $raw_content = get_the_content();
  if (preg_match('/\$([0-9.]+)/', $raw_content, $m)) {
    $precio_match = $m[0];
  }
  $content = apply_filters('the_content', get_the_content());
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('ficha-motel'); ?>>

  <header class="ficha-hero">
    <nav class="ficha-breadcrumbs" aria-label="Breadcrumb">
      <a href="<?php echo home_url('/'); ?>">Chile</a><span class="sep"> / </span>
      <?php if (!empty($cats)): ?>
        <a href="<?php echo get_category_link($cats[0]->term_id); ?>"><?php echo esc_html($ciudad); ?></a><span class="sep"> / </span>
      <?php endif; ?>
      <span class="current">Motel <?php echo esc_html($nombre_motel); ?></span>
    </nav>

    <h1 class="ficha-title"><?php the_title(); ?></h1>

    <?php if ($excerpt): ?>
      <p class="ficha-resumen"><?php echo esc_html($excerpt); ?></p>
    <?php endif; ?>

    <div class="ficha-badges">
      <?php if ($precio_match): ?>
        <span class="badge badge-price">Desde <?php echo esc_html($precio_match); ?></span>
      <?php endif; ?>
      <span class="badge badge-hours">24 horas</span>
      <span class="badge badge-location"><?php echo esc_html($ciudad); ?></span>
    </div>

    <div class="ficha-ctas">
      <a href="#tarifas" class="btn btn-primary">Ver tarifas</a>
      <a href="#mapa" class="btn btn-outline">C&oacute;mo llegar</a>
    </div>
  </header>

  <div class="ficha-content">
    <?php echo $content; ?>
  </div>

</article>

<?php
$jsonld = array(
  '@context' => 'https://schema.org',
  '@graph' => array(
    array(
      '@type' => 'WebPage',
      '@id' => get_permalink() . '#webpage',
      'url' => get_permalink(),
      'name' => get_the_title(),
      'isPartOf' => array('@id' => home_url('/') . '#website'),
      'about' => array('@id' => get_permalink() . '#motel'),
      'dateModified' => get_the_modified_time('c')
    ),
    array(
      '@type' => 'BreadcrumbList',
      '@id' => get_permalink() . '#breadcrumb',
      'itemListElement' => array(
        array('@type' => 'ListItem', 'position' => 1, 'name' => 'Chile', 'item' => home_url('/')),
        array('@type' => 'ListItem', 'position' => 2, 'name' => $ciudad, 'item' => home_url('/' . $ciudad_slug . '/')),
        array('@type' => 'ListItem', 'position' => 3, 'name' => get_the_title())
      )
    ),
    array(
      '@type' => 'Motel',
      '@id' => get_permalink() . '#motel',
      'name' => get_the_title(),
      'url' => get_permalink(),
      'description' => $excerpt,
      'currenciesAccepted' => 'CLP',
      'address' => array(
        '@type' => 'PostalAddress',
        'addressLocality' => $ciudad,
        'addressCountry' => 'CL'
      ),
      'openingHoursSpecification' => array(
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
        'opens' => '00:00',
        'closes' => '23:59'
      )
    )
  )
);
?>
<script type="application/ld+json">
<?php echo wp_json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
</script>

<?php endwhile; ?>

</main>

<?php get_footer(); ?>