@php
    $org = $client->service_provider;
@endphp
<div class="row" id="step-2-booking">

  <!-- Hero Image -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.hero_image.label') }}
    </label>
    <p class="text-muted small mb-0">
      {{ __('admin.hero_image.description') }}
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <div id="hero-preview-container" class="position-relative w-px-100 h-px-100 {{ $org && $org->hero_image ? '' : 'd-none' }}">
        <img id="hero-preview-image"
          src="{{ $org && $org->hero_image ? '/191014/' . $org->hero_image : '' }}"
          class="w-100 h-100 rounded border shadow-sm"
          alt="hero image">
      </div>
      <label
        class="flex-grow-1 border border-dashed border-light bg-light-subtle rounded d-flex flex-column justify-content-center align-items-center text-center p-5 cursor-pointer">
        <div class="mb-2">
          <i class="ti ti-cloud-upload"></i>
        </div>
        <div class="fw-semibold mb-2">
          {{ __('admin.hero_image.upload') }}
        </div>
        <div class="text-muted small">
          {{ __('admin.hero_image.formats') }}
        </div>
        <input type="file" id="hero_image" class="d-none" name="hero_image" accept=".png,.jpg,.jpeg,.webp,.svg+xml">
      </label>
    </div>
    <div id="fileName" class="small mt-2" style="display: none;"></div>
  </div>

  <!-- Hero Description -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.hero_description.label') }}
    </label>
    <p class="text-muted small mb-0">
      {{ __('admin.hero_description.description') }}
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <input
      type="text"
      class="form-control"
      name="hero_description"
      maxlength="100"
      value="{{ old('hero_description', $org->hero_description ?? '') }}"
      placeholder="{{ __('admin.hero_description.placeholder') }}">
  </div>

  <!-- Services Short Description -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.services_short_description.label') }} *
    </label>
    <p class="text-muted small mb-0">
      {{ __('admin.services_short_description.description') }}
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <textarea
      class="form-control"
      rows="3"
      name="services_short_description"
      maxlength="200"
      placeholder="{{ __('admin.services_short_description.placeholder') }}">{{ old('services_short_description', $org->services_short_description ?? '') }}</textarea>
  </div>

  <!-- Appointment Link -->
  <div class="col-md-5">
    <label class="fw-semibold">
      {{ __('admin.appointment_link.label') }}
    </label>
    <p class="text-muted small mb-0">
      {{ __('admin.appointment_link.description') }}
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <div class="input-group shadow-none">
      <span class="input-group-text">
        <i class="ti ti-link"></i>
      </span>
      <input
        type="url"
        class="form-control"
        name="appointment_link"
        maxlength="500"
        value="{{ old('appointment_link', $org->appointment_link ?? '') }}"
        placeholder="{{ __('admin.appointment_link.placeholder') }}">
    </div>
    <small class="text-muted">
      {{ __('admin.appointment_link.hint') }}
    </small>
  </div>

  <!-- Operating Mode -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.operating_mode.label') }} *
    </label>
    <p class="text-muted small mb-0">
      {{ __('admin.operating_mode.description') }}
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <select
      class="form-select select2"
      name="operating_mode"
      data-placeholder="{{ __('admin.operating_mode.placeholder') }}">
      <option value=""></option>
      <option value="online_remote" {{ (old('operating_mode', $org->operating_mode ?? '') == 'online_remote') ? 'selected' : '' }}>
        {{ __('admin.operating_mode.options.online_remote') }}
      </option>
      <option value="on_site" {{ (old('operating_mode', $org->operating_mode ?? '') == 'on_site') ? 'selected' : '' }}>
        {{ __('admin.operating_mode.options.on_site') }}
      </option>
      <option value="online_on_site" {{ (old('operating_mode', $org->operating_mode ?? '') == 'online_on_site') ? 'selected' : '' }}>
        {{ __('admin.operating_mode.options.online_on_site') }}
      </option>
    </select>
  </div>

  <!-- Operating Region -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.operating_region.label') }} *
    </label>
    <p class="text-muted small mb-0">
      {{ __('admin.operating_region.description') }}
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <select
      class="form-select select2"
      name="operating_region"
      data-placeholder="{{ __('admin.operating_region.placeholder') }}">
      <option value=""></option>
      <option value="global" {{ (old('operating_region', $org->operating_region ?? '') == 'global') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.global') }}
      </option>
      <option value="mena" {{ (old('operating_region', $org->operating_region ?? '') == 'mena') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.mena') }}
      </option>
      <option value="gcc" {{ (old('operating_region', $org->operating_region ?? '') == 'gcc') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.gcc') }}
      </option>
      <option value="africa" {{ (old('operating_region', $org->operating_region ?? '') == 'africa') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.africa') }}
      </option>
      <option value="europe" {{ (old('operating_region', $org->operating_region ?? '') == 'europe') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.europe') }}
      </option>
      <option value="north_america" {{ (old('operating_region', $org->operating_region ?? '') == 'north_america') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.north_america') }}
      </option>
      <option value="asia" {{ (old('operating_region', $org->operating_region ?? '') == 'asia') ? 'selected' : '' }}>
        {{ __('admin.operating_region.options.asia') }}
      </option>
    </select>
  </div>

</div>

@push('scripts')
<script>
  $('#hero_image').on('change', function() {
    const fileName = this.files[0] ? this.files[0].name : '';
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/svg+xml'];

    if (!fileName) {
      $('#hero-preview-container').addClass('d-none');
      $('#fileName').hide();
      return;
    }

    if (this.files[0].size > maxSize) {
      this.value = '';
      $('#fileName').hide();
      return;
    }

    if (!allowedTypes.includes(this.files[0].type)) {
      this.value = '';
      $('#fileName').hide();
      return;
    }

    $('#fileName').text(fileName).show();

    const reader = new FileReader();
    reader.onload = function (e) {
      $('#hero-preview-container').removeClass('d-none');
      $('#hero-preview-image').attr('src', e.target.result);
    };
    reader.readAsDataURL(this.files[0]);
  });
</script>
@endpush