@extends('layouts.admin')
@section('title', __('admin.Services Categories') )
@section('index_url', route('serviceCategories.index') )
@section('subTitle', __('admin.Add new service category'))
@php
    $user = auth()->user();
    $isOrgMan = $user->hasRole('organizationManager');
    $org_id = $user->organization_id ?? 0;
@endphp
@section('content')
<!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
        
        <div class="content-body">
            <!-- BuilderForm start -->
            <section id="basic-input">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('admin.Add new service category')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    @if(session()->has('msg'))
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-warning alert-danger mb-2" role="alert">
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-error"></i>
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
                                    <form method="post" action="{{route('serviceCategories.store')}}">
                                        {!! csrf_field() !!}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <fieldset class="form-group {{ $isOrgMan ? 'd-none' : '' }}">
                                                    <label for="organization_id">{{ __('cycles.Select_Organization')}}</label>
                                                    <select required class="form-select" id="organization_id" name="organization_id" data-placeholder="{{ __('cycles.Select_Organization') }}">
                                                        @if( !$isOrgMan ) <option selected disabled value="">{{ __('cycles.Select_Organization') }}</option>  @endif
                                                        @foreach( \App\Organization::all() as $org )
                                                            @php if( $isOrgMan && $org->id != $org_id ) { continue; } @endphp
                                                            <option {{ $isOrgMan && $org->id == $org_id ? 'selected' : '' }} value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
                                                        @endforeach
                                                    </select>
                                                </fieldset>
                                                <fieldset class="form-group">
                                                    <label for="basicInput">{{ __('admin.English Name')}}</label>
                                                    <input style="direction: ltr;" type="text" class="form-control" id="name_en" name='name_en'
                                                        required placeholder="{{__('admin.English Name')}}"
                                                        value="{{ old('name_en') }}">
                                                </fieldset>
                                                <fieldset class="form-group">
                                                    <label for="basicInput">{{__('admin.Arabic Name')}}</label>
                                                    <input style="direction: rtl;" type="text" class="form-control" id="name_ar" name='name_ar'
                                                        required placeholder="{{__('admin.Arabic Name')}}"
                                                        value="{{ old('name_ar') }}">
                                                </fieldset>

                                            </div>
                                        </div>
                                        @if( $_user->ableToAny(['serviceCategories.create', 'serviceCategories.store'], $permissions) )
                                        <div class="row">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
                                            </div>
                                        </div>
                                        @endif
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
@section('pagescripts')

@stop
