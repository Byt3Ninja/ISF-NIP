@extends('layouts.admin')

@section('title', __('dashboard.Mentors'))
@section('subTitle', __('dashboard.Mentors'))

@section('content')
@if (!Auth::user()->first_name_ar)
<div class="d-none">
  {!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
</div>
@endif

<!-- Content wrapper -->
<div class="">
  <div class="">
    <div class="content-body">
      <!-- Alert -->
      @if (session()->has('msg'))
      <div class="row">
        <div class="col-md-12">
          <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
            role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center">
              <i class="ti ti-error"></i>
              <span> {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }} </span>
            </div>
          </div>
        </div>
      </div>
      @endif
      <!-- / Alert -->

      <!-- Header -->
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
              <div class="d-flex bg-label-warning p-2 justify-content-center align-items-center rounded shadow">
                <div class="bg-white d-flex justify-content-center align-items-center rounded"
                  style="width: 40px; height: 40px; shadow">
                  <i class="ti ti-users ti-24px"></i>
                </div>
                <div class="mx-4" style="height: 50px;">
                  <p class="text-black fw-medium fs-5" style="margin-bottom: -3px;">
                    {{ __('dashboard.Mentors') }}
                  </p>
                  <span class="fs-5">
                    <!-- Total Mentors Count -->
                    {{ count($mentors) }}
                  </span>
                </div>
              </div>
              <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mt-4 mt-md-0">
                <!-- Search Bar -->
                <div class="input-group input-group-merge search-form" style="width: 300px;">
                  <span class="input-group-text">
                    <i class="ti ti-search text-muted"></i>
                  </span>
                  <input 
                    type="text" 
                    id="searchInput" 
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="{{ __('admin.Search') }} {{ __('dashboard.Mentors') }}" 
                  />
                </div>
                
                <!-- Reset Button (shown when filters are active) -->
                @php
                  $hasActiveFilters = request()->hasAny(['programFilter', 'rateSearch', 'expertises', 'linkedin', 'enrolledInProgramFilter', 'search']) || 
                                    (request()->has('expertises') && !empty(request('expertises')));
                @endphp
                @if($hasActiveFilters)
                  <a href="{{ route('mentors.list') }}" class="btn btn-outline-secondary" title="{{ __('admin.Reset') }}">
                    <i class="ti ti-refresh me-2"></i>
                    {{ __('admin.Reset') }}
                  </a>
                @endif
                
                <!-- Filter Button -->
                <button type="button" class="btn btn-label-secondary text-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                  <i class="ti ti-filter me-2"></i>
                  <span class="mx-1">{{ __('cycles.Show_Filters') }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Header -->

      <!-- Mentors -->
      <section class="mb-10 mt-8">
        <div class="row" id="mentorsContainer">
          <!-- Mentors List -->
          @if(count($mentors) > 0)
          @foreach ($mentors as $key => $mentor)
          <!-- Mentor Card -->
          <div class="col-md-4 rounded mb-6">
            <div class="position-relative">
              <img 
                src="{{ $mentor->image_url ?? '/images/placeholder.jpg' }}" 
                onerror="this.onerror=null;this.src='/images/placeholder.jpg';"
                alt="Mentor Image" 
                class="img-fluid rounded mb-2" 
                style="height: 293px; width: 100%; object-fit: cover;"
              >
            </div>
            <div class="card card-body mx-2 p-4"
              style="height: 380px; z-index: 1; margin-top: -25px; border-radius: 15px;">
              <div
                class="bg-white shadow-sm border border-label-secondary rounded px-2 py-1 d-flex justify-content-between align-items-center mb-2"
                style="height: 55px;">
                <a href="{{ route('mentors.profile_view', $mentor->id) }}" class="d-flex flex-column">
                  <span class="text-black fw-bold fs-6">{{ $mentor->name }}</span>
                  <small class="text-secondary">{{ $mentor->title }}</small>
                </a>
                @if ($mentor->bio->linkedin ?? 0)
                <a title="Linkedin URL" target="_blank" href="{{ $mentor->bio->linkedin }}">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a>
                @endif
              </div>
              
              <!-- Mentor Bio -->
              <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="ti ti-bubble-text ti-sm me-2"></i>
                  <p class="fs-6 fw-bold text-black mb-0">{{ __('dashboard.Bio') }}</p>
                </div>
                @if ($mentor->bio && !empty(trim($mentor->bio->{'bio_' . $l})))
                <div style="max-height: 80px; overflow-y: auto;">
                  <small class="text-muted line-clamp-3">
                    {{ $mentor->bio->{'bio_' . $l} }}
                  </small>
                </div>
                @else
                <div class="text-muted">
                  <small>{{ __('admin.N/A') }}</small>
                </div>
                @endif
              </div>
              <!-- / Mentor Bio -->

              <!-- Mentor Expertises -->
              <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                  <i class="ti ti-bulb ti-sm me-2"></i>
                  <p class="fs-6 fw-bold text-dark mb-0">{{ __('admin.Expertises') }}</p>
                </div>
                @if ($mentor->mentorExpertises->isNotEmpty())
                <div class="d-flex flex-wrap gap-1" style="max-height: 70px; overflow-y: auto;">
                  @php
                  $expertises = $mentor->mentorExpertises;
                  @endphp
                  @foreach ($expertises as $expert)
                  <span class="badge bg-label-warning rounded-pill px-2 py-1 small">
                    {{ $expert->title }}
                  </span>
                  @endforeach
                </div>
                @else
                <div class="text-muted">
                  <small>{{ __('admin.N/A') }}</small>
                </div>
                @endif
              </div>
              <!-- / Mentor Expertises -->

              <!-- Stats Section -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <div class="bg-label-secondary rounded p-2 text-center">
                    <div class="d-flex align-items-center text-dark">
                      <i class="ti ti-books ti-sm me-1"></i>
                      <small class="fw-semibold me-2">{{ __('cycles.Cycles') }}</small>
                      <span class="fw-bold text-dark">{{ $mentor->mentorCycles->count() }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="bg-label-secondary rounded p-2 text-center">
                    <div class="d-flex align-items-center text-dark">
                      <i class="ti ti-star ti-sm text-dark me-1"></i>
                      <small class="fw-semibold me-2">{{ __('admin.Rating') }}</small>
                      @php
                      $rateMentorAVG = \App\MentorshipRequest::where('mentor_id', $mentor->id)
                      ->where('approved', 'Yes')
                      ->avg('rating');
                      @endphp
                      <span class="fw-bold text-dark">{{ number_format($rateMentorAVG ?: 0, 1) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- / Stats Section -->

              <!-- Action Button -->
              <div class="row px-4 mt-auto">
                @php
                  $canRequestMentorship = auth()->user()->company && 
                                         auth()->user()->company->cycle != 0 && 
                                         (auth()->user()->hasRole('Registered') || 
                                          (!auth()->user()->hasRole('Mentor') && !auth()->user()->hasRole('Admin')));
                  
                  if ($canRequestMentorship) {
                    $getCycle = auth()->user()->company->cycledata;
                  }
                @endphp

                @if ($canRequestMentorship && auth()->user()->company->id && $mentor->id)
                 @if($mentor->schedule->isNotEmpty())
                    <button 
                      type="button" 
                      class="btn btn-primary" 
                      onclick="if (typeof fetchSlots === 'function') { fetchSlots({{ $mentor->id }}, {{ $getCycle->id }}, {{ $getCycle->organization_id }}); }"
                    >
                      {{ __('cycles.Request_Mentorship') }}
                      <i class="ti ti-calendar-plus ms-2"></i>
                    </button>
                  @else
                    <button type="button" class="btn btn-primary" disabled>
                      {{ __('cycles.Not_Available_Request_Mentorship') }}
                      <i class="ti ti-calendar-plus ms-2"></i>
                    </button>
                  @endif
                @else
                  <a href="{{ route('mentors.profile_view', $mentor->id) }}" class="btn btn-primary">
                    {{ __('admin.View Profile') }}
                  </a>
                @endif
              </div>
            </div>
          </div>
          <!-- Mentor Card -->
            @if ($canRequestMentorship && auth()->user()->company->id)
              @include('partials.book-session-modal')
            @endif
          @endforeach
          @else
          <!-- No Mentors Result -->
          <div class="col-12" id="noMentorsResult">
            @if(request()->has('programFilter') && request('programFilter') != '')
              @include('includes.no-result', ['message' => __('cycles.No_Mentors_For_Selected_Program')])
            @else
              @if(request()->get('enrolledInProgramFilter') == '1')
                @include('includes.no-result', ['message' => __('cycles.No_Mentors_Enrolled')])
              @else
                @include('includes.no-result', ['message' => __('cycles.No_Mentors_Found')])
              @endif
            @endif
          </div>
          @endif
          <!-- / Mentors List -->
          
          <!-- Hidden No Search Results Template -->
          <div class="col-12 d-none" id="noSearchResultsTemplate">
            @include('includes.no-result', ['message' => __('admin.No Result')])
          </div>
        </div>
      </section>
      <!-- / Mentors -->
    </div>
  </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form method="GET" action="{{ route('mentors.list') }}">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">
            <i class="ti ti-filter me-2"></i>
            {{ __('admin.Filter') }} {{ __('dashboard.Mentors') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Program Filter -->
          <div class="form-group mb-4">
            <label for="programFilter" class="form-label">{{ __('cycles.Cycle') }}</label>
            <select name="programFilter" id="programFilter" class="form-select select2" data-placeholder="{{ __('admin.Select Program') }}">
              <option value="">{{ __('admin.All_Programs') }}</option>
              @if(isset($cycles) && count($cycles) > 0)
                @foreach ($cycles as $cycle)
                  <option value="{{ $cycle->id }}" {{ request('programFilter') == $cycle->id ? 'selected' : '' }}>
                    {{ $cycle->title }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Rate Filter -->
          <div class="form-group mb-4">
            <label for="rateSearch" class="form-label">{{ __('admin.rating') }}</label>
            <select name="rateSearch" id="rateSearch" class="form-select select2" data-placeholder="{{ __('admin.select_rating') }}">
              <option value="">{{ __('cycles.Any_Rating') }}</option>
              @foreach ([1, 2, 3, 4, 5] as $index => $rating)
                <option value="{{ $rating }}" {{ request('rateSearch') == $rating ? 'selected' : '' }}>
                  {{ $index !== 4 ? '+' : '' }} {{ $rating }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Expertise Filter -->
          <div class="form-group mb-4">
            <label for="expertises" class="form-label">{{ __('admin.Expertises') }}</label>
            <select name="expertises[]" id="expertises" class="form-select select2" multiple="multiple" data-placeholder="{{ __('admin.select_expertises') }}">
              @php
              $expertises = App\Expertise::where('parent_id', null)->get();
              $selectedExpertises = request('expertises', []);
              @endphp
              @foreach ($expertises as $expertise_group)
              <optgroup label="{{ $l == 'ar' ? $expertise_group->title_ar : $expertise_group->title }}">
                @foreach ($expertise_group->children as $expertise)
                <option value="{{ $expertise->id }}" {{ in_array($expertise->id, $selectedExpertises) ? 'selected' : '' }}>
                  {{ $l == 'ar' ? $expertise->title_ar : $expertise->title }}
                </option>
                @endforeach
              </optgroup>
              @endforeach
            </select>
          </div>

          <!-- LinkedIn Profile Filter -->
          <div class="form-group mb-4">
            <label class="form-label">
              {{ __('cycles.Has_LinkedIn_Profile') }}
            </label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="linkedin" value="" 
                       id="linkedinAll" {{ !request()->has('linkedin') || request('linkedin') === '' || is_null(request('linkedin')) ? 'checked' : '' }}>
                <label class="form-check-label" for="linkedinAll">
                  {{ __('admin.All') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="linkedin" value="1"
                       id="linkedinYes" {{ request('linkedin') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="linkedinYes">
                  {{ __('admin.Yes') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="linkedin" value="0"
                       id="linkedinNo" {{ request('linkedin') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="linkedinNo">
                  {{ __('admin.No') }}
                </label>
              </div>
            </div>
          </div>

          <!-- Enrolled in Program Filter -->
          <div class="form-group mb-4">
            <label class="form-label">
              {{ __('cycles.Enrolled_Program') }}
            </label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="enrolledInProgramFilter" value=""
                       id="enrolledAll" {{ !request()->has('enrolledInProgramFilter') || request('enrolledInProgramFilter') === '' || is_null(request('enrolledInProgramFilter')) ? 'checked' : '' }}>
                <label class="form-check-label" for="enrolledAll">
                  {{ __('admin.All') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="enrolledInProgramFilter" value="1"
                       id="enrolledYes" {{ request('enrolledInProgramFilter') == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="enrolledYes">
                  {{ __('admin.Yes') }}
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="enrolledInProgramFilter" value="0"
                       id="enrolledNo" {{ request('enrolledInProgramFilter') == '0' ? 'checked' : '' }}>
                <label class="form-check-label" for="enrolledNo">
                  {{ __('admin.No') }}
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="ti ti-x me-2"></i>
            {{ __('admin.Cancel') }}
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-filter me-2"></i>
            {{ __('cycles.Apply_Filters') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
$(function () {
  // Debounce helper
  const debounce = (fn, delay = 1000) => {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  };

  // Create debounced handler ONCE
  const handleSearch = debounce((value) => {
    const url = new URL(window.location);
      value
      ? url.searchParams.set('search', value)
      : url.searchParams.delete('search');

      window.location.href = url.href;
    }, 1000);

  // Bind event
  $('#searchInput').on('keyup', function () {
    handleSearch(this.value.toLowerCase());
  });
});
</script>
@stop