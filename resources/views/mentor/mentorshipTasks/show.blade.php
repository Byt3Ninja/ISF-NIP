@extends('layouts.admin')
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
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
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
                                                    <td>{{__('mentor.mentorshipTasks.reply')}}:</td>
                                                    <td >{!! $task->reply??'----------------------------' !!}</td>
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

