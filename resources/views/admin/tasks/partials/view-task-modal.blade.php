{{-- ═══════════════════════════════════════════
     Offcanvas — View Task Details
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="viewTaskModal" aria-labelledby="viewTaskModalLabel">

  {{-- Header --}}
  <div class="offcanvas-header border-bottom">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm flex-shrink-0">
        <div class="avatar-initial rounded-circle bg-label-info">
          <i class="ti ti-eye ti-sm"></i>
        </div>
      </div>
      <h5 class="offcanvas-title mb-0" id="viewTaskModalLabel">{{ __('admin.task_details') }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  {{-- Body --}}
  <div class="offcanvas-body p-0">
    <div id="task-details-content" class="p-4">
      {{-- Populated via JS --}}
    </div>
  </div>

  {{-- Footer --}}
  <div class="offcanvas-footer border-top p-3 text-end">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
      <i class="ti ti-x me-1"></i>{{ __('admin.close') }}
    </button>
  </div>

</div>
{{-- /Offcanvas View Task Details --}}
