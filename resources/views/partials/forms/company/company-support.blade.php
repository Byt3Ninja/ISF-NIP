@php
  $expertises = [
    ['id' => 1, 'name' => 'IT'], ['id' => 2, 'name' => 'Design'], ['id' => 3, 'name' => 'Marketing'],
    ['id' => 4, 'name' => 'Sales'], ['id' => 5, 'name' => 'HR'], ['id' => 6, 'name' => 'Finance'],
    ['id' => 7, 'name' => 'Legal'], ['id' => 8, 'name' => 'Operations'], ['id' => 9, 'name' => 'Customer Support'],
    ['id' => 10, 'name' => 'Product Management'], ['id' => 11, 'name' => 'Business Development'],
  ];
  // Ensure $expertisesSelected is an array of strings or objects
  $expertisesSelected = $expertisesSelected ?? [];
@endphp

<div class="card">
  <div class="card-body">
    <h5 class="card-title border-bottom pb-2 mb-6">
      {{ __('startup.support_mentorship_title') }}
    </h5>
    
    <form id="expertiseForm" method="post" action="{{ route('company.profile_update_mine', $client->company->id ?? auth()->id()) }}">
      @csrf
      <input name="CompanyN" type="hidden" value="14">

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
          {{ __('startup.support_and_mentorship_validation') }}
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
  const ALL_OPTIONS = @json($expertises);
  const INITIAL_VALUES = @json($expertisesSelected);
  const LANG = '{{ $l }}';
  
  let selectedTags = [];

  document.addEventListener('DOMContentLoaded', function() {
    // Initialize data
    if (Array.isArray(INITIAL_VALUES)) {
      selectedTags = INITIAL_VALUES
        .map(v => (typeof v === 'object' ? v.name : v))
        .filter(Boolean);
    }

    const $input = $('#expertiseSearchInput');
    const $dropdown = $('#expertiseDropdownList');
    const $form = $('#expertiseForm');
    const $error = $('#expertiseErr');
    const $widget = $('#expertiseWidget');
    const $btn = $('#expertiseSubmitBtn');

    render();

    function render() {
      const container = document.getElementById('expertiseTagsContainer');
      container.innerHTML = '';

      selectedTags.forEach((tag, index) => {
        const chip = document.createElement('div');
        chip.className = 'badge bg-primary rounded-pill d-flex align-items-center gap-2 px-3 py-2 fs-6 expertise-chip';

        chip.innerHTML = `
          <span>${tag}</span>
          <button type="button" class="btn-close btn-close-white p-0 remove-tag" data-idx="${index}" style="width:0.5rem; height:0.5rem"></button>
        `;

        container.appendChild(chip);
      });

      // Update hidden input for form submission
      document.getElementById('expertise_tags_input').value = JSON.stringify(selectedTags);

      if (selectedTags.length > 0) {
        $error.addClass('d-none');
        $widget.removeClass('border-danger');
      }
    }

    function addTag(val) {
      val = val.trim();
      // Check if value exists in ALL_OPTIONS
      const exists = ALL_OPTIONS.some(o => o.name.toLowerCase() === val.toLowerCase());

      if (!exists) return; //
      
      if (val && !selectedTags.some(t => t.toLowerCase() === val.toLowerCase())) {
        selectedTags.push(val);
        render();
      }
      $input.val('');
      $dropdown.removeClass('show');
    }

    // Event: Remove tag
    $(document).on('click', '.remove-tag', function() {
      const idx = $(this).data('idx');
      selectedTags.splice(idx, 1);
      render();
    });

    // Event: Search & Dropdown Filter
    $input.on('input focus', function() {
      const q = $(this).val().toLowerCase().trim();

      const filtered = ALL_OPTIONS.filter(o => 
        o.name.toLowerCase().includes(q) &&
        !selectedTags.some(t => t.toLowerCase() === o.name.toLowerCase())
      );

      $dropdown.empty();

      if (q || filtered.length) {
        filtered.forEach(o => {
          $dropdown.append(`<li><a class="dropdown-item" href="javascript:void(0);">${o.name}</a></li>`);
        });

        // // Add custom option if it doesn't exist
        // if (q && !filtered.some(f => f.name.toLowerCase() === q)) {
        //   const addLabel = LANG === 'en' ? '+ Add:' : '+ إضافة:';
        //   $dropdown.append(`<li><a class="dropdown-item text-primary border-top" href="javascript:void(0);"><strong>${addLabel}</strong> ${q}</a></li>`);
        // }

        $dropdown.addClass('show');
      } else {
        $dropdown.removeClass('show');
      }
    });

    // Event: Select from dropdown
    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();

      const val = $(this).text().trim();

      // Only allow valid options
      const exists = ALL_OPTIONS.some(o => o.name.toLowerCase() === val.toLowerCase());

      if (exists) {
        addTag(val);
      }
    });

    // Event: Close dropdown when clicking outside
    $(document).on('mousedown', function(e) {
      if (!$(e.target).closest('#expertiseWidget').length) {
        $dropdown.removeClass('show');
      }
    });

    // Form Submit Validation
    $form.on('submit', function (e) {
      e.preventDefault();
      if (selectedTags.length === 0) {  
        $error.removeClass('d-none');
        $widget.addClass('border-2 border-danger');
        $input.focus();
        return;
      }

      $btn.prop('disabled', true)
          .html('<span class="spinner-border spinner-border-sm me-1"></span> ' + (LANG === 'en' ? 'Saving...' : 'جاري الحفظ...'));

      const formData = new FormData($form[0]);

      formData.set('expertise_tags', JSON.stringify(selectedTags));

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
          toaster.success(response.message || "{{ __('admin.support_and_mentorship_success') }}");
        },
        error: function(xhr) {
          console.error('Error:', xhr);
          let errorMessage = '';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMessage = errors.join(', ');
          }
          
          toastr.error(errorMessage || "{{ 'admin.unexpected_error' }}");
        },
        complete: function() {
          $btn.prop('disabled', false).html("{{ __('admin.Save') }}");
        }
      });
    });
  });
})();
</script>
@endpush