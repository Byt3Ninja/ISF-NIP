@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="wrap">
      {{-- <h1>Admin settings</h1>
      <div class="sub">Manage industries, sectors, technologies and mappings. Import or export JSON for bulk edits.
      </div> --}}

      <!-- Navigation Pills -->
      <ul class="nav nav-pills mb-3" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="industries-tab" data-bs-toggle="pill" data-bs-target="#industries" type="button" role="tab" aria-controls="industries" aria-selected="true">Industries</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="sectors-tab" data-bs-toggle="pill" data-bs-target="#sectors" type="button" role="tab" aria-controls="sectors" aria-selected="false">Sectors</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="technologies-tab" data-bs-toggle="pill" data-bs-target="#technologies" type="button" role="tab" aria-controls="technologies" aria-selected="false">Technologies</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="mapping-tab" data-bs-toggle="pill" data-bs-target="#mapping" type="button" role="tab" aria-controls="mapping" aria-selected="false">Mapping</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="json-tab" data-bs-toggle="pill" data-bs-target="#json" type="button" role="tab" aria-controls="json" aria-selected="false">JSON</button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content p-0" id="settingsTabContent">
        <!-- Industries Tab -->
        <div class="tab-pane fade show active" id="industries" role="tabpanel" aria-labelledby="industries-tab">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Industries</h5>
                <div>
                  <button class="btn btn-primary btn-sm" id="addIndustry">Add industry</button>
                  <button class="btn btn-outline-warning btn-sm" id="importJson">Import JSON</button>
                  <button class="btn btn-outline-secondary btn-sm" id="exportJson">Export JSON</button>
                  <button class="btn btn-outline-secondary btn-sm" onclick="exportIndustries('csv')"><i class="ti ti-file-text me-1"></i>Export CSV</button>
                  <button class="btn btn-outline-secondary btn-sm" onclick="exportIndustries('excel')"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="tblIndustries">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px">#</th>
                      <th>Name</th>
                      <th style="width:160px">Actions</th>
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
              <div class="row mb-3">
                <label for="selIndustry">Industry focus</label>
                <select class="form-select" id="selIndustry"></select>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Sectors</h5>
                <div>
                  <button class="btn btn-primary btn-sm" id="addSector">Add sector</button>
                  <button class="btn btn-outline-secondary btn-sm" id="addOtherSector">Add Other</button>
                  <button class="btn btn-outline-secondary btn-sm" onclick="exportSectors('csv')"><i class="ti ti-file-text me-1"></i>Export CSV</button>
                  <button class="btn btn-outline-secondary btn-sm" onclick="exportSectors('excel')"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="tblSectors">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px">#</th>
                      <th>Sector</th>
                      <th class="text-end">Actions</th>
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
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Technologies</h5>
                <div>
                  <button class="btn btn-primary btn-sm" id="addTech">Add technology</button>
                  <button class="btn btn-outline-secondary btn-sm" id="addOtherTech">Add Other</button>
                  <button class="btn btn-outline-secondary btn-sm" onclick="exportTechnologies('csv')"><i class="ti ti-file-text me-1"></i>Export CSV</button>
                  <button class="btn btn-outline-secondary btn-sm" onclick="exportTechnologies('excel')"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="tblTech">
                  <thead class="table-light">
                    <tr>
                      <th style="width:40px">#</th>
                      <th>Technology</th>
                      <th style="width:160px" class="text-end">Actions</th>
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
              <h5 class="card-title">Mapping preview</h5>
              <div class="alert alert-info">Select an industry to view its sectors. Technologies are global.</div>
              <div id="mapPreview" class="d-flex flex-wrap gap-2"></div>
            </div>
          </div>
        </div>

        <!-- JSON Tab -->
        <div class="tab-pane fade" id="json" role="tabpanel" aria-labelledby="json-tab">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">JSON Output</h5>
                <div>
                  <button class="btn btn-primary btn-sm me-3" id="saveAll">Save all</button>
                  <span id="status" class="btn btn-sm btn-label-warning">Unsaved changes</span>
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
        <h5 class="modal-title" id="renameModalLabel">Rename</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="renameInput" class="form-label">New Name</label>
          <input type="text" class="form-control" id="renameInput" placeholder="Enter new name">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
      </div>
    </div>
  </div>
