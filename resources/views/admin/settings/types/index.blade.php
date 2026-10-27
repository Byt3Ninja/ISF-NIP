@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.Types') )
@section('subTitle', __('admin.Type`s List'))
@section('content')
<!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
        @if( $_user->ableToAny(['types.create', 'types.store'], $permissions) )
        <div class="invoice-create-btn mb-4">
            <a href="{{route('types.create')}}" class="btn btn-primary glow">{{ __('admin.Add New')}}</a>
        </div>
        @endif
        <div class="content-body">
            <section id="basic-datatable">

                <?php $index=1; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
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
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ __('admin.English Name')}}</th>
                                                    <th>{{ __('admin.Arabic Name')}}</th>
                                                    <th>{{ __('admin.Created At')}}</th>
                                                    @if( $_user->ableToAny(['types.show', 'types.edit', 'types.update', 'types.destroy'], $permissions) )<th>{{ __('admin.Actions')}}</th>@endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($types as $type)
                                                    <tr>
                                                        <td>{{$index++}}</td>
                                                        <td>{{$type->name_en}}</td>
                                                        <td>{{$type->name_ar}}</td>
                                                        <td>{{$type->created_at}}</td>
                                                        @if( $_user->ableToAny(['types.show', 'types.edit', 'types.update', 'types.destroy'], $permissions) )
                                                        <td>
                                                            @if( $_user->ableToAny(['types.edit', 'types.update'], $permissions) )
                                                                <a href="{{route('types.edit', ['type' => $type->id])}}" class="btn btn-label-info"><i class="ti ti-edit text-info"> </i></a>
                                                            @endif
                                                            @if( $_user->ableTo('types.destroy', $permissions) )
                                                                <button id='deleteAction' data-id='{{$type->id}}' data-url="{{route('types.destroy', ['type' => $type->id])}}" type="button" class="btn btn-label-danger"><i class="ti ti-trash text-danger"> </i></button>
                                                            @endif
                                                        </td>
                                                        @endif
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
            </section>
        </div>
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
        releaseAlert('warning', {
          title: "{{__('admin.SureDelete')}}",
          message: "{{__('admin.Recover')}}",
          confirmText: "{{__('admin.DYes')}}",
          cancelText: "{{__('admin.No')}}!",
          confirmColor: 'danger'
        })
        .then((ok) => {
            if (ok) {

                $.post(url, {'_method':'DELETE' , '_token':token},
                    function(response){
                        releaseAlert('success', {
                          title: '{{__('admin.Deleted')}}!',
                          message: '{{__('admin.FileDeleted')}}.'
                        });

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
