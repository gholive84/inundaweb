/* ===== HEADER SCROLL ===== */
const header = document.getElementById('header');
window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

/* ===== HAMBURGER MENU ===== */
const hamburger = document.getElementById('hamburger');
const nav = document.getElementById('nav');

hamburger.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('open');
    hamburger.classList.toggle('open', isOpen);
    hamburger.setAttribute('aria-expanded', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
});

nav.querySelectorAll('.nav__link').forEach(link => {
    link.addEventListener('click', () => {
        nav.classList.remove('open');
        hamburger.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    });
});

/* ===== MEGA MENU ===== */
const megaTrigger = document.getElementById('megaTrigger');
const megaMenu    = document.getElementById('megaMenu');
const navServicos = document.getElementById('navServicos');

if (megaTrigger && megaMenu) {
    megaTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = navServicos.classList.toggle('open');
        megaMenu.classList.toggle('open', isOpen);
        megaTrigger.setAttribute('aria-expanded', isOpen);
    });

    // fecha ao clicar fora
    document.addEventListener('click', (e) => {
        if (!navServicos.contains(e.target)) {
            navServicos.classList.remove('open');
            megaMenu.classList.remove('open');
            megaTrigger.setAttribute('aria-expanded', 'false');
        }
    });

    // fecha ao pressionar Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            navServicos.classList.remove('open');
            megaMenu.classList.remove('open');
            megaTrigger.setAttribute('aria-expanded', 'false');
        }
    });

    // fecha ao clicar em link do mega menu
    megaMenu.querySelectorAll('.mega-item').forEach(item => {
        item.addEventListener('click', () => {
            navServicos.classList.remove('open');
            megaMenu.classList.remove('open');
            megaTrigger.setAttribute('aria-expanded', 'false');
        });
    });
}

/* ===== SCROLL REVEAL ===== */
const revealEls = document.querySelectorAll('.fade-up');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const siblings = Array.from(entry.target.parentElement.children);
            const idx = siblings.indexOf(entry.target);
            entry.target.style.transitionDelay = `${idx * 0.08}s`;
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.12 });

revealEls.forEach(el => observer.observe(el));

/* ===== SMOOTH SCROLL ===== */
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        e.preventDefault();
        const offset = header.offsetHeight + 16;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    });
});
