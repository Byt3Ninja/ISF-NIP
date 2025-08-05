@extends('layouts.hr-admin')

@section('content')
<!-- Main Content -->
<div class="container mt-4">
  <h2 class="mb-4">{{ $l == 'en' ? 'Manage your team for' : 'إدارة فريقك لـ' }} <span class="text-primary">{{ $l == 'en'
      ? $team_Formation_raw->first()->tf_name : $team_Formation_raw->first()->tf_name }}</span></h2>

  <!-- No Team Section -->
  <div id="no-team-section" class="card card-body text-center p-5" style="display:none;">
    <i class="ti ti-users ti-xl text-muted mb-3"></i>
    <h4>{{ $l == 'en' ? 'You haven\'t joined or created a team yet.' : 'لم تنضم إلى فريق بعد أو لم تقم بإنشاء فريق.' }}
    </h4>
    <p>{{ $l == 'en' ? 'You can create a new team or join an existing team from the hackathon details page.' : 'يمكنك
      إنشاء فريق جديد أو الانضمام إلى فريق موجود من خلال صفحة تفاصيل الهاكثون.' }}</p>
    <a href="#" class="btn btn-primary mt-3">
      <i class="ti ti-arrow-left me-1"></i> {{ $l == 'en' ? 'Back to Hackathon & Setup Team' : 'العودة لصفحة الهاكثون
      وتكوين فريق' }}
    </a>
  </div>

  <!-- Team Management Section -->
  <div id="team-management-section" class="row">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">{{ $l == 'en' ? 'Team:' : 'فريق:' }} <span id="team-name-display" class="fw-bold">{{ $l ==
              'en' ? $team_Formation_raw->first()->tf_name : $team_Formation_raw->first()->tf_name }}</span></h5>
        </div>
        <div class="card-body">

          @if( session()->has('success') )
          <div class="row">
            <div class="col-md-12">
              <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
                {{ __('admin.'. session()->get('success')) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>
          </div>
          @endif
          @if( session()->has('error') )
          <div class="row">
            <div class="col-md-12">
              <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
                {{ __('admin.'.session()->get('error')) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            </div>
          </div>
          @endif

          @php
          $maxMembers = $count_team;
          $members = $team_Formation_raw->where('tm_acceptInvition', 'accepted')->count();
         
          $T=0;
          $leaderOrnot = $team_Formation_raw->where('tm_isTeamLeader', 1)->where('tm_user_id', Auth::user()->id);
          
          @endphp
          {{-- {{ dd($team_Formation_raw) }} --}}
          <h6><i class="ti ti-users me-2"></i>{{ $l == 'en' ? 'Team Members' : 'أعضاء الفريق' }} (<span
              id="member-count">{{ $members }}</span>/{{ $maxMembers }}):</h6>
          <ul class="list-group mb-3" id="team-members-list">
            @php
            $team_leader = $team_Formation_raw->where('tm_isTeamLeader', 1)->first();
            @endphp
            {{-- {{ dd($team_leader, $team_members) }} --}}
            @if($team_leader)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <div class="avatar avatar-sm me-2">
                  <img src="{{ '/191014/'.$team_leader->u_img_url ?? '/vuexy/assets/img/avatars/1.png' }}" alt="Avatar"
                    class="rounded-circle">
                </div>
               <strong>
                    @if($leaderOrnot)
                        {{ $l == 'en' 
                            ? "Leader ({$team_leader->user_name_en})" 
                            : "قائد ({$team_leader->user_name_ar})" }}
                    @else
                        {{ $l == 'en' ? 'You' : 'انت' }}
                    @endif
                </strong>
              </div>
              <span class="badge bg-primary rounded-pill">{{ $l == 'en' ? 'Leader' : 'قائد' }}</span>
            </li>
            @endif
          
            @foreach ($team_members as $member)
          @php $T++ @endphp
            <li id="Member{{ $member->tm_id }}"
              class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <div class="avatar avatar-sm me-2">
                  <img src="{{ '/191014/'. $member->u_img_url ?? '/vuexy/assets/img/avatars/3.png' }}" alt="Avatar"
                    class="rounded-circle">
                </div>
                {{ $l == 'en' ?  $member->tm_name .' ( Team Member '.$T.')' : $member->tm_name . '( عضو الفريق '.$T.' )' }}
              </div>
              @if($leaderOrnot->count() > 0)
              <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                title="{{ $l == 'en' ? 'Remove Member (Leader only)' : 'إزالة العضو (متاح للقائد فقط)' }}"
                onclick="confirmRemoveMember({{ $member->tm_id }}, '{{ $member->tm_name }}')">
                <i class="ti ti-user-minus"></i>
              </button>
              @endif
            </li>
            @endforeach

            @foreach ($team_Pinding as $index => $pendingMember)
            <li id="pendingMember{{ $pendingMember->tm_id }}"
              class="list-group-item d-flex justify-content-between align-items-center text-muted">
              <div>
                <i class="ti ti-user-plus me-2"></i>
                {{ $l == 'en' ? 'Pending invitation to: '. $pendingMember->tm_email.'' : 'دعوة معلقة لـ:
                user3@example.com' }}
              </div>

              <input type="hidden" id="pendingMemberEmail" value="{{ $pendingMember->tm_id }}">
              @if($leaderOrnot->count() > 0)
              <button
                class="btn btn-sm btn-outline-secondary" data-id="{{ $pendingMember->tm_id }}" data-bs-toggle="tooltip"
                title="{{ $l == 'en' ? 'Cancel Invitation' : 'إلغاء الدعوة' }}"
                onclick="confirmCancelInvitation({{ $pendingMember->tm_id, $pendingMember->tm_email }})">
                <i class="ti ti-x"></i>
              </button>
              @endif

            </li>
            @endforeach

          </ul>
          {{-- {{ dd($members, $maxMembers) }} --}}
            @if($leaderOrnot->count() > 0)
              @if($members <= $maxMembers)
              <h6><i class="ti ti-user-plus me-2"></i>{{ $l == 'en' ? 'Invite New Members:' : 'دعوة أعضاء جدد:' }}</h6>
              <form class="row g-3 align-items-center" method="POST" action="{{ route('hackathon.invitation') }}">
                @csrf
                <div class="col-auto flex-grow-1">
                  <input type="email" name="memberEmail" class="form-control" id="memberEmail"
                    placeholder="{{ $l == 'en' ? 'Member email address' : ' بريد الإلكتروني للمستخدم ' }}" required>
                </div>
                <div class="col-auto">
                  <button type="submit" class="btn btn-success">
                    <i class="ti ti-send me-1"></i> {{ $l == 'en' ? 'Send Invitation' : 'إرسال دعوة' }}
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
            $teamLeader = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition',
            'join')->where('tm_team_formation_id', $team_Formation_raw->first()->tf_id);
            @endphp


            <h6><i class="ti ti-user-check me-2"></i>{{ $l == 'en' ? 'Pending Join Requests:' : 'طلبات الانضمام المعلقة:'
              }}</h6>
            <div id="join-requests-list">
              @if($teamLeader->isEmpty())
              <div class="alert alert-info">{{ $l == 'en' ? 'No join requests currently.' : 'لا توجد طلبات انضمام حالياً.'
                }}</div>
              @else

              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $teamLeader->first()->tm_name }}</strong>

                </div>


                <button class="btn btn-outline-primary btn-sm"
                  onclick="ApprovedjoinTeam('{{ $teamLeader->first()->tm_id }}','{{ $teamLeader->first()->tm_email }}')">
                  {{ $l == 'en' ? 'Approve' : ' موافقة ' }}
                  <i class="bi bi-person-plus-fill ms-2"></i>
                </button>

                <button class="btn btn-outline-danger btn-sm"
                  onclick="RejectedjoinTeam('{{ $teamLeader->first()->tm_id }}','{{ $teamLeader->first()->tm_email }}')">
                  {{ $l == 'en' ? 'Rejects' : ' رفض ' }}
                  <i class="bi bi-person-plus-fill ms-2"></i>
                </button>

                {{-- <button class="btn btn-outline-primary btn-sm" disabled>
                  {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
                  <i class="bi bi-person-plus-fill ms-2"></i>
                </button> --}}


              </div>

              @endif
            </div>
          @endif
        </div>
        <div class="card-footer">
          {{-- <a href="/dashboard/hackathon/my-team" class="btn btn-info">
            <i class="ti ti-message-circle me-1"></i> {{ $l == 'en' ? 'Team Workspace' : 'مساحة عمل الفريق' }}
          </a> --}}
          {{-- @if($leaderOrnot->count() < 0) <button class="btn btn-danger float-end" onclick="confirmLeaveTeam()">
            <i class="ti ti-logout me-1"></i> {{ $l == 'en' ? 'Leave Team' : 'مغادرة الفريق' }}
            </button>
            @endif
            @if($leaderOrnot->count() > 0)
            <button disabled class="btn btn-outline-danger float-end me-2" onclick="confirmDisbandTeam()"
              id="disbandTeamBtn" style="display:none;">
              <i class="ti ti-trash me-1"></i> {{ $l == 'en' ? 'Disband Team (Leader)' : 'حل الفريق (للقائد)' }}
            </button>
            @endif --}}
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-target me-2"></i> {{ $l == 'en' ? 'Team Goal & Project' : 'هدف الفريق
            ومشروعه' }}</h5>
        </div>
        <div class="card-body">
          <h6>{{ $l == 'en' ? 'Project Idea Name :' : 'اسم فكرة المشروع :' }} {{
            $team_Formation_raw->first()->tf_projectName_en }}</h6>
          <hr>
          <p id="team-project-idea">{{ $l == 'en' ? 'Project Idea Description :' : 'وصف فكرة المشروع :' }} <br>{{
            $team_Formation_raw->first()->tf_projectDescription_en }}</p>
          <button class="btn btn-sm btn-outline-secondary mb-3" data-bs-toggle="modal"
            data-bs-target="#editProjectIdeaModal">
            <i class="ti ti-edit me-1"></i> {{ $l == 'en' ? 'Edit Project Idea' : 'تعديل فكرة المشروع' }}
          </button>

          {{-- <h6>{{ $l == 'en' ? 'Target Track:' : 'المحور المستهدف:' }}</h6>
          <p id="team-target-track">Idea + Team, Define the problem. Form your team. Agree on the project goal.
            Research, Validate the idea. Explore similar solutions. Finalize key features.
            Design, Sketch user flow. Draft UI. Pick your tools and tech stack.
            Build, Set up the repo. Develop core features. Test early and often.
            Ready for Pitch, Polish the demo. Prepare slides. Practice the story.</p> --}}

          <h6>{{ $l == 'en' ? 'Project Status:' : 'حالة المشروع:' }}</h6>
          <div class="progress mb-2" style="height: 20px;">
            <div class="progress-bar bg-success" role="progressbar"
              style="width:{{ $team_Formation_raw->first()->tf_projectStatus  }}%;"
              aria-valuenow="{{ $team_Formation_raw->first()->tf_projectStatus  }}" aria-valuemin="0"
              aria-valuemax="100">{{ $team_Formation_raw->first()->tf_projectStatus }}% {{ $l == 'en' ? 'Complete' :
              'مكتمل' }}</div>
          </div>
          <small class="text-muted">
            @if($team_Formation_raw->first()->tf_projectStatus == 20)
            {{ __('admin.Idea + Team') }} : {{ __('admin.Define the problem and form a team with clear roles.') }}
            @elseif($team_Formation_raw->first()->tf_projectStatus == 40)
            {{ __('admin.Research') }} : {{ __('admin.Validate the idea, study users, and outline key features.') }}
            @elseif($team_Formation_raw->first()->tf_projectStatus == 60)
            {{ __('admin.Design & Build') }} : {{ __('admin.Sketch UI, set up the project, and develop core
            functionality.') }}
            @elseif($team_Formation_raw->first()->tf_projectStatus == 80)
            {{ __('admin.Ready for Pitch') }} : {{ __('admin.Prepare demo, finalize slides, and rehearse the
            presentation.') }}
            @elseif($team_Formation_raw->first()->tf_projectStatus == 100)
            {{ __('admin.Project Submitted') }} : {{ __('admin.Submit code, share the demo, and complete documentation.
            story.') }}
            @endif
          </small>
        </div>
      </div>

      {{-- <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-video me-2"></i> {{ $l == 'en' ? 'Upcoming Mentorship Sessions for Your Team'
            : 'جلسات الإرشاد القادمة لفريقكم' }}</h5>
        </div>
        <div class="card-body" id="team-mentorship-sessions">
          <div id="no-mentorship-sessions" class="text-muted">
            <p>{{ $l == 'en' ? 'No mentorship sessions currently scheduled for your team.' : 'لا توجد جلسات إرشاد مجدولة
              حالياً لفريقكم.' }}</p>
          </div>
          <ul class="list-group list-group-flush" id="booked-sessions-list" style="display:none;">
            <li class="list-group-item px-0">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">{{ $l == 'en' ? 'With' : 'مع' }} <strong>{{ $l == 'en' ? 'Dr. Ahmed Abdullah' : 'د.
                    أحمد عبدالله' }}</strong> ({{ $l == 'en' ? 'AI Expert' : 'خبير ذكاء اصطناعي' }})</h6>
                <small>{{ $l == 'en' ? 'Tomorrow, 02:00 PM' : 'غداً, 02:00 م' }}</small>
              </div>
              <p class="mb-1">{{ $l == 'en' ? 'Topic: Product development strategy and idea validation.' : 'الموضوع:
                استراتيجية تطوير المنتج والتحقق من الفكرة.' }}</p>
              <small class="text-success">
                <i class="ti ti-video me-1"></i> {{ $l == 'en' ? 'Virtual meeting link:' : 'رابط الاجتماع الافتراضي:' }}
                <a href="#" target="_blank">{{ $l == 'en' ? 'Click here to join' : 'اضغط هنا للانضمام' }}</a>
              </small>
            </li>
          </ul>
          <a href="#" class="btn btn-outline-primary btn-sm mt-3" id="link-to-book-mentorship">
            <i class="ti ti-calendar-plus me-1"></i> {{ $l == 'en' ? 'Request New Mentorship Session / View Mentors' :
            'طلب جلسة إرشاد جديدة / عرض المرشدين' }}
          </a>
        </div>
      </div> --}}

      {{-- <div class="card mt-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-link me-2"></i>{{ $l == 'en' ? 'Important Hackathon Links' : 'روابط مهمة
            للهاكثون' }}</h5>
        </div>
        <div class="card-body">
          <ul class="list-unstyled">
            <li class="mb-2"><a href="#" id="link-hackathon-details"><i class="ti ti-info-circle me-1"></i> {{ $l ==
                'en' ? 'Back to Hackathon Details' : 'العودة لصفحة تفاصيل الهاكثون' }}</a></li>
            <li class="mb-2"><a href="#" id="link-submit-project" class="text-success fw-bold"><i
                  class="ti ti-upload me-1"></i> {{ $l == 'en' ? 'Go to Project Submission Page' : 'الذهاب لصفحة رفع
                المشروع' }}</a></li>
            <li><a href="#"><i class="ti ti-file-text me-1"></i> {{ $l == 'en' ? 'Hackathon Rules & Guidelines' : 'قواعد
                وتعليمات الهاكثون' }}</a></li>
          </ul>
        </div>
      </div> --}}
      
    </div>
  </div>
</div>

<!-- Edit Project Idea Modal -->
<div class="modal fade" id="editProjectIdeaModal" tabindex="-1" aria-labelledby="editProjectIdeaModalLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProjectIdeaModalLabel">{{ $l == 'en' ? 'Edit Project Idea' : 'تعديل فكرة
          المشروع' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/dashboard/hackathon/EditProjectIdea">
          @csrf
          <input type="hidden" name="team_formation_id" value="{{ $team_Formation_raw->first()->tf_id }}">
          <div class="mb-3">
            <label for="Project_Name_en" class="form-label">
              {{ __('admin.Project_Name') }}
            </label>
            <input type="text" class="form-control" name="Project_Name_en"
              value="{{ $team_Formation_raw->first()->tf_projectName_en }}" id="Project_Name_en" required>
          </div>
          {{-- <div class="mb-3">
            <label for="Project_Name_ar" class="form-label">
              {{__('admin.Project_Name') ." " . __('admin.Arabic') }}
            </label>
            <input type="text" class="form-control" name="Project_Name_ar"
              value="{{ $team_Formation_raw->first()->tf_projectName_ar }}" id="Project_Name_ar" required>
          </div> --}}

          <div class="mb-3">
            <label for="Project_Description_en" class="form-label">
              {{ __('admin.Project_Description') }}
            </label>
            <textarea class="form-control" name="Project_Description_en" id="Project_Description_en"
              rows="2">{{ $team_Formation_raw->first()->tf_projectDescription_en }}</textarea>
          </div>

          {{-- <div class="mb-3">
            <label for="Project_Description_ar" class="form-label">
              {{ __('admin.Project_Description') ." " . __('admin.Arabic') }}
            </label>
            <textarea class="form-control" name="Project_Description_ar" id="Project_Description_ar"
              rows="2">{{ $team_Formation_raw->first()->tf_projectDescription_ar }}</textarea>
          </div> --}}

          <div class="mb-3">
            <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Project Status' : 'حالة المشروع' }}</label>
            <select class="form-select" id="Project_Status" name="Project_Status">
              <option value="">{{ $l == 'en' ? 'Choose v...' : 'اختر الحالة...' }}</option>
              <option value="20" {{ $team_Formation_raw->first()->tf_projectStatus == '20' ? 'selected' : '' }} >{{
                __('admin.Idea + Team') }}</option>
              <option value="40" {{ $team_Formation_raw->first()->tf_projectStatus == '40' ? 'selected' : '' }} >{{
                __('admin.Research') }}</option>
              <option value="60" {{ $team_Formation_raw->first()->tf_projectStatus == '60' ? 'selected' : '' }} >{{
                __('admin.Design & Build') }}</option>
              <option value="80" {{ $team_Formation_raw->first()->tf_projectStatus == '80' ? 'selected' : '' }} >{{
                __('admin.Ready for Pitch') }}</option>
              <option value="100" {{ $team_Formation_raw->first()->tf_projectStatus == '100' ? 'selected' : '' }} >{{
                __('admin.Project Submitted') }}</option>
            </select>
          </div>

          {{-- <div class="mb-3">
            <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Target Track' : 'المحور المستهدف' }}</label>
            <select class="form-select" id="Project_Status" name="Project_Status">
              <option value="">{{ $l == 'en' ? 'Choose Status...' : 'اختر الحالة...' }}</option>
              @php
              $selectedStatus = (int) ($team_Formation_raw->first()->tf_projectStatus ?? 0);
              @endphp
              @for ($s = 0; $s <= 100; $s +=25) <option value="{{ $s }}" {{ $selectedStatus==$s ? 'selected' : '' }}>{{
                $s .'%' }}</option>
                @endfor
            </select>
          </div> --}}



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
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' : 'إلغاء'
        }}</button>
      <button type="button" class="btn btn-primary" onclick="saveProjectIdea()">{{ $l == 'en' ? 'Save Changes' : 'حفظ
        التغييرات' }}</button>
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
        
        // Mentorship sessions display logic
        const teamHasBookedSessions = true; // This would be dynamic based on actual team data
        if (teamHasBookedSessions) {
          $('#no-mentorship-sessions').hide();
          $('#booked-sessions-list').show();
        } else {
          $('#no-mentorship-sessions').show();
          $('#booked-sessions-list').hide();
        }

      } else {
        $('#team-management-section').hide();
        $('#no-team-section').show();
        // Hide mentorship section if no team
        $('#team-mentorship-sessions').hide();
      }

      $('#inviteMemberForm').on('submit', function(e) {
        e.preventDefault();
        const email = $('#memberEmail').val();
        if (email) {
          Swal.fire({
            icon: 'success',
            title: '{{ $l == 'en' ? 'Invitation Sent!' : 'تم إرسال الدعوة!' }}',
            text: `{{ $l == 'en' ? 'Invitation sent to' : 'تم إرسال دعوة إلى' }} ${email}.`,
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            showCancelButton: false,
            showConfirmButton: true,
            confirmButtonText: "{{ __('admin.OK') }}",
            customClass: {
                confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
          });
          
          const list = $('#team-members-list');
          const newItem = $(`
            <li class="list-group-item d-flex justify-content-between align-items-center text-muted">
              <div><i class="ti ti-user-plus me-2"></i> {{ $l == 'en' ? 'Pending invitation to:' : 'دعوة معلقة لـ:' }} ${email}</div>
              <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ $l == 'en' ? 'Cancel Invitation' : 'إلغاء الدعوة' }}" >
                <i class="ti ti-x"></i>
              </button>
            </li>
          `);
          list.append(newItem);
          $('[data-bs-toggle="tooltip"]').tooltip();
          $('#memberEmail').val('');
        }
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
        confirmButtonText: "{{ __('admin.OK') }}",
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
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            showCancelButton: false,
            showConfirmButton: true,
            confirmButtonText: "{{ __('admin.OK') }}",
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
            // Remove the pending member from the list
            $('#Member'+id).remove();
              // Update member count
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
          // Here you would typically make an AJAX call to remove the member
          // For now, we'll just show a success message and remove the element
          
        
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
            // Remove the pending member from the list
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
          // Here you would typically make an AJAX call to cancel the invitation
          // For now, we'll just show a success message and remove the element
          
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
            confirmButtonText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
            showCancelButton: false,
            showConfirmButton: true,
            confirmButtonText: "{{ __('admin.OK') }}",
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
      
        if (confirm(`{{ $l == "en" ? "Are you sure you want to Approved This request to join team" : "هل أنت متأكد أنك تريد الموافقة على  طلب الانضمام إلى فريق" }} "${teamName}"{{ $l == "en" ? "?" : "؟" }}`)) {
            
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
                return response.json(); // أو response.text() حسب ما يرجع الـ controller
            })
            .then(data => {
                alert(`{{ $l == "en" ? "Join request sent to team" : "تم إرسال طلب الانضمام إلى فريق" }} "${teamName}".`);
                // إعادة توجيه أو تحديث
                window.location.href = window.location.href;
            })
            .catch(error => {
                alert("{{ $l == 'en' ? 'There was an error sending the request.' : 'حدث خطأ أثناء إرسال الطلب.' }}");
                console.error(error);
            });
        }
    }

    function RejectedjoinTeam(id, teamName) {
      
        if (confirm(`{{ $l == "en" ? "Are you sure you want to Reject This request to join team" : "هل أنت متأكد أنك تريد الرفض على  طلب الانضمام إلى فريق" }} "${teamName}"{{ $l == "en" ? "?" : "؟" }}`)) {
            
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
                return response.json(); // أو response.text() حسب ما يرجع الـ controller
            })
            .then(data => {
                alert(`{{ $l == "en" ? "Join request sent to team" : "تم إرسال طلب الانضمام إلى فريق" }} "${teamName}".`);
                // إعادة توجيه أو تحديث
                window.location.href = window.location.href;
            })
            .catch(error => {
                alert("{{ $l == 'en' ? 'There was an error sending the request.' : 'حدث خطأ أثناء إرسال الطلب.' }}");
                console.error(error);
            });
        }
    }
</script>
@stop