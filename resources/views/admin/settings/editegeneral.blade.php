@extends('layouts.admin')

@section('style')
  <style>
     .custom-container {
      width: 100%;
    }

    @media screen and (min-width: 768px) {
      .custom-container {
        width: 75%;
      }
    }
  </style>
@stop

@php
  $title = $l == 'en' ? 'Settings' : 'الإعدادات';

  $tabs = collect([
    ['general', 'ti-settings', __('settings.General Settings')],
    ['analysis', 'ti-graph', __('settings.Analysis Settings')],
    ['SMTP', 'ti-mail', __('settings.SMTP Configuration')],
    ['app-identity', 'ti-id-badge', __('settings.Application Identity')],
    ['Notification', 'ti-bell', __('settings.Push Notification Settings')],
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
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
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
              <a href="#navs-left-{{ $tab['target'] }}"
                class="nav-link {{ $key == 0 ? 'active' : '' }}"
                data-bs-toggle="tab" role="tab">
                <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                {{ $tab['label'] }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
      <!-- / Tabs (mobile) -->
      <div class="d-flex justify-content-start w-100 mb-6">
        <nav 
          class="nav-align-left nav-tabs-shadow mb-6 p-4 bg-white position-fixed d-none d-md-block" 
          style="min-height: 500px; min-width: 18%; max-width: 18%;">
          <!-- Tabs -->
          <ul class="nav nav-tabs nav-fill border-none m-auto" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item bg-label-light mb-2 rounded overflow-x-hidden d-flex align-items-center">
                <a href="#navs-left-{{ $tab['target'] }}"
                  class="nav-link {{ $key == 0 ? 'active' : '' }} py-3"
                  data-bs-toggle="tab" role="tab">
                  <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                  {{ $tab['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
          <!-- / Tabs -->
        </nav>
        <!-- Tab Content On Tab Click -->
        <div class="tab-content p-0 overflow-y-auto custom-container mb-6" 
          style="{{ $l == 'en' ? 'margin-left: auto' : 'margin-right: auto' }}"
        >
          @foreach ($tabs as $index => $tab)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }} mb-6"
              id="navs-left-{{ $tab['target'] }}">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title pb-3 border-bottom">{{ $tab['label'] }}</h5>
                </div>
                <div class="card-body">
                  @switch($tab['target'])
                    @case('general')
                      @include('partials.forms.general-settings-form')
                      @break
                    @case('analysis')
                      @include('partials.forms.analysis-settings-form')
                      @break
                    @case('SMTP')
                      @include('partials.forms.SMTP-configuration-form')
                      @break
                    @case('app-identity')
                      @include('partials.forms.app-identity-form')
                      @break
                    @case('Notification')
                      @include('partials.forms.notification-settings-form')
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
        <!-- / Tab Content On Tab Click -->
      </div>
    </div>
    <!-- / Content -->
  </div>
  <!-- / Content wrapper -->
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
            console.log(id, value);
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
    // --- Configuration ---
    const pollingInterval = 5000; // Check status every 5 seconds
    const maxPollingAttempts = 36; // Stop after 36 * 5s = 180s (3 minutes)

    // --- Global State ---
    let pollingTimer = null;
    let pollingAttempts = 0;
    let isPolling = false;

    // --- DOM References ---
    const sessionIdInput = document.getElementById('sessionIdInput');
    const monitorButton = document.getElementById('monitorButton');
    const feedbackArea = document.getElementById('feedbackArea');
    const qrCodeImageArea = document.getElementById('qrCodeImageArea');
    const qrImageElement = document.getElementById('qrImageElement');
    const pollingFeedbackArea = document.getElementById('pollingFeedbackArea');
    const pollingStatusContent = document.getElementById('pollingStatusContent');

     // --- Helper Functions ---
    function showFeedback(message, type = 'info') {
        feedbackArea.textContent = message;
        feedbackArea.className = `feedback ${type}`;
        feedbackArea.style.display = 'block';
    }

    function updatePollingStatus(message) {
        pollingStatusContent.textContent = message;
        pollingFeedbackArea.style.display = 'block';
    }

    function clearOutputs() {
         feedbackArea.style.display = 'none';
         qrCodeImageArea.style.display = 'none';
         qrImageElement.src = ''; // Clear previous QR image
         pollingFeedbackArea.style.display = 'none';
         updatePollingStatus('Waiting to start...');
         stopPolling(); // Ensure any previous polling is stopped
    }

    function getSessionId(actionName = 'perform action') {
        const sessionId = sessionIdInput.value.trim();
        if (!sessionId) {
            showFeedback(`Please enter a Session ID to ${actionName}.`, 'error');
            return null;
        }
        // Basic validation - adjust if your IDs have different rules
        if (!/^[a-zA-Z0-9\-]+$/.test(sessionId)) {
             showFeedback(`Invalid Session ID format. Only alphanumeric characters and hyphens are allowed.`, 'error');
             return null;
        }
        return sessionId;
    }

    function stopPolling() {
        if (pollingTimer) {
            clearTimeout(pollingTimer);
            pollingTimer = null;
        }
        isPolling = false;
        monitorButton.disabled = false; // Re-enable button
        console.log("Polling stopped.");
    }

    // --- Fetch Functions (Calling Laravel Backend) ---

    // Fetches QR Code Image Blob from Laravel backend
    async function fetchQrImageBlob(sessionId) {
        // Construct the URL to the Laravel route, replacing placeholder
        const url = `{{ route('whatsapp.session.qr.image', ['sessionId' => 'SESSION_ID_PLACEHOLDER']) }}`
                    .replace('SESSION_ID_PLACEHOLDER', encodeURIComponent(sessionId));

        console.log(`Fetching QR Image from: ${url}`);
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'image/png', // Important: Tell backend we want an image
                'X-Requested-With': 'XMLHttpRequest' // Often helpful for Laravel
            }
        });

        if (!response.ok) {
            // Try to parse error JSON if backend sent one
            let errorJson = null;
            try {
                errorJson = await response.json();
            } catch (e) { /* Ignore if response is not JSON */ }
            throw {
                status: response.status,
                error: errorJson?.error || `Failed to fetch QR image (HTTP ${response.status})`
            };
        }

        // Check content type *again* to be sure backend sent an image
        const contentType = response.headers.get('Content-Type');
        if (!contentType || !contentType.startsWith('image/')) {
             throw { status: 502, error: 'Backend did not return a valid image file.' };
        }

        return await response.blob(); // Return the image data as a Blob
    }

    // Fetches Status JSON from Laravel backend
    async function fetchSessionStatus(sessionId) {
         const url = `{{ route('whatsapp.session.status', ['sessionId' => 'SESSION_ID_PLACEHOLDER']) }}`
                     .replace('SESSION_ID_PLACEHOLDER', encodeURIComponent(sessionId));

        console.log(`Fetching Status from: ${url}`);
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json(); // Assume backend always returns JSON (even for errors)

        if (!response.ok) {
             throw { status: response.status, error: data.error || `Failed to fetch status (HTTP ${response.status})` };
        }
        if (!data.success) {
             throw { status: response.status, error: data.error || 'API indicated unsuccessful status check.' };
        }

        return data; // Return the parsed JSON { success: true, state: '...', message: '...' }
    }


    // --- Polling Logic ---
    async function checkStatus(sessionId) {
        if (!isPolling) return;

        pollingAttempts++;
        updatePollingStatus(`Attempt ${pollingAttempts}/${maxPollingAttempts}: Checking status...`);

        if (pollingAttempts > maxPollingAttempts) {
            showFeedback('Polling timed out. User may not have scanned the QR code.', 'warning');
            updatePollingStatus(`Timed out after ${maxPollingAttempts} attempts.`);
            stopPolling();
            return;
        }

        try {
            const statusData = await fetchSessionStatus(sessionId); // Call backend
            console.log("Received Status:", statusData);
            updatePollingStatus(`Attempt ${pollingAttempts}/${maxPollingAttempts}: Status is '${statusData.state}'.`);

            // --- Check the 'state' field from the response ---
            if (statusData.state === 'CONNECTED') {
                showFeedback(`Session '${sessionId}' is now CONNECTED!`, 'success');
                updatePollingStatus(`CONNECTED! (Attempt ${pollingAttempts})`);
                qrCodeImageArea.style.display = 'none'; // Hide QR once connected
                stopPolling();

                // --- !!! NEXT STEP HERE !!! ---
                console.log("Session connected successfully.");
                // You could redirect, enable other parts of the UI, etc.
                alert(`Session ${sessionId} successfully connected!`);

            } else if (statusData.state === 'SCAN_QR_CODE') {
                // Continue polling
                pollingTimer = setTimeout(() => checkStatus(sessionId), pollingInterval);
            } else {
                // Handle other states reported by the API (e.g., TIMEOUT, OFFLINE, ERROR)
                showFeedback(`Session '${sessionId}' status changed to '${statusData.state}'. Monitoring stopped. Message: ${statusData.message || 'N/A'}`, 'warning');
                updatePollingStatus(`Stopped. Final status: ${statusData.state}`);
                 qrCodeImageArea.style.display = 'none'; // Hide QR on other final states
                stopPolling();
            }

        } catch (error) {
             console.error('Polling Error:', error);
             const errorMsg = error.error || `An unexpected error occurred (Status: ${error.status || 'N/A'})`;
             showFeedback(`Error during status check: ${errorMsg}`, 'error');
             updatePollingStatus(`Error on attempt ${pollingAttempts}. Stopping.`);
             qrCodeImageArea.style.display = 'none'; // Hide QR on error
             stopPolling();
        }
    }

     // --- Main Monitoring Workflow Trigger ---
     async function startSessionMonitoring() {
        clearOutputs();
        const sessionId = getSessionId('start and monitor');
        if (!sessionId) return;

        monitorButton.disabled = true;
        showFeedback(`Requesting QR code for session '${sessionId}'...`, 'info');
        pollingFeedbackArea.style.display = 'block'; // Show monitoring area early
        updatePollingStatus('Fetching QR code...');

        try {
             // 1. Fetch QR Code Image from Laravel Backend
            const imageBlob = await fetchQrImageBlob(sessionId);
            const qrImageUrl = URL.createObjectURL(imageBlob); // Create local URL for the blob

            // 2. Display QR Code
            qrImageElement.src = qrImageUrl;
            qrImageElement.alt = `WhatsApp QR Code for ${sessionId}`;
            qrCodeImageArea.style.display = 'block';
            showFeedback(`QR Code for '${sessionId}' displayed. Waiting for scan...`, 'info');

            // 3. Start Polling Status
            pollingAttempts = 0;
            isPolling = true;
            updatePollingStatus('Waiting for scan (Attempt 0)...');
            // Start first check slightly delayed to allow rendering
            pollingTimer = setTimeout(() => checkStatus(sessionId), 1000);

        } catch (error) {
            console.error("Monitoring Setup Error:", error);
            const errorMsg = error.error || `An unexpected error occurred (Status: ${error.status || 'N/A'})`;
            showFeedback(`Failed to get QR Code or start monitoring: ${errorMsg}`, 'error');
            updatePollingStatus('Failed to initiate monitoring.');
            monitorButton.disabled = false; // Re-enable on failure
        }
    }

    // --- Event Listeners ---
    monitorButton.addEventListener('click', startSessionMonitoring);

    // Add listeners for other buttons if you have them (e.g., restart, terminate)
    // Example:
    // const restartButton = document.getElementById('restartButton');
    // if(restartButton) {
    //     restartButton.addEventListener('click', async () => {
    //         const sessionId = getSessionId('restart');
    //         if (!sessionId) return;
    //         // Add fetch call to your Laravel restart endpoint here
    //         showFeedback(`Restart request sent for ${sessionId} (Simulated)`, 'info');
    //     });
    // }

</script>
@stop