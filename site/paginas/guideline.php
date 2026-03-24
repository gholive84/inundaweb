<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guideline & Style Guide — Inunda IA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --color-primary:      #22d3ee;
            --color-primary-dark: #06b6d4;
            --color-dark:         #0f172a;
            --color-dark-2:       #1e293b;
            --color-light:        #f8fafc;
            --color-white:        #ffffff;
            --color-text:         #0f172a;
            --color-muted:        #64748b;
            --color-border:       #e2e8f0;
            --color-border-dark:  rgba(255,255,255,0.08);
            --gradient-accent:    linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%);
            --font-base:          'Inter', -apple-system, sans-serif;
            --font-mono:          'JetBrains Mono', monospace;
            --radius-sm:  8px;
            --radius-md:  12px;
            --radius-lg:  16px;
            --shadow-card: 0 4px 24px rgba(0,0,0,0.06);
        }

        body {
            font-family: var(--font-base);
            background: #080f1c;
            color: #e2e8f0;
            line-height: 1.6;
        }

        /* ── LAYOUT ── */
        .gl-wrap { display: flex; min-height: 100vh; }

        /* ── SIDEBAR ── */
        .gl-sidebar {
            width: 240px;
            flex-shrink: 0;
            background: #0a1120;
            border-right: 1px solid rgba(255,255,255,0.06);
            padding: 32px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .gl-sidebar__brand {
            padding: 0 24px 28px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 20px;
        }
        .gl-sidebar__logo {
            font-size: 1.375rem;
            font-weight: 800;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }
        .gl-sidebar__sub {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-muted);
            margin-top: 2px;
        }
        .gl-sidebar nav a {
            display: block;
            padding: 8px 24px;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: color 0.15s, background 0.15s;
            border-left: 2px solid transparent;
        }
        .gl-sidebar nav a:hover {
            color: var(--color-primary);
            background: rgba(34,211,238,0.04);
            border-left-color: var(--color-primary);
        }
        .gl-sidebar__group {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.25);
            padding: 20px 24px 6px;
        }

        /* ── MAIN ── */
        .gl-main {
            flex: 1;
            padding: 56px 64px;
            max-width: 960px;
        }

        /* ── SECTIONS ── */
        .gl-section { margin-bottom: 80px; }
        .gl-section:last-child { margin-bottom: 0; }

        .gl-section__title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gl-section__title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin-left: 8px;
        }
        .gl-section__desc {
            font-size: 0.9375rem;
            color: var(--color-muted);
            margin-bottom: 32px;
            max-width: 600px;
        }

        /* ── HERO HEADER ── */
        .gl-hero {
            margin-bottom: 72px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .gl-hero__badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--color-primary);
            background: rgba(34,211,238,0.1);
            border: 1px solid rgba(34,211,238,0.2);
            border-radius: 100px;
            padding: 4px 12px;
            margin-bottom: 20px;
        }
        .gl-hero__title {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #fff;
            margin-bottom: 16px;
        }
        .gl-hero__title span {
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gl-hero__sub {
            font-size: 1rem;
            color: rgba(255,255,255,0.5);
            max-width: 560px;
        }

        /* ── COLOR PALETTE ── */
        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 16px;
        }
        .color-swatch {
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .color-swatch__preview {
            height: 72px;
        }
        .color-swatch__info {
            background: #0d1829;
            padding: 12px 14px;
        }
        .color-swatch__name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 3px;
        }
        .color-swatch__token {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: var(--color-muted);
        }
        .color-swatch__hex {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: var(--color-primary);
            margin-top: 2px;
        }

        /* ── GRADIENTS ── */
        .gradient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }
        .gradient-card {
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.06);
        }
        .gradient-card__preview {
            height: 88px;
        }
        .gradient-card__info {
            background: #0d1829;
            padding: 14px 16px;
        }
        .gradient-card__name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 6px;
        }
        .gradient-card__code {
            font-family: var(--font-mono);
            font-size: 0.6875rem;
            color: var(--color-muted);
            line-height: 1.5;
            word-break: break-all;
        }

        /* ── TYPOGRAPHY ── */
        .type-scale { display: flex; flex-direction: column; gap: 4px; }
        .type-row {
            display: flex;
            align-items: baseline;
            gap: 24px;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .type-row:last-child { border-bottom: none; }
        .type-row__meta {
            flex-shrink: 0;
            width: 130px;
        }
        .type-row__label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-muted);
        }
        .type-row__specs {
            font-family: var(--font-mono);
            font-size: 0.6875rem;
            color: rgba(34,211,238,0.7);
            margin-top: 4px;
            line-height: 1.6;
        }
        .type-row__sample { flex: 1; color: #fff; }

        /* ── BUTTONS ── */
        .btn-showcase {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            padding: 32px;
            background: #0d1829;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255,255,255,0.06);
            margin-bottom: 16px;
        }
        .btn-showcase--light {
            background: #f1f5f9;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-base);
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            padding: 12px 24px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn--lg { padding: 16px 32px; font-size: 1rem; }
        .btn--gradient { background: var(--gradient-accent); color: var(--color-dark); font-weight: 700; }
        .btn--gradient:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(34,211,238,0.35); filter: brightness(1.05); }
        .btn--ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.3); }
        .btn--ghost:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.5); }
        .btn--accent { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); }
        .btn--accent:hover { background: rgba(34,211,238,0.08); }
        .btn--outline-dark { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); }

        /* ── CARDS ── */
        .cards-showcase {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }
        .demo-card {
            border-radius: var(--radius-lg);
            padding: 28px;
        }
        .demo-card--dark {
            background: var(--color-dark-2);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .demo-card--light {
            background: var(--color-white);
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-card);
        }
        .demo-card__icon {
            width: 48px;
            height: 48px;
            background: rgba(34,211,238,0.1);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-primary);
            margin-bottom: 16px;
        }
        .demo-card__title {
            font-size: 1.0625rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .demo-card--dark .demo-card__title { color: #fff; }
        .demo-card--light .demo-card__title { color: var(--color-text); }
        .demo-card__text { font-size: 0.9rem; line-height: 1.6; }
        .demo-card--dark .demo-card__text { color: rgba(255,255,255,0.55); }
        .demo-card--light .demo-card__text { color: var(--color-muted); }

        /* ── SPACING ── */
        .spacing-grid { display: flex; flex-direction: column; gap: 12px; }
        .spacing-row { display: flex; align-items: center; gap: 20px; }
        .spacing-row__bar {
            background: rgba(34,211,238,0.15);
            border: 1px solid rgba(34,211,238,0.3);
            height: 16px;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .spacing-row__info { font-family: var(--font-mono); font-size: 0.8125rem; color: var(--color-muted); }
        .spacing-row__info strong { color: #fff; }

        /* ── RADIUS ── */
        .radius-grid { display: flex; gap: 24px; flex-wrap: wrap; }
        .radius-item { text-align: center; }
        .radius-item__box {
            width: 80px;
            height: 80px;
            background: rgba(34,211,238,0.1);
            border: 1px solid rgba(34,211,238,0.25);
            margin-bottom: 10px;
        }
        .radius-item__label { font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-muted); }
        .radius-item__value { font-size: 0.8125rem; font-weight: 600; color: #fff; margin-top: 2px; }

        /* ── SHADOWS ── */
        .shadow-grid { display: flex; gap: 24px; flex-wrap: wrap; }
        .shadow-item {
            background: #1e293b;
            border-radius: var(--radius-md);
            padding: 24px 28px;
            text-align: center;
        }
        .shadow-item__label { font-size: 0.8125rem; font-weight: 600; color: #fff; }
        .shadow-item__code { font-family: var(--font-mono); font-size: 0.7rem; color: var(--color-muted); margin-top: 6px; max-width: 200px; word-break: break-all; }

        /* ── CODE BLOCK ── */
        .code-block {
            background: #060e1b;
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: var(--radius-md);
            padding: 20px 24px;
            font-family: var(--font-mono);
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.7);
            line-height: 1.8;
            overflow-x: auto;
            margin-top: 12px;
        }
        .code-block .c-key   { color: #22d3ee; }
        .code-block .c-val   { color: #a5f3fc; }
        .code-block .c-str   { color: #fde68a; }
        .code-block .c-com   { color: rgba(255,255,255,0.3); }

        /* ── LABEL COMPONENT ── */
        .demo-label {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        .demo-section-header {
            padding: 28px;
            background: var(--color-dark);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .demo-section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .demo-section-title .highlight {
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .demo-section-sub {
            margin-top: 8px;
            font-size: 0.9375rem;
            color: var(--color-muted);
        }

        /* ── ANIMATION DEMO ── */
        .anim-demo {
            padding: 32px;
            background: #0d1829;
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .anim-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .anim-bar__row {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .anim-bar__label { font-size: 0.8125rem; color: var(--color-muted); width: 100px; flex-shrink: 0; }
        .anim-bar__track {
            flex: 1;
            height: 6px;
            background: rgba(255,255,255,0.07);
            border-radius: 100px;
            overflow: hidden;
        }
        .anim-bar__fill {
            height: 100%;
            border-radius: 100px;
            background: var(--gradient-accent);
        }
        .anim-bar__val { font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-primary); width: 48px; text-align: right; }

        /* ── SECTION PATTERN ── */
        .section-pattern {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sp-row {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .sp-block {
            height: 40px;
            flex: 1;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            padding-left: 16px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        .sp-block--dark { background: #0f172a; color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.06); }
        .sp-block--light { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
        .sp-block--white { background: #ffffff; color: #64748b; border: 1px solid #e2e8f0; }
        .sp-arrow { color: rgba(34,211,238,0.5); font-size: 1.125rem; }

        /* ── TABLE ── */
        .gl-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .gl-table th { text-align: left; padding: 10px 16px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--color-muted); border-bottom: 1px solid rgba(255,255,255,0.07); }
        .gl-table td { padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); color: rgba(255,255,255,0.75); vertical-align: middle; }
        .gl-table td:first-child { font-family: var(--font-mono); color: var(--color-primary); font-size: 0.8125rem; }
        .gl-table tr:last-child td { border-bottom: none; }
        .gl-table-wrap { background: #0d1829; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.06); overflow: hidden; }

        /* ── DIVIDER ── */
        .gl-divider { height: 1px; background: rgba(255,255,255,0.05); margin: 48px 0; }
    </style>
</head>
<body>

<div class="gl-wrap">

    <!-- SIDEBAR -->
    <aside class="gl-sidebar">
        <div class="gl-sidebar__brand">
            <div class="gl-sidebar__logo">inunda ia</div>
            <div class="gl-sidebar__sub">Style Guide</div>
        </div>
        <nav>
            <div class="gl-sidebar__group">Fundamentos</div>
            <a href="#cores">Cores & Tokens</a>
            <a href="#gradientes">Gradientes</a>
            <a href="#tipografia">Tipografia</a>

            <div class="gl-sidebar__group">Componentes</div>
            <a href="#botoes">Botões</a>
            <a href="#cards">Cards</a>
            <a href="#labels">Labels & Headers</a>

            <div class="gl-sidebar__group">Layout</div>
            <a href="#espacamento">Espaçamento</a>
            <a href="#raios">Border Radius</a>
            <a href="#sombras">Sombras</a>
            <a href="#secoes">Padrão de Seções</a>

            <div class="gl-sidebar__group">Motion</div>
            <a href="#animacoes">Animações</a>

            <div class="gl-sidebar__group">Tokens</div>
            <a href="#variaveis">Variáveis CSS</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="gl-main">

        <!-- HERO -->
        <div class="gl-hero">
            <div class="gl-hero__badge">Documento vivo</div>
            <h1 class="gl-hero__title">Guideline <span>Visual</span><br>Inunda IA</h1>
            <p class="gl-hero__sub">Referência central de design para o site. Use este documento antes de criar ou alterar qualquer componente, seção ou página.</p>
        </div>


        <!-- CORES -->
        <section class="gl-section" id="cores">
            <h2 class="gl-section__title">Cores & Tokens</h2>
            <p class="gl-section__desc">Paleta completa do site. Sempre use as variáveis CSS — nunca valores hex diretamente no código.</p>

            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-muted); margin-bottom: 12px;">Cores de marca</div>
                <div class="color-grid">
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #22d3ee;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Primary</div>
                            <div class="color-swatch__token">--color-primary</div>
                            <div class="color-swatch__hex">#22d3ee</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #06b6d4;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Primary Dark</div>
                            <div class="color-swatch__token">--color-primary-dark</div>
                            <div class="color-swatch__hex">#06b6d4</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-muted); margin-bottom: 12px;">Fundos</div>
                <div class="color-grid">
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #0f172a; border-bottom: 1px solid rgba(255,255,255,0.06);"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Dark</div>
                            <div class="color-swatch__token">--color-dark</div>
                            <div class="color-swatch__hex">#0f172a</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #1e293b; border-bottom: 1px solid rgba(255,255,255,0.06);"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Dark 2</div>
                            <div class="color-swatch__token">--color-dark-2</div>
                            <div class="color-swatch__hex">#1e293b</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #f8fafc;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Light</div>
                            <div class="color-swatch__token">--color-light</div>
                            <div class="color-swatch__hex">#f8fafc</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">White</div>
                            <div class="color-swatch__token">--color-white</div>
                            <div class="color-swatch__hex">#ffffff</div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-muted); margin-bottom: 12px;">Texto & UI</div>
                <div class="color-grid">
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #0f172a;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Text Primary</div>
                            <div class="color-swatch__token">--color-text</div>
                            <div class="color-swatch__hex">#0f172a</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #64748b;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Muted</div>
                            <div class="color-swatch__token">--color-muted</div>
                            <div class="color-swatch__hex">#64748b</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: #e2e8f0;"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Border</div>
                            <div class="color-swatch__token">--color-border</div>
                            <div class="color-swatch__hex">#e2e8f0</div>
                        </div>
                    </div>
                    <div class="color-swatch">
                        <div class="color-swatch__preview" style="background: rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.06);"></div>
                        <div class="color-swatch__info">
                            <div class="color-swatch__name">Border Dark</div>
                            <div class="color-swatch__token">--color-border-dark</div>
                            <div class="color-swatch__hex">rgba(255,255,255,0.08)</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- GRADIENTES -->
        <section class="gl-section" id="gradientes">
            <h2 class="gl-section__title">Gradientes</h2>
            <p class="gl-section__desc">Três gradientes principais do site. O accent é o mais usado — em botões CTA, textos em destaque e ícones.</p>

            <div class="gradient-grid">
                <div class="gradient-card">
                    <div class="gradient-card__preview" style="background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%);"></div>
                    <div class="gradient-card__info">
                        <div class="gradient-card__name">Gradient Accent (principal)</div>
                        <div class="gradient-card__code">linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%)<br><span style="color: var(--color-primary);">var(--gradient-accent)</span></div>
                    </div>
                </div>
                <div class="gradient-card">
                    <div class="gradient-card__preview" style="background: linear-gradient(to bottom, rgba(15,23,42,0.7), rgba(15,23,42,0.92));"></div>
                    <div class="gradient-card__info">
                        <div class="gradient-card__name">Hero Overlay</div>
                        <div class="gradient-card__code">linear-gradient(to bottom, rgba(15,23,42,0.7), rgba(15,23,42,0.92))<br><span style="color: rgba(255,255,255,0.3);">sobre imagem de fundo</span></div>
                    </div>
                </div>
                <div class="gradient-card">
                    <div class="gradient-card__preview" style="background: linear-gradient(to right, #0f172a 30%, rgba(15,23,42,0.80) 58%, rgba(15,23,42,0.20) 100%), url('https://images.pexels.com/photos/6248967/pexels-photo-6248967.jpeg?auto=compress&cs=tinysrgb&w=400') right center / cover no-repeat;"></div>
                    <div class="gradient-card__info">
                        <div class="gradient-card__name">Hero Lateral (páginas internas)</div>
                        <div class="gradient-card__code">linear-gradient(to right, #0f172a 30%, rgba(15,23,42,0.80) 58%, rgba(15,23,42,0.20) 100%)<br><span style="color: rgba(255,255,255,0.3);">+ imagem à direita</span></div>
                    </div>
                </div>
                <div class="gradient-card">
                    <div class="gradient-card__preview" style="background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%);"></div>
                    <div class="gradient-card__info">
                        <div class="gradient-card__name">Section Dark</div>
                        <div class="gradient-card__code">linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%)<br><span style="color: rgba(255,255,255,0.3);">seções escuras com toque azul</span></div>
                    </div>
                </div>
            </div>
        </section>


        <!-- TIPOGRAFIA -->
        <section class="gl-section" id="tipografia">
            <h2 class="gl-section__title">Tipografia</h2>
            <p class="gl-section__desc">Fonte única: <strong style="color:#fff;">Inter</strong> via Google Fonts. Pesos usados: 400, 500, 600, 700, 800.</p>

            <div class="type-scale">
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">Display</div>
                        <div class="type-row__specs">4rem–5rem<br>weight 800<br>line-height 1.05</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 3rem; font-weight: 800; line-height: 1.05; letter-spacing: -0.02em;">Inunda IA</div>
                </div>
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">H1 Hero</div>
                        <div class="type-row__specs">2.75rem–3.5rem<br>weight 800<br>line-height 1.1</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 2.25rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.02em;">Tecnologia que <span style="background: var(--gradient-accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">flui</span> com você</div>
                </div>
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">H2 Seção</div>
                        <div class="type-row__specs">2rem–2.5rem<br>weight 700<br>line-height 1.15</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 1.875rem; font-weight: 700; line-height: 1.15;">Tudo que um projeto <span style="background: var(--gradient-accent); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">sério</span> precisa</div>
                </div>
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">H3 Card</div>
                        <div class="type-row__specs">1.125rem–1.5rem<br>weight 700<br>line-height 1.3</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 1.25rem; font-weight: 700; line-height: 1.3;">Desenvolvimento Customizado</div>
                </div>
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">Subtítulo</div>
                        <div class="type-row__specs">1rem–1.125rem<br>weight 400<br>line-height 1.7</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 1.0625rem; font-weight: 400; line-height: 1.7; color: rgba(255,255,255,0.6);">Arquitetura robusta, performance otimizada e desenvolvimento customizado para projetos de médio e grande porte.</div>
                </div>
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">Corpo</div>
                        <div class="type-row__specs">0.9375rem<br>weight 400<br>line-height 1.7</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 0.9375rem; font-weight: 400; line-height: 1.7; color: rgba(255,255,255,0.55);">Temas e plugins desenvolvidos do zero, sem gambiarras ou page builders que comprometem performance.</div>
                </div>
                <div class="type-row">
                    <div class="type-row__meta">
                        <div class="type-row__label">Label</div>
                        <div class="type-row__specs">0.75rem<br>weight 600<br>letter-spacing 0.1em</div>
                    </div>
                    <div class="type-row__sample" style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-primary);">O que entregamos</div>
                </div>
            </div>

            <div class="code-block" style="margin-top: 28px;">
<span class="c-com">/* Destaque em headline — nunca tags bold, sempre span com classe */</span>
&lt;h1&gt;WordPress que &lt;span class=<span class="c-str">"text--gradient"</span>&gt;transforma&lt;/span&gt; resultados&lt;/h1&gt;

<span class="c-com">/* CSS */</span>
<span class="c-key">.text--gradient</span> {
  background: <span class="c-val">var(--gradient-accent)</span>;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
            </div>
        </section>


        <!-- BOTÕES -->
        <section class="gl-section" id="botoes">
            <h2 class="gl-section__title">Botões</h2>
            <p class="gl-section__desc">Quatro variantes. Todos com <code style="font-family: var(--font-mono); color: var(--color-primary); font-size: 0.85em;">font-weight: 600</code> e <code style="font-family: var(--font-mono); color: var(--color-primary); font-size: 0.85em;">transition: all 0.2s</code>.</p>

            <div class="btn-showcase">
                <a class="btn btn--gradient">Falar com especialista</a>
                <a class="btn btn--ghost">Ver o que entregamos</a>
                <a class="btn btn--accent">Conhecer planos</a>
            </div>

            <div class="btn-showcase" style="padding: 24px 32px;">
                <a class="btn btn--gradient btn--lg">CTA Principal — LG</a>
                <a class="btn btn--ghost btn--lg">Secundário — LG</a>
            </div>

            <div class="gl-table-wrap" style="margin-top: 20px;">
                <table class="gl-table">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Uso</th>
                            <th>Fundo</th>
                            <th>Hover</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>.btn--gradient</td>
                            <td>CTA principal, ação primária</td>
                            <td>gradient-accent</td>
                            <td>translateY(-2px) + shadow cyan</td>
                        </tr>
                        <tr>
                            <td>.btn--ghost</td>
                            <td>Ação secundária em fundo escuro</td>
                            <td>transparente</td>
                            <td>bg rgba(255,255,255,0.08)</td>
                        </tr>
                        <tr>
                            <td>.btn--accent</td>
                            <td>Outline em fundo escuro</td>
                            <td>transparente</td>
                            <td>bg rgba(34,211,238,0.08)</td>
                        </tr>
                        <tr>
                            <td>.btn--lg</td>
                            <td>Modificador de tamanho (heroes)</td>
                            <td>—</td>
                            <td>—</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>


        <!-- CARDS -->
        <section class="gl-section" id="cards">
            <h2 class="gl-section__title">Cards</h2>
            <p class="gl-section__desc">Dois temas: dark (seções escuras) e light (seções claras). Padding interno 24–32px, radius 16px.</p>

            <div class="cards-showcase">
                <div class="demo-card demo-card--dark">
                    <div class="demo-card__icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <div class="demo-card__title">Card Dark</div>
                    <div class="demo-card__text">Usado em seções com fundo #0f172a. Border sutil rgba(255,255,255,0.08).</div>
                </div>
                <div class="demo-card demo-card--light">
                    <div class="demo-card__icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <div class="demo-card__title">Card Light</div>
                    <div class="demo-card__text">Usado em seções claras #f8fafc. Shadow suave, border #e2e8f0.</div>
                </div>
                <div class="demo-card demo-card--dark" style="border-color: rgba(34,211,238,0.15);">
                    <div style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-primary); margin-bottom: 8px;">Recomendado</div>
                    <div class="demo-card__title">Card Destaque</div>
                    <div class="demo-card__text">Variante featured — border com toque de cyan, badge no topo.</div>
                </div>
            </div>

            <div class="code-block" style="margin-top: 20px;">
<span class="c-com">/* Card dark */</span>
<span class="c-key">border-radius</span>: <span class="c-val">16px</span>;
<span class="c-key">background</span>:    <span class="c-val">#1e293b</span>;
<span class="c-key">border</span>:        <span class="c-val">1px solid rgba(255,255,255,0.08)</span>;
<span class="c-key">padding</span>:       <span class="c-val">24px–32px</span>;

<span class="c-com">/* Card light */</span>
<span class="c-key">background</span>:  <span class="c-val">#ffffff</span>;
<span class="c-key">border</span>:      <span class="c-val">1px solid #e2e8f0</span>;
<span class="c-key">box-shadow</span>: <span class="c-val">0 4px 24px rgba(0,0,0,0.06)</span>;
            </div>
        </section>


        <!-- LABELS & SECTION HEADERS -->
        <section class="gl-section" id="labels">
            <h2 class="gl-section__title">Labels & Section Headers</h2>
            <p class="gl-section__desc">Padrão de abertura de seção: label uppercase + título com palavra em destaque + subtítulo opcional.</p>

            <div class="demo-section-header">
                <div class="demo-label">O que entregamos</div>
                <div class="demo-section-title">Tudo que um projeto<br><span class="highlight">sério</span> precisa</div>
                <div class="demo-section-sub">Da estratégia ao deploy — cuidamos de cada detalhe técnico para que você foque no negócio.</div>
            </div>

            <div class="code-block" style="margin-top: 16px;">
<span class="c-key">.label</span> {
  font-size:      <span class="c-val">0.75rem</span>;
  font-weight:    <span class="c-val">600</span>;
  letter-spacing: <span class="c-val">0.1em</span>;
  text-transform: <span class="c-val">uppercase</span>;
  color:          <span class="c-val">var(--color-primary)</span>;  <span class="c-com">/* #22d3ee */</span>
  margin-bottom:  <span class="c-val">16px</span>;
}
            </div>
        </section>


        <!-- ESPAÇAMENTO -->
        <section class="gl-section" id="espacamento">
            <h2 class="gl-section__title">Espaçamento</h2>
            <p class="gl-section__desc">Sistema baseado em múltiplos de 8px. Padding vertical de seções: 80–120px.</p>

            <div class="spacing-grid">
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 8px;"></div><div class="spacing-row__info"><strong>8px</strong> — espaço mínimo, gaps internos</div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 16px;"></div><div class="spacing-row__info"><strong>16px</strong> — margin-bottom de labels, gaps de elementos</div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 24px;"></div><div class="spacing-row__info"><strong>24px</strong> — padding de cards pequenos, container horizontal</div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 32px;"></div><div class="spacing-row__info"><strong>32px</strong> — padding de cards, gaps de nav</div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 48px;"></div><div class="spacing-row__info"><strong>48px</strong> — gaps de grid, padding de cards grandes</div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 64px;"></div><div class="spacing-row__info"><strong>64px</strong> — margin-bottom de section-header</div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 96px;"></div><div class="spacing-row__info"><strong>96px</strong> — padding vertical padrão de seções <span style="color: var(--color-primary);">--section-padding</span></div></div>
                <div class="spacing-row"><div class="spacing-row__bar" style="width: 120px;"></div><div class="spacing-row__info"><strong>120px</strong> — padding vertical de seções hero</div></div>
            </div>

            <div style="margin-top: 28px;">
                <div style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-muted); margin-bottom: 12px;">Container</div>
                <div class="code-block">
<span class="c-key">max-width</span>: <span class="c-val">1200px</span>;  <span class="c-com">/* --container-max */</span>
<span class="c-key">padding</span>:   <span class="c-val">0 24px</span>;     <span class="c-com">/* --container-padding */</span>
<span class="c-key">margin</span>:    <span class="c-val">0 auto</span>;
                </div>
            </div>
        </section>


        <!-- BORDER RADIUS -->
        <section class="gl-section" id="raios">
            <h2 class="gl-section__title">Border Radius</h2>

            <div class="radius-grid">
                <div class="radius-item">
                    <div class="radius-item__box" style="border-radius: 8px;"></div>
                    <div class="radius-item__label">--radius-sm</div>
                    <div class="radius-item__value">8px</div>
                    <div style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">Botões</div>
                </div>
                <div class="radius-item">
                    <div class="radius-item__box" style="border-radius: 12px;"></div>
                    <div class="radius-item__label">--radius-md</div>
                    <div class="radius-item__value">12px</div>
                    <div style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">Ícones, badges</div>
                </div>
                <div class="radius-item">
                    <div class="radius-item__box" style="border-radius: 16px;"></div>
                    <div class="radius-item__label">--radius-lg</div>
                    <div class="radius-item__value">16px</div>
                    <div style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">Cards, seções</div>
                </div>
                <div class="radius-item">
                    <div class="radius-item__box" style="border-radius: 100px;"></div>
                    <div class="radius-item__label">pill</div>
                    <div class="radius-item__value">100px</div>
                    <div style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">Badges, labels</div>
                </div>
                <div class="radius-item">
                    <div class="radius-item__box" style="border-radius: 50%;"></div>
                    <div class="radius-item__label">circle</div>
                    <div class="radius-item__value">50%</div>
                    <div style="font-size: 0.75rem; color: var(--color-muted); margin-top: 4px;">WhatsApp btn</div>
                </div>
            </div>
        </section>


        <!-- SOMBRAS -->
        <section class="gl-section" id="sombras">
            <h2 class="gl-section__title">Sombras</h2>

            <div class="shadow-grid">
                <div class="shadow-item" style="box-shadow: 0 4px 24px rgba(0,0,0,0.06);">
                    <div class="shadow-item__label">Card</div>
                    <div class="shadow-item__code">0 4px 24px rgba(0,0,0,0.06)</div>
                    <div style="font-size: 0.75rem; color: var(--color-primary); margin-top: 6px;">--shadow-card</div>
                </div>
                <div class="shadow-item" style="box-shadow: 0 4px 24px rgba(0,0,0,0.2);">
                    <div class="shadow-item__label">Card Dark</div>
                    <div class="shadow-item__code">0 4px 24px rgba(0,0,0,0.2)</div>
                    <div style="font-size: 0.75rem; color: var(--color-primary); margin-top: 6px;">--shadow-card-dark</div>
                </div>
                <div class="shadow-item" style="box-shadow: 0 24px 64px rgba(0,0,0,0.2);">
                    <div class="shadow-item__label">Hero / Mockup</div>
                    <div class="shadow-item__code">0 24px 64px rgba(0,0,0,0.2)</div>
                    <div style="font-size: 0.75rem; color: var(--color-primary); margin-top: 6px;">--shadow-hero</div>
                </div>
                <div class="shadow-item" style="box-shadow: 0 6px 24px rgba(34,211,238,0.35);">
                    <div class="shadow-item__label">Botão Hover</div>
                    <div class="shadow-item__code">0 6px 24px rgba(34,211,238,0.35)</div>
                    <div style="font-size: 0.75rem; color: var(--color-primary); margin-top: 6px;">estado hover btn--gradient</div>
                </div>
            </div>
        </section>


        <!-- PADRÃO DE SEÇÕES -->
        <section class="gl-section" id="secoes">
            <h2 class="gl-section__title">Padrão de Seções</h2>
            <p class="gl-section__desc">Seções alternam entre fundos escuros e claros. A homepage começa sempre escura (hero). Nunca colocar duas seções claras seguidas.</p>

            <div class="section-pattern">
                <div class="sp-row">
                    <div class="sp-block sp-block--dark">Hero — #0f172a + overlay</div>
                </div>
                <div class="sp-row"><div class="sp-arrow">↕</div></div>
                <div class="sp-row">
                    <div class="sp-block sp-block--light">Seção Clara — #f8fafc ou #ffffff</div>
                </div>
                <div class="sp-row"><div class="sp-arrow">↕</div></div>
                <div class="sp-row">
                    <div class="sp-block sp-block--dark">Seção Escura — #0f172a</div>
                </div>
                <div class="sp-row"><div class="sp-arrow">↕</div></div>
                <div class="sp-row">
                    <div class="sp-block sp-block--white">Seção Branca — #ffffff</div>
                </div>
                <div class="sp-row"><div class="sp-arrow">↕</div></div>
                <div class="sp-row">
                    <div class="sp-block sp-block--dark">CTA Final — #0f172a com gradient</div>
                </div>
            </div>

            <div style="margin-top: 24px; padding: 20px; background: rgba(34,211,238,0.05); border: 1px solid rgba(34,211,238,0.15); border-radius: var(--radius-md);">
                <div style="font-size: 0.8125rem; font-weight: 600; color: var(--color-primary); margin-bottom: 8px;">Regras de alternância</div>
                <ul style="font-size: 0.875rem; color: rgba(255,255,255,0.6); line-height: 2; padding-left: 18px;">
                    <li>Nunca duas seções escuras consecutivas sem justificativa visual</li>
                    <li>Nunca duas seções claras consecutivas</li>
                    <li>Padding vertical padrão: <code style="font-family: var(--font-mono); color: var(--color-primary);">96px 0</code> (var --section-padding)</li>
                    <li>Heroes de páginas internas: <code style="font-family: var(--font-mono); color: var(--color-primary);">min-height: 70vh</code></li>
                </ul>
            </div>
        </section>


        <!-- ANIMAÇÕES -->
        <section class="gl-section" id="animacoes">
            <h2 class="gl-section__title">Animações</h2>
            <p class="gl-section__desc">Scroll reveals via IntersectionObserver. Padrão único: fade + translateY. Sutis e rápidas.</p>

            <div class="anim-demo">
                <div class="anim-bar">
                    <div class="anim-bar__row">
                        <div class="anim-bar__label">opacity</div>
                        <div class="anim-bar__track"><div class="anim-bar__fill" style="width: 100%;"></div></div>
                        <div class="anim-bar__val">0 → 1</div>
                    </div>
                    <div class="anim-bar__row">
                        <div class="anim-bar__label">translateY</div>
                        <div class="anim-bar__track"><div class="anim-bar__fill" style="width: 70%;"></div></div>
                        <div class="anim-bar__val">24px → 0</div>
                    </div>
                    <div class="anim-bar__row">
                        <div class="anim-bar__label">duration</div>
                        <div class="anim-bar__track"><div class="anim-bar__fill" style="width: 50%;"></div></div>
                        <div class="anim-bar__val">0.5s</div>
                    </div>
                    <div class="anim-bar__row">
                        <div class="anim-bar__label">easing</div>
                        <div class="anim-bar__track"><div class="anim-bar__fill" style="width: 60%;"></div></div>
                        <div class="anim-bar__val">ease</div>
                    </div>
                    <div class="anim-bar__row">
                        <div class="anim-bar__label">stagger</div>
                        <div class="anim-bar__track"><div class="anim-bar__fill" style="width: 20%;"></div></div>
                        <div class="anim-bar__val">0.1s</div>
                    </div>
                </div>
            </div>

            <div class="code-block" style="margin-top: 16px;">
<span class="c-com">/* CSS — estado inicial */</span>
<span class="c-key">.fade-up</span> {
  opacity:   <span class="c-val">0</span>;
  transform: <span class="c-val">translateY(24px)</span>;
  transition: opacity <span class="c-val">0.5s ease</span>, transform <span class="c-val">0.5s ease</span>;
}
<span class="c-key">.fade-up.visible</span> {
  opacity:   <span class="c-val">1</span>;
  transform: <span class="c-val">translateY(0)</span>;
}

<span class="c-com">/* JS — IntersectionObserver com stagger */</span>
<span class="c-key">document</span>.querySelectorAll(<span class="c-str">'.fade-up'</span>).forEach((el, i) => {
  observer.observe(el);
  el.style.transitionDelay = <span class="c-str">`${i * 0.1}s`</span>;
});
            </div>

            <div style="margin-top: 16px; padding: 16px 20px; background: rgba(255,200,80,0.06); border: 1px solid rgba(255,200,80,0.15); border-radius: var(--radius-md);">
                <div style="font-size: 0.8125rem; color: rgba(255,220,100,0.8);">⚠ Nunca usar animações em elementos above-the-fold (hero). O stagger máximo permitido é 5 elementos por grupo para não arrastar demais.</div>
            </div>
        </section>


        <!-- VARIÁVEIS CSS -->
        <section class="gl-section" id="variaveis">
            <h2 class="gl-section__title">Variáveis CSS</h2>
            <p class="gl-section__desc">Definidas em <code style="font-family: var(--font-mono); color: var(--color-primary);">/site/assets/css/variables.css</code>. Sempre use tokens — nunca valores diretos.</p>

            <div class="code-block">
<span class="c-key">:root</span> {
  <span class="c-com">/* Cores */</span>
  <span class="c-key">--color-primary</span>:      <span class="c-val">#22d3ee</span>;
  <span class="c-key">--color-primary-dark</span>: <span class="c-val">#06b6d4</span>;
  <span class="c-key">--color-dark</span>:         <span class="c-val">#0f172a</span>;
  <span class="c-key">--color-dark-2</span>:       <span class="c-val">#1e293b</span>;
  <span class="c-key">--color-light</span>:        <span class="c-val">#f8fafc</span>;
  <span class="c-key">--color-white</span>:        <span class="c-val">#ffffff</span>;
  <span class="c-key">--color-text</span>:         <span class="c-val">#0f172a</span>;
  <span class="c-key">--color-muted</span>:        <span class="c-val">#64748b</span>;
  <span class="c-key">--color-border</span>:       <span class="c-val">#e2e8f0</span>;
  <span class="c-key">--color-border-dark</span>:  <span class="c-val">rgba(255,255,255,0.08)</span>;

  <span class="c-com">/* Gradientes */</span>
  <span class="c-key">--gradient-accent</span>: <span class="c-val">linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%)</span>;

  <span class="c-com">/* Tipografia */</span>
  <span class="c-key">--font-base</span>: <span class="c-str">'Inter'</span>, -apple-system, BlinkMacSystemFont, sans-serif;

  <span class="c-com">/* Layout */</span>
  <span class="c-key">--section-padding</span>: <span class="c-val">96px 0</span>;
  <span class="c-key">--container-max</span>:   <span class="c-val">1200px</span>;
  <span class="c-key">--container-padding</span>: <span class="c-val">0 24px</span>;

  <span class="c-com">/* Border radius */</span>
  <span class="c-key">--radius-sm</span>: <span class="c-val">8px</span>;
  <span class="c-key">--radius-md</span>: <span class="c-val">12px</span>;
  <span class="c-key">--radius-lg</span>: <span class="c-val">16px</span>;

  <span class="c-com">/* Sombras */</span>
  <span class="c-key">--shadow-card</span>:      <span class="c-val">0 4px 24px rgba(0,0,0,0.06)</span>;
  <span class="c-key">--shadow-card-dark</span>: <span class="c-val">0 4px 24px rgba(0,0,0,0.2)</span>;
  <span class="c-key">--shadow-hero</span>:      <span class="c-val">0 24px 64px rgba(0,0,0,0.2)</span>;

  <span class="c-com">/* Transição */</span>
  <span class="c-key">--transition</span>: <span class="c-val">all 0.2s ease</span>;
}
            </div>
        </section>

        <div style="margin-top: 80px; padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 0.8125rem; color: rgba(255,255,255,0.25); text-align: center;">
            Inunda IA — Guideline Interno &nbsp;·&nbsp; Não indexado pelos buscadores
        </div>

    </main>
</div>

</body>
</html>
