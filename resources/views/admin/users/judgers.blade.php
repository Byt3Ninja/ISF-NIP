@extends('layouts.admin')

@section('title', __('admin.Users'))
@section('subTitle', __('admin.Users`s List'))

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
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
        <div class="card-header border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-3 mb-md-0">{{__('admin.Judge`s List')}}</h5>
            <div class="d-flex align-items-center gap-3">
              <div class="dropdown">
                <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                  <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
                </ul>
              </div>
              {{-- <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                {{ __('admin.Add New') }}
              </a> --}}
            </div>
          </div>
        </div>
        <div class="card-content">
          <div class="card-body">
            <!-- Datatable filter configuration fields in a row -->
           
            <!-- /Datatable filter configuration fields -->

            <!-- datatable start -->
            <div class="table-responsive">
              <table id="users-list-datatable" class="table datatables-ajax">
                <thead>
                  <tr class="table-light">
                    <th>#</th>
                    <th>{{ __('admin.Name') }}</th>
                    <th>{{ __('admin.Email') }}</th>
                    <th>{{ __('admin.Phone') }}</th>
                    <th>{{ __('admin.Gender') }}</th>
                    {{-- <th>{{ __('admin.Role') }}</th>
                    <th>{{ __('admin.HaveCompany') }}</th> --}}
                    <th>{{ __('admin.Join_Date') }}</th>
                    <th>
                      @if( $_user->ableTo('users.destroy', $permissions) )
                      {{ __('admin.Delete') }}
                      @endif
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($judgers as $user)
                  <tr id="row_{{ $user->id }}">
                    <td>
                      <a href="{{ route('users.edit', ['user' => $user->id]) }}">{{ $user->id }}</a>
                    </td>
                    <td style="font-size: small;">
                      <a href="{{ route('users.edit', ['user' => $user->id]) }}">
                        {{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}
                      </a>
                    </td>
                    <td style="font-size: small;">{{ $user->email }}</td>
                    <td style="font-size: small;">{{ $user->phone }}</td>
                    <td style="font-size: small;" data-search="{{ $user->gender }}" data-sort="{{ $user->gender }}">
                      {{ trim($user->gender) ? __('admin.' . $user->gender) : '-' }}
                    </td>
                    {{-- <td style="font-size: small;" data-search="{{ $user->email_verified_at ? ($user->roles->first()?->name ?? 'No Role Assigned') : 'Not Verified' }}">
                      {{ $user->email_verified_at ? ($user->roles->first()?->name ?? 'No Role Assigned') : 'Not Verified' }}
                    </td>
                    <td style="font-size: small;" data-search="{{ $user->company ? 'Yes' : 'No' }}">
                      {{ $user->company ? __('admin.Yes') : __('admin.No') }}
                    </td> --}}
                    <td style="font-size: small;">{{ $user->created_at }}</td>
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
    const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

    // Initialize DataTable
    var usersTable = $('#users-list-datatable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
      language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      initComplete: function() {
        $('.filters').append( $('#datatable-filters') );
      },
      columnDefs: [
        {
          targets: [6], // Actions column
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
              // Remove the row from DataTable
              usersTable.row('#row_' + id).remove().draw();

              releaseSuccessAlert({
                title: "{{ __('admin.Deleted') }}!",
                message: "{{ __('admin.DeletedSuccessfully') }}.",
              });
            }
          ).fail(function(error) { 
            releaseFailureAlert({
							title: "{{ __('admin.Error') }}!",
							message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
						});
          });
        }
      });
    });
  });
</script>
@stop