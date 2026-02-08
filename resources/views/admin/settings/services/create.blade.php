@extends('layouts.admin')

@section('title', __('admin.Services'))
@section('index_url', route('services.index'))
@section('subTitle', __('admin.Add new service'))

@section('style')
<style>
  .ql-toolbar.ql-snow {
    border-top-right-radius: 0.5rem;
    border-top-left-radius: 0.5rem;
  }
</style>
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
    <div class="content-overlay"></div>
    <div class="content-body">
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header border-bottom">
                <h5 class="card-title mb-0">{{ __('admin.Add new service') }}</h5>
              </div>
              <div class="card-content">
                <div class="card-body mt-6">
                  @if (session()->has('msg'))
                  <div class="row">
                    <div class="col-md-12">
                      <div class="alert alert-warning alert-danger mb-2" role="alert">
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    </div>
                  </div>
                  @endif
                  <!-- Validation messages -->
                  @if (count($errors) > 0)
                  <div class="row">
                    <div class="col-md-12">
                      <div class="alert alert-danger">
                        <ul>
                          @foreach ($errors->all() as $error)
                          <li>{{ $error }} </li>
                          @endforeach
                        </ul>
                      </div>
                    </div>
                  </div>
                  @endif

                  <form method="post" action="{{ route('services.store') }}" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="row">
                      <div class="col-md-12">

                        <fieldset class="form-group mb-4">
                          <label class="form-label" for="basicInput">{{ __('admin.Service_Name') }}</label>
                          <input style="direction: ltr;" type="text" class="form-control col-md-6 col-sm-12"
                            id="name_en" name='name_en' required value="{{ old('name_en') }}">
                        </fieldset>

                        <fieldset class="form-group mb-4">
                          <label class="form-label" for="basicInput">{{ __('admin.Price') }}</label>
                          <input style="direction: ltr;" type="text" class="form-control col-md-3 col-sm-12" id="price"
                            name='price' required placeholder="{{ __('admin.Price') }}"
                            value="{{ old('price') }}">
                        </fieldset>
                       
                        <fieldset class="form-group mb-4">
                          <label class="form-label" for="Currency">{{ __('admin.Currency') }}</label>
                          <select class="form-control form-select" name="Currency" id="Currency" required>
                            <option value="" disabled>{{ __('settings.Currency') }}</option>
                            
                            @foreach ($currencies as $Currency)
                              <option value="{{ $Currency->currency_code }}"
                                {{ $_user->currency == $Currency->currency_code ? 'selected' : '' }}>
                                {{ __('admin.' . $Currency->currency_name) }}
                              </option>
                            @endforeach
                          </select>
                        </fieldset>




                        <fieldset class="form-group mb-4">
                          <label class="form-label" for="basicInput">{{ __('admin.Description') }}</label>
                          <input type="hidden" name="description_en" id="description_en"
                            value="{{ old('description_en') }}" />
                          <div id="quill-editor" style="height: 200px;">{!! old('description_en') !!}</div>
                        </fieldset>

                        @if( $_user->hasRole('serviceProvider') )
                        @php
                          $organizations = App\Organization::all();   
                        @endphp
                        <fieldset class="form-group mb-4">
                          <label class="form-label">{{ __('admin.Organizations') }}</label>
                          <div class="row">
                            @foreach ($organizations as $organization)
                            <div class="col-md-4">
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                  id="organization_{{ $organization->id }}" name="organization_ids[]"
                                  value="{{ $organization->id }}">
                                <label class="form-check-label" for="organization_{{ $organization->id }}">
                                  {{ $organization->{'name_' . app()->getLocale()} }}
                                </label>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </fieldset>
                        @endif

                        <fieldset class="form-group mb-4">
                          <label class="form-label" for="basicInput">{{ __('admin.Services Categories') }}</label>
                          <select class="select2 form-control" required id="service_category_id" name='service_category_id'>
                            <option value="">{{ __('admin.Services Categories') }}</option>
                            @foreach ($serviceCategories as $category)
                            <option value="{{ $category->id }}" {{ old('service_category_id')==$category->id ? 'selected' : '' }}>
                              {{ $category->{'name_' . $l} }}</option>
                            @endforeach
                          </select>
                        </fieldset>

                        @if( $organizations->count() > 0 && !$_user->hasRole('serviceProvider') )
                        <fieldset class="form-group mb-4">
                          <label class="form-label">{{ __('admin.Organizations') }}</label>
                          <div class="row">
                            @foreach ($organizations as $organization)
                            <div class="col-md-4">
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                  id="organization_{{ $organization->id }}" name="organization_ids[]"
                                  value="{{ $organization->id }}">
                                <label class="form-check-label" for="organization_{{ $organization->id }}">
                                  {{ $organization->{'name_' . app()->getLocale()} }}
                                </label>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </fieldset>
                        @endif

                        

                        @if( !$_user->hasRole('serviceProvider') )
                        <!-- Vendor Details -->
                        <fieldset class="form-group mb-4">
                          <label class="form-label" for="basicInput">{{ __('admin.Service Provider') }}</label>
                          <select class="select2 form-control" required id="service_provider_id" name='service_provider_id'>
                            <option value="">{{ __('admin.Service Provider') }}</option>
                            @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}" {{ old('service_provider_id')==$provider->id ? 'selected' : '' }}>
                              {{ $provider->company_name }}</option>
                            @endforeach
                          </select>
                        </fieldset>

                        <fieldset class="form-group mb-4">
                          <label class="form-label">{{ __('admin.Vendor_Name') }}</label>
                          <input type="text" class="form-control col-md-6 col-sm-12" id="vendor_name" name="vendor_name" value="{{ old('vendor_name') }}" readonly disabled>
                        </fieldset>

                        <fieldset class="form-group mb-4">
                          <label class="form-label">{{ __('admin.Vendor_Email') }}</label>
                          <input type="email" class="form-control col-md-6 col-sm-12" id="vendor_email" name="vendor_email" value="{{ old('vendor_email') }}" readonly disabled>
                        </fieldset>

                        <fieldset class="form-group mb-4">
                          <label class="form-label">{{ __('admin.Vendor_Phone') }}</label>
                          <input type="text" class="form-control col-md-6 col-sm-12" id="vendor_phone" name="vendor_phone" value="{{ old('vendor_phone') }}" readonly disabled>
                        </fieldset>
                        @endif

                        <!-- Visibility Controls -->
                        <fieldset class="form-group mb-4">
                          <div class="form-check">
                            <input type="hidden" name="hide_price" value="0">
                            <input type="checkbox" class="form-check-input" id="hide_price" name="hide_price" value="1"
                              {{ old('hide_price') ? 'checked' : '' }}>
                            <label class="form-check-label" for="hide_price">{{ __('admin.Hide_Price') }}</label>
                          </div>
                        </fieldset>

                        <fieldset class="form-group mb-4">
                          <div class="form-check">
                            <input type="hidden" name="hide_vendor_info" value="0">
                            <input type="checkbox" class="form-check-input" id="hide_vendor_info"
                              name="hide_vendor_info" value="1" {{ old('hide_vendor_info') ? 'checked' : '' }}>
                            <label class="form-check-label" for="hide_vendor_info">{{ __('admin.Hide_Vendor') }}</label>
                          </div>
                        </fieldset>

                        <fieldset class="form-group mt-3">
                          <label for="img_url" class="form-label">{{ __('admin.Image') }}</label>
                          <div class="input-group">
                            <input type="file" class="form-control" id="img_url" name="img_url" accept="image/*">
                            <label class="input-group-text" for="img_url">
                              <i class="ti ti-upload me-1"></i>{{ __('admin.SelectFile') }}
                            </label>
                          </div>
                        </fieldset>
                      </div>
                    </div>
                    @if( $_user->ableToAny(['services.create', 'services.store'], $permissions) )
                    <div class="row mt-6">
                      <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Save') }}</button>
                      </div>
                    </div>
                    @endif
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<!-- END: Content-->
@stop

@section('pagescripts')
@parent
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  <script>
    var providers = @json( $providers );
    $(document).on('change', '#service_provider_id', function() {
      var provider_id = $(this).val();
      var provider = providers.find(function(provider) {
        return provider.id == provider_id;
      });
      if( typeof provider !== 'undefined' )
      {
        $('#vendor_name').val( provider.company_name );
        $('#vendor_email').val( provider.email );
        $('#vendor_phone').val( provider.phone );
      }
    });
    document.addEventListener('DOMContentLoaded', function () {
      var quill = new Quill('#quill-editor', {
        theme: 'snow'
      });

      var descriptionInput = document.getElementById('description_en');
      quill.on('text-change', function () {
        descriptionInput.value = quill.root.innerHTML;
      });
    });
  </script>
@stop