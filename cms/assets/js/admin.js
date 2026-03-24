// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm || 'Tem certeza?')) e.preventDefault();
    });
});

// Auto-generate slug from title
const titleInput = document.getElementById('post_title');
const slugInput  = document.getElementById('post_slug');
if (titleInput && slugInput && !slugInput.dataset.locked) {
    titleInput.addEventListener('input', () => {
        slugInput.value = titleInput.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '');
    });
    slugInput.addEventListener('input', () => { slugInput.dataset.locked = '1'; });
}

// Auto-generate slug for content types / items
const typeNameInput = document.getElementById('type_name');
const typeSlugInput = document.getElementById('type_slug');
if (typeNameInput && typeSlugInput && !typeSlugInput.dataset.locked) {
    typeNameInput.addEventListener('input', () => {
        typeSlugInput.value = typeNameInput.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '');
    });
    typeSlugInput.addEventListener('input', () => { typeSlugInput.dataset.locked = '1'; });
}

const itemTitleInput = document.getElementById('item_title');
const itemSlugInput  = document.getElementById('item_slug');
if (itemTitleInput && itemSlugInput && !itemSlugInput.dataset.locked) {
    itemTitleInput.addEventListener('input', () => {
        itemSlugInput.value = itemTitleInput.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-|-$/g, '');
    });
    itemSlugInput.addEventListener('input', () => { itemSlugInput.dataset.locked = '1'; });
}

// Tabs
document.querySelectorAll('.tab[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        const target = tab.dataset.tab;
        document.querySelectorAll('.tab[data-tab]').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(target)?.classList.add('active');
    });
});

// Dynamic field builder (content types)
const fieldsBuilder = document.getElementById('fieldsBuilder');
const addFieldBtn   = document.getElementById('addField');
if (fieldsBuilder && addFieldBtn) {
    addFieldBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'field-row';
        row.innerHTML = `
            <div class="form-group" style="flex:1">
                <label>Label</label>
                <input type="text" name="fields[label][]" placeholder="ex: Preço" required>
            </div>
            <div class="form-group" style="flex:1">
                <label>Chave (key)</label>
                <input type="text" name="fields[key][]" placeholder="ex: preco" required>
            </div>
            <div class="form-group" style="width:140px">
                <label>Tipo</label>
                <select name="fields[type][]">
                    <option value="text">Texto</option>
                    <option value="textarea">Área de texto</option>
                    <option value="number">Número</option>
                    <option value="url">URL</option>
                    <option value="image">Imagem (upload)</option>
                    <option value="file">Arquivo (upload)</option>
                    <option value="date">Data (calendário)</option>
                    <option value="select">Select</option>
                    <option value="checkbox">Checkbox</option>
                </select>
            </div>
            <button type="button" class="btn btn-danger btn-sm btn-remove-field" style="margin-bottom:1px">✕</button>
        `;
        fieldsBuilder.appendChild(row);
        // auto-generate key from label
        const labelInp = row.querySelector('input[name*="[label]"]');
        const keyInp   = row.querySelector('input[name*="[key]"]');
        labelInp.addEventListener('input', () => {
            if (!keyInp.dataset.locked) {
                keyInp.value = labelInp.value.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g,'');
            }
        });
        keyInp.addEventListener('input', () => keyInp.dataset.locked = '1');
    });
    fieldsBuilder.addEventListener('click', e => {
        if (e.target.classList.contains('btn-remove-field')) {
            e.target.closest('.field-row').remove();
        }
    });
}

// Lead status update (inline select)
document.querySelectorAll('.lead-status-select').forEach(sel => {
    sel.addEventListener('change', async () => {
        const id = sel.dataset.id;
        const status = sel.value;
        await fetch('/cms/leads/update-status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&status=${status}&csrf_token=${document.querySelector('meta[name=csrf]')?.content ?? ''}`
        });
    });
});

// ── File Upload ──────────────────────────────────────────────────────────────
function uploadFile(inputId, previewId) {
    const picker = document.createElement('input');
    picker.type = 'file';
    picker.accept = 'image/*,.pdf,.doc,.docx';
    picker.style.display = 'none';
    document.body.appendChild(picker);
    picker.click();
    picker.addEventListener('change', async () => {
        if (!picker.files.length) return;
        const file = picker.files[0];
        const csrf = document.querySelector('meta[name=csrf]')?.content ?? '';
        const fd = new FormData();
        fd.append('file', file);
        fd.append('csrf_token', csrf);

        const btn = document.querySelector(`[data-upload-for="${inputId}"]`);
        if (btn) { btn.disabled = true; btn.textContent = 'Enviando…'; }

        try {
            const res = await fetch('/cms/upload.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.ok) {
                const inp = document.getElementById(inputId);
                if (inp) {
                    inp.value = data.url;
                    inp.dispatchEvent(new Event('input'));
                }
                if (previewId) {
                    const img = document.getElementById(previewId);
                    if (img) { img.src = data.url; img.style.display = 'block'; }
                }
            } else {
                alert('Erro no upload: ' + (data.error || 'Falha desconhecida'));
            }
        } catch (e) {
            alert('Erro de conexão no upload.');
        }
        if (btn) { btn.disabled = false; btn.textContent = '↑ Upload'; }
        document.body.removeChild(picker);
    });
}

// Attach upload buttons
document.querySelectorAll('[data-upload-for]').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        const inputId   = btn.dataset.uploadFor;
        const previewId = btn.dataset.uploadPreview ?? null;
        uploadFile(inputId, previewId);
    });
});

// Image URL preview
document.querySelectorAll('input[data-preview]').forEach(inp => {
    inp.addEventListener('input', () => {
        const target = document.getElementById(inp.dataset.preview);
        if (target) {
            target.src = inp.value;
            target.style.display = inp.value ? 'block' : 'none';
        }
    });
});

// Mobile sidebar toggle
const hamburger = document.getElementById('admHamburger');
const sidebar   = document.querySelector('.adm-sidebar');
if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
    document.addEventListener('click', e => {
        if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    });
}
