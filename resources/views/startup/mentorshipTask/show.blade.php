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
                                            <h5 class="mb-1"><i class="bx bx-info-circle"></i> {{__('mentor.mentorshipTasks.show')}}</h5>
                                            <hr>
                                            <table class="table table-borderless table-hover">
                                                <tbody>
                                                <tr>
                                                    <td class="width-200">{{__('mentor.mentorshipTasks.title')}}:</td>
                                                    <td>{{ $task->title }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('mentor.mentorshipTasks.status')}}:</td>
                                                    <td>
                                                        {{ $task->status =='pending'?__('No'):__('Yes') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('mentor.mentorshipTasks.description')}}:</td>
                                                    <td >{!! $task->description !!}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{__('mentor.mentorshipTasks.comments')}}:</td>
                                                @forelse($task->comments as $comment)
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <div class="card">
                                                                <div class="card-header border-bottom">
                                                                    <div class="media align-items-center mt-2 mb-1">
                                                                        <div class="media-left mr-50">
                                                                            <img src="{{$comment->user->image_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                                        </div>
                                                                        <div class="d-flex align-items-center gap-1">
                                                                            {{$comment->user->name }}
                                                                        </div>

                                                                    </div>
                                                                    <small>
                                                                            {{ $comment->created_at->diffForHumans() }}
                                                                        </small>
                                                                    <p>
                                                                        {!! $comment->comment !!}
                                                                    </p>

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
                                                        <form class="form-horizontal form-bordered" id="basicForm" action= "{{ route('startup.mentorshipTasks.sendComment') }}" method="POST" enctype="multipart/form-data">
                                                            {!! csrf_field() !!}
                                                            <input type="hidden" name="mentorship_task_id" value="{{ $task->id }}">
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
        });
    </script>
@endsection

