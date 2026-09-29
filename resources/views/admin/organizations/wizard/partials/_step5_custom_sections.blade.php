@php
  $org = $organization ?? null;
  $sections = $customSections ?? collect();
  $maxSections = 10;
  $sectionCount = $sections->count();

  // Visibility configuration for consistent rendering
  $visibilityConfig = [
    'public' => ['class' => 'success', 'icon' => 'world', 'label' => __('admin.public')],
    'participants' => ['class' => 'info', 'icon' => 'users', 'label' => __('admin.participants')],
    'admins_only' => ['class' => 'warning', 'icon' => 'lock', 'label' => __('admin.admins_only')],
    'private' => ['class' => 'warning', 'icon' => 'lock', 'label' => __('admin.private')],
  ];
@endphp

<!-- Custom Sections Header -->
<div class="card mb-4 rounded-0 border-top border-bottom shadow-none">
  <div class="card-header py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h6 class="card-title mb-0">
          {{ __('admin.Add rich content sections to your landing page') }}
        </h6>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-label-info" id="sectionCount">
          <span id="currentCount">{{ $sectionCount }}</span>/{{ $maxSections }}
          {{ __('admin.sections') }}
        </span>
        <button
          type="button"
          class="btn btn-sm btn-primary"
          id="addSectionBtn"
          {{ $sectionCount >= $maxSections ? 'disabled' : '' }}
        >
          <i class="ti ti-plus me-1"></i>{{ __('admin.Add Custom Section') }}
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Existing Sections Container -->
<div id="customSectionsContainer">
  @forelse($sections as $index => $section)
    @php
      $visibility = $section->visibility ?? 'public';
      $config = $visibilityConfig[$visibility] ?? $visibilityConfig['public'];
    @endphp

    <div
      class="card mb-3 border shadow-sm custom-section-card"
      data-section-id="{{ $section->id }}"
      data-index="{{ $index }}"
    >
      <div class="card-header py-3 d-flex justify-content-between align-items-center bg-transparent">
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" style="cursor: grab">
            <i class="ti ti-grip-vertical fs-5"></i>
          </span>
          <div>
            <h6 class="mb-0 card-title section-title-display">
              {{ $section->title ?: __('admin.Untitled Section') }}
            </h6>
            {{-- <small class="text-muted">
              <span class="badge bg-label-{{ $config['class'] }}">
                <i class="ti ti-{{ $config['icon'] }} me-1"></i>
                {{ $config['label'] }}
              </span>
            </small> --}}
          </div>
        </div>
        <div class="d-flex gap-1">
          <button
            type="button"
            class="btn btn-sm btn-icon btn-outline-primary preview-section-btn"
            title="{{ __('admin.Preview') }}"
          >
            <i class="ti ti-eye"></i>
          </button>
          <button
            type="button"
            class="btn btn-sm btn-icon btn-outline-danger delete-section-btn"
            title="{{ __('admin.Delete') }}"
          >
            <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>

      <!-- Form Container - Always visible -->
      <div class="card-body">
        @include('admin.organizations.wizard.partials._step5_section_form', [
          'section' => $section,
          'index' => $index,
        ])
      </div>

      <!-- Hidden Inputs for Form Submission -->
      <div class="d-none">
        <input
          type="hidden"
          name="custom_sections[{{ $index }}][id]"
          value="{{ $section->id }}"
        />
        <input
          type="hidden"
          name="custom_sections[{{ $index }}][sort_order]"
          value="{{ $section->sort_order ?? $index }}"
          class="hidden-sort-order"
        />
      </div>
    </div>
  @empty
    <div class="text-center py-5 border border-light border-dashed rounded" id="sectionsEmptyState">
      <i class="ti ti-layout-grid text-muted fs-1 mb-2 d-block"></i>
      <h5 class="text-muted mb-1">{{ __('admin.No custom sections yet') }}</h5>
      <p class="text-muted small mb-3">
        {{ __('admin.Add custom sections to enrich your landing page') }}
      </p>
      <button type="button" class="btn btn-primary" id="addFirstSectionBtn">
        <i class="ti ti-plus me-1"></i>{{ __('admin.Add Your First Section') }}
      </button>
    </div>
  @endforelse
</div>

<!-- Section Preview Modal -->
<div class="modal fade" id="sectionPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.Section Preview') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="sectionPreviewContent">
        <div class="text-center text-muted py-4">
          <i class="ti ti-eye-off fs-1 mb-2 d-block"></i>
          <p>{{ __('admin.Select a section to preview') }}</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ __('admin.Close') }}
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Section Template -->
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
      <input type="hidden" name="custom_sections[__INDEX__][visibility]" value="" class="hidden-visibility">
      <input type="hidden" name="custom_sections[__INDEX__][sort_order]" value="__INDEX__" class="hidden-sort-order">
    </div>
  </div>
</template>
