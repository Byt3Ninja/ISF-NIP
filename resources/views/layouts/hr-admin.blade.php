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
    <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
      <div class="layout-container">
        <!-- Navbar -->

        @include('admin.includes.header')


        <!-- Layout container -->
        <div class="layout-page">
          <!-- Content wrapper -->
          <div class="">
            <!-- Menu -->
            <!-- Menu Here -->
            <!-- / Menu -->

            <!-- Content -->

            <div class="">
              @yield('content')
            </div>
            <!--/ Content -->

            <!-- Footer -->
            <footer class="mx-6">
              @include('admin.includes.footer')
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!--/ Content wrapper -->
        </div>

        <!--/ Layout container -->
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
    <!-- For Searchable Select -->
    <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>  
    <!-- For Date & Time Picker -->
    <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
    @include('partials.flatpickr-locale')

    <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
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
    
    <!-- For Searchable Select -->
    <script src="/vuexy/assets/js/forms-selects.js"></script>

    @auth
      @if (env('ENABLE_CHAT', false))
        <script>
          (function (d, t) {
            var BASE_URL = "https://app.chatwoot.com";
            var g = d.createElement(t),
              s = d.getElementsByTagName(t)[0];
            g.src = BASE_URL + "/packs/js/sdk.js";
            g.defer = true;
            g.async = true;
            s.parentNode.insertBefore(g, s);

            g.onload = function () {
              window.chatwootSDK.run({
                websiteToken: '{{ env("CHATWOOT_WEBSITE_TOKEN") }}',
                baseUrl: '{{ env("CHATWOOT_WEBSITE_BASEURL") }}',
              });

              window.addEventListener("chatwoot:ready", function () {
                @if (auth()->check())
                  window.$chatwoot.setUser("{{ auth()->user()->id }}", {
                    email: "{{ auth()->user()->email }}",
                    name: "{{ auth()->user()->name }}",
                    avatar_url:
                      "{{ Storage::disk('public_uploads')->url(auth()->user()->img_url) }}",
                    phone_number:
                      "{{ auth()->user()->country_code . auth()->user()->phone ?? '' }}",
                  });
                @endif
              });
            };
          })(document, "script");
        </script>
      @endif
    @endauth

   @if (auth()->user()->hasAnyrole('Registered','Mentor'))
      <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
      <script>
        const pusherConfig = @json(getPusherConfig());
        // console.log("pusherConfig", pusherConfig);
        const pusherAppKey = pusherConfig.PUSHER_APP_KEY;
        const pusherCluster = pusherConfig.PUSHER_APP_CLUSTER;
        // Pusher.logToConsole = true;

        const pusher = new Pusher(pusherAppKey, {
          cluster: pusherCluster,
          forceTLS: true,
        });

        Pusher.log = function (message) {
          const logMessage = message;

          // Extract the JSON part (after `: `)
          const jsonPart = logMessage.substring(logMessage.indexOf("["));

          // Parse it
          const parsedLog = JSON.parse(jsonPart);

          // console.log("parsedLog", parsedLog);

          if (parsedLog?.length > 0 && parsedLog[0]) {
            // console.log("parsedLog[0]", parsedLog[0]);
          }

          if (parsedLog?.length > 0 && parsedLog[1]) {
            // Access the data object
            const parsedLogData = parsedLog[1];

            const data = parsedLogData?.data;

            if (data && data.message) {
              // console.log("data.message", data.message);
              const notifContainer = document.querySelector(
                ".position-relative .ti-bell"
              )?.parentElement;

              if (notifContainer) {
                let count = 1; // Default count

                // Find existing badge
                const existingBadge = notifContainer.querySelector(
                  ".badge-notifications"
                );
                if (existingBadge) {
                  // Extract the current count
                  count = parseInt(existingBadge.textContent, 10) + 1;
                  existingBadge.remove(); // Remove old badge
                }

                // Insert new badge with updated count
                notifContainer.insertAdjacentHTML(
                  "beforeend",
                  `<span class="badge badge-notifications bg-danger w-px-20 h-px-20 rounded-circle d-flex justify-content-center align-items-center">${count}</span>`
                );

                let lang = document.documentElement.lang;

                document.querySelector('#new-count').textContent = `${count} ${lang == 'ar' ? 'جديد' : 'New'}`;

                document.querySelector('#notification-list').insertAdjacentHTML(
                  "beforeend",
                  `
                  <li class="list-group item list-group-item-action dropdown-notifications-item border-bottom">
                    <h6 class="media-heading">${data.message}</h6>
                  </li>
                  `
                );
              }
            }
          }
        };

        const channel = pusher.subscribe("step-changes");
        channel.bind("ChangeStep", function (event) {
          // console.log("Event received:", event);

          // Parse the data field to access the message
          const eventData = JSON.parse(event.data);
          // console.log("Message:", eventData.message);
        });

       const mentorshipChannel = pusher.subscribe("request-mentorship");
       mentorshipChannel.bind("RequestMentorship", function (event) {
        const eventData = JSON.parse(event.data);
    });

     const startupChannel = pusher.subscribe("startup-joining");
       startupChannel.bind("StartupJoining", function (event) {
        const eventData = JSON.parse(event.data);
    });

    const judgerChannel = pusher.subscribe("judges");
       judgerChannel.bind("JudgeAssigned", function (event) {
        const eventData = JSON.parse(event.data);
    });

     const mentorStartupChannel = pusher.subscribe("mentorship-session");
       judgerChannel.bind("MentorshipSessionUpdated", function (event) {
        const eventData = JSON.parse(event.data);
    });

        // Listen for Pusher internal events for debugging
        pusher.connection.bind("state_change", function (states) {
          // console.log("Connection state changed:", states);
        });

        pusher.connection.bind("error", function (err) {
          // console.error("Pusher error:", err);
        });
      </script>
    @endif

    @hasanyrole('Admin|Manager')
      <script
        src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"
      ></script>
    @endhasanyrole

    <script>
      function sweet_delete(url, message, user_id) {
        $("#row_" + user_id)
          .css("background-color", "#000000")
          .css("color", "white");
        swal({
          title: message,
          icon: "warning",
          buttons: true,
          dangerMode: true,
        }).then((willDelete) => {
          if (willDelete) {
            $.ajax({
              url: url,
              type: "post", // Use POST method
              data: {
                _method: "DELETE", // Specify DELETE method using _method parameter
                _token: "{{ csrf_token() }}", // Include CSRF token for Laravel
              },
              success: function (response) {
                swal({
                  title: "{{ trans('alert') }}",
                  text: "{{ trans('admin.DeletedSuccessfully') }}",
                  icon: "success",
                  timer: 1000,
                });
                $("#row_" + user_id).hide(1000);
              },
              error: function (xhr, status, error) {
                // Handle errors here
                console.error(error);
              },
            });
          } else {
            $("#row_" + user_id).removeAttr("style");
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