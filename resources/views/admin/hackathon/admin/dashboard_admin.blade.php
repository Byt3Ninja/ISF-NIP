@extends('layouts.admin')

@php
  $idParam  = request()->route('id');
  $isEn     = $l === 'en';

  // ── Metrics ──────────────────────────────────────────────────────────────
  $metrics = [
    ['icon' => 'users',       'color' => 'primary',   'title' => __('admin.Teams'),               'value' => $data['count_teams']],
    ['icon' => 'user-check',  'color' => 'success',   'title' => __('admin.Submitted_Projects'),  'value' => $data['Count_submitted_projects']],
    ['icon' => 'user-dollar', 'color' => 'info',      'title' => __('admin.Founders'),            'value' => $data['count_members']],
    ['icon' => 'user-screen', 'color' => 'secondary', 'title' => __('admin.mentorship_management'), 'value' => $data['CountNextSessions'], 'is_mentorship' => true],
  ];

  // ── Chart data ────────────────────────────────────────────────────────────
  $regMonths = ['january','february','march','april','may','june','july'];
  $regCounts = [65, 59, 80, 81, 156, 155, 30];

  $pieLabels = [$isEn ? 'In Team' : 'في فريق', $isEn ? 'Not in Team' : 'ليس في فريق'];
  $pieData   = [$data['countInTeams'], $data['countNotInTeams']];

  // ── Submitted-projects progress bar ──────────────────────────────────────
  $spTotal   = $data['usersWithFormDataCount'] + $data['usersWithoutFormDataCount'];
  $spPercent = $spTotal > 0 ? ($data['usersWithFormDataCount'] / $spTotal) * 100 : 0;
  $spColor   = $spPercent < 25 ? 'bg-danger' : ($spPercent < 50 ? 'bg-warning' : ($spPercent < 75 ? 'bg-info' : 'bg-success'));

  // ── Mentors list ──────────────────────────────────────────────────────────
  $mentorsList = showMintors(new \Illuminate\Http\Request(['cycleIdHackathon' => $idParam]));

  // ── Teams JSON for modal ──────────────────────────────────────────────────
  $teamsJson = $data['all_teams']->map(fn($t) => [
    'id'          => $t->id,
    'name'        => $t->name,
    'leader'      => $t->user_name,
    'projectIdea' => $t->project_name_en ?? __('admin.idea_not_specified_yet'),
    'members'     => collect($t->members ?? [])
      ->filter(fn($m) => !empty($m->name))
      ->map(fn($m) => [
        'name'      => $m->name,
        'email'     => $m->email     ?? '',
        'img_url'   => $m->img_url   ? '/191014/'.$m->img_url : '',
        'is_leader' => $m->is_leader ?? false,
      ])->values()->all(),
  ])->keyBy('id')->toJson(JSON_UNESCAPED_UNICODE);

  // ── Project-status helper (used multiple times) ───────────────────────────
  $statusMap = [
    20  => ['label' => __('admin.Idea + Team'),       'badge' => 'primary'],
    40  => ['label' => __('admin.Research'),           'badge' => 'info'],
    60  => ['label' => __('admin.Design & Build'),     'badge' => 'warning'],
    80  => ['label' => __('admin.Ready for Pitch'),    'badge' => 'secondary'],
    100 => ['label' => __('admin.submitted'),          'badge' => 'success'],
  ];
@endphp

{{-- ── Reusable export-dropdown snippet (inline, no partial needed) ──────── --}}
@php
  /**
   * Usage: {!! exportDropdown('my-table-class') !!}
   * Renders the Export button + CSV/Excel dropdown for a given DataTable class.
   */
  function exportDropdown(string $tableClass, string $l = 'en'): string {
    $export = __('cycles.Export');
    $csv    = $l === 'ar' ? 'تصدير CSV'   : 'Export CSV';
    $excel  = $l === 'ar' ? 'تصدير Excel' : 'Export Excel';
    return <<<HTML
      <div class="btn-group ms-3">
        <button type="button" class="btn btn-label-secondary dropdown-toggle"
          data-bs-toggle="dropdown" aria-expanded="false">
          <i class="ti ti-file-export me-2"></i> {$export}
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="javascript:void(0)"
              onclick="exportTable('{$tableClass}','csv')">
              <i class="ti ti-file-type-csv me-2"></i>{$csv}
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0)"
              onclick="exportTable('{$tableClass}','excel')">
              <i class="ti ti-file-type-xls me-2"></i>{$excel}
            </a>
          </li>
        </ul>
      </div>
    HTML;
  }
