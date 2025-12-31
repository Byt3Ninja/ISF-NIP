<!-- How It Works Section -->
<div class="row" id="howItWorksContent">
  <div class="col-12 mb-6">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ __('admin.How It Works') }}</h5>
      <div class="form-check form-switch me-3">
        <input
          type="checkbox"
          class="form-check-input"
          id="how_it_works_enabled"
          name="how_it_works_enabled"
          value="1"
          {{ old('how_it_works_enabled', $landingPage->how_it_works_enabled ?? false) ? 'checked' : '' }}
          data-section="howItWorks"
        />
        <label class="form-check-label" for="how_it_works_enabled">
          {{ __('admin.Enable Section') }}
        </label>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="how_it_works_title">{{ __('admin.Section Title') }}</label>
        <input
          type="text"
          class="form-control"
          id="how_it_works_title"
          name="how_it_works_title"
          value="{{ old('how_it_works_title', $landingPage->how_it_works_title ?? '') }}"
          placeholder="{{ __('admin.Enter section title') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="how_it_works_subtitle">{{ __('admin.Section Subtitle') }}</label>
        <input
          type="text"
          class="form-control"
          id="how_it_works_subtitle"
          name="how_it_works_subtitle"
          value="{{ old('how_it_works_subtitle', $landingPage->how_it_works_subtitle ?? '') }}"
          placeholder="{{ __('admin.Enter section subtitle') }}"
          maxlength="255"
        >
      </div>
      <div class="col-12 mb-3">
        <label class="form-label" for="how_it_works_description">{{ __('admin.Section Description') }}</label>
        <textarea
          class="form-control"
          id="how_it_works_description"
          name="how_it_works_description"
          rows="2"
          placeholder="{{ __('admin.Enter section description') }}"
          maxlength="1000"
        >{{ old('how_it_works_description', $landingPage->how_it_works_description ?? '') }}</textarea>
      </div>
    </div>

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
            <div class="card-body py-3">
              <div class="row align-items-center">
                <div class="col-md-1 text-center">
                  <span class="badge bg-primary rounded-circle step-number" style="width: 32px; height: 32px; line-height: 24px;">{{ $index + 1 }}</span>
                </div>
                <div class="col-md-3">
                  <input type="text" class="form-control form-control-sm" name="process_steps[{{ $index }}][title]" value="{{ $step['title'] ?? '' }}" placeholder="{{ __('admin.Step Title') }}" maxlength="255">
                </div>
                <div class="col-md-4">
                  <input type="text" class="form-control form-control-sm" name="process_steps[{{ $index }}][description]" value="{{ $step['description'] ?? '' }}" placeholder="{{ __('admin.Step Description') }}" maxlength="1000">
                </div>
                <div class="col-md-3">
                  @php
                    $stepIcons = [
                      'ti-file-text' => 'File Text',
                      'ti-clipboard-check' => 'Clipboard Check',
                      'ti-users' => 'Users',
                      'ti-rocket' => 'Rocket',
                      'ti-chart-bar' => 'Chart Bar',
                      'ti-trophy' => 'Trophy',
                      'ti-bulb' => 'Bulb',
                      'ti-target' => 'Target',
                      'ti-check' => 'Check',
                      'ti-star' => 'Star',
                      'ti-heart' => 'Heart',
                      'ti-mail' => 'Mail',
                      'ti-send' => 'Send',
                      'ti-calendar' => 'Calendar',
                      'ti-clock' => 'Clock',
                      'ti-settings' => 'Settings',
                      'ti-search' => 'Search',
                      'ti-home' => 'Home',
                      'ti-briefcase' => 'Briefcase',
                      'ti-world' => 'World',
                      'ti-message' => 'Message',
                      'ti-phone' => 'Phone',
                      'ti-lock' => 'Lock',
                      'ti-shield-check' => 'Shield Check',
                      'ti-gift' => 'Gift',
                      'ti-thumb-up' => 'Thumb Up',
                      'ti-lightbulb' => 'Lightbulb',
                      'ti-bolt' => 'Bolt',
                      'ti-flag' => 'Flag',
                      'ti-award' => 'Award',
                    ];
                  @endphp
                  <select class="form-select form-select-sm icon-select" name="process_steps[{{ $index }}][icon]">
                    <option value="">{{ __('admin.Select icon') }}</option>
                    @foreach($stepIcons as $iconValue => $iconLabel)
                      <option value="{{ $iconValue }}" {{ ($step['icon'] ?? '') == $iconValue ? 'selected' : '' }}>{{ $iconLabel }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-1 text-end">
                  <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4 empty-state rounded border-dashed" id="stepsEmptyState">
            <i class="ti ti-list-numbers fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.Maximum 4 steps allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>