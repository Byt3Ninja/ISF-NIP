<div class="d-flex justify-content-between align-items-center px-3 mt-2">
  <div class="d-flex align-items-center w-100">
    <i class="ti ti-menu-2 ti-lg cursor-pointer d-block d-lg-none me-4 mb-4" data-bs-toggle="sidebar"
      data-target="#app-email-sidebar" data-overlay></i>
    <div class="mb-4 w-100">
      <div class="input-group input-group-merge shadow-none">
        <span class="input-group-text border-0 ps-0 py-0" id="messages-search">
          <i class="ti ti-search ti-lg"></i>
        </span>
        <input 
          type="text" 
          class="form-control messages-search-input border-0 py-0"
          placeholder="{{ __('admin.Search Messages') }}" 
          aria-label="Search messages"
          aria-describedby="messages-search" 
          onkeyup="filterMessages(this.value)"
        />
      </div>
    </div>
  </div>
</div>