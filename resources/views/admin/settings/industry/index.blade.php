@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="wrap">
      <!-- Navigation Pills -->
      @php
        $pills = ['industries', 'sectors', 'technologies'];
      @endphp
      <ul class="nav nav-pills mb-4" id="settingsTabs" role="tablist">
        @foreach($pills as $i => $p)
        <li class="nav-item" role="presentation">
          <button 
            class="nav-link @if($i==0) active @endif"
            id="{{ $p }}-tab"
            data-bs-toggle="pill"
            data-bs-target="#{{ $p }}"
            type="button"
            role="tab"
            aria-controls="{{ $p }}"
            aria-selected="@if($i==0) true @else false @endif"
          >
            {{ __('settings.' . ucfirst($p)) }}
          </button>
        </li>
        @endforeach
      </ul>

      <!-- Tab Content -->
      <div class="tab-content p-0" id="settingsTabContent">
        <!-- Industries Tab -->
        @include('admin.settings.industry.partials.industries-tab')

        <!-- Sectors Tab -->
        @include('admin.settings.industry.partials.sectors-tab')

        <!-- Technologies Tab -->
        @include('admin.settings.industry.partials.technologies-tab')

        <!-- Mapping Tab -->
        <div class="tab-pane fade" id="mapping" role="tabpanel" aria-labelledby="mapping-tab">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">{{ __('settings.Mapping_Preview') }}</h5>
              <div class="alert alert-info">{{ __('settings.Mapping_Info_Message') }}</div>
              <div id="mapPreview" class="d-flex flex-wrap gap-2"></div>
            </div>
          </div>
        </div>

        <!-- JSON Tab -->
        <div class="tab-pane fade" id="json" role="tabpanel" aria-labelledby="json-tab">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('settings.JSON_Output') }}</h5>
                <div>
                  <button class="btn btn-primary btn-sm me-3" id="saveAll">{{ __('settings.Save_All') }}</button>
                  <span id="status" class="btn btn-sm btn-label-warning">{{ __('settings.Unsaved_Changes') }}</span>
                </div>
              </div>
              <pre id="jsonOut"></pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">{{ __('settings.Edit') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="editInput" class="form-label">{{ __('settings.Name') }} *</label>
          <input type="text" class="form-control" id="editInput" placeholder="{{ __('settings.Enter_Name') }}" required>
          <div class="invalid-feedback" data-error="required">
            {{ __('settings.Name_Required') }}
          </div>
          <div class="invalid-feedback" data-error="maxlength" style="display: none;">
            {{ __('settings.Name_Max_50') }}
          </div>
        </div>
        <div class="mb-3" id="industrySelectContainer" style="display: none;">
          <label for="editIndustrySelect" class="form-label">{{ __('settings.Industry') }} *</label>
          <select class="form-select" id="editIndustrySelect" required></select>
          <div class="invalid-feedback">
            {{ __('settings.Industry_Required') }}
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('settings.Cancel') }}</button>
        <button type="button" class="btn btn-primary" id="confirmAction">{{ __('settings.Confirm') }}</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
// Define translation strings as a JavaScript object to avoid syntax issues
const translations = {
  up: @json(__('settings.Up')),
  down: @json(__('settings.Down')),
  edit: @json(__('settings.Edit')),
  delete: @json(__('settings.Delete')),
  add_industry: @json(__('settings.Add_Industry')),
  edit_industry: @json(__('settings.Edit_Industry')),
  save_changes: @json(__('settings.Save')),
  add_sector: @json(__('settings.Add_Sector')),
  edit_sector: @json(__('settings.Edit_Sector')),
  add_technology: @json(__('settings.Add_Technology')),
  edit_technology: @json(__('settings.Edit_Technology')),
  admin_delete: @json(__('admin.delete')),
  admin_cancel: @json(__('admin.cancel'))
};

