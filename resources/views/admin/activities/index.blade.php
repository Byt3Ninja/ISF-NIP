@extends('layouts.admin')

@section('title', __('admin.Activities'))

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    {{-- Alerts --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('admin.Close')"></button>
      </div>
    @endif

    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('admin.Close')"></button>
      </div>
    @endif

    <h4 class="fw-bold mb-4">@lang('admin.Activities_Dashboard')</h4>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
      @foreach ([
        ['label' => 'Total_Logins_Today', 'value' => $totalLoginsToday],
        ['label' => 'Total_Logins_Week', 'value' => $totalLoginsWeek],
        ['label' => 'Total_Logins_Month', 'value' => $totalLoginsMonth],
      ] as $stat)
      <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body text-center">
            <h5 class="fw-bold">@lang('admin.' . $stat['label'])</h5>
            <div class="display-4 fw-bold text-primary">{{ $stat['value'] }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Device/Browser/Country Stats --}}
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-device-desktop me-1"></i> @lang('admin.Top_Devices')</h6>
            @if ($topDevices->isNotEmpty())
            <ul class="list-group list-group-flush">
              @foreach ($topDevices as $device)
                <li class="list-group-item">{{ $device->device }}: <strong>{{ $device->count }}</strong> @lang('admin.Logins')</li>
              @endforeach
            </ul>
            @else
            <p class="text-muted">@lang('admin.No_devices_logged')</p>
            @endif
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-world me-1"></i> @lang('admin.Top_Browsers')</h6>
            @if ($topBrowsers->isNotEmpty())
            <ul class="list-group list-group-flush">
              @foreach ($topBrowsers as $browser)
                <li class="list-group-item">{{ $browser->browser }}: <strong>{{ $browser->count }}</strong> @lang('admin.Logins')</li>
              @endforeach
            </ul>
            @else
            <p class="text-muted">@lang('admin.No_browsers_logged')</p>
            @endif
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-flag me-1"></i> @lang('admin.Logins_by_Country')</h6>
            @if ($loginsByCountry->isNotEmpty())
            <ul class="list-group list-group-flush">
              @foreach ($loginsByCountry as $country)
                <li class="list-group-item">{{ $country->location }}: <strong>{{ $country->count }}</strong> @lang('admin.Logins')</li>
              @endforeach
            </ul>
            @else
            <p class="text-muted">@lang('admin.No_countries_logged')</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Peak Hours & Suspicious Logins --}}
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-clock me-1"></i> @lang('admin.Peak_Login_Hours')</h6>
            @if ($peakHours->isNotEmpty())
            <ul class="list-group list-group-flush">
              @foreach ($peakHours as $hour)
                <li class="list-group-item">
                  {{ sprintf('%02d:00', $hour->hour) }}: <strong>{{ $hour->count }}</strong> @lang('admin.Logins')
                </li>
              @endforeach
            </ul>
            @else
            <p class="text-muted">@lang('admin.No_peak_hours_logged')</p>
            @endif
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="mb-3 text-danger"><i class="ti ti-alert-triangle me-1"></i> @lang('admin.Suspicious_Logins')</h6>
            @if ($suspiciousLogins->isNotEmpty())
            <ul class="list-group list-group-flush">
              @foreach ($suspiciousLogins as $login)
                <li class="list-group-item">
                  <strong>{{ $login->user->first_name_en ?? 'Unknown' }} {{ $login->user->last_name_en ?? 'Unknown' }}</strong><br>
                  <small>@lang('admin.Logins'): {{ $login->login_count }}, @lang('admin.Locations'): {{ $login->locations ?? 'N/A' }}</small>
                </li>
              @endforeach
            </ul>
            @else
            <p class="text-muted">@lang('admin.No_suspicious_logins')</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Filter Form --}}
    @if ($activities->isNotEmpty())
    <div class="card mb-4">
      <div class="card-body">
        <h6 class="mb-3 text-primary"><i class="ti ti-filter me-1"></i> @lang('admin.Filter_Activities')</h6>
        <form method="GET">
          <div class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label">@lang('admin.user')</label>
              <input type="text" name="user" class="form-control" placeholder="@lang('admin.user')" value="{{ request('user') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.Date_From')</label>
              <input type="text" name="date_from" class="form-control datepicker" placeholder="@lang('admin.Date_From')" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.Date_To')</label>
              <input type="text" name="date_to" class="form-control datepicker" placeholder="@lang('admin.Date_To')" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.device')</label>
              <input type="text" name="device" class="form-control" placeholder="@lang('admin.device')" value="{{ request('device') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.location')</label>
              <input type="text" name="location" class="form-control" placeholder="@lang('admin.location')" value="{{ request('location') }}">
            </div>
            <div class="col-md-2 d-grid">
              <button type="submit" class="btn btn-primary">@lang('admin.Filter')</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @endif

    {{-- Activities Table --}}
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0 text-primary"><i class="ti ti-list-details me-1"></i> @lang('admin.Users_Activities')</h6>
          <button id="exportExcelBtn" class="btn btn-outline-success btn-sm">
            <i class="ti ti-file-export me-1"></i> @lang('admin.Export_to_Excel')
          </button>
        </div>
        <div class="table-responsive">
          <table id="activities-datatable" class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>@lang('admin.user')</th>
                <th>@lang('admin.browser')</th>
                <th>@lang('admin.device')</th>
                <th>@lang('admin.platform')</th>
                <th>@lang('admin.IP_Address')</th>
                <th>@lang('admin.location')</th>
                <th>@lang('admin.Login_Time')</th>
                <th>@lang('admin.Actions')</th>
              </tr>
            </thead>
            <tbody>
              @foreach($activities as $activity)
              <tr>
                <td>{{ $activity->user->first_name_en . ' ' . $activity->user->last_name_en }}</td>
                <td>{{ $activity->browser }}</td>
                <td>{{ $activity->device }}</td>
                <td>{{ $activity->platform }}</td>
                <td>{{ $activity->ip_address }}</td>
                <td>{{ $activity->location }}</td>
                <td>{{ $activity->activity_time->format('d M Y, H:i') }}</td>
                <td>
                  <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button 
                      type="submit" 
                      class="btn btn-sm btn-icon btn-label-danger" 
                      title="@lang('admin.Delete')"
                    >
                      <i class="ti ti-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script>
  // Initialize Date Picker
  flatpickr(".datepicker", {
    dateFormat: "Y-m-d", 
    allowInput: true, 
  });

  $(document).ready(function() {
    const lang = "{{ $l }}";

    const datatableLanguage = lang === 'ar' ? {
        "sEmptyTable": "@lang('admin.No_data_available_in_table')",
        "sLoadingRecords": "@lang('admin.Loading')...",
        "sProcessing": "@lang('admin.Loading')...",
        "sLengthMenu": "@lang('admin.Show') _MENU_ @lang('admin.Entries')",
        "sZeroRecords": "@lang('admin.No_records_found')",
        "sInfo": "@lang('admin.showing') _START_ @lang('admin.to') _END_ @lang('admin.of') _TOTAL_ @lang('admin.Entries')",
        "sInfoEmpty": "@lang('admin.showing') 0 @lang('admin.to') 0 @lang('admin.of') 0 @lang('admin.Entries')",
        "sInfoFiltered": "(@lang('admin.Filtered_from') _MAX_ @lang('admin.Total_Entries'))",
        "sInfoPostFix": "",
        "sSearch": "@lang('admin.Search'):",
        "sUrl": "",
        "oPaginate": {
            "sFirst": "@lang('admin.First')",
            "sPrevious": "@lang('admin.Previous')",
            "sNext": "@lang('admin.Next')",
            "sLast": "@lang('admin.Last')"
        },
        "oAria": {
            "sSortAscending": ": @lang('admin.Activate_to_sort_ascending')",
            "sSortDescending": ": @lang('admin.Activate_to_sort_descending')"
        }
    } : {};

    const table = $('#activities-datatable').DataTable({
      language: datatableLanguage,
      order: [[4, 'desc']],
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      searching: true,
      dom: 'Bfrtip',
      buttons: [],
    });

    $('#exportExcelBtn').on('click', function() {
      table.button('.buttons-excel').trigger();
    });
  });

  // Delete confirmation
  $(document).on("submit", ".delete-form", function (e) {
    e.preventDefault();

    const form = this;

    Swal.fire({
      title: "{{ __('admin.SureDelete') }}",
      text: "{{ __('admin.Recover') }}",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('admin.No') }}",
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit(); // Submit natively without re-triggering the event
      }
    });
  });
</script>
@stop