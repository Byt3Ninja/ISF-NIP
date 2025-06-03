<!-- WhatsApp Authentication Interface -->
<div class="">
  <!-- Main Authentication Card -->
  <div class="mb-4">
    <div class="">
      {{-- <div class="d-flex align-items-center">
        <i class="fab fa-whatsapp fa-2x me-3"></i>
        <div>
          <h5 class="mb-0">{{ __('admin.whatsapp_authentication') }}</h5>
          <small class="opacity-75">{{ __('admin.connect_whatsapp_account') }}</small>
        </div>
      </div> --}}
    </div>
    
    <div class="">
      <!-- Connection Status -->
      <div id="connectionStatus" class="connection-status mb-4">
        <div class="status-indicator disconnected" id="statusIndicator">
          <div class="status-dot"></div>
          <div class="status-info">
            <h6 id="connectionTitle" class="mb-1">{{ __('admin.whatsapp_not_connected') }}</h6>
            <small id="connectionSubtitle" class="text-muted">{{ __('admin.start_session_to_connect') }}</small>
          </div>
        </div>
      </div>

      <!-- Session Input -->
      <div class="input-group mb-8 shadow-none">
        <label for="sessionIdInput" class="form-label">{{ __('admin.session_id') }}</label>
        <div class="input-group">
          <span class="input-group-text">
            <i class="fas fa-id-card"></i>
          </span>
          <input 
            type="text" 
            class="form-control" 
            id="sessionIdInput" 
            placeholder="{{ __('admin.enter_session_id') }}" 
            value=""
            pattern="[0-9]+"
            title="{{ __('admin.only_numbers_allowed') }}"
          >
          {{-- <button class="btn btn-outline-info" type="button" onclick="generateRandomSessionId()">
            <i class="fas fa-random"></i>
          </button> --}}
        </div>
        <div class="form-text">
          <i class="fas fa-info-circle"></i>
          {{ __('admin.unique_identifier_session') }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons mb-4">
        <button id="connectButton" class="btn btn-primary btn-lg me-2" onclick="startAuthentication()">
          <i class="fas fa-qrcode"></i>
          <span>{{ __('admin.connect_whatsapp') }}</span>
        </button>
        <button id="statusButton" class="btn btn-outline-primary" onclick="checkConnectionStatus()">
          <i class="fas fa-signal"></i>
          {{ __('admin.status') }}
        </button>
        <button id="disconnectButton" class="btn btn-outline-danger" onclick="disconnectSession()" style="display: none;">
          <i class="fas fa-sign-out-alt"></i>
          {{ __('admin.disconnect') }}
        </button>
        <button id="clearSessionButton" class="btn btn-outline-warning" onclick="clearStoredSession()" title="{{ __('admin.clear_stored_session') }}">
          <i class="fas fa-trash"></i>
          {{ __('admin.clear_session') }}
        </button>
      </div>

      <!-- QR Code Display -->
      <div id="qrCodeSection" class="qr-section" style="display: none;">
        <div class="card border-success">
          <div class="card-header bg-light">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h6 class="mb-0">
                  <i class="fas fa-qrcode text-success"></i>
                  {{ __('admin.scan_qr_code') }}
                </h6>
              </div>
              <div id="qrTimer" class="timer-display"></div>
            </div>
          </div>
          <div class="card-body text-center">
            <div id="qrImageContainer" class="qr-image-container mb-3">
              <img id="qrImage" src="" alt="WhatsApp QR Code" class="qr-image">
              <div id="qrLoader" class="qr-loader">
                <div class="spinner-border text-success" role="status">
                  <span class="visually-hidden">{{ __('admin.Loading') }}...</span>
                </div>
                <p class="mt-2">{{ __('admin.generating_qr_code') }}</p>
              </div>
            </div>
            <div class="qr-instructions">
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="instruction-step">
                    <div class="step-number">1</div>
                    <p class="small mb-0">{{ __('admin.open_whatsapp_phone') }}</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="instruction-step">
                    <div class="step-number">2</div>
                    <p class="small mb-0">{{ __('admin.go_to_settings_linked_devices') }}</p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="instruction-step">
                    <div class="step-number">3</div>
                    <p class="small mb-0">{{ __('admin.tap_link_device_scan') }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Connection Progress -->
      <div id="connectionProgress" class="connection-progress" style="display: none;">
        <div class="progress mb-2">
          <div id="progressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <div class="d-flex justify-content-between">
          <span id="progressText" class="small text-muted">{{ __('admin.waiting_for_connection') }}...</span>
          <span id="progressAttempt" class="small text-muted">0/36</span>
        </div>
      </div>

      <!-- Success State -->
      <div id="successState" class="success-state" style="display: none;">
        <div class="alert alert-success border-0">
          <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x text-success me-3"></i>
            <div>
              <h6 class="alert-heading mb-1">{{ __('admin.whatsapp_connected_successfully') }}</h6>
              <p class="mb-0">{{ __('admin.whatsapp_account_linked') }}</p>
            </div>
          </div>
        </div>
        
        <!-- Connected Session Info -->
        <div id="sessionInfo" class="session-info mt-3">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="info-item">
                <label class="small text-muted">{{ __('admin.session_id_caps') }}</label>
                <div id="connectedSessionId" class="fw-bold">-</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <label class="small text-muted">{{ __('admin.connected_since_caps') }}</label>
                <div id="connectedSince" class="fw-bold">-</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Test Message Section -->
        <div class="test-section mt-4">
          <h6 class="mb-3">
            <i class="fas fa-paper-plane"></i>
            {{ __('admin.send_test_message') }}
          </h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="testPhoneNumber" class="form-label">{{ __('admin.phone_number') }}</label>
              <input type="tel" class="form-control" id="testPhoneNumber" placeholder="{{ __('admin.phone_placeholder') }}">
            </div>
            <div class="col-md-6">
              <label for="testMessage" class="form-label">{{ __('admin.message') }}</label>
              <input type="text" class="form-control" id="testMessage" placeholder="{{ __('admin.hello_whatsapp_api') }}">
            </div>
            <div class="col-12">
              <button id="sendTestButton" class="btn btn-primary" onclick="sendTestMessage()">
                <i class="fas fa-paper-plane me-2"></i>
                {{ __('admin.send_test_message') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Messages -->
      <div id="statusMessages" class="status-messages mt-2"></div>
    </div>
  </div>
</div>