// Seed dataset
let DATA = [
  { industry: "Agritech", sectors: ["Crop Production Solutions", "Precision Agriculture", "Farm Management Software", "Agricultural Supply Chain Platforms", "Agri-Biotech and Genetics", "Agri-Finance and Insurance", "Smart Irrigation Systems", "Agri Drones and Remote Sensing", "Livestock Management", "Food Traceability and Safety", "Other"] },
  { industry: "Fintech", sectors: ["Digital Banking / Neo Banks", "Payments and Remittances", "Payment Gateways and Processors", "Lending and Microfinance", "Wealth Management / Robo-Advisory", "InsurTech", "RegTech / Compliance", "Fraud Detection and Risk Scoring", "Digital Wallets", "Buy Now Pay Later (BNPL)", "Crypto Exchanges", "Crowdfunding Platforms", "Other"] },
  { industry: "Healthtech", sectors: ["Telemedicine and Remote Care", "Electronic Health Records (EHR)", "Medical Devices and Wearables", "Diagnostics and Imaging", "Health Data Analytics", "Wellness and Fitness Platforms", "Genomics and Precision Medicine", "Drug Discovery Platforms", "Remote Patient Monitoring", "Virtual Hospitals", "Healthcare Supply Chain", "Other"] },
  { industry: "Edtech", sectors: ["Online Learning Platforms", "K-12 Solutions", "Higher Education Solutions", "Corporate Training and LMS", "Testing and Assessment Tools", "Language Learning", "Virtual Classrooms", "Skill Development Platforms", "AR/VR Learning Environments", "Gamified Learning Apps", "Other"] },
  { industry: "E-commerce & Retail", sectors: ["Online Marketplaces", "Direct-to-Consumer Brands", "Social Commerce", "Retail Analytics and Personalization", "Logistics and Last-Mile Delivery", "Subscription Commerce", "Flash Sales Platforms", "B2B E-commerce", "Inventory Management Systems", "Omni-Channel Retail", "Digital Payments Integration", "Other"] },
  { industry: "Mobility & Logistics", sectors: ["Ride-Hailing", "Fleet Management", "Delivery and Courier Services", "Smart Public Transport", "Supply Chain Platforms", "Drone Delivery", "Micro-Mobility (e-scooters, bikes)", "Autonomous Vehicles", "Freight and Cargo Management", "Smart Parking Systems", "Mobility-as-a-Service (MaaS)", "Other"] },
  { industry: "Recycling & Greentech", sectors: ["Waste Management Platforms", "Renewable Energy Solutions", "Energy Storage Systems", "Carbon Capture and Trading", "Water Treatment Technologies", "Smart Grid Management", "Energy Efficiency Monitoring", "Sustainable Packaging", "Circular Economy Platforms", "Other"] },
  { industry: "SaaS / Enterprise Tech", sectors: ["Cloud Infrastructure", "Productivity Tools", "CRM and ERP Systems", "Cybersecurity Platforms", "HR and Payroll Systems", "Data Analytics and BI Tools", "DevOps and CI/CD Platforms", "Marketing Automation", "Customer Support Platforms", "Project Management Tools", "Workflow Automation", "Other"] },
  { industry: "Tourism & TravelTech", sectors: ["Online Booking Platforms", "Travel Management Systems", "Hospitality Solutions", "Experience Marketplaces", "Mobility Services for Travel", "Virtual Travel Experiences", "Travel Insurance Platforms", "Dynamic Pricing Engines", "Loyalty and Rewards Platforms", "Other"] },
  { industry: "Media & Entertainment", sectors: ["Streaming Platforms", "Gaming", "Digital Publishing", "Advertising Technology (AdTech)", "Music Tech", "Podcast Platforms", "AR/VR Entertainment", "Content Creation Tools", "Esports Platforms", "Social Media Networks", "Other"] },
  { industry: "Foodtech", sectors: ["Food Delivery Platforms", "Alternative Proteins", "Smart Kitchen Devices", "Food Safety and Traceability", "Meal Kit Services", "Restaurant Management Systems", "Vertical Farming", "Grocery Delivery", "Other"] },
  { industry: "AI & Deeptech", sectors: ["Machine Learning Platforms", "Natural Language Processing", "Computer Vision", "Robotics", "Quantum Computing", "Edge AI Solutions", "Neural Interface Technology", "Autonomous Systems", "Other"] },
  { industry: "LegalTech", sectors: ["Contract Management", "e-Discovery", "Compliance Platforms", "Online Dispute Resolution", "Case Management Systems", "IP Management Tools", "Legal Research Platforms", "Other"] },
  { industry: "Social Impact & Civic Tech", sectors: ["GovTech Services", "Community Platforms", "Non-Profit Management Tools", "Civic Engagement Apps", "Digital Identity Solutions", "Open Data Platforms", "Public Service Portals", "Other"] },
  { industry: "Manufacturing", sectors: ["Smart Factory (Industry 4.0)", "3D Printing and Additive Manufacturing", "Supply Chain Automation", "Industrial IoT", "Predictive Maintenance", "Robotics Integration", "Quality Control Systems", "Digital Twin Platforms", "Advanced Materials", "Other"] },
  { industry: "Creative Arts", sectors: ["Digital Art Platforms", "Music Production Tools", "Design Marketplaces", "Content Creation Platforms", "Virtual Galleries", "NFT Art Marketplaces", "AR/VR Creative Experiences", "Other"] }
];

