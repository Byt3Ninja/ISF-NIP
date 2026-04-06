/**
 * cycles/registered.js
 * Manages the "Registered Startups" admin page:
 *  - DataTable init + CSV/Excel export
 *  - Row / bulk checkbox selection with tag strip
 *  - Single & bulk "Change Step" modal
 *  - Single & bulk "Mark Alumni" action
 *  - Cycle CRUD (DoChange)
 *  - Date-range picker init
 */

/* ─────────────────────────────────────────────
   Helpers
───────────────────────────────────────────── */
const cfg = window.PageConfig;        // injected by the blade view
const i18n = cfg.i18n;

function ajaxSetup() {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': cfg.csrfToken } });
}

function releaseSuccess(message) {
  if (typeof releaseSuccessAlert === 'function') {
    releaseSuccessAlert({ title: i18n.success, message });
  }
}

function releaseFailure(message) {
  if (typeof releaseFailureAlert === 'function') {
    releaseFailureAlert({ title: i18n.error, message });
  }
}

/* ─────────────────────────────────────────────
   State
───────────────────────────────────────────── */
/** @type {Object.<string, {name:string, userId:string, atAlumniStep:boolean}>} */
let selectedStartups = {};
let currentModalCtx = 'bulk';   // 'bulk' | 'single'
let singleCompanyData = null;

/* ─────────────────────────────────────────────
   DataTable
───────────────────────────────────────────── */
let table;

function initDataTable() {
  table = $('#startupsTable').DataTable({
    ...getSharedDataTableConfig({ title: 'Registered', descIndex: 0, actionsIndex: 6, withoutFilter: true }),
    dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-lg-nowrap align-items-center"l>'
      + '<"col-12 col-md-3 d-flex justify-content-center justify-content-lg-end"f>>'
      + 't<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
  });

  $('#export-csv').on('click', e => { e.preventDefault(); table.button('.buttons-csv').trigger(); });
  $('#export-excel').on('click', e => { e.preventDefault(); table.button('.buttons-excel').trigger(); });
}

/* ─────────────────────────────────────────────
   Selection / Tags
───────────────────────────────────────────── */
function updateSelectedTags() {
  const count = Object.keys(selectedStartups).length;
  const tagsEl = $('#selectedStartupsTags');
  const bulkAlumniOk = Object.values(selectedStartups).some(d => d.atAlumniStep && !d.isAlumni);

  $('#selected-count').text(count);
  $('#selectedCount').text(count);

  if (count > 0) {
    $('#clearAllSelections').removeClass('d-none');
    tagsEl.empty();

    $.each(selectedStartups, (id, data) => {
      tagsEl.append(
        $(`<span class="badge bg-label-primary d-flex align-items-center gap-1">
            <span>${data.name}</span>
            <i class="ti ti-x cursor-pointer remove-tag"
               data-company-id="${id}" style="font-size:14px;"></i>
           </span>`)
      );
    });

    $('#bulkChangeStepBtn').prop('disabled', false);
    $('#bulkMarkAlumniBtn').prop('disabled', !bulkAlumniOk);
  } else {
    $('#clearAllSelections').addClass('d-none');
    tagsEl.empty().append(
      $(`<span class="text-muted fst-italic">${i18n.noStartupsSelected}.</span>`)
    );
    $('#bulkChangeStepBtn').prop('disabled', true);
    $('#bulkMarkAlumniBtn').prop('disabled', true);
  }
}

