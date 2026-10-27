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
    {{-- {{ dump(request()->route()->getName()) }} --}}
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Sidebar Menu -->
        @if ($_user->hasAnyRole('Admin|organizationManager|subadmin'))
          @include('partials.organized-menu')
        @elseif ($_user->hasRole('Registered'))
          @include('partials.menu-startup')
        @else
          @include('partials.menu')
        @endif

        <div class="layout-page">
          <!-- Navbar -->
          @include('admin.includes.header')

          <div class="content-wrapper">
            <div class="container-xxl flex-grow-1 pt-4 pb-8">

              <!-- Breadcrumb -->
              @include('components.breadcrumbs')

              <!-- Content -->
              @yield('content')
            </div>
          </div>

          <!-- Profile Reminder Modal -->
          @include('partials.profile_reminder_modal')
          <!-- /Profile Reminder Modal -->

          <!-- Survey Reminder Modal -->
          @include('partials.survey_reminder_modal')
          <!-- /Survey Reminder Modal -->

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
    {{-- <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script> --}}
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
    {{-- Do NOT set a global moment locale here (moment.locale('ar')). The bundled
         Arabic moment locale has a postformat() that rewrites Latin digits to
         Arabic-Indic ones (١٢٣), which corrupts every machine-readable format()
         we post to the backend -- e.g. the MM/DD/YYYY date_range value parsed by
         Carbon in CyclesController::modify().

         Widgets are localized individually instead, and views that need localized
         human-facing dates call .locale(...) on the individual moment. --}}
    @include('partials.daterangepicker-locale')

    <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="/vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>
    <script src="/vuexy/assets/vendor/libs/swiper/swiper.js"></script>
    @yield('scripts')
    <!-- End Vendor JS-->

    <!-- Main JS -->
    <script src="/vuexy/assets/js/main.js"></script>
    {{-- <script src="/vuexy/assets/js/form-page-close.js"></script> --}}

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

   @if (auth()->user()->hasRole('Registered') || auth()->user()->hasRole('Mentor'))
      <script>console.log('Echo script section executing - user has correct role');</script>
      <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
      <script>
        // console.log('Pusher script executed');
        const pusherConfig = @json(getPusherConfig());
        // console.log("pusherConfig", pusherConfig);
        const pusherAppKey = pusherConfig.PUSHER_APP_KEY;
        const pusherCluster = pusherConfig.PUSHER_APP_CLUSTER;
        
        // console.log('Pusher credentials:', { pusherAppKey, pusherCluster });
        
        // Ensure Pusher is available globally
        window.Pusher = Pusher;
        // console.log('Pusher loaded:', typeof Pusher, Pusher);
        
        // Load Laravel Echo dynamically to ensure proper loading
        (function() {
          // console.log('Starting Echo dynamic load...');
          const script = document.createElement('script');
          script.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.17.1/dist/echo.iife.js';
          script.onload = function() {
            // console.log('Echo script loaded successfully');
            // console.log('typeof Echo after load:', typeof Echo);
            // console.log('window.Echo before init:', window.Echo);
            
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
                // console.log('Echo initialized successfully:', window.Echo);
                
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

        // Broadcast events that legitimately drive the header notification bell.
        // Anything else that travels over Pusher (chat messages on Echo's
        // private-conversation.* channels, presence/connection frames, ...) must be
        // ignored here: Pusher.log is a static on the Pusher class, so it sees every
        // frame on every connection this page opens.
        const NOTIFICATION_BELL_EVENTS = [
          "ChangeStep",
          "RequestMentorship",
          "StartupJoining",
          "JudgeAssigned",
        ];

        Pusher.log = function (message) {
          const logMessage = message;

          const start = logMessage.indexOf("[");
          if (start === -1) {
            return;
          }

          // Extract the JSON part (after `: `)
          const jsonPart = logMessage.substring(start);

          // Parse it -- most Pusher log lines are not JSON at all.
          let parsedLog;
          try {
            parsedLog = JSON.parse(jsonPart);
          } catch (e) {
            return;
          }

          if (parsedLog?.length > 0 && parsedLog[1]) {
            // Access the data object
            const parsedLogData = parsedLog[1];

            const eventName = parsedLogData?.event;
            if (!NOTIFICATION_BELL_EVENTS.includes(eventName)) {
              return;
            }

            const data = parsedLogData?.data;

            // Only a plain string is renderable; object payloads would print
            // "[object Object]".
            if (data && typeof data.message === "string" && data.message.trim() !== "") {
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
                  // A badge hidden by "mark all as read" holds a stale count and
                  // must not be carried forward.
                  if (existingBadge.style.display !== "none") {
                    count = parseInt(existingBadge.textContent, 10) + 1;
                  }
                  existingBadge.remove(); // Remove old badge
                }

                // Insert new badge with updated count
                notifContainer.insertAdjacentHTML(
                  "beforeend",
                  `<span class="badge badge-notifications bg-danger w-px-20 h-px-20 rounded-circle d-flex justify-content-center align-items-center">${count}</span>`
                );

                let lang = document.documentElement.lang;

                const newCount = document.querySelector('#new-count');
                if (newCount) {
                  newCount.textContent = `${count} ${lang == 'ar' ? 'جديد' : 'New'}`;
                }

                const notificationList = document.querySelector('#notification-list');
                if (notificationList) {
                  const heading = document.createElement('h6');
                  heading.className = 'media-heading';
                  heading.textContent = data.message;

                  const item = document.createElement('li');
                  item.className = 'list-group item list-group-item-action dropdown-notifications-item border-bottom';
                  item.appendChild(heading);

                  notificationList.appendChild(item);
                }
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

    <script>
      // Global Toast function
      function showToast(options) {
        // Accept object with { message, type } properties
        const message = options.message || '';
        const type = options.type || 'info';
        
        // Simple toast notification - you can enhance this with your preferred toast library
        const toast = `
          <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
              <div class="toast-body fs-6">${message}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
          </div>
        `;
        
        $('body').append(toast);
        $('.toast').last().toast({ delay: 3000 }).toast('show').on('hidden.bs.toast', function() {
          $(this).remove();
        });
      }

      window.getSharedDataTableConfig = ({ title, pageLength, descIndex, actionsIndex, withoutFilter = false }) => {
        const config = {
          responsive: true,
          pageLength: pageLength ?? 10,
          order: descIndex ? [[descIndex, 'desc']] : [],
          rowId: row => 'row_' + row.id,
          language: {
            url:
              document.dir === 'rtl'
                ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
                : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
            sLengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: '{{ __('admin.Search') }}...',
          },
          dom: '<"d-flex justify-content-between align-items-center mx-0 mb-0 row"<"col-sm-12 col-md-1 mb-0"l><"col-sm-12 col-md-7 mb-0 filters"><"col-sm-12 col-md-3 mb-0"f>>Bt<"d-flex justify-content-between mx-0 mb-0 row"<"col-sm-12 col-md-6 mb-0"i><"col-sm-12 col-md-6 mb-0"p>>',
          ...(actionsIndex && {
            columnDefs: [
              {
                targets: [actionsIndex], // Actions column
                orderable: false,
                searchable: false
              }
            ],
            buttons: [
              {
                extend: 'csv',
                text: 'CSV',
                className: 'd-none', // Hide default button, trigger via custom dropdown
                title: title ? title + '_Export_' + new Date().toISOString().slice(0, 10) : '',
                exportOptions: {
                  columns: Array.from({ length: actionsIndex }, (_, index) => index), // Export these columns
                }
              },
              {
                extend: 'excel',
                text: 'Excel',
                className: 'd-none', // Hide default button, trigger via custom dropdown
                title: title ? title + '_Export_' + new Date().toISOString().slice(0, 10) : '',
                exportOptions: {
                  columns: Array.from({ length: actionsIndex }, (_, index) => index), // Export these columns
                }
              }
            ]
          }),
        };

        if (withoutFilter) {
          config.initComplete = function () {
            $('.dataTables_filter .form-control').removeClass('form-control-sm');
            $('.dataTables_length .form-select').removeClass('form-select-sm');
            
            const $wrapper = $(this.api().table().container());
            $wrapper.find('.form-select, .dataTables_length select').each(function () {
              const $this = $(this);
              if ($this.hasClass('select2-hidden-accessible')) return;

              const isLength = $this.closest('.dataTables_length').length > 0;

              $this.wrap('<div class="position-relative' + (isLength ? ' d-inline-block' : '') + '"></div>');

              $this.select2({
                dropdownParent: $this.parent(),
                width: isLength ? '80px' : '100%',
                minimumResultsForSearch: Infinity,
              });

              if (!isLength) {
                $this.next('.select2-container').css({
                  'min-width': '150px',
                  'max-width': '200px'
                });
              }
            });
          };
        }

        return config;
      };
    </script>

    <!-- Keeps bootstrap-daterangepicker dropdowns inside modals/offcanvas on screen -->
    <script src="/js/daterangepicker-viewport.js"></script>

    <!-- Lets rich-text editor dialogs (link/image/video) take focus over a modal/offcanvas -->
    <script src="/js/editor-dialogs-in-modal.js"></script>

    <!-- BEGIN: Page JS-->
    @yield('pagescripts')


    <script>
      // Show/hide search menu
      $(function () {
        const html = document.documentElement;

        const updateMenuState = () => {
          if (window.Helpers.isCollapsed()) {
            if (html.classList.contains('layout-menu-hover')) {
              // console.log('layout-menu-hover');
              $('#menu-footer').show(); // Show menu footer
              $('#search-menu').show();
              $('#search-menu-icon').hide();
            } else {
              $('#menu-footer').hide(); // hide menu footer
              $('#search-menu').hide();
              $('#search-menu-icon').show();
            }
          } else {
            // When menu is expanded, always show search input and hide icon
            $('#search-menu').show();
            $('#search-menu-icon').hide();
          }
        };

        // Run once on load
        updateMenuState();

        // Watch for class changes
        new MutationObserver(updateMenuState).observe(html, {
          attributes: true,
          attributeFilter: ['class']
        });
      });
    </script>

    {{-- Add nav=menu parameter to all menu links for breadcrumb tracking --}}
    <script>
      document.querySelectorAll('#layout-menu a.menu-link').forEach(function(link) {
        if (link.href && !link.href.includes('javascript:') && !link.href.includes('#')) {
          const url = new URL(link.href, window.location.origin);
          url.searchParams.set('nav', 'menu');
          link.href = url.toString();
        }
      });
    </script>
    
    @include('partials.alert-modals')

    @stack('scripts')
    <!-- END: Page JS-->
  </body>
  <!-- END: Body-->
</html>
