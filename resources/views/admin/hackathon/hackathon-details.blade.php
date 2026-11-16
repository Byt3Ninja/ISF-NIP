@extends('layouts.admin')

@section('style')
<!-- Additional Styles -->
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
<link rel="stylesheet" href="/css/hackathon.css" />
@stop

@php
  // ====================================
  // Tab Configuration for Hackathon Details
  // ====================================
  
  $user = Auth::user();
  $isAdmin = $user->hasRole('admin') || $user->hasRole('subadmin') || $user->hasRole('organizationmanager');
  
  // Determine if hackathon is finished and has results to display
  $isFinishedHackathon = isset($currentHackathon['status']) && 
                         $currentHackathon['status'] === 'Finished' && 
                         isset($finishedHackathon) && 
                         !empty($finishedHackathon);
  
  // Get user's cycle ID for permission checks
  $userCycleId = $user->team_member?->teamFormation?->user?->company?->cycle ?? 
                 $user->company?->cycle ?? 
                 null;
  
  // Check if user is participating in current hackathon
  $isUserParticipating = ($currentHackathon['cycle_id'] == $userCycleId);
  
  // ====================================
  // Tab Data Generation
  // ====================================
  
  if ($isFinishedHackathon) {
    // Finished Hackathon: Show simplified results-focused tabs
    $tabsData = [
        [
            'title' => 'overview',
            'icon' => 'ti ti-file-text',
            'label' => $l == 'en' ? 'Overview' : 'نظرة عامة',
            'type' => 'static',
        ],
        [
            'title' => 'winners',
            'icon' => 'ti ti-trophy',
            'label' => $l == 'en' ? 'Winners' : 'الفائزون',
            'type' => 'static',
        ],
        [
            'title' => 'projects',
            'icon' => 'ti ti-folder',
            'label' => $l == 'en' ? 'Projects Gallery' : 'معرض المشاريع',
            'type' => 'static',
        ],
    ];

    // لو المستخدم أدمن نضيف عناصر إضافية
    if ($isAdmin) {
        $tabsData = array_merge($tabsData, [
            [
                'title' => 'teams',
                'icon' => 'ti ti-users-group',
                'label' => $l == 'en' ? 'Teams' : 'الفرق',
                'type' => 'static',
            ],
            [
                'title' => 'mentors',
                'icon' => 'ti ti-user',
                'label' => $l == 'en' ? 'Mentors' : 'المرشدون',
                'type' => 'static',
            ],
            [
                'title' => 'mentorship',
                'icon' => 'ti ti-school',
                'label' => $l == 'en' ? 'Mentorship & Workshops' : 'الإرشاد وورش العمل',
                'type' => 'static',
            ],
            [
                'title' => 'results-winners',
                'icon' => 'ti ti-trophy',
                'label' => $l == 'en' ? 'Results & Winners' : 'النتائج والفائزون',
                'type' => 'static',
            ],
            [
              'title' => 'submissions', 
              'icon' => 'ti ti-award', 
              'label' => $l == 'en' ? 'Applicants' : 'المتقدمين', 
              'type' => 'static'
            ],
        ]);
    }
  } else {
    // Active/Ongoing Hackathon: Show full feature set
    $tabsData = [];
    
    // Add dynamic pages from database
    foreach ($pages as $page) {
      $tabsData[] = [
        'title' => $page->code, 
        'icon' => 'ti ti-list', 
        'label' => $page->title, 
        'type' => 'dynamic'
      ];
    }
    
    // Add admin-only submissions tab
    if ($isAdmin) {
      $tabsData[] = [
        'title' => 'submissions', 
        'icon' => 'ti ti-award', 
        'label' => $l == 'en' ? 'Applicants' : 'المتقدمين', 
        'type' => 'static'
      ];
    }
    
    // Generate participant/admin specific tabs
    $participantTabs = [];
    
    if ($isUserParticipating) {
      // Full participant experience
      $participantTabs = [
        ['title' => 'teams', 'icon' => 'bi-people-fill', 'label' => $l == 'en' ? 'Teams' : 'الفرق', 'type' => 'static'],
        ['title' => 'mentors', 'icon' => 'bi-person-video3', 'label' => $l == 'en' ? 'Mentors' : 'المرشدون', 'type' => 'static'],
        ['title' => 'mentorship', 'icon' => 'bi-award-fill', 'label' => $l == 'en' ? 'Mentorship & Workshops' : 'الارشاد وورش العمل', 'type' => 'static'],
        ['title' => 'submission', 'icon' => 'bi-upload', 'label' => $l == 'en' ? 'Project Submission' : 'رفع المشاريع', 'type' => 'static'],
        ['title' => 'results-winners', 'icon' => 'bi-trophy-fill', 'label' => $l == 'en' ? 'Results & Winners' : 'النتائج والفائزون', 'type' => 'static']
      ];
    } elseif ($isAdmin) {
      // Admin view for non-participating hackathons
      $participantTabs = [
        ['title' => 'teams', 'icon' => 'bi-people-fill', 'label' => $l == 'en' ? 'Teams' : 'الفرق', 'type' => 'static'],
        ['title' => 'mentors', 'icon' => 'bi-person-video3', 'label' => $l == 'en' ? 'Mentors' : 'المرشدون', 'type' => 'static'],
        ['title' => 'mentorship', 'icon' => 'bi-award-fill', 'label' => $l == 'en' ? 'Mentorship & Workshops' : 'الارشاد وورش العمل', 'type' => 'static'],
        ['title' => 'results-winners', 'icon' => 'bi-trophy-fill', 'label' => $l == 'en' ? 'Results & Winners' : 'النتائج والفائزون', 'type' => 'static']
      ];
    }
    
    // Merge all tabs
    $tabsData = array_merge($tabsData, $participantTabs);
  }
  
  // ====================================
  // Final Tab Processing
  // ====================================
  
  $tabs = collect($tabsData)->map(function ($tab) {
    $action = 'normal';
    $href = '#';
    if( $tab['title'] == 'mentors' )
    {
      $action = 'redirect';
      $href = route('mentors.list');
    }
    return [
      'action' => $action, 
      'href' => $href,
      'target' => $tab['title'], 
      'icon' => $tab['icon'], 
      'label' => $tab['label'], 
      'type' => $tab['type']
    ];
  })->toArray();
