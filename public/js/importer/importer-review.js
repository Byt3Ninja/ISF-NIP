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
        if (saved.organization_id) {
            orgSelect.value = String(saved.organization_id);
        } else if (orgs.length === 1) {
            orgSelect.value = String(orgs[0].id);
            orgSelect.disabled = true;
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

            // PR 7 will call the execute endpoint here.
            alert('Start Import hooks the execute endpoint in PR 7.');
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }
})();
