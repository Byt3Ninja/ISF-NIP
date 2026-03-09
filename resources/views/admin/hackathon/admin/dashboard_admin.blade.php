@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
@stop

@php
$idParam = request()->route('id');

$metrics = collect([
  ['icon' => 'users', 'color' => 'primary', 'title' => __('admin.Teams'), 'value' => $data['count_teams']],
  ['icon' => 'user-check', 'color' => 'success', 'title' => __('admin.Submitted_Projects'), 'value' => $data['Count_submitted_projects']],
  ['icon' => 'user-dollar', 'color' => 'info', 'title' => __('admin.Founders'), 'value' => $data['count_members']],
  ['icon' => 'user-screen', 'color' => 'secondary', 'title' => __('admin.mentorship_management'), 'value' => $data['CountNextSessions'], 'is_mentorship' => true],
])->map(function ($metric) {
  return [
    'icon' => $metric['icon'],
    'color' => $metric['color'],
    'title' => $metric['title'],
    'value' => $metric['value'],
    'is_mentorship' => $metric['is_mentorship'] ?? false,
  ];
})->toArray();

$chartIds = ['registrationsChart', 'submissionsPerTrackChart', 'teamsStatusChart'];

// Registration Chart Data
$registrationMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july'];
$registrationCounts = [65, 59, 80, 81, 156, 155, 30];

// Projects By Theme Chart
$submissionLabels = $data['submissionLabels'];
$submissionCounts = $data['submissionCounts'];
// Original Data
$completedTeamCount = $data['countInTeams'];
$uncompletedTeamCount = $data['countNotInTeams'];

$count_in_team = $l == 'en' ? 'In Team' : 'في فريق';
$count_not_in_team = $l == 'en' ? 'Not in Team' : 'ليس في فريق';
// --- New Data for Pie Chart ---
// These labels will be for the pie slices themselves.
$teamsStatusPieLabels = [$count_in_team, $count_not_in_team];
// Sum the counts to get a total for each status.
$teamsStatusPieData = [$completedTeamCount, $uncompletedTeamCount]; // Result: [43, 7]
@endphp

