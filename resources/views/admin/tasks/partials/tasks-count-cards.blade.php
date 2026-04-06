@php
  $countCards = [
    ['key' => 'total_tasks', 'title' => __('admin.total_tasks'), 'subtitle' => __('admin.all_tasks')],
    ['key' => 'pending',     'title' => __('admin.Pending'),     'subtitle' => __('admin.tasks_waiting')],
    ['key' => 'active',      'title' => __('admin.Active'),      'subtitle' => null],
    ['key' => 'done',        'title' => __('admin.Completed') . ' / ' . __('admin.archived'),   'subtitle' => null],
  ];
  $icons  = ['ti-clipboard-list', 'ti-clock', 'ti-refresh', 'ti-check'];
  $colors = ['primary', 'warning', 'info', 'success'];
@endphp

<!-- Count Cards -->
<div class="row g-6 mb-6">
  @foreach ($countCards as $key => $card)
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">{{ $card['title'] }}</span>
            <div class="d-flex align-items-center my-1">
              @if ($card['key'] === 'active')
                <h4 class="mb-0 me-2">
                  {{ ($stats['active']['in_progress_count'] ?? 0) + ($stats['active']['in_review_count'] ?? 0) }}
                </h4>
              @elseif ($card['key'] === 'done')
                <h4 class="mb-0 me-2">
                  {{ ($stats['completed'] ?? 0) + ($stats['archived'] ?? 0) }}
                </h4>
              @else
                <h4 class="mb-0 me-2">{{ $stats[$card['key']] ?? 0 }}</h4>
              @endif
            </div>
            @if ($card['key'] === 'active')
              <small class="mb-0 text-muted">
                ( {{ $stats['active']['in_progress_count'] ?? 0 }} ) {{ __('admin.boardTitleProgress') }},
                ( {{ $stats['active']['in_review_count'] ?? 0 }} ) {{ __('admin.boardTitleReview') }}
              </small>
            @elseif ($card['key'] === 'done')
              <small class="mb-0 text-muted">
                ( {{ $stats['completed'] ?? 0 }} ) {{ __('admin.Completed') }},
                ( {{ $stats['archived'] ?? 0 }} ) {{ __('admin.archived') }}
              </small>
            @else
              <small class="mb-0 text-muted">{{ $card['subtitle'] }}</small>
            @endif
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-{{ $colors[$key] }}">
              <i class="ti {{ $icons[$key] }} ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<!--/ Count Cards -->
