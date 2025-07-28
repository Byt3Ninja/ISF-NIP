@extends('layouts.hr-admin')

@section('style')
<!-- Additional Styles -->
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
<link rel="stylesheet" href="/css/hackathon.css" />
@stop

@php
$user = Auth::user();

$tabsData = [
  ['overview', 'bi-card-text', $l == 'en' ? 'Overview' : 'نظرة عامة'],
  // ['themes', 'bi-lightbulb-fill', $l == 'en' ? 'Themes' : 'المحاور'],
  // ['schedule', 'bi-calendar2-week-fill', $l == 'en' ? 'Schedule' : 'جدول الأعمال'],
  // ['prizes', 'bi-trophy-fill', $l == 'en' ? 'Prizes' : 'الجوائز'],
  // ['evaluation', 'bi-clipboard-check-fill', $l == 'en' ? 'Evaluation Criteria' : 'معايير التقييم'],
  ['mentors', 'bi-person-video3', $l == 'en' ? 'Mentors & Judges' : 'المرشدون والمحكمون'],
  ['teams', 'bi-people-fill', $l == 'en' ? 'Teams' : ' الفرق'],
  ['results-winners', 'bi-trophy-fill', $l == 'en' ? 'Results & winners' : 'النتائج والفائزون'],
  ['submission', 'bi-upload', $l == 'en' ? 'Project Submission' : 'رفع المشاريع'],
  // ['results', 'bi-award-fill', $l == 'en' ? 'Results' : 'النتائج'],
  ['mentorship', 'bi-award-fill', $l == 'en' ? 'Mentorship & Workshops' : 'الارشاد وورش العمل'],
  ['submissions', 'bi-award-fill', $l == 'en' ? 'Submissions' : 'الاجابات'],
];

$tabs = collect($tabsData)->map(function ($tab) {
  return ['target' => $tab[0], 'icon' => $tab[1], 'label' => $tab[2]];
})->toArray();
@endphp

@section('content')
<div class="row">
  <div class="container mt-4">
    <!-- Hackathon Details Banner -->
    @include('admin.hackathon.partials.details-banner')
    

    <!-- Countdown Timers -->
    @include('admin.hackathon.partials.countdown')
    <!--/ Countdown Timers -->

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs nav-fill mb-4 pb-4" id="hackathonTab" role="tablist">
      @foreach($tabs as $tab)
      <li class="nav-item me-1" role="presentation">
        <button class="nav-link rounded {{ $loop->first ? 'active' : '' }}" id="{{ $tab['target'] }}-tab"
          data-bs-toggle="tab" data-bs-target="#{{ $tab['target'] }}" type="button" role="tab"
          aria-controls="{{ $tab['target'] }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
          <i class="{{ $tab['icon'] }} me-2"></i> {{ $tab['label'] }}
        </button>
      </li>
      @endforeach
    </ul>
    <!--/ Tabs Navigation -->

    <!-- Tabs Content -->
    <div class="tab-content p-0 mb-12" id="hackathonTabContent">
      @foreach($tabs as $tab)
      @php
      // Map tab id to partial view
      $partials = [
      'overview' => 'admin.hackathon.partials.about-tab',
      'themes' => 'admin.hackathon.partials.themes-tab',
      'schedule' => 'admin.hackathon.partials.schedule-tab',
      'prizes' => 'admin.hackathon.partials.prizes-tab',
      'evaluation' => 'admin.hackathon.partials.evaluation-tab',
      'mentors' => 'admin.hackathon.partials.mentors-tab',
      'teams' => 'admin.hackathon.partials.teams-tab',
      'results-winners' => 'admin.hackathon.partials.winners-tab',
      'submission' => 'admin.hackathon.partials.projects-tab',
      'results' => 'admin.hackathon.partials.results-tab',
      'mentorship' => 'admin.hackathon.partials.mentorship-tab',
      'submissions' => 'admin.hackathon.partials.submissions-tab',
      ];
      $partial = $partials[$tab['target']] ?? null;
      // Tab visibility conditions
      $showTab = true;
      if ($tab['target'] === 'teams' && ($companyStep?->stype == 1)) $showTab = false;
      if ($tab['target'] === 'submissions' && !($user->hasRole('admin') || $user->hasRole('organizationmanager')))
      $showTab = false;
      @endphp
      @if($showTab && $partial)
      <div class="tab-pane fade{{ $loop->first ? ' show active' : '' }}" id="{{ $tab['target'] }}" role="tabpanel"
        aria-labelledby="{{ $tab['target'] }}-tab">
        @include($partial)
      </div>
      @endif
      @endforeach
    </div>
    <!--/ Tabs Content -->
    
  </div>
</div>

@if($companyStep?->stype == 6)
<!-- Create Team Modal -->
@include('admin.hackathon.partials.create-team-modal')
@endif

<!-- Solution Details Modal -->
@include('admin.hackathon.partials.solution-modal')

<!-- Apply Modal (same as in index.html, can be a shared component in a real app) -->
@include('admin.hackathon.partials.apply-modal')

<!-- Terms Modal (nested for hackathon details page) -->
@include('admin.hackathon.partials.terms-modal')

{{--
<footer class="footer mt-auto py-3 bg-dark text-white">
  <div class="container text-center">
    <span>
      © 2024
      {{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات. جميع الحقوق
      محفوظة.' }}
    </span>
  </div>
</footer> --}}
@stop

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && $.fn.DataTable) {
      $('.table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
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
</script>
@stop

