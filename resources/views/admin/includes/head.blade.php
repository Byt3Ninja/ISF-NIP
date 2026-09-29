@php($gtag = $_settings?->url ?? null)
@php( $platformName = $_settings ? ( $_settings->Platform_Name ?? config('app.name') ) : config('app.name') )

<!-- Goggle Tag Manger -->
@if ($gtag)
  <script async src="https://www.googletagmanager.com/gtag/js?id={!! is_numeric($gtag) ? $gtag : (is_string($gtag) ? json_decode($gtag)->url : $gtag->url) !!}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '{!! is_numeric($gtag) ? $gtag : (is_string($gtag) ? json_decode($gtag)->url : $gtag->url) !!}');
  </script>
@endif
<!-- End Goggle Tag Manger -->

<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport"
  content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<meta name="author" content="GrowthLab">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $platformName }} - {{ env('Version') }}</title>
<link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">

<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

<!-- Flag Icons CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

{{-- <!-- Fonts -->
@if ($l == 'ar')
  <link href="https://fonts.googleapis.com/css?family=Cairo&display=swap" rel="stylesheet">
@else
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />
@endif
<!-- End Fonts --> --}}

<!-- Icons -->
<link rel="stylesheet" href="/vuexy/assets/vendor/fonts/fontawesome.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/fonts/flag-icons.css" />
<!-- End Icons -->

<!-- Core CSS -->
<link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
<link rel="stylesheet" href="/vuexy/assets/css/demo.css" />
<!-- End Core CSS -->

<!-- Vendors CSS -->
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/toastr/toastr.css" />
<!-- For Searchable Select -->
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
<!-- For Date & Time Picker -->
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />

{{-- <link rel="stylesheet" href="/app-assets/plugins/sweatalert/sweetalert2.min.css"> --}}
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />

@yield('vendorstyle')
<!-- END: Vendor CSS-->

<!-- BEGIN: Page CSS-->
@yield('style')
<style>
  .hide-before::before {
    display: none;
  }

  .dataTables_wrapper .row {
    margin-bottom: 0 !important;
  }

  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_paginate {
    margin-top: 0 !important;
  }

  .card-datatable {
    padding-top: 0 !important;
  }

  /* Daterangepicker dropdowns inside a modal/offcanvas are positioned by
     /js/daterangepicker-viewport.js using viewport-fixed coordinates, so they
     stay fully visible instead of drifting off-screen after a date is picked.
     The margin-top override neutralises the per-page offsets that fought it. */
  .daterangepicker.drp-viewport-fixed {
    margin-top: 0 !important;
    position: fixed !important;
    z-index: 1080;
  }
</style>
<!-- END: Page CSS-->

<!-- BEGIN: Custom CSS-->
<!-- END: Custom CSS-->

<!-- Jquery Js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Helpers -->
<script src="/vuexy/assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
<script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="/vuexy/assets/js/config.js"></script>
