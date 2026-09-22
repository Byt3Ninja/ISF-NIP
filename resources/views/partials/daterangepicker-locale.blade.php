{{-- bootstrap-daterangepicker builds its default locale from whatever moment locale
     is active at construction time (monthNames: moment.monthsShort(), etc).

     We deliberately do NOT call moment.locale('ar') globally: the bundled Arabic
     moment locale defines a postformat() that rewrites Latin digits to Arabic-Indic
     ones (١٢٣), which corrupts every machine-readable format() we post to the
     backend -- e.g. the MM/DD/YYYY date_range value parsed by Carbon in
     CyclesController::modify().

     Instead we wrap $.fn.daterangepicker and inject Arabic *display* strings only.
     locale.format is left alone, so the value written into the input stays
     MM/DD/YYYY with Latin digits and the backend parse is unaffected. Calendar day
     numbers are rendered via moment.date() (a raw JS number), so they stay Latin
     too. Per-call locale options still win, since they are merged last. --}}
@if (app()->getLocale() === 'ar')
<script>
  (function () {
    if (typeof jQuery === 'undefined') return;

    var arLocale = {
      direction: 'rtl',
      applyLabel: 'تطبيق',
      cancelLabel: 'إلغاء',
      fromLabel: 'من',
      toLabel: 'إلى',
      customRangeLabel: 'نطاق مخصص',
      weekLabel: 'أسبوع',
      daysOfWeek: ['ح', 'ن', 'ث', 'ر', 'خ', 'ج', 'س'],
      monthNames: [
        'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
      ],
      firstDay: 6 // Saturday, matching moment's Arabic locale
    };

    // The daterangepicker library is loaded per-view, not in the layout, so this
    // partial cannot assume it is already present. Install the wrapper as soon as
    // the plugin appears, whenever that happens.
    var installed = false;

    function install() {
      if (installed || typeof jQuery.fn.daterangepicker !== 'function') return false;
      installed = true;

      var original = jQuery.fn.daterangepicker;

      jQuery.fn.daterangepicker = function (options, callback) {
        options = options || {};
        // Per-call locale options take precedence over the Arabic defaults.
        options.locale = jQuery.extend({}, arLocale, options.locale || {});
        return original.call(this, options, callback);
      };

      return true;
    }

    // Already loaded (or loaded later in the page / on DOM ready).
    if (!install()) {
      jQuery(function () { install(); });
    }
  })();
</script>
@endif
