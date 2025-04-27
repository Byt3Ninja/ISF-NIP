<form class="form d-flex flex-column h-100" action="{{ route('settings.system_push_notifications_update') }}" method="POST" enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group mb-3">
      <label class="form-label mb-1">{{ __('settings.Select BroadCast') }}</label>
      <fieldset class="form-label-group">
        <select class="form-control" id="BroadCast" name="BroadCast" required>
          <option value="0" selected></option>
          <option value="pusher" {{ $Title->BroadCast == 'pusher' ? 'selected' : '' }}>Pusher</option>
        </select>
      </fieldset>
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-1" for="PUSHER_APP_KEY">{{ __('settings.PUSHER_APP_KEY') }}</label>
      <input id="PUSHER_APP_KEY" type="text" class="form-control" value="{{ $Title->PUSHER_APP_KEY }}"
        name="PUSHER_APP_KEY">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-1" for="PUSHER_APP_ID">{{ __('settings.PUSHER_APP_ID') }}</label>
      <input type="text" class="form-control" value="{{ $Title->PUSHER_APP_ID }}" name="PUSHER_APP_ID"
        id="PUSHER_APP_ID">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-1" for="PUSHER_APP_SECRET">{{ __('settings.PUSHER_APP_SECRET') }}</label>
      <input type="text" class="form-control" value="{{ $Title->PUSHER_APP_SECRET }}" name="PUSHER_APP_SECRET"
        id="PUSHER_APP_SECRET">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-1" for="PUSHER_APP_CLUSTER">{{ __('settings.PUSHER_APP_CLUSTER') }}</label>
      <input type="text" class="form-control" value="{{ $Title->PUSHER_APP_CLUSTER }}" name="PUSHER_APP_CLUSTER"
        id="PUSHER_APP_CLUSTER">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-1" for="MIX_PUSHER_APP_KEY">{{ __('settings.MIX_PUSHER_APP_KEY') }}</label>
      <input type="text" class="form-control" value="{{ $Title->MIX_PUSHER_APP_KEY }}" name="MIX_PUSHER_APP_KEY"
        id="MIX_PUSHER_APP_KEY">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-1" for="MIX_PUSHER_APP_CLUSTER">{{ __('settings.MIX_PUSHER_APP_CLUSTER') }}</label>
      <input type="text" class="form-control" value="{{ $Title->MIX_PUSHER_APP_CLUSTER }}"
        name="MIX_PUSHER_APP_CLUSTER" id="MIX_PUSHER_APP_CLUSTER" required>
    </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
