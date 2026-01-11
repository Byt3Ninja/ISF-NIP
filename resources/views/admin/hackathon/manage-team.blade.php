@extends('layouts.admin')

@section('content')
@php
// Define variables at the top to avoid undefined variable issues
$leaderOrnot = $team_Formation_raw->where('tm_isTeamLeader', 1)->where('tm_user_id', Auth::user()->id);
$maxMembers = $count_team;
$members = $team_Formation_raw->where('tm_acceptInvition', 'accepted')->count();
@endphp

<!-- Main Content -->
<div class="">
  <div class="">
    <!-- Page Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card bg-gradient-primary text-white">
          <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
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
              <!-- Chat Button -->
              <a href="/dashboard/chat" class="btn btn-white" id="openTeamChatBtn" data-bs-toggle="tooltip" title="{{ $l == 'en' ? 'Open Team Chat' : 'فتح محادثة الفريق' }}">
                <i class="ti ti-message-circle me-1"></i>
                {{ $l == 'en' ? 'Team Chat' : 'محادثة الفريق' }}
              </a>
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
      <div class="col-lg-7">
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
                        <span class="badge bg-label-dark">{{ __('admin.Project Submitted') }}</span>
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
              @if($leaderOrnot->count() > 0)
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
              @endif
            </ul>
            </div>
            <!-- Team Recruitment Section -->
            @if($leaderOrnot->count() > 0 && $members < $maxMembers)
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
            @php
              // $deletemembers = $team_Formation_raw->whereIn('tm_acceptInvition', ['join', 'pending'])->delete();
              $deletemembers = App\Team_member::whereIn('acceptInvition', ['join', 'pending'])->where('team_formation_id', $team_Formation_raw->first()->tf_id)->delete();
            @endphp
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
      <div class="col-lg-5">
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
                    {{-- @if (app()->environment('local', 'dev', 'test'))
                      <form method="POST" action="/dashboard/hackathon/deleteTeamInfo" id="deleteTeamForm">
                        <div class="modal-body">
                          @csrf
                          <input type="hidden" name="team_formation_id" value="{{ $team_Formation_raw->first()->tf_id }}">
                          </div>
                          <button type="submit" class="btn btn-sm btn-danger"><i class="ti ti-trash me-1"></i>{{ $l == 'en' ? 'Delete Team' : 'حذف الفريق' }}</button>
                      </form>
                    @endif --}}
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

          <!-- Team Workspace Tabs -->
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <!-- Workspace Navigation -->
                <ul class="nav nav-pills mb-0" id="workspaceTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="project-info-tab" data-bs-toggle="tab" data-bs-target="#project-info" type="button" role="tab" aria-controls="project-info" aria-selected="true">
                      <i class="ti ti-bulb me-1"></i>
                      {{ $l == 'en' ? 'Project' : 'المشروع' }}
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shared-files-tab" data-bs-toggle="tab" data-bs-target="#shared-files" type="button" role="tab" aria-controls="shared-files" aria-selected="false">
                      <i class="ti ti-folder me-1"></i>
                      {{ $l == 'en' ? 'Files' : 'الملفات' }}
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="team-tasks-tab" data-bs-toggle="tab" data-bs-target="#team-tasks" type="button" role="tab" aria-controls="team-tasks" aria-selected="false">
                      <i class="ti ti-list-check me-1"></i>
                      {{ $l == 'en' ? 'Tasks' : 'المهام' }}
                    </button>
                  </li>
                </ul>
              </div>
              
              <div class="card-body">
                <div class="tab-content" id="workspaceTabsContent">
                  <!-- Project Information Tab -->
                  <div class="tab-pane fade show active" id="project-info" role="tabpanel" aria-labelledby="project-info-tab">
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

                  <!-- Shared Files Tab -->
                  <div class="tab-pane fade" id="shared-files" role="tabpanel" aria-labelledby="shared-files-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0">{{ $l == 'en' ? 'Team Files' : 'ملفات الفريق' }}</h6>
                      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        <i class="ti ti-cloud-upload me-1"></i>
                        {{ $l == 'en' ? 'Upload' : 'رفع' }}
                      </button>
                    </div>

                    <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                      <ul class="list-group list-group-flush" id="fileList">
                        @if(isset($sharedFile) && $sharedFile->count() > 0)
                          @foreach($sharedFile as $file)
                            @php
                              $user = Auth::user();
                              $fullPath = $file->sf_file;
                              $pathInfo = pathinfo($fullPath);
                              $filename = $pathInfo['filename'];
                              $extension = $pathInfo['extension'];
                              if (file_exists($fullPath)) {
                                  $bytes = filesize($fullPath);
                                  $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                  $factor = floor((strlen($bytes) - 1) / 3);
                                  $sizeReadable = sprintf('%.2f', $bytes / pow(1024, $factor)) . $units[$factor];
                              } else {
                                  $sizeReadable = $l == 'en' ? 'File not found' : 'الملف غير موجود';
                              }
                            @endphp
                            
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-2 py-2">
                                <div class="d-flex align-items-center" style="min-width: 0;">
                                    <i class="ti ti-file text-primary me-2"></i>
                                    <div class="flex-grow-1 me-2" style="min-width: 0;">
                                        <div class="small fw-medium text-truncate">
                                            {{ $filename.".".$extension }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $sizeReadable }} - {{ $l == 'en' ? 'by ' . $file->{"user_name_". $l} : 'بواسطة ' . $file->{"user_name_". $l} }}
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm flex-shrink-0">
                                    <a href="{{ asset($file->sf_file) }}" download class="btn btn-outline-primary btn-sm" title="{{ $l == 'en' ? 'Download' : 'تنزيل' }}">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    @if($file->sf_user_id == $user->id || (isset($file->tf_user_id) && $file->tf_user_id == $user->id))
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(this, {{ $file->sf_id }})" title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </li>
                          @endforeach
                        @else
                          <li class="list-group-item border-0 text-center py-4">
                            <i class="ti ti-folder-open text-muted mb-2 d-block" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">{{ $l == 'en' ? 'No files uploaded yet' : 'لم يتم رفع ملفات بعد' }}</p>
                          </li>
                        @endif
                      </ul>
                    </div>
                  </div>

                  <!-- Team Tasks Tab -->
                  <div class="tab-pane fade" id="team-tasks" role="tabpanel" aria-labelledby="team-tasks-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0">{{ $l == 'en' ? 'Team Tasks' : 'مهام الفريق' }}</h6>
                      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="ti ti-plus me-1"></i>
                        {{ $l == 'en' ? 'Add Task' : 'إضافة مهمة' }}
                      </button>
                    </div>

                    <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                      <ul class="list-group list-group-flush" id="taskList">
                        @if(isset($todoList) && $todoList->count() > 0)
                        {{-- {{ dd($todoList) }} --}}
                          @foreach($todoList as $task)
                            <li class="list-group-item border-0 px-2 py-2" id="task-{{ $task->tl_id }}">
                              <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                  <div class="d-flex align-items-center mb-2">
                                    <span class="fw-medium me-2">{{ $task->tl_title }}</span>
                                    @php
                                      $statusClass = 'secondary';
                                      $statusText = 'Todo';
                                      $statusIcon = 'ti-circle';
                                      
                                      if($task->tl_status == 'DONE') {
                                        $statusClass = 'success';
                                        $statusText = $l == 'en' ? 'Done' : 'مكتملة';
                                        $statusIcon = 'ti-check';
                                      } elseif($task->tl_status == 'inprogress') {
                                        $statusClass = 'warning';
                                        $statusText = $l == 'en' ? 'In Progress' : 'قيد التنفيذ';
                                        $statusIcon = 'ti-progress';
                                      } else {
                                        $statusText = $l == 'en' ? 'Todo' : 'مهمة';
                                      }
                                    @endphp
                                    <span class="badge bg-label-{{ $statusClass }} rounded-pill py-1 px-2">
                                      <i class="ti {{ $statusIcon }} ti-16px me-1"></i>
                                      {{ $statusText }}
                                    </span>
                                  </div>
                                  <small class="text-muted">
                                    {{ $l == 'en' ? 'Assigned to: '. $task->assignee_name_en : 'مُسند إلى: '. $task->assignee_name_en }}
                                    @if($task->tl_due_date)
                                      - {{ $l == 'en' ? 'Due: ' . $task->tl_due_date : 'موعد الانتهاء: ' . $task->tl_due_date }}
                                    @endif
                                  </small>
                                </div>
                                
                                @if($team_Formation_leader OR $task->tl_assignee_id == auth()->user()->id) 
                                <div class="btn-group btn-group-sm ms-2">
                                  <button class="btn btn-outline-secondary btn-sm" 
                                    onclick="editTask('{{ $task->tl_id }}', '{{ $task->tl_title }}', '{{ $task->tl_assignee_id }}', '{{ $task->tl_due_date }}', '{{ $task->tl_description }}', '{{ $task->tl_status }}')"
                                    title="{{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة' }}">
                                    <i class="ti ti-edit"></i>
                                  </button>
                                  <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteTask('{{ $task->tl_id }}', '{{ $task->tl_title }}')"
                                    title="{{ $l == 'en' ? 'Delete Task' : 'حذف المهمة' }}">
                                    <i class="ti ti-trash"></i>
                                  </button>
                                </div>
                                @endif
                              </div>
                            </li>
                          @endforeach
                        @else
                          <li class="list-group-item border-0 text-center py-4">
                            <i class="ti ti-list-check text-muted mb-2 d-block" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">{{ $l == 'en' ? 'No tasks created yet' : 'لم يتم إنشاء مهام بعد' }}</p>
                          </li>
                        @endif
                      </ul>
                    </div>
                  </div>
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

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadFileModalLabel">
          <i class="ti ti-cloud-upload me-2"></i>
          {{ $l == 'en' ? 'Upload New File' : 'رفع ملف جديد' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="fileUploadForm" method="POST" action="/dashboard/hackathon/addSharedFile" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label for="fileInput" class="form-label">{{ $l == 'en' ? 'Choose File' : 'اختر ملف' }}</label>
            <input class="form-control" name="file" type="file" id="fileInput" required>
            <div class="invalid-feedback file-err" style="display: none;">{{ $l == 'en' ? 'Please select a file to upload.' : 'يرجى اختيار ملف للرفع.' }}</div>
            <div class="invalid-feedback file-size-err" style="display: none;">{{ $l == 'en' ? 'Maximum file size: 10MB' : 'أقصى حجم للملف: 10 ميجابايت' }}</div>
            <div class="invalid-feedback file-type-err" style="display: none;">{{ $l == 'en' ? 'Allowed: PDF, DOCX, PPTX, ZIP' : 'المسموح: PDF, DOCX, PPTX, ZIP' }}</div>
          </div>
          <div class="mb-3">
            <label for="fileDescription" class="form-label">
              {{ $l == 'en' ? 'File Description (Optional)' : 'وصف الملف (اختياري)' }}
            </label>
            <input type="text" class="form-control" id="fileDescription" name="description"
              placeholder="{{ $l == 'en' ? 'e.g., Initial logo design' : 'مثال: تصميم الشعار الأولي' }}">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i>
          {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
        </button>
        <button type="button" class="btn btn-primary" onclick="uploadFile()">
          <span class="btn-text">
            <i class="ti ti-cloud-upload me-1"></i>
            {{ $l == 'en' ? 'Upload' : 'رفع' }}
          </span>
          <span class="btn-spinner d-none">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            {{ $l == 'en' ? 'Uploading...' : 'جاري الرفع...' }}
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTaskModalLabel">
          <i class="ti ti-plus me-2"></i>
          {{ $l == 'en' ? 'Add New Task' : 'إضافة مهمة جديدة' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="taskForm" method="POST" action="/dashboard/hackathon/AddtodoList">
          @csrf
          <div class="mb-3">
            <label for="taskTitle" class="form-label">{{ $l == 'en' ? 'Task Title' : 'عنوان المهمة' }}</label>
            <input type="text" class="form-control" name="title" id="taskTitle" required>
          </div>
          
          <div class="mb-3">
            <label for="taskAssignee" class="form-label">{{ $l == 'en' ? 'Assignee' : 'المسؤول' }}</label>
            <select class="form-select" id="taskAssignee" name="assignee" required>
              <option value="">{{ $l == 'en' ? 'Select assignee...' : 'اختر المسؤول...' }}</option>
              <option value="You">{{ $l == 'en' ? 'You' : 'أنت' }}</option>
               @if($team_Formation_leader) 
              @if(isset($team_members))
                @foreach($team_members as $member)
                  <option value="{{ $member->tm_id }}">
                    {{ $l == 'en' ? $member->tm_name : $member->tm_name }}
                  </option>
                @endforeach
              @endif
              @endif
            </select>
          </div>
          
          <div class="mb-3">
            <label for="taskStatus" class="form-label">{{ $l == 'en' ? 'Status' : 'الحالة' }}</label>
            <select class="form-select" id="taskStatus" name="status">
              <option value="todo">
                {{ $l == 'en' ? 'Todo' : 'مهمة' }}
              </option>
              <option value="inprogress">
                {{ $l == 'en' ? 'In Progress' : 'قيد التنفيذ' }}
              </option>
              <option value="DONE">
                {{ $l == 'en' ? 'Done' : 'مكتملة' }}
              </option>
            </select>
          </div>
          

          <div class="mb-3">
            <label for="taskDueDate" class="form-label">{{ $l == 'en' ? 'Due Date' : 'تاريخ الاستحقاق' }}</label>
            <input type="date" name="due_date" class="form-control datepicker" id="taskDueDate">
          </div>
          <div class="mb-3">
            <label for="taskDescription" class="form-label">
              {{ $l == 'en' ? 'Task Description (Optional)' : 'وصف المهمة (اختياري)' }}
            </label>
            <textarea class="form-control" id="taskDescription" name="description" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i>
          {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
        </button>
        <button type="button" class="btn btn-primary" onclick="addTask()">
          <span class="btn-text">
            <i class="ti ti-plus me-1"></i>
            {{ $l == 'en' ? 'Add Task' : 'إضافة المهمة' }}
          </span>
          <span class="btn-spinner d-none">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            {{ $l == 'en' ? 'Adding...' : 'جاري الإضافة...' }}
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="EditTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTaskModalLabel">
          <i class="ti ti-edit me-2"></i>
          {{ $l == 'en' ? 'Edit Task' : 'تعديل المهمة' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editTaskForm">
          @csrf
          <div class="mb-3">
            <label for="EtaskTitle" class="form-label">{{ $l == 'en' ? 'Task Title' : 'عنوان المهمة' }}</label>
            <input type="text" class="form-control" name="title" id="EtaskTitle" required>
          </div>
        
            {{-- @dd($team_Formation_leader ) --}}
            <div class="mb-3">
              <label for="EtaskAssignee" class="form-label">{{ $l == 'en' ? 'Assignee' : 'المسؤول' }}</label>
              <select class="form-select" id="EtaskAssignee" name="assignee" required>
                <option value="">{{ $l == 'en' ? 'Select assignee...' : 'اختر المسؤول...' }}</option>
                <option value="You" >{{ $l == 'en' ? 'You' : 'أنت' }}</option>
                @if(isset($team_Formation_leader) && !empty($team_members))
                  @foreach($team_members as $member)
                      <option value="{{ $member->tm_id }}"
                          {{ $member->tm_id == $team_Formation_leader->tf_user_id ? 'selected' : '' }}>
                          {{ $l == 'en' ? $member->tm_name : $member->tm_name }}
                      </option>
                  @endforeach
              @endif
              </select>
            </div>

            <div class="mb-3">
              <label for="EtaskStatus" class="form-label">{{ $l == 'en' ? 'Status' : 'الحالة' }}</label>
              <select class="form-select" id="EtaskStatus" name="statustodo">
                <option value="todo">
                  <i class="ti ti-circle me-1"></i>
                  {{ $l == 'en' ? 'Todo' : 'مهمة' }}
                </option>
                <option value="inprogress">
                  <i class="ti ti-progress me-1"></i>
                  {{ $l == 'en' ? 'In Progress' : 'قيد التنفيذ' }}
                </option>
                <option value="DONE">
                  <i class="ti ti-check me-1"></i>
                  {{ $l == 'en' ? 'Done' : 'مكتملة' }}
                </option>
              </select>
            </div>
               
            
          

          <div class="mb-3">
            <label for="EtaskDueDate" class="form-label">{{ $l == 'en' ? 'Due Date' : 'تاريخ الاستحقاق' }}</label>
            <input type="date" name="due_date" class="form-control datepicker" id="EtaskDueDate">
          </div>
          <div class="mb-3">
            <label for="EtaskDescription" class="form-label">
              {{ $l == 'en' ? 'Task Description (Optional)' : 'وصف المهمة (اختياري)' }}
            </label>
            <textarea class="form-control" id="EtaskDescription" name="description" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i>
          {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
        </button>
        <button type="button" class="btn btn-primary" onclick="updateTask()">
          <span class="btn-text">
            <i class="ti ti-pencil me-1"></i>
            {{ $l == 'en' ? 'Update Task' : 'تحديث المهمة' }}
          </span>
          <span class="btn-spinner d-none">
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            {{ $l == 'en' ? 'Updating...' : 'جاري التحديث...' }}
          </span>
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

  $('.datepicker').flatpickr({
    monthSelectorType: 'static',
    dateFormat: 'Y-m-d',
    minDate: 'today'
  });

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
      showCancelButton: true,
      showConfirmButton: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
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
      showCancelButton: true,
      showConfirmButton: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
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
            releaseFailureAlert({
							title: "{{ __('admin.Error') }}!",
							message: error?.message || "{{ __('admin.DeleteFailed') }}",
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
            releaseFailureAlert({
              title: "{{ __('admin.Error') }}!",
              message: error?.message || "{{ __('admin.CancelFailed') }}",
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

  // File Upload Functions
  function uploadFile() {
    const fileInput = document.getElementById('fileInput');
    const fileDescription = document.getElementById('fileDescription') ? document.getElementById('fileDescription').value : '';
    const l = '{{ $l }}'; // "en" or "ar"

    if (!fileInput || fileInput.files.length === 0) {
      fileInput.classList.add('is-invalid');
      $('.file-err').show();
      $('.file-type-err').hide(); // Hide other errors if no file is selected
      $('.file-size-err').hide();
      return; // Exit the function immediately
    }
    
    const file = fileInput.files[0];

    // Allowed file types
    const allowedTypes = [
      'application/pdf', 
      'application/zip',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   // docx
    ];

    if (!allowedTypes.includes(file.type)) {
      fileInput.classList.add('is-invalid');
      $('.file-type-err').show();
      $('.file-err').hide(); // Hide other errors
      $('.file-size-err').hide();
      return; // Exit the function immediately
    }

    // Max size: 10 MB
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
      fileInput.classList.add('is-invalid');
      $('.file-size-err').show();
      $('.file-err').hide(); // Hide other errors
      $('.file-type-err').hide();
      return; // Exit the function immediately
    }

    // Reset error messages if all checks pass
    fileInput.classList.remove('is-invalid');
    $('.file-err').hide();
    $('.file-type-err').hide();
    $('.file-size-err').hide();

    // Build FormData
    const formData = new FormData();
    formData.append('file', file);
    formData.append('description', fileDescription);

    // URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const hackathonId = urlParams.get('hackathonId') || '1';
    const teamId = urlParams.get('teamId') || '123';
    formData.append('hackathon_id', hackathonId);
    formData.append('team_id', teamId);

    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

    // Helper function to choose icon
    const getIconClass = (name) => {
      if (name.endsWith('.zip')) return 'ti-file-zip text-warning';
      if (name.endsWith('.pptx')) return 'ti-file-type-ppt text-danger';
      if (name.endsWith('.docx')) return 'ti-file-text text-info';
      if (name.endsWith('.pdf')) return 'ti-file-type-pdf text-danger';
      if (name.match(/\.(jpe?g|png)$/i)) return 'ti-photo text-success';
      return 'ti-file-text';
    };

    const createListItem = (file) => {
      const fileList = document.getElementById('fileList');
      if (!fileList) return;
      const newItem = document.createElement('li');
      newItem.className = 'list-group-item d-flex justify-content-between align-items-center border-0 px-2 py-2 file-list-item';

      const iconClass = getIconClass(file.name);
      const uploadedByText = l === 'en' ? 'by You' : 'بواسطة أنت';

      newItem.innerHTML = `
        <div class="d-flex align-items-center">
          <i class="ti ${iconClass} me-2"></i>
          <div>
            <div class="small fw-medium">${file.name}</div>
            <div class="text-muted small">${(file.size / 1024).toFixed(1)}KB - ${uploadedByText}</div>
          </div>
        </div>
        <div class="btn-group btn-group-sm">
          <a href="#" class="btn btn-outline-primary btn-sm" onclick="downloadFile('${file.name}', '${file.name.replace(/\s+/g, '_')}')" title="${l === 'en' ? "Download" : "تنزيل"}">
            <i class="ti ti-download"></i>
          </a>
          <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(this, '${file.name}')" title="${l === 'en' ? "Delete" : "حذف"}">
            <i class="ti ti-trash"></i>
          </button>
        </div>`;
      fileList.prepend(newItem);
    };

    // Send request to the route
    fetch('/dashboard/hackathon/addSharedFile', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        releaseSuccessAlert({
          title: l === 'en' ? "File Uploaded!" : "تم رفع الملف!",
          message: l === 'en' ? "Your file has been uploaded successfully." : "تم رفع الملف بنجاح.",
        });
        createListItem(file);
      } else {
        releaseFailureAlert({
          title: l === 'en' ? "Error" : "خطأ",
          message: data.message || (l === 'en' ? "Upload failed" : "فشل رفع الملف"),
        });
      }
    })
    .catch(error => {
      releaseFailureAlert({
        title: l === 'en' ? "Error" : "خطأ",
        message: error.message || (l === 'en' ? "Upload failed" : "فشل رفع الملف"),
      });
    })
    .finally(() => {
      const uploadModalEl = document.getElementById('uploadFileModal');
      const uploadModal = uploadModalEl ? bootstrap.Modal.getInstance(uploadModalEl) : null;
      if (uploadModal) uploadModal.hide();

      const form = document.getElementById('fileUploadForm');
      if (form && typeof form.reset === 'function') form.reset();
    });
 
   
  }

  function deleteFile(buttonElement, fileId) {
    console.log('deleteFile called with:', buttonElement, fileId);

    if (typeof Swal === 'undefined') {
      console.error('SweetAlert2 is not loaded!');
      alert('Delete functionality not available - SweetAlert2 not loaded');
      return;
    }

    Swal.fire({
      title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
      text: '{{ $l == "en" ? "Do you want to delete this file?" : "هل تريد حذف هذا الملف؟" }}',
      icon: 'warning',
      confirmButtonText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفها!" }}',
      cancelButtonText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
      showCancelButton: true,
      showConfirmButton: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        fetch("{{ url('/dashboard/hackathon/deleteSharedFile') }}/" + fileId, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        .then(response => {
          if (!response.ok) throw new Error("Error deleting file");
          return response.json();
        })
        .then(data => {
          if (data.status === 'success') {
            // Remove element from page
            const fileElement = buttonElement.closest('li');
            if (fileElement) fileElement.remove();

            Swal.fire({
              title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
              text: '{{ $l == "en" ? "File has been deleted." : "تم حذف الملف." }}',
              icon: 'success',
              confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          } else {
            Swal.fire({
              title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
              text: data.message || '{{ $l == "en" ? "Could not delete file." : "تعذر حذف الملف." }}',
              icon: 'error',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
            text: '{{ $l == "en" ? "Could not delete file." : "تعذر حذف الملف." }}',
            icon: 'error',
            showCancelButton: false,
            showConfirmButton: true,
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
        });
      }
    });
  }

  // Task Management Functions
  function addTask() {
    const taskTitle = document.getElementById('taskTitle').value;
    const taskAssignee = document.getElementById('taskAssignee').value;
    const taskDueDate = document.getElementById('taskDueDate').value;
    const taskDescription = document.getElementById('taskDescription').value;
    const taskStatus = document.getElementById('taskStatus').value;

    if (!taskTitle) {
      Swal.fire({
        icon: 'warning',
        title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
        text: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
        showCancelButton: false,
        showConfirmButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
      return;
    }

    if (!taskAssignee) {
      Swal.fire({
        icon: 'warning',
        title: '{{ $l == "en" ? "Assignee Required" : "المسؤول مطلوب" }}',
        text: '{{ $l == "en" ? "Please select an assignee for the task." : "يرجى اختيار مسؤول للمهمة." }}',
        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
        showCancelButton: false,
        showConfirmButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
      return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Create FormData instead of JSON
    const formData = new FormData();
    formData.append('title', taskTitle);
    formData.append('assignee', taskAssignee);
    formData.append('due_date', taskDueDate);
    formData.append('description', taskDescription);
    formData.append('status', taskStatus);
    formData.append('_token', csrfToken);

    fetch('/dashboard/hackathon/AddtodoList', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success === true) {
        Swal.fire({
          icon: 'success',
          title: '{{ $l == "en" ? "Task Added!" : "تمت إضافة المهمة!" }}',
          text: data.message || '{{ $l == "en" ? "Task added successfully." : "تمت إضافة المهمة بنجاح." }}',
          confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
          showCancelButton: false,
          showConfirmButton: true,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        }).then(() => {
          window.location.reload();
        });

        const addTaskModal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
        addTaskModal.hide();
        document.getElementById('taskForm').reset();

      } else {
        Swal.fire({
          icon: 'error',
          title: '{{ $l == "en" ? "Error" : "خطأ" }}',
          text: data.message || '{{ $l == "en" ? "Something went wrong." : "حدث خطأ ما." }}',
          confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
          showCancelButton: false,
          showConfirmButton: true,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: '{{ $l == "en" ? "Error" : "خطأ" }}',
        text: '{{ $l == "en" ? "Failed to connect to the server." : "فشل الاتصال بالخادم." }}',
        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
        showCancelButton: false,
        showConfirmButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
    });
  }

  function editTask(taskId, taskTitle, taskAssigneeId, taskDueDate, taskDescription, taskStatus) {
    console.log('editTask called with:',taskAssigneeId);
    
    try {
      let currentUserId = {{ auth()->user()->id }};
      if (taskAssigneeId == currentUserId) {
          document.getElementById('EtaskAssignee').value = 'You';
      } else {
          document.getElementById('EtaskAssignee').value = taskAssigneeId || '';
      }
      // Pre-fill the modal with existing task data
      document.getElementById('EtaskTitle').value = taskTitle;
      // document.getElementById('EtaskAssignee').valsue = taskAssigneeId || 'You';
      document.getElementById('EtaskDueDate').value = taskDueDate || '';
      document.getElementById('EtaskDescription').value = taskDescription || '';
      document.getElementById('EtaskStatus').value = taskStatus || 'todo';
      
      // Store the task ID for updating
      document.getElementById('EditTaskModal').setAttribute('data-editing-task', taskId);
      
      // Show the modal
      const editModal = new bootstrap.Modal(document.getElementById('EditTaskModal'));
      editModal.show();
    } catch (error) {
      console.error('Error in editTask:', error);
    }
  }

  function updateTask() {
    const taskTitle = document.getElementById('EtaskTitle').value;
    const taskAssignee = document.getElementById('EtaskAssignee').value;
    const taskDueDate = document.getElementById('EtaskDueDate').value;
    const taskDescription = document.getElementById('EtaskDescription').value;
    const taskStatus = document.getElementById('EtaskStatus').value;

    const taskModal = document.getElementById('EditTaskModal');
    const editingTaskId = taskModal.getAttribute('data-editing-task');

    if (!taskTitle) {
      Swal.fire({
        icon: 'warning',
        title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
        text: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
        showCancelButton: false,
        showConfirmButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
      return;
    }

    if (!taskAssignee) {
      Swal.fire({
        icon: 'warning',
        title: '{{ $l == "en" ? "Assignee Required" : "المسؤول مطلوب" }}',
        text: '{{ $l == "en" ? "Please select an assignee for the task." : "يرجى اختيار مسؤول للمهمة." }}',
        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
        showCancelButton: false,
        showConfirmButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
      return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Create FormData instead of JSON
    const formData = new FormData();
    formData.append('id', editingTaskId);
    formData.append('title', taskTitle);
    formData.append('assignee', taskAssignee);
    formData.append('due_date', taskDueDate);
    formData.append('status', taskStatus);
    formData.append('description', taskDescription);
    formData.append('_token', csrfToken);

    console.log('Sending form data...');

    fetch('/dashboard/hackathon/EdittodoList', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
        // Don't set Content-Type for FormData, let browser set it
      },
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      
      if (data.success === true) {
        Swal.fire({
          icon: 'success',
          title: '{{ $l == "en" ? "Task Updated!" : "تم تعديل المهمة!" }}',
          text: data.message || '{{ $l == "en" ? "Task updated successfully." : "تم تعديل المهمة بنجاح." }}',
          confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
          showCancelButton: false,
          showConfirmButton: true,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        }).then(() => {
          window.location.reload();
        });

        const editTaskModal = bootstrap.Modal.getInstance(document.getElementById('EditTaskModal'));
        editTaskModal.hide();
        taskModal.removeAttribute('data-editing-task');

      } else {
        Swal.fire({
          icon: 'error',
          title: '{{ $l == "en" ? "Error" : "خطأ" }}',
          text: data.message || '{{ $l == "en" ? "Something went wrong." : "حدث خطأ ما." }}',
          confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
          showCancelButton: false,
          showConfirmButton: true,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        });
      }
    })
    .catch(error => {
      console.error('Fetch error details:', error);
      console.error('Error message:', error.message);
      
      Swal.fire({
        icon: 'error',
        title: '{{ $l == "en" ? "Connection Error" : "خطأ في الاتصال" }}',
        text: '{{ $l == "en" ? "Failed to connect to the server. Please check your internet connection and try again." : "فشل الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت والمحاولة مرة أخرى." }}',
        confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
        showCancelButton: false,
        showConfirmButton: true,
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
    });
  }

  function deleteTask(taskId, taskTitle) {
    console.log('deleteTask called with:', taskId, taskTitle);

    if (typeof Swal === 'undefined') {
      console.error('SweetAlert2 is not loaded!');
      alert('Delete functionality not available - SweetAlert2 not loaded');
      return;
    }

    Swal.fire({
      icon: 'warning',
      title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
      text: `{{ $l == "en" ? "Do you want to delete the task: " : "هل تريد حذف المهمة: " }}"${taskTitle}"?`,
      confirmButtonText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفها!" }}',
      cancelButtonText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
      showCancelButton: true,
      showConfirmButton: true,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        fetch("{{ url('/dashboard/hackathon/deleteTodoList') }}/" + taskId, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          console.log('data', data);
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
              text: data.message || '{{ $l == "en" ? "Task has been deleted." : "تم حذف المهمة." }}',
              confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            }).then(() => {
              // Remove deleted task
              $(`#task-${taskId}`).remove();
            });
          } else {
            Swal.fire({
              title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
              text: data.message || '{{ $l == "en" ? "Could not delete the task." : "تعذر حذف المهمة." }}',
              icon: 'error',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          }
        })
        .catch(error => {
          console.error(error);
          Swal.fire({
            title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
            text: '{{ $l == "en" ? "Could not delete the task." : "تعذر حذف المهمة." }}',
            icon: 'error',
            showCancelButton: false,
            showConfirmButton: true,
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
        });
      }
    });
  }

  function updateTaskStatus(taskId, isChecked) {
    console.log('updateTaskStatus called with:', taskId, isChecked);

    if (typeof Swal === 'undefined') {
      console.error('SweetAlert2 is not loaded!');
      alert('Update functionality not available - SweetAlert2 not loaded');
      return;
    }

    const status = isChecked ? 'DONE' : 'TODO';

    Swal.fire({
      title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
      text: `{{ $l == "en" ? "Do you want to mark the task as: " : "هل تريد وضع علامة على المهمة كـ: " }}${status}"?`,
      icon: 'warning',
      confirmButtonText: '{{ $l == "en" ? "Yes, update it!" : "نعم، قم بتحديثها!" }}',
      cancelButtonText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
      showCancelButton: true,
      showConfirmButton: true,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        fetch("{{ url('/dashboard/hackathon/EdittodoListStatus') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ status, id: taskId })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              title: '{{ $l == "en" ? "Updated!" : "تم التحديث!" }}',
              text: data.message || '{{ $l == "en" ? "Task status has been updated." : "تم تحديث حالة المهمة." }}',
              icon: 'success',
              confirmButtonText: '{{ $l == "en" ? "OK" : "موافق" }}',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            }).then(() => {
              // Get current task checkbox and label
              const currentTaskCheckbox = $(`#task${taskId}-checkbox`);
              const currentTaskLabel = currentTaskCheckbox.closest('.form-check').find('label');

              // Update checkbox and label styles
              currentTaskCheckbox.prop('checked', isChecked);
              currentTaskLabel.css('text-decoration', isChecked ? 'line-through' : 'none');
            });
          } else {
            Swal.fire({
              title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
              text: data.message || '{{ $l == "en" ? "Could not update the task." : "تعذر تحديث المهمة." }}',
              icon: 'error',
              showCancelButton: false,
              showConfirmButton: true,
              customClass: {
                confirmButton: 'btn btn-primary',
              },
              buttonsStyling: false,
            });
          }
        })
        .catch(error => {
          console.error(error);
          Swal.fire({
            title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
            text: '{{ $l == "en" ? "Could not update the task." : "تعذر تحديث المهمة." }}',
            icon: 'error',
            showCancelButton: false,
            showConfirmButton: true,
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
        });
      }
    });
  }

</script>
@stop