@extends('layouts.admin')
@section('title', __('admin.Services') )
@section('index_url', route('services.index') )
@section('subTitle', __('admin.Edit service'))

@section('style')
<style>
    .ql-toolbar.ql-snow {
        border-top-right-radius: 0.5rem;
        border-top-left-radius: 0.5rem;
    }
</style>
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="">
    <div class="">
        <div class="content-overlay"></div>

        <div class="content-body">
            <!-- BuilderForm start -->
            <section id="basic-input">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('admin.Edit service')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    @if(session()->has('msg'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-warning alert-danger mb-2" role="alert">
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
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
                                    <form method="post"
                                        action="{{route('services.update', ['service' => $service->id])}}"
                                        enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        @method('patch')
                                        <div class="row">
                                            <div class="col-md-12">

                                                <fieldset class="form-group">
                                                    <label class="fs-5 mb-2" for="basicInput">{{
                                                        __('admin.Service_Name')}}</label>
                                                    <input style="direction: ltr;" type="text" class="form-control"
                                                        id="name_en" name='name_en' required
                                                        value="{{ $service->name_en }}">
                                                </fieldset>
                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2"
                                                        for="basicInput">{{__('admin.Price')}}</label>
                                                    <input style="direction: ltr;" type="text"
                                                        class="form-control col-md-4 col-sm-12" id="price" name='price'
                                                        required placeholder="{{__('admin.Price')}}"
                                                        value="{{ $service->price}}">
                                                </fieldset>
                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2" for="basicInput">{{ __('admin.Description')
                                                        }}</label>
                                                    <input type="hidden" name="description_en" id="description_en"
                                                        value="{{ old('description_en', $service->description_en) }}" />
                                                    <div id="quill-editor" style="height: 200px;">{!!
                                                        old('description_en', $service->description_en) !!}</div>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label class="form-label" for="basicInput">{{ __('admin.Service Status') }}</label>
                                                    <select class="select2 form-control" required id="status" name='status'>
                                                        <option value="1" {{ old('status', $service->status) == 1 ? 'selected' : ''}} >{{ __('admin.Active') }}</option>
                                                        <option value="0" {{ old('status', $service->status) == 0 ? 'selected' : ''}} >{{ __('admin.Not Active') }}</option>
                                                    </select>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2" for="basicInput">{{ __('admin.Services Categories')}}</label>
                                                    <select class="select2 form-control" required
                                                        id="service_category_id" name='service_category_id'>
                                                        <option value="">{{ __('admin.Services Categories')}}</option>
                                                        @foreach($serviceCategories as $category)
                                                        <option value="{{$category->id}}" {{ ($category->id ==
                                                            $service->service_category_id ? "selected":"") }}>
                                                            {{$category->{'name_'.$l} }}</option>
                                                        @endforeach
                                                    </select>
                                                </fieldset>

                                                @if( $organizations->count() > 0 )
                                                <fieldset class="form-group mt-4">
                                                    <label class="fs-5 mb-2">{{ __('admin.Organizations') }}</label>
                                                    <div class="row">
                                                        @foreach ($organizations as $organization)
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="organization_{{ $organization->id }}"
                                                                    name="organization_ids[]"
                                                                    value="{{ $organization->id }}" {{
                                                                    in_array($organization->id, old('organization_ids',
                                                                $service->organizations->pluck('id')->toArray() ?? []))
                                                                ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="organization_{{ $organization->id }}">
                                                                    {{ $organization->{'name_' . app()->getLocale()} }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </fieldset>
                                                @endif
                                                @if( !$_user->hasRole('serviceProvider') )
                                                <!-- Vendor Details -->
                                                <fieldset class="form-group mb-4">
                                                    <label class="form-label" for="basicInput">{{ __('admin.Service Provider') }}</label>
                                                    <select class="select2 form-control" required id="service_provider_id" name='service_provider_id'>
                                                        <option value="">{{ __('admin.Service Provider') }}</option>
                                                        @foreach ($providers as $provider)
                                                            <option value="{{ $provider->id }}" {{ old('service_provider_id', $service->service_provider_id)==$provider->id ? 'selected' : '' }}> {{ $provider->company_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </fieldset>
                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2">{{ __('admin.Vendor_Name') }}</label>
                                                    <input type="text" class="form-control col-md-6 col-sm-12" id="vendor_name" name="vendor_name" value="{{ old('vendor_name', $service->vendor_name) }}" readonly disabled>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2">{{ __('admin.Vendor_Email') }}</label>
                                                    <input type="email" class="form-control col-md-6 col-sm-12" id="vendor_email" name="vendor_email" value="{{ old('vendor_email', $service->vendor_email) }}" readonly disabled>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2">{{ __('admin.Vendor_Phone') }}</label>
                                                    <input type="text" class="form-control col-md-6 col-sm-12" id="vendor_phone" name="vendor_phone" value="{{ old('vendor_phone', $service->vendor_phone) }}" readonly disabled>
                                                </fieldset>
                                                @endif

                                                <!-- Visibility Controls -->
                                                <fieldset class="form-group mt-3">
                                                    <div class="form-check">
                                                        <input type="hidden" name="hide_price" value="0">
                                                        <input type="checkbox" class="form-check-input" id="hide_price"
                                                            name="hide_price" value="1" {{ old('hide_price',
                                                            $service->hide_price) == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="hide_price">{{
                                                            __('admin.Hide_Price') }}</label>
                                                    </div>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <div class="form-check">
                                                        <input type="hidden" name="hide_vendor_info" value="0">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="hide_vendor_info" name="hide_vendor_info" value="1" {{
                                                            old('hide_vendor_info', $service->hide_vendor_info) == 1 ?
                                                        'checked' : '' }}>
                                                        <label class="form-check-label" for="hide_vendor_info">{{
                                                            __('admin.Hide_Vendor') }}</label>
                                                    </div>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label for="img_url" class="fs-5 mb-2">{{
                                                        __('admin.Image')}}</label>
                                                    <div class="input-group">
                                                        <input type="file" class="form-control" id="img_url"
                                                            name="img_url" accept="image/*">
                                                        <label class="input-group-text" for="img_url">
                                                            <i class="ti ti-upload me-1"></i>{{ __('admin.SelectFile')}}
                                                        </label>
                                                    </div>
                                                    @if($service->img_url)
                                                    <div class="mt-2">
                                                        <small class="text-muted">{{ __('admin.Current Image')
                                                            }}:</small>
                                                        <div class="mt-1">
                                                            <img src="{{ Storage::disk('public_uploads')->url($service->img_url) }}"
                                                                alt="Service Image" class="img-thumbnail"
                                                                style="max-width: 150px; max-height: 150px;">
                                                        </div>
                                                    </div>
                                                    @endif
                                                </fieldset>

                                            </div>
                                        </div>
                                        @if( $_user->ableToAny(['services.edit', 'services.update'], $permissions) )
                                        <div class="row">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit"
                                                    class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
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
@parent
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        var providers = @json( $providers );
        $(document).on('change', '#service_provider_id', function() {
            var provider_id = $(this).val();
            var provider = providers.find(function(provider) {
                return provider.id == provider_id;
            });
            if( typeof provider !== 'undefined' )
            {
                $('#vendor_name').val( provider.company_name );
                $('#vendor_email').val( provider.email );
                $('#vendor_phone').val( provider.phone );
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
        var quill = new Quill('#quill-editor', {
            theme: 'snow'
        });

        var descriptionInput = document.getElementById('description_en');
        quill.on('text-change', function () {
            descriptionInput.value = quill.root.innerHTML;
        });
        });
    </script>
@stop