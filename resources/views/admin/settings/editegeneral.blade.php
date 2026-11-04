@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
@stop

@section('style')
<link rel="stylesheet" href="/css/whatsapp-auth.css" />
<style>
  .sidebar-sticky {
    position: sticky;
    top: 20px;
  }
</style>
@stop

@php
$title = $l == 'en' ? 'Settings' : 'الإعدادات';

$tabs = collect([
  ['general', 'ti-settings', __('settings.General Settings')],
  ['branding', 'ti-palette', __('settings.branding_title')],
  ['analysis', 'ti-graph', __('settings.Analysis Settings')],
  ['SMTP', 'ti-mail', __('settings.Email Configuration')],
  ['app-identity', 'ti-id-badge', __('settings.Application Identity')],
  ['Notification', 'ti-bell', __('settings.Push Notification Settings')],
  ['NotificationChannels', 'ti-bell-ringing', __('settings.Notification Channels')],
  ['MeetingTab', 'ti-device-computer-camera', __('settings.Meeting Setting')],
  ['SocialMediaLinks', 'ti-share', __('settings.SocialMediaLinks')],
  ['SocialMediaLogin', 'ti-brand-facebook', __('settings.SocialMediaLogin')],
  ['HomePage', 'ti-home', __('admin.HomePageSetting')],
  ['WhatsApp', 'ti-brand-whatsapp', __('admin.WhatsApp')],
  ])->map(function ($tab) {
    return ['target' => $tab[0], 'icon' => $tab[1], 'label' => $tab[2]];
  })->toArray();
