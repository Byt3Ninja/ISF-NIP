<div class="row" id="step-2-booking">
  <!-- Hero Image -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">Hero Image *</label>
    <p class="text-muted small mb-0">
      Featured image for services section.
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <label
      class="w-100 border border-dashed border-light bg-light-subtle rounded d-flex flex-column justify-content-center align-items-center text-center p-5">
      <div class="mb-2">
        <i class="ti ti-cloud-upload"></i>
      </div>
      <div class="fw-semibold">Click to upload</div>
      <div class="text-muted small">
        or drag and drop<br>
        PNG, JPG, JPEG, WEBP, SVG (max. 10MB)
      </div>
      <input type="file" class="d-none" name="hero_image">
    </label>
  </div>

  <!-- Hero Description -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">Hero Description *</label>
    <p class="text-muted small mb-0">
      Main headline for your services section.
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <input type="text" class="form-control" name="hero_description" placeholder="e.g., Professional Services Tailored to Your Needs">
  </div>

  <!-- Services Short Description -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">Services Short Description *</label>
    <p class="text-muted small mb-0">
      Brief overview of what you offer. 2–3 sentences.
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <textarea class="form-control" rows="3" name="services_short_description" placeholder="Describe the range of services you provide..."></textarea>
  </div>

  <!-- Appointment Link -->
  <div class="col-md-5">
    <label class="fw-semibold">
      Appointment Scheduling Link
    </label>
    <p class="text-muted small mb-0">
      Direct link to book appointments (Calendly, Cal.com, etc.).
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <div class="input-group">
      <span class="input-group-text">
        <i class="ti ti-link"></i>
      </span>
      <input type="url" class="form-control" name="appointment_link" placeholder="https://calendly.com/your-username">
    </div>
    <small class="text-muted">
      This link will appear as a “Book Appointment” button on your profile.
    </small>
  </div>

  <!-- Operating Mode -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">Operating mode *</label>
    <p class="text-muted small mb-0">
      Selection: Online- Remote/ On-site/ Online + On-site
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <select class="form-select select2" name="operating_mode" data-placeholder="Select Operating Mode">
      <option value=""></option>
      <option value="online_remote">Online- Remote</option>
      <option value="on_site">On-site</option>
      <option value="online_on_site">Online + On-site</option>
    </select>
  </div>

  <!-- Operating Region -->
  <div class="col-md-5 mb-4">
    <label class="fw-semibold">Operating Region *</label>
    <p class="text-muted small mb-0">
      Selection: Global/MENA/GCC/Africa/Europe/North America/Asia
    </p>
  </div>

  <div class="col-md-7 mb-4">
    <select class="form-select select2" name="operating_region" data-placeholder="Select Operating Region">
      <option value=""></option>
      <option value="global">Global</option>
      <option value="mena">MENA</option>
      <option value="gcc">GCC</option>
      <option value="africa">Africa</option>
      <option value="europe">Europe</option>
      <option value="north_america">North America</option>
      <option value="asia">Asia</option>
    </select>
  </div>

</div>
