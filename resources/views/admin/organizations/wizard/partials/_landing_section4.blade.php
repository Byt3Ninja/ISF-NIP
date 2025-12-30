<!-- Partners Section -->
<div class="row" id="partnersContent">
  <div class="col-12 mb-6">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ __('admin.Partners') }}</h5>
      <div class="form-check form-switch me-3">
        <input
          type="checkbox"
          class="form-check-input"
          id="partners_enabled"
          name="partners_enabled"
          value="1"
          {{ old('partners_enabled', $landingPage->partners_enabled ?? false) ? 'checked' : '' }}
          data-section="partners"
        />
        <label class="form-check-label" for="partners_enabled">
          {{ __('admin.Enable Section') }}
        </label>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <label class="form-label" for="partners_title">{{ __('admin.Section Title') }}</label>
        <input
          type="text"
          class="form-control"
          id="partners_title"
          name="partners_title"
          value="{{ old('partners_title', $landingPage->partners_title ?? '') }}"
          placeholder="{{ __('admin.Enter section title') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label" for="partners_subtitle">{{ __('admin.Section Subtitle') }}</label>
        <input
          type="text"
          class="form-control"
          id="partners_subtitle"
          name="partners_subtitle"
          value="{{ old('partners_subtitle', $landingPage->partners_subtitle ?? '') }}"
          placeholder="{{ __('admin.Enter section subtitle') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label" for="partners_description">{{ __('admin.Section Description') }}</label>
        <textarea
          class="form-control"
          id="partners_description"
          name="partners_description"
          rows="2"
          placeholder="{{ __('admin.Enter section description') }}"
          maxlength="1000"
        >{{ old('partners_description', $landingPage->partners_description ?? '') }}</textarea>
      </div>
    </div>

    <!-- Partners List -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ __('admin.Partner Organizations') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="partner" data-max="20">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Partner') }}
        </button>
      </div>
      <div class="repeater-container row" id="partnersContainer">
        @forelse($partnersList as $index => $partner)
          <div class="repeater-item col-md-4 col-lg-3 mb-3" data-index="{{ $index }}">
            <div class="card border h-100">
              <div class="card-body py-3 text-center">
                <button type="button" class="btn btn-sm btn-icon btn-label-danger position-absolute top-0 end-0 m-2 remove-item-btn">
                  <i class="ti ti-x"></i>
                </button>
                @if(!empty($partner['logo']))
                  <div class="mb-2">
                    <img src="{{ $landingPage->getPartnerLogoUrl($partner['logo']) }}" class="img-fluid rounded" style="max-height: 60px;">
                    <input type="hidden" name="partners_list[{{ $index }}][existing_logo]" value="{{ $partner['logo'] }}">
                  </div>
                @else
                  <div class="mb-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 60px;">
                    <i class="ti ti-building text-muted fs-3"></i>
                  </div>
                @endif
                <input type="text" class="form-control form-control-sm mb-2" name="partners_list[{{ $index }}][name]" value="{{ $partner['name'] ?? '' }}" placeholder="{{ __('admin.Partner Name') }}" maxlength="255">
                <input type="file" class="form-control form-control-sm" name="partners_list[{{ $index }}][logo]" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                <small class="text-muted d-block mt-1">PNG, JPG, SVG (2MB)</small>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="text-center text-muted py-4 empty-state rounded border-dashed" id="partnersEmptyState">
              <i class="ti ti-building-community fs-1 mb-2 d-block opacity-50"></i>
              <p class="mb-0">{{ __('admin.Maximum 20 partners allowed') }}</p>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>