let TECHNOLOGIES = [
  "Artificial Intelligence (AI)", 
  "Machine Learning (ML)", 
  "Internet of Things (IoT)", 
  "Cloud Computing", 
  "Big Data & Analytics", 
  "Automation", 
  "Robotics", 
  "Blockchain", 
  "Mobile Applications", 
  "Web Applications", 
  "AR/VR", 
  "Other"
];

let industriesTable = null;
let sectorsTable = null;
let techTable = null;

// Modal context
let currentModalAction = ''; // 'add' or 'edit'
let currentModalType = '';   // 'industry', 'sector', or 'tech'
let currentModalIndex = -1;  // Index for industry/sector
let currentModalName = '';   // Old name for edit, or new name for add
let currentModalIndustryIndex = -1; // Industry index for sector edit

// AJAX Configuration
const AJAX_CONFIG = {
  baseUrl: '/admin/industries',
  timeout: 10000,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

if (AJAX_CONFIG.csrfToken) {
  AJAX_CONFIG.headers['X-CSRF-TOKEN'] = AJAX_CONFIG.csrfToken;
}

// AJAX Request Function
function makeAjaxRequest(action, params, callback) {
  // Check for network connectivity first
  if (!navigator.onLine) {
    releaseFailureAlert({ message: 'No internet connection. Please check your network and try again.' });
    callback(false, { error: 'No internet connection' });
    return;
  }

  const actionConfig = {
    'up': { method: 'POST', endpoint: '/reorder', data: { ...params, direction: 'up' } },
    'down': { method: 'POST', endpoint: '/reorder', data: { ...params, direction: 'down' } },
    'sec-up': { method: 'POST', endpoint: '/sectors/reorder', data: { ...params, direction: 'up' } },
    'sec-down': { method: 'POST', endpoint: '/sectors/reorder', data: { ...params, direction: 'down' } },
    'edit': { method: 'PUT', endpoint: `/${params.type}/edit`, data: params },
    'industry-delete': { method: 'DELETE', endpoint: `/${params.index}`, data: {} },
    'sector-delete': { method: 'DELETE', endpoint: `/sectors/${params.index}`, data: { industryIndex: params.industryIndex } },
    'tech-delete': { method: 'DELETE', endpoint: `/technologies/${encodeURIComponent(params.name)}`, data: {} },
    'add-industry': { method: 'POST', endpoint: '', data: params },
    'add-sector': { method: 'POST', endpoint: '/sectors', data: params },
    'add-technology': { method: 'POST', endpoint: '/technologies', data: params },
    'save-all': { method: 'POST', endpoint: '/save-all', data: { industries: DATA, technologies: TECHNOLOGIES } }
  };

  const config = actionConfig[action];
  if (!config) {
    console.error('Unknown action:', action);
    releaseFailureAlert({ message: 'Invalid operation requested' });
    callback(false, { error: 'Unknown action' });
    return;
  }

  const requestOptions = {
    method: config.method,
    headers: AJAX_CONFIG.headers,
    timeout: AJAX_CONFIG.timeout
  };

  if (['POST', 'PUT', 'PATCH'].includes(config.method)) {
    requestOptions.body = JSON.stringify(config.data);
  }

  // Only show loading for successful requests
  let shouldShowLoading = true;
  
  fetch(`${AJAX_CONFIG.baseUrl}${config.endpoint}`, requestOptions)
    .then(response => {
      if (!response.ok) {
        shouldShowLoading = false;
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      if (shouldShowLoading) showLoadingIndicator(true);
      return response.json();
    })
    .then(data => {
      if (shouldShowLoading) showLoadingIndicator(false);
      if (data.success !== false) {
        callback(true, { ...params, response: data });
      } else {
        releaseFailureAlert({ message: data.message || 'Operation failed' });
        callback(false, { ...params, error: data.message || 'Operation failed' });
      }
    })
    .catch(error => {
      if (shouldShowLoading) showLoadingIndicator(false);
      console.error('AJAX request failed:', error);
      
      let errorMessage = 'Network error occurred';
      if (error.name === 'AbortError') {
        errorMessage = 'Request timeout';
      } else if (error.message.includes('HTTP error')) {
        errorMessage = `Server error: ${error.message}`;
      } else if (error.message.includes('Failed to fetch')) {
        errorMessage = 'Network connection failed';
      }
      
      releaseFailureAlert({ message: errorMessage });
      callback(false, { ...params, error: errorMessage });
    });
}

// Loading Indicator
function showLoadingIndicator(show) {
  let indicator = document.getElementById('loadingIndicator');
  if (show) {
    if (!indicator) {
      indicator = document.createElement('div');
      indicator.id = 'loadingIndicator';
      indicator.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-25';
      indicator.style.zIndex = '9999';
      indicator.innerHTML = `
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      `;
      document.body.appendChild(indicator);
    }
    indicator.style.display = 'flex';
  } else {
    if (indicator) {
      indicator.style.display = 'none';
    }
  }
}

// DOM Elements
const elements = {
  selIndustrySector: document.getElementById('selIndustrySector'),
  jsonOut: document.getElementById('jsonOut'),
  statusBtn: document.getElementById('status'),
  mapPreview: document.getElementById('mapPreview'),
  editModal: $('#editModal'),
  editModalLabel: $('#editModalLabel'),
  editInput: $('#editInput'),
  editIndustrySelect: $('#editIndustrySelect'),
  industrySelectContainer: $('#industrySelectContainer'),
  confirmActionBtn: $('#confirmAction'),
};

// DataTable Language Configuration
const dataTableLanguageConfig = {
  @if ($l == 'ar')
    search: @json(__('admin.Search')),
    lengthMenu: @json(__('admin.Show')) + " _MENU_ " + @json(__('admin.Entries')),
    info: @json(__('admin.showing')) + " _START_ " + @json(__('admin.To')) + " _END_ " + @json(__('admin.Of')) + " _TOTAL_ " + @json(__('admin.Entries')),
    infoEmpty: @json(__('admin.no_entries_available')),
    infoFiltered: "(" + @json(__('admin.filtered from')) + " _MAX_ " + @json(__('admin.total entries')) + ")",
    paginate: {
      first: @json(__('admin.First')),
      last: @json(__('admin.Last')),
      next: @json(__('admin.Next')),
      previous: @json(__('admin.Previous'))
    },
    emptyTable: @json(__('admin.No_data_available_in_table')),
    zeroRecords: @json(__('admin.No_matching_records_found'))
  @endif
};

// Generate Action Buttons
const generateTableActions = (type, indexOrName, extraData = '') => {
  const baseActions = {
    industry: `
      <button class="btn btn-sm btn-label-info" data-act="up" data-i="${indexOrName}">${translations.up} <i class="ti ti-sm ti-arrow-up ms-2"></i></button>
      <button class="btn btn-sm btn-label-warning" data-act="down" data-i="${indexOrName}">${translations.down} <i class="ti ti-sm ti-arrow-down ms-2"></i></button>
    `,
    sector: `
      <button class="btn btn-sm btn-label-info" data-act="sec-up" data-j="${indexOrName}">${translations.up} <i class="ti ti-sm ti-arrow-up ms-2"></i></button>
      <button class="btn btn-sm btn-label-warning" data-act="sec-down" data-j="${indexOrName}">${translations.down} <i class="ti ti-sm ti-arrow-down ms-2"></i></button>
    `,
    tech: ``
  };
  const editData = type === 'tech' ? `data-name="${indexOrName}"` : `data-i="${indexOrName}"`;
  const deleteData = type === 'tech' ? `data-name="${indexOrName}"` : `data-i="${indexOrName}"`;

  return `
    <div class="d-flex align-items-center justify-content-end gap-2">
      ${baseActions[type] || ''}
      <button class="btn btn-sm btn-label-secondary text-nowrap" data-act="${type}-edit" ${editData}>${translations.edit} <i class="ti ti-sm ti-pencil ms-2"></i></button>
      <button class="btn btn-sm btn-label-danger" data-act="${type}-del" ${deleteData}>${translations.delete} <i class="ti ti-sm ti-trash ms-2"></i></button>
    </div>
  `;
};

// Initialize DataTable
function initializeDataTable(tableId, tableVar, columns, dataPopulator, domConfig = 'Bfrtip', extraInitComplete = null) {
  if (!tableVar) {
    tableVar = $(`#${tableId}`).DataTable({
      responsive: true,
      pageLength: 10,
      ordering: false,
      language: dataTableLanguageConfig,
      columnDefs: [
        { targets: 0, render: (data, type, row, meta) => meta.row + 1 },
        { orderable: false, targets: columns.length - 1 }
      ],
      columns: columns.map(title => ({ title: title })),
      dom: domConfig,
      initComplete: extraInitComplete,
      buttons: [
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `${tableId}_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1] } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `${tableId}_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1] } }
      ]
    });
  }
  tableVar.clear();
  dataPopulator(tableVar);
  tableVar.draw();
  return tableVar;
}

