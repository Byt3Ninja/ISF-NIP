@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('Users Permissions') )
@section('subTitle', __('Permissions list'))
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    
        <div class="content-header mb-4"> <h4 class="content-card-title">{{ $l == 'en' ? 'Permissions List' : 'قائمة الصلاحيات' }}</h4> </div>
        <div class="content-body">
          <section id="basic-datatable">
            <div class="row">
              <div class="col-12">
                <div class="card">
                    <div class="card-header mb-4">
                        <a href="{{route('permissions.create')}}" class="mx-2 btn btn-primary glow"> <i class="ti ti-plus me-1"></i> {{ __('admin.Add New')}} </a>
                        <a href="{{route('permission-groups.index')}}" class="mx-2 btn btn-dark glow"> <i class="ti ti-list me-1"></i> {{ __('admin.permissionGroups')}} </a>
                    </div>
                            <div class="card-content">
                                <div class="card-body card-dashboard">
                                    @if(session()->has('msg'))
                                        <div class="row">
                                            <div class="col-md-12">
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
                                    <div class="table-responsive">
                                        <table class="table zero-configuration">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('ID')}}</th>
                                                    <th>{{ __('Title')}}</th>
                                                    <th>{{ $l == 'en' ? 'Assigned To' : 'الموظفين المُستخدمين'}}</th>
                                                    <th>{{ __('Edit/ Delete')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($_permissions as $key => $permission)
                                                    <tr>
                                                        <td> {{ $permission->id ?? '' }} </td>
                                                        <td>
                                                            @if( $permission->core )
                                                            <i title="Core Permission" class="fas fa-fw fa-check-circle text-success"></i>
                                                            @endif
                                                            {{ $permission->name  ?? '' }}
                                                        </td>
                                                        <td>
                                                            @foreach( $permission->roles as $role )
                                                                <a target="_blank" href="{{ route('roles.edit', ['role' => $role->id]) }}" class="mx-2 btn btn-xs btn-label-primary">{{ $role->name ?? '' }} </a/>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            <a href="{{route('permissions.edit', ['permission' => $permission->id])}}" class="btn btn-label-info"><i class="ti ti-edit text-info"> </i></a>
                                                            {{--  <button id='deleteAction' data-id='{{$permission->id}}' data-url="{{route('permissions.destroy', ['permission' => $permission->id])}}" type="button" class="btn btn-label-danger"><i class="ti ti-trash text-danger"> </i></button>  --}}
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
                </div>
            </div>
        </section>
    </div>
</div>
@stop
@section('scripts')
    @if($l=='ar')
        <script type="text/javascript">
            $(document).ready(function() {
                $('.zero-configuration').DataTable({
                    "language": {
                        "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                        "sLoadingRecords": "جارٍ التحميل...",
                        "sProcessing":   "جارٍ التحميل...",
                        "sLengthMenu":   "أظهر _MENU_ مدخلات",
                        "sZeroRecords":  "لم يعثر على أية سجلات",
                        "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                        "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل",
                        "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                        "sInfoPostFix":  "",
                        "sSearch":       "ابحث:",
                        "sUrl":          "",
                        "oPaginate": {
                            "sFirst":    "الأول",
                            "sPrevious": "السابق",
                            "sNext":     "التالي",
                            "sLast":     "الأخير"
                        },
                        "oAria": {
                            "sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً",
                            "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
                        }
                    }
                } );
            });
        </script>
    @endif
    <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
    <script src="/app-assets/js/scripts/modal/components-modal.js"></script>
    <script>
        $(document).on("click", "#deleteAction", function (e) {
            var url = $(this).data('url');
            var token = '{{ csrf_token() }}';
            Swal.fire({
                title: "{{__('admin.SureDelete')}}",
                text: "{{__('admin.Recover')}}",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "{{__('admin.DYes')}}",
                cancelButtonText: "{{__('admin.No')}}!",
            })
                .then((result) => {
                    if (result.value) {

                        $.post(url, {'_method':'DELETE' , '_token':token},
                            function(response){
                                Swal.fire(
                                    '{{__('admin.Deleted')}}!',
                                    '{{__('admin.FileDeleted')}}.',
                                    'success'
                                );

                                location.reload();
                            }
                        ).fail(
                            function(error) {
                                console.log(error);
                            }
                        );
                    }else{
                        console.log('Canceled')
                    }
                })
        });
    </script>
@stop
