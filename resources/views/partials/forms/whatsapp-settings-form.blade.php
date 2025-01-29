<form class="form d-flex flex-column h-100" action="{{ route('updateSMWhatsApp') }}" method="POST"
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






  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
