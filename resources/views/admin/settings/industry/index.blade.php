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
// Seed dataset with IDs
let DATA = [
  { 
    id: 1,
    industry: "Agritech", 
    sectors: [
      { id: 1, name: "Crop Production Solutions" },
      { id: 2, name: "Precision Agriculture" },
      { id: 3, name: "Farm Management Software" },
      { id: 4, name: "Agricultural Supply Chain Platforms" },
      { id: 5, name: "Agri-Biotech and Genetics" },
      { id: 6, name: "Agri-Finance and Insurance" },
      { id: 7, name: "Smart Irrigation Systems" },
      { id: 8, name: "Agri Drones and Remote Sensing" },
      { id: 9, name: "Livestock Management" },
      { id: 10, name: "Food Traceability and Safety" },
      { id: 11, name: "Other" }
    ] 
  },
  { 
    id: 2,
    industry: "Fintech", 
    sectors: [
      { id: 1, name: "Digital Banking / Neo Banks" },
      { id: 2, name: "Payments and Remittances" },
      { id: 3, name: "Payment Gateways and Processors" },
      { id: 4, name: "Lending and Microfinance" },
      { id: 5, name: "Wealth Management / Robo-Advisory" },
      { id: 6, name: "InsurTech" },
      { id: 7, name: "RegTech / Compliance" },
      { id: 8, name: "Fraud Detection and Risk Scoring" },
      { id: 9, name: "Digital Wallets" },
      { id: 10, name: "Buy Now Pay Later (BNPL)" },
      { id: 11, name: "Crypto Exchanges" },
      { id: 12, name: "Crowdfunding Platforms" },
      { id: 13, name: "Other" }
    ] 
  }
];

// Technologies with IDs
let TECHNOLOGIES = [
  { id: 1, name: "Artificial Intelligence (AI)" },
  { id: 2, name: "Machine Learning (ML)" },
  { id: 3, name: "Internet of Things (IoT)" },
  { id: 4, name: "Cloud Computing" },
  { id: 5, name: "Big Data & Analytics" },
  { id: 6, name: "Automation" },
  { id: 7, name: "Robotics" },
  { id: 8, name: "Blockchain" },
  { id: 9, name: "Mobile Applications" },
  { id: 10, name: "Web Applications" },
  { id: 11, name: "AR/VR" },
  { id: 12, name: "Other" }
];

let industriesTable = null;
let sectorsTable = null;
let techTable = null;

// ID generators
let nextIndustryId = Math.max(...DATA.map(d => d.id), 0) + 1;
let nextTechId = Math.max(...TECHNOLOGIES.map(t => t.id), 0) + 1;

function getNextSectorId(industryId) {
  const industry = DATA.find(d => d.id === industryId);
  if (!industry || !industry.sectors.length) return 1;
  return Math.max(...industry.sectors.map(s => s.id), 0) + 1;
}

// Helper functions to find by ID
function findIndustryById(id) {
  return DATA.find(d => d.id === parseInt(id));
}

function findIndustryIndexById(id) {
  return DATA.findIndex(d => d.id === parseInt(id));
}

function findSectorById(industryId, sectorId) {
  const industry = findIndustryById(industryId);
  return industry ? industry.sectors.find(s => s.id === parseInt(sectorId)) : null;
}

function findSectorIndexById(industryId, sectorId) {
  const industry = findIndustryById(industryId);
  return industry ? industry.sectors.findIndex(s => s.id === parseInt(sectorId)) : -1;
}

function findTechById(id) {
  return TECHNOLOGIES.find(t => t.id === parseInt(id));
}

function findTechIndexById(id) {
  return TECHNOLOGIES.findIndex(t => t.id === parseInt(id));
}

// Modal context
let currentModalAction = ''; // 'add' or 'edit'
let currentModalType = '';   // 'industry', 'sector', or 'tech'
let currentModalId = null;   // ID instead of index
let currentModalName = '';   // Old name for edit, or new name for add
let currentModalIndustryId = null; // Industry ID for sector edit

