@extends('layouts.admin')

@section('title', __('Users Permissions') )
@section('subTitle', __('Permissions list'))

@section('content')
<div class="">
  <div class="">
    @if(session()->has('msg'))
      <div class="row">
        <div class="col-12">
          <div class="alert alert-success alert-dismissible mb-2" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center">
              <i class="ti ti-check"></i>
              <span>
                {{ __('admin.'.session()->get('msg')) }}
                {{ Session::forget('msg') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <h5 class="card-title mb-3 mb-md-0">{{ $l == 'en' ? 'Permissions List' : 'قائمة الصلاحيات' }}</h5>
          <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
            <a href="{{route('permission-groups.index')}}" class="btn btn-label-primary"> 
              <i class="ti ti-list me-2"></i>
              {{ __('admin.permissionGroups')}} 
            </a>
            <a href="{{route('permissions.create')}}" class="btn btn-primary"> 
              <i class="ti ti-plus me-2"></i> 
              {{ __('admin.Add New')}} 
            </a>
          </div>
        </div>
      </div>

      <div class="card-body">
        <table class="table table-responsive permissions-table">
          <thead class="table-light">
            <tr>
              <th>{{ __('admin.ID')}}</th>
              <th>{{ __('admin.Title')}}</th>
              <th>{{ $l == 'en' ? 'Assigned To' : 'الموظفين المُستخدمين' }}</th>
              <th>{{ __('admin.Actions')}}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($_permissions as $key => $permission)
            <tr id="row_{{ $permission->id }}">
              <td> {{ $permission->id ?? '' }} </td>
              <td>
                @if( $permission->core )
                <i title="Core Permission" class="ti ti-circle-check text-success"></i>
                @endif
                {{ $permission->name ?? '' }}
              </td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  @foreach( $permission->roles as $role )
                    <a 
                      target="_blank"
                      href="{{ route('roles.edit', ['role' => $role->id]) }}"
                      class="btn btn-xs btn-label-primary d-flex align-items-center px-1">
                      <small>{{ $role->name ?? '' }}</small>
                      <i class="fas fa-times text-danger mx-2 strip-role-permission" data-role="{{ $role->id }}" data-permission="{{ $permission->id }}" role="button"></i>
                    </a>
                  @endforeach
                </div>
              </td>
              <td>
                <a href="{{route('permissions.edit', ['permission' => $permission->id])}}"
                  class="btn btn-icon rounded-circle btn-label-info">
                  <i class="ti ti-edit"></i>
                </a>
                {{-- <button id='deleteAction' data-id='{{$permission->id}}'
                  data-url="{{route('permissions.destroy', ['permission' => $permission->id])}}"
                  type="button" class="btn btn-label-danger"><i class="ti ti-trash text-danger">
                  </i></button> --}}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@stop

@php
  $stripFromRoles_url = URL::to('dashboard/permissions/strip-from-roles');
@endphp

@section('scripts')
  <script>
    $(document).ready(function() {
      const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

      // Initialize DataTable
      var table = $('.permissions-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
        language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-2"l><"col-sm-12 col-md-7 filters"><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
          {
            extend: 'excel',
            title: '{{ $l == "en" ? "Permissions List" : "قائمة الأذونات" }}',
            exportOptions: {
              columns: [0, 1, 2],
            }
          },
          {
            extend: 'csv',
            title: '{{ $l == "en" ? "Permissions List" : "قائمة الأذونات" }}',
            exportOptions: {
              columns: [0, 1, 2],
            }
          },
        ],
      });

      // Handle export buttons
      $('#exportCsv').on('click', function() {
        table.button('.buttons-csv').trigger();
      });

      $('#exportExcel').on('click', function() {
        table.button('.buttons-excel').trigger();
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
                table.row('#row_' + id).remove().draw();

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

      $(document).on("click", ".strip-role-permission", function (e) {
        e.preventDefault();
        var el = $(this);
        const token = '{{ csrf_token() }}';
        const url = "{{ $stripFromRoles_url }}";
        const permissionId = $(this).data('permission');
        const roleId = $(this).data('role');

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
            $.post(url, {'_token': token, 'permission': permissionId, 'role': roleId},
              function(response) {
                if( response.status )
                {
                  var load_roles = '';
                  $.each(response.data, function(index, item) {
                    load_roles += `
                      <a 
                        target="_blank"
                        href="${item.url}"
                        class="btn btn-xs btn-label-primary d-flex align-items-center px-1">
                        <small>${item.name}</small>
                        <i class="fas fa-times text-danger mx-2 strip-role-permission" data-role="${item.role_id}" data-permission="${item.permission_id}" role="button"></i>
                      </a>
                    `;
                  })
                  el.parent().parent().html( load_roles );
                  releaseSuccessAlert({
                    title: "{{ __('admin.Deleted') }}!",
                    message: "{{ __('admin.DeletedSuccessfully') }}.",
                  });
                }
                else
                {
                  releaseFailureAlert({
                    title: "{{ __('admin.Error') }}!",
                    message: response?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
                  });
                }
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