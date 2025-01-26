<!DOCTYPE html>
<html 
  lang="{{ $l }}" 
  dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  data-theme="theme-default" 
  data-assets-path="/vuexy/assets/" 
  data-template="vertical-menu-template"
  data-style="light"
>

<head>
  @include('admin.includes.head')
</head>

<!-- BEGIN: Body-->
<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Sidebar Menu -->
      @include('admin.includes.menu')

      <div class="layout-page">
        <!-- Navbar -->
        @include('admin.includes.header')

        <!-- Content -->
        @yield('content')

        <!-- Footer -->
        <footer class="mx-6">
          @include('admin.includes.footer')
        </footer>
      </div>
    </div>
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  {{-- <script src="{{ mix('js/app.js') }}"></script> --}}
  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
  <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
  <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/vuexy/assets/vendor/js/menu.js"></script>
  <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/swiper/swiper.js"></script>
  @yield('scripts')
  <!-- End Vendor JS-->

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Custom JS -->
  {{-- <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script> --}}
  <script src="/app-assets/js/1.js"></script>

  <script src="/app-assets/plugins/sweatalert/sweetalert2.all.min.js"></script>
  <script src="/app-assets/plugins/alertify/alertify.min.js"></script>

  <!-- Main Page JS -->
  <!-- End Main JS-->

  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  @hasanyrole('Admin|Manager')
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
      integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <!-- Pusher scripts -->
    {{-- <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script> --}}
    @role('Registered')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
     const pusherConfig = @json(getPusherConfig());
     const pusherAppKey = pusherConfig.MIX_PUSHER_APP_KEY;
     const pusherCluster = pusherConfig.MIX_PUSHER_APP_CLUSTER;
     Pusher.logToConsole = true;
 
     const pusher = new Pusher(pusherAppKey, {
         cluster: pusherCluster,
         forceTLS: true,
     });
 
     const channel = pusher.subscribe('step-changes');
     channel.bind('ChangeStep', function(e) {
         console.log('New Notification:', e);
     });
   </script>
   @endrole
  <!--/ Pusher scripts -->

      {{-- <script>
      $(document).ready(function() {
        var notificationsWrapper = $('.dropdown-menu-media');
        var countWrapper = $('.dropdown-notification');
        var notificationsCountElem = notificationsWrapper.find('li[data-count]');
        var notificationsCount = parseInt(notificationsCountElem.data('count'));
        var notifications = notificationsWrapper.find('li.scrollable-container');
        var audio = new Audio('{{ asset('/notification-sound.mp3') }}');

        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;
        var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
          cluster: 'eu'
        });
        // Subscribe to the channel we specified in our Laravel Event
        var channel = pusher.subscribe('Web-Message');
        // Bind a function to a Event (the full Laravel class)
        channel.bind('App\\Events\\WebMessage', function(data) {
          var existingNotifications = notifications.html();
          // play sound
          audio.play();
          if (data.type === 'mymsg') {
            var mylink = 'dashboard/mymsg';
            var mymessage = '{{ __('admin.New Contact us message from') }}' + ' ' + data.username;
          } else if (data.type === 'NewUser') {
            var mylink = 'dashboard/users';
            var mymessage = data.username + ' ' + '{{ __('admin.Register new account') }}';
          } else if (data.type === 'NewComp') {
            var mylink = 'dashboard/companies';
            var mymessage = data.username + ' ' + '{{ __('admin.Create a new Company') }}';
          } else if (data.type === 'UpdateComp') {
            var mylink = 'dashboard/companies';
            var mymessage = data.username + ' ' + '{{ __('admin.Update Company profile') }}';
          } else if (data.type === 'NewMsg') {
            var mylink = 'dashboard/messages';
            var mymessage = '{{ __('admin.New Message from') }}' + ' ' + data.username;
          } else if (data.type === 'NewServ') {
            var mylink = 'dashboard/requests/all';
            var mymessage = '{{ __('admin.New Service request from') }}' + ' ' + data.username;
          } else if (data.type === 'NewMent') {
            var mylink = 'dashboard/mentorship/sessions';
            var mymessage = '{{ __('admin.New mentorship request from') }}' + ' ' + data.username;
          }
          var newNotificationHtml = `
    <a class="d-flex justify-content-between" href="/` + mylink + `">
                                        <div class="media d-flex align-items-center">
                                            <div class="media-left pr-0">
                                                <div class="avatar mr-1 m-0"><img src="/app-assets/images/portrait/small/avatar.jpg" alt="avatar" height="39" width="39"></div>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="media-heading">` + mymessage + `.</h6>
                                            </div>
                                        </div>
                                    </a>`;
          notifications.html(newNotificationHtml + existingNotifications);
          notificationsCount += 1;
          var nplus = 1;
          nplus += parseInt(notificationsWrapper.find('.notif-count').text());
          notificationsWrapper.find('#notif-plus-count').text(nplus);
          notificationsCountElem.attr('data-count', nplus);
          countWrapper.find('.notif-count').text(nplus);
          notificationsWrapper.find('.notif-count').text(nplus);
          notificationsWrapper.show();
          notificationsWrapper.css('display', '');
        });
      });
    </script> --}}
    <!-- End of Pusher -->
  @endhasanyrole
  <script>
    function sweet_delete(url, message, user_id) {
      $("#row_" + user_id).css('background-color', '#000000').css('color', 'white');
      swal({
        title: message,
        icon: "warning",
        buttons: true,
        dangerMode: true,
      }).then((willDelete) => {
        if (willDelete) {
          $.ajax({
            url: url,
            type: 'post', // Use POST method
            data: {
              _method: 'DELETE', // Specify DELETE method using _method parameter
              _token: '{{ csrf_token() }}' // Include CSRF token for Laravel
            },
            success: function(response) {
              swal({
                title: "{{ trans('alert') }}",
                text: "{{ trans('admin.DeletedSuccessfully') }}",
                icon: "success",
                timer: 1000
              });
              $("#row_" + user_id).hide(1000);
            },
            error: function(xhr, status, error) {
              // Handle errors here
              console.error(error);
            }
          });
        } else {
          $("#row_" + user_id).removeAttr('style');
        }
      });
    }
  </script>


  <!-- BEGIN: Page JS-->
  @yield('pagescripts')
  <!-- END: Page JS-->

</body>
<!-- END: Body-->

</html>
