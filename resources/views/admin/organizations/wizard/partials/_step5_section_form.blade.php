@php
  $sectionData = $section ?? null;
  $idx = $index ?? '__INDEX__';
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';

  // Get values
  $title = old("custom_sections.{$idx}.title", $sectionData->title ?? '');
  $content = old("custom_sections.{$idx}.content", $sectionData->content ?? '');
  $visibility = old("custom_sections.{$idx}.visibility", $sectionData->visibility ?? ($sectionData ? 'public' : ''));
@endphp

<div class="section-form py-3">
  <div class="row">
    <!-- Section Title -->
    <div class="col-md-12 mb-3">
      <label class="form-label fw-semibold">
        {{ __('admin.Section Title') }} <span class="text-danger">*</span>
      </label>
      <input
        type="text"
        class="form-control section-title-input @error("custom_sections.{$idx}.title") is-invalid @enderror"
        name="custom_sections[{{ $idx }}][title]"
        value="{{ $title }}"
        placeholder="{{ __('admin.Enter section title') }}"
        maxlength="255"
      >
      @error("custom_sections.{$idx}.title")
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <!-- Section Content (Rich Text Editor) -->
    <div class="col-md-12 mb-3">
      <label class="form-label fw-semibold">
        {{ __('admin.Section Content') }} <span class="text-danger">*</span>
      </label>
      <textarea
        id="content_{{ $idx }}"
        class="form-control rich-text-editor section-content-input @error("custom_sections.{$idx}.content") is-invalid @enderror"
        name="custom_sections[{{ $idx }}][content]"
        rows="6"
        placeholder="{{ __('admin.Enter section content') }}"
        data-index="{{ $idx }}"
      >{{ $content }}</textarea>
      @error("custom_sections.{$idx}.content")
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
      <small class="text-muted">{{ __('admin.Maximum 10,000 characters') }}</small>
    </div>

    <!-- Visibility -->
    <div class="col-md-12 mb-3">
      <label class="form-label fw-semibold">
        {{ __('admin.Visibility') }} <span class="text-danger">*</span>
      </label>
      <div class="d-flex flex-wrap gap-3">
        <div class="form-check">
          <input
            class="form-check-input visibility-radio"
            type="radio"
            name="custom_sections[{{ $idx }}][visibility]"
            id="visibility_public_{{ $idx }}"
            value="public"
            {{ $visibility == 'public' ? 'checked' : '' }}
          >
          <label class="form-check-label" for="visibility_public_{{ $idx }}">
            <i class="ti ti-world me-1 text-success"></i>{{ __('admin.Public') }}
            <small class="text-muted d-block">{{ __('admin.Visible to everyone') }}</small>
          </label>
        </div>
        <div class="form-check">
          <input
            class="form-check-input visibility-radio"
            type="radio"
            name="custom_sections[{{ $idx }}][visibility]"
            id="visibility_participants_{{ $idx }}"
            value="participants"
            {{ $visibility == 'participants' ? 'checked' : '' }}
          >
          <label class="form-check-label" for="visibility_participants_{{ $idx }}">
            <i class="ti ti-users me-1 text-info"></i>{{ __('admin.Participants') }}
            <small class="text-muted d-block">{{ __('admin.Organization members only') }}</small>
          </label>
        </div>
        <div class="form-check">
          <input
            class="form-check-input visibility-radio"
            type="radio"
            name="custom_sections[{{ $idx }}][visibility]"
            id="visibility_admins_{{ $idx }}"
            value="admins_only"
            {{ $visibility == 'admins_only' ? 'checked' : '' }}
          >
          <label class="form-check-label" for="visibility_admins_{{ $idx }}">
            <i class="ti ti-lock me-1 text-warning"></i>{{ __('admin.Admins Only') }}
            <small class="text-muted d-block">{{ __('admin.Administrators only') }}</small>
          </label>
        </div>
      </div>
      @error("custom_sections.{$idx}.visibility")
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Done Button -->
  <div class="text-end pt-2 border-top mt-3">
    <button type="button" class="btn btn-sm btn-primary done-editing-btn">
      <i class="ti ti-check me-1"></i>{{ __('admin.Done') }}
    </button>
  </div>
</div>
