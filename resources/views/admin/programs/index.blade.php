@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/dragula.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/extensions/drag-and-drop.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop
@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-12 mb-2 mt-1">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h5 class="content-header-title float-left pr-1 mb-0">{{__('admin.Cycles')}}</h5>
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb p-0 mb-0">
                                <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a></li>
                                <li class="breadcrumb-item active">{{__('admin.Cycles`s List')}}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-header row">
            @if(session()->has('msg'))
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <div class="d-flex align-items-center"> <i class="bx bx-like"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
                    </div>
                </div>
            @endif
                @if(session()->has('message2'))
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-error"></i>
                            <span>
                                                {{ __('admin.'.session()->get('message2')) }}
                                            </span>
                        </div>
                    </div>
                    </div>
                @endif
        </div>

        <div class="content-body">
            <section class="organizations-list-wrapper">
                <div class="row pb-1">
                    <div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
                        <a href="{{ route('organizations.create') }}" class="btn btn-block btn-success"> {{ __('admin.Add New')}} </a>
                    </div>
                </div>
                <div class="organizations-list-table">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <!-- datatable start -->
                                <div class="table-responsive">
                                    <table id="organizations-list-datatable" class="table">
                                        <thead>
                                        <tr class="table-light">
                                            <th>#</th>
                                            <th>{{__('admin.Name')}}</th>
                                            <th>{{__('admin.Image')}}</th>
                                            <th>{{__('admin.Email')}}</th>
                                            <th>{{__('admin.Phone')}}</th>
                                            <th>@can('delete_user'){{__('admin.Delete')}}@endcan</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach( $organizations as $organization )
                                        <tr>
                                            <td><a href="{{route('organizations.edit', ['organization' => $organization->id])}}">{{$organization->id}}</a></td>
                                            <td style="font-size: small;"><a href="{{route('organizations.edit', ['organization' => $organization->id])}}"> {{ $organization->{'name_'.$l} }}</a></td>

                                            <td>
                                                <div class="media align-items-center">
                                                    <a class="media-left mr-50" href="{{route('organizations.edit', ['organization' => $organization->id])}}">
                                                        <img src="{{$organization->image_url}}" alt="avatar" class="" height="40" >
                                                    </a>
                                                </div>
                                            </td>
                                            <td style="font-size: small;">{{$organization->user->email}}</td>
                                            <td style="font-size: small;">{{$organization->user->phone}}</td>
                                            <td style="padding: 0px;font-size: small;">
                                                @can('delete_user')
                                                    <button id='deleteAction' data-id='{{$organization->id}}' data-url="{{route('organizations.destroy', ['organization' => $organization->id])}}" type="button" class="btn btn-outline-danger btn-sm"><i class="bx bx-trash"> </i></button>
                                                @endcan
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


@stop