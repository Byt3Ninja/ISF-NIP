@php
  $sectionData = $section ?? null;
  $idx = $index ?? '__INDEX__';

  // Get values with proper fallbacks
  $title = old("custom_sections.{$idx}.title", $sectionData->title ?? '');
  $content = old("custom_sections.{$idx}.content", $sectionData->content ?? '');
  $visibility = old(
    "custom_sections.{$idx}.visibility",
    $sectionData->visibility ?? ($sectionData ? 'public' : '')
  );
@endphp

<div class="section-form-content" data-section-index="{{ $idx }}">
  <div class="row">
    <!-- Section Title -->
    <div class="col-md-12 mb-3">
      <label class="form-label fw-semibold">
        {{ __('admin.Section Title') }}
        <span class="text-danger">*</span>
      </label>
      <input
        type="text"
        class="form-control section-title-input @error("custom_sections.{$idx}.title") is-invalid @enderror"
        name="custom_sections[{{ $idx }}][title]"
        value="{{ $title }}"
        placeholder="{{ __('admin.Enter section title') }}"
        maxlength="255"
      />
      {{-- <div class="invalid-feedback title-feedback"></div> --}}
      @error("custom_sections.{$idx}.title")
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    <!-- Section Content (Rich Text Editor) -->
    <div class="col-md-12 mb-3">
      <label class="form-label fw-semibold">
        {{ __('admin.Section Content') }}
        <span class="text-danger">*</span>
      </label>
      <div
        id="content_{{ $idx }}"
        class="quill-editor @error("custom_sections.{$idx}.content") is-invalid @enderror"
        data-index="{{ $idx }}"
        data-placeholder="{{ __('admin.Enter section content') }}"
        style="min-height: 200px"
      ></div>
      <input
        type="hidden"
        name="custom_sections[{{ $idx }}][content]"
        class="hidden-content"
        value="{{ $content }}"
      />
      {{-- <div class="invalid-feedback content-feedback"></div> --}}
      @error("custom_sections.{$idx}.content")
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
      <small class="text-muted">
        {{ __('admin.Maximum 10,000 characters') }}
      </small>
    </div>

    <!-- Visibility Options -->
    <div class="col-md-12 mb-3">
      <label class="form-label fw-semibold">
        {{ __('admin.Visibility') }}
        <span class="text-danger">*</span>
      </label>
      <div class="row">
        <div class="col-12 col-lg-4">
           <!-- Public Option -->
          <div class="form-check custom-option custom-option-basic {{ $visibility == 'public' ? 'checked' : '' }}">
            <label class="form-check-label custom-option-content" for="visibility_public_{{ $idx }}">
              <input
                class="form-check-input visibility-radio"
                type="radio"
                name="custom_sections[{{ $idx }}][visibility]"
                id="visibility_public_{{ $idx }}"
                value="public"
                {{ $visibility == 'public' ? 'checked' : '' }}
              />
              <span class="custom-option-header">
                <span class="h6 mb-0">{{ __('admin.Public') }}</span>
                <small class="text-muted"></small>
              </span>
              <span class="custom-option-body">
                <small>{{ __('admin.Visible to everyone') }}</small>
              </span>
            </label>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <!-- Participants Option -->
          <div class="form-check custom-option custom-option-basic {{ $visibility == 'participants' ? 'checked' : '' }}">
            <label class="form-check-label custom-option-content" for="visibility_participants_{{ $idx }}">
              <input
                class="form-check-input visibility-radio"
                type="radio"
                name="custom_sections[{{ $idx }}][visibility]"
                id="visibility_participants_{{ $idx }}"
                value="participants"
                {{ $visibility == 'participants' ? 'checked' : '' }}
              />
              <span class="custom-option-header">
                <span class="h6 mb-0">{{ __('admin.Participants') }}</span>
                <small class="text-muted"></small>
              </span>
              <span class="custom-option-body">
                <small>{{ __('admin.Organization members only') }}</small>
              </span>
            </label>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <!-- Admins Only Option -->
          <div class="form-check custom-option custom-option-basic {{ $visibility == 'admins_only' ? 'checked' : '' }}">
            <label class="form-check-label custom-option-content" for="visibility_admins_{{ $idx }}">
              <input
                class="form-check-input visibility-radio"
                type="radio"
                name="custom_sections[{{ $idx }}][visibility]"
                id="visibility_admins_{{ $idx }}"
                value="admins_only"
                {{ $visibility == 'admins_only' ? 'checked' : '' }}
              />
              <span class="custom-option-header">
                <span class="h6 mb-0">{{ __('admin.Admins Only') }}</span>
                <small class="text-muted"></small>
              </span>
              <span class="custom-option-body">
                <small>{{ __('admin.Administrators only') }}</small>
              </span>
            </label>
          </div>
        </div>
      </div>
      {{-- <div class="invalid-feedback visibility-feedback"></div> --}}
      @error("custom_sections.{$idx}.visibility")
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>