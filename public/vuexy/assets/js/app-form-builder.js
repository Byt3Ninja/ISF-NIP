/**
 * app-form-builder.js  –  Form Builder
 *
 * All user-facing strings come from FB_CONFIG.translations (set in the Blade
 * @section('scripts') block). There are zero {{ __(...) }} calls in this file.
 *
 * Drag-and-drop strategy (GV-10420)
 * ──────────────────────
 * • SortableJS (vendor/libs/sortablejs) in pointer "fallback" mode: one list for
 *   the sections (#itemsContainer) and one per .section-questions zone, all
 *   sharing a group so a question can move to any section.
 * • Only the grip (.fb-handle) starts a drag; it also moves with ArrowUp/Down.
 * • While an item is held the builder folds to an outline (one line per section
 *   and question), so every target is on screen without scrolling.
 * • A move only changes the page. "Save" persists the layout, like every other
 *   edit in the builder.
 */

/* ═══════════════════════════════════════════════════════════════════
   STATE  –  declared first so the IIFE below can safely call any
             function that reads these bindings.
══════════════════════════════════════════════════════════════════ */
let itemCounter      = 0;
let questionCounters = {};   // sectionId → count (kept for compat)

/* Drag & drop */
const DRAG_TOLERANCE = 4;    // px the pointer must travel before a press becomes a drag

/* forceFallback drags a light copy with pointer events instead of the browser's
   native drag, which painted the whole section as a translucent screenshot,
   could not scroll the page and showed a "no drop" cursor between sections. */
const SORT_OPTIONS = {
  animation:            window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180,
  forceFallback:        true,
  fallbackTolerance:    DRAG_TOLERANCE,
  scroll:               true,
  scrollSensitivity:    96,
  scrollSpeed:          18,
  emptyInsertThreshold: 24,
  ghostClass:           'fb-slot',
  fallbackClass:        'fb-drag-proxy',
};

let sectionOrderAtGrab = [];      // section wrappers as they were when a section was picked up
let disarmFold         = () => {};

/* Convenience alias – assigned inside the IIFE once FB_CONFIG is live */
let t;   // = FB_CONFIG.translations

/* Unsaved-changes guard; module-scoped so the form modal can clear it on save */
let isBuilderFormDirty = false;

(function ($) {
  'use strict';

  t = FB_CONFIG.translations;   // single source of truth for all UI strings

  /* ─── Hide org filter for org-managers ─────────────────────────── */
  if (FB_CONFIG.isOrganizationManager) {
    $('#organizationFilterContainer').hide();
  }

  /* ─── Bootstrap the form ────────────────────────────────────────── */
  initSorting();
  if (FB_CONFIG.mode === 'ADD') {
    addSection();
  } else {
    populateForm(FB_CONFIG.formData, FB_CONFIG.builderData);
  }

  /* ─── Filter modal: organisation → form cascade ─────────────────── */
  $('#organizationFilter').on('change', updateFormSelect);
  updateFormSelect();

  let initialState = $('#builder-form').serialize();
  isBuilderFormDirty = false;

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
      releaseAlert('warning', {
        title: FB_CONFIG.translations.confirm_switch,
        message: FB_CONFIG.translations.confirm_switch_message,
        onConfirm: () => location.href = url,
      });

      return;
    }

    location.href = url;
  });

  $('#new-form-btn').on('click', function (e) {
    e.preventDefault();
    const url = '/dashboard/show-builder';

    if (isBuilderFormDirty) {
      releaseAlert('warning', {
        title:             t.are_you_sure,
        message:           t.unsaved_changes_msg,
        confirmText: t.discard_create,
        cancelText:  t.cancel,
        onConfirm:         () => location.href = url,
      });
      return;
    }

    location.href = url;
  });

})(jQuery);

