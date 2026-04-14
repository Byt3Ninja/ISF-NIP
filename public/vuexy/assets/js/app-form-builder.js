/**
 * app-form-builder.js  –  Form Builder
 *
 * All user-facing strings come from FB_CONFIG.translations (set in the Blade
 * @section('scripts') block). There are zero {{ __(...) }} calls in this file.
 *
 * Drag-and-drop strategy
 * ──────────────────────
 * • Sections  : draggable within #itemsContainer via the grip handle.
 * • Questions : draggable within their own .section-questions zone; a question
 *               can also be dragged from one section to another.
 * • draggable=true is set only while the grip handle is pressed, so normal
 *   form interactions (clicks, selects, inputs) are never accidentally captured.
 * • A live "placeholder" div shows the precise insertion point.
 */

/* ═══════════════════════════════════════════════════════════════════
   STATE  –  declared first so the IIFE below can safely call any
             function that reads these bindings.
══════════════════════════════════════════════════════════════════ */
let itemCounter      = 0;
let questionCounters = {};   // sectionId → count (kept for compat)

/* Active drag bookkeeping */
const drag = {
  el:          null,   // the element being dragged
  placeholder: null,   // the ghost insertion indicator
  type:        null,   // 'section' | 'question'
  sourceList:  null,   // original parent list
};

/* Reorder debounce timers */
const _reorderTimers = { sections: null, questions: null };

/* Convenience alias – assigned inside the IIFE once FB_CONFIG is live */
let t;   // = FB_CONFIG.translations

(function ($) {
  'use strict';

  t = FB_CONFIG.translations;   // single source of truth for all UI strings

  /* ─── Hide org filter for org-managers ─────────────────────────── */
  if (FB_CONFIG.isOrganizationManager) {
    $('#organizationFilterContainer').hide();
  }

  /* ─── Bootstrap the form ────────────────────────────────────────── */
  if (FB_CONFIG.mode === 'ADD') {
    addSection();
  } else {
    populateForm(FB_CONFIG.formData, FB_CONFIG.builderData);
  }

  /* ─── Filter modal: organisation → form cascade ─────────────────── */
  $('#organizationFilter').on('change', updateFormSelect);
  updateFormSelect();

  let initialState = $('#builder-form').serialize();
  let isBuilderFormDirty = false;

  console.log('builder-form', $('#builder-form'));

  $('#builder-form').on('input change', function () {
    isBuilderFormDirty = initialState !== $(this).serialize();
    // console.log('isDirty:', isBuilderFormDirty);
  });

  $('#formTitle').on('input', function () {
    isBuilderFormDirty = initialState !== $(this).serialize();
    // console.log('isDirty:', isBuilderFormDirty);
  });

  $('.form-org-select').on('change', function () {
    isBuilderFormDirty = initialState !== $(this).serialize();
    // console.log('isDirty:', isBuilderFormDirty);
  });

  $('#organizationFilter').on('change', function () {
    $(this).removeClass("is-invalid");
    $('#organizationFilterError').hide();
  });

  $('#formFilter').on('change', function () {
    $(this).removeClass("is-invalid");
    $('#formFilterError').hide();
  });

  $('#filterModal form').on('submit', function (e) {
    e.preventDefault();
    let org_id  = $('#organizationFilter').val();
    let form_id = $('#formFilter').val();
    let url     = '/dashboard/show-builder';

    if (!org_id || org_id === '0') {
      $('#organizationFilter').trigger('focus');
      $('#organizationFilter').addClass('is-invalid');
      $('#organizationFilterError').show();
      return;
    }
    
    if (!form_id) {
      $('#formFilter').trigger('focus');
      $('#formFilter').addClass('is-invalid');
      $('#formFilterError').show();
      return;
    }

    // console.log('isBuilderFormDirty', isBuilderFormDirty);
    
    if (form_id) {
      url += '/' + form_id;
      const selected = FB_CONFIG.allForms.find(f => String(f.id) === String(form_id));
      if (selected) org_id = selected.organization_id;
    }
    if (org_id && org_id !== '0') url += '?org_id=' + org_id;

    if (isBuilderFormDirty) {
      releaseWarningAlert({
        title: FB_CONFIG.translations.confirm_switch,
        message: FB_CONFIG.translations.confirm_switch_message,
        onConfirm: () => location.href = url,
      });

      return;
    }

    location.href = url;
  });

})(jQuery);

/* ═══════════════════════════════════════════════════════════════════
   DRAG & DROP – SECTIONS
   Sections live inside #itemsContainer.
══════════════════════════════════════════════════════════════════ */
function setupSectionDrag(sectionWrapper) {
  const handle = sectionWrapper.querySelector('.section-card .drag-handle');
  if (!handle) return;

  handle.addEventListener('mousedown',  () => { sectionWrapper.draggable = true; });
  handle.addEventListener('touchstart', () => { sectionWrapper.draggable = true; }, { passive: true });
  document.addEventListener('mouseup',  () => { sectionWrapper.draggable = false; });

  sectionWrapper.addEventListener('dragstart', onSectionDragStart);
  sectionWrapper.addEventListener('dragend',   onSectionDragEnd);
}

