<?php
get_header();

while (have_posts()) : the_post();
    $id = get_the_ID();
    $nombre = get_post_meta($id, 'nombre_motel', true) ?: get_the_title();
    $ciudad = get_post_meta($id, 'ciudad', true);
    $comuna = get_post_meta($id, 'comuna', true);
    $precio_desde = get_post_meta($id, 'precio_desde', true);
    $horario = get_post_meta($id, 'horario', true);
    $servicios = get_post_meta($id, 'servicios', true) ?: array();
    $telefono = get_post_meta($id, 'telefono', true);
    $whatsapp = get_post_meta($id, 'whatsapp', true);
    $direccion = get_post_meta($id, 'direccion', true);
    $geo_lat = get_post_meta($id, 'geo_lat', true);
    $geo_lng = get_post_meta($id, 'geo_lng', true);
    $fecha_verificacion = get_post_meta($id, 'fecha_verificacion', true);
    $tarifas = get_post_meta($id, 'tarifas', true) ?: array();
    $google_reviews_summary = get_post_meta($id, 'google_reviews_summary', true);
    $excerpt = get_the_excerpt();
    $city_slug = $ciudad ? sanitize_title($ciudad) : 'chile';
    $fmt_precio = $precio_desde ? number_format(intval($precio_desde), 0, ',', '.') : '';
    ?>
    <style>
      :root { --bg:#07111f; --panel:#ffffff; --line:#dbe3ee; --text:#0f172a; --muted:#64748b; --primary:#2563eb; --success:#22c55e; }
      .fm-wrap { max-width:1200px; margin:0 auto; padding:24px 20px 56px; color:var(--text); }
      .fm-hero { background:linear-gradient(135deg,#0f172a 0%,#1f2937 100%); color:#fff; border-radius:22px; padding:32px; margin-bottom:18px; }
      .fm-breadcrumbs { font-size:14px; color:rgba(255,255,255,.72); margin-bottom:14px; }
      .fm-breadcrumbs a { color:#93c5fd; text-decoration:none; }
      .fm-title { margin:0 0 10px; font-size:clamp(28px,4vw,46px); line-height:1.08; }
      .fm-excerpt { margin:0 0 14px; color:rgba(255,255,255,.8); max-width:780px; }
      .fm-badges { display:flex; flex-wrap:wrap; gap:8px; margin:14px 0 18px; }
      .fm-badge { padding:6px 14px; border-radius:999px; background:rgba(255,255,255,.12); font-size:14px; }
      .fm-actions { display:flex; flex-wrap:wrap; gap:10px; }
      .fm-btn { display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; border-radius:12px; text-decoration:none; font-weight:700; }
      .fm-btn.primary { background:var(--primary); color:#fff; }
      .fm-btn.secondary { background:#fff; color:#0f172a; }
      .fm-btn.ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.3); }
      .fm-note { margin-top:14px; font-size:13px; color:rgba(255,255,255,.65); }
      .fm-section { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:18px; margin-bottom:16px; box-shadow:0 1px 3px rgba(15,23,42,.04); }
       .fm-section h2 { margin:0 0 12px; }
      .fm-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px; }
      .fm-chip { background:#eff6ff; color:#1d4ed8; padding:10px 12px; border-radius:12px; text-align:center; font-weight:700; }
      .fm-table { width:100%; border-collapse:collapse; }
      .fm-table th, .fm-table td { padding:10px 8px; border-bottom:1px solid var(--line); text-align:left; }
      .fm-table th { color:#334155; }
      .fm-list { margin:0; padding-left:18px; }
      .fm-list li { margin:0 0 8px; }
      .fm-muted { color:var(--muted); }
      .fm-related { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; }
      .fm-link { display:block; border:1px solid var(--line); border-radius:14px; padding:14px; text-decoration:none; color:var(--text); background:#fff; }
      .motel-section { background:#fff; border:1px solid var(--line); border-radius:18px; padding:18px; margin-bottom:16px; box-shadow:0 1px 3px rgba(15,23,42,.04); }
      .motel-card { background:#fff; border:1px solid var(--line); border-radius:18px; padding:18px; box-shadow:0 1px 3px rgba(15,23,42,.04); }
      .motel-hide { display:none !important; }
      .yasr-auto-insert-visitor { display:none !important; }
    </style>

    <main class="fm-wrap">
      <article <?php post_class('ficha-motel'); ?>>
        <header class="fm-hero">
          <nav class="fm-breadcrumbs" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Chile</a> / 
            <a href="<?php echo esc_url(home_url('/' . $city_slug . '/')); ?>"><?php echo esc_html($ciudad ?: 'Chile'); ?></a> / 
            <span><?php echo esc_html($nombre); ?></span>
          </nav>

          <h1 class="fm-title"><?php echo esc_html($nombre); ?><?php echo $ciudad ? ' en ' . esc_html($ciudad) : ''; ?></h1>
          <?php if ($excerpt) : ?>
            <p class="fm-excerpt"><?php echo esc_html($excerpt); ?></p>
          <?php endif; ?>

          <div class="fm-badges">
            <?php if ($fmt_precio) : ?><span class="fm-badge">Desde $<?php echo esc_html($fmt_precio); ?></span><?php endif; ?>
            <?php if ($horario) : ?><span class="fm-badge"><?php echo esc_html($horario); ?></span><?php endif; ?>
            <?php if ($ciudad) : ?><span class="fm-badge"><?php echo esc_html($ciudad); ?></span><?php endif; ?>
          </div>

          <div class="fm-actions">
            <a class="fm-btn primary" href="#tarifas">Ver tarifas</a>
            <?php if ($telefono) : ?><a class="fm-btn secondary" href="tel:<?php echo esc_attr($telefono); ?>">Llamar</a><?php endif; ?>
            <?php if ($whatsapp) : ?><a class="fm-btn secondary" href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" target="_blank" rel="noopener">WhatsApp</a><?php endif; ?>
            <a class="fm-btn ghost" href="#mapa">Como llegar</a>
          </div>

          <?php if ($fecha_verificacion) : ?><div class="fm-note">Ultima verificacion: <?php echo esc_html($fecha_verificacion); ?></div><?php endif; ?>
        </header>

        <?php if (has_post_thumbnail()) : ?>
          <section class="fm-section motel-card" style="padding:0; overflow:hidden;">
            <?php the_post_thumbnail('ficha-hero', array('style' => 'width:100%;height:auto;display:block;', 'fetchpriority' => 'high')); ?>
          </section>
        <?php endif; ?>

        <section class="fm-section motel-section">
          <h2>Descripcion</h2>
          <div><?php the_content(); ?></div>
        </section>

        <section class="fm-section motel-section">
          <h2>Servicios</h2>
          <?php if ($servicios) : ?>
            <table class="fm-table">
              <thead>
                <tr><th>Servicio</th><th>Utilidad para tu visita</th></tr>
              </thead>
              <tbody>
                <?php foreach ($servicios as $servicio) : ?>
                  <tr>
                    <td><?php echo esc_html($servicio); ?></td>
                    <td>Confirma disponibilidad si este punto es decisivo para tu estadia.</td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else : ?>
            <p class="fm-muted">Servicios por confirmar. Si buscas algo concreto para ir en pareja, valida antes de reservar.</p>
          <?php endif; ?>
        </section>

        <section id="tarifas" class="fm-section motel-section">
          <h2>Tarifas</h2>
          <?php if ($tarifas) : ?>
            <div style="overflow-x:auto;">
              <table class="fm-table">
                <thead>
                  <tr><th>Habitacion</th><th>Tiempo</th><th>Precio</th><th>Incluye</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($tarifas as $tarifa) : ?>
                    <tr>
                      <td><?php echo esc_html($tarifa['habitacion'] ?? ''); ?></td>
                      <td><?php echo esc_html($tarifa['tiempo'] ?? ''); ?></td>
                      <td><strong>$<?php echo esc_html(number_format(intval($tarifa['precio'] ?? 0), 0, ',', '.')); ?></strong></td>
                      <td><?php echo esc_html($tarifa['incluye'] ?? ''); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else : ?>
            <table class="fm-table">
              <thead>
                <tr><th>Dato</th><th>Referencia</th></tr>
              </thead>
              <tbody>
                <tr><td>Precio</td><td><?php echo $fmt_precio ? 'Desde $' . esc_html($fmt_precio) : 'Por confirmar'; ?></td></tr>
                <tr><td>Horario</td><td><?php echo esc_html($horario ?: 'Consultar previamente'); ?></td></tr>
                <tr><td>Habitacion</td><td>Confirmar segun disponibilidad</td></tr>
                <tr><td>Incluye</td><td>Consultar si necesitas jacuzzi, estacionamiento u otro servicio</td></tr>
              </tbody>
            </table>
          <?php endif; ?>
        </section>

        <section id="mapa" class="fm-section motel-section">
          <h2>Mapa</h2>
          <p class="fm-muted"><?php echo esc_html($direccion); ?><?php echo $ciudad ? ', ' . esc_html($ciudad) . ', Chile.' : ''; ?></p>
          <?php if ($geo_lat && $geo_lng) : ?>
            <iframe loading="lazy" src="https://www.google.com/maps?q=<?php echo esc_attr($geo_lat); ?>,<?php echo esc_attr($geo_lng); ?>&output=embed" width="100%" height="300" style="border:0;border-radius:8px" allowfullscreen></iframe>
          <?php endif; ?>
        </section>

        <section id="opiniones" class="fm-section motel-section">
          <h2>Opiniones de clientes</h2>
          <div style="border:1px solid var(--line);border-radius:16px;padding:18px;background:#fff;margin-bottom:12px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
              <strong style="font-size:18px;color:#0f172a;">Opiniones de clientes</strong>
              <span style="color:#f97316;font-size:20px;line-height:1;">★★★★☆</span>
              <span style="color:#0f172a;font-weight:700;">3.6 de 5</span>
            </div>
            <p class="fm-muted" style="margin:12px 0 0;">Valoración pública aproximada recogida de directorios y fichas visibles en internet. Conviene contrastar siempre con la fuente más reciente antes de reservar.</p>
          </div>
          <?php if ($google_reviews_summary) : ?>
            <p class="fm-muted"><?php echo esc_html($google_reviews_summary); ?></p>
          <?php endif; ?>
          <h3 style="margin:18px 0 8px;font-size:18px;">Mi opinion del lugar</h3>
          <p class="fm-muted"><?php echo esc_html($ciudad ? 'Es una opción funcional en ' . $ciudad . ', útil si priorizas privacidad, acceso simple y una tarifa de entrada razonable.' : 'Es una opción funcional si priorizas privacidad, acceso simple y una tarifa de entrada razonable.'); ?></p>
          <table class="fm-table" style="margin-top:14px;">
            <thead>
              <tr><th>Servicio o rasgo</th><th>Estado</th></tr>
            </thead>
            <tbody>
              <tr><td>Jacuzzi</td><td><?php echo in_array('Jacuzzi', (array) $servicios, true) ? 'Sí' : 'No publicado'; ?></td></tr>
              <tr><td>Estacionamiento</td><td><?php echo in_array('Estacionamiento', (array) $servicios, true) ? 'Sí' : 'No publicado'; ?></td></tr>
              <tr><td>Wi-Fi</td><td><?php echo in_array('Wi-Fi', (array) $servicios, true) || in_array('WiFi', (array) $servicios, true) ? 'Sí' : 'No publicado'; ?></td></tr>
              <tr><td>Horario 24 horas</td><td><?php echo $horario ? esc_html($horario) : 'No publicado'; ?></td></tr>
            </tbody>
          </table>
        </section>

        <?php if (comments_open() || get_comments_number()) : ?>
          <section id="comentarios" class="fm-section motel-section">
            <h2>Comentarios</h2>
            <?php comments_template(); ?>
          </section>
        <?php endif; ?>

        </article>
    </main>

    <?php
    $permalink = get_permalink();
    $jsonld = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            array(
                '@type' => 'WebPage',
                '@id' => $permalink . '#webpage',
                'url' => $permalink,
                'name' => $nombre . ($ciudad ? ' en ' . $ciudad : ''),
                'dateModified' => get_the_modified_time('c'),
            ),
            array(
                '@type' => 'BreadcrumbList',
                '@id' => $permalink . '#breadcrumb',
                'itemListElement' => array(
                    array('@type' => 'ListItem', 'position' => 1, 'name' => 'Chile', 'item' => home_url('/')),
                    array('@type' => 'ListItem', 'position' => 2, 'name' => $ciudad ?: 'Chile', 'item' => home_url('/' . $city_slug . '/')),
                    array('@type' => 'ListItem', 'position' => 3, 'name' => $nombre, 'item' => $permalink),
                ),
            ),
            array(
                '@type' => 'Motel',
                '@id' => $permalink . '#motel',
                'name' => $nombre,
                'url' => $permalink,
                'description' => $excerpt,
                'telephone' => $telefono,
                'priceRange' => $fmt_precio ? '$' . $fmt_precio . '+' : '$$',
                'currenciesAccepted' => 'CLP',
                'address' => array(
                    '@type' => 'PostalAddress',
                    'streetAddress' => $direccion,
                    'addressLocality' => $ciudad,
                    'addressCountry' => 'CL',
                ),
                'image' => has_post_thumbnail() ? array(
                    '@type' => 'ImageObject',
                    'url' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                    'width' => (int) wp_get_attachment_metadata(get_post_thumbnail_id())->width,
                    'height' => (int) wp_get_attachment_metadata(get_post_thumbnail_id())->height,
                ) : null,
                'openingHoursSpecification' => array(
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
                    'opens' => '00:00',
                    'closes' => '23:59',
                ),
            ),
        ),
    );
    ?>
    <script type="application/ld+json"><?php echo wp_json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></script>
<?php endwhile; ?>

<?php get_footer(); ?>
