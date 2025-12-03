@php
$action = Auth::user()->hasanyRole('Admin|organizationManager') ? route('company.profile_update_mine', $client->company->id) : route('company.profile_update_mine');
@endphp

<div class="card">
  <div class="card-body">
    <form id="desc-form" method="post" action="{{ $action }}"
      enctype="multipart/form-data">
      {!! csrf_field() !!}
      <input type="hidden" value="3" name="CompanyN">

      <div class="border-bottom border-light mb-6">
        <h5 class="card-title">{{ $tab['label'] }}</h5>
      </div>
        <div class="form-group mb-6">
          <label for="About_the_Company"
            class="form-label mb-1 fw-semibold fs-6">{{ __('admin.About_the_Company') }}</label>
            <textarea class="form-control"
              id="About_the_Company" maxlength="500" name="company_description"
              rows="5"
              placeholder="{{ __('admin.About_the_Company') }}"
              oninput="this.value = this.value.trimStart()"
              >@if($client->company){{ $client->company->info_company }}@endif</textarea>
        </div>
  
        <div class="form-group mb-3">
          <label for="Sustainable_development"
            class="form-label mb-1 fw-semibold fs-6">{{ __('admin.Sustainable_development') }}</label>
          <div class="position-relative">
            {{-- <input 
              type="text" 
              id="sustainable_development"
              name="Sustainable_development[]"
              class="form-control"
              placeholder="{{ __('admin.Sustainable_development') }}"
              value="@if($client->company){{ $client->company->Sustainable_development }}@endif"
            /> --}}
            <select name="Sustainable_development[]"
              class="form-select select2 px-10"
              id="sustainable-mutiple-select"
              multiple="multiple">
              <option value="no_poverty"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'no_poverty',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.no_poverty') }}</option>
              <option value="zero_hunger"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'zero_hunger',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.zero_hunger') }}</option>
              <option value="good_health"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'good_health',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.good_health') }}</option>
              <option value="quality_education"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'quality_education',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.quality_education') }}</option>
              <option value="gender_equality"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'gender_equality',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.gender_equality') }}</option>
              <option value="clean_water"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'clean_water',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.clean_water') }}</option>
              <option value="clean_energy"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'clean_energy',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.clean_energy') }}</option>
              <option value="decent_work"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'decent_work',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.decent_work') }}</option>
              <option value="industry_innovation"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'industry_innovation',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.industry_innovation') }}</option>
              <option value="reduced_inequalities"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'reduced_inequalities',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.reduced_inequalities') }}</option>
              <option value="sustainable_cities"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'sustainable_cities',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.sustainable_cities') }}</option>
              <option value="responsible_consumption"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'responsible_consumption',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.responsible_consumption') }}</option>
              <option value="climate_action"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'climate_action',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.climate_action') }}</option>
              <option value="life_below_water"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'life_below_water',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.life_below_water') }}</option>
              <option value="life_on_land"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'life_on_land',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.life_on_land') }}</option>
              <option value="peace_justice"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'peace_justice',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.peace_justice') }}</option>
              <option value="partnership_goals"
              @if($client->company)
                @if (
                    $client->company->Sustainable_development &&
                        in_array(
                            'partnership_goals',
                            json_decode($client->company->Sustainable_development, true))) selected @endif @endif>
                {{ __('admin.partnership_goals') }}</option>
            </select>
            <div class="invalid-feedback" id="sustainable-mutiple-select-error" style="display: none;">
              {{ __('admin.Please select at least one value') }}
            </div>
          </div>
        </div>
      <div class="border-bottom border-light my-8">
        <h5 class="card-title">{{ __('admin.About_Problem') }}</h5>
      </div>
      <div class="row">
        <div class="col-md-6 col-12 mb-6">
            <label for="about_problem"
              class="form-label mb-1 fw-semibold fs-6">{{ __('admin.about_problem') }}*</label>
            <div class="position-relative">
              <i class="ti ti-alert-circle fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
              <textarea class="form-control px-8" id="about_problem" placeholder="{{ __('admin.about_problem') }}*" name="about_problem" autocomplete="about_problem" rows="4"
                oninput="this.value = this.value.trimStart()"
              >{{ $client->company ? $client->company->about_problem : old('about_problem') }}</textarea>
            </div>
          </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_market_opportunity"
              class="form-label mb-1 fw-semibold fs-6">{{ __('admin.about_market_opportunity') }}*</label>
            <div class="position-relative">
              <i class="ti ti-chart-bar fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
              <textarea
              oninput="this.value = this.value.trimStart()"
                class="form-control px-8" id="about_market_opportunity" placeholder="{{ __('admin.about_market_opportunity') }}*" name="about_market_opportunity" autocomplete="about_market_opportunity" rows="4">{{ $client->company ? $client->company->about_market_opportunity : old('about_market_opportunity') }}</textarea>
            </div>
          </div>

      <div class="col-md-6 col-12 mb-6">
        <label for="about_competitors"
          class="form-label mb-1 fw-semibold fs-6">{{ __('admin.about_competitors') }}*</label>
        <div class="position-relative">
          <i class="ti ti-flag fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
          <textarea
          oninput="this.value = this.value.trimStart()"
           class="form-control px-8" id="about_competitors" placeholder="{{ __('admin.about_competitors') }}*" name="about_competitors" autocomplete="about_competitors" rows="4">{{ $client->company ? $client->company->about_competitors : old('about_competitors') }}</textarea>
        </div>
      </div>

      <div class="col-md-6 col-12 mb-6">
        <label for="about_monetize" class="form-label mb-1 fw-semibold fs-6">{{ __('admin.about_monetize') }}
          *</label>
        <div class="position-relative">
          <i class="ti ti-cash fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
          <textarea
            oninput="this.value = this.value.trimStart()"
          class="form-control px-8" id="about_monetize" placeholder="{{ __('admin.about_monetize') }} *" name="about_monetize" autocomplete="about_monetize" rows="4">{{ $client->company ? $client->company->about_monetize : old('about_monetize') }}</textarea>
        </div>
      </div>

      <div class="col-md-6 col-12 mb-6">
        <label for="about_challenges" class="form-label mb-1 fw-semibold fs-6">{{ __('admin.about_challenges') }}
          *</label>
        <div class="position-relative">
          <i class="ti ti-target fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
          <textarea
            oninput="this.value = this.value.trimStart()"
          class="form-control px-8" id="about_challenges" placeholder="{{ __('admin.about_challenges') }} *" name="about_challenges" autocomplete="about_challenges" rows="4" style="padding-left: 40px;">{{ $client->company ? $client->company->about_challenges : old('about_challenges') }}</textarea>
        </div>
      </div>
    </div>

   

      <div class="row">
        <div class="col-12 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary" id="submit-button">
            <span id="button-text"> {{ __('admin.Update') }}
              <i class="ti ti-edit ms-2"></i></span>
            <span id="button-spinner"
              class="spinner-border spinner-border-sm d-none" role="status"
              aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    console.log('loaded');

    const descForm = document.getElementById('desc-form');

    descForm.addEventListener('submit', async (e) => {
      const sustainabilityField = document.getElementById('sustainable-mutiple-select');
      const sustainabilityFieldError = document.getElementById('sustainable-mutiple-select-error');

      $(sustainabilityField).on('select2:select select2:unselect', () => {
        if ($(sustainabilityField).val().length > 0) {
          $(sustainabilityField).removeClass('is-invalid');
          $(sustainabilityFieldError).hide();
        }
      });

      // if not selected values disable form and attach is-invalid class
      if (sustainabilityField.value.length === 0) {
        e.preventDefault();
        sustainabilityField.focus();
        sustainabilityField.classList.add('is-invalid');
        sustainabilityFieldError.style.display = 'block';
        return;
      } else {
        sustainabilityField.classList.remove('is-invalid');
        sustainabilityFieldError.style.display = 'none';
      }

      console.log('form is submitting');

    });
  })
</script>