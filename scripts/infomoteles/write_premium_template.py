import requests
import time

auth = ("cvieille", "QCZg BNQs mcnf HMXE OvBm MXpz")

tpl = r'''<?php
/**
 * Template Name: Ficha de Motel
 * Template Post Type: ficha_motel
 */

get_header();

$meta = get_post_meta(get_the_ID());
$nombre_motel = !empty($meta['nombre_motel'][0]) ? $meta['nombre_motel'][0] : get_the_title();
$ciudad = !empty($meta['ciudad'][0]) ? $meta['ciudad'][0] : '';
$comuna = !empty($meta['comuna'][0]) ? $meta['comuna'][0] : '';
$direccion = !empty($meta['direccion'][0]) ? $meta['direccion'][0] : '';
$telefono = !empty($meta['telefono'][0]) ? $meta['telefono'][0] : '';
$whatsapp = !empty($meta['whatsapp'][0]) ? preg_replace('/[^0-9]/', '', $meta['whatsapp'][0]) : '';
$horario = !empty($meta['horario'][0]) ? $meta['horario'][0] : '';
$precio_desde = !empty($meta['precio_desde'][0]) ? (int) $meta['precio_desde'][0] : 0;
$fecha_verificacion = !empty($meta['fecha_verificacion'][0]) ? $meta['fecha_verificacion'][0] : '';
$servicios = !empty($meta['servicios'][0]) ? maybe_unserialize($meta['servicios'][0]) : array();
$tarifas = !empty($meta['tarifas'][0]) ? maybe_unserialize($meta['tarifas'][0]) : array();
$imagenes = !empty($meta['imagenes_galeria'][0]) ? maybe_unserialize($meta['imagenes_galeria'][0]) : array();
$geo_lat = !empty($meta['geo_lat'][0]) ? $meta['geo_lat'][0] : '';
$geo_lng = !empty($meta['geo_lng'][0]) ? $meta['geo_lng'][0] : '';
$ciudad_slug = $ciudad ? sanitize_title($ciudad) : '';
$excerpt = get_the_excerpt();
$primary = get_theme_mod('im_ficha_primary', '#2563eb');
$accent = get_theme_mod('im_ficha_accent', '#ff8a00');
$hero_bg = get_theme_mod('im_ficha_hero_bg', '#0f172a');
$surface = get_theme_mod('im_ficha_surface', '#ffffff');
$text = get_theme_mod('im_ficha_text', '#111827');

?>

<style>
  .single-ficha_motel {
    background: #eef2f7;
    color: var(--im-text, #111827);
  }
  .single-ficha_motel .ficha-page {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 26px 20px 50px;
    --im-primary: <?php echo esc_html($primary); ?>;
    --im-accent: <?php echo esc_html($accent); ?>;
    --im-hero-bg: <?php echo esc_html($hero_bg); ?>;
    --im-surface: <?php echo esc_html($surface); ?>;
    --im-text: <?php echo esc_html($text); ?>;
  }
  .single-ficha_motel .ficha-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 24px;
    align-items: start;
  }
  .single-ficha_motel .ficha-main { display: grid; gap: 20px; }
  .single-ficha_motel .ficha-hero-image {
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 18px 50px rgba(15, 23, 42, .16);
    background: linear-gradient(135deg, #dbe7f5 0%, #f8fafc 100%);
  }
  .single-ficha_motel .ficha-hero-image img,
  .single-ficha_motel .ficha-hero-image .placeholder {
    display: block;
    width: 100%;
    height: clamp(250px, 34vw, 420px);
    object-fit: cover;
  }
  .single-ficha_motel .placeholder { background: linear-gradient(135deg, rgba(15, 23, 42, .15), rgba(15, 23, 42, .02)); }
  .single-ficha_motel .ficha-card {
    background: var(--im-surface);
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 18px;
    box-shadow: 0 10px 34px rgba(15, 23, 42, .07);
  }
  .single-ficha_motel .ficha-card { padding: 28px; }
  .single-ficha_motel .ficha-title-row {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 10px;
  }
  .single-ficha_motel .ficha-title {
    margin: 0; font-size: clamp(30px, 3vw, 44px); line-height: 1.04; letter-spacing: -.03em; color: var(--im-text);
  }
  .single-ficha_motel .ficha-back { color: #0f172a; text-decoration: none; font-weight: 700; white-space: nowrap; margin-top: 8px; }
  .single-ficha_motel .ficha-breadcrumbs {
    display: flex; flex-wrap: wrap; gap: 8px; align-items: center; color: #64748b; font-size: 14px; margin-bottom: 10px;
  }
  .single-ficha_motel .ficha-breadcrumbs a { color: #64748b; text-decoration: none; }
  .single-ficha_motel .ficha-breadcrumbs .current { color: #0f172a; font-weight: 600; }
  .single-ficha_motel .ficha-summary { color: #334155; line-height: 1.7; margin: 14px 0 0; font-size: 16px; }
  .single-ficha_motel .ficha-meta-line { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 14px; color: #475569; font-size: 14px; }
  .single-ficha_motel .ficha-meta-line .dot { width: 4px; height: 4px; border-radius: 50%; background: #cbd5e1; }
  .single-ficha_motel .ficha-badges { display: flex; flex-wrap: wrap; gap: 10px; margin: 18px 0 0; }
  .single-ficha_motel .badge { display: inline-flex; align-items: center; min-height: 34px; padding: 0 14px; border-radius: 999px; background: #edf2ff; color: #334155; font-size: 14px; font-weight: 600; }
  .single-ficha_motel .badge-price { background: var(--im-accent); color: #fff; }
  .single-ficha_motel .ficha-stars { display: flex; align-items: center; gap: 8px; margin-top: 18px; color: #1e293b; font-weight: 600; }
  .single-ficha_motel .stars { color: #f5b301; letter-spacing: 2px; font-size: 18px; }
  .single-ficha_motel .ficha-ctas { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-top: 20px; }
  .single-ficha_motel .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 52px; padding: 0 18px; border-radius: 12px; text-decoration: none; font-weight: 800; border: 1px solid transparent; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
  .single-ficha_motel .btn:hover { transform: translateY(-1px); }
  .single-ficha_motel .btn-primary { background: var(--im-primary); color: #fff; box-shadow: 0 10px 22px rgba(37, 99, 235, .18); }
  .single-ficha_motel .btn-secondary { background: #fff; color: var(--im-primary); border-color: rgba(37, 99, 235, .22); }
  .single-ficha_motel .btn-outline { background: #fff; color: #0f172a; border-color: rgba(15, 23, 42, .12); }
  .single-ficha_motel .ficha-freshness { margin: 14px 0 0; font-size: 13px; color: #64748b; }
  .single-ficha_motel .ficha-section { padding: 22px; }
  .single-ficha_motel .ficha-section h2 { margin: 0 0 14px; font-size: 20px; letter-spacing: -.02em; color: #0f172a; }
  .single-ficha_motel .ficha-section p, .single-ficha_motel .ficha-section li, .single-ficha_motel .ficha-section td, .single-ficha_motel .ficha-section summary { color: #334155; }
  .single-ficha_motel .ficha-copy { line-height: 1.8; font-size: 16px; }
  .single-ficha_motel .servicios-list { margin: 0; padding-left: 18px; display: grid; gap: 8px; }
  .single-ficha_motel .table-wrapper { overflow-x: auto; }
  .single-ficha_motel .tarifas-table { width: 100%; border-collapse: collapse; min-width: 560px; }
  .single-ficha_motel .tarifas-table th, .single-ficha_motel .tarifas-table td { padding: 14px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
  .single-ficha_motel .tarifas-table th { font-size: 13px; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
  .single-ficha_motel .tarifas-table td:nth-child(3) { font-weight: 800; color: var(--im-primary); }
  .single-ficha_motel .faq-item { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 12px; background: #fff; }
  .single-ficha_motel .faq-item summary { cursor: pointer; font-weight: 700; list-style: none; }
  .single-ficha_motel .faq-item summary::-webkit-details-marker { display: none; }
  .single-ficha_motel .faq-item p { margin: 10px 0 0; line-height: 1.7; }
  .single-ficha_motel .galeria-grid { display: grid; grid-template-columns: 1.6fr .9fr .9fr; gap: 12px; }
  .single-ficha_motel .galeria-hero { grid-row: span 2; }
  .single-ficha_motel .galeria-item { border-radius: 16px; overflow: hidden; background: #e2e8f0; min-height: 170px; }
  .single-ficha_motel .galeria-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
  @media (max-width: 1060px) {
    .single-ficha_motel .ficha-grid { grid-template-columns: 1fr; }
    .single-ficha_motel .galeria-grid { grid-template-columns: 1fr 1fr; }
    .single-ficha_motel .galeria-hero { grid-row: span 1; grid-column: 1 / -1; }
  }
  @media (max-width: 680px) {
    .single-ficha_motel .ficha-page { padding-inline: 14px; }
    .single-ficha_motel .ficha-card, .single-ficha_motel .ficha-section { padding: 18px; border-radius: 16px; }
    .single-ficha_motel .ficha-ctas { grid-template-columns: 1fr; }
    .single-ficha_motel .galeria-grid { grid-template-columns: 1fr; }
    .single-ficha_motel .ficha-title-row { flex-direction: column; }
  }
</style>

<div class="container">
<article id="post-<?php the_ID(); ?>" <?php post_class('ficha-motel ficha-page'); ?>>
  <div class="ficha-grid">
    <div class="ficha-main">
      <div class="ficha-hero-image">
        <?php if (has_post_thumbnail()): ?>
          <?php the_post_thumbnail('full'); ?>
        <?php else: ?>
          <div class="placeholder"></div>
        <?php endif; ?>
      </div>

      <section class="ficha-card">
        <nav class="ficha-breadcrumbs" aria-label="Breadcrumb">
          <a href="<?php echo esc_url(home_url('/')); ?>">Chile</a>
          <span>/</span>
          <?php if ($ciudad): ?>
            <a href="<?php echo esc_url(home_url('/' . $ciudad_slug . '/')); ?>"><?php echo esc_html($ciudad); ?></a>
            <span>/</span>
          <?php endif; ?>
          <span class="current"><?php echo esc_html($nombre_motel); ?></span>
        </nav>

        <div class="ficha-title-row">
          <h1 class="ficha-title"><?php echo esc_html($nombre_motel); ?></h1>
          <a class="ficha-back" href="<?php echo esc_url(wp_get_referer() ?: home_url('/')); ?>">← Atrás</a>
        </div>

        <div class="ficha-meta-line">
          <span><?php echo esc_html($direccion ?: ($ciudad ?: 'Chile')); ?></span>
          <span class="dot"></span>
          <span><?php echo esc_html($comuna ?: ''); ?></span>
        </div>

        <div class="ficha-badges">
          <?php if ($precio_desde): ?><span class="badge badge-price">Desde $<?php echo number_format($precio_desde, 0, ',', '.'); ?></span><?php endif; ?>
          <?php if ($horario): ?><span class="badge"><?php echo esc_html($horario); ?></span><?php endif; ?>
          <?php if ($ciudad): ?><span class="badge"><?php echo esc_html($ciudad); ?></span><?php endif; ?>
          <?php foreach (array_slice((array) $servicios, 0, 2) as $sv): ?><span class="badge"><?php echo esc_html($sv); ?></span><?php endforeach; ?>
        </div>

        <div class="ficha-stars">
          <span class="stars">★★★★★</span>
          <span>Ficha verificada</span>
        </div>

        <?php if ($excerpt): ?>
          <p class="ficha-summary"><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>

        <div class="ficha-ctas">
          <?php if ($telefono): ?><a href="tel:<?php echo esc_attr($telefono); ?>" class="btn btn-primary">CONTACTAR</a><?php else: ?><a href="#mapa" class="btn btn-primary">CONTACTAR</a><?php endif; ?>
          <?php if ($whatsapp): ?><a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" class="btn btn-secondary" target="_blank" rel="noopener">RESERVAR</a><?php else: ?><a href="#tarifas" class="btn btn-secondary">RESERVAR</a><?php endif; ?>
        </div>
      </section>

      <section id="descripcion" class="ficha-section ficha-card">
        <h2>Descripcion</h2>
        <div class="ficha-copy entry-content"><?php the_content(); ?></div>
      </section>

      <?php if (!empty($servicios)): ?>
        <section id="servicios" class="ficha-section ficha-card">
          <h2>Servicios y amenidades</h2>
          <ul class="servicios-list">
            <?php foreach ((array) $servicios as $sv): ?><li><?php echo esc_html($sv); ?></li><?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>

      <?php if (!empty($tarifas)): ?>
        <section id="tarifas" class="ficha-section ficha-card">
          <h2>Tarifas y habitaciones</h2>
          <div class="table-wrapper">
            <table class="tarifas-table">
              <thead>
                <tr>
                  <th>Habitacion</th>
                  <th>Tiempo</th>
                  <th>Precio</th>
                  <th>Incluye</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ((array) $tarifas as $t): ?>
                  <tr>
                    <td data-label="Habitacion"><?php echo esc_html($t['habitacion'] ?? ''); ?></td>
                    <td data-label="Tiempo"><?php echo esc_html($t['tiempo'] ?? ''); ?></td>
                    <td data-label="Precio"><?php echo !empty($t['precio']) ? '$' . number_format((int) $t['precio'], 0, ',', '.') : ''; ?></td>
                    <td data-label="Incluye"><?php echo esc_html($t['incluye'] ?? ''); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <section id="mapa" class="ficha-section ficha-card">
        <h2>Ubicacion</h2>
        <p class="ficha-direccion"><?php echo esc_html($direccion); ?><?php echo $comuna ? ', ' . esc_html($comuna) : ''; ?><?php echo $ciudad ? ', ' . esc_html($ciudad) : ''; ?>, Chile.</p>
        <div class="map-embed">
          <?php $map_query = urlencode(trim("$direccion $comuna $ciudad Chile")); ?>
          <iframe loading="lazy" src="https://www.google.com/maps?q=<?php echo $map_query; ?>&output=embed" width="100%" height="350" style="border:0; border-radius:16px;" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="Mapa de <?php echo esc_attr($nombre_motel); ?> en <?php echo esc_attr($ciudad); ?>"></iframe>
        </div>
      </section>
    </div>
  </div>

  <section class="ficha-section ficha-card">
    <h2>Preguntas frecuentes</h2>
    <details class="faq-item"><summary>Se puede pagar con tarjeta?</summary><p>Consulta con el establecimiento las opciones de pago disponibles.</p></details>
    <details class="faq-item"><summary>Tiene jacuzzi?</summary><p>Revisa los servicios publicados o confirma directamente con el motel.</p></details>
    <details class="faq-item"><summary>Abre toda la noche?</summary><p>Confirma el horario antes de ir.</p></details>
  </section>
</article>
</div>

<?php get_footer(); ?>
'''

php_code = "<?php\n$tpl = <<<'PHP'\n" + tpl + "\nPHP;\nfile_put_contents(get_theme_root() . '/orbital-child/single-ficha_motel.php', $tpl);\nreturn 'Template rewritten';\n"

r = requests.post(
    'https://infomoteles.cl/wp-json/code-snippets/v1/snippets',
    json={'name': 'rewrite-ficha-template-premium', 'code': php_code, 'scope': 'global', 'active': True},
    auth=auth,
    verify=False,
)
print(r.status_code)
print(r.text[:800])

if r.status_code == 200:
    sid = r.json()['id']
    time.sleep(0.5)
    trigger = requests.get('https://infomoteles.cl/wp-json/?write_template=1&cachebust=1', auth=auth, verify=False)
    print(trigger.status_code)
    print(trigger.text[:200])
    requests.post(f'https://infomoteles.cl/wp-json/code-snippets/v1/snippets/{sid}/deactivate', auth=auth, verify=False)
    requests.delete(f'https://infomoteles.cl/wp-json/code-snippets/v1/snippets/{sid}', auth=auth, verify=False)
