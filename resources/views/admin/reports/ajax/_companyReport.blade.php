<div class="col-md-12">
  <!-- Startup card details start -->
  <div class="card shadow-sm border mb-6">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
      <div class="d-flex gap-6">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3">
            <img src="{{ asset($company->logo) }}" alt="logo" class="rounded">
          </div>
          <div>
            <h4 class="mb-1">
              <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-heading">
                {{ $company->name }}
              </a>
            </h4>
            <p class="mb-0 text-muted">{{ $l == 'en' ? 'Startup Company' : 'شركة ناشئة' }}</p>
          </div>
        </div>
        {{-- Alumni Badge --}}
        @if ($company->alumni == '1' || $company->alumni == 1 || $company->alumni == true)
          <span class="badge bg-label-danger" style="height: fit-content;">
            <i class="ti ti-pennant ti-xs me-1"></i>
            {{ __('startup.alumni') }}
          </span>
        @endif
      </div>
      <div class="user-profile-header-banner">
        <a href="/dashboard/show/UserProfile/{{ $company?->user?->id }}" class="d-flex align-items-center">
          <div class="avatar avatar-sm me-2">
            <img src="{{ asset($company?->user?->image_url) }}" alt="avatar" class="rounded">
          </div>
          <div class="d-flex flex-column">
            <span class="fw-medium text-heading">{{ $company?->user?->name }}</span>
            <small class="text-muted">{{ $l == 'en' ? 'Startup Leader' : 'قائد الشركة' }}</small>
          </div>
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex flex-wrap gap-2 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 me-0 me-md-6">
          <span class="fw-semibold">{{ __('cycles.Program') }}</span>
          <span class="badge bg-label-success">
            <i class="ti ti-components ti-xs me-1"></i>
            {{ $company->cycledata->title }}
          </span>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 me-0 me-md-6">
          <span class="fw-semibold">{{ __('admin.Duration') }}</span>
          <span class="badge bg-label-info">
            <i class="ti ti-calendar ti-xs me-1"></i>
            {{ $company->cycledata->start_format }} - {{ $company->cycledata->end_format }}
          </span>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 me-0 me-md-6">
          <span class="fw-semibold">{{ __('cycles.Step') }}</span>
          <span class="badge bg-label-warning">
            <i class="ti ti-stairs ti-xs me-1"></i>
            {{ $company?->stepdata?->title ?? '-' }}
          </span>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-sm me-2">
            <img src="{{ asset($company->cycledata->organization->image_url) }}" alt="logo" class="rounded">
          </div>
          <div class="d-flex flex-column">
            <div class="text-dark fs-6">{{ $company->cycledata->organization->name }}</div>
            <small class="text-muted">{{ $l == 'en' ? 'Organization' : 'المؤسسة' }}</small>
          </div>
        </div>
        <a href="/dashboard/companyProfile/{{ $company->id }}" class="btn btn-sm btn-primary">
          <i class="ti ti-eye me-1"></i>
          {{ $l == 'en' ? 'Details' : 'التفاصيل' }}
        </a>
      </div>
    </div>
  </div>
  <!-- Startup card details ends -->
</div>

@php
$cards = [
  ['col' => 'col-lg-2 col-sm-6', 'color' => 'primary', 'icon' => 'ti-telescope', 'value' => $mentorRequests_count, 'title' => __('admin.Mentorship Sessions')],
  ['col' => 'col-lg-2 col-sm-6', 'color' => 'secondary', 'icon' => 'ti-calendar-event', 'value' => $mentorships_period, 'title' => __('admin.Mentorship Sessions Duration')],
  ['col' => 'col-lg-2 col-sm-6', 'color' => 'danger', 'icon' => 'ti-user-screen', 'value' => (int) $mentorship_attendance_rate . '%', 'title' => __('admin.Company Attendance Rate')],
  ['col' => 'col-lg-2 col-sm-6', 'color' => 'warning', 'icon' => 'ti-book', 'value' => $training_sessions_count, 'title' => __('admin.Training sessions')],
  ['col' => 'col-lg-2 col-sm-6', 'color' => 'success', 'icon' => 'ti-clock', 'value' => $training_sessions_period, 'title' => __('admin.Training sessions Duration')]
];
@endphp

