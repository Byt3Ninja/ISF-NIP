@php
  $org = $organization ?? null;
  $landingPage = $org ? $org->landingPage : null;
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';

  // Get existing data
  $processSteps = old('process_steps', $landingPage->process_steps ?? []);
  $metrics = old('metrics', $landingPage->metrics ?? []);
  $articles = old('articles', $landingPage->articles ?? []);
  $partnersList = old('partners_list', $landingPage->partners_list ?? []);
  $currentTheme = old('landing_theme', $landingPage->landing_theme ?? 'gradient');
@endphp

<!-- Organization URL Section -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="form-check form-switch me-3">
          <input
            type="checkbox"
            class="form-check-input landing-toggle"
            id="landingEnabledToggle"
            name="landing_enabled"
            value="1"
            {{ old('landing_enabled', $landingPage->landing_enabled ?? true) ? 'checked' : '' }}
          >
        </div>
        <div>
          <h5 class="mb-0 fw-bold">
            <i class="ti ti-link me-2 text-primary"></i>{{ __('admin.Organization URL') }}
          </h5>
          <small class="text-muted" id="landingStatusText">
            @if(old('landing_enabled', $landingPage->landing_enabled ?? true))
              <span class="text-success"><i class="ti ti-check me-1"></i>{{ __('admin.Landing page is published') }}</span>
            @else
              <span class="text-warning"><i class="ti ti-eye-off me-1"></i>{{ __('admin.Landing page is unpublished') }}</span>
            @endif
          </small>
        </div>
      </div>
      @if($org && $org->slug)
        <a href="{{ route('organization.wizard.preview', $org->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
          <i class="ti ti-external-link me-1"></i>{{ __('admin.Preview Landing Page') }}
        </a>
      @endif
    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-12 mb-3">
        <label class="form-label fw-semibold" for="slug">
          {{ __('admin.Custom URL Slug') }}
          <small class="text-muted">({{ __('admin.Optional') }})</small>
        </label>
        <div class="input-group">
          <span class="input-group-text bg-light">{{ url('/o/') }}/</span>
          <input
            type="text"
            class="form-control @error('slug') is-invalid @enderror"
            id="slug"
            name="slug"
            value="{{ old('slug', $org->slug ?? '') }}"
            placeholder="organization-name"
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            maxlength="100"
          >
          <button type="button" class="btn btn-outline-secondary" id="generateSlugBtn">
            <i class="ti ti-refresh me-1"></i>{{ __('admin.Generate from name') }}
          </button>
          <button type="button" class="btn btn-outline-info" id="checkSlugBtn">
            <i class="ti ti-check me-1"></i>{{ __('admin.Check Availability') }}
          </button>
        </div>
        <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
        <div id="slugAvailabilityFeedback" class="mt-1" style="display: none;"></div>
        <small class="text-muted">{{ __('admin.URL must contain only lowercase letters, numbers, and hyphens') }}</small>
      </div>
    </div>
  </div>
</div>

