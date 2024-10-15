<div class="row">
  <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <input type="hidden" value="3" name="CompanyN">

    <div class="form-group mb-3">
      <label for="About_the_Company" class="form-label">{{ __('admin.About_the_Company') }}</label>
      <textarea class="form-control" id="About_the_Company" maxlength="500" name="company_description" placeholder="{{ __('admin.About_the_Company') }}">{{ $client->company->info_company }}</textarea>
    </div>
    <div class="row">
    </div>

      <div class="form-group mb-3">
        <label for="Sustainable_development" class="form-label">{{ __('admin.Sustainable_development') }}</label>
        <select name="Sustainable_development[]" class="form-select select2" multiple="multiple">
          <option value="no_poverty" @if ($client->company->Sustainable_development && in_array('no_poverty', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.no_poverty') }}</option>
          <option value="zero_hunger" @if ($client->company->Sustainable_development && in_array('zero_hunger', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.zero_hunger') }}</option>
          <option value="good_health" @if ($client->company->Sustainable_development && in_array('good_health', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.good_health') }}</option>
          <option value="quality_education" @if ($client->company->Sustainable_development && in_array('quality_education', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.quality_education') }}</option>
          <option value="gender_equality" @if ($client->company->Sustainable_development && in_array('gender_equality', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.gender_equality') }}</option>
          <option value="clean_water" @if ($client->company->Sustainable_development && in_array('clean_water', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.clean_water') }}</option>
          <option value="clean_energy" @if ($client->company->Sustainable_development && in_array('clean_energy', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.clean_energy') }}</option>
          <option value="decent_work" @if ($client->company->Sustainable_development && in_array('decent_work', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.decent_work') }}</option>
          <option value="industry_innovation" @if ($client->company->Sustainable_development && in_array('industry_innovation', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.industry_innovation') }}</option>
          <option value="reduced_inequalities" @if ($client->company->Sustainable_development && in_array('reduced_inequalities', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.reduced_inequalities') }}</option>
          <option value="sustainable_cities" @if ($client->company->Sustainable_development && in_array('sustainable_cities', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.sustainable_cities') }}</option>
          <option value="responsible_consumption" @if ($client->company->Sustainable_development && in_array('responsible_consumption', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.responsible_consumption') }}</option>
          <option value="climate_action" @if ($client->company->Sustainable_development && in_array('climate_action', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.climate_action') }}</option>
          <option value="life_below_water" @if ($client->company->Sustainable_development && in_array('life_below_water', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.life_below_water') }}</option>
          <option value="life_on_land" @if ($client->company->Sustainable_development && in_array('life_on_land', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.life_on_land') }}</option>
          <option value="peace_justice" @if ($client->company->Sustainable_development && in_array('peace_justice', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.peace_justice') }}</option>
          <option value="partnership_goals" @if ($client->company->Sustainable_development && in_array('partnership_goals', json_decode($client->company->Sustainable_development, true))) selected @endif>{{ __('admin.partnership_goals') }}</option>
        </select>
      </div>

    <div class="row">
      <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="submit-button">
          <span id="button-text">{{ __('admin.Update') }}</span>
          <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
</div>