<!-- Achievements Section -->
<div class="row" id="achievementsContent">
  <div class="col-12 mb-6">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ __('admin.Achievements & Impact') }}</h5>
      <div class="form-check form-switch me-3">
        <input
          type="checkbox"
          class="form-check-input"
          id="achievements_enabled"
          name="achievements_enabled"
          value="1"
          {{ old('achievements_enabled', $landingPage->achievements_enabled ?? false) ? 'checked' : '' }}
          data-section="achievements"
        />
        <label class="form-check-label" for="achievements_enabled">
          {{ __('admin.Enable Section') }}
        </label>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="achievements_title">{{ __('admin.Section Title') }}</label>
        <input
          type="text"
          class="form-control"
          id="achievements_title"
          name="achievements_title"
          value="{{ old('achievements_title', $landingPage->achievements_title ?? '') }}"
          placeholder="{{ __('admin.Enter section title') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="achievements_subtitle">{{ __('admin.Section Subtitle') }}</label>
        <input
          type="text"
          class="form-control"
          id="achievements_subtitle"
          name="achievements_subtitle"
          value="{{ old('achievements_subtitle', $landingPage->achievements_subtitle ?? '') }}"
          placeholder="{{ __('admin.Enter section subtitle') }}"
          maxlength="255"
        >
      </div>
      <div class="col-12 mb-3">
        <label class="form-label" for="achievements_description">{{ __('admin.Section Description') }}</label>
        <textarea
          class="form-control"
          id="achievements_description"
          name="achievements_description"
          rows="2"
          placeholder="{{ __('admin.Enter section description') }}"
          maxlength="1000"
        >{{ old('achievements_description', $landingPage->achievements_description ?? '') }}</textarea>
      </div>
    </div>

    <!-- Metrics -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ __('admin.Impact Metrics') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="metric" data-max="8">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Metric') }}
        </button>
      </div>
      <div class="repeater-container" id="metricsContainer">
        @forelse($metrics as $index => $metric)
          <div class="repeater-item card shadow-none mb-3 border" data-index="{{ $index }}">
            <div class="card-body py-3">
              <div class="row align-items-center">
                <div class="col-md-3">
                  <input type="text" class="form-control form-control-sm" name="metrics[{{ $index }}][number]" value="{{ $metric['number'] ?? '' }}" placeholder="{{ __('admin.Metric Number') }}" maxlength="20">
                </div>
                <div class="col-md-5">
                  <input type="text" class="form-control form-control-sm" name="metrics[{{ $index }}][label]" value="{{ $metric['label'] ?? '' }}" placeholder="{{ __('admin.Metric Label') }}" maxlength="255">
                </div>
                <div class="col-md-3">
                  @php
                    $metricIcons = [
                      'ti-users' => 'Users',
                      'ti-building' => 'Building',
                      'ti-currency-dollar' => 'Currency Dollar',
                      'ti-rocket' => 'Rocket',
                      'ti-chart-line' => 'Chart Line',
                      'ti-briefcase' => 'Briefcase',
                      'ti-world' => 'World',
                      'ti-star' => 'Star',
                      'ti-trophy' => 'Trophy',
                      'ti-chart-bar' => 'Chart Bar',
                      'ti-chart-pie' => 'Chart Pie',
                      'ti-trending-up' => 'Trending Up',
                      'ti-target' => 'Target',
                      'ti-award' => 'Award',
                      'ti-flag' => 'Flag',
                      'ti-calendar' => 'Calendar',
                      'ti-clock' => 'Clock',
                      'ti-heart' => 'Heart',
                      'ti-thumb-up' => 'Thumb Up',
                      'ti-check' => 'Check',
                      'ti-bolt' => 'Bolt',
                      'ti-gift' => 'Gift',
                      'ti-home' => 'Home',
                      'ti-map-pin' => 'Map Pin',
                      'ti-globe' => 'Globe',
                      'ti-sparkles' => 'Sparkles',
                      'ti-flame' => 'Flame',
                      'ti-diamond' => 'Diamond',
                      'ti-crown' => 'Crown',
                      'ti-medal' => 'Medal',
                    ];
                  @endphp
                  <select class="form-select form-select-sm icon-select" name="metrics[{{ $index }}][icon]">
                    <option value="">{{ __('admin.Select icon') }}</option>
                    @foreach($metricIcons as $iconValue => $iconLabel)
                      <option value="{{ $iconValue }}" {{ ($metric['icon'] ?? '') == $iconValue ? 'selected' : '' }}>{{ $iconLabel }}</option>
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
          <div class="text-center text-muted py-4 empty-state rounded border-dashed" id="metricsEmptyState">
            <i class="ti ti-chart-bar fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.Maximum 8 metrics allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>