function renderIndustries() {
  industriesTable = initializeDataTable(
    'tblIndustries',
    industriesTable,
    ["#", @json(__('settings.Name')), @json(__('settings.Actions'))],
    (table) => {
      DATA.forEach((d, i) => table.row.add([null, d.industry, generateTableActions('industry', i)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  );

  elements.selIndustrySector.innerHTML = DATA.map(d => `<option value="${d.industry}">${d.industry}</option>`).join('');
  if (elements.selIndustrySector.options.length) elements.selIndustrySector.selectedIndex = 0;

  renderSectors();
  renderPreview();
  renderTech();
  dump();
}

function renderSectors() {
  const idx = elements.selIndustrySector.selectedIndex;
  const currentSectors = idx >= 0 ? DATA[idx].sectors : [];

  sectorsTable = initializeDataTable(
    'tblSectors',
    sectorsTable,
    ["#", @json(__('settings.Sector')), @json(__('settings.Actions'))],
    (table) => {
      currentSectors.forEach((s, j) => table.row.add([null, s, generateTableActions('sector', j)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters-sector"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    function () { $('.filters-sector').append($('#tblSectors-filters')); }
  );
}

function renderTech() {
  techTable = initializeDataTable(
    'tblTech',
    techTable,
    ["#", @json(__('settings.Technology')), @json(__('settings.Actions'))],
    (table) => {
      TECHNOLOGIES.forEach((t) => table.row.add([null, t, generateTableActions('tech', t)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  );
}

function renderPreview() {
  elements.mapPreview.innerHTML = '';
  const idx = elements.selIndustrySector.selectedIndex;
  if (idx < 0) return;
  DATA[idx].sectors.forEach(s => {
    const span = document.createElement('span');
    span.className = 'badge bg-secondary me-2 mb-2';
    span.textContent = s;
    elements.mapPreview.appendChild(span);
  });
}

function dump() {
  elements.jsonOut.textContent = JSON.stringify({ industries: DATA, technologies: TECHNOLOGIES }, null, 2);
  elements.statusBtn.textContent = 'Unsaved changes';
  elements.statusBtn.className = 'btn btn-sm btn-label-warning';
}

// Modal Logic
function openEditModal(action, type, index, name, industryIndex = -1) {
  currentModalAction = action;
  currentModalType = type;
  currentModalIndex = index;
  currentModalName = name;
  currentModalIndustryIndex = industryIndex;

  if (type === 'industry') {
    elements.editModalLabel.text(action === 'add' ? translations.add_industry : translations.edit_industry);
    elements.confirmActionBtn.text(action === 'add' ? translations.add_industry : translations.save_changes);
    elements.industrySelectContainer.hide();
  } else if (type === 'sector') {
    elements.editModalLabel.text(action === 'add' ? translations.add_sector : translations.edit_sector);
    elements.confirmActionBtn.text(action === 'add' ? translations.add_sector : translations.save_changes);
    elements.industrySelectContainer.show();
    elements.editIndustrySelect.html(DATA.map((d, i) => `<option value="${i}" ${i === industryIndex ? 'selected' : ''}>${d.industry}</option>`).join(''));
  } else {
    elements.editModalLabel.text(action === 'add' ? translations.add_technology : translations.edit_technology);
    elements.confirmActionBtn.text(action === 'add' ? translations.add_technology : translations.save_changes);
    elements.industrySelectContainer.hide();
  }

  elements.editInput.val(name || '');
  elements.editModal.modal('show');
}

// Input change handlers
elements.editInput.on('input', function() {
  const value = $(this).val().trim();
  const maxLengthFeedback = $(this).siblings('.invalid-feedback[data-error="maxlength"]');
  const requiredFeedback = $(this).siblings('.invalid-feedback[data-error="required"]');
  
  // Clear all validation states first
  $(this).removeClass('is-invalid');
  maxLengthFeedback.hide();
  requiredFeedback.hide();
  
  if (!value) {
    $(this).addClass('is-invalid');
    requiredFeedback.show();
  } else if (value.length > 50) {
    $(this).addClass('is-invalid');
    maxLengthFeedback.show();
  }
});

elements.editIndustrySelect.on('change', function() {
  const value = $(this).val();
  if (value !== null && value !== '') {
    $(this).removeClass('is-invalid');
  }
});

// Reset validation state when modal opens
elements.editModal.on('show.bs.modal', function() {
  elements.editInput.removeClass('is-invalid').siblings('.invalid-feedback').hide();
  elements.editIndustrySelect.removeClass('is-invalid');
});

elements.confirmActionBtn.on('click', function () {
  const newName = elements.editInput.val().trim();
  const selectedIndustryIndex = currentModalType === 'sector' ? parseInt(elements.editIndustrySelect.val()) : -1;
  
  // Reset validation state
  elements.editInput.removeClass('is-invalid').siblings('.invalid-feedback').hide();
  elements.editIndustrySelect.removeClass('is-invalid');
  
  // Validate input length and required fields
  let isValid = true;
  
  // Name validation
  if (!newName) {
    elements.editInput.addClass('is-invalid');
    elements.editInput.siblings('.invalid-feedback[data-error="required"]').show();
    isValid = false;
  } else if (newName.length > 50) {
    elements.editInput.addClass('is-invalid');
    elements.editInput.siblings('.invalid-feedback[data-error="maxlength"]').show();
    isValid = false;
  }
  
  // Industry validation for sector type
  if (currentModalType === 'sector' && selectedIndustryIndex === -1) {
    elements.editIndustrySelect.addClass('is-invalid');
    isValid = false;
  }
  
  if (!isValid) return;
  
  elements.editModal.modal('hide');

  if (currentModalAction === 'add') {
    handleAddAction(currentModalType, newName, selectedIndustryIndex);
  } else if (currentModalAction === 'edit') {
    if (newName === currentModalName && (currentModalType !== 'sector' || selectedIndustryIndex === currentModalIndustryIndex)) return;
    handleEditAction(currentModalType, currentModalIndex, currentModalName, newName, selectedIndustryIndex);
  }
});

function handleAddAction(type, newName, industryIndex) {
  const actionMap = {
    'industry': 'add-industry',
    'sector': 'add-sector', 
    'tech': 'add-technology'
  };

  const params = {
    industry: { name: newName },
    sector: { name: newName, industryIndex: industryIndex >= 0 ? industryIndex : elements.selIndustrySector.selectedIndex },
    tech: { name: newName }
  };

  makeAjaxRequest(actionMap[type], params[type], (success, result) => {
    if (success) {
      switch (type) {
        case 'industry':
          DATA.push({ industry: newName, sectors: ['Other'] });
          renderIndustries();
          break;
        case 'sector':
          const idxSector = industryIndex >= 0 ? industryIndex : elements.selIndustrySector.selectedIndex;
          if (idxSector >= 0) {
            DATA[idxSector].sectors.push(newName);
            renderSectors();
            renderPreview();
          }
          break;
        case 'tech':
          if (!TECHNOLOGIES.includes(newName)) {
            TECHNOLOGIES.push(newName);
            renderTech();
          }
          break;
      }
      dump();
      releaseSuccessAlert({ message: `${type.charAt(0).toUpperCase() + type.slice(1)} added successfully` });
    } else {
      releaseFailureAlert({ message: result.error || `Failed to add ${type}` });
    }
  });
}

function handleEditAction(type, index, oldName, newName, industryIndex) {
  const params = type === 'sector' ? { type, index, oldName, newName, industryIndex, oldIndustryIndex: currentModalIndustryIndex } : { type, index, oldName, newName };

  makeAjaxRequest('edit', params, (success, result) => {
    if (success) {
      switch (type) {
        case 'industry':
          DATA[index].industry = newName;
          renderIndustries();
          break;
        case 'sector':
          if (currentModalIndustryIndex !== industryIndex) {
            DATA[currentModalIndustryIndex].sectors.splice(index, 1);
            DATA[industryIndex].sectors.push(newName);
          } else {
            DATA[industryIndex].sectors[index] = newName;
          }
          renderSectors();
          renderPreview();
          break;
        case 'tech':
          TECHNOLOGIES = TECHNOLOGIES.map(t => t === oldName ? newName : t);
          renderTech();
          break;
      }
      dump();
      releaseSuccessAlert({ message: 'Changes saved successfully' });
    } else {
      releaseFailureAlert({ message: result.error || 'Failed to save changes' });
    }
  });
}

// CRUD Actions
function moveItem(arr, index, direction) {
  if (direction === 'up' && index > 0) {
    [arr[index], arr[index - 1]] = [arr[index - 1], arr[index]];
    return true;
  }
  if (direction === 'down' && index < arr.length - 1) {
    [arr[index], arr[index + 1]] = [arr[index + 1], arr[index]];
    return true;
  }
  return false;
}

$(document).on('click', '#tblIndustries, #tblSectors, #tblTech', function (e) {
  const target = $(e.target).closest('button[data-act]');
  if (target.length === 0) return;

  const act = target.data('act');
  const i = target.data('i');
  const j = target.data('j');
  const name = target.data('name');

  const currentIndustryIndex = elements.selIndustrySector.selectedIndex;

  switch (act) {
    case 'up':
    case 'down':
      makeAjaxRequest(act, { index: i, type: 'industry' }, (success, result) => {
        if (success && moveItem(DATA, i, act)) {
          renderIndustries();
          dump();
          releaseSuccessAlert({ message: `Moved ${act} successfully` });
        } else if (!success) {
          releaseFailureAlert({ message: result.error || `Failed to move ${act}` });
        }
      });
      break;
    case 'sec-up':
    case 'sec-down':
      makeAjaxRequest(act, { index: j, industryIndex: currentIndustryIndex }, (success, result) => {
        if (success && moveItem(DATA[currentIndustryIndex].sectors, j, act.split('-')[1])) {
          renderSectors();
          renderPreview();
          dump();
          releaseSuccessAlert({ message: `Sector moved ${act.split('-')[1]} successfully` });
        } else if (!success) {
          releaseFailureAlert({ message: result.error || `Failed to move sector ${act.split('-')[1]}` });
        }
      });
      break;
    case 'industry-edit':
      openEditModal('edit', 'industry', i, DATA[i].industry);
      break;
    case 'sector-edit':
      openEditModal('edit', 'sector', j, DATA[currentIndustryIndex].sectors[j], currentIndustryIndex);
      break;
    case 'tech-edit':
      openEditModal('edit', 'tech', -1, name);
      break;
    case 'industry-del':
    case 'sector-del':
    case 'tech-del':
      handleDeleteConfirmation(act.replace('-del', ''), i || j || name, currentIndustryIndex);
      break;
  }
});

function handleDeleteConfirmation(type, indexOrName, industryIndex = -1) {
  const titleMap = {
    industry: 'Delete Industry',
    sector: 'Delete Sector',
    tech: 'Delete Technology'
  };
  const textMap = {
    industry: 'Are you sure you want to delete this industry?',
    sector: 'Are you sure you want to delete this sector?',
    tech: 'Are you sure you want to delete this technology?'
  };

  Swal.fire({
    title: titleMap[type],
    text: textMap[type],
    icon: 'warning',
    showCancelButton: true,
    buttonStyling: false,
    showConfirmButton: true,
    confirmButtonText: translations.admin_delete,
    cancelButtonText: translations.admin_cancel,
    customClass: {
      confirmButton: 'btn btn-danger me-3',
      cancelButton: 'btn btn-label-secondary'
    },
  }).then((result) => {
    if (result.isConfirmed) {
      makeAjaxRequest(`${type}-delete`, { index: indexOrName, name: indexOrName, industryIndex }, (success, result) => {
        if (success) {
          switch (type) {
            case 'industry':
              DATA.splice(indexOrName, 1);
              renderIndustries();
              break;
            case 'sector':
              DATA[industryIndex].sectors.splice(indexOrName, 1);
              renderSectors();
              renderPreview();
              break;
            case 'tech':
              TECHNOLOGIES = TECHNOLOGIES.filter(x => x !== indexOrName);
              renderTech();
              break;
          }
          dump();
          releaseSuccessAlert({ message: `${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully` });
        } else {
          releaseFailureAlert({ message: result.error || `Failed to delete ${type}` });
        }
      });
    }
  });
}

// Add Buttons
document.getElementById('addIndustry').addEventListener('click', () => openEditModal('add', 'industry', -1, ''));
document.getElementById('addSector').addEventListener('click', () => openEditModal('add', 'sector', -1, '', elements.selIndustrySector.selectedIndex));
document.getElementById('addTech').addEventListener('click', () => openEditModal('add', 'tech', -1, ''));

document.getElementById('addOtherSector').addEventListener('click', () => {
  const idx = elements.selIndustrySector.selectedIndex;
  if (idx >= 0 && !DATA[idx].sectors.includes('Other')) {
    makeAjaxRequest('add-sector', { name: 'Other', industryIndex: idx }, (success, result) => {
      if (success) {
        DATA[idx].sectors.push('Other');
        renderSectors();
        renderPreview();
        dump();
        releaseSuccessAlert({ message: 'Other sector added successfully' });
      } else {
        releaseFailureAlert({ message: result.error || 'Failed to add Other sector' });
      }
    });
  }
});

document.getElementById('addOtherTech').addEventListener('click', () => {
  if (!TECHNOLOGIES.includes('Other')) {
    makeAjaxRequest('add-technology', { name: 'Other' }, (success, result) => {
      if (success) {
        TECHNOLOGIES.push('Other');
        renderTech();
        dump();
        releaseSuccessAlert({ message: 'Other technology added successfully' });
      } else {
        releaseFailureAlert({ message: result.error || 'Failed to add Other technology' });
      }
    });
  }
});

// Select Change Events
elements.selIndustrySector.addEventListener('change', () => {
  renderSectors();
  renderPreview();
});

// Export and Import
document.getElementById('exportJson').addEventListener('click', () => {
  const blob = new Blob([JSON.stringify({ industries: DATA, technologies: TECHNOLOGIES }, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'industry-sector-technology.json';
  a.click();
  URL.revokeObjectURL(url);
});

document.getElementById('importJson').addEventListener('click', () => {
  const inp = document.createElement('input');
  inp.type = 'file';
  inp.accept = 'application/json';
  inp.onchange = () => {
    const f = inp.files[0];
    if (!f) return;
    const reader = new FileReader();
    reader.onload = () => {
      try {
        const importedData = JSON.parse(reader.result);
        if (!Array.isArray(importedData.industries) || !importedData.industries.every(item => 
          item.industry && Array.isArray(item.sectors)
        ) || !Array.isArray(importedData.technologies)) {
          throw new Error('Invalid data structure');
        }
        makeAjaxRequest('save-all', { industries: importedData.industries, technologies: importedData.technologies }, (success, result) => {
          if (success) {
            DATA = importedData.industries;
            TECHNOLOGIES = importedData.technologies;
            renderIndustries();
            renderTech();
            dump();
            releaseSuccessAlert({ message: 'JSON imported successfully' });
          } else {
            releaseFailureAlert({ message: result.error || 'Failed to import JSON data' });
          }
        });
      } catch (e) {
        releaseFailureAlert({ message: 'Invalid JSON file format' });
        console.error('JSON parse error:', e);
      }
    };
    reader.readAsText(f);
  };
  inp.click();
});

document.getElementById('saveAll').addEventListener('click', () => {
  makeAjaxRequest('save-all', { industries: DATA, technologies: TECHNOLOGIES }, (success, result) => {
    if (success) {
      elements.jsonOut.textContent = JSON.stringify({ industries: DATA, technologies: TECHNOLOGIES }, null, 2);
      elements.statusBtn.textContent = 'Saved';
      elements.statusBtn.className = 'btn btn-sm btn-label-success';
      releaseSuccessAlert({ message: 'All changes saved successfully' });
      setTimeout(() => {
        elements.statusBtn.textContent = 'All saved';
        elements.statusBtn.className = 'btn btn-sm btn-label-success';
      }, 1200);
    } else {
      releaseFailureAlert({ message: result.error || 'Failed to save changes' });
    }
  });
});

// Global Export Functions
window.exportIndustries = function (format) {
  if (!industriesTable) { releaseFailureAlert({ message: 'No data available to export' }); return; }
  try { industriesTable.button(`.buttons-${format}`).trigger(); } catch (error) { console.error('Export error:', error); releaseFailureAlert({ message: 'Failed to export data. Please try again.' }); }
}
window.exportSectors = function (format) {
  if (!sectorsTable) { releaseFailureAlert({ message: 'No data available to export' }); return; }
  try { sectorsTable.button(`.buttons-${format}`).trigger(); } catch (error) { console.error('Export error:', error); releaseFailureAlert({ message: 'Failed to export data. Please try again.' }); }
}
window.exportTechnologies = function (format) {
  if (!techTable) { releaseFailureAlert({ message: 'No data available to export' }); return; }
  try { techTable.button(`.buttons-${format}`).trigger(); } catch (error) { console.error('Export error:', error); releaseFailureAlert({ message: 'Failed to export data. Please try again.' }); }
}

// Network Status Monitoring
let isOnline = navigator.onLine;
window.addEventListener('online', () => {
  isOnline = true;
  releaseSuccessAlert({ message: 'Connection restored' });
});
window.addEventListener('offline', () => {
  isOnline = false;
  releaseFailureAlert({ message: 'Connection lost - changes will be saved when connection is restored' });
});

// Retry Mechanism
function retryFailedRequest(action, params, maxRetries = 3, currentRetry = 0) {
  makeAjaxRequest(action, params, (success, result) => {
    if (!success && currentRetry < maxRetries && isOnline) {
      setTimeout(() => {
        console.log(`Retrying request ${currentRetry + 1}/${maxRetries}`);
        retryFailedRequest(action, params, maxRetries, currentRetry + 1);
      }, 1000 * Math.pow(2, currentRetry));
    } else if (!success) {
      releaseFailureAlert({ message: result.error || 'Request failed after multiple attempts' });
    }
  });
}

// Initialize
renderIndustries();
dump();

console.log('Industry Management System initialized');
console.log('AJAX Configuration:', AJAX_CONFIG);
console.log('Available endpoints:', Object.keys({
  'GET /industries': 'Get all industries',
  'POST /industries': 'Create new industry', 
  'PUT /industries/{id}': 'Update industry',
  'DELETE /industries/{id}': 'Delete industry',
  'POST /industries/reorder': 'Reorder industries',
  'POST /industries/sectors': 'Create new sector',
  'PUT /industries/sectors/{id}': 'Update sector',
  'DELETE /industries/sectors/{id}': 'Delete sector',
  'POST /industries/sectors/reorder': 'Reorder sectors',
  'POST /industries/technologies': 'Create new technology',
  'PUT /industries/technologies/{name}': 'Update technology',
  'DELETE /industries/technologies/{name}': 'Delete technology',
  'POST /industries/save-all': 'Save all changes'
}));
</script>
@stop