/* ═══════════════════════════════════════════════════════════════════
   DRAG & DROP
   Sections live inside #itemsContainer; questions inside .section-questions.
══════════════════════════════════════════════════════════════════ */
function initSorting() {
  const container = document.getElementById('itemsContainer');

  Sortable.create(container, {
    ...SORT_OPTIONS,
    draggable:  '.section-wrapper',
    handle:     '.fb-section-handle',
    onChoose:   e => { sectionOrderAtGrab = getSectionWrappers(); armFold(e, true); },
    onUnchoose: e => unfoldBuilder(e.item),
    onEnd:      e => { if (e.oldIndex !== e.newIndex) afterSectionMove(e.item, sectionOrderAtGrab); },
  });

  container.addEventListener('keydown', onHandleKeydown);

  // A user's jump choice must survive the panel rebuilds a move triggers.
  container.addEventListener('change', e => {
    if (!e.target.matches('.logic-options select')) return;
    const card = e.target.closest('.item-card');
    card._savedLogic = captureCurrentLogic(card);
  });
}

function initQuestionSorting(zone) {
  Sortable.create(zone, {
    ...SORT_OPTIONS,
    group:      'fb-questions',
    draggable:  '.question-card',
    handle:     '.fb-question-handle',
    onChoose:   e => armFold(e, false),
    onUnchoose: e => unfoldBuilder(e.item),
    onEnd:      e => {
      if (e.from !== e.to || e.oldIndex !== e.newIndex) afterQuestionMove(e.item, e.from !== e.to);
    },
  });
}

function getSectionWrappers() {
  return [...document.querySelectorAll('#itemsContainer > .section-wrapper')];
}

/* ── Outline mode ──
   Folding waits for the press to become a drag (a plain click on the grip
   changes nothing) and runs in the capture phase, i.e. before SortableJS
   measures the item to build the copy that follows the pointer. */
function armFold(evt, sectionsOnly) {
  const start = evt.originalEvent.touches?.[0] ?? evt.originalEvent;
  const onMove = move => {
    if (Math.max(Math.abs(move.clientX - start.clientX), Math.abs(move.clientY - start.clientY)) < DRAG_TOLERANCE) return;
    disarmFold();
    foldBuilder(evt.item, sectionsOnly);
  };
  disarmFold();
  document.addEventListener('pointermove', onMove, true);
  disarmFold = () => document.removeEventListener('pointermove', onMove, true);
}

/** Collapse every section (and, for a question drag, every question) to one line. */
function foldBuilder(item, sectionsOnly) {
  const container = document.getElementById('itemsContainer');
  const gripY     = midY(gripOf(item));

  getSectionWrappers().forEach((section, index) => {
    const count = section.querySelectorAll('.question-card').length;
    section.querySelector('.section-card > .card-body').prepend(outlineRow(
      section.querySelector('.section-title').value.trim() || `${t.section} ${index + 1}`,
      t.questions_count.replace(':count', count)
    ));
  });

  if (!sectionsOnly) {
    container.querySelectorAll('.question-card').forEach(card => {
      const type = card.querySelector('.question-type');
      card.querySelector('.card-body').prepend(outlineRow(
        card.querySelector('.question-title').value.trim() || t.question,
        type.options[type.selectedIndex]?.text ?? ''
      ));
    });
  }

  // Hold the page height so the page cannot shrink under the pointer.
  container.style.minHeight = container.offsetHeight + 'px';
  container.classList.add('fb-outline', sectionsOnly ? 'fb-outline--sections' : 'fb-outline--questions');
  document.body.classList.add('fb-dragging');

  // Bring the held grip back under the pointer, or the item would start out
  // somewhere else. Near the top of the page there is not enough room to
  // scroll up, so pad the list from above for the length of the drag.
  const lift = gripY - midY(gripOf(item));
  if (lift > window.scrollY) container.style.paddingTop = (lift - window.scrollY) + 'px';
  scrollGripTo(item, gripY);
}

function unfoldBuilder(item) {
  disarmFold();
  const container = document.getElementById('itemsContainer');
  if (!container.classList.contains('fb-outline')) return;

  const gripY = midY(gripOf(item));
  container.querySelectorAll('.fb-outline-row').forEach(row => row.remove());
  container.classList.remove('fb-outline', 'fb-outline--sections', 'fb-outline--questions');
  container.style.minHeight = container.style.paddingTop = '';
  document.body.classList.remove('fb-dragging');
  // Keep the dropped item where it was let go.
  scrollGripTo(item, gripY);
}

