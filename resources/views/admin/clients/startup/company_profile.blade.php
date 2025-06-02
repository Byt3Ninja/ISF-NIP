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
  position: absolute;
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
  z-index: 2;
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
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
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
                    <a role="button"
                      onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
                      class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                      {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                    </a>
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
                    <a role="button"
                      onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
                      class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                      {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                    </a>
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
                  <a role="button"
                    onclick="window.location.href='{{ route('company.profile_view_mine') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 1)"
                    class="cursor-pointer text-primary fw-medium text-decoration-underline mt-1">
                    {{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}
                  </a>
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
        @if (count($Achieved_Milestones) > 0)
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ __('admin.Achieved_Milestones') }}
            </h5>
            <div class="d-flex flex-column gap-2">
              @foreach ($Achieved_Milestones as $key => $Achieved)
              <div class="card" style="height: 132px;">
                <div class="card-body">
                  <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                    <div style="width: 32px; height: 32px;" class="rounded-circle bg-primary text-white d-flex 
                      justify-content-center align-items-center me-2">
                      {{ $key + 1 }}
                    </div>
                    {{ $Achieved->name }}
                  </h6>
                  <p class="text-muted line-clamp-2 fs-6">
                    {{ $Achieved->description }}
                  </p>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        @endif
        @if (count($Planned_Milestones) > 0)
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">{{ __('admin.Planned_Milestones') }}
            </h5>
            <div class="d-flex flex-column gap-2">
              @foreach ($Planned_Milestones as $key => $Planned)
              <div class="card" style="height: 132px;">
                <div class="card-body">
                  <h6 class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                    <div style="width: 32px; height: 32px;" class="rounded-circle bg-primary text-white d-flex 
                      justify-content-center align-items-center me-2">
                      {{ $key + 1 }}
                    </div>
                    {{ $Planned->name }}
                  </h6>
                  <p class="text-muted line-clamp-2 fs-6">
                    {{ $Planned->description }}
                  </p>
                </div>
              </div>
              @endforeach
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
            @php($fdata = \App\Formdata::where('user_id', $company->user->id)->get())
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
                    {!! strpos($field->answer, 'http') === 0
                    ? '<a target="_blank" href="' . $field->answer . '"> ' . __('admin.Download File') . ' </a>'
                    : $field->answer !!}
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
  <script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>
  <script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/form-wizard-numbered.js"></script>
  <script src="/vuexy/assets/js/form-wizard-validation.js"></script>
<script>
var url = {
  {
    $company - > pitch_deck
  }
};

var pdfjsLib = window['pdfjs-dist/build/pdf'];
pdfjsLib.GlobalWorkerOptions.workerSrc =
  'https://mozilla.github.io/pdf.js/build/pdf.worker.js';

var loadingTask = pdfjsLib.getDocument(url);
loadingTask.promise.then(function(pdf) {
  pdf.getPage(1).then(function(page) {
    var scale = 1.5;
    var viewport = page.getViewport({
      scale: scale
    });

    var canvas = document.getElementById('pdfViewer');
    var context = canvas.getContext('2d');
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    var renderContext = {
      canvasContext: context,
      viewport: viewport
    };
    page.render(renderContext);
  });
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
          window.location.reload();
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
@stop