@endphp

@section('content')
<div class="">
  <div class="">
    <!-- Hackathon Details Banner -->
    @include('admin.hackathon.partials.details-banner') 

    <!-- Countdown Timers -->
    @include('admin.hackathon.partials.countdown')
    <!--/ Countdown Timers -->

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills flex-nowrap max-w-100 overflow-auto mb-2 pb-3" id="hackathonTab" role="tablist">
      @foreach($tabs as $tab)
      <li class="nav-item me-1" role="presentation">
        @if( $tab['action'] == 'normal' )
          <button 
            type="button" 
            role="tab"
            class="nav-link {{ $loop->first ? 'active' : '' }}" 
            id="{{ $tab['target'] }}-tab"
            data-bs-toggle="tab" 
            data-bs-target="#{{ $tab['target'] }}" 
            aria-controls="{{ $tab['target'] }}" 
            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
          >
            <i class="{{ $tab['icon'] }} me-2"></i> {{ $tab['label'] }}
          </button>
        @elseif( $tab['action'] == 'redirect' )
          <a
            href="{{ $tab['href'] }}"
            class="nav-link {{ $loop->first ? 'active' : '' }}"
            id="{{ $tab['target'] }}-tab"
            aria-controls="{{ $tab['target'] }}"
            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
          >
            <i class="{{ $tab['icon'] }} me-2"></i> {{ $tab['label'] }}
          </a>
        @endif
      </li>
      @endforeach
    </ul>
    <!--/ Tabs Navigation -->

    <!-- Tabs Content -->
    <div class="tab-content p-0 shadow-none mb-12" id="hackathonTabContent">
      @foreach($tabs as $tab)
        @if( $tab['action'] == 'normal' )
          @if( $tab['type'] == 'dynamic' )
            @php $get_page = $pages->where('code', $tab['target'])->first(); @endphp
            <div class="tab-pane fade{{ $loop->first ? ' show active' : '' }}" id="{{ $tab['target'] }}" role="tabpanel" aria-labelledby="{{ $tab['target'] }}-tab">
              <div class="card">
                <div class="card-body">
                  {!! $get_page->content !!}
                </div>
              </div>
            </div>
          @else
            @php
              // ====================================
              // Partials Mapping Configuration
              // ====================================
              
              $partials = [
                // Overview and Information tabs
                'overview' => $isFinishedHackathon ? 'admin.hackathon.partials.finished-overview-tab' : '',
                'themes' => 'admin.hackathon.partials.themes-tab',
                'schedule' => 'admin.hackathon.partials.schedule-tab',
                'prizes' => 'admin.hackathon.partials.prizes-tab',
                'evaluation' => 'admin.hackathon.partials.evaluation-tab',
                
                // People and Teams tabs  
                'mentors' => 'admin.hackathon.partials.mentors-tab',
                'teams' => 'admin.hackathon.partials.teams-tab',
                
                // Projects tabs
                'projects' => 'admin.hackathon.partials.projects-gallery-tab',
                
                // Results and Winners tabs
                'winners' => 'admin.hackathon.partials.finished-winners-tab',
                'results-winners' => 'admin.hackathon.partials.winners-tab',
                'results' => 'admin.hackathon.partials.results-tab',
                
                // Activities tabs
                'submission' => 'admin.hackathon.partials.projects-tab',
                'mentorship' => 'admin.hackathon.partials.mentorship-tab',
                
                // Admin tabs
                'submissions' => 'admin.hackathon.partials.submissions-tab',
              ];
              
              $partial = $partials[$tab['target']] ?? null;
              
              // ====================================
              // Tab Visibility Logic
              // ====================================
              
              $showTab = true;
              
              if ($isFinishedHackathon) {
                // For finished hackathons, only show the 3 configured tabs
                $allowedFinishedTabs = ['overview', 'projects', 'winners'];
                $showTab = in_array($tab['target'], $allowedFinishedTabs);
              } else {
                // For active hackathons, apply original visibility rules
                if ($tab['target'] === 'teams' && !$isAdmin && (!isset($showStep) || $showStep == false || (isset($companyStep) && $companyStep?->stype == 1))) { 
                  $showTab = true; // Allow teams view for participants
                }
                if ($tab['target'] === 'submissions' && !$isAdmin) {
                  $showTab = false; // Hide submissions from non-admins
                }
              }
            if ($isAdmin) {
                $showTab = true;
            } 
            @endphp
            @if($showTab && $partial)
              <div class="tab-pane fade{{ $loop->first ? ' show active' : '' }}" id="{{ $tab['target'] }}" role="tabpanel" aria-labelledby="{{ $tab['target'] }}-tab">
                @if($isFinishedHackathon)
                  {{-- Pass finished hackathon data to partials --}}
                  @include($partial, [
                    'hackathon' => $finishedHackathon,
                    'teams' => $finishedHackathon['teams'] ?? collect([]),
                    'winners' => $finishedHackathon['winners'] ?? collect([]),
                    'isFinished' => true
                  ])
                @else
                  {{-- Regular hackathon partials with existing data --}}
                  @include($partial)
                @endif
              </div>
            @endif
          @endif
        @endif
      @endforeach
    </div>
    <!--/ Tabs Content -->
    
  </div>