/** The grip of an item: its outline row's icon while folded, its handle otherwise. */
function gripOf(item) {
  return item.querySelector('.fb-outline-row > .ti') || item.querySelector('.fb-handle');
}

function midY(el) {
  const rect = el.getBoundingClientRect();
  return rect.top + rect.height / 2;
}

/** Scroll so the item's grip is at viewport `y`. Instant: Bootstrap makes page scrolling
    smooth, and SortableJS places its drag copy from where the item is right now. */
function scrollGripTo(item, y) {
  window.scrollBy({ top: midY(gripOf(item)) - y, behavior: 'instant' });
}

function outlineRow(title, meta) {
  const row = document.createElement('div');
  row.className = 'fb-outline-row';
  row.innerHTML = '<i class="ti ti-grip-vertical"></i><span class="fb-outline-title"></span><span class="fb-outline-meta"></span>';
  row.querySelector('.fb-outline-title').textContent = title;
  row.querySelector('.fb-outline-meta').textContent  = meta;
  return row;
}

/* ── Keyboard: focus a grip, then ArrowUp / ArrowDown ── */
function onHandleKeydown(e) {
  const handle = e.target.closest('.fb-handle');
  if (!handle || (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;
  e.preventDefault();

  const up   = e.key === 'ArrowUp';
  const item = handle.closest('.question-card') || handle.closest('.section-wrapper');
  const moved = item.classList.contains('question-card')
    ? moveQuestionByKey(item, up)
    : moveSectionByKey(item, up);

  if (moved) {
    handle.focus({ preventScroll: true });
    handle.scrollIntoView({ block: 'nearest' });
  }
}

function moveSectionByKey(section, up) {
  const sibling = up ? section.previousElementSibling : section.nextElementSibling;
  if (!sibling?.classList.contains('section-wrapper')) return false;

  const orderBefore = getSectionWrappers();
  up ? sibling.before(section) : sibling.after(section);
  afterSectionMove(section, orderBefore);
  return true;
}

/** Within its section first; past the first/last question it moves into the neighbouring section. */
function moveQuestionByKey(card, up) {
  const sibling = up ? card.previousElementSibling : card.nextElementSibling;
  if (sibling) {
    up ? sibling.before(card) : sibling.after(card);
    afterQuestionMove(card, false);
    return true;
  }

  const sections = getSectionWrappers();
  const target   = sections[sections.indexOf(card.closest('.section-wrapper')) + (up ? -1 : 1)];
  if (!target) return false;

  const zone = target.querySelector('.section-questions');
  up ? zone.append(card) : zone.prepend(card);
  afterQuestionMove(card, true);
  return true;
}

/* ── After a move ── */
function afterSectionMove(section, orderBefore) {
  const sectionTargets = remapSectionTargets(orderBefore);
  updateSectionNumbers();
  updateAllLogicPanels(sectionTargets);
  afterMove(section);
}

function afterQuestionMove(card, changedSection) {
  // GV-8347: a question cannot jump to its own section, so its valid targets
  // change with the section it lives in.
  if (changedSection) refreshLogicPanels();
  afterMove(card);
}

function afterMove(item) {
  $('#builder-form').trigger('change');   // re-evaluates the unsaved-changes flag

  item.classList.remove('fb-moved');
  void item.offsetWidth;                  // restart the highlight on repeated moves
  item.classList.add('fb-moved');
  // animationcancel: folding hides cards, which cancels a running highlight.
  const clear = () => item.classList.remove('fb-moved');
  item.addEventListener('animationend', clear, { once: true });
  item.addEventListener('animationcancel', clear, { once: true });
}

/**
 * Jump targets are positional ("section-2" = third section). Moving or deleting
 * a section shifts positions, so point every target back at the section it
 * meant (a jump to a deleted section becomes "continue"); otherwise jumps
 * silently land on whichever section took the old place.
 *
 * Question targets are remapped in place; the sections' own targets are
 * returned (in current order) for updateAllLogicPanels(), because an old
 * <select> may not offer the new value.
 */
function remapSectionTargets(orderBefore) {
  const orderAfter = getSectionWrappers();
  const remap = value => {
    const match = /^section-(\d+)$/.exec(value ?? '');
    const section = match && orderBefore[match[1]];
    if (!section) return value;
    const index = orderAfter.indexOf(section);
    return index === -1 ? 'continue' : `section-${index}`;
  };

  document.querySelectorAll('.logic-panel').forEach(panel => {
    const card = panel.closest('.item-card');
    card._savedLogic = Object.fromEntries(
      Object.entries(card._savedLogic || {}).map(([key, target]) => [key, remap(target)])
    );
  });
  return [...document.querySelectorAll('.section-logic')].map(select => remap(select.value));
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION HELPERS
══════════════════════════════════════════════════════════════════ */
function getSectionsList() {
  const sections = [];
  // GV-8351: `id` MUST be the section's POSITIONAL index, not `dataset.itemId`.
  // itemId comes from a counter shared with questions, so on reload sections get
  // ids like 0, 4, 9 — while saveForm() normalizes every `section-N` target to a
  // positional index (see sectionIdMap) and the founder-side wizard resolves
  // `section-N` against the positional order of its rendered sections. Emitting
  // itemIds here put the <option> values in a different id-space than the stored
  // logic, so restoring a saved target matched no option and the select silently
  // blanked — losing every per-option jump on the next save.
  document.querySelectorAll('.section-wrapper').forEach((section, index) => {
    const titleInput = section.querySelector('.section-title');
    const title = (titleInput && titleInput.value.trim())
      ? titleInput.value.trim()
      : `${t.section} ${index + 1}`;
    sections.push({ id: index, title });
  });
  return sections;
}

/**
 * GV-8347: positional index of the section a question card lives in, in the
 * same id-space getSectionsList() emits (document order of .section-wrapper).
 * Returns -1 when the card is not inside a section yet. Used to drop the
 * current section from a question's Response Logic destinations: jumping to
 * the section you are already in is not a valid target.
 */
function getOwningSectionIndex(questionCard) {
  const wrapper = questionCard.closest('.section-wrapper');
  if (!wrapper) return -1;
  return [...document.querySelectorAll('.section-wrapper')].indexOf(wrapper);
}

/** @param {string[]|null} sectionTargets "After this section" values in section order; default: keep current. */
function updateAllLogicPanels(sectionTargets = null) {
  // GV-8351: rebuild via refreshLogicPanels() so each question replays its own
  // saved targets. The previous `addLogicPanel(qCard)` (no currentLogic) reset
  // every panel to `continue` — and addSection() calls this on every section
  // added, so simply loading a saved form wiped all per-option jumps.
  refreshLogicPanels();

  const sections = getSectionsList();
  document.querySelectorAll('.section-logic').forEach((select, ownIndex) => {
    const current = sectionTargets ? sectionTargets[ownIndex] : select.value;
    // A section cannot jump to itself: the applicant wizard treats that as a
    // loop and ends the form there.
    select.innerHTML =
      `<option value="continue">${t.continue_next_section}</option>` +
      sections.filter(s => s.id !== ownIndex)
        .map(s => `<option value="section-${s.id}">${t.go_to} ${s.title}</option>`).join('') +
      `<option value="submit">${t.submit_form}</option>`;
    select.value = select.querySelector(`option[value="${cssEscape(current)}"]`) ? current : 'continue';
  });
}

function updateSectionNumbers() {
  document.querySelectorAll('.section-wrapper').forEach((section, index) => {
    const badgeNum = index + 1;
    
    // Update the Section badge (e.g. "Section 1", "Section 2")
    const badge = section.querySelector('.badge.bg-primary');
    if (badge) {
      badge.textContent = `${t.section} ${badgeNum}`;
    }
    
    // Update the Add Question button text (e.g. "Add Question to Section 1")
    const addBtn = section.querySelector('button[onclick^="addQuestionToSection"]');
    if (addBtn) {
      addBtn.innerHTML = `<i class="ti ti-plus"></i> ${t.add_question_section} ${badgeNum}`;
    }
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
    <div class="section-wrapper mb-5" data-item-id="${sectionId}" data-item-type="section">
      <div class="position-absolute start-0 z-1" style="top:-0.5rem;">
        <span class="badge bg-primary">${t.section} ${badgeNum}</span>
      </div>
      <div class="item-card section-card card mb-3">
        <div class="card-body">
          <div class="d-flex align-items-start gap-3 mt-3">
            <span class="fb-handle fb-section-handle mt-1" role="button" tabindex="0"
              title="${t.drag_to_reorder}" aria-label="${t.drag_to_reorder}">
              <i class="ti ti-grip-vertical"></i>
            </span>
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

      <div class="section-questions d-flex flex-column gap-3 mb-4 px-4 ms-3" data-drop-hint="${t.drop_question_here}"></div>

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
  initQuestionSorting(sectionEl.querySelector('.section-questions'));

  if (sectionData?.logic) {
    sectionEl.querySelector('.section-logic').value = sectionData.logic;
  }

  updateSectionNumbers();
  // A section added by hand becomes a jump target for the others. (While a
  // saved form loads, populateForm() rebuilds once every section exists.)
  if (!sectionData) updateAllLogicPanels();
  return sectionEl;
}

function deleteSection(button) {
  const wrapper      = button.closest('.section-wrapper');
  const hasQuestions = wrapper.querySelectorAll('.question-card').length > 0;
  if (hasQuestions && !confirm(t.delete_section_questions)) return;
  const orderBefore = getSectionWrappers();
  Sortable.get(wrapper.querySelector('.section-questions'))?.destroy();
  wrapper.remove();
  const sectionTargets = remapSectionTargets(orderBefore);
  updateSectionNumbers();
  updateAllLogicPanels(sectionTargets);
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
          <span class="fb-handle fb-question-handle mt-1" role="button" tabindex="0"
            title="${t.drag_to_reorder}" aria-label="${t.drag_to_reorder}">
            <i class="ti ti-grip-vertical"></i>
          </span>
          <div class="flex-fill">
            <input type="text" name="questions[${itemId}][title]"
              class="question-title form-control form-control-lg border-bottom mb-4"
              placeholder="${t.question}"> 

            <select name="questions[${itemId}][question_type]" class="question-type form-select mb-3" onchange="updateQuestionOptions(this)">
              <option value="short_answer">${t.short_answer}</option> 
              <option value="paragraph">${t.paragraph}</option>
              <option value="multiple_choice">${t.multiple_choice}</option>
              <option value="checkboxes">${t.checkboxes}</option>
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
              placeholder="1" min="0" step="any">
          </div>
        </div>
      </div>`,

    email: `
      <div>
        <input type="email" class="form-control text-muted"
          placeholder="${t.email_placeholder}" disabled>
       `,

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
          <select name="questions[${cardId}][scale][from]" class="scale-from form-select">
            <option value="0">0</option><option value="1" selected>1</option>
          </select>
          <span class="text-muted small">${t.to}</span>
          <select name="questions[${cardId}][scale][to]" class="scale-to form-select">
            <option value="5" selected>5</option><option value="10">10</option>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <input type="text" name="questions[${cardId}][scale][label_low]" class="scale-label-low form-control"
              placeholder="${t.label_low}" value="${t.low}">
          </div>
          <div class="col-md-6">
            <input type="text" name="questions[${cardId}][scale][label_high]" class="scale-label-high form-control"
              placeholder="${t.label_high}" value="${t.high}">
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
        onfocus="rememberOptionValue(this)"
        onchange="updateLogicPanelOptions(this)"> 
      <button type="button" class="btn btn-link text-muted p-0" onclick="removeOption(this)">
        <i class="ti ti-x"></i>
      </button>
    </div>`;
}

function captureCurrentLogic(questionCard) {
  // Key off each select's own `data-logic-for` rather than pairing selects with
  // `.option-input`s by position — the two lists drift apart as soon as an
  // option is blank, added, or removed, which stored the target under the
  // wrong option value.
  const logic = {};
  questionCard.querySelectorAll('.logic-options select').forEach(sel => {
    const key = sel.dataset.logicFor;
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

  // GV-8346: a question may hold at most ONE "Other" option. The button stays
  // clickable, and every extra row it inserted was serialized as its own
  // `__OTHER__` entry by extractQuestionData(), so the founder-facing form
  // rendered one "Other" choice per click.
  const hasOther = Array.from(optionsContainer.querySelectorAll('.option-input'))
    .some(input => input.value.trim() === t.other_value);
  if (hasOther) return;

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

/** Stash an option's text before the user edits it, so a rename can be tracked. */
function rememberOptionValue(input) {
  input.dataset.previousValue = input.value;
}

function updateLogicPanelOptions(input) {
  const qCard        = input.closest('.item-card');
  const questionType = qCard.querySelector('.question-type').value;
  if (!['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) return;

  // A question's logic map is keyed by option TEXT, so renaming an option
  // orphans its configured target. Carry it over to the new key before the
  // panel is rebuilt.
  const oldKey = logicKeyFor(input.dataset.previousValue ?? '');
  const newKey = logicKeyFor(input.value);
  const saved  = qCard._savedLogic || captureCurrentLogic(qCard);

  if (oldKey && newKey && oldKey !== newKey && saved[oldKey]) {
    saved[newKey] = saved[oldKey];
    delete saved[oldKey];
    qCard._savedLogic = saved;
  }
  input.dataset.previousValue = input.value;

  addLogicPanel(qCard, qCard._savedLogic || {});
}

/* ═══════════════════════════════════════════════════════════════════
   LOGIC PANEL
══════════════════════════════════════════════════════════════════ */
/**
 * The key a question's `logic` map is stored under for a given option input
 * value. Mirrors extractQuestionData(): the free-text "Other" option is stored
 * as the sentinel `__OTHER__`, everything else as its trimmed literal value.
 */
function logicKeyFor(rawValue) {
  const val = (rawValue ?? '').trim();
  return val === t.other_value ? '__OTHER__' : val;
}

/** Minimal CSS.escape fallback for attribute-selector values. */
function cssEscape(value) {
  return (window.CSS && CSS.escape)
    ? CSS.escape(value)
    : String(value).replace(/["\\]/g, '\\$&');
}

/**
 * Select the stored target for every option select in a question's logic panel.
 *
 * A `section-N` target whose <option> is not in the DOM yet (a forward jump seen
 * while the form is still being populated) is LEFT ALONE rather than reset to
 * `continue`, and the desired map stays on the card so a later
 * refreshLogicPanels() can finish the job once every section exists.
 */
function applySavedLogic(questionCard, currentLogic = {}) {
  const saved = (currentLogic && Object.keys(currentLogic).length)
    ? currentLogic
    : (questionCard._savedLogic || {});

  questionCard._savedLogic = saved;

  questionCard.querySelectorAll('.logic-options select').forEach(select => {
    const key = select.dataset.logicFor ?? '';
    const want = saved[key];
    if (!want) {
      select.value = 'continue';
      return;
    }
    // Only assign a value the select actually offers; otherwise the browser
    // blanks it and the target is silently lost on the next save.
    if (select.querySelector(`option[value="${cssEscape(want)}"]`)) {
      select.value = want;
    }
  });
}

/**
 * Rebuild every logic panel (option lists change when sections are added,
 * renamed, reordered or deleted) WITHOUT losing the configured targets: each
 * card replays its own remembered map. This is what makes a forward jump
 * survive populate — during populate the target section may not exist yet.
 */
function refreshLogicPanels() {
  document.querySelectorAll('.logic-panel').forEach(panel => {
    const qCard = panel.closest('.item-card');
    if (qCard) addLogicPanel(qCard, qCard._savedLogic || {});
  });
}

function addLogicPanel(questionCard, currentLogic = {}) {
  // Remember the intended targets before the panel is torn down, so a rebuild
  // triggered by an unrelated section edit cannot wipe them.
  if (currentLogic && Object.keys(currentLogic).length) {
    questionCard._savedLogic = currentLogic;
  } else if (questionCard.querySelector('.logic-panel')) {
    questionCard._savedLogic = captureCurrentLogic(questionCard);
  }

  const existing = questionCard.querySelector('.logic-panel');
  if (existing) existing.remove();

  const questionType = questionCard.querySelector('.question-type').value;
  if (!['multiple_choice', 'dropdown', 'checkboxes'].includes(questionType)) return;
  if (questionType === 'checkboxes') return;

  // GV-8347: the question's own section is not a valid jump destination.
  // Note this is deliberately NOT applied to the section-level "After this
  // section" select, whose id-space getSectionsList() must keep intact.
  const ownSectionIndex = getOwningSectionIndex(questionCard);
  const sections = getSectionsList().filter(s => s.id !== ownSectionIndex);
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
            <select class="option-logic-${idx} form-select form-select-sm flex-fill"
              data-logic-for="${escHtml(logicKeyFor(opt.value))}">
              <option value="continue">${t.continue_next_section}</option>
              ${sections.map(s => `<option value="section-${s.id}">${t.go_to} ${s.title}</option>`).join('')}
              <option value="submit">${t.submit_form}</option>
            </select>
          </div>`).join('')}
      </div>
    </div>`;

  const footerDiv = questionCard.querySelector('.mt-4.pt-4.border-top');
  footerDiv.insertAdjacentHTML('beforebegin', logicHTML);

  // Restore each select by the option's own logic key. Positional restoration
  // (`options[idx]`) desynced whenever an option row was blank or reordered.
  // `applySavedLogic` is a no-op for values whose <option> does not exist yet
  // (a forward jump during populate) — refreshLogicPanels() re-applies once the
  // whole form is in the DOM.
  applySavedLogic(questionCard, currentLogic);
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
    releaseAlert('warning', { hideCancel: true, message: t.form_title_required });
    return;
  }
  if (!organizationId) {
    releaseAlert('warning', { hideCancel: true, message: t.organization_required });
    return;
  }

  // Validate that no section is empty (has 0 questions)
  let hasEmptySection = false;
  document.querySelectorAll('#itemsContainer > .section-wrapper').forEach(wrapper => {
    if (wrapper.querySelectorAll('.question-card').length === 0) {
      hasEmptySection = true;
    }
  });

  if (hasEmptySection) {
    releaseAlert('warning', { hideCancel: true, message: t.empty_section_error });
    return;
  }

  const formData = { title: formTitle, organization_id: organizationId, items: [] };
  const container = document.getElementById('itemsContainer');

  // GV-8351: no itemId->index remap here any more. Every `section-N` <option>
  // value is now emitted as a POSITIONAL index by getSectionsList(), and
  // updateAllLogicPanels() regenerates those options from live DOM order on
  // every reorder/delete — so the selected values are already in the stored
  // id-space. The old remap keyed itemIds, which after this change would
  // mistake a positional value for an itemId and rewrite it to a wrong section.
  Array.from(container.children).forEach(child => {
    const itemType = child.dataset.itemType;

    if (itemType === 'section') {
      const sectionLogic = child.querySelector('.section-logic').value;

      const section = {
        type:        'section',
        title:       child.querySelector('.section-title').value,
        description: child.querySelector('.section-description').value,
        logic:       sectionLogic,
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
        releaseAlert('danger', { message: data?.message || t.error_saving_form });
        return;
      }
      const wasUpdated = data.action === 'updated';
      releaseAlert('success', {
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
    .catch(() => releaseAlert('danger', { message: t.error_saving_form }));
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
        // Read the logic select by the option's OWN value rather than by a
        // positional `.option-logic-${idx}` class: `idx` here counts only
        // non-empty options, so a blank option row above shifts it out of sync
        // with the panel's own indexes and the wrong target gets stored.
        const logicSel = questionCard.querySelector(`[data-logic-for="${cssEscape(key)}"]`);
        if (logicSel) {
          question.logic[key] = logicSel.value;
        }
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
    // GV-8351 / GV-10420: a question or a section can jump forward to a
    // section whose <option> did not exist yet while it was being populated.
    // Now that every section is in the DOM, rebuild the lists and replay the
    // saved targets one final time.
    updateAllLogicPanels(builderData.map(section => section.logic ?? 'continue'));
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
      populateQuestionData(qEl, qData);
    });
  }
}

function populateQuestionData(questionCard, questionData) {
  const title        = questionData.title         ?? questionData.label       ?? '';
  // "Dropdown" was retired as an authorable type (GV-3828). Existing questions
  // stored as `dropdown` are loaded as `multiple_choice` — both store a single
  // scalar answer, so this is a render-time coercion with no data migration.
  // The type <select> no longer offers a `dropdown` option, so leaving it as
  // `dropdown` would also leave the select with no matching value.
  const rawType      = questionData.question_type  ?? questionData.type        ?? 'short_answer';
  const questionType = rawType === 'dropdown' ? 'multiple_choice' : rawType;
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
  if (typeof organization_id !== 'undefined' && organization_id !== null) {
    $('#modal_organization_id').val(organization_id).trigger('change');
  }
  const labels = { 0: t.edit_form, 1: t.copy_form, 2: t.add_new_form };
  $('#AddFormTitle').text(labels[set] ?? '');

  // Bind the handler directly instead of building an inline onclick string:
  // titles may contain quotes, which would produce invalid JS.
  $('#modelSubmit').off('click.formModal').on('click.formModal', function () {
    submitFormModal(set, id);
  });
  $('#AddForm').modal('show');
}

/**
 * Persist the modify-form modal (rename = 0, copy = 1, create = 2).
 * Reads the live modal values so a changed organization/title is respected.
 */
function submitFormModal(set, id) {
  const $submit = $('#modelSubmit');
  if ($submit.prop('disabled')) return;

  const title           = $.trim($('#mtitle').val() || '');
  const organization_id = $('#modal_organization_id').val();

  if (!title) {
    releaseAlert('danger', { message: t.form_title_required });
    return;
  }
  if (!organization_id) {
    releaseAlert('danger', { message: t.organization_required });
    return;
  }

  $submit.prop('disabled', true);
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
  $.ajax({
    type: 'POST',
    url:  '/dashboard/builder/' + set,
    data: { id, title, organization_id },
    success: function (newId) {
      // Leaving the page after a successful save: don't trip the unsaved-changes guard.
      isBuilderFormDirty = false;
      const targetId = newId || id;
      location.href = targetId
        ? '/dashboard/show-builder/' + targetId
        : '/dashboard/show-builder';
    },
    error: function () {
      $submit.prop('disabled', false);
      releaseAlert('danger', { message: t.error_occurred });
    },
  });
}

async function deleteForm(set, id, title, organization_id = 0) {
  releaseAlert('warning', {
    title:             t.are_you_sure,
    message:           t.deleting_form,
    confirmText: t.yes,
    cancelText:  t.no,
    confirmColor:      'danger',
    onConfirm: async () => {
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      $.ajax({
        type: 'POST',
        url:  '/dashboard/builder/' + set,
        data: { id, title, organization_id },
        success: () => { location.href = '/dashboard/show-builder'; },
        error:   () => { releaseAlert('danger', { message: t.error_occurred }); },
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
  const hasSelect2 = $fs.data('select2') !== undefined;

  if (hasSelect2) $fs.select2('destroy');

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

  if (hasSelect2) {
    $fs.select2({
      placeholder: document.documentElement.lang === 'en' ? 'Select' : 'اختر',
      dropdownParent: $fs.parent()
    });
  }
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