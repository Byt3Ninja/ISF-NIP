/* global window, document, fetch */
(function () {
    'use strict';

    const t        = window.importerT;
    const state    = window.importerState;
    const routes   = window.importerRoutes;
    const csrf     = window.importerCsrf;
    const schema   = window.importerSchema || {};
    const sections = window.importerSections || {};

    const DONT_IMPORT = '__dont_import__';

    const sectionsEl = document.getElementById('importer-mapping-sections');
    const countEl    = document.getElementById('importer-mapping-count');
    const totalEl    = document.getElementById('importer-mapping-total');
    const autoBtn    = document.getElementById('importer-automap-btn');
    const autoMsgEl  = document.getElementById('importer-automap-message');
    const backBtn    = document.getElementById('importer-mapping-back');
    const nextBtn    = document.getElementById('importer-mapping-next');

    const sectionLabels = {
        program:     'Program Details',
        founders:    'Founders Details',
        co_founders: 'Co-Founder Details',
        company:     'Company Details',
        employees:   'Employee Statistics',
        financials:  'Revenue & Financials',
    };

    const sectionLabelsAr = {
        program:     'تفاصيل البرنامج',
        founders:    'تفاصيل المؤسسين',
        co_founders: 'تفاصيل الشركاء المؤسسين',
        company:     'تفاصيل الشركة',
        employees:   'إحصائيات الموظفين',
        financials:  'الإيرادات والمالية',
    };

    window.importerMapping = { renderFor, getCurrentMappings };

    function renderFor(sessionState) {
        if (!sessionState || !sessionState.headers) return;

        var used_labels = $('html').attr('lang') === 'ar' ? sectionLabelsAr : sectionLabels;
        sectionsEl.innerHTML = '';
        const sectionOrder = Object.keys(sections);
        sectionOrder.forEach((section, sIdx) => {
            const fields = sections[section] || {};
            if (Object.keys(fields).length === 0) return;

            const card = document.createElement('div');
            card.className = 'accordion-item';
            card.innerHTML = `
                <h2 class="accordion-header">
                    <button class="accordion-button ${sIdx === 0 ? '' : 'collapsed'}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#importer-section-${section}">
                        ${escapeHtml(used_labels[section] || section)}
                    </button>
                </h2>
                <div id="importer-section-${section}" class="accordion-collapse collapse ${sIdx === 0 ? 'show' : ''}">
                    <div class="accordion-body" data-section="${section}"></div>
                </div>
            `;
            sectionsEl.appendChild(card);

            const body = card.querySelector('.accordion-body');
            Object.values(fields).forEach((field) => {
                body.appendChild(renderFieldRow(field, sessionState.headers, sessionState.mappings || {}));
            });
        });

        totalEl.textContent = String(Object.keys(schema).length);
        updateCount(sessionState.mappings || {});
    }

    function renderFieldRow(field, headers, currentMappings) {
        const locale = t.locale;
        const label  = locale === 'ar' ? (field.label_ar || field.label_en) : field.label_en;
        const row    = document.createElement('div');
        row.className = 'importer-field-row row g-2 align-items-center py-2 border-bottom';
        row.dataset.fieldKey = field.key;

        const selectId = `importer-field-${field.key}`;
        const current  = currentMappings[field.key];
        const currentValue = (current === null || current === undefined) ? DONT_IMPORT : String(current);

        const options = [`<option value="${DONT_IMPORT}">${escapeHtml(t.get('ui.dont_import'))}</option>`]
            .concat(headers.map((h, i) => `<option value="${i}" ${String(i) === currentValue ? 'selected' : ''}>${escapeHtml(h)}</option>`))
            .join('');

        const requiredMark = field.required ? '<span class="text-danger" aria-label="required">*</span>' : '';

        row.innerHTML = `
            <div class="col-md-5">
                <label for="${selectId}" class="form-label mb-0">
                    ${escapeHtml(label)} ${requiredMark}
                </label>
            </div>
            <div class="col-md-5">
                <select id="${selectId}" class="form-select form-select-sm" data-field-key="${field.key}">
                    ${options}
                </select>
            </div>
            <div class="col-md-2">
                <div class="importer-field-warning text-warning small d-none" role="alert"></div>
            </div>
        `;

        row.querySelector('select').addEventListener('change', (e) => {
            const val = e.target.value;
            const mappings = getCurrentMappings();
            mappings[field.key] = val === DONT_IMPORT ? null : Number(val);
            const activeUuid = state.getActive();
            const saved = state.load(activeUuid) || {};
            saved.mappings = mappings;
            state.save(activeUuid, saved);
            updateCount(mappings);
            clearWarningFor(field.key);
        });

        return row;
    }

    function getCurrentMappings() {
        const out = {};
        document.querySelectorAll('#importer-mapping-sections select[data-field-key]').forEach((sel) => {
            const v = sel.value;
            out[sel.dataset.fieldKey] = v === DONT_IMPORT ? null : Number(v);
        });
        return out;
    }

    function updateCount(mappings) {
        const mapped = Object.values(mappings).filter((v) => v !== null && v !== undefined).length;
        countEl.textContent = String(mapped);
    }

    function setWarningFor(fieldKey, messageKey) {
        const row = document.querySelector(`.importer-field-row[data-field-key="${fieldKey}"]`);
        if (!row) return;
        const w = row.querySelector('.importer-field-warning');
        w.textContent = t.get(messageKey);
        w.classList.remove('d-none');
        row.classList.add('importer-field-warned');
    }
    function clearWarningFor(fieldKey) {
        const row = document.querySelector(`.importer-field-row[data-field-key="${fieldKey}"]`);
        if (!row) return;
        row.querySelector('.importer-field-warning').classList.add('d-none');
        row.classList.remove('importer-field-warned');
    }
    function clearAllWarnings() {
        document.querySelectorAll('.importer-field-row').forEach((r) => {
            r.querySelector('.importer-field-warning').classList.add('d-none');
            r.classList.remove('importer-field-warned');
        });
    }

    if (autoBtn) {
        autoBtn.addEventListener('click', async () => {
            const uuid = state.getActive();
            if (!uuid) return;
            const r = await fetch(`${routes.automap}/${uuid}/automap`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            const body = await r.json();
            if (!r.ok || !body.success) return;

            Object.entries(body.data.mappings).forEach(([fieldKey, headerIdx]) => {
                const sel = document.querySelector(`select[data-field-key="${fieldKey}"]`);
                if (!sel) return;
                sel.value = headerIdx === null ? DONT_IMPORT : String(headerIdx);
            });

            const saved = state.load(uuid) || {};
            saved.mappings = body.data.mappings;
            state.save(uuid, saved);
            updateCount(body.data.mappings);

            autoMsgEl.classList.remove('d-none', 'alert-success', 'alert-warning');
            autoMsgEl.classList.add(body.data.matched_count > 0 ? 'alert-success' : 'alert-warning');
            autoMsgEl.textContent = t.pickLocalised(body.data);

            clearAllWarnings();
            body.data.unmatched_required.forEach((k) => setWarningFor(k, 'ui.automap_messages.required_missing'));
            body.data.unmatched_optional.forEach((k) => setWarningFor(k, 'ui.automap_messages.optional_missing'));
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            document.getElementById('importer-step-mapping').classList.add('d-none');
            document.getElementById('importer-step-upload').classList.remove('d-none');
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', async () => {
            clearRowErrors();
            clearConflicts();
            const uuid = state.getActive();
            if (!uuid) return;

            const mappings = getCurrentMappings();
            const missingRequired = Object.entries(schema)
                .filter(([_, f]) => f.required)
                .filter(([key]) => mappings[key] === null || mappings[key] === undefined)
                .map(([key]) => key);
            if (missingRequired.length > 0) {
                clearAllWarnings();
                missingRequired.forEach((k) => setWarningFor(k, 'ui.automap_messages.required_missing'));
                return;
            }

            nextBtn.disabled = true;
            try {
                // Step A: row validation
                const vr = await fetch(`${routes.validate}/${uuid}/validate`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mappings }),
                });
                const vbody = await vr.json();
                if (!vr.ok || !vbody.success) {
                    renderRowErrors([{ row: 0, field_key: null, message_en: 'Validation failed', message_ar: 'فشل التحقق' }]);
                    return;
                }
                if (!vbody.data.valid) {
                    renderRowErrors(vbody.data.row_errors, vbody.data.totals);
                    return;
                }

                const saved = state.load(uuid) || {};
                saved.status   = 'validated';
                saved.mappings = mappings;
                state.save(uuid, saved);

                // Step B: conflict detection requires organization_id.
                // Minimal approach for PR 5: fetch /organizations and use the first.
                let orgId = null;
                try {
                    const orgResp = await fetch(routes.organizations, { headers: { 'Accept': 'application/json' } });
                    if (orgResp.ok) {
                        const orgBody = await orgResp.json();
                        const orgs = (orgBody && orgBody.data && orgBody.data.organizations) || (orgBody && orgBody.organizations) || [];
                        orgId = orgs.length ? orgs[0].id : null;
                    }
                } catch (_) { /* noop */ }

                if (!orgId) {
                    alert(t.get('errors.organization_required'));
                    return;
                }

                const cr = await fetch(`${routes.conflicts}/${uuid}/conflicts`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ organization_id: orgId }),
                });
                const cbody = await cr.json();
                if (!cr.ok || !cbody.success) return;

                if ((cbody.data.total || 0) === 0) {
                    await acceptAndAdvance(uuid, orgId);
                    return;
                }

                renderConflicts(cbody.data.warnings);
                showConflictsModal(cbody.data.warnings, uuid, orgId);
            } finally {
                nextBtn.disabled = false;
            }
        });
    }

    function clearConflicts() {
        const box = document.getElementById('importer-conflicts');
        const list = document.getElementById('importer-conflicts-list');
        if (box) box.classList.add('d-none');
        if (list) list.innerHTML = '';
    }

    function renderConflicts(warnings) {
        const box = document.getElementById('importer-conflicts');
        const list = document.getElementById('importer-conflicts-list');
        if (!box || !list) return;
        list.innerHTML = warnings.map((w) => `<li>${escapeHtml(t.pickLocalised(w))}</li>`).join('');
        box.classList.remove('d-none');
    }

    function showConflictsModal(warnings, uuid, orgId) {
        const modalList = document.getElementById('importer-conflicts-modal-list');
        if (modalList) {
            modalList.innerHTML = warnings.map((w) => `<li>${escapeHtml(t.pickLocalised(w))}</li>`).join('');
        }
        const acceptBtn = document.getElementById('importer-conflicts-accept');
        if (acceptBtn) {
            acceptBtn.onclick = async () => { await acceptAndAdvance(uuid, orgId); };
        }
        if (window.jQuery) {
            window.jQuery('#importer-conflicts-modal').modal('show');
        } else {
            const m = document.getElementById('importer-conflicts-modal');
            if (m) { m.classList.add('show'); m.style.display = 'block'; }
        }
    }

    async function acceptAndAdvance(uuid, orgId) {
        await fetch(`${routes.conflicts}/${uuid}/conflicts`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ organization_id: orgId, accept: true }),
        });
        const saved = state.load(uuid) || {};
        saved.status = 'conflicts_reviewed';
        saved.organization_id = orgId;
        state.save(uuid, saved);

        if (window.jQuery) { window.jQuery('#importer-conflicts-modal').modal('hide'); }

        // Advance to Step 3
        document.getElementById('importer-step-mapping').classList.add('d-none');
        document.getElementById('importer-step-review').classList.remove('d-none');
        if (window.importerReview && window.importerReview.enter) {
            await window.importerReview.enter();
        }
    }

    function clearRowErrors() {
        const box = document.getElementById('importer-row-errors');
        if (!box) return;
        document.getElementById('importer-row-errors-body').innerHTML = '';
        document.getElementById('importer-row-errors-summary').textContent = '';
        box.classList.add('d-none');
    }

    function renderRowErrors(errors, totals) {
        const box  = document.getElementById('importer-row-errors');
        const body = document.getElementById('importer-row-errors-body');
        const summary = document.getElementById('importer-row-errors-summary');
        if (!box || !body) return;
        body.innerHTML = '';
        errors.forEach((e) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${e.row || ''}</td>
                <td>${e.field_key ? escapeHtml(e.field_key) : ''}</td>
                <td>${escapeHtml(t.pickLocalised(e))}</td>
            `;
            body.appendChild(tr);
        });
        if (totals && summary) {
            summary.textContent = `${totals.blocking} blocking / ${totals.warning} warning across ${totals.rows} rows`;
        }
        box.classList.remove('d-none');
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }
})();
