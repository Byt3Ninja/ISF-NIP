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
          <h4 class="card-title">{{__('admin.Users`s List')}}</h4>
        </div>
        <div class="card-content">
          <div class="card-body">
            <div class="users-list-filter px-1">
              <form method="GET" action="{{ url()->current() }}">
                <div class="row border rounded py-2 mb-2">
                  <div class="col-12 col-sm-3 col-lg-3">
                    <label class="mb-2" for="users-list-verified">{{ __('admin.HaveCompany') }}</label>
                    <fieldset class="form-group">
                    <select class="form-select select2" id="users-list-verified" name="haveCompany">
                      <option value=" " {{ request('haveCompany') === null ? 'selected' : '' }}>{{ __('admin.All') }}</option>
                      <option value="1" {{ request('haveCompany') === '1' ? 'selected' : '' }}>{{ __('admin.Yes') }}</option>
                      <option value="0" {{ request('haveCompany') === '0' ? 'selected' : '' }}>{{ __('admin.No') }}</option>
                    </select>

                    </fieldset>
                  </div>
                  <div class="col-12 col-sm-3 col-lg-3">
                    <label class="mb-2" for="users-list-role">{{ __('admin.Role') }}</label>
                    <fieldset class="form-group">
                      <select class="form-select select2" id="users-list-role" name="role">
                        <option value=" "  >{{ __('admin.All') }}</option>
                        @foreach (App\Role::get() as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ __('admin.' . $role->name) }}</option>
                        @endforeach
                        <option value="Not Verified">{{ __('admin.NotVerified') }}</option>
                      </select>
                    </fieldset>
                  </div>
                  <div class="col-12 col-sm-3 col-lg-3">
                    <label class="mb-2" for="users-list-status">{{ __('admin.Gender') }}</label>
                    <fieldset class="form-group">
                    <select class="form-select select2" id="users-list-status" name="gender">
                      <option value=" ">{{ __('admin.All') }}</option>
                      <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>{{ __('admin.Male') }}</option>
                      <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>{{ __('admin.Female') }}</option>
                    </select>
                    </fieldset>
                  </div>

              </form>
              <div class="col-12 col-sm-3 col-lg-2 offset-sm-2 offset-lg-1 d-flex align-items-end">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                  <i class="ti ti-plus me-1"></i>
                  {{ __('admin.Add New') }}
                </a>
              </div>
            </div>
            <!-- datatable start -->
            <div class="table-responsive mt-2">
              <table id="users-list-datatable" class="table datatables-ajax">
                <thead>
                  <tr class="table-light">
                    <th>#</th>
                    <th>{{ __('admin.Name') }}</th>
                    <th>{{ __('admin.Email') }}</th>
                    <th>{{ __('admin.Phone') }}</th>
                    <th>{{ __('admin.Gender') }}</th>
                    <th>{{ __('admin.Role') }}</th>
                    <th>{{ __('admin.HaveCompany') }}</th>
                    <th>{{ __('admin.Date') }}</th>
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
                    <td><a href="{{ route('users.edit', ['user' => $user->id]) }}">{{ $user->id }}</a></td>
                    <td style="font-size: small;"><a href="{{ route('users.edit', ['user' => $user->id]) }}">
                        {{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}</a></td>
                    <td style="font-size: small;">{{ $user->email }}</td>
                    <td style="font-size: small;">{{ $user->phone }}</td>
                    <td style="font-size: small;">{{ __('admin.' . $user->gender) }}</td>
                    <td style="font-size: small;">
                      {{ $user->email_verified_at ? ($user->roles->first()?->name ?? 'No Role Assigned') : 'Not
                      Verified' }}</td>
                    <td style="font-size: small;">{{ $user->company ? __('admin.Yes') : __('admin.No') }}</td>
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

@section('scripts')
<script>
  $(document).ready(function() {
    // Initialize DataTable
    $('#users-list-datatable').DataTable({
      responsive: true,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
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
      dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 10,
      lengthMenu: [10, 25, 50, 75, 100],
      order: [[0, 'desc']],
      columnDefs: [
        {
          targets: [8], // Actions column
          orderable: false,
          searchable: false
        }
      ]
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
                // Redirect to the index page
                window.location.href = "{{ route('users.index') }}";
              });
              // Remove the row from DataTable
              $('#users-list-datatable').DataTable().row('#row_' + id).remove().draw();
            }
          ).fail(function(error) { 
            console.log(error);
            Swal.fire({
              icon: 'error',
              title: "{{ __('admin.Error') }}!",
              text: "{{ __('admin.SomethingWentWrong') }}",
            });
          });
        }
      });
    });
  });



  
</script>
<script>

$(document).ready(function () {
  $('.form-select').on('change', function () {
    let params = new URLSearchParams(window.location.search);

    $('.form-select').each(function () {
      let key = $(this).attr('name');
      let value = $(this).val();

      if (key) {
        if (value && value !== '') {
          params.set(key, value);
        } else {
          params.delete(key);
        }
      }
    });

    let newUrl = window.location.pathname;
    let queryString = params.toString();
    if (queryString) newUrl += '?' + queryString;

    window.location.href = newUrl;
  });
});

</script>
@stop