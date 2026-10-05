@extends('layouts.admin')

@section('content')
<div class="">
  <div class="">
    <!-- Header Section -->
    <div class="col-12 mb-8 text-white text-center p-12 rounded"
      style="background: linear-gradient(180deg, #7367f0 0%, #6610f2 100%);">
      <div class="container">
        <h1 class="display-4 fw-bold text-white">
          {{ $l == 'en' ? 'Innovate, Collaborate, and Propel into the Future!' : 'ابتكر، تعاون، وانطلق نحو المستقبل!' }}
        </h1>
        <p class="lead">
          {{ $l == 'en' ? 'Join our virtual and hybrid hackathons, develop solutions to real-world problems, and qualify
          for advanced support stages.' : 'انضم إلى هاكثوناتنا الافتراضية والهجينة، طور حلولاً لمشكلات حقيقية، وتأهل
          لمراحل دعم متقدمة.' }}
        </p>
        <a href="#upcoming-hackathons" class="btn btn-label-primary btn-lg fw-bold mt-3">
          {{ $l == 'en' ? 'Discover Hackathons' : 'اكتشف الهاكثونات' }} <i
            class="ti ti-circle-arrow-down ti-md ms-2"></i>
        </a>
      </div>
    </div>
    <section id="multiple-column-form">
      @if (session()->has('message'))
      <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
        {{ __('admin.' . session()->get('message')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
    </section>
    <!-- Filter Section -->
    <div class="row mb-8" id="upcoming-hackathons">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form class="row g-3 align-items-end" action="/dashboard/hackathon" method="get">

              <div class="col-md-4">
                <label class="form-label fw-bold">{{ $l == 'en' ? 'Search' : 'البحث' }}</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="ti ti-search"></i></span>
                  <input type="text" class="form-control" name="searchHackathon"
                    value="{{ request('searchHackathon') }}"
                    placeholder="{{ $l == 'en' ? 'Search for hackathon...' : 'ابحث عن هاكثون...' }}">
                </div>
              </div>

              {{-- <div class="col-md-3">
                <label class="form-label fw-bold">{{ $l == 'en' ? 'Type' : 'النوع' }}</label>
                <select class="form-select select2">
                  <option selected>{{ $l == 'en' ? 'All Types' : 'جميع الأنواع' }}</option>
                  <option value="1">{{ $l == 'en' ? 'Virtual' : 'افتراضي' }}</option>
                  <option value="2">{{ $l == 'en' ? 'Hybrid' : 'هجين' }}</option>
                  <option value="3">{{ $l == 'en' ? 'In-Person' : 'حضوري' }}</option>
                </select>
              </div> --}}
              {{-- <div class="col-md-3">
                <label class="form-label fw-bold">{{ $l == 'en' ? 'Theme' : 'المحور' }}</label>
                <select class="form-select select2">
                  <option selected>{{ $l == 'en' ? 'All Themes' : 'جميع المحاور' }}</option>
                  <option value="1">{{ $l == 'en' ? 'FinTech' : 'التقنية المالية' }}</option>
                  <option value="2">{{ $l == 'en' ? 'AI' : 'الذكاء الاصطناعي' }}</option>
                  <option value="3">{{ $l == 'en' ? 'Sustainability' : 'الاستدامة' }}</option>
                </select>
              </div> --}}

              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="ti ti-search me-2"></i>
                  {{ $l == 'en' ? 'Search' : 'ابحث' }}
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming & Ongoing Hackathons -->
    <div class="row">
      <div class="col-12">
        <h5 class="fw-bold">
          <i class="ti ti-calendar-event ti-md me-1"></i> {{ $l == 'en' ? 'Upcoming & Ongoing Hackathons' : 'الهاكثونات
          القادمة والجارية' }}
        </h5>
      </div>
    </div>

    <div class="row g-4 mb-8">
      @php
	$user = Auth::user();
 
  if($user->team_member != null){
	$user_cycle_id = $user->team_member?->teamFormation?->user?->company?->cycle ?? null;
  }else{
	$user_cycle_id = $user->company?->cycle ?? null;
  }

	$isExistinghackathon = $user_cycle_id ? in_array($user_cycle_id, $hackathon_ids, true) : false;
      @endphp

      @foreach ($hackathons as $hackathon)
      @php

      $user = Auth::user();
      $step_id = $user->company?->step;
      if($hackathon['step_id'] == $step_id){
      $apply_q = "SELECT * FROM form_data WHERE user_id = ? ";
      if($step_id != null){
      $apply_q .= "AND step_id = ?";
      $apply = collect(DB::select($apply_q, [$user->id, $step_id]));
      }else{
      $apply = collect(DB::select($apply_q, [$user->id]));
      }
      }else{
      $apply = null;
      }

      $team_q = "SELECT * FROM team_formations WHERE user_id = ? ";
      if($step_id != null){
      $team_q .= "AND step_id = ?";
      $team = collect(DB::select($team_q, [$user->id, $step_id]));
      }else{
      $team = collect(DB::select($team_q, [$user->id]));
      }



      if($step_id != null){
      $stypeStep_q = "SELECT * FROM steps WHERE id = ?";
      $stypeStep = collect(DB::select($stypeStep_q, [$step_id]));
      }else{
      $stypeStep = null;
      }


      @endphp
      <div class="col-lg-4 col-md-6">
        @include('admin.hackathon.partials.hackathon-card', ['hackathon' => $hackathon, 'l' => $l, 'apply' => $apply,
        'team' => $team, 'stypeStep' => $stypeStep, 'user' => $user, 'isExistinghackathon' => $isExistinghackathon])
      </div>
      @endforeach
    </div>

    <!-- Past Hackathons -->
    <div class="row" id="past-hackathons">
      <div class="col-12">
        <h5 class="fw-bold">
          <i class="ti ti-calendar-check ti-md me-1"></i> {{ $l == 'en' ? 'Past Hackathons' : 'الهاكثونات السابقة' }}
        </h5>
      </div>
    </div>

    <div class="row g-4 mb-12">
      @if (count($pastHackathons) > 0)
      <div class="col-12">
        <div class="card">
          <div class="card-body p-0">
            <div class="list-group list-group-flush" id="pastHackathonsList">
              @foreach ($pastHackathons as $index => $hackathon)
                <div class="past-hackathon-item" style="{{ $index > 3 ? 'display:none;' : '' }}">
                  @include('admin.hackathon.partials.hackathon-item', ['hackathon' => $hackathon, 'l' => $l])
                </div>
              @endforeach
            </div>
            @if (count($pastHackathons) > 4)
            <div class="text-center py-3">
             <button id="loadMorePastHackathons" class="btn btn-outline-primary">
               <span>{{ $l == 'en' ? 'Show More' : 'عرض المزيد' }}</span>
               <i class="ti ti-chevron-down ms-2"></i>
            </button>
            </div>
            @endif
          </div>
        </div>
      </div>
      @else
      <div class="col-12">
        <div class="alert alert-info text-center">
          {{ $l == 'en' ? 'No past hackathons available.' : 'لا توجد هاكثونات سابقة متاحة.' }}
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Apply Modal -->
@include('admin.hackathon.partials.hackathon-apply', ['l' => $l])

<!-- Terms Modal -->
@include('admin.hackathon.partials.hackathon-terms', ['l' => $l])
@stop

@section('pagescripts')
 <script>
  $(function() {
    let shown = 4;
    const total = {{ count($pastHackathons) }};
    const $btn = $('#loadMorePastHackathons');

    function updateButton() {
      if (shown >= total) {
        $btn.html('<span>{{ $l == "en" ? "Show Less" : "عرض أقل" }}</span><i class="ti ti-chevron-up ms-2"></i>');
      } else {
        $btn.html('<span>{{ $l == "en" ? "Show More" : "عرض المزيد" }}</span><i class="ti ti-chevron-down ms-2"></i>');
      }
    }
    $btn.on('click', function() {
      if (shown >= total) {
        // Show less: hide all except first 4
        $('.past-hackathon-item').slice(4).slideUp();
        shown = 4;
        updateButton();
      } else {
        // Show more: show next 4
        $('.past-hackathon-item').slice(shown, shown + 4).slideDown();
        shown += 4;
        updateButton();
      }
    });
    updateButton();
  });
</script>
<script>
  // Initialize tooltips
    $(document).ready(function() {
      $('[data-bs-toggle="tooltip"]').tooltip();
      
      // Handle external team checkbox
      $('#externalTeamCheck').on('change', function() {
        $('#externalTeamDetails').toggle(this.checked);
      });
    });

    function submitApplication() {
      // Basic validation
      if (!document.getElementById('termsAndConditions').checked) {
        // Using SweetAlert2 for better UX
        releaseAlert('warning', {
          title: '{{ $l == 'en' ? 'Terms Required' : 'الشروط مطلوبة' }}',
          message: '{{ $l == 'en' ? 'Please agree to the terms and conditions.' : 'يرجى الموافقة على الشروط والأحكام.' }}',
          confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
          confirmColor: 'primary',
          hideCancel: true
        });
        return;
      }
      
      // Show success message
      releaseAlert('success', {
        title: '{{ $l == 'en' ? 'Application Submitted!' : 'تم إرسال الطلب!' }}',
        message: '{{ $l == 'en' ? 'Your application has been submitted successfully! You will be redirected to the hackathon details page.' : 'تم إرسال طلبك بنجاح! سيتم توجيهك إلى صفحة تفاصيل الهاكثون.' }}',
        confirmText: '{{ $l == 'en' ? 'OK' : 'موافق' }}',
        confirmColor: 'primary'
      }).then((ok) => {
        // Close modal
        $('#applyModal1').modal('hide');
        // Redirect or update UI
        // window.location.href = "hackathon-details.html?id=1&applied=true";
      });
    }
</script>
@stop
