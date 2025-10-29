@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.css" />
@stop

@section('style')
<style>
.card {
  border-radius: 15px !important;
}

.radius-15 {
  border-radius: 15px !important;
}

.light-shadow {
  box-shadow: 0px 2px 0px 0px #F2F1F3;
}

.section-3 {
  width: 100%;
}

.card-icon {
  width: 55px;
  height: 55px;
}

.sm-card-icon {
  width: 35px;
  height: 35px;
}

.text-xs {
  font-size: 12px;
}

.flex-container {
  display: flex;
  gap: 10px;
}

.flex-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}


.masonry {
  /* display: flex; */
  /* flex-wrap: wrap; */
  column-count: 1;
  column-gap: 20px;
}

.masonry .card {
  display: inline-block;
  width: 100%;
  margin-bottom: 20px;
  break-inside: avoid;
}

@media screen and (min-width: 768px) {
  .masonry {
    column-count: 2;
    column-gap: 20px;
  }

  .section-3 {
    width: 75%;
  }
}

.screening-gavel {
  position: fixed;
  background-color: var(--bs-primary-border-subtle);
  height: 56px;
  width: 70px;
  display: grid;
  place-items: center;
  top: 45%;
  @if($l == 'en')
    right: 0;
    border-top-left-radius: 35px;
    border-bottom-left-radius: 35px;
  @else
    left: 0;
    border-top-right-radius: 35px;
    border-bottom-right-radius: 35px;
  @endif
  z-index: 1000;
  border: 1px solid var(--bs-primary);
  cursor: pointer;
}
.transposed-icon {
  transform: rotate(360deg);
}
.screening-gavel:hover {
  background-color: var(--bs-white);
  transition: all 0.3s ease-in-out, transform 0.3s ease-in-out;
  transform: scale(1.2);
}

/* Milestones Carousel Styles */
.milestone-card {
  min-width: 0; /* Ensures flex items can shrink */
}

#milestonesContainer {
  transition: transform 0.3s ease;
}

/* Responsive adjustments for milestones */
@media (max-width: 991px) {
  .milestone-card {
    width: 50% !important; /* Show 2 cards on tablet */
  }
}

@media (max-width: 767px) {
  .milestone-card {
    width: 100% !important; /* Show 1 card on mobile */
  }
}

/* Navigation button hover effects */
#milestonesPrev:not(:disabled):hover,
#milestonesNext:not(:disabled):hover {
  background-color: var(--bs-primary);
  color: white !important;
  border-color: var(--bs-primary);
  transform: scale(1.1);
  transition: all 0.2s ease;
}

#milestonesPrev:not(:disabled):hover i,
#milestonesNext:not(:disabled):hover i {
  color: white !important;
}
</style>
@stop

@section('content')

@if($_user->hasRole('Judger'))
  @include('admin.clients.startup.partials._startup_screening_canvas')
@endif