// AJAX Configuration
const AJAX_CONFIG = {
  baseUrl: '/industries',
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
    'edit': { method: 'PUT', endpoint: `/${params.type}/${params.id}`, data: params },
    'industry-delete': { method: 'DELETE', endpoint: `/${params.id}`, data: {} },
    'sector-delete': { method: 'DELETE', endpoint: `/sectors/${params.id}`, data: { industryId: params.industryId } },
    'tech-delete': { method: 'DELETE', endpoint: `/technologies/${params.id}`, data: {} },
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
const dataTableLanguageConfig = {};

// Generate Action Buttons - Now using IDs
const generateTableActions = (type, id, extraData = '') => {
  return `
    <div class="d-flex align-items-center justify-content-end gap-2">
      <button class="btn btn-sm btn-label-secondary text-nowrap" data-act="${type}-edit" data-id="${id}" ${type === 'sector' ? `data-industry-id="${extraData}"` : ''}>{{ __('settings.Edit') }}<i class="ti ti-sm ti-pencil ms-2"></i></button>
      <button class="btn btn-sm btn-label-danger" data-act="${type}-del" data-id="${id}" ${type === 'sector' ? `data-industry-id="${extraData}"` : ''}>{{ __('settings.Delete') }} <i class="ti ti-sm ti-trash ms-2"></i></button>
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
    ["#", "Name", "Actions"],
    (table) => {
      DATA.forEach((d) => table.row.add([null, d.industry, generateTableActions('industry', d.id)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  );

  elements.selIndustrySector.innerHTML = DATA.map(d => `<option value="${d.id}">${d.industry}</option>`).join('');
  if (elements.selIndustrySector.options.length) elements.selIndustrySector.selectedIndex = 0;

  renderSectors();
  renderPreview();
  renderTech();
  dump();
}

function renderSectors() {
  const selectedIndustryId = parseInt(elements.selIndustrySector.value);
  const industry = findIndustryById(selectedIndustryId);
  const currentSectors = industry ? industry.sectors : [];

  sectorsTable = initializeDataTable(
    'tblSectors',
    sectorsTable,
    ["#", "Sector", "Actions"],
    (table) => {
      currentSectors.forEach((s) => table.row.add([null, s.name, generateTableActions('sector', s.id, selectedIndustryId)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters-sector"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    function () { $('.filters-sector').append($('#tblSectors-filters')); }
  );
}

function renderTech() {
  techTable = initializeDataTable(
    'tblTech',
    techTable,
    ["#", "Technology", "Actions"],
    (table) => {
      TECHNOLOGIES.forEach((t) => table.row.add([null, t.name, generateTableActions('tech', t.id)]));
    },
    '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  );
}

function renderPreview() {
  elements.mapPreview.innerHTML = '';
  const selectedIndustryId = parseInt(elements.selIndustrySector.value);
  const industry = findIndustryById(selectedIndustryId);
  
  if (!industry) return;
  
  industry.sectors.forEach(s => {
    const span = document.createElement('span');
    span.className = 'badge bg-secondary me-2 mb-2';
    span.textContent = s.name;
    elements.mapPreview.appendChild(span);
  });
}

function dump() {
  elements.jsonOut.textContent = JSON.stringify({ industries: DATA, technologies: TECHNOLOGIES }, null, 2);
  elements.statusBtn.textContent = 'Unsaved changes';
  elements.statusBtn.className = 'btn btn-sm btn-label-warning';
}

// Modal Logic - Updated to use IDs
function openEditModal(action, type, id, name, industryId = null) {
  currentModalAction = action;
  currentModalType = type;
  currentModalId = id;
  currentModalName = name;
  currentModalIndustryId = industryId;

  if (type === 'industry') {
    elements.editModalLabel.text(action === 'add' ? '{{ __('settings.Add_Industry') }}' : '{{ __('settings.Edit_Industry') }}');
    elements.confirmActionBtn.text(action === 'add' ? '{{ __('settings.Add_Industry') }}' : '{{ __('settings.Save') }}');
    elements.industrySelectContainer.hide();
  } else if (type === 'sector') {
    elements.editModalLabel.text(action === 'add' ? '{{ __('settings.Add_Sector') }}' : '{{ __('settings.Edit_Sector') }}');
    elements.confirmActionBtn.text(action === 'add' ? '{{ __('settings.Add_Sector') }}' : '{{ __('settings.Save') }}');
    elements.industrySelectContainer.show();
    elements.editIndustrySelect.html(DATA.map((d) => `<option value="${d.id}" ${d.id === industryId ? 'selected' : ''}>${d.industry}</option>`).join(''));
  } else {
    elements.editModalLabel.text(action === 'add' ? '{{ __('settings.Add_Technology') }}' : '{{ __('settings.Edit_Technology') }}');
    elements.confirmActionBtn.text(action === 'add' ? '{{ __('settings.Add_Technology') }}' : '{{ __('settings.Save') }}');
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

elements.editModal.on('show.bs.modal', function() {
  elements.editInput.removeClass('is-invalid').siblings('.invalid-feedback').hide();
  elements.editIndustrySelect.removeClass('is-invalid');
});

elements.confirmActionBtn.on('click', function () {
  const newName = elements.editInput.val().trim();
  const selectedIndustryId = currentModalType === 'sector' ? parseInt(elements.editIndustrySelect.val()) : null;
  
  elements.editInput.removeClass('is-invalid').siblings('.invalid-feedback').hide();
  elements.editIndustrySelect.removeClass('is-invalid');
  
  let isValid = true;
  
  if (!newName) {
    elements.editInput.addClass('is-invalid');
    elements.editInput.siblings('.invalid-feedback[data-error="required"]').show();
    isValid = false;
  } else if (newName.length > 50) {
    elements.editInput.addClass('is-invalid');
    elements.editInput.siblings('.invalid-feedback[data-error="maxlength"]').show();
    isValid = false;
  }
  
  if (currentModalType === 'sector' && !selectedIndustryId) {
    elements.editIndustrySelect.addClass('is-invalid');
    isValid = false;
  }
  
  if (!isValid) return;
  
  elements.editModal.modal('hide');

  if (currentModalAction === 'add') {
    handleAddAction(currentModalType, newName, selectedIndustryId);
  } else if (currentModalAction === 'edit') {
    if (newName === currentModalName && (currentModalType !== 'sector' || selectedIndustryId === currentModalIndustryId)) return;
    handleEditAction(currentModalType, currentModalId, currentModalName, newName, selectedIndustryId);
  }
});

function handleAddAction(type, newName, industryId) {
  const actionMap = {
    'industry': 'add-industry',
    'sector': 'add-sector', 
    'tech': 'add-technology'
  };

  const params = {
    industry: { name: newName },
    sector: { name: newName, industryId: industryId || parseInt(elements.selIndustrySector.value) },
    tech: { name: newName }
  };

  makeAjaxRequest(actionMap[type], params[type], (success, result) => {
    if (success) {
      switch (type) {
        case 'industry':
          const newIndustryId = result.response?.id || nextIndustryId++;
          DATA.push({ id: newIndustryId, industry: newName, sectors: [{ id: 1, name: 'Other' }] });
          renderIndustries();
          break;
        case 'sector':
          const targetIndustryId = industryId || parseInt(elements.selIndustrySector.value);
          const targetIndustry = findIndustryById(targetIndustryId);
          if (targetIndustry) {
            const newSectorId = result.response?.id || getNextSectorId(targetIndustryId);
            targetIndustry.sectors.push({ id: newSectorId, name: newName });
            renderSectors();
            renderPreview();
          }
          break;
        case 'tech':
          const newTechId = result.response?.id || nextTechId++;
          if (!TECHNOLOGIES.find(t => t.name === newName)) {
            TECHNOLOGIES.push({ id: newTechId, name: newName });
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

function handleEditAction(type, id, oldName, newName, industryId) {
  const params = { type, id, oldName, newName };
  if (type === 'sector') {
    params.industryId = industryId;
    params.oldIndustryId = currentModalIndustryId;
  }

  makeAjaxRequest('edit', params, (success, result) => {
    if (success) {
      switch (type) {
        case 'industry':
          const industry = findIndustryById(id);
          if (industry) {
            industry.industry = newName;
            renderIndustries();
          }
          break;
        case 'sector':
          if (currentModalIndustryId !== industryId) {
            // Moving sector to different industry
            const oldIndustry = findIndustryById(currentModalIndustryId);
            const newIndustry = findIndustryById(industryId);
            const sectorIndex = findSectorIndexById(currentModalIndustryId, id);
            
            if (oldIndustry && newIndustry && sectorIndex !== -1) {
              const sector = oldIndustry.sectors.splice(sectorIndex, 1)[0];
              sector.name = newName;
              newIndustry.sectors.push(sector);
            }
          } else {
            // Just updating name
            const sector = findSectorById(industryId, id);
            if (sector) {
              sector.name = newName;
            }
          }
          renderSectors();
          renderPreview();
          break;
        case 'tech':
          const tech = findTechById(id);
          if (tech) {
            tech.name = newName;
            renderTech();
          }
          break;
      }
      dump();
      releaseSuccessAlert({ message: 'Changes saved successfully' });
    } else {
      releaseFailureAlert({ message: result.error || 'Failed to save changes' });
    }
  });
}

$(document).on('click', '#tblIndustries, #tblSectors, #tblTech', function (e) {
  const target = $(e.target).closest('button[data-act]');
  if (target.length === 0) return;

  const act = target.data('act');
  const id = parseInt(target.data('id'));
  const industryId = target.data('industry-id') ? parseInt(target.data('industry-id')) : null;

  const selectedIndustryId = parseInt(elements.selIndustrySector.value);

  switch (act) {
    case 'industry-edit':
      const indToEdit = findIndustryById(id);
      if (indToEdit) {
        openEditModal('edit', 'industry', id, indToEdit.industry);
      }
      break;
    case 'sector-edit':
      const secToEdit = findSectorById(industryId || selectedIndustryId, id);
      if (secToEdit) {
        openEditModal('edit', 'sector', id, secToEdit.name, industryId || selectedIndustryId);
      }
      break;
    case 'tech-edit':
      const techToEdit = findTechById(id);
      if (techToEdit) {
        openEditModal('edit', 'tech', id, techToEdit.name);
      }
      break;
    case 'industry-del':
    case 'sector-del':
    case 'tech-del':
      handleDeleteConfirmation(act.replace('-del', ''), id, industryId || selectedIndustryId);
      break;
  }
});

function handleDeleteConfirmation(type, id, industryId = null) {
  const titleMap = {
    industry: '{{ __('settings.Delete_Industry') }}',
    sector: '{{ __('settings.Delete_Sector') }}',
    tech: '{{ __('settings.Delete_Technology') }}'
  };
  const textMap = {
    industry: '{{ __('settings.Delete_Industry_Confirmation') }}',
    sector: '{{ __('settings.Delete_Sector_Confirmation') }}',
    tech: '{{ __('settings.Delete_Technology_Confirmation') }}'
  };

  Swal.fire({
    title: titleMap[type],
    text: textMap[type],
    icon: 'warning',
    showCancelButton: true,
    buttonStyling: false,
    showConfirmButton: true,
    confirmButtonText: '{{ __('settings.Delete') }}',
    cancelButtonText: '{{ __('settings.Cancel') }}',
    customClass: {
      confirmButton: 'btn btn-danger me-3',
      cancelButton: 'btn btn-label-secondary'
    },
  }).then((result) => {
    if (result.isConfirmed) {
      makeAjaxRequest(`${type}-delete`, { id: id, industryId: industryId }, (success, result) => {
        if (success) {
          switch (type) {
            case 'industry':
              const indIndex = findIndustryIndexById(id);
              if (indIndex !== -1) {
                DATA.splice(indIndex, 1);
                renderIndustries();
              }
              break;
            case 'sector':
              const industry = findIndustryById(industryId);
              const secIndex = findSectorIndexById(industryId, id);
              if (industry && secIndex !== -1) {
                industry.sectors.splice(secIndex, 1);
                renderSectors();
                renderPreview();
              }
              break;
            case 'tech':
              const techIndex = findTechIndexById(id);
              if (techIndex !== -1) {
                TECHNOLOGIES.splice(techIndex, 1);
                renderTech();
              }
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

// Add Buttons - Updated to use IDs
document.getElementById('addIndustry').addEventListener('click', () => openEditModal('add', 'industry', null, ''));
document.getElementById('addSector').addEventListener('click', () => {
  const selectedIndustryId = parseInt(elements.selIndustrySector.value);
  openEditModal('add', 'sector', null, '', selectedIndustryId);
});
document.getElementById('addTech').addEventListener('click', () => openEditModal('add', 'tech', null, ''));

document.getElementById('addOtherSector').addEventListener('click', () => {
  const selectedIndustryId = parseInt(elements.selIndustrySector.value);
  const industry = findIndustryById(selectedIndustryId);
  
  if (industry && !industry.sectors.find(s => s.name === 'Other')) {
    makeAjaxRequest('add-sector', { name: 'Other', industryId: selectedIndustryId }, (success, result) => {
      if (success) {
        const newSectorId = result.response?.id || getNextSectorId(selectedIndustryId);
        industry.sectors.push({ id: newSectorId, name: 'Other' });
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
  if (!TECHNOLOGIES.find(t => t.name === 'Other')) {
    makeAjaxRequest('add-technology', { name: 'Other' }, (success, result) => {
      if (success) {
        const newTechId = result.response?.id || nextTechId++;
        TECHNOLOGIES.push({ id: newTechId, name: 'Other' });
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
        
        // Validate structure
        if (!Array.isArray(importedData.industries) || 
            !importedData.industries.every(item => 
              item.id && item.industry && Array.isArray(item.sectors) &&
              item.sectors.every(s => s.id && s.name)
            ) || 
            !Array.isArray(importedData.technologies) ||
            !importedData.technologies.every(t => t.id && t.name)) {
          throw new Error('Invalid data structure');
        }
        
        makeAjaxRequest('save-all', { industries: importedData.industries, technologies: importedData.technologies }, (success, result) => {
          if (success) {
            DATA = importedData.industries;
            TECHNOLOGIES = importedData.technologies;
            
            // Update ID generators
            nextIndustryId = Math.max(...DATA.map(d => d.id), 0) + 1;
            nextTechId = Math.max(...TECHNOLOGIES.map(t => t.id), 0) + 1;
            
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
  if (!industriesTable) { 
    releaseFailureAlert({ message: 'No data available to export' }); 
    return; 
  }
  try { 
    industriesTable.button(`.buttons-${format}`).trigger(); 
  } catch (error) { 
    console.error('Export error:', error); 
    releaseFailureAlert({ message: 'Failed to export data. Please try again.' }); 
  }
}

window.exportSectors = function (format) {
  if (!sectorsTable) { 
    releaseFailureAlert({ message: 'No data available to export' }); 
    return; 
  }
  try { 
    sectorsTable.button(`.buttons-${format}`).trigger(); 
  } catch (error) { 
    console.error('Export error:', error); 
    releaseFailureAlert({ message: 'Failed to export data. Please try again.' }); 
  }
}

window.exportTechnologies = function (format) {
  if (!techTable) { 
    releaseFailureAlert({ message: 'No data available to export' }); 
    return; 
  }
  try { 
    techTable.button(`.buttons-${format}`).trigger(); 
  } catch (error) { 
    console.error('Export error:', error); 
    releaseFailureAlert({ message: 'Failed to export data. Please try again.' }); 
  }
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
</script>
@stop