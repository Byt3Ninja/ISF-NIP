<div class="card card-body mb-6">
  <div class="w-100 d-flex flex-column flex-md-row" style="gap: 2rem;">
    <div class="section-3">
      <div class="row">
        <div class="col-12 col-md-4 mb-4">
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
        <div class="col-12 col-md-4 mb-4">
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
        <div class="col-12 col-md-4 mb-4">
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
        <div class="col-12 col-md-4 mb-4">
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
        <div class="col-12 col-md-4 mb-4">
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
        <div class="col-12 col-md-4 mb-4">
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
  </div>
</div>

@include('admin.clients.startup.partials._startup_badges')
{{-- @include('admin.clients.startup.partials._working_team') --}}