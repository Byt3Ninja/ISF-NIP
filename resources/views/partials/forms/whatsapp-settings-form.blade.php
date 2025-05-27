<form class="form d-flex flex-column h-100" action="{{ route('settings.system_social_whatsapp_update') }}" method="POST"
  enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="whatsApp_URL_callback">{{ __('settings.whatsApp_URL_callback') }}</label>
      <input id="whatsApp_URL_callback" type="text" class="form-control" value="{{ $Title->whatsApp_URL_callback }}"
        name="whatsApp_URL_callback">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="whatsApp_INSTANCE_ID">{{ __('settings.whatsApp_INSTANCE_ID') }}</label>
      <input type="text" class="form-control" value="{{ $Title->whatsApp_INSTANCE_ID }}" name="whatsApp_INSTANCE_ID"
        id="whatsApp_INSTANCE_ID">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="whatsApp_ACCESS_TOKEN">{{ __('settings.whatsApp_ACCESS_TOKEN') }}</label>
      <input type="text" class="form-control" value="{{ $Title->whatsApp_ACCESS_TOKEN }}" name="whatsApp_ACCESS_TOKEN"
        id="whatsApp_ACCESS_TOKEN">
    </div>

    

    </div>






  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
<h1>WhatsApp Session Manager</h1>

        <div class="action-group">
            <h2>Manage Specific Session</h2>
            <label for="sessionIdInput">Session ID:</label>
            <input type="text" id="sessionIdInput" placeholder="e.g., my-session-name">

            {{-- Button to trigger the primary workflow --}}
            <button id="monitorButton">Start & Monitor Session</button>
            <hr style="margin: 15px 0; border: none; border-top: 1px dashed #eee;">

            {{-- Add other direct action buttons if needed --}}
            {{-- <button id="restartButton" class="secondary">Restart</button> --}}
            {{-- <button id="terminateButton" class="danger">Terminate</button> --}}
        </div>

        {{-- Feedback Area --}}
        <div id="feedbackArea" class="feedback" style="display: none;"></div>

        {{-- QR Code Display Area --}}
         <div id="qrCodeImageArea" class="output-area" style="display: none;">
             <strong>Scan QR Code:</strong>
              <p style="font-size: 0.9em; color: #666; text-align: center;">Scan this QR code with WhatsApp on your phone.</p>
             <img id="qrImageElement" src="" alt="WhatsApp QR Code">
         </div>

        {{-- Polling Feedback Area --}}
        <div id="pollingFeedbackArea" class="output-area" style="display: none;">
             <strong>Monitoring Status:</strong>
             <span id="pollingStatusContent">Waiting to start...</span>
         </div>