function bindSelectionEvents() {
  // Select-all
  $('.checkAll').on('change', function () {
    $('.chboxcyclesReg').prop('checked', $(this).prop('checked')).trigger('change');
  });

  // Individual rows
  $(document).on('change', '.chboxcyclesReg', function () {
    const id = $(this).data('company-id');
    const name = $(this).data('company-name');
    const userId = $(this).data('user-id');
    const atAlumniStep = $(this).data('at-alumni-step') === 1 || $(this).data('at-alumni-step') === '1';
    const isAlumni = $(this).data('is-alumni') === 1 || $(this).data('is-alumni') === '1';

    if ($(this).prop('checked')) {
      selectedStartups[id] = { name, userId, atAlumniStep, isAlumni };
    } else {
      delete selectedStartups[id];
      $('.checkAll').prop('checked', false);
    }
    updateSelectedTags();
  });

  // Remove tag badge
  $(document).on('click', '.remove-tag', function () {
    const id = $(this).data('company-id');
    delete selectedStartups[id];
    $(`.chboxcyclesReg[data-company-id="${id}"]`).prop('checked', false);
    $('.checkAll').prop('checked', false);
    updateSelectedTags();
  });

  // Clear all
  $('#clearAllSelections').on('click', () => {
    selectedStartups = {};
    $('.chboxcyclesReg, .checkAll').prop('checked', false);
    updateSelectedTags();
  });
}

/* ─────────────────────────────────────────────
   Change Step – modal
───────────────────────────────────────────── */
function bindChangeStepEvents() {
  // Bulk open
  $('#bulkChangeStepBtn').on('click', () => {
    currentModalCtx = 'bulk';
    singleCompanyData = null;
    $('#modalStepSelect').val('');
    $('#singleCompanyInfo').hide();
    $('#affectedCompaniesInfo').show();
    updateSelectedTags();
  });

  // Single row open
  $(document).on('click', '.change-step-single', function () {
    currentModalCtx = 'single';
    singleCompanyData = {
      id: $(this).data('company-id'),
      name: $(this).data('company-name'),
      userName: $(this).data('user-name'),
      currentStep: $(this).data('current-step'),
      currentStepTitle: $(this).data('current-step-title'),
    };

    $('#modalStepSelect').val(singleCompanyData.currentStep);
    $('#selectedCount').text('1');
    $('#singleCompanyName').text(singleCompanyData.name);
    $('#singleCompanyUser').text(singleCompanyData.userName);
    $('#singleCompanyCurrentStep').text(singleCompanyData.currentStepTitle);
    $('#singleCompanyInfo').show();
    $('#affectedCompaniesInfo').hide();

    $('#changeStepModal').modal('show');
  });

  // Confirm
  $('#confirmStepChange').on('click', () => {
    const selectedStep = $('#modalStepSelect').val();
    if (!selectedStep) { alert(i18n.pleaseSelectStep); return; }

    const companies = currentModalCtx === 'bulk'
      ? Object.keys(selectedStartups)
      : (singleCompanyData ? [singleCompanyData.id] : []);

    if (!companies.length) { alert(i18n.noStartupsSelected); return; }

    changestep(companies, selectedStep);
  });
}

/* ─────────────────────────────────────────────
   Mark Alumni – single row
───────────────────────────────────────────── */
function bindMarkAlumniSingle() {
  $(document).on('click', '.mark-alumni-btn', function () {
    const companyId = $(this).data('company-id');
    const companyName = $(this).data('company-name');
    doMarkAlumni([companyId], companyName, false);
  });
}

/* ─────────────────────────────────────────────
   Mark Alumni – bulk
───────────────────────────────────────────── */
function bindMarkAlumniBulk() {
  $('#bulkMarkAlumniBtn').on('click', () => {
    const eligible = Object.entries(selectedStartups)
      .filter(([, d]) => d.atAlumniStep && !d.isAlumni);

    if (!eligible.length) {
      Swal.fire({
        title: i18n.warning,
        text: i18n.noStartupsSelected,
        icon: 'warning',
        confirmButtonText: i18n.confirmBtnText,
        customClass: { confirmButton: 'btn btn-primary ms-3' },
      });
      return;
    }

    const tags = eligible
      .map(([, d]) =>
        `<span class="d-inline-flex badge bg-label-primary border border-primary">
           <i class="ti ti-pennant ti-sm me-1"></i>${d.name}
         </span>`
      )
      .join('');

    const warningHtml = 
      `<p style="margin-bottom:12px;">${i18n.markAlumniConfirmBulk}</p>
        <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;">
          ${tags}
      </div>`;

    releaseWarningAlert({
      title: i18n.markAlumniTitle,
      html: warningHtml,
      confirmButtonText: i18n.confirmBtnText,
      cancelButtonText: i18n.cancelBtnText,
      onConfirm: () => doMarkAlumni(eligible.map(([id]) => id), null, true),
    });
  });
}

