<?php get_header(); ?>
<style>
  :root { --bg:#08101d; --panel:#10192a; --panel-2:#0d1524; --line:#24344d; --text:#e5e7eb; --muted:#94a3b8; --accent:#60a5fa; --green:#22c55e; --yellow:#f59e0b; }
  .im-wrap { max-width:1280px; margin:0 auto; padding:28px 20px 56px; color:var(--text); }
  .im-hero { background:linear-gradient(135deg,#10192a 0%,#0c1321 60%,#1e293b 100%); border:1px solid var(--line); border-radius:24px; padding:32px; margin-bottom:18px; }
  .im-hero h1 { margin:0 0 10px; font-size:clamp(30px,4vw,54px); line-height:1.05; }
  .im-hero p { margin:0 0 12px; color:#dbe3f0; max-width:760px; }
  .im-meta { color:var(--muted); font-size:14px; }
  .im-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:18px; }
  .im-btn { display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; border-radius:999px; text-decoration:none; font-weight:700; border:1px solid transparent; }
  .im-btn.primary { background:var(--green); color:#fff; }
  .im-btn.secondary { background:transparent; color:#fff; border-color:#37506f; }
  .im-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; margin:16px 0 22px; }
  .im-card { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:16px; }
  .im-kpi { font-size:28px; font-weight:700; margin:0; }
  .im-label { color:var(--muted); font-size:13px; margin-top:4px; }
  .im-section { background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:18px; margin-bottom:16px; }
  .im-section.soft { background:var(--panel-2); }
  .im-section h2 { margin:0 0 12px; font-size:20px; }
  .im-section h3 { margin:0 0 8px; font-size:16px; }
  .im-grid-links { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; }
  .im-link { display:block; padding:14px 14px; border-radius:14px; background:#0b1220; border:1px solid #22324a; color:#dbeafe; text-decoration:none; font-weight:700; }
  .im-link span { display:block; color:var(--muted); font-weight:400; font-size:13px; margin-top:4px; }
  .im-note { background:#09111d; border-left:4px solid var(--accent); padding:12px 14px; border-radius:10px; }
  .im-list { margin:0; padding-left:18px; }
  .im-list li { margin:0 0 8px; }
</style>

<main class="im-wrap">
  <section class="im-hero">
    <p class="im-meta">InfoMoteles.cl | SEO con foco en traccion, no en volumen por inercia</p>
    <h1>Rescata lo que ya trae busquedas y convierte la home en entrada comercial.</h1>
    <p>La prioridad es clara: fichas con señales reales, landings por ciudad, posts viejos con demanda recuperable y una ruta para moteles visible.</p>
    <div class="im-actions">
      <a class="im-btn primary" href="<?php echo esc_url(home_url('/para-moteles/')); ?>">Publicar mi motel</a>
      <a class="im-btn secondary" href="#ciudades">Ver por ciudad</a>
      <a class="im-btn secondary" href="#rescate">Posts a rescatar</a>
    </div>
  </section>

  <section class="im-grid">
    <div class="im-card"><p class="im-kpi">3</p><div class="im-label">fichas foco a potenciar</div></div>
    <div class="im-card"><p class="im-kpi">2</p><div class="im-label">URLs con CTR bajo a rescatar</div></div>
    <div class="im-card"><p class="im-kpi">1</p><div class="im-label">gap critico: /para-moteles/</div></div>
    <div class="im-card"><p class="im-kpi">0</p><div class="im-label">contenido nuevo por inercia</div></div>
  </section>

  <section class="im-section">
    <h2>Ciudades prioritarias</h2>
    <div id="ciudades" class="im-grid-links">
      <a class="im-link" href="<?php echo esc_url(home_url('/santiago/')); ?>">Santiago<span>Hub principal</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/antofagasta/')); ?>">Antofagasta<span>Hub de alta intención</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/copiapo/')); ?>">Copiapo<span>Fichas con tracción</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/calama/')); ?>">Calama<span>Consulta comercial fuerte</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/la-florida/')); ?>">La Florida<span>Rescate CTR</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/concepcion/')); ?>">Concepcion<span>Cluster local amplio</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/valparaiso/')); ?>">Valparaiso<span>Demanda regional</span></a>
      <a class="im-link" href="<?php echo esc_url(home_url('/vina-del-mar/')); ?>">Vina del Mar<span>Apoyo local</span></a>
    </div>
  </section>

  <section class="im-section soft">
    <h2>Fichas con evidencia</h2>
    <div class="im-note">Primero se potencia lo que ya recibe impresiones. Despues se escala el siguiente lote.</div>
    <ul class="im-list" style="margin-top:12px;">
      <li><a href="<?php echo esc_url(home_url('/copiapo/motel-los-sauces')); ?>">/copiapo/motel-los-sauces</a> - muchas impresiones, CTR bajo.</li>
      <li><a href="<?php echo esc_url(home_url('/la-florida/motel-la-giralda')); ?>">/la-florida/motel-la-giralda</a> - CTR muy bajo.</li>
      <li><a href="<?php echo esc_url(home_url('/motel-diamante-curico/')); ?>">/motel-diamante-curico/</a> - mejor URL por clics.</li>
      <li><a href="<?php echo esc_url(home_url('/antofagasta/motel-serrano-antofagasta/')); ?>">/antofagasta/motel-serrano-antofagasta/</a> - foco ganador.</li>
      <li><a href="<?php echo esc_url(home_url('/calama/motel-euro-calama/')); ?>">/calama/motel-euro-calama/</a> - foco ganador.</li>
    </ul>
  </section>

  <section id="rescate" class="im-section">
    <h2>Posts recuperables</h2>
    <p class="im-note">No se crea contenido nuevo hasta rescatar lo que ya puede ganar tráfico.</p>
    <ul class="im-list" style="margin-top:12px;">
      <li><a href="<?php echo esc_url(home_url('/blog/guia-de-moteles-en-santiago/')); ?>">Guia de moteles en Santiago</a> - hub local obvio.</li>
      <li><a href="<?php echo esc_url(home_url('/blog/los-mejores-moteles-en-ruta-68-santiago-confort-y-privacidad-en-tu-viaje/')); ?>">Moteles en Ruta 68</a> - intención local fuerte.</li>
      <li><a href="<?php echo esc_url(home_url('/blog/encontrar-motel-en-quilicura-panamericana/')); ?>">Motel en Quilicura Panamericana</a> - geo intent claro.</li>
      <li><a href="<?php echo esc_url(home_url('/blog/encontrar-motel-en-eyzaguirre-puente-alto/')); ?>">Motel en Eyzaguirre, Puente Alto</a> - geo intent accionable.</li>
      <li><a href="<?php echo esc_url(home_url('/blog/encontrar-motel-por-3-horas-santiago-centro/')); ?>">Motel por 3 horas en Santiago centro</a> - transaccional.</li>
    </ul>
  </section>

  <section class="im-section soft">
    <h2>Regla operativa</h2>
    <ul class="im-list">
      <li>Si una URL tiene impresiones, se optimiza.</li>
      <li>Si una URL no tiene demanda, no se publica por inercia.</li>
      <li>Si hay solape, una sola URL por intencion.</li>
      <li>Si un post ayuda, se usa para empujar la pagina pilar o la ficha dificil.</li>
    </ul>
  </section>

  <section class="im-section">
    <h2>Ruta inmediata</h2>
    <?php echo do_shortcode('[fichas_motel limite="6"]'); ?>
  </section>
</main>

<?php get_footer(); ?>
