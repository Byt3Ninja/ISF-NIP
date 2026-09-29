@php
  $org          = $organization ?? null;
  $sections     = $customSections ?? collect();
  $maxSections  = 10;
  $sectionCount = $sections->count();

  $visibilityOptions = [
    'public'       => ['icon' => 'ti-world', 'label' => __('admin.Public'),      'desc' => __('admin.Visible to everyone')],
    'participants' => ['icon' => 'ti-users', 'label' => __('admin.Participants'), 'desc' => __('admin.Organization members only')],
    'admins_only'  => ['icon' => 'ti-lock',  'label' => __('admin.Admins Only'), 'desc' => __('admin.Administrators only')],
  ];
@endphp


{{-- ═══════════════════════════════════════════════════════════
     FORM
════════════════════════════════════════════════════════════ --}}
<form
  action="{{ route('organization.wizard.update.step5', $org->id) }}"
  method="POST"
  enctype="multipart/form-data"
  id="customSectionsForm"
  novalidate
>
  @csrf
  @method('PUT')
  <input type="hidden" name="redirect_to" value="landing_page" />
  <input type="hidden" name="active_tab"  value="customSections" />

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
      <h5 class="mb-0">{{ __('admin.Custom Sections') }}</h5>
      <small class="text-muted">{{ __('admin.Add rich content sections to your landing page') }}</small>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-label-primary fs-6">
        <span id="cs-current-count">{{ $sectionCount }}</span>/{{ $maxSections }} {{ __('admin.sections') }}
      </span>
      <button type="button" class="btn btn-outline-primary" id="cs-add-btn"
        {{ $sectionCount >= $maxSections ? 'disabled' : '' }}>
        <i class="ti ti-plus me-1"></i>{{ __('admin.Add Custom Section') }}
      </button>
      <button type="submit" class="btn btn-primary">
        <i class="ti ti-device-floppy me-1"></i>{{ __('admin.Save') }}
      </button>
    </div>
  </div>

  {{-- Sections container --}}
  <div id="cs-container">
    @foreach($sections as $i => $section)
      @php $vis = $section->visibility ?? 'public'; @endphp

      <div class="card mb-3 border shadow-sm cs-card"
           data-section-id="{{ $section->id }}"
           data-index="{{ $i }}">

        {{-- Card header --}}
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <span class="drag-handle text-muted" style="cursor:grab" title="{{ __('admin.Drag to reorder') }}">
              <i class="ti ti-grip-vertical fs-5"></i>
            </span>
            <h6 class="mb-0 cs-title-display">{{ $section->title ?: __('admin.Untitled Section') }}</h6>
          </div>
          <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-icon btn-outline-primary cs-preview-btn" title="{{ __('admin.Preview') }}">
              <i class="ti ti-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-icon btn-outline-danger cs-delete-btn" title="{{ __('admin.Delete') }}">
              <i class="ti ti-trash"></i>
            </button>
          </div>
        </div>

        {{-- Card body --}}
        <div class="card-body pb-3">

          {{-- Title field --}}
          <div class="mb-3 cs-field-group" data-field="title">
            <label class="form-label fw-semibold">{{ __('admin.Section Title') }} <span class="text-danger">*</span></label>
            <input type="text" class="form-control cs-title-input"
                   value="{{ $section->title }}"
                   placeholder="{{ __('admin.Enter section title') }}"
                   maxlength="255" autocomplete="off" />
            <input type="hidden" name="custom_sections[{{ $i }}][title]"
                   class="cs-hidden-title" value="{{ $section->title }}" />
            <div class="cs-error-msg text-danger small mt-1" style="display:none"></div>
          </div>

          {{-- Content field --}}
          <div class="mb-3 cs-field-group" data-field="content">
            <label class="form-label fw-semibold">{{ __('admin.Section Content') }} <span class="text-danger">*</span></label>
            <div class="cs-quill-editor" data-index="{{ $i }}"
                 data-placeholder="{{ __('admin.Enter section content') }}"
                 style="min-height:200px"></div>
            <input type="hidden" name="custom_sections[{{ $i }}][content]"
                   class="cs-hidden-content" value="{{ $section->content }}" />
            <small class="text-muted">{{ __('admin.Maximum 10,000 characters') }}</small>
            <div class="cs-error-msg text-danger small mt-1" style="display:none"></div>
          </div>

          {{-- Visibility field --}}
          <div class="mb-0 cs-field-group" data-field="visibility">
            <label class="form-label fw-semibold">{{ __('admin.Visibility') }} <span class="text-danger">*</span></label>
            <div class="row g-2">
              @foreach($visibilityOptions as $value => $opt)
                <div class="col-12 col-lg-4">
                  <div class="form-check custom-option custom-option-basic js-visibility-option {{ $vis === $value ? 'checked' : '' }}">
                    <label class="form-check-label custom-option-content" for="vis_{{ $value }}_{{ $i }}">
                      <input class="form-check-input cs-visibility-radio" type="radio"
                            id="vis_{{ $value }}_{{ $i }}" value="{{ $value }}"
                            name="custom_sections[{{ $i }}][visibility_radio]"
                            {{ $vis === $value ? 'checked' : '' }} />
                      <span class="custom-option-header">
                        <span class="h6 mb-0"><i class="ti {{ $opt['icon'] }} me-1"></i>{{ $opt['label'] }}</span>
                      </span>
                      <span class="custom-option-body"><small>{{ $opt['desc'] }}</small></span>
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
            <input type="hidden" name="custom_sections[{{ $i }}][visibility]"
                  class="cs-hidden-visibility" value="{{ $vis }}" />
            <div class="cs-error-msg text-danger small mt-1" style="display:none"></div>
          </div>

        </div>{{-- /.card-body --}}

        {{-- Persisted meta --}}
        <div class="d-none">
          <input type="hidden" name="custom_sections[{{ $i }}][id]"         value="{{ $section->id }}" />
          <input type="hidden" name="custom_sections[{{ $i }}][sort_order]" value="{{ $section->sort_order ?? $i }}" class="cs-sort-order" />
        </div>

      </div>{{-- /.cs-card --}}
    @endforeach
  </div>{{-- /#cs-container --}}

  {{-- Empty state --}}
  <div id="cs-empty-state" class="text-center py-5 border border-dashed rounded {{ $sectionCount > 0 ? 'd-none' : '' }}">
    <i class="ti ti-layout-grid text-muted fs-1 mb-2 d-block"></i>
    <h5 class="text-muted mb-1">{{ __('admin.No custom sections yet') }}</h5>
    <p class="text-muted small mb-3">{{ __('admin.Add custom sections to enrich your landing page') }}</p>
    <button type="button" class="btn btn-primary" id="cs-add-first-btn">
      <i class="ti ti-plus me-1"></i>{{ __('admin.Add Your First Section') }}
    </button>
  </div>

</form>


{{-- ═══════════════════════════════════════════════════════════
     JS CARD TEMPLATE
     Cloned by custom-sections.js when "Add Section" is clicked.
     __IDX__ is replaced with the real index by JS before insertion.
════════════════════════════════════════════════════════════ --}}
<template id="cs-card-template">
  <div class="card mb-3 border shadow-none cs-card" data-section-id="" data-index="__IDX__">

    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <span class="drag-handle text-muted" style="cursor:grab">
          <i class="ti ti-grip-vertical fs-5"></i>
        </span>
        <h6 class="mb-0 cs-title-display line-clamp-2 text-break">{{ __('admin.New Section') }}</h6>
      </div>
      <div class="d-flex gap-1 ms-4">
        <button type="button" class="btn btn-sm btn-icon btn-outline-primary cs-preview-btn" title="{{ __('admin.Preview') }}">
          <i class="ti ti-eye"></i>
        </button>
        <button type="button" class="btn btn-sm btn-icon btn-outline-danger cs-delete-btn" title="{{ __('admin.Delete') }}">
          <i class="ti ti-trash"></i>
        </button>
      </div>
    </div>

    <div class="card-body">

      {{-- Title field --}}
      <div class="mb-3 cs-field-group" data-field="title">
        <label class="form-label fw-semibold">{{ __('admin.Section Title') }} <span class="text-danger">*</span></label>
        <input type="text" class="form-control cs-title-input"
               value="" placeholder="{{ __('admin.Enter section title') }}"
               maxlength="255" autocomplete="off" />
        <input type="hidden" name="custom_sections[__IDX__][title]" class="cs-hidden-title" value="" />
        <div class="cs-error-msg text-danger small mt-1" style="display:none"></div>
      </div>

      {{-- Content field --}}
      <div class="mb-3 cs-field-group" data-field="content">
        <label class="form-label fw-semibold">{{ __('admin.Section Content') }} <span class="text-danger">*</span></label>
        <div class="cs-quill-editor" data-index="__IDX__"
             data-placeholder="{{ __('admin.Enter section content') }}"
             style="min-height:200px"></div>
        <input type="hidden" name="custom_sections[__IDX__][content]" class="cs-hidden-content" value="" />
        <small class="text-muted">{{ __('admin.Maximum 10,000 characters') }}</small>
        <div class="cs-error-msg text-danger small mt-1" style="display:none"></div>
      </div>

      {{-- Visibility field --}}
      <div class="mb-0 cs-field-group" data-field="visibility">
        <label class="form-label fw-semibold">{{ __('admin.Visibility') }} <span class="text-danger">*</span></label>
        <div class="row g-2">
          @foreach($visibilityOptions as $value => $opt)
            <div class="col-12 col-lg-4">
              <div class="form-check custom-option custom-option-basic {{ $value === 'public' ? 'checked' : '' }}">
                <label class="form-check-label custom-option-content" for="vis_{{ $value }}___IDX__">
                  <input class="form-check-input cs-visibility-radio" type="radio"
                         id="vis_{{ $value }}___IDX__" value="{{ $value }}"
                         {{ $value === 'public' ? 'checked' : '' }} />
                  <span class="custom-option-header">
                    <span class="h6 mb-0"><i class="ti {{ $opt['icon'] }} me-1"></i>{{ $opt['label'] }}</span>
                  </span>
                  <span class="custom-option-body"><small>{{ $opt['desc'] }}</small></span>
                </label>
              </div>
            </div>
          @endforeach
        </div>
        <input type="hidden" name="custom_sections[__IDX__][visibility]" class="cs-hidden-visibility" value="public" />
        <div class="cs-error-msg text-danger small mt-1" style="display:none"></div>
      </div>

    </div>{{-- /.card-body --}}

    <div class="d-none">
      <input type="hidden" name="custom_sections[__IDX__][id]"         value="" />
      <input type="hidden" name="custom_sections[__IDX__][sort_order]" value="__IDX__" class="cs-sort-order" />
    </div>

  </div>{{-- /.cs-card --}}
</template>


{{-- ═══════════════════════════════════════════════════════════
     PREVIEW MODAL
════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="cs-preview-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.Section Preview') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="cs-preview-body">
        <div class="text-center text-muted py-4">
          <i class="ti ti-eye-off fs-1 mb-2 d-block"></i>
          <p>{{ __('admin.No content to preview') }}</p>
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