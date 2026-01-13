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
                <h5 class="text-primary mb-0">Users</h5>
              </li>
              <li class="chat-contact-list-item chat-list-item-0">
                <h6 class="text-muted mb-0">Loading users...</h6>
              </li>
            </ul>
            
            <!-- User Search Results -->
            <ul class="list-unstyled chat-contact-list mb-0 py-2" id="search-results" style="display: none;">
              <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
                <h5 class="text-primary mb-0">Search Results</h5>
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
                  <div class="flex-shrink-0 avatar avatar-online">
                    <img src="/vuexy/assets/img/avatars/4.png" alt="Avatar" class="rounded-circle"
                      data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-right" />
                  </div>
                  <div class="chat-contact-info flex-grow-1 ms-4">
                    <h6 class="m-0 fw-normal">Select a conversation</h6>
                    <small class="user-status text-body">Start chatting by searching for users</small>
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
                  <small class="text-muted">Select a conversation to start chatting</small>
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
    // Global variables
    let currentConversationId = null;
    let currentUser = null;
    let messagePollingInterval = null;
    let typingTimeout = null;
    let echo = null; // For Laravel Echo (real-time broadcasting)
    let lastMessageByUserId = {};
    let broadcastingEnabled = false;
    let lastReadByConversation = {};
    let unreadCounts = {};
    let conversationByUserId = {};
    const LS_KEYS = {
      lastConversationId: 'chat.lastConversationId',
      lastRead: 'chat.lastReadByConversation',
      unread: 'chat.unreadCounts'
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

    function loadPersistedState() {
      try {
        const lr = localStorage.getItem(LS_KEYS.lastRead);
        lastReadByConversation = lr ? JSON.parse(lr) : {};
      } catch (e) { lastReadByConversation = {}; }
      try {
        const uc = localStorage.getItem(LS_KEYS.unread);
        unreadCounts = uc ? JSON.parse(uc) : {};
      } catch (e) { unreadCounts = {}; }
    }

    function persistState() {
      try { localStorage.setItem(LS_KEYS.lastRead, JSON.stringify(lastReadByConversation)); } catch(e) {}
      try { localStorage.setItem(LS_KEYS.unread, JSON.stringify(unreadCounts)); } catch(e) {}
    }

    function setLastRead(conversationId, dateStr) {
      if (!conversationId) return;
      lastReadByConversation[conversationId] = dateStr || new Date().toISOString();
      persistState();
    }

    function incrementUnread(conversationId) {
      if (!conversationId) return;
      unreadCounts[conversationId] = (unreadCounts[conversationId] || 0) + 1;
      persistState();
      // Update badges without refetching
      refreshUserListUnreadBadges();
    }

    function clearUnread(conversationId) {
      if (!conversationId) return;
      unreadCounts[conversationId] = 0;
      persistState();
      refreshUserListUnreadBadges();
    }

    function refreshUserListUnreadBadges() {
      const items = document.querySelectorAll('#user-list .chat-contact-list-item a');
      items.forEach(a => {
        const onclick = a.getAttribute('onclick') || '';
        const match = onclick.match(/startConversationWithUser\((\d+),/);
        const userId = match ? parseInt(match[1]) : null;
        if (!userId) return;
        const convId = conversationByUserId[userId];
        const count = convId ? (unreadCounts[convId] || 0) : 0;
        const meta = a.querySelector('.chat-contact-meta');
        if (meta) {
          if (count > 0) {
            meta.innerHTML = `<span class="badge bg-danger rounded-pill">${count}</span>`;
          } else {
            meta.innerHTML = '<i class="ti ti-message-circle-plus text-primary"></i>';
          }
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

    // Fetch current user info
    async function fetchCurrentUser() {
      try {
        const data = await makeApiCall('/dashboard/chat/messages');
        currentUser = { id: data.user_id };
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

        // Reconcile unread counts with last read timestamps
        (data.conversations || []).forEach(conversation => {
          const last = (conversation.messages || [])[0];
          if (!last) return;
          const lastRead = lastReadByConversation[conversation.id];
          if (lastRead) {
            const msgTime = new Date(last.created_at).getTime();
            const readTime = new Date(lastRead).getTime();
            if (msgTime <= readTime) {
              unreadCounts[conversation.id] = 0;
            }
          }
        });
        persistState();

        displayConversations(data.conversations);
        refreshUserListUnreadBadges();
        return data.conversations;
      } catch (error) {
        showError('Failed to fetch conversations');
        console.error('Error fetching conversations:', error);
      }
    }

    // Fetch and display users list
    async function fetchUsers() {
      try {
        const userList = document.getElementById('user-list');
        showLoading(userList);

        const data = await makeApiCall('/dashboard/chat/users');
        console.log('Fetched users:', data);

        displayUsers(data.users);
        return data.users;
      } catch (error) {
        showError('Failed to fetch users');
        console.error('Error fetching users:', error);
      }
    }

    // Display users in the user list
    function displayUsers(users) {
      const userList = document.getElementById('user-list');

      if (!users || users.length === 0) {
        userList.innerHTML = `
          <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
            <h5 class="text-primary mb-0">Users</h5>
          </li>
          <li class="chat-contact-list-item chat-list-item-0">
            <h6 class="text-muted mb-0">No users available</h6>
          </li>
        `;
        return;
      }

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
          <h5 class="text-primary mb-0">Users <span class="badge bg-primary ms-2">${users.length}</span></h5>
        </li>
      `;

      users.forEach(user => {
        const avatarUrl = resolveAvatarUrl(user);
        const lastMsg = lastMessageByUserId[user.id]?.body || 'No messages yet';

        const convId = conversationByUserId[user.id];
        const count = convId ? (unreadCounts[convId] || 0) : 0;
        html += `
          <li class="chat-contact-list-item mb-1">
            <a class="d-flex align-items-center" href="#" onclick="startConversationWithUser(${user.id}, '${escapeHtml(user.name || '')}', '${avatarUrl}')">
              <div class="flex-shrink-0 avatar">
                <img src="${avatarUrl}" alt="Avatar" class="rounded-circle" onerror="this.onerror=null;this.src='/vuexy/assets/img/avatars/${(user.id % 12) + 1}.png'" />
              </div>
              <div class="chat-contact-info flex-grow-1 ms-4">
                <h6 class="chat-contact-name text-truncate m-0 fw-normal">${escapeHtml(user.name || '')}</h6>
                <small class="chat-contact-status text-truncate">${escapeHtml(lastMsg)}</small>
              </div>
              <div class="chat-contact-meta">
                ${count > 0 ? `<span class="badge bg-danger rounded-pill">${count}</span>` : `<i class="ti ti-message-circle-plus text-primary"></i>`}
              </div>
            </a>
          </li>
        `;
      });

      userList.innerHTML = html;
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
            <h5 class="text-primary mb-0">Chats</h5>
          </li>
          <li class="chat-contact-list-item chat-list-item-0">
            <h6 class="text-muted mb-0">No Conversations Found</h6>
          </li>
        `;
        return;
      }

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
          <h5 class="text-primary mb-0">Chats</h5>
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
        const messageBody = lastMessage?.body || 'No messages yet';
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

    // Display groups in chat list (merge with messages or separate section)
    function displayGroups(groups) {
      if (!groups || groups.length === 0) {
        return;
      }

      console.log('Available groups:', groups);
      // Groups are typically shown in the same list as conversations
      // This function can be used to add group-specific functionality
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

        // Setup real-time listeners for this conversation
        setupConversationListeners(conversationId);

        // Fetch conversation data and messages
        const convoData = await loadConversationMessages(conversationId);
        const messages = convoData?.messages || [];
        
        // Update chat header with conversation details
        updateChatHeaderFromConversation(convoData?.conversation);
        
        // Display messages
        displayConversationMessages(messages);
        // Mark as read, clear unread, persist last open conversation
        setLastRead(conversationId, new Date().toISOString());
        clearUnread(conversationId);
        try { localStorage.setItem(LS_KEYS.lastConversationId, String(conversationId)); } catch(e) {}
        
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
        messagesContainer.innerHTML = `
          <li class="text-center p-4">
            <small class="text-muted">No messages in this conversation yet</small>
          </li>
        `;
        return;
      }

      let html = '';
      
      messages.forEach(message => {
        const isCurrentUser = message.sendable_id === currentUser?.id;
        const messageClass = isCurrentUser ? 'chat-message-right' : '';
        const avatarSrc = isCurrentUser ? '/vuexy/assets/img/avatars/1.png' : '/vuexy/assets/img/avatars/4.png';
        const senderName = isCurrentUser ? 'You' : getDisplayName(message.sendable || {});
        const timeFormatted = formatMessageTime(message.created_at);
        
        html += `
          <li class="chat-message ${messageClass}" data-message-id="${message.id}">
            <div class="d-flex overflow-hidden">
              ${!isCurrentUser ? `
                <div class="user-avatar flex-shrink-0 me-4">
                  <div class="avatar avatar-sm">
                    <img src="${avatarSrc}" alt="Avatar" class="rounded-circle" />
                  </div>
                </div>
              ` : ''}
              
              <div class="chat-message-wrapper flex-grow-1">
                <div class="chat-message-text">
                  <p class="mb-0">${escapeHtml(message.body)}</p>
                </div>
                <div class="${isCurrentUser ? 'text-end' : ''} text-muted mt-1">
                  ${isCurrentUser ? '<i class="ti ti-checks ti-16px text-success me-1"></i>' : ''}
                  <small>${timeFormatted}</small>
                </div>
              </div>
              
              ${isCurrentUser ? `
                <div class="user-avatar flex-shrink-0 ms-4">
                  <div class="avatar avatar-sm">
                    <img src="${avatarSrc}" alt="Avatar" class="rounded-circle" />
                  </div>
                </div>
              ` : ''}
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
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
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
      const isCurrentUser = message.sendable_id === currentUser?.id;
      const messageClass = isCurrentUser ? 'chat-message-right' : '';
      const avatarSrc = isCurrentUser ? '/vuexy/assets/img/avatars/1.png' : '/vuexy/assets/img/avatars/4.png';
      const timeFormatted = formatMessageTime(message.created_at);
      
      const messageHtml = `
        <li class="chat-message ${messageClass}">
          <div class="d-flex overflow-hidden">
            ${!isCurrentUser ? `
              <div class="user-avatar flex-shrink-0 me-4">
                <div class="avatar avatar-sm">
                  <img src="${avatarSrc}" alt="Avatar" class="rounded-circle" />
                </div>
              </div>
            ` : ''}
            
            <div class="chat-message-wrapper flex-grow-1">
              <div class="chat-message-text">
                <p class="mb-0">${escapeHtml(message.body)}</p>
              </div>
              <div class="${isCurrentUser ? 'text-end' : ''} text-muted mt-1">
                ${isCurrentUser ? '<i class="ti ti-checks ti-16px text-success me-1"></i>' : ''}
                <small>${timeFormatted}</small>
              </div>
            </div>
            
            ${isCurrentUser ? `
              <div class="user-avatar flex-shrink-0 ms-4">
                <div class="avatar avatar-sm">
                  <img src="${avatarSrc}" alt="Avatar" class="rounded-circle" />
                </div>
              </div>
            ` : ''}
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
        chatStatus.textContent = 'Online';
      }
    }

    function updateChatHeaderFromConversation(conversation) {
      if (!conversation) return;
      if (conversation.type === 'private') {
        const other = (conversation.participants || []).find(p => p.participantable_id !== currentUser?.id);
        const participant = other?.participantable || {};
        const name = getDisplayName(participant);
        const avatarUrl = resolveAvatarUrl({ id: participant.id, img_url: participant.img_url });
        updateChatHeaderWithUser(name, avatarUrl);
        return;
      }
      updateChatHeader(conversation.id);
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
        
        // Clear input
        const messageInput = document.querySelector('.message-input');
        if (messageInput) {
          messageInput.value = '';
        }
        
        // If this was a new conversation, update the conversation list
        if (data.conversation && !conversationId) {
          await fetchMessages();
          loadConversation(data.conversation.id);
        } else {
          // Refresh the current conversation to replace temp message with real one
          if (conversationId) {
            await loadConversationMessages(conversationId);
          }
        }
        
        return data;
      } catch (error) {
        showError('Failed to send message');
        console.error('Error sending message:', error);
        
        // Remove the temporary message on error
        const tempMessageEl = tempMessage ? document.querySelector(`[data-message-id="${tempMessage.id}"]`) : null;
        if (tempMessageEl) {
          tempMessageEl.remove();
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
            <h5 class="text-primary mb-0">Search Results</h5>
          </li>
          <li class="chat-contact-list-item">
            <h6 class="text-muted mb-0">No users found</h6>
          </li>
        `;
        searchResults.style.display = 'block';
        if (userList) userList.style.display = 'none';
        return;
      }

      let html = `
        <li class="chat-contact-list-item chat-contact-list-item-title mt-0">
          <h5 class="text-primary mb-0">Search Results</h5>
          <button class="btn btn-sm btn-text-secondary float-end" onclick="hideSearchResults()">
            <i class="ti ti-x"></i>
          </button>
        </li>
      `;

      users.forEach(user => {
        const avatarUrl = resolveAvatarUrl(user);
        html += `
          <li class="chat-contact-list-item mb-1">
            <a class="d-flex align-items-center" href="#" onclick="startConversationWithUser(${user.id}, '${escapeHtml(user.name || '')}', '${avatarUrl}')">
              <div class="flex-shrink-0 avatar">
                <img src="${avatarUrl}" alt="Avatar" class="rounded-circle" onerror="this.onerror=null;this.src='/vuexy/assets/img/avatars/${(user.id % 12) + 1}.png'" />
              </div>
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

    function resolveAvatarUrl(user) {
      if (user && user.img_url) {
        if (user.img_url.startsWith('http://') || user.img_url.startsWith('https://')) {
          return user.img_url;
        }
        return `/dashboard/uploads/photos/${user.id}/${encodeURIComponent(user.img_url)}`;
      }
      const id = user && user.id ? user.id : 1;
      return `/vuexy/assets/img/avatars/${(id % 12) + 1}.png`;
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
    async function startConversationWithUser(userId, userName, avatarUrl) {
      try {
        // Hide search results
        hideSearchResults();
        
        // Show loading in messages area
        const messagesContainer = document.getElementById('chat-messages');
        showLoading(messagesContainer);
        
        // Create or get conversation
        const conversation = await createOrGetConversation(userId);
        
        if (conversation) {
          // Update chat header
          updateChatHeaderWithUser(userName, avatarUrl);
          
          // Load conversation messages
          currentConversationId = conversation.id;
          
          // Setup real-time listeners for this conversation
          setupConversationListeners(conversation.id);
          
          const convoData = await loadConversationMessages(conversation.id);
          updateChatHeaderFromConversation(convoData?.conversation);
          displayConversationMessages(convoData?.messages || []);
          setLastRead(conversation.id, new Date().toISOString());
          clearUnread(conversation.id);
          try { localStorage.setItem(LS_KEYS.lastConversationId, String(conversation.id)); } catch(e) {}
          
          // Refresh conversations list
          await fetchMessages();
          
          // Update active state
          updateActiveConversation(conversation.id);
        }
      } catch (error) {
        showError('Failed to start conversation');
        console.error('Error starting conversation:', error);
      }
    }

    // Update chat header with user info
    function updateChatHeaderWithUser(userName, avatarUrl) {
      const chatHeader = document.querySelector('.chat-history-header .chat-contact-info h6');
      const chatStatus = document.querySelector('.chat-history-header .user-status');
      const headerAvatarImg = document.querySelector('.chat-history-header .avatar img');
      
      if (chatHeader) {
        chatHeader.textContent = userName;
      }
      if (chatStatus) {
        chatStatus.textContent = 'Online';
      }
      if (headerAvatarImg && avatarUrl) {
        headerAvatarImg.src = avatarUrl;
      }
    }

    // Update active conversation in the list
    function updateActiveConversation(conversationId) {
      document.querySelectorAll('.chat-contact-list-item').forEach(item => {
        item.classList.remove('active');
      });
      
      // Find and activate the conversation
      const conversationElement = document.querySelector(`[onclick*="loadConversation(${conversationId})"]`);
      if (conversationElement) {
        conversationElement.closest('.chat-contact-list-item').classList.add('active');
      }
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
        console.log('typeof Echo:', typeof Echo);
        console.log('typeof window.Echo:', typeof window.Echo);
        console.log('window.Echo:', window.Echo);
        console.log('Available Echo-related globals:', Object.keys(window).filter(k => k.toLowerCase().includes('echo')));
        
        // Check if Echo constructor is available
        if (typeof window.Echo !== 'undefined' && window.Echo) {
          echo = window.Echo;
          console.log('Laravel Echo initialized successfully from window.Echo');
          return true;
        } else if (typeof Echo !== 'undefined' && Echo) {
          echo = Echo;
          console.log('Laravel Echo initialized successfully from Echo');
          return true;
        } else {
          console.warn('Laravel Echo not available - real-time features disabled');
          return false;
        }
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

    // Setup real-time listeners for a conversation
    function setupConversationListeners(conversationId) {
      if (!echo || !conversationId) {
        console.log('Cannot setup listeners: echo or conversationId missing');
        return;
      }

      // Leave previous conversation channel
      if (window.currentConversationChannel) {
        console.log('Leaving previous channel:', window.currentConversationChannel);
        echo.leave(window.currentConversationChannel);
      }

      // Join conversation channel
      const channelName = `conversation.${conversationId}`;
      window.currentConversationChannel = channelName;
      
      console.log('Attempting to join channel:', channelName);
      
      try {
        const channel = echo.private(channelName);
        
        console.log('Successfully created channel:', channel);
        
        // Listen for new messages (support both custom name and FQCN)
        channel.listen('.MessageCreated', (e) => {
          console.log('New message received via Echo (.MessageCreated):', e);
          handleIncomingMessage(extractMessage(e));
        });
        channel.listen('App\\Events\\MessageCreated', (e) => {
          console.log('New message received via Echo (App\\Events\\MessageCreated):', e);
          handleIncomingMessage(extractMessage(e));
        });
        // WireChat vendor events
        channel.listen('Namu\\WireChat\\Events\\MessageCreated', (e) => {
          console.log('New message received via Echo (Namu\\WireChat\\Events\\MessageCreated):', e);
          handleIncomingMessage(extractMessage(e));
        });

        // Listen for message deletions (support both custom name and FQCN)
        channel.listen('.MessageDeleted', (e) => {
          console.log('Message deleted via Echo (.MessageDeleted):', e);
          handleMessageDeletion(extractMessage(e));
        });
        channel.listen('App\\Events\\MessageDeleted', (e) => {
          console.log('Message deleted via Echo (App\\Events\\MessageDeleted):', e);
          handleMessageDeletion(extractMessage(e));
        });
        // WireChat vendor events
        channel.listen('Namu\\WireChat\\Events\\MessageDeleted', (e) => {
          console.log('Message deleted via Echo (Namu\\WireChat\\Events\\MessageDeleted):', e);
          handleMessageDeletion(extractMessage(e));
        });

        // Listen for typing indicators
        channel.listen('TypingIndicator', (e) => {
          console.log('User typing received:', e);
          if (e.is_typing) {
            showTypingIndicator(e.user);
          } else {
            hideTypingIndicator(e.user);
          }
        });

        console.log(`Successfully joined conversation channel: ${channelName}`);
        
        // Test the channel by listening for any events (debugging)
        if (channel.subscription && channel.subscription.bind_global) {
          channel.subscription.bind_global((eventName, data) => {
            console.log('Received event on channel:', eventName, data);
          });
        }
        
        // Test connection
        if (channel.subscription) {
          channel.subscription.bind('pusher:subscription_succeeded', () => {
            console.log('Successfully subscribed to channel:', channelName);
          });
          channel.subscription.bind('pusher:subscription_error', (error) => {
            console.error('Failed to subscribe to channel:', channelName, error);
          });
        }
        
      } catch (error) {
        console.error('Error setting up conversation listeners:', error);
      }
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
      
      // Update conversation list to show new message
      if (!currentConversationId || messageData.conversation_id != currentConversationId) {
        incrementUnread(messageData.conversation_id);
      }
      fetchMessages();
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

      if (typingIndicators.length === 0) {
        // First typing indicator
        indicatorText = user.name + ' is typing...';
      } else {
        // Multiple users typing - show combined indicator
        const typingUsers = [];
        typingIndicators.forEach(indicator => {
          const userId = indicator.getAttribute('data-user-id');
          if (userId && userId !== user.id.toString()) {
            typingUsers.push(indicator.getAttribute('data-user-name'));
          }
        });
        typingUsers.push(user.name);

        if (typingUsers.length === 1) {
          indicatorText = typingUsers[0] + ' is typing...';
        } else if (typingUsers.length === 2) {
          indicatorText = typingUsers.join(' and ') + ' are typing...';
        } else {
          indicatorText = typingUsers[0] + ' and ' + (typingUsers.length - 1) + ' others are typing...';
        }
      }

      // Add new typing indicator
      const typingHtml = `
        <li class="chat-message typing-indicator" data-user-id="${user.id}" data-user-name="${user.name}">
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
    function startMessagePolling(interval = 30000) {
      if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
      }
      
      messagePollingInterval = setInterval(async () => {
        if (currentConversationId) {
          // Only refresh if user is actively viewing a conversation
          // With real-time broadcasting, this is mainly for backup/fallback
          // await loadConversationMessages(currentConversationId);
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
        loadPersistedState();

        // Load initial data
        await Promise.all([
          fetchMessages(),
          fetchGroups(),
          fetchUsers()
        ]);

        // Set up event listeners
        setupEventListeners();

        // Start auto-refresh (reduce frequency if broadcasting is enabled)
        broadcastingEnabled = initializeBroadcasting();
        if (broadcastingEnabled) {
          console.log('Real-time broadcasting enabled - reduced polling frequency');
          // Use longer intervals since we have real-time updates
          startMessagePolling(60000); // Every minute instead of 30 seconds
        } else {
          console.log('Real-time broadcasting disabled - using polling');
          startMessagePolling(30000); // Every 30 seconds
        }

        console.log('Chat system initialized successfully');

        // Auto-open last conversation and rejoin channel after refresh
        const lastConvIdStr = localStorage.getItem(LS_KEYS.lastConversationId);
        const lastConvId = lastConvIdStr ? parseInt(lastConvIdStr) : null;
        if (lastConvId) {
          loadConversation(lastConvId);
        }
      } catch (error) {
        console.error('Failed to initialize chat system:', error);
        showError('Failed to initialize chat system');
      }
    }

    // Listen for Echo ready event
    window.addEventListener('EchoReady', function() {
      console.log('Echo ready event received, reinitializing broadcasting...');
      const broadcastingEnabled = initializeBroadcasting();
      if (broadcastingEnabled) {
        console.log('Broadcasting enabled after Echo ready');
        // Restart with real-time features
        startMessagePolling(60000);
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

    // Laravel Echo Real-time messaging setup
    function setupRealTimeMessaging() {
      if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo not available. Real-time messaging disabled.');
        return;
      }

      // Listen for WebMessage events (global notifications)
      window.Echo.channel('Web-Message')
        .listen('.WebMessage', (data) => {
          console.log('Received WebMessage:', data);
          // Handle global web messages if needed
        })
        .error((error) => {
          console.error('Web-Message channel error:', error);
        });

      console.log('Real-time messaging setup completed');
    }

    // Setup conversation-specific listeners
    function setupConversationListeners(conversationId) {
      if (typeof window.Echo === 'undefined' || !conversationId) {
        return;
      }

      // Remove existing listeners for this conversation
      if (window.currentConversationChannel) {
        window.currentConversationChannel.stopListening('MessageCreated');
        window.Echo.leave(window.currentConversationChannel.name);
      }

      // Create private channel for this conversation
      const channelName = `conversation.${conversationId}`;
      window.currentConversationChannel = window.Echo.private(channelName);

      window.currentConversationChannel
        .listen('.MessageCreated', (data) => {
          console.log('New message received:', data);
          handleNewMessage(data.message);
        })
        .error((error) => {
          console.error(`Conversation ${conversationId} channel error:`, error);
        });
    }

    // Handle incoming real-time messages
    function handleNewMessage(messageData) {
      if (currentConversationId && messageData.conversation_id == currentConversationId) {
        // Only add if it's not already in the UI (avoid duplicates)
        const existingMessage = document.querySelector(`[data-message-id="${messageData.id}"]`);
        if (!existingMessage) {
          addMessageToUI(messageData);
          scrollToBottom();
        }

        // Clear typing indicator for the user who sent the message
        if (messageData.sendable) {
          hideTypingIndicator(messageData.sendable);
        }
      } else {
        // Message for different conversation - increment unread count
        incrementUnread(messageData.conversation_id);
        refreshUserListUnreadBadges();
      }
    }

    // Add message to UI
    function addMessageToUI(messageData) {
      const messagesContainer = document.getElementById('chat-messages');
      if (!messagesContainer) return;

      const isCurrentUser = messageData.sendable_id === currentUser?.id;
      const messageClass = isCurrentUser ? 'chat-message-right' : '';
      const avatarSrc = isCurrentUser ? '/vuexy/assets/img/avatars/1.png' : '/vuexy/assets/img/avatars/4.png';
      const senderName = isCurrentUser ? 'You' : getDisplayName(messageData.sendable || {});
      const timeFormatted = formatMessageTime(messageData.created_at);

      const messageHtml = `
        <li class="chat-message ${messageClass}" data-message-id="${messageData.id}">
          <div class="d-flex overflow-hidden">
            ${!isCurrentUser ? `
              <div class="user-avatar flex-shrink-0 me-4">
                <div class="avatar avatar-sm">
                  <img src="${avatarSrc}" alt="Avatar" class="rounded-circle" />
                </div>
              </div>
            ` : ''}

            <div class="chat-message-wrapper flex-grow-1">
              <div class="chat-message-text">
                <p class="mb-0">${escapeHtml(messageData.body)}</p>
              </div>
              <div class="${isCurrentUser ? 'text-end' : ''} text-muted mt-1">
                <small>${timeFormatted}</small>
              </div>
            </div>
          </div>
        </li>
      `;

      // Insert at the end of the messages list (newest at bottom)
      messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    }

    // Laravel Echo Real-time messaging setup
    function setupRealTimeMessaging() {
      if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo not available. Real-time messaging disabled.');
        return;
      }

      // Listen for WebMessage events (global notifications)
      window.Echo.channel('Web-Message')
        .listen('.WebMessage', (data) => {
          console.log('Received WebMessage:', data);
          // Handle global web messages if needed
        })
        .error((error) => {
          console.error('Web-Message channel error:', error);
        });

      console.log('Real-time messaging setup completed');
    }

    // Setup conversation-specific listeners
    function setupConversationListeners(conversationId) {
      if (typeof window.Echo === 'undefined' || !conversationId) {
        return;
      }

      // Remove existing listeners for this conversation
      if (window.currentConversationChannel) {
        window.currentConversationChannel.stopListening('MessageCreated');
        window.Echo.leave(window.currentConversationChannel.name);
      }

      // Create private channel for this conversation
      const channelName = `conversation.${conversationId}`;
      window.currentConversationChannel = window.Echo.private(channelName);

      window.currentConversationChannel
        .listen('.MessageCreated', (data) => {
          console.log('New message received:', data);
          handleNewMessage(data.message);
        })
        .error((error) => {
          console.error(`Conversation ${conversationId} channel error:`, error);
        });
    }

    // Handle incoming real-time messages
    function handleNewMessage(messageData) {
      if (currentConversationId && messageData.conversation_id == currentConversationId) {
        // Only add if it's not already in the UI (avoid duplicates)
        const existingMessage = document.querySelector(`[data-message-id="${messageData.id}"]`);
        if (!existingMessage) {
          addMessageToUI(messageData);
          scrollToBottom();
        }

        // Clear typing indicator for the user who sent the message
        if (messageData.sendable) {
          hideTypingIndicator(messageData.sendable);
        }
      } else {
        // Message for different conversation - increment unread count
        incrementUnread(messageData.conversation_id);
        refreshUserListUnreadBadges();
      }
    }

    // Add message to UI
    function addMessageToUI(messageData) {
      const messagesContainer = document.getElementById('chat-messages');
      if (!messagesContainer) return;

      const isCurrentUser = messageData.sendable_id === currentUser?.id;
      const messageClass = isCurrentUser ? 'chat-message-right' : '';
      const avatarSrc = isCurrentUser ? '/vuexy/assets/img/avatars/1.png' : '/vuexy/assets/img/avatars/4.png';
      const senderName = isCurrentUser ? 'You' : getDisplayName(messageData.sendable || {});
      const timeFormatted = formatMessageTime(messageData.created_at);

      const messageHtml = `
        <li class="chat-message ${messageClass}" data-message-id="${messageData.id}">
          <div class="d-flex overflow-hidden">
            ${!isCurrentUser ? `
              <div class="user-avatar flex-shrink-0 me-4">
                <div class="avatar avatar-sm">
                  <img src="${avatarSrc}" alt="Avatar" class="rounded-circle" />
                </div>
              </div>
            ` : ''}

            <div class="chat-message-wrapper flex-grow-1">
              <div class="chat-message-text">
                <p class="mb-0">${escapeHtml(messageData.body)}</p>
              </div>
              <div class="${isCurrentUser ? 'text-end' : ''} text-muted mt-1">
                <small>${timeFormatted}</small>
              </div>
            </div>
          </div>
        </li>
      `;

      // Insert at the end of the messages list (newest at bottom)
      messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    }

    // Initialize real-time messaging when page loads
    document.addEventListener('DOMContentLoaded', function() {
      // Wait a bit for currentUser to be loaded
      setTimeout(() => {
        setupRealTimeMessaging();
      }, 1000);
    });

  </script>
@stop