@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
<style>
  .stat-card {
    text-align: center;
  }

  .stat-card .display-4 {
    font-weight: bold;
  }

  .chart-container {
    position: relative;
    margin: auto;
    height: 350px;
    width: 100%;
    background-color: #fff;
    padding: 1rem;
    border-radius: .25rem;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
  }

  .tags-input-wrapper {
    background: white;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
  }

  .tag-item {
    background-color: var(--bs-primary);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    margin: 0.25rem;
    display: flex;
    align-items: center;
    font-size: 0.9em;
  }

  .tag-item .remove-tag {
    background: none;
    border: none;
    color: white;
    margin-left: 0.5rem;
    cursor: pointer;
    font-size: 1.1em;
    line-height: 1;
  }

  .tags-input-wrapper input[type="text"] {
    border: none;
    outline: none;
    flex-grow: 1;
    padding: 0.25rem;
    min-width: 150px;
  }

  .dynamic-entry {
    border: 1px dashed #ccc;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: .25rem;
    position: relative;
    background-color: #fff;
    /* Explicit white background */
  }

  .total-weight {
    font-weight: bold;
    font-size: 1.1em;
    margin-top: 0.5rem;
  }
</style>
@stop

@php
// Registration Chart Data
$registrationMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july'];
$registrationCounts = [65, 59, 80, 81, 156, 155, 30];

// Projects By Theme Chart
$submissionLabels = ['التقنية المالية', 'الذكاء الاصطناعي', 'الاستدامة'];
$submissionCounts = [120, 90, 75];
// Original Data
$completedTeamCount = [25, 18];
$uncompletedTeamCount = [5, 2];

// --- New Data for Pie Chart ---
// These labels will be for the pie slices themselves.
$teamsStatusPieLabels = ['فرق مكتملة', 'فرق غير مكتملة'];
// Sum the counts to get a total for each status.
$teamsStatusPieData = [array_sum($completedTeamCount), array_sum($uncompletedTeamCount)]; // Result: [43, 7]

