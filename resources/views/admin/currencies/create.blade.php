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

                  <select id="currencySelect" class="form-control">
                    <option value="">اختر العملة</option>
                </select>

                <button id="saveCurrency" class="btn btn-primary">إضافة العملة</button>

<div id="message"></div>


                  <form action="{{ route('currencies.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{ __('admin.Name') }}</label>
                            <input type="text" name="currency_name" class="form-control" id="currency_name" required>
                            @error('currency_name')
                               <div class="text-danger">{{ $message }}</div>
                            @enderror
                          </div>
                      <div class="col-md-6">
                        <label>{{ __('admin.Code') }}</label>
                        <input type="text" name="currency_code" class="form-control" id="currency_code" required>
                        @error('currency_code')
                         <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                     
                      <div class="col-md-6 mt-3">
                        <label>{{ __('admin.Symbol') }}</label>
                        <input type="text" name="currency_symbol" class="form-control" id="currency_symbol" required>
                        @error('currency_symbol')
                          <div class="text-danger">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-md-6 mt-3">
                        <label>{{ __('admin.Exchange_Rate') }}</label>
                        <input type="number" name="exchange_rate_to_usd" step="0.000001" id="exchange_rate_to_usd" class="form-control" >
                        @error('exchange_rate_to_usd')
                          <div class="text-danger">{{ $message }}</div>
                       @enderror
                      </div>
                      <div class="col-md-6 mt-3">
                        <label>{{ __('admin.Flag') }}</label>
                        <input type="file" name="flag" class="form-control" id="flagInput" accept="image/jpeg, image/png, image/jpg">
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

  $(document).ready(function() {
    // جلب العملات من API
    var currenciesData = {};

    // جلب العملات من API
      $.ajax({
          url: "https://www.floatrates.com/daily/usd.json",
          type: "GET",
          success: function(response) {
              currenciesData = response; // تخزين البيانات لاستخدامها لاحقًا
              $.each(response, function(key, currency) {
                  $('#currencySelect').append('<option value="' + key + '">' + currency.name + ' (' + currency.code + ')</option>');
              });
          },
          error: function() {
              $('#message').html('<p style="color:red;">{{ __('admin.Error_Fetching_Currencies') }}</p>');
          }
      });

      // عند الضغط على زر "إضافة العملة"
      $('#saveCurrency').click(function() {
          var selectedKey = $('#currencySelect').val(); // الحصول على العملة المحددة
          
          if (selectedKey && currenciesData[selectedKey]) {
              var currency = currenciesData[selectedKey]; // جلب بيانات العملة المختارة

              // تحديث الحقول تلقائياً
              $('#currency_name').val(currency.name);
              $('#currency_code').val(currency.code);
              $('#currency_symbol').val(currency.alphaCode); // لا يوجد symbol في البيانات، لذلك استخدمت alphaCode
              $('#exchange_rate_to_usd').val(currency.rate);

              alert('{{ __('admin.Currency_Added_Successfully') }}');
          } else {
              $('#message').html('<p style="color:red;">{{ __('admin.Choose_Currency') }}  </p>');
          }
      });
  });
</script>

@stop