<div class="content-wrapper position-relative">
  <div class="container-xxl flex-grow-1 container-p-y">
    @if($_user->hasRole('Judger'))
      <div class="d-flex align-items-center gap-3 mb-6">
        <a id="save-screening-btn" class="btn btn-sm btn-icon shadow btn-white rounded-circle">
          <i class="ti ti-chevron-{{ $l == 'en' ? 'left' : 'right' }} ti-md text-dark"></i>
        </a>
        <a id="save-screening-btn" class="d-flex align-items-center gap-2 text-capitalize">
          <i class="ti ti-gavel fs-4 text-primary"></i>
          <strong class="text-dark">{{ __('admin.Screening') }}</strong>
        </a>
        <i class="ti ti-chevrons-{{ $l== 'en' ? 'right' : 'left' }}"></i>
        <div class="d-flex align-items-center gap-2 text-capitalize">
          <i class="ti ti ti-file-text fs-4 text-primary"></i>
          <strong class="text-dark">{{ __('admin.Company profile') }}</strong>
        </div>
      </div>
    @else
      <div class="d-flex flex-wrap justify-content-between align-items-md-center mb-4">
        <h4 class="fw-bold mb-0">{{ __('admin.My company') }}</h4>
        <div class="d-flex align-items-center gap-3">
          @if( $_user->hasAnyRole('Admin|organizationManager') || $_user->ableTo('company.profile_view_mine', $permissions) )
            <a href="/dashboard/myCompany/{{ $company->id }}" class="btn btn-primary"
              style="border-radius: 15px;">
              {{ __('admin.Edit') }} <i class="ti ti-edit ms-2"></i>
            </a>
          @else
            <a href="" data-bs-toggle="modal" data-bs-target="#MessageModal"
              class="btn btn-label-primary" style="border-radius: 15px;">
              {{ __('admin.Contact') }} <i class="ti ti-message ms-2"></i>
            </a>
          @endif
        </div>
      </div>
    @endif

    @include('admin.clients.startup.partials._startup_info')

    <section class="w-100 mt-6 d-flex flex-column flex-md-row" style="gap: 2rem;">
      <div class="section-3">
        <div class="row">
          <div class="col-6 col-md-4 mb-4">
            <div class="card" style="height: 170px; background-color: #c4b5fd;">
              <div class="card-body">
                <div class="align-self-center d-flex flex-column text-center align-items-center">
                  <div class="text-center">
                    <img class="card-icon mb-2" src="/icons/development-stages.svg" alt="icon" />
                  </div>
                  <h5 class="mb-1 fw-semibold">{{ __('admin.Funding_Stage') }}</h5>
                  <span
                    class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                    style="width: fit-content;">
                    {{ $company->development_stage ? __('admin.' . $company->development_stage) : __('admin.N/A') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4 mb-4">
            <div class="card" style="height: 170px; background-color: #7dd3fc;">
              <div class="card-body">
                <div class="align-self-center d-flex flex-column text-center align-items-center">
                  <div class="text-center">
                    <img class="card-icon mb-2" src="/icons/headquarter.svg" alt="icon" />
                  </div>
                  <h5 class="mb-1 fw-semibold">{{ __('admin.Country') }}</h5>
                  <span
                    class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                    style="width: fit-content;">
                    {{-- <i class="flag-icon flag-icon-eg" style="font-size: 10px;"></i> --}}
                    {{ optional($company->country)->{'name_' . $l} }}
                    <i class="ti ti-flag ti-16px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4 mb-4">
            <div class="card" style="height: 170px; background-color: #fdba74;">
              <div class="card-body">
                <div class="align-self-center d-flex flex-column text-center align-items-center">
                  <div class="text-center">
                    <img class="card-icon mb-2" src="/icons/coin.svg" alt="icon" />
                  </div>
                  @php
                  $stage = App\Stage::where('id', $company->stage)->first();
                  @endphp
                  <h5 class="mb-1 fw-semibold">{{ __('admin.development_stage') }}</h5>

                  @if(!is_numeric($company->stage))
                  <span
                    class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                    style="width: fit-content;">{{ $company->stage ? __('admin.stage_'.$company->stage) : __('admin.N/A') }}</span>
                  @else
                  <span
                    class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                    style="width: fit-content;">{{ $stage != null ? $stage->{'name_' . $l} : __('admin.N/A') }}</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4 mb-4">
            <div class="card" style="height: 170px; background-color: #5eead4; overflow: hidden;">
                <div class="py-6 px-2">
                <div class="align-self-center d-flex flex-column text-center align-items-center w-100">
                  <div class="text-center">
                  <img class="card-icon mb-2" src="/icons/target-market.svg" alt="icon"
                    style="width: 50px; height: 50px;"
                  />
                  </div>
                  <h5 class="mb-2 fw-semibold">{{ __('admin.Targeted_Market') }}</h5>
                  <div class="d-flex justify-content-center align-items-center gap-1 pb-2" style="width: 99.5%; overflow: auto; white-space: nowrap;">
                  @if($company->targeted_market != null)
                  @php
                  $countryIds = json_decode($company->targeted_market, true);
                  @endphp

                  @if (is_array($countryIds) && count($countryIds) > 0)
                  @php
                  $targetedCountries = App\Country::whereIn('id', $countryIds)->get()->keyBy('id');
                  @endphp
                    @foreach (array_slice($countryIds, 0, 1) as $index => $countryId)
                      <div
                        class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-2"
                        style="min-width: 110px;">
                        <span class="line-clamp-1">
                          {{ $targetedCountries[$countryId]->{'name_' . $l} ?? __('admin.N/A') }}
                        </span>
                      </div>
                    @endforeach
                    @if (is_array($countryIds) && count($countryIds) > 1)
                    <button type="button" class="btn p-0" data-bs-toggle="modal" data-bs-target="#targetedMarketModal">
                      <span
                        class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-3 py-1"
                        style="width: fit-content;"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        data-bs-title="{{ __('admin.Show more') }}">
                        <i class="ti ti-dots ti-16px"></i>
                      </span>
                    </button>
                    @endif
                  @else
                  <span
                    class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                    style="width: fit-content;">{{ __('admin.N/A') }}</span>
                  @endif
                  @endif
                  </div>
                </div>
                </div>
            </div>
          </div>
          <div class="col-6 col-md-4 mb-4">
            <div class="card" style="height: 170px; background-color: #fca5a5;">
              <div class="card-body">
                <div class="align-self-center d-flex flex-column text-center align-items-center">
                  <div class="text-center">
                    <img class="card-icon mb-2" src="/icons/business-modal.svg" alt="icon" />
                  </div>
                  <h5 class="mb-1 fw-semibold">{{ __('admin.Business Type') }}</h5>
                  <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 5px;">
                    <span
                      class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                      style="width: fit-content;">{{ $company->business_type ?? __('admin.N/A') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4 mb-4">
            <div class="card bg-light">
              <div class="card-body">
                <div class="align-self-center d-flex flex-column text-center align-items-center">
                  <div class="text-center">
                    <img class="card-icon mb-2" src="/icons/industries.svg" alt="icon" />
                  </div>
                  <h5 class="mb-1 fw-semibold">{{ __('admin.Sector') }}</h5>
                  <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 5px;">
                    @php
                    $sector = App\Sector::where('id', $company->sector)->first();
                    @endphp
                    <span
                      class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-4"
                      style="width: fit-content;">{{ $sector->name_en ?? __('admin.N/A') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="d-flex flex-grow-1 justify-content-center justify-content-md-start">
        <div class="row d-flex flex-column w-100" style="gap: 1rem;">
          <div class="border-primary w-100 rounded" style="border: 2px dashed; height: 100px;">
            <div class="p-2" style="display: grid; place-items: center;">
              <img class="sm-card-icon mb-2" src="/icons/coin.svg" alt="icon" />
              <span class="text-black" style="font-size: 12px;">{{ __('admin.Funding_Target') }}</span>
              <span class="text-primary commanums" style="font-size: 12px;">{{ $company->Funding_Target }}
                {{ $company->Currency }}</span>
            </div>
          </div>
          <div class="border-primary w-100 rounded" style="border: 2px dashed; height: 100px;">
            <div class="p-2" style="display: grid; place-items: center;">
              <img class="sm-card-icon mb-2" src="/icons/coin.svg" alt="icon" />
              <span class="text-black" style="font-size: 12px;">{{ __('admin.Pre_money_Valuation') }}</span>
              <span class="text-primary commanums" style="font-size: 12px;">{{ $company->Pre_money_Valuation }}
                {{ $company->Currency }}</span>
            </div>
          </div>
          <div class="border-primary w-100 rounded" style="border: 2px dashed; height: 100px;">
            <div class="p-2" style="display: grid; place-items: center;">
              <img class="sm-card-icon mb-2" src="/icons/coin.svg" alt="icon" />
              <span class="text-black" style="font-size: 12px;">{{ __('admin.raised_fund') }}</span>
              <span class="text-primary commanums" style="font-size: 12px;">{{ $company->raised_fund }}
                {{ $company->Currency }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    @include('admin.clients.startup.partials._working_team')

    @if($company->milestones && $company->count() > 0)
    <section class="mt-6">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  {{-- <i class="ti ti-target fs-3 me-3"></i> --}}
                  <h5 class="mb-0">{{ $l == 'en' ? 'Milestones' : 'الإنجازات' }}</h5>
                  <span class="badge bg-label-primary ms-3">{{ count($company->milestones) }}</span>
                </div>
                @if(count($company->milestones) > 3)
                <div class="d-flex align-items-center gap-2">
                  <button id="milestonesPrev" class="btn btn-icon btn-outline-secondary shadow-xs rounded-circle" disabled>
                    <i class="ti ti-chevron-{{ $l == 'en' ? 'left' : 'right' }} ti-sm"></i>
                  </button>
                  <button id="milestonesNext" class="btn btn-icon btn-outline-secondary shadow-xs rounded-circle">
                    <i class="ti ti-chevron-{{ $l == 'en' ? 'right' : 'left' }} ti-sm"></i>
                  </button>
                </div>
                @endif
              </div>
              
              <div class="position-relative">
                <div id="milestonesCarousel" class="overflow-hidden">
                  <div id="milestonesContainer" class="d-flex transition-all my-2" style="transform: translateX(0px); transition: transform 0.3s ease;">
                    @foreach($company->milestones as $index => $milestone)
                    <div class="milestone-card flex-shrink-0 pe-4" style="width: 33.333%;">
                      <div class="card h-100 border shadow-xs">
                        <div class="card-body d-flex flex-column">
                          <!-- Milestone Header -->
                          <div class="d-flex align-items-center mb-3">
                            {{-- <div class="rounded-circle d-flex flex-shrink-0 align-items-center justify-content-center me-3 {{ $milestone->completion_percentage >= 100 ? 'bg-success' : 'bg-primary' }} text-white" 
                                 style="width: 35px; height: 35px; font-size: 14px; font-weight: bold;">
                              @if($milestone->completion_percentage >= 100)
                                ✓
                              @else
                                {{ $index + 1 }}
                              @endif
                            </div> --}}
                            <div class="flex-grow-1">
                              <h6 class="mb-0 fw-semibold">{{ $milestone->name }}</h6>
                              {{-- <small class="text-muted">
                                {{ $l == 'ar' ? 'إنجاز' : 'Milestone' }} {{ $index + 1 }}
                              </small> --}}
                            </div>
                          </div>
                          
                          <!-- Milestone Description -->
                          <p class="text-muted mb-3 flex-grow-1" style="font-size: 0.9rem;">
                            {{ $milestone->description }}
                          </p>
                          
                          <!-- Progress Bar -->
                          <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">{{ $l == 'ar' ? 'نسبة الإكمال' : 'Completion' }}</small>
                              <small class="fw-semibold {{ $milestone->completion_percentage >= 100 ? 'text-success' : 'text-primary' }}">
                                {{ $milestone->completion_percentage ?? 0 }}%
                              </small>
                            </div>
                            <div class="progress" style="height: 6px;">
                              <div class="progress-bar {{ $milestone->completion_percentage >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                   role="progressbar" 
                                   style="width: {{ $milestone->completion_percentage ?? 0 }}%"
                                   aria-valuenow="{{ $milestone->completion_percentage ?? 0 }}" 
                                   aria-valuemin="0" 
                                   aria-valuemax="100">
                              </div>
                            </div>
                          </div>
                          
                          <!-- Supporting Documents -->
                          @if($milestone->documents)
                            @php
                              $documents = is_string($milestone->documents) ? json_decode($milestone->documents, true) : $milestone->documents;
                              $documents = is_array($documents) ? $documents : [];
                            @endphp
                            @if(count($documents) > 0)
                            <div class="mt-auto">
                              <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted">{{ $l == 'ar' ? 'المستندات' : 'Documents' }}</small>
                                <span class="badge bg-label-secondary">{{ count($documents) }}</span>
                              </div>
                              <div class="d-flex flex-wrap gap-1">
                                @foreach(array_slice($documents, 0, 3) as $doc)
                                  <a href="{{ $doc['url'] ?? '#' }}" 
                                     target="_blank" 
                                     class="btn btn-xs btn-outline-primary p-1" 
                                     title="{{ $doc['name'] ?? 'Document' }}">
                                    <i class="ti ti-file-text" style="font-size: 12px;"></i>
                                  </a>
                                @endforeach
                                @if(count($documents) > 3)
                                  <span class="btn btn-xs btn-outline-secondary p-1" title="{{ count($documents) - 3 }} more documents">
                                    +{{ count($documents) - 3 }}
                                  </span>
                                @endif
                              </div>
                            </div>
                            @endif
                          @endif
                          
                          <!-- Milestone Status Badge -->
                          <div class="mt-2">
                            {{-- @if($milestone->completion_percentage >= 100)
                              <span class="badge bg-label-success">
                                <i class="ti ti-check me-1"></i>
                                {{ $l == 'ar' ? 'مكتمل' : 'Completed' }}
                              </span>
                            @elseif($milestone->completion_percentage > 0)
                              <span class="badge bg-label-warning">
                                <i class="ti ti-clock me-1"></i>
                                {{ $l == 'ar' ? 'قيد التنفيذ' : 'In Progress' }}
                              </span>
                            @else
                              <span class="badge bg-label-secondary">
                                <i class="ti ti-calendar me-1"></i>
                                {{ $l == 'ar' ? 'مخطط' : 'Planned' }}
                              </span>
                            @endif --}}
                            
                            @if($milestone->created_at)
                              <small class="text-muted d-block mt-1">
                                {{ $l == 'ar' ? 'أضيف في' : 'Added on' }} {{ $milestone->created_at->format('M Y') }}
                              </small>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              </div>            
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    <section>
      <div class="row">
        @include('admin.clients.startup.partials._startup_founders')
        @include('admin.clients.startup.partials._startup_shareholders')
        @include('admin.clients.startup.partials._startup_investors')
      </div>
    </section>
    <!-- Sustainable Development Icons -->
    @php
    // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
    // $Sustainables = explode(',', $company->Sustainable_development);
    $Sustainables = json_decode($company->Sustainable_development);
    @endphp
    @if (!empty($Sustainables) && count($Sustainables) > 0)
    <section class="mt-6 row">
      <div class="col-12">
        <div class="card" style="min-height: 290px;">
          <div class="p-4">
            <h5 class="">
              {{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}
            </h5>
            <div class="row w-75">
              @foreach ($Sustainables as $Sustainable)
              @php
              $icon = App\SustainableIcon::where('title', $Sustainable)->first();
              @endphp
              <div class="col-md-2 mb-2">
                <div class="border light-shadow radius-15" style="display: grid; place-items: center;">
                  @if ($icon && $icon->icon_ar)
                  <img src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}" alt="icon"
                    style="width: 100px; height: 100px;">
                  @endif
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif
    <!-- / Sustainable Development Icons -->

    <section>
      <div class="row">
        <div class="col-12 col-md-6 mt-6">
          <div class="card" style="height: 520px;">
            <div class="card-body h-100">
              <h5 class="card-title">{{ __('admin.Pitch_Deck') }}:</h5>
              @if ($company->pitch_deck != null)
              <div class="card-body text-center h-100">
                <div class="d-flex flex-column justify-content-between align-items-center h-100">
                  <iframe src="{{ url($company->pitch_deck) }}" target="_blank"
                    style="width: 100%; height: 350px; object-fit:" class="img-fluid">
                    <p>
                      {{ $l == 'en' ? 'Your browser does not support iframes.' : 'متصفحك لا يدعم إطارات iframe.' }}
                    </p>
                  </iframe>
                  <div class="mt-auto d-flex flex-column mb-6">
                    <span class="text-muted">
                      {{ $l == 'en' ? 'To get analysis for your pitch deck please' : 'للحصول على تحليل لعرضك التقديمي، يرجى' }}
                    </span>
                    @if($_user->hasAnyRole('Registered'))
                    <a role="button"
                      onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
                      class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                      {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                    </a>
                    @endif
                  </div>
                </div>
              </div>
              @else
              <div class="card-body text-center h-100">
                <div class="d-flex flex-column justify-content-between align-items-center h-100">
                  <img src="/images/deck.svg" style="width: 350px; height: 350px;" />
                  <div class="mt-auto d-flex flex-column mb-6">
                    <span class="text-muted">
                      {{ $l == 'en' ? 'To get analysis for your pitch deck please upload your pitch deck' : 'للحصول على تحليل لعرضك التقديمي، يرجى تحميل عرضك التقديمي' }}
                    </span>
                    @if($_user->hasAnyRole('Registered'))
                    <a role="button"
                      onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
                      class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                      {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                    </a>
                    @endif
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 mt-6">
          <div class="card flex-grow-1" style="height: 520px;">
            <div class="card-body">
              <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}:</h5>
              <div class="d-flex flex-column justify-content-between align-items-center" style="height: 90%">
                @if ($company && $company->pitch_deck_video)
                @php
                $videoUrl = $company->pitch_deck_video;
                $youtubePattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/]+\/[^\/]+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
                $isYouTube = preg_match($youtubePattern, $videoUrl, $matches);
                $videoId = $isYouTube ? $matches[1] : null;
                @endphp

                @if ($videoId)
                {{-- Embed YouTube Video --}}
                <iframe class="h-100 w-100" src="https://www.youtube.com/embed/{{ $videoId }}"
                  title="YouTube video player" frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowfullscreen>
                </iframe>
                @elseif (Str::contains($videoUrl, 'vimeo.com'))
                {{-- Embed Vimeo Video --}}
                @php
                $vimeoId = preg_replace('/[^0-9]/', '', parse_url($videoUrl, PHP_URL_PATH));
                @endphp
                <iframe class="h-100 w-100" src="https://player.vimeo.com/video/{{ $vimeoId }}" frameborder="0"
                  allow="autoplay; fullscreen; picture-in-picture" allowfullscreen>
                </iframe>
                @elseif (Str::endsWith($videoUrl, ['.mp4', '.webm', '.ogg']))
                {{-- Direct Video File --}}
                <video class="h-100 w-100" controls>
                  <source src="{{ $videoUrl }}" type="video/mp4">
                    {{ $l == 'en' ? 'Your browser does not support the video tag.' : 'متصفحك لا يدعم علامة الفيديو.' }}
                </video>
                @else
                {{-- Unknown format --}}
                <a href="{{ $videoUrl }}" target="_blank" class="btn btn-primary">
                  {{ $l == 'en' ? 'View Video' : 'عرض الفيديو' }}
                </a>
                @endif

                @else
                <img id="deck-video-image" src="/images/deck-video.svg" style="width: 350px; height: height: 350px;" />
                @endif

                <div class="mt-auto d-flex flex-column pt-4 text-center">
                  <span class="text-muted">
                    {{ __('admin.To get analysis for your pitch deck please upload your pitch deck video') }}
                  </span>
                  @if($_user->hasAnyRole('Registered'))
                  <a role="button"
                    onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 1)"
                    class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                    {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                  </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mt-6">
      <div class="masonry">
        @if ($company->pitch_deck_summary)
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ __('admin.Pitch_Deck_Information') }}
            </h5>
            <div class="d-flex flex-column gap-2">
              <div class="card" style="height: 132px;  overflow-y: auto;">
                <div class="card-body">
                  <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                    {{ $company->pitch_deck_summary }}
                  </h6>
                  <p class="text-muted line-clamp-2 fs-6">
                    {{ $company->pitch_deck_description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
        @if ($company->business_model != null)
        <div class="card">
          <div class="card-body d-flex flex-column justify-content-between">
            <h4>{{ __('admin.business_model') }}</h4>
            <div class="d-flex justify-content-between py-4">
              <div class="d-flex align-items-center">
                <i class="ti ti-alert-circle ti-24px text-primary me-2"></i>
                {{ $company ? __('admin.' . $company->business_model) : '' }}
              </div>
              @if ($company->business_model == 'Other')
              <div class="d-flex align-items-center">
                <i class="ti ti-chart-bar ti-24px text-primary me-2"></i>
                {{ $company ? $company->other_business_model : '' }}
              </div>
              @endif
            </div>
            <div class="d-flex justify-content-between py-4">
              <div class="d-flex align-items-center">
                <i class="ti ti-flag ti-24px text-primary me-2"></i>
                {{ $company ? $company->business_model_description : '' }}
              </div>
              @php
              $fileNames = basename($company->business_plan);
              @endphp
              <div class="d-flex align-items-center">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-3">
                    <i class="ti ti-file fs-1 text-warning"></i>
                    <span>{{ $fileNames }}</span>
                  </div>
                  <a class="btn btn-transparent" href="{{ $company->business_plan }}" target="_blank" download>
                    <i class="ti ti-download fs-3 text-primary"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
        @if ($company->about_problem != null)
        <div class="card">
          <div class="card-body">
            <h5>{{ __('admin.about_problem') }}</h5>
            <div class="d-flex gap-2 mb-3">
              <i class="ti ti-alert-circle ti-24px text-primary me-2"></i>
              <div class="line-clamp-2">
                {{ $company ? $company->about_problem : '' }}
              </div>
            </div>
            @if($company->about_market_opportunity)
            <div class="d-flex gap-2 mb-3">
              <div class="line-clamp-2">
                <i class="ti ti-chart-bar ti-24px text-primary me-2"></i>
                {{ $company ? $company->about_market_opportunity : '' }}
              </div>
            </div>
            @endif
            @if($company->about_competitors)
            <div class="d-flex gap-2 mb-3">
              <i class="ti ti-flag ti-24px text-primary me-2"></i>
              <div class="line-clamp-2">
                {{ $company ? $company->about_competitors : '' }}
              </div>
            </div>
            @endif
            @if($company->about_monetize)
            <div class="d-flex gap-2 mb-3">
              <i class="ti ti-cash ti-28px text-primary me-2"></i>
              <div class="line-clamp-2">
                {{ $company ? $company->about_monetize : '' }}
              </div>
            </div>
            @endif
            @if($company->about_challenges)
            <div class="d-flex gap-2">
              <i class="ti ti-target ti-24px text-primary me-2"></i>
              <div class="line-clamp-2">
                {{ $company ? $company->about_challenges : '' }}
              </div>
            </div>
            @endif
          </div>
        </div>
        @endif
        @if ($company->financial_plan != null)
        <div class="card">
          <div class="card-body d-flex flex-column justify-content-between">
            <h4>{{ __('admin.financial_plan') }}</h4>
            <div class="d-flex justify-content-between py-4">
              <div class="d-flex align-items-center">
                <i class="ti ti-flag ti-24px text-primary me-2"></i>
                {{ $company ? $company->financial_plan_description : '' }}
              </div>
              @php
              $fileNamesf = basename($company->financial_plan);
              @endphp
              <div class="d-flex align-items-center">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-3">
                    <i class="ti ti-file fs-1 text-warning"></i>
                    <span>{{ $fileNamesf }}</span>
                  </div>
                  <a class="btn btn-transparent" href="{{ $company->financial_plan }}" target="_blank" download>
                    <i class="ti ti-download fs-3 text-primary"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
    </section>

    <section class="mt-6 px-4">
      <div class="row">
        @if ($company->cycle)
        @hasanyrole('Admin|organizationManager')
        <div class="card">
          <div class="card-body">
            <h5 class="mb-4">{{ __('admin.Cycle informations') }}</h5>
            <table class="table">
              <tbody>
                <tr>
                  <td class="width-200"> {{ __('admin.Cycle') }}:</td>
                  <td>{{ $company->cycledata->title }}</td>
                </tr>
                <tr>
                  <td>{{ __('admin.Current Step') }}:</td>
                  <td><b>{{ $company->stepdata ? $company->stepdata->arr : '' }}:</b>
                    {{ $company->stepdata ? $company->stepdata->title : '' }}</td>
                </tr>
                <tr>
                  <td>{{ __('admin.Screening result') }}:</td>
                  <td>
                    @php
                    $form = \App\Step::where('cycle_id', $company->cycle)
                    ->where('stype', 1)
                    ->orderBy('arr')
                    ->first(['id']);
                    $weight = $company->companyWeight($company->id, $form?->id, 0);
                    $judges = $company->judges($company->id, $form?->id);
                    $judges = \App\User::whereIn('id', $judges)->get();
                    @endphp
                    <span
                      class="badge badge-light-{{ $weight < 25 ? 'danger' : ($weight < 50 ? 'warning' : 'success') }}">
                      {{ $weight }} %</span>
                  </td>
                </tr>
                <tr>
                  <td>
                    {{ __('admin.Screening by') }}:
                  </td>
                  <td>
                    @if( $judges->count() > 0 )
                    @foreach ($judges as $ju)
                    ({{ $ju->id }})
                    {{ $ju->{'first_name_' . $l} }}
                    {{ $ju->{'last_name_' . $l} }},
                    @endforeach
                    @endif
                  </td>
                </tr>
              </tbody>
            </table>
            @php($fdata = \App\Formdata::where('user_id', $company->user->id)->with(['field'])->get())
            <br /><br />
            <h5 class="mb-4 pb-3">
              {{ __('admin.Data collected through Cycle') }}
            </h5>
            @if (count($fdata) > 0)
            <table class="table">
              <tbody>
                @foreach ($fdata as $field)
                <tr>
                  <td class="width-200">{{ $field->question }}:</td>
                  <td>
                    @if( isset( $field?->field?->options?->type ) && $field?->field?->options?->type == 'file' )
                      <a target="_blank" href="{{ URL::to('/'. $field->answer) }}"> {{ __('admin.Download File') }} </a>
                    @else
                      {!! strpos($field->answer, 'http') === 0
                      ? '<a target="_blank" href="' . $field->answer . '"> ' . __('admin.Download File') . ' </a>'
                      : $field->answer !!}
                    @endif
                    @php(
                    $comments = \App\Formdata::where('field_id', $field->field_id)->where('company_id',
                    $company->id)->first(['comments'])->comments
                    )
                    @if ($comments != null)
                    @foreach ($comments as $key => $cc)
                    @if ($cc)
                    <div class="warning">{{ __('admin.comment') }}({{ $key }}):
                      {{ $cc }}</div>
                    @endif
                    @endforeach
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @endif
          </div>
        </div>
        @endrole
        @endif
      </div>
    </section>
  </div>
</div>
<!-- Modal -->
<div class="modal fade text-left" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel33">
          {{ __('messages.SendMessageTo') }}
          {{ $company?->user?->{'first_name_' . $l} }}
          {{ $company?->user?->{'last_name_' . $l} }}
          ({{ $company->{'name_' . $l} }}).
        </h4>
      </div>
      <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" name="sender" value="{{ $_user->id }}">
        <input type="hidden" name="receiver" value="{{ $company?->user?->id }}">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{ __('messages.Subject') }}:</label>
            <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}" class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{ __('messages.Message') }}:
            </label>
            <textarea class="form-control" id="message" name="message" rows="8"
              placeholder="{{ __('messages.Write your message') }}" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            <span class="d-none d-sm-block">{{ __('messages.Close') }}</span>
          </button>
          <button type="submit" class="btn btn-primary ml-1">
            <span class="d-none d-sm-block">{{ __('messages.Send') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- / Modal -->

<!-- Warning Modal -->
  @include(
    'partials.warning-modal',
    [
      'title' => 'Confirm Save',
      'message' => 'Confirm_Save_Screening',
      'onConfirm' => 'saveScreening()',
    ]
  )
<!--/ Warning Modal -->

<!-- Targeted Market Modal -->
<div class="modal fade" id="targetedMarketModal" tabindex="-1" aria-labelledby="targetedMarketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="targetedMarketModalLabel">{{ __('admin.Targeted_Market') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
      </div>
      <div class="modal-body d-flex flex-wrap gap-2">
        @if(!empty($countryIds))
        @if (is_array($countryIds) && count($countryIds) > 0)
          @foreach ($countryIds as $countryId)
            <span class="text-xs badge bg-label-light text-dark rounded-pill px-3">
              {{ $targetedCountries[$countryId]->{'name_' . $l} ?? __('admin.N/A') }}
            </span>
          @endforeach
        @else
          <span class="text-xs">{{ __('admin.N/A') }}</span>
        @endif
        @endif
      </div>
    </div>
  </div>
</div>

@stop

@section('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/form-wizard-numbered.js"></script>
  <script src="/vuexy/assets/js/form-wizard-validation.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var url = "{{ $company->pitch_deck }}";
      
      // Check if URL exists and is valid
      if (!url || url.trim() === '' || url === 'null' || url === 'undefined') {
        console.log('No PDF URL provided');
        return;
      }
      
      // Check if PDF.js is available
      if (typeof pdfjsLib !== 'undefined') {
        // Set the worker source for PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        try {
          // Validate URL format
          var testUrl = new URL(url, window.location.origin);
          
          var loadingTask = pdfjsLib.getDocument({
            url: testUrl.href,
            httpHeaders: {
              'Cache-Control': 'no-cache'
            }
          });
          
          loadingTask.promise.then(function(pdf) {
            console.log('PDF loaded successfully');
            pdf.getPage(1).then(function(page) {
              var scale = 1.5;
              var viewport = page.getViewport({ scale: scale });

              var canvas = document.getElementById('pdfViewer');
              if (canvas) {
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                var renderContext = {
                  canvasContext: context,
                  viewport: viewport
                };
                page.render(renderContext);
              }
            }).catch(function(error) {
              console.error('Error rendering PDF page:', error);
            });
          }).catch(function(error) {
            console.error('Error loading PDF:', error);
            // Handle different types of PDF loading errors
            if (error.name === 'InvalidPDFException') {
              console.error('The file is not a valid PDF');
            } else if (error.name === 'MissingPDFException') {
              console.error('PDF file not found');
            } else if (error.name === 'UnexpectedResponseException') {
              console.error('Unexpected server response while loading PDF');
            }
          });
        } catch (urlError) {
          console.error('Invalid URL format:', urlError);
        }
      } else {
        console.error('PDF.js library not loaded. Please check if the script is properly included.');
      }
    });
  </script>

  <script>
    $('#save-screening-btn').on('click', function() {
      $('#warningModal').modal('show');
    });
  </script>

  @if( $_user->hasRole('Judger') && !is_null( $evaluationForm ) && !empty( $evaluationForm ) && $evaluationForm->isPublished == false )
    <script type="text/javascript">
      $('#save_screening_btn').on('click', function(e) {
        e.preventDefault();
        submitJudgerScreeningForm('draft');
      });

      $('#publish_screening_btn').on('click', function(e) {
        e.preventDefault();
        submitJudgerScreeningForm('publish');
      });

      function submitJudgerScreeningForm(type)
      {
        let url = "{{ route('screening.evaluate_save_draft') }}";
        if( type == 'publish' ) { url = "{{ route('screening.evaluate_publish') }}"; }
        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
        var data = $('#screening_evaluation_form').serializeArray();
        let result = {
          id: null,
          type: type,
          program: null,
          evaluation: [],
          final_comment: ''
        };
        var tempEvalMap = {};
        data.forEach(item => {
          const name = item.name;
          const value = item.value;
          if (name === 'evaluation_form_id') {
              result.id = parseInt(value);
          } else if (name === 'evaluation_program_id') {
              result.program = parseInt(value);
          } else if (name === 'final_comments') {
              result.final_comment = value;
          } else if (name.startsWith('score_')) {
              const evalId = name.split('_')[1];
              tempEvalMap[evalId] = tempEvalMap[evalId] || { id: parseInt(evalId), score: null, comment: '' };
              tempEvalMap[evalId].score = value ? parseInt(value) : null;
          } else if (name.startsWith('comment_')) {
              const evalId = name.split('_')[1];
              tempEvalMap[evalId] = tempEvalMap[evalId] || { id: parseInt(evalId), score: null, comment: '' };
              tempEvalMap[evalId].comment = value;
          }
        });
        result.evaluation = Object.values(tempEvalMap).filter(entry => entry.score !== null || entry.comment !== '');

        // Show a loading indicator
        const saveBtn = document.getElementById('save_screening_btn');
        const publishBtn = document.getElementById('publish_screening_btn');
        const originalSaveBtnHtml = saveBtn.innerHTML;
        const originalPublishBtnHtml = publishBtn.innerHTML;
        
        if (type === 'draft') {
          const saveIcon = saveBtn.querySelector('i');
          if (saveIcon) {
            saveIcon.classList = "spinner-border spinner-border-sm ms-2";
          }
          saveBtn.disabled = true;
        } else {
          const publishIcon = publishBtn.querySelector('i');
          if (publishIcon) {
            publishIcon.classList = "spinner-border spinner-border-sm ms-2";
          }
          publishBtn.disabled = true;
        }

        $.ajax({
          type: 'POST',
          url: url,
          data: result,
          success: function(response) {
            // Reset buttons
            if (type === 'draft') {
              saveBtn.innerHTML = originalSaveBtnHtml;
              saveBtn.disabled = false;
            } else {
              publishBtn.innerHTML = originalPublishBtnHtml;
              publishBtn.disabled = false;
            }
            
            // Update evaluation view with latest data
            if (typeof updateEvaluationView === 'function') {
              updateEvaluationView();
            }
            
            // Navigate to the evaluation tab to show the results
            document.getElementById('pills-view-evaluation-tab').click();
            
            // Show success message
            const successMsg = type === 'publish' 
              ? '{{ __("admin.Evaluation published successfully") }}' 
              : '{{ __("admin.Evaluation saved successfully") }}';
              
            // You can use a toast or alert to show the success message
            alert(successMsg);
            // window.location.reload();
          },
          error: function(error) { 
            // Reset buttons
            if (type === 'draft') {
              saveBtn.innerHTML = originalSaveBtnHtml;
              saveBtn.disabled = false;
            } else {
              publishBtn.innerHTML = originalPublishBtnHtml;
              publishBtn.disabled = false;
            }
            
            console.log("DB Error occurred, please try again");
            alert("Error: " + (error.responseJSON?.message || "Failed to submit evaluation. Please try again."));
          },
        });
      }
    </script>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Handle dropdown selection for sub-criteria
        const subcriteriaSelects = document.querySelectorAll('.subcriteria-select');
        subcriteriaSelects.forEach(select => {
          select.addEventListener('change', function() {
            const target = this.value;
            const tabElement = document.querySelector(target);
            const tabLink = document.querySelector(`[data-bs-target="${target}"]`);
            
            if (tabElement && tabLink) {
              // Deactivate all tabs
              const allTabs = document.querySelectorAll(`.subcriteria-tabs .nav-link[data-bs-target^="#tab${select.dataset.criteriaKey}_"]`);
              allTabs.forEach(tab => {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
              });
              
              // Deactivate all panes
              const allPanes = document.querySelectorAll(`.tab-pane[id^="tab${select.dataset.criteriaKey}_"]`);
              allPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
              });
              
              // Activate the selected tab
              tabLink.classList.add('active');
              tabLink.setAttribute('aria-selected', 'true');
              
              // Activate the selected pane
              tabElement.classList.add('show', 'active');
            }
          });
        });
        
        // Auto-switch to dropdown mode when there are more than 4 sub-criteria
        document.querySelectorAll('.subcriteria-container').forEach(container => {
          const tabs = container.querySelectorAll('.nav-item');
          if (tabs.length > 4) {
            // Add class for many criteria to apply specific styles
            container.classList.add('many-criteria');
            
            const dropdownContainer = container.querySelector('.subcriteria-dropdown');
            const tabsContainer = container.querySelector('.subcriteria-tabs');
            
            // On mobile, show the dropdown (handled by CSS)
            
            // On desktop, make tabs scrollable
            if (window.innerWidth > 992) {
              // Add visual indicator if there are more tabs to scroll
              const tabsNav = container.querySelector('.nav-tabs');
              if (tabsNav) {
                tabsNav.addEventListener('scroll', function() {
                  checkScrollIndicators(tabsNav);
                });
                
                // Initial check
                setTimeout(() => checkScrollIndicators(tabsNav), 100);
              }
            }
          }
        });
        
        // Helper function to check if scroll indicators are needed
        function checkScrollIndicators(element) {
          const isScrollable = element.scrollWidth > element.clientWidth;
          const isScrolledToEnd = element.scrollLeft + element.clientWidth >= element.scrollWidth - 10;
          const isScrolledToStart = element.scrollLeft <= 10;
          
          element.classList.toggle('has-more-right', isScrollable && !isScrolledToEnd);
          element.classList.toggle('has-more-left', isScrollable && !isScrolledToStart);
        }
        
        // Sync tab changes with dropdown
        document.querySelectorAll('.subcriteria-tabs .nav-link').forEach(tab => {
          tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.dataset.bsTarget;
            const criteriaKey = targetId.split('_')[0].replace('#tab', '');
            const dropdown = document.getElementById('subcriteria-select-' + criteriaKey);
            
            if (dropdown) {
              dropdown.value = targetId;
            }
          });
        });

        // Initialize real-time evaluation tab update
        initializeEvaluationUpdater();
      });

      // Initialize real-time evaluation update functionality
      function initializeEvaluationUpdater() {
        // Get all form elements that need to be monitored
        const scoreSelects = document.querySelectorAll('.score-select');
        const commentTextareas = document.querySelectorAll('textarea[id^="comment_"]');
        const finalCommentsTextarea = document.getElementById('final_comments');

        // Add event listeners for real-time updates
        scoreSelects.forEach(select => {
          select.addEventListener('change', updateEvaluationTab);
        });

        commentTextareas.forEach(textarea => {
          textarea.addEventListener('input', debounce(updateEvaluationTab, 500));
          textarea.addEventListener('blur', updateEvaluationTab);
        });

        if (finalCommentsTextarea) {
          finalCommentsTextarea.addEventListener('input', debounce(updateEvaluationTab, 500));
          finalCommentsTextarea.addEventListener('blur', updateEvaluationTab);
        }

        // Initial update of evaluation tab
        updateEvaluationTab();
      }

      // Debounce function to prevent excessive updates while typing
      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }

      // Main function to update the evaluation tab
      function updateEvaluationTab() {
        updateCategoryPercentages();
        updateEvaluationComments();
        updateFinalCommentsDisplay();
      }

      // Update category percentage scores in real-time
      function updateCategoryPercentages() {
        // Get all categories from the evaluation form
        const evaluationCategories = document.querySelectorAll('#pills-view-evaluation .row.mb-6 .col-3');
        
        evaluationCategories.forEach((categoryElement, categoryIndex) => {
          const categoryTitle = categoryElement.querySelector('h6');
          if (!categoryTitle) return;
          
          const categoryName = categoryTitle.textContent.trim();
          
          // Find all score inputs for this category
          let totalScore = 0;
          let scoreCount = 0;
          
          // Get all criteria containers
          const criteriaContainers = document.querySelectorAll('.content');
          
          criteriaContainers.forEach(container => {
            const containerCategoryName = container.querySelector('.content-header h6');
            if (containerCategoryName && containerCategoryName.textContent.trim() === categoryName) {
              // Find all score selects within this criteria container
              const scoreSelects = container.querySelectorAll('.score-select');
              scoreSelects.forEach(select => {
                if (select.value && select.value !== '') {
                  totalScore += parseInt(select.value);
                  scoreCount++;
                }
              });
            }
          });
          
          // Calculate percentage
          const percentage = scoreCount > 0 ? ((totalScore / (scoreCount * 5)) * 100) : 0;
          
          // Update the display
          const percentageDisplay = categoryElement.querySelector('.fs-5.fw-bold.text-dark');
          if (percentageDisplay) {
            percentageDisplay.textContent = percentage.toFixed(2);
          }
        });
      }

      // Update evaluation comments section in real-time
      function updateEvaluationComments() {
        const commentSections = document.querySelectorAll('#pills-view-evaluation .col-12.mb-5.border-bottom');
        
        commentSections.forEach(section => {
          const categoryHeader = section.querySelector('h5');
          if (!categoryHeader) return;
          
          const categoryName = categoryHeader.textContent.replace(' {{ __("admin.Comments") }}', '').trim();
          const commentItems = section.querySelectorAll('.d-flex.flex-column.mb-4');
          
          commentItems.forEach((item, index) => {
            const subcriteriaNameElement = item.querySelector('.fs-5.mb-0.text-dark');
            if (!subcriteriaNameElement) return;
            
            const subcriteriaName = subcriteriaNameElement.textContent.trim();
            
            // Find the corresponding comment textarea
            let matchingComment = '{{ __("admin.No comment provided") }}';
            
            // Look through all criteria containers
            const criteriaContainers = document.querySelectorAll('.content');
            
            criteriaContainers.forEach(container => {
              const containerCategoryName = container.querySelector('.content-header h6');
              if (containerCategoryName && containerCategoryName.textContent.trim() === categoryName) {
                // Find the subcriteria tabs
                const subcriteriaButtons = container.querySelectorAll('.nav-link');
                subcriteriaButtons.forEach(button => {
                  const buttonText = button.textContent.trim();
                  if (buttonText === subcriteriaName) {
                    // Get the target tab ID
                    const targetId = button.getAttribute('data-bs-target');
                    if (targetId) {
                      const targetTab = document.querySelector(targetId);
                      if (targetTab) {
                        const textarea = targetTab.querySelector('textarea[id^="comment_"]');
                        if (textarea && textarea.value.trim()) {
                          matchingComment = textarea.value.trim();
                        }
                      }
                    }
                  }
                });
              }
            });
            
            // Update the comment display
            const commentDisplay = item.querySelector('.text-light.line-clamp-3');
            if (commentDisplay) {
              commentDisplay.textContent = matchingComment;
            }
          });
        });
      }

      // Update final comments display in real-time
      function updateFinalCommentsDisplay() {
        const finalCommentsTextarea = document.getElementById('final_comments');
        const finalCommentsDisplay = document.querySelector('#pills-view-evaluation .col-12:last-child .text-light.line-clamp-3');
        
        if (finalCommentsTextarea && finalCommentsDisplay) {
          const finalCommentText = finalCommentsTextarea.value.trim() || '{{ __("admin.No final comments provided") }}';
          finalCommentsDisplay.textContent = finalCommentText;
        }
        
        // Alternative selector in case the first one doesn't work
        if (finalCommentsTextarea && !finalCommentsDisplay) {
          const alternativeDisplay = document.querySelector('#pills-view-evaluation h5:contains("{{ __("admin.Final Comments")}}") + small');
          if (alternativeDisplay) {
            const finalCommentText = finalCommentsTextarea.value.trim() || '{{ __("admin.No final comments provided") }}';
            alternativeDisplay.textContent = finalCommentText;
          }
        }
        
        // Most reliable approach - find by the heading text
        if (finalCommentsTextarea) {
          const headings = document.querySelectorAll('#pills-view-evaluation h5');
          headings.forEach(heading => {
            if (heading.textContent.includes('{{ __("admin.Final Comments") }}')) {
              const commentDisplay = heading.nextElementSibling;
              if (commentDisplay && commentDisplay.tagName === 'SMALL') {
                const finalCommentText = finalCommentsTextarea.value.trim() || '{{ __("admin.No final comments provided") }}';
                commentDisplay.textContent = finalCommentText;
              }
            }
          });
        }
      }
    </script>

    <script>
      function saveScreening() {
        // Go back
        window.history.back();
      }
    </script>

    {{-- <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Initial update of the evaluation view with existing data
        updateEvaluationView();
        
        // Update evaluation view when score or comment changes
        const scoreSelects = document.querySelectorAll('.score-select');
        const commentAreas = document.querySelectorAll('textarea[id^="comment_"]');
        const finalCommentArea = document.getElementById('final_comments');
        const nextButtons = document.querySelectorAll('.btn-next');
        const tabLinks = document.querySelectorAll('[data-bs-toggle="pill"]');
        
        // Add event listeners
        scoreSelects.forEach(select => {
          select.addEventListener('change', updateEvaluationView);
        });
        
        commentAreas.forEach(textarea => {
          textarea.addEventListener('blur', updateEvaluationView);
        });
        
        if (finalCommentArea) {
          finalCommentArea.addEventListener('blur', updateEvaluationView);
        }
        
        nextButtons.forEach(button => {
          button.addEventListener('click', updateEvaluationView);
        });
        
        // Previous buttons should also update the view
        const prevButtons = document.querySelectorAll('.btn-prev');
        prevButtons.forEach(button => {
          button.addEventListener('click', updateEvaluationView);
        });
        
        // View Evaluation button in wizard
        const viewEvalButtons = document.querySelectorAll('.view-evaluation-btn');
        viewEvalButtons.forEach(button => {
          button.addEventListener('click', function() {
            updateEvaluationView();
            document.getElementById('pills-view-evaluation-tab').click();
          });
        });
        
        tabLinks.forEach(tab => {
          tab.addEventListener('click', function(e) {
            if (e.target.getAttribute('data-bs-target') === '#pills-view-evaluation') {
              updateEvaluationView();
            }
          });
        });
        
        // Save and Publish buttons trigger evaluation update
        const saveBtn = document.getElementById('save_screening_btn');
        const publishBtn = document.getElementById('publish_screening_btn');
        
        if (saveBtn) {
          saveBtn.addEventListener('click', updateEvaluationView);
        }
        
        if (publishBtn) {
          publishBtn.addEventListener('click', updateEvaluationView);
        }
      });
                                
      function updateEvaluationView() {
        // Collect all evaluation data
        const scoreSelects = document.querySelectorAll('.score-select');
        const commentAreas = document.querySelectorAll('textarea[id^="comment_"]');
        const finalCommentArea = document.getElementById('final_comments');
        
        // Get container elements
        const evaluationSummaryContainer = document.getElementById('evaluation-summary-container');
        const finalCommentsDisplay = document.getElementById('final-comments-display');
        const noEvaluationsMessage = document.getElementById('no-evaluations-message');
        const noFinalComment = document.getElementById('no-final-comment');
        
        // Summary statistics
        const totalScoreElement = document.getElementById('total-score');
        const totalCategoriesElement = document.getElementById('total-categories');
        const totalCriteriaElement = document.getElementById('total-criteria');
        
        // Collect data from the form
        let hasEvaluations = false;
        let totalScore = 0;
        let scoredCriteria = 0;
        
        // Create a comprehensive data structure for evaluations
        const evaluationsData = {};
        const categories = new Set();
        
        // Process scores and comments together to build a combined data structure
        scoreSelects.forEach(select => {
          const criteriaId = select.id.split('_')[1];
          const criteriaName = select.closest('.tab-pane').querySelector('.nav-link.active').textContent.trim();
          const categoryName = select.closest('.content').querySelector('.content-header h6').textContent.trim();
          
          // Initialize category and criteria in the data structure if they don't exist
          if (!evaluationsData[categoryName]) {
            evaluationsData[categoryName] = {};
          }
          
          if (!evaluationsData[categoryName][criteriaName]) {
            evaluationsData[categoryName][criteriaName] = {
              criteriaId,
              score: '',
              comment: ''
            };
          }
          
          // Add score if it exists
          if (select.value) {
            hasEvaluations = true;
            totalScore += parseInt(select.value);
            scoredCriteria++;
            categories.add(categoryName);
            evaluationsData[categoryName][criteriaName].score = select.value;
          }
        });
        
        // Process comments
        commentAreas.forEach(textarea => {
          const criteriaId = textarea.id.split('_')[1];
          const criteriaName = textarea.closest('.tab-pane').querySelector('.nav-link.active').textContent.trim();
          const categoryName = textarea.closest('.content').querySelector('.content-header h6').textContent.trim();
          
          // Initialize category and criteria in the data structure if they don't exist
          if (!evaluationsData[categoryName]) {
            evaluationsData[categoryName] = {};
          }
          
          if (!evaluationsData[categoryName][criteriaName]) {
            evaluationsData[categoryName][criteriaName] = {
              criteriaId,
              score: '',
              comment: ''
            };
          }
          
          // Add comment if it exists
          if (textarea.value.trim()) {
            hasEvaluations = true;
            evaluationsData[categoryName][criteriaName].comment = textarea.value.trim();
          }
        });
        
        // Update evaluation summary section - combined scores and comments
        if (hasEvaluations) {
          if (noEvaluationsMessage) noEvaluationsMessage.style.display = 'none';
          let evalHTML = '';
          
          // For each category
          Object.keys(evaluationsData).forEach(categoryName => {
            // Calculate category total score
            let categoryTotalScore = 0;
            let categoryScoreCount = 0;
            Object.values(evaluationsData[categoryName]).forEach(criteria => {
              if (criteria.score) {
                categoryTotalScore += parseInt(criteria.score);
                categoryScoreCount++;
              }
            });
            
            // Create category header with total score
            evalHTML += `
              <div class="card mb-4">
                <div class="card-header bg-light">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">${categoryName}</h5>
                    ${categoryScoreCount > 0 ? 
                      `<div class="d-flex align-items-center gap-2">
                        <span class="text-secondary">{{ __('admin.Category Score') }}:</span>
                        <span class="badge bg-primary rounded-pill fs-6">${categoryTotalScore}/${categoryScoreCount * 5}</span>
                      </div>` : ''}
                  </div>
                </div>
                <div class="card-body">
            `;
            
            // For each criteria in this category
            Object.keys(evaluationsData[categoryName]).forEach(criteriaName => {
              const criteria = evaluationsData[categoryName][criteriaName];
              
              evalHTML += `
                <div class="border-bottom pb-3 mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold">${criteriaName}</h6>
                    ${criteria.score ? 
                      `<span class="badge bg-primary rounded-pill">${criteria.score}/5</span>` : 
                      `<span class="badge bg-label-secondary">{{ __('admin.No Score') }}</span>`}
                  </div>
              `;
              
              // Add comment if it exists
              if (criteria.comment) {
                evalHTML += `
                  <div class="mt-2">
                    <p class="mb-0 text-dark">${criteria.comment}</p>
                  </div>
                `;
              } else {
                evalHTML += `
                  <div class="mt-2">
                    <em class="text-muted">{{ __('admin.No comment provided') }}</em>
                  </div>
                `;
              }
              
              evalHTML += `</div>`;
            });
            
            evalHTML += `
                </div>
              </div>
            `;
          });
          
          evaluationSummaryContainer.innerHTML = evalHTML;
        } else {
          if (noEvaluationsMessage) noEvaluationsMessage.style.display = 'block';
        }
        
        // Update final comments
        if (finalCommentArea && finalCommentArea.value.trim()) {
          if (noFinalComment) noFinalComment.style.display = 'none';
          finalCommentsDisplay.innerHTML = finalCommentArea.value.trim();
        } else {
          if (noFinalComment) noFinalComment.style.display = 'block';
        }
        
        // Update summary statistics
        if (totalScoreElement) totalScoreElement.textContent = totalScore;
        if (totalCategoriesElement) totalCategoriesElement.textContent = categories.size;
        if (totalCriteriaElement) totalCriteriaElement.textContent = scoredCriteria;
      }
    </script> --}}

  @endif

  <script>
    // Milestones Carousel Functionality
    document.addEventListener('DOMContentLoaded', function() {
      const milestonesContainer = document.getElementById('milestonesContainer');
      const prevBtn = document.getElementById('milestonesPrev');
      const nextBtn = document.getElementById('milestonesNext');
      
      if (!milestonesContainer || !prevBtn || !nextBtn) return;
      
      const cards = document.querySelectorAll('.milestone-card');
      const totalCards = cards.length;
      let currentIndex = 0;
      
      function getCardsPerView() {
        if (window.innerWidth <= 767) return 1; // Mobile
        if (window.innerWidth <= 991) return 2; // Tablet
        return 3; // Desktop
      }
      
      function updateCarousel() {
        const cardsPerView = getCardsPerView();
        const cardWidth = 100 / cardsPerView;
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        
        // Adjust currentIndex if it exceeds new maxIndex
        if (currentIndex > maxIndex) {
          currentIndex = maxIndex;
        }
        
        const translateX = -(currentIndex * cardWidth);
        milestonesContainer.style.transform = `translateX(${translateX}%)`;
        
        // Update card widths for responsive behavior
        cards.forEach(card => {
          if (cardsPerView === 1) {
            card.style.width = '100%';
          } else if (cardsPerView === 2) {
            card.style.width = '50%';
          } else {
            card.style.width = '33.333%';
          }
        });
        
        // Update button states
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex >= maxIndex || totalCards <= cardsPerView;
        
        // Update button styles
        prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        nextBtn.style.opacity = (currentIndex >= maxIndex || totalCards <= cardsPerView) ? '0.5' : '1';
        
        // Hide navigation if not needed
        const navButtons = document.querySelector('.d-flex.align-items-center.gap-2');
        if (navButtons) {
          navButtons.style.display = totalCards <= cardsPerView ? 'none' : 'flex';
        }
      }
      
      // Event listeners
      prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
          currentIndex--;
          updateCarousel();
        }
      });
      
      nextBtn.addEventListener('click', function() {
        const cardsPerView = getCardsPerView();
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        if (currentIndex < maxIndex) {
          currentIndex++;
          updateCarousel();
        }
      });
      
      // Handle responsive behavior
      function handleResize() {
        updateCarousel();
      }
      
      window.addEventListener('resize', handleResize);
      
      // Initialize carousel
      updateCarousel();
      
      // Touch/swipe support for mobile
      let startX = 0;
      let isDragging = false;
      
      milestonesContainer.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        isDragging = true;
      });
      
      milestonesContainer.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
      });
      
      milestonesContainer.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        isDragging = false;
        
        const endX = e.changedTouches[0].clientX;
        const diffX = startX - endX;
        const cardsPerView = getCardsPerView();
        const maxIndex = Math.max(0, totalCards - cardsPerView);
        
        // Minimum swipe distance
        if (Math.abs(diffX) > 50) {
          if (diffX > 0 && currentIndex < maxIndex) {
            // Swipe left - next
            currentIndex++;
            updateCarousel();
          } else if (diffX < 0 && currentIndex > 0) {
            // Swipe right - previous
            currentIndex--;
            updateCarousel();
          }
        }
      });
    });
  </script>
@stop