<div class="col-12 mb-6">
  <div class="row justify-content-center g-4">
    @foreach($cards as $card)
    <div class="{{ $card['col'] }}">
      <div class="card card-border-shadow-{{ $card['color'] }} h-100">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <div class="avatar avatar-sm me-4">
              <div class="avatar-initial rounded bg-label-{{ $card['color'] }}">
                <i class="ti {{ $card['icon'] }} ti-xs"></i>
              </div>
            </div>
            <h5 class="card-title mb-0">{{ $card['value'] }}</h5>
          </div>
          <small class="text-center">{{ $card['title'] }}</small>
        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>

<div class="card shadow-sm border mb-6">
  <div class="card-header d-flex align-items-center">
    <div class="avatar me-3">
      <div class="avatar-initial rounded bg-label-primary">
        <i class="ti ti-components ti-lg"></i>
      </div>
    </div>
    <h5 class="card-title mb-0">{{ str_replace('&', ' ', __('startup.programs_summary')) }}</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="table" id="programsTable">
      <thead class="table-light">
        <tr>
          <th>{{ __('startup.program_Name') }}</th>
          <th>{{ __('startup.organization') }}</th>
          <th>{{ __('admin.Mentorship Sessions') }}</th>
          <th>{{ __('admin.Training sessions') }}</th>
          <th>{{ __('startup.attendance_rate') }}</th>
          <th>{{ __('startup.avgScreening') }}</th>
          <th>{{ __('startup.avgFinalScreening') }}</th>
          <th>{{ __('startup.status') }} / {{ __('cycles.Current Step') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($programs as $program)
        <tr>
          <td>{{ $program->program_name }}</td>
          <td>{{ $program->organization }}</td>
          <td>{{ $program->mentorship_sessions_count }}</td>
          <td>{{ $program->training_sessions_count }}</td>
          <td>{{ $program->attendance_rate }}%</td>
          <td>{{ $program->average_screening_score }}</td>
          <td>{{ $program->average_final_screening_score }}</td>
          <td>
            @if ($program->status === 'ongoing')
              <span class="badge bg-label-warning">
                {{ $program->current_step }}
              </span>
            @else
              <span class="badge bg-label-success">{{ __('admin.Completed') }}</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="col-12 mb-6">
  <div class="card shadow-sm border">
    <div class="card-header d-flex align-items-center">
      <div class="avatar me-3">
        <div class="avatar-initial rounded bg-label-secondary">
          <i class="ti ti-users ti-lg"></i>
        </div>
      </div>
      <h5 class="card-title mb-0">{{ __('admin.Mentorship Sessions') }}</h5>
    </div>
    <div id="mentorshipSessionsSection" class="card-datatable table-responsive">
      <table class="table" id="mentorshipSessionsTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>{{ __('cycles.Mentor') }}</th>
            <th>{{ __('cycles.Program') }}</th>
            <th>{{ __('cycles.Rate/Comment') }}</th>
            <th>{{ __('cycles.Date/Time') }}</th>
            <th>{{ __('cycles.Approved') }}</th>
            <th>{{ __('cycles.attendance_status') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($mentorship_sessions as $session)
          <tr>
            <td> {{ $session->id }}</td>
            <td>
              <a href="/dashboard/show/UserProfile/{{ $session->mentor->id }}" class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  <img src="{{ $session->mentor->image_url }}" alt="img" class="rounded">
                </div>
                <span class="fw-medium text-dark">{{ $session->mentor->name }}</span>
              </a>
            </td>
            <td>
              <span class="fw-medium">{{ $session->cycle->title }}</span>
            </td>
            <td>
              @if($session->rate_comment)
                <div class="mb-1">
                  @for($i = 1; $i <= $session->rating; $i++)
                    <i class="ti ti-star-filled text-warning"></i>
                  @endfor
                </div>
                <small class="text-muted">{{ $session->rate_comment ?? '-' }}</small>
              @else
                <small class="text-muted">{{ $session->comment ?? '-' }}</small>
              @endif
            </td>
            <td>
              <span class="fw-medium">{{ $session->zoom_date }}</span><br>
              <small class="text-muted">({{ $session->period }}M)</small>
            </td>
            <td>
              <span class="badge {{ $session->approved ? 'bg-label-success' : 'bg-label-warning' }}">{{ $session->approved ? __($session->approved) : __('Waiting') }}</span>
            </td>
            <td>
              <span class="badge {{ $session->done ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $session->done ? __('cycles.attended') : __('cycles.not_attended') }}</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card shadow-sm border">
    <div class="card-header d-flex align-items-center">
      <div class="avatar me-3">
        <div class="avatar-initial rounded bg-label-warning">
          <i class="ti ti-book ti-lg"></i>
        </div>
      </div>
      <h5 class="card-title mb-0">{{ __('admin.Training sessions') }}</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="table startup-tblTraining">
        <thead class="table-light">
          <tr>
            <th>{{ __('cycles.Type') }}</th>
            <th>{{ __('cycles.Title') }}</th>
            <th>{{ __('cycles.Date_Time') }}</th>
            <th>{{ __('cycles.details') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($training_sessions as $session)
          <tr>
            <td><span class="badge bg-label-info">{{ __('cycles.' . $session->type) }}</span></td>
            <td class="fw-medium">{{ $session->title }}</td>
            <td>
              @php
                $start  = \Carbon\Carbon::createFromTimestamp($session->datetime);
                $end    = $session->enddatetime ? \Carbon\Carbon::createFromTimestamp($session->enddatetime) : null;

                $startDate = $start->format('Y-m-d');
                $endDate   = $end ? $end->format('Y-m-d') : null;

                $startTime = $start->format('H:i:s');
                $endTime   = $end ? $end->format('H:i:s') : null;
              @endphp
              <small class="text-nowrap">
                @if ($session->type == 'online')
                  {{ $startDate }} @if($endDate) - {{ $endDate }}@endif
                @else
                  {{ $startDate }}
                @endif
              </small>
              <br>
              <small>
                @if($session->type != 'online')
                  {{ $startTime }}
                @endif
              </small>
            </td>
            <td>
              @if ($session->type == 'sessions')
                <small class="text-muted">{{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }}</small>
              @elseif ($session->type == 'offline')
                <div class="d-flex flex-column">
                  <small><strong>{{ __('cycles.trainer name') }}:</strong> {{ $session->trainer_name }}</small>
                  <small><strong>{{ __('cycles.training location') }}:</strong> {{ $session->location }}</small>
                </div>
              @else
                <a href="/dashboard/training/all/{{ $session->location }}" class="text-primary">{{ optional($session->course)->title }}</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('.startup-tblTraining, #mentorshipSessionsTable').DataTable({
    responsive: true,
    pageLength: 5,
    order: [[2, 'desc']]
    @if (app()->getLocale() == 'ar')
    ,language: {
      search: 'ابحث:',
      lengthMenu: 'أظهر _MENU_ مدخلات',
      info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
      paginate: { previous: 'السابق', next: 'التالي' },
      emptyTable: 'لا توجد بيانات'
    }
    @endif
  });

  $('#programsTable').DataTable({
    pageLength: 10,
    responsive: true,
    ordering: true,
    searching: true,
    order: [[3, 'desc']]
    @if (app()->getLocale() == 'ar')
    ,language: {
      search: 'ابحث:',
      lengthMenu: 'أظهر _MENU_ مدخلات',
      info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
      paginate: { previous: 'السابق', next: 'التالي' },
      emptyTable: 'لا توجد بيانات'
    }
    @endif
  });
});
</script>