@section('content')
<div class="">
  <div class="">
    <div class="p-6 bg-card rounded shadow-sm mb-6">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <i class="ti ti-dashboard ti-28px"></i>
          <h4 class="mb-0">@lang('admin.Hackathon_Dashboard')</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a class="btn btn-primary" href="/dashboard/hackathon/dashboard/winners/{{ $idParam }}" role="button">
            <i class="ti ti-trophy me-2"></i> {{ __('admin.View') }} {{ $l == 'en' ? 'Results & winners' : 'النتائج والفائزون' }}
          </a>
          <button class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#manageMentorsModal">
            <i class="ti ti-user-cog me-2"></i> 
            {{ __('admin.manage_mentors') }}
          </button>
          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewMentorshipRequestsModal">
            <i class="ti ti-pointer-question me-2"></i>
             {{ __('admin.mentorship_requests') }}
            </button>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-6 mb-6">
      @foreach ($metrics as $metric)
        <div class="col-sm-6 col-xl-3">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between">
                <div class="content-left">
                  <span class="text-heading">{{ $metric['title'] }}</span>
                  @if (!empty($metric['is_mentorship']))
                    <h6 class="mt-4 mb-n1">{{ __('admin.new_requests') }}: <span class="badge badge-center bg-danger rounded-pill bg-glow ms-2">{{ $metric['value'] }}</span></h6>
                  @else
                    <h4 class="mt-2 me-2 mb-n1">{{ $metric['value'] ?? 0 }}</h4>
                  @endif
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-{{ $metric['color'] }}">
                    <i class="ti ti-{{ $metric['icon'] }} ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Charts Section (Content is dynamic) -->
    <div class="row mb-6 g-6">
      @foreach ($chartIds as $index => $chartId)
      <div class="{{ $index == 0 ? 'col-12' : 'col-12 col-md-6' }}">
        <div class="position-relative mx-auto bg-white p-3 rounded shadow-sm" style="height: 350px; width: 100%;">
          <canvas id="{{ $chartId }}"></canvas>
        </div>
      </div>
      @endforeach
    </div>

    <!-- Manage Teams Table -->
    <div class="card shadow-sm mb-4" id="manage-teams-section">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="bi bi-diagram-3-fill me-2"></i> {{ __('admin.manage_participating_teams') }}</h4>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="teamsExportBtn">
              <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="exportTable('teams-table', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
              <li><a class="dropdown-item" href="#" onclick="exportTable('teams-table', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{ $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table teams-table table-hover align-middle">
            <thead>
              <tr>
                <th>{{ __('admin.team_name') }}</th>
                <th>{{ __('admin.team_leader') }}</th>
                <th>{{ __('admin.Email') }}</th>
                <th>{{ __('admin.Phone') }}</th>
                <th>{{ __('admin.number_of_members') }}</th>
                <th>{{ __('admin.creation_date') }}</th>
                <th>{{ __('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['all_teams'] as $team)
              <tr>
                <td>{{ $team->name }}</td>
                <td>{{ $team->user_name }}</td>
                <td>{{ $team->email }}</td>
                <td>{{ $team->u_phone }}</td>
                <td>{{ $team->count_members }} / {{ $team->max_count_members }}</td>
                <td>{{ $team->created_at }}</td>
                <td>
                  <button class="btn btn-icon btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#teamDetailsModal"
                    data-team-id="{{ $team->id }}">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                  @php $leader_id = $team->members->where('is_leader', 1)->first()->id; @endphp
                  <button class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#sendMessageModal"
                    data-team-id="{{ $leader_id }}" data-team-leader="{{ $team->user_name }}">
                    <i class="bi bi-envelope-fill"></i>
                  </button>

                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="manage-users-section">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> {{ __('admin.manage_Projects') }}</h4>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="projectsExportBtn">
              <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="exportTable('projects-table', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
              <li><a class="dropdown-item" href="#" onclick="exportTable('projects-table', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{ $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table projects-table table-hover align-middle">
            <thead>
              <tr>
                <th>{{ __('admin.project_name') }}</th>
                <th>{{ __('admin.project_description') }}</th>
                <th>{{ __('admin.team_leader') }}</th>
                <th>{{ __('admin.Email') }}</th>
                <th>{{ __('admin.Phone') }}</th>
                <th>{{ __('admin.Status') }}</th>
                <th>{{ __('admin.Region') }}</th>
                <th>{{ __('admin.Progress') }}</th>
                <th>{{ __('admin.creation_date') }}</th>
                <th>{{ __('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
            
              <!-- This is example data, should be populated from a loop in a real application -->
              @foreach ($data['all_projects'] as $project)
              <tr>
                <td>
                  <div style="width: 120px;">
                    {{ $project->project_name }}
                  </div>
                </td>
                <td> 
                  <div class="text-truncate" style="max-width: 200px;">
                    {{ $project->project_description }}
                  </div>
                </td>
                <td>{{ $project->leader->name }}</td>
                <td>{{ $project->leader->email }}</td>
                <td>{{ $project->leader->phone }}</td>
                <td>

                  @if($project->project_status == 20)
                  {{ __('admin.Idea + Team') }} 
                  {{-- : {{ __('admin.Define the problem and form a team with clear roles.') }} --}}
                  @elseif($project->project_status == 40)
                  {{ __('admin.Research') }} 
                  {{-- : {{ __('admin.Validate the idea, study users, and outline key features.') }} --}}
                  @elseif($project->project_status == 60)
                  {{ __('admin.Design & Build') }} 
                  {{-- : {{ __('admin.Sketch UI, set up the project, and develop core functionality.') }} --}}
                  @elseif($project->project_status == 80)
                  {{ __('admin.Ready for Pitch') }} 
                  {{-- : {{ __('admin.Prepare demo, finalize slides, and rehearse the presentation.') }} --}}
                  @elseif($project->project_status == 100)
                  {{ __('admin.Project Submitted') }} 
                  {{-- : {{ __('admin.Submit code, share the demo, and complete documentation story.') }} --}}
                  @endif
                </td>
                <td>{{$project->region ? __('admin.'.$project->region) : '' }}</td>
                <td>
                  <div class="progress mb-2" style="height: 22px; background-color: #f1f3f4; border-radius: 12px;">
                    <div 
                      class="progress-bar 
                        @if($project->project_status == 100)
                          bg-success
                        @elseif($project->project_status >= 80)
                          bg-primary
                        @elseif($project->project_status >= 60)
                          bg-info
                        @elseif($project->project_status >= 40)
                          bg-warning
                        @else
                          bg-danger
                        @endif
                      " 
                      role="progressbar"
                      style="width:{{ $project->project_status }}%; font-weight: 600; font-size: 1rem; border-radius: 12px;"
                      aria-valuenow="{{ $project->project_status }}"
                      aria-valuemin="0" aria-valuemax="100"
                    >
                      <span style="color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">
                        {{ $project->project_status }}%
                        {{-- {{ $l == 'en' ? 'Complete' : 'مكتمل' }} --}}
                      </span>
                    </div>
                  </div>
                </td>
                <td>{{ $project->created_at }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <button class="btn btn-sm btn-icon rounded-circle btn-label-info me-1" data-bs-toggle="modal"
                      data-bs-target="#projectDetailsModal{{ $project->id }}"
                      title="{{ $l == 'en' ? 'View Details' : 'عرض التفاصيل' }}">
                      <i class="ti ti-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-icon rounded-circle btn-label-primary" data-bs-toggle="modal"
                      data-bs-target="#editProjectIdeaModal{{ $project->id }}"
                      title="{{ $l == 'en' ? 'Edit Project' : 'تعديل المشروع' }}">
                      <i class="ti ti-edit ti-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Edit Project Idea Modal -->
              <div class="modal fade" id="editProjectIdeaModal{{ $project->id }}" tabindex="-1"
                aria-labelledby="editProjectIdeaModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editProjectIdeaModalLabel">{{ $l == 'en' ? 'Edit Project Idea' : 'تعديل
                        فكرة
                        المشروع' }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <form method="POST" action="/dashboard/hackathon/EditProjectIdea">
                        @csrf
                        <input type="hidden" name="team_formation_id" value="{{ $project->id }}">
                        <div class="mb-3">
                          <label for="Project_Name_en" class="form-label">
                            {{ __('admin.Project_Name') }}
                          </label>
                          <input type="text" class="form-control" name="Project_Name_en"
                            value="{{ $project->project_name }}" id="Project_Name_en" required>
                        </div>

                        <div class="mb-3">
                          <label for="Project_Description_en" class="form-label">
                            {{ __('admin.Project_Description') }}
                          </label>
                          <textarea class="form-control" name="Project_Description_en" id="Project_Description_en"
                            rows="2">{{ $project->project_description }}</textarea>
                        </div>

                        <div class="mb-3">
                          <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Project Status' : 'حالة المشروع'
                            }}</label>
                          <select class="form-select" id="Project_Status" name="Project_Status">
                            <option value="">{{ $l == 'en' ? 'Choose v...' : 'اختر الحالة...' }}</option>
                            <option value="20" {{ $project->project_status == '20' ? 'selected' : '' }} >{{
                              __('admin.Idea + Team') }}</option>
                            <option value="40" {{ $project->project_status == '40' ? 'selected' : '' }} >{{
                              __('admin.Research') }}</option>
                            <option value="60" {{ $project->project_status == '60' ? 'selected' : '' }} >{{
                              __('admin.Design & Build') }}</option>
                            <option value="80" {{ $project->project_status == '80' ? 'selected' : '' }} >{{
                              __('admin.Ready for Pitch') }}</option>
                            <option value="100" {{ $project->project_status == '100' ? 'selected' : '' }} >{{
                              __('admin.Project Submitted') }}</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label for="Region" class="form-label">{{ $l == 'en' ? 'Region' : 'المنطقة' }}</label>
                          <select class="form-select" id="Region" name="Region">
                            <option value="">{{ $l == 'en' ? 'Choose Region...' : 'اختر المنطقة...' }}</option>
                            <option value="AL_Madinah_AL_Munawwarah" {{ $project->region =='AL_Madinah_AL_Munawwarah' ? 'selected' : '' }}>
                              {{ __('admin.AL_Madinah_AL_Munawwarah') }}
                            </option>
                            <option value="Al_Ahsa" {{ $project->region == 'Al_Ahsa' ? 'selected' : '' }}>
                              {{ __('admin.Al_Ahsa') }}
                            </option>
                            <option value="Asir" {{ $project->region == 'Asir' ? 'selected' : '' }}>
                              {{ __('admin.Asir') }}
                            </option>
                          
                          </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
                      </button>
                      <button type="submit" class="btn btn-primary">
                        {{ $l == 'en' ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}
                      </button>
                    </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Project Details Modal -->
              <div class="modal fade" id="projectDetailsModal{{ $project->id }}" tabindex="-1"
                aria-labelledby="projectDetailsModalLabel{{ $project->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="projectDetailsModalLabel{{ $project->id }}">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ $l == 'en' ? 'Project Details' : 'تفاصيل المشروع' }}
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Project Name' : 'اسم المشروع' }}</label>
                            <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->project_name }}</p>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Team Leader' : 'قائد الفريق' }}</label>
                            <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->leader->name }}</p>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Email' : 'البريد الإلكتروني' }}</label>
                            <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->leader->email }}</p>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Phone' : 'رقم الهاتف' }}</label>
                            <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->leader->phone }}</p>
                          </div>
                        </div>
                        
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Region' : 'المنطقة' }}</label>
                            <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->region ? __('admin.'.$project->region) : '' }}</p>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Creation Date' : 'تاريخ الإنشاء' }}</label>
                            <p class="form-control-plaintext border p-2 rounded bg-light">{{ $project->created_at }}</p>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Project Status' : 'حالة المشروع' }}</label>
                            <div class="border p-2 rounded bg-light">
                              @if($project->project_status == 20)
                                <span class="badge bg-info">{{ __('admin.Idea + Team') }}</span>
                              @elseif($project->project_status == 40)
                                <span class="badge bg-warning">{{ __('admin.Research') }}</span>
                              @elseif($project->project_status == 60)
                                <span class="badge bg-primary">{{ __('admin.Design & Build') }}</span>
                              @elseif($project->project_status == 80)
                                <span class="badge bg-secondary">{{ __('admin.Ready for Pitch') }}</span>
                              @elseif($project->project_status == 100)
                                <span class="badge bg-success">{{ __('admin.Project Submitted') }}</span>
                              @endif
                            </div>
                          </div>
                          
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Progress' : 'التقدم' }}</label>
                            <div class="progress" style="height: 28px; background-color: #f1f3f4; border-radius: 14px;">
                              <div 
                                class="progress-bar 
                                  @if($project->project_status == 100)
                                    bg-success
                                  @elseif($project->project_status >= 80)
                                    bg-primary
                                  @elseif($project->project_status >= 60)
                                    bg-info
                                  @elseif($project->project_status >= 40)
                                    bg-warning
                                  @else
                                    bg-danger
                                  @endif
                                " 
                                role="progressbar"
                                style="width:{{ $project->project_status }}%; font-weight: 600; font-size: 1.1rem; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);"
                                aria-valuenow="{{ $project->project_status }}"
                                aria-valuemin="0" aria-valuemax="100"
                              >
                                <span style="color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.18); letter-spacing: 0.5px;">
                                  {{ $project->project_status }}% {{ $l == 'en' ? 'Complete' : 'مكتمل' }}
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-12">
                          <div class="mb-3">
                            <label class="form-label fw-bold">{{ $l == 'en' ? 'Project Description' : 'وصف المشروع' }}</label>
                            <div class="border p-3 rounded bg-light" style="min-height: 100px;">
                              {{ $project->project_description ?: ($l == 'en' ? 'No description provided' : 'لا يوجد وصف متاح') }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ $l == 'en' ? 'Close' : 'إغلاق' }}
                      </button>
                      <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal"
                        data-bs-target="#editProjectIdeaModal{{ $project->id }}">
                        <i class="ti ti-edit me-1"></i> {{ $l == 'en' ? 'Edit Project' : 'تعديل المشروع' }}
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
    </div>

    <div class="card shadow-sm mb-4" id="submitted-project-section">
      <div class="card-header">
        @php
          $total = $data['usersWithFormDataCount'] + $data['usersWithoutFormDataCount'];
          $percent = ($total > 0) ? ($data['usersWithFormDataCount'] / $total) * 100 : 0;
          if ($percent < 25) {
              $color = 'bg-danger';
          } elseif ($percent < 50) {
              $color = 'bg-warning';
          } elseif ($percent < 75) {
              $color = 'bg-info'; 
          } else {
              $color = 'bg-success';
          }
        @endphp
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> {{ __('admin.Submitted_Projects') }}
              @if(count($data['submitted_projects']) > 0)
              <div class="progress mt-2" style="height: 12px;">
                <div class="progress-bar {{ $color }}" role="progressbar"
                    style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}"
                    aria-valuemin="0" aria-valuemax="100">
                    {{ round($percent, 2) }}%
                </div>
              </div> 
              @endif
            </h4>
          </div>
          <div class="btn-group ms-3">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="submittedProjectsExportBtn">
              <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="exportTable('submitted-projects-table', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
              <li><a class="dropdown-item" href="#" onclick="exportTable('submitted-projects-table', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{ $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body">
        <table class="table submitted-projects-table table-hover align-middle">
          <thead>
            <tr>
              <th scope="col">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Submitted On' : 'تم التقديم في' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Project Name' : ' اسم المشروع ' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Date of Submission' : 'تاريخ التقديم' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Team Leader' : 'قائد الفريق' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Email' : 'البريد الإلكتروني' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Phone' : ' رقم الهاتف' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Region' : ' المنطقة' }}</th>
              <th scope="col">{{ $l == 'en' ? 'View Details' : 'عرض التفاصيل' }}</th>
            </tr>
          </thead>
          <tbody>
            <!-- This is example data, should be populated from a loop in a real application -->
            @foreach ($data['submitted_projects'] as $project)
              <tr>
                <td>{{ $project->team_name }}</td>
                <td>{{ $project->step_name }}</td>
                <td>{{ $project->project_name }}</td>
                <td>{{ $project->submissionDate }}</td>
                <td>{{ $project->team_leader }}</td>
                <td>{{ $project->eamil }}</td>
                <td>{{ $project->phone }}</td>
                <td>{{$project->region ? __('admin.'.$project->region) : '' }}</td>
                <td>
                  <a target="_blank" title="view" href="/dashboard/hackathon/viewSubmission/{{ $project->step_id }}/{{ $project->user_id }}" class="btn btn-label-primary btn-sm">
                    {{ $l == 'en' ? 'View' : 'عرض' }}
                  </a>
                </td>
            </tr>
           
            @endforeach
          </tbody>
        </table>

      </div>
    </div>


     <div class="card shadow-sm mb-4" id="submitted-project-section">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> {{ __('admin.Projects_NotSubmitted') }}
             
            </h4>
          </div>
          <div class="btn-group ms-3">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="submittedProjectsExportBtn">
              <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" onclick="exportTable('submitted-projects-table', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
              <li><a class="dropdown-item" href="#" onclick="exportTable('submitted-projects-table', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{ $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body">
        <table class="table submitted-projects-table table-hover align-middle">
          <thead>
            <tr>
              <th scope="col">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Not Submitted On' : 'لم يتم التقديم في' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Project Name' : ' اسم المشروع ' }}</th>
              {{-- <th scope="col">{{ $l == 'en' ? 'Date of Submission' : 'تاريخ التقديم' }}</th> --}}
              <th scope="col">{{ $l == 'en' ? 'Team Leader' : 'قائد الفريق' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Email' : 'البريد الإلكتروني' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Phone' : ' رقم الهاتف' }}</th>
              <th scope="col">{{ $l == 'en' ? 'Region' : ' المنطقة' }}</th>
              {{-- <th scope="col">{{ $l == 'en' ? 'View Details' : 'عرض التفاصيل' }}</th> --}}
            </tr>
          </thead>
          <tbody>
            <!-- This is example data, should be populated from a loop in a real application -->
            @foreach ($data['not_submitted_projects'] as $notproject)
              <tr>
                <td>{{ $notproject->team_name }}</td>
                <td>{{ $notproject->company_step }}</td>
                <td>{{ $notproject->project_name }}</td>
                {{-- <td>{{ $notproject->submissionDate }}</td> --}}
                <td>{{ $notproject->team_leader }}</td>
                <td>{{ $notproject->email }}</td>
                <td>{{ $notproject->phone }}</td>
                <td>{{$notproject->region ? __('admin.'.$notproject->region) : '' }}</td>
                {{-- <td>
                  <a target="_blank" title="view" href="/dashboard/hackathon/viewSubmission/{{ $project->step_id }}/{{ $project->user_id }}" class="btn btn-label-primary btn-sm">
                    {{ $l == 'en' ? 'View' : 'عرض' }}
                  </a>
                </td> --}}
            </tr>
           
            @endforeach
          </tbody>
        </table>

      </div>
    </div>

  </div>
