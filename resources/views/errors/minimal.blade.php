{{-- Shared layout for 403 / 419 / 429 / 500 / 503: the same themed page as 404, with Platform Branding. --}}
<!DOCTYPE html>
<html
  lang="{{ app()->getLocale() }}"
  dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  data-theme="theme-default"
  data-assets-path="/vuexy/assets/"
  data-template="vertical-menu-template"
  data-style="light"
>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>@yield('title')</title>
    <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

    <!-- Fonts -->
    @if (app()->getLocale() == 'ar')
      <link href="https://fonts.googleapis.com/css?family=Cairo&display=swap" rel="stylesheet">
    @else
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
      <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    @endif

    <!-- Core CSS -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-misc.css" />

    <!-- Helpers -->
    <script src="/vuexy/assets/vendor/js/helpers.js"></script>
    <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>
    <script src="/vuexy/assets/js/config.js"></script>

    <!-- BEGIN: Custom CSS-->
    @include('admin.includes.branding-styles')
    <!-- END: Custom CSS-->
  </head>

  <body>
    <div class="container-xxl container-p-y">
      <div class="misc-wrapper">
        <h1 class="mb-2 mx-2" style="line-height: 6rem; font-size: 6rem">@yield('code')</h1>
        <p class="mb-6 mx-2">@yield('message')</p>
        <a href="/" class="btn btn-primary mb-10">{{ __('BACK TO HOME') }}</a>
        <div class="mt-4">
          <img src="/vuexy/assets/img/illustrations/page-misc-error.png" alt="" width="225" class="img-fluid" />
        </div>
      </div>
    </div>
    <div class="container-fluid misc-bg-wrapper">
      <img src="/vuexy/assets/img/illustrations/bg-shape-image-light.png" height="355" alt=""
        data-app-light-img="illustrations/bg-shape-image-light.png"
        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
    </div>
  </body>
</html>
