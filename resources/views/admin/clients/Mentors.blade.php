@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/widgets.css">
<link rel="stylesheet" type="text/css"
  href="/app-assets/css{{ $l == 'ar' ? '-rtl' : '' }}/pages/dashboard-ecommerce.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
<!-- Only keep datepicker for the commented modal functionality -->
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<style>
  .radius-15 {
    border-radius: 15px;
  }

  .custom-shadow {
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .line-clamp-4 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
    overflow: hidden;
    text-oveflow: ellipsis;
  }
</style>
@stop

@section('content')
@if (!Auth::user()->first_name_ar)
<div class="d-none">
  {!! redirect()->route('user.profile_view')->with(['msg' => 'WaitingYourRedirect', 'class' => 'danger']) !!}
</div>
@endif

<!-- Content wrapper -->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
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
      {{-- <h4 class="fw-bold text-primary">{{ __('dashboard.Mentors') }}</h4> --}}

      <div class="card" style="border-radius: 20px;">
        <div class="card-body">
          <div class="row">
            <div class="col-12 col-md-2 w-auto mb-3 mb-sm-0">
              <div class="d-flex bg-label-warning p-2 justify-content-center align-items-center radius-15 shadow">
                <div class="bg-white d-flex justify-content-center align-items-center radius-15"
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
            </div>

            <div id="filterWrapper" class="col-12 col-md-10 d-flex justify-content-sm-end align-items-center ms-sm-auto">
              <div class="row w-100 d-flex align-items-center ms-sm-10">
                <div class="col-12 col-md-4 mb-3 mb-sm-0">
                  <div class="position-relative">
                    <div class="position-absolute top-50 translate-middle-y" style="left: 1rem;">
                      <i class="ti ti-search text-muted ti-sm"></i>
                    </div>
                    <input type="text" id="searchMentors" class="form-control px-10" placeholder="{{ __('admin.Search') }} {{ __('dashboard.Mentors') }}" />
                  </div>
                </div>
                <div class="col-12 col-md-2 mb-3 mb-sm-0">
                  <select id="rateFilter" class="form-select">
                    <option value="" selected>{{ __('admin.Rate') }}</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                  </select>
                </div>
                <div class="col-12 col-md-4 mb-3 mb-sm-0">
                  <select id="expertiseFilter" class="form-select select2" multiple="multiple" data-placeholder="{{ __('admin.Select Expertise') }}"
                    style="max-height: 40px; overflow: auto;"
                  >
                    @php
                    $expertises = App\Expertise::where('parent_id', null)->get();
                    @endphp
                    @foreach ($expertises as $expertise_group)
                      <optgroup label="{{ $l == 'ar' ? $expertise_group->title_ar : $expertise_group->title }}">
                        @foreach ($expertise_group->children as $expertise)
                          <option value="{{ $expertise->title }}">{{ $l == 'ar' ? $expertise->title_ar : $expertise->title }}</option>
                        @endforeach
                      </optgroup>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-md-1">
                  <div class="form-check form-switch d-flex align-items-center mt-2">
                    <input class="form-check-input me-2" type="checkbox" id="linkedinFilter">
                    <label class="form-check-label fw-bold text-nowrap" for="linkedinFilter">
                      {{ __('admin.linkedin') }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Header -->

      <!-- Mentors -->
      <section class="mb-10 mt-8">
        {{-- <h5 class="border-bottom pb-2 mb-6 mx-4 mx-md-0">{{ __('admin.Mentors') }}</h5> --}}
        <div class="row" id="mentorsContainer">
          <!-- Mentors List -->
          @if(count($mentors) > 0)
          @foreach ($mentors as $key => $mentor)
          <!-- Mentor Card -->
          <div class="col-md-4 radius-15 mb-3">
            <div class="border radius-15" style="background-image: url('{{ $mentor->image_url }}'); 
                    height:293px; background-size: cover;
                    background-position: center;">
            </div>
            <div class="card shadow-none mx-2 p-3"
              style="height: 350px; z-index: 1; margin-top: -25px; border-radius: 15px;">
              <div
                class="bg-white custom-shadow border radius-15 px-2 py-1 d-flex justify-content-between align-items-center mb-2"
                style="height: 55px;">
                <a target="_blank" href="{{ route('mentors.profile_view', $mentor->id) }}" class="d-flex flex-column">
                  <span class="text-black fw-bold fs-6">{{ $mentor->name }}</span>
                  <small class="text-secondary">{{ $mentor->title }}</small>
                </a>
                @if ($mentor->bio->linkedin ?? 0)
                <a title="Linkedin URL" target="_blank" href="{{ $mentor->bio->linkedin }}">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a>
                {{-- @else
                <!-- Please Change this SVG To color #eee -->
                <a title="Linkedin URL" target="_blank" href="#">
                  <img src="/icons/linkedin.svg" width="30" height="30" />
                </a> --}}
                @endif
              </div>
              <!-- Mentor Experience -->
              <div class="mb-3" style="max-height: 100px; overflow: auto;">
                <p class="fs-6 fw-bold text-black mb-2">{{ __('admin.Expertises') }}</p>
                <div class="d-flex flex-wrap" style="gap: 5px;">
                  @php
                  if ($mentor) {
                  $expertises = $mentor->mentorExpertises; // جلب جميع الخبرات المرتبطة بهذا الموجه
                  } else {
                  $expertises = [];
                  }
                  @endphp
                  @if ($mentor->mentorExpertises->isNotEmpty())
                  @foreach ($expertises as $expert)
                  <span class="btn btn-xs btn-outline-warning rounded-pill"> {{ $l === 'ar' ? $expert->title_ar : $expert->title }}</span>
                  @endforeach
                  @else
                  <span class="btn btn-xs btn-outline-warning rounded-pill">{{ __('cycles.NA') }}</span>
                  @endif
                </div>
              </div>
              <!-- / Mentor Experience -->
              <!-- Mentor Programs & Rating -->
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-white custom-shadow px-2 py-1 border" style="border-radius: 15px;">
                  <div class="d-flex flex-column justify-content-center align-items-center">
                    <small class="text-info fw-bold">{{ __('cycles.ProgramCount') }}</small>
                    <span class="text-black fw-bold fs-6">{{ $mentor->mentorCycles->count() }}</span>
                  </div>
                </div>
                @php
                $rateMentorAVG = \App\MentorshipRequest::where('mentor_id', $mentor->id)
                ->where(function ($query) {
                $query->where('approved', 'Yes')->orWhereNull('approved');
                })
                ->avg('rating');
                @endphp
                <div class="badge bg-label-light rounded rounded-pill">
                  <div class="d-flex align-items-center justify-content-center">

                    <span style="color: orange" class="fw-bold mt-1 mx-1">{{ number_format($rateMentorAVG, 2, '.', '')
                      }}</span>
                    <img src="/icons/star.svg" width="15" height="15" />
                  </div>
                </div>
              </div>
              <!-- / Mentor Programs & Rating -->
              <!-- Mentor Bio -->
              @if ($mentor->bio)
              <div class="mb-2 mt-auto">
                <p class="fs-6 fw-bold text-black mb-1">{{ __('dashboard.Bio') }}</p>
                <small class="text-muted line-clamp-4">
                  {{ $mentor->bio->{'bio_' . $l} }}
                </small>
              </div>
              @else
              <div class="mb-2 mt-auto">
                <p class="fs-6 fw-bold text-black mb-1">{{ __('dashboard.Bio') }}</p>
                <small class="text-muted line-clamp-4">
                  {{ __('cycles.NA') }}
                </small>
              </div>
              @endif
              <!-- / Mentor Bio -->
              <div class="row px-4 mt-auto">
                @if(auth()->user()->company && auth()->user()->company->cycle != 0 && auth()->user()->hasRole('Registered'))
                @php
                  $startup = auth()->user();
                  $getCycle = $startup->company->cycledata;
                @endphp
                <button type="button" class="btn btn-sm btn-success rounded-pill custom-shadow"
                  onclick="request({{$mentor->id}},'{{optional($startup)->name }}', {{ $getCycle->organization_id }},{{ $getCycle->id }})"
                  title="Add new request">
                  {{ __('dashboard.Request') }} <i class="ti ti-square-plus mx-3"></i>
                </button>
                @else

                <div class="btn btn-sm btn-success rounded-pill custom-shadow">{{ __('cycles.nosubscripe') }}</div>
                @endif
                @if(!auth()->user()->hasRole('Registered') && !auth()->user()->hasRole('Mentor') && !auth()->user()->hasRole('Admin'))
                @php
                $startup = auth()->user();
                $getCycle = $startup->company->cycledata;
                @endphp
                <button type="button" class="btn btn-sm btn-success rounded-pill custom-shadow" data-bs-toggle="modal"
                  data-bs-target="#SendRequest" title="Add new request" onclick="request({{ $mentor->id }},
                    {{ optional($startup)->first_nam }} 
                    {{ optional($startup)->last_name }},
                    {{ $getCycle->organization_id }},
                    {{ $getCycle->id }})">
                  {{ __('dashboard.Request') }} <i class="ti ti-square-plus mx-3"></i>
                </button>
                @endif


              </div>
            </div>
          </div>
          <!-- Mentor Card -->

          @endforeach
          @else
          <!-- No Mentors Result -->
          <div class="col-12" id="noMentorsResult">
            @include('includes.no-result', ['message' => __('admin.No Mentors Found')])
          </div>
          @endif
          <!-- / Mentors List -->

          <!-- No Filter Results (initially hidden) -->
          <div class="col-12" id="noFilterResult" style="display: none;">
            <div class="card card-body shadow-none h-100 d-flex justify-content-center align-items-center">
              <div class="d-flex flex-column">
                <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
                <h6 class="text-center text-secondary">
                  {{ __('admin.No mentors match your filters') }}
                </h6>
                <div class="text-center">
                  <button type="button" class="btn btn-light" id="resetFiltersBtn">
                    <i class="ti ti-reload me-2"></i>
                    <span class="mx-1">{{ __('admin.Reset') }}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- / Mentors -->
    </div>
  </div>