</div>

@php
$request = new \Illuminate\Http\Request([
'cycleIdHackathon' => $idParam
]);
$mentorsLists = showMintors($request);
@endphp
<!-- Manage Mentors Modal -->
<div class="modal fade" id="manageMentorsModal" tabindex="-1" aria-labelledby="manageMentorsModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manageMentorsModalLabel">{{ __('admin.manage_mentors') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.close') }}"></button>
      </div>
      <div class="modal-body">
        <a href="/dashboard/mentorship/assign/{{ $idParam }}" class="btn btn-success mb-3"><i
            class="bi bi-person-plus-fill me-2"></i> {{
          __('admin.Mentors') }}</a>
        @if($mentorsLists != null)
        @foreach ($mentorsLists as $key => $mentor)
        <div class="card mb-3">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">

                    <img src="{{ asset( $mentor->image_url ) }}" alt="Mentor Profile" class="rounded-circle"
                      style="width: 50px; height: 50px; object-fit: cover;">
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h5 class="card-title mb-0">{{ $mentor->name }}</h5>
                    <p class="card-text small text-muted mb-0">{{ $mentor->email }}</p>
                  </div>
                </div>
              </div>
              {{-- <div class="col-md-6">
                <div class="d-flex justify-content-end">
                  <button class="btn btn-primary me-2"><i class="bi bi-pencil-square me-1"></i> {{
                    __('admin.edit') }}</button>
                  <button class="btn btn-danger"><i class="bi bi-trash me-1"></i> {{ __('admin.delete') }}</button>
                </div>
              </div> --}}
            </div>
          </div>
        </div>
        @endforeach
        @else
        <p>{{ __('admin.current_mentors_table_placeholder') }}</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
      </div>
    </div>
  </div>
</div>

<!-- View Mentorship Requests Modal -->
<div class="modal fade" id="viewMentorshipRequestsModal" tabindex="-1"
  aria-labelledby="viewMentorshipRequestsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewMentorshipRequestsModalLabel">{{ __('admin.mentorship_session_requests')
          }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.close') }}"></button>
      </div>
      <div class="modal-body">


        <div class="col-12">
          <section>
            <div class="row">
              <div class="col-md-12">
                <div class="mt-10">
                  <div class="border-bottom pb-2 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center">
                        <i class='ti ti-calendar-event me-2'></i>
                        <h5 class="card-title mb-0">
                          {{ __("dashboard.UpcomingMentorsSession") }}

                        </h5>
                      </div>
                      <a class="btn btn-sm btn-primary" href="/dashboard/mentorship/mine">
                        <i class="ti ti-eye me-2 ti-16px"></i>
                        {{__('cycles.showAll')}}
                      </a>
                    </div>
                  </div>
                  <div class="">
                    <div class="table-responsive">
                      <table class="table full-featured-table mb-0 sessonsTable">
                        <thead class="bg-label-light">
                          <tr>
                            <th>{{__('cycles.User/Title')}}</th>
                            <th>{{__('cycles.Mentor')}}</th>
                            @if ($_user->hasRole('Admin'))<th>{{__('cycles.Rate/Comment')}}</th>@endif
                            <th>{{__('cycles.Date/Time')}}</th>
                            <th>{{__('cycles.Approved')}}</th>
                            <th>{{__('cycles.Done')}}</th>
                            <th>{{__('cycles.Actions')}}</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($nextSessions as $session)
                          <tr>
                            <td>
                              <div class="d-flex align-items-center gap-2">
                                <a class="media-left" href="/dashboard/users/{{ $session->user_id }}/edit">
                                  <img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle me-3"
                                    height="40" width="40">
                                </a>
                                <a href="/dashboard/users/{{ $session->user_id }}/edit">
                                  <div class="media-body">
                                    <h6 class="media-heading mb-0 text-nowrap">{{ $session->user->name }}</h6>
                                    <span class="font-small-2 text-nowrap">{{$session->user->title }}</span>
                                  </div>
                                </a>
                              </div>
                            </td>
                            <td>
                              <div style="cursor: pointer;" class="d-flex align-items-center"
                                onclick="mentor({{ $session->mentor_id }}, '{{ $session->mentor->name }}', '{{ $session->mentor->ratings }}')">
                                <div class="avatar">
                                  <img class="img-fluid rounded-circle me-3"
                                    src="{{$session->mentor->image_url ?? '/icons/account.svg'}}" alt="image"
                                    height="20" width="20" />
                                </div>
                                <span class="chip-text text-nowrap"> {{$session->mentor->name}} </span>
                              </div>
                            </td>
                            @if ($_user->hasRole('Admin'))
                            <td> <small> {!! $session->rate_comment ? str_repeat('<i class="bx bxs-star"
                                  style="color: #fc960f;"></i>', $session->rating) . '<br>' . $session->rate_comment
                                :
                                $session->comment !!} </small> </td>
                            @endif
                            <td> {{ $session->zoom_date }} <br> ({{ $session->period }}M) </td>
                            <td>
                              <div class="d-flex align-items-center gap-1"> {!! __( $session->approved ) ??'<span
                                  class="warning">'.__('Waiting').'</span>' !!} </div>
                            </td>
                            <td> {{ $session->done ?__('Yes') : __('No') }} </td>
                            <td>
                              <a class="btn btn-sm btn-label-primary" href="{{ $session->join_url }}" target="_blank">
                                <i class="ti ti-brand-zoom me-2"></i>
                                {{ __('dashboard.Join now') }}
                              </a>
                              @if (!$session->approved)
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-success"
                                onclick="approve({{ $session->id }}, 'Yes')" title="Approve">
                                <i class="ti ti-check"></i>
                              </button>
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-danger"
                                onclick="approve({{ $session->id }}, 'No')" title="Disapprove">
                                <i class="ti ti-x"></i>
                              </button>
                              @endif
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>

        <p>{{ __('admin.team_mentorship_requests_table_placeholder') }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
      </div>
    </div>
  </div>
</div>

<!-- Team Details Modal -->
<div class="modal fade" id="teamDetailsModal" tabindex="-1" aria-labelledby="teamDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="teamDetailsModalLabel">{{ __('admin.team_details_title') }} <span
            id="modal-team-name-detail"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.close') }}"></button>
      </div>
      <div class="modal-body">
        <div class="mb-6">
          <label class="form-label fw-bold me-2">{{ __('admin.Team Name') }}:</label>
          <span id="modal-team-hackathon" class="text-dark"></span>
        </div>
        <div class="mb-6">
          <label class="form-label fw-bold me-2">{{ __('admin.team_leader') }}:</label>
          <span id="modal-team-leader" class="text-dark"></span>
        </div>
        <div class="mb-6">
          <label class="form-label fw-bold me-2">{{ __('admin.members') }}</label>
          <ul class="list-group list-group-flush" id="modal-team-members-list"></ul>
        </div>
        <div class="mb-6">
          <label class="form-label fw-bold me-2">{{ __('admin.project_idea_if_available') }}</label>
          <div id="modal-team-project-idea" class="border rounded bg-light p-2 text-dark">
        {{ __('admin.idea_not_specified_yet') }}
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
        {{-- <button type="button" class="btn btn-primary"><i class="bi bi-envelope-fill me-2"></i> {{
          __('admin.message_team') }}</button> --}}
      </div>
    </div>
  </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendMessageModalLabel">
          {{ $l == 'en' ? 'Send message to team leader' : 'إرسال رسالة إلى قائد الفريق' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="sendMessageForm">
          <div class="mb-3">
            <label for="messageSubject" class="form-label">{{ $l == 'en' ? 'Subject' : 'الموضوع' }}</label>
            <input type="text" class="form-control" id="messageSubject" name="subject" placeholder="{{ $l == 'en' ? 'Enter subject' : 'أدخل الموضوع' }}" required>
          </div>
          <div class="mb-3">
            <label for="messageContent" class="form-label">{{ $l == 'en' ? 'Message' : 'الرسالة' }}</label>
            <textarea class="form-control" id="messageContent" name="message" rows="5" placeholder="{{ $l == 'en' ? 'Enter your message' : 'أدخل رسالتك' }}" required></textarea>
          </div>
          <input type="hidden" id="recipientId" name="recipient_id">

          <div class="d-flex justify-content-end items-center gap-3 mt-6">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
            <button type="submit" class="btn btn-primary w-px-150">{{ __('admin.Send_Message') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@php
$teamsJson = $data['all_teams']->map(function($team) {
  return [
    'id' => $team->id,
    'name' => $team->name,
    'hackathon' => $team->name,
    'leader' => $team->user_name,
    'members' => collect($team->members ?? [])->filter(function($m) {
      return !empty($m->name);
    })->map(function($m) {
      return [
      'id' => $m->id ?? null,
      'name' => $m->name ?? 'Unknown',
      'email' => $m->email ?? '',
      'title' => $m->title ?? '',
      'img_url' => $m->img_url ?  '/191014/' . $m->img_url : '',
      'is_leader' => $m->is_leader ?? false,
      'status' => $m->status ?? 'Unknown'
      ];
    })->all(),
    'projectIdea' => $team->project_name_en ?? __('admin.idea_not_specified_yet'),
  ];
})->keyBy('id')->toJson(JSON_UNESCAPED_UNICODE);
@endphp
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const getTableConfig = () => {
    return {
      paging: true,
      searching: true,
      ordering: true,
      order: [[0, 'desc']],
      dom: 'frtip', // Removed 'B' to hide default buttons
      language: {
        @if($l == 'ar')
        "search": "{{ __('admin.Search') }}:",
        "lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
        "info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
        "infoEmpty": "{{ __('admin.no_entries_available') }}",
        "infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
        "paginate": {
          "first": "{{ __('admin.First') }}",
          "last": "{{ __('admin.Last') }}",
          "next": "{{ __('admin.Next') }}",
          "previous": "{{ __('admin.Previous') }}"
        },
        "emptyTable": "{{ __('admin.No_data_available_in_table') }}",
        "zeroRecords": "{{ __('admin.No_matching_records_found') }}"
        @endif
      },
    };
  }

  // Store DataTable instances for export functionality
  let tableInstances = {};

  // Custom export function
  function exportTable(tableClass, format) {
    const table = $('.' + tableClass).DataTable();
    if (format === 'csv') {
      table.button('.buttons-csv').trigger();
    } else if (format === 'excel') {
      table.button('.buttons-excel').trigger();
    }
  }

  if (window.jQuery && $.fn.DataTable) {
    // Initialize each table with export capabilities
    tableInstances.teams = $('.teams-table').DataTable(Object.assign({}, getTableConfig(), {
      buttons: ['csv', 'excel']
    }));
    
    tableInstances.projects = $('.projects-table').DataTable(Object.assign({}, getTableConfig(), {
      buttons: ['csv', 'excel']
    }));
    
    tableInstances.submittedProjects = $('.submitted-projects-table').DataTable(Object.assign({}, getTableConfig(), {
      buttons: ['csv', 'excel']
    }));

    // Update export function to work with specific table instances
    window.exportTable = function(tableClass, format) {
      let tableInstance;
      if (tableClass === 'teams-table') tableInstance = tableInstances.teams;
      else if (tableClass === 'projects-table') tableInstance = tableInstances.projects;
      else if (tableClass === 'submitted-projects-table') tableInstance = tableInstances.submittedProjects;
      
      if (tableInstance) {
        if (format === 'csv') {
          tableInstance.button('.buttons-csv').trigger();
        } else if (format === 'excel') {
          tableInstance.button('.buttons-excel').trigger();
        }
      }
    };
  }
</script>
@stop

@section('pagescripts')
<script>
  const teamsData = {!! $teamsJson !!}; 
</script>
<script>
 
  function approve(id, stat) {
      if(stat == 'Yes'){
      alert('{{ __('admin.Accepting_request') }}')
      }else{
        alert('{{ __('admin.Rejecting_request') }}')
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id,
          "stat": stat,
          'rtype': 6,
        },
        success: function(response) {
          location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    const teamDetailsModalEl = document.getElementById('teamDetailsModal');
    if (teamDetailsModalEl) {
        teamDetailsModalEl.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const teamId = button.getAttribute('data-team-id');
            const teamData = teamsData[teamId];
            if (teamData) {
                document.getElementById('modal-team-name-detail').textContent = teamData.name;
                document.getElementById('modal-team-hackathon').textContent = teamData.hackathon;
                document.getElementById('modal-team-leader').textContent = teamData.leader;
                const membersList = document.getElementById('modal-team-members-list');
                membersList.innerHTML = '';
                const teamMembers = teamData?.members?.filter(member => member !== null && member !== undefined) || [];
                
                if (teamMembers.length === 0) {
                    const li = document.createElement('li');
                    li.classList.add('list-group-item', 'text-muted');
                    li.textContent = "{{ __('admin.no_members_found') }}";
                    membersList.appendChild(li);
                } else {
                    teamMembers.forEach(member => {
                        const li = document.createElement('li');
                        li.classList.add('list-group-item', 'd-flex', 'align-items-center');
                        
                        // Create member info HTML
                        const memberHtml = `
                            <div class="d-flex align-items-center w-100">
                                <div class="avatar me-3">
                                    <img src="${member.img_url ?? '/icons/account.svg'}" 
                                        alt="avatar" 
                                        class="rounded-circle" 
                                        style="width: 40px; height: 40px; object-fit: cover;"
                                        onerror="this.onerror=null; this.src='/icons/account.svg';"
                                    >
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-0">${member.name}</h6>
                                            <small class="text-muted">${member.email}</small>
                                            <!-- ${member.title ? `<br><small class="badge badge-outline-secondary">${member.title}</small>` : ''} -->
                                        </div>
                                        <div class="text-end">
                                            ${member.is_leader ? '<span class="badge bg-label-primary">{{ __("admin.team_leader") }}</span>' : ''}
                                            <!-- <span class="badge ${member.status === 'Accepted' ? 'bg-success' : member.status === 'Pending' ? 'bg-warning' : 'bg-secondary'}">${member.status}</span> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        li.innerHTML = memberHtml;
                        membersList.appendChild(li);
                    });
                }
                document.getElementById('modal-team-project-idea').textContent = teamData.projectIdea || "{{ __('admin.idea_not_specified_yet') }}";
            } else {
                // حال وجود خطأ في ID
                console.warn('لا توجد بيانات للفريق بالمعرّف:', teamId);
            }
        });
    }

   document.addEventListener('DOMContentLoaded', function () {
    const chartCanvases = [
        'registrationsChart',
        'submissionsPerTrackChart',
        'teamsStatusChart'
    ];

    chartCanvases.forEach((canvasId) => {
        const ctx = document.getElementById(canvasId)?.getContext('2d');

        if (ctx) {
            if (canvasId === 'registrationsChart') {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($registrationMonths),
                        datasets: [{
                            label: '{{ $l == 'ar' ? 'المسجلون الجدد شهرياً' : 'New Registrations Monthly' }}',
                            data: @json($registrationCounts),
                            borderColor: '#7367f0',
                            tension: 0.1,
                            fill: false,
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '{{ $l == 'ar' ? 'نمو التسجيلات' : 'Registration Growth' }}'
                            },
                        },
                    },
                });
            }
            if (canvasId === 'submissionsPerTrackChart') {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($submissionLabels),
                        datasets: [{
                            data: @json($submissionCounts),
                            backgroundColor: ['#7367f0', '#99e3ed', '#ffb7b9'],
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '{{ $l == 'ar' ? 'المشاريع حسب المحور' : 'Projects by Track' }}'
                            },
                        },
                    },
                });
            }
            if (canvasId === 'teamsStatusChart') {
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        // Assuming $teamsStatusPieLabels and $teamsStatusPieData are available and correctly formatted
                        labels: @json($teamsStatusPieLabels), 
                        datasets: [{
                            label: '{{ $l == 'ar' ? 'حالة الفرق' : 'Teams Status' }}',
                            data: @json($teamsStatusPieData),
                            backgroundColor: [
                                '#28c76f',
                                '#ff9f43',
                            ],
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '{{ $l == 'ar' ? 'حالة تكوين الفرق' : 'Team Formation Status' }}'
                            },
                        },
                    },
                });
            }
        }
    });
});


  // Send Message Modal Logic
  $(document).ready(function() {
    $('#sendMessageModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var teamLeader = button.data('team-leader'); // Extract info from data-* attributes
      var recipientId = button.data('team-id');

      var modal = $(this);
      modal.find('.modal-title').text('{{ $l == 'ar' ? 'إرسال رسالة إلى' : 'Send Message to' }} ' + teamLeader);

      // Clear previous message/subject if needed
      modal.find('#messageSubject').val('');
      modal.find('#messageContent').val('');
      modal.find('#recipientId').val(recipientId); // Set recipient ID in hidden input
    });

    // Handle send message form submission
    $('#sendMessageForm').on('submit', function(event) {
      event.preventDefault(); // Prevent default form submission

      var form = $(this);
      var formSubmitButton = form.find('button[type="submit"]');

      var originalButtonText = formSubmitButton.text(); // Store original text
      var subject = form.find('#messageSubject').val();
      var message = form.find('#messageContent').val();
      var recipientId = form.find('#recipientId').val();

      // Set pending state
      formSubmitButton.prop('disabled', true);
      formSubmitButton.text('{{ $l == 'en' ? 'Sending...' : 'جاري الإرسال...' }}');

      $.ajax({
        url: '/dashboard/sendteammessage',
        type: 'POST',
        data: {
          subject: subject,
          message: message,
          receiver: recipientId,
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          console.log('response:', response);
          if (response.success) {
            // Global success alert function
            releaseSuccessAlert({message: '{{ $l == 'en' ? 'Message sent successfully' : 'تم إرسال الرسالة بنجاح' }}'});
            $('#sendMessageModal').modal('hide'); // Hide the modal on success
          } else {
            // Global error alert function
            releaseErrorAlert({message: response.message || '{{ $l == 'en' ? 'Failed to send message' : 'فشل في إرسال الرسالة' }}'});
          }
        },
        error: function(xhr, status, error) {
          releaseErrorAlert({message: '{{ $l == 'en' ? 'An error occurred while sending the message' : 'حدث خطأ أثناء إرسال الرسالة' }}'});
        },
        complete: function() {
          // This function runs regardless of success or error
          formSubmitButton.prop('disabled', false); // Re-enable the button
          formSubmitButton.text(originalButtonText); // Restore original text
        }
      });
    });
  });
</script>
@stop