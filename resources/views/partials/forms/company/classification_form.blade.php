<div class="card">
  <div class="card-body">
    <h5 class="card-title border-bottom pb-2 mb-6">
      {{ $l == 'en' ? 'Industry' : 'الصناعة' }} ({{ $l == 'en' ? 'Sectors & Technologies' : 'القطاعات والتقنيات' }})
    </h5>
    <form 
      id="classificationForm" 
      method="post" 
      enctype="multipart/form-data" 
      action="{{ route('company.profile_update_mine', isset($client) && $client->company ? $client->company->id : auth()->user()->id) }}"
      novalidate
    >
      {!! csrf_field() !!}
      <input name="CompanyN" type="hidden" value="{{ \App\Enums\CompanyFormStep::Classification->value }}">
      <fieldset class="row">
        <div class="col-12">
          <div class="form-group mb-6">
            <label for="industry" class="form-label fs-6 fw-semibold">
              {{ $l == 'en' ? 'Industry' : 'الصناعة' }}
            </label>
            <select id="industry" class="form-select select2" name="industry_id" required></select>
            <small class="text-muted">
              {{ $l == 'en' ? 'Select the industry that best describes your business. Each industry has its specific sectors.' : 'حدد الصناعة التي تصف عملك بشكل أفضل. كل صناعة لها قطاعاتها الخاصة.' }}
            </small>
            <div class="error" id="industryErr" style="display:none;">
              {{ $l == 'en' ? 'Please select an industry' : 'يرجى اختيار صناعة' }}
            </div>
          </div>
        </div>
      </fieldset>

      <div class="col-12">
        <div class="form-group mb-6">
          <label id="sectorsLabel" class="form-label fs-6 fw-semibold d-block">
            {{ $l == 'en' ? 'Sectors' : 'القطاعات' }}
          </label>
          <small class="text-muted">
            {{ $l == 'en' ? 'Tag all that apply. Select Other to add a custom sector.' : 'وضع علامة على كل ما ينطبق. حدد "أخرى" لإضافة قطاع مخصص.' }}
          </small>
          <div class="row mt-4" id="sectorList"></div>
          <input 
            id="sectorOther" 
            class="form-control" 
            name="other_sector" 
            type="text" 
            placeholder="{{ $l == 'en' ? 'Type other sector' : 'اكتب قطاعًا آخر' }}" 
            style="display:none"
            pattern="[A-Za-z\s]+"
            title="{{ $l == 'en' ? 'Letters only' : 'حروف فقط' }}"
          />
          <input type="hidden" id="sector_ids" name="sector_ids">
          <div class="error" id="sectorErr" style="display:none;">
            {{ $l == 'en' ? 'Select at least one sector' : 'حدد قطاعًا واحدًا على الأقل' }}
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="form-group mb-6">
          <label id="technologiesLabel" class="form-label fs-6 fw-semibold d-block">
            {{ $l == 'en' ? 'Technologies used' : 'التقنيات المستخدمة' }}
          </label>
          <small class="text-muted mb-4">
            {{ $l == 'en' ? 'Select technologies from the list or search and add new ones. Max 50 characters per technology.' : 'حدد التقنيات من القائمة أو ابحث وأضف تقنيات جديدة. الحد الأقصى 50 حرفًا لكل تقنية.' }}
          </small>
          <div class="mt-3">
            <div id="techTagsContainer" class="d-flex flex-wrap gap-2 mb-3"></div>
            <select id="techSelect" class="form-select select2" multiple="multiple" data-placeholder="{{ $l == 'en' ? 'Search and select technologies...' : 'ابحث وحدد التقنيات...' }}" style="width: 100%;"></select>
            <input type="hidden" id="tech_tags" name="tech_tags"  />
            <div class="error" id="techErr" style="display:none; margin-top:6px; color: #dc3545;">
              {{ $l == 'en' ? 'Please enter at least one technology.' : 'يرجى إدخال تقنية واحدة على الأقل' }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 mt-12">
        <div class="d-flex gap-2 justify-content-end">
          <button type="submit" class="btn btn-primary">
            {{ $l == 'en' ? 'Save' : 'حفظ' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


@push('scripts')
<script>
$(function() {
  // Tech tags UI
  const $techTagsContainer = $('#techTagsContainer');
  const $techTagsHidden = $('#tech_tags');
  const MAX_TECH_LENGTH = 50;

  // initialize from existing technologies passed from controller (names)
  const initialTechs = @json(isset($technologiesSelected) ? $technologiesSelected : []);
  const existingTechNames = [];
  if (Array.isArray(initialTechs)) {
    initialTechs.forEach(tech => {
      // Handle both object format {name: 'techName'} and string format
      const techName = typeof tech === 'object' && tech.name ? tech.name : tech;
      if (techName) {
        existingTechNames.push(techName);
      }
    });
  }

  function renderTags() {
    $techTagsContainer.empty();
    existingTechNames.forEach((name, idx) => {
      const $chip = $(`
        <div class="badge bg-primary text-white d-inline-flex align-items-center" data-idx="${idx}" style="padding: .45rem .6rem;">
          <span class="me-2 line-clamp-1">${escapeHtml(name)}</span>
          <button type="button" class="btn-close btn-close-white btn-sm remove-tech" aria-label="Remove" data-idx="${idx}"></button>
        </div>
      `);
      $techTagsContainer.append($chip);
    });
    $techTagsHidden.val(JSON.stringify(existingTechNames));
  }

  function addTechTag(value) {
    if (!value) return;
    value = String(value).trim();
    if (!value) return;
    if (value.length > MAX_TECH_LENGTH) {
      return; // Silently prevent adding tags that are too long
    }
    // prevent duplicates (case-insensitive)
    const exists = existingTechNames.some(t => t.toLowerCase() === value.toLowerCase());
    if (exists) return;
    existingTechNames.push(value);
    renderTags();
    // Update select2 to include this tech
    const currentValues = $techSelectEl.val() || [];
    if (!currentValues.includes(value)) {
      currentValues.push(value);
      $techSelectEl.val(currentValues).trigger('change');
    }
  }

  function removeTechTag(idx) {
    const removedName = existingTechNames[idx];
    existingTechNames.splice(idx, 1);
    renderTags();
    // Update select2 to remove this tech
    const currentValues = $techSelectEl.val() || [];
    const updatedValues = currentValues.filter(v => v !== removedName);
    $techSelectEl.val(updatedValues).trigger('change');
  }

  function escapeHtml(unsafe) {
    return unsafe.replace(/[&<"'`=\/]/g, function (s) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;'
      })[s];
    });
  }

  $(document).on('click', '.remove-tech', function() {
    const idx = parseInt($(this).data('idx'));
    removeTechTag(idx);
    checkForChanges();
  });

  // Technologies - all available technologies for select2
  const ALL_TECHNOLOGIES = @json($technologies);
 
  // Industries dataset with sectors
  const DATA = @json($data);

  // Elements
  const $industryEl = $('#industry');
  const $sectorListEl = $('#sectorList');
  const $techSelectEl = $('#techSelect');
  const $sectorOtherEl = $('#sectorOther');
  const $sectorHidden = $('#sector_ids');
  const $form = $('#classificationForm');
  const $industryErr = $('#industryErr');
  const $sectorErr = $('#sectorErr');
  const $techErr = $('#techErr');
  const $submitBtn = $form.find('button[type="submit"]');

  let initialState = null;

  function getState() {
    return {
      industry: $industryEl.val(),
      sectors: $('.sector-checkbox:checked').map((_, el) => $(el).val()).get(),
      technologies: existingTechNames,
      otherSector: $sectorOtherEl.val().trim(),
    };
  }

  function saveInitialState() {
    initialState = JSON.stringify(getState());
  }

  function checkForChanges() {
    const current = JSON.stringify(getState());
    $submitBtn.prop('disabled', current === initialState);
  }

  function renderIndustryOptions() {
    $industryEl.empty();
    $industryEl.append('<option value="" disabled>Select Industry</option>');
    DATA.forEach(ind => {
      $industryEl.append(`<option value="${ind.id}" ${ind.selected ? 'selected' : ''}>${ind.name}</option>`);
    });
  }

  function renderSectors(industryId) {
    $sectorListEl.empty();
    const entry = DATA.find(d => d.id == industryId);
    if (!entry) return;
    
    // Only show sectors if the industry has sectors, otherwise show empty with just "Other"
    if (entry.sectors && entry.sectors.length > 0) {
    entry.sectors.forEach(sec => {
      $sectorListEl.append(`
        <div class="col-md-4 col-sm-6 mb-2">
          <div class="form-check">
            <input class="form-check-input sector-checkbox" type="checkbox" value="${sec.id}" id="sec-${sec.id}" ${sec.selected ? 'checked' : ''} />
            <label class="form-check-label" for="sec-${sec.id}">${sec.name}</label>
          </div>
        </div>
      `);
    });
    }
    
    // Always show "Other" option
    $sectorListEl.append(`
      <div class="col-md-4 col-sm-6 mb-2">
        <div class="form-check">
          <input class="form-check-input sector-checkbox" type="checkbox" value="other-sector" id="sec-other" />
          <label class="form-check-label" for="sec-other">Other</label>
        </div>
      </div>
    `);
  }

  function initializeTechSelect() {
    // Initialize select2 with all technologies
    const techOptions = ALL_TECHNOLOGIES.map(tech => ({
      id: tech.id || tech.name || tech,
      text: tech.name || tech
    }));
    
    // Add existing tech names that might not be in ALL_TECHNOLOGIES
    existingTechNames.forEach(name => {
      const exists = techOptions.some(opt => opt.text.toLowerCase() === name.toLowerCase() || opt.id === name);
      if (!exists) {
        techOptions.push({
          id: name,
          text: name
        });
      }
    });
    
    $techSelectEl.select2({
      data: techOptions,
      tags: true, // Allow adding new tags
      tokenSeparators: [','],
      placeholder: '{{ $l == "en" ? "Search and select technologies..." : "ابحث وحدد التقنيات..." }}',
      language: {
        noResults: function() {
          return '{{ $l == "en" ? "No results found. Type and press Enter to add new technology" : "لم يتم العثور على نتائج. اكتب واضغط Enter لإضافة تقنية جديدة" }}';
        },
        searching: function() {
          return '{{ $l == "en" ? "Searching..." : "جاري البحث..." }}';
        }
      },
      createTag: function (params) {
        const term = $.trim(params.term);
        if (term === '') {
          return null;
        }
        // Limit to 50 characters
        if (term.length > 50) {
          return null;
        }
        // Check if already exists
        const exists = techOptions.some(opt => opt.text.toLowerCase() === term.toLowerCase() || opt.id === term);
        if (exists) {
          return null;
        }
        return {
          id: term,
          text: term,
          newTag: true
        };
      }
    });
    
    // Set initial selected technologies
    if (existingTechNames.length > 0) {
      const selectedValues = existingTechNames.map(name => {
        // Try to find matching tech by name
        const found = techOptions.find(t => (t.text || t.id).toLowerCase() === name.toLowerCase());
        return found ? found.id : name;
      });
      $techSelectEl.val(selectedValues).trigger('change');
    }
    
    // Initial render of tags
    renderTags();
    
    // Handle selection change
    $techSelectEl.on('select2:select', function(e) {
      const data = e.params.data;
      const techName = data.text || data.id;
      if (techName) {
        addTechTag(techName);
        checkForChanges();
        $techErr.hide();
      }
    });
    
    // Handle removal
    $techSelectEl.on('select2:unselect', function(e) {
      const data = e.params.data;
      const techName = data.text || data.id;
      const idx = existingTechNames.findIndex(t => t.toLowerCase() === techName.toLowerCase());
      if (idx !== -1) {
        removeTechTag(idx);
        checkForChanges();
      }
    });
  }

  function toggleOtherInputs() {
    $sectorOtherEl.toggle($('.sector-checkbox[value="other-sector"]:checked').length > 0);
  }

  function updateHiddenInputs() {
    const sectorIds = $('.sector-checkbox:checked').map((_, el) => $(el).val()).get().filter(v => v !== 'other-sector');
    $sectorHidden.val(JSON.stringify(sectorIds));
  }

  // Events
  $industryEl.on('change', function() {
    renderSectors($(this).val());
    toggleOtherInputs();
    updateHiddenInputs();
    checkForChanges();
    $industryErr.hide();
  });

  $(document).on('change', '.sector-checkbox', function() {
    toggleOtherInputs();
    updateHiddenInputs();
    checkForChanges();
    $sectorErr.hide();
  });

  // clear errors on typing in "Other" fields with validation
  $sectorOtherEl.on('input', function() {
    let value = $(this).val();
    // Remove leading whitespace and filter non-letters (except spaces)
    value = value.replace(/^\s+/, '').replace(/[^A-Za-z\s]/g, '');
    $(this).val(value);
    $sectorErr.hide();
    checkForChanges();
  });

  // Prevent invalid characters on keypress
  function validateLettersOnly(e) {
    const char = String.fromCharCode(e.which);
    const isLetter = /[A-Za-z]/.test(char);
    const isSpace = char === ' ';
    const isEmpty = $(e.target).val().length === 0;
    
    // Allow letters, allow space only if field is not empty (no leading space)
    if (!isLetter && !(isSpace && !isEmpty)) {
      e.preventDefault();
      return false;
    }
  }
  
  $sectorOtherEl.on('keypress', validateLettersOnly);

  $form.on('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    $industryErr.hide();
    $sectorErr.hide();
    $techErr.hide();

    if (!$industryEl.val()) {
      $industryErr.show();
      isValid = false;
    }
    
    // Check if at least one sector is selected (including "other" with value)
    const checkedSectors = $('.sector-checkbox:checked');
    const hasOtherSector = checkedSectors.filter('[value="other-sector"]').length > 0;
    const hasOtherSectorValue = hasOtherSector && $sectorOtherEl.val().trim().length > 0;
    const hasRegularSectors = checkedSectors.filter('[value!="other-sector"]').length > 0;
    
    if (!hasRegularSectors && !hasOtherSectorValue) {
      $sectorErr.show();
      isValid = false;
    }
    
    if (hasOtherSector) {
      const sectorValue = $sectorOtherEl.val().trim();
      if (!sectorValue || !/^[A-Za-z\s]+$/.test(sectorValue)) {
        $sectorErr.text('{{ $l == "en" ? "Please specify a valid sector name (letters only)" : "يرجى تحديد اسم قطاع صالح (حروف فقط)" }}').show();
        isValid = false;
      }
    }
    
    // Check if there are tech tags
    const hasTechTags = existingTechNames && existingTechNames.length > 0;
    
    if (!hasTechTags) {
      $techErr.show();
        isValid = false;
    }

    if (!isValid) {
      return false;
    }

    // Update hidden fields before form submission
    const sectorIds = $('.sector-checkbox:checked').map((_, el) => $(el).val()).get().filter(v => v !== 'other-sector');
    $sectorHidden.val(JSON.stringify(sectorIds));

    // Ensure tech tags are up to date
    $techTagsHidden.val(JSON.stringify(existingTechNames));

    // Show loading state
    $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ $l == "en" ? "Saving..." : "جاري الحفظ..." }}');

    // Submit form via AJAX
    const formData = new FormData($form[0]);
    
    // Add tech tags to form data
    formData.set('tech_tags', JSON.stringify(existingTechNames));
    
    // Ensure other_sector is included if "Other" is checked
    if ($('.sector-checkbox[value="other-sector"]:checked').length > 0) {
      formData.set('other_sector', $sectorOtherEl.val().trim());
    } else {
      formData.set('other_sector', '');
    }

    // Log form data for debugging
    const formDataObj = {};
    formData.forEach((value, key) => {
      formDataObj[key] = value;
    });
    console.log('Form data:', formDataObj);

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
        toastr.success("{{ __('admin.startup_profile_updated') }}")
        saveInitialState(); // Update initial state after successful save
        checkForChanges();
      },
      error: function(xhr) {
        console.error('Error:', xhr);
        let errorMessage = '{{ $l == "en" ? "An error occurred. Please try again." : "حدث خطأ. يرجى المحاولة مرة أخرى." }}';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = Object.values(xhr.responseJSON.errors).flat();
          errorMessage = errors.join(', ');
        }
        
        if (typeof releaseFailureAlert === 'function') {
          releaseFailureAlert({
            title: '{{ $l == "en" ? "Error" : "خطأ" }}',
            message: errorMessage
          });
        } else if (typeof showToast === 'function') {
          showToast({
            type: 'error',
            message: errorMessage
          });
        } else {
          alert('{{ $l == "en" ? "Error" : "خطأ" }}: ' + errorMessage);
        }
      },
      complete: function() {
        $submitBtn.prop('disabled', false).html('{{ $l == "en" ? "Save" : "حفظ" }}');
      }
    });
  });

  // Init
  renderIndustryOptions();
  renderSectors($industryEl.val());
  initializeTechSelect();
  toggleOtherInputs();
  updateHiddenInputs();
  saveInitialState();
  checkForChanges(); // disable at start
});
</script>
@endpush
