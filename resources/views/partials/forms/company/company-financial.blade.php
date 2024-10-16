<form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data">
  {!! csrf_field() !!}
  <input type="hidden" value="4" name="CompanyN">

  <div class="row">
    <div class="col-12 mb-3">
      <label for="Currency" class="form-label">{{ __('settings.Currency') }}</label>
      <div class="position-relative">
        <select class="form-select border-light light-shadow px-8" name="Currency" id="Currency">
          <option value="">{{ __('settings.Currency') }}</option>
          @foreach ($Currencies as $Currency)
            <option value="{{ $Currency->currency_code }}" {{ $client->company && $client->company->Currency == $Currency->currency_code ? 'selected' : '' }}>
              {{ $Currency->currency_name }}
            </option>
          @endforeach
        </select>
        <i class="ti ti-currency-dollar fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
      </div>
    </div>

    <div class="col-12 mb-3">
      <label for="pre_money_valuation" class="form-label">{{ __('admin.Pre_money_Valuation') }}</label>
      <div class="position-relative">
        <input type="number" class="form-control border-light light-shadow px-8" id="pre_money_valuation" placeholder="{{ __('admin.Pre_money_Valuation') }}"
          value="{{ $client->company ? $client->company->pre_money_valuation : old('pre_money_valuation') }}" name="pre_money_valuation" autocomplete="pre_money_valuation">
        <i class="ti ti-cash fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
      </div>
    </div>

    <div class="col-12 mb-3">
      <label for="raised_fund" class="form-label">{{ __('admin.raised_fund_egp') }}{{ $Setting->Currency }}</label>
      <div class="position-relative">
        <input type="number" class="form-control border-light light-shadow px-8" id="raised_fund" placeholder="{{ __('admin.raised_fund_egp') }}"
          value="{{ $client->company ? $client->company->raised_fund : old('raised_fund') }}" name="raised_fund" autocomplete="raised_fund">
        <i class="ti ti-trending-up fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
      </div>
    </div>

    <div class="col-12 mb-3">
      <label for="stage" class="form-label">{{ __('admin.stage') }}*</label>
      <div class="position-relative">
        <select class="form-select border-light light-shadow px-8" required id="stage" name="stage">
          <option value="">{{ __('admin.stage') }}</option>
          <option value="idea" {{ !empty($client->company) && $client->company->stage == 'idea' ? 'selected' : '' }}>
            {{ __('admin.stage_idea') }}
          </option>
          <option value="prototype" {{ !empty($client->company) && $client->company->stage == 'prototype' ? 'selected' : '' }}>
            {{ __('admin.stage_prototype') }}
          </option>
          <option value="ready_product" {{ !empty($client->company) && $client->company->stage == 'ready_product' ? 'selected' : '' }}>
            {{ __('admin.stage_ready_product') }}
          </option>
          <option value="company_in_market" {{ !empty($client->company) && $client->company->stage == 'company_in_market' ? 'selected' : '' }}>
            {{ __('admin.stage_company_in_market') }}
          </option>
        </select>
        <i class="ti ti-flag fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
      </div>
    </div>

    <div class="col-12 mb-3">
      <label for="targeted_market" class="form-label">{{ __('admin.Targeted_Market') }}</label>
      <div class="position-relative">
        <input type="number" class="form-control border-light light-shadow px-8" id="targeted_market" placeholder="{{ __('admin.Targeted_Market') }}"
          value="{{ $client->company ? $client->company->targeted_market : old('Targeted Market') }}" name="targeted_market" autocomplete="targeted_market">
        <i class="ti ti-target fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
      </div>
    </div>

    <div class="col-12 mb-3">
      <label for="business_type" class="form-label">{{ __('admin.Business Type') }}*</label>
      <div class="position-relative">
        <select class="form-select border-light light-shadow px-8" required id="business_type" name="business_type">
          <option value="">{{ __('admin.Business Type') }}</option>
          <option value="B2C" {{ !empty($client->company) && $client->company->business_type == 'B2C' ? 'selected' : '' }}>B2C</option>
          <option value="B2B" {{ !empty($client->company) && $client->company->business_type == 'B2B' ? 'selected' : '' }}>B2B</option>
          <option value="B2B2C" {{ !empty($client->company) && $client->company->business_type == 'B2B2C' ? 'selected' : '' }}>B2B2C</option>
          <option value="C2C" {{ !empty($client->company) && $client->company->business_type == 'C2C' ? 'selected' : '' }}>C2C</option>
          <option value="B2G" {{ !empty($client->company) && $client->company->business_type == 'B2G' ? 'selected' : '' }}>B2G</option>
          <option value="Platform" {{ !empty($client->company) && $client->company->business_type == 'Platform' ? 'selected' : '' }}>Platform</option>
        </select>
        <i class="ti ti-briefcase fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-primary" id="submit-button">
        <span id="button-text">{{ __('admin.Update') }}</span>
        <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
      </button>
    </div>
  </div>
</form>