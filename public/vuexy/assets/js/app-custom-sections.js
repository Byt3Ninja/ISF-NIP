/**
 * app-custom-sections.js
 *
 * Manages the Custom Sections tab on the Landing Page editor.
 * Zero dependency on FormValidation (fv) — all validation is hand-rolled.
 *
 * Requires:
 *   - window.CS_CONFIG  (injected by the blade partial)
 *   - Quill             (loaded globally)
 *   - Sortable          (loaded globally)
 *   - Bootstrap 5 Modal
 *   - jQuery / $  (already present in the host page)
 */

(function ($) {
  'use strict';

  /* ─────────────────────────────────────────────
     CONFIG  (set by blade via window.CS_CONFIG)
  ───────────────────────────────────────────── */
  const CFG  = window.CS_CONFIG || {};
  const LANG = CFG.lang || {};

  /* ─────────────────────────────────────────────
     QUILL REGISTRY
  ───────────────────────────────────────────── */
  /** @type {Map<Element, Quill>} */
  const quillMap = new Map();

  /* ══════════════════════════════════════════════
     1.  VISIBILITY HANDLING
  ══════════════════════════════════════════════ */

  /**
   * Update visibility option styling when a radio button is selected
   * @param {jQuery} $radio - The radio button that was changed
   */
  function updateVisibilityStyling($radio) {
    const $card = $radio.closest('.cs-card');
    const $container = $radio.closest('.row.g-2');
    
    // Remove 'checked' class from all visibility options in this card
    $container.find('.js-visibility-option').removeClass('checked');
    
    // Add 'checked' class to the parent container of the selected radio
    $radio.closest('.js-visibility-option').addClass('checked');
    
    // Update the hidden visibility input
    const selectedValue = $radio.val();
    $card.find('.cs-hidden-visibility').val(selectedValue);
    
    // Clear any visibility error
    setFieldError($card, 'visibility', '');
  }

  /**
   * Initialize visibility options for a card
   * @param {jQuery} $card 
   */
  function initVisibilityForCard($card) {
    const $checkedRadio = $card.find('.cs-visibility-radio:checked');
    
    if ($checkedRadio.length) {
      // Ensure the parent option has the 'checked' class
      $checkedRadio.closest('.js-visibility-option').addClass('checked');
      
      // Ensure hidden input matches the checked radio
      $card.find('.cs-hidden-visibility').val($checkedRadio.val());
    } else {
      // If no radio is checked, default to 'public'
      const $publicRadio = $card.find('.cs-visibility-radio[value="public"]');
      if ($publicRadio.length) {
        $publicRadio.prop('checked', true);
        $publicRadio.closest('.js-visibility-option').addClass('checked');
        $card.find('.cs-hidden-visibility').val('public');
      }
    }
  }

  /* ══════════════════════════════════════════════
     2.  SYNC  —  UI controls → hidden inputs
  ══════════════════════════════════════════════ */

  /**
   * Copy all visible-control values into the three hidden inputs of a card.
   * Called before validation and before submit.
   *
   * @param {jQuery} $card
   */
  function syncCard($card) {
    // Title
    const titleVal = $card.find('.cs-title-input').val() || '';
    $card.find('.cs-hidden-title').val(titleVal);

    // Content from Quill (or fallback to hidden input as-is)
    const quillEl = $card.find('.cs-quill-editor')[0];
    if (quillEl && quillMap.has(quillEl)) {
      const quill   = quillMap.get(quillEl);
      let   html    = quill.root.innerHTML;
      // Normalise empty Quill output
      html = html.replace(/<p><br\s*\/?><\/p>/gi, '').replace(/&nbsp;/g, ' ').trim();
      $card.find('.cs-hidden-content').val(html);
    }

    // Visibility - already synced via change events, but ensure it's up to date
    const vis = $card.find('.cs-visibility-radio:checked').val() || 'public';
    $card.find('.cs-hidden-visibility').val(vis);

    // Sort order = current DOM position
    $card.find('.cs-sort-order').val($card.index());
  }

  /* ══════════════════════════════════════════════
     3.  VALIDATION
  ══════════════════════════════════════════════ */

  /**
   * Validate a single card. Returns true if valid.
   * Shows inline error messages for each failing field.
   *
   * @param {jQuery} $card
   * @returns {boolean}
   */
  function validateCard($card) {
    let valid = true;

    // ── title ──
    const title = ($card.find('.cs-hidden-title').val() || '').trim();
    setFieldError($card, 'title', title ? '' : LANG.required || 'Required');
    if (!title) valid = false;

    // ── content ──
    const content = ($card.find('.cs-hidden-content').val() || '').trim();
    setFieldError($card, 'content', content ? '' : LANG.required || 'Required');
    if (!content) valid = false;

    // ── visibility ──
    const vis = $card.find('.cs-hidden-visibility').val() || '';
    setFieldError($card, 'visibility', vis ? '' : LANG.required || 'Required');
    if (!vis) valid = false;

    return valid;
  }

  /**
   * Show or clear an inline error on a field-group inside a card.
   *
   * @param {jQuery} $card
   * @param {'title'|'content'|'visibility'} field
   * @param {string} message  – empty string clears the error
   */
  function setFieldError($card, field, message) {
    const $group = $card.find(`.cs-field-group[data-field="${field}"]`);
    const $err   = $group.find('.cs-error-msg');
    const $input = $group.find('.cs-title-input, .cs-quill-editor, .cs-visibility-radio').first();

    if (message) {
      $err.text(message).show();
      $group.find('.form-control, .cs-quill-editor').addClass('is-invalid');
      $input.closest('.js-visibility-option')
            .find('.form-check-input').addClass('is-invalid');
    } else {
      $err.text('').hide();
      $group.find('.form-control, .cs-quill-editor').removeClass('is-invalid');
      $group.find('.form-check-input').removeClass('is-invalid');
    }
  }

  /**
   * Clear all field errors on a card.
   *
   * @param {jQuery} $card
   */
  function clearCardErrors($card) {
    $card.find('.cs-error-msg').text('').hide();
    $card.find('.is-invalid').removeClass('is-invalid');
  }

  /**
   * Validate every card in the container.
   * Returns true only if all cards pass.
   *
   * @returns {boolean}
   */
  function validateAll() {
    let allValid = true;
    getCards().each(function () {
      syncCard($(this));
      if (!validateCard($(this))) allValid = false;
    });
    return allValid;
  }

  /* ══════════════════════════════════════════════
     4.  TITLE DISPLAY SYNC
  ══════════════════════════════════════════════ */

  function updateTitleDisplay($card) {
    const val = $card.find('.cs-title-input').val().trim();
    $card.find('.cs-title-display').text(val || LANG.newSection || 'New Section');
  }

  /* ══════════════════════════════════════════════
     5.  QUILL INITIALISATION
  ══════════════════════════════════════════════ */

  /**
   * Boot Quill on every .cs-quill-editor inside $scope.
   * Safe to call multiple times — skips already-initialised editors.
   *
   * @param {jQuery} $scope
   */
  function initQuill($scope) {
    if (typeof Quill === 'undefined') return;

    $scope.find('.cs-quill-editor').each(function () {
      const el = this;
      if (quillMap.has(el)) return; // already initialised

      const quill = new Quill(el, {
        theme    : 'snow',
        modules  : {
          toolbar   : [
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link', 'clean'],
          ],
          clipboard : { matchVisual: false },
        },
        placeholder : $(el).data('placeholder') || 'Enter section content…',
        direction   : LANG.quillDir || 'ltr',
      });

      quillMap.set(el, quill);

      // Pre-fill from the hidden input
      const $card    = $(el).closest('.cs-card');
      const existing = $card.find('.cs-hidden-content').val() || '';
      if (existing.trim()) quill.root.innerHTML = existing;

      // Live sync on change
      quill.on('text-change', () => {
        let html = quill.root.innerHTML;
        html = html.replace(/<p><br\s*\/?><\/p>/gi, '').replace(/&nbsp;/g, ' ').trim();
        $card.find('.cs-hidden-content').val(html);

        // Clear content error as soon as the user types something
        if (html) setFieldError($card, 'content', '');
      });
    });
  }

  /* ══════════════════════════════════════════════
     6.  REINDEX  — rename all inputs after DOM changes
  ══════════════════════════════════════════════ */

  /**
   * Walk every .cs-card in DOM order and stamp fresh sequential indices
   * onto every input[name] that carries a custom_sections[N] pattern.
   * Also updates data-index and sort-order hidden inputs.
   */
  function reindex() {
    getCards().each(function (newIdx) {
      const $card = $(this);
      $card.attr('data-index', newIdx);
      $card.find('.cs-sort-order').val(newIdx);

      // Rename every named input inside the card
      $card.find('[name*="custom_sections["]').each(function () {
        const $el     = $(this);
        const oldName = $el.attr('name');
        if (oldName) {
          $el.attr(
            'name',
            oldName.replace(/custom_sections\[\d+\]/, `custom_sections[${newIdx}]`)
          );
        }
      });
      
      // Also update radio button IDs to avoid duplicate IDs
      $card.find('.cs-visibility-radio').each(function() {
        const $radio = $(this);
        const value = $radio.val();
        const oldId = $radio.attr('id');
        if (oldId) {
          const newId = oldId.replace(/__IDX__|_\d+$/, `_${newIdx}`);
          $radio.attr('id', newId);
          $radio.closest('label').attr('for', newId);
        }
      });
    });

    updateCountBadge();
  }

  /* ══════════════════════════════════════════════
     7.  ADD  —  clone the template
  ══════════════════════════════════════════════ */

  function addSection() {
    const count = getCards().length;
    if (count >= CFG.maxSections) {
      if (typeof toastr !== 'undefined') toastr.warning(LANG.maxReached || 'Maximum sections reached');
      return;
    }

    const tpl = document.getElementById('cs-card-template');
    if (!tpl) return;

    const newIdx = count;
    // Replace all __IDX__ placeholders in the template HTML
    const html   = tpl.innerHTML.replace(/__IDX__/g, String(newIdx));
    const $card  = $(html);

    $card.attr('data-index', newIdx);

    $('#cs-container').append($card);
    $('#cs-empty-state').addClass('d-none');

    // Boot Quill on the new card
    initQuill($card);
    
    // Initialize visibility for the new card
    initVisibilityForCard($card);

    updateCountBadge();

    // Focus the title input
    setTimeout(() => $card.find('.cs-title-input').trigger('focus'), 100);
  }

  /* ══════════════════════════════════════════════
     8.  DELETE
  ══════════════════════════════════════════════ */

  function deleteSection($card) {
    const sectionId = $card.data('section-id');

    if (!sectionId) {
      // Destroy the Quill instance to free memory
      const quillEl = $card.find('.cs-quill-editor')[0];
      if (quillEl && quillMap.has(quillEl)) quillMap.delete(quillEl);
      
      removeSectionFromDom($card); // Unsaved new section — just remove it
      return;
    }

    // Show confirmation dialog (assuming releaseWarningAlert is globally available)
    if (typeof releaseWarningAlert === 'function') {
      releaseWarningAlert({
        title: LANG.warning || 'Warning',
        message: LANG.confirmDelete || 'Are you sure you want to delete this section?',
        confirmColor: 'danger',
        confirmText: LANG.DYes || 'Yes, delete it',
        cancelText: LANG.DNo || 'No, Cancel',
        onConfirm: () => {
          // Destroy the Quill instance to free memory
          const quillEl = $card.find('.cs-quill-editor')[0];
          if (quillEl && quillMap.has(quillEl)) quillMap.delete(quillEl);
          
          const url = (CFG.deleteUrl || '').replace('__ID__', sectionId);
          $.ajax({
            url    : url,
            type   : 'DELETE',
            headers: { 'X-CSRF-TOKEN': CFG.csrfToken },
            success(res) {
              if (res.success) {
                if (typeof toastr !== 'undefined') toastr.success(res.message);
                removeSectionFromDom($card);
              } else {
                if (typeof toastr !== 'undefined') toastr.error(res.message);
              }
            },
            error() {
              if (typeof toastr !== 'undefined') toastr.error(LANG.deleteError || 'Delete failed');
            },
          });
        }
      });
    } else {
      // Fallback if releaseWarningAlert is not available
      if (confirm(LANG.confirmDelete || 'Are you sure you want to delete this section?')) {
        const quillEl = $card.find('.cs-quill-editor')[0];
        if (quillEl && quillMap.has(quillEl)) quillMap.delete(quillEl);
        removeSectionFromDom($card);
      }
    }
  }

  function removeSectionFromDom($card) {
    $card.fadeOut(250, function () {
      $(this).remove();
      reindex();
      if (getCards().length === 0) $('#cs-empty-state').removeClass('d-none');
    });
  }

  /* ══════════════════════════════════════════════
     9.  PREVIEW MODAL
  ══════════════════════════════════════════════ */

  function previewSection($card) {
    syncCard($card);
    const title   = $card.find('.cs-hidden-title').val()   || LANG.untitled || 'Untitled';
    const content = $card.find('.cs-hidden-content').val() || '';

    $('#cs-preview-body').html(`
      <div class="p-2">
        <h4 class="mb-3">${escapeHtml(title)}</h4>
        <div class="preview-content">${content}</div>
      </div>
    `);

    const modalEl = document.getElementById('cs-preview-modal');
    if (modalEl) {
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  }

  /* ══════════════════════════════════════════════
     10.  SORTABLE
  ══════════════════════════════════════════════ */

  let sortableInstance = null;

  function initSortable() {
    const container = document.getElementById('cs-container');
    if (!container || typeof Sortable === 'undefined') return;
    
    // Destroy existing instance if it exists
    if (sortableInstance) {
      sortableInstance.destroy();
    }

    sortableInstance = new Sortable(container, {
      handle    : '.drag-handle',
      animation : 200,
      onEnd() {
        reindex();
        // Persist new order to server
        const orderedIds = Array.from(container.querySelectorAll('.cs-card'))
          .map(el => parseInt(el.dataset.sectionId))
          .filter(Boolean);

        if (!orderedIds.length) return;

        $.ajax({
          url    : CFG.reorderUrl,
          type   : 'POST',
          headers: { 'X-CSRF-TOKEN': CFG.csrfToken },
          data   : { ordered_ids: orderedIds },
          success(r) {
            if (r.success && typeof toastr !== 'undefined') toastr.success(r.message);
          },
          error() {
            if (typeof toastr !== 'undefined') toastr.error(LANG.reorderError || 'Reorder failed');
          },
        });
      },
    });
  }

  /* ══════════════════════════════════════════════
     11.  FORM SUBMIT INTERCEPT
  ══════════════════════════════════════════════ */

  function handleSubmit(e) {
    // Sync all cards first so hidden inputs are up to date
    getCards().each(function () { syncCard($(this)); });

    if (!validateAll()) {
      e.preventDefault();
      e.stopImmediatePropagation();
      // if (typeof toastr !== 'undefined') toastr.error(LANG.saveError || 'Please fix the errors before saving');

      // Scroll to first invalid card
      const $firstInvalid = getCards().filter(function () {
        return $(this).find('.cs-error-msg:visible').length > 0;
      }).first();
      if ($firstInvalid.length) {
        $firstInvalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return false;
    }
    
    // Show loading state if needed
    const $submitBtn = $(this).find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html(`<i class="ti ti-loader ti-spin me-1"></i> ${LANG.saving}...`);
    
    // Re-enable after a short delay (form will submit anyway)
    setTimeout(() => {
      $submitBtn.prop('disabled', false).html(originalText);
    }, 3000);
  }

  /* ══════════════════════════════════════════════
     12.  HELPERS
  ══════════════════════════════════════════════ */

  function getCards() {
    return $('#cs-container .cs-card');
  }

  function updateCountBadge() {
    const count = getCards().length;
    $('#cs-current-count').text(count);
    $('#cs-add-btn').prop('disabled', count >= CFG.maxSections);
  }

  function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  /* ══════════════════════════════════════════════
     13.  EVENT BINDINGS
  ══════════════════════════════════════════════ */

  function bindEvents() {
    // Add section
    $(document).on('click', '#cs-add-btn, #cs-add-first-btn', addSection);

    // Delete section
    $(document).on('click', '.cs-delete-btn', function (e) {
      e.preventDefault();
      deleteSection($(this).closest('.cs-card'));
    });

    // Preview section
    $(document).on('click', '.cs-preview-btn', function (e) {
      e.preventDefault();
      previewSection($(this).closest('.cs-card'));
    });

    // Title input → update display label + clear error live
    $(document).on('input', '.cs-title-input', function () {
      const $card = $(this).closest('.cs-card');
      $card.find('.cs-hidden-title').val($(this).val());
      updateTitleDisplay($card);
      if ($(this).val().trim()) setFieldError($card, 'title', '');
    });

    // Visibility radio → handle styling and sync
    $(document).on('change', '.cs-visibility-radio', function () {
      updateVisibilityStyling($(this));
    });

    // Form submit
    $('#customSectionsForm').on('submit', handleSubmit);
    
    // Optional: Reset form when modal closes
    $('#cs-preview-modal').on('hidden.bs.modal', function () {
      $('#cs-preview-body').html(`
        <div class="text-center text-muted py-4">
          <i class="ti ti-eye-off fs-1 mb-2 d-block"></i>
          <p>${LANG.noContent || 'No content to preview'}</p>
        </div>
      `);
    });
  }

  /* ══════════════════════════════════════════════
     14.  INIT
  ══════════════════════════════════════════════ */

  function init() {
    bindEvents();
    initSortable();

    // Boot Quill on all pre-rendered cards
    initQuill($('#cs-container'));
    
    // Initialize visibility for existing cards
    getCards().each(function() {
      initVisibilityForCard($(this));
    });

    updateCountBadge();
    
    // Optional: Add keyboard shortcut for saving (Ctrl+S)
    $(document).on('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        $('#customSectionsForm').submit();
      }
    });
  }

  // Boot on DOM ready
  $(init);

})(jQuery);