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
      action="{{ $_user->hasAnyRole('Admin|organizationManager') ? route('company.profile_update_mine', $client->company->id) : route('company.profile_update_mine') }}"
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
            {{ $l == 'en' ? 'Tag all that apply. Select Other to add a custom technology.' : 'وضع علامة على كل ما ينطبق. حدد "أخرى" لإضافة تقنية مخصصة.' }}
          </small>
          <div class="row mt-4" id="techList"></div>
          <input 
            id="techOther" 
            class="form-control" 
            name="other_tech" 
            type="text" 
            placeholder="{{ $l == 'en' ? 'Type other technology' : 'اكتب تقنية أخرى' }}" 
            style="display:none"
            pattern="[A-Za-z\s]+"
            title="{{ $l == 'en' ? 'Letters only' : 'حروف فقط' }}"
          />
          <input type="hidden" id="tech_ids" name="tech_ids">
          <div class="error" id="techErr" style="display:none;">
            {{ $l == 'en' ? 'Select at least one technology' : 'حدد تقنية واحدة على الأقل' }}
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
      otherTech: $techOtherEl.val().trim()
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
    if (!$('.technology-checkbox:checked').length) {
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
      e.preventDefault();
      return;
    }
    updateHiddenInputs();
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
