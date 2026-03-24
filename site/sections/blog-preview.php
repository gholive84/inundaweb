<?php
$posts = [];
try {
    $cms_boot = dirname(dirname(__DIR__)) . '/cms/boot.php';
    if (!function_exists('db') && file_exists($cms_boot)) {
        require_once $cms_boot;
    }
    if (function_exists('db')) {
        $rows = db()->query(
            "SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3"
        )->fetchAll(PDO::FETCH_ASSOC);
        $months = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        foreach ($rows as $r) {
            $dt = new DateTime($r['created_at']);
            $posts[] = [
                'slug'           => $r['slug'],
                'category'       => $r['category']      ?? '',
                'category_slug'  => $r['category_slug'] ?? '',
                'title'          => $r['title'],
                'excerpt'        => $r['excerpt']        ?? '',
                'image'          => $r['image_url']      ?? '',
                'date_formatted' => $dt->format('d') . ' ' . $months[(int)$dt->format('m') - 1] . ' ' . $dt->format('Y'),
                'read_time'      => $r['read_time']      ?? '',
                'content'        => $r['content']        ?? '',
            ];
        }
    }
} catch (Exception $e) {}

// Fallback to static file
if (empty($posts)) {
    $posts = array_slice(include dirname(__DIR__) . '/data/blog-posts.php', 0, 3);
}
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
