/**
 * app-team-info.js - Fixed Clear Search + Arabic Support
 */
(function ($) {
  "use strict";

  const PLURAL = {
    founder: "founders",
    investor: "investors",
    shareholder: "shareholders",
  };

  const TYPE_CONFIG = {
    founder: {
      teamsNo: "Founders",
      fieldMap: {
        name: "founder_name",
        email: "founder_email",
        linkedin: "founder_linkedin",
        phone: "founder_phone",
        type: "founder_type",
        join_date: "founder_join_date",
        commitment: "founder_commitment",
      },
    },
    investor: {
      teamsNo: "Investors",
      fieldMap: { name: "investor_name", type: "investment_type" },
    },
    shareholder: {
      teamsNo: "Shareholders",
      fieldMap: {
        name: "shareholder_name",
        role: "shareholder_role",
        percentage: "shareholder_ownership_percentage",
      },
    },
  };

  const VALIDATION_RULES = {
    founder: {
      name: { required: true },
      email: { required: true, email: true },
      type: { required: true },
      phone: { intlTel: true },
      linkedin: { url: true },
      join_date: { pastOrToday: true },
    },
    investor: {
      name: { required: true, errorId: "name-investor" },
      type: { errorId: "type-investor" },
    },
    shareholder: {
      name: { required: true, errorId: "name-shareholder" },
      role: {},
      percentage: { min: 0, max: 100 },
    },
  };

  const SERVER_ERROR_MAP = {
    founder_name: { errorId: "name", fieldName: "name" },
    founder_email: { errorId: "email", fieldName: "email" },
    founder_phone: { errorId: "phone", fieldName: "phone" },
    founder_linkedin: { errorId: "linkedin", fieldName: "linkedin" },
    founder_type: { errorId: "type", fieldName: "type" },
    founder_join_date: { errorId: "join_date", fieldName: "join_date" },
    founder_commitment: { errorId: "commitment", fieldName: "commitment" },
    investor_name: { errorId: "name-investor", fieldName: "name" },
    investment_type: { errorId: "type-investor", fieldName: "type" },
    shareholder_name: { errorId: "name-shareholder", fieldName: "name" },
    shareholder_role: { errorId: "role", fieldName: "role" },
    shareholder_ownership_percentage: {
      errorId: "percentage",
      fieldName: "percentage",
    },
  };

  const appData = {
    founders: [...TEAM_INFO_CONFIG.founders],
    investors: [...TEAM_INFO_CONFIG.investors],
    shareholders: [...TEAM_INFO_CONFIG.shareholders],
  };

  const itiInstances = {};

  // Detect current language
  const LANG = document.documentElement.lang || "en";

  /* ─────────────────────────────────────────────
     TRANSLATIONS
  ───────────────────────────────────────────── */

  const TRANSLATIONS = {
    en: {
      noResults: (term) => `No results for "${term}"`,
      noFounders: "No Cofounders added yet",
      foundersEmptyDesc:
        "Add your founding team to showcase the people behind your startup.",
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
      edit: "Edit",
      founderDeletedToast: "Founder deleted successfully.",
      founderDeleteError: "Something went wrong. Please try again.",
      deletedToast: "Deleted successfully.",
      ownership: "Ownership",
      standard: "Standard",
      founderType: "Founder",
      cofounderType: "Co-Founder",
      fullTime: "Full-Time",
      partTime: "Part-Time",
      investmentTypes: {
        Angel_Investor: "Angel Investor",
        Angel_Group: "Angel Group",
        Venture_Capital_Firm: "Venture Capital Firm",
        Accelerator: "Accelerator",
        Incubator: "Incubator",
        Corporate_Investor: "Corporate Investor",
        Family_Office: "Family Office",
        Family: "Family",
        Friends: "Friends",
      },
    },
    ar: {
      noResults: (term) => `لا توجد نتائج لـ "${term}"`,
      noFounders: "لم تتم إضافة شركاء مؤسسين بعد",
      foundersEmptyDesc:
        "أضف فريق المؤسسين لعرض الأشخاص الذين يقفون خلف شركتك الناشئة.",
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
      delete: "حذف",
      edit: "تعديل",
      founderDeletedToast: "تم حذف المؤسس بنجاح.",
      founderDeleteError: "حدث خطأ ما. يرجى المحاولة مرة أخرى.",
      deletedToast: "تم الحذف بنجاح.",
      ownership: "الملكية",
      standard: "غير محدد",
      founderType: "مؤسس",
      cofounderType: "مؤسس شريك",
      fullTime: "دوام كامل",
      partTime: "دوام جزئي",
      investmentTypes: {
        Angel_Investor: "مستثمر ملاك",
        Angel_Group: "مجموعة ملاك",
        Venture_Capital_Firm: "شركة رأس مال مخاطر",
        Accelerator: "مسرّع أعمال",
        Incubator: "حاضنة أعمال",
        Corporate_Investor: "مستثمر مؤسسي",
        Family_Office: "مكتب عائلي",
        Family: "عائلة",
        Friends: "أصدقاء",
      },
    },
  };

  const t = TRANSLATIONS[LANG] || TRANSLATIONS.en;

  function translateInvestmentType(raw) {
    if (!raw) return "";
    const direct = t.investmentTypes && t.investmentTypes[raw];
    if (direct) return direct;
    const normalized = raw.replace(/\s+/g, "_");
    return (t.investmentTypes && t.investmentTypes[normalized]) || raw;
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
    return s
      ? String(s).replace(
          /[&<>"']/g,
          (m) =>
            ({
              "&": "&amp;",
              "<": "&lt;",
              ">": "&gt;",
              '"': "&quot;",
              "'": "&#039;",
            })[m],
        )
      : "";
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
    Object.keys(PLURAL).forEach((type) => {
      const input = document.querySelector(`#form-${type} input[name="phone"]`);
      if (input && !itiInstances[type]) {
        itiInstances[type] = window.intlTelInput(input, {
          initialCountry: "eg",
          separateDialCode: true,
          utilsScript: "/vuexy/assets/vendor/libs/intl-tel-input/js/utils.js",
        });
      }
    });
  }

  function initFlatpickr() {
    const el = document.getElementById("founder_join_date");
    if (el && typeof flatpickr === "function") {
      flatpickr(el, { dateFormat: "Y-m-d", maxDate: "today" });
    }
  }

  /* ─────────────────────────────────────────────
     EVENTS
  ───────────────────────────────────────────── */

  function bindEvents() {
    const debouncedRenderList = debounce((type, value) => {
      renderList(type, value);
    }, 180);

    $(".search-input").on("input", function () {
      const type = $(this).data("type");
      const value = $(this).val();
      debouncedRenderList(type, value);
    });

    // Form submission & Modal/Offcanvas resets
    Object.keys(PLURAL).forEach((type) => {
      $(`#form-${type}`).on("submit", function (e) {
        e.preventDefault();
        if (validateForm(type)) saveItem(type, this);
      });

      if (type === "founder") {
        // Founder uses offcanvas drawer
        const offcanvasEl = document.getElementById("offcanvas-founder");
        if (offcanvasEl) {
          offcanvasEl.addEventListener("hidden.bs.offcanvas", function () {
            document.getElementById("form-founder").reset();
            clearAllErrors("founder");
          });
        }
      } else {
        $(`#modal-${type}`).on("hidden.bs.modal", function () {
          this.querySelector("form").reset();
          clearAllErrors(type);
        });
      }
    });

    // Real-time validation
    $(document).on(
      "input change blur",
      ".form-control, .form-select",
      function () {
        const $form = $(this).closest("form");
        const formId = $form.attr("id");
        if (!formId) return;

        const type = formId.replace("form-", "");
        const fieldName = $(this).attr("name");

        if (VALIDATION_RULES[type] && VALIDATION_RULES[type][fieldName]) {
          validateSingleField(type, fieldName);
        }
      },
    );
  }

  /* ─────────────────────────────────────────────
     RENDER FUNCTIONS
  ───────────────────────────────────────────── */

  function renderAll() {
    Object.keys(PLURAL).forEach((type) => renderList(type));
  }

  function getEmptyState(type, searchTerm = "") {
    const term = (searchTerm || "").trim();

    if (term) {
      // No results when searching
      return `
        <div class="col-12">
          <div class="text-center py-5 my-4 border rounded-3 border-dashed bg-light-subtle empty-state">
            <i class="ti ti-search text-secondary" style="font-size: 3.5rem; line-height: 1;"></i>
            <h5 class="mb-2 fw-semibold mt-4">${t.noResults(escapeHtml(term))}</h5>
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
      shareholder: t.noShareholders,
    };

    const addTexts = {
      founder: t.addFounder,
      investor: t.addInvestor,
      shareholder: t.addShareholder,
    };

    const icons = {
      founder: "ti-user-plus",
      investor: "ti-building-bank",
      shareholder: "ti-users-group",
    };

    return `
      <div class="col-12">
        <div class="text-center py-5 my-4 border rounded-3 border-dashed bg-light-subtle empty-state">
          <i class="ti ${icons[type]} text-secondary" style="font-size: 3.5rem; line-height: 1;"></i>
          <h5 class="mb-2 fw-semibold mt-4">${titles[type]}</h5>
          <p class="text-secondary mb-4">
            ${
              type === "founder"
                ? t.foundersEmptyDesc
                : type === "investor"
                  ? "Showcase your funding and investor network to build credibility"
                  : "Define your ownership structure and equity distribution."
            }
          </p>
          <button type="button" class="btn btn-primary waves-effect waves-light" onclick="${type === "founder" ? "TeamInfo.openFounderDrawer()" : `openModal('${type}')`}">
            <i class="ti ti-plus me-1"></i>${addTexts[type]}
          </button>
          <div class="text-muted small mt-3 opacity-75">${t.tip}</div>
        </div>
      </div>`;
  }

  function renderList(type, searchTerm = "") {
    const plural = PLURAL[type];
    const $container = $(`#list-${plural}`);
    const term = (searchTerm || "").toLowerCase().trim();

    const filteredData = appData[plural].filter(
      (item) => !term || (item.name && item.name.toLowerCase().includes(term)),
    );

    $container.empty();

    if (!filteredData.length) {
      $container.html(getEmptyState(type, searchTerm));
      $container.find(".empty-state").hide().fadeIn(400);
    } else {
      const cardsHtml = filteredData
        .map((item) => buildCard(type, item))
        .join("");
      $container.html(cardsHtml);
    }

    $(`#count-${plural}`).text(appData[plural].length);
  }

  function buildCard(type, item) {
    if (type === "founder") {
      const rows = [];
      if (item.email) {
        rows.push(
          `<div class="small mb-2"><i class="ti ti-mail me-2"></i>${escapeHtml(item.email)}</div>`,
        );
      }
      if (item.phone) {
        rows.push(
          `<div class="small mb-2"><i class="ti ti-phone me-2"></i>${escapeHtml(item.phone)}</div>`,
        );
      }
      if (item.join_date) {
        rows.push(
          `<div class="small mb-2"><i class="ti ti-calendar me-2"></i>${escapeHtml(item.join_date)}</div>`,
        );
      }
      const commitmentBadge = item.commitment
        ? `<span class="badge bg-label-secondary text-dark">${escapeHtml(item.commitment == 'Full-time' ? t['fullTime'] : t['partTime'])}</span>`
        : "";
      const linkedinIcon = item.linkedin
        ? `<a href="${escapeHtml(item.linkedin)}" target="_blank" rel="noopener noreferrer" class="btn btn-icon btn-text-secondary rounded-pill" title="LinkedIn" aria-label="LinkedIn"><i class="ti ti-brand-linkedin"></i></a>`
        : "";
      const footer =
        commitmentBadge || linkedinIcon
          ? `<div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top"><div>${commitmentBadge}</div><div>${linkedinIcon}</div></div>`
          : "";
      return `
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card item-card h-100 shadow-none border">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="flex-grow-1">
                <h6 class="mb-0 fw-bold">${escapeHtml(item.name || "")}</h6>
                ${item.type ? `<small class="text-muted">${escapeHtml(item.type == 'Founder' ? t['founderType'] : t['cofounderType'])}</small>` : ""}
              </div>
              ${buildDropdown("founder", item.id)}
            </div>
            ${rows.join("")}
            ${footer}
          </div>
        </div>
      </div>`;
    }

    if (type === "investor") {
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
                ${escapeHtml(translateInvestmentType(item.type) || t.standard)}
              </small>
              <small class="text-success fw-bold">${escapeHtml(item.investment_amount || "")}</small>
            </div>
            ${buildDropdown("investor", item.id)}
          </div>
        </div>
      </div>`;
    }

    if (type === "shareholder") {
      const pct = parseFloat(item.percentage) || 0;
      return `
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card item-card h-100 shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1 fw-bold">${escapeHtml(item.name)}</h6>
                <small class="text-muted">${escapeHtml(item.role || "Shareholder")}</small>
              </div>
              ${buildDropdown("shareholder", item.id)}
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="text-muted">${t.ownership}</small>
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

    return "";
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
              <i class="ti ti-pencil me-1"></i> ${t.edit}
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item text-danger" href="javascript:void(0)"
              onclick="TeamInfo.deleteItem('${type}', ${id})">
              <i class="ti ti-trash me-1"></i> ${t.delete}
            </a>
          </li>
        </ul>
      </div>`;
  }

  /* ─────────────────────────────────────────────
     CRUD & MODALS
  ───────────────────────────────────────────── */

  function openFounderDrawer(row) {
    const formEl = document.getElementById("form-founder");
    formEl.reset();
    clearAllErrors("founder");
    if (itiInstances.founder) itiInstances.founder.setNumber("");
    const titleEl = document.getElementById("offcanvasFounderLabel");
    const btnSave = document.getElementById("btn-save-founder");

    if (row) {
      console.log("row", row);
      // Edit mode
      if (titleEl)
        titleEl.textContent = LANG === "ar" ? "تعديل المؤسس" : "Edit Founder";
      if (btnSave)
        btnSave.textContent = LANG === "ar" ? "حفظ التغييرات" : "Save Changes";
      const $f = $("#form-founder");
      Object.entries(row).forEach(([k, v]) => {
        if (k === "phone" && itiInstances.founder) {
          itiInstances.founder.setNumber(v || "");
        } else if (k === "type" || k === "commitment") {
          $f.find(`[name="${k}"]`).val(v).trigger("change");
        } else {
          $f.find(`[name="${k}"]`).val(v);
        }
      });
    } else {
      // Add mode
      if (titleEl)
        titleEl.textContent = LANG === "ar" ? "إضافة مؤسس" : "Add Founder";
      if (btnSave)
        btnSave.textContent = LANG === "ar" ? "حفظ المؤسس" : "Save Founder";
      $('#form-founder [name="id"]').val("");
    }

    bootstrap.Offcanvas.getOrCreateInstance(
      document.getElementById("offcanvas-founder"),
    ).show();
  }

  function openModal(type) {
    if (type === "founder") {
      openFounderDrawer();
      return;
    }
    const $m = $(`#modal-${type}`);
    $m.find("form")[0].reset();
    $m.find('[name="id"]').val("");
    clearAllErrors(type);
    $m.modal("show");
  }

  function editItem(type, id) {
    const item = appData[PLURAL[type]].find((i) => String(i.id) === String(id));
    if (!item) return;

    if (type === "founder") {
      openFounderDrawer(item);
      return;
    }

    const $f = $(`#form-${type}`);
    clearAllErrors(type);

    Object.entries(item).forEach(([k, v]) => {
      if (k === "phone" && itiInstances[type]) {
        itiInstances[type].setNumber(v || "");
      } else {
        $f.find(`[name="${k}"]`).val(v);
      }
    });

    $(`#modal-${type}`).modal("show");
  }

  function saveItem(type, formEl) {
    const formData = Object.fromEntries(new FormData(formEl).entries());
    if (itiInstances[type]) {
      // getNumber() returns '' until utils.js loads — fall back to dial code + raw input.
      const iti = itiInstances[type];
      const e164 = iti.getNumber();
      if (e164) {
        formData.phone = e164;
      } else {
        const raw = (formData.phone || "").trim();
        if (raw) {
          const dialCode = iti.getSelectedCountryData().dialCode;
          formData.phone = dialCode ? `+${dialCode}${raw}` : raw;
        }
      }
    }

    const isEdit = !!formData.id;
    const payload = { ...buildPayload(type, formData), id: formData.id };
    const url = isEdit
      ? TEAM_INFO_CONFIG.routes.edit
      : TEAM_INFO_CONFIG.routes.add;

    $.ajax({
      url,
      method: "POST",
      data: payload,
      headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
      success(res) {
        toastr.success(res.state || "Success");
        const mapFounder = (f) => ({
          id: f.id,
          name: f.name,
          email: f.email,
          phone: f.phone || "",
          linkedin: f.linkedin || "",
          join_date: f.join_date || "",
          type: f.type || f.founder_type || "",
          commitment: f.commitment || f.working_commitment || "",
        });
        if (isEdit) {
          if (type === "founder" && res.founder) {
            const idx = appData.founders.findIndex(
              (i) => String(i.id) === String(formData.id),
            );
            if (idx !== -1) appData.founders[idx] = mapFounder(res.founder);
          } else {
            const idx = appData[PLURAL[type]].findIndex(
              (i) => String(i.id) === String(formData.id),
            );
            if (idx !== -1)
              appData[PLURAL[type]][idx] = {
                ...appData[PLURAL[type]][idx],
                ...formData,
              };
          }
        } else {
          if (type === "founder" && res.founder) {
            appData.founders.push(mapFounder(res.founder));
          } else {
            formData.id = Date.now();
            appData[PLURAL[type]].push(formData);
          }
        }
        if (type === "founder") {
          bootstrap.Offcanvas.getOrCreateInstance(
            document.getElementById("offcanvas-founder"),
          ).hide();
        } else {
          $(`#modal-${type}`).modal("hide");
        }
        renderList(type);
      },
      error(xhr) {
        if (xhr.status === 422 && xhr.responseJSON.errors) {
          applyServerErrors(type, xhr.responseJSON.errors);
        } else {
          toastr.error("Error saving data");
        }
      },
    });
  }

  function deleteItem(type, id) {
    const name = appData[PLURAL[type]].find(
      (i) => String(i.id) === String(id),
    ).name;

    if (type === "founder") {
      const deleteTitle = `${t.delete} ${t[type]}`;
      const deleteMsg = t.deleteMsg.replace("${name}", escapeHtml(name));
      const errorMsg = t.founderDeleteError;
      const successMsg = t.founderDeletedToast;

      Swal.fire({
        icon: "warning",
        title: deleteTitle,
        text: deleteMsg,
        showCancelButton: true,
        confirmButtonText: t.yesDelete,
        cancelButtonText: t.noCancel,
        customClass: {
          confirmButton: "btn btn-danger ms-3",
          cancelButton: "btn btn-label-secondary",
        },
        buttonsStyling: false,
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
          return new Promise((resolve) => {
            $.ajax({
              url: TEAM_INFO_CONFIG.routes.delete,
              method: "POST",
              data: { id },
              headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
              },
              success: (res) => {
                if (res && res.state === true) resolve(res);
                else {
                  Swal.showValidationMessage(errorMsg);
                  resolve(false);
                }
              },
              error: () => {
                Swal.showValidationMessage(errorMsg);
                resolve(false);
              },
            });
          });
        },
        allowOutsideClick: () => !Swal.isLoading(),
      }).then((result) => {
        if (result.isConfirmed && result.value) {
          appData[PLURAL[type]] = appData[PLURAL[type]].filter(
            (i) => String(i.id) !== String(id),
          );
          renderList(type);
          toastr.success(successMsg);
        }
      });
    } else {
      releaseWarningAlert({
        title: `${t.delete} ${t[type]}`,
        message: `${t.deleteMsg.replace("${name}", escapeHtml(name))}`,
        confirmText: `${t.yesDelete}`,
        cancelText: `${t.noCancel}`,
        confirmColor: "danger",
        onConfirm: () => {
          $.ajax({
            url: TEAM_INFO_CONFIG.routes.delete,
            method: "POST",
            data: { id },
            headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success() {
              appData[PLURAL[type]] = appData[PLURAL[type]].filter(
                (i) => String(i.id) !== String(id),
              );
              renderList(type);
              toastr.success(t.deletedToast);
            },
          });
        },
      });
    }
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
      if (m)
        showFieldError(
          type,
          m.errorId,
          m.fieldName,
          Array.isArray(v) ? v[0] : v,
        );
    });
  }

  function clearAllErrors(type) {
    const $f = $(`#form-${type}`);
    $f.find(".invalid-feedback").hide();
    $f.find(".is-invalid").removeClass("is-invalid");
  }

  function showFieldError(type, eid, fname, msg = null) {
    const $err = $(`#error-${eid}`);
    if ($err.data("defaultMsg") === undefined) {
      $err.data("defaultMsg", $.trim($err.text()));
    }
    $err.text(msg || $err.data("defaultMsg"));
    $err.show();
    $(`#form-${type} [name="${fname}"]`).addClass("is-invalid");
  }

  function clearFieldError(type, eid, fname) {
    $(`#error-${eid}`).hide();
    $(`#form-${type} [name="${fname}"]`).removeClass("is-invalid");
  }

  function validateForm(type) {
    let valid = true;
    const rules = VALIDATION_RULES[type];
    Object.keys(rules).forEach((f) => {
      if (!validateSingleField(type, f)) valid = false;
    });
    return valid;
  }

  function validateSingleField(type, field) {
    const rule = VALIDATION_RULES[type][field];
    if (!rule) return true;

    const eid = rule.errorId || field;
    const val = ($(`#form-${type} [name="${field}"]`).val() || "").trim();

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
      if (rule.intlTel && itiInstances[type]) {
        // Only run library validation when utils.js has finished loading.
        // Until then, accept anything non-empty — the server has its own max-length rule.
        if (
          typeof window.intlTelInputUtils !== "undefined" &&
          !itiInstances[type].isValidNumber()
        ) {
          showFieldError(type, eid, field);
          return false;
        }
      }
      if (rule.pastOrToday) {
        const d = new Date(val);
        const today = new Date();
        today.setHours(23, 59, 59, 999);
        if (isNaN(d.getTime()) || d > today) {
          showFieldError(type, eid, field);
          return false;
        }
      }
    }

    clearFieldError(type, eid, field);
    return true;
  }

  function isValidUrl(s) {
    try {
      new URL(s);
      return true;
    } catch {
      return false;
    }
  }

  // ===================== EXPORTS =====================
  window.TeamInfo = {
    openModal,
    openFounderDrawer,
    editItem,
    deleteItem,
    renderList,
  };

  // Back-compat: investor/shareholder blade buttons still call these bare globals.
  window.openModal = openModal;
  window.editItem = editItem;
  window.deleteItem = deleteItem;

  $(function () {
    init();
  });
})(jQuery);
