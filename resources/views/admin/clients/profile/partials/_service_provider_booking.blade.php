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
    <label
      class="w-100 border border-dashed border-light bg-light-subtle rounded d-flex flex-column justify-content-center align-items-center text-center p-5">
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
      placeholder="{{ __('admin.hero_description.placeholder') }}">
  </div>

  <!-- Services Short Description -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.services_short_description.label') }}
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
      placeholder="{{ __('admin.services_short_description.placeholder') }}"></textarea>
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
        placeholder="{{ __('admin.appointment_link.placeholder') }}">
    </div>
    <small class="text-muted">
      {{ __('admin.appointment_link.hint') }}
    </small>
  </div>

  <!-- Operating Mode -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.operating_mode.label') }}
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
      <option value="online_remote">
        {{ __('admin.operating_mode.options.online_remote') }}
      </option>
      <option value="on_site">
        {{ __('admin.operating_mode.options.on_site') }}
      </option>
      <option value="online_on_site">
        {{ __('admin.operating_mode.options.online_on_site') }}
      </option>
    </select>
  </div>

  <!-- Operating Region -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">
      {{ __('admin.operating_region.label') }}
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
      <option value="global">
        {{ __('admin.operating_region.options.global') }}
      </option>
      <option value="mena">
        {{ __('admin.operating_region.options.mena') }}
      </option>
      <option value="gcc">
        {{ __('admin.operating_region.options.gcc') }}
      </option>
      <option value="africa">
        {{ __('admin.operating_region.options.africa') }}
      </option>
      <option value="europe">
        {{ __('admin.operating_region.options.europe') }}
      </option>
      <option value="north_america">
        {{ __('admin.operating_region.options.north_america') }}
      </option>
      <option value="asia">
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
  });
</script>
@endpush