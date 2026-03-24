    </main><!-- /.adm-content -->
  </div><!-- /.adm-main -->
</div><!-- /.adm-wrap -->

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
