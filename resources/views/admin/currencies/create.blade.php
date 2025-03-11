@extends('layouts.admin')
@section('title', __('admin.Currencies') )
@section('index_url', route('currencies.index') )
@section('subTitle', __('admin.Add_new_currency'))
@section('content')
<!-- BEGIN: Content-->

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title">{{__('admin.Add_new_currency')}}</h4>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <form action="{{ route('currencies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{ __('admin.Name') }}</label>
                            <input type="text" name="currency_name" class="form-control" required>
                            @error('currency_name')
                               <div class="text-danger">{{ $message }}</div>
                            @enderror
                          </div>
                      <div class="col-md-6">
                        <label>{{ __('admin.Code') }}</label>
                        <input type="text" name="currency_code" class="form-control" required>
                        @error('currency_code')
                         <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                     
                      <div class="col-md-6 mt-3">
                        <label>{{ __('admin.Symbol') }}</label>
                        <input type="text" name="currency_symbol" class="form-control" required>
                        @error('currency_symbol')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6 mt-3">
                        <label>{{ __('admin.Exchange_Rate') }}</label>
                        <input type="number" name="exchange_rate_to_usd" class="form-control" >
                        @error('exchange_rate_to_usd')
                          <div class="text-danger">{{ $message }}</div>
                       @enderror
                      </div>
                      <div class="col-md-6 mt-3">
                        <label>{{ __('admin.Flag') }}</label>
                        <input type="file" name="flag" class="form-control" id="flagInput" accept="image/*">
                        @error('flag')
                         <div class="text-danger">{{ $message }}</div>
                       @enderror
                       <div class="mt-2">
                        <img id="flagPreview" src="" alt="Flag Preview" style="max-width: 100px; display: none;">
                      </div>
                      </div>
                      <div class="col-md-6 mt-10">
                        <label>{{ __('admin.Enabled') }}</label>
                        <input type="checkbox" name="enabled" value="1">
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">{{ __('admin.Save') }}</button>
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
@stop

<!-- END: Content-->

@section('pagescripts')
<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>

<script>
  document.getElementById('flagInput').addEventListener('change', function(event) {
      let file = event.target.files[0];
      if (file) {
          let reader = new FileReader();
          reader.onload = function(e) {
              let preview = document.getElementById('flagPreview');
              preview.src = e.target.result;
              preview.style.display = 'block';
          }
          reader.readAsDataURL(file);
      }
  });
</script>

@stop
