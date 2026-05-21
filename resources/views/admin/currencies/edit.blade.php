@extends('layouts.admin')
@section('title', __('admin.Currencies') )
@section('index_url', route('currencies.index') )
@section('subTitle', __('admin.edit_currency'))
@section('content')
<!-- BEGIN: Content-->

<div class="">
    <div class="">
        <div class="content-body">
            <section id="basic-input">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title fw-bold pb-3 border-bottom">{{ __('admin.edit_currency') }}</h5>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <form action="{{ route('currencies.update', $currency->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Name') }}</label>
                                                <input type="text" name="currency_name" class="form-control" value="{{ old('currency_name', $currency->currency_name) }}" required>
                                                @error('currency_name')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="currency_name_ar" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Name_AR') }}</label>
                                                <input type="text" name="currency_name_ar" class="form-control" id="currency_name_ar" value="{{ old('currency_name_ar', $currency->currency_name_ar) }}" >
                                                @error('currency_name_ar')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Code') }}</label>
                                                <input type="text" name="currency_code" class="form-control" value="{{ old('currency_code', $currency->currency_code) }}" required>
                                                @error('currency_code')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Symbol') }}</label>
                                                <input type="text" name="currency_symbol" class="form-control" value="{{ old('currency_symbol', $currency->currency_symbol) }}" required>
                                                @error('currency_symbol')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Exchange_Rate') }}</label>
                                                <input type="number" name="exchange_rate_to_usd" class="form-control" step="0.000001" value="{{ old('exchange_rate_to_usd', $currency->exchange_rate_to_usd) }}" required>
                                                @error('exchange_rate_to_usd')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Flag') }}</label>
                                                <input type="file" name="flag" class="form-control" id="flagInput" accept="image/jpeg, image/png, image/jpg">
                                                @error('flag')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                                @if($currency->flag)
                                                    <img src="{{ asset($currency->flag) }}" width="100" alt="Current Flag" class="mt-2 img-thumbnail">
                                                @endif
                                                <div class="mt-2">
                                                    <img id="flagPreview" src="" alt="Flag Preview" class="img-thumbnail" style="max-width: 100px; display: none;">
                                                </div>
                                            </div>
                                            <div class="col-md-6 mt-md-12">
                                                <div class="form-check">
                                                    <input type="checkbox" name="enabled" value="1" class="form-check-input" id="enabledCheckbox" {{ $currency->enabled ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="enabledCheckbox">{{ __('admin.Enabled') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
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
@stop

<!-- END: Content-->

@section('pagescripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

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
