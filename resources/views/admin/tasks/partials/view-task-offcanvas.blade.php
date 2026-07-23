{{-- ═══════════════════════════════════════════
     Offcanvas — View Task Details
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end w-px-500" tabindex="-1" id="viewTaskOffcanavas" aria-labelledby="viewTaskModalLabel">

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

  {{-- Tabs --}}
  <ul class="nav nav-tabs px-4 pb-0" id="viewTaskTabs" role="tablist" style="min-height: 50px;">
    <li class="nav-item h-100" role="presentation">
      <button class="nav-link active h-100" id="vto-overview-tab" data-bs-toggle="tab"
              data-bs-target="#vto-overview" type="button" role="tab"
              aria-controls="vto-overview" aria-selected="true">
        <i class="ti ti-layout-list ti-xs me-1"></i>{{ __('admin.overview') }}
      </button>
    </li>
    <li class="nav-item h-100" role="presentation">
      <button class="nav-link h-100" id="vto-assignees-tab" data-bs-toggle="tab"
              data-bs-target="#vto-assignees" type="button" role="tab"
              aria-controls="vto-assignees" aria-selected="false">
        <i class="ti ti-users ti-xs me-1"></i>{{ __('admin.assignees') }}
        <span id="vto-assignees-count" class="badge badge-center bg-label-secondary bg-glow rounded-pill ms-1"></span>
      </button>
    </li>
  </ul>
  
  <hr class="m-0" />

  {{-- Body --}}
  <div class="offcanvas-body p-0">
    <div class="tab-content p-0 h-100" id="viewTaskTabsContent">

      {{-- Overview Tab --}}
      <div class="tab-pane fade show active p-4" id="vto-overview" role="tabpanel" aria-labelledby="vto-overview-tab">
        <div id="vto-overview-content">
          {{-- Populated via JS --}}
        </div>
      </div>

      {{-- Assignees Tab --}}
      <div class="tab-pane fade p-4" id="vto-assignees" role="tabpanel" aria-labelledby="vto-assignees-tab">
        <div id="vto-assignees-content">
          {{-- Populated via JS --}}
        </div>
      </div>

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
