'use strict';

/**
 * GV-10436 / GV-10440 (ISF): the dependent behaviour of partials/forms/isf-education-fields.blade.php.
 *  - Entity is disabled until an Education Status is picked, then lists only that status's options.
 *    Changing the status clears the shown Entity; going back to a status restores the Entity that
 *    was chosen for it, so a mis-click loses nothing.
 *  - Only the identification field the status asks for (student code | national ID) is shown and
 *    posted. The other is hidden and disabled but keeps its value: they are separate columns and a
 *    founder may hold both, so switching back shows it again.
 * The placeholder options are disabled so they can't be picked as a value.
 * Validation stays with each page: onChange(fieldNames) names the fields whose state was reset, so
 * the page can clear their errors (they are checked again on submit, not on every status switch).
 */
(function ($) {
  function init(root, onChange) {
    const $root = $(root || document);
    const $status = $root.find('[data-isf-education-status]');
    const $entity = $root.find('[data-isf-education-entity]');
    if (!$status.length || !$entity.length) return;

    const identification = $status.data('identification') || {};
    const options = $entity.data('options') || {};

    // Entity last chosen per status, seeded with what the page rendered.
    const chosenEntity = {};
    let currentStatus = $status.val() || '';
    chosenEntity[currentStatus] = $entity.val() || '';

    function fillEntities(status, selected) {
      // The placeholder can't be picked; it is selected explicitly because a select with
      // no selected option would otherwise fall back to its first real entity.
      $entity.empty().append($('<option>', {
        value: '',
        text: status ? $entity.data('placeholder') : $entity.data('placeholderDisabled'),
        disabled: true,
        selected: true,
      }));
      (options[status] || []).forEach(function (o) {
        $entity.append($('<option>', { value: o.id, text: o.name, selected: String(o.id) === String(selected) }));
      });
      $entity.prop('disabled', !status).removeClass('is-invalid').trigger('change.select2');
    }

    function showIdentification(status) {
      const active = identification[status] || null;
      $root.find('[data-isf-id-field]').each(function () {
        const $wrap = $(this);
        const on = $wrap.data('isfIdField') === active;
        $wrap.toggleClass('d-none', !on);
        $wrap.find('input').prop('disabled', !on).removeClass('is-invalid');
      });
    }

    $status.on('change', function () {
      const status = $status.val() || '';
      if (status === currentStatus) return;
      chosenEntity[currentStatus] = $entity.val() || '';
      currentStatus = status;
      fillEntities(status, chosenEntity[status] || '');
      showIdentification(status);
      if (typeof onChange === 'function') onChange(['education_entity_id', 'national_id', 'student_code']);
    });
  }

  window.IsfEducationFields = { init: init };
})(jQuery);
