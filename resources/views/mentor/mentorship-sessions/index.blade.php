@extends('layouts.admin')

@php
$mentorship_sessions = collect([
    (object)[
        'id' => 1,
        'zoom_date' => now(),
        'period' => 60,
        'status' => 'Pending',
        'session_type' => 'Online',
        'location' => null,
        'start_url' => 'https://zoom.us/start/1',
        'passcode' => '123456',
        'comment' => 'Initial session',
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
        'survey' => (object)[
            'id' => 201,
            'status' => 'Pending',
            'is_required' => true,
        ],
    ],
    (object)[
        'id' => 2,
        'zoom_date' => '2025-01-11 10:00:00',
        'period' => 45,
        'status' => 'Completed',
        'session_type' => 'Offline',
        'location' => 'Cairo Hub',
        'start_url' => 'https://zoom.us/start/2',
        'passcode' => null,
        'comment' => 'Went well',
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
        'survey' => (object)[
            'id' => 202,
            'status' => 'Completed',
            'is_required' => false,
        ],
    ],
    (object)[
        'id' => 3,
        'zoom_date' => '2025-01-12 16:30:00',
        'period' => 30,
        'status' => 'In_Progress',
        'session_type' => 'Online',
        'location' => null,
        'start_url' => 'https://zoom.us/start/3',
        'passcode' => '789123',
        'comment' => null,
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
        'survey' => (object)[
            'id' => 203,
            'status' => 'In_Progress',
            'is_required' => true,
        ],
    ],
    (object)[
        'id' => 4,
        'zoom_date' => '2025-01-13 09:00:00',
        'period' => 90,
        'status' => 'Pending',
        'session_type' => 'Offline',
        'location' => 'Alexandria Office',
        'start_url' => 'https://zoom.us/start/4',
        'passcode' => null,
        'comment' => 'Waiting confirmation',
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
        'survey' => (object)[
            'id' => 204,
            'status' => 'Pending',
            'is_required' => false,
        ],
    ],
    (object)[
        'id' => 5,
        'zoom_date' => '2025-01-14 11:00:00',
        'period' => 60,
        'status' => 'Completed',
        'session_type' => 'Online',
        'location' => null,
        'start_url' => 'https://zoom.us/start/5',
        'passcode' => '555888',
        'comment' => 'Excellent feedback',
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
        'survey' => (object)[
            'id' => 205,
            'status' => 'Completed',
            'is_required' => true,
        ],
    ],
    (object)[
        'id' => 6,
        'zoom_date' => '2025-01-15 15:00:00',
        'period' => 45,
        'status' => 'Pending',
        'session_type' => 'Online',
        'location' => null,
        'start_url' => 'https://zoom.us/start/6',
        'passcode' => null,
        'comment' => null,
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
        'survey' => (object)[
            'id' => 206,
            'status' => 'Pending',
            'is_required' => true,
        ],
    ],
    (object)[
        'id' => 7,
        'zoom_date' => '2025-01-16 13:00:00',
        'period' => 30,
        'status' => 'In_Progress',
        'session_type' => 'Offline',
        'location' => 'Smart Village',
        'start_url' => 'https://zoom.us/start/7',
        'passcode' => '111222',
        'comment' => 'Ongoing',
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
        'survey' => (object)[
            'id' => 207,
            'status' => 'In_Progress',
            'is_required' => false,
        ],
    ],
    (object)[
        'id' => 8,
        'zoom_date' => '2025-01-17 10:30:00',
        'period' => 60,
        'status' => 'Completed',
        'session_type' => 'Online',
        'location' => null,
        'start_url' => 'https://zoom.us/start/8',
        'passcode' => null,
        'comment' => 'Completed successfully',
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
        'survey' => (object)[
            'id' => 208,
            'status' => 'Completed',
            'is_required' => true,
        ],
    ],
    (object)[
        'id' => 9,
        'zoom_date' => '2025-01-18 12:00:00',
        'period' => 45,
        'status' => 'Pending',
        'session_type' => 'Offline',
        'location' => 'Downtown Office',
        'start_url' => 'https://zoom.us/start/9',
        'passcode' => null,
        'comment' => 'Rescheduled',
        'user' => (object)[
            'id' => 19,
            'name' => 'Hassan Magdy',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 109,
                'name' => 'MarketGuru',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'survey' => (object)[
            'id' => 209,
            'status' => 'Pending',
            'is_required' => false,
        ],
    ],
    (object)[
        'id' => 10,
        'zoom_date' => '2025-01-19 17:00:00',
        'period' => 90,
        'status' => 'Completed',
        'session_type' => 'Online',
        'location' => null,
        'start_url' => 'https://zoom.us/start/10',
        'passcode' => '999000',
        'comment' => 'Final mentorship',
        'user' => (object)[
            'id' => 20,
            'name' => 'Laila Mostafa',
            'image_url' => '/icons/avatar.svg',
            'company' => (object)[
                'id' => 110,
                'name' => 'Startup Hub',
                'logo_url' => '/images/logo-placeholder.png',
            ],
        ],
        'survey' => (object)[
            'id' => 210,
            'status' => 'Completed',
            'is_required' => true,
        ],
    ],
]);

// Sort surveys by status priority: In_Progress > Pending > Completed
$mentorship_sessions = $mentorship_sessions->sortBy(function($session) {
    $statusPriority = [
        'In_Progress' => 1,
        'Pending' => 2,
        'Completed' => 3,
    ];
    return $statusPriority[$session->status] ?? 999;
});
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0 text-capitalize">
          {{ __("admin.Mentorship Sessions") }}
        </h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="table mentorship-sessions-table">
          <thead class="table-light">
            <th> {{ __('admin.Company') }} </th>
            <th> {{ __('admin.registered') }} </th>
            <th> {{ __('admin.Date & Time') }} </th>
            <th> {{ __('admin.session_status') }} </th>
            <th> {{ __('dashboard.Session_Type') }} </th>
            <th> {{ __('admin.survey_status') }} & {{ __('admin.Priority') }} </th>
            <th> {{ __('admin.Notes') }} </th>
            <th> {{ __('dashboard.Actions') }} </th>
          </thead>
          <tbody>
            @foreach( $mentorship_sessions as $key => $session )
            <tr>
              <td style="width: 100px;">
                <a 
                  class="d-flex align-items-center" style="width: 100px;"
                  @if ($_user->ableTo('company.profile_view', $permissions) && isset($session->user->company->id))
                    href="/dashboard/companyProfile/{{ $session->user->company->id }}" 
                  @else
                    href="javascript:void(0)"
                  @endif
                >
                  <img 
                    class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                    src="{{ $session->user->company->logo_url }}"
                    onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
                    height="36"
                    width="36"
                    alt="logo" 
                  />
                  <small class="text-dark">{{ $session->user->company->name }}</small>
                </a>
              </td>
              <td style="width: 100px;">
                <a 
                  class="d-flex align-items-center" style="width: 100px;"
                  @if ($_user->ableTo('users.edit', $permissions) && isset($session->user->id ))
                    href="/dashboard/users/{{ $session->user->id }}/edit"
                  @else
                    href="javascript:void(0)"
                  @endif
                >
                  <img 
                    class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                    src="{{ $session->user->image_url }}"
                    onerror="this.onerror=null;this.src='/icons/avatar.svg';"
                    height="36"
                    width="36"
                    alt="avatar" 
                  />
                  <small class="text-dark">{{ $session->user->name }}</small>
                </a>
              </td>
              <td> 
                <div class="text-muted">
                  <span class="small text-nowrap">{{ FormatDate($session->zoom_date, 'Y-m-d') }}</span>
                  <br />
                  <span class="small text-nowrap">{{ FormatDate($session->zoom_date, 'H:i A') }}</span>
                  <span class="small text-nowrap">({{ $session->period }} {{ __('admin.Min') }}) </span>
                </div>
              </td>
              <td> 
                @if ($session->status == 'Pending')
                  <div class="badge bg-label-warning">{{ __('admin.Pending') }}</div>
                @elseif ($session->status == 'Completed')
                  <div class="badge bg-label-success">{{ __('admin.Completed') }}</div>
                @else
                  <div class="badge bg-label-primary">{{ __('admin.In_Progress') }}</div>
                @endif
              </td>
              <td>
                <div class="d-flex justify-content-start align-items-start" style="width: 150px;">
                  <div class="d-flex flex-column " style="width: 100%; min-width: 0;">
                    @if ($session->session_type == 'Offline')
                      <span class="badge bg-label-secondary" style="width: fit-content;">{{ __('mentor.offline') }}</span>
                      <div class="d-flex align-items-center gap-1 mt-2">
                        <i class="ti ti-map-pin ti-14px"></i>
                        <small class="text-muted">{{ $session->location ?? '' }}</small>
                      </div>
                    @else
                      <span class="badge bg-label-info" style="width: fit-content;">{{ __('mentor.online') }}</span>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                <div>
                  @if ($session->survey->status == 'Pending')
                    <span class="badge bg-label-warning">{{ __('admin.Pending') }}</span>
                  @elseif ($session->survey->status == 'Completed')
                    <span class="badge bg-label-success">{{ __('admin.Completed') }}</span>
                  @else
                    <span class="badge bg-label-primary">{{ __('admin.In_Progress') }}</span>
                  @endif

                  @if ($session->survey->is_required)
                    <span class="badge bg-label-danger mt-2">{{ __('admin.mandatory') }}</span>
                  @else 
                    <span class="badge bg-label-secondary mt-2">{{ __('admin.optional') }}</span>
                  @endif
                </div>
              </td>
              <td>
                <small class="text-muted line-clamp-2">
                  {{ $session->comment ?? '...' }}
                </small>
              </td>
              <td>
                @php
                  $now = date('Y-m-d H:i');
                  $start_time = date('Y-m-d H:i', strtotime($session->zoom_date . '-15 minutes'));
                  $end_time = date('Y-m-d H:i', strtotime($session->zoom_date . '+' . $session->period .' minutes'));
                  @endphp

                <div class="d-flex align-items-center flex-wrap text-nowrap gap-2">
                  @if ($session->survey->status == 'Pending')
                    <a href="/dashboard/surveys/{{ $session->survey->id }}" class="btn btn-sm btn-outline-primary">
                      {{ __('admin.start_survey') }}
                    </a>
                  @elseif ($session->survey->status == 'In_Progress')
                    <a href="/dashboard/surveys/{{ $session->survey->id }}" class="btn btn-sm btn-outline-primary">
                      {{ __('admin.complete_survey') }}
                    </a>
                  @elseif ($session->survey->status == 'Completed')
                    <a href="/dashboard/surveys/{{ $session->survey->id }}" class="btn btn-sm btn-outline-primary">
                      {{ __('admin.view_survey') }}
                    </a>
                  @endif

                  @if( $now >= $start_time )
                    @if( $now <= $end_time )
                      <a href="{{ $session->start_url }}" target="_blank">
                        <button type="button" data-value="{{ $session->id }}" type="button" class="btn btn-sm btn-primary rounded">
                          {{ __('admin.Join Meeting') }}
                        </button>
                      </a>
                      @if( !empty( $session->passcode ) )
                        | <button title="Show Passcode" type="button" data-passcode="{{ $session->passcode }}" type="button" class="btn btn-sm btn-label-success show-passcode rounded">
                          <i class="ti ti-key"></i>
                        </button>
                      @endif
                    @endif
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
    // Initialize mentorship sessions DataTable
    $('.mentorship-sessions-table')?.DataTable({
      processing: true,
      pageLength: 10,
      order: [],
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
    
        // Startup (Company)
        addDropdownFilter(
          1,
          'startupFilter',
          '{{ __("cycles.All Startups") }}',
          200
        );
    
        // Session Status
        addDropdownFilter(
          4,
          'sessionStatusFilter',
          '{{ __("cycles.All Status") }}'
        );
    
        // Survey Mandatory / Optional
        api.columns(6).every(function () {
          const column = this;
    
          const select = $(`
            <select id="surveyMandatoryFilter"
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