</div>

@include('admin.clients.startup.partials._session_modal')

<!--  Edit SP Modal -->
{{-- <div class="modal fade text-left" id="SendRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">{{__('dashboard.Add mentorship session with')}} <span class="primary" id="rname"></span></h4>
        </div>
        <form method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data" autocomplete="off">

          <div class="modal-body">
            @csrf
            <input type="hidden" name="mentorid" value="0" id="mentorid">
            <input type="hidden" name="organization_id" id="addOrganizationID">
            <input type="hidden" name="cycle_id" id="addCycleID">
            <div>
              <h5 class="text-primary"><i class="bx bx-time text-secondary"></i> {{__('dashboard.Mentor schedule')}}
              </h5>
              <span class="d-block bg-info p-1 white">
                <ul class="mb-0" id="schedule"></ul>
              </span>
            </div>
            <hr>

            <div class="form-group" id="showdates">
              <label>{{__('dashboard.Start meeting Date/ Time')}}:</label>
              <input class=" width-250 form-control" id="flatpickr-datetimeLimit" type="text" name="date" required>
            </div>
            <div class="form-group">
              <label>{{__('dashboard.Meeting duration')}}:</label>
              <select name="period" class="select form-control width-150">
                <option value="30">30 {{__('dashboard.Minutes')}}</option>
                <option value="60" selected>1 {{__('dashboard.Hour')}}</option>
                <option value="120">2 {{__('dashboard.Hours')}}</option>
                <option value="180">3 {{__('dashboard.Hours')}}</option>
                <option value="240">4 {{__('dashboard.Hours')}}</option>
                <option value="300">5 {{__('dashboard.Hours')}}</option>
              </select>
            </div>
            <div class="form-group">
              <label for="basicInputFile">{{__('dashboard.Send file with your request')}}</label>
              <div class="custom-file">
                <input type="file" class="custom-file-input" id="inputGroupFile01" name="file_url"
                  accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
                <label class="custom-file-label" for="inputGroupFile01">{{__('dashboard.Choose file')}}</label>
              </div>
            </div>
            <label>{{__('dashboard.Add note')}}:</label>
            <div class="form-group">
              <textarea class="form-control" id="notes" name='notes' rows="4"
                placeholder="{{__('dashboard.Write your note here')}}"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary ml-1">

              <span class="d-none d-sm-block">{{__('dashboard.Submit')}}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> --}}
