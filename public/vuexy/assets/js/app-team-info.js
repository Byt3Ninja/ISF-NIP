/**
 * app-team-info.js - Fixed Clear Search + Arabic Support
 */
(function ($) {
  'use strict';

  const PLURAL = { founder: 'founders', investor: 'investors', shareholder: 'shareholders' };

  const TYPE_CONFIG = {
    founder: { teamsNo: 'Founders', fieldMap: { 
      name: 'founder_name', 
      email: 'founder_email', 
      linkedin: 'founder_linkedin',
      phone: 'founder_phone',
      type: 'founder_type',
      join_date: 'founder_join_date',
      commitment: 'founder_commitment'
    } },
    investor: { teamsNo: 'Investors', fieldMap: { name: 'investor_name', type: 'investment_type' } },
    shareholder: { teamsNo: 'Shareholders', fieldMap: { name: 'shareholder_name', role: 'shareholder_role', percentage: 'shareholder_ownership_percentage' } }
  };

  const VALIDATION_RULES = {
    founder: { name: { required: true }, email: { required: true, email: true }, type: { required: true }, phone: { intlTel: true }, linkedin: { url: true } },
    investor: { name: { required: true, errorId: 'name-investor' }, type: { errorId: 'type-investor' } },
    shareholder: { name: { required: true, errorId: 'name-shareholder' }, role: {}, percentage: { min: 0, max: 100 } }
  };

  const SERVER_ERROR_MAP = {
    founder_name: { errorId: 'name', fieldName: 'name' },
    founder_email: { errorId: 'email', fieldName: 'email' },
    founder_linkedin: { errorId: 'linkedin', fieldName: 'linkedin' },
    investor_name: { errorId: 'name-investor', fieldName: 'name' },
    investment_type: { errorId: 'type-investor', fieldName: 'type' },
    shareholder_name: { errorId: 'name-shareholder', fieldName: 'name' },
    shareholder_role: { errorId: 'role', fieldName: 'role' },
    shareholder_ownership_percentage: { errorId: 'percentage', fieldName: 'percentage' }
  };

  const appData = {
    founders: [...TEAM_INFO_CONFIG.founders],
    investors: [...TEAM_INFO_CONFIG.investors],
    shareholders: [...TEAM_INFO_CONFIG.shareholders]
  };

  const itiInstances = {};

  // Detect current language
  const LANG = document.documentElement.lang || 'en';

  /* ─────────────────────────────────────────────
     TRANSLATIONS
  ───────────────────────────────────────────── */

  const TRANSLATIONS = {
    en: {
      noResults: (term) => `No results for "${term}"`,
      noFounders: "No Founders Yet",
      noInvestors: "No Investors Listed",
      noShareholders: "No Shareholders",
      addFounder: "Add Founder",
      addInvestor: "Add Investor",
      addShareholder: "Add Shareholder",
      tip: "Tip: Use search above to filter existing members",
      noResultsDesc: "Try adjusting your search term",
      clearSearch: "Clear search",
      deleteMsg: "Are you sure you want to delete ${name}?",
      yesDelete: "Yes, delete!",
      noCancel: "No, cancel!",
      founder: "Founder",
      investor: "Investor",
      shareholder: "Shareholder",
      delete: "Delete",
       investmentTypes: {
        Angel_Investor:      'Angel Investor',
        Angel_Group:         'Angel Group',
        Venture_Capital_Firm:'Venture Capital Firm',
        Accelerator:         'Accelerator',
        Incubator:           'Incubator',
        Corporate_Investor:  'Corporate Investor',
        Family_Office:       'Family Office',
        Family:              'Family',
        Friends:             'Friends',
      }
    },
    ar: {
      noResults: (term) => `لا توجد نتائج لـ "${term}"`,
      noFounders: "لا يوجد مؤسسون بعد",
      noInvestors: "لم يتم إضافة مستثمرين",
      noShareholders: "لا يوجد مساهمون",
      addFounder: "إضافة مؤسس",
      addInvestor: "إضافة مستثمر",
      addShareholder: "إضافة مساهم",
      tip: "نصيحة: استخدم مربع البحث أعلاه لتصفية الأعضاء",
      noResultsDesc: "حاول تعديل كلمة البحث",
      clearSearch: "مسح البحث",
      deleteMsg: "هل تريد حذف ${name}؟",
      yesDelete: "نعم، حذف!",
      noCancel: "لا، إلغاء!",
      founder: "المؤسس",
      investor: "المستثمر",
      shareholder: "المساهم",
       investmentTypes: {
        Angel_Investor:      'مستثمر ملاك',
        Angel_Group:         'مجموعة ملاك',
        Venture_Capital_Firm:'شركة رأس مال مخاطر',
        Accelerator:         'مسرّع أعمال',
        Incubator:           'حاضنة أعمال',
        Corporate_Investor:  'مستثمر مؤسسي',
        Family_Office:       'مكتب عائلي',
        Family:              'عائلة',
        Friends:             'أصدقاء',
      }
    }
  };

  const t = TRANSLATIONS[LANG] || TRANSLATIONS.en;

  function translateInvestmentType(raw) {
    if (!raw) return '';
    return (t.investmentTypes && t.investmentTypes[raw]) || raw;
  }

  /* ─────────────────────────────────────────────
     UTILITIES
  ───────────────────────────────────────────── */

  function debounce(fn, delay = 180) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  function escapeHtml(s) {
    return s ? String(s).replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m])) : '';
  }

  /* ─────────────────────────────────────────────
     INITIALIZATION
  ───────────────────────────────────────────── */

  function init() {
    renderAll();
    initIntlInputs();
    initFlatpickr();
    bindEvents();
  }

  function initIntlInputs() {
    Object.keys(PLURAL).forEach(type => {
      const input = document.querySelector(`#form-${type} input[name="phone"]`);
      if (input && !itiInstances[type]) {
        itiInstances[type] = window.intlTelInput(input, {
          initialCountry: 'eg',
          separateDialCode: true,
          utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18/build/js/utils.js',
        });
      }
    });
  }

  function initFlatpickr() {
    const el = document.getElementById('join_date');
    if (el && typeof flatpickr === 'function') {
      flatpickr(el, { dateFormat: 'Y-m-d', maxDate: 'today' });
    }
  }

  /* ─────────────────────────────────────────────
     EVENTS
  ───────────────────────────────────────────── */

  function bindEvents() {
    const debouncedRenderList = debounce((type, value) => {
      renderList(type, value);
    }, 180);

    $('.search-input').on('input', function () {
      const type = $(this).data('type');
      const value = $(this).val();
      debouncedRenderList(type, value);
    });

    // Form submission & Modal resets
    Object.keys(PLURAL).forEach(type => {
      $(`#form-${type}`).on('submit', function (e) {
        e.preventDefault();
        if (validateForm(type)) saveItem(type, this);
      });

      $(`#modal-${type}`).on('hidden.bs.modal', function () {
        this.querySelector('form').reset();
        clearAllErrors(type);
      });
    });

    // Real-time validation
    $(document).on('input change blur', '.form-control, .form-select', function () {
      const $form = $(this).closest('form');
      const formId = $form.attr('id');
      if (!formId) return;

      const type = formId.replace('form-', '');
      const fieldName = $(this).attr('name');

      if (VALIDATION_RULES[type] && VALIDATION_RULES[type][fieldName]) {
        validateSingleField(type, fieldName);
      }
    });
  }

  /* ─────────────────────────────────────────────
     RENDER FUNCTIONS
  ───────────────────────────────────────────── */

  function renderAll() {
    Object.keys(PLURAL).forEach(type => renderList(type));
  }

  function getEmptyState(type, searchTerm = '') {
    const term = (searchTerm || '').trim();

    if (term) {
      // No results when searching
      return `
        <div class="col-12">
          <div class="text-center py-5 my-4 border rounded-3 border-dashed bg-light-subtle empty-state">
            <div class="avatar avatar-lg bg-card mx-auto mb-4">
              <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                <i class="ti ti-search text-dark"></i>
              </div>
            </div>
            <h5 class="mb-2 fw-semibold">${t.noResults(escapeHtml(term))}</h5>
            <p class="text-secondary mb-4">${t.noResultsDesc}</p>
            <button type="button" class="btn btn-outline-secondary btn-sm" 
                    onclick="TeamInfo.renderList('${type}')">
              <i class="ti ti-x me-1"></i>${t.clearSearch}
            </button>
          </div>
        </div>`;
    }

    // Default empty state
    const titles = {
      founder: t.noFounders,
      investor: t.noInvestors,
      shareholder: t.noShareholders
    };

    const addTexts = {
      founder: t.addFounder,
      investor: t.addInvestor,
      shareholder: t.addShareholder
    };

    const icons = {
      founder: 'ti-users',
      investor: 'ti-building-bank',
      shareholder: 'ti-users-group'
    };

    return `
      <div class="col-12">
        <div class="text-center py-5 my-4 border rounded-3 border-dashed bg-light-subtle empty-state">
          <div class="avatar avatar-lg bg-card mx-auto mb-4">
            <div class="w-100 h-100 d-flex align-items-center justify-content-center">
              <i class="ti ${icons[type]} text-dark"></i>
            </div>
          </div>
          <h5 class="mb-2 fw-semibold">${titles[type]}</h5>
          <p class="text-secondary mb-4">
            ${type === 'founder' ? 'Your founding team is the heart of your startup. Add your core team members to showcase leadership' :
              type === 'investor' ? 'Showcase your funding and investor network to build credibility' :
              'Define your ownership structure and equity distribution.'}
          </p>
          <button type="button" class="btn btn-primary waves-effect waves-light" onclick="openModal('${type}')">
            <i class="ti ti-plus me-1"></i>${addTexts[type]}
          </button>
          <div class="text-muted small mt-3 opacity-75">${t.tip}</div>
        </div>
      </div>`;
  }

  function renderList(type, searchTerm = '') {
    const plural = PLURAL[type];
    const $container = $(`#list-${plural}`);
    const term = (searchTerm || '').toLowerCase().trim();

    const filteredData = appData[plural].filter(item =>
      !term || (item.name && item.name.toLowerCase().includes(term))
    );

    $container.empty();

    if (!filteredData.length) {
      $container.html(getEmptyState(type, searchTerm));
      $container.find('.empty-state').hide().fadeIn(400);
    } else {
      const cardsHtml = filteredData.map(item => buildCard(type, item)).join('');
      $container.html(cardsHtml);
    }

    $(`#count-${plural}`).text(appData[plural].length);
  }

  function buildCard(type, item) {
    if (type === 'founder') {
      return `
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card item-card h-100 shadow-none border">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold">${escapeHtml(item.name)}</h6>
                <small class="text-muted">${escapeHtml(item.type)}</small>
              </div>
              ${buildDropdown('founder', item.id)}
            </div>
            <div class="small mb-2">
              <i class="ti ti-mail me-2"></i>${escapeHtml(item.email)}
            </div>
            <div class="small mb-2">
              <i class="ti ti-phone me-2"></i>${escapeHtml(item.phone || 'N/A')}
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
              <span class="badge bg-label-secondary text-dark">${escapeHtml(item.commitment || '')}</span>
              <small class="text-muted">${escapeHtml(item.join_date || 'N/A')}</small>
            </div>
          </div>
        </div>
      </div>`;
    }

    if (type === 'investor') {
      return `
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card item-card h-100 shadow-none border">
          <div class="card-body d-flex align-items-start">
            <div class="avatar">
              <div class="avatar-initial text-dark px-5 rounded flex-shrink-0">
                <i class="ti ti-building-bank"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="mb-0 fw-bold">${escapeHtml(item.name)}</h6>
              <small class="text-secondary d-block mb-1">
                ${escapeHtml(translateInvestmentType(item.type) || 'Standard')}
              </small>
              <small class="text-success fw-bold">${escapeHtml(item.investment_amount || '')}</small>
            </div>
            ${buildDropdown('investor', item.id)}
          </div>
        </div>
      </div>`;
    }

    if (type === 'shareholder') {
      const pct = parseFloat(item.percentage) || 0;
      return `
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card item-card h-100 shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1 fw-bold">${escapeHtml(item.name)}</h6>
                <small class="text-muted">${escapeHtml(item.role || 'Shareholder')}</small>
              </div>
              ${buildDropdown('shareholder', item.id)}
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="text-muted">Ownership</small>
              <span class="fw-semibold">${pct}%</span>
            </div>
            <div class="position-relative mb-3">
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-primary" role="progressbar" 
                     style="width:${pct}%" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </div>
      </div>`;
    }

    return '';
  }

  function buildDropdown(type, id) {
    return `
      <div class="dropdown">
        <button class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
          data-bs-toggle="dropdown" aria-expanded="false">
          <i class="ti ti-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li>
            <a class="dropdown-item" href="javascript:void(0)"
              onclick="TeamInfo.editItem('${type}', ${id})">
              <i class="ti ti-pencil me-1"></i> Edit
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="javascript:void(0)"
              onclick="TeamInfo.deleteItem('${type}', ${id})">
              <i class="ti ti-trash me-1"></i> Delete
            </a>
          </li>
        </ul>
      </div>`;
  }

  /* ─────────────────────────────────────────────
     CRUD & MODALS
  ───────────────────────────────────────────── */

  function openModal(type) {
    const $m = $(`#modal-${type}`);
    $m.find('form')[0].reset();
    $m.find('[name="id"]').val('');
    clearAllErrors(type);
    $m.modal('show');
  }

  function editItem(type, id) {
    const item = appData[PLURAL[type]].find(i => String(i.id) === String(id));
    if (!item) return;

    const $f = $(`#form-${type}`);
    clearAllErrors(type);

    Object.entries(item).forEach(([k, v]) => {
      if (k === 'phone' && itiInstances[type]) {
        itiInstances[type].setNumber(v || '');
      } else {
        $f.find(`[name="${k}"]`).val(v);
      }
    });

    $(`#modal-${type}`).modal('show');
  }

  function saveItem(type, formEl) {
    const formData = Object.fromEntries(new FormData(formEl).entries());
    if (itiInstances[type]) formData.phone = itiInstances[type].getNumber();

    const isEdit = !!formData.id;
    const payload = { ...buildPayload(type, formData), id: formData.id };
    const url = isEdit ? TEAM_INFO_CONFIG.routes.edit : TEAM_INFO_CONFIG.routes.add;

    $.ajax({
      url, method: 'POST', data: payload,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success(res) {
        toastr.success(res.state || 'Success');
        if (isEdit) {
          const idx = appData[PLURAL[type]].findIndex(i => String(i.id) === String(formData.id));
          if (idx !== -1) appData[PLURAL[type]][idx] = { ...appData[PLURAL[type]][idx], ...formData };
        } else {
          formData.id = Date.now();
          appData[PLURAL[type]].push(formData);
        }
        $(`#modal-${type}`).modal('hide');
        renderList(type);
      },
      error(xhr) {
        if (xhr.status === 422 && xhr.responseJSON.errors) {
          applyServerErrors(type, xhr.responseJSON.errors);
        } else {
          toastr.error('Error saving data');
        }
      }
    });
  }

  function deleteItem(type, id) {
    // if (!confirm(LANG === 'ar' ? 'تأكيد الحذف؟' : 'Confirm deletion?')) return;

    const name = appData[PLURAL[type]].find(i => String(i.id) === String(id)).name;

    releaseWarningAlert({
      title: `${t.delete} ${t[type]}`,
      message: `${t.deleteMsg.replace('${name}', escapeHtml(name))}`,
      confirmText: `${t.yesDelete}`,
      cancelText: `${t.noCancel}`,
      confirmColor: 'danger',
      onConfirm: () => {
        $.ajax({
          url: TEAM_INFO_CONFIG.routes.delete,
          method: 'POST',
          data: { id },
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success() {
            appData[PLURAL[type]] = appData[PLURAL[type]].filter(i => String(i.id) !== String(id));
            renderList(type);
            toastr.success(LANG === 'ar' ? 'تم الحذف' : 'Deleted');
          }
        });
      }
    });
  }

  /* ─────────────────────────────────────────────
     VALIDATION HELPERS
  ───────────────────────────────────────────── */

  function buildPayload(type, data) {
    const cfg = TYPE_CONFIG[type];
    const p = { TeamsNo: cfg.teamsNo };
    Object.entries(cfg.fieldMap).forEach(([jsKey, serverKey]) => {
      if (data[jsKey] !== undefined) p[serverKey] = data[jsKey];
    });
    return p;
  }

  function applyServerErrors(type, errors) {
    Object.entries(errors).forEach(([k, v]) => {
      const m = SERVER_ERROR_MAP[k];
      if (m) showFieldError(type, m.errorId, m.fieldName, Array.isArray(v) ? v[0] : v);
    });
  }

  function clearAllErrors(type) {
    const $f = $(`#form-${type}`);
    $f.find('.invalid-feedback').hide();
    $f.find('.is-invalid').removeClass('is-invalid');
  }

  function showFieldError(type, eid, fname, msg = null) {
    const $err = $(`#error-${eid}`);
    if (msg) $err.text(msg);
    $err.show();
    $(`#form-${type} [name="${fname}"]`).addClass('is-invalid');
  }

  function clearFieldError(type, eid, fname) {
    $(`#error-${eid}`).hide();
    $(`#form-${type} [name="${fname}"]`).removeClass('is-invalid');
  }

  function validateForm(type) {
    let valid = true;
    const rules = VALIDATION_RULES[type];
    Object.keys(rules).forEach(f => {
      if (!validateSingleField(type, f)) valid = false;
    });
    return valid;
  }

  function validateSingleField(type, field) {
    const rule = VALIDATION_RULES[type][field];
    if (!rule) return true;

    const eid = rule.errorId || field;
    const val = ($(`#form-${type} [name="${field}"]`).val() || '').trim();

    if (rule.required && !val) {
      showFieldError(type, eid, field);
      return false;
    }

    if (val) {
      if (rule.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        showFieldError(type, eid, field);
        return false;
      }
      if (rule.url && !isValidUrl(val)) {
        showFieldError(type, eid, field);
        return false;
      }
      if (rule.min !== undefined && parseFloat(val) < rule.min) {
        showFieldError(type, eid, field);
        return false;
      }
      if (rule.max !== undefined && parseFloat(val) > rule.max) {
        showFieldError(type, eid, field);
        return false;
      }
      if (rule.intlTel && itiInstances[type] && !itiInstances[type].isValidNumber()) {
        showFieldError(type, eid, field);
        return false;
      }
    }

    clearFieldError(type, eid, field);
    return true;
  }

  function isValidUrl(s) {
    try { new URL(s); return true; } catch { return false; }
  }

  // ===================== EXPORTS =====================
  window.openModal = openModal;
  window.editItem = editItem;
  window.deleteItem = deleteItem;

  window.TeamInfo = {
    openModal,
    editItem,
    deleteItem,
    renderList   // ← Fixed: Now accessible from onclick
  };

  $(function () { init(); });

}(jQuery));