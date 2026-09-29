{{-- Archived Tasks Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="archivedTasksOffcanvas" aria-labelledby="archivedTasksOffcanvasLabel" style="width: 480px;">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="archivedTasksOffcanvasLabel">
      <i class="ti ti-archive me-2 text-secondary"></i>{{ __('admin.Archived_Tasks') }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-4">
    @if(empty($archivedTasks))
      <div class="text-center py-10">
        <i class="ti ti-inbox-off ti-3x text-muted mb-3 d-block"></i>
        <p class="text-muted mb-0">{{ __('admin.No_Archived_Tasks') }}</p>
      </div>
    @else
      <div class="d-flex flex-column gap-4">
        @foreach($archivedTasks as $task)
          <div class="card border shadow-none mb-0">
            <div class="card-body p-4">
              @if(!empty($task['program']))
                <div class="mb-1"><strong style="font-size:1rem;">{{ $task['program'] }}</strong></div>
              @endif
              <h6 class="mb-2">{{ $task['title'] }}</h6>
              <div class="text-muted small mb-3">{!! \Illuminate\Support\Str::limit(strip_tags($task['description']), 120) !!}</div>
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="badge bg-label-secondary">
                  <i class="ti ti-archive me-1"></i>{{ __('admin.archived') }}
                </span>
                <span class="badge bg-label-secondary">
                  <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($task['archived_at'])->format('M d, Y') }}
                </span>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
