@extends('layouts.admin')

@section('content')
@php
  $t = [
    'overviewTab' => $l == 'ar' ? 'نظرة عامة' : 'Overview',
    'formsTab' => $l == 'ar' ? 'النماذج' : 'Forms',
    'submissionsTab' => $l == 'ar' ? 'الإرسالات' : 'Submissions',
    'addNewForm' => $l == 'ar' ? 'اضافة نموذج جديد' : 'Add New Form',
    'formId' => $l == 'ar' ? 'معرف النموذج' : 'Form ID',
    'formName' => $l == 'ar' ? 'عنوان النموذج' : 'Form Title',
    'assignedOrganization' => $l == 'ar' ? 'المنظمة المعينة' : 'Assigned Organization',
    'submissions' => $l == 'ar' ? 'الإرسالات' : 'Submissions',
    'lastModified' => $l == 'ar' ? 'آخر تعديل' : 'Last Modified',
    'createdDate' => $l == 'ar' ? 'تاريخ الإنشاء' : 'Created Date',
    'actions' => $l == 'ar' ? 'الإجراءات' : 'Actions',
    'formsList' => $l == 'ar' ? 'قائمة النماذج' : 'Forms List',
    'submissionsList' => $l == 'ar' ? 'قائمة الإرسالات' : 'Submissions List',
    'allOrganizations' => $l == 'ar' ? 'جميع المنظمات' : 'All Organizations',
    'search' => $l == 'ar' ? 'بحث' : 'Search',
    'totalForms' => $l == 'ar' ? 'إجمالي النماذج' : 'Total Forms',
    'totalSubmissions' => $l == 'ar' ? 'إجمالي الإرسالات' : 'Total Submissions',
    'totalOrganizations' => $l == 'ar' ? 'إجمالي المنظمات' : 'Total Organizations',
    'totalSubmitters' => $l == 'ar' ? 'إجمالي المرسلين' : 'Total Submitters',
    'submissionsOverTime' => $l == 'ar' ? 'الإرسالات مع مرور الزمن' : 'Submissions Over Time',
    'topOrganizations' => $l == 'ar' ? 'أفضل المنظمات' : 'Top Organizations',
    'formsByStatus' => $l == 'ar' ? 'النماذج حسب الحالة' : 'Forms By Status',
  ];

  $pills = [
    ['id' => 'overview', 'label' => $t['overviewTab'], 'icon' => 'file-text'],
    ['id' => 'forms', 'label' => $t['formsTab'], 'icon' => 'forms'],
    ['id' => 'submissions', 'label' => $t['submissionsTab'], 'icon' => 'send'],
  ];

  $kpis = [
    ['label' => $t['totalForms'], 'value' => $analytics['total_forms']],
    ['label' => $t['totalSubmissions'], 'value' => $analytics['total_submissions']],
    ['label' => $t['totalOrganizations'], 'value' => $analytics['total_organizations']],
    ['label' => $t['totalSubmitters'], 'value' => $analytics['total_submitters']],
  ];

  $colors = ['primary', 'success', 'warning', 'info'];
  $icons = ['file', 'send', 'calendar', 'check'];

  $isOrgManager = auth()->user()->hasAnyRole(['organizationManager']);

  if($isOrgManager) {
    $organizations = App\Organization::where('id', auth()->user()->organization_id)->get();
  } else {
    $organizations = App\Organization::all();
  }
