@extends('layouts.admin')

@section('title', __('admin.Users'))
@section('subTitle', __('admin.Users`s List'))

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-header row">
      @if (session()->has('msg'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
      @endif
      @if (session()->has('message2'))
        <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
      @endif
    </div>

    <div class="users-list-table">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
            <h5 class="card-title mb-0">{{__('admin.Users`s List')}}</h5>
            <div class="d-flex align-items-center">
              <div class="dropdown me-2">
                <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="ti ti-file-export me-1"></i> {{ __('admin.Export') }}
                </button>
                <div class="dropdown-menu" aria-labelledby="exportDropdown">
                  <a class="dropdown-item" href="javascript:void(0);" id="exportCsv">CSV</a>
                  <a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a>
                </div>
              </div>
              <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                {{ __('admin.Add New') }}
              </a>
            </div>
          </div>
        </div>
        <div class="card-content">
          <div class="card-body">
            <!-- Datatable filter configuration fields in a row -->
            <div class="row" id="datatable-filters">
              <div class="col-12 col-md-4 mb-2 mb-md-0">
                <select class="form-select form-select-sm dt-filter" id="filter-haveCompany" data-column="6">
                  <option value="" selected>{{ __('admin.HaveCompany') }}</option>
                  <option value="Yes">{{ __('admin.Yes') }}</option>
                  <option value="No">{{ __('admin.No') }}</option>
                </select>
              </div>
              <div class="col-12 col-md-4 mb-2 mb-md-0">
                <select class="form-select form-select-sm dt-filter" id="filter-role" data-column="5">
                  <option value="" selected>{{ __('admin.All_Roles') }}</option>
                  @foreach (App\Role::get() as $role)
                  <option value="{{ $role->name }}">{{ __('admin.' . $role->name) }}</option>
                  @endforeach
                  <option value="Not Verified">{{ __('admin.NotVerified') }}</option>
                </select>
              </div>
              <div class="col-12 col-md-4 mb-2 mb-md-0">
                <select class="form-select form-select-sm dt-filter" id="filter-gender" data-column="4">
                  <option value="" selected>{{ __('admin.All_Genders') }}</option>
                  <option value="Male">{{ __('admin.Male') }}</option>
                  <option value="Female">{{ __('admin.Female') }}</option>
                </select>
              </div>
            </div>
            <!-- /Datatable filter configuration fields -->

            <!-- datatable start -->
            <div class="table-responsive mt-2">
              <table id="users-list-datatable" class="table datatables-ajax">
                <thead>
                  <tr class="table-light">
                    <th>ID</th>
                    <th>{{ __('admin.Name') }}</th>
                    <th>{{ __('admin.Email') }}</th>
                    <th>{{ __('admin.Phone') }}</th>
                    <th>{{ __('admin.Gender') }}</th>
                    <th>{{ __('admin.Role') }}</th>
                    <th>{{ __('admin.HaveCompany') }}</th>
                    <th>{{ __('admin.CreatedAt') }}</th>
                    <th>
                      @if( $_user->ableTo('users.destroy', $permissions) )
                      {{ __('admin.Delete') }}
                      @endif
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($users as $user)
                  <tr id="row_{{ $user->id }}">
                    <td>
                      <a href="{{ route('users.edit', ['user' => $user->id]) }}">{{ $user->id }}</a>
                    </td>
                    <td>
                      <a href="{{ route('users.edit', ['user' => $user->id]) }}">
                        <small>{{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}</small>
                      </a>
                    </td>
                    <td><small>{{ $user->email }}</small></td>
                    <td><small>{{ $user->phone }}</small></td>
                    <td data-search="{{ $user->gender }}" data-sort="{{ $user->gender }}">
                      <small>{{ trim($user->gender) ? __('admin.' . $user->gender) : '-' }}</small>
                    </td>
                    <td data-search="{{ $user->email_verified_at ? ($user->roles->first()?->name ?? 'No Role Assigned') : 'Not Verified' }}">
                      <small>{{ $user->email_verified_at ? ($user->roles->first()?->name ?? 'No Role Assigned') : 'Not Verified' }}</small>
                    </td>
                    <td data-search="{{ $user->company ? 'Yes' : 'No' }}">
                      <small>{{ $user->company ? __('admin.Yes') : __('admin.No') }}</small>
                    </td>
                    <td><small class="text-nowrap">{{ $user->created_at }}</small></td>
                    <td style="padding: 0px;font-size: small;">
                      @if( $_user->ableTo('users.destroy', $permissions) )
                      <button id='deleteAction' data-id='{{ $user->id }}'
                        data-url="{{ route('users.destroy', ['user' => $user->id]) }}" type="button"
                        class="btn btn-label-danger btn-sm">
                        <i class="ti ti-trash text-danger"> </i>
                      </button>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <!-- datatable ends -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- END: Content-->
@stop

@section('pagescripts')
<script>
  $(document).ready(function() {
    var usersTable = $('#users-list-datatable').DataTable({
      responsive: true,
      pageLength: 10,
      language: {
        @if($l == 'ar')
        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
        @else
        "search": "{{ __('admin.Search') }}:",
        "lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
        "info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
        "infoEmpty": "{{ __('admin.no_entries_available') }}",
        "infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
        "paginate": {
          "first": "{{ __('admin.First') }}",
          "last": "{{ __('admin.Last') }}",
          "next": "{{ __('admin.Next') }}",
          "previous": "{{ __('admin.Previous') }}"
        },
        "emptyTable": "{{ __('admin.No_data_available_in_table') }}",
        "zeroRecords": "{{ __('admin.No_matching_records_found') }}"
        @endif
      },
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      initComplete: function() {
        $('.filters').append( $('#datatable-filters') );
      },
      columnDefs: [
        {
          targets: [8], // Actions column
          orderable: false,
          searchable: false
        }
      ],
      buttons: [
        {
          extend: 'csv',
          text: 'CSV',
          className: 'd-none', // Hide default button, trigger via custom dropdown
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7] // Export these columns
          }
        },
        {
          extend: 'excel',
          text: 'Excel',
          className: 'd-none', // Hide default button, trigger via custom dropdown
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5, 6, 7] // Export these columns
          }
        }
      ]
    });

    // Custom filtering for DataTable
    $('.dt-filter').on('change', function() {
      var columnIdx = $(this).data('column');
      var value = $(this).val();
      
      if (columnIdx == 4) { // Gender column - use exact match
        if (value === '') {
          usersTable.column(columnIdx).search('').draw();
        } else {
          // Use regex for exact match
          usersTable.column(columnIdx).search('^' + $.fn.dataTable.util.escapeRegex(value) + '$', true, false).draw();
        }
      } else {
        // Regular search for other columns
        usersTable.column(columnIdx).search(value).draw();
      }
    });

    // Handle export buttons
    $('#exportCsv').on('click', function() {
      usersTable.button('.buttons-csv').trigger();
    });

    $('#exportExcel').on('click', function() {
      usersTable.button('.buttons-excel').trigger();
    });

    // Handle delete action
    $(document).on("click", "#deleteAction", function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      var id = $(this).data('id');
      var token = '{{ csrf_token() }}';
      
      Swal.fire({
        title: "{{__('admin.SureDelete')}}",
        text: "{{__('admin.Recover')}}",
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
      })
      .then((result) => {
        if (result.value) {
          $.post(url, {'_method': 'DELETE' , '_token': token},
            function(response) {
              Swal.fire({
                icon: 'success',
                title: "{{ __('admin.Deleted') }}!",
                html: "{{ __('admin.DeletedSuccessfully') }}.",
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: "{{ __('admin.OK') }}",
                customClass: {
                  confirmButton: 'btn btn-primary',
                },
                buttonsStyling: false,
              }).then(function() {
                // Remove the row from DataTable
                usersTable.row('#row_' + id).remove().draw();
                // To avoid full page reload, if needed. If a full refresh is desired, uncomment the below.
                // window.location.href = "{{ route('users.index') }}";
              });
            }
          ).fail(function(error) { 
            releaseFailureAlert({
							title: "{{ __('admin.Error') }}!",
							text: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
						});
          });
        }
      });
    });
  });
</script>
@stop