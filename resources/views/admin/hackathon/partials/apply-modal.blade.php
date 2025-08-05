<div class="modal fade" id="applyModalHackathonDetails" tabindex="-1" aria-labelledby="applyModalHackathonDetailsLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="applyModalHackathonDetailsLabel">
          {{ $l == 'en' ? 'Submit Application' : 'تقديم طلب الانضمام' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>
          {{ $l == 'en' ? 'You are about to submit an application to join this hackathon. Please review the hackathon
          terms and conditions before proceeding.' : 'أنت على وشك تقديم طلب للانضمام إلى هذا الهاكثون. يرجى مراجعة
          شروط وأحكام الهاكثون قبل المتابعة.' }}
        </p>
        <form>
          <div class="mb-3">
            <label for="dh-applicantName" class="form-label">
              {{ $l == 'en' ? 'Full Name' : 'الاسم الكامل' }}
            </label>
            <input type="text" class="form-control" id="dh-applicantName"
              value="{{ $l == 'en' ? 'Entrepreneur (from profile)' : 'رائد أعمال (من الملف الشخصي)' }}" readonly>
          </div>
          <div class="mb-3">
            <label for="dh-motivation" class="form-label">
              {{ $l == 'en' ? 'Why do you want to participate? (Optional)' : 'لماذا ترغب في المشاركة؟ (اختياري)' }}
            </label>
            <textarea class="form-control" id="dh-motivation" rows="3"></textarea>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="dh-termsAndConditions" required>
            <label class="form-check-label" for="dh-termsAndConditions">
              {{ $l == 'en' ? 'I agree to the' : 'أوافق على' }}
              <a href="#terms" data-bs-toggle="modal" data-bs-target="#termsModalDetails">
                {{ $l == 'en' ? 'Terms and Conditions' : 'الشروط والأحكام' }}
              </a>.
            </label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ $l == 'en' ? 'Close' : 'إغلاق' }}
        </button>
        <button type="button" class="btn btn-primary" onclick="submitApplicationDetails()">
          {{ $l == 'en' ? 'Confirm Application' : 'تأكيد التقديم' }}
        </button>
      </div>
    </div>
  </div>
</div>