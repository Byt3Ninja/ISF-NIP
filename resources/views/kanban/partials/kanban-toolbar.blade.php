{{-- TOOLBAR --}}
<div class="card shadow-xs mb-6">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-md-center gap-3">
      <div class="d-flex align-items-center gap-2">
        <div class="avatar">
          <div class="avatar-initial rounded-circle">
            <i class="ti ti-rocket ti-md text-primary"></i>
          </div>
        </div>
        <h5 class="card-title mb-0">{{ $cycle['title'] }}</h5>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="input-group input-group-merge">
          <span class="input-group-text">
            <i class="ti ti-search text-muted"></i>
          </span>
          <input type="text" id="kanbanSearch" class="form-control" placeholder="{{ __('admin.Search') }}..." />
        </div>
        @if ($_user->ableTo('tasks.store', $permissions))
        <button class="btn btn-primary text-nowrap px-10" type="button" data-bs-toggle="offcanvas" data-bs-target="#addTaskOffcanvas">
          <i class="ti ti-plus me-2"></i> {{ __('admin.Add_Task') }}
        </button>
        @endif
      </div>  
    </div>
  </div>
</div>