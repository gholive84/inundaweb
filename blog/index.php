<?php
$root = dirname(__DIR__);

// Load all posts
$all_posts = include $root . '/site/data/blog-posts.php';

// ── Determine view mode ───────────────────────────────────────────────────────
$post_slug = isset($_GET['post']) ? trim($_GET['post']) : '';
$cat_slug  = isset($_GET['cat'])  ? trim($_GET['cat'])  : '';

// ── Single post view ──────────────────────────────────────────────────────────
if ($post_slug !== '') {
    // Find matching post
    $post = null;
    foreach ($all_posts as $p) {
        if ($p['slug'] === $post_slug) {
            $post = $p;
            break;
        }
    }

    // 404-style fallback — redirect to listing if slug not found
    if ($post === null) {
        header('Location: /blog/');
        exit;
    }

    $page_title       = htmlspecialchars($post['title']) . ' — Blog Inunda';
    $page_description = htmlspecialchars(strip_tags($post['excerpt']));

    include $root . '/site/includes/head-page.php';
    include $root . '/site/includes/header.php';
?>

<!-- ── POST HERO ──────────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--blog-post" style="
    background:
        linear-gradient(to bottom, rgba(15,23,42,0.72) 0%, rgba(15,23,42,0.92) 100%),
        url('<?php echo htmlspecialchars($post['image']); ?>') center / cover no-repeat;
">
    <div class="container page-hero__content">
        <a href="/blog/" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Voltar ao Blog
        </a>
        <span class="label"><?php echo htmlspecialchars($post['category']); ?></span>
        <h1 class="page-hero__title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="blog-post__hero-meta">
            <span><?php echo htmlspecialchars($post['date_formatted']); ?></span>
            <span class="blog-post__hero-sep" aria-hidden="true">·</span>
            <span><?php echo htmlspecialchars($post['read_time']); ?> de leitura</span>
        </div>
    </div>
</section>

<!-- ── POST CONTENT ───────────────────────────────────────────────────────── -->
<section class="blog-post" style="background: var(--color-dark); padding: 80px 0;">
    <div class="container">
        <div class="blog-post__content">
            <?php echo $post['content']; ?>
        </div>
        <div class="blog-post__back">
            <a href="/blog/" class="btn btn--ghost">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Voltar ao Blog
            </a>
        </div>
    </div>
</section>

<?php
    include $root . '/site/includes/footer.php';
    exit;
} // end single post view


// ── LISTING VIEW ─────────────────────────────────────────────────────────────

// Build unique category list from posts (preserves insertion order)
$categories = [];
foreach ($all_posts as $p) {
    if (!isset($categories[$p['category_slug']])) {
        $categories[$p['category_slug']] = $p['category'];
    }
}

// Filter posts by category if param present
$filtered_posts = $all_posts;
if ($cat_slug !== '' && isset($categories[$cat_slug])) {
    $filtered_posts = array_filter($all_posts, function ($p) use ($cat_slug) {
        return $p['category_slug'] === $cat_slug;
    });
}

$active_cat_label = $cat_slug !== '' && isset($categories[$cat_slug])
    ? $categories[$cat_slug]
    : 'Todos';

$page_title       = 'Blog — Inunda';
$page_description = 'Dicas, estratégias e cases sobre WordPress, performance, segurança e tecnologia para o seu negócio.';

include $root . '/site/includes/head-page.php';
include $root . '/site/includes/header.php';
?>

<!-- ── LISTING HERO ───────────────────────────────────────────────────────── -->
<section class="page-hero" style="min-height: 40vh;">
    <div class="page-hero__bg"></div>
    <div class="container page-hero__content" style="padding-top: 48px; padding-bottom: 64px;">
        <a href="/" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Voltar para o início
        </a>
        <span class="label">Blog</span>
        <h1 class="page-hero__title">
            Blog <span class="text--gradient">Inunda</span>
        </h1>
        <p class="page-hero__subtitle" style="max-width: 520px;">
            Conteúdo especializado em WordPress, performance, segurança e tecnologia para empreendedores e gestores digitais.
        </p>
    </div>
</section>

<!-- ── CATEGORY FILTER ────────────────────────────────────────────────────── -->
<section class="blog-filter" style="background: var(--color-dark-2); padding: 32px 0; border-bottom: 1px solid var(--color-border-dark);">
    <div class="container">
        <nav class="blog-filter__nav" aria-label="Filtrar por categoria">
            <a
                href="/blog/"
                class="blog-filter__btn<?php echo $cat_slug === '' ? ' blog-filter__btn--active' : ''; ?>"
            >Todos</a>
            <?php foreach ($categories as $slug => $label): ?>
            <a
                href="/blog/?cat=<?php echo urlencode($slug); ?>"
                class="blog-filter__btn<?php echo $cat_slug === $slug ? ' blog-filter__btn--active' : ''; ?>"
            ><?php echo htmlspecialchars($label); ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<!-- ── POSTS GRID ─────────────────────────────────────────────────────────── -->
<section class="blog-listing" style="background: var(--color-dark); padding: 80px 0;">
    <div class="container">

        <?php if (empty($filtered_posts)): ?>
        <p style="color: var(--color-muted); text-align: center; padding: 48px 0;">
            Nenhum artigo encontrado nesta categoria.
        </p>
        <?php else: ?>

        <div class="blog-grid blog-grid--full">
            <?php foreach ($filtered_posts as $post): ?>
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
                        <span class="blog-card__sep" aria-hidden="true">·</span>
                        <span class="blog-card__read-time"><?php echo htmlspecialchars($post['read_time']); ?></span>
                    </div>
                    <h2 class="blog-card__title"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p class="blog-card__excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <span class="blog-card__read-more">
                        Ler artigo
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </div>

            </a>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

    </div>
</section>

<?php include $root . '/site/includes/footer.php'; ?>
