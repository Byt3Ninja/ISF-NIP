<!DOCTYPE html>
<html 
  lang="{{ $l }}"
  dir={{ $l == 'ar' ? 'rtl' : 'ltr' }}
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" 
  data-theme="theme-default"
  data-assets-path="/vuexy/assets/"
  data-template="vertical-menu-template"
  data-style="light"
>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" /
    >
    <title>{{ __('Error 404 - Page not found') }}</title>
    <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

    <!-- Fonts -->
    @if ($l == 'ar')
      <link href="https://fonts.googleapis.com/css?family=Cairo&display=swap" rel="stylesheet">
    @else
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
      <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" 
      />
    @endif

    <!-- Icons -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->

    <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />

    <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />

    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-misc.css" />

    <!-- Helpers -->
    <script src="/vuexy/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="/vuexy/assets/js/config.js"></script>
    
    <!-- BEGIN: Custom CSS-->
    @include('admin.includes.branding-styles')
    <!-- END: Custom CSS-->
  </head>

  <body>
    <!-- Content -->

    <!-- Error -->
    <div class="container-xxl container-p-y">
      <div class="misc-wrapper">
        <h1 class="mb-2 mx-2" style="line-height: 6rem; font-size: 6rem">404</h1>
        <h4 class="mb-2 mx-2">{{ __('Page Not Found') }} ⚠️</h4>
        <p class="mb-6 mx-2">{{ __("we couldn't find the page you are looking for") }}</p>
        <a href="/dashboard" class="btn btn-primary mb-10">{{ __('BACK TO HOME') }}</a>
        <div class="mt-4">
          <img 
            src="/vuexy/assets/img/illustrations/page-misc-error.png"
            alt="page-misc-error" 
            width="225"
            class="img-fluid" 
          />
        </div>
      </div>
    </div>
    <div class="container-fluid misc-bg-wrapper">
      <img src="/vuexy/assets/img/illustrations/bg-shape-image-light.png" height="355" alt="page-misc-error"
        data-app-light-img="illustrations/bg-shape-image-light.png"
        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
    </div>
    <!-- /Error -->

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
    <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
    <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/vuexy/assets/vendor/js/menu.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="/vuexy/assets/js/main.js"></script>

    <!-- Page JS -->
  </body>
</html>
