<div class="card">
<div class="card-body">
  <h5 class="card-title">
    {{ $l == 'en' ? 'About the Hackathon' : 'عن الهاكثون' }}
  </h5>
  <p id="hackathon-description">
    {{ $l == 'en' ? $currentHackathon['description'] : $currentHackathon['description_ar'] }}
  </p>
  {{-- <h6 class="mt-4">
    {{ $l == 'en' ? 'Main Objectives:' : 'الأهداف الرئيسية:' }}
  </h6> --}}
  {{-- <ul id="hackathon-objectives">
    @if($l == 'en')
    <li>Encourage innovation in artificial intelligence.</li>
    <li>Provide a platform for collaboration and knowledge exchange.</li>
    <li>Develop practical and applicable solutions.</li>
    <li>Support promising projects to advance to higher stages.</li>
    @else
    <li>تشجيع الابتكار في مجال الذكاء الاصطناعي.</li>
    <li>توفير منصة للتعاون وتبادل الخبرات.</li>
    <li>تطوير حلول عملية قابلة للتطبيق.</li>
    <li>دعم المشاريع الواعدة للانتقال إلى مراحل متقدمة.</li>
    @endif
  </ul> --}}
  {{-- <h6 class="mt-4">
    {{ $l == 'en' ? 'Target Audience:' : 'الفئة المستهدفة:' }}
  </h6>
  <ul id="hackathon-audience">
    @if($l == 'en')
    <li>Entrepreneurs registered on the platform.</li>
    <li>Developers, designers, and idea holders.</li>
    <li>Those interested in artificial intelligence technologies.</li>
    <li>Participating teams from outside the incubator (according to hackathon conditions).</li>
    @else
    <li>رواد الأعمال المسجلين على المنصة.</li>
    <li>المطورون، المصممون، وأصحاب الأفكار.</li>
    <li>المهتمون بتقنيات الذكاء الاصطناعي.</li>
    <li>الفرق المشاركة من خارج المسرعة (حسب شروط الهاكثون).</li>
    @endif
  </ul> --}}
</div>
</div>