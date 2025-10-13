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
    <!-- For Searchable Select -->
    <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>  
    <!-- For Date & Time Picker -->
    <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>

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

    @auth
      <script>
        console.log('User roles:', @json(auth()->user()->roles->pluck('name')));
        console.log('Has Registered role:', @json(auth()->user()->hasRole('Registered')));
        console.log('Has Mentor role:', @json(auth()->user()->hasRole('Mentor')));
        console.log('Has any role (Registered|Mentor):', @json(auth()->user()->hasRole('Registered') || auth()->user()->hasRole('Mentor')));
      </script>
    @endauth

   @if (auth()->user()->hasRole('Registered') || auth()->user()->hasRole('Mentor'))
      <script>console.log('Echo script section executing - user has correct role');</script>
      <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
      <script>
        console.log('Pusher script executed');
        const pusherConfig = @json(getPusherConfig());
        console.log("pusherConfig", pusherConfig);
        const pusherAppKey = pusherConfig.PUSHER_APP_KEY;
        const pusherCluster = pusherConfig.PUSHER_APP_CLUSTER;
        
        console.log('Pusher credentials:', { pusherAppKey, pusherCluster });
        
        // Ensure Pusher is available globally
        window.Pusher = Pusher;
        console.log('Pusher loaded:', typeof Pusher, Pusher);
        
        // Load Laravel Echo dynamically to ensure proper loading
        (function() {
          console.log('Starting Echo dynamic load...');
          const script = document.createElement('script');
          script.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.17.1/dist/echo.iife.js';
          script.onload = function() {
            console.log('Echo script loaded successfully');
            console.log('typeof Echo after load:', typeof Echo);
            console.log('window.Echo before init:', window.Echo);
            
            // Initialize Laravel Echo after script loads
            if (typeof Echo !== 'undefined') {
              try {
                console.log('Attempting to initialize Echo...');
                window.Echo = new Echo({
                  broadcaster: 'pusher',
                  key: pusherAppKey,
                  cluster: pusherCluster,
                  forceTLS: true,
                  auth: {
                    headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                  }
                });
                console.log('Echo initialized successfully:', window.Echo);
                
                // Test Echo connection
                window.Echo.connector.pusher.connection.bind('connected', () => {
                  console.log('Pusher connection established successfully');
                });
                
                window.Echo.connector.pusher.connection.bind('error', (error) => {
                  console.error('Pusher connection error:', error);
                });
                
                // Trigger custom event to notify chat that Echo is ready
                window.dispatchEvent(new CustomEvent('EchoReady'));
              } catch (error) {
                console.error('Error initializing Echo:', error);
              }
            } else {
              console.error('Echo constructor not available after script load');
            }
          };
          script.onerror = function(e) {
            console.error('Failed to load Laravel Echo script:', e);
          };
          console.log('Appending Echo script to head...');
          document.head.appendChild(script);
        })();
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

    {{-- Fallback Echo loading for all authenticated users (for testing) --}}
    @auth
      <script>
        // If Echo wasn't loaded above due to role restrictions, load it anyway for testing
        setTimeout(function() {
          if (typeof window.Echo === 'undefined') {
            console.log('Loading Echo fallback for testing...');
            
            // Load Pusher first if not already loaded
            if (typeof window.Pusher === 'undefined') {
              const pusherScript = document.createElement('script');
              pusherScript.src = 'https://js.pusher.com/8.2.0/pusher.min.js';
              pusherScript.onload = function() {
                window.Pusher = Pusher;
                console.log('Pusher loaded via fallback');
                loadEchoFallback();
              };
              document.head.appendChild(pusherScript);
            } else {
              loadEchoFallback();
            }
            
            function loadEchoFallback() {
              const script = document.createElement('script');
              script.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.17.1/dist/echo.iife.js';
              script.onload = function() {
                console.log('Echo loaded via fallback');
                
                if (typeof Echo !== 'undefined') {
                  try {
                    // Get real Pusher config
                    const pusherConfig = @json(getPusherConfig());
                    console.log('Initializing Echo with config:', pusherConfig);
                    
                    window.Echo = new Echo({
                      broadcaster: 'pusher',
                      key: pusherConfig.PUSHER_APP_KEY,
                      cluster: pusherConfig.PUSHER_APP_CLUSTER,
                      forceTLS: true,
                      auth: {
                        headers: {
                          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                      }
                    });
                    
                    console.log('Echo initialized via fallback with real credentials:', window.Echo);
                    
                    // Test Echo connection
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                      console.log('Pusher connection established successfully');
                    });
                    
                    window.Echo.connector.pusher.connection.bind('error', (error) => {
                      console.error('Pusher connection error:', error);
                    });
                    
                    window.dispatchEvent(new CustomEvent('EchoReady'));
                  } catch (error) {
                    console.error('Error initializing Echo fallback:', error);
                  }
                }
              };
              script.onerror = function(e) {
                console.error('Failed to load Echo fallback:', e);
              };
              document.head.appendChild(script);
            }
          }
        }, 1000);
      </script>
    @endauth

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

    <script>
      // Global SweetAlert2 configuration
      const releaseSuccessAlert = ({title, message}) => {
        Swal.fire({
          icon: 'success',
          title: title || '',
          html: message,
          showCancelButton: false,
          showConfirmButton: true,
          confirmButtonText: "{{ __('admin.OK') }}",
          customClass: {
              confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        });
      }

      const releaseWarningAlert = ({title, message}) => {
        Swal.fire({
          icon: 'warning',
          title: title || '',
          html: message,
          showCancelButton: false,
          showConfirmButton: true,
          confirmButtonText: "{{ __('admin.OK') }}",
          customClass: {
              confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        });
      }

      const releaseFailureAlert = ({title, message}) => {
        Swal.fire({
          icon: 'error',
          title: title || '',
          html: message,
          showCancelButton: false,
          showConfirmButton: true,
          confirmButtonText: "{{ __('admin.OK') }}",
          customClass: {
              confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        });
      }
    </script>


    @stack('scripts')
    <!-- END: Page JS-->
  </body>
  <!-- END: Body-->
</html>