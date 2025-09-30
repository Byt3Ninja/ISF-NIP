@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="wrap">
      <!-- Navigation Pills -->
      @php
        $pills = ['industries', 'sectors', 'technologies'];
      @endphp
      <ul class="nav nav-pills mb-6" id="settingsTabs" role="tablist">
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
        <div class="tab-pane fade show active" id="industries" role="tabpanel" aria-labelledby="industries-tab">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="card-title">{{ __('settings.Industries') }}</h5>
                <div class="d-flex align-items-center gap-2">
                  <button class="btn btn-primary btn-sm" id="addIndustry"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Industry') }}</button>
                  <button class="btn btn-outline-warning btn-sm" id="importJson"><i class="ti ti-upload ti-sm me-1"></i>{{ __('settings.Import_JSON') }}</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export') }}
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" id="exportJson">
                          <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export_JSON') }}
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportIndustries('csv')">
                          <i class="ti ti-file-text me-1"></i>{{ __('settings.Export_CSV') }}
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportIndustries('excel')">
                          <i class="ti ti-file-spreadsheet me-1"></i>{{ __('settings.Export_Excel') }}
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="tblIndustries">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px">#</th>
                      <th>{{ __('settings.Name') }}</th>
                      <th style="width:160px">{{ __('settings.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Sectors Tab -->
        <div class="tab-pane fade" id="sectors" role="tabpanel" aria-labelledby="sectors-tab">
          <div class="card">
            <div class="card-body">
              <div class="row d-flex justify-content-end" id="tblSectors-filters">
                <div class="col-12 col-md-4 mb-2 mb-md-0">
                  <div class="d-flex align-items-center gap-2">
                    <label for="selIndustrySector" class="fs-6">{{ __('settings.Industry') }}:</label>
                    <select class="form-select form-select-sm" id="selIndustrySector"></select>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="card-title">{{ __('settings.Sectors') }}</h5>
                <div class="d-flex align-items-center gap-2">
                  <button class="btn btn-primary btn-sm" id="addSector"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Sector') }}</button>
                  <button class="btn btn-outline-secondary btn-sm" id="addOtherSector"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Other') }}</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export') }}
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportSectors('csv')">
                          <i class="ti ti-file-text me-1"></i>{{ __('settings.Export_CSV') }}
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportSectors('excel')">
                          <i class="ti ti-file-spreadsheet me-1"></i>{{ __('settings.Export_Excel') }}
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="tblSectors">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px">#</th>
                      <th>{{ __('settings.Sector') }}</th>
                      <th class="text-end">{{ __('settings.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Technologies Tab -->
        <div class="tab-pane fade" id="technologies" role="tabpanel" aria-labelledby="technologies-tab">
          <div class="card">
            <div class="card-body">
              <div class="row d-flex justify-content-end" id="tblTech-filters">
               <div class="col-12 col-md-4 mb-2 mb-md-0">
                 <div class="d-flex align-items-center gap-2">
                   <label for="selIndustryTech" class="fs-6">{{ __('settings.Industry') }}:</label>
                   <select class="form-select form-select-sm" id="selIndustryTech"></select>
                 </div>
               </div>
             </div>
              <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="card-title">{{ __('settings.Technologies') }}</h5>
                <div class="d-flex align-items-center gap-2">
                  <button class="btn btn-primary btn-sm" id="addTech"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Technology') }}</button>
                  <button class="btn btn-outline-secondary btn-sm" id="addOtherTech"><i class="ti ti-plus ti-sm me-1"></i>{{ __('settings.Add_Other') }}</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-download ti-sm me-1"></i>{{ __('settings.Export') }}
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportTechnologies('csv')">
                          <i class="ti ti-file-text me-1"></i>{{ __('settings.Export_CSV') }}
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="exportTechnologies('excel')">
                          <i class="ti ti-file-spreadsheet me-1"></i>{{ __('settings.Export_Excel') }}
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="tblTech">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px">#</th>
                      <th>{{ __('settings.Technology') }}</th>
                      <th style="width:160px" class="text-end">{{ __('settings.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

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

<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="renameModalLabel">{{ __('settings.Rename') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="renameInput" class="form-label">{{ __('settings.New_Name') }}</label>
          <input type="text" class="form-control" id="renameInput" placeholder="Enter new name">
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
  // Seed dataset - unchanged
let DATA = [
  { industry: "Agritech", sectors: ["Crop Production Solutions", "Precision Agriculture", "Farm Management Software", "Agricultural Supply Chain Platforms", "Agri-Biotech and Genetics", "Agri-Finance and Insurance", "Smart Irrigation Systems", "Agri Drones and Remote Sensing", "Livestock Management", "Food Traceability and Safety", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Internet of Things (IoT)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Blockchain", "Mobile Applications", "Web Applications", "Other"] },
  { industry: "Fintech", sectors: ["Digital Banking / Neo Banks", "Payments and Remittances", "Payment Gateways and Processors", "Lending and Microfinance", "Wealth Management / Robo-Advisory", "InsurTech", "RegTech / Compliance", "Fraud Detection and Risk Scoring", "Digital Wallets", "Buy Now Pay Later (BNPL)", "Crypto Exchanges", "Crowdfunding Platforms", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Blockchain", "Cloud Computing", "Big Data & Analytics", "Mobile Applications", "Web Applications", "Automation", "Internet of Things (IoT)", "Other"] },
  { industry: "Healthtech", sectors: ["Telemedicine and Remote Care", "Electronic Health Records (EHR)", "Medical Devices and Wearables", "Diagnostics and Imaging", "Health Data Analytics", "Wellness and Fitness Platforms", "Genomics and Precision Medicine", "Drug Discovery Platforms", "Remote Patient Monitoring", "Virtual Hospitals", "Healthcare Supply Chain", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Internet of Things (IoT)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Blockchain", "Mobile Applications", "Web Applications", "Other"] },
  { industry: "Edtech", sectors: ["Online Learning Platforms", "K-12 Solutions", "Higher Education Solutions", "Corporate Training and LMS", "Testing and Assessment Tools", "Language Learning", "Virtual Classrooms", "Skill Development Platforms", "AR/VR Learning Environments", "Gamified Learning Apps", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Automation", "Mobile Applications", "Web Applications", "Internet of Things (IoT)", "AR/VR", "Other"] },
  { industry: "E-commerce & Retail", sectors: ["Online Marketplaces", "Direct-to-Consumer Brands", "Social Commerce", "Retail Analytics and Personalization", "Logistics and Last-Mile Delivery", "Subscription Commerce", "Flash Sales Platforms", "B2B E-commerce", "Inventory Management Systems", "Omni-Channel Retail", "Digital Payments Integration", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Mobile Applications", "Web Applications", "Automation", "Internet of Things (IoT)", "Blockchain", "Other"] },
  { industry: "Mobility & Logistics", sectors: ["Ride-Hailing", "Fleet Management", "Delivery and Courier Services", "Smart Public Transport", "Supply Chain Platforms", "Drone Delivery", "Micro-Mobility (e-scooters, bikes)", "Autonomous Vehicles", "Freight and Cargo Management", "Smart Parking Systems", "Mobility-as-a-Service (MaaS)", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Internet of Things (IoT)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Mobile Applications", "Web Applications", "Blockchain", "Other"] },
  { industry: "Recycling & Greentech", sectors: ["Waste Management Platforms", "Renewable Energy Solutions", "Energy Storage Systems", "Carbon Capture and Trading", "Water Treatment Technologies", "Smart Grid Management", "Energy Efficiency Monitoring", "Sustainable Packaging", "Circular Economy Platforms", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Internet of Things (IoT)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Blockchain", "Mobile Applications", "Web Applications", "Other"] },
  { industry: "SaaS / Enterprise Tech", sectors: ["Cloud Infrastructure", "Productivity Tools", "CRM and ERP Systems", "Cybersecurity Platforms", "HR and Payroll Systems", "Data Analytics and BI Tools", "DevOps and CI/CD Platforms", "Marketing Automation", "Customer Support Platforms", "Project Management Tools", "Workflow Automation", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Automation", "Web Applications", "Mobile Applications", "Blockchain", "Other"] },
  { industry: "Tourism & TravelTech", sectors: ["Online Booking Platforms", "Travel Management Systems", "Hospitality Solutions", "Experience Marketplaces", "Mobility Services for Travel", "Virtual Travel Experiences", "Travel Insurance Platforms", "Dynamic Pricing Engines", "Loyalty and Rewards Platforms", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Mobile Applications", "Web Applications", "Automation", "Blockchain", "Other"] },
  { industry: "Media & Entertainment", sectors: ["Streaming Platforms", "Gaming", "Digital Publishing", "Advertising Technology (AdTech)", "Music Tech", "Podcast Platforms", "AR/VR Entertainment", "Content Creation Tools", "Esports Platforms", "Social Media Networks", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Mobile Applications", "Web Applications", "Automation", "Blockchain", "AR/VR", "Other"] },
  { industry: "Foodtech", sectors: ["Food Delivery Platforms", "Alternative Proteins", "Smart Kitchen Devices", "Food Safety and Traceability", "Meal Kit Services", "Restaurant Management Systems", "Vertical Farming", "Grocery Delivery", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Internet of Things (IoT)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Mobile Applications", "Web Applications", "Blockchain", "Other"] },
  { industry: "AI & Deeptech", sectors: ["Machine Learning Platforms", "Natural Language Processing", "Computer Vision", "Robotics", "Quantum Computing", "Edge AI Solutions", "Neural Interface Technology", "Autonomous Systems", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Blockchain", "Web Applications", "Mobile Applications", "Other"] },
  { industry: "LegalTech", sectors: ["Contract Management", "e-Discovery", "Compliance Platforms", "Online Dispute Resolution", "Case Management Systems", "IP Management Tools", "Legal Research Platforms", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Automation", "Blockchain", "Web Applications", "Mobile Applications", "Other"] },
  { industry: "Social Impact & Civic Tech", sectors: ["GovTech Services", "Community Platforms", "Non-Profit Management Tools", "Civic Engagement Apps", "Digital Identity Solutions", "Open Data Platforms", "Public Service Portals", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Automation", "Blockchain", "Web Applications", "Mobile Applications", "Other"] },
  { industry: "Manufacturing", sectors: ["Smart Factory (Industry 4.0)", "3D Printing and Additive Manufacturing", "Supply Chain Automation", "Industrial IoT", "Predictive Maintenance", "Robotics Integration", "Quality Control Systems", "Digital Twin Platforms", "Advanced Materials", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Internet of Things (IoT)", "Cloud Computing", "Big Data & Analytics", "Automation", "Robotics", "Blockchain", "Mobile Applications", "Web Applications", "Other"] },
  { industry: "Creative Arts", sectors: ["Digital Art Platforms", "Music Production Tools", "Design Marketplaces", "Content Creation Platforms", "Virtual Galleries", "NFT Art Marketplaces", "AR/VR Creative Experiences", "Other"], technologies: ["Artificial Intelligence (AI)", "Machine Learning (ML)", "Cloud Computing", "Big Data & Analytics", "Automation", "Blockchain", "Web Applications", "Mobile Applications", "AR/VR", "Other"] }
];

let industriesTable = null;
let sectorsTable = null;
let techTable = null;

// Modal context
let currentModalAction = ''; // 'add' or 'rename'
let currentModalType = '';   // 'industry', 'sector', or 'tech'
let currentModalIndex = -1;  // Index for industry/sector
let currentModalName = '';   // Old name for rename, or new name for add

// AJAX Configuration - Update these URLs according to your backend routes
const AJAX_CONFIG = {
  baseUrl: '/admin/industries', // Base URL for your industry management API
  timeout: 10000, // 10 seconds timeout
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  // Add CSRF token if using Laravel or similar framework
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

// Add CSRF token to headers if available
if (AJAX_CONFIG.csrfToken) {
  AJAX_CONFIG.headers['X-CSRF-TOKEN'] = AJAX_CONFIG.csrfToken;
}

// Real AJAX function
function makeAjaxRequest(action, params, callback) {
  // Show loading indicator
  showLoadingIndicator(true);

  // Map actions to HTTP methods and endpoints
  const actionConfig = {
    'up': { method: 'POST', endpoint: '/reorder', data: { ...params, direction: 'up' } },
    'down': { method: 'POST', endpoint: '/reorder', data: { ...params, direction: 'down' } },
    'sec-up': { method: 'POST', endpoint: '/sectors/reorder', data: { ...params, direction: 'up' } },
    'sec-down': { method: 'POST', endpoint: '/sectors/reorder', data: { ...params, direction: 'down' } },
    'rename': { method: 'PUT', endpoint: `/${params.type}/rename`, data: params },
    'industry-delete': { method: 'DELETE', endpoint: `/${params.index}`, data: {} },
    'sector-delete': { method: 'DELETE', endpoint: `/sectors/${params.index}`, data: { industryIndex: params.industryIndex } },
    'tech-delete': { method: 'DELETE', endpoint: `/technologies/${encodeURIComponent(params.name)}`, data: {} },
    'add-industry': { method: 'POST', endpoint: '', data: params },
    'add-sector': { method: 'POST', endpoint: '/sectors', data: params },
    'add-technology': { method: 'POST', endpoint: '/technologies', data: params },
    'save-all': { method: 'POST', endpoint: '/save-all', data: { industries: DATA } }
  };

  const config = actionConfig[action];
  if (!config) {
    console.error('Unknown action:', action);
    showLoadingIndicator(false);
    callback(false, { error: 'Unknown action' });
    return;
  }

  // Prepare request options
  const requestOptions = {
    method: config.method,
    headers: AJAX_CONFIG.headers,
    timeout: AJAX_CONFIG.timeout
  };

  // Add body for POST, PUT, PATCH requests
  if (['POST', 'PUT', 'PATCH'].includes(config.method)) {
    requestOptions.body = JSON.stringify(config.data);
  }

  // Make the request
  fetch(`${AJAX_CONFIG.baseUrl}${config.endpoint}`, requestOptions)
    .then(response => {
      showLoadingIndicator(false);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return response.json();
    })
    .then(data => {
      // Handle successful response
      if (data.success !== false) {
        callback(true, { ...params, response: data });
      } else {
        callback(false, { ...params, error: data.message || 'Operation failed' });
      }
    })
    .catch(error => {
      showLoadingIndicator(false);
      console.error('AJAX request failed:', error);
      
      // Handle different types of errors
      let errorMessage = 'Network error occurred';
      if (error.name === 'AbortError') {
        errorMessage = 'Request timeout';
      } else if (error.message.includes('HTTP error')) {
        errorMessage = `Server error: ${error.message}`;
      } else if (error.message.includes('Failed to fetch')) {
        errorMessage = 'Network connection failed';
      }
      
      callback(false, { ...params, error: errorMessage });
    });
}

// Loading indicator functions
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
  selIndustryTech: document.getElementById('selIndustryTech'),
  jsonOut: document.getElementById('jsonOut'),
  statusBtn: document.getElementById('status'),
  mapPreview: document.getElementById('mapPreview'),
  renameModal: $('#renameModal'),
  renameModalLabel: $('#renameModalLabel'),
  renameInput: $('#renameInput'),
  confirmActionBtn: $('#confirmAction'),
};

// Helper to get all unique technologies
const getAllTechnologies = () => Array.from(new Set(DATA.flatMap(d => d.technologies))).sort();

// Reusable DataTable language configuration
const dataTableLanguageConfig = {
  @if ($l == 'ar')
    "search": "{{ __('admin.Search') }}:",
    "lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
    "info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
    "infoEmpty": "{{ __('admin.no_entries_available') }}",
    "infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
    "paginate": {
      "first": "{{ __('admin.First') }}",
      "last": "{{ __('admin.Last') }}",
      "next": "{{ __('admin.Next') }}",
      "previous": "{{ __('admin.Previous') }}"
    },
    "emptyTable": "{{ __('admin.No_data_available_in_table') }}",
    "zeroRecords": "{{ __('admin.No_matching_records_found') }}"
  @endif
};

// Function to generate action buttons for tables
const generateTableActions = (type, indexOrName, extraData = '') => {
  const baseActions = {
    industry: `
      <button class="btn btn-sm btn-label-info" data-act="up" data-i="${indexOrName}">{{ __('settings.Up') }} <i class="ti ti-sm ti-arrow-up ms-2"></i></button>
      <button class="btn btn-sm btn-label-warning" data-act="down" data-i="${indexOrName}">{{ __('settings.Down') }} <i class="ti ti-sm ti-arrow-down ms-2"></i></button>
    `,
    sector: `
      <button class="btn btn-sm btn-label-info" data-act="sec-up" data-j="${indexOrName}">{{ __('settings.Up') }} <i class="ti ti-sm ti-arrow-up ms-2"></i></button>
      <button class="btn btn-sm btn-label-warning" data-act="sec-down" data-j="${indexOrName}">{{ __('settings.Down') }} <i class="ti ti-sm ti-arrow-down ms-2"></i></button>
    `,
    tech: `` // No up/down for tech in this table as it's global
  };
  const renameData = type === 'tech' ? `data-name="${indexOrName}"` : `data-i="${indexOrName}"`;
  const deleteData = type === 'tech' ? `data-name="${indexOrName}"` : `data-i="${indexOrName}"`;

  return `
    <div class="d-flex align-items-center justify-content-end gap-2">
      ${baseActions[type] || ''}
      <button class="btn btn-sm btn-label-secondary text-nowrap" data-act="${type}-rename" ${renameData}>{{ __('settings.Rename') }} <i class="ti ti-sm ti-pencil ms-2"></i></button>
      <button class="btn btn-sm btn-label-danger" data-act="${type}-del" ${deleteData}>{{ __('settings.Delete') }} <i class="ti ti-sm ti-trash ms-2"></i></button>
    </div>
`;
};

// Initialize or re-render DataTables
function initializeDataTable(tableId, tableVar, columns, dataPopulator, domConfig = 'Bfrtip', extraInitComplete = null) {
  if (!tableVar) {
    tableVar = $(`#${tableId}`).DataTable({
      "responsive": true,
      "pageLength": 10,
      "ordering": false,
      "language": dataTableLanguageConfig,
      "columnDefs": [
        { "targets": 0, "render": (data, type, row, meta) => meta.row + 1 },
        { "orderable": false, "targets": columns.length - 1 }
      ],
      "columns": columns.map(title => ({ "title": title })),
      "dom": domConfig,
      initComplete: extraInitComplete,
      "buttons": [
        { "extend": 'csv', "text": '<i class="ti ti-file-text me-1"></i>CSV', "className": 'd-none', "title": `${tableId}_Export_${new Date().toISOString().slice(0, 10)}`, "exportOptions": { "columns": [0, 1] } },
        { "extend": 'excel', "text": '<i class="ti ti-file-spreadsheet me-1"></i>Excel', "className": 'd-none', "title": `${tableId}_Export_${new Date().toISOString().slice(0, 10)}`, "exportOptions": { "columns": [0, 1] } }
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
    ["#", "{{ __('settings.Name') }}", "{{ __('settings.Actions') }}"],
    (table) => {
      DATA.forEach((d, i) => table.row.add([null, d.industry, generateTableActions('industry', i)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    // function () { $('.filters-sector').append($('#tblSectors-filters')); }
  );

  // Update dropdowns
  elements.selIndustrySector.innerHTML = DATA.map(d => `<option value="${d.industry}">${d.industry}</option>`).join('');
  if (elements.selIndustrySector.options.length) elements.selIndustrySector.selectedIndex = 0;
  elements.selIndustryTech.innerHTML = elements.selIndustrySector.innerHTML;
  elements.selIndustryTech.selectedIndex = elements.selIndustrySector.selectedIndex;

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
      ["#", "{{ __('settings.Sector') }}", "{{ __('settings.Actions') }}"],
    (table) => {
      currentSectors.forEach((s, j) => table.row.add([null, s, generateTableActions('sector', j)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters-sector"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    function () { $('.filters-sector').append($('#tblSectors-filters')); }
  );
}

function renderTech() {
  const idx = elements.selIndustryTech.selectedIndex;
  const currentTechnologies = idx >= 0 ? DATA[idx].technologies : [];

  techTable = initializeDataTable(
    'tblTech',
    techTable,
    ["#", "{{ __('settings.Technology') }}", "{{ __('settings.Actions') }}"],
    (table) => {
      currentTechnologies.forEach((t) => table.row.add([null, t, generateTableActions('tech', t)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters-tech"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    function () { $('.filters-tech').append($('#tblTech-filters')); }
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
  elements.jsonOut.textContent = JSON.stringify(DATA, null, 2);
  elements.statusBtn.textContent = 'Unsaved changes';
  elements.statusBtn.className = 'btn btn-sm btn-label-warning';
}

// --- Modal Logic ---
function openRenameModal(action, type, index, name) {
  currentModalAction = action;
  currentModalType = type;
  currentModalIndex = index;
  currentModalName = name;

  if (type === 'industry') {
    elements.renameModalLabel.text('{{ __('settings.Add_Industry') }}');
    elements.confirmActionBtn.text('{{ __('settings.Add_Industry') }}');
  } else if (type === 'sector') {
    elements.renameModalLabel.text('{{ __('settings.Add_Sector') }}');
    elements.confirmActionBtn.text('{{ __('settings.Add_Sector') }}');
  } else {
    elements.renameModalLabel.text('{{ __('settings.Add_Technology') }}');
    elements.confirmActionBtn.text('{{ __('settings.Add_Technology') }}');
  }

  elements.renameInput.val(name || '');
  elements.renameModal.modal('show');
}

elements.confirmActionBtn.on('click', function () {
  const newName = elements.renameInput.val().trim();
  if (!newName) return;
  elements.renameModal.modal('hide');

  if (currentModalAction === 'add') {
    handleAddAction(currentModalType, newName);
  } else if (currentModalAction === 'rename') {
    if (newName === currentModalName) return;
    handleRenameAction(currentModalType, currentModalIndex, currentModalName, newName);
  }
});

function handleAddAction(type, newName) {
  const actionMap = {
    'industry': 'add-industry',
    'sector': 'add-sector', 
    'tech': 'add-technology'
  };

  const params = {
    industry: { name: newName },
    sector: { name: newName, industryIndex: elements.selIndustrySector.selectedIndex },
    tech: { name: newName }
  };

  makeAjaxRequest(actionMap[type], params[type], (success, result) => {
    if (success) {
      switch (type) {
        case 'industry':
          DATA.push({ industry: newName, sectors: ['Other'], technologies: getAllTechnologies() });
          renderIndustries();
          break;
        case 'sector':
          const idxSector = elements.selIndustrySector.selectedIndex;
          if (idxSector >= 0) {
            DATA[idxSector].sectors.push(newName);
            renderSectors();
            renderPreview();
          }
          break;
        case 'tech':
          DATA.forEach(d => { if (!d.technologies.includes(newName)) d.technologies.push(newName); });
          renderTech();
          break;
      }
      dump();
      releaseSuccessAlert({ message: `${type.charAt(0).toUpperCase() + type.slice(1)} added successfully` });
    } else {
      releaseFailureAlert({ message: result.error || `Failed to add ${type}` });
    }
  });
}

function handleRenameAction(type, index, oldName, newName) {
  makeAjaxRequest('rename', { type, index, oldName, newName }, (success, result) => {
    if (success) {
      switch (type) {
        case 'industry':
          DATA[index].industry = newName;
          renderIndustries();
          renderTech();
          break;
        case 'sector':
          const idxSector = elements.selIndustrySector.selectedIndex;
          DATA[idxSector].sectors[index] = newName;
          renderSectors();
          renderPreview();
          break;
        case 'tech':
          DATA.forEach(d => {
            d.technologies = d.technologies.map(t => t === oldName ? newName : t);
          });
          renderTech();
          break;
      }
      dump();
      releaseSuccessAlert({ message: 'Renamed successfully' });
    } else {
      releaseFailureAlert({ message: result.error || 'Failed to rename' });
    }
  });
}

// --- CRUD Actions ---

// Generic move function
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

// Event delegation for table actions (up, down, rename, delete)
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
          renderTech();
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
    case 'industry-rename':
      openRenameModal('rename', 'industry', i, DATA[i].industry);
      break;
    case 'sec-rename':
      openRenameModal('rename', 'sector', j, DATA[currentIndustryIndex].sectors[j]);
      break;
    case 'tech-rename':
      openRenameModal('rename', 'tech', -1, name);
      break;
    case 'industry-del':
    case 'sec-del':
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
    tech: 'Are you sure you want to delete this technology globally?'
  };

  Swal.fire({
    title: titleMap[type],
    text: textMap[type],
    icon: 'warning',
    showCancelButton: true,
    buttonStyling: false,
    showConfirmButton: true,
    confirmButtonText: "{{ __('admin.delete') }}",
    cancelButtonText: "{{ __('admin.cancel') }}",
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
              renderTech();
              break;
            case 'sector':
              DATA[industryIndex].sectors.splice(indexOrName, 1);
              renderSectors();
              renderPreview();
              break;
            case 'tech':
              DATA.forEach(d => { d.technologies = d.technologies.filter(x => x !== indexOrName); });
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

// Add buttons
document.getElementById('addIndustry').addEventListener('click', () => openRenameModal('add', 'industry', -1, ''));
document.getElementById('addSector').addEventListener('click', () => openRenameModal('add', 'sector', -1, ''));
document.getElementById('addTech').addEventListener('click', () => openRenameModal('add', 'tech', -1, ''));

// Add 'Other' options
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
  makeAjaxRequest('add-technology', { name: 'Other' }, (success, result) => {
    if (success) {
      DATA.forEach(d => { if (!d.technologies.includes('Other')) d.technologies.push('Other'); });
      renderTech();
      dump();
      releaseSuccessAlert({ message: 'Other technology added successfully' });
    } else {
      releaseFailureAlert({ message: result.error || 'Failed to add Other technology' });
    }
  });
});

// Select change events
elements.selIndustrySector.addEventListener('change', () => {
  renderSectors();
  renderPreview();
  elements.selIndustryTech.selectedIndex = elements.selIndustrySector.selectedIndex;
  renderTech();
});
elements.selIndustryTech.addEventListener('change', () => renderTech());

// Export and Import
document.getElementById('exportJson').addEventListener('click', () => {
  const blob = new Blob([JSON.stringify(DATA, null, 2)], { type: 'application/json' });
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
        
        // Validate imported data structure
        if (!Array.isArray(importedData) || !importedData.every(item => 
          item.industry && Array.isArray(item.sectors) && Array.isArray(item.technologies)
        )) {
          throw new Error('Invalid data structure');
        }
        
        // Send to server for validation and saving
        makeAjaxRequest('save-all', { industries: importedData }, (success, result) => {
          if (success) {
            DATA = importedData;
            renderIndustries();
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
  makeAjaxRequest('save-all', { industries: DATA }, (success, result) => {
    if (success) {
      elements.jsonOut.textContent = JSON.stringify(DATA, null, 2);
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

// Global export functions for DataTables
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

// // Auto-save functionality (optional)
// let autoSaveTimer = null;
// function scheduleAutoSave() {
//   if (autoSaveTimer) {
//     clearTimeout(autoSaveTimer);
//   }
  
//   autoSaveTimer = setTimeout(() => {
//     makeAjaxRequest('save-all', { industries: DATA }, (success, result) => {
//       if (success) {
//         console.log('Auto-save completed');
//       } else {
//         console.warn('Auto-save failed:', result.error);
//       }
//     });
//   }, 30000); // Auto-save every 30 seconds
// }

// Call scheduleAutoSave() whenever data changes
const originalDump = dump;
dump = function() {
  originalDump();
  // scheduleAutoSave();
};

// Network status monitoring
let isOnline = navigator.onLine;
window.addEventListener('online', () => {
  isOnline = true;
  releaseSuccessAlert({ message: 'Connection restored' });
});
window.addEventListener('offline', () => {
  isOnline = false;
  releaseFailureAlert({ message: 'Connection lost - changes will be saved when connection is restored' });
});

// Retry mechanism for failed requests
function retryFailedRequest(action, params, maxRetries = 3, currentRetry = 0) {
  makeAjaxRequest(action, params, (success, result) => {
    if (!success && currentRetry < maxRetries && isOnline) {
      setTimeout(() => {
        console.log(`Retrying request ${currentRetry + 1}/${maxRetries}`);
        retryFailedRequest(action, params, maxRetries, currentRetry + 1);
      }, 1000 * Math.pow(2, currentRetry)); // Exponential backoff
    } else if (!success) {
      releaseFailureAlert({ message: result.error || 'Request failed after multiple attempts' });
    }
  });
}

// Initialize on load
renderIndustries();
dump();

// Add helpful console messages for debugging
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
  'POST /industries/save-all': 'Save all changes' // import industries
}));
</script>
@stop