<!-- Message List: Actions -->
<div class="d-flex justify-content-between align-items-center px-4 pt-3">
  <div class="d-flex align-items-center">
    <div class="form-check mb-0 ms-2">
      <input class="form-check-input" type="checkbox" id="email-select-all" />
      <label class="form-check-label" for="email-select-all"></label>
    </div>
    <div class="btn btn-text-secondary btn-icon rounded-pill me-1">
      <i class="ti ti-trash ti-md email-list-delete cursor-pointer"></i>
    </div>
    {{-- <div class="btn btn-text-secondary btn-icon rounded-pill me-1">
      <i class="ti ti-mail-opened ti-md email-list-read cursor-pointer"></i>
    </div> --}}
    <div class="dropdown me-1">
      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button" id="dropdownMenuFolderOne"
        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="ti ti-folder ti-md"></i>
      </button>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuFolderOne">
        {{-- <a class="dropdown-item" href="javascript:void(0)">
          <i class="ti ti-info-circle ti-sm me-1"></i>
          <span class="align-middle">Spam</span>
        </a> --}}
        <a class="dropdown-item" href="javascript:void(0)">
          <i class="ti ti-file ti-sm me-1"></i>
          <span class="align-middle">{{ __('messages.Draft' )}}</span>
        </a>
        <a class="dropdown-item" href="javascript:void(0)">
          <i class="ti ti-trash ti-sm me-1"></i>
          <span class="align-middle">{{ __('messages.Trash' )}}</span>
        </a>
      </div>
    </div>
    {{-- <div class="dropdown mx-1">
      <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown"
        aria-expanded="true" id="dropdownLabelOne">
        <i class="ti ti-tag ti-md"></i>
      </button>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLabelOne">
        <a class="dropdown-item" href="javascript:void(0)">
          <i class="badge badge-dot bg-success me-1"></i>
          <span class="align-middle">Workshop</span>
        </a>
        <a class="dropdown-item" href="javascript:void(0)">
          <i class="badge badge-dot bg-primary me-1"></i>
          <span class="align-middle">Company</span>
        </a>
        <a class="dropdown-item" href="javascript:void(0)">
          <i class="badge badge-dot bg-warning me-1"></i>
          <span class="align-middle">Important</span>
        </a>
        <a class="dropdown-item" href="javascript:void(0)">
          <i class="badge badge-dot bg-danger me-1"></i>
          <span class="align-middle">Private</span>
        </a>
      </div>
    </div> --}}
  </div>
  <div class="d-flex align-items-center">
    {{-- <span class="btn btn-icon btn-text-secondary rounded-pill me-1">
      <i class="ti ti-refresh ti-md scaleX-n1-rtl cursor-pointer email-refresh"></i>
    </span> --}}
    <div class="dropdown">
      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button" id="emailsActions"
        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="ti ti-dots-vertical ti-md"></i>
      </button>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="emailsActions">
        <a class="dropdown-item ToggleMarkRead" do="{{ $ids }}" mark="read" href="javascript:void(0)">
          {{ __('messages.Mark as read') }}
        </a>
        <a class="dropdown-item ToggleMarkUnRead" do="{{ $ids }}" mark="unRead" href="javascript:void(0)">
          {{ __('messages.Mark as unread') }}
        </a>
        {{-- <a class="dropdown-item" href="javascript:void(0)">Delete</a>
        <a class="dropdown-item" href="javascript:void(0)">Archive</a> --}}
      </div>
    </div>
  </div>
</div>
