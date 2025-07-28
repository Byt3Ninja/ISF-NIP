<div class="card">
  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Results and Winning Teams' : 'النتائج والفرق الفائزة' }}
    </h5>
    <div id="results-announcement" class="alert alert-success text-center">
      <i class="bi bi-trophy-fill display-4 mb-3 me-2"></i>
      <h4>{{ $l == 'en' ? 'Congratulations to the Winning Teams!' : 'تهانينا للفرق الفائزة!' }}</h4>
      <p>
        {{ $l == 'en' ? 'We announce the winners of the Future of AI Hackathon:' : 'نعلن عن الفائزين في هاكثون
        مستقبل الذكاء الاصطناعي:' }}
      </p>
    </div>
    <div class="row mt-4">
      <div class="col-md-4 mb-3">
        <div class="card text-center border-warning shadow-sm">
          <div class="card-header bg-warning text-dark fw-bold">
            {{ $l == 'en' ? 'First Place' : 'المركز الأول' }}
            <i class="bi bi-award-fill me-2"></i>
          </div>
          <div class="card-body">
            <h5 class="card-title">
              {{ $l == 'en' ? 'Team Innovative Minds' : 'فريق العقول المبتكرة' }}
            </h5>
            <p class="card-text">
              {{ $l == 'en' ? 'Project: Smart Medical Diagnosis System' : 'مشروع: نظام تشخيص طبي ذكي' }}
            </p>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#solutionDetailsModal"
              data-team="{{ $l == 'en' ? 'Innovative Minds' : 'العقول المبتكرة' }}"
              data-project="{{ $l == 'en' ? 'Smart Medical Diagnosis System' : 'نظام تشخيص طبي ذكي' }}">
              {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
            </button>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card text-center border-secondary shadow-sm">
          <div class="card-header bg-light fw-bold">
            {{ $l == 'en' ? 'Second Place' : 'المركز الثاني' }}
            <i class="bi bi-award me-2"></i>
          </div>
          <div class="card-body">
            <h5 class="card-title">
              {{ $l == 'en' ? 'Team Green Code' : 'فريق الكود الأخضر' }}
            </h5>
            <p class="card-text">
              {{ $l == 'en' ? 'Project: Energy Consumption Management Platform' : 'مشروع: منصة لإدارة استهلاك
              الطاقة' }}
            </p>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#solutionDetailsModal"
              data-team="{{ $l == 'en' ? 'Green Code' : 'الكود الأخضر' }}"
              data-project="{{ $l == 'en' ? 'Energy Consumption Management Platform' : 'منصة لإدارة استهلاك الطاقة' }}">
              {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
            </button>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card text-center shadow-sm" style="border-color: #cd7f32;">
          <div class="card-header fw-bold" style="background-color: #f0e6d1; color: #cd7f32;">
            {{ $l == 'en' ? 'Third Place' : 'المركز الثالث' }}
            <i class="bi bi-award me-2"></i>
          </div>
          <div class="card-body">
            <h5 class="card-title">
              {{ $l == 'en' ? 'Team Future Data' : 'فريق بيانات المستقبل' }}
            </h5>
            <p class="card-text">
              {{ $l == 'en' ? 'Project: AI Financial Analysis Tool' : 'مشروع: أداة تحليل مالي بالذكاء الاصطناعي'
              }}
            </p>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#solutionDetailsModal"
              data-team="{{ $l == 'en' ? 'Future Data' : 'بيانات المستقبل' }}"
              data-project="{{ $l == 'en' ? 'AI Financial Analysis Tool' : 'أداة تحليل مالي بالذكاء الاصطناعي' }}">
              {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <h6 class="mt-3">
      {{ $l == 'en' ? 'Other Outstanding Projects:' : 'المشاريع المتميزة الأخرى:' }}
    </h6>
    <ul class="list-group">
      <li class="list-group-item">
        {{ $l == 'en' ? 'Team "Tech Innovators" - Project "Smart City Traffic Flow"' : 'فريق "Tech Innovators" -
        مشروع "Smart City Traffic Flow"' }}
      </li>
      <li class="list-group-item">
        {{ $l == 'en' ? 'Team "AI Avengers" - Project "AI Powered Education Platform"' : 'فريق "AI Avengers" -
        مشروع "AI Powered Education Platform"' }}
      </li>
    </ul>
  </div>
</div>