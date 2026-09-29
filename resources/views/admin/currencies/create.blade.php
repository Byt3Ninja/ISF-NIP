@extends('layouts.admin')
@section('title', __('admin.Currencies') )
@section('index_url', route('currencies.index') )
@section('subTitle', __('admin.Add_new_currency'))
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
                <h5 class="card-title fw-bold pb-3 border-bottom">{{__('admin.Add_new_currency')}}</h5>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <form action="{{ route('currencies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label for="currency_name" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Name') }}</label>
                        <input type="text" name="currency_name" class="form-control" id="currency_name" required>
                        @error('currency_name')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6">
                        <label for="currency_name_ar" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Name_AR') }}</label>
                        <input type="text" name="currency_name_ar" class="form-control" id="currency_name_ar" required>
                        @error('currency_name_ar')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6">
                        <label for="currency_code" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Code') }}</label>
                        <input type="text" name="currency_code" class="form-control" id="currency_code" required>
                        @error('currency_code')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6">
                        <label for="currency_symbol" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Symbol') }}</label>
                        <input type="text" name="currency_symbol" class="form-control" id="currency_symbol" required>
                        @error('currency_symbol')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6">
                        <label for="exchange_rate_to_usd" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Exchange_Rate') }}</label>
                        <input 
                          type="number" 
                          name="exchange_rate_to_usd"
                          step="any" 
                          id="exchange_rate_to_usd" 
                          class="form-control" 
                        />
                        @error('exchange_rate_to_usd')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6">
                        <label for="flagInput" class="form-label fw-semibold mb-1 fs-6">{{ __('admin.Flag') }}</label>
                        <input type="file" name="flag" class="form-control" id="flagInput" accept="image/jpeg, image/png, image/jpg">
                        @error('flag')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                          <img id="flagPreview" src="" alt="Flag Preview" class="img-thumbnail" style="max-width: 100px; display: none;">
                        </div>
                      </div>
                      <div class="col-md-6 mt-md-12">
                        <div class="form-check">
                          <input type="checkbox" name="enabled" value="1" class="form-check-input" id="enabled">
                          <label for="enabled" class="form-check-label">{{ __('admin.Enabled') }}</label>
                        </div>
                      </div>
                    </div>
                    <div class="mt-4">
                      <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
                    </div>
                  </form>

                  <div class="mt-4">
                    <select id="currencySelect" class="form-select">
                      <option value="">{{ __('admin.Choose_Currency') }}</option>
                    </select>
                    <button id="saveCurrency" class="btn btn-primary mt-3">{{ __('admin.Add_Currency') }}</button>
                    <div id="message" class="mt-2"></div>
                  </div>
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

  $(document).ready(function() {
    var currenciesData = {};

    $.ajax({
        url: "https://www.floatrates.com/daily/usd.json",
        type: "GET",
        success: function(response) {
            currenciesData = response;
            $.each(response, function(key, currency) {
                $('#currencySelect').append('<option value="' + key + '">' + currency.name + ' (' + currency.code + ')</option>');
            });
        },
        error: function() {
            $('#message').html('<p style="color:red;">{{ __('admin.Error_Fetching_Currencies') }}</p>');
        }
    });

    $('#saveCurrency').click(function() {
        var selectedKey = $('#currencySelect').val();
        
        if (selectedKey && currenciesData[selectedKey]) {
            var currency = currenciesData[selectedKey];

            $('#currency_name').val(currency.name);
            $('#currency_code').val(currency.code);
            $('#currency_symbol').val(currency.alphaCode);
            $('#exchange_rate_to_usd').val(currency.rate);

            alert('{{ __('admin.Currency_Added_Successfully') }}');
            $('#message').html('<p></p>');
        } else {
            $('#message').html('<p style="color:red;">{{ __('admin.Choose_Currency') }}</p>');
        }
    });
  });
</script>

@stop
