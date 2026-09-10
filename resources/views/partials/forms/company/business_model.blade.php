<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title">{{ $tab['label'] }}</h5>
    </div>
    <form id="mvp-form" method="post" action="{{ route('company.profile_update_mine', isset($client) && $client->company ? $client->company->id : auth()->user()->id) }}" enctype="multipart/form-data">
      {!! csrf_field() !!}
      <input type="hidden" value="{{ \App\Enums\CompanyFormStep::BusinessModel->value }}" name="CompanyN">
      <div class="row">
        @php
        $models = ['Marketplace_model', 'On_demand_model', 'Disintermediation_model', 'Subscription_model',
        'Freemium_model', 'Virtual_good_model', 'Reseller_model', 'Other'];
        @endphp
        <div class="col-12 mb-4">
          <label for="business_model" class="form-label">
            {{ __('admin.business_model') }}*
          </label>
          <div class="position-relative">
            <select class="form-select select2 business_model" id="business_model" name="business_model" data-placeholder="{{ __('admin.Select') }}">
              <option value="">{{ __('admin.Select') }}</option>

              @foreach($models as $model)
              <option 
                value="{{ $model }}" 
                {{ $client->company && $model == $client->company->business_model ? 'selected' : '' }}
              >
                {{ __("admin." . $model) }}
              </option>
              @endforeach
            </select>
          </div>
        </div>

        <div 
          class="col-md-6 col-12 mb-4 IfOther" 
          @if(optional($client->company)->business_model != 'Other')
            style="display: none;" 
          @endif 
        >
          <label for="other_business_model" class="form-label">
            {{ __('admin.other_business_model') }}*
          </label>
          <div class="position-relative">
            <input type="text" class="form-control other_business_model" id="other_business_model"
              placeholder="{{ __('admin.other_business_model') }}*"
              value="{{ $client->company ? $client->company->other_business_model : old('other_business_model') }}"
              name="other_business_model" autocomplete="other_business_model">
          </div>
        </div>

        <div class="col-12 mb-4">
          <label for="business_model_description" class="form-label">
            {{ __('admin.business_model_description')}}*
          </label>
          <div class="position-relative">
            <textarea class="form-control" id="business_model_description"
              placeholder="{{ __('admin.business_model_description') }}*" name="business_model_description" required
              autocomplete="business_model_description">{{ $client->company ? $client->company->business_model_description : old('business_model_description') }}</textarea>
          </div>
        </div>
        
        <div class="col-12 mb-4">
          <label for="mvp_prototype_link" class="form-label">
            {{ __('startup.mvp_prototype_link')}}
          </label>
          <div class="position-relative">
            <input
              type="url"
              name="mvp_prototype_link"
              class="form-control"
              id="mvp_prototype_link"
              placeholder="{{ __('startup.mvp_prototype_link') }}"
              value="{{ $client->company ? $client->company->mvp_prototype_link : old('mvp_prototype_link') }}"
              autocomplete="mvp_prototype_link"
              maxlength="500"
            >
          </div>
        </div>

        <div class="col-12 mb-4">
          <label for="business_plan" class="form-label">
            {{ __('startup.business_plan_attachment') }}*
          </label>

          <div class="" style="display: grid; place-items: center;">
            <input
              id="business_plan"
              class="form-control @error('business_plan') is-invalid @enderror"
              type="file" name="business_plan"
              accept=".pdf,.doc,.docx,.ppt,.pptx"
              @if(optional($client->company)->business_plan == null) required @endif
            />

          </div>
          @error('business_plan')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <div class="position-relative mt-2">
            @if (optional($client->company)->business_plan != null)
            <a
              id="business_plan_card"
              href="{{ app(\App\Services\FileManagerService::class)->getUrl($client->company->business_plan, 'pitch_deck') }}"
              target="_blank"
              download
              class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
              <i class="ti ti-file text-warning"></i>
              {{ basename($client->company->business_plan) }}
              <i class="ti ti-download"></i>
            </a>
            @endif
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary" id="submit-button-mvp">
            <span class="me-2" id="button-text-mvp">{{ __('admin.Save') }}</span>
            <span id="button-spinner-mvp" class="spinner-border spinner-border-sm d-none" role="status"aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>