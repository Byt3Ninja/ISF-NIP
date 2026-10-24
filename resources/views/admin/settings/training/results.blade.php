@extends('layouts.admin')

@php
$tabs = [
  ['title' => __('admin.training_sessions'), 'url' => 'dashboard/training/sessions', 'icon' => 'book'],
  ['title' => __('admin.training_courses'), 'url' => 'dashboard/training/all', 'icon' => 'certificate'],
  ['title' => __('admin.training_results'), 'url' => 'dashboard/training/results', 'icon' => 'report-analytics'],
];
$currentLang = $l ?? app()->getLocale();

// Replicate the controller's company-fetching logic since $companies is not passed via compact().
// The controller queries by cycle (not by step_id), so we mirror that here.
$companies = collect([]);
if ($csession) {
    $companiesCycleIds = \App\Step::where('id', $csession->step_id)
        ->orderBy('arr')
        ->pluck('cycle_id')
        ->toArray();

    if (count($companiesCycleIds) > 0) {
        $companies = \App\Company::whereIn('cycle', $companiesCycleIds)->get();
    }
}
@endphp

@section('content')
{{-- Alerts --}}
@if (session()->has('message') || session()->has('message2'))
<x-alert type="success" :message="__('admin.' . (session()->get('message') ?? session()->get('message2')))" />
@endif

{{-- Navigation Pills --}}
<x-navigation-pills :tabs="$tabs" />

@if (count($sessions) > 0)
{{-- Session Header Card --}}
<div class="card mb-4">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ __('cycles.Portal training results') }}</h5>
    <a href="javascript:void(0)" class="btn btn-label-secondary ExportExcelTresult">
      <i class="ti ti-file-export me-1"></i> {{ __('admin.Export') }}
    </a>
  </div>
  <div class="card-body pt-4">
    <div class="row g-4">
      <div class="col-12 col-md-7">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
          <label class="form-label fs-6 text-nowrap mb-0">{{ __('cycles.Select training step') }}:</label>
          <select id="session_selector" class="form-select select2"
            onchange="window.location.href='/dashboard/training/results/'+this.value">
            @foreach ($sessions as $session_item)
            <option value="{{ $session_item->id }}" {{ $session_item->id == $id ? 'selected' : '' }}>
              {{ $session_item->title }} - {{ $session_item->step->title }}
              ({{ __('cycles.Cycle') }} "{{ $session_item->step->cycle->title }}")
            </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-12 col-md-5 d-flex justify-content-md-end gap-2">
        <a class="btn btn-outline-primary" href="/dashboard/cycles/{{ $csession->step->cycle->id }}">
          <i class="ti ti-adjustments me-1"></i> {{ __('cycles.Manage Cycle') }}
        </a>
        <a href="/dashboard/cycles/{{ $csession->step->cycle->id }}/registered" class="btn btn-primary">
          <i class="ti ti-settings-cog me-1"></i> {{ __('admin.manage_startups') }}
        </a>
      </div>
    </div>
  </div>
</div>