</div>

@if($companyStep?->stype == 6 || $showStep == true)
<!-- Create Team Modal -->
@include('admin.hackathon.partials.create-team-modal')
@endif

<!-- Solution Details Modal -->
@include('admin.hackathon.partials.solution-modal')

<!-- Apply Modal (same as in index.html, can be a shared component in a real app) -->
@include('admin.hackathon.partials.apply-modal')

<!-- Terms Modal (nested for hackathon details page) -->
@include('admin.hackathon.partials.terms-modal')

<!-- Session Modal -->
@include('admin.clients.startup.partials._session_modal')

<div class="modal fade" id="sessionDetails" tabindex="-1" aria-labelledby="sessionDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header pb-4 px-12 bg-light">
        <h5 class="modal-title" id="sessionDetailsLabel">{{ __('admin.session_details_label') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row px-4 pb-4">
          <div class="col-md-8">
            <div class="session-main-info mb-4">
              <h4 class="mb-2 text-truncate" id="session-title"></h4>
              <p class="text-muted text-truncate" id="session-desc"></p>
              <div class="d-flex align-items-center mt-3">
                <span class="badge bg-success text-white me-2">
                  <i class="fas fa-video me-1"></i>
                  <span id="session-type"></span> 
                </span>
                <span class="badge bg-label-info me-2" id="session-duration-badge">
                  <i class="far fa-clock me-1"></i> 
                  <span id="session-duration"></span>
                </span>
                {{-- <span class="badge bg-light text-dark">
                  ID: #33
                </span> --}}
              </div>
            </div>

            <div class="session-details">
              <div id="trainer-info-wrapper"> <!-- Add this wrapper -->
                <div class="d-flex align-items-center py-3 border-bottom border-top">
                  <div class="rounded-circle me-2 w-px-40 h-px-40 d-flex justify-content-center align-items-center bg-label-light border" id="circle-info">
                    <i class="ti ti-school ti-lg text-dark"></i>
                  </div>
                  <div class="trainer-details mb-3" id="session-trainer-info">
                    <h6 class="mb-1">
                      {{ __('admin.Trainer Name') }}: <span id="session-trainer-name"></span>
                    </h6>
                    <small>
                      <i class="fas fa-envelope me-1"></i>
                      <strong>{{ __('admin.Trainer Email') }}:</strong>
                      <a href="#" class="cursor-pointer" id="session-trainer-email"></a>
                    </small>
                  </div>
                </div>
              </div>

              <div id="course-info-wrapper" style="display: none;">
                <div class="d-flex align-items-center py-3 border-bottom border-top">
                  <div class="rounded-circle me-2 w-px-40 h-px-40 d-flex justify-content-center align-items-center bg-label-light border" id="circle-info">
                    <i class="ti ti-book ti-lg text-dark"></i>
                  </div>
                  <div class="course-details mb-3" id="session-course-info">
                    <h6 class="mb-1">
                      {{ __('admin.Course Name') }}: 
                    </h6>
                    <span id="session-course-name"></span>
                  </div>
                </div>
              </div>


              <div class="row border-bottom" id="date-info">
                <div id="date-only-col" class="col-md-6" style="display: none;">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-calendar-alt me-2"></i>{{ __('admin.Date') }}</div>
                    <div id="session-date"></div>
                  </div>
                </div>
                <div id="start-date-col" class="col-md-6" style="display: none;">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-calendar-alt me-2"></i>{{ __('admin.StartDate') }}</div>
                    <div id="session-start-date"></div>
                  </div>
                </div>
                <div id="end-date-col" class="col-md-6" style="display: none;">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-calendar-check me-2"></i>{{ __('admin.EndDate') }}</div>
                    <div id="session-end-date"></div>
                  </div>
                </div>
              </div>

              <div class="row border-bottom" id="time-info" style="display: none;">
                <div class="col-md-6">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-clock me-2"></i>{{ __('admin.Start Time') }}</div>
                    <div id="session-start-time"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-clock me-2"></i>{{ __('admin.End Time') }}</div>
                    <div id="session-end-time"></div>
                  </div>
                </div>
              </div>

              <div class="py-3 border-bottom" id="location-info">
                <div class="info-label" id="text-label"><i class="fas fa-map-marker-alt me-2"></i>{{ __('admin.location_address') }}</div>
                <a id="session-location" href="#" target="_blank" class="text-primary text-decoration-none line-clamp-2"></a>
                <div id="session-address" class="text-muted"></div>
              </div>

              <div class="py-3 border-bottom" id="join-info">
                <div class="info-label" id="join-link-label"><i class="fas fa-link me-2"></i>{{ __('admin.join_link') }}</div>
                <div class="d-flex align-items-center">
                  <a id="join-link" href="#" class="text-primary text-decoration-none"
                    target="_blank">
                  </a>
                  <div id="copy-link" style="display: none">
                    <button class="btn btn-sm btn-outline-primary ms-2"
                      onclick="copyToClipboard('#join-link')">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="py-3 border-bottom" id="zoom-info">
                <div class="info-label"><i class="fas fa-id-card me-2"></i>{{ __('admin.meeting_id') }}</div>
                <div id="zoom-id" class="text-truncate"></div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 py-3 mt-4">
              <div id="join-button" style="display: none;">
                <button class="btn btn-primary">
                  <i class="fas fa-video me-2"></i>
                  {{ __('admin.Join') }}
                </button>
              </div>
              <button id="cancel-button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                <i class="fas fa-close me-2"></i>
                {{ __('admin.Close') }}
              </button>
            </div>
          </div>

          <div class="col-md-4">
            {{-- <div class="qr-code-container">
              <h6 class="mb-3">امسح الرمز للانضمام</h6>
              <img src="/api/placeholder/180/180" alt="QR Code" class="img-fluid mb-3"
                style="border: 1px solid #ddd; padding: 5px;">
              <div class="text-muted small">رمز الجلسة: https://qr.example.com/t/33-141-177</div>
            </div> --}}

            {{-- <div class="mt-4">
              <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                  <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>معلومات إضافية</h6>
                  <div class="small text-muted mb-2">
                    <i class="fas fa-code me-1"></i> معرف التدريب: #141
                  </div>
                  <div class="small text-muted mb-2">
                    <i class="fas fa-user me-1"></i> معرف المستخدم: #177
                  </div>
                  <div class="small text-muted mb-2">
                    <i class="fas fa-check me-1"></i> آخر تحديث: ٤ أغسطس ٢٠٢٤
                  </div>
                </div>
              </div>
            </div> --}}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/js/mentorship-session.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
      $('.table:not(.table-custom)').DataTable({
        paging: true,
        searching: true,
        ordering: $('.table').hasClass('table-custom') ? false : true,
        order: [[0, 'desc']],
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
      });
    }
  });
