<?php get_header(); ?>
<style>
  :root { --bg:#07111f; --panel:#ffffff; --line:#dbe3ee; --text:#0f172a; --muted:#64748b; --primary:#2563eb; }
  .sp-wrap { max-width:980px; margin:0 auto; padding:24px 20px 56px; color:var(--text); }
  .sp-hero { background:linear-gradient(135deg,#0f172a 0%,#1f2937 100%); color:#fff; border-radius:22px; padding:32px; margin-bottom:18px; }
  .sp-hero h1 { margin:0 0 10px; font-size:clamp(28px,4vw,44px); line-height:1.08; }
  .sp-hero p { margin:0 0 12px; color:rgba(255,255,255,.82); }
  .sp-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:18px; }
  .sp-btn { display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; border-radius:12px; text-decoration:none; font-weight:700; }
  .sp-btn.primary { background:#22c55e; color:#fff; }
  .sp-btn.secondary { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.25); }
  .sp-section { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:18px; margin-bottom:16px; box-shadow:0 1px 3px rgba(15,23,42,.04); }
  .sp-section h2 { margin:0 0 12px; font-size:20px; }
  .sp-note { background:#09111d; border-left:4px solid var(--primary); padding:12px 14px; border-radius:10px; color:#dbeafe; }
  .sp-list { margin:0; padding-left:18px; }
  .sp-list li { margin:0 0 8px; }
  .sp-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; }
  .sp-link { display:block; border:1px solid var(--line); border-radius:14px; padding:14px; color:var(--text); text-decoration:none; background:#fff; }
  .sp-muted { color:var(--muted); }
</style>

<main class="sp-wrap">
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class(); ?>>
      <header class="sp-hero">
        <p style="margin:0 0 10px;color:rgba(255,255,255,.65)"><?php echo esc_html(get_the_date()); ?> | <?php echo esc_html(get_the_modified_date()); ?></p>
        <h1><?php the_title(); ?></h1>
        <?php if (has_excerpt()) : ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
        <div class="sp-actions">
          <a class="sp-btn primary" href="<?php echo esc_url(home_url('/para-moteles/')); ?>">Publicar mi motel</a>
          <a class="sp-btn secondary" href="<?php echo esc_url(home_url('/')); ?>">Volver al inicio</a>
        </div>
      </header>

      <section class="sp-section">
        <div class="sp-note">Si este post ya recibe impresiones, se optimiza antes de escribir otro nuevo.</div>
        <div style="margin-top:14px; line-height:1.7;"><?php the_content(); ?></div>
      </section>

      <section class="sp-section">
        <h2>Enlaces que empujan traccion</h2>
        <div class="sp-grid">
          <a class="sp-link" href="<?php echo esc_url(home_url('/')); ?>">Home<span class="sp-muted">Entrada comercial</span></a>
          <a class="sp-link" href="<?php echo esc_url(home_url('/para-moteles/')); ?>">/para-moteles/<span class="sp-muted">Ruta B2B</span></a>
          <a class="sp-link" href="<?php echo esc_url(home_url('/santiago/')); ?>">Santiago<span class="sp-muted">Hub principal</span></a>
          <a class="sp-link" href="<?php echo esc_url(home_url('/antofagasta/')); ?>">Antofagasta<span class="sp-muted">Hub local</span></a>
          <a class="sp-link" href="<?php echo esc_url(home_url('/copiapo/')); ?>">Copiapo<span class="sp-muted">Fichas con señales</span></a>
          <a class="sp-link" href="<?php echo esc_url(home_url('/calama/')); ?>">Calama<span class="sp-muted">Fichas con señales</span></a>
        </div>
      </section>

      <section class="sp-section">
        <h2>Prioridades con evidencia</h2>
        <ul class="sp-list">
          <li><a href="<?php echo esc_url(home_url('/copiapo/motel-los-sauces')); ?>">/copiapo/motel-los-sauces</a> - impresiones altas, CTR bajo.</li>
          <li><a href="<?php echo esc_url(home_url('/la-florida/motel-la-giralda')); ?>">/la-florida/motel-la-giralda</a> - CTR muy bajo.</li>
          <li><a href="<?php echo esc_url(home_url('/motel-diamante-curico/')); ?>">/motel-diamante-curico/</a> - foco ganador.</li>
        </ul>
      </section>
    </article>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>