{{-- Main Results Card --}}
<div class="card">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h5 class="card-title mb-1">{{ $csession->step->title }}</h5>
        <div class="text-muted small">
          <i class="ti ti-calendar-event me-1"></i> {{ $csession->step->from->format('d-m-Y') }}
          <span class="mx-1">to</span>
          <i class="ti ti-calendar-check me-1"></i> {{ $csession->step->to->format('d-m-Y') }}
        </div>
      </div>
      {{-- Bulk Actions --}}
      <div id="bulk-actions-container" class="d-none mt-4">
        <button type="button" class="btn btn-primary" onclick="openStepModal('bulk')">
          <i class="ti ti-arrows-transfer-down me-1"></i> {{ __('cycles.change_step') }}
        </button>
      </div>
    </div>
  </div>

  {{-- Selection Tags --}}
  <div id="selection-wrapper" class="card shadow-none border-bottom bg-light-subtle" style="display: none;">
    <div class="card-body">
      <div id="selection-tags" class="d-flex flex-wrap gap-2 mb-2"></div>
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <table class="table border-top" id="resultsDataTable">
      <thead class="table-light">
        <tr>
          <th width="10"><input type="checkbox" class="form-check-input" id="selectAll"></th>
          <th>{{ __('cycles.Company') }}</th>
          <th>{{ __('admin.Founder') }}</th>
          <th>{{ __('admin.Progress') }}</th>
          <th>{{ __('cycles.Current Step') }}</th>
          <th class="text-center">{{ __('admin.Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @php
          // Build a lookup map from $steps once outside the loop for performance
          $stepsMap = $steps->keyBy('id');

          // Assign each step a distinct, stable badge color by its position in the list.
          // Using a fixed palette of Vuexy bg-label-* classes — danger is reserved for Disapproved.
          $stepPalette = ['bg-label-primary', 'bg-label-success', 'bg-label-warning', 'bg-label-info', 'bg-label-secondary', 'bg-label-dark'];
          $stepColorMap = [];
          foreach ($steps as $i => $s) {
              $stepColorMap[$s->id] = $stepPalette[$i % count($stepPalette)];
          }
        @endphp
        @foreach ($companies as $company)
        @php
          $completedCount = \App\TrainingCompleted::where('user_id', $company?->user?->id)
            ->where('course_id', $csession->training_Address)
            ->count();
          $percentage = $contents ? ($completedCount / $contents) * 100 : 0;

          $isDisapproved = (int)$company->step === 0;
          $stepTitle = $isDisapproved
            ? __('admin.Disapprove')
            : ($stepsMap->get($company->step)?->title ?? __('admin.Disapprove'));
          // Disapproved always red; each step gets its own stable color from the palette
          $badgeClass = $isDisapproved
            ? 'bg-label-danger'
            : ($stepColorMap[$company->step] ?? 'bg-label-secondary');
        @endphp
        <tr data-id="{{ $company->id }}" data-name="{{ $company->{'name_' . $currentLang} }}"
          data-progress="{{ (int)$percentage }}" data-step="{{ $company->step }}">
          <td><input type="checkbox" class="form-check-input row-checkbox" value="{{ $company->id }}"></td>
          <td>
            <a href="/dashboard/companyProfile/{{ $company->id }}" class="d-flex align-items-center">
              <div class="avatar avatar-sm me-3">
                <img 
                  src="{{ $company->logo ? '/191014/'.$company->logo : '/app-assets/images/logo_placeholder.png' }}"
                  onerror="this.src='/app-assets/images/logo_placeholder.png'"
                  class="rounded-circle border"
                  alt="logo"
                  width="40"
                  height="40"
                />
              </div>
              <span>{{ $company->{'name_' . $currentLang} }}</span>
            </a>
          </td>
          <td>
            <a href="/dashboard/show/UserProfile/{{ $company?->user?->id }}" class="d-flex align-items-center gap-2">
              <img src="{{ $company?->user?->img_url ? '/191014/'.$company?->user?->img_url : '/icons/account.svg' }}"
                class="rounded-circle shadow-sm"
                alt="{{ $company?->user?->{'first_name_' . $currentLang} }}"
                onerror="this.src='/icons/account.svg'"
                width="30" 
                height="30"
              />
              <div class="d-flex flex-column">
                <span class="fw-medium">
                  {{ $company?->user?->{'first_name_' . $currentLang} }}
                  {{ $company?->user?->{'last_name_' . $currentLang} }}
                </span>
                <small class="text-muted">{{ $company?->user?->{'title_' . $currentLang} }}</small>
              </div>
            </a>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="progress w-100" style="height: 6px;">
                <div
                  class="progress-bar bg-{{ $percentage >= 100 ? 'success' : ($percentage > 0 ? 'primary' : 'secondary') }}"
                  role="progressbar" style="width: {{ $percentage }}%"></div>
              </div>
              <span class="fw-bold small">{{ (int)$percentage }}%</span>
            </div>
          </td>
          <td><span class="badge {{ $badgeClass }}">{{ $stepTitle }}</span></td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-label-primary"
              onclick="openStepModal({{ $company->id }}, '{{ addslashes($stepTitle) }}')"
              data-bs-toggle="tooltip" title="{{ __('cycles.change_step') }}">
              <i class="ti ti-pencil me-1"></i> {{ __('cycles.change_step') }}
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@else
<div class="card card-body text-center py-10">
  <img src="/icons/not-found.svg" class="mb-4 mx-auto" width="200" alt="Empty">
  <h5 class="text-secondary">{{ __('admin.No_Session_Message') }}</h5>
  <div class="mt-3">
    <a href="/dashboard/cycles" class="btn btn-primary">
      {{ __('admin.Go to Steps') }} <i class="ti ti-chevron-right ms-1"></i>
    </a>
  </div>
</div>
@endif

{{-- Change Step Modal --}}
<div class="modal fade" id="stepModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="stepForm">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('cycles.change_training_step') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="target_company_ids" name="company_ids">
        <div class="alert alert-info py-2 mb-4 d-flex align-items-center">
          <i class="ti ti-info-circle me-2"></i>
          <div>
            <small class="d-block text-uppercase fw-bold">{{ __('cycles.Current Step') }}</small>
            <span id="current_step_display" class="fw-medium"></span>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold">{{ __('cycles.select_new_step') }}</label>
          <select id="modal_step_select" class="form-select select2-modal" required>
            <option value="0">{{ __('admin.Disapprove') }}</option>
            @foreach ($steps as $s)
            <option value="{{ $s->id }}">{{ $s->title }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
        <button type="submit" class="btn btn-primary" id="btnSaveStep">{{ __('admin.Save Changes') }}</button>
      </div>
    </form>
  </div>
</div>
@stop

@section('pagescripts')
<script>
$(function () {
  'use strict';

  // 1. DataTable Initialization
  const table = $('#resultsDataTable').DataTable({
    ...getSharedDataTableConfig({}),
    responsive: true,
    dom: '<"card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2 py-3 px-4"<"d-flex flex-wrap align-items-center gap-2"l<"dt_filter_company"><"dt_filter_step"><"dt_filter_status">><"dt-toolbar__search"f>><"table-responsive"t><"card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-4"<"dt-info"i><"dt-paging"p>>',
    initComplete: function () {
      $('.dt_filter_company').html(`
        <select id="dt_filter_company" class="form-select filter-select2">
          <option value="">{{ __('admin.all_startups') }}</option>
          @foreach ($companies as $c)
            <option value="{{ $c->{'name_' . $currentLang} }}">{{ $c->{'name_' . $currentLang} }}</option>
          @endforeach
        </select>
      `);

      // Step Filter — options built from $steps passed by the controller
      $('.dt_filter_step').html(`
        <select id="dt_filter_step" class="form-select filter-select2">
          <option value="">{{ __('admin.All_Steps') }}</option>
          <option value="0">{{ __('admin.Disapprove') }}</option>
          @foreach ($steps as $s)
            <option value="{{ $s->id }}">{{ $s->title }}</option>
          @endforeach
        </select>
      `);

      $('.dt_filter_status').html(`
        <select id="dt_filter_status" class="form-select filter-select2">
          <option value="">{{ __('admin.All_statuses') }}</option>
          <option value="100">Completed (100%)</option>
          <option value="0">Not Started (0%)</option>
          <option value="progress">In Progress</option>
        </select>
      `);

      $('.dataTables_filter .form-control').removeClass('form-control-sm').css('width', '');
      $('.dataTables_length .form-select').removeClass('form-select-sm').css('width', 'auto');

      // Initialize Select2
      setTimeout(function() {
        $('#resultsDataTable_wrapper .filter-select2, #resultsDataTable_wrapper .dataTables_length select').each(function () {
          const $this = $(this);
          if (!$this.hasClass('select2-hidden-accessible')) {
            const isLength = $this.closest('.dataTables_length').length > 0;
            const $parent = isLength ? $this.parent() : ($this.closest('.dt-filter').length ? $this.closest('.dt-filter') : $('body'));
            $this.css('visibility', 'hidden');
            $this.select2({
              dropdownParent: $parent,
              width: 'resolve',
              minimumResultsForSearch: Infinity,
              allowClear: false
            });
            $this.next('.select2-container').css({
              'min-width': isLength ? '70px' : '150px',
              'max-width': isLength ? '90px' : '200px'
            });
            setTimeout(function() { $this.css('visibility', 'visible'); }, 0);
          }
        });
      }, 200);
    }
  });

  // 2. Custom Filter Logic (Progress + Step)
  // Rules:
  // - Read filter values with ?? '' so null (filters not yet in DOM) never triggers hiding
  // - Use settings.aoData[dataIndex].nTr — safe for all rows incl. paginated/off-page
  // - Guard with !rowNode return true so unrendered rows are never hidden
  function resultsTableFilter(settings, data, dataIndex) {
    if (settings.nTable !== document.getElementById('resultsDataTable')) return true;

    const statusFilter = $('#dt_filter_status').val() ?? '';
    const stepFilter   = $('#dt_filter_step').val()   ?? '';

    if (!statusFilter && !stepFilter) return true;

    const rowNode = settings.aoData[dataIndex].nTr;
    if (!rowNode) return true;

    const progress = parseInt(rowNode.getAttribute('data-progress') ?? '');
    const step     = String(rowNode.getAttribute('data-step') ?? '');

    if (statusFilter) {
      const p = isNaN(progress) ? 0 : progress;
      if (statusFilter === '100'      && p !== 100)              return false;
      if (statusFilter === '0'        && p !== 0)                return false;
      if (statusFilter === 'progress' && (p === 0 || p === 100)) return false;
    }

    if (stepFilter && step !== stepFilter) return false;

    return true;
  }

  // Remove any stale copy before pushing (safe for re-entry / AJAX navigation)
  $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn !== resultsTableFilter);
  $.fn.dataTable.ext.search.push(resultsTableFilter);

  // Clean up on page leave so the global stack never accumulates
  $(window).one('beforeunload', function () {
    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(fn => fn !== resultsTableFilter);
  });

  $(document).on('change', '#dt_filter_company', function () {
    table.column(1).search(this.value).draw();
  });

  $(document).on('change', '#dt_filter_step, #dt_filter_status', function () {
    table.draw();
  });

  // 3. Selection & Bulk UI
  const updateSelectionUI = () => {
    const selected = $('.row-checkbox:checked');
    const tagContainer = $('#selection-tags');
    const bulkContainer = $('#bulk-actions-container');
    tagContainer.empty();

    if (selected.length > 0) {
      $('#selection-wrapper').show();
      bulkContainer.removeClass('d-none');
      selected.each(function () {
        const id = $(this).val();
        const name = $(this).closest('tr').data('name');
        tagContainer.append(`<span class="badge bg-label-primary d-flex align-items-center gap-1 py-2 px-3">${name}<i class="ti ti-x ms-1 cursor-pointer remove-tag" data-id="${id}"></i></span>`);
      });
    } else {
      bulkContainer.addClass('d-none');
      $('#selection-wrapper').hide();
      $('#selectAll').prop('checked', false);
    }
  };

  $('#selectAll').on('change', function () { $('.row-checkbox').prop('checked', this.checked); updateSelectionUI(); });
  $(document).on('change', '.row-checkbox', updateSelectionUI);
  $(document).on('click', '.remove-tag', function () {
    const id = $(this).data('id');
    $(`.row-checkbox[value="${id}"]`).prop('checked', false).trigger('change');
  });

  // 4. Modal
  $('.select2-modal').select2({ dropdownParent: $('#stepModal') });

  window.openStepModal = function (id, currentStepName) {
    if (id === 'bulk') {
      const ids = $('.row-checkbox:checked').map(function () { return this.value; }).get();
      $('#target_company_ids').val(ids.join(','));
      $('#current_step_display').text("{{ __('admin.mutiple_selection') }} (" + ids.length + ")");
      // Use select2's val+trigger only on the modal select — scoped to prevent bubbling to filter listeners
      $('#modal_step_select').val('0').trigger('change.select2');
    } else {
      $('#target_company_ids').val(id);
      const displayStatus = (currentStepName === 'N/A' || !currentStepName) ? "{{ __('admin.Disapprove') }}" : currentStepName;
      $('#current_step_display').text(displayStatus);

      // Find matching option and set silently — trigger only select2's internal update, not DOM change
      let matchedVal = '';
      $('#modal_step_select option').each(function () {
        if ($(this).text().trim() === currentStepName) {
          matchedVal = $(this).val();
          return false; // break loop — prevent repeated triggers
        }
      });
      $('#modal_step_select').val(matchedVal || '0').trigger('change.select2');
    }
    $('#stepModal').modal('show');
  };

  // 5. Ajax Submit
  $('#stepForm').on('submit', function (e) {
    e.preventDefault();

    releaseAlert('warning', {
      title: "{{ __('cycles.sure change step') }}",
      message: "",
      confirmColor: "primary",
      onConfirm: () => {
        const btn = $('#btnSaveStep');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

        // Split comma-separated IDs into a real array so jQuery serializes
        // them as company[]=1&company[]=2&... instead of company=1,2,3
        const companyIds = $('#target_company_ids').val()
          .split(',')
          .map(id => id.trim())
          .filter(id => id !== '');

        $.ajax({
          url: "/dashboard/screening/changestep",
          method: "POST",
          data: {
            _token: "{{ csrf_token() }}",
            company: companyIds,
            step: $('#modal_step_select').val()
          },
          success: function () {
            location.reload();
          },
          error: function (xhr, status, error) {
            releaseAlert('danger', {
              title: "{{ __('admin.Error') }}",
              message: xhr.responseJSON.message ? xhr.responseJSON.message : "{{ __('admin.unexpected_error') }}",
            });

            btn.prop('disabled', false).text("{{ __('admin.Save Changes') }}");
          }
        });
      }
    });
  });
  // 6. Export Excel
  $(document).on('click', '.ExportExcelTresult', function () {
    const step = '{{ $csession?->step_id }}';

    if (!step) {
      alert('{{ __("admin.No_Session_Message") }}');
      return;
    }

    const url = window.location.origin + '/dashboard/export-Tresult?step=' + step;
    window.open(url, '_blank');
  });
});
</script>
@stop