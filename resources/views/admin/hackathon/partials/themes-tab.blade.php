<div class="card">
  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Hackathon Themes' : 'محاور الهاكثون' }}
    </h5>
    <p>
      {{ $l == 'en' ? 'The hackathon focuses on the following themes. Teams can choose one theme or combine
      multiple themes in their solutions.' : 'يركز الهاكثون على المحاور التالية. يمكن للفرق اختيار محور واحد أو
      دمج عدة محاور في حلولهم.' }}
    </p>
    <div class="row" id="hackathon-themes-list">
      <div class="col-md-6 mb-3">
        <div class="card bg-light">
          <div class="card-body">
            <h6 class="card-title">
              <i class="bi bi-robot me-2 text-primary me-2"></i>
              {{ $l == 'en' ? 'AI in Healthcare' : 'الذكاء الاصطناعي في الرعاية الصحية' }}
            </h6>
            <p class="card-text">
              {{ $l == 'en' ? 'Applications to improve diagnosis, patient data management, and personalized
              medicine.' : 'تطبيقات لتحسين التشخيص، إدارة بيانات المرضى، والطب الشخصي.' }}
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card bg-light">
          <div class="card-body">
            <h6 class="card-title">
              <i class="bi bi-bank2 me-2 text-success me-2"></i>
              {{ $l == 'en' ? 'AI in Financial Services' : 'الذكاء الاصطناعي في الخدمات المالية' }}
            </h6>
            <p class="card-text">
              {{ $l == 'en' ? 'Solutions for fraud detection, risk analysis, and automated financial
              consulting.' : 'حلول للكشف عن الاحتيال، تحليل المخاطر، وتقديم استشارات مالية آلية.' }}
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card bg-light">
          <div class="card-body">
            <h6 class="card-title">
              <i class="bi bi-buildings-fill me-2 text-info me-2"></i>
              {{ $l == 'en' ? 'AI for Smart Cities' : 'الذكاء الاصطناعي للمدن الذكية' }}
            </h6>
            <p class="card-text">
              {{ $l == 'en' ? 'Systems for traffic management, energy efficiency, and improving public
              services.' : 'أنظمة لإدارة المرور، كفاءة الطاقة، وتحسين الخدمات العامة.' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>