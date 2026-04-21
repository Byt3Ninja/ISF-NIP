/* global window, document, fetch, FormData */
(function () {
    'use strict';

    const t      = window.importerT;
    const state  = window.importerState;
    const routes = window.importerRoutes;
    const csrf   = window.importerCsrf;

    const dropZone    = document.getElementById('importer-drop-zone');
    const fileInput   = document.getElementById('importer-file-input');
    const errorsEl    = document.getElementById('importer-upload-errors');
    const infoEl      = document.getElementById('importer-uploaded-info');
    const fileNameEl  = document.getElementById('importer-file-name');
    const rowCountEl  = document.getElementById('importer-row-count');
    const replaceBtn  = document.getElementById('importer-replace-btn');

    let activeUuid = state.getActive();

    // Allow drop OR click to open file picker
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') fileInput.click(); });
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files && e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) handleFile(fileInput.files[0]);
    });

    replaceBtn.addEventListener('click', async () => {
        if (!activeUuid) return;
        await fetch(`${routes.deleteFile}/${activeUuid}/file`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        resetUi();
    });

    async function ensureSession() {
        if (activeUuid) return;
        const r = await fetch(routes.createSession, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const body = await r.json();
        if (!r.ok || !body.success) throw body;
        activeUuid = body.data.session_id;
        state.setActive(activeUuid);
    }

    async function uploadTo(uuid, file) {
        const form = new FormData();
        form.append('file', file);
        const up = await fetch(`${routes.uploadFile}/${uuid}/file`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: form,
        });
        return { status: up.status, body: await up.json() };
    }

    function isStaleSession(status, body) {
        if (status !== 409 && status !== 404) return false;
        const errs = body && body.errors ? body.errors : [];
        return errs.some(e => e.code === 'invalid_state_transition' || e.code === 'session_not_found');
    }

    async function handleFile(file) {
        clearErrors();
        try {
            await ensureSession();

            let { status, body } = await uploadTo(activeUuid, file);

            // If the cached session is terminal/missing, drop it and retry once with a fresh session.
            if (isStaleSession(status, body)) {
                state.clear(activeUuid);
                state.clearActive();
                activeUuid = null;
                await ensureSession();
                ({ status, body } = await uploadTo(activeUuid, file));
            }

            if (status < 200 || status >= 300 || !body.success) {
                renderErrors(body.errors || [], body.data || {});
                return;
            }

            // Success: persist state locally, show info
            state.save(activeUuid, {
                session_id:        body.data.session_id,
                status:            body.data.status,
                original_filename: body.data.original_filename,
                headers:           body.data.headers,
                row_count:         body.data.row_count,
                mappings:          {},
            });
            fileNameEl.textContent = body.data.original_filename || '';
            rowCountEl.textContent = String(body.data.row_count ?? 0);
            infoEl.classList.remove('d-none');
            dropZone.classList.add('d-none');
        } catch (err) {
            console.error(err);
            renderErrors([{
                code: 'unexpected',
                message_en: 'An unexpected error occurred. Please try again.',
                message_ar: 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.',
            }], {});
        }
    }

    function clearErrors() {
        errorsEl.innerHTML = '';
        errorsEl.classList.add('d-none');
    }

    function renderErrors(errors, data) {
        errorsEl.classList.remove('d-none');
        const items = errors.map(e => `<li>${escapeHtml(t.pickLocalised(e))}</li>`).join('');
        const name = data.original_filename
            ? `<div class="small text-muted mb-2">${escapeHtml(data.original_filename)}</div>`
            : '';
        errorsEl.innerHTML = `
            <div class="alert alert-danger">
                ${name}
                <ul class="mb-0 ps-3">${items}</ul>
            </div>
        `;
    }

    function resetUi() {
        state.clear(activeUuid);
        state.clearActive();
        activeUuid = null;
        clearErrors();
        infoEl.classList.add('d-none');
        dropZone.classList.remove('d-none');
        fileInput.value = '';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    const nextToMapping = document.getElementById('importer-next-to-mapping');
    if (nextToMapping) {
        nextToMapping.addEventListener('click', () => {
            const uuid = state.getActive();
            if (!uuid) return;
            const saved = state.load(uuid);
            if (!saved) return;

            document.getElementById('importer-step-upload').classList.add('d-none');
            document.getElementById('importer-step-mapping').classList.remove('d-none');

            if (window.importerMapping && window.importerMapping.renderFor) {
                window.importerMapping.renderFor(saved);
            }
        });
    }
})();
