@extends('layouts.admin')

@php
$surveys = collect([
    (object)[
        'id' => 1,
        'title' => 'Q1 Mentorship Feedback',
        'created_at' => now(),
        'due_date' => '2025-01-20 23:59:59',
        'status' => 'Pending',
        'survey_type' => 'Mentorship',
        'total_questions' => 10,
        'completed_questions' => 0,
        'is_required' => true,
        'user' => (object)[
            'id' => 11,
            'name' => 'Ahmed Ali',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 101,
                'name' => 'Tech Solutions',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => (object)[
            'id' => 1,
            'zoom_date' => '2025-01-10 14:00:00',
        ],
    ],
    (object)[
        'id' => 2,
        'title' => 'Program Evaluation Survey',
        'created_at' => '2025-01-05 10:00:00',
        'due_date' => '2025-01-15 23:59:59',
        'status' => 'Completed',
        'survey_type' => 'Program',
        'total_questions' => 15,
        'completed_questions' => 15,
        'is_required' => false,
        'user' => (object)[
            'id' => 12,
            'name' => 'Sara Mohamed',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 102,
                'name' => 'InnovateX',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => null,
    ],
    (object)[
        'id' => 3,
        'title' => 'Monthly Check-in',
        'created_at' => '2025-01-08 09:00:00',
        'due_date' => '2025-01-22 23:59:59',
        'status' => 'In_Progress',
        'survey_type' => 'Program',
        'total_questions' => 8,
        'completed_questions' => 4,
        'is_required' => true,
        'user' => (object)[
            'id' => 13,
            'name' => 'Omar Hassan',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 103,
                'name' => 'NextGen Labs',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => (object)[
            'id' => 3,
            'zoom_date' => '2025-01-12 16:30:00',
        ],
    ],
    (object)[
        'id' => 4,
        'title' => 'Quarterly Assessment',
        'created_at' => '2025-01-02 08:00:00',
        'due_date' => '2025-01-25 23:59:59',
        'status' => 'Pending',
        'survey_type' => 'Assessment',
        'total_questions' => 20,
        'completed_questions' => 0,
        'is_required' => false,
        'user' => (object)[
            'id' => 14,
            'name' => 'Mona Adel',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 104,
                'name' => 'Bright Minds',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => null,
    ],
    (object)[
        'id' => 5,
        'title' => 'Exit Survey',
        'created_at' => '2025-01-01 12:00:00',
        'due_date' => '2025-01-10 23:59:59',
        'status' => 'Completed',
        'survey_type' => 'Program',
        'total_questions' => 12,
        'completed_questions' => 12,
        'is_required' => true,
        'user' => (object)[
            'id' => 15,
            'name' => 'Youssef Samir',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 105,
                'name' => 'CodeCraft',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => null,
    ],
    (object)[
        'id' => 6,
        'title' => 'Mid-Program Feedback',
        'created_at' => '2025-01-06 15:30:00',
        'due_date' => '2025-01-18 23:59:59',
        'status' => 'Pending',
        'survey_type' => 'Program',
        'total_questions' => 10,
        'completed_questions' => 0,
        'is_required' => true,
        'user' => (object)[
            'id' => 16,
            'name' => 'Nada Fathy',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 106,
                'name' => 'FutureSoft',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => null,
    ],
    (object)[
        'id' => 7,
        'title' => 'Workshop Evaluation',
        'created_at' => '2025-01-07 11:00:00',
        'due_date' => '2025-01-17 23:59:59',
        'status' => 'In_Progress',
        'survey_type' => 'Workshop',
        'total_questions' => 6,
        'completed_questions' => 3,
        'is_required' => false,
        'user' => (object)[
            'id' => 17,
            'name' => 'Khaled Nabil',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 107,
                'name' => 'AI Works',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => null,
    ],
    (object)[
        'id' => 8,
        'title' => 'Mentorship Satisfaction',
        'created_at' => '2025-01-04 13:00:00',
        'due_date' => '2025-01-14 23:59:59',
        'status' => 'Completed',
        'survey_type' => 'Mentorship',
        'total_questions' => 10,
        'completed_questions' => 10,
        'is_required' => true,
        'user' => (object)[
            'id' => 18,
            'name' => 'Salma Tarek',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 108,
                'name' => 'DesignPro',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'session' => (object)[
            'id' => 8,
            'zoom_date' => '2025-01-17 10:30:00',
        ],
    ],
]);

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
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
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
                    <a href="/dashboard/surveys/{{ $survey->id }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-player-play ti-xs me-1"></i>
                      {{ __('admin.start_survey') }}
                    </a>
                  @elseif ($survey->status == 'In_Progress')
                    <a href="/dashboard/surveys/{{ $survey->id }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-progress ti-xs me-1"></i>
                      {{ __('admin.complete_survey') }}
                    </a>
                  @elseif ($survey->status == 'Completed')
                    <a href="/dashboard/surveys/{{ $survey->id }}" class="btn btn-sm btn-primary">
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