function onSectionDragStart(e) {
  drag.el          = this;
  drag.type        = 'section';
  drag.sourceList  = document.getElementById('itemsContainer');
  drag.placeholder = createPlaceholder(this.offsetHeight);

  this.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';

  drag.sourceList.addEventListener('dragover',  onContainerDragOver);
  drag.sourceList.addEventListener('dragleave', onContainerDragLeave);
  drag.sourceList.addEventListener('drop',      onContainerDrop);
}

function onSectionDragEnd() {
  this.classList.remove('dragging');
  this.draggable = false;
  removePlaceholder();
  const container = document.getElementById('itemsContainer');
  container.removeEventListener('dragover',  onContainerDragOver);
  container.removeEventListener('dragleave', onContainerDragLeave);
  container.removeEventListener('drop',      onContainerDrop);
  drag.el = null;
}

/* ═══════════════════════════════════════════════════════════════════
   DRAG & DROP – QUESTIONS
   Questions live inside .section-questions divs.
══════════════════════════════════════════════════════════════════ */
function setupQuestionDrag(questionCard) {
  const handle = questionCard.querySelector('.drag-handle');
  if (!handle) return;

  handle.addEventListener('mousedown',  () => { questionCard.draggable = true; });
  handle.addEventListener('touchstart', () => { questionCard.draggable = true; }, { passive: true });
  document.addEventListener('mouseup',  () => { questionCard.draggable = false; });

  questionCard.addEventListener('dragstart', onQuestionDragStart);
  questionCard.addEventListener('dragend',   onQuestionDragEnd);
}

function onQuestionDragStart(e) {
  drag.el          = this;
  drag.type        = 'question';
  drag.sourceList  = this.closest('.section-questions');
  drag.placeholder = createPlaceholder(this.offsetHeight);

  this.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.stopPropagation();

  document.querySelectorAll('.section-questions').forEach(zone => {
    zone.addEventListener('dragover',  onQuestionZoneDragOver);
    zone.addEventListener('dragleave', onQuestionZoneDragLeave);
    zone.addEventListener('drop',      onQuestionZoneDrop);
    zone.classList.add('drag-active');
  });
}

function onQuestionDragEnd() {
  this.classList.remove('dragging');
  this.draggable = false;
  removePlaceholder();
  document.querySelectorAll('.section-questions').forEach(zone => {
    zone.removeEventListener('dragover',  onQuestionZoneDragOver);
    zone.removeEventListener('dragleave', onQuestionZoneDragLeave);
    zone.removeEventListener('drop',      onQuestionZoneDrop);
    zone.classList.remove('drag-active');
  });
  drag.el = null;
}

/* ═══════════════════════════════════════════════════════════════════
   SHARED DRAG-OVER / DROP HELPERS
══════════════════════════════════════════════════════════════════ */
function getDragAfterElement(list, pointerY) {
  const draggables = [...list.children].filter(
    c => !c.classList.contains('drop-placeholder') && c !== drag.el
  );
  return draggables.reduce((closest, child) => {
    const box    = child.getBoundingClientRect();
    const offset = pointerY - box.top - box.height / 2;
    if (offset < 0 && offset > closest.offset) {
      return { offset, element: child };
    }
    return closest;
  }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
}

/* ── Section container ── */
function onContainerDragOver(e) {
  if (drag.type !== 'section') return;
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  const after = getDragAfterElement(this, e.clientY);
  after ? this.insertBefore(drag.placeholder, after) : this.appendChild(drag.placeholder);
}
function onContainerDragLeave(e) {
  if (!this.contains(e.relatedTarget)) removePlaceholder();
}
function onContainerDrop(e) {
  e.preventDefault();
  if (drag.type !== 'section' || !drag.el) return;
  this.insertBefore(drag.el, drag.placeholder);
  removePlaceholder();
  updateAllLogicPanels();
  reorderSections();
}

/* ── Question zones ── */
function onQuestionZoneDragOver(e) {
  if (drag.type !== 'question') return;
  e.preventDefault();
  e.stopPropagation();
  e.dataTransfer.dropEffect = 'move';
  const after = getDragAfterElement(this, e.clientY);
  after ? this.insertBefore(drag.placeholder, after) : this.appendChild(drag.placeholder);
}
function onQuestionZoneDragLeave(e) {
  if (!this.contains(e.relatedTarget)) removePlaceholder();
}
function onQuestionZoneDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  if (drag.type !== 'question' || !drag.el) return;
  const targetSection = this.closest('.section-wrapper');
  this.insertBefore(drag.el, drag.placeholder);
  removePlaceholder();
  reorderQuestions(targetSection);
}

/* ═══════════════════════════════════════════════════════════════════
   PLACEHOLDER ELEMENT
══════════════════════════════════════════════════════════════════ */
function createPlaceholder(height = 60) {
  removePlaceholder();
  const ph = document.createElement('div');
  ph.className    = 'drop-placeholder';
  ph.style.height = Math.max(height, 48) + 'px';
  drag.placeholder = ph;
  return ph;
}
function removePlaceholder() {
  if (drag.placeholder?.parentNode) {
    drag.placeholder.parentNode.removeChild(drag.placeholder);
  }
  drag.placeholder = null;
}

