@extends('layouts.admin')
@section('title', __('admin.Types') )
@section('index_url', route('types.index') )
@section('subTitle', __('admin.EditType'))
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
                            <div class="card-header">
                                <h4 class="card-title">{{__('admin.EditType')}}</h4>
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
                                    <form method="post" action="{{route('types.update', ['type' => $type->id])}}">
                                        {!! csrf_field() !!}
                                        @method('patch')
                                        <div class="row">
                                            <div class="col-md-12">
                                                <fieldset class="form-group">
                                                    <label for="basicInput">{{ __('admin.English Name')}}</label>
                                                    <input style="direction: ltr;" type="text" class="form-control" id="name_en" name='name_en'
                                                        required placeholder="{{__('admin.English Name')}}"
                                                        value="{{ $type->name_en }}">
                                                </fieldset>
                                                <fieldset class="form-group">
                                                    <label for="basicInput">{{__('admin.Arabic Name')}}</label>
                                                    <input style="direction: rtl;" type="text" class="form-control" id="name_ar" name='name_ar'
                                                        required placeholder="{{__('admin.Arabic Name')}}"
                                                        value="{{ $type->name_ar}}">
                                                </fieldset>
                                            </div>
                                        </div>
                                        @if( $_user->ableToAny(['types.edit', 'types.update'], $permissions) )
                                        <div class="row">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
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
