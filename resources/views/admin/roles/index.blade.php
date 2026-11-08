@extends('layouts.admin')
@section('title', __('Users Roles'))
@section('subTitle', __('Roles list'))

{{-- @section('style')
<link type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css" rel="stylesheet">
@stop --}}

@section('content')
<div class="">
  <div class="">
    <div class="row d-flex justify-content-between align-items-center mb-4">
      <div class="col-12 col-md-9">
        <h4 class="mb-1">{{ __('admin.Roles_List') }}</h4>
        <p class="mb-6">
          {{ __('admin.Roles_Description_1') }} <br />
          {{ __('admin.Roles_Description_2') }}
        </p>
      </div>
      <div class="col-12 col-md-3 text-start text-md-end mb-6 mb-md-0">
        @if( $_user->ableTo('permissions.index', $permissions) )
          <a href="{{ route('permissions.index') }}" class="btn btn-primary">
            <i class="ti ti-list me-1"></i>
            {{ __('admin.Permissions') }}
          </a>
        @endif
      </div>
    </div>
    
    <!-- Alert -->
    @if (session()->has('msg'))
      <div class="row">
        <div class="col-md-12">
          <div class="alert alert-success alert-dismissible mb-2" role="alert">
            {{ __('admin.' . session()->get('msg')) }}
            {{ Session::forget('msg') }}
            <button class="close" data-bs-dismiss="alert" type="button" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
      </div>
    @endif
    <!-- Alert -->

    <!-- Roles List -->
    <div class="row g-6">
      <!-- Add New Role Card -->
      @if($_user->ableToAny(['roles.create', 'roles.store'], $permissions))
      <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card h-100">
          <div class="row h-100">
            <div class="col-sm-5">
              <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4">
                <img
                  src="/vuexy/assets/img/illustrations/add-new-roles.png"
                  class="img-fluid mt-sm-4 mt-md-0"
                  alt="add-new-roles"
                  width="83" 
                />
              </div>
            </div>
            <div class="col-sm-7">
              <div class="card-body text-sm-end text-center ps-sm-0">
                <button
                  onclick="window.location.href='{{ route('roles.create') }}'"
                  class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role text-capitalize"
                  {{-- data-bs-target="#addRoleModal" --}}
                  {{-- data-bs-toggle="modal" --}}
                >
                  {{ __('admin.Add_New_Role') }} 
                </button>
                <p class="mb-0">
                  {{ __('admin.Add_New_Role') }} <br />
                  {{ __('admin.If_Role_Not_Exist') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
      <!--/ Add New Role Card -->
      @foreach ($roles as $key => $role)
        @php
          $roleUsers = $role->users;
          $totalUsers = $roleUsers->count();
        @endphp
        <!-- Role Card -->
        <div class="col-xl-4 col-lg-6 col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-normal mb-0 text-body">
                  {{ __('admin.totalUsers', ['num' => $totalUsers]) }}
                </h6>
                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                  @foreach ($roleUsers as $key => $roleUser)
                    @if ($key < 3)
                      <li
                        data-bs-toggle="tooltip"
                        data-popup="tooltip-custom"
                        data-bs-placement="top"
                        title="{{ $roleUser->name }}"
                        class="avatar pull-up"
                      >
                        <img
                          class="rounded-circle"
                          src="{{ $roleUser->image_url ? $roleUser->image_url : '/images/user-placeholder.jpeg'}}"
                          onerror="this.src='/images/user-placeholder.jpeg'"
                          alt="Avatar"
                        />
                      </li>
                    @endif
                  @endforeach
                  @if ($totalUsers > 3)
                    <li class="avatar">
                      <span
                        class="avatar-initial rounded-circle pull-up"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        title="{{ $totalUsers - 3 }} more"
                      >
                        +{{ $totalUsers - 3 }}
                      </span>
                    </li>
                  @endif
                </ul>
              </div>
              <div class="d-flex justify-content-between align-items-end">
                <div class="role-heading">
                  @php
                    // Translated like the Users page role filter; custom roles without a key keep their name.
                    $roleLabel = __('admin.' . $role->name);
                    $roleLabel = is_string($roleLabel) && $roleLabel !== 'admin.' . $role->name ? trim($roleLabel) : $role->name;
                  @endphp
                  <h5 class="mb-1">{{ $roleLabel }}</h5>
                  @if($_user->ableToAny(['roles.edit', 'roles.update'], $permissions))
                  <a
                    class="role-edit-modal"
                    href="{{ route('roles.edit', ['role' => $role->id]) }}"
                    {{-- href="javascript:;" --}}
                    {{-- data-bs-toggle="modal" --}}
                    {{-- data-bs-target="#addRoleModal" --}}
                  >
                    <span>{{ __('admin.Edit_Role') }}</span>
                  </a>
                  @endif
                </div>
                @if (!in_array($role->id, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]))
                  <button 
                    class="btn btn-xs btn-icon btn-label-danger" 
                    id='deleteAction' 
                    data-id='{{ $role->id }}'
                    data-url="{{ route('roles.destroy', ['role' => $role->id]) }}" 
                    type="button"
                  >
                    <i class="ti ti-trash ti-16px"></i>
                  </button>
                @endif
              </div>
            </div>
          </div>
        </div>
        <!--/ Role Card -->
      @endforeach
    </div>
    <!--/ Roles List -->

    <!--/ Permessions -->
    {{-- <div style="max-height: 100px; overflow-y: auto;">
      @foreach ($role->permissions as $key => $item)
      <a href="/dashboard/permissions/{{ $item->id }}/edit">
        <span class="btn rounded-pill btn-label-secondary m-1">{{
          $item->name }}</span>
      </a>
      @endforeach
    </div> --}}
    <!--/ Permessions -->
  </div>
</div>
@stop

@section('scripts')
@if ($l == 'ar')
<script type="text/javascript">
  $(document).ready(function() {
    $('.zero-configuration').DataTable({
      "language": {
        "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
        "sLoadingRecords": "جارٍ التحميل...",
        "sProcessing": "جارٍ التحميل...",
        "sLengthMenu": "أظهر _MENU_ مدخلات",
        "sZeroRecords": "لم يعثر على أية سجلات",
        "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
        "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
        "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
        "sInfoPostFix": "",
        "sSearch": "ابحث:",
        "sUrl": "",
        "oPaginate": {
          "sFirst": "الأول",
          "sPrevious": "السابق",
          "sNext": "التالي",
          "sLast": "الأخير"
        },
        "oAria": {
          "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
          "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
        }
      }
    });
  });
</script>
@endif
{{-- <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js">
</script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js">
</script>
<script src="/app-assets/js/scripts/datatables/datatable.js"></script>
<script src="/app-assets/js/scripts/modal/components-modal.js"></script> --}}
<script>
  $(document).on("click", "#deleteAction", function(e) {
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      Swal
        .fire({
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
                releaseAlert('success', {
                  title: '{{ __('admin.Deleted') }}!',
                  message: '{{ __('admin.FileDeleted') }}.'
                });

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
        });
    });
</script>
@stop