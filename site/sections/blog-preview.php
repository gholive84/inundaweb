<?php
$posts = include dirname(__DIR__) . '/data/blog-posts.php';
$posts = array_slice($posts, 0, 3);
?>
<section class="blog-preview" id="blog">
    <div class="container">

        <div class="section-header">
            <span class="label">Blog</span>
            <h2 class="section-title text--white">Conteúdo que <span class="text--gradient">transforma</span></h2>
            <p class="section-subtitle text--muted-dark">Dicas, estratégias e cases sobre WordPress, performance e tecnologia.</p>
        </div>

        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
            <a href="/blog/?post=<?php echo htmlspecialchars($post['slug']); ?>" class="blog-card">

                <div class="blog-card__image">
                    <img
                        src="<?php echo htmlspecialchars($post['image']); ?>"
                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                        loading="lazy"
                        width="800"
                        height="450">
                </div>

                <div class="blog-card__body">
                    <div class="blog-card__meta">
                        <span class="blog-card__category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <span class="blog-card__sep" aria-hidden="true">·</span>
                        <span class="blog-card__date"><?php echo htmlspecialchars($post['date_formatted']); ?></span>
                    </div>
                    <h3 class="blog-card__title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="blog-card__excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <span class="blog-card__read-more">
                        Ler artigo
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </div>

            </a>
            <?php endforeach; ?>
        </div>

        <div class="blog-preview__cta">
            <a href="/blog/" class="btn btn--ghost">Ver todos os posts</a>
        </div>

    </div>
</section>