</script>
@stop

@section('pagescripts')
<script>
    $('.like-project-action').on('click', function (e) {
      e.preventDefault();
      let element = $(this);
      let teamProjectId = element.data('project');
      $.ajaxSetup({
        headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),},
      });

      $.ajax({
        type: "POST",
        url: "/dashboard/hackathon/likeProject",
        data: {
          teamProjectId: teamProjectId,
        },
        success: function (response) {
          element.parent().find('.likes-count').text(response.total);
          if( element.hasClass('fas') )
          {
            element.removeClass('fas');
            element.addClass('far');
          }
          else if( element.hasClass('far') )
          {
            element.removeClass('far');
            element.addClass('fas');
          }
        },
        error: function (error) {
          console.log("Error Occurred, like failed");
        },
      });
    })
  function submitApplicationDetails() {
    if (!document.getElementById("dh-termsAndConditions").checked) {
      alert(
        "{{ $l == 'en' ? 'Please agree to the terms and conditions.' : 'يرجى الموافقة على الشروط والأحكام.' }}"
      );
      return;
    }
    alert(
      "{{ $l == 'en' ? 'Your application has been sent successfully! You can now create your team.' : 'تم إرسال طلبك بنجاح! يمكنك الآن تكوين فريقك.' }}"
    );
    var applyModal = bootstrap.Modal.getInstance(
      document.getElementById("applyModalHackathonDetails")
    );
    applyModal.hide();
    const url = new URL(window.location);
    url.searchParams.set("applied", "true");
    window.location.href = url.href;
  }

  function joinTeam(id, teamName) {
    if (
      confirm(
        `{{ $l == "en" ? "Are you sure you want to request to join team" : "هل أنت متأكد أنك تريد طلب الانضمام إلى فريق" }} "${teamName}"{{ $l == "en" ? "?" : "؟" }}`
      )
    ) {
      fetch("{{ route('hackathon.RequestJoinTeam') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
        body: JSON.stringify({
          id: id,
        }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          return response.json();
        })
        .then((data) => {
          alert(
            `{{ $l == "en" ? "Join request sent to team" : "تم إرسال طلب الانضمام إلى فريق" }} "${teamName}".`
          );
          window.location.href = window.location.href;
        })
        .catch((error) => {
          alert(
            "{{ $l == 'en' ? 'There was an error sending the request.' : 'حدث خطأ أثناء إرسال الطلب.' }}"
          );
          console.error(error);
        });
    }
  }

  function leaveTeam() {
    if (
      confirm(
        `{{ $l == "en" ? "Are you sure you want to leave the current team?" : "هل أنت متأكد أنك تريد مغادرة الفريق الحالي؟" }}`
      )
    ) {
      alert(
        `{{ $l == "en" ? "You have left the team." : "لقد غادرت الفريق." }}`
      );
      const url = new URL(window.location);
      url.searchParams.delete("teamCreated");
      url.searchParams.delete("joinedTeamName");
      window.location.href = url.href;
    }
  }

  function showSubmissionTab() {
    var submissionTab = new bootstrap.Tab(
      document.getElementById("submission-tab-btn")
    );
    submissionTab.show();
    document
      .getElementById("submission")
      .scrollIntoView({ behavior: "smooth" });
  }

  document
    .getElementById("projectSubmissionForm")
    ?.addEventListener("submit", function (e) {
      e.preventDefault();
      const projectName = document.getElementById("projectName").value;
      const projectFiles = document.getElementById("projectFiles").files;

      if (!projectName.trim() || projectFiles.length === 0) {
        alert(
          "{{ $l == 'en' ? 'Please fill in the project name and upload the required files.' : 'يرجى تعبئة اسم المشروع ورفع الملفات المطلوبة.' }}"
        );
        return;
      }

      // Simulate upload
      const progressBarContainer = document.getElementById(
        "uploadProgressBarContainer"
      );
      const progressBar = document.getElementById("uploadProgressBar");
      progressBarContainer.style.display = "flex";
      let progress = 0;
      const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + "%";
        progressBar.textContent = progress + "%";
        progressBar.setAttribute("aria-valuenow", progress);
        if (progress >= 100) {
          clearInterval(interval);
          progressBarContainer.style.display = "none";
          alert(
            '{{ $l == "en" ? "Your project has been uploaded successfully!" : "تم رفع مشروعك بنجاح!" }}'
          );
          document.getElementById("already-submitted-message").style.display =
            "block";
          document.getElementById("projectSubmissionForm").style.display =
            "none";
          const url = new URL(window.location);
          url.searchParams.set("submitted", "true");
          window.history.pushState({}, "", url);
        }
      }, 200);
    });

  function saveDraft() {
    alert(
      "{{ $l == 'en' ? 'Project draft saved. You can return later to complete the upload.' : 'تم حفظ مسودة المشروع. يمكنك العودة لاحقاً لإكمال الرفع.' }}"
    );
  }

  function startCountdown(targetDate, elementId, containerElement, typeText) {
    const countdownElement = document.getElementById(elementId);
    if (!countdownElement || !containerElement) return;

    const interval = setInterval(() => {
      const now = new Date().getTime();
      const distance = new Date(targetDate).getTime() - now;

      if (distance < 0) {
        clearInterval(interval);
        containerElement.classList.remove("alert-info", "alert-warning");
        containerElement.classList.add("alert-success");
        countdownElement.innerHTML = `{{ $l == 'en' ? 'Time for' : 'حان وقت' }} ${typeText.replace("{{ $l == 'en' ? 'to ' : 'ل' }}", "")}{{ $l == 'en' ? '!' : '!' }}`;

        if (
          elementId === "countdown-timer" &&
          new URLSearchParams(window.location.search).get("id") === "1"
        ) {
          const url = new URL(window.location);
          url.searchParams.set("applied", "true");
          window.location.href = url.href;
        }
        if (elementId === "submission-countdown-timer") {
          const submissionTabLi = document.getElementById("submission-tab-li");
          if (submissionTabLi) {
            submissionTabLi.style.display = "list-item";
          }
        }
        return;
      }
      containerElement.style.display = "block";

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor(
        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
      );
      const minutes = Math.floor(
        (distance % (1000 * 60 * 60)) / (1000 * 60)
      );
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      countdownElement.innerHTML = `${days} {{ $l == 'en' ? 'days' : 'يوم' }} {{ $l == 'en' ? 'and' : 'و' }} ${hours} {{ $l == 'en' ? 'hours' : 'ساعة' }} {{ $l == 'en' ? 'and' : 'و' }} ${minutes} {{ $l == 'en' ? 'minutes' : 'دقيقة' }} {{ $l == 'en' ? 'and' : 'و' }} ${seconds} {{ $l == 'en' ? 'seconds' : 'ثانية' }}`;

    }, 1000);
  }

  function changestep(company, step) {
    if (confirm("{{ __('cycles.sure change step') }}")) {
      $.ajaxSetup({
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/screening/changestep",
        data: {
          company: company,
          step: step,
          // Names the program a disapproval comes from.
          cycle: "{{ $currentHackathon['cycle_id'] ?? '' }}",
        },
        success: function (response) {
          console.log(response);
          location.reload();
        },
        error: function (error) {
          console.log("Error Occurred, arrange failed");
        },
      });
    } else {
      location.reload();
    }
  }
    let urlParams = new URLSearchParams(window.location.search);
    let tab = urlParams.get('tab');

    if (typeof(tab) !== 'undefined' && tab !== null) {
        $("#" + tab).trigger("click");
    } else {
        // $("#overview-tab").trigger("click");
    }

    // عند الضغط على أي تبويب، أطبع الـ ID
    $(".nav-pills .nav-link").on("click", function() {
        let tabId = $(this).attr("id");
        console.log("تم الضغط على التاب:", tabId);

        // لو عايز تحدث الرابط في المتصفح بدون تحديث الصفحة:
        let newUrl = new URL(window.location.href);
        newUrl.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', newUrl);
    });

    function ShowDetails(trainingId) { // Renamed 'training' to 'trainingId' for clarity
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Select modal elements once for efficiency and clarity
    const $modal = $('#sessionDetails');
    const $title = $('#session-title');

    const $dsc = $('#session-desc');
    const $sessionType = $('#session-type');

    const $location = $('#session-location');
    const $address = $('#session-address');

    const $trainerName = $('#session-trainer-name');
    const $trainerEmailLink = $('#session-trainer-email');

    const $datetime = $('#session-datetime');
    const $sessionDate = $('#session-date');
    const $sessionStartDate = $('#session-start-date');
    const $sessionEndDate = $('#session-end-date');
    const $sessionStartTime = $('#session-start-time');
    const $sessionEndTime = $('#session-end-time');
    const $sessionDuration = $('#session-duration');
    const $zoomId = $('#zoom-id');

    // Clear previous content (important if modal is reused)
    $title.text('Training Details'); // Default title
    $dsc.empty();
    $location.empty();
    $location.attr('href', '#'); // Reset location link
    $address.empty();
    $trainerName.empty();
    $trainerEmailLink.empty();
    $trainerEmailLink.attr('href', '#'); // Reset link
    $datetime.empty();
    $sessionStartTime.empty();
    $sessionEndTime.empty();
    $sessionDuration.empty();
    $zoomId.empty();
    $('#join-link').attr('href', '#'); // Reset link
    $('#join-link').text(''); // Reset text
    // --- End Reset ---

    $.ajax({
        type: "POST",
        url: `/dashboard/trainings/getdsc/${trainingId}`, // Using template literal for cleaner URL
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        // dataType: 'json', // Explicitly expect JSON if your backend always sends it
        success: function(response) {
          console.log(response);
            
            // Reset all visibility states
            $('#text-label').show();
            $('#location-info').show();
            $('#join-info').show();
            $('#zoom-info').show();
            $('#time-info').hide();
            
            // 1. Modal Title
            if (response.title) {
              $title.text(response.title);
            }

            // 2. Description
            if (response.dsc) {
              $dsc.html(response.dsc);
            }

            // 3. Session Type
            if (response.type) {
              if (response.type === 'sessions') {
                $sessionType.text('{{ __('cycles.sessions') }}');
              } else if (response.type === 'offline') {
                $sessionType.text('{{ __('cycles.offline') }}');
              } else if (response.type === 'online') {
                $sessionType.text('{{ __('cycles.online') }}');
              } else {
                $sessionType.text('{{ __('admin.course') }}');
              }
            }

            // 4. Location
            if (response.type == 'offline') {
              const locationText = response.training_location || response.location || '';
              const addressText = response.training_Address || '';
              
              // Check if locationText is already a map link
              const isMapLink = locationText.includes('google.com/maps') || locationText.includes('maps.google');
              
              // Set location text and make it clickable
              if (isMapLink) {
                // If locationText is already a map link, use it directly
                $location.text(locationText);
                $location.attr('href', locationText);
                $address.text(addressText);
           
              } else if (addressText) {
                const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(addressText)}`;
                $location.text(locationText);
                $location.attr('href', mapUrl);
                $address.text(addressText);
              } else if (locationText) {
                // Fallback to location name if address is not available
                const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(locationText)}`;
                $location.text(locationText);
                $location.attr('href', mapUrl);
                $('#map-link-btn').attr('href', mapUrl);
                $('#session-location-link').show();
              } else {
                $location.text('');
                $location.attr('href', '#');
              }
            } else{
              $('#text-label').hide();
              $('#location-info').hide();
            }

            // 5. Trainer Details
                if (response.type === 'sessions' || response.type === 'offline') {
                  $('#trainer-info-wrapper').show();
                  $('#session-duration-badge').show();
                  $trainerName.text(response.trainer_name);
                  $trainerEmailLink.text(response.trainer_email);
                  $trainerEmailLink.attr('href', `mailto:${response.trainer_email}`);
            } else {
             $('#trainer-info-wrapper').hide();
              $('#session-duration-badge').hide();
           }


            // 6. Date and Time
            if (response.date && (response.type === 'sessions' || response.type === 'offline')) {
              $('#date-only-col').show();
              $('#start-date-col').hide();
              $('#end-date-col').hide();
              $sessionDate.text(response.date);
              
              // Show time information for sessions and offline training
              if (response.starttime || response.endtime) {
                $('#time-info').show();
                $sessionStartTime.text(response.starttime || '-');
                $sessionEndTime.text(response.endtime || '-');
              } else {
                $('#time-info').hide();
              }
            } else {
              $('#date-only-col').hide();
              $('#start-date-col').show();
              $('#end-date-col').show();
              $('#time-info').hide();
              
              if (response.datetime1) {
                $sessionStartDate.text(response.datetime1);
              }
              if (response.enddatetime) {
                $sessionEndDate.text(response.enddatetime);
              }
            }

            let duration = response.duration || '';

            const startTime = response.starttime;
            const endTime = response.endtime;
            
            // Calculate duration from time strings
            if (startTime && endTime) {
              const start = moment(startTime, 'h:mm A');
              const end = moment(endTime, 'h:mm A');
              
              // Handle case where end time is next day
              if (end.isBefore(start)) {
                end.add(1, 'day');
              }
              
              duration = end.diff(start, 'minutes');
            }
            
            if (duration) {
              $sessionDuration.text(duration + ' ' + '{{ __('admin.minutes') }}');
            }

            // 7. Duration
            if (duration && (response.type === 'sessions' || response.type === 'offline')) {
              $sessionDuration.text(duration + ' ' + '{{ __('admin.minutes') }}');
            }

            // 8. Join Link
            if (response.type == 'sessions') {
              $('#join-link').attr('href', response.join_url);
              $('#join-link').text(response.join_url);
              $('#join-button').show();
              $('#copy-link').show();
            } else {
              $('#join-button').hide();
              $('#copy-link').hide();
              $('#join-link').hide();
              $('#join-info').hide();
            }

            // 9. Zoom ID
            if (response.type == 'sessions') {
              $zoomId.text(response.zoom_id);
            } else{
              $('#zoom-info').hide();
            }

            // 10. Course
            if (response.type === 'online' && response.course_Name) {
              $('#course-info-wrapper').show();
              $('#session-course-name').text(response.course_Name);
            } else {
              $('#course-info-wrapper').hide();
            }

            $modal.modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            console.error("Response Text:", jqXHR.responseText);
            // Consider a more user-friendly error display, e.g., a toast notification or an error message in the modal
            alert("An error occurred while fetching details. Please try again.");
        }
    });
}
</script>
@stop

