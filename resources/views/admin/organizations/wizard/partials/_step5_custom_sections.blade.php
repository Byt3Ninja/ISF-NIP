@php
  $org = $organization ?? null;
  $sections = $customSections ?? collect();
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';
  $maxSections = 10;
@endphp

<!-- Custom Sections Header -->
<div class="card mb-4 border-0 shadow-sm">
  <div class="card-header bg-white border-bottom-0 py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-1 fw-bold">
          <i class="ti ti-layout-grid me-2 text-primary"></i>{{ __('admin.Custom Sections') }}
        </h5>
        <small class="text-muted">{{ __('admin.Add rich content sections to your landing page') }}</small>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-label-info" id="sectionCount">
          <span id="currentCount">{{ $sections->count() }}</span>/{{ $maxSections }} {{ __('admin.sections') }}
        </span>
        <button type="button" class="btn btn-sm btn-primary" id="addSectionBtn" {{ $sections->count() >= $maxSections ? 'disabled' : '' }}>
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Custom Section') }}
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Existing Sections Container -->
<div id="customSectionsContainer">
  @forelse($sections as $index => $section)
    <div class="card mb-3 border shadow-sm custom-section-card" data-section-id="{{ $section->id }}" data-index="{{ $index }}">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <span class="drag-handle me-3 text-muted cursor-grab" style="cursor: grab;">
            <i class="ti ti-grip-vertical fs-5"></i>
          </span>
          <div>
            <h6 class="mb-0 fw-bold section-title-display">{{ $section->title }}</h6>
            <small class="text-muted">
              <span class="badge bg-label-{{ $section->visibility == 'public' ? 'success' : ($section->visibility == 'participants' ? 'info' : 'warning') }}">
                <i class="ti ti-{{ $section->visibility == 'public' ? 'world' : ($section->visibility == 'participants' ? 'users' : 'lock') }} me-1"></i>
                {{ __('admin.' . $section->visibility) }}
              </span>
            </small>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-icon btn-outline-primary preview-section-btn" title="{{ __('admin.Preview') }}">
            <i class="ti ti-eye"></i>
          </button>
          <button type="button" class="btn btn-sm btn-icon btn-outline-info edit-section-btn" title="{{ __('admin.Edit') }}">
            <i class="ti ti-pencil"></i>
          </button>
          <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-section-btn" title="{{ __('admin.Delete') }}">
            <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>
      <div class="card-body section-form-container" style="display: none;">
        @include('admin.organizations.wizard.partials._step5_section_form', ['section' => $section, 'index' => $index])
      </div>
      <!-- Hidden inputs for existing sections when collapsed -->
      <div class="section-hidden-inputs">
        <input type="hidden" name="custom_sections[{{ $index }}][id]" value="{{ $section->id }}">
        <input type="hidden" name="custom_sections[{{ $index }}][title]" value="{{ $section->title }}" class="hidden-title">
        <input type="hidden" name="custom_sections[{{ $index }}][content]" value="{{ $section->content }}" class="hidden-content">
        <input type="hidden" name="custom_sections[{{ $index }}][visibility]" value="{{ $section->visibility }}" class="hidden-visibility">
        <input type="hidden" name="custom_sections[{{ $index }}][bg_type]" value="{{ $section->bg_type }}" class="hidden-bg-type">
        <input type="hidden" name="custom_sections[{{ $index }}][bg_color]" value="{{ $section->bg_color }}" class="hidden-bg-color">
        <input type="hidden" name="custom_sections[{{ $index }}][bg_gradient_start]" value="{{ $section->bg_gradient_start }}" class="hidden-bg-gradient-start">
        <input type="hidden" name="custom_sections[{{ $index }}][bg_gradient_end]" value="{{ $section->bg_gradient_end }}" class="hidden-bg-gradient-end">
        <input type="hidden" name="custom_sections[{{ $index }}][sort_order]" value="{{ $section->sort_order }}" class="hidden-sort-order">
      </div>
    </div>
  @empty
    <div class="text-center py-5 empty-state" id="sectionsEmptyState">
      <div class="avatar avatar-lg bg-label-primary mb-3">
        <span class="avatar-initial"><i class="ti ti-layout-grid fs-2"></i></span>
      </div>
      <h5 class="text-muted mb-2">{{ __('admin.No custom sections yet') }}</h5>
      <p class="text-muted mb-3">{{ __('admin.Add custom sections to enrich your landing page') }}</p>
      <button type="button" class="btn btn-primary" id="addFirstSectionBtn">
        <i class="ti ti-plus me-1"></i>{{ __('admin.Add Your First Section') }}
      </button>
    </div>
  @endforelse
