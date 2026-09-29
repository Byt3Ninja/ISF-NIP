/**
 * Global fix for bootstrap-daterangepicker instances opened inside a Bootstrap
 * modal or offcanvas.
 *
 * The plugin positions its dropdown with absolute coordinates relative to the
 * document. Inside a scrollable/transformed modal that math is wrong, so the
 * calendar drifts off-screen once it re-renders after a date is picked (and the
 * per-page `.daterangepicker { margin-top: Xrem }` hacks made it worse). Here we
 * switch the dropdown to viewport-fixed coordinates and clamp it to the visible
 * area, so it stays fully on screen no matter where the input sits.
 *
 * Self-installing: any picker whose input lives in a `.modal`/`.offcanvas` is
 * handled automatically — no per-view wiring required.
 */
(function ($) {
  'use strict';

  if (!$ || !$.fn || !$.fn.daterangepicker) return;

  var GAP = 4;

  function reposition($input, drp) {
    if (!drp || !drp.isShowing || !drp.container) return;

    var $picker = drp.container;
    var input = $input[0];
    if (!input || !input.isConnected) return;

    var rect = input.getBoundingClientRect();
    var pickerH = $picker.outerHeight();
    var pickerW = $picker.outerWidth();
    var viewportH = window.innerHeight;
    var viewportW = window.innerWidth;

    // Prefer below the input; flip above when it would overflow the bottom, and
    // fall back to "as low as still fits" when neither side has room.
    var top = rect.bottom + GAP;
    if (top + pickerH > viewportH - GAP) {
      var above = rect.top - pickerH - GAP;
      top = above >= GAP ? above : Math.max(GAP, viewportH - pickerH - GAP);
    }

    var left;
    if (drp.opens === 'left') {
      left = rect.right - pickerW;
    } else if (drp.opens === 'center') {
      left = rect.left + rect.width / 2 - pickerW / 2;
    } else {
      left = rect.left;
    }
    left = Math.max(GAP, Math.min(left, viewportW - pickerW - GAP));

    $picker.css({ top: top + 'px', left: left + 'px', right: 'auto', bottom: 'auto' });
  }

  /**
   * Attach viewport-fixed positioning to a single picker input.
   * Safe to call repeatedly on the same input (handlers are namespaced).
   */
  function attach($input) {
    $input = $($input);
    var drp = $input.data('daterangepicker');
    if (!drp || !drp.container) return;

    drp.container.addClass('drp-viewport-fixed');

    var run = function () { reposition($input, drp); };
    // The plugin re-renders the calendar after each click, so recompute on the
    // next tick to measure the final size.
    var runSoon = function () { setTimeout(run, 0); };

    $input
      .off('.drpViewport')
      .on('show.daterangepicker.drpViewport showCalendar.daterangepicker.drpViewport', runSoon);

    // Any click inside the calendar (month/year nav, day cell) can resize it.
    drp.container.off('.drpViewport').on('click.drpViewport', runSoon);

    // Keep it pinned to the input while the surrounding container scrolls.
    $input.closest('.modal, .offcanvas').off('.drpViewport').on('scroll.drpViewport', run);
    $input.parents('.modal-body, .modal-dialog, .offcanvas-body').off('.drpViewport').on('scroll.drpViewport', run);
    $(window).on('scroll.drpViewport resize.drpViewport', run);

    if (drp.isShowing) run();
  }

  // Auto-install: catch pickers created anywhere inside a modal/offcanvas,
  // whether they are initialised on page load or lazily when a dialog opens.
  $(document).on('show.daterangepicker', function (e) {
    var $input = $(e.target);
    if (!$input.closest('.modal, .offcanvas').length) return;
    if ($input.data('drpViewportBound')) return;
    $input.data('drpViewportBound', true);
    attach($input);
    // This `show` already fired before binding, so position it now.
    setTimeout(function () { reposition($input, $input.data('daterangepicker')); }, 0);
  });

  // Exposed for views that want to opt a picker in explicitly.
  window.attachViewportDateRangePicker = attach;
})(window.jQuery);
