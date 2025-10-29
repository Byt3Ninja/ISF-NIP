<div class="card">
  <div class="card-header">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Industry' : 'الصناعة' }} ({{ $l == 'en' ? 'Sectors & Technologies' : 'القطاعات والتقنيات' }})
    </h5>
  </div>
  <div class="card-body">
    <form 
      id="classificationForm" 
      method="post" 
      enctype="multipart/form-data" 
      action="{{ route('company.profile_update_mine', isset($client) && $client->company ? $client->company->id : auth()->user()->id) }}"
      novalidate
    >
      {!! csrf_field() !!}
      <input name="CompanyN" type="hidden" value="14">
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
            {{ $l == 'en' ? 'Add technologies your startup uses (press Enter to add each). Max 50 characters per technology.' : 'أضف التقنيات التي يستخدمها مشروعك (اضغط Enter لإضافة كلٍ منها). الحد الأقصى 50 حرفًا لكل تقنية.' }}
          </small>
          <div class="mt-3">
            <div id="techTagsContainer" class="d-flex flex-wrap gap-2"></div>
            <input id="techTagInput" class="form-control mt-2" type="text" placeholder="{{ $l == 'en' ? 'Type a technology and press Enter' : 'اكتب تقنية ثم اضغط Enter' }}" maxlength="50" />
            <input type="hidden" id="tech_tags" name="tech_tags" />
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
  const $techTagInput = $('#techTagInput');
  const $techTagsHidden = $('#tech_tags');
  const MAX_TECH_LENGTH = 50;

  // initialize from existing technologies passed from controller (names)
  const initialTechs = @json($technologies);
  const existingTechNames = [];
  if (Array.isArray(initialTechs)) {
    initialTechs.forEach(tech => {
      // Handle both object format {name: 'techName'} and string format
      const techName = typeof tech === 'object' && tech.name ? tech.name : tech;
      if (techName) {
        addTechTag(techName);
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
    // update tech checkboxes hidden inputs used elsewhere
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
  }

  function removeTechTag(idx) {
    existingTechNames.splice(idx, 1);
    renderTags();
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

  $techTagInput.on('keypress', function(e) {
    if (e.which === 13) { // Enter
      e.preventDefault();
      addTechTag($(this).val());
      $(this).val('');
    }
  });

  $(document).on('click', '.remove-tech', function() {
    const idx = parseInt($(this).data('idx'));
    removeTechTag(idx);
  });

  // Technologies
  const COMMON_TECHNOLOGIES = @json($technologies);
 
  // Industries dataset with sectors
  const DATA = @json($data);

  // Elements
  const $industryEl = $('#industry');
  const $sectorListEl = $('#sectorList');
  const $techListEl = $('#techList');
  const $sectorOtherEl = $('#sectorOther');
  const $techOtherEl = $('#techOther');
  const $sectorHidden = $('#sector_ids');
  const $techHidden = $('#tech_ids');
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
      technologies: $('.technology-checkbox:checked').map((_, el) => $(el).val()).get(),
      otherSector: $sectorOtherEl.val().trim(),
      // otherTech: $techOtherEl.val().trim()
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
    $sectorListEl.append(`
      <div class="col-md-4 col-sm-6 mb-2">
        <div class="form-check">
          <input class="form-check-input sector-checkbox" type="checkbox" value="other-sector" id="sec-other" />
          <label class="form-check-label" for="sec-other">Other</label>
        </div>
      </div>
    `);
  }

  function renderTechnologies() {
    $techListEl.empty();
    COMMON_TECHNOLOGIES.forEach(tech => {
      $techListEl.append(`
        <div class="col-md-4 col-sm-6 mb-2">
          <div class="form-check">
            <input class="form-check-input technology-checkbox" type="checkbox" value="${tech.id}" id="tech-${tech.id}" ${tech.selected ? 'checked' : ''} />
            <label class="form-check-label" for="tech-${tech.id}">${tech.name}</label>
          </div>
        </div>
      `);
    });
    $techListEl.append(`
      <div class="col-md-4 col-sm-6 mb-2">
        <div class="form-check">
          <input class="form-check-input technology-checkbox" type="checkbox" value="other-tech" id="tech-other" />
          <label class="form-check-label" for="tech-other">Other</label>
        </div>
      </div>
    `);
  }

  function toggleOtherInputs() {
    $sectorOtherEl.toggle($('.sector-checkbox[value="other-sector"]:checked').length > 0);
    $techOtherEl.toggle($('.technology-checkbox[value="other-tech"]:checked').length > 0);
  }

  function updateHiddenInputs() {
    const sectorIds = $('.sector-checkbox:checked').map((_, el) => $(el).val()).get().filter(v => v !== 'other-sector');
    const techIds = $('.technology-checkbox:checked').map((_, el) => $(el).val()).get().filter(v => v !== 'other-tech');
    $sectorHidden.val(JSON.stringify(sectorIds));
    $techHidden.val(JSON.stringify(techIds));
  }

  // Events
  $industryEl.on('change', function() {
    renderSectors($(this).val());
    toggleOtherInputs();
    updateHiddenInputs();
    checkForChanges();
    $industryErr.hide();
  });

  $(document).on('change', '.sector-checkbox, .technology-checkbox', function() {
    toggleOtherInputs();
    updateHiddenInputs();
    checkForChanges();
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
  
  $techOtherEl.on('input', function() {
    let value = $(this).val();
    // Remove leading whitespace and filter non-letters (except spaces)
    value = value.replace(/^\s+/, '').replace(/[^A-Za-z\s]/g, '');
    $(this).val(value);
    $techErr.hide();
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
  $techOtherEl.on('keypress', validateLettersOnly);

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
    if (!$('.sector-checkbox:checked').length) {
      $sectorErr.show();
      isValid = false;
    }
    
    // Check if either technology checkboxes are checked or there are tech tags
    const hasCheckedTech = $('.technology-checkbox:checked').length > 0;
    const hasTechTags = existingTechNames && existingTechNames.length > 0;
    
    if (!hasCheckedTech && !hasTechTags) {
      $techErr.show();
      isValid = false;
    }
    
    if ($('.sector-checkbox[value="other-sector"]:checked').length) {
      const sectorValue = $sectorOtherEl.val().trim();
      if (!sectorValue || !/^[A-Za-z\s]+$/.test(sectorValue)) {
        $sectorErr.text('{{ $l == "en" ? "Please specify a valid sector name (letters only)" : "يرجى تحديد اسم قطاع صالح (حروف فقط)" }}').show();
        isValid = false;
      }
    }
    if ($('.technology-checkbox[value="other-tech"]:checked').length) {
      const techValue = $techOtherEl.val().trim();
      if (!techValue || !/^[A-Za-z\s]+$/.test(techValue)) {
        $techErr.text('{{ $l == "en" ? "Please specify a valid technology name (letters only)" : "يرجى تحديد اسم تقنية صالح (حروف فقط)" }}').show();
        isValid = false;
      }
    }

    if (!isValid) {
      return false;
    }

    // Update hidden fields before form submission
    const sectorIds = $('.sector-checkbox:checked').map((_, el) => $(el).val()).get();
    $sectorHidden.val(JSON.stringify(sectorIds));

    const techIds = $('.technology-checkbox:checked').map((_, el) => $(el).val()).get();
    $techHidden.val(JSON.stringify(techIds));

    // Ensure tech tags are up to date
    $techTagsHidden.val(JSON.stringify(existingTechNames));

    // Show loading state
    $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ $l == "en" ? "Saving..." : "جاري الحفظ..." }}');

    // Submit form via AJAX
    const formData = new FormData($form[0]);
    
    // Add tech tags to form data
    formData.set('tech_tags', JSON.stringify(existingTechNames));

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
      success: function(response) {
        showToast('success', '{{ $l == "en" ? "Success" : "تم بنجاح" }}', '{{ $l == "en" ? "Company classification updated successfully" : "تم تحديث تصنيف الشركة بنجاح" }}');
      },
      error: function(xhr) {
        console.error('Error:', xhr);
        let errorMessage = '{{ $l == "en" ? "An error occurred. Please try again." : "حدث خطأ. يرجى المحاولة مرة أخرى." }}';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        showToast('error', '{{ $l == "en" ? "Error" : "خطأ" }}', errorMessage);
      },
      complete: function() {
        $submitBtn.prop('disabled', false).html('{{ $l == "en" ? "Save" : "حفظ" }}');
      }
    });
  });

  // Init
  renderIndustryOptions();
  renderSectors($industryEl.val());
  renderTechnologies();
  toggleOtherInputs();
  updateHiddenInputs();
  saveInitialState();
  checkForChanges(); // disable at start
});
</script>
@endpush
