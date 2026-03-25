@extends('layouts.admin')

@section('content')
<div class="">
  <div class="">
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
            @if( !$_user->hasRole('mentor') )
              <th> {{ __('admin.Mentor') }} </th>
            @endif
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
                  @if ($_user->ableTo('company.profile_view', $permissions) && isset($session->user->company->id ))
                    href="/dashboard/companyProfile/{{ $session->user->company->id }}"
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
              @if( !$_user->hasRole('mentor') )
                <td style="width: 100px;">
                  <a 
                    class="d-flex align-items-center" style="width: 100px;"
                    @if ($_user->ableTo('users.edit', $permissions) && isset($session->mentor->id ))
                      href="/dashboard/users/{{ $session->mentor->id }}/edit"
                    @else
                      href="javascript:void(0)"
                    @endif
                  >
                    <img 
                      class="rounded-circle me-2 border bg-label-light flex-shrink-0"
                      src="{{ $session->mentor->image_url }}"
                      onerror="this.onerror=null;this.src='/icons/avatar.svg';"
                      height="36"
                      width="36"
                      alt="avatar" 
                    />
                    <small class="text-dark">{{ $session->mentor->name }}</small>
                  </a>
                </td>
              @endif
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
                  @if($session->survey)
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
                  @else
                    <span class="text-muted">--</span>
                  @endif  
                </div>
              </td>
              <td>
                <small class="text-muted line-clamp-2">
                  {{ $session->comment ?? '...' }}
                </small>
              </td>
              <td>
                <div class="d-flex align-items-center flex-wrap text-nowrap gap-2">
                  @if($session->survey)
                    @if ($session->survey->status == 'Pending')
                      <a href="/dashboard/surveys/start/{{ $session->survey->id }}" class="btn btn-sm btn-outline-primary">
                        {{ __('admin.start_survey') }}
                      </a>
                    @elseif ($session->survey->status == 'In_Progress')
                      <a href="/dashboard/surveys/start/{{ $session->survey->id }}" class="btn btn-sm btn-outline-primary">
                        {{ __('admin.complete_survey') }}
                      </a>
                    @elseif ($session->survey->status == 'Completed')
                      <a href="/dashboard/surveys/start/{{ $session->survey->id }}" class="btn btn-sm btn-outline-primary">
                        {{ __('admin.view_survey') }}
                      </a>
                    @endif
                  @else
                    <span class="text-muted">--</span>
                  @endif
                  @if( $now >= $session->start_time )
                    @if( $now <= $session->end_time )
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