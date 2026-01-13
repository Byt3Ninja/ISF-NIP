@extends('layouts.admin')

@section('title', __('admin.Users'))
@section('subTitle', __('admin.Users`s List'))

@section('content')
<div class="">
  <div class="">
    @if (session()->has('msg'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
    @endif
    @if (session()->has('message2'))
      <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
    @endif

    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <h5 class="card-title mb-3 mb-md-0">{{__('admin.Users`s List')}}</h5>
          <div class="d-flex align-items-center gap-3">
            <div class="dropdown me-2">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
              <i class="ti ti-plus me-2"></i>
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
                  <th>{{ __('admin.Actions') }}</th>
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
                  <td>
                    <div class="d-flex align-items-center gap-1">
                      @if( $_user->ableTo('users.login_as', $permissions) )
                        <a href="{{ route('users.login_as', ['id' => $user->id]) }}" title="{{ __('admin.Login as User') }}" class="btn btn-icon btn-label-dark btn-sm"> <i class="fa-solid fa-user-secret fs-5"></i> </a>
                      @endif
                      <button data-bs-toggle="modal" data-bs-target="#MessageModal"
                        class="btn btn-icon btn-label-primary btn-sm">
                        <i class="ti ti-message"></i>
                      </button>   
                      @if( $_user->ableTo('users.destroy', $permissions) )
                        <button id='deleteAction' data-id='{{ $user->id }}'
                          data-url="{{ route('users.destroy', ['user' => $user->id]) }}" type="button"
                          class="btn btn-icon btn-label-danger btn-sm">
                          <i class="ti ti-trash"> </i>
                        </button>
                      @endif
                    </div>
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

  <div class="modal fade text-left" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">{{ __('messages.SendMessageTo') }} {{$user->{'first_name_'.$l} }}
            {{ $user->{'last_name_'.$l} }}.</h4>
        </div>
        <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
          {!! csrf_field() !!}
          <input type="hidden" name="sender" value="{{Auth::user()->id}}">
          <input type="hidden" name="receiver" value="{{$user->id}}">
          <div class="modal-body">
            <div class="form-group mb-3">
              <label class="form-label">{{ __('messages.Subject') }}:</label>
              <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">{{ __('messages.Message') }}: </label>
              <textarea class="form-control" id="message" name='message' rows="8"
                placeholder="{{__('messages.Write your message')}}" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              {{__('messages.Close')}}
            </button>
            <button type="submit" class="btn btn-primary ml-1">
              <i class="ti ti-send me-2"></i>
              {{__('messages.Send')}}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  $(document).ready(function() {
    const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

    // Initialize DataTable
    var usersTable = $('#users-list-datatable').DataTable({
      responsive: true,
      pageLength: 10,
      language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
      order: [[7, 'desc']],
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