<!--/ Edit SP Modal -->

{{-- 
<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title border-bottom pb-3 w-100" id="filterModalLabel">{{ __('admin.Filter_Mentors') }}</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="GET" action="{{ route('mentors.list') }}">
          <div class="d-flex flex-column">
            <!-- Search Bar -->
            <div class="position-relative mb-6">
              <div class="position-absolute top-50 translate-middle-y search-icon" style="left: 1rem;">
                <i class="ti ti-search text-primary ti-24px"></i>
              </div>
              <div class="Search">
                <input
                  class="form-control custom-shadow search border rounded bg-transparent text-primary px-10 py-2"
                  id="search-table" placeholder="{{ __('admin.Search') }}" name="search"
                  value="{{ request('search') }}" />
              </div>
              <div class="RateSelect">
                <select name="rateSearch"
                  class="form-control custom-shadow search border rounded bg-transparent text-primary px-10 py-2">
                  <option value="" {{ request('rateSearch') == '' ? 'selected' : '' }}>{{ __('admin.empty') }}</option>
                  <option value="1" {{ request('rateSearch') == '1' ? 'selected' : '' }}>1</option>
                  <option value="2" {{ request('rateSearch') == '2' ? 'selected' : '' }}>2</option>
                  <option value="3" {{ request('rateSearch') == '3' ? 'selected' : '' }}>3</option>
                  <option value="4" {{ request('rateSearch') == '4' ? 'selected' : '' }}>4</option>
                  <option value="5" {{ request('rateSearch') == '5' ? 'selected' : '' }}>5</option>
                </select>
              </div>
              
            </div>

            <!-- Sorting -->
            <div id="sortContainer" class="d-flex align-items-center mb-6">
              <div class="row d-flex align-items-center me-4" style="gap: 10px;">
                <div class="col-md-3 d-flex flex-nowrap align-items-center">
                  <div class="btn btn-sm btn-label-primary rounded-pill"
                    style="height:25px; min-width:95px; font-size:13px;">
                    <i class="ti ti-star me-2 text-warning"></i>
                    <input type="checkbox" {{ request('rateSearch') != '' ? 'checked' : '' }} class="RateShowOrNot">
                  </div>
                </div>
              </div>
              <div class="row d-flex align-items-center" style="gap: 10px;">
                <div class="col-md-3 d-flex flex-nowrap align-items-center">
                  <div class="btn btn-sm btn-label-primary rounded-pill"
                    style="height:25px; min-width:95px; font-size:13px;">
                    <i class="ti-24px ti ti-brand-linkedin me-2 text-info"></i>
                    <input type="checkbox" {{ request('linkedin') != '' ? 'checked' : '' }}
                      class="linkedin" name="linkedin">
                  </div>
                </div>
              </div>
            </div>
            <!-- / Sorting -->

            @php
            $expertises = App\Expertise::where('parent_id', null)->get();
            @endphp
            <div class="form-group mb-4">
              <div class="d-flex flex-column">
                <label for="selectExpertises" class="form-label fs-6 fw-bold">
                  {{ __('admin.Select Expertise') }}
                </label>
                <small class="mb-3">{{ __('admin.SelectMultiExpertises') }}.</small>
              </div>
              <div class="row">
                <div class="col-12">
                  <select name="expertises[]" class="form-select select2" multiple="multiple" id="selectExpertises"
                    data-placeholder="{{ __('admin.Select Expertise') }}"
                    placeholder="{{ __('admin.Select Expertise') }}"
                  >
                    <option value="">{{ __('admin.Select Expertise') }}</option>
                    @foreach ($expertises as $expertise_group)
                    <optgroup label="{{ $expertise_group->title }}">
                      @foreach ($expertise_group->children as $expertise)
                      <option value="{{ $expertise->id }}">{{ __('admin.'.$expertise->title) }}</option>
                      @endforeach
                    </optgroup>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-end align-items-center gap-3 mt-6">
            <button type="reset" class="btn btn-label-secondary" onclick="window.location.href = '/dashboard/show/Mentors'">
              {{ __('admin.Reset') }}
            </button>
            <button type="submit" class="btn btn-primary">{{ __('admin.Apply') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- / Filter Modal -->
--}}
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
@stop

@section('pagescripts')
<!-- Page JS -->
<script src="/vuexy/assets/js/mentorship-session.js"></script>

<script>
  // Client-side filtering functionality
  $(document).ready(function() {
    // Filter functionality
    function filterMentors() {
      const searchTerm = $('#searchMentors').val().toLowerCase();
      const rateFilter = $('#rateFilter').val();
      const linkedinFilter = $('#linkedinFilter').is(':checked');
      const expertiseFilter = $('#expertiseFilter').val(); // This will be an array for multiple selections

      $('.col-md-4.radius-15.mb-3').each(function() {
        const mentorCard = $(this);
        let showCard = true;

        // Search filter
        if (searchTerm) {
          const mentorName = mentorCard.find('.text-black.fw-bold.fs-6').text().toLowerCase();
          const mentorTitle = mentorCard.find('.text-secondary').text().toLowerCase();
          const mentorBio = mentorCard.find('.line-clamp-4').text().toLowerCase();
          
          if (!mentorName.includes(searchTerm) && 
              !mentorTitle.includes(searchTerm) && 
              !mentorBio.includes(searchTerm)) {
            showCard = false;
          }
        }

        // Rate filter
        if (rateFilter && showCard) {
          const ratingText = mentorCard.find('span[style*="color: orange"]').text().trim();
          const rating = parseFloat(ratingText) || 0;
          const selectedRating = parseInt(rateFilter);
          
          // Filter by exact rating (rounded to nearest integer)
          if (Math.round(rating) !== selectedRating) {
            showCard = false;
          }
        }

        // LinkedIn filter
        if (linkedinFilter && showCard) {
          const hasLinkedIn = mentorCard.find('a[title="Linkedin URL"]').attr('href');
          if (!hasLinkedIn) {
            showCard = false;
          }
        }

        // Expertise filter
        if (expertiseFilter && showCard) {
          const selectedExpertises = $('#expertiseFilter').val() || [];
          if (selectedExpertises.length > 0) {
            let hasAnyExpertise = false;
            mentorCard.find('.btn-outline-warning').each(function() {
              const mentorExpertise = $(this).text().toLowerCase().trim();
              selectedExpertises.forEach(function(selectedExp) {
                if (mentorExpertise.includes(selectedExp.toLowerCase())) {
                  hasAnyExpertise = true;
                  return false; // break inner loop
                }
              });
              if (hasAnyExpertise) return false; // break outer loop
            });
            if (!hasAnyExpertise) {
              showCard = false;
            }
          }
        }

        // Show/hide card
        if (showCard) {
          mentorCard.show();
        } else {
          mentorCard.hide();
        }
      });

      // Update mentors count and handle no-result sections
      const visibleCount = $('.col-md-4.radius-15.mb-3:visible').length;
      $('.fs-5').last().text(visibleCount);
      
      // Handle no-result sections
      const hasAnyFilters = searchTerm || rateFilter || linkedinFilter || (expertiseFilter && expertiseFilter.length > 0);
      
      if (visibleCount === 0) {
        if (hasAnyFilters) {
          // Show no filter results message
          $('#noFilterResult').show();
          $('#noMentorsResult').hide();
        } else {
          // Show no mentors message (this should already be handled by the blade condition)
          $('#noFilterResult').hide();
          $('#noMentorsResult').show();
        }
      } else {
        // Hide both no-result sections when mentors are visible
        $('#noFilterResult').hide();
        $('#noMentorsResult').hide();
      }
    }

    // Event listeners
    $('#searchMentors').on('input', filterMentors);
    $('#rateFilter').on('change', filterMentors);
    $('#linkedinFilter').on('change', filterMentors);
    $('#expertiseFilter').on('change', filterMentors);

    // Reset filters
    $('#resetFilters').on('click', function() {
      $('#searchMentors').val('');
      $('#rateFilter').val('');
      $('#linkedinFilter').prop('checked', false);
      $('#expertiseFilter').val(null).trigger('change'); // Clear select2 multiple selection
      filterMentors();
    });

    // Reset filters button in no-result section
    $('#resetFiltersBtn').on('click', function() {
      $('#searchMentors').val('');
      $('#rateFilter').val('').trigger('change'); // Clear select2 single selection
      $('#linkedinFilter').prop('checked', false);
      $('#expertiseFilter').val(null).trigger('change'); // Clear select2 multiple selection
      filterMentors();
    });

    // Initialize
    filterMentors();
    
    // Initially hide no-result sections if there are mentors
    @if(count($mentors) > 0)
      $('#noMentorsResult').hide();
      $('#noFilterResult').hide();
    @endif
  });

  // Remove old filter button functionality
  /*
  const sortContainer = document.getElementById('sortContainer');
    const sortContainerMobile = document.getElementById('sortContainerMobile');
    const filterButton = document.getElementById('filterButton');

    const sortLabel = `<span class="mx-2 fw-bold">Sorting:</span>`;

   const sortContent = `
      <div class="row d-flex align-items-center" style="gap: 10px;">
        <div class="col-md-3 d-flex flex-nowrap align-items-center">
          <span class="fw-bold mx-1 text-black d-none d-md-flex" style="font-size:13px;">Rate</span>
          <button class="btn btn-sm btn-label-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#rateModal"
            style="height:25px; min-width:95px; font-size:13px;">
            <i class="ti ti-star me-2 text-warning"></i>
            <span class="fw-bold text-primary">Default</span>
          </button>
        </div>
      </div>
      <div class="row d-flex align-items-center" style="gap: 10px;">
        <div class="col-md-3 d-flex flex-nowrap align-items-center">
          <span class="fw-bold mx-1 text-black d-none d-md-flex" style="font-size:13px;">Linkedin</span>
          <button class="btn btn-sm btn-label-primary rounded-pill"
            style="height:25px; min-width:95px; font-size:13px;">
            <i class="ti-24px ti ti-brand-linkedin me-2 text-info"></i>
            <span class="fw-bold text-primary">Default</span>
          </button>
        </div>
      </div>
      <div class="row d-flex align-items-center" style="gap: 10px;">
        <div class="col-md-3 d-flex flex-nowrap align-items-center">
          <span class="fw-bold mx-1 text-black d-none d-md-flex text-nowrap" 
          style="font-size:13px;">Program Count</span>
          <button class="btn btn-sm btn-label-primary rounded-pill"
            style="height:25px; min-width:95px; font-size:13px;">
            <i class="ti-24px ti ti-dashboard me-2 text-danger"></i>
            <span class="fw-bold text-primary">Default</span>
          </button>
        </div>
      </div>
    `;

    const modalContent = `
    <div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="rateModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rateModalLabel">Filter By Rate</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <ul class="list-group">
              <li class="list-group-item">
                <i class="ti ti-star ti-filled text-warning fill-warning me-2"></i> 1
              </li>
              <li class="list-group-item">
                <i class="ti ti-star ti-filled text-warning fill-warning me-2"></i> 2
              </li>
              <li class="list-group-item">
                <i class="ti ti-star ti-filled text-warning fill-warning me-2"></i> 3
              </li>
              <li class="list-group-item">
                <i class="ti ti-star ti-filled text-warning fill-warning me-2"></i> 4
              </li>
              <li class="list-group-item">
                <i class="ti ti-star ti-filled text-warning fill-warning me-2"></i> 5
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    `;

    // Append the modal content to the body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    let isToggled = false;

    filterButton.addEventListener('click', () => {
      console.log('Filter Button Clicked');
      isToggled = !isToggled; // Toggle the state

      if (isToggled) {
        sortContainer.innerHTML = sortLabel + sortContent; // Set the content
        sortContainerMobile.innerHTML = sortContent; // Set the content
      } else {
        sortContainer.innerHTML = ''; // Clear the container
        sortContainerMobile.innerHTML = ''; // Clear the container
      }
    });
  */
</script>

<script>
  $(document).ready(function() {
      $('#dfrom, #dto').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
      });
    });
