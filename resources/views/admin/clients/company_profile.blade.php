@extends('layouts.admin')

@section('style')
  <style>
    .radius-15 {
      border-radius: 15px;
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

    .progress-circle {
      position: relative;
      display: inline-block;
    }

    .circle-bg {
      stroke: #e6e6e6;
      /* Background circle color */
    }

    .circle {
      stroke: rgba(115, 103, 240, 0.9);
      /* Progress color */
      transition: stroke-dasharray 0.3s ease;
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

    @media screen and (min-width: 768px) {
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
      <section>
        <!-- Company Info Card -->
        <div class="card shadow-sm" style="border-radius: 15px;">
          <div class="card-body row">
            <div class="col-md-10">
              <div class="d-flex align-items-center" style="gap: 1rem;">
                <div class="border d-flex radius-15 d-flex flex-shrink-0"
                  style="width: 90px; height: 90px;">
                  <img src="{{ $company->logo_url }}" class="img-fluid m-auto"
                    alt="logo" style="width: 90px; height: 90px; border-radius: 15px;" />
                </div>
                <div class="d-flex flex-column pt-3">
                  <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                      <i
                        class="ti ti-building-skyscraper ti-24px me-2 text-primary"></i>
                      <span
                        class="fw-bold fs-5 pt-1">{{ $company->{'name_' . $l} }}
                        </h5>
                    </div>
                    <div class="d-flex align-items-center mx-8"
                      style="gap: 1rem;">
                      @if ($company->facebook)
                        <a class="d-flex align-items-center"
                          href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                          target='_blank'>
                          <i class="fa-brands fa-facebook-f fs-4"></i>
                        </a>
                      @endif
                      @if ($company->linkedin)
                        <a class="d-flex align-items-center"
                          href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                          target='_blank'>
                          <img src="/icons/linkedin.svg" />
                        </a>
                      @endif
                      <a href="{{ 'https://wa.me/' . $company->country_code . $company->whatsapp }}"
                        target="_blank" class="d-flex align-items-center">
                        <i class="fa-brands fa-whatsapp fs-3 text-success"></i>
                      </a>
                      @if ($company->twitter)
                        <a class="d-flex align-items-center"
                          href="{{ strpos($company->twitter, 'http') === 0 ? $company->twitter : 'http://' . $company->twitter }}"
                          target='_blank'>
                          <i class="fa-brands fa-twitter fs-4 text-info"></i>
                        </a>
                      @endif
                      @if ($company->youtube)
                        <a class="d-flex align-items-center"
                          href="{{ strpos($company->youtube, 'http') === 0 ? $company->youtube : 'http://' . $company->youtube }}"
                          target='_blank'>
                          <i class="fa-brands fa-youtube fs-4 text-danger"></i>
                        </a>
                      @endif
                    </div>
                  </div>
                  <p class="text-light mt-2 fw-medium line-clamp-1">
                    {{ __($company->sector) }}
                  </p>
                </div>

              </div>
            </div>
            <!-- Action Button -->
            <div class="col-md-2 d-flex align-items-center justify-content-end">

              @if (isset(Auth::user()->company()->first()->id) &&
                      $company->id == Auth::user()->company()->first()->id)
                <a href="/dashboard/myCompany"
                  class="btn btn-label-primary shadow-sm text-primary fw-bold"
                  style="border-radius: 15px;">
                  {{ __('admin.Edit') }} <i class="ti ti-edit mx-2"></i>
                </a>
              @else
                <a href="" data-bs-toggle="modal"
                  data-bs-target="#MessageModal"
                  class="btn btn-label-primary shadow-sm text-primary fw-bold text-nowrap"
                  style="border-radius: 15px;">
                  {{ __('admin.Contact') }} <i class="ti ti-message mx-2"></i>
                </a>
              @endif
            </div>
            <!-- / Action Button -->
          </div>
        </div>
        <!-- / Company Info Card -->
      </section>

      <section class="mt-6">
        <div class="row">
          <div class="col-md-6">
            <div class="card shadow-sm"
              style="border-radius: 15px; height: 220px;">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div class="d-flex flex-column justify-content-between">
                    <div class="d-flex">
                      <div
                        class="p-2 bg-label-warning radius-15 d-flex align-items-center"
                        style="width: 150px; height: 74px; gap: 10px;">
                        <i class="ti ti-users ti-28px"></i>
                        <div class="mx-1 d-flex flex-column">
                          <span
                            class="fw-bold text-black fs-6">{{ $company->team_num ?? 0 }}</span>
                          <span class="fw-bold mt-2"
                            style="font-size: 12px;">{{ __('admin.Team number') }}</span>
                        </div>
                      </div>
                      <div class="p-2 bg-label-info mx-4 radius-15"
                        style="width: 150px; height: 74px;">
                        <div
                          class="d-flex flex-column align-items-center justif-content-center">
                          <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-calendar-time ti-28px"></i>
                            <span
                              class="mx-1 fw-bold text-black fs-6">
                              {{ $company->est_date ?? __('admin.N/A') }}
                            </span>
                          </div>
                          <span class="text-nowrap"
                            style="font-size: 12px;">
                            {{ __('admin.Established date') }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="mt-10" style="max-width: 220px;">
                      <span
                        class="fw-bold">{{ __('admin.Working_Team_Details') }}</span>
                      <p class="text-muted line-clamp-2 p-2">
                        {{ $company->working_team_summary ?? __('admin.N/A') }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 mt-6 mt-md-0">
            <div class="card shadow-sm"
              style="border-radius: 15px; height: 220px;">
              <div
                class="card-body d-flex flex-column h-100 justify-content-between">
                <div class="d-flex justify-content-between py-4">
                  <div class="d-flex align-items-center">
                    <i class="ti ti-map-2 ti-24px text-primary me-2"></i>
                    {{ optional($company->country)->{'name_' . $l} }}
                  </div>
                  <div class="d-flex align-items-center">
                    <i class="ti ti-map-pin ti-24px text-primary me-2"></i>
                    {{ optional($company->state)->{'name_' . $l} }}
                  </div>
                  <div class="d-flex align-items-center">
                    <i
                      class="ti ti-current-location ti-24px text-primary me-2"></i>
                    {{ optional($company->city)->{'name_' . $l} }}
                  </div>
                </div>
                <div class="d-flex justify-content-between">
                  @if ($company->email)
                    <div class="d-flex align-items-center">
                      <i class="ti ti-mail ti-24px text-primary me-2"></i>
                      {{ $company->email }}
                    </div>
                  @endif
                  @if ($company->website)
                    <div class="d-flex align-items-center">
                      <i class="ti ti-world ti-24px text-primary me-2"></i>
                      <a href="{{ strpos($company->website, 'http') === 0 ? $company->website : 'http://' . $company->website }}"
                        target="_blank">
                        {{ $company->website }}
                      </a>
                    </div>
                  @endif
                </div>
                @if ($company->phone)
                  <div class="d-flex align-items-center">
                    <i class="ti ti-phone ti-28px text-primary me-2"></i>
                    {{ $company->phone }}
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="w-100 mt-6 d-flex flex-column flex-md-row"
        style="gap: 2rem;">
        <div class="section-3">
          <div class="row">
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm"
                style="background-color: #c4b5fd; border-radius: 15px;">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2"
                        src="/icons/development-stages.svg" alt="icon" />
                    </div>
                    <span
                      class="text-black fs-5">{{ __('admin.development_stage') }}</span>
                    <span
                      class="text-primary">{{ $company->development_stage ?? __('admin.N/A') }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm"
                style="background-color: #7dd3fc; border-radius: 15px;">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/headquarter.svg"
                        alt="icon" />
                    </div>
                    <span
                      class="text-black fs-5">{{ __('admin.Country') }}</span>
                    <span
                      class="text-primary">{{ optional($company->country)->{'name_' . $l} }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm"
                style="background-color: #fdba74; border-radius: 15px;">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/coin.svg"
                        alt="icon" />
                    </div>
                    <span class="text-black fs-5">{{ __('admin.Stage') }}</span>
                    <span
                      class="text-primary">{{ $company->stage ? __('admin.stage_'.$company->stage) : __('admin.N/A') }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm"
                style="background-color: #5eead4; border-radius: 15px;">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/target-market.svg"
                        alt="icon" />
                    </div>
                    <span
                      class="text-black fs-5">{{ __('admin.Targeted_Market') }}</span>
                    <div
                      class="d-flex align-items-center justify-content-center mt-2"
                      style="gap: 5px;">
                      @php
                        $countryIds  = json_decode($company->targeted_market, true);
                      @endphp

                        @if(is_array($countryIds) && count($countryIds) > 0)
                        @php
                            $targetedCountries = App\Country::whereIn('id', $countryIds)->get()->keyBy('id');
                        @endphp
                        @foreach ($countryIds as $countryId)
                            <button class="btn btn-outline-primary btn-xs rounded-pill">
                                {{ $targetedCountries[$countryId]->{"name_".$l} ?? __('admin.N/A') }}
                            </button>
                        @endforeach
                        @else
                        <p>{{ __('admin.N/A') }}</p>
                        @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card shadow-sm"
                style="background-color: #fca5a5; border-radius: 15px;">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2"
                        src="/icons/business-modal.svg" alt="icon" />
                    </div>
                    <span
                      class="text-black fs-5">{{ __('admin.Business Type') }}</span>
                    <div
                      class="d-flex align-items-center justify-content-center mt-2"
                      style="gap: 5px;">
                      <button
                        class="btn btn-outline-primary btn-xs rounded-pill">{{ $company->business_type ?? __('admin.N/A') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-4 mb-4">
              <div class="card bg-light">
                <div class="card-body">
                  <div class="align-self-center d-flex flex-column text-center">
                    <div class="text-center">
                      <img class="card-icon mb-2" src="/icons/industries.svg"
                        alt="icon" />
                    </div>
                    <span
                      class="text-black fs-5">{{ __('admin.Sector') }}</span>
                    <div
                      class="d-flex align-items-center justify-content-center mt-2"
                      style="gap: 5px;">
                      <button
                        class="btn btn-outline-primary btn-xs rounded-pill">{{ $company->sector ?? __('admin.N/A') }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex flex-grow-1">
          <div class="row d-flex flex-column w-100" style="gap: 1rem;">
            <div class="border-primary w-100"
              style="border: 2px dashed; height: 100px;">
              <div class="p-2" style="display: grid; place-items: center;">
                <img class="sm-card-icon mb-2" src="/icons/coin.svg"
                  alt="icon" />
                <span class="text-black"
                  style="font-size: 12px;">{{ __('admin.Funding_Target') }}</span>
                <span class="text-primary commanums"
                  style="font-size: 12px;">{{ $company->Funding_Target }}
                  {{ $company->Currency }}</span>
              </div>
            </div>
            <div class="border-primary w-100"
              style="border: 2px dashed; height: 100px;">
              <div class="p-2" style="display: grid; place-items: center;">
                <img class="sm-card-icon mb-2" src="/icons/coin.svg"
                  alt="icon" />
                <span class="text-black"
                  style="font-size: 12px;">{{ __('admin.Pre_money_Valuation') }}</span>
                <span class="text-primary commanums"
                  style="font-size: 12px;">{{ $company->Pre_money_Valuation }}
                  {{ $company->Currency }}</span>
              </div>
            </div>
            <div class="border-primary w-100"
              style="border: 2px dashed; height: 100px;">
              <div class="p-2" style="display: grid; place-items: center;">
                <img class="sm-card-icon mb-2" src="/icons/coin.svg"
                  alt="icon" />
                <span class="text-black"
                  style="font-size: 12px;">{{ __('admin.raised_fund') }}</span>
                <span class="text-primary commanums"
                  style="font-size: 12px;">{{ $company->raised_fund }}
                  {{ $company->Currency }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="mt-6">
        <div class="row">
          @if (count($fonders) > 0)
            <div class="col-md-4">
              <div class="card shadow-sm"
                style="border-radius: 15px;">
                <div class="p-3">
                  <h5 class="card-title mb-3">{{ __('admin.Founders') }}</h5>
                  <div class="row">
                    @foreach ($fonders as $fonder)
                      <div class="col-12 mb-3">
                        <div class="p-3 shadow-sm border overflow-hidden"
                          style="border-radius: 15px;">
                          <div class="d-flex align-items-center">
                            <div
                              class="border d-flex radius-15 d-flex flex-shrink-0"
                              style="width: 80px; height: 80px;">
                              <img src="/images/background/logo.png"
                                class="img-fluid m-auto" alt="logo" />
                            </div>
                            <div
                              class="mx-3 d-flex flex-column justify-content-center gap-1"
                              style="height: 76px;">
                              <div class="text-black fs-6">{{ $fonder->Name }}
                              </div>
                              <div class="d-flex align-items-center gap-2">
                                <i class="ti ti-mail text-primary"></i>
                                <span
                                  class="text-muted text-xs line-clamp-1">{{ $fonder->Email }}</span>
                              </div>
                              <a href="{{ $fonder->LinkedIn }}"
                                class="d-flex align-items-center gap-2"
                                style="width: 200px;">
                                <img src="/icons/linkedin.svg" width="18"
                                  height="18" />
                                <span
                                  class="text-muted text-xs line-clamp-1">{{ $fonder->LinkedIn }}</span>
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          @endif
          @if (count($Shareholders) > 0)
            <div class="col-md-4">
              <div class="card shadow-sm"
                style="border-radius: 15px;">
                <div class="p-3">
                  <h5 class="card-title mb-3">{{ __('admin.shareholders') }}
                  </h5>
                  <div class="row">
                    @foreach ($Shareholders as $Shareholder)
                      <div class="col-12 mb-3">
                        <div class="p-3 shadow-sm border"
                          style="border-radius: 15px;">
                          <div
                            class="d-flex align-items-center justify-content-between">
                            <div
                              class="mx-3 d-flex flex-column justify-content-center gap-1"
                              style="height: 76px;">
                              <div class="text-black fs-6">
                                {{ $Shareholder->Name }}</div>
                              <button
                                class="btn btn-outline-primary btn-xs rounded-pill mt-1 fw-bold text-nowrap"
                                style="height: 22px; width: fit-content">
                                {{ $Shareholder->RoleShereholder }}
                              </button>
                            </div>
                            <div class="mx-3 d-flex align-items-center gap-3"
                              style="height: 76px;">
                              <i class="ti ti-chevrons-right ti-28px;"></i>
                              <!-- Circular Progress -->
                              <div
                                class="progress-circle p-3 position-relative d-flex align-items-center justify-content-center"
                                style="width: 54px; height: 54px;">
                                <span
                                  class="position-absolute text-black fw-bold"
                                  style="font-size: 13px;">{{ $Shareholder->PresentagShereholder }}%</span>
                                <svg class="position-absolute" width="54"
                                  height="54" viewBox="0 0 36 36">
                                  <path class="circle-bg"
                                    d="M18 2.0845
                                                      a 15.9155 15.9155 0 0 1 0 31.831
                                                      a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                                    stroke="#e6e6e6" stroke-width="2.5" />
                                  <path class="circle"
                                    d="M18 2.0845
                                                      a 15.9155 15.9155 0 0 1 0 31.831
                                                      a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none" stroke="#007bff"
                                    stroke-width="2.5"
                                    stroke-dasharray="{{ $Shareholder->PresentagShereholder }}, 100" />
                                </svg>
                              </div>
                              <!-- Circular Progress -->
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          @endif
          @if (count($Investors) > 0)
          <div class="col-md-4">
            <div class="card shadow-sm"
              style="border-radius: 15px;">
              <div class="p-3">
                <h5 class="card-title mb-3">{{ __('admin.investors') }}</h5>
                @foreach ($Investors as $Investor)
                  <div class="card shadow-sm mb-4 border"
                    style="border-radius: 15px;">
                    <div class="p-3">
                      <div class="d-flex align-items-center">
                        <div
                          class="border d-flex radius-15 d-flex flex-shrink-0"
                          style="width: 80px; height: 80px;">
                          <img src="/images/background/logo.png"
                            class="img-fluid m-auto" alt="logo" />
                        </div>
                        <div
                          class="mx-3 d-flex flex-column justify-content-center gap-1"
                          {{-- style="height: 76px;" --}}>
                          <div class="text-black fs-6">{{ $Investor->Name }}
                          </div>
                          <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-drone text-info"></i>
                            <button
                              class="btn btn-outline-primary btn-xs rounded-pill fw-bold"
                              style="height: 18px;">
                              {{ $Investor->investorType }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
          @endif
        </div>
      </section>
      <!-- Sustainable Development Icons -->
      @php
        // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
        //  $Sustainables = explode(',', $company->Sustainable_development);
        $Sustainables = json_decode(
            $company->Sustainable_development,
        );
      @endphp
      @if (!empty($Sustainables) && count($Sustainables) > 0)
        <section class="mt-6 row">
          <div class="col-12">
            <div class="card shadow-sm"
              style="border-radius: 15px; min-height: 290px;">
              <div class="p-4">
                <h5 class="">
                  {{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}
                </h5>
                <div class="row w-75">
                  @foreach ($Sustainables as $Sustainable)
                    @php
                      $icon = App\SustainableIcon::where(
                          'title',
                          $Sustainable,
                      )->first();
                    @endphp
                    <div class="col-md-2 mb-2">
                      <div class="border light-shadow radius-15" style="display: grid; place-items: center;">
                        @if ($icon->icon_ar)
                          <img
                            src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}"
                            alt="icon"
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
      <section class="mt-6">
        <div class="row">
          <div class="col-md-6">
            <div class="card shadow-sm"
              style="border-radius: 15px; height: 520px;">
              <div class="p-3 h-100">
                <h5 class="card-title">{{ __('admin.Pitch_Deck') }}:</h5>
                @if ($company->pitch_deck != null)
                  <div class="card-body text-center h-100">
                    <div
                      class="d-flex flex-column justify-content-between align-items-center h-100">
                      <iframe src="{{ url($company->pitch_deck) }}"
                        target="_blank"
                        style="width: 100%; height: 350px; object-fit:"
                        class="img-fluid">
                        <p>Your browser does not support iframes.</p>
                      </iframe>
                      <div class="mt-auto d-flex flex-column mb-6">
                        <span class="text-muted">To get analysis for your pitch
                          deck please</span>
                        <a href="{{ route('my_company') }}"
                          class="text-primary fw-medium text-decoration-underline mt-1">Click
                          here</a>
                      </div>
                    </div>
                  </div>
                @else
                  <div class="card-body text-center h-100">
                    <div
                      class="d-flex flex-column justify-content-between align-items-center h-100">
                      <img src="/images/deck.svg"
                        style="width: 350px; height: 350px;" />
                      <div class="mt-auto d-flex flex-column mb-6">
                        <span class="text-muted">To get analysis for your pitch
                          deck please</span>
                        <a href="{{ route('my_company') }}"
                          class="text-primary fw-medium text-decoration-underline mt-1">Click
                          here</a>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card shadow-sm"
              style="border-radius: 15px; height: 520px;">
              <div class="p-3 h-100">
                <h5 class="card-title">{{ __('admin.Pitch_Deck_Video') }}:</h5>
                @if ($company->pitch_deck_video != null)
                  <div
                    class="card-body text-center d-flex flex-column justify-content-between h-100">
                    <div
                      class="d-flex flex-column justify-content-between align-items-center h-100">
                      <video width="100%" controls>
                        <source src="{{ $company->pitch_deck_video }}"
                          type="video/mp4">
                        Your browser does not support the video tag.
                      </video>
                      <div class="mt-auto d-flex flex-column mb-6">
                        <span class="text-muted">
                          To get analysis for your pitch deck please upload your
                          pitch deck video
                        </span>
                        <a href="{{ route('my_company') }}"
                          class="text-primary fw-medium text-decoration-underline mt-1">Click
                          here</a>
                      </div>
                    </div>
                  </div>
                @else
                  <div
                    class="card-body text-center d-grid justify-content-between">
                    <div
                      class="d-flex flex-column justify-content-between align-items-center">
                      <img src="/images/deck-video.svg"
                        style="width: 350px; height: 350px;" />
                      <div class="mt-auto d-flex flex-column">
                        <span class="text-muted">
                          To get analysis for your pitch deck please upload your
                          pitch deck video
                        </span>
                        <a href="{{ route('my_company') }}"
                          class="text-primary fw-medium text-decoration-underline mt-1">Click
                          here</a>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="mt-6">
        <div class="row">
          @if ($company->pitch_deck_summary)
          <div class="col-md-6">
            <div class="card shadow-sm"
              style="border-radius: 15px; height: 300px; overflow-y:auto;">
              <div class="p-3">
                <h5 class="card-title">{{ __('admin.Pitch_Deck_Information') }}
                </h5>
                <div class="d-flex flex-column gap-2">
                  <div class="card shadow-sm"
                    style="height: 132px; border-radius: 15px;  overflow-y: auto;">
                    <div class="card-body">
                      <h6
                        class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
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
          </div>
          @endif

          @php
                  // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
              $documents = explode(',', $company->documents);
              // استخراج اسم كل ملف من الروابط
              $fileNames = array_map(function ($url) {
                  return basename($url); // استخراج اسم الملف من الرابط
              }, $documents);

          @endphp


        @if (!empty($document))
          <div class="col-md-6 mb-6">
            <div class="card shadow-sm"
              style="border-radius: 15px; height: 300px; overflow-y:auto;">
              <div class="p-3">
                
                <h5 class="card-title">{{ __('admin.Documents') }}</h5>
                <div class="d-flex flex-column gap-2">
                  @foreach ($documents as $index => $document)
                    @if (is_string($document))
                      <div class="card shadow-sm"
                        style="border-radius: 15px; max-height: 300px;  overflow-y: auto;">
                        <div class="card-body">
                          <div
                            class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                              <i class="ti ti-file fs-1 text-warning"></i>
                              <span>{{ $fileNames[$index] }}</span>
                            </div>
                            <a class="btn btn-transparent"
                              href="{{ $document }}" target="_blank"
                              download>
                              <i class="ti ti-download fs-3 text-primary"></i>
                            </a>
                          </div>
                        </div>
                      </div>
                    @endif
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif



        </div>
      </section>
      
      <section>
        <div class="row">
          @if (count($Achieved_Milestones) > 0)
            <div class="col-md-6">
              <div class="card shadow-sm"
                style="border-radius: 15px; height: 400px; overflow-y: auto;">
                <div class="p-3">
                  <h5 class="card-title">{{ __('admin.Achieved_Milestones') }}
                  </h5>
                  <div class="d-flex flex-column gap-2">
                    @foreach ($Achieved_Milestones as $key => $Achieved)
                      <div class="card shadow-sm"
                        style="height: 132px; border-radius: 15px;">
                        <div class="card-body">
                          <h6
                            class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                            <div style="width: 32px; height: 32px;"
                              class="rounded-circle bg-primary text-white d-flex 
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
            </div>
          @endif
          @if (count($Planned_Milestones) > 0)
            <div class="col-md-6">
              <div class="card shadow-sm"
                style="border-radius: 15px; height: 400px; overflow-y: auto;">
                <div class="p-3">
                  <h5 class="card-title">{{ __('admin.Planned_Milestones') }}
                  </h5>
                  <div class="d-flex flex-column gap-2">
                    @foreach ($Planned_Milestones as $key => $Planned)
                      <div class="card shadow-sm"
                        style="height: 132px; border-radius: 15px;">
                        <div class="card-body">
                          <h6
                            class="border-bottom d-flex gap-2 pb-2 d-flex align-items-center">
                            <div style="width: 32px; height: 32px;"
                              class="rounded-circle bg-primary text-white d-flex 
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
            </div>
          @endif
        </div>
      </section>
      <section class="mt-6 px-4">
        <div class="row">
          @if ($company->cycle)
            @role('Admin')
              <div class="card shadow-sm" style="border-radius: 15px;">
                <div class="p-3">
                  <h5 class="mb-4">{{ __('admin.Cycle informations') }}</h5>
                  <table class="table">
                    <tbody>
                      <tr>
                        <td class="width-200"> {{ __('admin.Cycle') }}:</td>
                        <td>{{ $company->cycledata->title }}</td>
                      </tr>
                      <tr>
                        <td>{{ __('admin.Current Step') }}:</td>
                        <td><b>{{ $company->stepdata->arr }}:</b>
                          {{ $company->stepdata->title }}</td>
                      </tr>
                      <tr>
                        <td>{{ __('admin.Screening result') }}:</td>
                        <td>
                          @php
                            $form = \App\Step::where(
                                'cycle_id',
                                $company->cycle,
                            )
                                ->where('stype', 1)
                                ->orderBy('arr')
                                ->first(['id']);
                            $weight = $company->companyWeight(
                                $company->id,
                                $form->id,
                                0,
                            );
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
                          @foreach ($judges as $ju)
                            ({{ $ju->id }})
                            {{ $ju->{'first_name_' . $l} }}
                            {{ $ju->{'last_name_' . $l} }},
                          @endforeach
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
                                  ? '<a target="_blank" href="' .
                                      $field->answer .
                                      '"> ' .
                                      __('admin.Download File') .
                                      ' </a>'
                                  : $field->answer !!}
                              {{-- @php(
                                $comments = \App\Formdata::where('field_id', $field->field_id)->where('company_id', $company->id)->first(['comments'])->comments,
                                )
                              @foreach ($comments as $key => $cc)
                                @if ($cc)
                                  <div class="warning">{{ __('admin.comment') }}({{ $key }}):
                                    {{ $cc }}</div>
                                @endif
                              @endforeach --}}
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
  <div class="modal fade text-left" id="MessageModal" tabindex="-1"
    role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"
      role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">
            {{ __('messages.SendMessageTo') }}
            {{ $company->user->{'first_name_' . $l} }}
            {{ $company->user->{'last_name_' . $l} }}
            ({{ $company->{'name_' . $l} }}).
          </h4>
        </div>
        <form method="post" action="/dashboard/send_message"
          enctype="multipart/form-data">
          {!! csrf_field() !!}
          <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
          <input type="hidden" name="receiver"
            value="{{ $company->user->id }}">
          <div class="modal-body">
            <div class="form-group mb-3">
              <label
                class="form-label fs-6 mb-1">{{ __('messages.Subject') }}:</label>
              <input name="subject" type="text"
                placeholder="{{ __('messages.Subject') }}"
                class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('messages.Message') }}:
              </label>
              <textarea class="form-control" id="message" name="message" rows="8"
                placeholder="{{ __('messages.Write your message') }}" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary"
              data-bs-dismiss="modal">
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
    var url = {{ $company->pitch_deck }}; // رابط ملف الـ PDF

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
