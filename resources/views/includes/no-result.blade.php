<div class="card card-body shadow-none h-100 d-flex justify-content-center align-items-center">
  <div class="d-flex flex-column">
    <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
    <h6 class="text-center text-secondary">{{ __('admin.No Result') }}</h6>
    <div class="text-center">
      @if(request()->query())
        <button type="button" class="btn btn-light" onclick="window.location.href='{{ request()->url() }}'">
          <i class="ti ti-reload me-2"></i>
          <span class="mx-1">{{ __('admin.Reset') }}</span>
        </button>
      @endif
    </div>
  </div>
</div>