@php($gtag = \App\Settings::findorfail(1)->first('url'))

<!-- Goggle Tag Manger -->
@if ($gtag)
  <script async src="https://www.googletagmanager.com/gtag/js?id={!! $gtag->url !!}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '{!! $gtag->url !!}');
  </script>
@endif
<!-- End Goggle Tag Manger -->

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta name="author" content="GrowthLab">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name') }}-V2</title>
<link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">

<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">


<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
  href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
  rel="stylesheet" />
<!-- End Fonts -->

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
@yield('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
<!-- END: Vendor CSS-->

<!-- BEGIN: Page CSS-->
@yield('style')
<!-- END: Page CSS-->

<!-- BEGIN: Custom CSS-->
<!-- END: Custom CSS-->

<!-- Helpers -->
<script src="/vuexy/assets/vendor/js/helpers.js"></script>
<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
{{-- <script src="/vuexy/assets/vendor/js/template-customizer.js"></script> --}}

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
<script src="/vuexy/assets/js/config.js"></script>
