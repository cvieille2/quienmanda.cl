<?php get_header(); ?>
<style>
  :root { --bg:#07111f; --panel:#10192a; --line:#24344d; --text:#e5e7eb; --muted:#94a3b8; --accent:#60a5fa; --green:#22c55e; }
  .pm-wrap { max-width:1120px; margin:0 auto; padding:28px 20px 56px; color:var(--text); }
  .pm-hero { background:linear-gradient(135deg,#10192a 0%,#0c1321 60%,#1f2937 100%); border:1px solid var(--line); border-radius:24px; padding:32px; margin-bottom:18px; }
  .pm-hero h1 { margin:0 0 10px; font-size:clamp(30px,4vw,48px); line-height:1.08; }
  .pm-hero p { margin:0 0 12px; color:#dbe3f0; max-width:760px; }
  .pm-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:18px; }
  .pm-btn { display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; border-radius:999px; text-decoration:none; font-weight:700; border:1px solid transparent; }
  .pm-btn.primary { background:var(--green); color:#fff; }
  .pm-btn.secondary { background:transparent; color:#fff; border-color:#37506f; }
  .pm-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px; margin:16px 0 22px; }
  .pm-card { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:16px; }
  .pm-section { background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:18px; margin-bottom:16px; }
  .pm-section.soft { background:#0d1524; }
  .pm-section h2 { margin:0 0 12px; font-size:20px; }
  .pm-list { margin:0; padding-left:18px; }
  .pm-list li { margin:0 0 8px; }
  .pm-note { background:#09111d; border-left:4px solid var(--accent); padding:12px 14px; border-radius:10px; }
  .pm-contact { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
  .pm-contact a, .pm-contact span { display:block; border:1px solid #22324a; border-radius:14px; padding:14px; background:#0b1220; color:#dbeafe; text-decoration:none; }
</style>

<main class="pm-wrap">
  <section class="pm-hero">
    <p style="color:var(--muted);margin:0 0 10px;">Ruta comercial B2B | InfoMoteles</p>
    <h1>Publica tu motel donde ya hay demanda, no donde falta traccion.</h1>
    <p>La oferta prioriza fichas vendibles, setup cobrado y SEO local aplicado sobre URLs con señales reales. Si no hay demanda, no se inventa volumen.</p>
    <div class="pm-actions">
      <a class="pm-btn primary" href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>?subject=<?php echo rawurlencode('Quiero aparecer en InfoMoteles'); ?>">Pedir diagnóstico</a>
      <a class="pm-btn secondary" href="#proceso">Ver proceso</a>
      <a class="pm-btn secondary" href="#prioridades">Ver prioridades</a>
    </div>
  </section>

  <section class="pm-grid">
    <div class="pm-card"><strong>1</strong><br>Ficha destacada con intención comercial.</div>
    <div class="pm-card"><strong>2</strong><br>Setup cobrado en la primera venta.</div>
    <div class="pm-card"><strong>3</strong><br>SEO local solo donde ya exista tracción.</div>
    <div class="pm-card"><strong>4</strong><br>Seguimiento y medición desde el inicio.</div>
  </section>

  <section class="pm-section">
    <h2>Lo que se resuelve</h2>
    <ul class="pm-list">
      <li>Visibilidad en búsquedas locales y de marca.</li>
      <li>CTR y presencia en la SERP con una ficha clara.</li>
      <li>Ruta para captar contactos con menor fricción.</li>
      <li>Orden de URLs para evitar canibalización y duplicados.</li>
    </ul>
  </section>

  <section id="proceso" class="pm-section soft">
    <h2>Proceso</h2>
    <ul class="pm-list">
      <li>Auditar la ficha o la URL existente.</li>
      <li>Reescribir title, meta, hero y CTA.</li>
      <li>Conectar con la arquitectura correcta.</li>
      <li>Publicar, medir y ajustar.</li>
    </ul>
  </section>

  <section id="prioridades" class="pm-section">
    <h2>Prioridades con evidencia</h2>
    <div class="pm-note">Primero se rescatan URLs con impresiones y CTR bajo. Después se escala el resto.</div>
    <ul class="pm-list" style="margin-top:12px;">
      <li><code>/copiapo/motel-los-sauces</code> - muchas impresiones, CTR bajo.</li>
      <li><code>/la-florida/motel-la-giralda</code> - CTR muy bajo.</li>
      <li><code>/motel-diamante-curico/</code> - mejor URL por clics.</li>
      <li><code>/antofagasta/motel-serrano-antofagasta/</code> - foco ganador.</li>
      <li><code>/calama/motel-euro-calama/</code> - foco ganador.</li>
    </ul>
  </section>

  <section class="pm-section soft">
    <h2>Contacto</h2>
    <div class="pm-contact">
      <a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>">Escribir por email<span><?php echo esc_html(get_option('admin_email')); ?></span></a>
      <a href="<?php echo esc_url(home_url('/')); ?>">Volver al sitio<span>Revisar fichas y ciudades</span></a>
      <span>Si quieres WhatsApp directo, configura el número en los metadatos del sitio.</span>
    </div>
  </section>
</main>

<?php get_footer(); ?>
