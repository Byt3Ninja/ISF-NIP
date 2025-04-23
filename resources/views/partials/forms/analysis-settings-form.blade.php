<form class="form d-flex flex-column h-100" action="{{ route('settings.system_section_two_update') }}" method="POST" enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group">
      <label class="form-label mb-2" for="gtag">{{ __('settings.Google Analytics Code') }}</label>
      <input type="text" class="form-control" value="{{ $Title->url }}" name="gtag" id="gtag">
    </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
