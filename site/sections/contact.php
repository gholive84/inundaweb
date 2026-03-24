<section class="contact" id="contato">
    <div class="container">
        <div class="section-header">
            <span class="label">Fale Conosco</span>
            <h2 class="section-title text--white">Vamos conversar sobre <span class="text--gradient">seu projeto</span>?</h2>
            <p class="section-subtitle text--muted-dark">Preencha o formulário e entraremos em contato em até 24 horas.</p>
        </div>

        <div class="contact__inner">

            <!-- Formulário -->
            <form class="contact-form" id="contactForm" novalidate>
                <div class="contact-form__fields">
                    <div class="form-group">
                        <label for="cf_name">Nome</label>
                        <input type="text" id="cf_name" name="name" placeholder="Seu nome completo" autocomplete="name">
                        <span class="form-error">Por favor, informe seu nome</span>
                    </div>
                    <div class="form-group">
                        <label for="cf_email">E-mail</label>
                        <input type="email" id="cf_email" name="email" placeholder="seu@email.com" autocomplete="email">
                        <span class="form-error">Informe um e-mail válido</span>
                    </div>
                    <div class="form-group">
                        <label for="cf_phone">Telefone</label>
                        <input type="tel" id="cf_phone" name="phone" placeholder="(00) 00000-0000" autocomplete="tel" maxlength="15">
                        <span class="form-error">Informe um telefone válido</span>
                    </div>
                    <button type="submit" class="btn btn--gradient btn--lg">
                        Enviar mensagem
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <div class="form-success" id="formSuccess">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Mensagem enviada! Entraremos em contato em breve.
                </div>
            </form>

            <!-- Informações de contato -->
            <div class="contact__info">
                <div class="contact-info-item">
                    <div class="contact-info-item__icon">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <h4>WhatsApp</h4>
                        <a href="https://wa.me/5541992050559?text=Olá! Gostaria de saber mais sobre os serviços da Inunda." target="_blank" rel="noopener">+55 (41) 99205-0559</a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-item__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <h4>E-mail</h4>
                        <a href="mailto:contato@inunda.com.br">contato@inunda.com.br</a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-item__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h4>Localização</h4>
                        <p>Curitiba, Paraná — Brasil</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-item__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <h4>Horário de atendimento</h4>
                        <p>Segunda a Sexta, das 9h às 18h</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