<!-- Theme Selection Section -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <h5 class="mb-0 fw-bold">
      <i class="ti ti-palette me-2 text-primary"></i>{{ __('admin.Landing Page Theme') }}
    </h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <p class="text-muted mb-3">{{ __('admin.Select a theme for your public landing page') }}</p>
        <div class="row g-3">
          <!-- Gradient Theme -->
          <div class="col-md-4">
            <label class="theme-option card h-100 {{ $currentTheme == 'gradient' ? 'border-primary border-2' : 'border' }}" for="theme_gradient">
              <input type="radio" name="landing_theme" id="theme_gradient" value="gradient" class="d-none" {{ $currentTheme == 'gradient' ? 'checked' : '' }}>
              <div class="card-body text-center p-3">
                <div class="theme-preview mb-3 rounded overflow-hidden" style="height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                  <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
                    <i class="ti ti-building fs-1 mb-1"></i>
                    <small>Organization</small>
                  </div>
                </div>
                <h6 class="fw-bold mb-1">{{ __('admin.Modern Gradient') }}</h6>
                <small class="text-muted">{{ __('admin.Colorful gradients, SaaS-style design') }}</small>
              </div>
              <div class="card-footer bg-transparent text-center py-2 {{ $currentTheme == 'gradient' ? 'bg-primary bg-opacity-10' : '' }}">
                @if($currentTheme == 'gradient')
                  <i class="ti ti-check text-primary me-1"></i>
                  <span class="text-primary fw-semibold">{{ __('admin.Selected') }}</span>
                @else
                  <span class="text-muted">{{ __('admin.Click to select') }}</span>
                @endif
              </div>
            </label>
          </div>

          <!-- Minimal Theme -->
          <div class="col-md-4">
            <label class="theme-option card h-100 {{ $currentTheme == 'minimal' ? 'border-primary border-2' : 'border' }}" for="theme_minimal">
              <input type="radio" name="landing_theme" id="theme_minimal" value="minimal" class="d-none" {{ $currentTheme == 'minimal' ? 'checked' : '' }}>
              <div class="card-body text-center p-3">
                <div class="theme-preview mb-3 rounded overflow-hidden" style="height: 100px; background: #f8f9fa; border: 1px solid #e9ecef;">
                  <div class="d-flex flex-column align-items-center justify-content-center h-100 text-dark">
                    <i class="ti ti-building fs-1 mb-1"></i>
                    <small>Organization</small>
                  </div>
                </div>
                <h6 class="fw-bold mb-1">{{ __('admin.Clean Minimal') }}</h6>
                <small class="text-muted">{{ __('admin.White backgrounds, subtle shadows') }}</small>
              </div>
              <div class="card-footer bg-transparent text-center py-2 {{ $currentTheme == 'minimal' ? 'bg-primary bg-opacity-10' : '' }}">
                @if($currentTheme == 'minimal')
                  <i class="ti ti-check text-primary me-1"></i>
                  <span class="text-primary fw-semibold">{{ __('admin.Selected') }}</span>
                @else
                  <span class="text-muted">{{ __('admin.Click to select') }}</span>
                @endif
              </div>
            </label>
          </div>

          <!-- Vuexy Theme -->
          <div class="col-md-4">
            <label class="theme-option card h-100 {{ $currentTheme == 'vuexy' ? 'border-primary border-2' : 'border' }}" for="theme_vuexy">
              <input type="radio" name="landing_theme" id="theme_vuexy" value="vuexy" class="d-none" {{ $currentTheme == 'vuexy' ? 'checked' : '' }}>
              <div class="card-body text-center p-3">
                <div class="theme-preview mb-3 rounded overflow-hidden" style="height: 100px; background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%);">
                  <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
                    <i class="ti ti-building fs-1 mb-1"></i>
                    <small>Organization</small>
                  </div>
                </div>
                <h6 class="fw-bold mb-1">{{ __('admin.Vuexy Style') }}</h6>
                <small class="text-muted">{{ __('admin.Match admin panel colors') }}</small>
              </div>
              <div class="card-footer bg-transparent text-center py-2 {{ $currentTheme == 'vuexy' ? 'bg-primary bg-opacity-10' : '' }}">
                @if($currentTheme == 'vuexy')
                  <i class="ti ti-check text-primary me-1"></i>
                  <span class="text-primary fw-semibold">{{ __('admin.Selected') }}</span>
                @else
                  <span class="text-muted">{{ __('admin.Click to select') }}</span>
                @endif
              </div>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- How It Works Section -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="form-check form-switch me-3">
          <input
            type="checkbox"
            class="form-check-input section-toggle"
            id="how_it_works_enabled"
            name="how_it_works_enabled"
            value="1"
            {{ old('how_it_works_enabled', $landingPage->how_it_works_enabled ?? false) ? 'checked' : '' }}
            data-section="howItWorks"
          >
        </div>
        <h5 class="mb-0 fw-bold">
          <i class="ti ti-list-numbers me-2 text-primary"></i>{{ __('admin.How It Works') }}
        </h5>
      </div>
      <span class="badge bg-label-secondary">{{ __('admin.Optional') }}</span>
    </div>
  </div>
  <div class="card-body section-content" id="howItWorksContent" style="{{ old('how_it_works_enabled', $landingPage->how_it_works_enabled ?? false) ? '' : 'display: none;' }}">
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="how_it_works_title">{{ __('admin.Section Title') }}</label>
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
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="how_it_works_subtitle">{{ __('admin.Section Subtitle') }}</label>
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
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="how_it_works_description">{{ __('admin.Section Description') }}</label>
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
        <h6 class="fw-bold mb-0">{{ __('admin.Process Steps') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="step" data-max="4">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Step') }}
        </button>
      </div>
      <div class="repeater-container" id="processStepsContainer">
        @forelse($processSteps as $index => $step)
          <div class="repeater-item card mb-2 border" data-index="{{ $index }}">
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
          <div class="text-center text-muted py-4 empty-state" id="stepsEmptyState">
            <i class="ti ti-list-numbers fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.Maximum 4 steps allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Achievements Section -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="form-check form-switch me-3">
          <input
            type="checkbox"
            class="form-check-input section-toggle"
            id="achievements_enabled"
            name="achievements_enabled"
            value="1"
            {{ old('achievements_enabled', $landingPage->achievements_enabled ?? false) ? 'checked' : '' }}
            data-section="achievements"
          >
        </div>
        <h5 class="mb-0 fw-bold">
          <i class="ti ti-trophy me-2 text-primary"></i>{{ __('admin.Achievements & Impact') }}
        </h5>
      </div>
      <span class="badge bg-label-secondary">{{ __('admin.Optional') }}</span>
    </div>
  </div>
  <div class="card-body section-content" id="achievementsContent" style="{{ old('achievements_enabled', $landingPage->achievements_enabled ?? false) ? '' : 'display: none;' }}">
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="achievements_title">{{ __('admin.Section Title') }}</label>
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
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="achievements_subtitle">{{ __('admin.Section Subtitle') }}</label>
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
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="achievements_description">{{ __('admin.Section Description') }}</label>
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
        <h6 class="fw-bold mb-0">{{ __('admin.Impact Metrics') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="metric" data-max="8">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Metric') }}
        </button>
      </div>
      <div class="repeater-container" id="metricsContainer">
        @forelse($metrics as $index => $metric)
          <div class="repeater-item card mb-2 border" data-index="{{ $index }}">
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
          <div class="text-center text-muted py-4 empty-state" id="metricsEmptyState">
            <i class="ti ti-chart-bar fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.Maximum 8 metrics allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Media & Recognition Section -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="form-check form-switch me-3">
          <input
            type="checkbox"
            class="form-check-input section-toggle"
            id="media_enabled"
            name="media_enabled"
            value="1"
            {{ old('media_enabled', $landingPage->media_enabled ?? false) ? 'checked' : '' }}
            data-section="media"
          >
        </div>
        <h5 class="mb-0 fw-bold">
          <i class="ti ti-news me-2 text-primary"></i>{{ __('admin.Media & Recognition') }}
        </h5>
      </div>
      <span class="badge bg-label-secondary">{{ __('admin.Optional') }}</span>
    </div>
  </div>
  <div class="card-body section-content" id="mediaContent" style="{{ old('media_enabled', $landingPage->media_enabled ?? false) ? '' : 'display: none;' }}">
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="media_title">{{ __('admin.Section Title') }}</label>
        <input
          type="text"
          class="form-control"
          id="media_title"
          name="media_title"
          value="{{ old('media_title', $landingPage->media_title ?? '') }}"
          placeholder="{{ __('admin.Enter section title') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="media_subtitle">{{ __('admin.Section Subtitle') }}</label>
        <input
          type="text"
          class="form-control"
          id="media_subtitle"
          name="media_subtitle"
          value="{{ old('media_subtitle', $landingPage->media_subtitle ?? '') }}"
          placeholder="{{ __('admin.Enter section subtitle') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="media_description">{{ __('admin.Section Description') }}</label>
        <textarea
          class="form-control"
          id="media_description"
          name="media_description"
          rows="2"
          placeholder="{{ __('admin.Enter section description') }}"
          maxlength="1000"
        >{{ old('media_description', $landingPage->media_description ?? '') }}</textarea>
      </div>
    </div>

    <!-- Articles -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">{{ __('admin.Press Articles') }}</h6>
        <button type="button" class="btn btn-sm btn-outline-primary add-item-btn" data-type="article" data-max="8">
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Article') }}
        </button>
      </div>
      <div class="repeater-container" id="articlesContainer">
        @forelse($articles as $index => $article)
          <div class="repeater-item card mb-3 border" data-index="{{ $index }}">
            <div class="card-body py-3">
              <div class="row">
                <div class="col-md-3 mb-2">
                  <input type="text" class="form-control form-control-sm" name="articles[{{ $index }}][publication_name]" value="{{ $article['publication_name'] ?? '' }}" placeholder="{{ __('admin.Publication Name') }}" maxlength="255">
                </div>
                <div class="col-md-2 mb-2">
                  <input type="date" class="form-control form-control-sm" name="articles[{{ $index }}][date]" value="{{ $article['date'] ?? '' }}">
                </div>
                <div class="col-md-3 mb-2">
                  <input type="text" class="form-control form-control-sm" name="articles[{{ $index }}][title]" value="{{ $article['title'] ?? '' }}" placeholder="{{ __('admin.Article Title') }}" maxlength="255">
                </div>
                <div class="col-md-3 mb-2">
                  <input type="url" class="form-control form-control-sm" name="articles[{{ $index }}][link]" value="{{ $article['link'] ?? '' }}" placeholder="{{ __('admin.Article Link') }}" maxlength="500">
                </div>
                <div class="col-md-1 text-end mb-2">
                  <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
                <div class="col-md-8 mb-2">
                  <textarea class="form-control form-control-sm" name="articles[{{ $index }}][summary]" rows="2" placeholder="{{ __('admin.Article Summary') }}" maxlength="1000">{{ $article['summary'] ?? '' }}</textarea>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label small">{{ __('admin.Article Image') }}</label>
                  @if(!empty($article['image']))
                    <div class="mb-2">
                      <img src="{{ $landingPage->getArticleImageUrl($article['image']) }}" class="img-thumbnail" style="max-height: 60px;">
                      <input type="hidden" name="articles[{{ $index }}][existing_image]" value="{{ $article['image'] }}">
                    </div>
                  @endif
                  <input type="file" class="form-control form-control-sm" name="articles[{{ $index }}][image]" accept="image/png,image/jpeg,image/jpg">
                  <small class="text-muted">PNG, JPG ({{ __('admin.Max size') }}: 5MB)</small>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4 empty-state" id="articlesEmptyState">
            <i class="ti ti-news fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.Maximum 8 articles allowed') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<!-- Partners Section -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="form-check form-switch me-3">
          <input
            type="checkbox"
            class="form-check-input section-toggle"
            id="partners_enabled"
            name="partners_enabled"
            value="1"
            {{ old('partners_enabled', $landingPage->partners_enabled ?? false) ? 'checked' : '' }}
            data-section="partners"
          >
        </div>
        <h5 class="mb-0 fw-bold">
          <i class="ti ti-building-community me-2 text-primary"></i>{{ __('admin.Partners') }}
        </h5>
      </div>
      <span class="badge bg-label-secondary">{{ __('admin.Optional') }}</span>
    </div>
  </div>
  <div class="card-body section-content" id="partnersContent" style="{{ old('partners_enabled', $landingPage->partners_enabled ?? false) ? '' : 'display: none;' }}">
    <div class="row mb-4">
      <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" for="partners_title">{{ __('admin.Section Title') }}</label>
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
        <label class="form-label fw-semibold" for="partners_subtitle">{{ __('admin.Section Subtitle') }}</label>
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
        <label class="form-label fw-semibold" for="partners_description">{{ __('admin.Section Description') }}</label>
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
        <h6 class="fw-bold mb-0">{{ __('admin.Partner Organizations') }}</h6>
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
            <div class="text-center text-muted py-4 empty-state" id="partnersEmptyState">
              <i class="ti ti-building-community fs-1 mb-2 d-block opacity-50"></i>
              <p class="mb-0">{{ __('admin.Maximum 20 partners allowed') }}</p>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>


<!-- Section Background Colors -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <div class="avatar avatar-sm bg-label-info me-3">
          <span class="avatar-initial"><i class="ti ti-palette"></i></span>
        </div>
        <div>
          <h5 class="card-title mb-0 fw-bold">{{ __('admin.Section Background Colors') }}</h5>
          <small class="text-muted">{{ __('admin.Customize background colors for each section') }}</small>
        </div>
      </div>
    </div>
  </div>
  <div class="card-body">
    @php
      $sections = [
        'hero' => ['label' => __('admin.Hero Section'), 'default_color' => '#667eea', 'default_gradient_start' => '#667eea', 'default_gradient_end' => '#764ba2'],
        'how_it_works' => ['label' => __('admin.How It Works'), 'default_color' => '#f8f7fa', 'default_gradient_start' => '#f8f7fa', 'default_gradient_end' => '#ffffff'],
        'achievements' => ['label' => __('admin.Achievements & Impact'), 'default_color' => '#ffffff', 'default_gradient_start' => '#ffffff', 'default_gradient_end' => '#f8f7fa'],
        'media' => ['label' => __('admin.Media & Recognition'), 'default_color' => '#f8f7fa', 'default_gradient_start' => '#f8f7fa', 'default_gradient_end' => '#ffffff'],
        'partners' => ['label' => __('admin.Partners'), 'default_color' => '#ffffff', 'default_gradient_start' => '#ffffff', 'default_gradient_end' => '#f8f7fa'],
      ];
    @endphp

    @foreach($sections as $sectionKey => $sectionData)
      @php
        $bgTypeField = $sectionKey . '_bg_type';
        $bgColorField = $sectionKey . '_bg_color';
        $bgGradientStartField = $sectionKey . '_bg_gradient_start';
        $bgGradientEndField = $sectionKey . '_bg_gradient_end';

        $currentBgType = old($bgTypeField, $landingPage->$bgTypeField ?? 'solid');
        $currentBgColor = old($bgColorField, $landingPage->$bgColorField ?? $sectionData['default_color']);
        $currentGradientStart = old($bgGradientStartField, $landingPage->$bgGradientStartField ?? $sectionData['default_gradient_start']);
        $currentGradientEnd = old($bgGradientEndField, $landingPage->$bgGradientEndField ?? $sectionData['default_gradient_end']);
      @endphp

      <div class="row mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
        <div class="col-md-3">
          <label class="form-label fw-semibold mb-2">{{ $sectionData['label'] }}</label>
          <div class="bg-color-preview rounded" id="{{ $sectionKey }}_preview" style="height: 40px; {{ $currentBgType == 'gradient' ? 'background: linear-gradient(135deg, ' . $currentGradientStart . ' 0%, ' . $currentGradientEnd . ' 100%)' : 'background: ' . $currentBgColor }}; border: 1px solid #e9ecef;"></div>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">{{ __('admin.Type') }}</label>
          <select class="form-select form-select-sm bg-type-select" name="{{ $bgTypeField }}" data-section="{{ $sectionKey }}">
            <option value="solid" {{ $currentBgType == 'solid' ? 'selected' : '' }}>{{ __('admin.Solid Color') }}</option>
            <option value="gradient" {{ $currentBgType == 'gradient' ? 'selected' : '' }}>{{ __('admin.Gradient') }}</option>
          </select>
        </div>
        <div class="col-md-7">
          <!-- Solid Color Input -->
          <div class="solid-color-input {{ $currentBgType == 'gradient' ? 'd-none' : '' }}" id="{{ $sectionKey }}_solid">
            <label class="form-label fw-semibold">{{ __('admin.Background Color') }}</label>
            <div class="input-group input-group-sm">
              <input type="color" class="form-control form-control-color bg-color-picker" name="{{ $bgColorField }}" value="{{ $currentBgColor }}" data-section="{{ $sectionKey }}" data-type="solid" style="width: 50px; padding: 2px;">
              <input type="text" class="form-control bg-color-text" value="{{ $currentBgColor }}" data-section="{{ $sectionKey }}" data-type="solid" placeholder="#ffffff">
            </div>
          </div>
          <!-- Gradient Color Inputs -->
          <div class="gradient-color-input {{ $currentBgType == 'solid' ? 'd-none' : '' }}" id="{{ $sectionKey }}_gradient">
            <div class="row">
              <div class="col-6">
                <label class="form-label fw-semibold">{{ __('admin.Start Color') }}</label>
                <div class="input-group input-group-sm">
                  <input type="color" class="form-control form-control-color bg-color-picker" name="{{ $bgGradientStartField }}" value="{{ $currentGradientStart }}" data-section="{{ $sectionKey }}" data-type="gradient-start" style="width: 50px; padding: 2px;">
                  <input type="text" class="form-control bg-color-text" value="{{ $currentGradientStart }}" data-section="{{ $sectionKey }}" data-type="gradient-start" placeholder="#667eea">
                </div>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">{{ __('admin.End Color') }}</label>
                <div class="input-group input-group-sm">
                  <input type="color" class="form-control form-control-color bg-color-picker" name="{{ $bgGradientEndField }}" value="{{ $currentGradientEnd }}" data-section="{{ $sectionKey }}" data-type="gradient-end" style="width: 50px; padding: 2px;">
                  <input type="text" class="form-control bg-color-text" value="{{ $currentGradientEnd }}" data-section="{{ $sectionKey }}" data-type="gradient-end" placeholder="#764ba2">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

<style>
  .section-toggle {
    width: 3rem;
    height: 1.5rem;
  }
  .section-toggle:checked {
    background-color: #696cff;
    border-color: #696cff;
  }
  .repeater-item {
    position: relative;
    transition: all 0.2s ease;
  }
  .repeater-item:hover {
    border-color: #696cff !important;
  }
  .icon-select option {
    font-family: inherit;
  }
  .empty-state {
    border: 2px dashed #e9ecef;
    border-radius: 0.5rem;
  }
</style>