/* ═══════════════════════════════════════════════════════════════════
   REORDER API CALLS
══════════════════════════════════════════════════════════════════ */
function reorderSections() {
  clearTimeout(_reorderTimers.sections);

  _reorderTimers.sections = setTimeout(() => {
    const elements = [...document.querySelectorAll('#itemsContainer > .section-wrapper')];

    const ids = elements.map(el => {
      return el.dataset.dbId || `temp-${el.dataset.itemId}`;
    });

    const idsNums = ids.map(id => parseInt(id.replace('temp-', ''), 10) + 1);

    _reorderPost(FB_CONFIG.routes.reorderSections, {
      section_ids: idsNums,
    });
  }, 400);
}

function reorderQuestions(targetSection = null) {
  clearTimeout(_reorderTimers.questions);

  _reorderTimers.questions = setTimeout(() => {
    const sections = targetSection
      ? [targetSection]
      : [...document.querySelectorAll('#itemsContainer .section-wrapper')];

    sections.forEach(section => {
      const sectionId = section.dataset.dbId || `temp-${section.dataset.itemId}`;

      const sectionIdNum  = parseInt(sectionId.replace('temp-', ''), 10) + 1;

      const ids = [...section.querySelectorAll('.question-card')]
        .map(el => el.dataset.dbId || `temp-${el.dataset.itemId}`);

      const idsNums = ids.map(id => parseInt(id.replace('temp-', ''), 10));

      if (!ids.length) return;

      _reorderPost(FB_CONFIG.routes.reorderQuestions, {
        section_db_id: sectionIdNum,
        question_ids: idsNums,
      });
    });
  }, 400);
}

