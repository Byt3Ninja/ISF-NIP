<div class="card">
  <div class="card-header">
    <h5 class="card-title">
      {{ $heading }} ({{ $l == 'en' ? 'Sectors & Technologies' : 'القطاعات والتقنيات' }})
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
          />
          <input type="hidden" id="sector_ids" name="sector_ids[]" />
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
          />
          <input type="hidden" id="tech_ids" name="tech_ids[]" />
          <div class="error" id="techErr" style="display:none;">
            {{ $l == 'en' ? 'Select at least one technology' : 'حدد تقنية واحدة على الأقل' }}
          </div>
        </div>
      </div>
    
      <div class="col-12 mt-12">
        <div class="d-flex gap-2 justify-content-end">
          <button type="submit" class="btn btn-primary">
            {{ $l == 'en' ? 'Save Classification' : 'حفظ التصنيف' }}
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
  const COMMON_TECHNOLOGIES = [
    {id: 1, name:"Artificial Intelligence (AI)"},
    {id: 2, name:"Machine Learning (ML)"},
    {id: 3, name:"Internet of Things (IoT)"},
    {id: 4, name:"Cloud Computing"},
    {id: 5, name:"Big Data & Analytics"},
    {id: 6, name:"Other"}
  ];

  // Industries dataset with sectors
  const DATA = [
    {
      id: 1,
      name: "Agritech",
      sectors: [
        {id: 101, name:"Crop Production Solutions"},
        {id: 102, name:"Precision Agriculture"},
        {id: 199, name:"Other"}
      ]
    },
    {
      id: 2,
      name: "Fintech",
      sectors: [
        {id: 201, name:"Digital Banking"},
        {id: 202, name:"Payments and Remittances"},
        {id: 299, name:"Other"}
      ]
    },
    {
      id: 3,
      name: "Healthtech",
      sectors: [
        {id: 301, name:"Telemedicine and Remote Care"},
        {id: 302, name:"Electronic Health Records (EHR)"},
        {id: 399, name:"Other"}
      ]
    }
    // ...continue for all industries
  ];

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

  // Populate industry options
  function renderIndustryOptions() {
    $industryEl.empty();
    $industryEl.append('<option value="" disabled selected>Select Industry</option>');
    DATA.forEach(ind => {
      $industryEl.append(`<option value="${ind.id}">${ind.name}</option>`);
    });
  }

  // Render sectors for industry
  function renderSectors(industryId) {
    $sectorListEl.empty();
    const entry = DATA.find(d => d.id == industryId);
    if (!entry) return;

    entry.sectors.forEach(sec => {
      const $wrap = $(`
        <div class="col-md-4 col-sm-6 mb-2">
          <div class="form-check">
            <input class="form-check-input sector-checkbox" type="checkbox" value="${sec.id}" id="sec-${sec.id}" />
            <label class="form-check-label" for="sec-${sec.id}">${sec.name}</label>
          </div>
        </div>
      `);
      $sectorListEl.append($wrap);
    });
  }

  // Render technologies
  function renderTechnologies() {
    $techListEl.empty();
    COMMON_TECHNOLOGIES.forEach(tech => {
      const $wrap = $(`
        <div class="col-md-4 col-sm-6 mb-2">
          <div class="form-check">
            <input class="form-check-input technology-checkbox" type="checkbox" value="${tech.id}" id="tech-${tech.id}" />
            <label class="form-check-label" for="tech-${tech.id}">${tech.name}</label>
          </div>
        </div>
      `);
      $techListEl.append($wrap);
    });
  }

  // Show/hide Other inputs
  function toggleOtherInputs() {
    $sectorOtherEl.toggle($('.sector-checkbox[value="199"]:checked').length > 0);
    $techOtherEl.toggle($('.technology-checkbox[value="6"]:checked').length > 0);
  }

  // Update hidden inputs with selected IDs
  function updateHiddenInputs() {
    const sectorIds = $('.sector-checkbox:checked').map((_, el) => $(el).val()).get();
    const techIds = $('.technology-checkbox:checked').map((_, el) => $(el).val()).get();
    $sectorHidden.val(JSON.stringify(sectorIds));
    $techHidden.val(JSON.stringify(techIds));
  }

  // Event bindings
  $industryEl.on('change', function() {
    renderSectors($(this).val());
    renderTechnologies();
    $industryErr.hide();
  });

  $(document).on('change', '.sector-checkbox, .technology-checkbox', function() {
    toggleOtherInputs();
    updateHiddenInputs();
  });

  $sectorOtherEl.on('input', () => $sectorErr.hide());
  $techOtherEl.on('input', () => $techErr.hide());

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

    // Require "Other" text if selected
    if ($('.sector-checkbox[value="199"]:checked').length && !$sectorOtherEl.val().trim()) {
      $sectorErr.text('{{ $l == "en" ? "Please specify the other sector" : "يرجى تحديد القطاع الآخر" }}').show();
      isValid = false;
    }
    if ($('.technology-checkbox[value="6"]:checked').length && !$techOtherEl.val().trim()) {
      $techErr.text('{{ $l == "en" ? "Please specify the other technology" : "يرجى تحديد التقنية الأخرى" }}').show();
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
      return;
    }

    updateHiddenInputs();
  });

  // Init
  renderIndustryOptions();
});
</script>
@endpush