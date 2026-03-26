@extends('layouts.admin')
@section('style')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
    <style>
        .users-view {
            padding: 0.5rem 0;
        }
        .table-borderless td {
            padding: 0.8rem  ;
        }
    </style>
    <!-- BEGIN: Content .users-view .table-borderless td -->
    <div class="">
    <div class="">
      <div class="content-overlay"></div>
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('mentor.mentorshipTasks.index')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ route('mentorshipTasks.index') }}"></a>
                                    </li>
                                    <li class="breadcrumb-item active">{{ $task['name'] }}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- users view start -->
                <section class="users-view">
                    <!-- users view media object ends -->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- users view card details start -->
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h5 class="mb-0"><i class="bx bx-info-circle"></i> {{__('mentor.mentorshipTasks.show')}}</h5>
                                                <form action="{{ route('mentorshipTasks.destroy', $task['id']) }}" method="POST" id="deleteTaskForm">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-sm" id="deleteTaskBtn">
                                                        <i class="ti ti-trash"></i> {{ __('admin.Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                            <hr>
                                            <table class="table table-borderless table-hover">
                                                <tbody>
                                                <tr>
                                                    <td class="width-200">{{__('cycles.Company')}}:</td>
                                                    <td>

                                                            <div class="card">
                                                                <div class="card-header border-bottom">
                                                                    <a href="/dashboard/companyProfile/0">
                                                                     <div class="media align-items-center mt-2 mb-1">
                                                                        <div class="media-left mr-50">
                                                                            <img src="{{ $task['assignee']['photo'] }}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                                        </div>
                                                                        <div class="d-flex align-items-center gap-1">
                                                                            {{ $task['assignee']['name'] }}
                                                                        </div>
                                                                        <div class="media-left mr-50">
                                                                            <img src="#" alt="company avatar" class="rounded-circle" height="40" width="40">
                                                                        </div>
                                                                        <div class="d-flex align-items-center gap-1"> company name </div>
                                                                    </div>
                                                                    </a>
                                                                </div>
                                                            </div>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="width-200">{{__('mentor.mentorshipTasks.title')}}:</td>
                                                    <td>{{ $task['name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('mentor.mentorshipTasks.status')}}:</td>
                                                    <td>
                                                        {{ $task['visible_status'] }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('mentor.mentorshipTasks.description')}}:</td>
                                                    <td >{!! $task['content'] !!}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('mentor.mentorshipTasks.comments')}}:</td>
                                                    @forelse($task['comments'] as $comment)
                                                        <tr>
                                                            <td></td>
                                                            <td>
                                                                <div class="card">
                                                                    <div class="card-header border-bottom">
                                                                        <div class="media align-items-center mt-2 mb-1">
                                                                            <div class="media-left mr-50">
                                                                                <img src="{{ $comment['commenter']['photo'] }}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                                            </div>
                                                                            <div class="d-flex align-items-center gap-1">
                                                                                {{ $comment['commenter']['name'] }}
                                                                            </div>
                                                                        </div>
                                                                        <small> {{ $comment['created_since'] }} </small>
                                                                        <p> {!! $comment['comment'] !!} </p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <td>
                                                            <div class="alert alert-warning text-center">
                                                                @lang('mentor.mentorshipTasks.no comments')
                                                            </div>
                                                        </td>
                                                    @endforelse
                                                <tr>
                                                    <td></td>
                                                    <td>
                                                        <form class="form-horizontal form-bordered" id="basicForm" action= "{{ route('mentorship_tasks.comment') }}" method="POST" enctype="multipart/form-data">
                                                            {!! csrf_field() !!}
                                                            <input type="hidden" name="mentorship_task_id" value="{{ $task['id'] }}">
                                                            <div class="form-label-group">
                                                                <textarea class="form-control" id="summernote" name="comment" required></textarea>
                                                            </div>
                                                            <div class="col-12 d-flex justify-content-end">
                                                                <button type="submit" class="btn btn-success mr-1 mb-1">{{__('settings.Send')}}</button>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- users view card details ends -->
                        </div>
                    </div>
                </section>
                <!-- users view ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->
 @stop
@section('pagescripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote();

            $('#deleteTaskBtn').on('click', function() {
                Swal.fire({
                    title: "{{__('admin.SureDelete')}}",
                    text: "{{__('admin.Recover')}}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('admin.DYes') }}",
                    cancelButtonText: "{{ __('admin.No') }}",
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger me-2',
                        cancelButton: 'btn btn-label-secondary',
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteTaskForm').submit();
                    }
                });
            });
        });
    </script>
@endsection

