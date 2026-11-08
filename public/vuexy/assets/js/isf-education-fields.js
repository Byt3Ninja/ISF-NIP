'use strict';

/**
 * GV-10436 / GV-10440 (ISF): the dependent behaviour of partials/forms/isf-education-fields.blade.php.
 *  - Entity is disabled until an Education Status is picked, then lists only that status's options.
 *  - Changing the status clears the chosen Entity.
 *  - Only the identification field the status asks for (student code | national ID) is shown and
 *    posted; the one being hidden is cleared.
 * Validation stays with each page; onChange(fieldNames) lets it re-check the affected fields.
 */
(function ($) {
  function init(root, onChange) {
    const $root = $(root || document);
    const $status = $root.find('[data-isf-education-status]');
    const $entity = $root.find('[data-isf-education-entity]');
    if (!$status.length || !$entity.length) return;

    const identification = $status.data('identification') || {};
    const options = $entity.data('options') || {};

    function fillEntities(status, selected) {
      $entity.empty().append($('<option>', {
        value: '',
        text: status ? $entity.data('placeholder') : $entity.data('placeholderDisabled'),
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
        const $input = $wrap.find('input');
        if (!on && !$input.prop('disabled')) $input.val('');
        $wrap.toggleClass('d-none', !on);
        $input.prop('disabled', !on).removeClass('is-invalid');
      });
    }

    $status.on('change', function () {
      const status = $status.val() || '';
      fillEntities(status, '');
      showIdentification(status);
      if (typeof onChange === 'function') onChange(['education_entity_id', 'national_id', 'student_code']);
    });
  }

  window.IsfEducationFields = { init: init };
})(jQuery);
