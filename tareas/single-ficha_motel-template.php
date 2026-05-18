<?php
/**
 * Template Name: Ficha de Motel
 * Template Post Type: ficha_motel
 */

$nombre_motel = get_post_meta(get_the_ID(), 'nombre_motel', true);
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

get_header();
?>

<article class="ficha-motel" itemscope itemtype="https://schema.org/Motel">
  <meta itemprop="name" content="<?php echo esc_attr($nombre_motel); ?>">
  <meta itemprop="telephone" content="<?php echo esc_attr($telefono); ?>">

  <header class="hero" style="background:#0f172a;color:#fff;padding:40px 20px;text-align:center">
    <nav style="font-size:14px;margin-bottom:16px;color:#94a3b8">
      <a href="/" style="color:#60a5fa">Chile</a> &gt;
      <a href="/fichas/<?php echo sanitize_title($ciudad); ?>/" style="color:#60a5fa"><?php echo esc_html($ciudad); ?></a> &gt;
      <span style="color:#fff"><?php echo esc_html($nombre_motel); ?></span>
    </nav>

    <h1 style="font-size:32px;margin:0 0 8px"><?php echo esc_html($nombre_motel); ?> en <?php echo esc_html($ciudad); ?></h1>
    <p style="color:#94a3b8;margin:0 0 20px">Motel en <?php echo esc_html($ciudad); ?>. Informacion verificada y tarifas orientativas actualizadas.</p>

    <div class="badges" style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:20px">
      <span style="background:#2563eb;padding:6px 14px;border-radius:20px;font-size:14px;font-weight:600">Desde $<?php echo number_format(intval($precio_desde), 0, ',', '.'); ?></span>
      <span style="background:#1e293b;padding:6px 14px;border-radius:20px;font-size:14px"><?php echo esc_html($horario); ?></span>
      <?php foreach (array_slice($servicios, 0, 3) as $sv): ?>
        <span style="background:#1e293b;padding:6px 14px;border-radius:20px;font-size:14px"><?php echo esc_html($sv); ?></span>
      <?php endforeach; ?>
    </div>

    <div class="ctas" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:16px">
      <a href="#tarifas" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">Ver tarifas</a>
      <?php if ($telefono): ?>
        <a href="tel:<?php echo esc_attr($telefono); ?>" style="background:#22c55e;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">Llamar</a>
      <?php endif; ?>
      <?php if ($whatsapp): ?>
        <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" style="background:#25D366;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">WhatsApp</a>
      <?php endif; ?>
      <a href="#mapa" style="background:#64748b;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600">Como llegar</a>
    </div>

    <?php if ($fecha_verificacion): ?>
      <p style="font-size:13px;color:#64748b;margin:0">Ultima verificacion: <?php echo esc_html($fecha_verificacion); ?></p>
    <?php endif; ?>
  </header>

  <div style="width:100%;max-width:none;margin:0 auto;padding:20px">
    <?php if (has_post_thumbnail()): ?>
      <section id="galeria" style="margin-bottom:32px">
        <?php the_post_thumbnail('ficha-hero', array('style' => 'width:100%;height:400px;object-fit:cover;border-radius:12px', 'fetchpriority' => 'high')); ?>
      </section>
    <?php endif; ?>

    <section id="descripcion" style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Descripcion</h2>
      <div style="color:#475569;line-height:1.7"><?php the_content(); ?></div>
    </section>

    <section id="servicios" style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Servicios</h2>
      <?php if ($servicios): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px">
          <?php foreach ($servicios as $sv): ?>
            <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;font-size:14px;color:#334155;text-align:center"><?php echo esc_html($sv); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section id="tarifas" style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Tarifas</h2>
      <?php if ($tarifas): ?>
        <div style="overflow-x:auto">
          <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1)">
            <thead>
              <tr style="background:#0f172a;color:#fff">
                <th style="padding:12px 16px;text-align:left">Habitacion</th>
                <th style="padding:12px 16px;text-align:left">Tiempo</th>
                <th style="padding:12px 16px;text-align:right">Precio</th>
                <th style="padding:12px 16px;text-align:left">Incluye</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tarifas as $t): ?>
                <tr style="border-bottom:1px solid #e2e8f0">
                  <td style="padding:12px 16px;font-weight:600"><?php echo esc_html($t['habitacion']); ?></td>
                  <td style="padding:12px 16px;color:#64748b"><?php echo esc_html($t['tiempo']); ?></td>
                  <td style="padding:12px 16px;text-align:right;font-weight:700;color:#2563eb">$<?php echo number_format(intval($t['precio']), 0, ',', '.'); ?></td>
                  <td style="padding:12px 16px;color:#475569"><?php echo esc_html($t['incluye']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="color:#64748b;margin:0">Tarifas por confirmar. Contacta directamente por telefono o WhatsApp para validar disponibilidad y precio actualizado.</p>
      <?php endif; ?>
    </section>

    <section id="mapa" style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Ubicacion</h2>
      <p style="color:#475569;margin:0 0 12px"><?php echo esc_html($direccion); ?>, <?php echo esc_html($ciudad); ?>, Chile.</p>
      <?php if ($geo_lat && $geo_lng): ?>
        <iframe loading="lazy" src="https://www.google.com/maps?q=<?php echo $geo_lat; ?>,<?php echo $geo_lng; ?>&output=embed" width="100%" height="300" style="border:0;border-radius:8px" allowfullscreen></iframe>
      <?php endif; ?>
    </section>

    <section id="resenas" style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Resenas</h2>
      <p style="color:#64748b">Resenas verificadas de usuarios de InfoMoteles.cl.</p>
    </section>

    <section id="faq" style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Preguntas frecuentes</h2>
      <div style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
        <details style="border-bottom:1px solid #e2e8f0;padding:16px">
          <summary style="font-weight:600;cursor:pointer;color:#0f172a">¿Como puedo reservar?</summary>
          <p style="margin:8px 0 0;color:#475569">Contacta directamente al motel via telefono o WhatsApp. Sujeto a disponibilidad y confirmacion con el establecimiento.</p>
        </details>
        <details style="border-bottom:1px solid #e2e8f0;padding:16px">
          <summary style="font-weight:600;cursor:pointer;color:#0f172a">¿Cual es el horario?</summary>
          <p style="margin:8px 0 0;color:#475569"><?php echo esc_html($horario); ?></p>
        </details>
        <details style="padding:16px">
          <summary style="font-weight:600;cursor:pointer;color:#0f172a">¿Que metodos de pago aceptan?</summary>
          <p style="margin:8px 0 0;color:#475569">Sujeto a confirmacion con el establecimiento. Consulta directamente via telefono o WhatsApp.</p>
        </details>
      </div>
    </section>

    <aside style="margin-bottom:32px">
      <h2 style="font-size:24px;margin:0 0 12px;color:#0f172a">Moteles similares en <?php echo esc_html($ciudad); ?></h2>
      <?php echo do_shortcode('[fichas_motel ciudad="' . sanitize_title($ciudad) . '" limite="4"]'); ?>
    </aside>
  </div>
</article>

<?php
get_footer();