@endphp

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->
  <div class="">
    <!-- Alert -->
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ __('admin.' . session()->get('message')) }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session()->has('message2'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {{ __('admin.' . session()->get('message2')) }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <!-- / Alert -->
    <!-- Tabs (mobile) -->
    <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none">
      <ul class="nav nav-pills flex-nowrap" role="tablist">
        @foreach ($tabs as $key => $tab)
        <li class="nav-item d-flex justify-content-center align-items-center">
          <a href="#navs-left-{{ $tab['target'] }}" class="nav-link {{ $tab['target'] === $activeTab ? 'active' : '' }}"
            data-bs-toggle="tab" role="tab">
            <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
            {{ $tab['label'] }}
          </a>
        </li>
        @endforeach
      </ul>
    </div>
    <!-- / Tabs (mobile) -->
    <div class="row">
      <div class="col-lg-3">
        <nav class="nav-align-left nav-tabs-shadow mb-6 p-4 bg-white d-none d-md-block sidebar-sticky">
          <!-- Tabs -->
          <ul class="nav nav-tabs nav-fill border-none m-auto" role="tablist">
            @foreach ($tabs as $key => $tab)
            <li class="nav-item bg-label-light mb-2 rounded overflow-x-hidden d-flex align-items-center">
              <a href="#navs-left-{{ $tab['target'] }}" class="nav-link {{ $tab['target'] === $activeTab ? 'active' : '' }} py-3"
                data-bs-toggle="tab" role="tab">
                <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                {{ $tab['label'] }}
              </a>
            </li>
            @endforeach
          </ul>
          <!-- / Tabs -->
        </nav>
      </div>
      <!-- Tab Content On Tab Click -->
      <div class="col-lg-9">
        <div class="tab-content p-0 overflow-y-auto mb-6">
          @foreach ($tabs as $index => $tab)
          <div class="tab-pane fade {{ $tab['target'] === $activeTab ? 'show active' : '' }} mb-6" id="navs-left-{{ $tab['target'] }}">
            <div class="card mb-6">
            <div class="card-header">
              {{-- Keyed on the target, not the index: the WhatsApp header has a subtitle,
     and a positional check silently retargets when a tab is inserted. --}}
              @if($tab['target'] !== 'WhatsApp')
              <h5 class="card-title pb-3 border-bottom">{{ $tab['label'] }}</h5>
              @else
              <div class="card-title pb-3 border-bottom">
                <h5 class="mb-0 card-title ">{{ __('admin.whatsapp_authentication') }}</h5>
                <small class="opacity-75">{{ __('admin.connect_whatsapp_account') }}</small>
              </div>
              @endif
            </div>
            <div class="card-body">
              @switch($tab['target'])
              @case('general')
              @include('partials.forms.general-settings-form')
              @break
              @case('branding')
              @include('partials.forms.platform-branding-form')
              @break
              @case('analysis')
              @include('partials.forms.analysis-settings-form')
              @break
              @case('SMTP')
              @include('partials.forms.email-configuration')
              @break
              @case('app-identity')
              @include('partials.forms.app-identity-form')
              @break
              @case('Notification')
              @include('partials.forms.notification-settings-form')
              @break
              @case('NotificationChannels')
              @include('admin.settings.notifications.form')
              @break
              @case('MeetingTab')
              @include('partials.forms.meeting-settings-form')
              @break
              @case('SocialMediaLinks')
              @include('partials.forms.social-link-form')
              @break
              @case('SocialMediaLogin')
              @include('partials.forms.social-login-form')
              @break
              @case('HomePage')
              @include('partials.forms.home-settings-form')
              @break
              @case('WhatsApp')
              @include('partials.forms.whatsapp-settings-form')
              @break
              
              @endswitch
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
    <!-- / Tab Content On Tab Click -->
    </div>
  </div>
  <!-- / Content -->
</div>
<!-- / Content wrapper -->
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
@stop

@section('pagescripts')
<script>
  $(document).ready(function() {
      getStates($('#country_id').val());
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      var $state = $('#state_id');
     
      var currentState = '{{ $Title->State }}';
      var selected = '';
      $.ajax({
        url: "{{ route('utils.locations_list_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          // console.log(data);
          $state.html('<option value="" disabled >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            // console.log(id, value);
            // selected = '';
            
            if (currentState == id) {
              selected = 'selected';
              // getCities(id)
            } else {
              selected = '';
            }
            
            $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
            $('#city_id').html('<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }    
</script>

<script>
class WhatsAppAuthenticator {
  constructor() {
    this.pollingInterval = 3000; // 3 seconds
    this.maxPollingAttempts = 60; // 3 minutes total
    this.pollingTimer = null;
    this.pollingAttempts = 0;
    this.isPolling = false;
    this.currentSessionId = null;
    this.qrTimer = null;
    this.qrTimeRemaining = 180; // 3 minutes
    this.storageKey = 'whatsapp_session_id';
    
    this.initializeElements();
    this.loadStoredSession();
  }

  initializeElements() {
    this.sessionIdInput = document.getElementById('sessionIdInput');
    this.connectButton = document.getElementById('connectButton');
    this.statusButton = document.getElementById('statusButton');
    this.disconnectButton = document.getElementById('disconnectButton');
    this.qrCodeSection = document.getElementById('qrCodeSection');
    this.qrImage = document.getElementById('qrImage');
    this.qrLoader = document.getElementById('qrLoader');
    this.connectionProgress = document.getElementById('connectionProgress');
    this.progressBar = document.getElementById('progressBar');
    this.progressText = document.getElementById('progressText');
    this.progressAttempt = document.getElementById('progressAttempt');
    this.successState = document.getElementById('successState');
    this.statusIndicator = document.getElementById('statusIndicator');
    this.connectionTitle = document.getElementById('connectionTitle');
    this.connectionSubtitle = document.getElementById('connectionSubtitle');
    this.connectedSessionId = document.getElementById('connectedSessionId');
    this.connectedSince = document.getElementById('connectedSince');
    this.statusMessages = document.getElementById('statusMessages');
    this.qrTimerDisplay = document.getElementById('qrTimer');
  }

  async loadStoredSession() {
    try {
      // First, check if there's a session ID in the database
      const dbSessionResponse = await this.apiCall('/dashboard/whatsapp/getWASession');
      
      if (dbSessionResponse.success && dbSessionResponse.sessionId) {
        const dbSessionId = dbSessionResponse.sessionId;
        const dbSessionStatus = dbSessionResponse.status;
        
        // Update UI with database session ID
        if (this.sessionIdInput) {
          this.sessionIdInput.value = dbSessionId;
        }
        
        // Show checking status
        this.updateConnectionStatus('checking');
        this.showMessage('{{__('admin.Checking existing session status')}}...', 'info', 3000);

        if (dbSessionStatus === 'connected') {
          // Session is connected in the database
          this.currentSessionId = dbSessionId;
          this.updateConnectionStatus('connected', dbSessionId);
          
          // Store session in localStorage for future use
          this.storeSession(dbSessionId);
          
          // Show success state
          if (this.successState) {
            this.successState.style.display = 'block';
          }

          this.showMessage(`{{__('admin.Already connected to WhatsApp with session')}}: ${dbSessionId}`, 'success');
          return;
        } else {
          // Session exists in database but not connected - automatically show QR code
          this.showMessage(`{{__('admin.Found existing session')}} ${dbSessionId}. {{__('admin.Displaying QR code for reconnection')}}...`, 'info', 3000);
          
          // Automatically start the authentication process with the existing session ID
          await this.autoStartAuthentication(dbSessionId);
          return;
        }
      }
      
      // If no session in database, check localStorage as fallback
      const storedSessionId = localStorage.getItem(this.storageKey);
      const storedConnectionTime = localStorage.getItem(`${this.storageKey}_connected_at`);
      
      if (storedSessionId) {
        // Update UI with stored session ID
        if (this.sessionIdInput) {
          this.sessionIdInput.value = storedSessionId;
        }
        
        // Show checking status
        this.updateConnectionStatus('checking');
        this.showMessage('{{__('admin.Checking stored session status')}}...', 'info', 3000);

        // Check if the stored session is still connected
        const response = await this.apiCall(`/dashboard/whatsapp/session/${storedSessionId}/status`);
        
        if (response.status === 'CONNECTED' || response.status === 'OPEN') {
          // Session is still active
          this.currentSessionId = storedSessionId;
          this.updateConnectionStatus('connected', storedSessionId);
          
          // Update connected since time from storage
          if (this.connectedSince && storedConnectionTime) {
            this.connectedSince.textContent = new Date(parseInt(storedConnectionTime)).toLocaleString();
          }
          
          // Show success state
          if (this.successState) {
            this.successState.style.display = 'block';
          }

          this.showMessage(`{{__('admin.Already connected to WhatsApp with session')}}: ${storedSessionId}`, 'success');
        } else {
          // Session is no longer active, clear storage
          this.clearStoredSession();
          this.updateConnectionStatus('disconnected');
          this.showMessage('{{__('admin.Stored session is no longer active. Please reconnect')}}', 'warning');
        }
      } else {
        // No stored session
        this.updateConnectionStatus('disconnected');
      }
    } catch (error) {
      // Error checking session, clear localStorage and show disconnected state
      this.clearStoredSession();
      this.updateConnectionStatus('disconnected');
      this.showMessage('Error checking session status. Please reconnect.', 'warning');
      console.error('Error checking session:', error);
    }
  }

  async autoStartAuthentication(sessionId) {
    // This method automatically starts authentication for existing sessions
    // without user interaction (no validation needed since session ID comes from database)
    
    this.resetInterface();
    this.updateConnectionStatus('connecting');
    
    try {
      // Start session
      await this.apiCall(`/dashboard/whatsapp/session/${sessionId}/start`);
      
      // Show QR code section
      if (this.qrCodeSection) {
        this.qrCodeSection.style.display = 'block';
      }
      
      // Load QR code
      await this.loadQRCode(sessionId);
      
      // Start monitoring
      this.startPolling(sessionId);
      this.startQRTimer();
      
    } catch (error) {
      console.log('error', error);
      
      // Parse error response if it's a JSON string
      let errorData = error;
      if (typeof error.message === 'string') {
        try {
          errorData = JSON.parse(error.message);
        } catch (parseError) {
          // If parsing fails, use the original error
          errorData = { api_error: error.message };
        }
      }
      
      // Extract the specific error message
      const errorMessage = errorData.api_error || errorData.error || error.message || 'Unknown error occurred';
      
      // Check if session already exists - if so, proceed with QR code
      if (errorData.api_status === 422 && errorMessage.toLowerCase().includes('session already exists')) {
        this.showMessage(`Session already exists for ${sessionId}. Proceeding to QR code...`, 'info');
        
        // Show QR code section
        if (this.qrCodeSection) {
          this.qrCodeSection.style.display = 'block';
        }
        
        // Load QR code directly since session exists
        try {
          await this.loadQRCode(sessionId);
          
          // Start monitoring
          this.startPolling(sessionId);
          this.startQRTimer();
        } catch (qrError) {
          this.showMessage(`Failed to load QR code: ${qrError.message}`, 'error');
          this.updateConnectionStatus('disconnected');
        }
      } else {
        // Handle other errors normally
        this.showMessage(`Failed to auto-reconnect session: ${errorMessage}`, 'error');
        this.updateConnectionStatus('disconnected');
      }
    }
  }

  storeSession(sessionId) {
    try {
      localStorage.setItem(this.storageKey, sessionId);
      localStorage.setItem(`${this.storageKey}_connected_at`, Date.now().toString());
    } catch (error) {
      console.error('Error storing session:', error);
    }
  }

  clearStoredSession() {
    try {
      localStorage.removeItem(this.storageKey);
      localStorage.removeItem(`${this.storageKey}_connected_at`);
    } catch (error) {
      console.error('Error clearing stored session:', error);
    }
  }

  updateConnectionStatus(status, sessionId = null) {
    const statusConfig = {
      disconnected: {
        title: '{{ __('admin.whatsapp_not_connected_title') }}',
        subtitle: '{{ __('admin.start_session_to_connect_subtitle') }}',
        className: 'disconnected',
        showConnect: true,
        showDisconnect: false
      },
      checking: {
        title: '{{ __('admin.checking_connection_title') }}',
        subtitle: '{{ __('admin.verifying_session_subtitle') }}',
        className: 'connecting',
        showConnect: false,
        showDisconnect: false
      },
      connecting: {
        title: '{{ __('admin.connecting_whatsapp_title') }}',
        subtitle: '{{ __('admin.establishing_connection_subtitle') }}',
        className: 'connecting',
        showConnect: false,
        showDisconnect: false
      },
      connected: {
        title: '{{ __('admin.whatsapp_connected_title') }}',
        subtitle: '{{ __('admin.account_linked_subtitle') }}',
        className: 'connected',
        showConnect: false,
        showDisconnect: true
      }
    };

    const config = statusConfig[status] || statusConfig.disconnected;
    
    if (this.statusIndicator) {
      this.statusIndicator.className = `status-indicator ${config.className}`;
    }
    
    if (this.connectionTitle) {
      this.connectionTitle.textContent = config.title;
    }
    
    if (this.connectionSubtitle) {
      this.connectionSubtitle.textContent = config.subtitle;
    }

    if (this.connectButton) {
      this.connectButton.style.display = config.showConnect ? 'inline-block' : 'none';
      this.connectButton.disabled = !config.showConnect || status === 'checking' || status === 'connecting';
    }
    
    if (this.disconnectButton) {
      this.disconnectButton.style.display = config.showDisconnect ? 'inline-block' : 'none';
    }

    // Update session info for connected state
    if (status === 'connected' && sessionId) {
      this.currentSessionId = sessionId;
      if (this.connectedSessionId) {
        this.connectedSessionId.textContent = sessionId;
      }
      if (this.connectedSince) {
        this.connectedSince.textContent = new Date().toLocaleString();
      }
    }
  }

  showMessage(message, type = 'info', duration = 5000) {
    if (!this.statusMessages) return;
    
    const alertClass = {
      success: 'alert-success',
      error: 'alert-danger',
      warning: 'alert-warning',
      info: 'alert-info'
    }[type] || 'alert-info';

    const messageElement = document.createElement('div');
    messageElement.className = `alert ${alertClass} alert-dismissible fade show`;
    messageElement.innerHTML = `
      <div class="d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      </div>
    `;

    this.statusMessages.appendChild(messageElement);

    if (duration > 0) {
      setTimeout(() => {
        if (messageElement.parentNode) {
          messageElement.remove();
        }
      }, duration);
    }
  }

  startQRTimer() {
    this.qrTimeRemaining = 180; // 3 minutes
    this.updateQRTimer();
    
    this.qrTimer = setInterval(() => {
      this.qrTimeRemaining--;
      this.updateQRTimer();
      
      if (this.qrTimeRemaining <= 0) {
        clearInterval(this.qrTimer);
        this.showMessage('QR Code expired. Please try again.', 'warning');
        this.resetInterface();
      }
    }, 1000);
  }

  updateQRTimer() {
    if (!this.qrTimerDisplay) return;
    
    const minutes = Math.floor(this.qrTimeRemaining / 60);
    const seconds = this.qrTimeRemaining % 60;
    this.qrTimerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
  }

  stopQRTimer() {
    if (this.qrTimer) {
      clearInterval(this.qrTimer);
      this.qrTimer = null;
    }
  }

  async startAuthentication() {
    // Prevent multiple simultaneous connection attempts
    if (this.isPolling || this.currentSessionId) {
      this.showMessage('{{__('admin.Already connecting or connected. Please wait or disconnect first')}}', 'warning');
      return;
    }
    
    const sessionId = this.sessionIdInput?.value?.trim();
    if (!sessionId) {
      this.showMessage('{{__('admin.Please enter a session ID')}}', 'error');
      return;
    }

    if (!/^[0-9]+$/.test(sessionId)) {
      this.showMessage('{{__('admin.Invalid session ID format. only numbers allowed')}}', 'error');
      return;
    }

    this.resetInterface();
    this.updateConnectionStatus('connecting');
    
    try {
      // Start session
      await this.apiCall(`/dashboard/whatsapp/session/${sessionId}/start`);
      
      // Show QR code section
      if (this.qrCodeSection) {
        this.qrCodeSection.style.display = 'block';
      }
      
      // Load QR code
      await this.loadQRCode(sessionId);
      
      // Start monitoring
      this.startPolling(sessionId);
      this.startQRTimer();
      
    } catch (error) {
      console.log('error', error);
      
      // Parse error response if it's a JSON string
      let errorData = error;
      if (typeof error.message === 'string') {
      try {
        errorData = JSON.parse(error.message);
      } catch (parseError) {
        // If parsing fails, use the original error
        errorData = { api_error: error.message };
      }
      }
      
      // Extract the specific error message
      const errorMessage = errorData.api_error || errorData.error || error.message || 'Unknown error occurred';
      
      // Check if session already exists - if so, proceed with QR code
      if (errorData.api_status === 422 && errorMessage.toLowerCase().includes('session already exists')) {
        this.showMessage(`Session already exists for ${sessionId}. Proceeding to QR code...`, 'info');
        
        // Show QR code section
        if (this.qrCodeSection) {
          this.qrCodeSection.style.display = 'block';
        }
        
        // Load QR code directly since session exists
        try {
          await this.loadQRCode(sessionId);
          
          // Start monitoring
          this.startPolling(sessionId);
          this.startQRTimer();
        } catch (qrError) {
          this.showMessage(`Failed to load QR code: ${qrError.message}`, 'error');
          this.updateConnectionStatus('disconnected');
        }
      } else {
        // Handle other errors normally
        this.showMessage(`Failed to start session: ${errorMessage}`, 'error');
        this.updateConnectionStatus('disconnected');
        
        // Display sweet alert of error with proper error message
        alert(`Error: ${errorMessage}`);
      }
    }
  }

  async loadQRCode(sessionId) {
    const maxRetries = 36;
    const retryDelay = 5000; // 5 seconds
    
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
      try {
        const qrUrl = `/dashboard/whatsapp/session/${sessionId}/qr-image`;
        
        // Show loader
        if (this.qrLoader) {
          this.qrLoader.style.display = 'block';
          // Update loader text to show retry attempt
          const loaderText = this.qrLoader.querySelector('p');
          if (loaderText) {
            if (attempt === 1) {
              loaderText.textContent = 'Generating QR Code...';
            } else {
              loaderText.textContent = `Waiting for QR Code... (${attempt}/${maxRetries})`;
            }
          }
        }
        if (this.qrImage) {
          this.qrImage.style.display = 'none';
        }

        // Fetch image with authentication headers
        const headers = {
          'Accept': 'image/png',
          'X-Requested-With': 'XMLHttpRequest'
        };

        // Add CSRF token if available
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
          headers['X-CSRF-TOKEN'] = token.getAttribute('content');
        }

        const response = await fetch(qrUrl, {
          method: 'POST',
          headers: headers,
          credentials: 'same-origin' // Include cookies for authentication
        });

        if (response.ok) {
          // Check if response is an image
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.startsWith('image/')) {
            // Convert response to blob and create object URL
            const blob = await response.blob();
            const imageUrl = URL.createObjectURL(blob);

            // Display image
            if (this.qrImage) {
              this.qrImage.src = imageUrl;
              this.qrImage.style.display = 'block';
              
              // Clean up object URL when image is loaded
              this.qrImage.onload = () => {
                URL.revokeObjectURL(imageUrl);
              };
            }
            
            if (this.qrLoader) {
              this.qrLoader.style.display = 'none';
            }
            
            return; // Success, exit the retry loop
          }
        }
        
        // If we get here, check if it's a JSON response with retry info
        const result = await response.json().catch(() => null);
        
        if (result && result.retry && attempt < maxRetries) {
          // API said to retry, wait and try again
          console.log(`QR code not ready, retrying in ${retryDelay}ms (attempt ${attempt}/${maxRetries})`);
          await new Promise(resolve => setTimeout(resolve, retryDelay));
          continue;
        }
        
        // Handle specific error cases
        if (result && result.error) {
          throw new Error(result.error);
        }
        
        throw new Error(`Failed to fetch QR code: ${response.status} ${response.statusText}`);
        
      } catch (error) {
        console.error(`QR Code loading attempt ${attempt} failed:`, error);
        
        // If this is the last attempt, show detailed troubleshooting
        if (attempt === maxRetries) {
          this.showQRCodeTroubleshooting(sessionId, error.message);
          
          // Hide loader on final error
          if (this.qrLoader) {
            this.qrLoader.style.display = 'none';
          }
          
          throw error;
        }
        
        // For non-retryable errors, throw immediately
        if (error.message.includes('already scanned')) {
          this.showMessage('QR code was already scanned. Please check your connection status.', 'warning');
          
          if (this.qrLoader) {
            this.qrLoader.style.display = 'none';
          }
          
          throw error;
        }
        
        // Wait before retrying
        await new Promise(resolve => setTimeout(resolve, retryDelay));
      }
    }
  }

  showQRCodeTroubleshooting(sessionId, errorMessage) {
    // Hide the QR section and show troubleshooting information
    if (this.qrCodeSection) {
      this.qrCodeSection.style.display = 'none';
    }

    // Create troubleshooting card
    const troubleshootingHtml = `
      <div class="card border-warning mb-4" id="troubleshootingCard">
        <div class="card-header bg-warning text-dark">
          <h6 class="mb-0">
            <i class="fas fa-exclamation-triangle"></i>
            QR Code Generation Issue
          </h6>
        </div>
        <div class="card-body">
          <div class="alert alert-warning">
            <strong>Issue:</strong> The WhatsApp API server could not generate a QR code for session "${sessionId}".
          </div>
          
          <div class="mb-3">
            <strong>Error Details:</strong>
            <code class="d-block mt-1 p-2 bg-light rounded">${errorMessage}</code>
          </div>

          <div class="mb-3">
            <strong>Possible Solutions:</strong>
            <ol class="mb-0">
              <li><strong>Try a different session ID:</strong> The current session might be in an invalid state.</li>
              <li><strong>Check API server status:</strong> The WhatsApp API server might be experiencing issues.</li>
              <li><strong>Wait and retry:</strong> Sometimes the server needs more time to initialize the session.</li>
              <li><strong>Contact administrator:</strong> The API server configuration might need attention.</li>
            </ol>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="this.closest('#troubleshootingCard').remove(); startAuthentication();">
              <i class="fas fa-redo"></i> Try Again
            </button>
            <button class="btn btn-outline-primary" onclick="generateRandomSessionId(); this.closest('#troubleshootingCard').remove(); startAuthentication();">
              <i class="fas fa-random"></i> Try Different Session ID
            </button>
            <button class="btn btn-outline-secondary" onclick="checkConnectionStatus();">
              <i class="fas fa-signal"></i> Check Status
            </button>
          </div>
        </div>
      </div>
    `;

    // Insert troubleshooting card after the action buttons
    const actionButtons = document.querySelector('.action-buttons');
    if (actionButtons) {
      actionButtons.insertAdjacentHTML('afterend', troubleshootingHtml);
    }

    this.showMessage('QR code generation failed. See troubleshooting information below.', 'error', 0);
  }

  startPolling(sessionId) {
    this.isPolling = true;
    this.pollingAttempts = 0;
    
    if (this.connectionProgress) {
      this.connectionProgress.style.display = 'block';
    }
    
    this.checkStatus(sessionId);
  }

  async checkStatus(sessionId) {
    if (!this.isPolling) return;

    this.pollingAttempts++;
    
    // Update progress
    const progress = (this.pollingAttempts / this.maxPollingAttempts) * 100;
    if (this.progressBar) {
      this.progressBar.style.width = `${Math.min(progress, 100)}%`;
    }
    if (this.progressText) {
      this.progressText.textContent = 'Waiting for WhatsApp scan...';
    }
    if (this.progressAttempt) {
      this.progressAttempt.textContent = `${this.pollingAttempts}/${this.maxPollingAttempts}`;
    }

    try {
      const response = await this.apiCall(`/dashboard/whatsapp/session/${sessionId}/status`);
      
      if (response.status === 'CONNECTED' || response.status === 'OPEN') {
        this.onConnectionSuccess(sessionId);
        return;
      }
      
      if (this.pollingAttempts >= this.maxPollingAttempts) {
        this.showMessage('Connection timeout. Please try again.', 'warning');
        this.resetInterface();
        return;
      }
      
      // Continue polling
      setTimeout(() => this.checkStatus(sessionId), this.pollingInterval);
      
    } catch (error) {
      this.showMessage(`Status check failed: ${error.message}`, 'error');
      this.resetInterface();
    }
  }

  onConnectionSuccess(sessionId) {
    this.isPolling = false;
    this.stopQRTimer();
    
    // Store session in localStorage
    this.storeSession(sessionId);
    
    // Hide QR and progress sections
    if (this.qrCodeSection) {
      this.qrCodeSection.style.display = 'none';
    }
    if (this.connectionProgress) {
      this.connectionProgress.style.display = 'none';
    }
    
    // Show success state
    if (this.successState) {
      this.successState.style.display = 'block';
    }
    
    this.updateConnectionStatus('connected', sessionId);
    this.showMessage('{{ __('admin.whatsapp_connected_successfully') }}', 'success');
  }

  async disconnectSession() {
    const sessionId = this.currentSessionId || this.sessionIdInput?.value?.trim();
    if (!sessionId) {
      this.showMessage('No session to disconnect', 'error');
      return;
    }

    if (!confirm(`{{__('admin.Are you sure you want to disconnect session')}} '${sessionId}'?`)) {
      return;
    }

    try {
      await this.apiCall(`/dashboard/whatsapp/session/${sessionId}/terminate`, 'DELETE');
      
      // Clear stored session
      this.clearStoredSession();

      this.showMessage('{{__('admin.WhatsApp_disconnected_successfully')}}', 'success');
      this.resetInterface();
    } catch (error) {
      this.showMessage(`Failed to disconnect: ${error.message}`, 'error');
    }
  }

  async checkConnectionStatus() {
    const sessionId = this.sessionIdInput?.value?.trim();
    if (!sessionId) {
      this.showMessage('{{__('admin.Please enter a session ID')}}', 'error');
      return;
    }

    try {
      const response = await this.apiCall(`/dashboard/whatsapp/session/${sessionId}/status`);
      
      // Show detailed status information
      // ${response.raw_response ? `<strong>Raw Response:</strong> ${JSON.stringify(response.raw_response, null, 2)}` : ''}
      const statusInfo = `
        <div class="alert alert-info">
          <div class="d-flex flex-column gap-2">
            <strong>{{__('admin.Session Status Details')}}:</strong>
            <strong>{{__('admin.session_id')}}:</strong> ${sessionId}
            <strong>{{__('admin.Status')}}:</strong> ${response.status || 'Unknown'}
            <strong>{{__('admin.message')}}:</strong> ${response.message || 'No message'}
          </div>
        </div>
      `;
      
      this.showMessage(statusInfo, 'info', 10000);
    } catch (error) {
      this.showMessage(`Failed to check status: ${error.message}`, 'error');
    }
  }

  async sendTestMessage() {
    const phoneNumber = document.getElementById('testPhoneNumber')?.value?.trim();
    const message = document.getElementById('testMessage')?.value?.trim();
    
    if (!phoneNumber || !message) {
      this.showMessage('{{__('admin.Please enter both phone number and message')}}', 'error');
      return;
    }

    if (!this.currentSessionId) {
      this.showMessage('{{__('admin.No active session. Please connect first')}}', 'error');
      return;
    }

    try {
      await this.apiCall(`/dashboard/whatsapp/session/${this.currentSessionId}/send`, 'POST', {
        to: phoneNumber,
        message: message
      });
      this.showMessage('{{__('admin.Test message sent successfully')}}', 'success');

      // Clear inputs
      document.getElementById('testPhoneNumber').value = '';
      document.getElementById('testMessage').value = '';
    } catch (error) {
      this.showMessage(`{{__('admin.Failed to send message')}}: ${error.message}`, 'error');
    }
  }

  resetInterface() {
    this.isPolling = false;
    this.stopQRTimer();
    this.currentSessionId = null;
    
    // Clear stored session only if explicitly resetting due to connection failure
    // (not when just hiding UI elements)
    
    // Hide all sections
    if (this.qrCodeSection) {
      this.qrCodeSection.style.display = 'none';
    }
    if (this.connectionProgress) {
      this.connectionProgress.style.display = 'none';
    }
    if (this.successState) {
      this.successState.style.display = 'none';
    }

    // Remove any troubleshooting cards
    const troubleshootingCard = document.getElementById('troubleshootingCard');
    if (troubleshootingCard) {
      troubleshootingCard.remove();
    }
    
    this.updateConnectionStatus('disconnected');
  }

  async apiCall(endpoint, method = 'POST', data = null) {
    const options = {
      method: method,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
      }
    };

    const token = document.querySelector('meta[name="csrf-token"]');
    if (token && method !== 'GET') {
      options.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
    }

    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(endpoint, options);
    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.error || `HTTP ${response.status}`);
    }

    return result;
  }
}

// Initialize the authenticator
let whatsappAuth;
document.addEventListener('DOMContentLoaded', function() {
  whatsappAuth = new WhatsAppAuthenticator();
});

// Global functions for button onclick handlers
function startAuthentication() {
  whatsappAuth?.startAuthentication();
}

function checkConnectionStatus() {
  whatsappAuth?.checkConnectionStatus();
}

function disconnectSession() {
  whatsappAuth?.disconnectSession();
}

function sendTestMessage() {
  whatsappAuth?.sendTestMessage();
}

function clearStoredSession() {
  if (whatsappAuth) {
    if (confirm('Are you sure you want to clear the stored session? This will remove the locally stored connection information.')) {
      whatsappAuth.clearStoredSession();
      whatsappAuth.currentSessionId = null;
      whatsappAuth.resetInterface();
      whatsappAuth.showMessage('Stored session cleared successfully', 'success');
      
      // Clear the session ID input
      const sessionInput = document.getElementById('sessionIdInput');
      if (sessionInput) {
        sessionInput.value = '';
      }
    }
  }
}

function generateRandomSessionId() {
  const adjectives = ['quick', 'smart', 'brave', 'calm', 'bright', 'fast', 'cool', 'wise'];
  const nouns = ['tiger', 'eagle', 'wolf', 'lion', 'shark', 'hawk', 'bear', 'fox'];
  const randomAdjective = adjectives[Math.floor(Math.random() * adjectives.length)];
  const randomNoun = nouns[Math.floor(Math.random() * nouns.length)];
  const randomNumber = Math.floor(Math.random() * 1000);
  
  const sessionId = `${randomAdjective}-${randomNoun}-${randomNumber}`;
  document.getElementById('sessionIdInput').value = sessionId;
}

// Legacy functions for backward compatibility
function startSession(sessionId = null) {
  if (sessionId) {
    document.getElementById('sessionIdInput').value = sessionId;
  }
  startAuthentication();
}

function checkStatus(sessionId = null) {
  if (sessionId) {
    document.getElementById('sessionIdInput').value = sessionId;
  }
  checkConnectionStatus();
}

function getQRData(sessionId = null) {
  if (sessionId) {
    document.getElementById('sessionIdInput').value = sessionId;
  }
  startAuthentication();
}

function showQRCode(sessionId = null) {
  if (sessionId) {
    document.getElementById('sessionIdInput').value = sessionId;
  }
  startAuthentication();
}

function showMessage(message, type) {
  whatsappAuth?.showMessage(message, type);
}


// Enable connectButton oninput change
document.getElementById('sessionIdInput').addEventListener('input', function() {
  const connectButton = document.getElementById('connectButton');
  if (connectButton) {
    connectButton.disabled = !this.value.trim();
  }
});
</script>
@stop
