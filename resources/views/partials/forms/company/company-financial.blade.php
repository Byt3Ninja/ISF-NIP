@php
  $currenciesCollection = collect($Currencies);

  $selectedCurrency = $currenciesCollection->firstWhere(
      'currency_code',
      optional($client->company)->Currency ?? $Setting->Currency
  ) ?? $currenciesCollection->first();
@endphp

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="border-bottom pb-3 mb-4">
      <h5 class="card-title fw-semibold mb-0">{{ $tab['label'] }}</h5>
    </div>
    <form
      id="financial-form"
      method="post"
      action="{{ route('company.profile_update_mine', isset($client) && $client->company ? $client->company->id : auth()->user()->id) }}"
      enctype="multipart/form-data"
    >
      {!! csrf_field() !!}
      <input type="hidden" value="{{ \App\Enums\CompanyFormStep::FinancialInfo->value }}" name="CompanyN">

      {{-- Financial Overview --}}
      <div class="mb-4">
        <h6 class="text-uppercase text-muted fw-semibold small mb-3 letter-spacing-1">{{ __('admin.financial_overview') ?? 'Financial Overview' }}</h6>
        <div class="row">

          <div class="col-md-6 mb-3">
            <label for="Currency" class="form-label fw-medium">{{ __('settings.Currency') }}</label>
            <select class="form-select select2" name="Currency" id="Currency">
              <option value="">{{ __('settings.Currency') }}</option>
              @foreach ($Currencies as $Currency)
              <option value="{{ $Currency->currency_code }}"
                {{ $client->company && $client->company->Currency == $Currency->currency_code ? 'selected' : '' }}>
                {{ $Currency->currency_name }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="pre_money_valuation" class="form-label fw-medium">{{ __('admin.Pre_money_Valuation') }}</label>
            <input
              type="number"
              class="form-control"
              id="pre_money_valuation"
              placeholder="{{ __('admin.Pre_money_Valuation') }}"
              value="{{ $client->company ? $client->company->Pre_money_Valuation : old('pre_money_valuation') }}"
              name="pre_money_valuation"
              autocomplete="Pre_money_Valuation"
            >
          </div>

          <div class="col-md-6 mb-3">
            <label for="raised_fund" class="form-label fw-medium">
              {{ __('admin.raised_fund_egp') }}
              <span class="text-muted fw-normal">( {{ $selectedCurrency->currency_code ?? '' }} )</span>
            </label>
            <input
              type="number"
              class="form-control"
              id="raised_fund"
              placeholder="{{ __('admin.raised_fund_egp') }}"
              value="{{ $client->company ? $client->company->raised_fund : old('raised_fund') }}"
              name="raised_fund"
              autocomplete="raised_fund"
            >
          </div>

          <div class="col-md-6 mb-3">
            <label for="Funding_Target" class="form-label fw-medium">
              {{ __('admin.Funding_Target') }}
              <span class="text-muted fw-normal">( {{ $selectedCurrency->currency_code ?? '' }} )</span>
            </label>
            <input
              type="number"
              class="form-control"
              id="Funding_Target"
              placeholder="{{ __('admin.Funding_Target') }}"
              value="{{ $client->company ? $client->company->Funding_Target : old('Funding_Target') }}"
              name="Funding_Target"
              autocomplete="Funding_Target"
            >
          </div>

          {{-- Does your business generate revenue? --}}
          <div class="col-md-6 mb-3">
            <label for="generate_revenue" class="form-label fw-medium">
              {{ __('admin.generate_revenue') }}
            </label>
            <select class="form-select select2" required id="generate_revenue" name="generate_revenue">
              <option value="" disabled>{{ __('admin.generate_revenue') }}</option>
              <option value="yes"
                {{ !empty($client->company) && $client->company->generate_revenue === 'yes' ? 'selected' : '' }}>
                {{ __('admin.Yes') }}
              </option>
              <option value="no"
                {{ !empty($client->company) && $client->company->generate_revenue === 'no' ? 'selected' : '' }}>
                {{ __('admin.No') }}
              </option>
            </select>
          </div>

          {{-- Average Annual Revenues (shown only when generate_revenue = yes) --}}
          <div
            class="col-md-6 mb-3"
            id="average_annual_revenues_wrapper"
            style="{{ !empty($client->company) && $client->company->generate_revenue === 'yes' ? '' : 'display:none;' }}"
          >
            <label for="average_annual_revenues" class="form-label fw-medium">
              {{ __('admin.average_annual_revenues') }}
              <span class="text-muted fw-normal">( {{ $selectedCurrency->currency_code ?? '' }} )</span>
            </label>
            <input
              type="number"
              class="form-control"
              id="average_annual_revenues"
              placeholder="{{ __('admin.average_annual_revenues') }}"
              value="{{ $client->company ? $client->company->average_annual_revenues : old('average_annual_revenues') }}"
              name="average_annual_revenues"
              min="0"
              step="1"
              inputmode="numeric"
              oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)"
              autocomplete="average_annual_revenues"
            >
          </div>

        </div>
      </div>

      <hr class="my-4">

      {{-- Business Profile --}}
      <div class="mb-4">
        <h6 class="text-uppercase text-muted fw-semibold small mb-3">{{ __('admin.business_profile') ?? 'Business Profile' }}</h6>
        <div class="row">

          <div class="col-md-6 mb-3">
            <label for="stage" class="form-label fw-medium">{{ __('admin.development_stage') }}</label>
            @php
              $hasSelectedStage = !empty($client->company) && $Stages->contains('id', $client->company->stage);
            @endphp
            <select class="form-select select2" required id="stage" name="stage">
              <option value="" disabled {{ !$hasSelectedStage ? 'selected' : '' }}>{{ __('admin.development_stage') }}</option>
              @foreach ($Stages as $Stage)
              <option value="{{ $Stage->id }}"
                {{ !empty($client->company) && $client->company->stage == $Stage->id ? 'selected' : '' }}>
                {{ $Stage->{'name_' . $l} }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="development_stage" class="form-label fw-medium">{{ __('admin.Funding_Stage') }}</label>
            <select class="form-select select2" required id="development_stage" name="development_stage">
              <option value="" disabled>{{ __('admin.Funding_Stage') }}</option>
              <option value="Pre_seed_funding_stage" {{ !empty($client->company) && $client->company->development_stage == 'Pre_seed_funding_stage' ? 'selected' : '' }}>
                {{ __('admin.Pre_seed_funding_stage') }}
              </option>
              <option value="Seed_funding_stage" {{ !empty($client->company) && $client->company->development_stage == 'Seed_funding_stage' ? 'selected' : '' }}>
                {{ __('admin.Seed_funding_stage') }}
              </option>
              <option value="Series_A_funding" {{ !empty($client->company) && $client->company->development_stage == 'Series_A_funding' ? 'selected' : '' }}>
                {{ __('admin.Series_A_funding') }}
              </option>
              <option value="Series_B_funding" {{ !empty($client->company) && $client->company->development_stage == 'Series_B_funding' ? 'selected' : '' }}>
                {{ __('admin.Series_B_funding') }}
              </option>
              <option value="Series_C_funding" {{ !empty($client->company) && $client->company->development_stage == 'Series_C_funding' ? 'selected' : '' }}>
                {{ __('admin.Series_C_funding') }}
              </option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="business_type" class="form-label fw-medium">{{ __('admin.Business Type') }}</label>
            <select class="form-select select2" required id="business_type" name="business_type">
              <option value="">{{ __('admin.Business Type') }}</option>
              @foreach (['B2C', 'B2B', 'B2B2C', 'C2C', 'B2G', 'Platform'] as $type)
              <option value="{{ $type }}"
                {{ !empty($client->company) && $client->company->business_type == $type ? 'selected' : '' }}>
                {{ $type }}
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="targeted_market" class="form-label fw-medium">{{ __('admin.Targeted_Market') }}</label>
            <select class="form-select select2" id="targeted_market" name="targeted_market[]" multiple autocomplete="targeted_market">
              @foreach($countries as $country)
              <option value="{{ $country->id }}"
                @if($client->company)
                  @if($client->company->targeted_market != null)
                    {{ in_array($country->id, json_decode($client->company->targeted_market ?? '[]')) ? 'selected' : '' }}
                  @endif
                @endif
              >
                {{ $country->emoji . ' ' . $country->{'name_' . $l} }}
              </option>
              @endforeach
            </select>
          </div>

        </div>
      </div>

      <hr class="my-4">

      {{-- Financial Plan --}}
      <div class="mb-4">
        <h6 class="text-uppercase text-muted fw-semibold small mb-3">{{ __('admin.financial_plan') }}</h6>
        <div class="row">

          <div class="col-12 mb-3">
            <label for="financial_plan_description" class="form-label fw-medium">{{ __('admin.financial_plan_description') }}</label>
            <textarea
              oninput="this.value = this.value.trimStart()"
              class="form-control"
              id="financial_plan_description"
              rows="4"
              placeholder="{{ __('admin.financial_plan_description') }}"
              name="financial_plan_description"
              autocomplete="financial_plan_description"
              maxlength="255"
            >{{ $client->company ? $client->company->financial_plan_description : old('financial_plan_description') }}</textarea>
          </div>

          <div class="col-12 mb-3">
            <label for="financial_plan" class="form-label fw-medium">{{ __('admin.Financial plan attachment') }}</label>
            <input
              id="financial_plan"
              class="form-control @error('financial_plan') is-invalid @enderror"
              type="file"
              name="financial_plan"
              accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
            @error('financial_plan')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          @if(optional($client->company)->viewerFileUrl('financial_plan'))
          <div class="col-12 mb-3">
            <a
              href="{{ $client->company->viewerFileUrl('financial_plan') }}"
              target="_blank"
              download
              class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
              <i class="ti ti-file text-warning"></i>
              {{ basename($client->company->financial_plan) }}
              <i class="ti ti-download"></i>
            </a>
          </div>
          @endif

        </div>
      </div>

      <div class="d-flex justify-content-end pt-2 border-top">
        <button type="submit" class="btn btn-primary px-4" id="submit-button-financial">
          <span id="button-text-financial">{{ __('admin.Save') }}</span>
          <span id="button-spinner-financial" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>

    </form>
  </div>
</div>