function _reorderPost(url, payload) {
  // console.log('[REORDER]', url, payload); // 👈 DEBUG

  fetch(url, {
    method:  'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  })
    .then(r => {
      if (!r.ok) throw new Error(`HTTP ${r.status}`);
    })
    .catch(err => console.warn('[FormBuilder] reorder failed:', err));
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION HELPERS
══════════════════════════════════════════════════════════════════ */
function getSectionsList() {
  const sections = [];
  document.querySelectorAll('.section-wrapper').forEach((section, index) => {
    const titleInput = section.querySelector('.section-title');
    const title = (titleInput && titleInput.value.trim())
      ? titleInput.value.trim()
      : `${t.section} ${index + 1}`;
    sections.push({ id: section.dataset.itemId, title });
  });
  return sections;
}

function updateAllLogicPanels() {
  document.querySelectorAll('.logic-panel').forEach(panel => {
    const qCard = panel.closest('.item-card');
    if (qCard) addLogicPanel(qCard);
  });

  const sections = getSectionsList();
  document.querySelectorAll('.section-logic').forEach(select => {
    const current = select.value;
    select.innerHTML =
      `<option value="continue">${t.continue_next_section}</option>` +
      sections.map(s => `<option value="section-${s.id}">${t.go_to} ${s.title}</option>`).join('') +
      `<option value="submit">${t.submit_form}</option>`;
    select.value = current;
  });
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION CREATION
══════════════════════════════════════════════════════════════════ */
function addSection(sectionData = null) {
  const container = document.getElementById('itemsContainer');
  const sectionId = itemCounter++;
  questionCounters[sectionId] = 0;
  const sections  = getSectionsList();
  const badgeNum  = document.querySelectorAll('.section-wrapper').length + 1;

  const sectionHtml = `
    <div class="section-wrapper mb-5 position-relative" data-item-id="${sectionId}" data-item-type="section">
      <div class="position-absolute start-0 z-1" style="top:-0.5rem;">
        <span class="badge bg-primary">${t.section} ${badgeNum}</span>
      </div>
      <div class="item-card section-card card mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start gap-3 mt-3">
            <i class="ti ti-grip-vertical text-muted drag-handle cursor-move mt-1"
               title="${t.drag_to_reorder}"></i>
            <div class="flex-fill">
              <input type="text" name="sections[${sectionId}][title]" class="section-title form-control mb-2"
                placeholder="${t.section_title_optional}"
                onchange="updateAllLogicPanels()"
                value="${escHtml(sectionData?.title ?? '')}"> 
              <input type="text" name="sections[${sectionId}][description]" class="section-description form-control mb-3"
                placeholder="${t.section_description_optional}"
                value="${escHtml(sectionData?.description ?? '')}"> 
            </div>
            <button type="button" class="btn btn-link text-muted p-0"
              onclick="deleteSection(this)"
              title="${t.remove_section}">
              <i class="ti ti-trash"></i>
            </button>
          </div>
          <div class="mt-4 pt-4 border-top">
            <div class="text-muted small fw-medium mb-2">${t.after_this_section}</div>
            <select name="sections[${sectionId}][logic]" class="section-logic form-select">
              <option value="continue">${t.continue_next_section}</option> 
              ${sections.map(s => `<option value="section-${s.id}">${t.go_to} ${s.title}</option>`).join('')}
              <option value="submit">${t.submit_form}</option>
            </select>
          </div>
        </div>
      </div>

      <div class="section-questions d-flex flex-column gap-3 mb-4 px-4 ms-3"></div>

      <button type="button" onclick="addQuestionToSection(this)"
        class="btn btn-outline-primary border-2 border-dashed d-flex align-items-center justify-content-center gap-2 w-100 p-3">
        <i class="ti ti-plus"></i>
        ${t.add_question_section} ${badgeNum}
      </button>
    </div>
  `;

  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = sectionHtml;
  const sectionEl = tempDiv.firstElementChild;
  container.appendChild(sectionEl);
  setupSectionDrag(sectionEl);

  if (sectionData?.logic) {
    sectionEl.querySelector('.section-logic').value = sectionData.logic;
  }

  return sectionEl;
}

function deleteSection(button) {
  const wrapper      = button.closest('.section-wrapper');
  const hasQuestions = wrapper.querySelectorAll('.question-card').length > 0;
  if (hasQuestions && !confirm(t.delete_section_questions)) return;
  wrapper.remove();
  updateAllLogicPanels();
}

/* ═══════════════════════════════════════════════════════════════════
   QUESTION CREATION
══════════════════════════════════════════════════════════════════ */
function createQuestionHTML(itemId, inSection = false) {
  return `
    <div class="item-card question-card card ${inSection ? '' : 'mb-4'}"
         data-item-id="${itemId}" data-item-type="question">
      <div class="card-body">
        <div class="d-flex align-items-start gap-3">
          <i class="ti ti-grip-vertical text-muted drag-handle cursor-move mt-1"
             title="${t.drag_to_reorder}"></i>
          <div class="flex-fill">
            <input type="text" name="questions[${itemId}][title]"
              class="question-title form-control form-control-lg border-bottom mb-4"
              placeholder="${t.question}"> 

            <select name="questions[${itemId}][question_type]" class="question-type form-select mb-3" onchange="updateQuestionOptions(this)">
              <option value="short_answer">${t.short_answer}</option> 
              <option value="paragraph">${t.paragraph}</option>
              <option value="multiple_choice">${t.multiple_choice}</option>
              <option value="checkboxes">${t.checkboxes}</option>
              <option value="dropdown">${t.dropdown}</option>
              <option value="linear_scale">${t.linear_scale}</option>
              <option value="number">${t.number}</option>
              <option value="email">${t.email}</option>
              <option value="url">${t.url}</option>
              <option value="date">${t.date}</option>
              <option value="time">${t.time}</option>
              <option value="file">${t.file_upload}</option>
            </select>

            <div class="question-options mt-4">
              <input type="text" class="form-control text-muted"
                placeholder="${t.short_answer_text}" disabled>
            </div>
          </div>
          <button type="button" class="btn btn-link text-muted p-0" onclick="deleteItem(this)">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="mt-4 pt-4 border-top d-flex align-items-center justify-content-end gap-3">
          <div class="form-check">
            <input type="checkbox" name="questions[${itemId}][required]" class="question-required form-check-input"> 
            <label class="form-check-label">${t.required}</label>
          </div>
        </div>
      </div>
    </div>
  `;
}

function addQuestionToSection(button) {
  const sectionWrapper     = button.closest('.section-wrapper');
  const sectionId          = sectionWrapper.dataset.itemId;
  const questionsContainer = sectionWrapper.querySelector('.section-questions');
  if (!questionCounters[sectionId]) questionCounters[sectionId] = 0;

  const questionId  = itemCounter++;
  const tempDiv     = document.createElement('div');
  tempDiv.innerHTML = createQuestionHTML(questionId, true);
  const qEl         = tempDiv.firstElementChild;
  questionsContainer.appendChild(qEl);
  setupQuestionDrag(qEl);
}

function deleteItem(button) {
  button.closest('.question-card').remove();
}

/* ═══════════════════════════════════════════════════════════════════
   QUESTION OPTIONS (type-specific UI)
══════════════════════════════════════════════════════════════════ */
function updateQuestionOptions(select) {
  const questionCard     = select.closest('.item-card');
  const optionsContainer = questionCard.querySelector('.question-options');
  const type             = select.value;
  const cardId           = questionCard.dataset.itemId;

  const tplMap = {
    short_answer: `<input type="text"  class="form-control text-muted" placeholder="${t.short_answer_text}" disabled>`,
    paragraph:    `<textarea           class="form-control text-muted" placeholder="${t.long_answer_text}"  disabled rows="3"></textarea>`,
    date:         `<input type="date"  class="form-control" disabled>`,
    time:         `<input type="time"  class="form-control" disabled>`,
    file:         `<input type="file"  class="form-control" disabled>`,

    number: `
      <div>
        <input type="number" class="form-control text-muted"
          placeholder="${t.number_placeholder}" disabled>
        <div class="row g-3 mt-1">
          <div class="col-md-4">
            <label class="form-label small text-muted">${t.min_value}</label>
            <input type="number" name="questions[${cardId}][number_config][min]" class="number-min form-control form-control-sm"
              placeholder="${t.min_optional}"> 
          </div>
          <div class="col-md-4">
            <label class="form-label small text-muted">${t.max_value}</label>
            <input type="number" name="questions[${cardId}][number_config][max]" class="number-max form-control form-control-sm"
              placeholder="${t.max_optional}"> 
          </div>
          <div class="col-md-4">
            <label class="form-label small text-muted">${t.step}</label>
            <input type="number" name="questions[${cardId}][number_config][step]" class="number-step form-control form-control-sm"
              placeholder="1" min="0.000001"> 
          </div>
        </div>
      </div>`,

    email: `
      <div>
        <input type="email" class="form-control text-muted"
          placeholder="${t.email_placeholder}" disabled>
        <div class="form-check mt-2">
          <input type="checkbox" name="questions[${cardId}][email_config][confirm]" class="email-confirm-toggle form-check-input"
            id="emailConfirmToggle_${cardId}"
            onchange="toggleEmailConfirm(this)"> 
          <label class="form-check-label small" for="emailConfirmToggle_${cardId}">
            ${t.require_email_confirmation}
          </label>
        </div>
        <div class="email-confirm-field mt-2 d-none">
          <input type="email" class="form-control text-muted"
            placeholder="${t.confirm_email_placeholder}" disabled>
        </div>
      </div>`,

    url: `
      <div>
        <div class="input-group">
          <span class="input-group-text text-muted"><i class="ti ti-link"></i></span>
          <input type="url" class="form-control text-muted"
            placeholder="${t.url_placeholder}" disabled>
        </div>
        <div class="mt-2">
          <label class="form-label small text-muted">${t.allowed_domains_optional}</label>
          <input type="text" name="questions[${cardId}][url_config][allowed_domains]" class="url-allowed-domains form-control form-control-sm"
            placeholder="${t.allowed_domains_placeholder}"> 
          <div class="form-text">${t.allowed_domains_hint}</div>
        </div>
      </div>`,

    linear_scale: `
      <div>
        <div class="d-flex align-items-center gap-3 mb-3">
          <select class="scale-from form-select" disabled>
            <option value="0">0</option><option value="1" selected>1</option>
          </select>
          <span class="text-muted small">${t.to}</span>
          <select class="scale-to form-select" disabled>
            <option value="5" selected>5</option><option value="10">10</option>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <input type="text" name="questions[${cardId}][scale][label_low]" class="scale-label-low form-control"
              placeholder="${t.label_low}" value="${t.low}" disabled> 
          </div>
          <div class="col-md-6">
            <input type="text" name="questions[${cardId}][scale][label_high]" class="scale-label-high form-control"
              placeholder="${t.label_high}" value="${t.high}" disabled> 
          </div>
        </div>
      </div>`,
  };

  if (tplMap[type]) {
    optionsContainer.innerHTML = tplMap[type];
  } else if (type === 'multiple_choice' || type === 'checkboxes') {
    optionsContainer.innerHTML = `
      <div class="options-list">
        ${createOptionHTML(type, 1, t.option_1)}
        ${createOptionHTML(type, 2, t.option_2)}
        <div class="d-flex gap-2 mt-2">
          <button type="button" class="btn btn-link text-primary p-0 fw-medium"
            onclick="addOption(this, '${type}')">${t.add_option}</button>
          <span class="text-muted">${t.or}</span>
          <button type="button" class="btn btn-link text-info p-0 fw-medium"
            onclick="addOtherOption(this, '${type}')">${t.add_other}</button>
        </div>
      </div>`;
  } else if (type === 'dropdown') {
    optionsContainer.innerHTML = `
      <div class="options-list">
        ${createOptionHTML('dropdown', 1, t.option_1)}
        ${createOptionHTML('dropdown', 2, t.option_2)}
        <button type="button" class="btn btn-link text-primary p-0 fw-medium mt-2"
          onclick="addOption(this, 'dropdown')">${t.add_option}</button>
      </div>`;
  }

  addLogicPanel(questionCard);
}

/** Show / hide the e-mail confirmation field inside a question card. */
function toggleEmailConfirm(checkbox) {
  const confirmField = checkbox.closest('.question-options').querySelector('.email-confirm-field');
  if (confirmField) confirmField.classList.toggle('d-none', !checkbox.checked);
}

/* ═══════════════════════════════════════════════════════════════════
   OPTION HELPERS
══════════════════════════════════════════════════════════════════ */
function createOptionHTML(type, number, value = '') {
  const icon = type === 'multiple_choice' ? '○'
             : type === 'checkboxes'      ? '☐'
             :                              number + '.';
  return `
    <div class="d-flex align-items-center gap-2">
      <span class="text-muted">${icon}</span>
      <input type="text" name="questions[OPTION_ID][options][]" class="option-input form-control form-control-sm border-0 border-bottom flex-fill"
        placeholder="${t.option} ${number}"
        value="${escHtml(value)}"
        onchange="updateLogicPanelOptions(this)"> 
      <button type="button" class="btn btn-link text-muted p-0" onclick="removeOption(this)">
        <i class="ti ti-x"></i>
      </button>
    </div>`;
}

function captureCurrentLogic(questionCard) {
  const logic  = {};
  const inputs = questionCard.querySelectorAll('.logic-options select');
  const opts   = questionCard.querySelectorAll('.option-input');
  inputs.forEach((sel, i) => {
    const key = opts[i]?.value?.trim();
    if (key) logic[key] = sel.value;
  });
  return logic;
}

function addOption(button, questionType) {
  const optionsContainer = button.closest('.options-list');
  const qCard            = button.closest('.item-card');
  const currentLogic     = captureCurrentLogic(qCard);
  const optionNumber     = optionsContainer.querySelectorAll('.d-flex.align-items-center').length + 1;

  const tempDiv     = document.createElement('div');
  tempDiv.innerHTML = createOptionHTML(questionType, optionNumber, `${t.option} ${optionNumber}`);
  const newOpt      = tempDiv.firstElementChild;

  const refNode = button.parentElement === optionsContainer ? button : button.closest('div');
  optionsContainer.insertBefore(newOpt, refNode);

  if (questionType === 'dropdown') updateDropdownNumbers(optionsContainer);
  if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
    addLogicPanel(qCard, currentLogic);
  }
}

function addOtherOption(button, questionType) {
  const optionsContainer = button.closest('.options-list');
  const qCard            = button.closest('.item-card');
  const currentLogic     = captureCurrentLogic(qCard);
  const icon             = questionType === 'multiple_choice' ? '○' : '☐';

  const newOpt = document.createElement('div');
  newOpt.className = 'd-flex align-items-center gap-2';
  newOpt.innerHTML = `
    <span class="text-muted">${icon}</span>
    <input type="text" class="option-input form-control form-control-sm border-0 border-bottom flex-fill"
      value="${t.other_value}" readonly>
    <button type="button" class="btn btn-link text-muted p-0 ms-auto" onclick="removeOption(this)">
      <i class="ti ti-x"></i>
    </button>`;

  optionsContainer.insertBefore(newOpt, button.closest('div'));
  addLogicPanel(qCard, currentLogic);
}

function removeOption(button) {
  const optionDiv        = button.parentElement;
  const optionsContainer = optionDiv?.parentElement;
  if (!optionsContainer) return;

  const qCard        = button.closest('.item-card');
  const questionType = qCard.querySelector('.question-type').value;
  const currentLogic = captureCurrentLogic(qCard);

  optionDiv.remove();

  if (optionsContainer.querySelector('.text-muted')?.textContent.includes('.')) {
    updateDropdownNumbers(optionsContainer);
  }
  if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
    addLogicPanel(qCard, currentLogic);
  }
}

function updateDropdownNumbers(container) {
  container.querySelectorAll('.d-flex.align-items-center').forEach((opt, i) => {
    const span = opt.querySelector('span.text-muted');
    if (span && span.textContent.includes('.')) {
      span.textContent = (i + 1) + '.';
    }
  });
}

function updateLogicPanelOptions(input) {
  const qCard        = input.closest('.item-card');
  const questionType = qCard.querySelector('.question-type').value;
  if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
    addLogicPanel(qCard);
  }
}

/* ═══════════════════════════════════════════════════════════════════
   LOGIC PANEL
══════════════════════════════════════════════════════════════════ */
function addLogicPanel(questionCard, currentLogic = {}) {
  const existing = questionCard.querySelector('.logic-panel');
  if (existing) existing.remove();

  const questionType = questionCard.querySelector('.question-type').value;
  if (!['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) return;
  if (questionType === 'checkboxes') return;

  const sections = getSectionsList();
  const options  = questionCard.querySelectorAll('.option-input');

  const logicHTML = `
    <div class="logic-panel mt-4 p-4 rounded bg-light-subtle border">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="small fw-medium text-primary">${t.response_logic}</span>
        <button type="button" onclick="toggleLogicOptions(this)"
          class="btn btn-link text-primary p-0 small">${t.configure}</button>
      </div>
      <div class="logic-options d-none">
        ${Array.from(options).map((opt, idx) => `
          <div class="d-flex align-items-center gap-3 small mb-3">
            <span class="text-muted" style="min-width:6rem;">
              ${t.if} "${escHtml(opt.value) || t.option + ' ' + (idx + 1)}":
            </span>
            <select class="option-logic-${idx} form-select form-select-sm flex-fill">
              <option value="continue">${t.continue_next_section}</option>
              ${sections.map(s => `<option value="section-${s.id}">${t.go_to} ${s.title}</option>`).join('')}
              <option value="submit">${t.submit_form}</option>
            </select>
          </div>`).join('')}
      </div>
    </div>`;

  const footerDiv = questionCard.querySelector('.mt-4.pt-4.border-top');
  footerDiv.insertAdjacentHTML('beforebegin', logicHTML);

  const newSelects = questionCard.querySelectorAll('.logic-options select');
  newSelects.forEach((select, idx) => {
    const optVal = options[idx]?.value?.trim() ?? '';
    select.value = currentLogic[optVal] || 'continue';
  });
}

function toggleLogicOptions(button) {
  const opts = button.closest('.logic-panel').querySelector('.logic-options');
  opts.classList.toggle('d-none');
  button.textContent = opts.classList.contains('d-none') ? t.configure : t.hide;
}

/* ═══════════════════════════════════════════════════════════════════
   SAVE FORM
══════════════════════════════════════════════════════════════════ */
function saveForm() {
  const formTitle      = document.getElementById('formTitle').value.trim();
  const organizationId = document.getElementById('organization_id').value;

  if (!formTitle) {
    releaseWarningAlert({ message: t.form_title_required });
    return;
  }
  if (!organizationId) {
    releaseWarningAlert({ message: t.organization_required });
    return;
  }

  const formData = { title: formTitle, organization_id: organizationId, items: [] };
  const container = document.getElementById('itemsContainer');

  Array.from(container.children).forEach(child => {
    if (child.classList.contains('drop-placeholder')) return;
    const itemType = child.dataset.itemType;

    if (itemType === 'section') {
      const section = {
        type:        'section',
        title:       child.querySelector('.section-title').value,
        description: child.querySelector('.section-description').value,
        logic:       child.querySelector('.section-logic').value,
        questions:   [],
      };
      child.querySelectorAll('.question-card').forEach(qCard => {
        section.questions.push(extractQuestionData(qCard));
      });
      formData.items.push(section);
    } else if (itemType === 'question') {
      formData.items.push(extractQuestionData(child));
    }
  });

  fetch(FB_CONFIG.routes.saveForm, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ form_fields_data: formData }),
  })
    .then(r => r.json())
    .then(data => {
      if (!data?.success) {
        releaseFailureAlert({ message: data?.message || t.error_saving_form });
        return;
      }
      const wasUpdated = data.action === 'updated';
      releaseSuccessAlert({
        message: wasUpdated ? t.form_updated_successfully : t.form_saved_successfully
      });
      // Reload the builder for the same form so the edited values
      // survive a refresh and the form remains accessible for further
      // edits. Previously edit-mode saves navigated to show-form, which
      // broke the "stays accessible after saving" expectation.
      setTimeout(() => {
        if (!data.id) return;
        const orgParam = data.organization_id ? '?org_id=' + data.organization_id : '';
        window.location.href = `/dashboard/show-builder/${data.id}${orgParam}`;
      }, 1500);
    })
    .catch(() => releaseFailureAlert({ message: t.error_saving_form }));
}

