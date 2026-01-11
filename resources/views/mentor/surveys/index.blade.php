@extends('layouts.admin')

@php


// Sort surveys by status priority: In_Progress > Pending > Completed
$surveys = $surveys->sortBy(function($survey) {
    $statusPriority = [
        'In_Progress' => 1,
        'Pending' => 2,
        'Completed' => 3,
    ];
    return $statusPriority[$survey->status] ?? 999;
});
@endphp

@section('content')
<div class="">
  <div class="">
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0 text-capitalize">
          {{ __("admin.Surveys") }}
        </h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="table surveys-table">
          <thead class="table-light">
            <th> {{ __('admin.Company') }} </th>
            <th> {{ __('admin.User') }} </th>
            <th> {{ __('admin.Title') }} </th>
            <th> {{ __('admin.surveyType') }} </th>
            <th> {{ __('admin.Due_Date') }} </th>
            <th> {{ __('admin.Progress') }} </th>
            <th> {{ __('admin.Status') }} </th>
            <th> {{ __('admin.Priority') }} </th>
            <th> {{ __('dashboard.Actions') }} </th>
          </thead>
          <tbody>
            @foreach( $surveys as $key => $survey )
            <tr>
              <td style="width: 100px;">
                <a 
                  class="d-flex align-items-center" style="width: 100px;"
                  @if ($_user->ableTo('company.profile_view', $permissions) && isset($survey->user->company->id))
                    href="/dashboard/companyProfile/{{ $survey->user->company->id }}" 
                  @else
                    href="javascript:void(0)"
                  @endif
                >
                  <img 
                    class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                    src="{{ $survey->user->company->logo_url }}"
                    onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
                    height="36"
                    width="36"
                    alt="logo" 
                  />
                  <small class="text-dark">{{ $survey->user->company->name }}</small>
                </a>
              </td>
              <td style="width: 100px;">
                <a 
                  class="d-flex align-items-center" style="width: 100px;"
                  @if ($_user->ableTo('users.edit', $permissions) && isset($survey->user->id ))
                    href="/dashboard/users/{{ $survey->user->id }}/edit"
                  @else
                    href="javascript:void(0)"
                  @endif
                >
                  <img 
                    class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                    src="{{ $survey->user->image_url }}"
                    onerror="this.onerror=null;this.src='/icons/avatar.svg';"
                    height="36"
                    width="36"
                    alt="avatar" 
                  />
                  <small class="text-dark">{{ $survey->user->name }}</small>
                </a>
              </td>
              <td>
                <div class="d-flex flex-column">
                  <span class="fw-medium">{{ $survey->title }}</span>
                  @if ($survey->session)
                    <small class="text-muted">
                      <i class="ti ti-calendar ti-xs"></i>
                      Session: {{ FormatDate($survey->session->zoom_date, 'Y-m-d H:i') }}
                    </small>
                  @endif
                </div>
              </td>
              <td>
                @if ($survey->survey_type == 'Mentorship')
                  <span class="badge bg-label-info">{{ __('admin.Mentorship') }}</span>
                @elseif ($survey->survey_type == 'Program')
                  <span class="badge bg-label-primary">{{ __('admin.Program') }}</span>
                @elseif ($survey->survey_type == 'Assessment')
                  <span class="badge bg-label-warning">{{ __('admin.Assessment') }}</span>
                @elseif ($survey->survey_type == 'Workshop')
                  <span class="badge bg-label-secondary">{{ __('admin.Workshop') }}</span>
                @elseif ($survey->survey_type == 'Exit')
                  <span class="badge bg-label-danger">{{ __('admin.Exit') }}</span>
                @else
                  <span class="badge bg-label-dark">{{ __('admin.Check-in') }}</span>
                @endif
              </td>
              <td> 
                @php
                  $now = strtotime(date('Y-m-d H:i'));
                  $due = strtotime($survey->due_date);
                  $isOverdue = $now > $due && $survey->status != 'Completed';
                @endphp
                <div class="text-muted">
                  <span class="small text-nowrap {{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                    {{ FormatDate($survey->due_date, 'Y-m-d') }}
                  </span>
                  <span class="small text-nowrap {{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                    {{ FormatDate($survey->due_date, 'H:i A') }}
                  </span>
                  @if ($isOverdue)
                    <br />
                    <span class="badge bg-label-danger mt-1">{{ __('admin.overdue') }}</span>
                  @endif
                </div>
              </td>
              <td>
                @php
                  $progress = $survey->total_questions > 0 
                    ? round(($survey->completed_questions / $survey->total_questions) * 100) 
                    : 0;
                @endphp
                <div class="d-flex flex-column gap-1">
                  <small class="text-muted">
                    {{ $survey->completed_questions }}/{{ $survey->total_questions }} {{ __('admin.Questions') }}
                  </small>
                  <div class="progress" style="height: 6px;">
                    <div 
                      class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}" 
                      role="progressbar" 
                      style="width: {{ $progress }}%;" 
                      aria-valuenow="{{ $progress }}" 
                      aria-valuemin="0" 
                      aria-valuemax="100">
                    </div>
                  </div>
                  <small class="text-muted">{{ $progress }}%</small>
                </div>
              </td>
              <td> 
                @if ($survey->status == 'Pending')
                  <div class="badge bg-label-warning">{{ __('admin.Pending') }}</div>
                @elseif ($survey->status == 'Completed')
                  <div class="badge bg-label-success">{{ __('admin.Completed') }}</div>
                @else
                  <div class="badge bg-label-primary">{{ __('admin.In_Progress') }}</div>
                @endif
              </td>
              <td>
                @if ($survey->is_required)
                  <span class="badge bg-label-danger">{{ __('admin.mandatory') }}</span>
                @else 
                  <span class="badge bg-label-secondary">{{ __('admin.optional') }}</span>
                @endif
              </td>
              <td>
                <div class="d-flex align-items-center flex-wrap text-nowrap gap-2">
                  @if ($survey->status == 'Pending')
                    <a href="/dashboard/surveys/start/{{ $survey->id }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-player-play ti-xs me-1"></i>
                      {{ __('admin.start_survey') }}
                    </a>
                  @elseif ($survey->status == 'In_Progress')
                    <a href="/dashboard/surveys/start/{{ $survey->id }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-progress ti-xs me-1"></i>
                      {{ __('admin.complete_survey') }}
                    </a>
                  @elseif ($survey->status == 'Completed')
                    <a href="/dashboard/surveys/start/{{ $survey->id }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-eye ti-xs me-1"></i>
                      {{ __('admin.view_survey') }}
                    </a>
                  @endif
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('pagescripts')
<script>
  $(function() {
    // Initialize surveys DataTable
    $('.surveys-table')?.DataTable({
      processing: true,
      pageLength: 10,
      order: [], // Remove default ordering to keep PHP sort order
      dom: '<"row"' +
        '<"col-12 col-lg-10 d-flex flex-wrap align-items-center ps-0 ps-lg-3"l>' +
        '<"col-12 col-lg-2 dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 pe-0 pe-lg-2"f>' +
        '>' +
        't' +
        '<"row px-6"' +
          '<"col-sm-12 col-lg-6"i>' +
          '<"col-sm-12 col-lg-6 d-flex justify-content-end"p>' +
        '>',
      language: {
        url: '{{ $l }}' === "ar"
          ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
          : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",	
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: '{{ __('admin.Search') }}...',
      },
    
      initComplete: function () {
        const api = this.api();
    
        const filterContainer = $(
          '<div class="d-flex flex-wrap flex-md-nowrap gap-3 ms-3"></div>'
        );
    
        $('.dataTables_length').after(filterContainer);
    
        const addDropdownFilter = (colIndex, id, placeholder, width = 150) => {
          api.columns(colIndex).every(function () {
            const column = this;
    
            const select = $(`
              <select id="${id}"
                class="form-select text-capitalize"
                style="min-width:${width}px">
                <option value="">${placeholder}</option>
              </select>
            `)
              .appendTo(filterContainer)
              .on('change', function () {
                let val = $(this).val();
    
                if (!val) {
                  column.search('', true, false).draw();
                  return;
                }
    
                // ✅ Normalize input
                val = val.trim().toLowerCase();
    
                // ✅ Escape regex safely
                val = $.fn.dataTable.util.escapeRegex(val);
    
                // ✅ Match exact word ignoring extra spaces
                column
                  .search(`^\\s*${val}\\s*$`, true, false)
                  .draw();
              });
    
            // Populate unique values (clean text only)
            column
              .data()
              .unique()
              .sort()
              .each(function (d) {
                const text = $('<div>')
                  .html(d)
                  .text()
                  .replace(/\s+/g, ' ')
                  .trim();
    
                if (text) {
                  select.append(
                    `<option value="${text.toLowerCase()}">${text}</option>`
                  );
                }
              });
          });
        };
    
        // Company Filter
        addDropdownFilter(
          0,
          'companyFilter',
          '{{ __("cycles.All Startups") }}',
          200
        );
    
        // Survey Type Filter
        addDropdownFilter(
          3,
          'surveyTypeFilter',
          '{{ __("admin.All_Types") }}'
        );
    
        // Status Filter
        addDropdownFilter(
          6,
          'statusFilter',
          '{{ __("cycles.All Status") }}'
        );
    
        // Priority (Mandatory/Optional) Filter
        api.columns(7).every(function () {
          const column = this;
    
          const select = $(`
            <select id="priorityFilter"
              class="form-select text-capitalize"
              style="min-width:170px">
              <option value="">
                {{ __("admin.All_Priorities") }}
              </option>
              <option value="mandatory">
                {{ __("admin.mandatory") }}
              </option>
              <option value="optional">
                {{ __("admin.optional") }}
              </option>
            </select>
          `)
          .appendTo(filterContainer)
          .on('change', function () {
            const val = $(this).val();
            column.search(val ? val : '', true, false).draw();
          });
        });
    
        setTimeout(() => {
          $('.dataTables_filter .form-control').removeClass('form-control-sm');
          $('.dataTables_length .form-select').removeClass('form-select-sm');
        }, 300);
      }
    });
  });
</script>
@endsection