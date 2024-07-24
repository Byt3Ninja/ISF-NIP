@extends('layouts.admin')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('admin.Funding Data')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ route('mentorshipTasks.index') }}"><i class="bx bx-briefcase"></i></a>
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
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
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
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
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
                <section id="multiple-column-form">
                    <div class="row match-height">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form-horizontal form-bordered" id="basicForm" action= "{{ route('mentorshipTasks.store') }}" method="POST" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <input type="hidden" name="mentorship_request_id" value="{{ $mentorship->id }}">
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-md-12 col-12">
                                                        <label for="title">{{__('mentor.mentorshipTasks.title')}}</label>
                                                        <div class="form-label-group">
                                                            <input type="text"  class="form-control" placeholder="{{__('mentor.mentorshipTasks.title')}}" name="title" value="{{ old('title') }}" id="title" required>
                                                        </div>
                                                    </div>
                                                    <br><br>
                                                    <div class="col-md-12 col-12">
                                                        <label for="description">{{__('mentor.mentorshipTasks.description')}}</label>

                                                        <div class="form-label-group">
                                                            <textarea class="form-control" id="description"  name="description" required>
                                                            {!! old('description') !!}
                                                            </textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button type="submit" class="btn btn-success mr-1 mb-1">{{__('settings.Submit')}}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
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


