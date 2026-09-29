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
              <th> {{ __('admin.Date & Time') }} </th>
              <th> {{ __('admin.Company') }} </th>
              <th> {{ __('admin.registered') }} </th>
              <th> {{ __('dashboard.Session_Type') }} </th>
              <th> {{ __('admin.session_status') }} </th>
              <th> {{ __('admin.Notes') }} </th>
              <th> {{ __('dashboard.Actions') }} </th>
            </thead>
            <tbody>
              @foreach($mentorship_sessions as $session)
                @php $showJoin = $now >= $session->start_time && $now <= $session->end_time; @endphp
                <tr>
                  <td>
                    <div class="text-muted">
                      <span class="small text-nowrap">{{ FormatDate($session->zoom_date, 'Y-m-d') }}</span>
                      <br />
                      <span class="small text-nowrap">{{ FormatDate($session->zoom_date, 'H:i A') }}</span>
                      <span class="small text-nowrap">({{ $session->period }} {{ __('admin.Min') }})</span>
                    </div>
                  </td>
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
                      @if ($_user->ableTo('company.profile_view', $permissions) && isset($session->user->company->id))
                        href="/dashboard/show/Profile/{{ $session->user->id }}"
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
                    <div class="d-flex justify-content-start align-items-start" style="width: 150px;">
                      <div class="d-flex flex-column" style="width: 100%; min-width: 0;">
                        @if ($session->session_type == 'Offline')
                          <span class="badge bg-label-secondary" style="width: fit-content;">{{ __('mentor.offline') }}</span>
                        @else
                          <span class="badge bg-label-info" style="width: fit-content;">{{ __('mentor.online') }}</span>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    @if ($session->status == 'Upcoming')
                      <div class="badge bg-label-warning">{{ __('admin.Upcoming') }}</div>
                    @elseif ($session->status == 'Completed')
                      <div class="badge bg-label-success">{{ __('admin.Completed') }}</div>
                    @else
                      <div class="badge bg-label-primary">{{ __('admin.In_Progress') }}</div>
                    @endif
                  </td>
                  <td>
                    <small class="text-muted line-clamp-2">
                      {{ $session->comment ?? '...' }}
                    </small>
                  </td>
                  <td>
                    <div class="d-flex align-items-center flex-wrap text-nowrap gap-2">
                      @if ($session->approved !== 'Yes')
                        <span class="badge bg-label-warning">{{ __('admin.pending_approval') }}</span>
                      @elseif ($showJoin)
                        <a href="{{ $session->start_url }}" target="_blank" class="btn btn-sm btn-primary">
                          <i class="ti ti-brand-zoom me-1"></i>
                          {{ __('admin.join_session') }}
                        </a>
                      @elseif ($now > $session->end_time)
                        <span class="text-danger"><i class="ti ti-alarm me-1"></i>{{ __('admin.Meeting_Expired') }}</span>
                      @elseif ($session->session_type == 'Offline' && $session->google_map_url)
                        <a href="{{ $session->google_map_url }}" target="_blank" class="btn btn-sm btn-primary">
                          <i class="ti ti-map-pin me-1"></i>
                          {{ __('admin.map_link') }}
                        </a>
                        <div class="d-flex align-items-center gap-1 mt-0">
                          <i class="fas fa-map-marker-alt"></i>
                          <small class="text-dark">{{ $session->location_address ?? '-' }}</small>
                        </div>
                      @else
                        <span class="text-muted">-</span>
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
    $('.mentorship-sessions-table')?.DataTable({
      processing: true,
      pageLength: 10,
      order: [[0, 'desc']],
      order: [[0, 'desc']],
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
        emptyTable: '{{ __("admin.no_mentorship_sessions") }}',
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

                val = val.trim().toLowerCase();
                val = $.fn.dataTable.util.escapeRegex(val);

                column
                  .search(`^\\s*${val}\\s*$`, true, false)
                  .draw();
              });

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

        // Startup (Company) — column 1
        addDropdownFilter(
          1,
          'startupFilter',
          '{{ __("cycles.All Startups") }}',
          200
        );

        // Session Status — column 4
        addDropdownFilter(
          4,
          'sessionStatusFilter',
          '{{ __("cycles.All Status") }}'
        );

        setTimeout(() => {
          $('.dataTables_filter .form-control').removeClass('form-control-sm');
          $('.dataTables_length .form-select').removeClass('form-select-sm');
        }, 300);
      }
    });
  });
</script>
@endsection
