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

@push('scripts')
<script>
@if($isEdit && $maxStep >= 5)
(function() {
  const maxSections = 10;
  let sectionIndex = {{ $customSections->count() ?? 0 }};
  let tinyMCEInitialized = {};

  // Initialize Sortable for drag & drop
  const container = document.getElementById('customSectionsContainer');
  if (container) {
    new Sortable(container, {
      handle: '.drag-handle',
      animation: 150,
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      onEnd: function(evt) {
        updateSortOrders();
        saveReorder();
      }
    });
  }

  // Initialize TinyMCE for existing editors
  function initTinyMCE(selector) {
    if (typeof tinymce === 'undefined') {
      console.warn('TinyMCE not loaded');
      return;
    }

    tinymce.init({
      selector: selector,
      height: 250,
      menubar: false,
      plugins: 'lists link image paste',
      toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link | removeformat',
      content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 14px; }',
      directionality: '{{ $l == "ar" ? "rtl" : "ltr" }}',
      branding: false,
      promotion: false,
      setup: function(editor) {
        editor.on('change', function() {
          editor.save();
          syncHiddenInputs($(editor.getElement()).closest('.custom-section-card'));
        });
      }
    });
  }

  // Initialize existing editors on page load
  setTimeout(function() {
    initTinyMCE('.rich-text-editor');
  }, 500);

  // Add Section Button
  $('#addSectionBtn, #addFirstSectionBtn').on('click', function() {
    console.log('clicked')
    if (sectionIndex >= maxSections) {
      toastr.warning('{{ __("admin.Maximum 10 sections allowed") }}');
      return;
    }

    addNewSection();
  });

  function addNewSection() {
    // Hide empty state
    $('#sectionsEmptyState').hide();

    // Get template and replace index
    const template = document.getElementById('sectionTemplate').innerHTML;
    const html = template.replace(/__INDEX__/g, sectionIndex);

    // Add to container
    $('#customSectionsContainer').append(html);

    // Initialize TinyMCE for new section
    setTimeout(function() {
      initTinyMCE('#customSectionsContainer .custom-section-card:last-child .rich-text-editor');
    }, 100);

    // Update counter
    sectionIndex++;
    updateSectionCount();

    // Auto-expand new section for editing
    const newCard = $('#customSectionsContainer .custom-section-card:last-child');
    newCard.find('.section-form-container').show();
    newCard.find('.section-hidden-inputs').hide();
    newCard.addClass('editing');
  }

  // Edit Section Button
  $(document).on('click', '.edit-section-btn', function() {
    const card = $(this).closest('.custom-section-card');
    toggleSectionEdit(card, true);
  });

  // Done Editing Button
  $(document).on('click', '.done-editing-btn', function() {
    const card = $(this).closest('.custom-section-card');
    toggleSectionEdit(card, false);
  });

  function toggleSectionEdit(card, isEditing) {
    if (isEditing) {
      card.find('.section-form-container').slideDown();
      card.find('.section-hidden-inputs').hide();
      card.addClass('editing');

      // Re-initialize TinyMCE if needed
      const textarea = card.find('.rich-text-editor');
      if (textarea.length && !tinymce.get(textarea.attr('id'))) {
        setTimeout(function() {
          initTinyMCE('#' + textarea.attr('id'));
        }, 100);
      }
    } else {
      // Save TinyMCE content
      card.find('.rich-text-editor').each(function() {
        const editor = tinymce.get(this.id);
        if (editor) {
          editor.save();
        }
      });

      // Sync to hidden inputs
      syncHiddenInputs(card);

      // Update display title
      const title = card.find('.section-title-input').val() || '{{ __("admin.Untitled Section") }}';
      card.find('.section-title-display').text(title);

      // Update visibility badge
      const visibility = card.find('.visibility-radio:checked').val() || 'public';
      const badgeClass = visibility === 'public' ? 'success' : (visibility === 'participants' ? 'info' : 'warning');
      const iconClass = visibility === 'public' ? 'world' : (visibility === 'participants' ? 'users' : 'lock');
      const visibilityLabels = {
        'public': '{{ __("admin.public") }}',
        'participants': '{{ __("admin.participants") }}',
        'admins_only': '{{ __("admin.admins_only") }}'
      };
      card.find('.badge').removeClass('bg-label-success bg-label-info bg-label-warning')
        .addClass('bg-label-' + badgeClass)
        .html('<i class="ti ti-' + iconClass + ' me-1"></i>' + visibilityLabels[visibility]);

      card.find('.section-form-container').slideUp();
      card.find('.section-hidden-inputs').show();
      card.removeClass('editing');
    }
  }

  function syncHiddenInputs(card) {
    const form = card.find('.section-form-container');
    const hidden = card.find('.section-hidden-inputs');

    hidden.find('.hidden-title').val(form.find('.section-title-input').val());
    hidden.find('.hidden-content').val(form.find('.section-content-input').val());
    hidden.find('.hidden-visibility').val(form.find('.visibility-radio:checked').val());
    hidden.find('.hidden-bg-type').val(form.find('.section-bg-type-select').val());
    hidden.find('.hidden-bg-color').val(form.find('.section-bg-color').val());
    hidden.find('.hidden-bg-gradient-start').val(form.find('.section-bg-gradient-start').val());
    hidden.find('.hidden-bg-gradient-end').val(form.find('.section-bg-gradient-end').val());
  }

  // Delete Section Button
  $(document).on('click', '.delete-section-btn', function() {
    const card = $(this).closest('.custom-section-card');
    const sectionId = card.data('section-id');

    if (confirm('{{ __("admin.Are you sure you want to delete this section?") }}')) {
      if (sectionId) {
        // Delete from server
        $.ajax({
          url: '{{ $isEdit ? route("organization.wizard.step5.delete", [$organization->id, "__ID__"]) : "" }}'.replace('__ID__', sectionId),
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              card.slideUp(300, function() {
                $(this).remove();
                updateSectionCount();
                checkEmptyState();
              });
              toastr.success(response.message);
            } else {
              toastr.error(response.message);
            }
          },
          error: function() {
            toastr.error('{{ __("admin.Error deleting section") }}');
          }
        });
      } else {
        // Just remove from DOM (new unsaved section)
        card.slideUp(300, function() {
          $(this).remove();
          updateSectionCount();
          checkEmptyState();
        });
      }
    }
  });

  // Preview Section Button
  $(document).on('click', '.preview-section-btn', function() {
    const card = $(this).closest('.custom-section-card');

    // Save TinyMCE content first
    card.find('.rich-text-editor').each(function() {
      const editor = tinymce.get(this.id);
      if (editor) {
        editor.save();
      }
    });

    const title = card.find('.section-title-input').val() || card.find('.hidden-title').val() || '{{ __("admin.Untitled Section") }}';
    const content = card.find('.section-content-input').val() || card.find('.hidden-content').val() || '';
    const bgType = card.find('.section-bg-type-select').val() || card.find('.hidden-bg-type').val() || 'solid';
    const bgColor = card.find('.section-bg-color').val() || card.find('.hidden-bg-color').val() || '#ffffff';
    const bgGradientStart = card.find('.section-bg-gradient-start').val() || card.find('.hidden-bg-gradient-start').val() || '#ffffff';
    const bgGradientEnd = card.find('.section-bg-gradient-end').val() || card.find('.hidden-bg-gradient-end').val() || '#f8f9fa';

    let bgStyle = '';
    if (bgType === 'gradient') {
      bgStyle = 'background: linear-gradient(135deg, ' + bgGradientStart + ' 0%, ' + bgGradientEnd + ' 100%);';
    } else {
      bgStyle = 'background-color: ' + bgColor + ';';
    }

    const previewHtml = `
      <div class="p-4 rounded" style="${bgStyle}">
        <h4 class="mb-3">${title}</h4>
        <div class="section-content">${content}</div>
      </div>
    `;

    $('#sectionPreviewContent').html(previewHtml);
    new bootstrap.Modal(document.getElementById('sectionPreviewModal')).show();
  });

  // Background type toggle
  $(document).on('change', '.section-bg-type-select', function() {
    const idx = $(this).data('index');
    const card = $(this).closest('.custom-section-card');

    if ($(this).val() === 'gradient') {
      card.find('.section-solid-input').addClass('d-none');
      card.find('.section-gradient-input').removeClass('d-none');
    } else {
      card.find('.section-solid-input').removeClass('d-none');
      card.find('.section-gradient-input').addClass('d-none');
    }
  });

  // Color picker sync
  $(document).on('input', '.section-bg-color', function() {
    $(this).siblings('.section-bg-color-text').val($(this).val());
  });
  $(document).on('input', '.section-bg-color-text', function() {
    $(this).siblings('.section-bg-color').val($(this).val());
  });
  $(document).on('input', '.section-bg-gradient-start', function() {
    $(this).siblings('.section-bg-gradient-start-text').val($(this).val());
  });
  $(document).on('input', '.section-bg-gradient-start-text', function() {
    $(this).siblings('.section-bg-gradient-start').val($(this).val());
  });
  $(document).on('input', '.section-bg-gradient-end', function() {
    $(this).siblings('.section-bg-gradient-end-text').val($(this).val());
  });
  $(document).on('input', '.section-bg-gradient-end-text', function() {
    $(this).siblings('.section-bg-gradient-end').val($(this).val());
  });

  // Title input sync
  $(document).on('input', '.section-title-input', function() {
    const card = $(this).closest('.custom-section-card');
    card.find('.section-title-display').text($(this).val() || '{{ __("admin.New Section") }}');
  });

  function updateSectionCount() {
    const count = $('.custom-section-card').length;
    $('#currentCount').text(count);

    if (count >= maxSections) {
      $('#addSectionBtn').prop('disabled', true);
    } else {
      $('#addSectionBtn').prop('disabled', false);
    }
  }

  function checkEmptyState() {
    if ($('.custom-section-card').length === 0) {
      $('#sectionsEmptyState').show();
    }
  }

  function updateSortOrders() {
    $('.custom-section-card').each(function(index) {
      $(this).find('.hidden-sort-order').val(index);
      $(this).find('input[name$="[sort_order]"]').val(index);
    });
  }

  function saveReorder() {
    const orderedIds = [];
    $('.custom-section-card').each(function() {
      const id = $(this).data('section-id');
      if (id) {
        orderedIds.push(id);
      }
    });

    if (orderedIds.length === 0) return;

    $.ajax({
      url: '{{ $isEdit ? route("organization.wizard.step5.reorder", $organization->id) : "" }}',
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: {
        ordered_ids: orderedIds
      },
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
        }
      },
      error: function() {
        toastr.error('{{ __("admin.Error reordering sections") }}');
      }
    });
  }

  // Save Step 5
  $('#saveStep5').on('click', function() {
    submitStep5Form();
  });

  // Save as Draft Step 5
  $('#saveAsDraftStep5').on('click', function() {
    // Close all editing sections first
    $('.custom-section-card.editing').each(function() {
      toggleSectionEdit($(this), false);
    });

    $('#draftForm input[name="current_step"]').val(5);
    copyStep5DataToDraft();
    $('#draftForm').submit();
  });

  // Publish Step 5
  $('#publishStep5').on('click', function() {
    // First save, then publish
    submitStep5Form(function() {
      $('#publishForm').submit();
    });
  });

  function submitStep5Form(callback) {
    // Close all editing sections first and sync data
    $('.custom-section-card.editing').each(function() {
      toggleSectionEdit($(this), false);
    });

    // Build form data
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PUT');

    // Collect section data
    $('.custom-section-card').each(function(index) {
      const card = $(this);
      const sectionId = card.data('section-id') || '';

      formData.append('custom_sections[' + index + '][id]', sectionId);
      formData.append('custom_sections[' + index + '][title]', card.find('.hidden-title').val() || card.find('.section-title-input').val() || '');
      formData.append('custom_sections[' + index + '][content]', card.find('.hidden-content').val() || card.find('.section-content-input').val() || '');
      formData.append('custom_sections[' + index + '][visibility]', card.find('.hidden-visibility').val() || card.find('.visibility-radio:checked').val() || 'public');
      formData.append('custom_sections[' + index + '][bg_type]', card.find('.hidden-bg-type').val() || card.find('.section-bg-type-select').val() || 'solid');
      formData.append('custom_sections[' + index + '][bg_color]', card.find('.hidden-bg-color').val() || card.find('.section-bg-color').val() || '#ffffff');
      formData.append('custom_sections[' + index + '][bg_gradient_start]', card.find('.hidden-bg-gradient-start').val() || card.find('.section-bg-gradient-start').val() || '#ffffff');
      formData.append('custom_sections[' + index + '][bg_gradient_end]', card.find('.hidden-bg-gradient-end').val() || card.find('.section-bg-gradient-end').val() || '#f8f9fa');
      formData.append('custom_sections[' + index + '][sort_order]', index);
    });

    $.ajax({
      url: '{{ $isEdit ? route("organization.wizard.update.step5", $organization->id) : "" }}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        toastr.success('{{ __("admin.Custom sections saved successfully") }}');
        if (callback) {
          callback();
        } else {
          // Reload page to reflect changes
          window.location.reload();
        }
      },
      error: function(xhr) {
        if (xhr.status === 422) {
          const errors = xhr.responseJSON.errors;
          Object.keys(errors).forEach(function(key) {
            toastr.error(errors[key][0]);
          });
        } else {
          toastr.error('{{ __("admin.Error saving custom sections") }}');
        }
      }
    });
  }

  function copyStep5DataToDraft() {
    const draftForm = $('#draftForm');

    // Remove previous Step 5 inputs
    draftForm.find('[name^="custom_sections"]').remove();

    // Copy section data
    $('.custom-section-card').each(function(index) {
      const card = $(this);
      const prefix = 'custom_sections[' + index + ']';

      draftForm.append('<input type="hidden" name="' + prefix + '[id]" value="' + (card.data('section-id') || '') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[title]" value="' + (card.find('.hidden-title').val() || '') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[content]" value="' + (card.find('.hidden-content').val() || '') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[visibility]" value="' + (card.find('.hidden-visibility').val() || 'public') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[bg_type]" value="' + (card.find('.hidden-bg-type').val() || 'solid') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[bg_color]" value="' + (card.find('.hidden-bg-color').val() || '#ffffff') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[bg_gradient_start]" value="' + (card.find('.hidden-bg-gradient-start').val() || '#ffffff') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[bg_gradient_end]" value="' + (card.find('.hidden-bg-gradient-end').val() || '#f8f9fa') + '">');
      draftForm.append('<input type="hidden" name="' + prefix + '[sort_order]" value="' + index + '">');
    });
  }
})();
@endif
</script>
@endpush
