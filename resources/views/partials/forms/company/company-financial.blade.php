<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title">{{ $tab['label'] }}</h5>
    </div>
    <form method="post" action="{{ route('update_my_company') }}"
      enctype="multipart/form-data">
      {!! csrf_field() !!}
      <input type="hidden" value="4" name="CompanyN">

      <div class="row">
        <div class="col-6 mb-3">
          <label for="Currency"
            class="form-label">{{ __('settings.Currency') }}</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10"
              name="Currency" id="Currency">
              <option value="">{{ __('settings.Currency') }}</option>
              @foreach ($Currencies as $Currency)
                <option value="{{ $Currency->currency_code }}"
                  {{ $client->company && $client->company->Currency == $Currency->currency_code ? 'selected' : '' }}>
                  {{ $Currency->currency_name }}
                </option>
              @endforeach
            </select>
            <i class="ti ti-currency-dollar fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        <div class="col-6 mb-3">
          <label for="pre_money_valuation"
            class="form-label">{{ __('admin.Pre_money_Valuation') }}</label>
          <div class="position-relative">
            <input type="number"
              class="form-control border-light light-shadow px-10"
              id="pre_money_valuation"
              placeholder="{{ __('admin.Pre_money_Valuation') }}"
              value="{{ $client->company ? $client->company->Pre_money_Valuation : old('pre_money_valuation') }}"
              name="pre_money_valuation" autocomplete="Pre_money_Valuation">
            <i class="ti ti-cash fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        <div class="col-6 mb-3">
          <label for="raised_fund"
            class="form-label">{{ __('admin.raised_fund_egp') }} {{ $Setting->Currency }}</label>
          <div class="position-relative">
            <input type="number"
              class="form-control border-light light-shadow px-10"
              id="raised_fund" placeholder="{{ __('admin.raised_fund_egp') }}"
              value="{{ $client->company ? $client->company->raised_fund : old('raised_fund') }}"
              name="raised_fund" autocomplete="raised_fund">
            <i class="ti ti-trending-up fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        

        <div class="col-6 mb-3">
          <label for="Funding_Target"
            class="form-label">{{ __('admin.Funding_Target') }}{{ $Setting->Currency }}</label>
          <div class="position-relative">
            <input type="number"
              class="form-control border-light light-shadow px-10"
              id="Funding_Target" placeholder="{{ __('admin.Funding_Target') }}"
              value="{{ $client->company ? $client->company->Funding_Target : old('Funding_Target') }}"
              name="Funding_Target" autocomplete="Funding_Target">
            <i class="ti ti-trending-up fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        <div class="col-6 mb-3">
          <label for="stage"
            class="form-label">{{ __('admin.development_stage') }}*</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10" required
              id="stage" name="stage">
              <option value="">{{ __('admin.stage') }}</option>

              @foreach ($Stages as $Stage)
              <option value="{{ $Stage->id }}"
              {{ !empty($client->company) && $client->company->stage == $Stage->id ? 'selected' : '' }}>
              {{ $Stage->{'name_' . $l} }}
            </option>
              @endforeach
              
            </select>
            <i class="ti ti-flag fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>


        <div class="col-6 mb-3">
          <label for="development_stage"
            class="form-label">{{ __('admin.Funding_Stage') }}*</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10" required
              id="development_stage" name="development_stage">
              <option value="" disabled>{{ __('admin.development_stage') }}</option>
              <option value="Pre_seed_funding_stage"
                {{ !empty($client->company) && $client->company->development_stage == 'Pre_seed_funding_stage' ? 'selected' : '' }}>
                {{ __('admin.Pre_seed_funding_stage') }}
              </option>
              <option value="Seed_funding_stage "
                {{ !empty($client->company) && $client->company->development_stage == 'Seed_funding_stage' ? 'selected' : '' }}>
                {{ __('admin.Seed_funding_stage') }}
              </option>
              <option value="Series_A_funding"
                {{ !empty($client->company) && $client->company->development_stage == 'Series_A_funding' ? 'selected' : '' }}>
                {{ __('admin.Series_A_funding') }}
              </option>
              <option value="Series_B_funding"
                {{ !empty($client->company) && $client->company->development_stage == 'Series_B_funding' ? 'selected' : '' }}>
                {{ __('admin.Series_B_funding') }}
              </option>
              <option value="Series_C_funding"
                {{ !empty($client->company) && $client->company->development_stage == 'Series_C_funding' ? 'selected' : '' }}>
                {{ __('admin.Series_C_funding') }}
              </option>
            </select>
            <i class="ti ti-flag fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        <div class="col-6 mb-3">
          <label for="targeted_market"
            class="form-label">{{ __('admin.Targeted_Market') }}</label>
            <div class="position-relative">
              <select
                class="form-select select2 border-light light-shadow px-10"
                id="targeted_market"
                name="targeted_market[]"
                multiple
                autocomplete="targeted_market">
                @foreach($countries as $country)
                <option value="{{$country->id}}" @if($client->company) @if ($client->company->targeted_market !=null)
                 {{ in_array($country->id, json_decode($client->company ? $client->company->targeted_market : old('Targeted Market', '[]'))) ? 'selected' : '' }} @endif @endif> {{$country->emoji . ' ' . $country->{'name_'.$l} }} </option>
            @endforeach
                <!-- Add more options as needed -->
              </select>
              <i class="ti ti-target fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="right: 7px;"></i>
            </div>
        </div>

        <div class="col-6 mb-3">
          <label for="business_type"
            class="form-label">{{ __('admin.Business Type') }}*</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10" required
              id="business_type" name="business_type">
              <option value="">{{ __('admin.Business Type') }}</option>
              <option value="B2C"
                {{ !empty($client->company) && $client->company->business_type == 'B2C' ? 'selected' : '' }}>
                B2C</option>
              <option value="B2B"
                {{ !empty($client->company) && $client->company->business_type == 'B2B' ? 'selected' : '' }}>
                B2B</option>
              <option value="B2B2C"
                {{ !empty($client->company) && $client->company->business_type == 'B2B2C' ? 'selected' : '' }}>
                B2B2C</option>
              <option value="C2C"
                {{ !empty($client->company) && $client->company->business_type == 'C2C' ? 'selected' : '' }}>
                C2C</option>
              <option value="B2G"
                {{ !empty($client->company) && $client->company->business_type == 'B2G' ? 'selected' : '' }}>
                B2G</option>
              <option value="Platform"
                {{ !empty($client->company) && $client->company->business_type == 'Platform' ? 'selected' : '' }}>
                Platform</option>
            </select>
            <i class="ti ti-briefcase fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        <hr>

        <div class="col-md-6 col-12 mb-6">
          <label for="financial_plan" class="form-label">{{ __('admin.financial_plan') }}
            *</label>
          <div class="position-relative">
              <div class="mt-3" style="display: grid; place-items: center;">
                  <input id="financial_plan" readonly
                    class="form-control border-light light-shadow px-8 "
                    type="text" name="financial_plan" value="">
                  <a data-input="financial_plan" data-preview="financial_plan"
                    href="#"
                    class="lfm btn btn-outline-primary text-black fw-bold rounded-pill">
                    <i
                      class="ti ti-plus me-3"></i>{{ __('admin.Add new document') }}
                  </a>
                </div>
          </div>
        </div>

        


        <div class="col-md-6 col-12 mb-6">
          <label for="financial_plan_description"
            class="form-label">{{ __('admin.financial_plan_description') }}*</label>
          <div class="position-relative">
              <textarea class="form-control border-light light-shadow px-10"
              id="financial_plan_description" placeholder="{{ __('admin.financial_plan_description') }}*" name="financial_plan_description" required autocomplete="financial_plan_description">{{ $client->company ? $client->company->financial_plan_description : old('financial_plan_description') }}</textarea>
            <i class="ti ti-chart-bar fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>



       





        <div class="col-12 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary" id="submit-button">
            <span id="button-text">{{ __('admin.Update') }}</span>
            <span id="button-spinner"
              class="spinner-border spinner-border-sm d-none" role="status"
              aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
