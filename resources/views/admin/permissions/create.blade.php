@extends('layouts.admin')
@section('title', __('admin.Permissions') )
@section('index_url', route('permissions.index') )
@section('subTitle', __('admin.CreatePermission'))
@section('content')
<!-- BEGIN: Content-->
<div class="">
    <div class="">
        <div class="content-overlay"></div>
        <div class="content-body">
            <!-- BuilderForm start -->
            <section id="basic-input">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header mb-4">
                                <h4 class="card-title">{{__('admin.CreatePermission')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    @if(session()->has('msg'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-warning alert-danger mb-2" role="alert">
                                                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                                                <div class="d-flex align-items-center"> <i class="ti ti-error"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <!-- Validation messages -->
                                    @if(count($errors) > 0 )
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                    <li>{{ $error }} </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <form method="POST" action="{{ route('permissions.store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-12">
                                                <fieldset class="form-group my-6">
                                                    <label class="fs-6 mb-2" for="basicInput">{{ __('admin.Title')}}</label>
                                                    <input type="text" class="form-control" id="name" name='name' required placeholder="{{__('admin.Title')}}" />
                                                </fieldset>
                                                <fieldset class="form-group my-6">
                                                    <label class="fs-6 mb-2" for="description">{{ __('admin.Description')}}</label>
                                                    <input type="text" class="form-control" id="description" name='description' placeholder="{{__('admin.Description')}}" />
                                                </fieldset>
                                                <fieldset class="form-group my-6">
                                                    <label class="fs-6 mb-1" for="permission_group_id">{{ __('admin.permissionsGroup')}}</label>
                                                    <select class="form-control form-select" name="permission_group_id" id="permission_group_id">
                                                        <option value="">Un categorized</option>
                                                        @foreach( $groups as $group )
                                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </fieldset>
                                                <div class="form-check my-6">
                                                    <input type="checkbox" name="is_core" class="form-check-input select-all-row" id="is_core">
                                                    <label class="form-check-label" for="is_core">Core Permission</label>
                                                    <div><small class="form-text text-muted">Core permission will be granted to all users even if not assigned to any role</small></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- BuilderForm end -->
        </div>
    </div>
</div>
<!-- END: Content-->
@stop
