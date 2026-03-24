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

/* ===== PHONE MASK ===== */
const phoneInput = document.getElementById('cf_phone');
if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
        let d = e.target.value.replace(/\D/g, '').substring(0, 11);
        if (d.length > 7)      d = `(${d.slice(0,2)}) ${d.slice(2,7)}-${d.slice(7)}`;
        else if (d.length > 2) d = `(${d.slice(0,2)}) ${d.slice(2)}`;
        else if (d.length > 0) d = `(${d}`;
        e.target.value = d;
    });
}

/* ===== CONTACT FORM VALIDATION ===== */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    const validate = (field, test) => {
        const g = field.closest('.form-group');
        const ok = test(field.value.trim());
        g.classList.toggle('has-error', !ok);
        return ok;
    };

    contactForm.querySelectorAll('input').forEach(inp => {
        inp.addEventListener('input', () => inp.closest('.form-group').classList.remove('has-error'));
    });

    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const name  = document.getElementById('cf_name');
        const email = document.getElementById('cf_email');
        const phone = document.getElementById('cf_phone');

        const v1 = validate(name,  v => v.length >= 2);
        const v2 = validate(email, v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));
        const v3 = validate(phone, v => v.replace(/\D/g,'').length >= 10);

        if (v1 && v2 && v3) {
            const btn = contactForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            fetch('/cms/leads/submit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nome:     name.value.trim(),
                    email:    email.value.trim(),
                    telefone: phone.value.trim(),
                    mensagem: '',
                    origem:   'formulario-site'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    contactForm.querySelector('.contact-form__fields').style.display = 'none';
                    document.getElementById('formSuccess').classList.add('visible');
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Enviar mensagem';
                    alert(data.error || 'Erro ao enviar. Tente novamente.');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = 'Enviar mensagem';
                alert('Erro de conexão. Tente novamente.');
            });
        }
    });
}

/* ===== SERVICE BLOCKS IN-VIEW ===== */
const serviceBlocks = document.querySelectorAll('.service-block');
if (serviceBlocks.length) {
    const blockObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                blockObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    serviceBlocks.forEach(block => blockObserver.observe(block));
}

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
