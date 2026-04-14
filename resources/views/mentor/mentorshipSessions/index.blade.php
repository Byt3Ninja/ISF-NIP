@extends('layouts.admin')

@section('content')

@include('admin.tasks.partials.tasks-count-cards')

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center border-bottom">
    <h5 class="card-title mb-0">{{ __('admin.tasks') }}</h5>
    <x-export-dropdown callback="exportCycles" />
  </div>
  <div class="card-datatable table-responsive p-0">
    <table id="cyclesTable" class="table align-middle mb-0" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>{{ __('admin.Program') }}</th>
          <th>{{ __('admin.Completed') }}</th>
          <th>{{ __('admin.Pending') }}</th>
          <th>{{ __('admin.Total')  }}</th>
          <th>{{ __('admin.overall_status') }}</th>
          <th class="text-center">{{ __('admin.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cycles as $cycle)
          @php
            $total    = $cycle['completed'] + $cycle['pending'];
            $progress = $total > 0 ? round(($cycle['completed'] / $total) * 100) : 0;
          @endphp
          <tr>
            <td><strong>{{ $cycle['title'] }}</strong></td>
            <td><span class="badge bg-label-success fw-bold">{{ $cycle['completed'] }}</span></td>
            <td><span class="badge bg-label-warning fw-bold">{{ $cycle['pending'] }}</span></td>
            <td><span class="badge bg-label-info fw-bold">{{ $total }}</span></td>
            <td>
              @if($cycle['pending'] == 0 && $total > 0)
                <span class="badge bg-label-success">{{ __('admin.Completed') }}</span>
              @elseif($cycle['completed'] == 0)
                <span class="badge bg-label-secondary">{{ __('admin.NotStarted') }}</span>
              @else
                <div class="progress" style="height: 8px;">
                  <div
                    class="progress-bar bg-primary"
                    role="progressbar"
                    style="width: {{ $progress }}%"
                    aria-valuenow="{{ $progress }}"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  ></div>
                </div>
                <small>{{ $progress }}% {{ __('admin.Done') }}</small>
              @endif
            </td>
            <td class="text-center">
              <a
                href="{{ route('mentorshipTasks.index', ['cycle_id' => $cycle['id']]) }}"
                class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
              >
                <i class="ti ti-eye ti-md"></i>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

  const table = $('#cyclesTable').DataTable({
    ...getSharedDataTableConfig({ title: 'Session_Tasks', actionsIndex: 5 }),
    dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
    initComplete: function () {
      const api = this.api();

      const $filterContainer = $('<div class="d-flex flex-wrap gap-2 mt-3 mt-md-0 ms-md-8"></div>');

      $('.dataTables_length').after($filterContainer);
      const addDropdownFilter = (colIndex, id, placeholder, width = 240) => {
        api.columns(colIndex).every(function () {
          const column = this;

          const $select = $(
            `<select id="${id}" class="form-select" style="min-width:${width}px">
              <option value="">${placeholder}</option>
            </select>`
          )
            .appendTo($filterContainer)
            .on('change', function () {
              const val = $(this).val().trim().toLowerCase();

              if (!val) {
                column.search('', true, false).draw();
                return;
              }

              const escaped = $.fn.dataTable.util.escapeRegex(val);
              column.search(`^\\s*${escaped}\\s*$`, true, false).draw();
            });

          column
            .data()
            .unique()
            .sort()
            .each(function (d) {
              const text = $('<div>').html(d).text().replace(/\s+/g, ' ').trim();
              if (text) {
                $select.append(
                  `<option value="${text.toLowerCase()}">${text}</option>`
                );
              }
            });
        });
      };

      addDropdownFilter(0, 'cycleFilter', '{{ __("admin.All_Programs") }}', 240);

      setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
      }, 200);
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

  // Handle Export
  window.exportCycles = function (format) {
    const btnMap = {
      csv:   '.buttons-csv',
      excel: '.buttons-excel',
    };
    const selector = btnMap[format];
    if (selector) {
      table.button(selector).trigger();
    }
  };

});
</script>
@endsection