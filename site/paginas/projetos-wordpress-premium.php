<?php
$root = dirname(dirname(__DIR__));
$page_title = 'Projetos WordPress Premium — Inunda';
$page_description = 'Desenvolvimento WordPress empresarial com arquitetura robusta, performance otimizada e segurança avançada. Conheça nossos projetos.';
include $root . '/site/includes/head-page.php';
include $root . '/site/includes/header.php';
?>

<!-- HERO -->
<section class="page-hero page-hero--projetos">
    <div class="page-hero__bg"></div>
    <div class="container page-hero__content">
        <a href="/" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Voltar para o início
        </a>
        <span class="label">Projetos WordPress</span>
        <h1 class="page-hero__title">
            WordPress que <span class="text--gradient">transforma</span><br>
            resultados reais
        </h1>
        <p class="page-hero__subtitle">
            Arquitetura robusta, performance otimizada e desenvolvimento customizado para projetos de médio e grande porte. Não entregamos apenas sites — entregamos plataformas que crescem com o seu negócio.
        </p>
        <div class="page-hero__actions">
            <a href="https://wa.me/5541992050559?text=Quero iniciar um projeto WordPress com a Inunda."
               class="btn btn--gradient btn--lg" target="_blank" rel="noopener">
                Falar sobre meu projeto
            </a>
            <a href="#o-que-fazemos" class="btn btn--ghost btn--lg">Ver o que entregamos</a>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="inner-section" id="o-que-fazemos">
    <div class="container">
        <div class="section-header">
            <span class="label">O que entregamos</span>
            <h2 class="section-title text--white">Tudo que um projeto<br><span class="text--gradient">sério</span> precisa</h2>
            <p class="section-subtitle text--muted-dark">Da estratégia ao deploy — cuidamos de cada detalhe técnico para que você foque no negócio.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h3>Desenvolvimento Customizado</h3>
                <p>Temas e plugins desenvolvidos do zero, sem gambiarras ou page builders que comprometem performance.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <h3>Performance Avançada</h3>
                <p>Core Web Vitals otimizados, CDN configurado, cache em múltiplas camadas e carregamento abaixo de 2 segundos.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Segurança Enterprise</h3>
                <p>Hardening avançado, proteção contra injeções, WAF e conformidade com LGPD e boas práticas OWASP.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <h3>Integrações & APIs</h3>
                <p>Conectamos seu WordPress com ERPs, CRMs, gateways de pagamento e qualquer sistema via REST ou webhooks.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
                <h3>E-commerce WooCommerce</h3>
                <p>Lojas de alto desempenho com customizações avançadas, gestão de estoque, multimoedas e experiência de compra otimizada.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg>
                </div>
                <h3>Sites Institucionais & Portais</h3>
                <p>Presença digital sólida para empresas de médio e grande porte, com multisite, multilíngua e gestão de conteúdo avançada.</p>
            </div>
        </div>
    </div>
</section>

<!-- PROCESSO -->
<section class="inner-section inner-section--alt">
    <div class="container">
        <div class="section-header">
            <span class="label">Como trabalhamos</span>
            <h2 class="section-title text--white">Processo <span class="text--gradient">transparente</span>,<br>resultados previsíveis</h2>
        </div>
        <div class="process-steps">
            <div class="process-step fade-up">
                <div class="process-step__num">01</div>
                <h3>Descoberta</h3>
                <p>Mapeamos seus objetivos, público, integrações necessárias e benchmarks técnicos antes de escrever uma linha de código.</p>
            </div>
            <div class="process-step__arrow" aria-hidden="true">→</div>
            <div class="process-step fade-up">
                <div class="process-step__num">02</div>
                <h3>Arquitetura</h3>
                <p>Definimos stack, estrutura de dados, fluxos de usuário e estimativas realistas baseadas em escopo fechado.</p>
            </div>
            <div class="process-step__arrow" aria-hidden="true">→</div>
            <div class="process-step fade-up">
                <div class="process-step__num">03</div>
                <h3>Desenvolvimento</h3>
                <p>Sprints quinzenais com entregas parciais, code review e acesso ao ambiente de homologação em tempo real.</p>
            </div>
            <div class="process-step__arrow" aria-hidden="true">→</div>
            <div class="process-step fade-up">
                <div class="process-step__num">04</div>
                <h3>Launch & Suporte</h3>
                <p>Deploy com zero downtime, monitoramento pós-lançamento e handoff completo com documentação técnica.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="inner-cta">
    <div class="container inner-cta__inner">
        <div>
            <h2 class="inner-cta__title">Pronto para começar?</h2>
            <p class="inner-cta__subtitle">Conta seu projeto. Sem compromisso, sem pitch de vendas — só uma conversa técnica honesta.</p>
        </div>
        <a href="https://wa.me/5541992050559?text=Quero iniciar um projeto WordPress com a Inunda."
           class="btn btn--gradient btn--lg" target="_blank" rel="noopener">
            Falar com um especialista
        </a>
    </div>
</section>

<?php include $root . '/site/includes/footer.php'; ?>
