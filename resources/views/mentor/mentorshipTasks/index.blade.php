@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">

@stop
@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('mentor.mentorshipTasks.index')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{__('mentor.mentorshipTasks.all')}}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="multiple-column-form">
                    @if(session()->has('message'))
                        <div class="alert alert-success alert-dismissible mb-2" role="alert">
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-like"></i>
                                <span>
                                                    {{ __('admin.'.session()->get('message')) }}
                                                </span>
                            </div>
                        </div>
                    @endif

                    @if(session()->has('message2')|| !empty($errors->all()))
                        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error"></i>
                                <span>
                                                    {{ __('admin.'.session()->get('message2')) }}
                                    @forelse($errors->all() as $message)

					                {{ $message }}
                                    @empty
                                    @endforelse

                                                </span>
                            </div>
                        </div>
                    @endif
                </section>
                <section class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title align-items-center">
                                    <i class='bx bx-card font-medium-4 mr-1'></i>{{__('mentor.mentorshipTasks.all')}}:
                                    <a href="{{ route('mentorshipTasks.create',['mentorship_id'=>$mentorship->id]) }}" class="btn btn-icon  btn-success float-right" >
                                        <i class="bx bx-plus"></i>{{__('mentor.mentorshipTasks.create')}}
                                    </a>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="card-text">
                                    <div class="table-responsive">
                                        <table class="table mb-0" id="userstable">
                                            <thead class="thead-dark">
                                            <tr>

                                                <th class="text-center">{{__('mentor.mentorshipTasks.mentorship request')}}</th>
{{--                                                <th class="text-center">{{__('mentor.mentorshipTasks.type')}}</th>--}}
                                                <th class="text-center">{{__('cycles.Company')}}</th>
                                                <th class="text-center">{{__('cycles.User/Title')}}</th>
                                                <th class="text-center">{{__('mentor.mentorshipTasks.title')}}</th>
                                                <th class="text-center">{{__('cycles.Done')}}</th>
                                                <th class="text-center">{{__('cycles.Actions')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @foreach($mentorshipTasks as $task)
                                                <tr @if(is_null($task->title)) class="btn-light-warning" @endif id="row_{{ $task->id }}">

                                                    <td class="text-center">
                                                        ({{ $task->mentorshipRequest->code }})
                                                        <br>
                                                        {{ $task->mentorshipRequest->zoom_date }}
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="media align-items-center">
                                                            <a class="media-left mr-50" href="/dashboard/companyProfile/{{$task->company_id}}">
                                                                <img src="{{$task->company->logo_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                            </a>
                                                            <a href="/dashboard/companyProfile/{{$task->company->id}}">
                                                                <div class="media align-items-center">
                                                                    {{$task->company->name }}
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="media align-items-center">
                                                            <a class="media-left mr-50" href="/dashboard/users/{{$task->user_id}}/edit">
                                                                <img src="{{$task->user->image_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                            </a>
                                                            <a href="/dashboard/users/{{$task->user_id}}/edit">
                                                                <div class="media-body">
                                                                    <h6 class="media-heading mb-0">{{$task->user->name }} </h6>
                                                                    <span class="font-small-2">{{$task->user->title}}</span>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        {{ $task->title??"-----" }}
                                                    </td>
                                                    <td class="text-center">
                                                     {{ $task->status =='pending'?__('Uncompleted'):__('Completed') }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if(!is_null($task->title))
                                                            <a href="{{ route('mentorshipTasks.show',['mentorshipTask'=>$task->id]) }}" title="Show" class="btn btn-icon rounded-circle btn-light-info">
                                                                <i class="bx bxs-show"></i></a>
                                                        @endif
                                                        <a href="{{ route('mentorshipTasks.edit',['mentorshipTask'=>$task->id]) }}" title="Edit" class="btn btn-icon rounded-circle btn-light-primary">
                                                            <i class="bx bxs-edit-alt"></i></a>
                                                        <a onclick="sweet_delete( '{{ route('mentorshipTasks.destroy',['mentorshipTask' => $task->id ])  }} ' , '{{ trans('admin.deleted_msg_confirm') }}' ,{{ $task->id }} )" title="Delete" class="btn btn-icon rounded-circle btn-light-danger">
                                                            <i class="bx bxs-x-square"></i></a>
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



                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->

@stop
@section('pagescripts')
    <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="/js/jquery.datetimepicker.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.table').DataTable({
                "responsive": true, "searching": true, "lengthChange": false, "paging": true, "bInfo": false,
                "columnDefs": [
                    {"orderable": false, "targets":  [7]},
                ],
                @if ($l=='ar')
                "language": {
                    "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                    "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
                    "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
                    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
                    "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
                    "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}}
                @endif
            });
        });
    </script>
@stop