</script>

<script>
  $(document).ready(function() {
        $("#mentorstable").DataTable({
            "responsive": true,
            "searching": true,
            "lengthChange": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{"orderable": false, "targets": [3]}],
            @if ($l == 'ar')
            "language": {
                "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
                "sLoadingRecords": "جارٍ التحميل...",
                "sProcessing": "جارٍ التحميل...",
                "sLengthMenu": "أظهر _MENU_ مدخلات",
                "sZeroRecords": "لم يعثر على أية سجلات",
                "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
                "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                "sInfoPostFix": "",
                "sSearch": "ابحث:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "الأول",
                    "sPrevious": "السابق",
                    "sNext": "التالي",
                    "sLast": "الأخير"
                },
                "oAria": {
                    "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
                    "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
                }
            }
            @endif
        });

        $(".sessonsTable").DataTable({
            "responsive": true,
            "searching": true,
            "lengthChange": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{"orderable": false, "targets": [3]}],
            @if ($l == 'ar')
            "language": {
                "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
                "sLoadingRecords": "جارٍ التحميل...",
                "sProcessing": "جارٍ التحميل...",
                "sLengthMenu": "أظهر _MENU_ مدخلات",
                "sZeroRecords": "لم يعثر على أية سجلات",
                "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
                "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                "sInfoPostFix": "",
                "sSearch": "ابحث:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "الأول",
                    "sPrevious": "السابق",
                    "sNext": "التالي",
                    "sLast": "الأخير"
                },
                "oAria": {
                    "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
                    "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
                }
            } @endif
              });

              $("#date").datetimepicker({
		format: 'Y-m-d H:i', onGenerate: function( ct ) { jQuery(this).find('.xdsoft_date').toggleClass('xdsoft_disabled'); },
		maxDate: '{{date('Y/m/d',strtotime("-1 days"))}}',
		timepicker: true
	});
});

