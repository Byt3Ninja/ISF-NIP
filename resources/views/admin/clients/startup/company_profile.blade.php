@extends('layouts.admin')

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
</style>
@stop

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">{{ __('admin.My company') }}</h4>
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
              <div class="card-body">
                <div class="align-self-center d-flex flex-column text-center align-items-center w-100">
                  <div class="text-center">
                    <img class="card-icon mb-2" src="/icons/target-market.svg" alt="icon"
                      style="width: 50px; height: 50px;"
                    />
                  </div>
                  <h5 class="mb-2 fw-semibold">{{ __('admin.Targeted_Market') }}</h5>
                  <div class="d-flex align-items-center gap-1 pb-2" style="width: 99.5%; overflow: auto; white-space: nowrap;">
                    @if($company->targeted_market != null)
                    @php
                    $countryIds = json_decode($company->targeted_market, true);
                    @endphp

                    @if (is_array($countryIds) && count($countryIds) > 0)
                    @php
                    $targetedCountries = App\Country::whereIn('id', $countryIds)->get()->keyBy('id');
                    @endphp
                    @foreach ($countryIds as $countryId)
                    <span
                      class="text-xs badge bg-label-light text-dark rounded-pill d-flex justify-content-center align-items-center gap-2 px-3"
                      style="width: fit-content;">
                      {{ $targetedCountries[$countryId]->{'name_' . $l} ?? __('admin.N/A') }}
                    </span>
                    @endforeach
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
                      onclick="window.location.href='{{ route('my_company') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
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
                      onclick="window.location.href='{{ route('my_company') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 0)"
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
                    onclick="window.location.href='{{ route('my_company') }}'; localStorage.setItem('activeCompanyTab', 5); localStorage.setItem('activePitchTab', 1)"
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
                    $weight = $company->companyWeight($company->id, $form->id, 0);
                    $judges = $company->judges($company->id, $form->id);
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
          {{ $company->user->{'first_name_' . $l} }}
          {{ $company->user->{'last_name_' . $l} }}
          ({{ $company->{'name_' . $l} }}).
        </h4>
      </div>
      <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" name="sender" value="{{ $_user->id }}">
        <input type="hidden" name="receiver" value="{{ $company->user->id }}">
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
@stop
@section('scripts')
<script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>

@stop
@section('pagescripts')

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

@stop