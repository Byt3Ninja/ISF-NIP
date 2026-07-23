@php
  $countCards = [
    [
      'title'    => __('admin.total_tasks'),
      'count'    => $stats['total_tasks'] ?? 0,
      'subtitle' => __('admin.all_tasks'),
      'icon'     => 'ti-clipboard-list',
      'color'    => 'primary',
    ],
    [
      'title'    => __('admin.boardTitlePending'),
      'count'    => $stats['pending'] ?? 0,
      'subtitle' => __('admin.tasks') . ' ' .  __('admin.boardTitlePending'),
      'icon'     => 'ti-trending-up',
      'color'    => 'warning',
    ],
    [
      'title'    => __('admin.boardTitleReview'),
      'count'    => $stats['active']['in_review_count'] ?? 0,
      'subtitle' => __('admin.tasks') . ' ' . __('admin.boardTitleReview'),
      'icon'     => 'ti-activity',
      'color'    => 'info',
    ],
    [
      'title'    => __('admin.Completed') . ' / ' . __('admin.archived'),
      'count'    => ($stats['completed'] ?? 0) + ($stats['archived'] ?? 0),
      'subtitle' => '( ' . ($stats['completed'] ?? 0) . ' ) ' . __('admin.Completed') . ', ( ' . ($stats['archived'] ?? 0) . ' ) ' . __('admin.archived'),
      'icon'     => 'ti-check',
      'color'    => 'success',
    ],
  ];
@endphp

<!-- Count Cards -->
<div class="row g-6 mb-6">
  @foreach ($countCards as $card)
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">{{ $card['title'] }}</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $card['count'] }}</h4>
            </div>
            @if ($card['subtitle'])
              <small class="mb-0 text-muted">{{ $card['subtitle'] }}</small>
            @endif
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-{{ $card['color'] }}">
              <i class="ti {{ $card['icon'] }} ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<!--/ Count Cards -->
