@php
  $orgPrograms = $orgPrograms ?? ($org ? $org->cycles()->whereNull('deleted_at')->with(['focusAreas', 'benefits'])->orderBy('created_at', 'desc')->get() : collect());
  $focusAreaOptions = $focusAreaOptions ?? \App\StaticCategory::all();
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';

  $benefitIcons = [
    'ti-cash' => 'Funding',
    'ti-building' => 'Workspace',
    'ti-users' => 'Mentors',
    'ti-bulb' => 'Idea',
    'ti-rocket' => 'Rocket',
    'ti-world' => 'Network',
    'ti-certificate' => 'Certificate',
    'ti-presentation' => 'Pitch',
    'ti-flask' => 'R&D',
    'ti-handshake' => 'Partners',
    'ti-key' => 'IP',
    'ti-trending-up' => 'Scale',
    'ti-heart' => 'Community',
    'ti-checkup-list' => 'MVP',
  ];
@endphp

<!-- Programs Section -->
<div class="row" id="programsContent">
  <div class="col-12 mb-4">
    <div class="card border shadow-none">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check form-switch">
            <input
              type="checkbox"
              class="form-check-input"
              id="programs_enabled"
              name="programs_enabled"
              value="1"
              {{ old('programs_enabled', $landingPage->programs_enabled ?? true) ? 'checked' : '' }}
              data-section="programs"
            />
            <label class="form-check-label" for="programs_enabled">
              {{ __('admin.Enable Section') }}
            </label>
          </div>
        </div>

        <!-- Section Header Fields -->
        <div class="row mb-4">
          <div class="col-md-6 mb-3">
            <label class="form-label" for="programs_title">{{ __('admin.Section Title') }}</label>
            <input type="text" class="form-control" id="programs_title" name="programs_title" 
                   value="{{ old('programs_title', $landingPage->programs_title ?? '') }}" placeholder="{{ __('admin.Enter section title') }}"
                   maxlength="50">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label" for="programs_subtitle">{{ __('admin.Section Subtitle') }}</label>
            <input type="text" class="form-control" id="programs_subtitle" name="programs_subtitle" 
                   value="{{ old('programs_subtitle', $landingPage->programs_subtitle ?? '') }}" placeholder="{{ __('admin.Enter section subtitle') }}"
                   maxlength="50">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label" for="programs_description">{{ __('admin.Section Description') }}</label>
            <textarea class="form-control" id="programs_description" name="programs_description" rows="2"
                      placeholder="{{ __('admin.Enter section description') }}"
                      maxlength="100">{{ old('programs_description', $landingPage->programs_description ?? '') }}</textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <!-- Configuration Bar -->
    <div class="d-flex justify-content-between align-items-end mb-4 px-2">
      <div>
        <h6 class="mb-1">{{ __('admin.Program Cards') }}</h6>
        <p class="text-muted small mb-0">{{ __('admin.edit_landing_programs_cards_desc') }}</p>
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('admin.Display Order') }}</label>
        <select class="form-select form-select-sm" name="programs_order_by">
          <option value="newest" {{ old('programs_order_by', $landingPage->programs_order_by ?? 'newest') == 'newest' ? 'selected' : '' }}>{{ __('admin.Newest First') }}</option>
          <option value="oldest" {{ old('programs_order_by', $landingPage->programs_order_by ?? '') == 'oldest' ? 'selected' : '' }}>{{ __('admin.Oldest First') }}</option>
          <option value="custom" {{ old('programs_order_by', $landingPage->programs_order_by ?? '') == 'custom' ? 'selected' : '' }}>{{ __('admin.Custom Order') }}</option>
        </select>
      </div>
    </div>

    <!-- Programs List - Draggable -->
    <div id="programsLandingContainer" class="sortable-container">
      @forelse($orgPrograms as $index => $program)
        <div class="program-item card border shadow-none mb-3 {{ $program->landing_visible ? 'border-primary' : 'border-light opacity-75' }}" data-program-id="{{ $program->id }}" data-index="{{ $index }}">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center gap-4">
              <div class="d-flex align-items-center gap-3 me-4">
                <div class="drag-handle text-muted cursor-move fs-4" title="Drag to reorder">
                  <i class="ti ti-grip-vertical"></i>
                </div>
                <div>
                  <h6 class="card-title mb-0 mt-1">{{ $program->title }}</h6>
                  {{-- <span class="badge bg-label-{{ $program->type == 'Hackathon' ? 'warning' : 'info' }} rounded-pill fs-6">
                    {{ __('cycles.' . ($program->type ?? 'General_Program')) }}
                  </span> --}}
                </div>
              </div>
              
              <div class="ms-auto position-relative">
                <div class="form-check form-switch d-flex align-items-center">
                  <input class="form-check-input program-visible-toggle" type="checkbox" 
                         name="programs_visibility[{{ $index }}]" value="{{ $program->id }}" 
                         id="visible{{ $program->id }}" {{ $program->landing_visible ? 'checked' : '' }}>
                  <label class="form-check-label ms-2 {{ $program->landing_visible ? 'invisible' : '' }}" for="visible{{ $program->id }}">
                    {{ __('admin.visible') }}
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center border-light border-dashed rounded py-5">
          <div class="mb-3"><i class="ti ti-packages fs-1 text-muted"></i></div>
          <p class="small text-muted mb-0">{{ __('admin.No_Programs_Message') }}</p>
        </div>
      @endforelse
    </div>

    {{-- Hidden fields for order (populated by JS) --}}
    <input type="hidden" name="programs_landing_order" id="programs_landing_order" value="">
  </div>
</div>