function extractQuestionData(questionCard) {
  const questionType = questionCard.querySelector('.question-type').value;
  const question = {
    type:          'question',
    title:         questionCard.querySelector('.question-title').value,
    question_type: questionType,
    required:      questionCard.querySelector('.question-required').checked,
    options:       [],
    logic:         {},
  };

  if (['multiple_choice', 'checkboxes', 'dropdown'].includes(questionType)) {
    questionCard.querySelectorAll('.option-input').forEach((input, idx) => {
      const val = input.value.trim();
      if (!val) return;
      const key = val === t.other_value ? '__OTHER__' : val;
      question.options.push(key);
      if (questionType === 'multiple_choice' || questionType === 'dropdown') {
        const logicSel = questionCard.querySelector(`.option-logic-${idx}`);
        if (logicSel) question.logic[key] = logicSel.value;
      }
    });
  } else if (questionType === 'linear_scale') {
    question.scale = {
      from:       questionCard.querySelector('.scale-from')?.value,
      to:         questionCard.querySelector('.scale-to')?.value,
      label_low:  questionCard.querySelector('.scale-label-low')?.value,
      label_high: questionCard.querySelector('.scale-label-high')?.value,
    };
  } else if (questionType === 'number') {
    question.number_config = {
      min:  questionCard.querySelector('.number-min')?.value  ?? '',
      max:  questionCard.querySelector('.number-max')?.value  ?? '',
      step: questionCard.querySelector('.number-step')?.value ?? '',
    };
  } else if (questionType === 'email') {
    question.email_config = {
      confirm: questionCard.querySelector('.email-confirm-toggle')?.checked ?? false,
    };
  } else if (questionType === 'url') {
    question.url_config = {
      allowed_domains: questionCard.querySelector('.url-allowed-domains')?.value.trim() ?? '',
    };
  }

  return question;
}

