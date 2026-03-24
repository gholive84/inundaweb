    </main><!-- /.adm-content -->
  </div><!-- /.adm-main -->
</div><!-- /.adm-wrap -->

<!-- ── Global AI Chat Panel ─────────────────────────────────────────────── -->
<div id="globalAiPanel" class="gai-panel" aria-hidden="true">
  <div class="gai-panel__header">
    <div class="gai-panel__title">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Assistente IA
    </div>
    <button id="globalAiClose" class="gai-panel__close" aria-label="Fechar">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="gai-panel__body" id="globalAiMessages">
    <div class="gai-msg gai-msg--ai">
      <div class="gai-msg__bubble">
        Olá! Sou seu assistente de gestão do CMS. Posso te ajudar com:
        <ul style="margin:.5rem 0 0 1rem;line-height:1.8">
          <li>📊 Estatísticas do site</li>
          <li>✍️ Criar posts</li>
          <li>📄 Listar páginas e posts</li>
          <li>👥 Consultar leads</li>
          <li>⚙️ Alterar configurações e menus</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="gai-panel__footer">
    <div class="gai-panel__input-wrap">
      <textarea id="globalAiInput" class="gai-panel__input" placeholder="Ex: Quantos posts publicados temos?" rows="1"></textarea>
      <button id="globalAiSend" class="gai-panel__send" aria-label="Enviar">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      </button>
    </div>
    <button id="globalAiClear" class="gai-panel__clear">Limpar conversa</button>
  </div>
</div>
<div id="globalAiOverlay" class="gai-overlay"></div>

<!-- Quill (carregado apenas quando há editor) -->
<?php if (defined('USE_QUILL') && USE_QUILL): ?>
<link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
<script>
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link', 'image'],
            ['clean']
        ]
    }
});

// Carrega conteúdo existente
const existingContent = document.getElementById('content').value;
if (existingContent) {
    const delta = quill.clipboard.convert({ html: existingContent });
    quill.setContents(delta, 'silent');
}

// Injeta HTML no hidden input antes do submit
document.querySelector('form').addEventListener('submit', () => {
    document.getElementById('content').value = quill.getSemanticHTML();
});
</script>
<?php endif; ?>

<script src="<?= CMS_URL ?>/assets/js/admin.js"></script>
</body>
</html>
