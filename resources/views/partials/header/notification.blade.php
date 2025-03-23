@php
$user = auth()->user();
$notifications = collect();
$all = 0;

if ($user && $user->hasRole('Registered')) {
    $notifications = $user->unreadNotifications()->latest()->take(5)->get();
    $all = $notifications->count();
}
@endphp


<!-- Notification -->
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
  <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);"
    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
    <span class="position-relative">
      <i class="ti ti-bell ti-md"></i>
      {{-- @if ($all > 0) --}}
      <div id="notification-count-container">

      </div>
      {{-- @endif --}}
    </span>
  </a>

  <!-- Notification Dropdown -->
  <ul class="dropdown-menu dropdown-menu-end p-0">
    {{-- @if ($all > 0) --}}
      <li class="dropdown-menu-header border-bottom">
        <div class="dropdown-header d-flex align-items-center py-3">
          <h6 class="mb-0 me-auto" style="text-transform:capitalize;">Notification</h6>
          <div class="d-flex align-items-center h6 mb-0">
            <span class="badge bg-label-primary me-2" id="new-count">0 {{ $l == 'en' ? 'New': 'جديد' }}</span>

            <a href="javascript:void(0)" class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all"
              data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read"><i
                class="ti ti-mail-opened text-heading"></i></a>
          </div>
        </div>
      </li>
    {{-- @endif --}}

    <!-- Notification List -->
    <li class="dropdown-notifications-list scrollable-container">
      <ul id="notification-list" class="list-group list-group-flush">
        {{-- @role('Registered')
        @if ($notifications->isEmpty())
          <li class="list-group-item list-group-item-action dropdown-notifications-item text-center">
            <span class="text-muted">No new notifications</span>
          </li>
        @else
          @foreach ($notifications as $notification)
            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <h6 class="media-heading">{{ $notification->data['message'] ?? 'New Notification' }}</h6>
            </li>
          @endforeach
        @endif
        @endrole --}}

        @if ($messages)
          <li class="list-group-item list-group-item-action dropdown-notifications-item">
            <a href="/dashboard/inbox">
              <h6 class="media-heading">
                {{ __('admin.YouHve') }} <span id="notif-plus-count1">{{ $messages }}</span>
                {{ __('admin.UnreadM') }}
              </h6>
            </a>
          </li>
        @endif
        
        {{-- @role('Registered')
         @if ($notifications)
           <li class="list-group-item list-group-item-action dropdown-notifications-item">
            <h6 class="media-heading">{{ $notifications->data['message'] ?? 'New Notification' }}</h6>
           </li>
         @endif
        @endrole --}}

        @hasanyrole('Admin|Manager')
          @if ($msgs)
            <li class="list-group-item list-group-item-action dropdown-notifications-item">
              <a class="d-flex justify-content-between" href="/dashboard/mymsg">
                <div class="media d-flex align-items-center">
                  <div class="media-left pr-0">
                    <div class="mr-1"><i class="bx bxs-envelope bx-md"></i></div>
                  </div>
                  <div>
                    <h6 class="">{{ __('admin.YouHve') }} <span
                        id="notif-plus-count2">{{ $msgs }}</span> {{ __('admin.UnreadCM') }}</h6>
                  </div>
                </div>
              </a>
            </li>
          @endif
        @endhasanyrole

        {{-- <li class="list-group-item list-group-item-action dropdown-notifications-item">
           <div class="d-flex">
            <div class="flex-shrink-0 me-3">
              <div class="avatar">
                <img src="/vuexy/assets/img/avatars/1.png" alt class="rounded-circle" />
              </div>
            </div>
            <div class="flex-grow-1">
              <h6 class="small mb-1">Congratulation Lettie 🎉</h6>
              <small class="mb-1 d-block text-body">Won the monthly best seller gold badge</small>
              <small class="text-muted">1h ago</small>
            </div>
            <div class="flex-shrink-0 dropdown-notifications-actions">
              <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
              <a href="javascript:void(0)" class="dropdown-notifications-archive"><span class="ti ti-x"></span></a>
            </div>
          </div> 
        </li> --}}
      </ul>
    </li>

    {{-- @if ($messages)
      <li class="border-top">
        <div class="d-grid p-4">
          <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
            <small class="align-middle">View all notifications</small>
          </a>
        </div>
      </li>
    @endif --}}

  </ul>
</li>
<!--/ Notification -->



