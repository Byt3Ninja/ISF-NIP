@php
  $action = Auth::user()->hasanyRole('Admin|organizationManager')
      ? route('company.profile_update_mine', $client->company->id)
      : route('company.profile_update_mine');

  $sustainable = $client->company && $client->company->Sustainable_development
      ? json_decode($client->company->Sustainable_development, true)
      : [];

  $sdgOptions = [
      'no_poverty', 'zero_hunger', 'good_health', 'quality_education',
      'gender_equality', 'clean_water', 'clean_energy', 'decent_work',
      'industry_innovation', 'reduced_inequalities', 'sustainable_cities',
      'responsible_consumption', 'climate_action', 'life_below_water',
      'life_on_land', 'peace_justice', 'partnership_goals'
  ];
@endphp

<div class="card">
  <div class="card-body">
    <form id="desc-form" method="post" action="{{ $action }}" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <input type="hidden" name="CompanyN" value="3">

      {{-- Header --}}
      <div class="border-bottom mb-4">
        <h5 class="card-title">{{ $tab['label'] }}</h5>
      </div>

      {{-- About Company --}}
      <div class="mb-4">
        <label class="form-label fw-semibold">
          {{ __('admin.About_the_Company') }}
        </label>
        <textarea
          class="form-control"
          name="company_description"
          rows="5"
          maxlength="500"
          placeholder="{{ __('admin.About_the_Company') }}"
          oninput="this.value = this.value.trimStart()"
        >{{ optional($client->company)->info_company }}</textarea>
      </div>

      {{-- Sustainable Development --}}
      <div class="mb-4">
        <label class="form-label fw-semibold">
          {{ __('admin.sustainable_development') }}
        </label>

        <select
          name="Sustainable_development[]"
          id="sustainable-mutiple-select"
          class="form-select select2"
          data-placeholder="{{ __('admin.Select') }}"
          
          data-allow-clear="true"
          multiple
        >
          @foreach($sdgOptions as $option)
            <option value="{{ $option }}"
              {{ in_array($option, $sustainable) ? 'selected' : '' }}>
              {{ __('admin.' . $option) }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- About Problem Section --}}
      <div class="border-bottom my-4">
        <h5 class="card-title">{{ __('admin.About_Problem') }}</h5>
      </div>

      <div class="row g-4">

        @php
          $fields = [
            'about_problem',
            'about_market_opportunity',
            'about_competitors',
            'about_monetize',
            'about_challenges'
          ];
        @endphp

        @foreach($fields as $field)
          <div class="col-md-6">
            <label class="form-label fw-semibold">
              {{ __('admin.' . $field) }} *
            </label>
            <textarea
              class="form-control"
              name="{{ $field }}"
              rows="4"
              placeholder="{{ __('admin.' . $field) }} *"
              oninput="this.value = this.value.trimStart()"
            >{{ optional($client->company)->$field ?? old($field) }}</textarea>
          </div>
        @endforeach

      </div>

      {{-- Products / Services --}}
      <div class="mt-4">
        <label class="form-label fw-semibold">
          {{ __('admin.product_service_description') }}
        </label>

        <textarea
          class="form-control"
          id="products_services_description"
          name="products_services_description"
          rows="4"
          maxlength="1000"
          placeholder="{{ __('admin.product_service_description') }}"
        >{{ old('products_services_description', optional($client->company)->products_services_description) }}</textarea>

        <div class="text-end mt-1">
          <small class="text-muted" id="products_services_description_count">
            {{ strlen(optional($client->company)->products_services_description ?? '') }}/1000
          </small>
        </div>
      </div>

      {{-- Submit --}}
      <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary" id="submit-button-desc">
          <span class="me-2" id="button-text-desc">{{ __('admin.Save') }}</span>
          <span id="button-spinner-desc" class="spinner-border spinner-border-sm d-none"></span>
        </button>
      </div>

    </form>
  </div>
</div>