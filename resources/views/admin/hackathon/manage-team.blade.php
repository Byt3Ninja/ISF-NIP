@extends('layouts.admin')

@section('content')
@php
// Define variables at the top to avoid undefined variable issues
$leaderOrnot = $team_Formation_raw->where('tm_isTeamLeader', 1)->where('tm_user_id', Auth::user()->id);
$maxMembers = $count_team;
$members = $team_Formation_raw->where('tm_acceptInvition', 'accepted')->count();
@endphp

<!-- Main Content -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-gradient-primary text-white">
          <div class="card-body py-4">
            <div class="d-flex align-items-center">
              <div class="avatar avatar-lg me-3">
                <div class="avatar-initial bg-white bg-opacity-20 rounded">
                  <i class="ti ti-user-cog ti-lg text-primary"></i>
                </div>
              </div>
              <div>
                <h3 class="mb-1 text-white">
                  {{ $l == 'en' ? 'Team Management' : 'إدارة الفريق' }}
                </h3>
                <p class="mb-0 text-white-75">
                  {{ $l == 'en' ? 'Manage your team for' : 'إدارة فريقك لـ' }}
                  <span class="fw-semibold">{{ $team_Formation_raw->first()->tf_name }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alerts -->
    <div class="row">
      @if(session()->has('success'))
      <div class="col-md-12 mb-4">
        <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
          {{ __('admin.'. session()->get('success')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
      @endif

      @if(session()->has('error'))
      <div class="col-md-12 mb-4">
        <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
          {{ __('admin.'.session()->get('error')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
      @endif
    </div>
    <!-- Alerts -->

    <!-- No Team Section -->
    <div id="no-team-section" class="card card-body text-center p-5" style="display:none;">
      <i class="ti ti-users ti-xl text-muted mb-3"></i>
      <h4>{{ $l == 'en' ? 'You haven\'t joined or created a team yet.' : 'لم تنضم إلى فريق بعد أو لم تقم بإنشاء فريق.'
        }}</h4>
      <p>{{ $l == 'en' ? 'You can create a new team or join an existing team from the hackathon details page.' : 'يمكنك
        إنشاء فريق جديد أو الانضمام إلى فريق موجود من خلال صفحة تفاصيل الهاكثون.' }}</p>
      <a href="#" class="btn btn-primary mt-3">
        <i class="ti ti-arrow-left me-1"></i>
        {{ $l == 'en' ? 'Back to Hackathon & Setup Team' : 'العودة لصفحة الهاكثون وتكوين فريق' }}
      </a>
    </div>

    <!-- Team Management Section -->
    <div id="team-management-section" class="row g-4">
      <!-- Team Members Column -->
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header bg-light border-bottom py-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-1">
                  <i class="ti ti-users text-primary me-2"></i>
                  {{ $l == 'en' ? 'Team:' : 'فريق:' }}
                  <span id="team-name-display" class="text-primary fw-bold">{{ $team_Formation_raw->first()->tf_name }}</span>
                </h5>
                <small class="text-secondary">
                  {{ $l == 'en' ? 'Manage your team members and invitations' : 'إدارة أعضاء الفريق والدعوات' }}
                </small>
              </div>
              @if($leaderOrnot->count() > 0)
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTeamInfoModal"
                data-bs-toggle="tooltip" title="{{ $l == 'en' ? 'Edit Team Information' : 'تعديل معلومات الفريق' }}">
                <i class="ti ti-edit me-1"></i>
                {{ $l == 'en' ? 'Edit Team' : 'تعديل الفريق' }}
              </button>
              @endif
            </div>
          </div>
          <div class="card-body">
            <!-- Team Stats -->
            <div class="row mb-4">
              <div class="col-md-4">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-label-primary rounded">
                      <i class="ti ti-users"></i>
                    </div>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ $l == 'en' ? 'Members' : 'الأعضاء' }}</h6>
                    <small class="text-muted">
                      <span id="member-count" class="fw-semibold">{{ $members }}</span>/{{ $maxMembers }}
                    </small>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-label-warning rounded">
                      <i class="ti ti-clock"></i>
                    </div>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ $l == 'en' ? 'Pending' : 'معلق' }}</h6>
                    <small class="text-muted">{{ $team_Pinding->count() }} {{ $l == 'en' ? 'invitations' : 'دعوات' }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-label-info rounded">
                      <i class="ti ti-progress"></i>
                    </div>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ $l == 'en' ? 'Project Status' : 'حالة المشروع' }}</h6>
                    <small class="text-muted">
                      @php
                      $projectStatus = $team_Formation_raw->first()->tf_projectStatus;
                      @endphp
                      @if($projectStatus == 20)
                        <span class="badge bg-label-primary">{{ __('admin.Idea + Team') }}</span>
                      @elseif($projectStatus == 40)
                        <span class="badge bg-label-info">{{ __('admin.Research') }}</span>
                      @elseif($projectStatus == 60)
                        <span class="badge bg-label-warning">{{ __('admin.Design & Build') }}</span>
                      @elseif($projectStatus == 80)
                        <span class="badge bg-label-success">{{ __('admin.Ready for Pitch') }}</span>
                      @elseif($projectStatus == 100)
                        <span class="badge bg-label-dark">{{ __('admin.Submitted') }}</span>
                      @else
                        <span class="badge bg-label-secondary">{{ $l == 'en' ? 'Not Set' : 'غير محدد' }}</span>
                      @endif
                    </small>
                  </div>
                </div>
              </div>
            </div>

            <!-- Team Members List -->
            <div class="mb-4">
              <h6 class="mb-3">
                <i class="ti ti-users me-2 text-primary"></i>
                {{ $l == 'en' ? 'Team Members' : 'أعضاء الفريق' }}
              </h6>
            <ul class="list-group list-group-flush" id="team-members-list">
              @php
              $team_leader = $team_Formation_raw->where('tm_isTeamLeader', 1)->first();
              $T = 0;
              @endphp

              @if($team_leader)
              <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <img src="{{ '/191014/'.$team_leader->u_img_url ?? '/icons/account.svg' }}" alt="Avatar"
                      onerror="this.onerror=null;this.src='/icons/account.svg';" class="rounded-circle">
                  </div>
                  <div>
                    <h6 class="mb-0">
                      @if($leaderOrnot->count() > 0)
                      {{ $l == 'en' ? 'You (Team Leader)' : 'أنت (قائد الفريق)' }}
                      @else
                      {{ $l == 'en'
                      ? $team_leader->user_name_en . " (Leader)"
                      : $team_leader->user_name_ar . " (قائد)" }}
                      @endif
                    </h6>
                    <small class="text-muted">{{ $team_leader->u_email ?? '' }}</small>
                  </div>
                </div>
                <span class="badge bg-label-primary rounded-pill">
                  <i class="ti ti-crown me-1"></i>
                  {{ $l == 'en' ? 'Leader' : 'قائد' }}
                </span>
              </li>
              @endif
              @foreach ($team_members as $member)
              @php $T++ @endphp
              <li id="Member{{ $member->tm_id }}"
                class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <img src="{{ '/191014/'. $member->u_img_url ?? '/icons/account.svg' }}" alt="Avatar"
                      onerror="this.onerror=null;this.src='/icons/account.svg';" class="rounded-circle">
                  </div>
                  <div>
                    <h6 class="mb-0">
                      {{ $l == 'en' ? $member->tm_name : $member->tm_name }}
                    </h6>
                    <small class="text-muted">
                      {{ $l == 'en' ? 'Team Member ' . $T : 'عضو الفريق ' . $T }}
                    </small>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-success rounded-pill me-2">
                    <i class="ti ti-check me-1"></i>
                    {{ $l == 'en' ? 'Active' : 'نشط' }}
                  </span>
                  @if($leaderOrnot->count() > 0)
                  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                    title="{{ $l == 'en' ? 'Remove Member (Leader only)' : 'إزالة العضو (متاح للقائد فقط)' }}"
                    onclick="confirmRemoveMember({{ $member->tm_id }}, '{{ $member->tm_name }}')">
                    <i class="ti ti-user-minus"></i>
                  </button>
                  @endif
                </div>
              </li>
              @endforeach

              @foreach ($team_Pinding as $index => $pendingMember)
              <li id="pendingMember{{ $pendingMember->tm_id }}"
                class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <div class="avatar-initial bg-label-warning rounded-circle">
                      <i class="ti ti-clock"></i>
                    </div>
                  </div>
                  <div>
                    <h6 class="mb-0 text-muted">{{ $pendingMember->tm_email }}</h6>
                    <small class="text-muted">
                      {{ $l == 'en' ? 'Pending invitation' : 'دعوة معلقة' }}
                    </small>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-warning rounded-pill me-2">
                    <i class="ti ti-clock me-1"></i>
                    {{ $l == 'en' ? 'Pending' : 'معلق' }}
                  </span>
                  <input type="hidden" id="pendingMemberEmail" value="{{ $pendingMember->tm_id }}">
                  @if($leaderOrnot->count() > 0)
                  <button class="btn btn-sm btn-outline-danger" data-id="{{ $pendingMember->tm_id }}"
                    data-bs-toggle="tooltip" title="{{ $l == 'en' ? 'Cancel Invitation' : 'إلغاء الدعوة' }}"
                    onclick="confirmCancelInvitation({{ $pendingMember->tm_id }}, '{{ $pendingMember->tm_email }}')">
                    <i class="ti ti-x"></i>
                  </button>
                  @endif
                </div>
              </li>
              @endforeach
            </ul>
            </div>
            <!-- Team Recruitment Section -->
            @if($leaderOrnot->count() > 0 && $members <= $maxMembers)
            <div class="card bg-label-light border-0 mb-4">
              <div class="card-body">
                <h6 class="mb-3">
                  <i class="ti ti-user-plus me-2 text-success"></i>
                  {{ $l == 'en' ? 'Invite New Members' : 'دعوة أعضاء جدد' }}
                </h6>
                <div class="row g-3 mb-3">
                  <div class="col-12">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                      data-bs-target="#viewAllMembersModal">
                      <i class="ti ti-users me-1"></i>
                      {{ $l == 'en' ? 'Browse Available Members' : 'تصفح الأعضاء المتاحين' }}
                    </button>
                  </div>
                </div>
                <form class="row g-3 align-items-end" method="POST" action="{{ route('hackathon.invitation') }}">
                  @csrf
                  <div class="col-md-8">
                    <label for="memberEmail" class="form-label small text-muted">
                      {{ $l == 'en' ? 'Or invite by email' : 'أو دعوة عبر البريد الإلكتروني' }}
                    </label>
                    <input type="email" name="memberEmail" class="form-control" id="memberEmail"
                      placeholder="{{ $l == 'en' ? 'Enter member email address' : 'أدخل البريد الإلكتروني للعضو' }}" required>
                  </div>
                  <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100" id="sendInvitationBtn">
                      <span class="btn-text">
                        <i class="ti ti-send me-1"></i>
                        {{ $l == 'en' ? 'Send Invite' : 'إرسال دعوة' }}
                      </span>
                      <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ $l == 'en' ? 'Sending...' : 'جاري الإرسال...' }}
                      </span>
                    </button>
                  </div>
                </form>
              </div>
            </div>
            @elseif($members >= $maxMembers)
            <div class="alert alert-success d-flex align-items-center">
              <i class="ti ti-check-circle me-2"></i>
              <div>
                <strong>{{ $l == 'en' ? 'Team Complete!' : 'الفريق مكتمل!' }}</strong>
                <div class="small">{{ $l == 'en' ? 'Your team has reached the maximum number of members.' : 'وصل فريقك إلى العدد الأقصى من الأعضاء.' }}</div>
              </div>
            </div>
            @endif
            <!-- Join Requests Section -->
            @if($leaderOrnot->count() > 0)
            @php
            $teamLeader = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition',
            'join')->where('tm_team_formation_id', $team_Formation_raw->first()->tf_id);
            @endphp
            
            <div class="card border-warning">
              <div class="card-header bg-label-warning">
                <h6 class="mb-0">
                  <i class="ti ti-user-check me-2"></i>
                  {{ $l == 'en' ? 'Pending Join Requests' : 'طلبات الانضمام المعلقة' }}
                </h6>
              </div>
              <div class="card-body">
                <div id="join-requests-list">
                  @if($teamLeader->isEmpty())
                  <div class="text-center py-3">
                    <i class="ti ti-user-check ti-xl text-muted mb-2"></i>
                    <p class="text-muted mb-0">{{ $l == 'en' ? 'No join requests currently.' : 'لا توجد طلبات انضمام حالياً.' }}</p>
                  </div>
                  @else
                  <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-3">
                        <div class="avatar-initial bg-warning rounded-circle">
                          <i class="ti ti-user text-white"></i>
                        </div>
                      </div>
                      <div>
                        <h6 class="mb-0">{{ $teamLeader->first()->tm_name }}</h6>
                        <small class="text-muted">{{ $l == 'en' ? 'Wants to join your team' : 'يريد الانضمام إلى فريقك' }}</small>
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                      <button class="btn btn-sm btn-success approve-btn"
                        onclick="ApprovedjoinTeam('{{ $teamLeader->first()->tm_id }}','{{ $teamLeader->first()->tm_email }}')"
                        data-id="{{ $teamLeader->first()->tm_id }}">
                        <span class="btn-text">
                          <i class="ti ti-check me-1"></i>
                          {{ $l == 'en' ? 'Approve' : 'موافقة' }}
                        </span>
                        <span class="btn-spinner d-none">
                          <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                          {{ $l == 'en' ? 'Approving...' : 'جاري الموافقة...' }}
                        </span>
                      </button>
                      <button class="btn btn-sm btn-outline-danger reject-btn"
                        onclick="RejectedjoinTeam('{{ $teamLeader->first()->tm_id }}','{{ $teamLeader->first()->tm_email }}')"
                        data-id="{{ $teamLeader->first()->tm_id }}">
                        <span class="btn-text">
                          <i class="ti ti-x me-1"></i>
                          {{ $l == 'en' ? 'Reject' : 'رفض' }}
                        </span>
                        <span class="btn-spinner d-none">
                          <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                          {{ $l == 'en' ? 'Rejecting...' : 'جاري الرفض...' }}
                        </span>
                      </button>
                    </div>
                  </div>
                  @endif
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Team Project & Info Column -->
      <div class="col-lg-4">
        <div class="row g-4">
          <!-- Team Image & Quick Info Card -->
          <div class="col-12">
            <div class="card">
              <div class="card-body text-center">
                <!-- Team Image Section -->
                <div class="mb-3">
                  <div class="avatar avatar-xl mx-auto mb-3 position-relative">
                    @php
                    $teamImage = $team_Formation_raw->first();
                    $imageSrc = '/icons/team-default.svg';
                    if ($teamImage && property_exists($teamImage, 'tf_img_team') && $teamImage->tf_img_team) {
                        $imageSrc = '/' . $teamImage->tf_img_team . '?t=' . time();
                    }
                    @endphp
                    <img 
                      src="{{ $imageSrc }}" 
                      alt="{{ $team_Formation_raw->first()->tf_name }}" 
                      class="rounded-circle w-100 h-100 border-3 border-light shadow-sm"
                      onerror="this.onerror=null;this.src='/icons/team-default.svg';"
                      id="team-image-display"
                    >
                    @if($leaderOrnot->count() > 0)
                    <div class="position-absolute bottom-0 end-0">
                      <button class="btn btn-sm btn-primary rounded-circle p-2" data-bs-toggle="modal" data-bs-target="#editTeamInfoModal"
                        data-bs-toggle="tooltip" title="{{ $l == 'en' ? 'Edit Team Image' : 'تعديل صورة الفريق' }}">
                        <i class="ti ti-edit"></i>
                      </button>
                    </div>
                    @endif
                  </div>
                  <h5 class="mb-1">{{ $team_Formation_raw->first()->tf_name }}</h5>
                  @if($team_Formation_raw->first()->tf_description)
                  <p class="text-muted small mb-3">{{ $team_Formation_raw->first()->tf_description }}</p>
                  @endif
                  
                  @if($leaderOrnot->count() > 0)
                  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editTeamInfoModal">
                    <i class="ti ti-edit me-1"></i>
                    {{ $l == 'en' ? 'Edit Team Info' : 'تعديل معلومات الفريق' }}
                  </button>
                  @endif
                  <a 
                    href="/dashboard/hackathon/team/{{ $team_Formation_raw->first()->tf_id }}"
                    class="btn btn-sm btn-outline-info"
                  >
                    <i class="ti ti-eye me-1"></i>
                    {{ $l == 'en' ? 'View Team' : 'عرض الفريق' }}
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Project Information Card -->
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="mb-0">
                  <i class="ti ti-bulb text-warning me-2"></i>
                  {{ $l == 'en' ? 'Project Information' : 'معلومات المشروع' }}
                </h5>
              </div>
              <div class="card-body">
                <div class="mb-4">
                  <label class="form-label small text-muted fw-semibold">
                    {{ $l == 'en' ? 'Project Name' : 'اسم المشروع' }}
                  </label>
                  <h6 class="mb-0">{{ $team_Formation_raw->first()->tf_projectName_en }}</h6>
                </div>

                <div class="mb-4">
                  <label class="form-label small text-muted fw-semibold">
                    {{ $l == 'en' ? 'Project Description' : 'وصف المشروع' }}
                  </label>
                  <p class="small mb-0" id="team-project-idea">{{ $team_Formation_raw->first()->tf_projectDescription_en }}</p>
                </div>

                <button class="btn btn-outline-info btn-sm w-100 mb-3" data-bs-toggle="modal"
                  data-bs-target="#editProjectIdeaModal">
                  <i class="ti ti-edit me-1"></i>
                  {{ $l == 'en' ? 'Edit Project Details' : 'تعديل تفاصيل المشروع' }}
                </button>

                <!-- Project Progress -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label small text-muted fw-semibold mb-0">
                      {{ $l == 'en' ? 'Project Progress' : 'تقدم المشروع' }}
                    </label>
                    <span class="badge bg-primary">{{ $team_Formation_raw->first()->tf_projectStatus }}%</span>
                  </div>
                  <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-gradient-primary" role="progressbar"
                      style="width:{{ $team_Formation_raw->first()->tf_projectStatus  }}%;"
                      aria-valuenow="{{ $team_Formation_raw->first()->tf_projectStatus  }}" aria-valuemin="0"
                      aria-valuemax="100">
                    </div>
                  </div>
                  <small class="text-muted">
                    @if($team_Formation_raw->first()->tf_projectStatus == 20)
                    <strong>{{ __('admin.Idea + Team') }}</strong><br>
                    {{ __('admin.Define the problem and form a team with clear roles.') }}
                    @elseif($team_Formation_raw->first()->tf_projectStatus == 40)
                    <strong>{{ __('admin.Research') }}</strong><br>
                    {{ __('admin.Validate the idea, study users, and outline key features.') }}
                    @elseif($team_Formation_raw->first()->tf_projectStatus == 60)
                    <strong>{{ __('admin.Design & Build') }}</strong><br>
                    {{ __('admin.Sketch UI, set up the project, and develop core functionality.') }}
                    @elseif($team_Formation_raw->first()->tf_projectStatus == 80)
                    <strong>{{ __('admin.Ready for Pitch') }}</strong><br>
                    {{ __('admin.Prepare demo, finalize slides, and rehearse the presentation.') }}
                    @elseif($team_Formation_raw->first()->tf_projectStatus == 100)
                    <strong>{{ __('admin.Project Submitted') }}</strong><br>
                    {{ __('admin.Submit code, share the demo, and complete documentation. story.') }}
                    @endif
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Team Info Modal -->
<div class="modal fade" id="editTeamInfoModal" tabindex="-1" aria-labelledby="editTeamInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light pb-6">
        <h5 class="modal-title" id="editTeamInfoModalLabel">
          <i class="ti ti-edit me-2"></i>
          {{ $l == 'en' ? 'Edit Team Information' : 'تعديل معلومات الفريق' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="/dashboard/hackathon/EditTeamInfo" id="editTeamInfoForm" enctype="multipart/form-data">
        <div class="modal-body">
          @csrf
          <input type="hidden" name="team_formation_id" value="{{ $team_Formation_raw->first()->tf_id }}">
          
          <!-- Team Image Section -->
          <div class="row">
            <div class="col-md-4">
              <div class="text-center mb-4">
                <div class="avatar avatar-xl mx-auto mb-3 position-relative">
                  @php
                  $teamImageModal = $team_Formation_raw->first();
                  $imageSrcModal = '/icons/team-default.svg';
                  if ($teamImageModal && property_exists($teamImageModal, 'tf_img_team') && $teamImageModal->tf_img_team) {
                      $imageSrcModal = '/' . $teamImageModal->tf_img_team . '?t=' . time();
                  }
                  @endphp
                  <img 
                    src="{{ $imageSrcModal }}" 
                    alt="{{ $team_Formation_raw->first()->tf_name }}" 
                    class="rounded-circle w-100 h-100 border-3 border-light shadow"
                    onerror="this.onerror=null;this.src='/icons/team-default.svg';"
                    id="team-image-preview"
                  >
                  <div class="position-absolute bottom-0 end-0">
                    <div class="avatar avatar-xs">
                      <div class="avatar-initial bg-primary rounded-circle">
                        <i class="ti ti-camera text-white"></i>
                      </div>
                    </div>
                  </div>
                </div>
                
                @php
                $teamImageCheck = $team_Formation_raw->first();
                $hasImage = $teamImageCheck && property_exists($teamImageCheck, 'tf_img_team') && $teamImageCheck->tf_img_team;
                @endphp
                @if($hasImage)
                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                  <label class="form-check-label" for="remove_image">
                    <small class="text-muted">{{ $l == 'en' ? 'Remove current image' : 'إزالة الصورة الحالية' }}</small>
                  </label>
                </div>
                @endif
              </div>
            </div>
            
            <div class="col-md-8">
              <div class="mb-3">
                <label for="team_image" class="form-label">
                  <i class="ti ti-photo me-1"></i>
                  {{ $l == 'en' ? 'Team Image (Optional)' : 'صورة الفريق (اختياري)' }}
                </label>
                <input 
                  type="file" 
                  class="form-control" 
                  name="team_image"
                  id="team_image" 
                  accept="image/*"
                  onchange="previewTeamImage(this)"
                >
                <div class="form-text">
                  {{ $l == 'en' ? 'Supported formats: JPG, PNG, GIF. Maximum size: 2MB' : 'الصيغ المدعومة: JPG, PNG, GIF. الحد الأقصى: 2 ميجابايت' }}
                </div>
              </div>
              
              <div class="mb-3">
                <label for="team_name" class="form-label">
                  <i class="ti ti-users me-1"></i>
                  {{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}
                </label>
                <input 
                  type="text" 
                  class="form-control" 
                  name="team_name"
                  value="{{ $team_Formation_raw->first()->tf_name }}" 
                  id="team_name" 
                  required
                  maxlength="50"
                >
                <div class="form-text">{{ $l == 'en' ? 'Maximum 50 characters' : 'حد أقصى 50 حرف' }}</div>
              </div>

              <div class="mb-3">
                <label for="team_description" class="form-label">
                  <i class="ti ti-file-text me-1"></i>
                  {{ $l == 'en' ? 'Team Description (Optional)' : 'وصف الفريق (اختياري)' }}
                </label>
                <textarea 
                  class="form-control" 
                  name="team_description" 
                  id="team_description"
                  rows="4"
                  maxlength="500"
                  placeholder="{{ $l == 'en' ? 'Brief description about your team...' : 'وصف مختصر عن فريقكم...' }}"
                >{{ $team_Formation_raw->first()->tf_description ?? '' }}</textarea>
                <div class="form-text">{{ $l == 'en' ? 'Maximum 500 characters' : 'حد أقصى 500 حرف' }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light pt-6">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <i class="ti ti-x me-1"></i>
            {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
          </button>
          <button type="submit" class="btn btn-primary" id="editTeamInfoBtn">
            <span class="btn-text">
              <i class="ti ti-device-floppy me-1"></i>
              {{ $l == 'en' ? 'Save Changes' : 'حفظ التغييرات' }}
            </span>
            <span class="btn-spinner d-none">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              {{ $l == 'en' ? 'Saving...' : 'جاري الحفظ...' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Project Idea Modal -->
<div class="modal fade" id="editProjectIdeaModal" tabindex="-1" aria-labelledby="editProjectIdeaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProjectIdeaModalLabel">
          {{ $l == 'en' ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="/dashboard/hackathon/EditProjectIdea" id="editProjectForm">
        <div class="modal-body">
          @csrf
          <input type="hidden" name="team_formation_id" value="{{ $team_Formation_raw->first()->tf_id }}">
          <div class="mb-3">
            <label for="Project_Name_en" class="form-label">
              {{ __('admin.Project_Name') }}
            </label>
            <input 
              type="text" 
              class="form-control" 
              name="Project_Name_en"
              value="{{ $team_Formation_raw->first()->tf_projectName_en }}" 
              id="Project_Name_en" 
              required
            >
          </div>

          <div class="mb-3">
            <label for="Project_Description_en" class="form-label">
              {{ __('admin.Project_Description') }}
            </label>
            <textarea 
              class="form-control" 
              name="Project_Description_en" 
              id="Project_Description_en"
              rows="2"
            >{{ $team_Formation_raw->first()->tf_projectDescription_en }}</textarea>
          </div>

          <div class="mb-3">
            <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Project Status' : 'حالة المشروع' }}</label>
            <select class="form-select" id="Project_Status" name="Project_Status">
              <option value="">{{ $l == 'en' ? 'Choose v...' : 'اختر الحالة...' }}</option>
              <option value="20" {{ $team_Formation_raw->first()->tf_projectStatus == '20' ? 'selected' : '' }}>
                {{ __('admin.Idea + Team') }}
              </option>
              <option value="40" {{ $team_Formation_raw->first()->tf_projectStatus == '40' ? 'selected' : '' }}>
                {{ __('admin.Research') }}
              </option>
              <option value="60" {{ $team_Formation_raw->first()->tf_projectStatus == '60' ? 'selected' : '' }}>
                {{ __('admin.Design & Build') }}
              </option>
              <option value="80" {{ $team_Formation_raw->first()->tf_projectStatus == '80' ? 'selected' : '' }}>
                {{ __('admin.Ready for Pitch') }}
              </option>
              <option value="100" {{ $team_Formation_raw->first()->tf_projectStatus == '100' ? 'selected' : '' }}>
                {{ __('admin.Project Submitted') }}
              </option>
            </select>
          </div>


          <div class="mb-3">
            <label for="Region" class="form-label">{{ $l == 'en' ? 'Region' : 'المنطقة' }}</label>
            <select class="form-select" id="Region" name="Region">
              <option value="">{{ $l == 'en' ? 'Choose Region...' : 'اختر المنطقة...' }}</option>
              <option value="AL_Madinah_AL_Munawwarah" {{ $team_Formation_raw->first()->tf_region == 'AL_Madinah_AL_Munawwarah' ? 'selected' : '' }}>
                {{ __('admin.AL_Madinah_AL_Munawwarah') }}
              </option>
              <option value="Al_Ahsa" {{ $team_Formation_raw->first()->tf_region == 'Al_Ahsa' ? 'selected' : '' }}>
                {{ __('admin.Al_Ahsa') }}
              </option>
              <option value="Asir" {{ $team_Formation_raw->first()->tf_region == 'Asir' ? 'selected' : '' }}>
                {{ __('admin.Asir') }}
              </option>
             
            </select>
          </div>


        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
          </button>
          <button type="submit" class="btn btn-primary" id="editProjectBtn">
            <span class="btn-text">
              {{ $l == 'en' ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}
            </span>
            <span class="btn-spinner d-none">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              {{ $l == 'en' ? 'Saving...' : 'جاري الحفظ...' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View All Members Modal -->
<div class="modal fade" id="viewAllMembersModal" tabindex="-1" aria-labelledby="viewAllMembersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewAllMembersModalLabel">
          <i class="ti ti-users me-2"></i>{{ $l == 'en' ? 'Available Members' : 'الأعضاء المتاحين' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        @if(empty($availableMembers))
          <div class="text-center py-5">
            <div class="avatar avatar-xl mx-auto mb-3">
              <div class="avatar-initial bg-label-secondary rounded-circle">
                <i class="ti ti-users-off ti-xl"></i>
              </div>
            </div>
            <h5 class="text-muted">{{ $l == 'en' ? 'No available members found' : 'لا يوجد أعضاء متاحين' }}</h5>
            <p class="text-muted">{{ $l == 'en' ? 'All eligible members are already part of teams or have been invited.' : 'جميع الأعضاء المؤهلين هم بالفعل جزء من فرق أو تم دعوتهم.' }}</p>
          </div>
        @else
          <div class="row g-4">
            @foreach($availableMembers as $member)
              <div class="col-md-6 col-lg-4">
                <div class="card h-100 border {{ $member['isInvited'] ? 'border-warning' : 'border-light' }} shadow-sm">
                  <div class="card-body text-center">
                    <div class="avatar avatar-xl mx-auto mb-3 position-relative">
                      <img 
                        src="{{ '/191014/'.$member['image'] ?? '/icons/account.svg' }}" 
                        onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                        alt="{{ $member['name'] }}" 
                        class="rounded-circle w-100 h-100 border-2 border-light"
                      />
                      @if($member['isInvited'])
                      <div class="position-absolute top-0 end-0">
                        <span class="badge bg-warning rounded-pill">
                          <i class="ti ti-clock"></i>
                        </span>
                      </div>
                      @endif
                    </div>
                    <h6 class="card-title mb-2">{{ $member['name'] }}</h6>
                    
                    @if ($member['title'])
                      <p class="text-muted small mb-2">
                        <i class="ti ti-briefcase me-1"></i>{{ $member['title'] }}
                      </p>
                    @endif
                    
                    @if ($member['email'])
                      <p class="text-muted small mb-2">
                        <i class="ti ti-mail me-1"></i>{{ $member['email'] }}
                      </p>
                    @endif
                    
                    @if($member['city'] || $member['state'] || $member['country'])
                      <p class="text-muted small mb-3">
                        <i class="ti ti-map-pin me-1"></i>
                        {{ $member['city'] ?? '' }}
                        @if($member['city'] && ($member['state'] || $member['country'])), @endif
                        {{ $member['state'] ?? '' }}
                        @if($member['state'] && $member['country']), @endif
                        {{ $member['country'] ?? '' }}
                      </p>
                    @endif
                    
                    <button 
                      type="button" 
                      id="inviteMemberBtn{{ $member['id'] }}"
                      class="btn {{ $member['isInvited'] ? 'btn-warning' : 'btn-primary' }} btn-sm w-100 invite-member-btn" 
                      onclick="sendInvitationToMember('{{ $member['email'] }}', '{{ $member['name'] }}', '{{ $member['id'] }}', {{ $member['isInvited'] ? 'true' : 'false' }})"
                      data-member-id="{{ $member['id'] }}"
                      @if($member['isInvited']) disabled @endif
                    >
                      <span class="btn-text">
                        <i class="ti ti-{{ $member['isInvited'] ? 'check' : 'send' }} me-1"></i>
                        @if($member['isInvited'])
                          {{ $l == 'en' ? 'Invitation Sent' : 'تم إرسال الدعوة' }}
                        @else
                          {{ $l == 'en' ? 'Send Invitation' : 'إرسال دعوة' }}
                        @endif
                      </span>
                      <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ $l == 'en' ? 'Sending...' : 'جاري الإرسال...' }}
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i>
          {{ $l == 'en' ? 'Close' : 'إغلاق' }}
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  const HACKATHON_ID_TEAM_PAGE = new URLSearchParams(window.location.search).get('hackathonId') || '1';
  const HACKATHON_NAME = "{{ $l == 'en' ? 'Future of AI Hackathon' : 'هاكثون مستقبل الذكاء الاصطناعي' }}"; 
  const IS_TEAM_LEADER = true; 

  $(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Set dynamic content
    $('#breadcrumb-hackathon-name').text(HACKATHON_NAME);
    $('#breadcrumb-hackathon-name').attr('href', `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}`);
    $('#hackathon-title-team-page').text(HACKATHON_NAME);
    $('#link-hackathon-details').attr('href', `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}`);
    $('#link-submit-project').attr('href', `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}&submissionOpen=true#submission`);
    $('#link-to-book-mentorship').attr('href', `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}#mentorship`);

    const userHasTeam = new URLSearchParams(window.location.search).get('teamCreated') === 'true' || true; 

    if (userHasTeam) {
      $('#team-management-section').show(); 
      $('#no-team-section').hide();
      const joinedTeamName = new URLSearchParams(window.location.search).get('joinedTeamName');
      if(joinedTeamName) {
        $('#team-name-display').text(joinedTeamName);
      }
      if(IS_TEAM_LEADER) {
        $('#disbandTeamBtn').show();
      }
    } else {
      $('#team-management-section').hide();
      $('#no-team-section').show();
    }

    $('#inviteMemberForm').on('submit', function(e) {
      e.preventDefault();
      const email = $('#memberEmail').val();
      const submitBtn = $('#sendInvitationBtn');
      
      if (email) {
        // Show spinner and disable button
        submitBtn.prop('disabled', true);
        submitBtn.find('.btn-text').addClass('d-none');
        submitBtn.find('.btn-spinner').removeClass('d-none');

        // Submit form
        $.post($(this).attr('action'), $(this).serialize())
          .done(function(response) {
            Swal.fire({
              icon: 'success',
              title: '{{ $l == 'en' ? 'Invitation Sent!' : 'تم إرسال الدعوة!' }}',
              text: `{{ $l == 'en' ? 'Invitation sent to' : 'تم إرسال دعوة إلى' }} ${email}.`,
              confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            }).then(() => {
              window.location.reload();
            });
          })
          .fail(function(error) {
            Swal.fire({
              icon: 'error',
              title: '{{ $l == 'en' ? 'Error!' : 'خطأ!' }}',
              text: '{{ $l == 'en' ? 'There was an error sending the invitation.' : 'حدث خطأ أثناء إرسال الدعوة.' }}',
              confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          })
          .always(function() {
            // Reset button state
            submitBtn.prop('disabled', false);
            submitBtn.find('.btn-text').removeClass('d-none');
            submitBtn.find('.btn-spinner').addClass('d-none');
          });
      }
    });

    $('#editProjectForm').on('submit', function(e) {
      e.preventDefault();
      const submitBtn = $('#editProjectBtn');
      
      // Show spinner and disable button
      submitBtn.prop('disabled', true);
      submitBtn.find('.btn-text').addClass('d-none');
      submitBtn.find('.btn-spinner').removeClass('d-none');

      // Submit form
      $.post($(this).attr('action'), $(this).serialize())
        .done(function(response) {
          Swal.fire({
            icon: 'success',
            title: '{{ $l == 'en' ? 'Project Updated!' : 'تم تحديث المشروع!' }}',
            text: '{{ $l == 'en' ? 'Project idea updated successfully.' : 'تم تحديث فكرة المشروع بنجاح.' }}',
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            showCancelButton: false,
            showConfirmButton: true,
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          }).then(() => {
            window.location.reload();
          });
        })
        .fail(function(error) {
          Swal.fire({
            icon: 'error',
            title: '{{ $l == 'en' ? 'Error!' : 'خطأ!' }}',
            text: '{{ $l == 'en' ? 'There was an error updating the project.' : 'حدث خطأ أثناء تحديث المشروع.' }}',
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
        })
        .always(function() {
          // Reset button state
          submitBtn.prop('disabled', false);
          submitBtn.find('.btn-text').removeClass('d-none');
          submitBtn.find('.btn-spinner').addClass('d-none');
        });
    });

    $('#editTeamInfoForm').on('submit', function(e) {
      e.preventDefault();
      const submitBtn = $('#editTeamInfoBtn');
      
      // Show spinner and disable button
      submitBtn.prop('disabled', true);
      submitBtn.find('.btn-text').addClass('d-none');
      submitBtn.find('.btn-spinner').removeClass('d-none');

      // Create FormData for file upload
      const formData = new FormData(this);

      // Submit form with file upload
      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          // Update the team image display immediately
          if (response.new_image_url) {
            // Update main team image display
            $('#team-image-display').attr('src', response.new_image_url + '?t=' + new Date().getTime());
            // Update modal preview image
            $('#team-image-preview').attr('src', response.new_image_url + '?t=' + new Date().getTime());
          } else {
            // If image was removed, show default
            $('#team-image-display').attr('src', '/icons/team-default.svg');
            $('#team-image-preview').attr('src', '/icons/team-default.svg');
          }
          
          Swal.fire({
            icon: 'success',
            title: '{{ $l == 'en' ? 'Team Info Updated!' : 'تم تحديث معلومات الفريق!' }}',
            text: '{{ $l == 'en' ? 'Team information updated successfully.' : 'تم تحديث معلومات الفريق بنجاح.' }}',
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            showCancelButton: false,
            showConfirmButton: true,
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          }).then(() => {
            // Close the modal instead of reloading the page
            $('#editTeamInfoModal').modal('hide');
            
            // Update team name if changed
            const newTeamName = $('#team_name').val();
            if (newTeamName) {
              $('#team-name-display').text(newTeamName);
            }
          });
        },
        error: function(error) {
          Swal.fire({
            icon: 'error',
            title: '{{ $l == 'en' ? 'Error!' : 'خطأ!' }}',
            text: error?.message || '{{ $l == 'en' ? 'There was an error updating the team information.' : 'حدث خطأ أثناء تحديث معلومات الفريق.' }}',
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
        },
        complete: function() {
          // Reset button state
          submitBtn.prop('disabled', false);
          submitBtn.find('.btn-text').removeClass('d-none');
          submitBtn.find('.btn-spinner').addClass('d-none');
        }
      });
    });

    $('#editProjectIdeaModal').on('show.bs.modal', function (event) {
      $('#projectIdeaText').val($('#team-project-idea').text());
    });

    // Character counters for team info form
    function updateCharacterCount(inputId, maxLength) {
      const input = $(`#${inputId}`);
      const currentLength = input.val().length;
      const remaining = maxLength - currentLength;
      const formText = input.siblings('.form-text');
      
      if (remaining < 0) {
        formText.text(`{{ $l == 'en' ? 'Exceeds maximum by' : 'يتجاوز الحد الأقصى بـ' }} ${Math.abs(remaining)} {{ $l == 'en' ? 'characters' : 'حرف' }}`).addClass('text-danger');
      } else {
        formText.text(`{{ $l == 'en' ? 'Remaining:' : 'المتبقي:' }} ${remaining} {{ $l == 'en' ? 'characters' : 'حرف' }}`).removeClass('text-danger');
      }
    }

    // Bind character counters
    $('#team_name').on('input', function() {
      updateCharacterCount('team_name', 50);
    });

    $('#team_description').on('input', function() {
      updateCharacterCount('team_description', 500);
    });

    // Initialize character counters when modal opens
    $('#editTeamInfoModal').on('shown.bs.modal', function() {
      updateCharacterCount('team_name', 50);
      updateCharacterCount('team_description', 500);
    });
  });

  function saveProjectIdea() {
    const newIdea = $('#projectIdeaText').val();
    const newTrack = $('#targetTrackSelect option:selected').text();
    $('#team-project-idea').text(newIdea);
    
    if(newTrack !== "{{ $l == 'en' ? 'Choose track...' : 'اختر المحور...' }}") {
      $('#team-target-track').text(newTrack);
    }
    
    $('#editProjectIdeaModal').modal('hide');
    
    Swal.fire({
      icon: 'success',
      title: '{{ $l == 'en' ? 'Project Idea Saved!' : 'تم حفظ فكرة المشروع!' }}',
      text: '{{ $l == 'en' ? 'Project idea saved successfully.' : 'تم حفظ فكرة المشروع بنجاح.' }}',
      confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
      showCancelButton: false,
      showConfirmButton: true,
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
    });
  }

  function confirmLeaveTeam() {
    Swal.fire({
      title: '{{ $l == 'en' ? 'Are you sure?' : 'هل أنت متأكد؟' }}',
      text: '{{ $l == 'en' ? 'Are you sure you want to leave this team?' : 'هل أنت متأكد أنك تريد مغادرة هذا الفريق؟' }}',
      icon: 'warning',
      confirmButtonText: '{{ $l == 'en' ? 'Yes, leave team' : 'نعم، مغادرة الفريق' }}',
      cancelButtonText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          icon: 'success',
          title: '{{ $l == 'en' ? 'Team Left!' : 'تم مغادرة الفريق!' }}',
          text: '{{ $l == 'en' ? 'You have left the team.' : 'لقد غادرت الفريق.' }}',
          confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
          showCancelButton: false,
          showConfirmButton: true,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        }).then(() => {
          window.location.href = `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}&applied=true`;
        });
      }
    });
  }

  function confirmRemoveMember(id, memberName) {
    var url = '{{ route('hackathon.getOutTeam') }}';
    var token = '{{ csrf_token() }}';

    Swal.fire({
      title: '{{ $l == 'en' ? 'Remove Team Member?' : 'إزالة عضو الفريق؟' }}',
      text: `{{ $l == 'en' ? 'Are you sure you want to remove' : 'هل أنت متأكد أنك تريد إزالة' }} ${memberName} {{ $l == 'en' ? 'from the team?' : 'من الفريق؟' }}`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '{{ $l == 'en' ? 'Yes, remove member' : 'نعم، إزالة العضو' }}',
      cancelButtonText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(url, {'_method': 'POST' , '_token': token, 'id': id},
          function(response) {
            Swal.fire({
              icon: 'success',
              title: '{{ $l == 'en' ? 'Member Removed!' : 'تم إزالة العضو!' }}',
              text: `${memberName} {{ $l == 'en' ? 'has been removed from the team.' : 'تم إزالته من الفريق.' }}`,
              confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            }).then(() => {
              $('#Member'+id).remove();
              const currentCount = parseInt($('#member-count').text());
              $('#member-count').text(currentCount - 1);
            });
          }).fail(function(error) { 
            console.log(error);
            Swal.fire({
              icon: 'error',
              title: "{{ __('admin.Error') }}!",
              text: "{{ __('admin.DeleteFailed') }}",
              showCancelButton: false,
              showConfirmButton: true,
              confirmButtonText: "{{ __('admin.OK') }}",
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          });
      }
    });
  }

  function confirmCancelInvitation(id, email) {
    var url = '{{ route('hackathon.Cancelinvitation') }}';
    var token = '{{ csrf_token() }}';

    Swal.fire({
      title: '{{ $l == 'en' ? 'Cancel Invitation?' : 'إلغاء الدعوة؟' }}',
      text: `{{ $l == 'en' ? 'Are you sure you want to cancel the invitation sent to' : 'هل أنت متأكد أنك تريد إلغاء الدعوة المرسلة إلى' }} ${email}{{ $l == 'en' ? '?' : '؟' }}`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '{{ $l == 'en' ? 'Yes, cancel invitation' : 'نعم، إلغاء الدعوة' }}',
      cancelButtonText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      customClass: {
        confirmButton: 'btn btn-warning me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(url, {'_method': 'POST' , '_token': token, 'id': id},
          function(response) {
            Swal.fire({
              icon: 'success',
              title: '{{ $l == 'en' ? 'Invitation Cancelled!' : 'تم إلغاء الدعوة!' }}',
              text: `{{ $l == 'en' ? 'The invitation to' : 'الدعوة إلى' }} ${email} {{ $l == 'en' ? 'has been cancelled.' : 'تم إلغاؤها.' }}`,
              confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            }).then(() => {
              $('#pendingMember'+id).remove();
            });
          }).fail(function(error) { 
            console.log(error);
            Swal.fire({
              icon: 'error',
              title: "{{ __('admin.Error') }}!",
              text: "{{ __('admin.CancelFailed') }}",
              showCancelButton: false,
              showConfirmButton: true,
              confirmButtonText: "{{ __('admin.OK') }}",
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          });
      }
    });
  }

  function confirmDisbandTeam() {
    Swal.fire({
      title: '{{ $l == 'en' ? 'Are you sure?' : 'هل أنت متأكد؟' }}',
      text: '{{ $l == 'en' ? 'Are you sure you want to disband this team? This action cannot be undone and will remove all members.' : 'هل أنت متأكد أنك تريد حل هذا الفريق؟ هذا الإجراء لا يمكن التراجع عنه وسيؤدي إلى إزالة جميع الأعضاء.' }}',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '{{ $l == 'en' ? 'Yes, disband team' : 'نعم، حل الفريق' }}',
      cancelButtonText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          icon: 'success',
          title: '{{ $l == 'en' ? 'Team Disbanded!' : 'تم حل الفريق!' }}',
          text: '{{ $l == 'en' ? 'Team has been disbanded successfully.' : 'تم حل الفريق بنجاح.' }}',
          confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
          showCancelButton: false,
          showConfirmButton: true,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        }).then(() => {
          window.location.href = `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}&applied=true`;
        });
      }
    });
  }

  function ApprovedjoinTeam(id, teamName) {
    if (confirm(`{{ $l == "en" ? "Are you sure you want to Approved This request to join team" : "هل أنت متأكد أنك تريد الموافقة على طلب الانضمام إلى فريق" }} "${teamName}"{{ $l == "en" ? "?" : "؟" }}`)) {
      const button = $(`.approve-btn[data-id="${id}"]`);
      
      // Show spinner and disable button
      button.prop('disabled', true);
      button.find('.btn-text').addClass('d-none');
      button.find('.btn-spinner').removeClass('d-none');

      fetch("{{ route('hackathon.ApprovedRequestJoinTeam') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          id: id
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then(data => {
        alert(`{{ $l == "en" ? "Request approved successfully" : "تم الموافقة على الطلب بنجاح" }}`);
        window.location.href = window.location.href;
      })
      .catch(error => {
        alert("{{ $l == 'en' ? 'There was an error processing the request.' : 'حدث خطأ أثناء معالجة الطلب.' }}");
        console.error(error);
      })
      .finally(() => {
        // Reset button state
        button.prop('disabled', false);
        button.find('.btn-text').removeClass('d-none');
        button.find('.btn-spinner').addClass('d-none');
      });
    }
  }

  function RejectedjoinTeam(id, teamName) {
    if (confirm(`{{ $l == "en" ? "Are you sure you want to Reject This request to join team" : "هل أنت متأكد أنك تريد الرفض على طلب الانضمام إلى فريق" }} "${teamName}"{{ $l == "en" ? "?" : "؟" }}`)) {
      const button = $(`.reject-btn[data-id="${id}"]`);
      
      // Show spinner and disable button
      button.prop('disabled', true);
      button.find('.btn-text').addClass('d-none');
      button.find('.btn-spinner').removeClass('d-none');

      fetch("{{ route('hackathon.RejectedRequestJoin') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          id: id
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then(data => {
        alert(`{{ $l == "en" ? "Request rejected successfully" : "تم رفض الطلب بنجاح" }}`);
        window.location.href = window.location.href;
      })
      .catch(error => {
        alert("{{ $l == 'en' ? 'There was an error processing the request.' : 'حدث خطأ أثناء معالجة الطلب.' }}");
        console.error(error);
      })
      .finally(() => {
        // Reset button state
        button.prop('disabled', false);
        button.find('.btn-text').removeClass('d-none');
        button.find('.btn-spinner').addClass('d-none');
      });
    }
  }

  function sendInvitationToMember(email, name, id, isInvited = false) {
    console.log('isInvited', isInvited)

    if (isInvited) {
      Swal.fire({
        icon: 'info',
        title: '{{ $l == 'en' ? 'Invitation Already Sent' : 'تم إرسال الدعوة بالفعل' }}',
        text: `{{ $l == 'en' ? 'An invitation has already been sent to' : 'تم إرسال دعوة بالفعل إلى' }} ${name} (${email}).`,
        confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
      return;
    }

    Swal.fire({
      title: '{{ $l == 'en' ? 'Send Invitation?' : 'إرسال دعوة؟' }}',
      text: `{{ $l == 'en' ? 'Send invitation to' : 'إرسال دعوة إلى' }} ${name} (${email}){{ $l == 'en' ? '?' : '؟' }}`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '{{ $l == 'en' ? 'Yes, send invitation' : 'نعم، إرسال دعوة' }}',
      cancelButtonText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      customClass: {
        confirmButton: 'btn btn-primary me-2',
        cancelButton: 'btn btn-label-secondary'
      },
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        const button = $(`.invite-member-btn[data-member-id="${id}"]`);
        
        // Show spinner and disable button
        button.prop('disabled', true);
        button.find('.btn-text').addClass('d-none');
        button.find('.btn-spinner').removeClass('d-none');

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('user_id', id);
        formData.append('memberEmail', email);

        fetch("{{ route('hackathon.invitation') }}", {
          method: "POST",
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.text();
        })
        .then(data => {
          Swal.fire({
            icon: 'success',
            title: '{{ $l == 'en' ? 'Invitation Sent!' : 'تم إرسال الدعوة!' }}',
            text: `{{ $l == 'en' ? 'Invitation sent to' : 'تم إرسال دعوة إلى' }} ${name}.`,
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            showCancelButton: false,
            showConfirmButton: true,
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          }).then(() => {
            window.location.reload();
          });
        })
        .catch(error => {
          Swal.fire({
            icon: 'error',
            title: '{{ $l == 'en' ? 'Error!' : 'خطأ!' }}',
            text: '{{ $l == 'en' ? 'There was an error sending the invitation.' : 'حدث خطأ أثناء إرسال الدعوة.' }}',
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
          console.error(error);
        })
        .finally(() => {
          // Reset button state
          button.prop('disabled', false);
          button.find('.btn-text').removeClass('d-none');
          button.find('.btn-spinner').addClass('d-none');
        });
      }
    });
  }

  // Function to preview team image before upload
  function previewTeamImage(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        $('#team-image-preview').attr('src', e.target.result);
      };
      
      reader.readAsDataURL(input.files[0]);
      
      // Uncheck remove image checkbox if file is selected
      $('#remove_image').prop('checked', false);
    }
  }

  // Additional ready function for image handling
  $(document).ready(function() {
    // Handle remove image checkbox
    $('#remove_image').on('change', function() {
      if ($(this).is(':checked')) {
        // Clear file input
        $('#team_image').val('');
        // Reset preview to default
        $('#team-image-preview').attr('src', '/icons/team-default.svg');
      }
    });
  });
</script>
@stop