@extends('layouts.admin')

@section('title', __('admin.Activities'))

@section('vendorstyle')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" />
@stop

@section('content')
    <div class="container mx-auto p-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('admin.Close')"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('admin.Close')"></button>
            </div>
        @endif

        <h5 class="mb-4">@lang('admin.Activities_Dashboard')</h5>

        <!-- Dashboard Statistics - Totals -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6>@lang('admin.Total_Logins_Today')</h6>
                        <p class="text-center display-4">{{ $totalLoginsToday }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6>@lang('admin.Total_Logins_Week')</h6>
                        <p class="text-center display-4">{{ $totalLoginsWeek }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6>@lang('admin.Total_Logins_Month')</h6>
                        <p class="text-center display-4">{{ $totalLoginsMonth }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Devices -->
        <div class="card mb-4">
            <div class="card-body">
                <h6>@lang('admin.Top_Devices')</h6>
                @if ($topDevices->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($topDevices as $device)
                            <li class="list-group-item">
                                {{ $device->device }}: {{ $device->count }} @lang('admin.Logins')
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>@lang('admin.No_data_available')</p>
                @endif
            </div>
        </div>

        <!-- Top Browsers -->
        <div class="card mb-4">
            <div class="card-body">
                <h6>@lang('admin.Top_Browsers')</h6>
                @if ($topBrowsers->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($topBrowsers as $browser)
                            <li class="list-group-item">
                                {{ $browser->browser }}: {{ $browser->count }} @lang('admin.Logins')
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>@lang('admin.No_data_available')</p>
                @endif
            </div>
        </div>

        <!-- Logins by Country -->
        <div class="card mb-4">
            <div class="card-body">
                <h6>@lang('admin.Logins_by_Country')</h6>
                @if ($loginsByCountry->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($loginsByCountry as $country)
                            <li class="list-group-item">
                                {{ $country->location }}: {{ $country->count }} @lang('admin.Logins')
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>@lang('admin.No_data_available')</p>
                @endif
            </div>
        </div>

        <!-- Peak Login Hours -->
        <div class="card mb-4">
            <div class="card-body">
                <h6>@lang('admin.Peak_Login_Hours')</h6>
                @if ($peakHours->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($peakHours as $hour)
                            <li class="list-group-item">
                                {{ sprintf('%02d:00', $hour->hour) }}: {{ $hour->count }} @lang('admin.Logins')
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>@lang('admin.No_data_available')</p>
                @endif
            </div>
        </div>

        <!-- Suspicious Logins -->
        <div class="card mb-4">
            <div class="card-body">
                <h6>@lang('admin.Suspicious_Logins')</h6>
                @if ($suspiciousLogins->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($suspiciousLogins as $login)
                            <li class="list-group-item">
                                @lang('admin.User'): {{ $login->user->first_name_en ?? 'Unknown' }} {{ $login->user->last_name_en ?? 'Unknown' }} 
                                (@lang('admin.Logins'): {{ $login->login_count }}, @lang('admin.Locations'): {{ $login->locations ?? 'N/A' }})
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>@lang('admin.No_suspicious_logins')</p>
                @endif
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-2">
                    <input type="text" name="user" class="form-control" placeholder="@lang('admin.user')" value="{{ request('user') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="device" class="form-control" placeholder="@lang('admin.device')" value="{{ request('device') }}">
                </div>
                <div class="col-md-2">
                    <input type="text" name="location" class="form-control" placeholder="@lang('admin.location')" value="{{ request('location') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">@lang('admin.Filter')</button>
                </div>
            </div>
        </form>

        <table id="activities-datatable" class="table table-striped table-hover">
            <thead>
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
                            <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="delete-form" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="@lang('admin.Delete')">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            const lang = "{{ $l }}";

            const datatableLanguage = lang === 'ar' ? {
                "sEmptyTable": "@lang('admin.No_data_available_in_table')",
                "sLoadingRecords": "@lang('admin.Loading')...",
                "sProcessing": "@lang('admin.Loading')...",
                "sLengthMenu": "@lang('admin.Show') _MENU_ @lang('admin.Entries')",
                "sZeroRecords": "@lang('admin.No_records_found')",
                "sInfo": "@lang('admin.Showing') _START_ @lang('admin.to') _END_ @lang('admin.of') _TOTAL_ @lang('admin.Entries')",
                "sInfoEmpty": "@lang('admin.Showing') 0 @lang('admin.to') 0 @lang('admin.of') 0 @lang('admin.Entries')",
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
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '@lang('admin.Export_to_Excel')',
                        title: 'Activities_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });

            $('#exportExcelBtn').on('click', function() {
                table.button('.buttons-excel').trigger();
            });
        });
    </script>
@stop