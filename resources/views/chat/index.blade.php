@extends('layouts.admin')

@section('title', __('admin.chat'))

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-chat.css" />
  <style>
    /* Typing indicator animation */
    .typing-dots {
      display: inline-block;
      position: relative;
      padding: 8px 12px;
      background: #f8f9fa;
      border-radius: 18px;
      margin-bottom: 4px;
    }
    
    .typing-dots span {
      display: inline-block;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background-color: #6c757d;
      margin: 0 1px;
      animation: typing 1.4s infinite ease-in-out;
    }
    
    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    .typing-dots span:nth-child(3) { animation-delay: 0s; }
    
    @keyframes typing {
      0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
      }
      40% {
        transform: scale(1);
        opacity: 1;
      }
    }
    
    .typing-indicator {
      opacity: 0.7;
    }

    .app-chat-contacts .sidebar-body {
      max-height: calc(100vh - 75px);
      overflow-y: auto;
    }

    /*
     * Make the message history scrollable.
     * Vuexy's app-chat.css sets `.app-chat .app-chat-history .chat-history-body { overflow: hidden }`
     * (it expects a PerfectScrollbar JS instance that this custom page never initializes),
     * so without overriding it the message list is clipped and cannot scroll.
     * We match Vuexy's selector specificity (3 classes) and load after its stylesheet,
     * so these rules win the cascade. We KEEP Vuexy's height for .chat-history-body and
     * only flip overflow to native auto scrolling.
     */
    .app-chat .app-chat-history .chat-history-body {
      overflow-y: auto !important;
      overflow-x: hidden;
    }
    /* A message that failed to send: dim it and show the inline error note. */
    .chat-message.chat-message-failed .chat-message-text {
      opacity: 0.6;
    }
    .chat-message-error {
      font-size: 0.75rem;
    }
    .app-chat .app-chat-history .chat-history-body .chat-history {
      min-height: 0;
    }

    /* Initials avatar: same footprint as the photo it replaces, so photo<->initials never shifts layout. */
    .chat-avatar-initial {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      font-weight: 500;
      letter-spacing: -0.02em;
      text-transform: uppercase;
      border-radius: 50%;
    }
    .app-chat .avatar img.rounded-circle { object-fit: cover; }
    /* Overlapping member avatars on a team row. */
    .chat-avatar-group { display: inline-flex; align-items: center; }
    .chat-avatar-group > .avatar,
    .chat-avatar-group > .chat-avatar-presence,
    .chat-avatar-group > .chat-avatar-leader { margin-inline-start: -0.5rem; }
    .chat-avatar-group > :first-child { margin-inline-start: 0; }
    .chat-avatar-group .avatar img,
    .chat-avatar-group .chat-avatar-initial,
    .chat-avatar-group .chat-avatar-leader .avatar img,
    .chat-avatar-group .chat-avatar-leader .chat-avatar-initial { box-shadow: 0 0 0 2px var(--bs-card-bg, #fff); }
    .chat-avatar-group .chat-avatar-more {
      margin-inline-start: -0.5rem;
      font-size: 0.75rem;
    }
    .chat-avatar-leader { position: relative; display: inline-flex; }
    /* Leader sits ABOVE its neighbors so the crown is never covered by the next
       overlapping avatar (B2). Rendered first + raised z-index + a touch of
       trailing room so the negative margin of the next avatar can't clip the badge. */
    .chat-avatar-group > .chat-avatar-leader { z-index: 3; padding-inline-end: 3px; }
    .chat-avatar-leader-icon {
      position: absolute;
      top: -6px;
      /* Crown centered over the top of the avatar so an overlapping neighbor
         (which encroaches from the trailing side) can never hide it. */
      inset-inline-start: 50%;
      transform: translateX(-50%);
      font-size: 0.62rem;
      color: #ffab00; /* amber/gold — theme warning tint */
      background: var(--bs-card-bg, #fff);
      box-shadow: 0 0 0 1.5px var(--bs-card-bg, #fff);
      border-radius: 50%;
      line-height: 1;
      padding: 1.5px;
      z-index: 4;
    }
    /* Inline leader crown in the header participant subline (B2). */
    .chat-leader-crown {
      font-size: 0.72rem;
      color: #ffab00;
      margin-inline-start: 0.15rem;
      vertical-align: baseline;
    }

    /* ============================================================
       Chat sidebar redesign — conversation rows as cards (theme-consistent).
       One accent (primary) drives hover + active; everything else is quiet.
       All logical properties for RTL safety.
       ============================================================ */

    /* Section eyebrow: "Team" / "Users" as an uppercase, letter-spaced label.
       ISSUE 3: label + count pill share one baseline via inline-flex so the pill is
       vertically centered against the (smaller) uppercase label. */
    .app-chat-contacts .chat-contact-list-item-title h5 {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--bs-secondary-color, #6c757d) !important;
    }
    /* ISSUE 3: even, consistent rhythm above/below each section header. */
    .app-chat-contacts .chat-contact-list-item-title { margin-top: 1rem; margin-bottom: 0.25rem; }
    .app-chat-contacts .chat-contact-list-item-title:first-child { margin-top: 0.25rem; }
    /* Keep the Users count pill readable against the muted eyebrow. The inline-flex
       gap replaces ms-2, so zero out its inline margin to stay centered. */
    .app-chat-contacts .chat-contact-list-item-title h5 .badge {
      font-size: 0.65rem;
      font-weight: 600;
      margin-inline-start: 0 !important;
    }

    /* The tappable card. ISSUE 3: identical padding on active + inactive so text
       never shifts on selection (the active fill lives on this same <a>). */
    .app-chat-contacts .chat-contact-list-item:not(.chat-contact-list-item-title) > a {
      padding: 0.625rem 0.75rem;
      border-radius: 0.5rem;
      transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    /* ISSUE 3: consistent vertical gap between rows. Override Bootstrap's mb-1 with a
       single tuned rhythm value so rows breathe evenly regardless of utility drift. */
    .app-chat-contacts .chat-contact-list-item:not(.chat-contact-list-item-title) {
      margin-bottom: 0.25rem;
    }
    /* ISSUE 3: the presence dot and leader crown are positioned OUTSIDE the avatar
       circle; the row's truncation uses overflow:hidden which would clip them. Let the
       avatar wrapper + contact info render overflow so the dot/crown are never cut off,
       while text truncation still works on the inner .text-truncate elements. */
    .app-chat-contacts .chat-contact-list-item .chat-avatar-presence,
    .app-chat-contacts .chat-contact-list-item .chat-avatar-leader,
    .app-chat-contacts .chat-contact-list-item .chat-avatar-group,
    .app-chat-contacts .chat-contact-list-item .flex-shrink-0 { overflow: visible; }
    /* ISSUE 3: give the stacked member avatars breathing room from the preview line
       above so the preview text and the avatar-stack never collide/overlap. */
    .app-chat-contacts .chat-contact-info .chat-avatar-group { margin-top: 0.375rem; }
    .app-chat-contacts .chat-contact-list-item:not(.chat-contact-list-item-title) > a:hover {
      background-color: var(--bs-primary-bg-subtle, rgba(105, 108, 255, 0.08));
    }
    .app-chat-contacts .chat-contact-name {
      font-weight: 500;
      font-size: 0.9375rem;
      color: var(--bs-heading-color, var(--bs-body-color));
    }
    .app-chat-contacts .chat-contact-status {
      display: block;
      font-size: 0.8125rem;
      color: var(--bs-secondary-color, #8592a3);
    }
    .app-chat-contacts .chat-contact-meta { align-self: center; }

    /* ----- Active state with GUARANTEED contrast (B4): solid primary + white.
       ISSUE 1: Vuexy's own theme styles `.chat-contact-list li.active` (app-chat.css
       ~L244) with its own color + a purple box-shadow directly on the <li>. Layered
       under our inner-<a> primary fill + shadow, that reads as a doubled/inner outline
       ("border inside the border"). Neutralize the theme's <li> surface so ONLY our
       inner <a> renders as a single clean rounded primary card. We match/raise the
       theme's 5-class + style-context selector so this wins the cascade. */
    .light-style .app-chat .app-chat-contacts .sidebar-body .chat-contact-list .chat-contact-list-item.active,
    .light-style .app-chat .app-chat-contacts .sidebar-body .chat-contact-list .chat-contact-list-item.active:hover,
    .dark-style .app-chat .app-chat-contacts .sidebar-body .chat-contact-list .chat-contact-list-item.active,
    .dark-style .app-chat .app-chat-contacts .sidebar-body .chat-contact-list .chat-contact-list-item.active:hover,
    .app-chat .app-chat-contacts .sidebar-body .chat-contact-list .chat-contact-list-item.active,
    .app-chat .app-chat-contacts .sidebar-body .chat-contact-list .chat-contact-list-item.active:hover {
      background-color: transparent !important;
      box-shadow: none !important;
    }
    .app-chat-contacts .chat-contact-list-item.active > a,
    .app-chat-contacts .chat-contact-list-item.active > a:hover {
      background-color: var(--bs-primary, #696cff);
      /* Soft, tinted lift (not a crisp dark edge) so the active card reads as one
         raised surface rather than an outlined box. */
      box-shadow: 0 2px 8px rgba(105, 108, 255, 0.28);
    }
    .app-chat-contacts .chat-contact-list-item.active .chat-contact-name,
    .app-chat-contacts .chat-contact-list-item.active .chat-contact-status {
      color: #fff !important;
    }
    /* Initials circle on the active row: readable on primary. */
    .app-chat-contacts .chat-contact-list-item.active .chat-avatar-initial {
      background-color: rgba(255, 255, 255, 0.24) !important;
      color: #fff !important;
    }
    /* Unread pill stays legible on the active (primary) surface. */
    .app-chat-contacts .chat-contact-list-item.active .badge.bg-danger {
      background-color: #fff !important;
      color: var(--bs-primary, #696cff) !important;
    }
    /* Stacked-avatar rings pick up the primary surface when active. */
    .app-chat-contacts .chat-contact-list-item.active .chat-avatar-group .avatar img,
    .app-chat-contacts .chat-contact-list-item.active .chat-avatar-group .chat-avatar-initial {
      box-shadow: 0 0 0 2px var(--bs-primary, #696cff);
    }

    /* ----- Presence dot (B5): small circle at the avatar's bottom-trailing corner. */
    .chat-presence-dot {
      position: absolute;
      bottom: 0;
      inset-inline-end: 0;
      width: 0.6rem;
      height: 0.6rem;
      border-radius: 50%;
      box-shadow: 0 0 0 2px var(--bs-card-bg, #fff);
    }
    .chat-presence-dot.is-active { background-color: var(--bs-success, #71dd37); }
    .chat-presence-dot.is-offline { background-color: var(--bs-secondary, #a8b1bb); }
    /* Smaller dot on the tiny stacked team-member avatars. */
    .chat-avatar-group .chat-presence-dot { width: 0.45rem; height: 0.45rem; box-shadow: 0 0 0 1.5px var(--bs-card-bg, #fff); }
    /* Wrapper that positions the dot over an avatar without disturbing layout. */
    .chat-avatar-presence { position: relative; display: inline-flex; }
    /* On the active row, the presence ring matches the primary surface. */
    .app-chat-contacts .chat-contact-list-item.active .chat-presence-dot {
      box-shadow: 0 0 0 2px var(--bs-primary, #696cff);
    }

    /* Header presence dot sizing (larger avatar in the header). */
    .chat-history-header .chat-presence-dot { width: 0.7rem; height: 0.7rem; }

    /* Neutral pre-selection header placeholder (B6): icon-in-a-circle, not a person photo. */
    .chat-header-placeholder {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2.375rem;
      height: 2.375rem;
      border-radius: 50%;
      font-size: 1.25rem;
    }
  </style>
@stop

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->

  <div class="">
    <div class="app-chat card overflow-hidden">
      <div class="row g-0">
        <!-- Sidebar Left -->
        <div class="col app-chat-sidebar-left app-sidebar overflow-hidden" id="app-chat-sidebar-left">
          <div
            class="chat-sidebar-left-user sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-6 pt-12">
         
            <h5 class="mt-4 mb-0">John Doe</h5>
            <span>Admin</span>
            <i class="ti ti-x ti-lg cursor-pointer close-sidebar" data-bs-toggle="sidebar" data-overlay
              data-target="#app-chat-sidebar-left"></i>
          </div>
          <div class="sidebar-body px-6 pb-6">
            <div class="my-6">
              <label for="chat-sidebar-left-user-about" class="text-uppercase text-muted mb-1">About</label>
              <textarea id="chat-sidebar-left-user-about" class="form-control chat-sidebar-left-user-about" rows="3"
                maxlength="120">
Hey there, we’re just writing to let you know that you’ve been subscribed to a repository on GitHub.</textarea>
            </div>
            <div class="my-6">
              <p class="text-uppercase text-muted mb-1">Status</p>
              <div class="d-grid gap-2 pt-2 text-heading ms-2">
                <div class="form-check form-check-success">
                  <input name="chat-user-status" class="form-check-input" type="radio" value="active" id="user-active"
                    checked />
                  <label class="form-check-label" for="user-active">Online</label>
                </div>
                <div class="form-check form-check-warning">
                  <input name="chat-user-status" class="form-check-input" type="radio" value="away" id="user-away" />
                  <label class="form-check-label" for="user-away">Away</label>
                </div>
                <div class="form-check form-check-danger">
                  <input name="chat-user-status" class="form-check-input" type="radio" value="busy" id="user-busy" />
                  <label class="form-check-label" for="user-busy">Do not Disturb</label>
                </div>
                <div class="form-check form-check-secondary">
                  <input name="chat-user-status" class="form-check-input" type="radio" value="offline"
                    id="user-offline" />
                  <label class="form-check-label" for="user-offline">Offline</label>
                </div>
              </div>
            </div>
            <div class="my-6">
              <p class="text-uppercase text-muted mb-1">Settings</p>
              <ul class="list-unstyled d-grid gap-4 ms-2 pt-2 text-heading">
                <li class="d-flex justify-content-between align-items-center">
                  <div>
                    <i class="ti ti-lock ti-md me-1"></i>
                    <span class="align-middle">Two-step Verification</span>
                  </div>
                  <div class="form-check form-switch mb-0 me-1">
                    <input type="checkbox" class="form-check-input" checked />
                  </div>
                </li>
                <li class="d-flex justify-content-between align-items-center">
                  <div>
                    <i class="ti ti-bell ti-md me-1"></i>
                    <span class="align-middle">Notification</span>
                  </div>
                  <div class="form-check form-switch mb-0 me-1">
                    <input type="checkbox" class="form-check-input" />
                  </div>
                </li>
                <li>
                  <i class="ti ti-user-plus ti-md me-1"></i>
                  <span class="align-middle">Invite Friends</span>
                </li>
                <li>
                  <i class="ti ti-trash ti-md me-1"></i>
                  <span class="align-middle">Delete Account</span>
                </li>
              </ul>
            </div>
            <div class="d-flex mt-6">
              <button class="btn btn-primary w-100" data-bs-toggle="sidebar" data-overlay
                data-target="#app-chat-sidebar-left">
                Logout<i class="ti ti-logout ti-16px ms-2"></i>
              </button>
            </div>
          </div>
        </div>
        <!-- /Sidebar Left-->

        <!-- Chat & Contacts -->
        <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
          <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
            <div class="d-flex align-items-center me-6 me-lg-0">
   
              <div class="flex-grow-1 input-group input-group-merge">
                <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                <input type="text" class="form-control chat-search-input" placeholder="Search..." aria-label="Search..."
                  aria-describedby="basic-addon-search31" />
              </div>
            </div>
            <i class="ti ti-x ti-lg cursor-pointer position-absolute top-50 end-0 translate-middle d-lg-none d-block"
              data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
          </div>
          <div class="sidebar-body">
            <!-- Users -->
            <ul class="list-unstyled chat-contact-list py-2 mb-0" id="user-list">
              <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                <h5 class="text-primary mb-0">{{ __('admin.chat_users') }}</h5>
              </li>
              <li class="chat-contact-list-item chat-list-item-0">
                <h6 class="text-muted mb-0">{{ __('admin.chat_loading_users') }}</h6>
              </li>
            </ul>

            <!-- User Search Results -->
            <ul class="list-unstyled chat-contact-list mb-0 py-2" id="search-results" style="display: none;">
              <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                <h5 class="text-primary mb-0">{{ __('admin.chat_search_results') }}</h5>
              </li>
            </ul>
          </div>
        </div>
        <!-- /Chat contacts -->

        <!-- Chat History -->
        <div class="col app-chat-history">
          <div class="chat-history-wrapper">
            <div class="chat-history-header border-bottom">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex overflow-hidden align-items-center">
                  <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar"
                    data-overlay data-target="#app-chat-contacts"></i>
                  <div class="flex-shrink-0 avatar chat-header-avatar-slot">
                    <span class="chat-header-placeholder bg-label-secondary" data-bs-toggle="sidebar" data-overlay
                      data-target="#app-chat-sidebar-right">
                      <i class="ti ti-messages"></i>
                    </span>
                  </div>
                  <div class="chat-contact-info flex-grow-1 ms-4">
                    <h6 class="m-0 fw-normal">{{ __('admin.chat_select_conversation') }}</h6>
                    <small class="user-status text-body">{{ __('admin.chat_start_chatting') }}</small>
                  </div>
                </div>
                {{-- <div class="d-flex align-items-center">
                  <i
                    class="ti ti-phone ti-md cursor-pointer d-sm-inline-flex d-none me-1 btn btn-sm btn-text-secondary text-secondary btn-icon rounded-pill"></i>
                  <i
                    class="ti ti-video ti-md cursor-pointer d-sm-inline-flex d-none me-1 btn btn-sm btn-text-secondary text-secondary btn-icon rounded-pill"></i>
                  <i
                    class="ti ti-search ti-md cursor-pointer d-sm-inline-flex d-none me-1 btn btn-sm btn-text-secondary text-secondary btn-icon rounded-pill"></i>
                  <div class="dropdown">
                    <button
                      class="btn btn-sm btn-icon btn-text-secondary text-secondary rounded-pill dropdown-toggle hide-arrow"
                      data-bs-toggle="dropdown" aria-expanded="true" id="chat-header-actions">
                      <i class="ti ti-dots-vertical ti-md"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="chat-header-actions">
                      <a class="dropdown-item" href="javascript:void(0);">View Contact</a>
                      <a class="dropdown-item" href="javascript:void(0);">Mute Notifications</a>
                      <a class="dropdown-item" href="javascript:void(0);">Block Contact</a>
                      <a class="dropdown-item" href="javascript:void(0);">Clear Chat</a>
                      <a class="dropdown-item" href="javascript:void(0);">Report</a>
                    </div>
                  </div>
                </div> --}}
              </div>
            </div>
            <div class="chat-history-body">
              <ul class="list-unstyled chat-history" id="chat-messages">
                <!-- Messages will be loaded dynamically -->
                <li class="text-center p-4">
                  <small class="text-muted">{{ __('admin.chat_select_to_start') }}</small>
                </li>
              </ul>
            </div>
            <!-- Chat message form -->
            <div class="chat-history-footer shadow-xs">
              <form class="form-send-message d-flex justify-content-between align-items-center">
                <input class="form-control message-input border-0 me-4 shadow-none"
                  placeholder="Type your message here..." />
                <div class="message-actions d-flex align-items-center">
               
                  <button class="btn btn-primary d-flex send-msg-btn">
                    <span class="align-middle d-md-inline-block d-none">Send</span>
                    <i class="ti ti-send ti-16px ms-md-2 ms-0"></i>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- /Chat History -->

        <!-- Sidebar Right -->
        <div class="col app-chat-sidebar-right app-sidebar overflow-hidden" id="app-chat-sidebar-right">
          <div class="sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap px-6 pt-12">
            <div class="avatar avatar-xl avatar-online chat-sidebar-avatar">
              <img src="/vuexy/assets/img/avatars/4.png" alt="Avatar" class="rounded-circle" />
            </div>
            <h5 class="mt-4 mb-0">Felecia Rower</h5>
            <span>NextJS Developer</span>
            <i class="ti ti-x ti-lg cursor-pointer close-sidebar d-block" data-bs-toggle="sidebar" data-overlay
              data-target="#app-chat-sidebar-right"></i>
          </div>
          <div class="sidebar-body p-6 pt-0">
            <div class="my-6">
              <p class="text-uppercase mb-1 text-muted">About</p>
              <p class="mb-0">
                It is a long established fact that a reader will be distracted by the readable content .
              </p>
            </div>
            <div class="my-6">
              <p class="text-uppercase mb-1 text-muted">Personal Information</p>
              <ul class="list-unstyled d-grid gap-4 mb-0 ms-2 py-2 text-heading">
                <li class="d-flex align-items-center">
                  <i class="ti ti-mail ti-md"></i>
                  <span class="align-middle ms-2">josephGreen@email.com</span>
                </li>
                <li class="d-flex align-items-center">
                  <i class="ti ti-phone-call ti-md"></i>
                  <span class="align-middle ms-2">+1(123) 456 - 7890</span>
                </li>
                <li class="d-flex align-items-center">
                  <i class="ti ti-clock ti-md"></i>
                  <span class="align-middle ms-2">Mon - Fri 10AM - 8PM</span>
                </li>
              </ul>
            </div>
            <div class="my-6">
              <p class="text-uppercase text-muted mb-1">Options</p>
              <ul class="list-unstyled d-grid gap-4 ms-2 py-2 text-heading">
                <li class="cursor-pointer d-flex align-items-center">
                  <i class="ti ti-badge ti-md"></i>
                  <span class="align-middle ms-2">Add Tag</span>
                </li>
                <li class="cursor-pointer d-flex align-items-center">
                  <i class="ti ti-star ti-md"></i>
                  <span class="align-middle ms-2">Important Contact</span>
                </li>
                <li class="cursor-pointer d-flex align-items-center">
                  <i class="ti ti-photo ti-md"></i>
                  <span class="align-middle ms-2">Shared Media</span>
                </li>
                <li class="cursor-pointer d-flex align-items-center">
                  <i class="ti ti-trash ti-md"></i>
                  <span class="align-middle ms-2">Delete Contact</span>
                </li>
                <li class="cursor-pointer d-flex align-items-center">
                  <i class="ti ti-ban ti-md"></i>
                  <span class="align-middle ms-2">Block Contact</span>
                </li>
              </ul>
            </div>
            <div class="d-flex mt-6">
              <button class="btn btn-danger w-100" data-bs-toggle="sidebar" data-overlay
                data-target="#app-chat-sidebar-right">
                Delete Contact<i class="ti ti-trash ti-16px ms-2"></i>
              </button>
            </div>
          </div>
        </div>
        <!-- /Sidebar Right -->

        <div class="app-overlay"></div>
      </div>
    </div>
  </div>
  <!-- / Content -->
</div>
<!-- Content wrapper -->
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js"></script>
  <script src="{{ mix('js/app.js') }}"></script>
@endsection

@section('pagescripts')
  <!-- Custom chat implementation - removed conflicting app-chat.js -->

  <script>
    window.CHAT_I18N = {
      teamChatWaiting: @json(__('admin.team_chat_waiting')),
      others: @json(__('admin.chat_others')),
      teamMembers: @json(__('admin.chat_team_members')),
      noMessagesYet: @json(__('admin.chat_no_messages_yet')),
      users: @json(__('admin.chat_users')),
      chats: @json(__('admin.chat_chats')),
      team: @json(__('admin.chat_team')),
      searchResults: @json(__('admin.chat_search_results')),
      noUsersAvailable: @json(__('admin.chat_no_users_available')),
      noUsersFound: @json(__('admin.chat_no_users_found')),
      noConversations: @json(__('admin.chat_no_conversations')),
      noMessagesInConversation: @json(__('admin.chat_no_messages_in_conversation')),
      online: @json(__('admin.chat_online')),
      active: @json(__('admin.chat_active')),
      offline: @json(__('admin.chat_offline')),
      teamChat: @json(__('admin.chat_team_chat')),
      messageNotSent: @json(__('admin.chat_message_not_sent')),
      isTyping: @json(__('admin.chat_is_typing')),
      areTyping: @json(__('admin.chat_are_typing')),
      and: @json(__('admin.chat_and')),
    };
    window.CHAT_HAS_TEAMMATES = false; // set true once users load

    // Global variables
    let currentConversationId = null;
    let currentUser = null;
    let messagePollingInterval = null;
    let typingTimeout = null;
    let echo = null; // For Laravel Echo (real-time broadcasting)
    let subscribedConversationIds = new Set(); // conversation channels we're already subscribed to
    let lastMessageByUserId = {};
    let broadcastingEnabled = false;
    let unreadByConversation = {}; // server-authored unread counts, keyed by conversation id
    let conversationByUserId = {};
    let teamGroups = []; // team group conversations returned by /dashboard/chat/groups
    const LS_KEYS = {
      lastConversationId: 'chat.lastConversationId'
    };

    // Get CSRF token for API requests
    function getCsrfToken() {
      return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // Show loading state
    function showLoading(element) {
      if (element) {
        element.innerHTML = '<div class="text-center p-3"><i class="ti ti-loader ti-spin"></i> Loading...</div>';
      }
    }

    // Show error message
    function showError(message) {
      console.error('Chat Error:', message);
      // You can implement a toast notification here
    }

    // Update the "last message" preview text under each user in the sidebar (live).
    function refreshUserListPreviews() {
      const items = document.querySelectorAll('#user-list .chat-contact-list-item a');
      items.forEach(a => {
        const onclick = a.getAttribute('onclick') || '';
        const match = onclick.match(/startConversationWithUser\((\d+)\)/);
        const userId = match ? parseInt(match[1]) : null;
        if (!userId) return;
        const status = a.querySelector('.chat-contact-status');
        if (status) {
          const preview = lastMessageByUserId[userId]?.body || (window.CHAT_I18N?.noMessagesYet || 'No messages yet');
          status.textContent = preview;
        }
        const convId = conversationByUserId[userId];
        const count = convId ? (unreadByConversation[convId] || 0) : 0;
        const meta = a.querySelector('.chat-contact-meta');
        if (meta) {
          meta.innerHTML = count > 0 ? `<span class="badge bg-danger rounded-pill">${count > 99 ? '99+' : count}</span>` : '';
        }
      });
    }

    // API Helper function
    async function makeApiCall(url, options = {}) {
      const defaultOptions = {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      };

      const response = await fetch(url, { ...defaultOptions, ...options });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      return await response.json();
    }

    // ---- Presence heartbeat ----------------------------------------------------
    // "Active" means the user has the chat OPEN and in the FOREGROUND. A person
    // reading the conversation is present even without clicking, so we can't rely on
    // discrete interactions alone — we send a heartbeat on a timer WHILE the tab is
    // visible. When the tab is hidden/backgrounded we stop, so a truly-away user
    // decays to "Offline" after the server's 5-minute window. The ping only advances
    // the current user's own last_active_at (server-side, auth-scoped), never a
    // background poll — so presence reflects real foreground presence, not polling.
    let lastPresencePing = 0;
    let presenceHeartbeatInterval = null;
    const PRESENCE_PING_THROTTLE = 60000; // don't hit the server more than once/min

    function pingPresence(force = false) {
      // Only count as present when the tab is actually visible/foreground.
      if (document.visibilityState !== 'visible') return;
      const now = Date.now();
      if (!force && now - lastPresencePing < PRESENCE_PING_THROTTLE) return;
      lastPresencePing = now;
      makeApiCall('/dashboard/chat/ping', { method: 'POST', body: JSON.stringify({}) })
        .catch(() => { /* best-effort */ });
    }

    function startPresenceHeartbeat() {
      if (presenceHeartbeatInterval) return;
      // Heartbeat well inside the server's 5-min window so an open, watched tab
      // never lapses to offline. Throttle in pingPresence() prevents duplicates.
      presenceHeartbeatInterval = setInterval(() => pingPresence(), 60000);
    }
    function stopPresenceHeartbeat() {
      if (presenceHeartbeatInterval) { clearInterval(presenceHeartbeatInterval); presenceHeartbeatInterval = null; }
    }

    // Bound once from init.
    function setupPresenceHeartbeat() {
      // Immediate interactions refresh presence right away (bypass throttle a bit).
      const onActivity = () => pingPresence();
      document.addEventListener('keydown', onActivity, { passive: true });
      document.addEventListener('click', onActivity, { passive: true });
      // Foreground <-> background: ping + run the heartbeat only while visible.
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          pingPresence(true);        // came back to the tab -> immediately present
          startPresenceHeartbeat();
        } else {
          stopPresenceHeartbeat();   // tab hidden -> stop; decay to offline
        }
      });
      window.addEventListener('focus', () => pingPresence(true));
      // Start now (the chat page is open and visible).
      pingPresence(true);
      startPresenceHeartbeat();
    }

    // Fetch current user info
    async function fetchCurrentUser() {
      try {
        const data = await makeApiCall('/dashboard/chat/messages');
        currentUser = { id: data.user_id, avatar_url: null, initials: 'ME' };
        // Try to enrich self from any conversation participant that is me.
        (data.conversations || []).some(c =>
          (c.participants || []).some(p => {
            if (p.participantable_id === data.user_id && p.participantable) {
              currentUser.avatar_url = p.participantable.avatar_url || null;
              currentUser.initials   = p.participantable.initials || 'ME';
              currentUser.name       = p.participantable.name;
              return true;
            }
            return false;
          })
        );
        return currentUser;
      } catch (error) {
        showError('Failed to fetch user information');
        return null;
      }
    }

    // Fetch and display messages
    async function fetchMessages() {
      try {
        const chatList = document.getElementById('chat-list');
        if (chatList) {
          showLoading(chatList);
        }

        const data = await makeApiCall('/dashboard/chat/messages');
        console.log('Fetched conversations:', data);
        lastMessageByUserId = {};
        conversationByUserId = {};
        (data.conversations || []).forEach(conversation => {
          if (conversation.type === 'private') {
            const other = (conversation.participants || []).find(p => p.participantable_id !== currentUser?.id);
            const otherId = other?.participantable_id;
            const last = (conversation.messages || [])[0];
            if (otherId && last) {
              lastMessageByUserId[otherId] = last;
            }
            if (otherId) {
              conversationByUserId[otherId] = conversation.id;
            }
          }
        });

        (data.conversations || []).forEach(c => { unreadByConversation[c.id] = c.unread_count || 0; });
        // DM rows read their badge from unreadByConversation via conversationByUserId (see displayUsers).

        displayConversations(data.conversations);
        refreshUserListPreviews();
        return data.conversations;
      } catch (error) {
        showError('Failed to fetch conversations');
        console.error('Error fetching conversations:', error);
      }
    }

    // Fetch and display users list
    // background=true skips the loading spinner (used by the 15s poll so the sidebar
    // presence dots refresh live without flashing "Loading..." every tick).
    async function fetchUsers(background = false) {
      try {
        const userList = document.getElementById('user-list');
        if (!background) { showLoading(userList); }

        const data = await makeApiCall('/dashboard/chat/users');

        displayUsers(data.users);
        return data.users;
      } catch (error) {
        if (!background) { showError('Failed to fetch users'); }
        console.error('Error fetching users:', error);
      }
    }

    // Display users in the user list
    function displayUsers(users) {
      // Team-chat gating: no teammates => disabled empty state.
      window.CHAT_HAS_TEAMMATES = Array.isArray(users) && users.length > 0;
      setChatInputEnabled(window.CHAT_HAS_TEAMMATES);
      if (!window.CHAT_HAS_TEAMMATES) {
        showTeamWaitingEmptyState();
      }

      const userList = document.getElementById('user-list');

      if (!users || users.length === 0) {
        userList.innerHTML = `
          <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
            <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.users || 'Users')}</h5>
          </li>
          <li class="chat-contact-list-item chat-list-item-0">
            <h6 class="text-muted mb-0">${escapeHtml(window.CHAT_I18N?.noUsersAvailable || 'No users available')}</h6>
          </li>
        `;
        renderTeamGroupsSection();
        return;
      }

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
          <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.users || 'Users')} <span class="badge bg-primary ms-2">${users.length}</span></h5>
        </li>
      `;

      users.forEach(user => {
        const avatarUrl = resolveAvatarUrl(user);
        const lastMsg = lastMessageByUserId[user.id]?.body || (window.CHAT_I18N?.noMessagesYet || 'No messages yet');

        const convId = conversationByUserId[user.id];
        const count = convId ? (unreadByConversation[convId] || 0) : 0;
        const isActive = convId && currentConversationId === convId;
        html += `
          <li class="chat-contact-list-item mb-1 ${isActive ? 'active' : ''}">
            <a class="d-flex align-items-center" href="#" onclick="startConversationWithUser(${user.id})">
              <div class="flex-shrink-0">${avatarWithPresence(user)}</div>
              <div class="chat-contact-info flex-grow-1 ms-4">
                <h6 class="chat-contact-name text-truncate m-0">${escapeHtml(user.name || '')}</h6>
                <small class="chat-contact-status text-truncate">${escapeHtml(lastMsg)}</small>
              </div>
              <div class="chat-contact-meta">
                ${count > 0 ? `<span class="badge bg-danger rounded-pill">${count > 99 ? '99+' : count}</span>` : ''}
              </div>
            </a>
          </li>
        `;
      });

      userList.innerHTML = html;

      // Re-inject the team group section on top (displayUsers replaced the list).
      renderTeamGroupsSection();

      // The initial isActive computed above uses conversationByUserId, which may not
      // have been populated on first render; re-apply once the list is in the DOM so a
      // DM row highlights even if the map became available later (ISSUE 2).
      applyActiveConversation(currentConversationId);
    }

    function setChatInputEnabled(enabled) {
      const input = document.querySelector('.message-input');
      const sendBtn = document.querySelector('.send-msg-btn');
      if (input) { input.disabled = !enabled; }
      if (sendBtn) { sendBtn.disabled = !enabled; }
    }

    function showTeamWaitingEmptyState() {
      const list = document.getElementById('chat-messages');
      if (list) {
        list.innerHTML =
          '<li class="text-center p-4"><small class="text-muted">' +
          escapeHtml(window.CHAT_I18N?.teamChatWaiting || '') +
          '</small></li>';
      }
      const header = document.querySelector('.chat-history-header .chat-contact-info h6');
      if (header) { header.textContent = ''; }
    }

    // Fetch and display groups
    async function fetchGroups() {
      try {
        const data = await makeApiCall('/dashboard/chat/groups');
        console.log('Fetched groups:', data);
        
        displayGroups(data.groups);
        return data.groups;
      } catch (error) {
        showError('Failed to fetch groups');
        console.error('Error fetching groups:', error);
      }
    }

    // Display conversations in chat list
    function displayConversations(conversations) {
      const chatList = document.getElementById('chat-list');
      if (!chatList) {
        return;
      }
      if (!conversations || conversations.length === 0) {
        chatList.innerHTML = `
          <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
            <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.chats || 'Chats')}</h5>
          </li>
          <li class="chat-contact-list-item chat-list-item-0">
            <h6 class="text-muted mb-0">${escapeHtml(window.CHAT_I18N?.noConversations || 'No Conversations Found')}</h6>
          </li>
        `;
        return;
      }

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
          <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.chats || 'Chats')}</h5>
        </li>
      `;

      conversations.forEach(conversation => {
        // Get the other participant(s) for display
        const otherParticipants = conversation.participants?.filter(p => 
          p.participantable_id !== currentUser?.id
        ) || [];
        
        let conversationName = 'Unknown User';
        if (conversation.type === 'private' && otherParticipants.length > 0) {
          const participant = otherParticipants[0].participantable;
          if (participant) {
            conversationName = getDisplayName(participant);
          }
        } else if (conversation.type === 'group') {
          conversationName = conversation.name || `Group Chat (${conversation.id})`;
        }
          
        const lastMessage = conversation.messages?.[0];
        const messageBody = lastMessage?.body || (window.CHAT_I18N?.noMessagesYet || 'No messages yet');
        const timeAgo = lastMessage ? formatTimeAgo(lastMessage.created_at) : '';
        const isActive = currentConversationId === conversation.id;
        
        html += `
          <li class="chat-contact-list-item mb-1 ${isActive ? 'active' : ''}">
            <a class="d-flex align-items-center" href="#" onclick="loadConversation(${conversation.id})">
              <div class="flex-shrink-0 avatar avatar-online">
                <img src="/vuexy/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
              </div>
              <div class="chat-contact-info flex-grow-1 ms-4">
                <h6 class="chat-contact-name text-truncate m-0 fw-normal">${conversationName}</h6>
                <small class="chat-contact-status text-truncate">${messageBody}</small>
              </div>
              <div class="chat-contact-meta text-end">
                <small class="text-muted">${timeAgo}</small>
              </div>
            </a>
          </li>
        `;
      });

      chatList.innerHTML = html;
    }

    // Store team groups and (re)render the sidebar so they appear above Users.
    function displayGroups(groups) {
      teamGroups = Array.isArray(groups) ? groups : [];
      console.log('Available groups:', teamGroups);
      // Re-render the user list so the team group section is (re)injected at the top.
      renderTeamGroupsSection();
    }

    // Inject/refresh the team-group entries at the TOP of #user-list (idempotent).
    function renderTeamGroupsSection() {
      const userList = document.getElementById('user-list');
      if (!userList) return;

      // Remove any previously-injected team-group section to avoid duplicates.
      userList.querySelectorAll('[data-team-group-section="1"]').forEach(el => el.remove());

      if (!teamGroups || teamGroups.length === 0) return;

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0" data-team-group-section="1">
          <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.team || 'Team')}</h5>
        </li>
      `;
      teamGroups.forEach(group => {
        // B1: use the REAL team name from the backend; only fall back to a
        // generic localized label if the name is truly empty.
        const groupName = (group.name && group.name.trim())
          ? group.name
          : (window.CHAT_I18N?.teamChat || 'Team Chat');
        const isActive = currentConversationId === group.id;
        const preview = group.last_message || (window.CHAT_I18N?.noMessagesYet || 'No messages yet');
        const unread = group.unread_count || 0;

        // Stacked participant avatars (cap 3, then +N). Leader is always shown first.
        const members = Array.isArray(group.participants) ? group.participants : [];
        const ordered = members.slice().sort((a, b) => (b.is_leader ? 1 : 0) - (a.is_leader ? 1 : 0));
        const CAP = 3;
        let stack = '<div class="chat-avatar-group">';
        ordered.slice(0, CAP).forEach(m => {
          // Each teammate avatar carries a presence dot (B5); the leader also gets the crown (B2).
          stack += m.is_leader
            ? `<span class="chat-avatar-leader">${avatarWithPresence(m, 'avatar-xs')}<i class="ti ti-crown chat-avatar-leader-icon" title="Team leader"></i></span>`
            : avatarWithPresence(m, 'avatar-xs');
        });
        const extra = (group.participants_count || ordered.length) - CAP;
        if (extra > 0) {
          stack += `<span class="avatar avatar-xs chat-avatar-more"><span class="chat-avatar-initial bg-label-secondary">+${extra}</span></span>`;
        }
        stack += '</div>';

        html += `
          <li class="chat-contact-list-item mb-1 ${isActive ? 'active' : ''}" data-team-group-section="1">
            <a class="d-flex align-items-center" href="#" onclick="loadConversation(${group.id}); return false;">
              <div class="flex-shrink-0">${avatarMarkup({ id: group.id, name: groupName, avatar_url: group.avatar_url, initials: group.initials })}</div>
              <div class="chat-contact-info flex-grow-1 ms-4">
                <h6 class="chat-contact-name text-truncate m-0">${escapeHtml(groupName)}</h6>
                <small class="chat-contact-status text-truncate">${escapeHtml(preview)}</small>
                ${stack}
              </div>
              <div class="chat-contact-meta text-end">
                ${unread > 0 ? `<span class="badge bg-danger rounded-pill">${unread > 99 ? '99+' : unread}</span>` : ''}
              </div>
            </a>
          </li>
        `;
      });
      // Prepend so the Team section sits above the "Users" title.
      userList.insertAdjacentHTML('afterbegin', html);

      // This re-render dropped .active from the team rows it just replaced; re-apply
      // the highlight so the open conversation stays selected (ISSUE 2).
      applyActiveConversation(currentConversationId);
    }

    // Load conversation messages
    async function loadConversation(conversationId, evt) {
      if (!conversationId) return;
      
      currentConversationId = conversationId;
      
      try {
        // Update active state in chat list
        document.querySelectorAll('.chat-contact-list-item').forEach(item => {
          item.classList.remove('active');
        });
        const e = evt || (typeof event !== 'undefined' ? event : null);
        if (e && e.target) {
          e.target.closest('.chat-contact-list-item')?.classList.add('active');
        }

        // Show loading in messages area
        const messagesContainer = document.getElementById('chat-messages');
        showLoading(messagesContainer);

        // Setup real-time listeners for this conversation (best-effort; must NOT block loading)
        try {
          setupConversationListeners(conversationId);
        } catch (e) {
          console.warn('Real-time listeners unavailable; continuing without live updates:', e);
        }

        // Fetch conversation data and messages
        const convoData = await loadConversationMessages(conversationId);
        const messages = convoData?.messages || [];
        
        // Update chat header with conversation details
        updateChatHeaderFromConversation(convoData?.conversation);
        
        // Display messages
        displayConversationMessages(messages);
        // Server marked the conversation read in getConversationMessages; refresh sidebar badges.
        try { localStorage.setItem(LS_KEYS.lastConversationId, String(conversationId)); } catch(e) {}
        await fetchMessages();
        await fetchGroups();
        // fetchMessages/fetchGroups re-render the sidebar lists (dropping .active);
        // re-apply the highlight as the LAST step so it survives (ISSUE 2).
        applyActiveConversation(currentConversationId);

      } catch (error) {
        showError('Failed to load conversation');
        console.error('Error loading conversation:', error);
      }
    }

    // Load conversation messages
    async function loadConversationMessages(conversationId) {
      try {
        const data = await makeApiCall(`/dashboard/chat/conversations/${conversationId}/messages`);
        return data;
      } catch (error) {
        console.error('Error loading conversation messages:', error);
        showError('Failed to load conversation messages');
        return { messages: [], conversation: null };
      }
    }

    // Create or get conversation with a user
    async function createOrGetConversation(userId) {
      try {
        const data = await makeApiCall('/dashboard/chat/conversations', {
          method: 'POST',
          body: JSON.stringify({
            user_id: userId,
            type: 'private'
          })
        });
        
        return data.conversation;
      } catch (error) {
        console.error('Error creating/getting conversation:', error);
        showError('Failed to create conversation');
        return null;
      }
    }

    // Display conversation messages
    function displayConversationMessages(messages) {
      const messagesContainer = document.getElementById('chat-messages');
      
      if (!messages || messages.length === 0) {
        // A leader-only team has no one to chat with yet, so the generic
        // "no messages" copy is wrong here — show the waiting notice instead.
        if (!window.CHAT_HAS_TEAMMATES) {
          showTeamWaitingEmptyState();
          return;
        }
        messagesContainer.innerHTML = `
          <li class="text-center p-4">
            <small class="text-muted">${escapeHtml(window.CHAT_I18N?.noMessagesInConversation || 'No messages in this conversation yet')}</small>
          </li>
        `;
        return;
      }

      let html = '';
      
      messages.forEach(message => {
        const isCurrentUser = message.sendable_id === currentUser?.id;
        const messageClass = isCurrentUser ? 'chat-message-right' : '';
        const senderEntity = isCurrentUser
          ? { id: currentUser?.id, name: 'You', avatar_url: currentUser?.avatar_url || null, initials: currentUser?.initials || 'ME' }
          : { id: message.sendable_id, name: message.sender_name || getDisplayName(message.sendable || {}), avatar_url: message.sender_avatar_url || null, initials: message.sender_initials || getInitials(message.sender_name || getDisplayName(message.sendable || {})) };
        const timeFormatted = formatMessageTime(message.created_at);
        const avatarHtml = `<div class="user-avatar flex-shrink-0 ${isCurrentUser ? 'ms-4' : 'me-4'}">${avatarMarkup(senderEntity, 'avatar-sm')}</div>`;

        html += `
          <li class="chat-message ${messageClass}" data-message-id="${message.id}">
            <div class="d-flex overflow-hidden">
              ${!isCurrentUser ? avatarHtml : ''}
              <div class="chat-message-wrapper flex-grow-1">
                <div class="chat-message-text"><p class="mb-0">${escapeHtml(message.body)}</p></div>
                <div class="${isCurrentUser ? 'text-end' : ''} text-muted mt-1">
                  ${isCurrentUser ? '<i class="ti ti-checks ti-16px text-success me-1"></i>' : ''}
                  <small>${timeFormatted}</small>
                </div>
              </div>
              ${isCurrentUser ? avatarHtml : ''}
            </div>
          </li>
        `;
      });
      
      messagesContainer.innerHTML = html;
      
      // Scroll to bottom
      scrollToBottom();
    }

    // Format message time
    function formatMessageTime(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMins / 60);
      
      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins}m ago`;
      if (diffHours < 24) return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      
      return date.toLocaleDateString();
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
      if (text === null || text === undefined) return '';
      return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    // Scroll to bottom of messages
    function scrollToBottom() {
      const chatBody = document.querySelector('.chat-history-body');
      if (chatBody) {
        setTimeout(() => {
          chatBody.scrollTop = chatBody.scrollHeight;
        }, 100);
      }
    }

    // Add message to conversation
    function addMessageToConversation(message) {
      const messagesContainer = document.getElementById('chat-messages');
      if (!messagesContainer) return;
      // Idempotency: never render the same message twice (optimistic insert + echo, or overlapping listeners).
      if (message && message.id != null && messagesContainer.querySelector(`[data-message-id="${message.id}"]`)) {
        return;
      }
      const isCurrentUser = message.sendable_id === currentUser?.id;
      const messageClass = isCurrentUser ? 'chat-message-right' : '';
      const senderEntity = isCurrentUser
        ? { id: currentUser?.id, name: 'You', avatar_url: currentUser?.avatar_url || null, initials: currentUser?.initials || 'ME' }
        : { id: message.sendable_id, name: message.sender_name || getDisplayName(message.sendable || {}), avatar_url: message.sender_avatar_url || null, initials: message.sender_initials || getInitials(message.sender_name || getDisplayName(message.sendable || {})) };
      const timeFormatted = formatMessageTime(message.created_at);
      const avatarHtml = `<div class="user-avatar flex-shrink-0 ${isCurrentUser ? 'ms-4' : 'me-4'}">${avatarMarkup(senderEntity, 'avatar-sm')}</div>`;

      const messageHtml = `
        <li class="chat-message ${messageClass}" data-message-id="${message.id}">
          <div class="d-flex overflow-hidden">
            ${!isCurrentUser ? avatarHtml : ''}
            <div class="chat-message-wrapper flex-grow-1">
              <div class="chat-message-text"><p class="mb-0">${escapeHtml(message.body)}</p></div>
              <div class="${isCurrentUser ? 'text-end' : ''} text-muted mt-1">
                ${isCurrentUser ? '<i class="ti ti-checks ti-16px text-success me-1"></i>' : ''}
                <small>${timeFormatted}</small>
              </div>
            </div>
            ${isCurrentUser ? avatarHtml : ''}
          </div>
        </li>
      `;
      
      messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
      scrollToBottom();
    }

    // Update chat header with conversation info
    function updateChatHeader(conversationId) {
      const chatHeader = document.querySelector('.chat-history-header .chat-contact-info h6');
      const chatStatus = document.querySelector('.chat-history-header .user-status');
      if (chatHeader) {
        chatHeader.textContent = `Conversation ${conversationId}`;
      }
      if (chatStatus) {
        chatStatus.textContent = (window.CHAT_I18N?.online || 'Online');
      }
    }

    // Header for a team group: team name + participant names subline + team avatar.
    function updateChatHeaderForGroup(group) {
      const header = document.querySelector('.chat-history-header .chat-contact-info h6');
      const status = document.querySelector('.chat-history-header .user-status');
      const avatarWrap = document.querySelector('.chat-history-header .chat-header-avatar-slot');
      // B1: real team name; only fall back to a generic label if truly empty.
      const groupName = (group.name && group.name.trim())
        ? group.name
        : (window.CHAT_I18N?.teamChat || 'Team Chat');
      if (header) header.textContent = groupName;
      if (status) {
        const parts = (group.participants || []).slice().sort((a, b) => (b.is_leader ? 1 : 0) - (a.is_leader ? 1 : 0));
        const CAP = 3;
        const shown = parts.slice(0, CAP).map(p => {
          const nm = escapeHtml(p.name || '');
          // B2: refined inline gold crown marker next to the leader's name.
          return p.is_leader ? `${nm}<i class="ti ti-crown chat-leader-crown" title="Team leader"></i>` : nm;
        });
        let html = shown.join(', ');
        if (parts.length > CAP) html += ` +${parts.length - CAP} ${escapeHtml(window.CHAT_I18N?.others || 'others')}`;
        status.innerHTML = html || escapeHtml(window.CHAT_I18N?.teamMembers || 'Team members');
      }
      if (avatarWrap) {
        // B3: pass the team name/initials so a logo-less team shows its initials circle.
        // Keep the stable .chat-header-avatar-slot class; a group has no single presence (no dot).
        avatarWrap.outerHTML = avatarMarkup({ id: group.id, name: groupName, avatar_url: group.avatar_url, initials: group.initials })
          .replace('class="avatar"', 'class="flex-shrink-0 avatar chat-header-avatar-slot"');
      }
    }

    function updateChatHeaderFromConversation(conversation) {
      if (!conversation) return;
      if (conversation.type === 'private') {
        const other = (conversation.participants || []).find(p => p.participantable_id !== currentUser?.id);
        const participant = other?.participantable || {};
        updateChatHeaderWithUser(participant);
        return;
      }
      // group: prefer the enriched teamGroups entry (has participants/avatar), fall back to conversation.
      const group = (teamGroups || []).find(g => g.id === conversation.id) || {
        id: conversation.id, name: conversation.name, avatar_url: null, initials: getInitials(conversation.name), participants: []
      };
      updateChatHeaderForGroup(group);
    }

    // Send message
    async function sendMessage(conversationId, messageText, userId = null) {
      if ((!conversationId && !userId) || !messageText.trim()) return;

      let tempMessage = null;
      try {
        // Add message to UI immediately for better UX
        tempMessage = {
          id: Date.now(), // Temporary ID
          body: messageText.trim(),
          sendable_id: currentUser?.id,
          created_at: new Date().toISOString(),
          sendable: {
            id: currentUser?.id,
            name: 'You'
          }
        };
        
        addMessageToConversation(tempMessage);

        // Clear the input immediately (optimistic) — the text is already captured in
        // messageText and shown in the history, so there's no reason to keep it in the box.
        const messageInput = document.querySelector('.message-input');
        if (messageInput) {
          messageInput.value = '';
        }

        // Prepare request data
        const requestData = {
          message: messageText.trim()
        };

        // Add conversation_id or user_id
        if (conversationId) {
          requestData.conversation_id = conversationId;
        } else if (userId) {
          requestData.user_id = userId;
        }

        const data = await makeApiCall('/dashboard/chat/messages', {
          method: 'POST',
          body: JSON.stringify(requestData)
        });

        console.log('Message sent:', data);

        // Remove the optimistic temp message (the real one arrives via re-render/echo, deduped by id).
        if (tempMessage && tempMessage.id != null) {
          const tempEl = document.querySelector(`[data-message-id="${tempMessage.id}"]`);
          if (tempEl) tempEl.remove();
        }

        // If this was a new conversation, update the conversation list
        if (data.conversation && !conversationId) {
          await fetchMessages();
          loadConversation(data.conversation.id);
        } else if (conversationId) {
          // Re-render the conversation from the server so the real message (with real id) shows once.
          const convoData = await loadConversationMessages(conversationId);
          if (convoData && Array.isArray(convoData.messages)) {
            displayConversationMessages(convoData.messages);
            scrollToBottom();
          }
        }

        return data;
      } catch (error) {
        showError('Failed to send message');
        console.error('Error sending message:', error);

        // Surface the failure in the chat history: keep the optimistic message visible
        // and annotate it as failed (instead of silently dropping it), so the user knows
        // the message was not delivered.
        const tempMessageEl = tempMessage ? document.querySelector(`[data-message-id="${tempMessage.id}"]`) : null;
        if (tempMessageEl) {
          tempMessageEl.classList.add('chat-message-failed');
          const wrapper = tempMessageEl.querySelector('.chat-message-wrapper');
          if (wrapper && !wrapper.querySelector('.chat-message-error')) {
            const err = document.createElement('div');
            err.className = 'chat-message-error text-danger mt-1';
            err.innerHTML = '<small><i class="ti ti-alert-circle ti-14px me-1"></i>' + escapeHtml(window.CHAT_I18N?.messageNotSent || 'Message not sent. Please try again.') + '</small>';
            wrapper.appendChild(err);
          }
        }
      }
    }

    // Create new group
    async function createGroup(groupData) {
      try {
        const data = await makeApiCall('/dashboard/chat/groups', {
          method: 'POST',
          body: JSON.stringify(groupData)
        });

        console.log('Group created:', data);
        
        // Refresh groups list
        await fetchGroups();
        
        return data;
      } catch (error) {
        showError('Failed to create group');
        console.error('Error creating group:', error);
      }
    }

    // Search users for new conversations
    async function searchUsers(query) {
      try {
        const data = await makeApiCall(`/dashboard/chat/users/search?q=${encodeURIComponent(query)}`);
        return data.users || [];
      } catch (error) {
        console.error('Error searching users:', error);
        showError('Failed to search users');
        return [];
      }
    }

    // Display search results
    function displaySearchResults(users) {
      const searchResults = document.getElementById('search-results');
      const userList = document.getElementById('user-list');
      
      if (!users || users.length === 0) {
        searchResults.innerHTML = `
          <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
            <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.searchResults || 'Search Results')}</h5>
          </li>
          <li class="chat-contact-list-item">
            <h6 class="text-muted mb-0">${escapeHtml(window.CHAT_I18N?.noUsersFound || 'No users found')}</h6>
          </li>
        `;
        searchResults.style.display = 'block';
        if (userList) userList.style.display = 'none';
        return;
      }

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
          <h5 class="text-primary mb-0">${escapeHtml(window.CHAT_I18N?.searchResults || 'Search Results')}</h5>
          <button class="btn btn-sm btn-text-secondary float-end" onclick="hideSearchResults()">
            <i class="ti ti-x"></i>
          </button>
        </li>
      `;

      users.forEach(user => {
        const avatarUrl = resolveAvatarUrl(user);
        html += `
          <li class="chat-contact-list-item mb-1">
            <a class="d-flex align-items-center" href="#" onclick="startConversationWithUser(${user.id})">
              <div class="flex-shrink-0">${avatarMarkup(user)}</div>
              <div class="chat-contact-info flex-grow-1 ms-4">
                <h6 class="chat-contact-name text-truncate m-0 fw-normal">${escapeHtml(user.name || '')}</h6>
              </div>
              <div class="chat-contact-meta">
                <i class="ti ti-message-circle-plus text-primary"></i>
              </div>
            </a>
          </li>
        `;
      });

      searchResults.innerHTML = html;
      searchResults.style.display = 'block';
      if (userList) userList.style.display = 'none';
    }

    // Hide search results and show conversations
    function hideSearchResults() {
      const searchResults = document.getElementById('search-results');
      const userList = document.getElementById('user-list');
      const searchInput = document.querySelector('.chat-search-input');
      
      searchResults.style.display = 'none';
      if (userList) userList.style.display = 'block';
      
      if (searchInput) {
        searchInput.value = '';
      }
    }

    // Get user initials for avatar
    function getUserInitials(name) {
      if (!name) return 'U';
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }

    // Server is the single source of truth for avatar URLs. No path guessing.
    function resolveAvatarUrl(entity) {
      return (entity && entity.avatar_url) ? entity.avatar_url : null;
    }

    // Small presence dot for the bottom-trailing corner of an avatar (B5).
    // Green when active, muted grey when offline. Driven by data, never hardcoded.
    function presenceDot(presence) {
      const cls = presence === 'active' ? 'is-active' : 'is-offline';
      return `<span class="chat-presence-dot ${cls}"></span>`;
    }

    // Wrap avatar markup so a presence dot can be positioned over it.
    function avatarWithPresence(entity, sizeClass) {
      return `<span class="chat-avatar-presence">${avatarMarkup(entity, sizeClass)}${presenceDot(entity && entity.presence)}</span>`;
    }

    // Initials: "User Name" => "UN"; single token => first two letters; empty => "?".
    function getInitials(name) {
      const n = (name || '').trim();
      if (!n) return '?';
      const parts = n.split(/\s+/);
      if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    // Deterministic theme-tint color per entity id (stable across renders).
    const AVATAR_COLORS = ['bg-label-primary','bg-label-info','bg-label-success','bg-label-warning','bg-label-danger','bg-label-secondary'];
    function avatarColorClass(id) {
      const i = Math.abs(parseInt(id) || 0) % AVATAR_COLORS.length;
      return AVATAR_COLORS[i];
    }

    // Avatar markup: real photo (falling back to an initials circle on load error)
    // or an initials circle directly. The onerror fallback is handled by a named
    // function via data-* attributes so NO HTML is inlined into the onerror attribute
    // (previous inline-HTML approach corrupted the DOM when the initials markup
    // contained double quotes).
    function avatarMarkup(entity, sizeClass) {
      entity = entity || {};
      const initials = entity.initials || getInitials(entity.name);
      const colorClass = avatarColorClass(entity.id);
      const cls = 'avatar' + (sizeClass ? ' ' + sizeClass : '');
      const initialSpan = `<span class="chat-avatar-initial ${colorClass}">${escapeHtml(initials)}</span>`;
      const url = resolveAvatarUrl(entity);
      if (url) {
        // On load error, onImgError() swaps the <img> for the initials circle using
        // the data-* payload — never a stock/numbered photo, and no attribute-escaping hazard.
        return `<div class="${cls}"><img src="${escapeHtml(url)}" alt="${escapeHtml(entity.name || '')}" class="rounded-circle" data-initials="${escapeHtml(initials)}" data-color="${escapeHtml(colorClass)}" onerror="onImgError(this)" /></div>`;
      }
      return `<div class="${cls}">${initialSpan}</div>`;
    }

    // Swap a broken avatar <img> for its initials circle (data-driven, no inline HTML in attributes).
    function onImgError(img) {
      if (!img || img.dataset.fallbackApplied) return;
      img.dataset.fallbackApplied = '1';
      const span = document.createElement('span');
      span.className = 'chat-avatar-initial ' + (img.dataset.color || 'bg-label-secondary');
      span.textContent = img.dataset.initials || '?';
      img.replaceWith(span);
    }

    function getDisplayName(person) {
      if (!person) return 'User';
      const direct = person.name || person.full_name || '';
      if (direct) return direct;
      const first = person.first_name_en || person.first_name_ar || person.first_name || person.firstname || '';
      const last = person.last_name_en || person.last_name_ar || person.last_name || person.lastname || '';
      const combined = (first + ' ' + last).trim();
      if (combined) return combined;
      if (person.email) return person.email;
      if (person.id) return `User ${person.id}`;
      return 'User';
    }

    // Start conversation with a user
    async function startConversationWithUser(userId) {
      try {
        hideSearchResults();
        const messagesContainer = document.getElementById('chat-messages');
        showLoading(messagesContainer);
        const conversation = await createOrGetConversation(userId);
        if (conversation) {
          currentConversationId = conversation.id;
          try { setupConversationListeners(conversation.id); } catch (e) {}
          const convoData = await loadConversationMessages(conversation.id);
          updateChatHeaderFromConversation(convoData?.conversation);
          displayConversationMessages(convoData?.messages || []);
          try { localStorage.setItem(LS_KEYS.lastConversationId, String(conversation.id)); } catch(e) {}
          await fetchMessages();
          // fetchMessages re-renders the user list; re-apply the DM highlight last (ISSUE 2).
          applyActiveConversation(currentConversationId);
        }
      } catch (error) {
        showError('Failed to start conversation');
        console.error('Error starting conversation:', error);
      }
    }

    // Update chat header for a private conversation with a presented user entity.
    function updateChatHeaderWithUser(entity) {
      const chatHeader = document.querySelector('.chat-history-header .chat-contact-info h6');
      const chatStatus = document.querySelector('.chat-history-header .user-status');
      const avatarWrap = document.querySelector('.chat-history-header .chat-header-avatar-slot');
      const name = getDisplayName(entity || {});
      // B5: real presence — no hardcoded "Online"/avatar-online.
      const isActive = (entity && entity.presence) === 'active';
      if (chatHeader) chatHeader.textContent = name;
      if (chatStatus) {
        chatStatus.textContent = isActive
          ? (window.CHAT_I18N?.active || 'Active')
          : (window.CHAT_I18N?.offline || 'Offline');
      }
      if (avatarWrap) {
        // Rebuild the header avatar with a real presence dot (bottom-trailing corner).
        // Keep the stable .chat-header-avatar-slot class so later header updates can find it.
        const inner = avatarMarkup({ id: entity.id, name, avatar_url: entity.avatar_url, initials: entity.initials });
        avatarWrap.outerHTML =
          `<span class="flex-shrink-0 chat-avatar-presence chat-header-avatar-slot">${inner}${presenceDot(entity && entity.presence)}</span>`;
      }
    }

    // Single source of truth for the sidebar's active-row highlight (ISSUE 2).
    // Works for BOTH row types:
    //   - TEAM/group rows: onclick="loadConversation(id)"           (keyed by conversation id)
    //   - DM/user rows:    onclick="startConversationWithUser(uid)" (keyed by USER id, not conv id)
    // For DM rows we reverse-map the conversation id back to a user id via
    // conversationByUserId (userId -> conversationId, populated in fetchMessages),
    // then match that user's row. Called after every sidebar re-render so the class
    // survives fetchMessages/fetchGroups redraws.
    function applyActiveConversation(conversationId) {
      document.querySelectorAll('#user-list .chat-contact-list-item').forEach(li => li.classList.remove('active'));
      if (!conversationId) return;
      // Team/group rows: onclick loadConversation(id)
      const teamRow = document.querySelector(`#user-list [onclick*="loadConversation(${conversationId})"]`);
      teamRow?.closest('.chat-contact-list-item')?.classList.add('active');
      // DM rows: map conversationId back to a userId, then match startConversationWithUser(userId)
      const userId = Object.keys(conversationByUserId).find(uid => conversationByUserId[uid] === conversationId);
      if (userId) {
        const dmRow = document.querySelector(`#user-list [onclick*="startConversationWithUser(${userId})"]`);
        dmRow?.closest('.chat-contact-list-item')?.classList.add('active');
      }
    }

    // Back-compat shim: keep the old name as a thin wrapper over the single source of truth.
    function updateActiveConversation(conversationId) {
      applyActiveConversation(conversationId);
    }

    // Add user to group
    async function addUserToGroup(groupId, userId) {
      try {
        const data = await makeApiCall(`/dashboard/chat/groups/${groupId}/add-user`, {
          method: 'POST',
          body: JSON.stringify({
            user_id: userId
          })
        });

        console.log('User added to group:', data);
        return data;
      } catch (error) {
        showError('Failed to add user to group');
        console.error('Error adding user to group:', error);
      }
    }

    // Remove user from group
    async function removeUserFromGroup(groupId, userId) {
      try {
        const data = await makeApiCall(`/dashboard/chat/groups/${groupId}/remove-user`, {
          method: 'POST',
          body: JSON.stringify({
            user_id: userId
          })
        });

        console.log('User removed from group:', data);
        return data;
      } catch (error) {
        showError('Failed to remove user from group');
        console.error('Error removing user from group:', error);
      }
    }

    // Exit group
    async function exitGroup(groupId) {
      try {
        const data = await makeApiCall(`/dashboard/chat/groups/${groupId}/exit`, {
          method: 'POST'
        });

        console.log('Exited group:', data);
        
        // Refresh groups list
        await fetchGroups();
        
        return data;
      } catch (error) {
        showError('Failed to exit group');
        console.error('Error exiting group:', error);
      }
    }

    // Delete group
    async function deleteGroup(groupId) {
      if (!confirm('Are you sure you want to delete this group?')) return;

      try {
        const data = await makeApiCall(`/dashboard/chat/groups/${groupId}`, {
          method: 'DELETE'
        });

        console.log('Group deleted:', data);
        
        // Refresh groups list and clear current conversation if it was deleted
        if (currentConversationId === groupId) {
          currentConversationId = null;
        }
        await fetchGroups();
        
        return data;
      } catch (error) {
        showError('Failed to delete group');
        console.error('Error deleting group:', error);
      }
    }

    // Search users
    async function searchUsers(query) {
      if (!query.trim()) return [];

      try {
        const data = await makeApiCall(`/dashboard/chat/users/search?q=${encodeURIComponent(query)}`);
        console.log('Search results:', data);
        return data.users || [];
      } catch (error) {
        showError('Failed to search users');
        console.error('Error searching users:', error);
        return [];
      }
    }

    // Format time ago
    function formatTimeAgo(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMins / 60);
      const diffDays = Math.floor(diffHours / 24);

      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''}`;
      if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''}`;
      if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''}`;
      
      return date.toLocaleDateString();
    }

    // Handle message form submission
    function handleMessageSubmit(event) {
      event.preventDefault();

      if (!window.CHAT_HAS_TEAMMATES) { return; }

      const messageInput = document.querySelector('.message-input');
      const messageText = messageInput?.value?.trim();

      if (messageText && currentConversationId) {
        // Clear typing indicator before sending message
        sendTypingIndicator(false);

        sendMessage(currentConversationId, messageText);
      }
    }

    // Handle search input
    function handleSearch(event) {
      // Prevent event from bubbling and being handled multiple times
      event.stopPropagation();
      event.stopImmediatePropagation();
      
      const query = event.target.value.trim();
      console.log('Search triggered:', query, 'Event type:', event.type);
      
      // Only process actual input changes, not programmatic ones
      if (event.isTrusted === false) {
        console.log('Ignoring programmatic event');
        return;
      }
      
      if (query.length > 2) {
        // Debounce search
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(async () => {
          console.log('Performing search for:', query);
          const users = await searchUsers(query);
          displaySearchResults(users);
        }, 300);
      } else if (query.length === 0) {
        hideSearchResults();
      }
    }

    // Initialize real-time broadcasting with retry mechanism
    function initializeBroadcasting() {
      try {
        console.log('Checking Echo availability...');

        // Only accept a real Echo INSTANCE (has .private/.channel), not the Echo class/constructor.
        const candidate = (typeof window.Echo !== 'undefined' && window.Echo) ? window.Echo : (typeof Echo !== 'undefined' ? Echo : null);
        if (candidate && typeof candidate.private === 'function' && typeof candidate.channel === 'function') {
          echo = candidate;
          console.log('Laravel Echo instance ready.');
          return true;
        }
        console.warn('Laravel Echo instance not available yet - real-time features disabled for now.');
        return false;
      } catch (error) {
        console.error('Failed to initialize broadcasting:', error);
        return false;
      }
    }

    // Retry mechanism for Echo initialization
    function initializeBroadcastingWithRetry(maxRetries = 5, delay = 1000) {
      let retryCount = 0;
      
      function tryInitialize() {
        if (initializeBroadcasting()) {
          return true;
        }
        
        retryCount++;
        if (retryCount < maxRetries) {
          console.log(`Echo not ready, retrying in ${delay}ms... (${retryCount}/${maxRetries})`);
          setTimeout(tryInitialize, delay);
        } else {
          console.error('Failed to initialize Echo after maximum retries');
        }
      }
      
      return tryInitialize();
    }

    // Mark the focused conversation and ensure we're subscribed to its channel.
    // Delegates listener binding to the idempotent subscribeToConversation so the
    // channel is never double-bound (which would render incoming messages twice),
    // and never leaves other channels (background subscriptions must stay alive).
    function setupConversationListeners(conversationId) {
      if (!conversationId) return;
      window.currentConversationChannel = `conversation.${conversationId}`;
      subscribeToConversation(conversationId); // idempotent: binds once, no-op if already subscribed
    }

    // Subscribe to a single conversation channel WITHOUT leaving others (additive).
    // Used to keep ALL of the user's conversations live, even when not focused.
    function subscribeToConversation(conversationId) {
      if (!echo || typeof echo.private !== 'function' || !conversationId) return null;
      if (subscribedConversationIds.has(conversationId)) return null; // already listening (idempotent)
      try {
        const channel = echo.private(`conversation.${conversationId}`);
        // Message created (custom alias + FQCN variants)
        channel.listen('.MessageCreated', (e) => handleIncomingMessage(extractMessage(e)));
        channel.listen('App\\Events\\MessageCreated', (e) => handleIncomingMessage(extractMessage(e)));
        channel.listen('Namu\\WireChat\\Events\\MessageCreated', (e) => handleIncomingMessage(extractMessage(e)));
        // Message deleted
        channel.listen('.MessageDeleted', (e) => handleMessageDeletion(extractMessage(e)));
        channel.listen('App\\Events\\MessageDeleted', (e) => handleMessageDeletion(extractMessage(e)));
        channel.listen('Namu\\WireChat\\Events\\MessageDeleted', (e) => handleMessageDeletion(extractMessage(e)));
        // Typing indicator
        channel.listen('TypingIndicator', (e) => {
          if (e.is_typing) { showTypingIndicator(e.user); } else { hideTypingIndicator(e.user); }
        });
        subscribedConversationIds.add(conversationId);
        return channel;
      } catch (err) {
        console.warn('Failed to subscribe to conversation', conversationId, err);
        return null;
      }
    }

    // Subscribe to every conversation the user participates in (DMs + team groups).
    function subscribeToAllConversations(conversationIds) {
      if (!Array.isArray(conversationIds)) return;
      conversationIds.forEach(id => { if (id) subscribeToConversation(id); });
    }

    // Handle incoming real-time messages
    function handleIncomingMessage(messageData) {
      // Only add message if we're viewing the same conversation
      if (currentConversationId && messageData.conversation_id == currentConversationId) {
        // If full message body exists, use it directly; otherwise fetch by ID
        if (messageData && messageData.body) {
          addMessageToConversation(messageData);
          showMessageNotification(messageData);
        } else {
          loadMessageById(messageData.id).then(message => {
            if (message) {
              addMessageToConversation(message);
              showMessageNotification(message);
            }
          });
        }
      }
      
      // A message arrived. If it's not the focused conversation, the server unread_count
      // will reflect it — just refresh the sidebar (DMs + team groups) to update badges.
      if (!currentConversationId || messageData.conversation_id != currentConversationId) {
        fetchMessages();
        fetchGroups();
      } else {
        fetchMessages();
        // Keep the open conversation's header (presence/avatar) fresh on live activity.
        loadConversationMessages(currentConversationId).then(d => {
          if (d && d.conversation) updateChatHeaderFromConversation(d.conversation);
        }).catch(() => {});
      }
    }

    // Handle message deletions
    function handleMessageDeletion(messageData) {
      const messageElement = document.querySelector(`[data-message-id="${messageData.id}"]`);
      if (messageElement) {
        messageElement.remove();
      }
    }

    function extractMessage(e) {
      if (!e) return e;
      if (e.message) return e.message;
      return e;
    }

    // Global (non-conversation) real-time notifications. Best-effort.
    function setupRealTimeMessaging() {
      if (!echo || typeof echo.channel !== 'function') {
        console.warn('Echo instance not ready; global real-time notifications disabled.');
        return;
      }
      try {
        echo.channel('Web-Message')
          .listen('.WebMessage', (data) => {
            console.log('Received WebMessage:', data);
          })
          .error((error) => {
            console.error('Web-Message channel error:', error);
          });
        console.log('Real-time messaging setup completed');
      } catch (e) {
        console.warn('Failed to set up global real-time messaging:', e);
      }
    }

    // Load a specific message by ID
    async function loadMessageById(messageId) {
      try {
        const data = await makeApiCall(`/dashboard/chat/messages/${messageId}`);
        return data.message;
      } catch (error) {
        console.error('Error loading message:', error);
        return null;
      }
    }

    // Show typing indicator
    function showTypingIndicator(user) {
      if (!user || !user.id || !currentConversationId) return;

      const chatMessages = document.getElementById('chat-messages');
      if (!chatMessages) return;

      // Remove existing typing indicator for this user
      const existingIndicator = document.querySelector(`.typing-indicator[data-user-id="${user.id}"]`);
      if (existingIndicator) {
        existingIndicator.remove();
      }

      // Check if there are any existing typing indicators
      const typingIndicators = document.querySelectorAll('.typing-indicator');
      let indicatorText = '';
      const safeName = escapeHtml(user.name || '');

      if (typingIndicators.length === 0) {
        // First typing indicator
        indicatorText = safeName + ' ' + (window.CHAT_I18N?.isTyping || 'is typing...');
      } else {
        // Multiple users typing - show combined indicator
        const typingUsers = [];
        typingIndicators.forEach(indicator => {
          const userId = indicator.getAttribute('data-user-id');
          if (userId && userId !== user.id.toString()) {
            typingUsers.push(indicator.getAttribute('data-user-name'));
          }
        });
        typingUsers.push(safeName);

        if (typingUsers.length === 1) {
          indicatorText = typingUsers[0] + ' ' + (window.CHAT_I18N?.isTyping || 'is typing...');
        } else if (typingUsers.length === 2) {
          indicatorText = typingUsers.join(' ' + (window.CHAT_I18N?.and || 'and') + ' ') + ' ' + (window.CHAT_I18N?.areTyping || 'are typing...');
        } else {
          indicatorText = typingUsers[0] + ' ' + (window.CHAT_I18N?.and || 'and') + ' ' + (typingUsers.length - 1) + ' ' + (window.CHAT_I18N?.others || 'others') + ' ' + (window.CHAT_I18N?.areTyping || 'are typing...');
        }
      }

      // Add new typing indicator
      const typingHtml = `
        <li class="chat-message typing-indicator" data-user-id="${user.id}" data-user-name="${escapeHtml(user.name || '')}">
          <div class="d-flex overflow-hidden">
            <div class="user-avatar flex-shrink-0 me-4">
              <div class="avatar avatar-sm">
                <img src="/vuexy/assets/img/avatars/4.png" alt="Avatar" class="rounded-circle" />
              </div>
            </div>
            <div class="chat-message-wrapper flex-grow-1">
              <div class="chat-message-text">
                <div class="typing-dots">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <small class="text-muted">${indicatorText}</small>
              </div>
            </div>
          </div>
        </li>
      `;

      chatMessages.insertAdjacentHTML('beforeend', typingHtml);
      scrollToBottom();

      // Remove typing indicator after 3 seconds (in case no "stop typing" event is received)
      setTimeout(() => {
        const indicator = document.querySelector(`.typing-indicator[data-user-id="${user.id}"]`);
        if (indicator) {
          indicator.remove();

          // Update other typing indicators if there are multiple users
          updateTypingIndicators();
        }
      }, 3000);
    }

    // Hide typing indicator
    function hideTypingIndicator(user) {
      if (!user || !user.id || !currentConversationId) return;

      const chatMessages = document.getElementById('chat-messages');
      if (!chatMessages) return;

      // Remove existing typing indicator for this user
      const existingIndicator = document.querySelector(`.typing-indicator[data-user-id="${user.id}"]`);
      if (existingIndicator) {
        existingIndicator.remove();
        updateTypingIndicators();
      }
    }

    // Show notification for new messages
    function showMessageNotification(message) {
      // Only show notification if the user is not actively viewing the chat
      if (document.hidden) {
        // Browser notification (if permission granted)
        if (Notification.permission === 'granted') {
          new Notification('New Message', {
            body: message.body || 'New message received',
            icon: '/vuexy/assets/img/avatars/1.png'
          });
        }
      }
      
      // Play notification sound
      playNotificationSound();
    }

    // Play notification sound
    function playNotificationSound() {
      try {
        const audio = new Audio('/vuexy/assets/audio/notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Could not play notification sound'));
      } catch (error) {
        // Notification sound not available
      }
    }

    // Send typing indicator
    function sendTypingIndicator(isTyping) {
      if (echo && currentConversationId && window.currentConversationChannel) {
        // Dispatch typing indicator event to backend for broadcasting
        fetch('/dashboard/chat/typing', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            conversation_id: currentConversationId,
            is_typing: isTyping
          })
        }).catch(error => {
          console.log('Typing indicator error (non-critical):', error);
        });
      }
    }

    // Auto-refresh messages periodically
    function startMessagePolling(interval = 15000) {
      if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
      }

      messagePollingInterval = setInterval(async () => {
        try {
          // 1) Refresh the sidebar: unread counts + previews (fetchMessages),
          //    team rows (fetchGroups), and USER ROWS with fresh presence dots
          //    (fetchUsers re-renders displayUsers, which reads each user's live
          //    `presence`). background=true skips the loading spinner so this is
          //    silent. This is what makes online/offline update live, not just on refresh.
          await fetchMessages();
          await fetchGroups();
          await fetchUsers(true);
          // 2) Refresh the OPEN conversation: its messages AND its header (name/avatar/presence).
          if (currentConversationId) {
            const data = await loadConversationMessages(currentConversationId);
            if (data && Array.isArray(data.messages)) {
              displayConversationMessages(data.messages);
            }
            // Re-render the header from fresh conversation/participant data so online/offline + avatar update.
            if (data && data.conversation) {
              updateChatHeaderFromConversation(data.conversation);
            }
            // Keep the active-row highlight after fetchMessages/fetchGroups re-render the sidebar.
            if (typeof applyActiveConversation === 'function') {
              applyActiveConversation(currentConversationId);
            }
          }
        } catch (e) {
          // silent — polling is best-effort
        }
      }, interval);
    }

    // Stop message polling
    function stopMessagePolling() {
      if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
        messagePollingInterval = null;
      }
    }

    // Initialize chat system
    async function initializeChat() {
      try {
        // Disable any conflicting chat scripts
        if (window.searchChatContacts) {
          window.searchChatContacts = function() {}; // Override conflicting function
        }

        // Fetch current user

        // Fetch current user info
        await fetchCurrentUser();

        // Load initial data
        const [conversations, groups] = await Promise.all([
          fetchMessages(),
          fetchGroups(),
          fetchUsers()
        ]);

        // Subscribe to ALL conversations (DMs + team groups) so messages arrive
        // live even when that conversation isn't the focused one.
        const allConversationIds = [
          ...((conversations || []).map(c => c.id)),
          ...((groups || []).map(g => g.id)),
        ];
        // Deferred slightly so Echo (loaded async from the layout) is ready.
        setTimeout(() => subscribeToAllConversations(allConversationIds), 600);

        // Set up event listeners
        setupEventListeners();

        // Presence: ping only on genuine user activity (not background polls).
        setupPresenceHeartbeat();

        // Start auto-refresh. Real-time broadcasting (Pusher) gives instant updates
        // WHEN it delivers, but it has proven unreliable here — so the poll is the
        // reliable floor for unread counts and presence and must stay reasonably tight
        // regardless. Each tick is only a few light JSON calls, so 15s is a good balance
        // between "feels live" and not over-fetching.
        broadcastingEnabled = initializeBroadcasting();
        if (broadcastingEnabled) {
          setupRealTimeMessaging();
        }
        startMessagePolling(15000); // Every 15s (unread + presence + open thread)

        console.log('Chat system initialized successfully');

        // Auto-open last conversation and rejoin channel after refresh
        const lastConvIdStr = localStorage.getItem(LS_KEYS.lastConversationId);
        const lastConvId = lastConvIdStr ? parseInt(lastConvIdStr) : null;
        if (lastConvId && window.CHAT_HAS_TEAMMATES) {
          // GV-7375: the stored id may point at a DM with a member who has since
          // been removed from the team. Only restore it if the server still lists
          // it (or it is one of my team groups); otherwise forget it.
          const stillListed =
            (conversations || []).some(c => c.id === lastConvId) ||
            (groups || []).some(g => g.id === lastConvId);
          if (stillListed) {
            loadConversation(lastConvId);
          } else {
            try { localStorage.removeItem(LS_KEYS.lastConversationId); } catch (e) {}
          }
        }
      } catch (error) {
        console.error('Failed to initialize chat system:', error);
        showError('Failed to initialize chat system');
      }
    }

    // Listen for Echo ready event
    window.addEventListener('EchoReady', function() {
      console.log('EchoReady received — (re)initializing real-time.');
      if (initializeBroadcasting()) {
        setupRealTimeMessaging();
        // Re-subscribe the conversation the user currently has open, if any.
        if (typeof currentConversationId !== 'undefined' && currentConversationId) {
          try { setupConversationListeners(currentConversationId); } catch (e) {
            console.warn('Re-subscribe after EchoReady failed:', e);
          }
        }
        // Re-subscribe every known conversation now that Echo is ready.
        subscribeToAllConversations([
          ...subscribedConversationIds,
          currentConversationId,
        ].filter(Boolean));
        startMessagePolling(15000);
      }
    });

    // Set up event listeners
    function setupEventListeners() {
      // Message form submission
      const messageForm = document.querySelector('.form-send-message');
      if (messageForm) {
        messageForm.addEventListener('submit', handleMessageSubmit);
      }

      // Add typing indicator to message input
      const messageInput = document.querySelector('.message-input');
      if (messageInput) {
        let typingTimer = null;
        let isTyping = false;

        messageInput.addEventListener('input', () => {
          const messageText = messageInput.value.trim();

          if (messageText.length > 0 && !isTyping) {
            // User started typing
            isTyping = true;
            sendTypingIndicator(true);

            // Clear previous timer
            clearTimeout(typingTimer);

            // Set timer to stop typing indicator after 2 seconds of no input
            typingTimer = setTimeout(() => {
              isTyping = false;
              sendTypingIndicator(false); // Send stop typing event
            }, 2000);
          } else if (messageText.length === 0 && isTyping) {
            // User cleared input while typing
            isTyping = false;
            clearTimeout(typingTimer);
            sendTypingIndicator(false); // Send stop typing event
          } else if (messageText.length > 0 && isTyping) {
            // User is still typing, reset timer
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
              isTyping = false;
              sendTypingIndicator(false); // Send stop typing event
            }, 2000);
          }
        });
      }

      // Search input - ensure it's editable and working
      let searchInput = document.querySelector('.chat-search-input');
      if (searchInput) {
        console.log('Found search input:', searchInput);
        
        // Remove any existing event listeners by cloning the element
        const newInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newInput, searchInput);
        searchInput = newInput; // Update reference
        
        // Make sure the input is not disabled or readonly
        searchInput.removeAttribute('disabled');
        searchInput.removeAttribute('readonly');
        searchInput.removeAttribute('contenteditable');
        
        // Ensure it's focusable and editable
        searchInput.style.pointerEvents = 'auto';
        searchInput.style.userSelect = 'text';
        searchInput.tabIndex = 0;
        
        // Add a single input event listener with proper debouncing
        let searchTimeout = null;
        let lastValue = '';
        
        const debouncedSearch = function(event) {
          const currentValue = event.target.value.trim();
          
          // Only process if value actually changed
          if (currentValue === lastValue) {
            return;
          }
          lastValue = currentValue;
          
          console.log('Search input changed to:', currentValue);
          
          // Clear previous timeout
          if (searchTimeout) {
            clearTimeout(searchTimeout);
          }
          
          if (currentValue.length > 2) {
            searchTimeout = setTimeout(async () => {
              console.log('Performing search for:', currentValue);
              const users = await searchUsers(currentValue);
              displaySearchResults(users);
            }, 300);
          } else if (currentValue.length === 0) {
            hideSearchResults();
          }
        };
        
        // Use only input event to avoid duplicates
        searchInput.addEventListener('input', debouncedSearch, { passive: false });
        
        // Test that the input is working
        searchInput.addEventListener('focus', () => {
          console.log('Search input focused - ready for typing');
        });
        
        // Prevent any other scripts from interfering
        searchInput.setAttribute('data-custom-search', 'true');
        
        console.log('Search input initialized successfully');
        
      } else {
        console.error('Search input not found');
      }

      // Handle window visibility change to pause/resume polling
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          stopMessagePolling();
        } else {
          startMessagePolling();
        }
      });

      // Handle page unload
      window.addEventListener('beforeunload', () => {
        stopMessagePolling();
      });
    }

    // Initialize when page loads - with delay to override other scripts
    document.addEventListener('DOMContentLoaded', () => {
      // Delay to ensure Echo is loaded from admin layout
      setTimeout(initializeChat, 500);
    });
    
    // Also try immediate initialization in case DOMContentLoaded already fired
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initializeChat, 500);
      });
    } else {
      setTimeout(initializeChat, 500);
    }

    // Expose functions globally for onclick handlers
    window.loadConversation = loadConversation;
    window.sendMessage = sendMessage;
    window.createGroup = createGroup;
    window.addUserToGroup = addUserToGroup;
    window.removeUserFromGroup = removeUserFromGroup;
    window.exitGroup = exitGroup;
    window.startConversationWithUser = startConversationWithUser;
    window.hideSearchResults = hideSearchResults;
    window.deleteGroup = deleteGroup;
    window.searchUsers = searchUsers;
    window.onImgError = onImgError;
    window.applyActiveConversation = applyActiveConversation;


  </script>
@stop