<!-- Send Message Modal -->
<div class="modal fade" id="emailComposeSidebar" tabindex="-1"
aria-labelledby="emailComposeSidebarLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-simple">
  <div class="modal-content p-0">
    <div class="modal-header py-3 justify-content-between">
      <h5 class="modal-title text-body fs-5">Send Message</h5>
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
          data-bs-dismiss="modal" aria-label="Close">
          <i class="ti ti-x"></i>
        </button>
      </div>
    </div>
    <div class="modal-body flex-grow-1 pb-sm-0 p-5 py-2">
      <form class="email-compose-form">
        <div class="email-compose-cc d-none">
          <hr class="mx-n5 my-0" />
          <div class="d-flex align-items-center">
            <label for="email-cc" class="fw-medium text-muted">Cc:</label>
            <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2" id="email-cc"
              placeholder="someone@email.com" />
          </div>
        </div>
        <div class="email-compose-bcc d-none">
          <hr class="mx-n5 my-0" />
          <div class="d-flex align-items-center">
            <label for="email-bcc" class="fw-medium text-muted">Bcc:</label>
            <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2" id="email-bcc"
              placeholder="someone@email.com" />
          </div>
        </div>
        <hr class="mx-n5 my-0" />
        <div class="email-compose-subject d-flex align-items-center">
          <label for="email-subject" class="fw-medium text-muted">Subject:</label>
          <input type="text" class="form-control border-0 shadow-none flex-grow-1 mx-2"
            id="email-subject" />
        </div>
        <div class="email-compose-message">
          <hr class="mx-n5 my-0" />
          <div class="d-flex justify-content-end mx-n1">
            <div class="email-editor-toolbar border-0 w-100 px-0">
              <span class="ql-formats me-0">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
                <button class="ql-link"></button>
                <button class="ql-image"></button>
              </span>
            </div>
          </div>
          <hr class="mx-n5 my-0" />
          <div class="email-editor border-0 mx-n5"></div>
        </div>
        <hr class="mx-n5 mt-0 mb-2" />
        <div class="email-compose-actions d-flex justify-content-between align-items-center my-4">
          <div class="d-flex align-items-center">
            <div class="btn-group">
              <button type="button" class="btn btn-primary dropdown-toggle email-send-btn"
                data-bs-toggle="dropdown" aria-expanded="false">
                Send <i class="ti ti-send ti-xs scaleX-n1-rtl ms-2"></i>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0);">Save draft</a></li>
              </ul>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="reset" class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
              data-bs-dismiss="modal" aria-label="Close">
              <i class="ti ti-trash"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
<!-- /Send Message Modal -->