</div>

<!-- Section Preview Modal -->
<div class="modal fade" id="sectionPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.Section Preview') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="sectionPreviewContent">
        <!-- Preview content will be injected here -->
      </div>
    </div>
  </div>
</div>

<!-- Section Template (hidden, used for adding new sections) -->
<template id="sectionTemplate">
  <div class="card mb-3 border shadow-sm custom-section-card" data-section-id="" data-index="__INDEX__">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <span class="drag-handle me-3 text-muted" style="cursor: grab;">
          <i class="ti ti-grip-vertical fs-5"></i>
        </span>
        <div>
          <h6 class="mb-0 fw-bold section-title-display">{{ __('admin.New Section') }}</h6>
          <small class="text-muted">
            <span class="badge bg-label-success">
              <i class="ti ti-world me-1"></i>{{ __('admin.public') }}
            </span>
          </small>
        </div>
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-icon btn-outline-primary preview-section-btn" title="{{ __('admin.Preview') }}">
          <i class="ti ti-eye"></i>
        </button>
        <button type="button" class="btn btn-sm btn-icon btn-outline-info edit-section-btn" title="{{ __('admin.Edit') }}">
          <i class="ti ti-pencil"></i>
        </button>
        <button type="button" class="btn btn-sm btn-icon btn-outline-danger delete-section-btn" title="{{ __('admin.Delete') }}">
          <i class="ti ti-trash"></i>
        </button>
      </div>
    </div>
    <div class="card-body section-form-container">
      @include('admin.organizations.wizard.partials._step5_section_form', ['section' => null, 'index' => '__INDEX__'])
    </div>
    <!-- Hidden inputs -->
    <div class="section-hidden-inputs">
      <input type="hidden" name="custom_sections[__INDEX__][id]" value="">
      <input type="hidden" name="custom_sections[__INDEX__][title]" value="" class="hidden-title">
      <input type="hidden" name="custom_sections[__INDEX__][content]" value="" class="hidden-content">
      <input type="hidden" name="custom_sections[__INDEX__][visibility]" value="public" class="hidden-visibility">
      <input type="hidden" name="custom_sections[__INDEX__][bg_type]" value="solid" class="hidden-bg-type">
      <input type="hidden" name="custom_sections[__INDEX__][bg_color]" value="#ffffff" class="hidden-bg-color">
      <input type="hidden" name="custom_sections[__INDEX__][bg_gradient_start]" value="#ffffff" class="hidden-bg-gradient-start">
      <input type="hidden" name="custom_sections[__INDEX__][bg_gradient_end]" value="#f8f9fa" class="hidden-bg-gradient-end">
      <input type="hidden" name="custom_sections[__INDEX__][sort_order]" value="__INDEX__" class="hidden-sort-order">
    </div>
  </div>
</template>

<style>
  .custom-section-card {
    transition: all 0.2s ease;
  }
  .custom-section-card:hover {
    border-color: #696cff !important;
  }
  .custom-section-card.editing {
    border-color: #696cff !important;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15) !important;
  }
  .drag-handle {
    cursor: grab;
  }
  .drag-handle:active {
    cursor: grabbing;
  }
  .sortable-ghost {
    opacity: 0.4;
  }
  .sortable-chosen {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  }
  .empty-state {
    border: 2px dashed #e9ecef;
    border-radius: 0.5rem;
    background: #fafafa;
  }
  .section-form-container {
    background: #f8f7fa;
    border-top: 1px solid #e9ecef;
  }
</style>