</div>

@stop

@section('pagescripts')
<script>
  // Seed dataset same as previous plus Other entries
    let DATA = [
      {industry: "Agritech", sectors: ["Crop Production Solutions","Precision Agriculture","Farm Management Software","Agricultural Supply Chain Platforms","Agri-Biotech and Genetics","Agri-Finance and Insurance","Smart Irrigation Systems","Agri Drones and Remote Sensing","Livestock Management","Food Traceability and Safety","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Blockchain","Mobile Applications","Web Applications","Other"]},
      {industry: "Fintech", sectors: ["Digital Banking / Neo Banks","Payments and Remittances","Payment Gateways and Processors","Lending and Microfinance","Wealth Management / Robo-Advisory","InsurTech","RegTech / Compliance","Fraud Detection and Risk Scoring","Digital Wallets","Buy Now Pay Later (BNPL)","Crypto Exchanges","Crowdfunding Platforms","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Blockchain","Cloud Computing","Big Data & Analytics","Mobile Applications","Web Applications","Automation","Internet of Things (IoT)","Other"]},
      {industry: "Healthtech", sectors: ["Telemedicine and Remote Care","Electronic Health Records (EHR)","Medical Devices and Wearables","Diagnostics and Imaging","Health Data Analytics","Wellness and Fitness Platforms","Genomics and Precision Medicine","Drug Discovery Platforms","Remote Patient Monitoring","Virtual Hospitals","Healthcare Supply Chain","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Blockchain","Mobile Applications","Web Applications","Other"]},
      {industry: "Edtech", sectors: ["Online Learning Platforms","K-12 Solutions","Higher Education Solutions","Corporate Training and LMS","Testing and Assessment Tools","Language Learning","Virtual Classrooms","Skill Development Platforms","AR/VR Learning Environments","Gamified Learning Apps","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Automation","Mobile Applications","Web Applications","Internet of Things (IoT)","AR/VR","Other"]},
      {industry: "E-commerce & Retail", sectors: ["Online Marketplaces","Direct-to-Consumer Brands","Social Commerce","Retail Analytics and Personalization","Logistics and Last-Mile Delivery","Subscription Commerce","Flash Sales Platforms","B2B E-commerce","Inventory Management Systems","Omni-Channel Retail","Digital Payments Integration","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Mobile Applications","Web Applications","Automation","Internet of Things (IoT)","Blockchain","Other"]},
      {industry: "Mobility & Logistics", sectors: ["Ride-Hailing","Fleet Management","Delivery and Courier Services","Smart Public Transport","Supply Chain Platforms","Drone Delivery","Micro-Mobility (e-scooters, bikes)","Autonomous Vehicles","Freight and Cargo Management","Smart Parking Systems","Mobility-as-a-Service (MaaS)","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Mobile Applications","Web Applications","Blockchain","Other"]},
      {industry: "Recycling & Greentech", sectors: ["Waste Management Platforms","Renewable Energy Solutions","Energy Storage Systems","Carbon Capture and Trading","Water Treatment Technologies","Smart Grid Management","Energy Efficiency Monitoring","Sustainable Packaging","Circular Economy Platforms","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Blockchain","Mobile Applications","Web Applications","Other"]},
      {industry: "SaaS / Enterprise Tech", sectors: ["Cloud Infrastructure","Productivity Tools","CRM and ERP Systems","Cybersecurity Platforms","HR and Payroll Systems","Data Analytics and BI Tools","DevOps and CI/CD Platforms","Marketing Automation","Customer Support Platforms","Project Management Tools","Workflow Automation","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Automation","Web Applications","Mobile Applications","Blockchain","Other"]},
      {industry: "Tourism & TravelTech", sectors: ["Online Booking Platforms","Travel Management Systems","Hospitality Solutions","Experience Marketplaces","Mobility Services for Travel","Virtual Travel Experiences","Travel Insurance Platforms","Dynamic Pricing Engines","Loyalty and Rewards Platforms","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Mobile Applications","Web Applications","Automation","Blockchain","Other"]},
      {industry: "Media & Entertainment", sectors: ["Streaming Platforms","Gaming","Digital Publishing","Advertising Technology (AdTech)","Music Tech","Podcast Platforms","AR/VR Entertainment","Content Creation Tools","Esports Platforms","Social Media Networks","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Mobile Applications","Web Applications","Automation","Blockchain","AR/VR","Other"]},
      {industry: "Foodtech", sectors: ["Food Delivery Platforms","Alternative Proteins","Smart Kitchen Devices","Food Safety and Traceability","Meal Kit Services","Restaurant Management Systems","Vertical Farming","Grocery Delivery","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Mobile Applications","Web Applications","Blockchain","Other"]},
      {industry: "AI & Deeptech", sectors: ["Machine Learning Platforms","Natural Language Processing","Computer Vision","Robotics","Quantum Computing","Edge AI Solutions","Neural Interface Technology","Autonomous Systems","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Blockchain","Web Applications","Mobile Applications","Other"]},
      {industry: "LegalTech", sectors: ["Contract Management","e-Discovery","Compliance Platforms","Online Dispute Resolution","Case Management Systems","IP Management Tools","Legal Research Platforms","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Automation","Blockchain","Web Applications","Mobile Applications","Other"]},
      {industry: "Social Impact & Civic Tech", sectors: ["GovTech Services","Community Platforms","Non-Profit Management Tools","Civic Engagement Apps","Digital Identity Solutions","Open Data Platforms","Public Service Portals","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Automation","Blockchain","Web Applications","Mobile Applications","Other"]},
      {industry: "Manufacturing", sectors: ["Smart Factory (Industry 4.0)","3D Printing and Additive Manufacturing","Supply Chain Automation","Industrial IoT","Predictive Maintenance","Robotics Integration","Quality Control Systems","Digital Twin Platforms","Advanced Materials","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Internet of Things (IoT)","Cloud Computing","Big Data & Analytics","Automation","Robotics","Blockchain","Mobile Applications","Web Applications","Other"]},
      {industry: "Creative Arts", sectors: ["Digital Art Platforms","Music Production Tools","Design Marketplaces","Content Creation Platforms","Virtual Galleries","NFT Art Marketplaces","AR/VR Creative Experiences","Other"], technologies: ["Artificial Intelligence (AI)","Machine Learning (ML)","Cloud Computing","Big Data & Analytics","Automation","Blockchain","Web Applications","Mobile Applications","AR/VR","Other"]}
    ]

    let industriesTable = null;
    let sectorsTable = null;
    let techTable = null;

    // Rename modal context
    let currentRenameType = '';
    let currentRenameIndex = -1;
    let currentRenameName = '';

    // Add modal context
    let currentActionType = '';
    let currentAddType = '';

    // Simulate AJAX function
    function simulateAjax(action, params, callback) {
      setTimeout(() => {
        const success = Math.random() > 0.2; // 80% success rate
        callback(success, params);
      }, 500);
    }

    const tblIndustries = document.getElementById('tblIndustries').querySelector('tbody')
    const tblSectors = document.getElementById('tblSectors').querySelector('tbody')
    const tblTech = document.getElementById('tblTech').querySelector('tbody')
    const selIndustry = document.getElementById('selIndustry')
    const out = document.getElementById('jsonOut')
    const status = document.getElementById('status')

    const techMaster = () => Array.from(new Set(DATA.flatMap(d => d.technologies))).sort()

    function renderIndustries(){
      if (!industriesTable) {
        industriesTable = $('#tblIndustries').DataTable({
          "responsive": true,
          "pageLength": 10,
          "ordering": false,
          "columnDefs": [
            {
              "targets": 0,
              "render": function (data, type, row, meta) {
                return meta.row + 1;
              }
            },
            { "orderable": false, "targets": 2 }
          ],
          "columns": [
            { "title": "#" },
            { "title": "Name" },
            { "title": "Actions", "orderable": false }
          ],
          "dom": 'Bfrtip',
          "buttons": [
            {
              "extend": 'csv',
              "text": '<i class="ti ti-file-text me-1"></i>CSV',
              "className": 'd-none',
              "title": 'Industries_Export_' + new Date().toISOString().slice(0,10),
              "exportOptions": {
                "columns": [0, 1]
              }
            },
            {
              "extend": 'excel',
              "text": '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
              "className": 'd-none',
              "title": 'Industries_Export_' + new Date().toISOString().slice(0,10),
              "exportOptions": {
                "columns": [0, 1]
              }
            }
          ]
        });
      }
      industriesTable.clear();
      DATA.forEach((d, i) => {
        const actions = `<div class="d-flex align-items-center justify-content-end gap-2">
          <button class="btn btn-sm btn-label-info" data-act="up" data-i="${i}">
            Up <i class="ti ti-sm ti-arrow-up ms-2"></i>
          </button>
          <button class="btn btn-sm btn-label-warning" data-act="down" data-i="${i}">
            Down <i class="ti ti-sm ti-arrow-down ms-2"></i>
          </button>
          <button class="btn btn-sm btn-label-secondary" data-act="rename" data-i="${i}">
            Rename <i class="ti ti-sm ti-pencil ms-2"></i>
          </button>
          <button class="btn btn-sm btn-label-danger" data-act="del" data-i="${i}">
            Delete <i class="ti ti-sm ti-trash ms-2"></i>
          </button>
        </div>`;
        industriesTable.row.add([null, d.industry, actions]);
      });
      industriesTable.draw();
      selIndustry.innerHTML = ''
      DATA.forEach((d, i) => {
        const opt = document.createElement('option')
        opt.value = d.industry
        opt.textContent = d.industry
        selIndustry.appendChild(opt)
      })
      if(selIndustry.options.length) selIndustry.selectedIndex = 0
      renderSectors()
      renderPreview()
      dump()
    }

    function renderSectors(){
      if (!sectorsTable) {
        sectorsTable = $('#tblSectors').DataTable({
          "responsive": true,
          "pageLength": 10,
          "ordering": false,
          "columnDefs": [
            {
              "targets": 0,
              "render": function (data, type, row, meta) {
                return meta.row + 1;
              }
            },
            { "orderable": false, "targets": 2 }
          ],
          "columns": [
            { "title": "#" },
            { "title": "Sector" },
            { "title": "Actions", "orderable": false }
          ],
          "dom": 'Bfrtip',
          "buttons": [
            {
              "extend": 'csv',
              "text": '<i class="ti ti-file-text me-1"></i>CSV',
              "className": 'd-none',
              "title": 'Sectors_Export_' + new Date().toISOString().slice(0,10),
              "exportOptions": {
                "columns": [0, 1]
              }
            },
            {
              "extend": 'excel',
              "text": '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
              "className": 'd-none',
              "title": 'Sectors_Export_' + new Date().toISOString().slice(0,10),
              "exportOptions": {
                "columns": [0, 1]
              }
            }
          ]
        });
      }
      sectorsTable.clear();
      const idx = selIndustry.selectedIndex
      if(idx >= 0){
        DATA[idx].sectors.forEach((s, j) => {
          const actions = `<div class="d-flex align-items-center justify-content-end gap-2">
            <button class="btn btn-sm btn-label-info" data-act="sec-up" data-j="${j}">
              Up <i class="ti ti-sm ti-arrow-up ms-2"></i>
            </button>
            <button class="btn btn-sm btn-label-warning" data-act="sec-down" data-j="${j}">
              Down <i class="ti ti-sm ti-arrow-down ms-2"></i>
            </button>
            <button class="btn btn-sm btn-label-secondary" data-act="sec-rename" data-j="${j}">
              Rename <i class="ti ti-sm ti-pencil ms-2"></i>
            </button>
            <button class="btn btn-sm btn-label-danger" data-act="sec-del" data-j="${j}">
              Delete <i class="ti ti-sm ti-trash ms-2"></i>
            </button>
          </div>`;
          sectorsTable.row.add([null, s, actions]);
        });
      }
      sectorsTable.draw();
    }

    function renderTech(){
      if (!techTable) {
        techTable = $('#tblTech').DataTable({
          "responsive": true,
          "pageLength": 10,
          "ordering": false,
          "columnDefs": [
            {
              "targets": 0,
              "render": function (data, type, row, meta) {
                return meta.row + 1;
              }
            },
            { "orderable": false, "targets": 2 }
          ],
          "columns": [
            { "title": "#" },
            { "title": "Technology" },
            { "title": "Actions", "orderable": false }
          ],
          "dom": 'Bfrtip',
          "buttons": [
            {
              "extend": 'csv',
              "text": '<i class="ti ti-file-text me-1"></i>CSV',
              "className": 'd-none',
              "title": 'Technologies_Export_' + new Date().toISOString().slice(0,10),
              "exportOptions": {
                "columns": [0, 1]
              }
            },
            {
              "extend": 'excel',
              "text": '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
              "className": 'd-none',
              "title": 'Technologies_Export_' + new Date().toISOString().slice(0,10),
              "exportOptions": {
                "columns": [0, 1]
              }
            }
          ]
        });
      }
      techTable.clear();
      techMaster().forEach((t, i) => {
        const actions = `<div class="d-flex align-items-center justify-content-end gap-2">
          <button class="btn btn-sm btn-label-secondary" data-act="tech-rename" data-name="${t}">
            Rename <i class="ti ti-sm ti-pencil ms-2"></i>
          </button>
          <button class="btn btn-sm btn-label-danger" data-act="tech-del" data-name="${t}">
            Delete <i class="ti ti-sm ti-trash ms-2"></i>
          </button>
        </div>`;
        techTable.row.add([null, t, actions]);
      });
      techTable.draw();
    }

    function renderPreview(){
      const div = document.getElementById('mapPreview')
      div.innerHTML = ''
      const idx = selIndustry.selectedIndex
      if(idx < 0) return
      DATA[idx].sectors.forEach(s => {
        const span = document.createElement('span')
        span.className = 'badge bg-secondary me-2 mb-2'
        span.textContent = s
        div.appendChild(span)
      })
    }

    function dump(){
      out.textContent = JSON.stringify(DATA, null, 2)
      status.textContent = 'Unsaved changes'
    }

    // Actions
    document.getElementById('addIndustry').addEventListener('click', () => {
      currentActionType = 'add';
      currentAddType = 'industry';
      $('#renameModalLabel').text('Add Industry');
      $('#confirmAction').text('Add');
      $('#renameInput').val('');
      $('#renameModal').modal('show');
    })

    document.getElementById('addSector').addEventListener('click', () => {
      currentActionType = 'add';
      currentAddType = 'sector';
      $('#renameModalLabel').text('Add Sector');
      $('#confirmAction').text('Add');
      $('#renameInput').val('');
      $('#renameModal').modal('show');
    })

    document.getElementById('addOtherSector').addEventListener('click', () => {
      const idx = selIndustry.selectedIndex
      if(!DATA[idx].sectors.includes('Other')) DATA[idx].sectors.push('Other')
      renderSectors(); renderPreview(); dump()
    })

    document.getElementById('addTech').addEventListener('click', () => {
      currentActionType = 'add';
      currentAddType = 'tech';
      $('#renameModalLabel').text('Add Technology');
      $('#confirmAction').text('Add');
      $('#renameInput').val('');
      $('#renameModal').modal('show');
    })

    document.getElementById('addOtherTech').addEventListener('click', () => {
      DATA.forEach(d => { if(!d.technologies.includes('Other')) d.technologies.push('Other') })
      renderTech(); dump()
    })

    $(document).on('click', '[data-act="up"]', function() {
      const i = +$(this).data('i');
      if (i <= 0) return;
      simulateAjax('up', { index: i }, (success) => {
        if (success) {
          const t = DATA[i]; DATA[i] = DATA[i-1]; DATA[i-1] = t;
          renderIndustries(); renderTech(); dump();
          releaseSuccessAlert({ message: 'Moved up successfully' });
        } else {
          releaseFailureAlert({ message: 'Failed to move up' });
        }
      });
    })

    $(document).on('click', '[data-act="down"]', function() {
      const i = +$(this).data('i');
      if (i >= DATA.length - 1) return;
      simulateAjax('down', { index: i }, (success) => {
        if (success) {
          const t = DATA[i]; DATA[i] = DATA[i+1]; DATA[i+1] = t;
          renderIndustries(); renderTech(); dump();
          releaseSuccessAlert({ message: 'Moved down successfully' });
        } else {
          releaseFailureAlert({ message: 'Failed to move down' });
        }
      });
    })

    $(document).on('click', '[data-act="rename"]', function() {
      currentActionType = 'rename';
      currentRenameType = 'industry';
      currentRenameIndex = +$(this).data('i');
      currentRenameName = DATA[currentRenameIndex].industry;
      $('#renameInput').val(currentRenameName);
      $('#renameModalLabel').text('Rename Industry');
      $('#confirmAction').text('Rename');
      $('#renameModal').modal('show');
    })

    $(document).on('click', '[data-act="del"]', function() {
      const i = +$(this).data('i');
      Swal.fire({
        title: 'Delete Industry',
        text: 'Are you sure you want to delete this industry?',
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
          simulateAjax('delete', { type: 'industry', index: i }, (success) => {
            if (success) {
              DATA.splice(i, 1);
              renderIndustries(); renderTech(); dump();
              releaseSuccessAlert({ message: 'Industry deleted successfully' });
            } else {
              releaseFailureAlert({ message: 'Failed to delete industry' });
            }
          });
        }
      });
    })

    $(document).on('click', '[data-act="sec-up"]', function() {
      const j = +$(this).data('j')
      const idx = selIndustry.selectedIndex
      const arr = DATA[idx].sectors
      if(j>0){ const t = arr[j]; arr[j]=arr[j-1]; arr[j-1]=t }
      renderSectors(); renderPreview(); dump()
    })

    $(document).on('click', '[data-act="sec-down"]', function() {
      const j = +$(this).data('j')
      const idx = selIndustry.selectedIndex
      const arr = DATA[idx].sectors
      if(j<arr.length-1){ const t = arr[j]; arr[j]=arr[j+1]; arr[j+1]=t }
      renderSectors(); renderPreview(); dump()
    })

    $(document).on('click', '[data-act="sec-rename"]', function() {
      currentActionType = 'rename';
      currentRenameType = 'sector';
      currentRenameIndex = +$(this).data('j');
      const idx = selIndustry.selectedIndex;
      currentRenameName = DATA[idx].sectors[currentRenameIndex];
      $('#renameInput').val(currentRenameName);
      $('#renameModalLabel').text('Rename Sector');
      $('#confirmAction').text('Rename');
      $('#renameModal').modal('show');
    })

    $(document).on('click', '[data-act="sec-up"]', function() {
      const j = +$(this).data('j');
      const idx = selIndustry.selectedIndex;
      if (j <= 0) return;
      simulateAjax('sec-up', { index: j, industryIndex: idx }, (success) => {
        if (success) {
          const arr = DATA[idx].sectors;
          const t = arr[j]; arr[j] = arr[j-1]; arr[j-1] = t;
          renderSectors(); renderPreview(); dump();
          releaseSuccessAlert({ message: 'Sector moved up successfully' });
        } else {
          releaseFailureAlert({ message: 'Failed to move sector up' });
        }
      });
    })

    $(document).on('click', '[data-act="sec-down"]', function() {
      const j = +$(this).data('j');
      const idx = selIndustry.selectedIndex;
      const arr = DATA[idx].sectors;
      if (j >= arr.length - 1) return;
      simulateAjax('sec-down', { index: j, industryIndex: idx }, (success) => {
        if (success) {
          const t = arr[j]; arr[j] = arr[j+1]; arr[j+1] = t;
          renderSectors(); renderPreview(); dump();
          releaseSuccessAlert({ message: 'Sector moved down successfully' });
        } else {
          releaseFailureAlert({ message: 'Failed to move sector down' });
        }
      });
    })

    $(document).on('click', '[data-act="sec-del"]', function() {
      const j = +$(this).data('j');
      const idx = selIndustry.selectedIndex;
      Swal.fire({
        title: 'Delete Sector',
        text: 'Are you sure you want to delete this sector?',
        icon: 'warning',
        showCancelButton: false,
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
          simulateAjax('sec-delete', { index: j, industryIndex: idx }, (success) => {
            if (success) {
              DATA[idx].sectors.splice(j, 1);
              renderSectors(); renderPreview(); dump();
              releaseSuccessAlert({ message: 'Sector deleted successfully' });
            } else {
              releaseFailureAlert({ message: 'Failed to delete sector' });
            }
          });
        }
      });
    });

    $(document).on('click', '[data-act="tech-rename"]', function() {
      currentActionType = 'rename';
      currentRenameType = 'tech';
      currentRenameIndex = -1;
      currentRenameName = $(this).data('name');
      $('#renameInput').val(currentRenameName);
      $('#renameModalLabel').text('Rename Technology');
      $('#confirmAction').text('Rename');
      $('#renameModal').modal('show');
    })

    $('#confirmAction').on('click', function() {
      const newName = $('#renameInput').val().trim();
      if (!newName) return;
      
      $('#renameModal').modal('hide');
      
      if (currentActionType === 'add') {
        if (currentAddType === 'industry') {
          DATA.push({industry: newName, sectors: ['Other'], technologies: techMaster()});
          renderIndustries(); renderTech(); dump();
          releaseSuccessAlert({ message: 'Industry added successfully' });
        } else if (currentAddType === 'sector') {
          const idx = selIndustry.selectedIndex;
          DATA[idx].sectors.push(newName);
          renderSectors(); renderPreview(); dump();
          releaseSuccessAlert({ message: 'Sector added successfully' });
        } else if (currentAddType === 'tech') {
          DATA.forEach(d => { if(!d.technologies.includes(newName)) d.technologies.push(newName) });
          renderTech(); dump();
          releaseSuccessAlert({ message: 'Technology added successfully' });
        }
      } else if (currentActionType === 'rename') {
        if (newName === currentRenameName) return;
        simulateAjax('rename', { type: currentRenameType, index: currentRenameIndex, oldName: currentRenameName, newName }, (success) => {
          if (success) {
            if (currentRenameType === 'industry') {
              DATA[currentRenameIndex].industry = newName;
              renderIndustries(); renderTech();
            } else if (currentRenameType === 'sector') {
              const idx = selIndustry.selectedIndex;
              DATA[idx].sectors[currentRenameIndex] = newName;
              renderSectors(); renderPreview();
            } else if (currentRenameType === 'tech') {
              DATA.forEach(d => {
                d.technologies = d.technologies.map(t => t === currentRenameName ? newName : t);
              });
              renderTech();
            }
            dump();
            releaseSuccessAlert({ message: 'Renamed successfully' });
          } else {
            releaseFailureAlert({ message: 'Failed to rename' });
          }
        });
      }
    })

    $(document).on('click', '[data-act="tech-del"]', function() {
      const name = $(this).data('name');
      Swal.fire({
        title: 'Delete Technology',
        text: 'Are you sure you want to delete this technology globally?',
        icon: 'warning',
        showCancelButton: true,
        buttonStyling: false,
        showConfirmButton: true,
        confirmButtonText: "{{ __('admin.delete') }}",
        cancelButtonText: "{{ __('admin.cancel') }}",
        customClass: {
          confirmButton: 'btn btn-danger me-3',
          // cancelButton: 'btn btn-label-secondary'
        },
      }).then((result) => {
        if (result.isConfirmed) {
          simulateAjax('tech-delete', { name }, (success) => {
            if (success) {
              DATA.forEach(d => { d.technologies = d.technologies.filter(x => x !== name) });
              renderTech(); dump();
              releaseSuccessAlert({ message: 'Technology deleted successfully' });
            } else {
              releaseFailureAlert({ message: 'Failed to delete technology' });
            }
          });
        }
      });
    })

    selIndustry.addEventListener('change', () => { renderSectors(); renderPreview() })

    // Export and Import
    document.getElementById('exportJson').addEventListener('click', () => {
      const blob = new Blob([JSON.stringify(DATA, null, 2)], {type: 'application/json'})
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = 'industry-sector-technology.json'
      a.click()
      URL.revokeObjectURL(url)
    })

    document.getElementById('importJson').addEventListener('click', () => {
      const inp = document.createElement('input')
      inp.type = 'file'
      inp.accept = 'application/json'
      inp.onchange = () => {
        const f = inp.files[0]
        if(!f) return
        const reader = new FileReader()
        reader.onload = () => {
          try { DATA = JSON.parse(reader.result) } catch(e){ releaseFailureAlert({ message: 'Invalid JSON file' }); }
          renderIndustries(); renderTech(); dump()
        }
        reader.readAsText(f)
      }
      inp.click()
    })

    document.getElementById('saveAll').addEventListener('click', () => {
      out.textContent = JSON.stringify(DATA, null, 2)
      status.textContent = 'Saved'
      status.className = 'btn btn-sm btn-label-success'
      setTimeout(()=>{ 
        status.textContent = 'Unsaved changes'
        status.className = 'btn btn-sm btn-label-warning'
      }, 1200)
    })

    window.exportIndustries = function(format) {
      if (!industriesTable) {
        releaseFailureAlert({ message: 'No data available to export' });
        return;
      }
      try {
        if (format === 'csv') {
          industriesTable.button('.buttons-csv').trigger();
        } else if (format === 'excel') {
          industriesTable.button('.buttons-excel').trigger();
        }
      } catch (error) {
        console.error('Export error:', error);
        releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
      }
    }

    window.exportSectors = function(format) {
      if (!sectorsTable) {
        releaseFailureAlert({ message: 'No data available to export' });
        return;
      }
      try {
        if (format === 'csv') {
          sectorsTable.button('.buttons-csv').trigger();
        } else if (format === 'excel') {
          sectorsTable.button('.buttons-excel').trigger();
        }
      } catch (error) {
        console.error('Export error:', error);
        releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
      }
    }

    window.exportTechnologies = function(format) {
      if (!techTable) {
        releaseFailureAlert({ message: 'No data available to export' });
        return;
      }
      try {
        if (format === 'csv') {
          techTable.button('.buttons-csv').trigger();
        } else if (format === 'excel') {
          techTable.button('.buttons-excel').trigger();
        }
      } catch (error) {
        console.error('Export error:', error);
        releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
      }
    }

    // init
    renderIndustries(); renderTech(); dump()
</script>
@stop