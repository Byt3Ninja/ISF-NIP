<form class="form d-flex flex-column h-100" action="{{ route('settings.system_mail_update') }}" method="POST" enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="host">{{ __('settings.host') }}</label>
      <input id="host" type="text" class="form-control" value="{{ $Title->host }}" name="host">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="Mail">{{ __('settings.Mail') }}</label>
      <input id="Mail" type="text" class="form-control" value="{{ $Title->Mail }}" name="Mail">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="Mailpassword">{{ __('settings.Mailpassword') }}</label>
      <input type="text" class="form-control" value="{{ $Title->Mailpassword }}" name="Mailpassword"
        id="Mailpassword">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="Port">{{ __('settings.Port') }}</label>
      <input type="text" class="form-control" value="{{ $Title->Port }}" name="Port" id="Port">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="Port">{{ __('settings.Encryption') }}</label>
      <select class="form-control" name="Encryption" id="Encryption">
        <option value="tls" {{ $Title->Encryption == 'tls' ? 'selected' : '' }}>tls</option>
        <option value="ssl" {{ $Title->Encryption == 'ssl' ? 'selected' : '' }}>ssl</option>
      </select>
    </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