/**
 * Shared alumni AJAX helper.
 * @param {string[]} companyIds
 * @param {string|null} companyName  – null for bulk
 * @param {boolean} isBulk           – bulk skips the confirmation Swal (already shown)
 */
function doMarkAlumni(companyIds, companyName, isBulk) {
  const fire = (ids) => {
    ajaxSetup();
    const requests = ids.map(id =>
      $.ajax({ type: 'POST', url: `/dashboard/AddCompanyalumni/${id}` })
        .then(() => ({ id, ok: true }), xhr => ({ id, ok: false, xhr }))
    );
    Promise.all(requests).then(results => {
      const failed = results.filter(r => !r.ok);
      if (failed.length === 0) {
        releaseSuccess(i18n.markAlumniSuccess);
        setTimeout(() => location.reload(), 1000);
      } else {
        const firstFail = failed[0];

        if (firstFail.xhr.status === 422 && firstFail.xhr.responseJSON?.message) {
          releaseWarningAlert({ title: i18n.warning, message: firstFail.xhr.responseJSON.message});
          if (results.some(r => r.ok)) location.reload();
        } else {
          releaseFailure(i18n.markAlumniError);
          if (results.some(r => r.ok)) {
            setTimeout(location.reload(), 1000);
          }
        }
      }
    });
  };

  if (isBulk) {
    // Confirmation already shown in bindMarkAlumniBulk — fire directly
    fire(companyIds);
    return;
  }

  // Single-row: show confirmation Swal first
  releaseWarningAlert({
    title: i18n.markAlumniTitle,
    message: i18n.markAlumniConfirmation,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: i18n.confirmBtnText,
    cancelButtonText: i18n.cancelBtnText,
    onConfirm: () => fire(companyIds),
  });
}

/* ─────────────────────────────────────────────
  Delete Program AJAX
───────────────────────────────────────────── */

$('#delete-button').on('click', function () {
  const cycleId = $('#ccycle').find(':selected').val();
  ajaxSetup();

  if (!cycleId) return;

  releaseWarningAlert({
    title: i18n.deleteProgram,
    message: i18n.sureDeleteCycle,
    confirmText: i18n.yesDelete,
    cancelText: i18n.noCancel,
    confirmColor: 'danger',
    onConfirm: () => {
      $.ajax({
        type: 'POST',
        url: '/dashboard/cycles/modify',
        data: { cycleid: cycleId, type: 3 },
      }).done((res) => {
        toastr.success(res?.data?.message || res?.message || i18n.programDeleted);
        setTimeout(() => location.href = '/dashboard/cycles', 500);  
      }).fail(err => {
        // console.error(err);
        toastr.error(err instanceof Error ? err.message : i18n.unexpectedError)
      });
    }
  })
});

/* ─────────────────────────────────────────────
   Change Step AJAX  (kept globally accessible)
───────────────────────────────────────────── */
window.changestep = function (company, step) {
  ajaxSetup();
  $.ajax({
    type: 'POST',
    url: '/dashboard/screening/changestep',
    data: { company, step },
  }).done(() => {
    location.href = cfg.redirectBase;
  }).fail(err => {
    console.error('changestep failed', err);
  });
};

/* ─────────────────────────────────────────────
   Bootstrap
───────────────────────────────────────────── */
$(document).ready(() => {
  initDataTable();
  bindSelectionEvents();
  bindChangeStepEvents();
  bindMarkAlumniSingle();
  bindMarkAlumniBulk();
});