/* global window, document, fetch */
(function () {
    'use strict';

    const t      = window.importerT;
    const state  = window.importerState;
    const routes = window.importerRoutes;
    const csrf   = window.importerCsrf;
    const schema = window.importerSchema || {};

    const orgSelect   = document.getElementById('importer-review-org');
    const orgError    = document.getElementById('importer-review-org-error');
    const mappingsEl  = document.getElementById('importer-review-mappings');
    const previewHead = document.getElementById('importer-review-preview-head');
    const previewBody = document.getElementById('importer-review-preview-body');
    const backBtn     = document.getElementById('importer-review-back');
    const startBtn    = document.getElementById('importer-review-start');

    window.importerReview = { enter };

    async function enter() {
        const uuid = state.getActive();
        if (!uuid) return;

        // 1. Load organization list
        const oresp = await fetch(routes.organizations, { headers: { 'Accept': 'application/json' } });
        const obody = await oresp.json();
        const orgs = obody && obody.data && obody.data.organizations ? obody.data.organizations : [];
        orgSelect.innerHTML = '<option value="">—</option>' +
            orgs.map(function (o) {
                const label = t.locale === 'ar' ? (o.name_ar || o.name_en) : (o.name_en || o.name_ar);
                return '<option value="' + o.id + '">' + escapeHtml(label) + '</option>';
            }).join('');

        // Preselect from localStorage if available
        const saved = state.load(uuid) || {};
        if (orgs.length === 1) {
            // Single-org user (e.g., Org Manager): auto-select and hide the dropdown row.
            orgSelect.value = String(orgs[0].id);
            orgSelect.disabled = true;
            const orgRow = document.getElementById('importer-review-org-row');
            if (orgRow) orgRow.classList.add('d-none');
        } else if (saved.organization_id) {
            orgSelect.value = String(saved.organization_id);
        }

        // 2. Render mapping summary
        renderMappingSummary(saved.mappings || {});

        // 3. Load preview
        const presp = await fetch(routes.preview + '/' + uuid + '/preview?rows=3', { headers: { 'Accept': 'application/json' } });
        const pbody = await presp.json();
        if (pbody.success) renderPreview(pbody.data);
    }

    function renderMappingSummary(mappings) {
        const groups = {};
        Object.values(schema).forEach(function (f) {
            const sec = f.section;
            if (!groups[sec]) groups[sec] = [];
            groups[sec].push(f);
        });

        mappingsEl.innerHTML = Object.entries(groups).map(function (entry) {
            const section = entry[0];
            const fields = entry[1];
            const rows = fields.map(function (f) {
                const idx = mappings[f.key];
                const headerLabel = (idx === null || idx === undefined)
                    ? '<span class="text-muted fst-italic">' + escapeHtml(t.get('ui.dont_import')) + '</span>'
                    : escapeHtml(getHeaderName(idx));
                const label = t.locale === 'ar' ? f.label_ar : f.label_en;
                return '<tr>' +
                    '<td style="width: 45%;">' + escapeHtml(label) + (f.required ? ' <span class="text-danger">*</span>' : '') + '</td>' +
                    '<td>' + headerLabel + '</td>' +
                    '</tr>';
            }).join('');
            return '<div class="card mb-3">' +
                '<div class="card-header py-2"><strong>' + escapeHtml(section) + '</strong></div>' +
                '<div class="card-body p-0">' +
                '<table class="table table-sm mb-0">' + rows + '</table>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    function getHeaderName(headerIdx) {
        const uuid = state.getActive();
        const saved = state.load(uuid) || {};
        return (saved.headers || [])[headerIdx] || ('#' + headerIdx);
    }

    function renderPreview(data) {
        const cols = data.columns || [];
        const rows = data.rows || [];
        previewHead.innerHTML = '<tr>' + cols.map(function (c) {
            const label = t.locale === 'ar' ? c.label_ar : c.label_en;
            return '<th>' + escapeHtml(label) + '</th>';
        }).join('') + '</tr>';
        previewBody.innerHTML = rows.map(function (r) {
            return '<tr>' + cols.map(function (c) {
                return '<td>' + escapeHtml(r[c.field_key] || '') + '</td>';
            }).join('') + '</tr>';
        }).join('');
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            document.getElementById('importer-step-review').classList.add('d-none');
            document.getElementById('importer-step-mapping').classList.remove('d-none');
        });
    }

    if (startBtn) {
        startBtn.addEventListener('click', async function () {
            orgError.classList.add('d-none');
            const orgId = Number(orgSelect.value || 0);
            if (!orgId) {
                orgError.textContent = t.get('errors.organization_required');
                orgError.classList.remove('d-none');
                orgSelect.classList.add('is-invalid');
                orgSelect.focus();
                return;
            }
            orgSelect.classList.remove('is-invalid');

            const uuid = state.getActive();
            if (!uuid) return;

            startBtn.disabled = true;
            try {
                const resp = await fetch(routes.execute + '/' + uuid + '/execute', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ organization_id: orgId, accepted_warnings: true })
                });
                const body = await resp.json();
                if (!resp.ok || !body.success) {
                    const msg = (body && body.errors && body.errors[0])
                        ? (t.locale === 'ar' ? body.errors[0].message_ar : body.errors[0].message_en)
                        : 'Error';
                    alert(msg);
                    return;
                }
                renderResult(body.data);
            } catch (e) {
                alert(e.message || 'Import failed');
            } finally {
                startBtn.disabled = false;
            }
        });
    }

    function renderResult(data) {
        const panel = document.getElementById('importer-result');
        if (!panel) return;
        panel.classList.remove('d-none');

        const banner = document.getElementById('importer-result-banner');
        const totals = data.totals || {};
        const cls = (totals.failed === 0 && totals.skipped === 0)
            ? 'alert-success'
            : (totals.imported === 0 ? 'alert-danger' : 'alert-warning');
        banner.className = 'alert ' + cls;
        banner.textContent = t.locale === 'ar' ? data.status_message_ar : data.status_message_en;

        document.getElementById('importer-result-processed').textContent = totals.processed || 0;
        document.getElementById('importer-result-imported').textContent  = totals.imported  || 0;
        document.getElementById('importer-result-skipped').textContent   = totals.skipped   || 0;
        document.getElementById('importer-result-failed').textContent    = totals.failed    || 0;

        const list = document.getElementById('importer-result-list');
        const rows = [].concat(data.failed_rows || [], data.skipped_rows || []);
        list.innerHTML = rows.map(function (r) {
            const msg = t.locale === 'ar' ? r.reason_ar : r.reason_en;
            return '<li>Row ' + r.row + ': ' + escapeHtml(msg) + '</li>';
        }).join('');
    }

    const startNewBtn = document.getElementById('importer-start-new');
    if (startNewBtn) {
        startNewBtn.addEventListener('click', async function () {
            const uuid = state.getActive();
            if (uuid) {
                try {
                    await fetch(routes.discardSession + '/' + uuid, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    });
                } catch (e) { /* ignore */ }
                state.clear && state.clear(uuid);
                state.clearActive && state.clearActive();
            }
            window.location.reload();
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }
})();
