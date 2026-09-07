@php
$user = auth()->user();
$notifications = collect();
$all = 0;

if($user)
{
  $notifications = $user->unreadNotifications()->latest()->get();
  $all = $notifications->count();
}
@endphp


<!-- Notification -->
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-1 me-md-2">
  <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow p-1 p-md-2" href="javascript:void(0);"
    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
    <span class="position-relative">
      <i class="ti ti-bell ti-md"></i>
      @if ($all > 0)
      <span 
        class="badge badge-notifications bg-danger rounded-circle d-flex justify-content-center align-items-center flex-shrink-0 w-px-20 h-px-20">
        {{ $all }}
      </span>
      @endif
    </span>
  </a>

  <!-- Notification Dropdown -->
  <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 280px; max-width: 400px;">
    @if ($all > 0)
      <li class="dropdown-menu-header border-bottom">
        <div class="dropdown-header d-flex align-items-center py-3 px-3">
          <h6 class="mb-0 me-auto" style="text-transform:capitalize;">{{ __('notification') }}</h6>
          <div class="d-flex align-items-center h6 mb-0">
            <span class="badge bg-label-primary me-2" id="new-count">{{ $all }} {{ $l == 'en' ? 'New': 'جديد' }}</span>

           <a href="javascript:void(0)" class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all p-1"
              data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.mark_all_as_read') }}"
              onclick="markAllAsRead()">
            <i class="ti ti-mail-opened text-heading"></i>
            </a>
          </div>
        </div>
      </li>
    @endif

    <!-- Notification List -->
    <li class="dropdown-notifications-list scrollable-container" style="max-height: 300px; overflow-y: auto;">
      <ul id="notification-list" class="list-group list-group-flush">
        @if ($notifications->isEmpty())
          <li class="list-group-item list-group-item-action dropdown-notifications-item text-center py-3">
            <span class="text-muted">{{ __('admin.NoNotifications') }}</span>
          </li>
        @else
          @foreach ($notifications as $notification)
            <li class="list-group-item list-group-item-action dropdown-notifications-item py-3 px-3" onclick="markAsRead('{{ $notification->id }}')" style="cursor: pointer;">
                <h6 class="media-heading mb-1" style="font-size: 0.875rem; line-height: 1.3;">{{ $notification->data['message'] ?? '-' }}</h6>
                <small class="text-muted" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</small>
            </li>
          @endforeach
        @endif

        {{-- @if ($messages)
          <li class="list-group-item list-group-item-action dropdown-notifications-item">
            <a href="/dashboard/inbox">
              <h6 class="media-heading">
                {{ __('admin.YouHve') }} <span id="notif-plus-count1">{{ $messages }}</span>
                {{ __('admin.UnreadM') }}
              </h6>
            </a>
          </li>
        @endif --}}
        
        {{-- @role('Registered')
         @if ($notifications)
           <li class="list-group-item list-group-item-action dropdown-notifications-item">
            <h6 class="media-heading">{{ $notifications->data['message'] ?? 'New Notification' }}</h6>
           </li>
         @endif
        @endrole --}}

        @hasanyrole('Admin|organizationManager')
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

<script>
let currentPage = 1;

function markAllAsRead() {
    fetch('/dashboard/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // #new-count only exists when the page rendered with unread
            // notifications; guard so the badge cleanup below always runs.
            const countElement = document.getElementById('new-count');
            if (countElement) {
                countElement.textContent = `0 ${countElement.textContent.includes('New') ? 'New' : 'جديد'}`;
            }
            const badge = document.querySelector('.badge-notifications');
            if (badge) badge.remove();
            document.querySelectorAll('.dropdown-notifications-item:not(:has(a[href="/dashboard/inbox"])):not(:has(a[href="/dashboard/mymsg"]))').forEach(item => item.remove());
            document.getElementById('notification-list').innerHTML = `
                <li class="list-group-item list-group-item-action dropdown-notifications-item text-center">
                    <span class="text-muted">{{ __('admin.NoNotifications') }}</span>
                </li>
                ${document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/inbox"])') ? document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/inbox"])').outerHTML : ''}
                ${document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/mymsg"])') ? document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/mymsg"])').outerHTML : ''}`;
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAsRead(notificationId) {
    fetch(`/dashboard/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationItem = document.querySelector(`[onclick="markAsRead('${notificationId}')"]`);
            if (notificationItem) notificationItem.remove();
            const countElement = document.getElementById('new-count');
            let count = 0;
            if (countElement) {
                count = Math.max(parseInt(countElement.textContent) - 1, 0);
                countElement.textContent = `${count} ${countElement.textContent.includes('New') ? 'New' : 'جديد'}`;
            }
            const badge = document.querySelector('.badge-notifications');
            if (count === 0 && badge) badge.remove();
            if (!document.querySelector('.dropdown-notifications-item:not([class*="text-center"]):not(:has(a[href="/dashboard/inbox"])):not(:has(a[href="/dashboard/mymsg"]))')) {
                document.getElementById('notification-list').innerHTML = `
                    <li class="list-group-item list-group-item-action dropdown-notifications-item text-center">
                        <span class="text-muted">{{ __('admin.NoNotifications') }}</span>
                    </li>
                    ${document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/inbox"])') ? document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/inbox"])').outerHTML : ''}
                    ${document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/mymsg"])') ? document.querySelector('.dropdown-notifications-item:has(a[href="/dashboard/mymsg"])').outerHTML : ''}`;
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function loadMoreNotifications() {
    currentPage++;
    fetch(`/dashboard/notifications?page=${currentPage}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const notificationList = document.getElementById('notification-list');
        const loadMoreButton = document.getElementById('load-more');
        
        if (data.notifications.length > 0) {
            data.notifications.forEach(notification => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action dropdown-notifications-item';
                li.setAttribute('onclick', `markAsRead('${notification.id}')`);
                li.innerHTML = `
                    <h6 class="media-heading">${notification.data.message || '-'}</h6>
                    <small class="text-muted">${notification.created_at_diff}</small>`;
                notificationList.insertBefore(li, loadMoreButton);
            });
        }
        
        if (!data.has_more) {
            loadMoreButton.remove();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>


