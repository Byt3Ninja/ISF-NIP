<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">
          <i class="ti ti-file-text me-2"></i>
          {{ $l == 'en' ? 'Hackathon Terms and Conditions' : 'شروط وأحكام الهاكثون' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{{ $l == 'en' ? 'This is placeholder text for terms and conditions...' : 'هذا نص وهمي للشروط والأحكام...'
          }}</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam tincidunt arcu vel arcu fermentum, eget
          accumsan dolor dignissim. Nam eget quam mauris. In hac habitasse platea dictumst.</p>
        <ol>
          <li>{{ $l == 'en' ? 'First term: ...' : 'البند الأول: ...' }}</li>
          <li>{{ $l == 'en' ? 'Second term: ...' : 'البند الثاني: ...' }}</li>
          <li>{{ $l == 'en' ? 'Third term: ...' : 'البند الثالث: ...' }}</li>
        </ol>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-target="#applyModal1" data-bs-toggle="modal"
          data-bs-dismiss="modal">
          <i class="ti ti-check me-1"></i>
          {{ $l == 'en' ? 'I Agree and Return to Application' : 'أوافق وأعود للتقديم' }}
        </button>
      </div>
    </div>
  </div>
</div>