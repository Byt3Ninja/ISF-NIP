@php
  $org = $organization ?? null;
@endphp

<div class="row">
  <!-- Organization Name (English) -->
  <div class="col-md-6 mb-3">
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
      required
    >
    <div class="invalid-feedback">{{ $errors->first('name_en') }}</div>
  </div>

  <!-- Organization Name (Arabic) -->
  <div class="col-md-6 mb-3">
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
      required
    >
    <div class="invalid-feedback">{{ $errors->first('name_ar') }}</div>
  </div>

  <!-- Organization Type -->
  <div class="col-md-6 mb-3">
    <label class="form-label fw-semibold" for="organization_type_id">
      {{ __('admin.Organization Type') }} <span class="text-danger">*</span>
    </label>
    <select
      class="form-select select2 @error('organization_type_id') is-invalid @enderror"
      id="organization_type_id"
      name="organization_type_id"
      data-placeholder="{{ __('admin.Select Organization Type') }}"
      required
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
  <div class="col-md-6 mb-3">
    <label class="form-label fw-semibold" for="status">
      {{ __('admin.Status') }} <span class="text-danger">*</span>
    </label>
    <select
      class="form-select @error('status') is-invalid @enderror"
      id="status"
      name="status"
      required
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
  <div class="col-12 mb-3">
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
      required
    >{{ old('bio', $org->bio ?? '') }}</textarea>
    <div class="d-flex justify-content-between mt-1">
      <div class="invalid-feedback">{{ $errors->first('bio') }}</div>
      <small class="text-muted" id="bioCharCount">0 / 1000</small>
    </div>
  </div>

  <!-- Industry Focus -->
  <div class="col-12 mb-3">
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
          {{ app()->getLocale() == 'ar' ? $industry->name_ar : $industry->name_en }}
        </option>
      @endforeach
    </select>
    <div class="invalid-feedback">{{ $errors->first('industries') }}</div>
    <small class="text-muted">{{ __('admin.Select the industries this organization focuses on') }}</small>
  </div>

  <!-- Organization Logo -->
  <div class="col-12 mb-3">
    <label class="form-label fw-semibold d-block">
      {{ __('admin.Organization Logo') }}
      <small class="text-muted">({{ __('admin.Optional') }})</small>
    </label>
    <div class="d-flex justify-content-center">
      <label for="img_url" class="d-flex justify-content-center align-items-center position-relative rounded"
        style="height: 140px; cursor: pointer; width: 200px;">
        <img
          src="{{ $org && $org->img_url ? $org->imageUrl : '/images/logo-placeholder.png' }}"
          alt="Organization Logo"
          class="rounded img-fluid border shadow-sm"
          style="height: 140px; width: 200px; object-fit: cover;"
          id="logoPreview"
        />
        <input type="file" class="form-control d-none @error('img_url') is-invalid @enderror" id="img_url" name="img_url" accept="image/png, image/jpg, image/jpeg">
        <i class="ti ti-camera-plus position-absolute fs-3 text-dark end-0 bottom-0 m-4 z-1"></i>
      </label>
    </div>
    @error('img_url')
      <div class="text-danger text-center mt-2">{{ $message }}</div>
    @enderror
    <small class="text-muted d-block text-center mt-2">
      {{ __('admin.Accepted formats') }}: PNG, JPG | {{ __('admin.Max size') }}: 5MB
    </small>
  </div>
</div>
