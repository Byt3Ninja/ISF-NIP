@php
  $categories = array_keys(App\Company::static_categories());
  $selectedCategories = old('business_categories', $client->company->business_categories ?? []);
  if (is_string($selectedCategories)) { $selectedCategories = json_decode($selectedCategories, true) ?? []; }

  // Find "Other" / "أخرى" and get the custom value that follows it
  $otherValue = '';
  $otherIndexEn = array_search('Other', $selectedCategories);
  $otherIndexAr = array_search('أخرى', $selectedCategories);

  if ($otherIndexEn !== false && isset($selectedCategories[$otherIndexEn + 1])) {
      $otherValue = $selectedCategories[$otherIndexEn + 1];
  } elseif ($otherIndexAr !== false && isset($selectedCategories[$otherIndexAr + 1])) {
      $otherValue = $selectedCategories[$otherIndexAr + 1];
  }
@endphp

<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title fw-semibold">{{ $tab['label'] }}</h5>
    </div>

    @if ($_user->hasAnyRole('Admin|organizationManager'))
      <form id="edit-info-form" method="post" action="{{ route('company.profile_update_mine', $client->company->id) }}" enctype="multipart/form-data">
    @else
      <form id="edit-info-form" method="post" action="{{ route('company.profile_update_mine') }}" enctype="multipart/form-data">
    @endif
      {!! csrf_field() !!}
      <input name="CompanyN" type="hidden" value="{{ \App\Enums\CompanyFormStep::BasicInfo->value }}">
      <input name="locale" type="hidden" value="{{ app()->getLocale() }}">

      <div class="row">

        {{-- ── Company Name ── --}}
        <div class="col-12 mb-3">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="name_en">{{ __('admin.Company_Name') }} *</label>
            <input class="form-control" id="name_en" name="name_en" type="text"
              value="{{ $client->company ? $client->company->name_en : old('name_en') }}"
              style="direction: ltr;"
              placeholder="{{ __('admin.English Name') }} *"
              required autocomplete="name_en">
          </div>
        </div>

        @php
          $stateID = $client->company ? optional($client->company)->state_id : 0;
        @endphp

        {{-- ── Country ── --}}
        <div class="col-md-6 mb-3">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="country_id">{{ __('admin.Country') }} *</label>
            <select class="form-select select2" id="country_id" name="country_id" required>
              <option value="">{{ __('admin.Country') }}</option>
              @foreach ($countries as $country)
                <option value="{{ $country->id }}" {{ $client->company && $country->id == $client->company->country_id ? 'selected' : '' }}>
                  {{ $country->emoji . ' ' . $country->{'name_' . $l} }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ── Governorate / State (renamed) ── --}}
        <div class="col-md-6 mb-6">
          <label class="form-label fs-6 fw-semibold mb-2" for="state_id">{{ __('admin.Governorate_State') }} *</label>
          <select class="form-select select2" id="state_id" name="state_id" required
            data-placeholder="{{ __('admin.Governorate_State') }}"
            onchange="this.classList.remove('is-invalid');
              const stateSelect = document.getElementById('state_id');
              const stateSelectParent = stateSelect.parentElement;
              const invalidFeedback = stateSelectParent.nextElementSibling;
              if (invalidFeedback) invalidFeedback.innerHTML = '';
            "
          >
            <option value="">{{ __('admin.Governorate_State') }}</option>
            @foreach ($states as $state)
              <option value="{{ $state->id }}" {{ $state->id == $stateID ? 'selected' : '' }}>
                {{ $state->{'name_' . $l} }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- ── Business Categories ── --}}
        <div class="mb-3">
          <label class="form-label fs-6 fw-semibold mb-2" for="categories">{{ __('admin.Categories') }} *</label>
          <div class="row">
            @foreach ($categories as $category)
              <div class="col-md-4 col-6">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    id="category_{{ $loop->index }}"
                    name="business_categories[]"
                    type="checkbox"
                    value="{{ $category }}"
                    {{ in_array($category, $selectedCategories) ? 'checked' : '' }}
                  >
                  <label class="form-check-label" for="category_{{ $loop->index }}">
                    {{ $category }}
                  </label>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- ── Other category text input ── --}}
        <div class="mb-3">
          <div class="mt-3" id="otherCategoryInput" style="display: none;">
            <label class="form-label fs-6 fw-semibold mb-2" for="other_category">{{ __('admin.other? Please Specify') }}</label>
            <div class="input-group input-group-merge">
              <input class="form-control" id="other_category" name="other_category" type="text"
                value="{{ old('other_category', $otherValue) }}"
                placeholder="{{ __('admin.other? Please Specify') }}">
            </div>
          </div>
        </div>

        {{-- ── English Address ── --}}
        <div class="col-md-12 col-12 mb-4">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="address_en">{{ __('admin.English Address') }} *</label>
            <textarea class="form-control" id="address_en" name="address_en"
              style="direction: ltr;" required
              placeholder="{{ __('admin.English Address') }} *">{{ $client->company ? $client->company->address_en : old('address_en') }}</textarea>
          </div>
        </div>

        {{-- ── Team Size ── --}}
        <div class="col-md-6 col-12 mb-4">
          <div class="form-group w-100">
            <label class="form-label fw-semibold fs-6 mb-2" for="team_num">{{ __('startup.startup_size') }}</label>
            <select class="form-select select2" id="team_num" name="team_num">
              <option value="" disabled>{{ __('startup.startup_size') }}</option>
              @foreach (range(1, 9) as $i)
                <option value="{{ $i }}" {{ $client->company && $client->company->team_num == $i ? 'selected' : '' }}>
                  {{ __('startup.startup_size_' . $i) }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ── Establishment Date ── --}}
        <div class="col-md-6 col-12 mb-4">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="est_date">{{ __('admin.est_date') }} *</label>
            <input class="form-control" id="est-date" name="est_date" type="text"
              value="{{ $client->company ? $client->company->est_date : old('est_date') }}"
              placeholder="YYYY-MM-DD" />
          </div>
        </div>

        {{-- ── Registered Company ── --}}
        <div class="col-md-12 col-12 mb-4">
          <label class="form-label fs-6 fw-semibold mb-2" for="CreatedTax">{{ __('admin.Have_Rgister_Company') }} *</label>
          <select class="form-select CreatedTax" id="CreatedTax" name="CreatedTax" required>
            <option value="0" {{ $client->company && $client->company->CreatedTax == '0' ? 'selected' : '' }}>
              {{ __('admin.No') }}
            </option>
            <option value="1" {{ $client->company && $client->company->CreatedTax == '1' ? 'selected' : '' }}>
              {{ __('admin.Yes') }}
            </option>
          </select>
        </div>

        <div class="row ShowCreatedTax mb-3" style="{{ $client->company && $client->company->CreatedTax == '1' ? '' : 'display: none;' }}">

          {{-- Country of Registration --}}
          <div class="col-12 mb-4">
            <label class="form-label fs-6 fw-semibold mb-2" for="Country_Register">{{ __('admin.Country_Register') }}</label>
            <select class="form-select select2 Country_Register" id="Country_Register" name="Country_Register"
              data-placeholder="{{ __('admin.Country') }}">
              <option value="">{{ __('admin.Country') }}</option>
              @foreach ($countries as $country)
                <option value="{{ $country->id }}" {{ $client->company && $country->id == $client->company->Country_Register_id ? 'selected' : '' }}>
                  {{ $country->emoji . ' ' . $country->{'name_' . $l} }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Commercial Register --}}
          <div class="col-12 mb-4">
            <label class="form-label fs-6 fw-semibold mb-2" for="Commercial_Register">
              {{ __('admin.Commercial_Register') }} *
            </label>
            <input class="form-control Commercial_Register" id="Commercial_Register" name="Commercial_Register" type="file"
              placeholder="{{ __('admin.Commercial_Register') }} *" />
            <input type="hidden" name="Commercial_Register_value" value="{{ $client->company ? $client->company->Commercial_Register : '' }}">

            @if ($client->company && $client->company->viewerFileUrl('Commercial_Register'))
              <div class="mt-2 d-flex align-items-center gap-2">
                @php
                  $fileUrl   = $client->company->viewerFileUrl('Commercial_Register');
                  $extension = strtolower(pathinfo($client->company->Commercial_Register, PATHINFO_EXTENSION));
                @endphp
                @if (in_array($extension, ['jpg','jpeg','png']))
                  <a href="{{ $fileUrl }}" target="_blank"><i class="ti ti-photo text-secondary fs-5"></i></a>
                @elseif ($extension === 'pdf')
                  <a href="{{ $fileUrl }}" target="_blank" class="d-flex align-items-center"><i class="ti ti-file-type-pdf text-danger fs-5"></i></a>
                @else
                  <a href="{{ $fileUrl }}" target="_blank" class="d-flex align-items-center"><i class="ti ti-file text-secondary fs-5"></i></a>
                @endif
                <a class="badge bg-label-secondary" href="{{ $fileUrl }}" target="_blank">{{ basename($client->company->Commercial_Register) }}</a>
              </div>
            @endif
          </div>

          {{-- Tax Card --}}
          <div class="col-12 mb-4">
            <label class="form-label fs-6 fw-semibold mb-2" for="Tax_Card">{{ __('admin.Tax_Card') }} *</label>
            <input class="form-control Tax_Card" id="Tax_Card" name="Tax_Card" type="file"
              placeholder="{{ __('admin.Tax_Card') }} *" />
            <input type="hidden" name="Tax_Card_value" value="{{ $client->company ? $client->company->Tax_Card : '' }}">

            @if ($client->company && $client->company->viewerFileUrl('Tax_Card'))
              <div class="mt-2 d-flex align-items-center gap-2">
                @php
                  $fileUrl   = $client->company->viewerFileUrl('Tax_Card');
                  $extension = strtolower(pathinfo($client->company->Tax_Card, PATHINFO_EXTENSION));
                @endphp
                @if (in_array($extension, ['jpg','jpeg','png']))
                  <a href="{{ $fileUrl }}" target="_blank"><i class="ti ti-photo text-secondary fs-5"></i></a>
                @elseif ($extension === 'pdf')
                  <a href="{{ $fileUrl }}" target="_blank" class="d-flex align-items-center"><i class="ti ti-file-type-pdf text-danger fs-5"></i></a>
                @else
                  <a href="{{ $fileUrl }}" target="_blank" class="d-flex align-items-center"><i class="ti ti-file text-secondary fs-5"></i></a>
                @endif
                <a class="badge bg-label-secondary" href="{{ $fileUrl }}" target="_blank">{{ basename($client->company->Tax_Card) }}</a>
              </div>
            @endif
          </div>
        </div>

        {{-- ── Years of Operations ── --}}
        <div class="col-md-6 col-12 mb-4">
          <div class="form-group w-100">
            <label class="form-label fs-6 fw-semibold mb-2" for="years_of_operations">{{ __('admin.Years of Operations') }} *</label>
            <select class="form-select select2" id="years_of_operations" name="years_of_operations" required
              data-placeholder="{{ __('admin.Select Years of Operations') }}">
              <option value="">{{ __('admin.Select Years of Operations') }}</option>
              @foreach (['0-2','2-5','5-10','10+'] as $range)
                <option value="{{ $range }}" {{ optional($client->company)->years_of_operations === $range ? 'selected' : '' }}>{{ $range }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ── Product or Service ── --}}
        <div class="col-md-6 col-12 mb-4">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="product_or_service">{{ __('admin.Product or Service') }} *</label>
            <select class="form-select select2" id="product_or_service" name="product_or_service"
              data-placeholder="{{ __('admin.Select Product or Service') }}" required>
              <option value="">{{ __('admin.Select Product or Service') }}</option>
              @foreach (['Product','Service','Product + Service'] as $opt)
                <option value="{{ $opt }}" {{ optional($client->company)->product_or_service === $opt ? 'selected' : '' }}>
                  {{ __('admin.' . $opt) }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ════════════════════════════════════════════════════ --}}
        {{-- ── NEW: Company Registration Type ── --}}
        {{-- ════════════════════════════════════════════════════ --}}
        <div class="col-md-6 col-12 mb-4">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="company_registration_type">
              {{ __('admin.company_registration_type') }} *
            </label>
            <select class="form-select select2" id="company_registration_type" name="company_registration_type" required
              data-placeholder="{{ __('admin.company_registration_type_placeholder') }}">
              <option value="">{{ __('admin.company_registration_type_placeholder') }}</option>
              @foreach ([
                'Not registered yet',
                'Individual (Sole Proprietorship)',
                'Limited Liability Company (LLC)',
                'One Person Company (OPC)',
                'Partnership',
                'Shareholding',
                'Other',
              ] as $type)
                <option value="{{ $type }}" {{ optional($client->company)->company_registration_type === $type ? 'selected' : '' }}>
                  {{ __('admin.' . $type) }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- ── NEW: Previous Incubation ── --}}
        <div class="col-md-6 col-12 mb-4">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="previous_incubation">
              {{ __('admin.previous_incubation') }} *
            </label>
            <select class="form-select select2" id="previous_incubation" name="previous_incubation" required
              data-placeholder="{{ __('admin.Select') }}">
              <option value="">{{ __('admin.Select') }}</option>
              <option value="1" {{ optional($client->company)->previous_incubation == '1' ? 'selected' : '' }}>
                {{ __('admin.Yes') }}
              </option>
              <option value="0" {{ optional($client->company)->previous_incubation == '0' && !is_null(optional($client->company)->previous_incubation) ? 'selected' : '' }}>
                {{ __('admin.No') }}
              </option>
            </select>
          </div>
        </div>

        {{-- ── NEW: Previous Incubator (conditional) ── --}}
        <div class="col-12 mb-4" id="previousIncubatorWrapper"
          style="{{ optional($client->company)->previous_incubation == '1' ? '' : 'display: none;' }}">
          <div class="form-group">
            <label class="form-label fs-6 fw-semibold mb-2" for="previous_incubator">
              {{ __('admin.previous_incubator') }}
            </label>
            <input class="form-control" id="previous_incubator" name="previous_incubator" type="text"
              maxlength="150"
              value="{{ old('previous_incubator', optional($client->company)->previous_incubator) }}"
              placeholder="{{ __('admin.previous_incubator_placeholder') }}" />
          </div>
        </div>
        
        {{-- Products / Services --}}
        <div class="mb-4">
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
 
        {{-- ── Submit ── --}}
        <div class="col-12 d-flex justify-content-end mt-4">
          <button class="btn btn-primary" id="submit-button" type="submit">
            <span id="button-text">{{ __('admin.Save') }}</span>
            <span class="spinner-border spinner-border-sm ms-2 d-none" id="button-spinner" role="status" aria-hidden="true"></span>
          </button>
        </div>

      </div>
    </form>
  </div>
</div>
