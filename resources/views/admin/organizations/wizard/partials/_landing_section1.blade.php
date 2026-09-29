<!-- How It Works Section -->
<div class="row" id="howItWorksContent">
  <div class="col-12 mb-6">
    <div class="card border shadow-none">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check form-switch me-3">
            <input type="checkbox" class="form-check-input" id="how_it_works_enabled" name="how_it_works_enabled"
              value="1" {{ old('how_it_works_enabled', $landingPage->how_it_works_enabled ?? false) ? 'checked' : '' }}
            data-section="howItWorks"
            />
            <label class="form-check-label" for="how_it_works_enabled">
              {{ __('admin.Enable Section') }}
            </label>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <label class="form-label" for="how_it_works_title">{{ __('admin.Section Title') }}</label>
            <input type="text" class="form-control" id="how_it_works_title" name="how_it_works_title"
              value="{{ old('how_it_works_title', $landingPage->how_it_works_title ?? '') }}"
              placeholder="{{ __('admin.Enter section title') }}" maxlength="255">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label" for="how_it_works_subtitle">{{ __('admin.Section Subtitle') }}</label>
            <input type="text" class="form-control" id="how_it_works_subtitle" name="how_it_works_subtitle"
              value="{{ old('how_it_works_subtitle', $landingPage->how_it_works_subtitle ?? '') }}"
              placeholder="{{ __('admin.Enter section subtitle') }}" maxlength="255">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label" for="how_it_works_description">{{ __('admin.Section Description') }}</label>
            <textarea class="form-control" id="how_it_works_description" name="how_it_works_description" rows="2"
              placeholder="{{ __('admin.Enter section description') }}"
              maxlength="1000">{{ old('how_it_works_description', $landingPage->how_it_works_description ?? '') }}</textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">

    <!-- Process Steps -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ __('admin.Process Steps') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="step" data-max="4">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Step') }}
        </button>
      </div>
      <div class="repeater-container" id="processStepsContainer">
        @forelse($processSteps as $index => $step)
        <div class="repeater-item card shadow-none mb-3 border" data-index="{{ $index }}">
          <!-- Card Header -->
          <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0">{{ __('admin.Step')}} {{ $index + 1 }}</h6>
            <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
              <i class="ti ti-trash"></i>
            </button>
          </div>

          <div class="card-body py-3">
            <div class="row">

              <!-- Title -->
              <div class="col-12 col-md-6 mb-2">
                <input type="text" class="form-control" name="process_steps[{{ $index }}][title]"
                  value="{{ $step['title'] ?? '' }}" placeholder="{{ __('admin.Step Title') }}" maxlength="255">
              </div>

              <!-- Icon -->
              <div class="col-12 col-md-6 mb-2">
                <select class="form-select icon-select" name="process_steps[{{ $index }}][icon]">
                  <option value="">{{ __('admin.Select icon') }}</option>
                  @foreach($stepIcons as $iconValue => $iconLabel)
                  <option value="{{ $iconValue }}" {{ ($step['icon'] ?? '' )==$iconValue ? 'selected' : '' }}>
                    {{ $iconLabel }}
                  </option>
                  @endforeach
                </select>
              </div>

              <!-- Description -->
              <div class="col-12">
                <textarea class="form-control" name="process_steps[{{ $index }}][description]"
                  rows="2" placeholder="{{ __('admin.Step Description') }}"
                  maxlength="1000">{{ $step['description'] ?? '' }}</textarea>
              </div>

            </div>
          </div>
        </div>
        @empty
        <div class="text-center text-muted py-4 empty-state rounded border-light border-dashed" id="stepsEmptyState">
          <i class="ti ti-list-numbers fs-1 mb-2 d-block opacity-50"></i>
          <p class="small mb-0">{{ __('admin.no_items_added') }}</p>
          <p class="small mb-0">{{ __('admin.Maximum 4 steps allowed') }}</p>
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>