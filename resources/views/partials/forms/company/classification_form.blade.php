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
            <select id="industry" class="form-select select2" name="industry" required>
              {{-- <option value="" selected disabled>Select industry</option> --}}
              <option selected>Agritech</option>
              <option>Fintech</option>
              <option>Healthtech</option>
              <option>Edtech</option>
              <option>E-commerce & Retail</option>
              <option>Mobility & Logistics</option>
              <option>Recycling & Greentech</option>
              <option>SaaS / Enterprise Tech</option>
              <option>Tourism & TravelTech</option>
              <option>Media & Entertainment</option>
              <option>Foodtech</option>
              <option>AI & Deeptech</option>
              <option>LegalTech</option>
              <option>Social Impact & Civic Tech</option>
              <option>Manufacturing</option>
              <option>Creative Arts</option>
            </select>
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
          <label for="sectors" id="sectorsLabel" class="form-label fs-6 fw-semibold d-block">
            {{ $l == 'en' ? 'Sectors' : 'القطاعات' }}
          </label>
          <small class="text-muted">
            {{ $l == 'en' ? 'Tag all that apply. Select Other to add a custom sector.' : 'وضع علامة على كل ما ينطبق. حدد "أخرى" لإضافة قطاع مخصص.' }}
          </small>
          <div class="row mt-4" id="sectorList" aria-label="Sector list">
            <!-- sectors populated by JS based on dataset and selected industry -->
          </div>
          <input 
            id="sectorOther" 
            class="form-control" 
            name="sectorOther" 
            type="text" 
            placeholder="{{ $l == 'en' ? 'Type other sector' : 'اكتب قطاعًا آخر' }}" 
            style="display:none" 
          />
          <div class="error" id="sectorErr" style="display:none;">
            {{ $l == 'en' ? 'Select at least one sector' : 'حدد قطاعًا واحدًا على الأقل' }}
          </div>
        </div>
      </div>
    
      <div class="col-12">
        <div class="form-group mb-6">
          <label for="technologies" id="technologiesLabel" class="form-label fs-6 fw-semibold d-block">
            {{ $l == 'en' ? 'Technologies used' : 'التقنيات المستخدمة' }}
          </label>
          <small class="text-muted mb-4">
            {{ $l == 'en' ? 'Tag all that apply. Select Other to add a custom technology.' : 'وضع علامة على كل ما ينطبق. حدد "أخرى" لإضافة تقنية مخصصة.' }}
          </small>
          <div class="row mt-4" id="techList" aria-label="Technology list">
            <!-- technologies populated by JS based on dataset -->
          </div>
          <input 
            id="techOther" 
            class="form-control" 
            name="techOther" 
            type="text" 
            placeholder="{{ $l == 'en' ? 'Type other technology' : 'اكتب تقنية أخرى' }}" 
            style="display:none" 
          />
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
$(document).ready(function() {
  // Common technologies across all industries
  const COMMON_TECHNOLOGIES = [
    "Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)",
    "Cloud Computing","Big Data & Analytics","Automation","Robotics","Blockchain",
    "Mobile Applications","Web Applications","AR/VR","3D Printing",
    "Natural Language Processing","Computer Vision","Quantum Computing",
    "Biometrics","Drone Technology","5G","Edge Computing","Digital Twin",
    "Geospatial Technology","Cybersecurity","API Integration","Other"
  ];

  // Dataset with sectors for each industry
  const DATA = [
    {
      industry: "Agritech",
      sectors: [
        "Crop Production Solutions","Precision Agriculture","Farm Management Software",
        "Agricultural Supply Chain Platforms","Agri-Biotech and Genetics","Agri-Finance and Insurance",
        "Smart Irrigation Systems","Agri Drones and Remote Sensing","Livestock Management",
        "Food Traceability and Safety","Other"
      ]
    },
    {
      industry: "Fintech",
      sectors: [
        "Digital Banking / Neo Banks","Payments and Remittances","Payment Gateways and Processors",
        "Lending and Microfinance","Wealth Management / Robo-Advisory","InsurTech","RegTech / Compliance",
        "Fraud Detection and Risk Scoring","Digital Wallets","Buy Now Pay Later (BNPL)","Crypto Exchanges",
        "Crowdfunding Platforms","Other"
      ]
    },
    {
      industry: "Healthtech",
      sectors: [
        "Telemedicine and Remote Care","Electronic Health Records (EHR)","Medical Devices and Wearables",
        "Diagnostics and Imaging","Health Data Analytics","Wellness and Fitness Platforms",
        "Genomics and Precision Medicine","Drug Discovery Platforms","Remote Patient Monitoring",
        "Virtual Hospitals","Healthcare Supply Chain","Other"
      ]
    },
    {
      industry: "Edtech",
      sectors: [
        "Online Learning Platforms","K-12 Solutions","Higher Education Solutions",
        "Corporate Training and LMS","Testing and Assessment Tools","Language Learning",
        "Virtual Classrooms","Skill Development Platforms","AR/VR Learning Environments",
        "Gamified Learning Apps","Other"
      ]
    },
    {
      industry: "E-commerce & Retail",
      sectors: [
        "Online Marketplaces","Direct-to-Consumer Brands","Social Commerce",
        "Retail Analytics and Personalization","Logistics and Last-Mile Delivery",
        "Subscription Commerce","Flash Sales Platforms","B2B E-commerce",
        "Inventory Management Systems","Omni-Channel Retail","Digital Payments Integration","Other"
      ]
    },
    {
      industry: "Mobility & Logistics",
      sectors: [
        "Ride-Hailing","Fleet Management","Delivery and Courier Services","Smart Public Transport",
        "Supply Chain Platforms","Drone Delivery","Micro-Mobility (e-scooters, bikes)",
        "Autonomous Vehicles","Freight and Cargo Management","Smart Parking Systems",
        "Mobility-as-a-Service (MaaS)","Other"
      ]
    },
    {
      industry: "Recycling & Greentech",
      sectors: [
        "Waste Management Platforms","Renewable Energy Solutions","Energy Storage Systems",
        "Carbon Capture and Trading","Water Treatment Technologies","Smart Grid Management",
        "Energy Efficiency Monitoring","Sustainable Packaging","Circular Economy Platforms","Other"
      ]
    },
    {
      industry: "SaaS / Enterprise Tech",
      sectors: [
        "Cloud Infrastructure","Productivity Tools","CRM and ERP Systems","Cybersecurity Platforms",
        "HR and Payroll Systems","Data Analytics and BI Tools","DevOps and CI/CD Platforms",
        "Marketing Automation","Customer Support Platforms","Project Management Tools",
        "Workflow Automation","Other"
      ]
    },
    {
      industry: "Tourism & TravelTech",
      sectors: [
        "Online Booking Platforms","Travel Management Systems","Hospitality Solutions",
        "Experience Marketplaces","Mobility Services for Travel","Virtual Travel Experiences",
        "Travel Insurance Platforms","Dynamic Pricing Engines","Loyalty and Rewards Platforms","Other"
      ]
    },
    {
      industry: "Media & Entertainment",
      sectors: [
        "Streaming Platforms","Gaming","Digital Publishing","Advertising Technology (AdTech)",
        "Music Tech","Podcast Platforms","AR/VR Entertainment","Content Creation Tools",
        "Esports Platforms","Social Media Networks","Other"
      ]
    },
    {
      industry: "Foodtech",
      sectors: [
        "Food Delivery Platforms","Alternative Proteins","Smart Kitchen Devices",
        "Food Safety and Traceability","Meal Kit Services","Restaurant Management Systems",
        "Vertical Farming","Grocery Delivery","Other"
      ]
    },
    {
      industry: "AI & Deeptech",
      sectors: [
        "Machine Learning Platforms","Natural Language Processing","Computer Vision",
        "Robotics","Quantum Computing","Edge AI Solutions","Neural Interface Technology",
        "Autonomous Systems","Other"
      ]
    },
    {
      industry: "LegalTech",
      sectors: [
        "Contract Management","e-Discovery","Compliance Platforms","Online Dispute Resolution",
        "Case Management Systems","IP Management Tools","Legal Research Platforms","Other"
      ]
    },
    {
      industry: "Social Impact & Civic Tech",
      sectors: [
        "GovTech Services","Community Platforms","Non-Profit Management Tools",
        "Civic Engagement Apps","Digital Identity Solutions","Open Data Platforms",
        "Public Service Portals","Other"
      ]
    },
    {
      industry: "Manufacturing",
      sectors: [
        "Smart Factory (Industry 4.0)","3D Printing and Additive Manufacturing","Supply Chain Automation",
        "Industrial IoT","Predictive Maintenance","Robotics Integration","Quality Control Systems",
        "Digital Twin Platforms","Advanced Materials","Other"
      ]
    },
    {
      industry: "Creative Arts",
      sectors: [
        "Digital Art Platforms","Music Production Tools","Design Marketplaces","Content Creation Platforms",
        "Virtual Galleries","NFT Art Marketplaces","AR/VR Creative Experiences","Other"
      ]
    }
  ];

  // Cache jQuery selectors for better performance
  const $industryEl = $('#industry');
  const $sectorListEl = $('#sectorList');
  const $techListEl = $('#techList');
  const $sectorOtherEl = $('#sectorOther');
  const $techOtherEl = $('#techOther');
  const $industryErr = $('#industryErr');
  const $sectorErr = $('#sectorErr');
  const $techErr = $('#techErr');
  const $form = $('#classificationForm');

  // Generate a safe ID from text
  function generateId(text, prefix = '') {
    return prefix + text.replace(/[^a-z0-9]+/gi, '-').toLowerCase();
  }

  // Update labels based on selected industry
  function updateLabels(industry) {
    @if ($l == 'ar')
      $('#sectorsLabel').text(`اختر القطاعات لـ ${industry}`);
      $('#technologiesLabel').text('اختر التقنيات المستخدمة');
    @else
      $('#sectorsLabel').text(`Choose sectors for ${industry}`);
      $('#technologiesLabel').text('Choose technologies used');
    @endif
  }

  // Render sectors based on selected industry
  function renderSectors(industryName) {
    $sectorListEl.empty();
    
    const entry = DATA.find(d => d.industry === industryName);
    // console.log('Selected Industry:', industryName);
    // console.log('Industry Data:', entry);
    
    if (!entry) {
      console.warn('No data found for industry:', industryName);
      return;
    }

    entry.sectors.forEach(sector => {
      const id = generateId(sector, 'sec-');
      const $wrap = $('<div>', {
        class: 'col-md-4 col-sm-6 mb-2',
        html: `
          <div class="form-check">
            <input 
              class="form-check-input sector-checkbox" 
              type="checkbox" 
              name="sectors" 
              value="${sector}" 
              id="${id}"
            />
            <label class="form-check-label text-nowrap" for="${id}">
              ${sector}
            </label>
          </div>
        `
      });
      $sectorListEl.append($wrap);
    });
  }

  // Render technologies based on selected industry
  function renderTechnologies(industryName) {
    $techListEl.empty();
    
    COMMON_TECHNOLOGIES.forEach(tech => {
      const id = generateId(tech, 'tech-');
      const $wrap = $('<div>', {
        class: 'col-md-4 col-sm-6 mb-2',
        html: `
          <div class="form-check">
            <input 
              class="form-check-input technology-checkbox" 
              type="checkbox" 
              name="technologies" 
              value="${tech}" 
              id="${id}"
            />
            <label class="form-check-label text-nowrap" for="${id}">
              ${tech}
            </label>
          </div>
        `
      });
      $techListEl.append($wrap);
    });
  }



  // Toggle visibility of "Other" input fields
  function toggleOtherInputs() {
    const sectorOtherChecked = $('input[name="sectors"][value="Other"]').is(':checked');
    const techOtherChecked = $('input[name="technologies"][value="Other"]').is(':checked');
    
    $sectorOtherEl.toggle(sectorOtherChecked);
    $techOtherEl.toggle(techOtherChecked);
  }

  // Hide error messages
  function hideErrors() {
    $industryErr.hide();
    $sectorErr.hide();
    $techErr.hide();
  }
  
  // Industry selection change
  $industryEl.on('change', function() {
    const selectedIndustry = $(this).val();
    // console.log('Industry changed to:', selectedIndustry);
    
    renderSectors(selectedIndustry);
    renderTechnologies(selectedIndustry);
    updateLabels(selectedIndustry);
    $industryErr.hide(); // Hide industry error when industry is selected
    toggleOtherInputs();
  });

  // Handle checkbox changes using event delegation
  $(document).on('change', 'input[name="technologies"]', function() {
    toggleOtherInputs();
    $techErr.hide(); // Hide tech error when technology is selected
  });

  $(document).on('change', 'input[name="sectors"]', function() {
    toggleOtherInputs();
    $sectorErr.hide(); // Hide sector error when sector is selected
  });

  // Handle other input field changes
  $sectorOtherEl.on('input', function() {
    // Hide sector error when user types in "Other" field
    if ($(this).val().trim()) {
      $sectorErr.hide();
    }
  });

  $techOtherEl.on('input', function() {
    // Hide tech error when user types in "Other" field
    if ($(this).val().trim()) {
      $techErr.hide();
    }
  });

  // Form submission
  $form.on('submit', function(e) {
    e.preventDefault();
    
    let isValid = true;
    hideErrors();

    // Validate industry selection
    if (!$industryEl.val()) {
      $industryErr.show();
      isValid = false;
    }

    // Validate sector selection
    if (!$('input[name="sectors"]:checked').length) {
      $sectorErr.show();
      isValid = false;
    }

    // Validate technology selection
    if (!$('input[name="technologies"]:checked').length) {
      $techErr.show();
      isValid = false;
    }

    // Validate "Other" fields if "Other" is selected
    const sectorOtherChecked = $('input[name="sectors"][value="Other"]').is(':checked');
    const techOtherChecked = $('input[name="technologies"][value="Other"]').is(':checked');

    if (sectorOtherChecked && !$sectorOtherEl.val().trim()) {
      $sectorErr.text('{{ $l == "en" ? "Please specify the other sector" : "يرجى تحديد القطاع الآخر" }}').show();
      isValid = false;
    }

    if (techOtherChecked && !$techOtherEl.val().trim()) {
      $techErr.text('{{ $l == "en" ? "Please specify the other technology" : "يرجى تحديد التقنية الأخرى" }}').show();
      isValid = false;
    }

    if (!isValid) {
      return;
    }

    e.currentTarget.submit();

    // // Collect form data
    // const sectors = $('input[name="sectors"]:checked').map(function() {
    //   return $(this).val();
    // }).get();

    // const technologies = $('input[name="technologies"]:checked').map(function() {
    //   return $(this).val();
    // }).get();

    // // Add custom "Other" values if provided
    // const customSector = $sectorOtherEl.val().trim();
    // const customTech = $techOtherEl.val().trim();

    // const payload = {
    //   refId: $('#refId').val().trim() || null,
    //   industry: $industryEl.val(),
    //   sectors: customSector ? [...sectors, customSector] : sectors,
    //   technologies: customTech ? [...technologies, customTech] : technologies
    // };

    // console.log('Form submission payload:', payload);
    
    // // Here you would typically send the data to your backend
    // // Example: $.post('/api/classifications', payload).done(function(response) { ... });
    
  });

  // Initialize the form
  function init() {
    // If there's a default industry selected, render its sectors and technologies
    const defaultIndustry = $industryEl.val();
    if (defaultIndustry) {
      renderSectors(defaultIndustry);
      renderTechnologies(defaultIndustry);
      updateLabels(defaultIndustry);
    }
  }

  // Initialize when DOM is ready
  init();
});
</script>
@endpush