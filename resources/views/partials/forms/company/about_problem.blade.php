<div class="card">
    <div class="card-body">
      <div class="border-bottom border-light mb-6">
        <h5 class="card-title">{{ $tab['label'] }}</h5>
      </div>

      @if($_user->hasAnyRole('Admin|organizationManager'))
    <form method="post" action="{{ route('company.profile_update_mine', $client->company->id) }}"
      enctype="multipart/form-data">
@else
<form method="post" action="{{ route('company.profile_update_mine') }}"
  enctype="multipart/form-data">
@endif
        {!! csrf_field() !!}
        <input type="hidden" value="{{ \App\Enums\CompanyFormStep::AboutProblemLegacy->value }}" name="CompanyN">
        <div class="row">
            <div class="col-md-6 col-12 mb-6">
                <label for="about_problem"
                  class="form-label">{{ __('admin.about_problem') }}*</label>
                <div class="position-relative">
                  <i class="ti ti-alert-circle fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
                  <textarea class="form-control px-8" id="about_problem" placeholder="{{ __('admin.about_problem') }}*" name="about_problem" required autocomplete="about_problem" rows="4">{{ $client->company ? $client->company->about_problem : old('about_problem') }}</textarea>
                </div>
              </div>

              <div class="col-md-6 col-12 mb-6">
                <label for="about_market_opportunity"
                  class="form-label">{{ __('admin.about_market_opportunity') }}*</label>
                <div class="position-relative">
                  <i class="ti ti-chart-bar fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
                  <textarea class="form-control px-8" id="about_market_opportunity" placeholder="{{ __('admin.about_market_opportunity') }}*" name="about_market_opportunity" required autocomplete="about_market_opportunity" rows="4">{{ $client->company ? $client->company->about_market_opportunity : old('about_market_opportunity') }}</textarea>
                </div>
              </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_competitors"
              class="form-label">{{ __('admin.about_competitors') }}*</label>
            <div class="position-relative">
              <i class="ti ti-flag fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
              <textarea class="form-control px-8" id="about_competitors" placeholder="{{ __('admin.about_competitors') }}*" name="about_competitors" required autocomplete="about_competitors" rows="4">{{ $client->company ? $client->company->about_competitors : old('about_competitors') }}</textarea>
            </div>
          </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_monetize" class="form-label">{{ __('admin.about_monetize') }}
              *</label>
            <div class="position-relative">
              <i class="ti ti-cash fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
              <textarea class="form-control px-8" id="about_monetize" placeholder="{{ __('admin.about_monetize') }} *" name="about_monetize" required autocomplete="about_monetize" rows="4">{{ $client->company ? $client->company->about_monetize : old('about_monetize') }}</textarea>
            </div>
          </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_challenges" class="form-label">{{ __('admin.about_challenges') }}
              *</label>
            <div class="position-relative">
              <i class="ti ti-target fs-5 position-absolute me-3 text-primary" style="{{$l == 'en' ? 'left: 7px;' : 'right: 7px;'}} top: 0.65rem;"></i>
              <textarea class="form-control px-8" id="about_challenges" placeholder="{{ __('admin.about_challenges') }} *" name="about_challenges" required autocomplete="about_challenges" rows="4" style="padding-left: 40px;">{{ $client->company ? $client->company->about_challenges : old('about_challenges') }}</textarea>
            </div>
          </div>
        </div>

        <div class="row">
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
  