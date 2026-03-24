<?php
$root = dirname(__DIR__);
$page_title = 'Suporte WordPress Premium — Inunda';
$page_description = 'Seu time técnico WordPress terceirizado. Monitoramento 24/7, hospedagem gerenciada, SLA garantido e suporte humanizado.';
include $root . '/site/includes/head-page.php';
include $root . '/site/includes/header.php';
?>

<!-- HERO -->
<section class="page-hero page-hero--suporte">
    <div class="page-hero__bg page-hero__bg--support"></div>
    <div class="container page-hero__content">
        <a href="/" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Voltar para o início
        </a>
        <span class="label">Suporte & Hospedagem</span>
        <h1 class="page-hero__title">
            Seu time técnico<br>
            <span class="text--gradient">WordPress</span> 24/7
        </h1>
        <p class="page-hero__subtitle">
            Cuidamos de tudo: monitoramento, atualizações, backups, hospedagem e suporte — para que você foque no que realmente importa: fazer o seu negócio crescer.
        </p>
        <div class="page-hero__actions">
            <a href="https://wa.me/5541992050559?text=Quero saber sobre o suporte premium da Inunda."
               class="btn btn--gradient btn--lg" target="_blank" rel="noopener">
                Conhecer os planos
            </a>
            <a href="#planos" class="btn btn--ghost btn--lg">Ver planos e preços</a>
        </div>
        <div class="page-hero__trust">
            <div class="trust-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Monitoramento 24/7</span>
            </div>
            <div class="trust-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>SLA garantido em contrato</span>
            </div>
            <div class="trust-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Sem custo de setup</span>
            </div>
        </div>
    </div>
</section>

<!-- PLANOS -->
<section class="inner-section" id="planos">
    <div class="container">
        <div class="section-header">
            <span class="label">Planos</span>
            <h2 class="section-title text--white">Escolha o plano<br><span class="text--gradient">certo para você</span></h2>
            <p class="section-subtitle text--muted-dark">Todos os planos incluem hospedagem gerenciada, backups diários e suporte humanizado.</p>
        </div>
        <div class="plans-grid">
            <div class="plan-card fade-up">
                <div class="plan-card__tag">Essencial</div>
                <h3 class="plan-card__name">Start</h3>
                <p class="plan-card__desc">Para sites institucionais e blogs com tráfego moderado.</p>
                <ul class="plan-card__features">
                    <li>Hospedagem gerenciada (até 30GB)</li>
                    <li>Monitoramento de uptime</li>
                    <li>Atualizações mensais de WordPress e plugins</li>
                    <li>Backup diário com retenção de 30 dias</li>
                    <li>Certificado SSL gerenciado</li>
                    <li>Suporte via ticket (horário comercial)</li>
                </ul>
                <a href="https://wa.me/5541992050559?text=Tenho interesse no plano Start da Inunda."
                   class="btn btn--accent" target="_blank" rel="noopener">
                    Falar sobre o Start
                </a>
            </div>

            <div class="plan-card plan-card--featured fade-up">
                <div class="plan-card__badge">Mais popular</div>
                <div class="plan-card__tag">Recomendado</div>
                <h3 class="plan-card__name">Pro</h3>
                <p class="plan-card__desc">Para empresas que dependem do site e precisam de agilidade.</p>
                <ul class="plan-card__features">
                    <li>Hospedagem gerenciada (até 100GB)</li>
                    <li>Monitoramento 24/7 com alertas em tempo real</li>
                    <li>Atualizações semanais com teste em homologação</li>
                    <li>Backup diário com retenção de 90 dias</li>
                    <li>CDN global incluído</li>
                    <li>Otimização mensal de performance</li>
                    <li>Suporte via WhatsApp (até 20h por mês)</li>
                    <li>SLA de resposta em até 4 horas</li>
                </ul>
                <a href="https://wa.me/5541992050559?text=Tenho interesse no plano Pro da Inunda."
                   class="btn btn--gradient" target="_blank" rel="noopener">
                    Falar sobre o Pro
                </a>
            </div>

            <div class="plan-card fade-up">
                <div class="plan-card__tag">Corporativo</div>
                <h3 class="plan-card__name">Enterprise</h3>
                <p class="plan-card__desc">Para operações críticas que não podem parar.</p>
                <ul class="plan-card__features">
                    <li>Infraestrutura dedicada sob medida</li>
                    <li>Monitoramento ativo com NOC dedicado</li>
                    <li>Deploy e atualizações com pipeline CI/CD</li>
                    <li>Backup contínuo com retenção ilimitada</li>
                    <li>CDN + WAF avançado</li>
                    <li>Gerente técnico dedicado</li>
                    <li>Suporte prioritário 24/7 via WhatsApp e telefone</li>
                    <li>SLA de resposta em até 1 hora</li>
                </ul>
                <a href="https://wa.me/5541992050559?text=Quero saber sobre o plano Enterprise da Inunda."
                   class="btn btn--accent" target="_blank" rel="noopener">
                    Falar sobre o Enterprise
                </a>
            </div>
        </div>
    </div>
</section>

<!-- DIFERENCIAIS -->
<section class="inner-section inner-section--alt">
    <div class="container">
        <div class="section-header">
            <span class="label">Por que a Inunda</span>
            <h2 class="section-title text--white">Suporte que vai além<br>do <span class="text--gradient">básico</span></h2>
        </div>
        <div class="features-grid features-grid--3">
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3>Resposta rápida</h3>
                <p>Problemas críticos atendidos em minutos, não horas. Equipe real, não bots ou scripts genéricos.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3>Time especializado</h3>
                <p>Você fala direto com quem entende de WordPress. Sem repassar tickets entre departamentos.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <h3>Proativo, não reativo</h3>
                <p>Identificamos e resolvemos problemas antes que impactem seu negócio. Você é avisado, não surpreendido.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Segurança contínua</h3>
                <p>Scans diários de vulnerabilidades, atualizações testadas e proteção contra invasões e malwares.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
                <h3>Backups confiáveis</h3>
                <p>Backups automáticos diários testados regularmente. Restauração completa em minutos se necessário.</p>
            </div>
            <div class="feature-card fade-up">
                <div class="feature-card__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <h3>Relatórios mensais</h3>
                <p>Relatório detalhado de uptime, performance, ações executadas e recomendações para o próximo mês.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="inner-cta">
    <div class="container inner-cta__inner">
        <div>
            <h2 class="inner-cta__title">Deixa a parte técnica com a gente</h2>
            <p class="inner-cta__subtitle">Fale conosco e descubra qual plano faz mais sentido para o seu negócio.</p>
        </div>
        <a href="https://wa.me/5541992050559?text=Quero saber sobre o suporte premium da Inunda."
           class="btn btn--gradient btn--lg" target="_blank" rel="noopener">
            Falar com um especialista
        </a>
    </div>
</section>

<?php include $root . '/site/includes/footer.php'; ?>