// function request(id,mentor,organization_id=null,cycle_id=null) {
// 		$('#mentorid').val(id);
// 		$('#rname').html(mentor);
// 		$.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
// 		$.ajax({
// 			type: "POST", url: "/dashboard/mentors/modify",
// 			data: {"id": id, "rtype": 5,},
// 			success: function (response) {
// 				$('#SendRequest li').remove();
// 				$('#name').html(name);
// 				if (response.length>0) {
// 					$.each(response, function(i, item) {
// 						let note = '';
// 						let time = item.tfrom + " {{__('dashboard.to')}} " + item.tto;
// 						if (item.notes) {note = item.notes;}
// 						switch (item.type)
// 						{
// 							case 'OneDay': $('<li>').html("<b>" + item.dfrom + "</b> ("+ time +").").appendTo('#schedule'); break;
// 							case 'Weekly': $('<li>').html("<b> {{__('dashboard.Weekly')}} </b>(" + item.days + ") - ("+ time +").").appendTo('#schedule'); break;
// 							case 'WorkingDays': $('<li>').html("<b>{{__('dashboard.All working days')}} </b>("+ time +").").appendTo('#schedule'); break;
// 							case 'Period': $('<li>').html("<b> {{__('dashboard.from')}} " + item.dfrom + " {{__('dashboard.to')}} " + item.dto +" </b> ("+ time +").").appendTo('#schedule'); break;
// 							default:
// 						}
// 					});
// 				} else { $('<li>').html("{{__('dashboard.No records saved')}}.</li>").appendTo('#schedule'); }
// 				$('#addOrganizationID').val(organization_id);
// 				$('#addCycleID').val(cycle_id);
// 				$('#SendRequest').modal('show');
// 			}, error: function (error) { console.log(error); alert("Error Occurred, Try again!"); }
// 		});
// 	}
</script>
<script>
  $(document).ready(function() {
    // Old RateShowOrNot functionality removed as it's replaced by inline filtering
    
    // Keep existing mentor modification functions 
  function modify(type, id, stat) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      if (type === 3) {
        //delete
        if (confirm("{{ __('dashboard.sure del schedule') }}")) {
          $.ajax({
            type: "POST",
            url: "/dashboard/mentors/modify",
            data: {
              "id": id,
              "rtype": type,
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
      } else if (type === 2) {
        //Enable-disable
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
            "stat": stat
          },
          success: function(response) {
            location.reload();
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else if (type === 1) {
        // Edit current
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
          },
          success: function(response) {
            switchtypes(response.type);
            $('#sid').val(response.id);
            $('#rtype').val(4);
            $('#type').val(response.type);
            $('#days').val(response.days);
            $('#dfrom').val(response.dfrom);
            $('#dto').val(response.dto);
            $('#tfrom').val(response.tfrom);
            $('#tto').val(response.tto);
            $('#notes').val(response.notes);
            $('#SendRequest').modal('show');
            console.log(response);
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else {
        //show from as new
        $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
        $('#rtype').val('0');
        switchtypes(type);
        $('#SendRequest').modal('show');
      }
    }

    function SelectType(type) {
      switchtypes(type);
    }

    function switchtypes(type) {
      switch (type) {
        case 'OneDay':
          $('#showdates').show();
          $('#showtto, #showdays').hide();
          $("#dfrom").prop('required', true);
          $("#days, #dto").prop('required', false);
          break;
        case 'Weekly':
          $('#showdates').hide();
          $('#showdays').show();
          $("#days").prop('required', true);
          $("#dfrom, #dto").prop('required', false);
          break;
        case 'WorkingDays':
          $('#showdates, #showdays').hide();
          $("#dfrom, #dto, #days").prop('required', false);
          break;
        case 'Period':
          $('#showtto,#showdates').show();
          $('#showdays').hide();
          $("#dfrom, #dto").prop('required', true);
          $("#days").prop('required', false);
          break;
      }
    }

    $('.start_meeting').click(function() {
      $.ajax({
        type: "GET",
        url: "/dashboard/mentor/meetingRedirect/" + $(this).data('value'),
        success: function(response) {
          alert(response['message']);
          window.open(response['join_url'], '_blank');
          window.location.href = '{{ route('mentorshipTasks.index') }}' + '?mentorship_id=' + response[
            'mentorship_id'];
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    });
</script>
@stop