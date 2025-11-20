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
        <input type="hidden" value="13" name="CompanyN">
        <div class="row">
            @php
$models = ['Marketplace_model', 'On_demand_model', 'Disintermediation_model', 'Subscription_model', 'Freemium_model', 'Virtual_good_model', 'Reseller_model', 'Other'];
@endphp
            <div class="col-12 mb-6">
                <label for="business_model"
                  class="form-label">{{ __('admin.business_model') }}*</label>
                <div class="position-relative">
                    <select class="form-select business_model" 
                    id="business_model" name="business_model">
                    <option value="" disabled>{{ __('admin.business_model') }}</option>
                   
                    @foreach($models as $model)
                    <option value="{{ $model }}" {{ $client->company && $model == $client->company->business_model ? 'selected' : '' }}>
                        {{ __("admin." . $model) }}
                    </option>
                @endforeach
                  </select>
                  
                </div>
              </div>

       
              <div class="col-md-6 col-12 mb-6 IfOther" @if(optional($client->company)->business_model != 'Other')  style="display: none;" @endif >
                <label for="other_business_model"
                  class="form-label">{{ __('admin.other_business_model') }}*</label>
                <div class="position-relative">
                  <input type="text"
                    class="form-control other_business_model"
                    id="other_business_model" placeholder="{{ __('admin.other_business_model') }}*"
                    value="{{ $client->company ? $client->company->other_business_model : old('other_business_model') }}"
                    name="other_business_model" autocomplete="other_business_model">
                </div>
              </div>
           

          <div class="col-12 mb-6">
            <label for="business_model_description"
              class="form-label">{{ __('admin.business_model_description') }}*</label>
            <div class="position-relative">
                <textarea class="form-control" 
                id="business_model_description" 
                placeholder="{{ __('admin.business_model_description') }}*"
                name="business_model_description" 
                required 
                autocomplete="business_model_description">{{ $client->company ? $client->company->business_model_description : old('business_model_description') }}</textarea>
            </div>
          </div>

          <div class="col-12 mb-6">
            <label for="business_plan" class="form-label">{{ __('startup.business_plan_attachment') }}
              *</label>

               <div class="" style="display: grid; place-items: center;">
                    <input id="business_plan"
                      class="form-control"
                      type="file" name="business_plan" value="{{ old('business_plan') }}">
                      
                  </div>
            <div class="position-relative">

              @if (optional($client->company)->business_plan != null)
                      <div class="card shadow-sm border mt-6">
                        <div class="p-4">
                          <div
                            class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                              <i class="ti ti-file text-warning"></i>
                              <span>{{ basename($client->company->business_plan) }}</span>
                            </div>
                            <a class="btn btn-transparent"
                              href="{{ $client->company->business_plan }}" target="_blank"
                              download="{{ basename($client->company->business_plan) }}">
                              <i class="ti ti-download text-primary"></i>
                            </a>
                          </div>
                        </div>
                      </div>
                    @endif                              
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