@endphp

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <!-- Navigation Pills -->
    <div class="card shadow-sm p-4 mb-4">
      <ul class="nav nav-pills gap-2" id="formsSubmissionsTab" role="tablist">
        @foreach($pills as $key => $pill)
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $pill['id'] == 'overview' ? 'active' : '' }}" id="{{ $pill['id'] }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $pill['id'] }}-pane" type="button" role="tab">
              <i class="ti ti-{{ $pill['icon'] }} me-2"></i>
              {{ $pill['label'] }}
            </button>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="tab-content p-0 shadow-none" id="formsSubmissionsTabContent">

      <!-- Overview Pane -->
      <div class="tab-pane fade show active" id="overview-pane" role="tabpanel">
        
        <!-- KPI Cards -->
        <div class="row mb-4">
          @foreach($kpis as $key => $item)
            <div class="col-xl-3">
              <div class="card card-border-shadow-{{ $colors[$key] }}">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <h6 class="mb-0">{{ $item['label'] }}</h6>
                      <h4 class="mb-0">{{ $item['value'] ?? 0 }}</h4>
                    </div>
                    <div class="avatar">
                      <span class="avatar-initial rounded bg-label-{{ $colors[$key] }}">
                        <i class="ti ti-{{ $icons[$key] }} ti-lg"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      
        <!-- Bar Chart -->
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card">
              <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                  <h5 class="m-0 me-2">{{ $t['topOrganizations'] }}</h5>
                  <small class="text-muted">{{ $l == 'ar' ? 'النماذج وإرسالات حسب المنظمات' : 'Forms & Submissions by organizations' }}</small>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-12">
                    <div id="organizationsChart"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Forms Table -->
      <div class="tab-pane fade" id="forms-pane" role="tabpanel">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="card-title mb-0">{{ $t['formsList'] }}</h5>
            <a href="/dashboard/show-builder" class="btn btn-primary">
              <i class="ti ti-plus me-1"></i> {{ $t['addNewForm'] }}
            </a>
          </div>
          <div class="card-datatable table-responsive">
            <table class="table datatables-basic" id="formsTable">
              <thead class="table-light">
                <tr>
                  <th style="width: 60px;">{{ $t['formId'] }}</th>
                  <th>{{ $t['formName'] }}</th>
                  <th>{{ $t['assignedOrganization'] }}</th>
                  <th style="width:110px;">{{ $t['submissions'] }}</th>
                  <th style="width:140px;">{{ $t['lastModified'] }}</th>
                  <th style="width:140px;">{{ $t['createdDate'] }}</th>
                  <th style="width:110px; text-align:center;">{{ $t['actions'] }}</th>
                </tr>
              </thead>
              <tbody>
                @if(!empty($data) && count($data))
                  @foreach($data as $form)
                    @php
                      $form_id = $form->form_id ?? ($form->id ?? '');
                      $title = $form->form_name ?? $form->name ?? '—';
                      $assignedOrg = $form->assigned_organization ?? ($form->organization_name ?? '—');
                      $submissions = $form->submissions ?? 0;
                      $updated = (isset($form->last_modified) && $form->last_modified != 'null') ? \Carbon\Carbon::parse($form->last_modified)->format('Y-m-d') : '—';
                      $created = (isset($form->created_date) && $form->created_date != 'null') ? \Carbon\Carbon::parse($form->created_date)->format('Y-m-d') : '—';
                      $org_id = $form->organization_id ?? '';
                    @endphp
                    <tr data-org="{{ $assignedOrg }}">
                      <td>{{ $form_id }}</td>
                      <td>{{ $title }}</td>
                      <td>{{ $assignedOrg }}</td>
                      <td>{{ $submissions }}</td>
                      <td>{{ $updated }}</td>
                      <td>{{ $created }}</td>
                      <td class="table-actions text-center">
                        <a href="/dashboard/show-form/{{ $form_id }}" class="btn btn-sm btn-label-primary" title="{{ $t['search'] }}">
                          <i class="ti ti-eye ti-sm"></i>
                        </a>
                        <a href="/dashboard/show-builder/{{ $form_id }}{{ $org_id ? '?org_id=' . $org_id : '' }}"
                           class="btn btn-sm btn-label-info" title="Edit">
                          <i class="ti ti-edit ti-sm"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Submissions Table -->
      <div class="tab-pane fade" id="submissions-pane" role="tabpanel">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="card-title mb-0">{{ $t['submissionsList'] }}</h5>
          </div>
          <div class="card-datatable table-responsive pt-0">
            <table class="datatables-basic table" id="submissionsTable">
              <thead class="table-light">
                <tr>
                  <th>Submission Id</th>
                  <th>Submitted By</th>
                  <th>Startup Name</th>
                  <th>Organization Name</th>
                  <th>Program Name</th>
                  <th>Created At</th>
                  <th>Step Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @if(!empty($submissionsData) && count($submissionsData))
                  @foreach($submissionsData as $submission)
                    <tr>
                      <td>{{ $submission->submission_id ?? '—' }}</td>
                      <td>{{ $submission->submitted_by ?? '—' }}</td>
                      <td>{{ $submission->startup_name ?? '—' }}</td>
                      <td>{{ $submission->organization_name ?? '—' }}</td>
                      <td>{{ $submission->program_name ?? '—' }}</td>
                      <td>{{ $submission->created_at ?? '—' }}</td>
                      <td>{{ $submission->step_name ?? '—' }}</td>
                      <td>
                        <a href="/dashboard/submission-details/{{ $submission->step_id ?? '' }}" class="btn btn-sm btn-primary">
                          {{ $l == 'en' ? 'View Details' : 'عرض التفاصيل' }}
                        </a>
                      </td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="8" class="text-center text-muted">
                      {{ $l == 'ar' ? 'لا توجد بيانات متاحة' : 'No data available' }}
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Advanced Export Modal -->
  <div class="modal fade" id="advancedExportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center">
            <i class="ti ti-package me-2"></i> {{ __('admin.advanced_export') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0">{{ __('admin.export_status') }}</h6>
                <button class="btn btn-sm btn-outline-primary refresh-export-btn">
                    <i class="ti ti-refresh me-1"></i> {{ __('admin.refresh') }}
                </button>
            </div>
            
            <div class="export-loading d-none text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">{{ __('admin.processing') }}</p>
            </div>

            <div class="export-content">
                <div class="row mb-4 text-center">
                    <div class="col-6 border-end">
                        <h3 class="text-primary mb-0 ready-count">-</h3>
                        <small class="text-muted">{{ __('admin.ready_to_download') }}</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-dark mb-0 total-count">-</h3>
                        <small class="text-muted">{{ __('admin.total_entries') }}</small>
                    </div>
                </div>

                <div class="progress-container d-none mb-4">
                    <label class="form-label">{{ __('admin.export_progress') }}</label>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                </div>

                <div class="file-info alert alert-secondary d-none mb-4">
                    <div class="d-flex align-items-center mb-1">
                        <i class="ti ti-file-zip fs-4 me-2"></i>
                        <span class="fw-bold file-name">filename.zip</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small ps-4">
                        <span class="file-size">-</span>
                        <span class="file-date">-</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary generate-export-btn">
                        <i class="ti ti-settings me-1"></i> {{ __('admin.generate_export') }}
                    </button>
                    
                    <button class="btn btn-success download-export-btn d-none">
                        <i class="ti ti-download me-1"></i> {{ __('admin.download_generated_file') }}
                    </button>
                    
                    <button class="btn btn-outline-secondary regenerate-export-btn d-none">
                        <i class="ti ti-reload me-1"></i> {{ __('admin.regenerate') }}
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('pagescripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  // Helper to create export dropdown HTML
  function createExportDropdown(table, id) {
    const dropdownHtml = `
      <div class="dropdown ms-2 d-inline-block">
        <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown${id}" data-bs-toggle="dropdown" aria-expanded="false">
          Export
        </button>
        <ul class="dropdown-menu" aria-labelledby="exportDropdown${id}">
          <li><a class="dropdown-item" href="#" data-type="csv"><i class="ti ti-file-type-csv me-1"></i>CSV</a></li>
          <li><a class="dropdown-item" href="#" data-type="excel"><i class="ti ti-file-type-xls me-1"></i>Excel</a></li>
          ${
            id === 'submissions' ? 
            `
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#advancedExportModal"><i class="ti ti-package me-1"></i> @lang('admin.advanced_export')</a></li>
            ` 
            : ``
          }
        </ul>
      </div>
    `;
    const container = $(table.table().container()).find('.dataTables_filter');
    container.append(dropdownHtml);

    // Trigger hidden DataTables export buttons
    container.find('.dropdown-item').on('click', function(e) {
      e.preventDefault();
      const type = $(this).data('type');
      if (type === 'csv') table.button('.buttons-csv').trigger();
      if (type === 'excel') table.button('.buttons-excel').trigger();
    });
  }

  // Forms Table
  const formsTable = $('#formsTable').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    processing: true,
    pageLength: 10,
    lengthMenu: [ [7, 10, 25, 50, -1], [7, 10, 25, 50, "@lang('admin.All')"] ],
    columnDefs: [{ orderable: false, targets: -1 }],
    language: {
      url: '{{ $l }}' === 'ar'
        ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
        : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json"
    },
    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'fB>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      { extend: 'csvHtml5', className: 'd-none' },
      { extend: 'excelHtml5', className: 'd-none' }
    ],
    initComplete: function () {
      createExportDropdown(formsTable, 'forms');

      @if(!$isOrgManager)
        const orgFilterHtml = `
          <div class="dt-org-filter ms-3 d-inline-block">
            <select id="orgFilterForms" class="form-select form-select-sm" style="width:220px;">
              <option value="">{{ $t['allOrganizations'] }}</option>
              @foreach($organizations as $org)
                <option value="{{ $org->name }}">{{ $org->name }}</option>
              @endforeach
            </select>
          </div>
        `;
        $(this.api().table().container()).find('.dataTables_length').append(orgFilterHtml);
        $('#orgFilterForms').on('change', function () {
          const val = this.value;
          formsTable.column(2).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
        });
      @endif
    }
  });

  // Submissions Table
  const submissionsTable = $('#submissionsTable').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    processing: true,
    responsive: true,
    dom: "<'row'<'col-sm-12 col-md-8'l><'col-sm-12 col-md-4'fB>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      { extend: 'csvHtml5', className: 'd-none' },
      { extend: 'excelHtml5', className: 'd-none' }
    ],
    initComplete: function () {
      createExportDropdown(submissionsTable, 'submissions');

      const api = this.api();
      const lengthContainer = $(api.table().container()).find('.dataTables_length');

      // Organization filter
      const orgs = [...new Set(api.column(3).data().toArray().filter(v => v))];
      if (orgs.length > 0) {
        const orgFilter = $(`
          <select id="orgFilterSubs" class="form-select form-select-sm ms-3 me-2 mb-2" style="width:130px;">
            <option value="">{{ $l == 'en' ? 'All Organizations' : 'جميع المنظمات' }}</option>
          </select>
        `).appendTo(lengthContainer);
        orgs.forEach(org => orgFilter.append('<option value="'+org+'">'+org+'</option>'));
        orgFilter.on('change', function () {
          const val = this.value;
          api.column(3).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
        });
      }

      // Program filter
      const programs = [...new Set(api.column(4).data().toArray().filter(v => v))];
      if (programs.length > 0) {
        const programFilter = $(`
          <select id="programFilter" class="form-select form-select-sm ms-2 me-2 mb-2" style="width:130px;">
            <option value="">{{ $l == 'en' ? 'All Programs' : 'جميع البرامج' }}</option>
          </select>
        `).appendTo(lengthContainer);
        programs.forEach(prog => programFilter.append('<option value="'+prog+'">'+prog+'</option>'));
        programFilter.on('change', function () {
          const val = this.value;
          api.column(4).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
        });
      }

      // Step filter
      const steps = [...new Set(api.column(6).data().toArray().filter(v => v))];
      if (steps.length > 0) {
        const stepFilter = $(`
          <select id="stepFilter" class="form-select form-select-sm ms-2 mb-2" style="width:130px;">
            <option value="">{{ $l == 'en' ? 'All Steps' : 'جميع الخطوات' }}</option>
          </select>
        `).appendTo(lengthContainer);
        steps.forEach(step => stepFilter.append('<option value="'+step+'">'+step+'</option>'));
        stepFilter.on('change', function () {
          const val = this.value;
          api.column(6).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
        });
      }
    },
    language: {
      url: '{{ $l }}' === 'ar'
        ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
        : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json"
    }
  });


  // Advanced Export Logic
  const exportModal = document.getElementById('advancedExportModal');

  if (exportModal) {
    const refreshBtn = exportModal.querySelector('.refresh-export-btn');
    const generateBtn = exportModal.querySelector('.generate-export-btn');
    const regenerateBtn = exportModal.querySelector('.regenerate-export-btn');
    const downloadBtn = exportModal.querySelector('.download-export-btn');
    const statusContainer = exportModal.querySelector('.export-status-container');
    const loadingContainer = exportModal.querySelector('.export-loading');
    const contentContainer = exportModal.querySelector('.export-content');
    const progressBarContainer = exportModal.querySelector('.progress-container');
    const progressBar = exportModal.querySelector('.progress-bar');
    
    // Counters
    const readyCounter = exportModal.querySelector('.ready-count');
    const totalCounter = exportModal.querySelector('.total-count');
    
    // File Info
    const fileInfo = exportModal.querySelector('.file-info');
    const fileName = exportModal.querySelector('.file-name');
    const fileSize = exportModal.querySelector('.file-size');
    const fileDate = exportModal.querySelector('.file-date');

    let progressInterval;

    function updateExportStatus() {
        loadingContainer.classList.remove('d-none');
        contentContainer.classList.add('d-none');
        progressBarContainer.classList.add('d-none'); // Ensure progress bar is hidden when status is updated
        
        $.get("{{ route('forms_submissions.export.status') }}", function(data) {
            loadingContainer.classList.add('d-none');
            contentContainer.classList.remove('d-none');
            
            readyCounter.textContent = data.ready_to_download;
            totalCounter.textContent = data.total_entries;
            
            if (data.has_export) {
                generateBtn.classList.add('d-none');
                regenerateBtn.classList.remove('d-none');
                downloadBtn.classList.remove('d-none');
                fileInfo.classList.remove('d-none');
                
                fileName.textContent = data.file_name;
                fileSize.textContent = data.file_size;
                fileDate.textContent = data.last_generated;
            } else {
                generateBtn.classList.remove('d-none');
                regenerateBtn.classList.add('d-none');
                downloadBtn.classList.add('d-none');
                fileInfo.classList.add('d-none');
            }
        }).fail(function() {
            loadingContainer.classList.add('d-none');
            alert("{{ __('admin.status_load_failed') }}");
        });
    }

    function startProgressPolling() {
        progressBarContainer.classList.remove('d-none');
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        
        progressInterval = setInterval(function() {
            $.get("{{ route('forms_submissions.export.progress') }}", function(data) {
                let percentage = data.progress;
                progressBar.style.width = percentage + '%';
                progressBar.textContent = percentage + '%';
                
                if (percentage >= 100) {
                    clearInterval(progressInterval);
                    setTimeout(function() {
                        progressBarContainer.classList.add('d-none');
                        updateExportStatus();
                    }, 1000);
                }
            });
        }, 1000);
    }

    function generateExport() {
        // Show loading state on buttons
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ' + "{{ __('admin.processing') }}";
        
        // Start polling immediately
        startProgressPolling();

        $.post("{{ route('forms_submissions.export.generate') }}", {
            _token: "{{ csrf_token() }}"
        }, function(response) {
            if (response.success) {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                
                setTimeout(function() {
                      progressBarContainer.classList.add('d-none');
                      window.location.href = "{{ route('forms_submissions.export.download') }}";
                      updateExportStatus();
                }, 500);
            }
        }).fail(function() {
            clearInterval(progressInterval);
            progressBarContainer.classList.add('d-none');
            alert("{{ __('admin.export_failed') }}");
        }).always(function() {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    exportModal.addEventListener('show.bs.modal', updateExportStatus);
    refreshBtn.addEventListener('click', updateExportStatus);
    generateBtn.addEventListener('click', generateExport);
    regenerateBtn.addEventListener('click', generateExport);
      downloadBtn.addEventListener('click', function() {
        window.location.href = "{{ route('forms_submissions.export.download') }}";
    });
  }

  // Analytics Data for Charts
  const analyticsData = @json($analytics);

  // Initialize Charts
  function initializeCharts() {
    const organizationsChartElement = document.querySelector("#organizationsChart");
    if (organizationsChartElement) {
      const orgData = analyticsData.top_organizations || {};

      const barOptions = {
        series: [
          {
            name: '{{ $l == "ar" ? "النماذج" : "Forms" }}',
            data: Object.values(orgData).map(i => i.forms_with_submissions),
          },
          {
            name: '{{ $l == "ar" ? "الإرسالات" : "Submissions" }}',
            data: Object.values(orgData).map(i => i.total_submissions),
          }
        ],
        chart: {
          type: 'bar',
          height: 350,
          fontFamily: 'Inter, sans-serif',
          foreColor: '#6f6b7d',
          toolbar: {
            show: false
          }
        },
        colors: ['#696cff', '#28c76f'],
        plotOptions: {
          bar: {
            borderRadius: 4,
            columnWidth: '60%',
            distributed: false
          }
        },
        dataLabels: {
          enabled: false
        },
        grid: {
          borderColor: '#dbe0e2',
          strokeDashArray: 3
        },
        xaxis: {
          categories: Object.keys(orgData),
          labels: {
            style: {
              colors: '#6f6b7d'
            }
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: '#6f6b7d'
            }
          },
          min: 0
        },
        tooltip: {
          theme: 'light'
        }
      };

      const organizationsChart = new ApexCharts(organizationsChartElement, barOptions);
      organizationsChart.render();
    }
  }

  // Initialize charts on page load
  initializeCharts();
});
</script>
@stop
