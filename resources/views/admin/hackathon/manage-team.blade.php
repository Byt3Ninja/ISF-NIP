@extends('layouts.admin')

@section('content')
  <!-- Main Content -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <h2 class="mb-4">
        {{ $l == 'en' ? 'Manage your team for' : 'إدارة فريقك لـ' }} 
        <span class="text-primary">{{ $team_Formation_raw->first()->tf_name }}</span>
      </h2>

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
        <h4>{{ $l == 'en' ? 'You haven\'t joined or created a team yet.' : 'لم تنضم إلى فريق بعد أو لم تقم بإنشاء فريق.' }}</h4>
        <p>{{ $l == 'en' ? 'You can create a new team or join an existing team from the hackathon details page.' : 'يمكنك إنشاء فريق جديد أو الانضمام إلى فريق موجود من خلال صفحة تفاصيل الهاكثون.' }}</p>
        <a href="#" class="btn btn-primary mt-3">
          <i class="ti ti-arrow-left me-1"></i> 
          {{ $l == 'en' ? 'Back to Hackathon & Setup Team' : 'العودة لصفحة الهاكثون وتكوين فريق' }}
        </a>
      </div>

      <!-- Team Management Section -->
      <div id="team-management-section" class="row">
        <div class="col-md-7">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">
                {{ $l == 'en' ? 'Team:' : 'فريق:' }} 
                <span id="team-name-display" class="fw-bold">{{ $team_Formation_raw->first()->tf_name }}</span>
              </h5>
            </div>
            <div class="card-body">
              @php
                $maxMembers = $count_team;
                $members = $team_Formation_raw->where('tm_acceptInvition', 'accepted')->count();
                $T = 0;
                $leaderOrnot = $team_Formation_raw->where('tm_isTeamLeader', 1)->where('tm_user_id', Auth::user()->id);
              @endphp

              <h6>
                <i class="ti ti-users me-2"></i>
                {{ $l == 'en' ? 'Team Members' : 'أعضاء الفريق' }} 
                (<span id="member-count">{{ $members }}</span>/{{ $maxMembers }}):
              </h6>
              <ul class="list-group mb-3" id="team-members-list">
                @php
                  $team_leader = $team_Formation_raw->where('tm_isTeamLeader', 1)->first();
                @endphp

                @if($team_leader)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <div class="avatar avatar-sm me-2">
                        <img 
                          src="{{ '/191014/'.$team_leader->u_img_url ?? '/icons/account.svg' }}" 
                          alt="Avatar"
                          onerror="this.onerror=null;this.src='/icons/account.svg';"
                          class="rounded-circle"
                        >
                      </div>
                      <strong>
                        @if($leaderOrnot->count() > 0)
                          {{ $l == 'en' ? 'You' : 'انت' }}
                        @else
                          {{ $l == 'en' 
                            ? "Leader ({$team_leader->user_name_en})" 
                            : "قائد ({$team_leader->user_name_ar})" }}
                        @endif
                      </strong>
                    </div>
                    <span class="badge bg-label-primary rounded-pill">{{ $l == 'en' ? 'Leader' : 'قائد' }}</span>
                  </li>
                @endif
                @foreach ($team_members as $member)
                  @php $T++ @endphp
                  <li id="Member{{ $member->tm_id }}" class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <div class="avatar avatar-sm me-2">
                        <img 
                          src="{{ '/191014/'. $member->u_img_url ?? '/icons/account.svg' }}" 
                          alt="Avatar"
                          onerror="this.onerror=null;this.src='/icons/account.svg';"
                          class="rounded-circle"
                        >
                      </div>
                      {{ $l == 'en' ?  $member->tm_name .' ( Team Member '.$T.')' : $member->tm_name . '( عضو الفريق '.$T.' )' }}
                    </div>
                    @if($leaderOrnot->count() > 0)
                      <button 
                        class="btn btn-sm btn-outline-danger" 
                        data-bs-toggle="tooltip"
                        title="{{ $l == 'en' ? 'Remove Member (Leader only)' : 'إزالة العضو (متاح للقائد فقط)' }}"
                        onclick="confirmRemoveMember({{ $member->tm_id }}, '{{ $member->tm_name }}')"
                      >
                        <i class="ti ti-user-minus"></i>
                      </button>
                    @endif
                  </li>
                @endforeach

                @foreach ($team_Pinding as $index => $pendingMember)
                  <li id="pendingMember{{ $pendingMember->tm_id }}" class="list-group-item d-flex justify-content-between align-items-center text-muted">
                    <div>
                      <i class="ti ti-user-plus me-2"></i>
                      {{ $l == 'en' ? 'Pending invitation to: '. $pendingMember->tm_email : 'دعوة معلقة لـ: '. $pendingMember->tm_email }}
                    </div>
                    <input type="hidden" id="pendingMemberEmail" value="{{ $pendingMember->tm_id }}">
                    @if($leaderOrnot->count() > 0)
                      <button
                        class="btn btn-sm btn-outline-danger" 
                        data-id="{{ $pendingMember->tm_id }}" 
                        data-bs-toggle="tooltip"
                        title="{{ $l == 'en' ? 'Cancel Invitation' : 'إلغاء الدعوة' }}"
                        onclick="confirmCancelInvitation({{ $pendingMember->tm_id }}, '{{ $pendingMember->tm_email }}')"
                      >
                        <i class="ti ti-x"></i>
                      </button>
                    @endif
                  </li>
                @endforeach
              </ul>
              @if($leaderOrnot->count() > 0)
                @if($members <= $maxMembers)
                  <h6 class="mt-8">
                    <i class="ti ti-user-plus me-2"></i>
                    {{ $l == 'en' ? 'Invite New Members:' : 'دعوة أعضاء جدد:' }}
                  </h6>
                  <div class="row g-3 mb-3">
                    <div class="col-12">
                      <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#viewAllMembersModal">
                        <i class="ti ti-users me-1"></i> 
                        {{ $l == 'en' ? 'View All Available Members' : 'عرض جميع الأعضاء المتاحين' }}
                      </button>
                    </div>
                  </div>
                  <form class="row g-3 align-items-center" method="POST" action="{{ route('hackathon.invitation') }}">
                    @csrf
                    <div class="col-auto flex-grow-1">
                      <input 
                        type="email" 
                        name="memberEmail" 
                        class="form-control" 
                        id="memberEmail"
                        placeholder="{{ $l == 'en' ? 'Member email address' : ' بريد الإلكتروني للمستخدم ' }}" 
                        required
                      >
                    </div>
                    <div class="col-auto">
                      <button type="submit" class="btn btn-success" id="sendInvitationBtn">
                        <span class="btn-text">
                          <i class="ti ti-send me-1"></i> 
                          {{ $l == 'en' ? 'Send Invitation' : 'إرسال دعوة' }}
                        </span>
                        <span class="btn-spinner d-none">
                          <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                          {{ $l == 'en' ? 'Sending...' : 'جاري الإرسال...' }}
                        </span>
                      </button>
                    </div>
                  </form>
                @else
                  <div class="alert alert-info">{{ $l == 'en' ? 'The team is full.' : 'الفريق مكتمل.' }}</div>
                @endif
              @endif
              <hr>
              @if($leaderOrnot->count() > 0)
                @php
                  $teamLeader = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition', 'join')->where('tm_team_formation_id', $team_Formation_raw->first()->tf_id);
                @endphp

                <h6>
                  <i class="ti ti-user-pause me-2"></i>
                  {{ $l == 'en' ? 'Pending Join Requests:' : 'طلبات الانضمام المعلقة:' }}
                </h6>
                <div id="join-requests-list">
                  @if($teamLeader->isEmpty())
                    <div class="alert alert-info">{{ $l == 'en' ? 'No join requests currently.' : 'لا توجد طلبات انضمام حالياً.' }}</div>
                  @else
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <strong>{{ $teamLeader->first()->tm_name }}</strong>
                      </div>
                      <div>
                        <button 
                          class="btn btn-outline-primary btn-sm approve-btn"
                          onclick="ApprovedjoinTeam('{{ $teamLeader->first()->tm_id }}','{{ $teamLeader->first()->tm_email }}')"
                          data-id="{{ $teamLeader->first()->tm_id }}"
                        >
                          <span class="btn-text">
                            {{ $l == 'en' ? 'Approve' : ' موافقة ' }}
                            <i class="bi bi-person-plus-fill ms-2"></i>
                          </span>
                          <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            {{ $l == 'en' ? 'Approving...' : 'جاري الموافقة...' }}
                          </span>
                        </button>
                        <button 
                          class="btn btn-outline-danger btn-sm reject-btn"
                          onclick="RejectedjoinTeam('{{ $teamLeader->first()->tm_id }}','{{ $teamLeader->first()->tm_email }}')"
                          data-id="{{ $teamLeader->first()->tm_id }}"
                        >
                          <span class="btn-text">
                            {{ $l == 'en' ? 'Rejects' : ' رفض ' }}
                            <i class="bi bi-person-plus-fill ms-2"></i>
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
              @endif
            </div>
          </div>
        </div>

        <div class="col-md-5">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">
                <i class="ti ti-target me-2"></i> 
                {{ $l == 'en' ? 'Team Goal & Project' : 'هدف الفريق ومشروعه' }}
              </h5>
            </div>
            <div class="card-body">
              <h6>
                {{ $l == 'en' ? 'Project Idea Name :' : 'اسم فكرة المشروع :' }} 
                {{ $team_Formation_raw->first()->tf_projectName_en }}
              </h6>
              <hr>
              <p id="team-project-idea">
                {{ $l == 'en' ? 'Project Idea Description :' : 'وصف فكرة المشروع :' }} 
                <br>{{ $team_Formation_raw->first()->tf_projectDescription_en }}
              </p>
              <button 
                class="btn btn-sm btn-outline-info mb-3" 
                data-bs-toggle="modal"
                data-bs-target="#editProjectIdeaModal"
              >
                <i class="ti ti-edit me-1"></i> 
                {{ $l == 'en' ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}
              </button>

              <h6>{{ $l == 'en' ? 'Project Status:' : 'حالة المشروع:' }}</h6>
              <div class="progress mb-2" style="height: 20px;">
                <div 
                  class="progress-bar bg-success" 
                  role="progressbar"
                  style="width:{{ $team_Formation_raw->first()->tf_projectStatus  }}%;"
                  aria-valuenow="{{ $team_Formation_raw->first()->tf_projectStatus  }}" 
                  aria-valuemin="0"
                  aria-valuemax="100"
                >
                  {{ $team_Formation_raw->first()->tf_projectStatus }}% {{ $l == 'en' ? 'Complete' : 'مكتمل' }}
                </div>
              </div>
              <small class="text-muted">
                @if($team_Formation_raw->first()->tf_projectStatus == 20)
                  {{ __('admin.Idea + Team') }} : {{ __('admin.Define the problem and form a team with clear roles.') }}
                @elseif($team_Formation_raw->first()->tf_projectStatus == 40)
                  {{ __('admin.Research') }} : {{ __('admin.Validate the idea, study users, and outline key features.') }}
                @elseif($team_Formation_raw->first()->tf_projectStatus == 60)
                  {{ __('admin.Design & Build') }} : {{ __('admin.Sketch UI, set up the project, and develop core functionality.') }}
                @elseif($team_Formation_raw->first()->tf_projectStatus == 80)
                  {{ __('admin.Ready for Pitch') }} : {{ __('admin.Prepare demo, finalize slides, and rehearse the presentation.') }}
                @elseif($team_Formation_raw->first()->tf_projectStatus == 100)
                  {{ __('admin.Project Submitted') }} : {{ __('admin.Submit code, share the demo, and complete documentation. story.') }}
                @endif
              </small>
            </div>
          </div>
        </div>
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
      <div class="modal-body">
        <div class="row g-3">
          @foreach($availableMembers as $member)
            <div class="col-md-6 col-lg-4">
              <div class="card h-100 border">
                <div class="card-body text-center">
                  <div class="avatar avatar-xl mx-auto mb-3">
                    <img 
                      src="{{ '/191014/'.$member['image'] }}" 
                      alt="{{ $member['name'] }}" 
                      class="rounded-circle w-100 h-100"
                    >
                  </div>
                  <h6 class="card-title mb-2">{{ $member['name'] }}</h6>
                  <p class="text-muted small mb-1">
                    <i class="ti ti-briefcase me-1"></i>{{ $member['title'] }}
                  </p>
                  <p class="text-muted small mb-1">
                    <i class="ti ti-mail me-1"></i>{{ $member['email'] }}
                  </p>
                  <p class="text-muted small mb-3">
                    <i class="ti ti-map-pin me-1"></i>{{ $member['city'] }}, {{ $member['state'] }}, {{ $member['country'] }}
                  </p>
                  <button 
                    type="button" 
                    id="inviteMemberBtn{{ $member['id'] }}"
                    class="btn btn-primary btn-sm invite-member-btn" 
                    onclick="sendInvitationToMember('{{ $member['email'] }}', '{{ $member['name'] }}', '{{ $member['id'] }}', {{ $member['isInvited'] ? 'true' : 'false' }})"
                    data-member-id="{{ $member['id'] }}"
                    @if($member['isInvited']) disabled @endif
                  >
                    <span class="btn-text">
                      <i class="ti ti-send me-1"></i>
                      @if($member['isInvited'])
                        {{ $l == 'en' ? 'Invitation Sent' : 'تم إرسال الدعوة' }}
                      @else
                        {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
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
        @if(empty($availableMembers))
          <div class="text-center py-5">
            <i class="ti ti-users-off ti-xl text-muted mb-3"></i>
            <h6 class="text-muted">{{ $l == 'en' ? 'No available members found' : 'لا يوجد أعضاء متاحين' }}</h6>
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
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

    $('#editProjectIdeaModal').on('show.bs.modal', function (event) {
      $('#projectIdeaText').val($('#team-project-idea').text());
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
</script>
@stop