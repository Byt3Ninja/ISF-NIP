@extends('layouts.admin')
@section('title', __('admin.Services') )
@section('index_url', route('services.index') )
@section('subTitle', __('admin.Edit service'))
@section('content')
<!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
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
                                    <form method="post" action="{{route('services.update', ['service' => $service->id])}}"
                                        enctype="multipart/form-data">
                                        {!! csrf_field() !!}
                                        @method('patch')
                                        <div class="row">
                                            <div class="col-md-12">

                                                <fieldset class="form-group">
                                                    <label for="basicInput">{{ __('admin.Service_Name')}}</label>
                                                    <input style="direction: ltr;" type="text" class="form-control" id="name_en" name='name_en'
                                                        required 
                                                        value="{{ $service->name_en }}">
                                                </fieldset>
                                                <fieldset class="form-group mt-3">
                                                    <label for="basicInput">{{__('admin.Price')}}</label>
                                                    <input style="direction: ltr;" type="text" class="form-control col-md-4 col-sm-12" id="price" name='price'
                                                           required placeholder="{{__('admin.Price in EGP')}}"
                                                           value="{{ $service->price}}">
                                                </fieldset>
                                                <fieldset class="form-group mt-3">
                                                    <label for="basicInput">{{ __('admin.Description')}}</label>
                                                    <textarea class="form-control" id="basicTextarea"
                                                        name='description_en' required>{!! $service->description_en !!}</textarea>
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label for="basicInput">{{ __('admin.Services Categories')}}</label>
                                                    <select class="select2 form-control" required
                                                        id="service_category_id" name='service_category_id'>
                                                        <option value="">{{ __('admin.Services Categories')}}</option>
                                                        @foreach($serviceCategories as $category)
                                                        <option value="{{$category->id}}"
                                                            {{ ($category->id == $service->service_category_id ? "selected":"") }}>
                                                            {{$category->{'name_'.$l} }}</option>
                                                        @endforeach
                                                    </select>
                                                </fieldset>
                                                  <!-- Vendor Details -->
                                                  <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2">{{ __('admin.Vendor_Name') }}</label>
                                                    <input type="text" class="form-control col-md-6 col-sm-12" name="vendor_name"
                                                           value="{{ old('vendor_name', $service->vendor_name) }}">
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2">{{ __('admin.Vendor_Email') }}</label>
                                                    <input type="email" class="form-control col-md-6 col-sm-12" name="vendor_email"
                                                           value="{{ old('vendor_email', $service->vendor_email) }}">
                                                </fieldset>

                                                <fieldset class="form-group mt-3">
                                                    <label class="fs-5 mb-2">{{ __('admin.Vendor_Phone') }}</label>
                                                    <input type="text" class="form-control col-md-6 col-sm-12" name="vendor_phone"
                                                           value="{{ old('vendor_phone', $service->vendor_phone) }}">
                                                </fieldset>

                                                <!-- Visibility Controls -->
<fieldset class="form-group mt-3">
    <div class="form-check">
        <input type="hidden" name="hide_price" value="0">
        <input type="checkbox" class="form-check-input" id="hide_price" name="hide_price" value="1"
               {{ old('hide_price', $service->hide_price) == 1 ? 'checked' : '' }}>
        <label class="form-check-label" for="hide_price">{{ __('admin.Hide_Price') }}</label>
    </div>
</fieldset>

<fieldset class="form-group mt-3">
    <div class="form-check">
        <input type="hidden" name="hide_vendor_info" value="0">
        <input type="checkbox" class="form-check-input" id="hide_vendor_info" name="hide_vendor_info" value="1"
               {{ old('hide_vendor_info', $service->hide_vendor_info) == 1 ? 'checked' : '' }}>
        <label class="form-check-label" for="hide_vendor_info">{{ __('admin.Hide_Vendor') }}</label>
    </div>
</fieldset>

                <fieldset class="form-group">
                      <label for="basicInput">{{ __('admin.Image')}}</label>
                       <div class="input-group">
                         <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="img_url"
                                name="img_url" accept="image/*" aria-describedby="inputGroupFileAddon01">
                            <label class="custom-file-label"
                            for="inputGroupFile01">{{__('admin.SelectFile')}}</label>
                            </div>
                           </div>

                         </fieldset>

                        </div>
                                        </div>
                                        @if( $_user->ableToAny(['services.edit', 'services.update'], $permissions) )
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
<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('description_ar', {
        language: 'ar'
    });
    CKEDITOR.replace('description_en', {
        language: 'en'
    });

</script>
@stop
