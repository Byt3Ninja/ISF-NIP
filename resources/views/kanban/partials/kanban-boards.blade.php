{{-- KANBAN BOARDS --}}
<div class="d-flex align-items-start gap-4 overflow-x-auto pb-3" id="kanbanWrapper">
  @foreach ($boards as $statusKey => $board)
    @php $boardTasks = array_values(array_filter($tasks, fn($t) => $t['status'] === $statusKey)); @endphp
    <div class="bg-body border rounded p-2 flex-shrink-0 kanban-board" data-board="{{ $statusKey }}" style="width: 300px;">
      <header class="d-flex align-items-center gap-2 px-2 py-2 mb-2">
        <span class="badge {{ $board['color'] }} rounded-pill board-count">{{ count($boardTasks) }}</span>
        <h6 class="mb-0 fw-semibold">{{ $board['label'] }}</h6>
      </header>

      <main class="kanban-drag d-flex flex-column gap-2" style="min-height: 60px;">
        @forelse ($boardTasks as $task)
          @php
            $p = $priorityConfig[$task['priority']] ?? $priorityConfig['Medium'];
            $r = $task['creator'] ?? ['name' => 'User'];
            $ri = strtoupper(substr($r['name'], 0, 2));
            $attCount = count($task['attachments'] ?? []);
            $commentCount = count($task['comments'] ?? []);
            $lastFeedback = !empty($task['feedbacks']) ? end($task['feedbacks']) : null;
          @endphp
          <div class="card shadow-sm border-0 cursor-grab kanban-item" data-task-id="{{ $task['id'] }}" data-search-text="{{ strtolower($task['title']) }}">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge {{ $p['class'] }} rounded-pill" style="font-size: 0.7rem;">
                  <span class="d-inline-block rounded-circle me-1 {{ $p['dot'] }}" style="width: 6px; height: 6px;"></span>{{ $p['label'] }}
                </span>
                <div class="d-flex align-items-center gap-1">
                  <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle view-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.View') }}"><i class="ti ti-eye ti-md text-dark"></i></button>
                  @if ($_user->ableTo('tasks.update', $permissions))
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle edit-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.Edit') }}">
                      <i class="ti ti-edit ti-md text-dark"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle archive-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.archive_task') }}">
                      <i class="ti ti-archive ti-md text-dark"></i>
                    </button>
                  @endif
                </div>
              </div>
              <h6 class="mb-1 text-truncate">{{ $task['title'] }}</h6>
              <div class="text-muted small mb-2 line-clamp-2">{!! strip_tags($task['description']) !!}</div>
              @if ($lastFeedback)
                <span class="badge {{ $lastFeedback['decision'] === 'revision' ? 'bg-label-danger' : 'bg-label-success' }} rounded-pill mb-2" style="font-size: 0.7rem;">
                  <i class="ti {{ $lastFeedback['decision'] === 'revision' ? 'ti-rotate' : 'ti-check' }} ti-xs me-1"></i>
                  {{ $lastFeedback['decision'] === 'revision' ? __('admin.needs_revision') : __('admin.pass_task') }}
                </span>
              @endif
              <div class="d-flex align-items-center justify-content-between small text-muted mt-3">
                <span class="d-flex align-items-center gap-2">
                  @if($attCount > 0)
                    <span class="d-flex align-items-center" title="{{ $attCount }} Attachments">
                      <i class="ti ti-paperclip ti-xs me-1"></i>{{ $attCount }}
                    </span>
                  @endif
                  @if($commentCount > 0)
                    <span class="d-flex align-items-center" title="{{ $commentCount }} Comments">
                      <i class="ti ti-message ti-xs me-1"></i>{{ $commentCount }}
                    </span>
                  @endif
                  <span class="d-flex align-items-center">
                    <i class="ti ti-calendar ti-xs me-1"></i> {{ $task['due_date'] }}
                  </span>
                </span>
                <div class="d-flex align-items-center gap-2">
                  <span>{{ __('admin.Reporter') }}:</span>
                  <div data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $r['name'] }}">
                    @if (!empty($r['img_url']))
                      <img src="{{ $r['img_url'] }}" alt="avatar" class="rounded-circle object-fit-cover" style="width: 24px; height: 24px;" />
                    @else
                      <span class="badge bg-label-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 24px; height: 24px;">{{ $ri }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <p class="text-muted small text-center py-3 m-0" data-empty-board>{{ __('admin.No_Tasks') }}</p>
        @endforelse
      </main>
    </div>
  @endforeach
</div>
