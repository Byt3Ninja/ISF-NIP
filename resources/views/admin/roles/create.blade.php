@extends('layouts.admin')
@section('title', __('admin.Roles') )
@section('index_url', route('roles.index') )
@section('subTitle', __('admin.CreateRole'))
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
        {{-- @include('admin.includes.heading') --}}
        <div class="content-body">
            <!-- BuilderForm start -->
            <section id="basic-input">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('admin.CreateRole')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    @if(session()->has('msg'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-warning alert-danger mb-2" role="alert">
                                                <button type="button" class="close" data-bs-dismiss="alert"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-error"></i>
                                                    <span>
                                                        {{ __('admin.'.session()->get('msg')) }}
                                                        {{ Session::forget('msg') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <!-- Validation messages -->
                                    @if(count($errors) >0)
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
                                        <form method="POST" action="{{ route("roles.store") }}" enctype="multipart/form-data">
                                            @csrf
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <fieldset class="form-group">
                                                    <label class="fs-6 mb-1" for="basicInput">{{ __('admin.Title')}}</label>
                                                    <input type="text" class="form-control" id="name" name='name'
                                                        required placeholder="{{__('admin.Title')}}" >
                                                </fieldset>

                                            </div>
                                        </div>
                                            <div class="form-group">
                                                <label class="fs-6 mb-1" class="required" for="permissions">{{ __('Permissions') }}</label>

                                                <select class="form-control select {{ $errors->has('permissions') ? 'is-invalid' : '' }}" name="permissions[]" id="permissions" multiple >
                                                    @foreach($permissions as $id => $permission)
                                                        <option value="{{ $id }}" {{ in_array($id, old('permissions', [])) ? 'selected' : '' }}>{{ $permission }}</option>
                                                    @endforeach
                                                </select>
                                                @if($errors->has('permissions'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('permissions') }}
                                                    </div>
                                                @endif

                                            </div>
                                        <div class="row mt-4">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit"
                                                    class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
                                            </div>
                                        </div>
                                </div>
                                </form>
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
