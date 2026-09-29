<!-- Submissions Table -->
<div class="tab-pane fade {{ $activeTab == 'submissions' ? 'show active' : '' }}" id="submissions-pane" role="tabpanel">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom">
      <h5 class="card-title mb-0">{{ __('forms.submissions_list') }}</h5>
    </div>
    <div class="card-datatable table-responsive pt-0">
      <table class="datatables-basic table" id="submissionsTable">
        <thead class="table-light">
          <tr>
            <th>{{ __('forms.submission_id') }}</th>
            <th style="width:150px;">{{ __('forms.submitted_by') }}</th>
            <th>{{ __('forms.startup_name') }}</th>
            <th>{{ __('forms.organization_name') }}</th>
            <th>{{ __('forms.program_name') }}</th>
            <th>{{ __('forms.created_at') }}</th>
            <th>{{ __('forms.step_name') }}</th>
            <th>{{ __('forms.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($submissionsData as $submission)
            <tr>
              <td>{{ $submission->submission_id ?? '—' }}</td>
              <td style="width:150px; overflow: hidden;">
                @php
                  $submittedBy = $submission->submitted_by ?? '';
                  if (!empty($submittedBy)) {
                    $usernames = array_filter(array_map('trim', explode(',', $submittedBy)));
                    $totalCount = count($usernames);
                    $displayLimit = 2;
                    $modalId = 'usernamesModal-' . $submission->submission_id;

                    echo '<div class="d-flex flex-wrap gap-1">';
                    $displayed = 0;
                    foreach ($usernames as $username) {
                      if ($displayed < $displayLimit) {
                        echo '<small class="badge bg-label-info text-truncate d-inline-block" title="' . e($username) . '">' . e($username) . '</small>';
                        $displayed++;
                      } else {
                        break;
                      }
                    }
                    if ($totalCount > $displayLimit) {
                      $remaining = $totalCount - $displayLimit;
                      echo '<button class="btn btn-xs btn-label-secondary badge" data-bs-toggle="modal" data-bs-target="#' . $modalId . '">+' . $remaining . '</button>';
                    }
                    echo '</div>';
                  } else {
                    echo '—';
                  }
                @endphp
              </td>
              <td>{{ $submission->startup_name ?? '—' }}</td>
              <td>{{ $submission->organization_name ?? '—' }}</td>
              <td>{{ $submission->program_name ?? '—' }}</td>
              <td>{{ $submission->created_at ?? '—' }}</td>
              <td>{{ $submission->step_name ?? '—' }}</td>
              <td>
                <a href="/dashboard/submission-details/{{ $submission->step_id ?? '' }}"
                  class="btn btn-sm btn-primary text-nowrap">
                  {{ __('forms.view_details') }}
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Usernames Modals -->
  @foreach($submissionsData as $submission)
    @php
      $submittedBy = $submission->submitted_by ?? '';
      $usernames = !empty($submittedBy)
        ? array_filter(array_map('trim', explode(',', $submittedBy)))
        : [];
      $modalId = 'usernamesModal-' . $submission->submission_id;
    @endphp
    @if(count($usernames) > 2)
      <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ __('forms.all_contributors') }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="mb-3">{{ __('forms.submission_id') }}: <strong>{{ $submission->submission_id ?? '—' }}</strong></p>
              <div class="usernames-list">
                @foreach($usernames as $username)
                  <div class="d-flex align-items-center mb-2">
                    <i class="ti ti-user me-2 text-primary"></i>
                    <span class="fw-medium">{{ $username }}</span>
                  </div>
                @endforeach
              </div>
              <div class="text-muted small mt-3">
                <i class="ti ti-info-circle me-1"></i>
                {{ __('forms.total_contributors') }}: {{ count($usernames) }}
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('forms.close') }}</button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endforeach
</div>
