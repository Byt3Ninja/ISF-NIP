 <!-- Message View -->
 <div class="col app-email-view flex-grow-0 bg-lighter" id="app-email-view">
  <div class="card shadow-none border-0 rounded-0 app-email-view-header p-5 pt-md-4 py-2">
    <!-- Message View : Title  bar-->
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center overflow-hidden">
        <span class="ms-sm-2 me-4"><i class="ti ti-chevron-left ti-md cursor-pointer"
            data-bs-toggle="sidebar" data-target="#app-email-view"></i></span>
        <h6 id="message-details-subject" class="text-truncate mb-0 me-2 fw-normal"></h6>
        {{-- <span class="badge bg-label-warning rounded-pill">Important</span> --}}
      </div>
    </div>
    <hr class="app-email-view-hr mx-n5 mb-2" />
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <span class="btn btn-icon btn-text-secondary rounded-pill me-1">
          <i class="ti ti-trash ti-md cursor-pointer"></i>
        </span>
        {{-- <span class="btn btn-icon btn-text-secondary rounded-pill me-1"><i
            class="ti ti-mail ti-md cursor-pointer" data-bs-toggle="sidebar"
            data-target="#app-email-view"></i></span> --}}
        <div class="dropdown">
          <button class="btn btn-icon btn-text-secondary rounded-pill p-0 me-1" type="button"
            id="dropdownMenuFolderTwo" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-folder ti-md"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuFolderTwo">
            {{-- <a class="dropdown-item" href="javascript:void(0)">
              <i class="ti ti-alert-octagon me-1"></i>
              <span class="align-middle">Spam</span>
            </a> --}}
            <a class="dropdown-item" href="javascript:void(0)">
              <i class="ti ti-edit me-1"></i>
              <span class="align-middle">{{ __('messages.Draft') }}</span>
            </a>
            <a class="dropdown-item" href="javascript:void(0)">
              <i class="ti ti-trash me-1"></i>
              <span class="align-middle">{{ __('messages.Trash') }}</span>
            </a>
          </div>
        </div>
        {{-- <div class="dropdown">
          <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
            id="dropdownLabelTwo" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-tag ti-md"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLabelTwo">
            <a class="dropdown-item" href="javascript:void(0)">
              <i class="badge badge-dot bg-success me-1"></i>
              <span class="align-middle">Workshop</span>
            </a>
            <a class="dropdown-item" href="javascript:void(0)">
              <i class="badge badge-dot bg-primary me-1"></i>
              <span class="align-middle">Company</span>
            </a>
            <a class="dropdown-item" href="javascript:void(0)">
              <i class="badge badge-dot bg-info me-1"></i>
              <span class="align-middle">Important</span>
            </a>
          </div>
        </div> --}}
      </div>
      <div class="d-flex align-items-center">
        <button type="button" class="btn btn-icon btn-text-secondary rounded-pill p-0 StarredToggele" data-id="">
          <i class="far fa-star fs-5"></i>
        </button>
        
        <div class="dropdown ms-1">
          <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
            id="dropdownMoreOptions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ti ti-dots-vertical ti-md"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMoreOptions">
            <a class="dropdown-item ToggleMarkUnRead" do="" mark="" href="javascript:void(0)">
              {{-- <i class="ti ti-mail me-1"></i> --}}
              <span class="align-middle">{{ __('messages.Mark as read') }}</span>
            </a>
            <a class="dropdown-item ToggleMarkRead" do="" mark="" href="javascript:void(0)">
              {{-- <i class="ti ti-mail-opened me-1"></i> --}}
              <span class="align-middle">{{ __('messages.Mark as unread') }}</span>
            </a>
            {{-- <a class="dropdown-item StarredToggele" href="javascript:void(0)">
              <i class="ti ti-star ti-sm me-1"></i>
              <span class="align-middle">Add star</span>
            </a> --}}
            {{-- <a class="dropdown-item" href="javascript:void(0)">
              <i class="ti ti-calendar me-1"></i>
              <span class="align-middle">Create Event</span>
            </a>
            <a class="dropdown-item" href="javascript:void(0)">
              <i class="ti ti-volume-off me-1"></i>
              <span class="align-middle">Mute</span>
            </a>
            <a class="dropdown-item d-sm-none d-block" href="javascript:void(0)">
              <i class="ti ti-printer me-1"></i>
              <span class="align-middle">Print</span>
            </a> --}}
          </div>
        </div>
      </div>
    </div>
  </div>
  <hr class="m-0" />
  <!-- Message View : Content-->
  <div class="app-email-view-content">
    <!-- Message View : Last mail-->
    <div class="card mx-sm-6 mx-3 mt-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap border-bottom">
        <div class="d-flex align-items-center mb-sm-0 mb-3">
          <img id="message-details-image" src="/icons/account.svg" alt="user-avatar"
            class="flex-shrink-0 rounded-circle me-2" height="38" width="38" />
          <div class="flex-grow-1 ms-1">
            <h6 id="message-details-createdBy" class="m-0 fw-normal"></h6>
            {{-- <small class="text-body">iAmAhoot@email.com</small> --}}
          </div>
        </div>
        <div class="d-flex align-items-center">
          <p class="mb-0 me-4 text-muted" id="message-details-createdAt"></p>
          {{-- <span class="btn btn-icon btn-text-secondary rounded-pill"><i
              class="ti ti-paperclip ti-md cursor-pointer"></i></span> --}}
          {{-- <span class="btn btn-icon btn-text-secondary rounded-pill"><i
              class="email-list-item-bookmark ti ti-star ti-md cursor-pointer"></i></span> --}}
          <div class="dropdown">
            <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
              id="dropdownEmailTwo" data-bs-toggle="dropdown" aria-expanded="true">
              <i class="ti ti-dots-vertical ti-md"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownEmailTwo">
              <a id="reply-btn" class="dropdown-item scroll-to-reply" href="javascript:void(0)">
                <i class="ti ti-corner-up-left me-1"></i>
                <span class="align-middle">{{ __('messages.Reply') }}</span>
              </a>
              {{-- <a class="dropdown-item" href="javascript:void(0)">
                <i class="ti ti-corner-up-right me-1"></i>
                <span class="align-middle">Forward</span>
              </a>
              <a class="dropdown-item" href="javascript:void(0)">
                <i class="ti ti-alert-octagon me-1"></i>
                <span class="align-middle">Report</span>
              </a> --}}
            </div>
          </div>
        </div>
      </div>
      <div class="card-body pt-5">
        {{-- <p class="fw-medium">{{ __('messages.Subject') }}:</p>
        <p id="message-details-subject"></p>
        <br /><br /> --}}
        <h6 class="fw-semibold">{{ __('messages.Message') }}:</h6>
        <p id="message-details-text"></p>

        <hr class="my-4" />

        <div id="message-replies-container" style="display: none;">
          <h6 id="replies-heading" class="fw-semibold">{{ __('messages.Replies') }}:</h6>
          <ul class="list-unstyled" id="message-details-replies"></ul>
        </div>

      </div>
    </div>
    @include('admin.clients.messages.partials.reply-message')
  </div>
</div>
<!--/ Message View -->