/* ═══════════════════════════════════════════════════════════════════
   POPULATE (edit mode)
══════════════════════════════════════════════════════════════════ */
function populateForm(formData, builderData) {
  if (formData?.form_name) {
    document.getElementById('formTitle').value = formData.form_name;
  }
  if (Array.isArray(builderData)) {
    builderData.forEach(item => populateSection(item));
  }
}

function populateSection(sectionData) {
  const sectionEl = addSection(sectionData);

  if (sectionData.id)    sectionEl.dataset.dbId = sectionData.id;
  if (sectionData.logic) sectionEl.querySelector('.section-logic').value = sectionData.logic;

  if (Array.isArray(sectionData.questions)) {
    const zone = sectionEl.querySelector('.section-questions');
    sectionData.questions.forEach(qData => {
      const questionId  = itemCounter++;
      const tempDiv     = document.createElement('div');
      tempDiv.innerHTML = createQuestionHTML(questionId, true);
      const qEl         = tempDiv.firstElementChild;
      zone.appendChild(qEl);
      setupQuestionDrag(qEl);
      populateQuestionData(qEl, qData);
    });
  }
}

function populateQuestionData(questionCard, questionData) {
  const title        = questionData.title         ?? questionData.label       ?? '';
  const questionType = questionData.question_type  ?? questionData.type        ?? 'short_answer';
  const required     = questionData.required       ?? questionData.is_required ?? false;
  const options      = questionData.options        ?? [];
  const logic        = questionData.logic          ?? {};
  const scale        = questionData.scale          ?? null;
  const numberConfig = questionData.number_config  ?? null;
  const emailConfig  = questionData.email_config   ?? null;
  const urlConfig    = questionData.url_config     ?? null;

  if (questionData.id) questionCard.dataset.dbId = questionData.id;

  const titleInput = questionCard.querySelector('.question-title');
  if (titleInput) titleInput.value = title;

  const typeSelect = questionCard.querySelector('.question-type');
  if (typeSelect) {
    typeSelect.value = questionType;
    updateQuestionOptions(typeSelect);
  }

  const reqBox = questionCard.querySelector('.question-required');
  if (reqBox) reqBox.checked = required;

  if (['multiple_choice', 'checkboxes', 'dropdown'].includes(questionType) && options.length) {
    const optionsList = questionCard.querySelector('.options-list');
    optionsList.querySelectorAll('.d-flex.align-items-center.gap-2').forEach(n => n.remove());

    let hasOther = false;
    const regularOpts = options.filter(o => {
      if (o === '__OTHER__') { hasOther = true; return false; }
      return true;
    });

    regularOpts.forEach((opt, idx) => {
      const tempDiv     = document.createElement('div');
      tempDiv.innerHTML = createOptionHTML(questionType, idx + 1, opt);
      const btnRef      = optionsList.querySelector('.d-flex.gap-2.mt-2')
                       || optionsList.querySelector('button[onclick^="addOption"]');
      optionsList.insertBefore(tempDiv.firstElementChild, btnRef);
    });

    if (hasOther) {
      const otherBtn = optionsList.querySelector('button[onclick^="addOtherOption"]');
      if (otherBtn) addOtherOption(otherBtn, questionType);
    }

    if (questionType === 'dropdown') updateDropdownNumbers(optionsList);
  }

  if (questionType === 'linear_scale' && scale) {
    const get = cls => questionCard.querySelector(cls);
    if (get('.scale-from'))       get('.scale-from').value       = scale.from       ?? 1;
    if (get('.scale-to'))         get('.scale-to').value         = scale.to         ?? 5;
    if (get('.scale-label-low'))  get('.scale-label-low').value  = scale.label_low  ?? t.low;
    if (get('.scale-label-high')) get('.scale-label-high').value = scale.label_high ?? t.high;
  }

  if (questionType === 'number' && numberConfig) {
    const get = cls => questionCard.querySelector(cls);
    if (get('.number-min'))  get('.number-min').value  = numberConfig.min  ?? '';
    if (get('.number-max'))  get('.number-max').value  = numberConfig.max  ?? '';
    if (get('.number-step')) get('.number-step').value = numberConfig.step ?? '';
  }

  if (questionType === 'email' && emailConfig) {
    const toggle = questionCard.querySelector('.email-confirm-toggle');
    if (toggle) {
      toggle.checked = emailConfig.confirm ?? false;
      toggleEmailConfirm(toggle);
    }
  }

  if (questionType === 'url' && urlConfig) {
    const domainsInput = questionCard.querySelector('.url-allowed-domains');
    if (domainsInput) domainsInput.value = urlConfig.allowed_domains ?? '';
  }

  if (['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) {
    addLogicPanel(questionCard, logic);
  }
}

/* ═══════════════════════════════════════════════════════════════════
   FORM MODAL HELPERS (edit / copy / delete)
══════════════════════════════════════════════════════════════════ */
function showForm(set, id, title, organization_id) {
  $('#mtitle').val(title);
  if (typeof organization_id !== 'undefined') {
    $('#modal_organization_id').val(organization_id).trigger('change');
  }
  const labels = { 0: t.edit_form, 1: t.copy_form, 2: t.add_new_form };
  $('#AddFormTitle').text(labels[set] ?? '');
  $('#modelSubmit').attr('onclick', `DoChange(${set},${id},'${title}',${organization_id})`);
  $('#AddForm').modal('show');
}

async function deleteForm(set, id, title, organization_id = 0) {
  releaseWarningAlert({
    title:             t.are_you_sure,
    message:           t.deleting_form,
    confirmButtonText: t.yes,
    cancelButtonText:  t.no,
    confirmColor:      'danger',
    onConfirm: async () => {
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      $.ajax({
        type: 'POST',
        url:  '/dashboard/builder/' + set,
        data: { id, title, organization_id },
        success: () => { location.href = '/dashboard/show-builder'; },
        error:   () => { releaseFailureAlert({ message: t.error_occurred }); },
      });
    },
  });
}

/* ═══════════════════════════════════════════════════════════════════
   FILTER MODAL: org → form cascade
══════════════════════════════════════════════════════════════════ */
function updateFormSelect() {
  const selectedOrg   = $('#organizationFilter').val();
  const filteredForms = selectedOrg == 0
    ? FB_CONFIG.allForms
    : FB_CONFIG.allForms.filter(f => String(f.organization_id) === String(selectedOrg));

  const $fs = $('#formFilter');
  $fs.find('option:not(:first)').remove();

  filteredForms.forEach(form => {
    let text = form.form_name;
    if (form.organization && !FB_CONFIG.isOrganizationManager) {
      text += ' - ' + (form.organization['name_' + FB_CONFIG.lang] || '');
    }
    if (form.cycles?.length) {
      text += ' ' + form.cycles.map(c => c.title).join(' - ');
    }
    const selected = (FB_CONFIG.formData && form.id == FB_CONFIG.formData.id) ? ' selected' : '';
    $fs.append(`<option value="${form.id}"${selected}>${text}</option>`);
  });

  if ($fs.hasClass('select2-hidden-accessible')) $fs.trigger('change');
}

/* ═══════════════════════════════════════════════════════════════════
   UTILITY
══════════════════════════════════════════════════════════════════ */
function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}