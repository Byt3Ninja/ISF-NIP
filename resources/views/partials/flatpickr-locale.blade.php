{{-- flatpickr ships core-only (no locale files), so Arabic translations must be
     applied explicitly. localize() sets the Arabic locale as the default for every
     picker created on the page from the moment this runs. --}}
<script>
  if ('{{ app()->getLocale() }}' === 'ar' && typeof flatpickr !== 'undefined') {
    flatpickr.localize({
      weekdays: {
        shorthand: ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'],
        longhand: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']
      },
      months: {
        shorthand: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'],
        longhand: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
      },
      firstDayOfWeek: 6,
      rangeSeparator: ' إلى ',
      weekAbbreviation: 'أسبوع',
      scrollTitle: 'قم بالتمرير',
      toggleTitle: 'اضغط للتبديل',
      amPM: ['ص', 'م'],
      yearAriaLabel: 'سنة',
      time_24hr: false
    });
  }
</script>