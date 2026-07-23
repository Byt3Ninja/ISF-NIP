@extends('layouts.admin')

@section('content')
@php
  $pills = [
    ['id' => 'overview', 'label' => __('forms.overview_tab'), 'icon' => 'file-text'],
    ['id' => 'forms', 'label' => __('forms.forms_tab'), 'icon' => 'forms'],
    ['id' => 'submissions', 'label' => __('forms.submissions_tab'), 'icon' => 'send'],
  ];

  $activeTab = request('tab', 'overview');

  $kpis = [
    ['label' => __('forms.total_forms'), 'value' => $analytics['total_forms']],
    ['label' => __('forms.total_submissions'), 'value' => $analytics['total_submissions']],
    ['label' => __('forms.total_organizations'), 'value' => $analytics['total_organizations']],
    ['label' => __('forms.total_submitters'), 'value' => $analytics['total_submitters']],
  ];

  $colors = ['primary', 'success', 'warning', 'info'];
  $icons = ['file', 'send', 'calendar', 'check'];

  $user = auth()->user();
  $isOrgManager = $user->hasAnyRole(['organizationManager']);

  if($user->hasRole('Admin')) {
    $organizations = App\Organization::all();
  } elseif($isOrgManager || $user->isSubAdmin()) {
    $orgIds = $user->organizations_ids;
    $organizations = App\Organization::whereIn('id', $orgIds)->get();
  } else {
    $organizations = collect();
  }
@endphp

<div class="">
  <div class="">

    <!-- Navigation Pills -->
    <div class="card shadow-sm p-4 mb-4">
      <ul class="nav nav-pills gap-2" id="formsSubmissionsTab" role="tablist">
        @foreach($pills as $key => $pill)
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $pill['id'] == $activeTab ? 'active' : '' }}" id="{{ $pill['id'] }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $pill['id'] }}-pane" type="button" role="tab">
              <i class="ti ti-{{ $pill['icon'] }} me-2"></i>
              {{ $pill['label'] }}
            </button>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="tab-content p-0 shadow-none" id="formsSubmissionsTabContent">
      @include('admin.settings.forms.partials.overview-tab', ['activeTab' => $activeTab])
      @include('admin.settings.forms.partials.forms-tab', ['activeTab' => $activeTab])
      @include('admin.settings.forms.partials.submissions-tab', ['activeTab' => $activeTab])
    </div>
  </div>

  @include('admin.settings.forms.partials.submissions-export-modal')
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('pagescripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // Helper to create export dropdown HTML
  function createExportDropdown(table, id) {
    const dropdownHtml = `
      <div class="dropdown ms-2 d-inline-block">
        <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown${id}" data-bs-toggle="dropdown" aria-expanded="false">
          @lang('admin.Export')
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
    order: [[5, 'desc']],
    lengthMenu: [ [7, 10, 25, 50, -1], [7, 10, 25, 50, "@lang('admin.All')"] ],
    columnDefs: [{ orderable: false, targets: -1 }],
   language: {
      url:
        document.dir === 'rtl'
          ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
          : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...',
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
              <option value="@lang('forms.all_organizations')">@lang('forms.all_organizations')</option>
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

      setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
      }, 300);
    }
  });

  // Submissions Table
  const submissionsTable = $('#submissionsTable').DataTable({
    paging: true,
    searching: true,
    ordering: true,
    processing: true,
    responsive: true,
    order: [[5, 'desc']],
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
            <option value="">@lang('forms.all_organizations')</option>
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
            <option value="">@lang('forms.all_programs')</option>
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
            <option value="">@lang('forms.all_steps')</option>
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
      url:
        document.dir === 'rtl'
          ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
          : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...',
    },
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
            name: '{{ __('forms.forms') }}',
            data: orgData ? Object.values(orgData).map(i => i.forms_with_submissions) : [],
          },
          {
            name: '{{ __('forms.submissions') }}',
            data: orgData ? Object.values(orgData).map(i => i.total_submissions) : [],
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
          categories: orgData ? Object.keys(orgData) : [],
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

  // Handle tab clicks to update URL
  $('#formsSubmissionsTab .nav-link').on('click', function() {
    const tabId = $(this).attr('id').replace('-tab', '');
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.pushState({}, '', url);
  });
});
</script>
@stop
