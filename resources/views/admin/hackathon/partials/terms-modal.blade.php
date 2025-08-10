<div class="modal fade" id="termsModalDetails" tabindex="-1" aria-labelledby="termsModalDetailsLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalDetailsLabel">
          {{ $l == 'en' ? 'Hackathon Terms and Conditions' : 'شروط وأحكام الهاكثون' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{{ $l == 'en' ? 'This is a placeholder text for terms and conditions...' : 'هذا نص وهمي للشروط والأحكام...'
          }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-target="#applyModalHackathonDetails"
          data-bs-toggle="modal" data-bs-dismiss="modal">
          {{ $l == 'en' ? 'I Agree and Return to Application' : 'أوافق وأعود للتقديم' }}
        </button>
      </div>
    </div>
  </div>
</div>