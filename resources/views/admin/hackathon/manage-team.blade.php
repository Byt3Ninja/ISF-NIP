@extends('layouts.admin')

@section('content')
@php
// View-model comes from HackathonController@manage. $isLeader, $canInvite, $occupied,
// $maxMembers, $remaining, $team, $teamLeaderRow, $joinRequests, $projectStatusMeta,
// $sharedFiles, $todoItems are all prepared there.
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
                    <span class="fw-semibold">{{ $team->tf_name }}</span>
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
        <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-1"></i>
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
                  <span id="team-name-display" class="text-primary fw-bold">{{ $team->tf_name }}</span>
                </h5>
                <small class="text-secondary">
                  {{ $l == 'en' ? 'Manage your team members and invitations' : 'إدارة أعضاء الفريق والدعوات' }}
                </small>
              </div>
              @if($isLeader)
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
                      <span id="seat-counter" class="fw-semibold">{{ $occupied }}/{{ $maxMembers }}</span>
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
                    <small class="text-muted">{{ $team_Pending->count() }} {{ $l == 'en' ? 'invitations' : 'دعوات' }}</small>
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
                      <span class="badge {{ $projectStatusMeta['class'] }}">{{ $projectStatusMeta['label'] }}</span>
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
              @php $team_leader = $teamLeaderRow; $T = 0; @endphp

              @if($team_leader)
              <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <img src="{{ '/191014/'.$team_leader->u_img_url ?? '/icons/account.svg' }}" alt="Avatar"
                      onerror="this.onerror=null;this.src='/icons/account.svg';" class="rounded-circle">
                  </div>
                  <div>
                    <h6 class="mb-0">
                      @if($isLeader)
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
                      {{ $member->u_email }}
                    </small>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <span class="badge bg-label-success rounded-pill me-2">
                    <i class="ti ti-check me-1"></i>
                    {{ $l == 'en' ? 'Active' : 'نشط' }}
                  </span>
                  @if($isLeader)
                  <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                    title="{{ $l == 'en' ? 'Remove Member (Leader only)' : 'إزالة العضو (متاح للقائد فقط)' }}"
                    onclick="confirmRemoveMember({{ $member->tm_id }}, '{{ $member->tm_name }}')">
                    <i class="ti ti-user-minus"></i>
                  </button>
                  @endif
                </div>
              </li>
              @endforeach
              @if($isLeader)
              @foreach ($team_Pending as $index => $pendingMember)
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
                      @if($pendingMember->tm_acceptInvition === 'failed')
                        {{ $l == 'en' ? 'Invitation email failed' : 'فشل إرسال بريد الدعوة' }}
                      @else
                        {{ $l == 'en' ? 'Pending invitation' : 'دعوة معلقة' }}
                      @endif
                    </small>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  @if($pendingMember->tm_acceptInvition === 'failed')
                  <span class="badge bg-label-danger rounded-pill me-2 pendingStatusBadge">
                    <i class="ti ti-alert-triangle me-1"></i>
                    {{ __('admin.invitation_failed_status') }}
                  </span>
                  <button class="btn btn-sm btn-outline-primary me-1 retryInviteBtn" data-id="{{ $pendingMember->tm_id }}"
                    data-bs-toggle="tooltip" title="{{ __('admin.retry_invitation') }}"
                    onclick="retryInvitation({{ $pendingMember->tm_id }})">
                    <i class="ti ti-refresh"></i>
                  </button>
                  @else
                  <span class="badge bg-label-warning rounded-pill me-2 pendingStatusBadge">
                    <i class="ti ti-clock me-1"></i>
                    {{ $l == 'en' ? 'Pending' : 'معلق' }}
                  </span>
                  @endif
                  <input type="hidden" class="pendingMemberId" value="{{ $pendingMember->tm_id }}">
                  @if($isLeader)
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
            <div id="invite-new-members" @if(!$canInvite) style="display:none" @endif>
            @if($isLeader)
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
                <form id="inviteByEmailForm" class="row g-3 align-items-end" method="POST" action="{{ route('hackathon.invitation') }}">
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
            @endif
            </div>
            <div id="team-complete-alert" @if($remaining > 0) style="display:none" @endif>
            <div class="alert alert-success d-flex align-items-center">
              <i class="ti ti-check-circle me-2"></i>
              <div>
                <strong>{{ $l == 'en' ? 'Team Complete!' : 'الفريق مكتمل!' }}</strong>
                <div class="small">{{ $l == 'en' ? 'Your team has reached the maximum number of members.' : 'وصل فريقك إلى العدد الأقصى من الأعضاء.' }}</div>
              </div>
            </div>
            </div>
            <!-- Join Requests Section -->
            @if($isLeader)
            @php $teamLeader = $joinRequests; @endphp
            
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
                    $imageSrc = ($team && $team->tf_img_team) ? route('hackathon.teamLogo', basename($team->tf_img_team)) : '/icons/team-default.svg';
                    @endphp
                    <img
                      src="{{ $imageSrc }}"
                      alt="{{ $team->tf_name }}"
                      class="rounded-circle w-100 h-100 object-fit-contain bg-light border-3 border-light shadow-sm"
                      onerror="this.onerror=null;this.src='/icons/team-default.svg';"
                      id="team-image-display"
                    >
                    @if($isLeader)
                    <div class="position-absolute bottom-0 end-0">
                      <button class="btn btn-sm btn-primary rounded-circle p-2" data-bs-toggle="modal" data-bs-target="#editTeamInfoModal"
                        data-bs-toggle="tooltip" title="{{ $l == 'en' ? 'Edit Team Image' : 'تعديل صورة الفريق' }}">
                        <i class="ti ti-edit"></i>
                      </button>
                    </div>
                    @endif
                  </div>
                  <h5 class="mb-1">{{ $team->tf_name }}</h5>
                  @if($team->tf_description)
                  <p class="text-muted small mb-3">{{ $team->tf_description }}</p>
                  @endif
                  
                  @if($isLeader)
                    {{-- @if (app()->environment('local', 'dev', 'test'))
                      <form method="POST" action="/dashboard/hackathon/deleteTeamInfo" id="deleteTeamForm">
                        <div class="modal-body">
                          @csrf
                          <input type="hidden" name="team_formation_id" value="{{ $team->tf_id }}">
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
                    href="/dashboard/hackathon/team/{{ $team->tf_id }}"
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
                      <h6 class="mb-0">{{ $team->tf_projectName_en }}</h6>
                    </div>

                    <div class="mb-4">
                      <label class="form-label small text-muted fw-semibold">
                        {{ $l == 'en' ? 'Project Description' : 'وصف المشروع' }}
                      </label>
                      <p class="small mb-0" id="team-project-idea">{{ $team->tf_projectDescription_en }}</p>
                    </div>

                    @if($isLeader)
                    <button class="btn btn-outline-info btn-sm w-100 mb-3" data-bs-toggle="modal"
                      data-bs-target="#editProjectIdeaModal">
                      <i class="ti ti-edit me-1"></i>
                      {{ $l == 'en' ? 'Edit Project Details' : 'تعديل تفاصيل المشروع' }}
                    </button>
                    @endif

                    <!-- Project Progress -->
                    <div class="mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small text-muted fw-semibold mb-0">
                          {{ $l == 'en' ? 'Project Progress' : 'تقدم المشروع' }}
                        </label>
                        <span class="badge bg-primary">{{ $team->tf_projectStatus }}%</span>
                      </div>
                      <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-gradient-primary" role="progressbar"
                          style="width:{{ $team->tf_projectStatus  }}%;"
                          aria-valuenow="{{ $team->tf_projectStatus  }}" aria-valuemin="0"
                          aria-valuemax="100">
                        </div>
                      </div>
                      <small class="text-muted">
                        <strong>{{ $projectStatusMeta['label'] }}</strong><br>
                        {{ $projectStatusMeta['description'] ?? '' }}
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
                        @if($sharedFiles->count() > 0)
                          @foreach($sharedFiles as $file)
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-2 py-2">
                                <div class="d-flex align-items-center" style="min-width: 0;">
                                    <i class="ti ti-file text-primary me-2"></i>
                                    <div class="flex-grow-1 me-2" style="min-width: 0;">
                                        <div class="small fw-medium text-truncate">
                                            {{ $file->display_name }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $file->size_readable }} - {{ $l == 'en' ? 'by ' . $file->{"user_name_". $l} : 'بواسطة ' . $file->{"user_name_". $l} }}
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm flex-shrink-0">
                                    <a href="{{ route('hackathon.downloadSharedFile', $file->sf_id) }}" onclick="downloadFile(event, {{ $file->sf_id }})" class="btn btn-outline-primary btn-sm" title="{{ $l == 'en' ? 'Download' : 'تنزيل' }}">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    @if($file->sf_user_id == $_user->id || (isset($file->tf_user_id) && $file->tf_user_id == $_user->id))
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(this, {{ $file->sf_id }})" title="{{ $l == 'en' ? 'Delete' : 'حذف' }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </li>
                          @endforeach
                        @endif
                        <li id="noFilesPlaceholder" class="list-group-item border-0 text-center py-4" @if($sharedFiles->count() > 0) style="display: none;" @endif>
                          <i class="ti ti-folder-open text-muted mb-2 d-block" style="font-size: 2rem;"></i>
                          <p class="text-muted mb-0">{{ $l == 'en' ? 'No files uploaded yet' : 'لم يتم رفع ملفات بعد' }}</p>
                        </li>
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
                        @if($todoItems->count() > 0)
                          @foreach($todoItems as $task)
                            <li class="list-group-item border-0 px-2 py-2" id="task-{{ $task->tl_id }}">
                              <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                  <div class="d-flex align-items-center mb-2">
                                    <span class="fw-medium me-2">{{ $task->tl_title }}</span>
                                    <span class="badge bg-label-{{ $task->status_meta['class'] }} rounded-pill py-1 px-2">
                                      <i class="ti {{ $task->status_meta['icon'] }} ti-16px me-1"></i>
                                      {{ $task->status_meta['text'] }}
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
@if($isLeader)
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
          <input type="hidden" name="team_formation_id" value="{{ $team->tf_id }}">
          
          <!-- Team Image Section -->
          <div class="row">
            <div class="col-md-4">
              <div class="text-center mb-4">
                <div class="avatar avatar-xl mx-auto mb-3 position-relative">
                  @php
                  $imageSrcModal = ($team && $team->tf_img_team) ? route('hackathon.teamLogo', basename($team->tf_img_team)) : '/icons/team-default.svg';
                  @endphp
                  <img
                    src="{{ $imageSrcModal }}"
                    alt="{{ $team->tf_name }}"
                    class="rounded-circle w-100 h-100 object-fit-contain bg-light border-3 border-light shadow"
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
                $hasImage = $team && $team->tf_img_team;
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
                  value="{{ $team->tf_name }}" 
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
                >{{ $team->tf_description ?? '' }}</textarea>
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
@endif

<!-- Edit Project Idea Modal -->
@if($isLeader)
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
          <input type="hidden" name="team_formation_id" value="{{ $team->tf_id }}">
          <div class="mb-3">
            <label for="Project_Name_en" class="form-label">
              {{ __('admin.Project_Name') }}
            </label>
            <input 
              type="text" 
              class="form-control" 
              name="Project_Name_en"
              value="{{ $team->tf_projectName_en }}" 
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
            >{{ $team->tf_projectDescription_en }}</textarea>
          </div>

          <div class="mb-3">
            <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Project Status' : 'حالة المشروع' }}</label>
            <select class="form-select" id="Project_Status" name="Project_Status">
              <option value="">{{ $l == 'en' ? 'Choose v...' : 'اختر الحالة...' }}</option>
              <option value="20" {{ $team->tf_projectStatus == '20' ? 'selected' : '' }}>
                {{ __('admin.Idea + Team') }}
              </option>
              <option value="40" {{ $team->tf_projectStatus == '40' ? 'selected' : '' }}>
                {{ __('admin.Research') }}
              </option>
              <option value="60" {{ $team->tf_projectStatus == '60' ? 'selected' : '' }}>
                {{ __('admin.Design & Build') }}
              </option>
              <option value="80" {{ $team->tf_projectStatus == '80' ? 'selected' : '' }}>
                {{ __('admin.Ready for Pitch') }}
              </option>
              <option value="100" {{ $team->tf_projectStatus == '100' ? 'selected' : '' }}>
                {{ __('admin.Project Submitted') }}
              </option>
            </select>
          </div>


          {{-- <div class="mb-3">
            <label for="Region" class="form-label">{{ $l == 'en' ? 'Region' : 'المنطقة' }}</label>
            <select class="form-select" id="Region" name="Region">
              <option value="">{{ $l == 'en' ? 'Choose Region...' : 'اختر المنطقة...' }}</option>
              <option value="AL_Madinah_AL_Munawwarah" {{ $team->tf_region == 'AL_Madinah_AL_Munawwarah' ? 'selected' : '' }}>
                {{ __('admin.AL_Madinah_AL_Munawwarah') }}
              </option>
              <option value="Al_Ahsa" {{ $team->tf_region == 'Al_Ahsa' ? 'selected' : '' }}>
                {{ __('admin.Al_Ahsa') }}
              </option>
              <option value="Asir" {{ $team->tf_region == 'Asir' ? 'selected' : '' }}>
                {{ __('admin.Asir') }}
              </option>
             
            </select>
          </div> --}}


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
@endif

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
                <div class="card h-100 border {{ $member['isInvited'] ? 'border-warning' : 'border-light' }} shadow-sm invite-member-card" data-member-card-id="{{ $member['id'] }}">
                  <div class="card-body text-center">
                    <div class="avatar avatar-xl mx-auto mb-3 position-relative">
                      <img
                        src="{{ '/191014/'.$member['image'] ?? '/icons/account.svg' }}"
                        onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                        alt="{{ $member['name'] }}"
                        class="rounded-circle w-100 h-100 border-2 border-light"
                      />
                      <div class="position-absolute top-0 end-0 invite-pending-badge {{ $member['isInvited'] ? '' : 'd-none' }}">
                        <span class="badge bg-warning rounded-pill">
                          <i class="ti ti-clock"></i>
                        </span>
                      </div>
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
                    
                    @php
                      $locationParts = collect([$member['city'] ?? null, $member['state'] ?? null, $member['country'] ?? null])
                        ->map(fn ($part) => is_string($part) ? trim($part) : $part)
                        ->reject(fn ($part) => $part === null || $part === '' || mb_strtolower((string) $part) === 'n/a')
                        ->values();
                    @endphp
                    <p class="text-muted small mb-3">
                      <i class="ti ti-map-pin me-1"></i>
                      {{ $locationParts->isEmpty() ? ($l == 'ar' ? 'غير متاح' : 'Not available') : $locationParts->implode(', ') }}
                    </p>
                    
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
            <div class="invalid-feedback file-type-err" style="display: none;">{{ $l == 'en' ? 'Allowed: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR' : 'المسموح: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR' }}</div>
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
        <button type="button" class="btn btn-primary" id="uploadFileBtn" onclick="uploadFile()">
          <span class="btn-text" id="uploadFileBtnText">
            <i class="ti ti-cloud-upload me-1"></i>
            {{ $l == 'en' ? 'Upload' : 'رفع' }}
          </span>
          <span class="btn-spinner d-none" id="uploadFileBtnSpinner">
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
  var is_upload_in_progress = false;
  const HACKATHON_ID_TEAM_PAGE = new URLSearchParams(window.location.search).get('hackathonId') || '1';
  const HACKATHON_NAME = "{{ $l == 'en' ? 'Future of AI Hackathon' : 'هاكثون مستقبل الذكاء الاصطناعي' }}";
  const IS_TEAM_LEADER = true;

  const inviteUrl = "{{ route('hackathon.invitation') }}";
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const LANG = '{{ $l }}';

  async function postInvitation(payload) {
    const formData = new FormData();
    formData.append('_token', csrfToken);
    Object.keys(payload).forEach(k => formData.append(k, payload[k]));
    const res = await fetch(inviteUrl, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: formData
    });
    // Always read the JSON body, even on 422 (it carries success:false + code).
    return res.json();
  }

  function updateSeatUI(data) {
    if (typeof data.occupied === 'undefined') return;
    const counter = document.getElementById('seat-counter');
    if (counter) counter.textContent = data.occupied + '/' + data.max;
    const inviteBox = document.getElementById('invite-new-members');
    const completeBox = document.getElementById('team-complete-alert');
    if (data.remaining <= 0) {
      if (inviteBox) inviteBox.style.display = 'none';
      if (completeBox) completeBox.style.display = '';
    } else {
      if (inviteBox) inviteBox.style.display = '';
      if (completeBox) completeBox.style.display = 'none';
    }
  }

  function appendPendingRow(member) {
    const list = document.getElementById('team-members-list');
    if (!list || document.getElementById('pendingMember' + member.id)) return;
    const pendingText = LANG === 'en' ? 'Pending invitation' : 'دعوة معلقة';
    const pendingBadge = LANG === 'en' ? 'Pending' : 'معلق';
    const cancelTitle = LANG === 'en' ? 'Cancel Invitation' : 'إلغاء الدعوة';
    const li = document.createElement('li');
    li.id = 'pendingMember' + member.id;
    li.className = 'list-group-item d-flex justify-content-between align-items-center border-0 px-0';
    li.innerHTML =
      '<div class="d-flex align-items-center">' +
        '<div class="avatar avatar-sm me-3"><div class="avatar-initial bg-label-warning rounded-circle"><i class="ti ti-clock"></i></div></div>' +
        '<div><h6 class="mb-0 text-muted"></h6><small class="text-muted">' + pendingText + '</small></div>' +
      '</div>' +
      '<div class="d-flex align-items-center">' +
        '<span class="badge bg-label-warning rounded-pill me-2"><i class="ti ti-clock me-1"></i>' + pendingBadge + '</span>' +
        '<input type="hidden" class="pendingMemberId" value="' + member.id + '">' +
        '<button class="btn btn-sm btn-outline-danger" data-id="' + member.id + '" title="' + cancelTitle + '"><i class="ti ti-x"></i></button>' +
      '</div>';
    // Set email as text (avoid HTML injection via email string).
    li.querySelector('h6').textContent = member.email;
    li.querySelector('button').addEventListener('click', function () {
      confirmCancelInvitation(member.id, member.email);
    });
    list.appendChild(li);
  }

  function applyInviteResult(data) {
    if (data.member) {
      appendPendingRow(data.member);
      // Reflect the invited state on the matching Available Members card, so an
      // invite sent via the email field also disables that member's card button
      // (its user_id, when the email belongs to a registered member).
      if (data.member.user_id) markInviteCard(data.member.user_id);
    }
    updateSeatUI(data);
  }

  // Revert an Available Members card button back to "Send Invitation" (mirror
  // of the flip in sendInvitationToMember) after its pending invite is cancelled.
  function resetInviteCard(memberId) {
    if (!memberId) return;
    const button = $(`.invite-member-btn[data-member-id="${memberId}"]`);
    if (!button.length) return;
    button.removeClass('btn-warning').addClass('btn-primary').prop('disabled', false);
    button.find('.btn-text').html('<i class="ti ti-send me-1"></i>' +
      (LANG === 'en' ? 'Send Invitation' : 'إرسال دعوة'));
    // Clear the invited-state visuals on the card (border + pending clock badge).
    const card = $(`.invite-member-card[data-member-card-id="${memberId}"]`);
    card.removeClass('border-warning').addClass('border-light');
    card.find('.invite-pending-badge').addClass('d-none');
  }

  // Apply the invited state on a card (button + border + pending badge), the
  // exact counterpart to resetInviteCard, so invite and cancel stay symmetric.
  function markInviteCard(memberId) {
    if (!memberId) return;
    const button = $(`.invite-member-btn[data-member-id="${memberId}"]`);
    if (button.length) {
      button.removeClass('btn-primary').addClass('btn-warning').prop('disabled', true);
      button.find('.btn-spinner').addClass('d-none');
      button.find('.btn-text').removeClass('d-none').html('<i class="ti ti-check me-1"></i>' +
        (LANG === 'en' ? 'Invitation Sent' : 'تم إرسال الدعوة'));
    }
    const card = $(`.invite-member-card[data-member-card-id="${memberId}"]`);
    card.removeClass('border-light').addClass('border-warning');
    card.find('.invite-pending-badge').removeClass('d-none');
  }

  function showInviteError(data) {
    Swal.fire({
      icon: data.code === 'already_invited' ? 'info' : 'error',
      title: LANG === 'en' ? 'Notice' : 'تنبيه',
      text: data.message || (LANG === 'en' ? 'There was an error sending the invitation.' : 'حدث خطأ أثناء إرسال الدعوة.'),
      confirmButtonText: LANG === 'en' ? 'OK' : 'موافق',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false,
    });
    updateSeatUI(data);
  }

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

    $(document).on('submit', '#inviteByEmailForm', function (e) {
      e.preventDefault();
      const form = this;
      const email = form.querySelector('#memberEmail').value.trim();
      if (!email) return;
      const btn = $('#sendInvitationBtn');
      btn.prop('disabled', true);
      btn.find('.btn-text').addClass('d-none');
      btn.find('.btn-spinner').removeClass('d-none');

      postInvitation({ memberEmail: email })
        .then(data => {
          if (data.success) {
            form.reset();
            applyInviteResult(data);
            releaseAlert('success', {
              title: LANG === 'en' ? 'Invitation Sent!' : 'تم إرسال الدعوة!',
              message: data.message,
              confirmText: LANG === 'en' ? 'OK' : 'موافق',
              confirmColor: 'primary'
            });
          } else {
            showInviteError(data);
          }
        })
        .catch(() => showInviteError({}))
        .finally(() => {
          btn.prop('disabled', false);
          btn.find('.btn-text').removeClass('d-none');
          btn.find('.btn-spinner').addClass('d-none');
        });
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
          releaseAlert('success', {
            title: '{{ $l == 'en' ? 'Project Updated!' : 'تم تحديث المشروع!' }}',
            message: '{{ $l == 'en' ? 'Project idea updated successfully.' : 'تم تحديث فكرة المشروع بنجاح.' }}',
            confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            confirmColor: 'primary'
          }).then(() => {
            window.location.reload();
          });
        })
        .fail(function(error) {
          releaseAlert('danger', {
            title: '{{ $l == 'en' ? 'Error!' : 'خطأ!' }}',
            message: '{{ $l == 'en' ? 'There was an error updating the project.' : 'حدث خطأ أثناء تحديث المشروع.' }}',
            confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            confirmColor: 'primary'
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
          
          releaseAlert('success', {
            title: '{{ $l == 'en' ? 'Team Info Updated!' : 'تم تحديث معلومات الفريق!' }}',
            message: '{{ $l == 'en' ? 'Team information updated successfully.' : 'تم تحديث معلومات الفريق بنجاح.' }}',
            confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            confirmColor: 'primary'
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
          releaseAlert('danger', {
            title: '{{ $l == 'en' ? 'Error!' : 'خطأ!' }}',
            message: error?.message || '{{ $l == 'en' ? 'There was an error updating the team information.' : 'حدث خطأ أثناء تحديث معلومات الفريق.' }}',
            confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            confirmColor: 'primary'
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
    
    releaseAlert('success', {
      title: '{{ $l == 'en' ? 'Project Idea Saved!' : 'تم حفظ فكرة المشروع!' }}',
      message: '{{ $l == 'en' ? 'Project idea saved successfully.' : 'تم حفظ فكرة المشروع بنجاح.' }}',
      confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
      confirmColor: 'primary'
    });
  }

  function confirmLeaveTeam() {
    releaseAlert('warning', {
      title: '{{ $l == 'en' ? 'Are you sure?' : 'هل أنت متأكد؟' }}',
      message: '{{ $l == 'en' ? 'Are you sure you want to leave this team?' : 'هل أنت متأكد أنك تريد مغادرة هذا الفريق؟' }}',
      confirmText: '{{ $l == 'en' ? 'Yes,
      cancelText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        releaseAlert('success', {
          title: '{{ $l == 'en' ? 'Team Left!' : 'تم مغادرة الفريق!' }}',
          message: '{{ $l == 'en' ? 'You have left the team.' : 'لقد غادرت الفريق.' }}',
          confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
          confirmColor: 'primary'
        }).then(() => {
          window.location.href = `/dashboard/hackathon/details?id=${HACKATHON_ID_TEAM_PAGE}&applied=true`;
        });
      }
    });
  }

  function confirmRemoveMember(id, memberName) {
    var url = '{{ route('hackathon.getOutTeam') }}';
    var token = '{{ csrf_token() }}';

    releaseAlert('warning', {
      title: '{{ $l == 'en' ? 'Remove Team Member?' : 'إزالة عضو الفريق؟' }}',
      message: `{{ $l == 'en' ? 'Are you sure you want to remove' : 'هل أنت متأكد أنك تريد إزالة' }} ${memberName} {{ $l == 'en' ? 'from the team?' : 'من الفريق؟' }}`,
      confirmText: '{{ $l == 'en' ? 'Yes,
      cancelText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        $.post(url, {'_method': 'POST' , '_token': token, 'id': id},
          function(response) {
            releaseAlert('success', {
              title: '{{ $l == 'en' ? 'Member Removed!' : 'تم إزالة العضو!' }}',
              message: `${memberName} {{ $l == 'en' ? 'has been removed from the team.' : 'تم إزالته من الفريق.' }}`,
              confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
              confirmColor: 'primary'
            }).then(() => {
              $('#Member'+id).remove();
              const currentCount = parseInt($('#member-count').text());
              $('#member-count').text(currentCount - 1);
            });
          }).fail(function(error) { 
            releaseAlert('danger', {
							title: "{{ __('admin.Error') }}!",
							message: error?.message || "{{ __('admin.DeleteFailed') }}",
						});
          });
      }
    });
  }

  function retryInvitation(id) {
    var url = '{{ route('hackathon.retryInvitation') }}';
    var token = '{{ csrf_token() }}';
    var row = document.getElementById('pendingMember' + id);
    var btn = row ? row.querySelector('.retryInviteBtn') : null;
    // Dismiss the Retry button's hover tooltip so it doesn't linger after the click.
    dismissTooltips(btn);
    if (btn) { btn.disabled = true; }

    $.post(url, {'_method': 'POST', '_token': token, 'id': id})
      .done(function(data) {
        if (data.success) {
          if (row) {
            var badge = row.querySelector('.pendingStatusBadge');
            if (badge) {
              badge.className = 'badge bg-label-warning rounded-pill me-2 pendingStatusBadge';
              badge.innerHTML = '<i class="ti ti-clock me-1"></i>' + '{{ $l == 'en' ? 'Pending' : 'معلق' }}';
            }
            if (btn) { btn.remove(); }
          }
          releaseAlert('success', {
            title: '{{ $l == 'en' ? 'Invitation Resent!' : 'تم إعادة إرسال الدعوة!' }}',
            message: data.message,
            confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            confirmColor: 'primary'
          });
        } else {
          if (btn) { btn.disabled = false; }
          releaseAlert('danger', {
            title: "{{ __('admin.Error') }}!",
            message: data.message || "{{ __('admin.retry_invitation') }}",
            confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            confirmColor: 'primary'
          });
        }
      }).fail(function(error) {
        if (btn) { btn.disabled = false; }
        releaseAlert('danger', {
          title: "{{ __('admin.Error') }}!",
          message: error?.responseJSON?.message || "{{ __('admin.retry_invitation') }}",
        });
      });
  }

  // Bootstrap keeps a tooltip's popup on <body>; if the trigger loses hover to a
  // SweetAlert dialog (or the trigger's row is later removed) the popup is
  // orphaned and lingers on screen. Hide the active tooltip and sweep any strays.
  function dismissTooltips(el) {
    if (el) { try { $(el).tooltip('hide'); } catch (e) {} }
    document.querySelectorAll('.tooltip.show, .bs-tooltip-auto, [id^="tooltip"]').forEach(function (t) {
      if (t.classList.contains('tooltip')) t.remove();
    });
  }

  function confirmCancelInvitation(id, email) {
    var url = '{{ route('hackathon.Cancelinvitation') }}';
    var token = '{{ csrf_token() }}';

    // Dismiss the Cancel button's hover tooltip before the dialog opens.
    dismissTooltips(document.querySelector('#pendingMember' + id + ' [data-bs-toggle="tooltip"]'));

    releaseAlert('warning', {
      title: '{{ $l == 'en' ? 'Cancel Invitation?' : 'إلغاء الدعوة؟' }}',
      message: `{{ $l == 'en' ? 'Are you sure you want to cancel the invitation sent to' : 'هل أنت متأكد أنك تريد إلغاء الدعوة المرسلة إلى' }} ${email}{{ $l == 'en' ? '?' : '؟' }}`,
      confirmText: '{{ $l == 'en' ? 'Yes,
      cancelText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      confirmColor: 'warning'
    }).then((ok) => {
      if (ok) {
        $.post(url, {'_method': 'POST' , '_token': token, 'id': id})
          .done(function(data) {
            if (data.success) {
              const row = document.getElementById('pendingMember' + id);
              if (row) row.remove();
              dismissTooltips(); // clear any tooltip orphaned by the removed row
              updateSeatUI(data);
              resetInviteCard(data.member_id);
              releaseAlert('success', {
                title: '{{ $l == 'en' ? 'Invitation Cancelled!' : 'تم إلغاء الدعوة!' }}',
                message: `{{ $l == 'en' ? 'The invitation to' : 'الدعوة إلى' }} ${email} {{ $l == 'en' ? 'has been cancelled.' : 'تم إلغاؤها.' }}`,
                confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
                confirmColor: 'primary'
              });
            } else {
              releaseAlert('danger', {
                title: "{{ __('admin.Error') }}!",
                message: data.message || "{{ __('admin.CancelFailed') }}",
              });
            }
          }).fail(function(error) {
            console.log(error);
            releaseAlert('danger', {
              title: "{{ __('admin.Error') }}!",
              message: error?.message || "{{ __('admin.CancelFailed') }}",
            });
          });
      }
    });
  }

  function confirmDisbandTeam() {
    releaseAlert('warning', {
      title: '{{ $l == 'en' ? 'Are you sure?' : 'هل أنت متأكد؟' }}',
      message: '{{ $l == 'en' ? 'Are you sure you want to disband this team? This action cannot be undone and will remove all members.' : 'هل أنت متأكد أنك تريد حل هذا الفريق؟ هذا الإجراء لا يمكن التراجع عنه وسيؤدي إلى إزالة جميع الأعضاء.' }}',
      confirmText: '{{ $l == 'en' ? 'Yes,
      cancelText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        releaseAlert('success', {
          title: '{{ $l == 'en' ? 'Team Disbanded!' : 'تم حل الفريق!' }}',
          message: '{{ $l == 'en' ? 'Team has been disbanded successfully.' : 'تم حل الفريق بنجاح.' }}',
          confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
          confirmColor: 'primary'
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
    // Prefer the button's live DOM state over the baked-in isInvited argument,
    // so a card reset (after cancelling its invite) makes it invitable again.
    const cardBtn = $(`.invite-member-btn[data-member-id="${id}"]`);
    if (cardBtn.length) {
      isInvited = cardBtn.hasClass('btn-warning') || cardBtn.prop('disabled');
    }

    if (isInvited) {
      releaseAlert('info', {
        title: '{{ $l == 'en' ? 'Invitation Already Sent' : 'تم إرسال الدعوة بالفعل' }}',
        message: `{{ $l == 'en' ? 'An invitation has already been sent to' : 'تم إرسال دعوة بالفعل إلى' }} ${name} (${email}).`,
        confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
        confirmColor: 'primary'
      });
      return;
    }

    releaseAlert('info', {
      title: '{{ $l == 'en' ? 'Send Invitation?' : 'إرسال دعوة؟' }}',
      message: `{{ $l == 'en' ? 'Send invitation to' : 'إرسال دعوة إلى' }} ${name} (${email}){{ $l == 'en' ? '?' : '؟' }}`,
      confirmText: '{{ $l == 'en' ? 'Yes,
      cancelText: '{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}',
      confirmColor: 'primary',
      hideCancel: false
    }).then((ok) => {
      if (ok) {
        const button = $(`.invite-member-btn[data-member-id="${id}"]`);
        button.prop('disabled', true);
        button.find('.btn-text').addClass('d-none');
        button.find('.btn-spinner').removeClass('d-none');

        postInvitation({ user_id: id, memberEmail: email })
          .then(data => {
            if (data.success) {
              // applyInviteResult() flips this card (button + border + badge) to
              // the invited state via markInviteCard(data.member.user_id).
              applyInviteResult(data);
              releaseAlert('success', {
                title: LANG === 'en' ? 'Invitation Sent!' : 'تم إرسال الدعوة!',
                message: `${LANG === 'en' ? 'Invitation sent to' : 'تم إرسال دعوة إلى'} ${name}.`,
                confirmText: LANG === 'en' ? 'OK' : 'موافق',
                confirmColor: 'primary'
              });
              button.prop('disabled', true); // keep disabled after success
            } else {
              showInviteError(data);
              button.prop('disabled', false);
            }
          })
          .catch(() => {
            showInviteError({});
            button.prop('disabled', false);
          })
          .finally(() => {
            button.find('.btn-spinner').addClass('d-none');
            button.find('.btn-text').removeClass('d-none');
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
  function downloadFile(event, fileId) {
    if (event && typeof event.preventDefault === 'function') event.preventDefault();
    const l = '{{ $l }}';

    fetch(`/dashboard/hackathon/sharedFile/${fileId}/download`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
    .then(response => {
      if (!response.ok) throw new Error('download-failed');
      const disposition = response.headers.get('Content-Disposition') || '';
      const match = disposition.match(/filename\*?=(?:UTF-8''|")?([^";]+)/i);
      const name = match ? decodeURIComponent(match[1].replace(/"/g, '')) : 'download';
      return response.blob().then(blob => ({ blob, name }));
    })
    .then(({ blob, name }) => {
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = name;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    })
    .catch(() => {
      if (typeof releaseAlert === 'function') {
        releaseAlert('danger', {
          title: l === 'en' ? 'Error' : 'خطأ',
          message: l === 'en' ? 'File download failed. Please try again.' : 'فشل تحميل الملف. يرجى المحاولة مرة أخرى.',
        });
      } else if (typeof Swal !== 'undefined') {
        releaseAlert('danger', {
          title: l === 'en' ? 'Error' : 'خطأ',
          message: l === 'en' ? 'File download failed. Please try again.' : 'فشل تحميل الملف. يرجى المحاولة مرة أخرى.'
        });
      }
    });

    return false;
  }

  function uploadFile() {
    if( is_upload_in_progress == true ) { return; }
    const fileInput = document.getElementById('fileInput');
    const fileDescription = document.getElementById('fileDescription') ? document.getElementById('fileDescription').value : '';
    const l = '{{ $l }}'; // "en" or "ar"
    const uploadFileBtnText = document.getElementById('uploadFileBtnText');
    const uploadFileBtnSpinner = document.getElementById('uploadFileBtnSpinner');

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
      'application/x-zip-compressed',
      'application/vnd.rar',
      'application/x-rar-compressed',
      'application/x-compressed',
      'application/vnd.ms-powerpoint',
      'application/msword',
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

    const createListItem = (file, record) => {
      const fileList = document.getElementById('fileList');
      if (!fileList) return;
      const newItem = document.createElement('li');
      newItem.className = 'list-group-item d-flex justify-content-between align-items-center border-0 px-2 py-2 file-list-item';

      const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const iconClass = getIconClass(file.name);
      const uploadedByText = l === 'en' ? 'by You' : 'بواسطة أنت';
      const displayName = escapeHtml(record.original_name || file.name);

      newItem.innerHTML = `
        <div class="d-flex align-items-center">
          <i class="ti ${iconClass} me-2"></i>
          <div>
            <div class="small fw-medium">${displayName}</div>
            <div class="text-muted small">${(file.size / 1024).toFixed(1)}KB - ${uploadedByText}</div>
          </div>
        </div>
        <div class="btn-group btn-group-sm">
          <a href="/dashboard/hackathon/sharedFile/${record.id}/download" onclick="downloadFile(event, ${record.id})" class="btn btn-outline-primary btn-sm" title="${l === 'en' ? "Download" : "تنزيل"}">
            <i class="ti ti-download"></i>
          </a>
          <button class="btn btn-outline-danger btn-sm" onclick="deleteFile(this, ${record.id})" title="${l === 'en' ? "Delete" : "حذف"}">
            <i class="ti ti-trash"></i>
          </button>
        </div>`;
      fileList.prepend(newItem);

      // Hide the "No files uploaded yet" placeholder now that a file exists
      const placeholder = document.getElementById('noFilesPlaceholder');
      if (placeholder) placeholder.style.display = 'none';
    };

    uploadFileBtnText.classList.add('d-none');
    uploadFileBtnSpinner.classList.remove('d-none');
    is_upload_in_progress = true;
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
      uploadFileBtnText.classList.remove('d-none');
      uploadFileBtnSpinner.classList.add('d-none');
      is_upload_in_progress = false;
      if (data.status === 'success') {
        releaseAlert('success', {
          title: l === 'en' ? "File Uploaded!" : "تم رفع الملف!",
          message: l === 'en' ? "Your file has been uploaded successfully." : "تم رفع الملف بنجاح.",
        });
        createListItem(file, data);
      } else {
        releaseAlert('danger', {
          title: l === 'en' ? "Error" : "خطأ",
          message: data.message || (l === 'en' ? "Upload failed" : "فشل رفع الملف"),
        });
      }
    })
    .catch(error => {
      uploadFileBtnText.classList.remove('d-none');
      uploadFileBtnSpinner.classList.add('d-none');
      is_upload_in_progress = false;
      releaseAlert('danger', {
        title: l === 'en' ? "Error" : "خطأ",
        message: error.message || (l === 'en' ? "Upload failed" : "فشل رفع الملف"),
      });
    })
    .finally(() => {
      is_upload_in_progress = false;
      uploadFileBtnText.classList.remove('d-none');
      uploadFileBtnSpinner.classList.add('d-none');
      const uploadModalEl = document.getElementById('uploadFileModal');
      const uploadModal = uploadModalEl ? bootstrap.Modal.getInstance(uploadModalEl) : null;
      if (uploadModal) uploadModal.hide();

      const form = document.getElementById('fileUploadForm');
      if (form && typeof form.reset === 'function') form.reset();
    });
 
   
  }

  const uploadModal = document.getElementById('uploadFileModal');
  // Fires IMMEDIATELY when the modal starts opening / rendering
  uploadModal.addEventListener('show.bs.modal', function (event) {
    $('.file-err').hide();
    $('.file-type-err').hide();
    $('.file-size-err').hide();
    const fileInput = document.getElementById('fileInput');
    fileInput.classList.remove('is-invalid');
  });

  function deleteFile(buttonElement, fileId) {
    console.log('deleteFile called with:', buttonElement, fileId);

    if (typeof Swal === 'undefined') {
      console.error('SweetAlert2 is not loaded!');
      alert('Delete functionality not available - SweetAlert2 not loaded');
      return;
    }

    releaseAlert('warning', {
      title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
      message: '{{ $l == "en" ? "Do you want to delete this file?" : "هل تريد حذف هذا الملف؟" }}',
      confirmText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفها!" }}',
      cancelText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
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

            // Restore the "No files uploaded yet" placeholder if no real files remain
            const fileList = document.getElementById('fileList');
            const placeholder = document.getElementById('noFilesPlaceholder');
            if (fileList && placeholder && !fileList.querySelector('li:not(#noFilesPlaceholder)')) {
              placeholder.style.display = '';
            }

            releaseAlert('success', {
              title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
              message: '{{ $l == "en" ? "File removed successfully." : "تم حذف الملف بنجاح." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
              confirmColor: 'primary'
            });
          } else {
            releaseAlert('danger', {
              title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
              message: data.message || '{{ $l == "en" ? "Could not delete file." : "تعذر حذف الملف." }}',
              confirmColor: 'primary'
            });
          }
        })
        .catch(err => {
          console.error(err);
          releaseAlert('danger', {
            title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
            message: '{{ $l == "en" ? "Could not delete file." : "تعذر حذف الملف." }}',
            confirmColor: 'primary'
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
      releaseAlert('warning', {
        title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
        message: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
        confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
        confirmColor: 'primary',
        hideCancel: true
      });
      return;
    }

    if (!taskAssignee) {
      releaseAlert('warning', {
        title: '{{ $l == "en" ? "Assignee Required" : "المسؤول مطلوب" }}',
        message: '{{ $l == "en" ? "Please select an assignee for the task." : "يرجى اختيار مسؤول للمهمة." }}',
        confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
        confirmColor: 'primary',
        hideCancel: true
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
        releaseAlert('success', {
          title: '{{ $l == "en" ? "Task Added!" : "تمت إضافة المهمة!" }}',
          message: data.message || '{{ $l == "en" ? "Task added successfully." : "تمت إضافة المهمة بنجاح." }}',
          confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
          confirmColor: 'primary'
        }).then(() => {
          window.location.reload();
        });

        const addTaskModal = bootstrap.Modal.getInstance(document.getElementById('addTaskModal'));
        addTaskModal.hide();
        document.getElementById('taskForm').reset();

      } else {
        releaseAlert('danger', {
          title: '{{ $l == "en" ? "Error" : "خطأ" }}',
          message: data.message || '{{ $l == "en" ? "Something went wrong." : "حدث خطأ ما." }}',
          confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
          confirmColor: 'primary'
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      releaseAlert('danger', {
        title: '{{ $l == "en" ? "Error" : "خطأ" }}',
        message: '{{ $l == "en" ? "Failed to connect to the server." : "فشل الاتصال بالخادم." }}',
        confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
        confirmColor: 'primary'
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
      releaseAlert('warning', {
        title: '{{ $l == "en" ? "Task Title Required" : "عنوان المهمة مطلوب" }}',
        message: '{{ $l == "en" ? "Please enter a task title." : "يرجى إدخال عنوان للمهمة." }}',
        confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
        confirmColor: 'primary',
        hideCancel: true
      });
      return;
    }

    if (!taskAssignee) {
      releaseAlert('warning', {
        title: '{{ $l == "en" ? "Assignee Required" : "المسؤول مطلوب" }}',
        message: '{{ $l == "en" ? "Please select an assignee for the task." : "يرجى اختيار مسؤول للمهمة." }}',
        confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
        confirmColor: 'primary',
        hideCancel: true
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
        releaseAlert('success', {
          title: '{{ $l == "en" ? "Task Updated!" : "تم تعديل المهمة!" }}',
          message: data.message || '{{ $l == "en" ? "Task updated successfully." : "تم تعديل المهمة بنجاح." }}',
          confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
          confirmColor: 'primary'
        }).then(() => {
          window.location.reload();
        });

        const editTaskModal = bootstrap.Modal.getInstance(document.getElementById('EditTaskModal'));
        editTaskModal.hide();
        taskModal.removeAttribute('data-editing-task');

      } else {
        releaseAlert('danger', {
          title: '{{ $l == "en" ? "Error" : "خطأ" }}',
          message: data.message || '{{ $l == "en" ? "Something went wrong." : "حدث خطأ ما." }}',
          confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
          confirmColor: 'primary'
        });
      }
    })
    .catch(error => {
      console.error('Fetch error details:', error);
      console.error('Error message:', error.message);
      
      releaseAlert('danger', {
        title: '{{ $l == "en" ? "Connection Error" : "خطأ في الاتصال" }}',
        message: '{{ $l == "en" ? "Failed to connect to the server. Please check your internet connection and try again." : "فشل الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت والمحاولة مرة أخرى." }}',
        confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
        confirmColor: 'primary'
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

    releaseAlert('warning', {
      title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
      message: `{{ $l == "en" ? "Do you want to delete the task: " : "هل تريد حذف المهمة: " }}"${taskTitle}"?`,
      confirmText: '{{ $l == "en" ? "Yes, delete it!" : "نعم، احذفها!" }}',
      cancelText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
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
            releaseAlert('success', {
              title: '{{ $l == "en" ? "Deleted!" : "تم الحذف!" }}',
              message: data.message || '{{ $l == "en" ? "Task has been deleted." : "تم حذف المهمة." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
              confirmColor: 'primary'
            }).then(() => {
              // Remove deleted task
              $(`#task-${taskId}`).remove();
            });
          } else {
            releaseAlert('danger', {
              title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
              message: data.message || '{{ $l == "en" ? "Could not delete the task." : "تعذر حذف المهمة." }}',
              confirmColor: 'primary'
            });
          }
        })
        .catch(error => {
          console.error(error);
          releaseAlert('danger', {
            title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
            message: '{{ $l == "en" ? "Could not delete the task." : "تعذر حذف المهمة." }}',
            confirmColor: 'primary'
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

    releaseAlert('warning', {
      title: '{{ $l == "en" ? "Are you sure?" : "هل أنت متأكد؟" }}',
      message: `{{ $l == "en" ? "Do you want to mark the task as: " : "هل تريد وضع علامة على المهمة كـ: " }}${status}"?`,
      confirmText: '{{ $l == "en" ? "Yes, update it!" : "نعم، قم بتحديثها!" }}',
      cancelText: '{{ $l == "en" ? "Cancel" : "إلغاء" }}',
      confirmColor: 'primary'
    }).then((ok) => {
      if (ok) {
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
            releaseAlert('success', {
              title: '{{ $l == "en" ? "Updated!" : "تم التحديث!" }}',
              message: data.message || '{{ $l == "en" ? "Task status has been updated." : "تم تحديث حالة المهمة." }}',
              confirmText: '{{ $l == "en" ? "OK" : "موافق" }}',
              confirmColor: 'primary'
            }).then(() => {
              // Get current task checkbox and label
              const currentTaskCheckbox = $(`#task${taskId}-checkbox`);
              const currentTaskLabel = currentTaskCheckbox.closest('.form-check').find('label');

              // Update checkbox and label styles
              currentTaskCheckbox.prop('checked', isChecked);
              currentTaskLabel.css('text-decoration', isChecked ? 'line-through' : 'none');
            });
          } else {
            releaseAlert('danger', {
              title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
              message: data.message || '{{ $l == "en" ? "Could not update the task." : "تعذر تحديث المهمة." }}',
              confirmColor: 'primary'
            });
          }
        })
        .catch(error => {
          console.error(error);
          releaseAlert('danger', {
            title: '{{ $l == "en" ? "Error!" : "خطأ!" }}',
            message: '{{ $l == "en" ? "Could not update the task." : "تعذر تحديث المهمة." }}',
            confirmColor: 'primary'
          });
        });
      }
    });
  }

</script>
@stop