@endphp

@section('content')
<div class="p-6 bg-card rounded shadow-sm mb-6">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="ti ti-dashboard ti-28px"></i>
      <h4 class="mb-0">@lang('admin.Hackathon_Dashboard')</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-primary" href="/dashboard/hackathon/dashboard/winners/{{ $idParam }}">
        <i class="ti ti-trophy me-2"></i>
        {{ $isEn ? 'Results & Winners' : 'النتائج والفائزون' }}
      </a>
      <button class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#manageMentorsModal">
        <i class="ti ti-user-cog me-2"></i> {{ __('admin.manage_mentors') }}
      </button>
      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewMentorshipRequestsModal">
        <i class="ti ti-pointer-question me-2"></i> {{ __('admin.mentorship_requests') }}
      </button>
    </div>
  </div>
</div>

{{-- ── Metrics Cards ────────────────────────────────────────────────────── --}}
<div class="row g-6 mb-6">
  @foreach ($metrics as $m)
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <span class="text-heading">{{ $m['title'] }}</span>
            @if (!empty($m['is_mentorship']))
              <h6 class="mt-4 mb-n1">
                {{ __('admin.new_requests') }}:
                <span class="badge badge-center bg-danger rounded-pill bg-glow ms-2">{{ $m['value'] }}</span>
              </h6>
            @else
              <h4 class="mt-2 me-2 mb-n1">{{ $m['value'] ?? 0 }}</h4>
            @endif
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-{{ $m['color'] }}">
              <i class="ti ti-{{ $m['icon'] }} ti-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- ── Charts ───────────────────────────────────────────────────────────── --}}
<div class="row mb-6 g-6">
  <div class="col-12">
    <div class="bg-card p-6 rounded shadow" style="height:350px">
      <canvas id="registrationsChart"></canvas>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="bg-card p-6 rounded shadow" style="height:350px">
      <canvas id="submissionsPerTrackChart"></canvas>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="bg-card p-6 rounded shadow" style="height:350px">
      <canvas id="teamsStatusChart"></canvas>
    </div>
  </div>
</div>

