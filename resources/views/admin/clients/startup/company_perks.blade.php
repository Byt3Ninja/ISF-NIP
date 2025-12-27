@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="w-100 h-100" id="iframe-container">
      <iframe
        id="perks-iframe"
        src="https://www.2.perks-marketplace.com/home-growthlabs"
        class="w-100 h-100 rounded"
        frameborder="0">
      </iframe>
    </div>

    @php
      $message = 
        $l == 'en' 
          ? 'Perks & Discounts is currently unavailable. Please try again later.'
          : 'خدمة العروض والخصومات غير متاحة حالياً. يرجى المحاولة مرة أخرى لاحقاً';
    @endphp

    <div id="error-message" class="d-none">
      @include('includes.no-result', ['message' => $message]);
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
  const $iframe = $('#perks-iframe');
  const $iframeContainer = $('#iframe-container');
  const $errorMessage = $('#error-message');

  // Iframe loaded successfully
  $iframe.on('load', function () {
    $errorMessage.addClass('d-none');
    $iframeContainer.removeClass('d-none');
  });

  // Iframe failed to load
  $iframe.on('error', function () {
    $iframeContainer.addClass('d-none');
    $errorMessage.removeClass('d-none');
  });

  // Fallback check after 5 seconds
  setTimeout(function () {
    try {
      const iframeEl = $iframe[0];

      if (!iframeEl.contentWindow || !iframeEl.contentDocument) {
        $iframeContainer.addClass('d-none');
        $errorMessage.removeClass('d-none');
      }
    } catch (e) {
      // Cross-origin access issue → assume failure
      $iframeContainer.addClass('d-none');
      $errorMessage.removeClass('d-none');
    }
  }, 5000);
});
</script>
@endsection
