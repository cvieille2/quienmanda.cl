<?php get_header(); ?>
<?php
while (have_posts()) : the_post();
    $slug = get_post_field('post_name', get_the_ID());
    $category = get_category_by_slug($slug);
    $is_cluster = $category && !is_wp_error($category);
    $term_id = $is_cluster ? $category->term_id : 0;
    $posts = array();

    if ($is_cluster) {
        $cluster_query = new WP_Query(array(
            'cat' => $term_id,
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        if ($cluster_query->have_posts()) {
            $posts = $cluster_query->posts;
        }
        wp_reset_postdata();
    }
    ?>
    <style>
      :root { --bg:#07111f; --panel:#ffffff; --panel-soft:#0d1524; --line:#dbe3ee; --text:#0f172a; --muted:#64748b; --primary:#2563eb; --green:#22c55e; }
      .pg-wrap { max-width:1200px; margin:0 auto; padding:24px 20px 56px; color:var(--text); }
      .pg-hero { background:linear-gradient(135deg,#0f172a 0%,#1f2937 100%); color:#fff; border-radius:22px; padding:32px; margin-bottom:18px; }
      .pg-hero h1 { margin:0 0 10px; font-size:clamp(30px,4vw,46px); line-height:1.08; }
      .pg-hero p { margin:0 0 12px; color:rgba(255,255,255,.82); max-width:780px; }
      .pg-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:18px; }
      .pg-btn { display:inline-flex; align-items:center; justify-content:center; padding:12px 18px; border-radius:12px; text-decoration:none; font-weight:700; }
      .pg-btn.primary { background:var(--green); color:#fff; }
      .pg-btn.secondary { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.25); }
      .pg-section { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:18px; margin-bottom:16px; box-shadow:0 1px 3px rgba(15,23,42,.04); }
      .pg-section.soft { background:var(--panel-soft); color:#e5e7eb; border-color:#23324a; }
      .pg-section h2 { margin:0 0 12px; font-size:20px; }
      .pg-note { background:#09111d; border-left:4px solid var(--primary); padding:12px 14px; border-radius:10px; color:#dbeafe; }
      .pg-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px; }
      .pg-card { border:1px solid var(--line); border-radius:16px; padding:16px; background:#fff; }
      .pg-card h3 { margin:0 0 8px; font-size:18px; }
      .pg-card p { margin:0 0 10px; color:var(--muted); }
      .pg-card a { color:var(--primary); font-weight:700; text-decoration:none; }
      .pg-list { margin:0; padding-left:18px; }
      .pg-list li { margin:0 0 8px; }
      .pg-cluster { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px; }
      .pg-cluster a { display:block; border:1px solid #22324a; border-radius:14px; padding:14px; background:#fff; color:var(--text); text-decoration:none; }
      .pg-cluster a span { display:block; color:var(--muted); font-size:13px; margin-top:4px; }
      .pg-cluster-empty { color:#cbd5e1; }
    </style>

    <main class="pg-wrap">
      <section class="pg-hero">
        <?php if ($is_cluster) : ?>
          <p style="margin:0 0 10px;color:rgba(255,255,255,.65)">Pilar de categoria | <?php echo esc_html($category->name); ?></p>
          <h1><?php echo esc_html($category->name); ?>: cluster de moteles y posts utiles</h1>
          <p>Esta pagina pilar muestra el cluster de la categoria <?php echo esc_html($category->name); ?> y empuja a las URLs que ya tienen traccion o intencion comercial.</p>
        <?php else : ?>
          <p style="margin:0 0 10px;color:rgba(255,255,255,.65)">Pagina pilar</p>
          <h1><?php the_title(); ?></h1>
          <?php if (has_excerpt()) : ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
        <?php endif; ?>

        <div class="pg-actions">
          <a class="pg-btn primary" href="<?php echo esc_url(home_url('/para-moteles/')); ?>">Publicar mi motel</a>
          <a class="pg-btn secondary" href="#cluster">Ver cluster</a>
        </div>
      </section>

      <?php if ($is_cluster) : ?>
        <section class="pg-section">
          <h2>URLs del cluster</h2>
          <div id="cluster" class="pg-cluster">
            <?php if (!empty($posts)) : ?>
              <?php foreach ($posts as $cluster_post) : ?>
                <?php
                  $post_id = $cluster_post->ID;
                  $post_link = get_permalink($post_id);
                  $post_title = get_the_title($post_id);
                  $post_excerpt = get_the_excerpt($post_id);
                ?>
                <a href="<?php echo esc_url($post_link); ?>">
                  <strong><?php echo esc_html($post_title); ?></strong>
                  <span><?php echo esc_html(wp_trim_words($post_excerpt ?: wp_strip_all_tags(get_post_field('post_content', $post_id)), 18, '...')); ?></span>
                </a>
              <?php endforeach; ?>
            <?php else : ?>
              <div class="pg-cluster-empty">No hay posts publicados en esta categoria todavia.</div>
            <?php endif; ?>
          </div>
        </section>

        <section class="pg-section soft">
          <h2>Regla del cluster</h2>
          <ul class="pg-list">
            <li>Una sola categoria por pagina pilar.</li>
            <li>Los posts dentro del cluster deben empujar a la pagina pilar y a las fichas con traccion.</li>
            <li>Si una URL no aporta, se fusiona o se corta.</li>
          </ul>
        </section>

        <section class="pg-section">
          <h2>Descripcion de la categoria</h2>
          <div><?php echo wpautop(wp_kses_post(term_description($term_id))); ?></div>
        </section>
      <?php else : ?>
        <section class="pg-section">
          <div><?php the_content(); ?></div>
        </section>
      <?php endif; ?>
    </main>
<?php endwhile; ?>

<?php get_footer(); ?>