{{-- ── Teams Table ──────────────────────────────────────────────────────── --}}
<div class="card mb-6" id="manage-teams-section">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('admin.manage_participating_teams') }}</h5>
      {!! exportDropdown('teams-table', $l) !!}
    </div>
  </div>
  <div class="table-responsive card-datatable">
    <table class="table teams-table align-middle">
      <thead class="table-light">
        <tr>
          <th>{{ __('admin.team_name') }}</th>
          <th>{{ __('admin.team_leader') }}</th>
          <th>{{ __('admin.Email') }}</th>
          <th>{{ __('admin.Phone') }}</th>
          <th>{{ __('admin.number_of_members') }}</th>
          <th>{{ __('admin.current_step') }}</th>
          <th>{{ __('admin.creation_date') }}</th>
          <th>{{ __('admin.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['all_teams'] as $team)
        @php $leaderId = $team->members->where('is_leader', 1)->first()?->id; @endphp
        <tr>
          <td>{{ $team->name }}</td>
          <td>{{ $team->user_name }}</td>
          <td>{{ $team->email }}</td>
          <td>{{ $team->u_phone }}</td>
          <td>{{ $team->count_members }} / {{ $team->max_count_members }}</td>
          <td>
            @if($team->step_state === 'step')
              <span class="badge bg-label-primary">{{ $team->company_step }}</span>
            @elseif($team->step_state === 'disapproved')
              <span class="badge bg-label-danger">{{ __('admin.disapproved') }}</span>
            @elseif($team->step_state === 'alumni')
              <span class="badge bg-label-success">{{ __('admin.alumni') }}</span>
            @else
              <span class="text-muted">&mdash;</span>
            @endif
          </td>
          <td>{{ $team->created_at }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-icon btn-text-secondary waves-effect rounded-pill"
                data-bs-toggle="modal" data-bs-target="#teamDetailsModal"
                data-team-id="{{ $team->id }}" title="{{ __('admin.View_Details') }}">
                <i class="ti ti-eye ti-md"></i>
              </button>
              <button class="btn btn-icon btn-text-secondary waves-effect rounded-pill"
                data-bs-toggle="modal" data-bs-target="#sendMessageModal"
                data-team-id="{{ $leaderId }}" data-team-leader="{{ $team->user_name }}"
                title="{{ __('admin.Send_Message') }}">
                <i class="ti ti-mail ti-md"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── Projects Table ───────────────────────────────────────────────────── --}}
<div class="card mb-6" id="manage-users-section">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('admin.manage_Projects') }}</h5>
      {!! exportDropdown('projects-table', $l) !!}
    </div>
  </div>
  <div class="table-responsive card-datatable">
    <table class="table projects-table align-middle">
      <thead class="table-light">
        <tr>
          <th>{{ __('admin.project_name') }}</th>
          <th>{{ __('admin.project_description') }}</th>
          <th>{{ __('admin.team_leader') }}</th>
          <th>{{ __('admin.Email') }}</th>
          <th>{{ __('admin.Phone') }}</th>
          <th>{{ __('admin.Status') }}</th>
          <th>{{ __('admin.Region') }}</th>
          <th>{{ __('admin.Progress') }}</th>
          <th>{{ __('admin.Created At') }}</th>
          <th>{{ __('admin.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['all_projects'] as $project)
        @php
          $status = $statusMap[$project->project_status] ?? null;
          $region = $project->region ? __('admin.'.$project->region) : '';
        @endphp
        <tr>
          <td><div class="fw-semibold w-px-100">{{ $project->project_name }}</div></td>
          <td><div class="small text-truncate" style="max-width:200px">{{ $project->project_description }}</div></td>
          <td>{{ $project->leader->name }}</td>
          <td><div class="small w-px-100">{{ $project->leader->email }}</div></td>
          <td><div class="small">{{ $project->leader->phone }}</div></td>
          <td>
            @if($status)
              <span class="badge bg-label-{{ $status['badge'] }}">{{ $status['label'] }}</span>
            @endif
          </td>
          <td>{{ $region }}</td>
          <td>
            <div class="d-flex flex-column mb-2">
              <div class="d-flex justify-content-between mb-1">
                <small class="fs-tiny">{{ __('admin.Progress') }}</small>
                <small class="fs-tiny fw-bold">{{ $project->project_status }}%</small>
              </div>
              <div class="progress" style="height:8px">
                <div class="progress-bar" role="progressbar"
                  style="width:{{ $project->project_status }}%;border-radius:12px"
                  aria-valuenow="{{ $project->project_status }}" aria-valuemin="0" aria-valuemax="100">
                </div>
              </div>
            </div>
          </td>
          <td><div class="small w-px-100">{{ $project->created_at }}</div></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-icon btn-text-secondary waves-effect rounded-pill"
                data-bs-toggle="modal" data-bs-target="#projectDetailsModal{{ $project->id }}"
                title="{{ __('admin.View_Details') }}">
                <i class="ti ti-eye ti-md"></i>
              </button>
              <button class="btn btn-icon btn-text-secondary waves-effect rounded-pill"
                data-bs-toggle="modal" data-bs-target="#editProjectModal{{ $project->id }}"
                title="{{ __('admin.edit_project') }}">
                <i class="ti ti-pencil ti-md"></i>
              </button>
            </div>
          </td>
        </tr>

        {{-- Edit Project Modal --}}
        <div class="modal fade" id="editProjectModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">{{ $isEn ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form method="POST" action="/dashboard/hackathon/EditProjectIdea">
                @csrf
                <input type="hidden" name="team_formation_id" value="{{ $project->id }}">
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">{{ __('admin.Project_Name') }}</label>
                    <input type="text" class="form-control" name="Project_Name_en" value="{{ $project->project_name }}" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ __('admin.Project_Description') }}</label>
                    <textarea class="form-control" name="Project_Description_en" rows="2">{{ $project->project_description }}</textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ $isEn ? 'Project Status' : 'حالة المشروع' }}</label>
                    <select class="form-select" name="Project_Status">
                      <option value="">{{ $isEn ? 'Choose...' : 'اختر الحالة...' }}</option>
                      @foreach($statusMap as $val => $s)
                        <option value="{{ $val }}" {{ $project->project_status == $val ? 'selected' : '' }}>{{ $s['label'] }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">{{ $isEn ? 'Region' : 'المنطقة' }}</label>
                    <select class="form-select" name="Region">
                      <option value="">{{ $isEn ? 'Choose Region...' : 'اختر المنطقة...' }}</option>
                      @foreach(['AL_Madinah_AL_Munawwarah','Al_Ahsa','Asir'] as $reg)
                        <option value="{{ $reg }}" {{ $project->region === $reg ? 'selected' : '' }}>{{ __('admin.'.$reg) }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    {{ $isEn ? 'Cancel' : 'إلغاء' }}
                  </button>
                  <button type="submit" class="btn btn-primary">
                    {{ $isEn ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        {{-- Project Details Modal --}}
        <div class="modal fade" id="projectDetailsModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="ti ti-info-circle me-2"></i>{{ $isEn ? 'Project Details' : 'تفاصيل المشروع' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6">
                    @foreach([
                      [$isEn ? 'Project Name' : 'اسم المشروع', $project->project_name],
                      [$isEn ? 'Team Leader'  : 'قائد الفريق', $project->leader->name],
                      [$isEn ? 'Email'        : 'البريد الإلكتروني', $project->leader->email],
                      [$isEn ? 'Phone'        : 'رقم الهاتف', $project->leader->phone],
                    ] as [$lbl, $val])
                    <div class="mb-3">
                      <label class="form-label fw-bold">{{ $lbl }}</label>
                      <p class="form-control-plaintext border p-2 rounded bg-light">{{ $val }}</p>
                    </div>
                    @endforeach
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold">{{ $isEn ? 'Region' : 'المنطقة' }}</label>
                      <p class="form-control-plaintext border p-2 rounded bg-light">{{ $region }}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">{{ $isEn ? 'Creation Date' : 'تاريخ الإنشاء' }}</label>
                      <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->created_at }}</p>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">{{ $isEn ? 'Project Status' : 'حالة المشروع' }}</label>
                      <div class="border p-2 rounded bg-light">
                        @if($status)
                          <span class="badge bg-{{ $status['badge'] }}">{{ $status['label'] }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label fw-bold">{{ $isEn ? 'Progress' : 'التقدم' }}</label>
                      @php
                        $pbColor = $project->project_status == 100 ? 'bg-success'
                          : ($project->project_status >= 80 ? 'bg-primary'
                          : ($project->project_status >= 60 ? 'bg-info'
                          : ($project->project_status >= 40 ? 'bg-warning' : 'bg-danger')));
                      @endphp
                      <div class="progress" style="height:28px;border-radius:14px">
                        <div class="progress-bar {{ $pbColor }}"
                          style="width:{{ $project->project_status }}%;border-radius:14px;font-weight:600"
                          role="progressbar"
                          aria-valuenow="{{ $project->project_status }}" aria-valuemin="0" aria-valuemax="100">
                          {{ $project->project_status }}% {{ $isEn ? 'Complete' : 'مكتمل' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-bold">{{ $isEn ? 'Project Description' : 'وصف المشروع' }}</label>
                  <div class="border p-3 rounded bg-light" style="min-height:100px">
                    {{ $project->project_description ?: ($isEn ? 'No description provided' : 'لا يوجد وصف متاح') }}
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                  {{ $isEn ? 'Close' : 'إغلاق' }}
                </button>
                <button type="button" class="btn btn-primary"
                  data-bs-dismiss="modal" data-bs-toggle="modal"
                  data-bs-target="#editProjectModal{{ $project->id }}">
                  <i class="ti ti-edit me-1"></i> {{ $isEn ? 'Edit Project' : 'تعديل المشروع' }}
                </button>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── Submitted Projects ───────────────────────────────────────────────── --}}
<div class="card mb-6" id="submitted-project-section">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <div class="flex-grow-1">
        <h5 class="card-title mb-0">
          {{ __('admin.Submitted_Projects') }}
          @if(count($data['submitted_projects']) > 0)
            <div class="progress mt-2" style="height:12px">
              <div class="progress-bar {{ $spColor }}" role="progressbar"
                style="width:{{ $spPercent }}%"
                aria-valuenow="{{ $spPercent }}" aria-valuemin="0" aria-valuemax="100">
                {{ round($spPercent, 2) }}%
              </div>
            </div>
          @endif
        </h5>
      </div>
      {!! exportDropdown('submitted-projects-table', $l) !!}
    </div>
  </div>
  <div class="table-responsive card-datatable">
    <table class="table submitted-projects-table align-middle">
      <thead class="table-light">
        <tr>
          <th>{{ $isEn ? 'Team Name'         : 'اسم الفريق' }}</th>
          <th>{{ $isEn ? 'Submitted On'       : 'تم التقديم في' }}</th>
          <th>{{ $isEn ? 'Project Name'       : 'اسم المشروع' }}</th>
          <th>{{ $isEn ? 'Date of Submission' : 'تاريخ التقديم' }}</th>
          <th>{{ $isEn ? 'Team Leader'        : 'قائد الفريق' }}</th>
          <th>{{ $isEn ? 'Email'              : 'البريد الإلكتروني' }}</th>
          <th>{{ $isEn ? 'Phone'              : 'رقم الهاتف' }}</th>
          <th>{{ $isEn ? 'Region'             : 'المنطقة' }}</th>
          <th>{{ $isEn ? 'View Details'       : 'عرض التفاصيل' }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['submitted_projects'] as $project)
        <tr>
          <td>{{ $project->team_name }}</td>
          <td>{{ $project->step_name }}</td>
          <td>{{ $project->project_name }}</td>
          <td>{{ $project->submissionDate }}</td>
          <td>{{ $project->team_leader }}</td>
          <td>{{ $project->eamil }}</td>
          <td>{{ $project->phone }}</td>
          <td>{{ $project->region ? __('admin.'.$project->region) : '' }}</td>
          <td>
            <a target="_blank" href="/dashboard/hackathon/viewSubmission/{{ $project->step_id }}/{{ $project->user_id }}"
              class="btn btn-label-primary btn-sm">{{ $isEn ? 'View' : 'عرض' }}</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── Not-Submitted Projects ───────────────────────────────────────────── --}}
<div class="card mb-6" id="not-submitted-project-section">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('admin.Projects_NotSubmitted') }}</h5>
      {!! exportDropdown('not-submitted-projects-table', $l) !!}
    </div>
  </div>
  <div class="table-responsive card-datatable">
    <table class="table not-submitted-projects-table align-middle">
      <thead class="table-light">
        <tr>
          <th>{{ $isEn ? 'Team Name'        : 'اسم الفريق' }}</th>
          <th>{{ $isEn ? 'Not Submitted On' : 'لم يتم التقديم في' }}</th>
          <th>{{ $isEn ? 'Project Name'     : 'اسم المشروع' }}</th>
          <th>{{ $isEn ? 'Team Leader'      : 'قائد الفريق' }}</th>
          <th>{{ $isEn ? 'Email'            : 'البريد الإلكتروني' }}</th>
          <th>{{ $isEn ? 'Phone'            : 'رقم الهاتف' }}</th>
          <th>{{ $isEn ? 'Region'           : 'المنطقة' }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['not_submitted_projects'] as $p)
        <tr>
          <td>{{ $p->team_name }}</td>
          <td>{{ $p->company_step }}</td>
          <td>{{ $p->project_name }}</td>
          <td>{{ $p->team_leader }}</td>
          <td>{{ $p->email }}</td>
          <td>{{ $p->phone }}</td>
          <td>{{ $p->region ? __('admin.'.$p->region) : '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     MODALS
     ════════════════════════════════════════════════════════════════════════ --}}

