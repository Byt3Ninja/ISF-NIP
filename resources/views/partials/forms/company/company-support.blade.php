@php
  $expertiseOptions = $areasOfExpertise ?? [];
  $initialExpertiseIds = $companyExpertiseIds ?? [];
@endphp

<div class="card">
  <div class="card-body">
    <h5 class="card-title border-bottom pb-2 mb-6">
      {{ __('startup.support_mentorship_title') }}
    </h5>

    <form id="expertiseForm" method="post" action="{{ route('company.profile_update_mine', $client->company->id ?? auth()->id()) }}">
      @csrf
      <input name="CompanyN" type="hidden" value="{{ \App\Enums\CompanyFormStep::ExpertiseTags->value }}">

      <div class="mb-4">
        <label class="form-label fw-semibold">
          {{ __('startup.support_and_mentorship_label') }}
          <span class="text-danger">*</span>
        </label>

        <!-- Widget Container -->
        <div id="expertiseWidget" class="position-relative border rounded-3 p-3 bg-card">

          <!-- Selected Tags Container -->
          <div id="expertiseTagsContainer" class="d-flex flex-wrap gap-2 mb-2"></div>

          <!-- Input Group -->
          <div class="position-relative">
            <input
              type="text"
              id="expertiseSearchInput"
              class="form-control border-0 bg-transparent shadow-none p-0"
              placeholder="{{ __('admin.select_expertises') }}..."
              autocomplete="off"
            >

            <!-- Dropdown List -->
            <ul id="expertiseDropdownList" class="dropdown-menu w-100" style="max-height: 200px; overflow-y: auto;"></ul>
          </div>
        </div>

        <input type="hidden" id="expertise_tags_input" name="expertise_tags">

        <div id="supportAreasHelp" class="form-text">
          {{ __('startup.support_and_mentorship_subtitle') }}
        </div>

        <!-- Error Message -->
        <div id="expertiseErr" class="mt-2 text-danger d-none">
          <i class="ti ti-alert-circle me-1"></i>
          <span id="expertiseErrText">{{ __('admin.this_field_is_required') }}</span>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" id="expertiseSubmitBtn" class="btn btn-primary px-4">
          {{ __('admin.Save') }}
        </button>
      </div>
    </form>

  </div>
</div>

@push('scripts')
<script>
(function () {
  const ALL_OPTIONS = @json($expertiseOptions);
  const INITIAL_IDS = @json($initialExpertiseIds);
  const LANG = '{{ $l }}';
  const MSG_REQUIRED = @json(__('admin.this_field_is_required'));
  const MSG_DUPLICATE = @json(__('admin.support_area_already_selected'));
  const MSG_SUCCESS = @json(__('admin.startup_profile_updated'));

  const OPTIONS_BY_ID = {};
  ALL_OPTIONS.forEach(o => { OPTIONS_BY_ID[o.id] = o; });

  let selectedIds = [];

  document.addEventListener('DOMContentLoaded', function() {
    if (Array.isArray(INITIAL_IDS)) {
      selectedIds = INITIAL_IDS
        .map(v => (typeof v === 'object' && v !== null ? parseInt(v.id, 10) : parseInt(v, 10)))
        .filter(id => !isNaN(id) && OPTIONS_BY_ID[id]);
    }

    const $input = $('#expertiseSearchInput');
    const $dropdown = $('#expertiseDropdownList');
    const $form = $('#expertiseForm');
    const $error = $('#expertiseErr');
    const $errorText = $('#expertiseErrText');
    const $widget = $('#expertiseWidget');
    const $btn = $('#expertiseSubmitBtn');

    render();

    function render() {
      const container = document.getElementById('expertiseTagsContainer');
      container.innerHTML = '';

      selectedIds.forEach((id, index) => {
        const option = OPTIONS_BY_ID[id];
        if (!option) return;

        const chip = document.createElement('div');
        chip.className = 'badge bg-primary rounded-pill d-flex align-items-center gap-2 px-3 py-2 fs-6 expertise-chip';
        const label = document.createElement('span');
        label.textContent = option.name;
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-close btn-close-white p-0 remove-tag';
        removeBtn.setAttribute('data-idx', index);
        removeBtn.style.width = '0.5rem';
        removeBtn.style.height = '0.5rem';
        chip.appendChild(label);
        chip.appendChild(removeBtn);
        container.appendChild(chip);
      });

      document.getElementById('expertise_tags_input').value = JSON.stringify(selectedIds);

      if (selectedIds.length > 0) {
        hideError();
      }
    }

    function showError(message) {
      $errorText.text(message);
      $error.removeClass('d-none');
      $widget.addClass('border-2 border-danger');
    }

    function hideError() {
      $error.addClass('d-none');
      $widget.removeClass('border-2 border-danger');
    }

    function addTagById(id) {
      id = parseInt(id, 10);
      if (isNaN(id) || !OPTIONS_BY_ID[id]) return;
      if (selectedIds.includes(id)) {
        showError(MSG_DUPLICATE);
        return;
      }
      selectedIds.push(id);
      render();
      $input.val('');
      $dropdown.removeClass('show');
    }

    $(document).on('click', '.remove-tag', function() {
      const idx = parseInt($(this).data('idx'), 10);
      if (!isNaN(idx)) {
        selectedIds.splice(idx, 1);
        render();
      }
    });

    $input.on('input focus', function() {
      const q = $(this).val().toLowerCase().trim();

      const filtered = ALL_OPTIONS.filter(o =>
        o.name.toLowerCase().includes(q) && !selectedIds.includes(o.id)
      );

      $dropdown.empty();

      if (filtered.length) {
        filtered.forEach(o => {
          const item = $('<li><a class="dropdown-item" href="javascript:void(0);"></a></li>');
          item.find('a').text(o.name).attr('data-id', o.id);
          $dropdown.append(item);
        });
        $dropdown.addClass('show');
      } else {
        $dropdown.removeClass('show');
      }
    });

    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      const id = $(this).data('id');
      if (id) {
        addTagById(id);
      }
    });

    $(document).on('mousedown', function(e) {
      if (!$(e.target).closest('#expertiseWidget').length) {
        $dropdown.removeClass('show');
      }
    });

    $form.on('submit', function (e) {
      e.preventDefault();

      if (selectedIds.length === 0) {
        showError(MSG_REQUIRED);
        $input.focus();
        return;
      }

      $btn.prop('disabled', true)
          .html('<span class="spinner-border spinner-border-sm me-1"></span> ' + (LANG === 'en' ? 'Saving...' : 'جاري الحفظ...'));

      const formData = new FormData($form[0]);
      formData.set('expertise_tags', JSON.stringify(selectedIds));

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
          hideError();
          if (typeof toastr !== 'undefined' && toastr.success) {
            toastr.success((response && response.message) || MSG_SUCCESS);
          }
        },
        error: function(xhr) {
          let errorMessage = '';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMessage = errors.join(', ');
          }
          showError(errorMessage || MSG_REQUIRED);
          if (typeof toastr !== 'undefined' && toastr.error) {
            toastr.error(errorMessage || MSG_REQUIRED);
          }
        },
        complete: function() {
          $btn.prop('disabled', false).html(@json(__('admin.Save')));
        }
      });
    });
  });
})();
</script>
@endpush
