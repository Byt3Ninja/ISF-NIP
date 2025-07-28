<div class="card">
  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Hackathon Schedule' : 'جدول أعمال الهاكثون' }}
    </h5>
    <ul class="timeline" id="hackathon-schedule-list">
      <li>
        <span class="time">
          {{ $l == 'en' ? 'Day 1: March 15, 2024' : 'اليوم الأول: 15 مارس 2024' }}
        </span>
        @if($l == 'en')
        <strong>09:00 - 10:00:</strong> Registration and hackathon opening<br>
        <strong>10:00 - 11:00:</strong> Keynote speeches and theme presentations<br>
        <strong>11:00 - 13:00:</strong> Workshop: Team formation and idea development<br>
        <strong>13:00 - 14:00:</strong> Lunch break<br>
        <strong>14:00 - 18:00:</strong> Start working on projects (mentoring sessions available)
        @else
        <strong>09:00 - 10:00:</strong> تسجيل وافتتاح الهاكثون<br>
        <strong>10:00 - 11:00:</strong> كلمات رئيسية وعرض المحاور<br>
        <strong>11:00 - 13:00:</strong> ورشة عمل: تكوين الفرق وتطوير الأفكار<br>
        <strong>13:00 - 14:00:</strong> استراحة غداء<br>
        <strong>14:00 - 18:00:</strong> بدء العمل على المشاريع (جلسات إرشاد متاحة)
        @endif
      </li>
      <li>
        <span class="time">
          {{ $l == 'en' ? 'Day 2: March 16, 2024' : 'اليوم الثاني: 16 مارس 2024' }}
        </span>
        @if($l == 'en')
        <strong>09:00 - 18:00:</strong> Continue working on projects (advanced mentoring sessions)<br>
        <strong>14:00 - 15:00:</strong> Workshop: How to make an effective pitch
        @else
        <strong>09:00 - 18:00:</strong> مواصلة العمل على المشاريع (جلسات إرشاد متقدمة)<br>
        <strong>14:00 - 15:00:</strong> ورشة عمل: كيفية تقديم عرض فعال (Pitching)
        @endif
      </li>
      <li>
        <span class="time">
          {{ $l == 'en' ? 'Day 3: March 17, 2024' : 'اليوم الثالث: 17 مارس 2024' }}
        </span>
        @if($l == 'en')
        <strong>09:00 - 12:00:</strong> Final touches on projects<br>
        <strong>12:00:</strong> Final deadline for project submission<br>
        <strong>14:00 - 17:00:</strong> Project presentations to judging panel<br>
        <strong>17:30 - 18:00:</strong> Results announcement and awards ceremony
        @else
        <strong>09:00 - 12:00:</strong> اللمسات الأخيرة على المشاريع<br>
        <strong>12:00:</strong> الموعد النهائي لرفع المشاريع<br>
        <strong>14:00 - 17:00:</strong> عروض المشاريع أمام لجنة التحكيم<br>
        <strong>17:30 - 18:00:</strong> إعلان النتائج وتوزيع الجوائز
        @endif
      </li>
    </ul>
  </div>
</div>