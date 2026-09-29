@extends('layouts.admin')

@section('title', __('admin.Services'))
@section('index_url', route('services.index'))
@section('subTitle', __('admin.Edit service'))

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@stop

@section('content')
<form method="post" action="{{ route('services.update', ['service' => $service->id]) }}" enctype="multipart/form-data" id="serviceForm">
  {!! csrf_field() !!}
  @method('patch')
  
  <div class="card">
    <div class="card-header border-bottom mb-6">
      <h5 class="card-title mb-0 text-capitalize">{{ __('admin.Edit service') }}</h5>
    </div>
    
    <div class="card-body">

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
  
      <!-- Service Details -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h5 class="card-title mb-1">{{ __('admin.add_service_description') }}</h5>
          <span class="text-muted">{{ __('admin.service_details') }}</span>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="activeStatus" name="status" value="1" {{ old('status', $service->status) == 1 ? 'checked' : '' }}>
          <label class="form-check-label" for="activeStatus">{{ __('admin.active_status') }}</label>
        </div>
      </div>
  
      <!-- Service Image -->
      <div class="mb-4">
        <label class="form-label mb-2">{{ __('admin.service_image') }}</label>
        <div class="border-dashed border-light bg-light-subtle rounded p-8 text-center cursor-pointer"
          onclick="document.getElementById('img_url').click()" id="uploadArea">
          <div id="imagePreviewContainer" style="display: none;">
            <img id="imagePreview" src="" alt="Preview" class="img-fluid rounded mb-2" style="max-height: 200px; object-fit: contain;">
          </div>
          <div id="uploadPlaceholder">
            <i class="ti ti-cloud-upload text-secondary fs-2 mb-2"></i>
            <div class="fw-medium">{{ __('admin.click_to_upload') }}</div>
            <div class="small text-muted mt-1">{{ __('admin.image_only_allowed') }} up to 5MB</div>
          </div>
        </div>
        <input type="file" name="img_url" id="img_url" class="d-none" accept="image/*">
        <div id="fileError" class="invalid-feedback mt-2" style="display: none;"></div>
        <div id="fileName" class="small mt-2" style="display: none;"></div>
        
        @if($service->img_url)
        <div class="mt-3">
          <small class="text-muted">{{ __('admin.Current Image') }}:</small>
          <div class="mt-2">
            <img src="{{ Storage::disk('public_uploads')->url($service->img_url) }}" alt="Service Image" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
          </div>
        </div>
        @endif
      </div>
  
      <!-- Service Name -->
      <div class="col-12 mb-4">
        <label class="form-label">{{ __('admin.Service_Name') }} *</label>
        <input type="text" class="form-control" name="name_en" id="name_en" placeholder="{{ __('admin.Service_Name') }}" value="{{ old('name_en', $service->name_en) }}">
      </div>
  
      <!-- Currency and Price -->
      <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
          <label class="form-label" for="currency">{{ __('admin.Currency') }} *</label>
          <select class="form-select select2" name="currency" id="currency" data-placeholder="{{ __('admin.Select Currency') }}" data-allow-clear="true">
            <option value=""></option>
            @foreach ($currencies as $Currency)
              <option value="{{ $Currency->currency_code }}" {{ old('currency', $service->currency) == $Currency->currency_code ? 'selected' : '' }}>
                {{ $Currency->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
          <label class="form-label">{{ __('admin.Price') }}</label>
          <input type="number" class="form-control" name="price" id="price" min="0" step="0.01" placeholder="0.00" value="{{ old('price', $service->price) }}">
        </div>
        <div class="col-md-2 d-flex align-items-center pt-4 mt-2">
          <div class="form-check form-switch">
            <input type="hidden" name="hide_price" value="0">
            <input class="form-check-input" type="checkbox" id="hide_price" name="hide_price" value="1" {{ old('hide_price', $service->hide_price) ? 'checked' : '' }}>
            <label class="form-check-label" for="hide_price">{{ __('admin.Hide_Price') }}</label>
          </div>
        </div>
      </div>
  
      <!-- Description -->
      <div class="mb-5">
        <label class="form-label">{{ __('admin.Description') }} *</label>
       <textarea 
            class="form-control" 
            name="description_en" 
            id="description_en" 
            rows="5" 
            maxlength="500"
            placeholder="{{ __('admin.Description') }}"
        >{{ old('description_en', strip_tags($service->description_en)) }}</textarea>
      </div>
  
      <hr class="my-5">
  
      <!-- Categorization & Assignment -->
      <div class="mb-4">
        <h5 class="mb-1">{{ __('admin.service_categorization_label') }}</h5>
        <span class="text-muted">{{ __('admin.service_categorization_description') }}</span>
      </div>
  
      <!-- Category -->
      <div class="mb-4">
        <label class="form-label">{{ __('admin.select_category') }} *</label>
        <select class="form-select select2" name="service_category_id" id="service_category_id" data-placeholder="{{ __('admin.select_category') }}" data-allow-clear="true">
          <option value=""></option>
          @foreach ($serviceCategories as $category)
            <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
              {{ $category->{'name_' . $l} }}
            </option>
          @endforeach
        </select>
      </div>
  
      <!-- Organization Assignment -->
      @if( $organizations->count() > 0 )
      <div class="card card-body shadow-none border mb-4">
        <div class="pb-4 d-flex justify-content-between align-items-center">
          <span>{{ __('admin.organizations_assignment') }}</span>
          <div>
            <button type="button" class="btn btn-sm btn-text-primary me-2" id="selectAllOrgs">{{ __('admin.select_all') }}</button>
            <button type="button" class="btn btn-sm btn-text-secondary" id="deselectAllOrgs">{{ __('admin.deselect_all') }}</button>
          </div>
        </div>
        <select class="form-select select2" name="organization_ids[]" id="organization_ids" data-placeholder="{{ __('admin.Select Organizations') }}" multiple>
          {{-- <option value="">{{ __('admin.Select Organizations') }}</option> --}}
          @foreach ($organizations as $organization)
            <option value="{{ $organization->id }}" {{ in_array($organization->id, old('organization_ids', $service->organizations->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
              {{ $organization->{'name_' . app()->getLocale()} }}
            </option>
          @endforeach
        </select>

        <div class="mt-4 d-flex justify-content-between">
          <small class="text-muted" id="orgCounter">0 {{ __('admin.Of') }} {{ $organizations->count() }} {{ $l == 'en' ? 'Organization' : 'مؤسسة' }} {{ __('admin.Selected') }}</small>
          <small class="text-muted">{{ __('admin.service_accessibility_description') }}</small>
        </div>
      </div>
      @endif
  
      <!-- Vendor Information -->
      {{-- Service Provider: show only "Hide My Details" toggle --}}
      @if( $_user->hasRole('serviceProvider') && 1 != 1 )
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-1">{{ __('admin.my_details') }}</h5>
        <div class="form-check form-switch">
          <input type="hidden" name="hide_vendor_info" value="0">
          <input class="form-check-input" type="checkbox" id="hide_vendor_info" name="hide_vendor_info" value="1" {{ old('hide_vendor_info', $service->hide_vendor_info) == 1 ? 'checked' : '' }}>
          <label class="form-check-label" for="hide_vendor_info">
            {{ __('admin.hide_my_details') }}
          </label>
        </div>
      </div>
      @endif

      {{-- Admin / Org Manager / Sub Admin: full vendor section --}}
      @if( !$_user->hasRole('serviceProvider') )
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="card-title mb-1">{{ __('admin.provider_details') }}</h5>
        </div>
        <div class="form-check form-switch">
          <input type="hidden" name="hide_vendor_info" value="0">
          <input class="form-check-input" type="checkbox" id="hide_vendor_info" name="hide_vendor_info" value="1" {{ old('hide_vendor_info', $service->hide_vendor_info) == 1 ? 'checked' : '' }}>
          <label class="form-check-label" for="hide_vendor_info">
            {{ __('admin.hide_provider_details') }}
          </label>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">{{ __('admin.Service Provider') }} *</label>
        <select class="form-select select2" name="service_provider_id" id="service_provider_id" data-placeholder="{{ __('admin.select_provider') }}">
          <option value="">{{ __('admin.select_provider') }}</option>
          @foreach ($providers as $provider)
            <option value="{{ $provider->id }}" {{ old('service_provider_id', $service->service_provider_id) == $provider->id ? 'selected' : '' }}>
              {{ $provider->company_name }}
            </option>
          @endforeach
        </select>
        <small class="text-muted">{{ __('admin.hidden_provider_details_description') }}</small>
      </div>

      <div class="row">
        <div class="col-md-4 mb-4">
          <label class="form-label">{{ __('admin.Vendor_Name') }}</label>
          <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="{{ old('vendor_name', $service->vendor_name) }}" readonly disabled>
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label">{{ __('admin.Vendor_Email') }}</label>
          <input type="email" class="form-control" id="vendor_email" name="vendor_email" value="{{ old('vendor_email', $service->vendor_email) }}" readonly disabled>
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label">{{ __('admin.Vendor_Phone') }}</label>
          <input type="text" class="form-control" id="vendor_phone" name="vendor_phone" value="{{ old('vendor_phone', $service->vendor_phone) }}" readonly disabled>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4 mb-4">
          <label class="form-label">{{ __('admin.vendor_rating') }}</label>
          <input type="text" class="form-control" id="vendor_rating" readonly disabled>
        </div>
        <div class="col-md-4 mb-4">
          <label class="form-label">{{ __('admin.vendor_response_time') }}</label>
          <input type="text" class="form-control" id="vendor_response_time" readonly disabled>
        </div>
        <div class="col-md-4 mb-4 d-flex align-items-end">
          <a href="#" id="view_full_profile_link" class="btn btn-outline-primary btn-sm d-none" target="_blank">
            {{ __('admin.view_full_profile') }} &rarr;
          </a>
        </div>
      </div>
      @endif
  
    </div>

    @if( $_user->ableToAny(['services.edit', 'services.update'], $permissions) )
    <div class="card-footer d-flex justify-content-end">
      <button type="submit" class="btn btn-primary me-3">{{ __('admin.Update') }}</button>
      <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">{{ __('admin.Cancel') }}</a>
    </div>
    @endif
  </div>
</form>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
@parent
<script>
  var providers = @json($providers);
  var providerStats = @json($providerStats ?? []);
  var profileBaseUrl = "{{ url('dashboard/service-provider') }}";
  
  $(document).ready(function() {
    // Trigger select2 change to remove validation
     $('.select2').on('change', function () {
      const fieldName = $(this).attr('name');

      // Remove invalid state from select
      $(this).removeClass('is-invalid');

      // Tell FormValidation to revalidate the field
      fv.revalidateField(fieldName);
    });

    // File Upload Handler
    $('#img_url').on('change', function(e) {
      var file = e.target.files[0];
      if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
          $('#fileError').text('{{ __("admin.File size exceeds 5MB") }}').show();
          $('#fileName').hide();
          $('#imagePreviewContainer').hide();
          $('#uploadPlaceholder').show();
          $(this).val('');
          return;
        }

        // Validate file type
        if (!file.type.match('image.*')) {
          $('#fileError').text('{{ __("admin.Please select an image file") }}').show();
          $('#fileName').hide();
          $('#imagePreviewContainer').hide();
          $('#uploadPlaceholder').show();
          $(this).val('');
          return;
        }

        // Display image preview
        var reader = new FileReader();
        reader.onload = function(e) {
          $('#imagePreview').attr('src', e.target.result);
          $('#imagePreviewContainer').show();
          $('#uploadPlaceholder').hide();
        };
        reader.readAsDataURL(file);

        $('#fileError').hide();
        $('#fileName').text(file.name).show();
      }
    });

    // Service Provider Change Handler
    function populateProviderStats(providerId) {
      var provider = providers.find(function(p) { return p.id == providerId; });
      if (typeof provider !== 'undefined') {
        $('#vendor_name').val(provider.company_name);
        $('#vendor_email').val(provider.email);
        $('#vendor_phone').val(provider.phone);

        // Rating and Response Time
        var stats = providerStats[provider.id] || {};
        $('#vendor_rating').val(stats.rating > 0
          ? stats.rating + ' / 5 (' + stats.review_count + ' {{ __("admin.reviews") }})'
          : '{{ __("admin.no_ratings_yet") }}');
        $('#vendor_response_time').val(stats.response_time !== null && stats.response_time !== undefined
          ? stats.response_time + 'h'
          : '{{ __("admin.no_data") }}');

        // View Full Profile link
        $('#view_full_profile_link')
          .attr('href', profileBaseUrl + '/' + provider.id + '/profile')
          .removeClass('d-none');
      } else {
        $('#vendor_name').val('');
        $('#vendor_email').val('');
        $('#vendor_phone').val('');
        $('#vendor_rating').val('');
        $('#vendor_response_time').val('');
        $('#view_full_profile_link').addClass('d-none');
      }
    }

    $(document).on('change', '#service_provider_id', function() {
      populateProviderStats($(this).val());
    });

    // Pre-populate stats for the already-selected provider on edit page
    var initialProviderId = $('#service_provider_id').val();
    if (initialProviderId) {
      populateProviderStats(initialProviderId);
    }

    // Organization Select/Deselect All
    $('#selectAllOrgs').on('click', function(e) {
      e.preventDefault();
      $('#organization_ids > option').prop('selected', true);
      $('#organization_ids').trigger('change');
      
      // Revalidate the field to remove any error state if it was previously empty
      fv.disableValidator('organization_ids[]', 'notEmpty');
      fv.revalidateField('organization_ids[]');
    });

    $('#deselectAllOrgs').on('click', function(e) {
      e.preventDefault();
      $('#organization_ids > option').prop('selected', false);
      $('#organization_ids').trigger('change');
      
      // Re-enable validation since the field is now empty
      fv.enableValidator('organization_ids[]', 'notEmpty');
      fv.revalidateField('organization_ids[]');
    });

    // Organization Counter
    function updateOrgCounter() {
      var selectedCount = $('#organization_ids').val() ? $('#organization_ids').val().length : 0;
      var totalCount = {{ $organizations->count() }};
      $('#orgCounter').text(selectedCount + ' {{ __("admin.Of") }} ' + totalCount + ' {{ $l == "en" ? "Organization" : "مؤسسة" }} {{ __("admin.Selected") }}');
    }
    
    // Initialize counter
    updateOrgCounter();
    
    $('#organization_ids').on('change', function() {
      updateOrgCounter();
    });

    // Initialize FormValidation
    const fv = FormValidation.formValidation(
      document.getElementById('serviceForm'),
      {
        fields: {
          name_en: {
            validators: {
              notEmpty: {
                message: '{{ __('admin.Required_Field') }}'
              },
            }
          },
          description_en: {
            validators: {
              notEmpty: {
                message: '{{ __('admin.Required_Field') }}'
              },
            }
          },
          currency: {
            validators: {
              notEmpty: {
                message: '{{ __('admin.Required_Field') }}'
              }
            }
          },
          price: {
            validators: {
              // notEmpty: {
              //   message: '{{ __('admin.Required_Field') }}'
              // },
              numeric: {
                message: '{{ __('admin.must_be_valid_number') }}'
              },
              lessThan: {
                max: 9999999.99,
                message: '{{ __('admin.price_max_validation', ['max' => 9999999.99]) }}'
              },
              greaterThan: {
                min: 0,
                message: '{{ __('admin.must_not_be_less_than_0') }}'
              },
              regexp: {
                regexp: /^\d+(\.\d{1,2})?$/,
                message: '{{ __('admin.price_max_two_decimal_places') }}'
              }
            }
          },
          "organization_ids[]": {
            validators: {
              notEmpty: {
                message: '{{ __('admin.organizations_select_required') }}'
              }
            }
          },
          service_category_id: {
            validators: {
              notEmpty: {
                message: '{{ __('admin.Required_Field') }}'
              }
            }
          },
          @if( !$_user->hasRole('serviceProvider') )
          service_provider_id: {
            validators: {
              notEmpty: {
                message: '{{ __('admin.Required_Field') }}'
              }
            }
          },
          @endif
          img_url: {
            validators: {
              file: {
                extension: 'jpg,jpeg,png,gif,webp',
                type: 'image/jpeg,image/png,image/gif,image/webp',
                maxSize: 5242880, // 5MB
                message: 'Please select a valid image file (jpg, jpeg, png, gif, webp) up to 5MB'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: function(field, ele) {
              switch (field) {
                case 'description_en':
                  return '.mb-5';
                default:
                  return '.mb-4';
              }
            }
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      }
    );
  });
</script>
@stop