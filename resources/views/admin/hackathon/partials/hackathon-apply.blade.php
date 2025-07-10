<div class="modal fade" id="applyModal1" tabindex="-1" aria-labelledby="applyModal1Label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="applyModal1Label">
          <i class="ti ti-send me-2"></i>
          {{ $l == 'en' ? 'Apply for Future of AI Hackathon' : 'تقديم طلب الانضمام لهاكثون مستقبل الذكاء الاصطناعي' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="ti ti-info-circle me-2"></i>
          <div>
            {{ $l == 'en' ? 'You are about to apply for the "Future of AI Hackathon". Please review the terms and
            conditions before proceeding.' : 'أنت على وشك تقديم طلب للانضمام إلى "هاكثون مستقبل الذكاء الاصطناعي".
            يرجى مراجعة شروط وأحكام الهاكثون قبل المتابعة.' }}
          </div>
        </div>

        <form id="hackathonApplicationForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="applicantName" class="form-label fw-bold">{{ $l == 'en' ? 'Full Name' : 'الاسم الكامل' }}</label>
              <input type="text" class="form-control" id="applicantName"
                value="{{ $l == 'en' ? 'Entrepreneur (From Profile)' : 'رائد أعمال (من الملف الشخصي)' }}" readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label for="applicantEmail" class="form-label fw-bold">{{ $l == 'en' ? 'Email' : 'البريد الإلكتروني' }}</label>
              <input type="email" class="form-control" id="applicantEmail"
                value="{{ $l == 'en' ? 'user@example.com (From Profile)' : 'user@example.com (من الملف الشخصي)' }}"
                readonly>
            </div>
          </div>

          <div class="mb-3">
            <label for="motivation" class="form-label fw-bold">{{ $l == 'en' ? 'Why do you want to participate? (Optional)' :
              'لماذا ترغب في المشاركة؟ (اختياري)' }}</label>
            <textarea class="form-control" id="motivation" rows="3"
              placeholder="{{ $l == 'en' ? 'Tell us about your motivation...' : 'أخبرنا عن دافعك...' }}"></textarea>
          </div>

          <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="externalTeamCheck">
            <label class="form-check-label" for="externalTeamCheck">
              {{ $l == 'en' ? 'I am part of an external team (if allowed)' : 'أنا جزء من فريق مشارك من خارج المسرعة
              (إذا كان مسموحاً)' }}
            </label>
          </div>

          <div id="externalTeamDetails" class="mb-3" style="display:none;">
            <label for="externalTeamName" class="form-label fw-bold">{{ $l == 'en' ? 'External Team Name' : 'اسم الفريق
              الخارجي' }}</label>
            <input type="text" class="form-control" id="externalTeamName"
              placeholder="{{ $l == 'en' ? 'Team name' : 'اسم الفريق' }}">
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="termsAndConditions" required>
            <label class="form-check-label" for="termsAndConditions">
              {{ $l == 'en' ? 'I agree to the' : 'أوافق على' }}
              <a href="#terms" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-primary">
                {{ $l == 'en' ? 'Terms and Conditions' : 'الشروط والأحكام' }}
              </a>
              {{ $l == 'en' ? 'of the hackathon.' : 'الخاصة بالهاكثون.' }}
            </label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i>
          {{ $l == 'en' ? 'Close' : 'إغلاق' }}
        </button>
        <button type="button" class="btn btn-primary" onclick="submitApplication()">
          <i class="ti ti-check me-1"></i>
          {{ $l == 'en' ? 'Confirm Application' : 'تأكيد التقديم' }}
        </button>
      </div>
    </div>
  </div>
</div>