{{-- Manage Mentors --}}
<div class="modal fade" id="manageMentorsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.manage_mentors') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <a href="/dashboard/mentorship/assign/{{ $idParam }}" class="btn btn-success mb-3">
          <i class="ti ti-users-plus me-2"></i> {{ __('admin.Mentors') }}
        </a>
        @forelse ($mentorsList ?? [] as $mentor)
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <img src="{{ asset($mentor->image_url) }}" alt="" class="rounded-circle me-3"
                style="width:50px;height:50px;object-fit:cover">
              <div>
                <h5 class="mb-0">{{ $mentor->name }}</h5>
                <p class="small text-muted mb-0">{{ $mentor->email }}</p>
              </div>
            </div>
          </div>
        </div>
        @empty
        <p>{{ __('admin.current_mentors_table_placeholder') }}</p>
        @endforelse
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
      </div>
    </div>
  </div>
</div>

{{-- Mentorship Requests --}}
<div class="modal fade" id="viewMentorshipRequestsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.mentorship_session_requests') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="border-bottom pb-2 mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class="ti ti-calendar-event me-2"></i>
              <h5 class="mb-0">{{ __('dashboard.UpcomingMentorsSession') }}</h5>
            </div>
            <a class="btn btn-sm btn-primary" href="/dashboard/mentorship/mine">
              <i class="ti ti-eye me-2 ti-16px"></i> {{ __('cycles.showAll') }}
            </a>
          </div>
        </div>
        <div class="table-responsive card-datatable">
          <table class="table sessonsTable mb-0">
            <thead class="bg-label-light">
              <tr>
                <th>{{ __('cycles.User/Title') }}</th>
                <th>{{ __('cycles.Mentor') }}</th>
                @if ($_user->hasRole('Admin'))<th>{{ __('cycles.Rate/Comment') }}</th>@endif
                <th>{{ __('cycles.Date/Time') }}</th>
                <th>{{ __('cycles.Approved') }}</th>
                <th>{{ __('cycles.Done') }}</th>
                <th>{{ __('cycles.Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($nextSessions as $session)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <a href="/dashboard/users/{{ $session->user_id }}/edit">
                      <img src="{{ $session->user->image_url }}" alt="" class="rounded-circle me-3" height="40" width="40">
                    </a>
                    <a href="/dashboard/users/{{ $session->user_id }}/edit">
                      <h6 class="mb-0 text-nowrap">{{ $session->user->name }}</h6>
                      <span class="font-small-2 text-nowrap">{{ $session->user->title }}</span>
                    </a>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center" style="cursor:pointer"
                    onclick="mentor({{ $session->mentor_id }},'{{ $session->mentor->name }}','{{ $session->mentor->ratings }}')">
                    <img class="rounded-circle me-3" src="{{ $session->mentor->image_url ?? '/icons/account.svg' }}"
                      height="20" width="20" alt="">
                    <span class="text-nowrap">{{ $session->mentor->name }}</span>
                  </div>
                </td>
                @if ($_user->hasRole('Admin'))
                <td>
                  <small>{!! $session->rate_comment
                    ? str_repeat('<i class="bx bxs-star" style="color:#fc960f"></i>', $session->rating).'<br>'.$session->rate_comment
                    : $session->comment !!}</small>
                </td>
                @endif
                <td>{{ $session->zoom_date }}<br>({{ $session->period }}M)</td>
                <td>{!! __($session->approved) ?? '<span class="warning">'.__('Waiting').'</span>' !!}</td>
                <td>{{ $session->done ? __('Yes') : __('No') }}</td>
                <td>
                  <a class="btn btn-sm btn-label-primary" href="{{ $session->join_url }}" target="_blank">
                    <i class="ti ti-brand-zoom me-2"></i>{{ __('dashboard.Join now') }}
                  </a>
                  @if (!$session->approved)
                  <button class="btn btn-sm btn-icon btn-label-success rounded-circle me-1"
                    onclick="approve({{ $session->id }},'Yes')" title="Approve">
                    <i class="ti ti-check"></i>
                  </button>
                  <button class="btn btn-sm btn-icon btn-label-danger rounded-circle"
                    onclick="approve({{ $session->id }},'No')" title="Disapprove">
                    <i class="ti ti-x"></i>
                  </button>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <p class="mt-3">{{ __('admin.team_mentorship_requests_table_placeholder') }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
      </div>
    </div>
  </div>
</div>

{{-- Team Details --}}
<div class="modal fade" id="teamDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.team_details_title') }} <span id="modal-team-name-detail"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <label class="form-label fw-bold me-2">{{ __('admin.Team Name') }}:</label>
          <span id="modal-team-hackathon"></span>
        </div>
        <div class="mb-4">
          <label class="form-label fw-bold me-2">{{ __('admin.team_leader') }}:</label>
          <span id="modal-team-leader"></span>
        </div>
        <div class="mb-4">
          <label class="form-label fw-bold me-2">{{ __('admin.members') }}</label>
          <ul class="list-group list-group-flush" id="modal-team-members-list"></ul>
        </div>
        <div class="mb-4">
          <label class="form-label fw-bold me-2">{{ __('admin.project_idea_if_available') }}</label>
          <div id="modal-team-project-idea" class="border rounded bg-light p-2"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
      </div>
    </div>
  </div>
</div>

{{-- Send Message --}}
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendMessageModalLabel">
          {{ $isEn ? 'Send message to team leader' : 'إرسال رسالة إلى قائد الفريق' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">{{ $isEn ? 'Subject' : 'الموضوع' }}</label>
          <input type="text" class="form-control" id="messageSubject"
            placeholder="{{ $isEn ? 'Enter subject' : 'أدخل الموضوع' }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ $isEn ? 'Message' : 'الرسالة' }}</label>
          <textarea class="form-control" id="messageContent" rows="5"
            placeholder="{{ $isEn ? 'Enter your message' : 'أدخل رسالتك' }}" required></textarea>
        </div>
        <input type="hidden" id="recipientId">
        <div class="d-flex justify-content-end gap-3 mt-4">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
          <button type="button" id="sendMessageBtn" class="btn btn-primary w-px-150">{{ __('admin.Send_Message') }}</button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ── DataTables ────────────────────────────────────────────────────────────
(function () {
  if (!window.jQuery || !$.fn.DataTable) return;

  const dtBase = {
    pageLength: 10,
    order: [[0, 'desc']],
    buttons: ['csv', 'excel'],
    language: {
      url: document.dir === 'rtl'
        ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...',
    },
    dom: '<"d-flex justify-content-between align-items-center row"<"col-sm-12 col-md-1"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    initComplete() {
      setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
      }, 300);
    },
  };

  const tables = {
    'teams-table':                 null,
    'projects-table':              null,
    'submitted-projects-table':    null,
    'not-submitted-projects-table':null,
  };

  Object.keys(tables).forEach(cls => {
    tables[cls] = $('.' + cls).DataTable($.extend(true, {}, dtBase));
  });

  window.exportTable = function (tableClass, format) {
    const dt = tables[tableClass];
    if (!dt) return;
    dt.button(format === 'csv' ? '.buttons-csv' : '.buttons-excel').trigger();
  };
})();
</script>
@endsection

@section('pagescripts')
<script>
const teamsData = {!! $teamsJson !!};

// ── Mentorship approve ────────────────────────────────────────────────────
function approve(id, stat) {
  alert(stat === 'Yes' ? '{{ __('admin.Accepting_request') }}' : '{{ __('admin.Rejecting_request') }}');
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
  $.post('/dashboard/mentors/modify', { id, stat, rtype: 6 })
    .done(() => location.reload())
    .fail(() => alert('Error Occurred, Try again!'));
}

// ── Team Details Modal ────────────────────────────────────────────────────
document.getElementById('teamDetailsModal')?.addEventListener('show.bs.modal', function (e) {
  const team = teamsData[e.relatedTarget.dataset.teamId];
  if (!team) return;

  document.getElementById('modal-team-name-detail').textContent = team.name;
  document.getElementById('modal-team-hackathon').textContent   = team.name;
  document.getElementById('modal-team-leader').textContent      = team.leader;
  document.getElementById('modal-team-project-idea').textContent =
    team.projectIdea || '{{ __('admin.idea_not_specified_yet') }}';

  const list = document.getElementById('modal-team-members-list');
  list.innerHTML = '';

  const members = (team.members || []).filter(Boolean);
  if (!members.length) {
    list.innerHTML = `<li class="list-group-item text-muted">{{ __('admin.no_members_found') }}</li>`;
    return;
  }

  const leaderLabel = '{{ __('admin.team_leader') }}';
  members.forEach(m => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex align-items-center';
    li.innerHTML = `
      <div class="d-flex align-items-center w-100">
        <img src="${m.img_url || '/icons/account.svg'}" alt="" class="rounded-circle me-3"
          style="width:40px;height:40px;object-fit:cover"
          onerror="this.src='/icons/account.svg'">
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h6 class="mb-0">${m.name}</h6>
              <small class="text-muted">${m.email}</small>
            </div>
            ${m.is_leader ? `<span class="badge bg-label-primary">${leaderLabel}</span>` : ''}
          </div>
        </div>
      </div>`;
    list.appendChild(li);
  });
});

// ── Send Message Modal ────────────────────────────────────────────────────
document.getElementById('sendMessageModal')?.addEventListener('show.bs.modal', function (e) {
  const btn = e.relatedTarget;
  document.getElementById('sendMessageModalLabel').textContent =
    '{{ $isEn ? 'Send Message to' : 'إرسال رسالة إلى' }} ' + btn.dataset.teamLeader;
  document.getElementById('messageSubject').value  = '';
  document.getElementById('messageContent').value  = '';
  document.getElementById('recipientId').value     = btn.dataset.teamId;
});

document.getElementById('sendMessageBtn')?.addEventListener('click', function () {
  const btn     = this;
  const subject = document.getElementById('messageSubject').value.trim();
  const message = document.getElementById('messageContent').value.trim();
  const receiver= document.getElementById('recipientId').value;

  if (!subject || !message) return;

  btn.disabled  = true;
  btn.textContent = '{{ $isEn ? 'Sending...' : 'جاري الإرسال...' }}';

  $.post('/dashboard/sendteammessage', { subject, message, receiver, _token: '{{ csrf_token() }}' })
    .done(r => {
      if (r.success) {
        releaseAlert('success', { message: '{{ $isEn ? 'Message sent successfully' : 'تم إرسال الرسالة بنجاح' }}' });
        bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
      } else {
        releaseErrorAlert({ message: r.message || '{{ $isEn ? 'Failed to send message' : 'فشل في إرسال الرسالة' }}' });
      }
    })
    .fail(() => releaseErrorAlert({ message: '{{ $isEn ? 'An error occurred while sending the message' : 'حدث خطأ أثناء إرسال الرسالة' }}' }))
    .always(() => { btn.disabled = false; btn.textContent = '{{ __('admin.Send_Message') }}'; });
});

// ── Charts ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const charts = [
    {
      id: 'registrationsChart',
      type: 'line',
      data: {
        labels: @json($regMonths),
        datasets: [{
          label: '{{ $isEn ? 'New Registrations Monthly' : 'المسجلون الجدد شهرياً' }}',
          data: @json($regCounts),
          borderColor: config.colors.primary,
          tension: 0.1,
          fill: false,
        }],
      },
      titleText: '{{ $isEn ? 'Registration Growth' : 'نمو التسجيلات' }}',
    },
    {
      id: 'submissionsPerTrackChart',
      type: 'doughnut',
      data: {
        labels: @json($data['submissionLabels']),
        datasets: [{
          data: @json($data['submissionCounts']),
          backgroundColor: [config.colors.primary, '#99e3ed', '#ffb7b9'],
        }],
      },
      titleText: '{{ $isEn ? 'Projects by Track' : 'المشاريع حسب المحور' }}',
    },
    {
      id: 'teamsStatusChart',
      type: 'pie',
      data: {
        labels: @json($pieLabels),
        datasets: [{
          label: '{{ $isEn ? 'Teams Status' : 'حالة الفرق' }}',
          data: @json($pieData),
          backgroundColor: ['#dfdfe3', config.colors.primary],
        }],
      },
      titleText: '{{ $isEn ? 'Team Formation Status' : 'حالة تكوين الفرق' }}',
    },
  ];

  charts.forEach(({ id, type, data, titleText }) => {
    const ctx = document.getElementById(id)?.getContext('2d');
    if (!ctx) return;
    new Chart(ctx, {
      type,
      data,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { title: { display: true, text: titleText } },
      },
    });
  });
});
</script>
@endsection