// Program Engagement Chart
$engagementLabels = ['المسجلون', 'كونوا فرقاً', 'قدموا مشاريع', 'فائزون'];
$engagementCounts = [200, 150, 120, 5];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
      <h1 class="fw-bold">{{ __('admin.management_dashboard') }}</h1>
      @php
      $idParam = request()->route('id');
      @endphp
      <a class="btn btn-primary btn-lg" href="/dashboard/hackathon/dashboard/winners/{{ $idParam }}" role="button">
        <i class="bi-trophy-fill me-2"></i> {{ $l == 'en' ? 'Results & winners' : 'النتائج والفائزون' }}
      </a>
      {{-- <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createHackathonModal">
        <i class="bi bi-plus-circle-fill me-2"></i> {{ __('admin.create_new_hackathon') }}
      </button> --}}
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4 g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4">
      <div class="col">
        <div class="card stat-card border-primary shadow-sm h-100">
          <div class="card-body">
            <i class="bi bi-calendar3-event display-4 text-primary mb-2"></i>
            <h5 class="card-title">{{ __('admin.Teams') }}</h5>
            <p class="card-text display-4">{{ $data['count_teams'] }}</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card stat-card border-success shadow-sm h-100">
          <div class="card-body">
            <i class="bi bi-person-check-fill display-4 text-success mb-2"></i>
            <h5 class="card-title">{{ __('admin.Projects') }}</h5>
            <p class="card-text display-4">{{ $data['count_projects'] }}</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card stat-card border-info shadow-sm h-100">
          <div class="card-body">
            <i class="bi bi-people-fill display-4 text-info mb-2"></i>
            <h5 class="card-title">{{ __('admin.Registered') }}</h5>
            <p class="card-text display-4">{{ $data['count_members'] }}</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card stat-card border-secondary shadow-sm h-100">
          <div class="card-body d-flex flex-column">
            <i class="bi bi-person-video3 display-4 text-secondary mb-2"></i>
            <h5 class="card-title">{{ __('admin.mentorship_management') }}</h5>
            <p class="mb-2">{{ __('admin.new_requests') }}: <span class="badge bg-danger rounded-pill">{{
                $data['CountNextSessions'] }}</span>
            </p>
            <div class="mt-auto">
              <button class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal"
                data-bs-target="#manageMentorsModal"><i class="bi bi-person-badge-fill me-1"></i> {{
                __('admin.manage_mentors') }}</button>
              <button class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal"
                data-bs-target="#viewMentorshipRequestsModal"><i class="bi bi-card-list me-1"></i> {{
                __('admin.mentorship_requests') }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section (Content is dynamic) -->
    <div class="row mb-4 g-3">
      <div class="col-md-12">
        <div class="chart-container"><canvas id="registrationsChart"></canvas></div>
      </div>
      <div class="col-md-6">
        <div class="chart-container"><canvas id="submissionsPerTrackChart"></canvas></div>
      </div>
      <div class="col-md-6">
        <div class="chart-container"><canvas id="teamsStatusChart"></canvas></div>
      </div>
    </div>
    {{-- <div class="row mb-4 g-3">
      <div class="col-md-6">
        <div class="chart-container"><canvas id="teamsStatusChart"></canvas></div>
      </div>
      <div class="col-md-6">
        <div class="chart-container"><canvas id="hackathonEngagementChart"></canvas></div>
      </div>
    </div> --}}

    <!-- Manage Hackathons Table -->
    {{-- <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h4 class="mb-0"><i class="bi bi-list-task me-2"></i> {{ __('admin.manage_hackathons') }}</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>{{ __('admin.hackathon_name') }}</th>
                <th>{{ __('admin.date') }}</th>
                <th>{{ __('admin.status') }}</th>
                <th>{{ __('admin.registrants') }}</th>
                <th>{{ __('admin.teams') }}</th>
                <th>{{ __('admin.projects') }}</th>
                <th>{{ __('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <!-- This is example data, should be populated from a loop in a real application -->
              <tr>
                <td>{{ __('admin.future_of_ai_hackathon') }}</td>
                <td>{{ __('admin.march_15_17_2024') }}</td>
                <td><span class="badge bg-warning text-dark">{{ __('admin.application_in_progress')
                    }}</span></td>
                <td>150</td>
                <td>30</td>
                <td>-</td>
                <td>
                  <a href="hackathon-details.html?id=1" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip"
                    title="{{ __('admin.view_details') }}"><i class="bi bi-eye-fill"></i></a>
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                    data-bs-target="#createHackathonModal" data-hackathon-id="1" onclick="loadHackathonForEdit(this)"
                    data-bs-toggle="tooltip" title="{{ __('admin.edit') }}"><i class="bi bi-pencil-fill"></i></button>
                  <a href="judging-portal.html?hackathonId=1" class="btn btn-sm btn-outline-success"
                    data-bs-toggle="tooltip" title="{{ __('admin.judging_portal') }}"><i
                      class="bi bi-clipboard2-check-fill"></i></a>
                  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                    title="{{ __('admin.delete') }}"><i class="bi bi-trash-fill"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div> --}}

    <!-- Manage Teams Table -->
    <div class="card shadow-sm mb-4" id="manage-teams-section">
      <div class="card-header">
        <h4 class="mb-0"><i class="bi bi-diagram-3-fill me-2"></i> {{ __('admin.manage_participating_teams') }}</h4>
      </div>
      <div class="card-body">
        {{-- <div class="row mb-3">
          <div class="col-md-4">
            <select class="form-select" id="filterTeamsByHackathon">
              <option selected value="">{{ __('admin.filter_by_hackathon') }}</option>
              <option value="1">{{ __('admin.future_of_ai_hackathon') }}</option>
              <option value="2">{{ __('admin.sustainability_solutions_hackathon') }}</option>
            </select>
          </div>
          <div class="col-md-4">
            <input type="text" class="form-control" placeholder="{{ __('admin.search_for_team') }}">
          </div>
        </div> --}}

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>{{ __('admin.team_name') }}</th>
                <th>{{ __('admin.team_leader') }}</th>
                <th>{{ __('admin.number_of_members') }}</th>
                <th>{{ __('admin.creation_date') }}</th>
                <th>{{ __('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <!-- This is example data, should be populated from a loop in a real application -->
              @foreach ($data['all_teams'] as $team)
              <tr>
                <td>{{ $team->name }}</td>
                <td> {{ $team->user_name }} ( {{ $team->email }} )</td>
                <td>{{ $team->count_members }} / {{ $team->max_count_members }}</td>
                <td>{{ $team->created_at }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#teamDetailsModal"
                    data-team-id="{{ $team->id }}"><i class="bi bi-eye-fill me-2"></i> {{ __('admin.details')
                    }}</button>

                  <button class="btn btn-sm btn-outline-warning"><i class="bi bi-envelope-fill me-2"></i> {{
                    __('admin.message') }}</button>
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
        <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> {{ __('admin.manage_Projects') }}</h4>
      </div>
      <div class="card-body">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>{{ __('admin.project_name') }}</th>
              <th>{{ __('admin.project_description') }}</th>
              <th>{{ __('admin.Status') }}</th>
              <th>{{ __('admin.Progress') }}</th>
              <th>{{ __('admin.creation_date') }}</th>
              {{-- <th>{{ __('admin.actions') }}</th> --}}
            </tr>
          </thead>
          <tbody>
            <!-- This is example data, should be populated from a loop in a real application -->
            @foreach ($data['all_projects'] as $project)
            <tr>
              <td>{{ $project->project_name }}</td>
              <td> {{ $project->project_description }}</td>
              <td>

                @if($project->project_status == 20)
                {{ __('admin.Idea + Team') }} : {{ __('admin.Define the problem and form a team with clear roles.') }}
                @elseif($project->project_status == 40)
                {{ __('admin.Research') }} : {{ __('admin.Validate the idea, study users, and outline key features.') }}
                @elseif($project->project_status == 60)
                {{ __('admin.Design & Build') }} : {{ __('admin.Sketch UI, set up the project, and develop core
                functionality.') }}
                @elseif($project->project_status == 80)
                {{ __('admin.Ready for Pitch') }} : {{ __('admin.Prepare demo, finalize slides, and rehearse the
                presentation.') }}
                @elseif($project->project_status == 100)
                {{ __('admin.Project Submitted') }} : {{ __('admin.Submit code, share the demo, and complete
                documentation.
                story.') }}
                @endif
              </td>
              <td>
                <div class="progress mb-2" style="height: 20px;">
                  <div class="progress-bar bg-success" role="progressbar"
                    style="width:{{ $project->project_status  }}%;" aria-valuenow="{{ $project->project_status  }}"
                    aria-valuemin="0" aria-valuemax="100">{{
                    $project->project_status }}% {{ $l == 'en' ? 'Complete' :
                    'مكتمل' }}</div>
                </div>
              </td>
              <td>{{ $project->created_at }}</td>
              <td>
                <button class="btn btn-sm btn-outline-primary mb-3" data-bs-toggle="modal"
                  data-bs-target="#editProjectIdeaModal{{ $project->id }}">
                  <i class="ti ti-edit me-1"></i> {{ $l == 'en' ? 'Edit ' : 'تعديل ' }}
                </button>
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
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' :
                    'إلغاء'
                    }}</button>
                  <button type="button" class="btn btn-primary" onclick="saveProjectIdea()">{{ $l == 'en' ? 'Save
                    Changes'
                    : 'حفظ
                    التغييرات' }}</button>
                </div>
              </div>
            </div>
            @endforeach
          </tbody>
        </table>

      </div>
    </div>

    <div class="card shadow-sm mb-4" id="submitted-project-section">
      <div class="card-header">
        <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> {{ __('admin.Submitted_Projects') }}</h4>
      </div>
      <div class="card-body">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
                <th scope="col">{{ $l == 'en' ? 'Submitter' : 'المستخدم' }}</th>
                <th scope="col">{{ $l == 'en' ? 'Email' : 'البريد الاليكترونى' }}</th>
                <th scope="col">{{ $l == 'en' ? 'Date of Submission' : 'تاريخ التقديم' }}</th>
                <th scope="col">{{ $l == 'en' ? 'View Details' : 'عرض التفاصيل' }}</th>
                <th scope="col">{{ $l == 'en' ? 'Actions' : 'الإجراءات' }}</th>
            </tr>
          </thead>
          <tbody>
            <!-- This is example data, should be populated from a loop in a real application -->
            @foreach ($data['submitted_projects'] as $project)
            <tr>
             <td>{{ $project->user_name }}</td>
          <td>{{ $project->email }}</td>
          <td>{{ $project->submissionDate }}</td>
          <td>
            <a target="_blank" title="view"
              href="/dashboard/hackathon/viewSubmission/{{ $project->step_id }}/{{ $project->user_id }}"
              class="btn btn-label-primary btn-sm">
              {{ $l == 'en' ? 'View' : 'عرض' }}
            </a>
          </td>
          <td>
            
          </td>
            </tr>
           
            @endforeach
          </tbody>
        </table>

      </div>
    </div>


  </div>
</div>

<!-- Create/Edit Hackathon Modal -->
<div class="modal fade" id="createHackathonModal" tabindex="-1" aria-labelledby="createHackathonModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createHackathonModalLabel">{{ __('admin.create_new_hackathon') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.close') }}"></button>
      </div>
      <div class="modal-body">
        <form id="createHackathonForm">
          <input type="hidden" id="hackathonEditId">

          <!-- Basic Information -->
          <div class="row g-3">
            <div class="col-md-6">
              <label for="hackathonName" class="form-label">{{ __('admin.hackathon_name') }} <span
                  class="text-danger">*</span></label>
              <input type="text" class="form-control" id="hackathonName" required>
            </div>
            <div class="col-md-3">
              <label for="hackathonType" class="form-label">{{ __('admin.hackathon_type') }} <span
                  class="text-danger">*</span></label>
              <select class="form-select" id="hackathonType" required>
                <option value="virtual">{{ __('admin.virtual') }}</option>
                <option value="hybrid">{{ __('admin.hybrid') }}</option>
                <option value="physical">{{ __('admin.physical') }}</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="teamCapacity" class="form-label">{{ __('admin.max_team_members') }} <span
                  class="text-danger">*</span></label>
              <input type="number" class="form-control" id="teamCapacity" min="1" max="10" value="5" required>
            </div>
          </div>

          <!-- Description -->
          <div class="mt-3">
            <label for="hackathonDescription" class="form-label">{{ __('admin.description') }} <span
                class="text-danger">*</span></label>
            <textarea class="form-control" id="hackathonDescription" rows="3" required></textarea>
          </div>

          <!-- Dates -->
          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <label for="hackathonStartDate" class="form-label">{{ __('admin.start_date') }} <span
                  class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control" id="hackathonStartDate" required>
            </div>
            <div class="col-md-4">
              <label for="hackathonEndDate" class="form-label">{{ __('admin.end_date') }} <span
                  class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control" id="hackathonEndDate" required>
            </div>
            <div class="col-md-4">
              <label for="registrationDeadline" class="form-label">{{ __('admin.registration_deadline') }}
                <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control" id="registrationDeadline" required>
            </div>
          </div>

          <hr class="my-4">

          <!-- Themes Section (Select2 Multiple Select) -->
          <div class="mb-3">
            <label class="form-label">{{ __('admin.themes_instruction') }}</label>
            <div id="hackathonThemesWrapper" class="tags-input-wrapper">
              <!-- Tag items will be rendered here by JS -->
              <input type="text" id="hackathonThemesInput" class="form-control"
                placeholder="{{ __('admin.add_theme_placeholder') }}" autocomplete="off">
              <input type="hidden" id="hackathonThemesActual" name="hackathonThemes">
            </div>
            {{-- <small class="form-text text-muted">{{ __('admin.themes_hint') }}</small> --}}
          </div>

          <!-- Prizes Section -->
          <div class="mb-3">
            <label class="form-label fw-bold">{{ __('admin.prizes') }}</label>
            <div id="prizesContainer" class="vstack gap-3">
              <!-- Prize entries will be added here by JS -->
            </div>
            <button type="button" class="btn btn-outline-success btn-sm mt-2" id="addPrizeBtn">
              <i class="bi bi-plus-circle me-1"></i> {{ __('admin.add_prize') }}
            </button>
          </div>

          <!-- Evaluation Criteria Section -->
          <div class="mb-3">
            <label class="form-label fw-bold">{{ __('admin.evaluation_criteria') }}</label>
            <div id="evaluationCriteriaContainer" class="vstack gap-3">
              <!-- Evaluation criteria entries will be added here by JS -->
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <button type="button" class="btn btn-outline-info btn-sm" id="addCriterionBtn">
                <i class="bi bi-plus-circle me-1"></i> {{ __('admin.add_evaluation_criterion') }}
              </button>
              <div class="text-end fw-bold">{{ __('admin.total_weights') }} <span id="totalCriteriaWeight">0</span>%
              </div>
            </div>
          </div>

          <hr class="my-4">

          <!-- File Upload & Options -->
          <div class="mb-3">
            <label for="hackathonBannerImage" class="form-label">{{ __('admin.hackathon_banner_image')
              }}</label>
            <input class="form-control" type="file" id="hackathonBannerImage" accept="image/*">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="allowExternalTeams">
            <label class="form-check-label" for="allowExternalTeams">
              {{ __('admin.allow_external_teams') }}
            </label>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel')
          }}</button>
        <button type="button" class="btn btn-primary" onclick="saveHackathon()">{{ __('admin.save_hackathon')
          }}</button>
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
                      <table class="table mb-0 sessonsTable">
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="teamDetailsModalLabel">{{ __('admin.team_details_title') }} <span
            id="modal-team-name-detail"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('admin.close') }}"></button>
      </div>
      <div class="modal-body">
        <p><strong>{{ __('admin.hackathon') }}:</strong> <span id="modal-team-hackathon"></span></p>
        <p><strong>{{ __('admin.team_leader') }}:</strong> <span id="modal-team-leader"></span></p>
        <h6>{{ __('admin.members') }}</h6>
        <ul class="list-group" id="modal-team-members-list"></ul>
        <h6 class="mt-3">{{ __('admin.project_idea_if_available') }}</h6>
        <p id="modal-team-project-idea" class="text-muted">{{ __('admin.idea_not_specified_yet') }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
        <button type="button" class="btn btn-primary"><i class="bi bi-envelope-fill me-2"></i> {{
          __('admin.message_team') }}</button>
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
'leader' => "{$team->name} ({$team->email})",
'members' => collect($team->members)->map(fn($m) => $m->name)->all(),
'projectIdea' => $team->project_name_en,
];
})->keyBy('id')->toJson(JSON_UNESCAPED_UNICODE);
@endphp
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
  const mockHackathonDb = {
        "1": {
            name: "هاكثون مستقبل الذكاء الاصطناعي",
            type: "virtual",
            teamCapacity: 5,
            description: "وصف هاكثون الذكاء الاصطناعي...",
            startDate: "2024-03-15T09:00",
            endDate: "2024-03-17T18:00",
            registrationDeadline: "2024-03-10T23:59",
            themes: ["الذكاء الاصطناعي", "البيانات الضخمة", "تعلم الآلة"],
            prizes: [
                { rank: "المركز الأول", description: "20,000 ريال + احتضان" },
                { rank: "المركز الثاني", description: "10,000 ريال" }
            ],
            evaluationCriteria: [
                { name: "الابتكار والأصالة", weight: 30, description: "مدى حداثة الفكرة وقدرتها على تقديم حل فريد." },
                { name: "التطبيق العملي", weight: 25, description: "إمكانية تطبيق الحل على أرض الواقع." },
                { name: "جودة النموذج الأولي", weight: 20, description: "مدى اكتمال وفعالية النموذج الأولي." },
                { name: "نموذج العمل", weight: 15, description: "وضوح نموذج العمل المقترح." },
                { name: "أداء الفريق", weight: 10, description: "مدى فعالية عمل الفريق." }
            ],
            allowExternal: true
        }
    };

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    const themesWrapper = document.getElementById('hackathonThemesWrapper');
    const themesInput = document.getElementById('hackathonThemesInput');
    const themesActualInput = document.getElementById('hackathonThemesActual');
    let themes = [];

    function renderThemes() {
        Array.from(themesWrapper.children).forEach(child => {
            if (child !== themesInput) themesWrapper.removeChild(child);
        });
        themes.forEach((theme, index) => {
            const tagElement = document.createElement('span');
            tagElement.classList.add('tag-item');
            tagElement.textContent = theme;
            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-tag');
            removeBtn.innerHTML = '×';
            removeBtn.type = 'button';
            removeBtn.onclick = () => { themes.splice(index, 1); renderThemes(); };
            tagElement.appendChild(removeBtn);
            themesWrapper.insertBefore(tagElement, themesInput);
        });
        themesActualInput.value = themes.join(',');
    }
    
    if(themesInput) {
        themesInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const themeText = themesInput.value.trim();
                if (themeText && !themes.includes(themeText)) {
                    themes.push(themeText);
                    themesInput.value = '';
                    renderThemes();
                }
            }
        });
    }

    const prizesContainer = document.getElementById('prizesContainer');
    const addPrizeBtn = document.getElementById('addPrizeBtn');
    
    function createPrizeEntry(rankValue = '', descriptionValue = '') {
        const prizeDiv = document.createElement('div');
        
        // Using .align-items-center for vertical alignment and .mb-3 for spacing.
        prizeDiv.classList.add('dynamic-entry', 'prize-entry', 'row', 'g-3', 'align-items-center', 'my-3');

        // For perfect accessibility, inputs should have unique IDs matching a label's 'for' attribute.
        // The column layout is adjusted for better balance (4 + 7 + 1 = 12).
        prizeDiv.innerHTML = `
            <div class="col-md-4">
                <label class="form-label visually-hidden">{{ __('admin.prize_rank') }}</label>
                <input type="text" class="form-control form-control-sm" name="prizeRank[]" placeholder="{{ __('admin.prize_rank_placeholder') }}" value="${rankValue}" required>
            </div>
            <div class="col-md-7">
                <label class="form-label visually-hidden">{{ __('admin.prize_description') }}</label>
                <input type="text" class="form-control form-control-sm" name="prizeDescription[]" placeholder="{{ __('admin.prize_description_placeholder') }}" value="${descriptionValue}" required>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-entry-btn" aria-label="{{ __('admin.remove_prize') }}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        prizesContainer.appendChild(prizeDiv);

        // Attach event listener to the new remove button
        prizeDiv.querySelector('.remove-entry-btn').addEventListener('click', () => prizeDiv.remove());
    }

    if(addPrizeBtn) addPrizeBtn.addEventListener('click', () => createPrizeEntry());
    
    const criteriaContainer = document.getElementById('evaluationCriteriaContainer');
    const addCriterionBtn = document.getElementById('addCriterionBtn');
    const totalCriteriaWeightEl = document.getElementById('totalCriteriaWeight');

    function calculateTotalWeight() {
        let totalWeight = 0;
        criteriaContainer.querySelectorAll('input[name="criterionWeight[]"]').forEach(input => {
            totalWeight += parseInt(input.value) || 0;
        });
        totalCriteriaWeightEl.textContent = totalWeight;
        if (totalWeight !== 100) {
            totalCriteriaWeightEl.classList.add('text-danger');
            totalCriteriaWeightEl.classList.remove('text-success');
        } else {
            totalCriteriaWeightEl.classList.remove('text-danger');
            totalCriteriaWeightEl.classList.add('text-success');
        }
    }

    function createCriterionEntry(nameVal = '', weightVal = '', descVal = '') {
        const criterionDiv = document.createElement('div');
        
        // Added .align-items-center for vertical alignment and .mb-3 for spacing between entries.
        criterionDiv.classList.add('dynamic-entry', 'criterion-entry', 'row', 'align-items-center', 'my-3');

        // For perfect accessibility, each input should have a unique ID that matches the label's 'for' attribute.
        // This would typically involve passing a unique index or timestamp to this function.
        // The column layout is adjusted to give the description more space (4 + 2 + 5 + 1 = 12).
        criterionDiv.innerHTML = `
            <div class="col-md-4">
                <label class="form-label visually-hidden">{{ __('admin.criterion_name') }}</label>
                <input type="text" class="form-control form-control-sm" name="criterionName[]" placeholder="{{ __('admin.criterion_name_placeholder') }}" value="${nameVal}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label visually-hidden">{{ __('admin.criterion_weight') }}</label>
                <input type="number" class="form-control form-control-sm criterion-weight-input" name="criterionWeight[]" placeholder="{{ __('admin.criterion_weight_placeholder') }}" min="0" max="100" value="${weightVal}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label visually-hidden">{{ __('admin.criterion_description') }}</label>
                <textarea class="form-control form-control-sm" name="criterionDescription[]" rows="1" placeholder="{{ __('admin.criterion_description_placeholder') }}">${descVal}</textarea>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-danger btn-sm remove-entry-btn" aria-label="{{ __('admin.remove_criterion') }}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        criteriaContainer.appendChild(criterionDiv);

        // Attach event listeners
        criterionDiv.querySelector('.remove-entry-btn').addEventListener('click', () => {
            criterionDiv.remove();
            calculateTotalWeight();
        });

        criterionDiv.querySelector('.criterion-weight-input').addEventListener('input', calculateTotalWeight);
    }

    if(addCriterionBtn) addCriterionBtn.addEventListener('click', () => {
        createCriterionEntry();
        calculateTotalWeight();
    });

    const createHackathonModalEl = document.getElementById('createHackathonModal');
    createHackathonModalEl.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const hackathonId = button ? button.getAttribute('data-hackathon-id') : null;
        const form = document.getElementById('createHackathonForm');
        form.reset(); 
        themes = []; renderThemes();
        prizesContainer.innerHTML = '';
        criteriaContainer.innerHTML = ''; calculateTotalWeight();

        document.getElementById('createHackathonModalLabel').textContent = "إنشاء هاكثون جديد";
        document.getElementById('hackathonEditId').value = "";

        if (hackathonId && mockHackathonDb[hackathonId]) { 
            document.getElementById('createHackathonModalLabel').textContent = "تعديل الهاكثون";
            document.getElementById('hackathonEditId').value = hackathonId;
            const data = mockHackathonDb[hackathonId];
            document.getElementById('hackathonName').value = data.name;
            document.getElementById('hackathonType').value = data.type;
            document.getElementById('teamCapacity').value = data.teamCapacity;
            document.getElementById('hackathonDescription').value = data.description;
            document.getElementById('hackathonStartDate').value = data.startDate;
            document.getElementById('hackathonEndDate').value = data.endDate;
            document.getElementById('registrationDeadline').value = data.registrationDeadline;
            document.getElementById('allowExternalTeams').checked = data.allowExternal;

            if(data.themes && data.themes.length > 0){ themes = [...data.themes]; renderThemes(); }
            
            if(data.prizes && data.prizes.length > 0){
                data.prizes.forEach(prize => createPrizeEntry(prize.rank, prize.description));
            } else { createPrizeEntry("المركز الأول", ""); createPrizeEntry("المركز الثاني", ""); }

            if(data.evaluationCriteria && data.evaluationCriteria.length > 0){
                data.evaluationCriteria.forEach(crit => createCriterionEntry(crit.name, crit.weight, crit.description));
            } else { 
                createCriterionEntry("الابتكار", 30, "وصف الابتكار...");
                createCriterionEntry("التنفيذ", 40, "وصف التنفيذ...");
                createCriterionEntry("العرض", 30, "وصف العرض...");
            }
            calculateTotalWeight();

        } else { 
            createPrizeEntry("المركز الأول", "");
            createPrizeEntry("المركز الثاني", "");
            createPrizeEntry("المركز الثالث", "");

            createCriterionEntry("الابتكار والأصالة", 30, "مدى حداثة الفكرة وقدرتها على تقديم حل فريد.");
            createCriterionEntry("التطبيق العملي والقابلية للتنفيذ", 25, "إمكانية تطبيق الحل على أرض الواقع وتأثيره المحتمل.");
            createCriterionEntry("جودة النموذج الأولي/العرض", 20, "مدى اكتمال وفعالية النموذج الأولي وطريقة عرضه.");
            createCriterionEntry("نموذج العمل والاستدامة", 15, "وضوح نموذج العمل المقترح وقدرة المشروع على الاستمرار والنمو.");
            createCriterionEntry("أداء الفريق والتعاون", 10, "مدى فعالية عمل الفريق وتعاونه خلال الهاكثون.");
            calculateTotalWeight();
        }
    });

    function loadHackathonForEdit(buttonElement){ /* Handled by show.bs.modal */ }

    function saveHackathon() {
        if (!document.getElementById('hackathonName').value) {
            alert('يرجى إدخال اسم الهاكثون.'); return;
        }
        if (parseInt(totalCriteriaWeightEl.textContent) !== 100) {
            alert('يجب أن يكون إجمالي أوزان معايير التقييم 100%.'); return;
        }

        const themesValue = themes.join(',');
        console.log("Themes to save:", themesValue);

        const prizeRanks = Array.from(document.querySelectorAll('input[name="prizeRank[]"]')).map(input => input.value);
        const prizeDescriptions = Array.from(document.querySelectorAll('input[name="prizeDescription[]"]')).map(input => input.value);
        const prizesData = prizeRanks.map((rank, index) => ({ rank: rank, description: prizeDescriptions[index] }));
        console.log("Prizes to save:", prizesData);

        const criteriaNames = Array.from(document.querySelectorAll('input[name="criterionName[]"]')).map(input => input.value);
        const criteriaWeights = Array.from(document.querySelectorAll('input[name="criterionWeight[]"]')).map(input => input.value);
        const criteriaDescriptions = Array.from(document.querySelectorAll('textarea[name="criterionDescription[]"]')).map(input => input.value);
        const criteriaData = criteriaNames.map((name, index) => ({ name: name, weight: criteriaWeights[index], description: criteriaDescriptions[index] }));
        console.log("Criteria to save:", criteriaData);
        
        const hackathonEditId = document.getElementById('hackathonEditId').value;
        if(hackathonEditId){
            alert('تم تعديل الهاكثون بنجاح (محاكاة).');
        } else {
            alert('تم حفظ الهاكثون بنجاح (محاكاة).');
        }
        
        var createHackathonModalInstance = bootstrap.Modal.getInstance(createHackathonModalEl);
        createHackathonModalInstance.hide();
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
                (teamData.members || []).forEach(member => {
                    const li = document.createElement('li');
                    li.classList.add('list-group-item');
                    li.textContent = member;
                    membersList.appendChild(li);
                });
                document.getElementById('modal-team-project-idea').textContent = teamData.projectIdea || "لم يتم تحديد فكرة بعد.";
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
        'teamsStatusChart',
        // 'hackathonEngagementChart', // Temporarily hidden
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
                            borderColor: 'rgb(75, 192, 192)',
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
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
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
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(255, 159, 64, 0.7)',
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
            
            // This entire block will be skipped when 'hackathonEngagementChart' is removed from the chartCanvases array
            if (canvasId === 'hackathonEngagementChart') {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($engagementLabels),
                        datasets: [{
                            label: '{{ $l == 'ar' ? 'مراحل المشاركة' : 'Participation Stages' }}',
                            data: @json($engagementCounts),
                            backgroundColor: [
                                '#FF6384',
                                '#36A2EB',
                                '#FFCE56',
                                '#4BC0C0',
                            ],
                        }, ],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: '{{ $l == 'ar' ? 'قمع المشاركة' : 'Participation Funnel' }}'
                            },
                        },
                    },
                });
            }
        }
    });
});
</script>
@stop