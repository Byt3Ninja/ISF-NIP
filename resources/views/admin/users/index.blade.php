@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.Users'))
@section('subTitle', __('admin.Users`s List'))
@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header row">
        @if (session()->has('msg'))
          <x-alert 
            type="success" 
            message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}"
          />
        @endif
      </div>
      <div class="content-body">
        <!-- users list start -->
        <section class="users-list-wrapper">
          
        </div>
        <div class="users-list-table">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">{{__('admin.Users`s List')}}</h4>
            </div>
            <div class="card-content">
              <div class="card-body">
                <div class="users-list-filter px-1">
                  <form>
                    <div class="row border rounded py-2 mb-2">
                      <div class="col-12 col-sm-3 col-lg-3">
                        <label class="mb-2" for="users-list-verified">{{ __('admin.HaveCompany') }}</label>
                        <fieldset class="form-group">
                          <select class="form-select select2" id="users-list-verified">
                            <option value="">{{ __('admin.All') }}</option>
                            <option value="{{ __('admin.Yes') }}">{{ __('admin.Yes') }}</option>
                            <option value="{{ __('admin.No') }}">{{ __('admin.No') }}</option>
                          </select>
                        </fieldset>
                      </div>
                      <div class="col-12 col-sm-3 col-lg-3">
                        <label class="mb-2" for="users-list-role">{{ __('admin.Role') }}</label>
                        <fieldset class="form-group">
                          <select class="form-select select2" id="users-list-role">
                            <option value="">{{ __('admin.All') }}</option>
                            @foreach (App\Role::get() as $role)
                              <option value="{{ $role->name }}">{{ __('admin.' . $role->name) }}</option>
                            @endforeach
                            <option value="Not Verified">{{ __('admin.NotVerified') }}</option>
                          </select>
                        </fieldset>
                      </div>
                      <div class="col-12 col-sm-3 col-lg-3">
                        <label class="mb-2" for="users-list-status">{{ __('admin.Gender') }}</label>
                        <fieldset class="form-group">
                          <select class="form-select select2" id="users-list-status">
                            <option value="">{{ __('admin.All') }}</option>
                            <option value="{{ __('admin.Male') }}">{{ __('admin.Male') }}</option>
                            <option value="{{ __('admin.Female') }}">{{ __('admin.Female') }}</option>
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
                <table id="users-list-datatable" class="table">
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
                      <tr>
                        <td><a href="{{ route('users.edit', ['user' => $user->id]) }}">{{ $user->id }}</a></td>
                        <td style="font-size: small;"><a href="{{ route('users.edit', ['user' => $user->id]) }}">
                            {{ $user->{'first_name_' . $l} }} {{ $user->{'last_name_' . $l} }}</a></td>
                        <td style="font-size: small;">{{ $user->email }}</td>
                        <td style="font-size: small;">{{ $user->phone }}</td>
                        <td style="font-size: small;">{{ __('admin.' . $user->gender) }}</td>
                        <td style="font-size: small;">
                          {{ $user->email_verified_at ? ($user->roles->first()?->name ?? 'No Role Assigned') : 'Not Verified' }}</td>
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
      </section>
      <!-- users list ends -->
    </div>
  </div>
  </div>
  <!-- END: Content-->

@stop
@section('scripts')
  <script src="/app-assets/js/scripts/pages/page-users.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
  <script src="/app-assets/js/scripts/modal/components-modal.js"></script>

  <script>
    $(document).on("click", "#deleteAction", function(e) {
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          type: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}!",
        })
        .then((result) => {
          if (result.value) {

            $.post(url, {
                '_method': 'DELETE',
                '_token': token
              },
              function(response) {
                Swal.fire(
                  '{{ __('admin.Deleted') }}!',
                  '{{ __('admin.FileDeleted') }}.',
                  'success'
                );

                location.reload();
              }
            ).fail(
              function(error) {
                console.log(error);
              }
            );
          } else {
            console.log('Canceled')
          }
        })
    });
  </script>
@stop
