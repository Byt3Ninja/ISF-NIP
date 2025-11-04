@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
           
            <div class="content-body">
                <!-- Zero configuration table -->
                <section id="basic-datatable">
                        <div class="row">
                            @if (count($received)>0)
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
                                      <h5 class="card-title mb-0">{{__('admin.Services')}}</h5>
                                      <br>
                                        <a href="/dashboard/directory" class="btn btn-primary glow">{{__('admin.Back to services')}}</a>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body card-dashboard">
                                            <div class="table-responsive">
                                                <table class="table zero-configuration">
                                                    <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{__('admin.Service')}}</th>
                                                        <th>{{__('admin.My Comment')}}</th>
                                                        <th>{{__('admin.Manager comment')}}</th>
                                                        <th>{{__('admin.Created at')}}</th>
                                                        <th>{{__('admin.Approved')}}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($received as $n=>$received)
                                                        <tr>
                                                            <td>{{$n+1}}</td>
                                                            <td>{{ $received->{'title_'.$l} }}</td>
                                                            <td>{{$received->comment??'-'}}</td>
                                                            <td>{{$received->admin_comment}}</td>
                                                            <td>{{$received->created_at}}</td>
                                                            @if ($received->approved=='Yes')
                                                            <td class="text-success py-1">
                                                              {{__('admin.'.$received->approved)}}
                                                            @elseif($received->approved=='No')
                                                            <td class="text-danger py-1">
                                                              {{__('admin.'.$received->approved)}}
                                                            @else
                                                            <td class="text-warning py-1"> {{__('admin.Waiting')}}
                                                            @endif
                                                                
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
                            @else
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-header border-bottom">
                                            <h4 class="card-title">{{__('admin.No Requests')}}</h4>
                                        </div>
                                        <div class="card-body">
                                            <a href="/dashboard/directory" class="btn btn-primary glow">{{__('admin.Show all services')}}</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
@stop
