@php
  $org = $organization ?? null;
@endphp

<div class="row px-8">
  <!--  Organization Logo -->
  <div class="col-12 mb-6">
    <div class="d-flex align-items-start align-items-sm-center gap-6">
      <img
        id="upload-org-logo-preview"
        class="d-block w-px-100 h-px-100 rounded border shadow-sm"
        alt="logo"
        src="{{ $org && $org->img_url ? route('organizations.logo', $org->img_url ?? '') : '/images/logo-placeholder.png' }}"
        onerror="this.onerror=null;this.src='/images/logo-placeholder.png'"
      />
      <div class="button-wrapper">
        <div class="d-flex align-items-start align-items-center">
          <label for="upload-org-logo" class="btn btn-primary me-3 mb-4" tabindex="0">
            <span class="d-none d-sm-block">{{ __('admin.upload_logo') }}</span>
            <i class="ti ti-upload d-block d-sm-none"></i>
            <input
              hidden
              type="file"
              name="img_url"
              class="logo-file-input @error('img_url') is-invalid @enderror"
              id="upload-org-logo"
              accept="image/png, image/jpg, image/jpeg"
            />
          </label>
          <div class="reset-org-logo" style="{{ $org && $org->img_url ? '' : 'display: none;' }}">
            <button type="button" class="btn btn-label-secondary reset-org-logo-btn mb-4">
              <i class="ti ti-refresh-dot d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('admin.Reset') }}</span>
            </button>
          </div>
        </div>
        <div>
          {{ __('admin.Accepted formats') }}: PNG, JPG | {{ __('admin.Max size') }}: 5MB
        </div>
      </div>
    </div>
    @error('img_url')
    <div class="invalid-feedback d-block mt-4">{{ $message }}</div>
    @enderror
  </div>

  <!-- Organization Name (English) -->
  <div class="col-md-6 mb-4">
    <label class="form-label fw-semibold" for="name_en">
      {{ __('admin.Organization Name') }} ({{ __('admin.English') }}) <span class="text-danger">*</span>
    </label>
    <input
      type="text"
      class="form-control @error('name_en') is-invalid @enderror"
      id="name_en"
      name="name_en"
      value="{{ old('name_en', $org->name_en ?? '') }}"
      placeholder="{{ __('admin.Enter organization name in English') }}"
      maxlength="255"
    >
    <div class="invalid-feedback">{{ $errors->first('name_en') }}</div>
  </div>

  <!-- Organization Name (Arabic) -->
  <div class="col-md-6 mb-4">
    <label class="form-label fw-semibold" for="name_ar">
      {{ __('admin.Organization Name') }} ({{ __('admin.Arabic') }}) <span class="text-danger">*</span>
    </label>
    <input
      type="text"
      class="form-control @error('name_ar') is-invalid @enderror"
      id="name_ar"
      name="name_ar"
      value="{{ old('name_ar', $org->name_ar ?? '') }}"
      placeholder="{{ __('admin.Enter organization name in Arabic') }}"
      dir="rtl"
      maxlength="255"
    >
    <div class="invalid-feedback">{{ $errors->first('name_ar') }}</div>
  </div>

  <!-- Organization Type -->
  <div class="col-md-6 mb-4">
    <label class="form-label fw-semibold" for="organization_type_id">
      {{ __('admin.Organization Type') }} <span class="text-danger">*</span>
    </label>
    <select
      class="form-select select2 @error('organization_type_id') is-invalid @enderror"
      id="organization_type_id"
      name="organization_type_id"
      data-placeholder="{{ __('admin.Select Organization Type') }}"
    >
      <option value=""></option>
      @foreach($organizationTypes as $type)
        <option
          value="{{ $type->id }}"
          {{ old('organization_type_id', $org->organization_type_id ?? '') == $type->id ? 'selected' : '' }}
        >
          {{ $type->localizedName }}
        </option>
      @endforeach
    </select>
    <div class="invalid-feedback">{{ $errors->first('organization_type_id') }}</div>
  </div>

  <!-- Status -->
  <div class="col-md-6 mb-4">
    <label class="form-label fw-semibold" for="status">
      {{ __('admin.Status') }} <span class="text-danger">*</span>
    </label>
    <select
      class="form-select @error('status') is-invalid @enderror"
      id="status"
      name="status"
    >
      <option value="1" {{ old('status', $org->status ?? 1) == 1 ? 'selected' : '' }}>
        {{ __('admin.Active') }}
      </option>
      <option value="0" {{ old('status', $org->status ?? 1) == 0 ? 'selected' : '' }}>
        {{ __('admin.Not Active') }}
      </option>
    </select>
    <div class="invalid-feedback">{{ $errors->first('status') }}</div>
  </div>

  <!-- Organization Description -->
  <div class="col-12 mb-4">
    <label class="form-label fw-semibold" for="bio">
      {{ __('admin.Organization Description') }} <span class="text-danger">*</span>
    </label>
    <textarea
      class="form-control @error('bio') is-invalid @enderror"
      id="bio"
      name="bio"
      rows="4"
      placeholder="{{ __('admin.Enter organization description') }}"
      maxlength="1000"
    >{{ old('bio', $org->bio ?? '') }}</textarea>
    <div class="invalid-feedback">{{ $errors->first('bio') }}</div>
  </div>

  <!-- Industry Focus -->
  <div class="col-12 mb-4">
    <label class="form-label fw-semibold" for="industries">
      {{ __('admin.Industry Focus') }}
      <small class="text-muted">({{ __('admin.Optional') }})</small>
    </label>
    <select
      class="form-select select2 @error('industries') is-invalid @enderror"
      id="industries"
      name="industries[]"
      multiple
      data-placeholder="{{ __('admin.Select Industries') }}"
    >
      @php
        $selectedIndustries = old('industries', $org ? $org->industries->pluck('id')->toArray() : []);
      @endphp
      @foreach($industries as $industry)
        <option
          value="{{ $industry->id }}"
          {{ in_array($industry->id, $selectedIndustries) ? 'selected' : '' }}
        >
          {{ app()->getLocale() == 'ar' ? ($industry->name_ar ?? $industry->name_en ) : $industry->name_en }}
        </option>
      @endforeach
    </select>
    <div class="invalid-feedback">{{ $errors->first('industries') }}</div>
    <small class="text-muted">{{ __('admin.Select the industries this organization focuses on') }}</small>
  </div>
</div>

@push('scripts')
<script>
  $(function () {
    $('#upload-org-logo').on('change', function(e) {
      const file = e.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = function(e) {
        $('#upload-org-logo-preview').attr('src', e.target.result);
        $('.reset-org-logo').show();
      };
      reader.readAsDataURL(file);
    });  
    
    $('.reset-org-logo-btn').on('click', function() {
      $('#upload-org-logo').val('');
      $('#upload-org-logo-preview').attr('src', '{{ $org && $org->img_url ? $org->imageUrl : '/images/logo-placeholder.png' }}');
      $(this).closest('.reset-org-logo').hide();
    });
  });
</script>
@endpush
