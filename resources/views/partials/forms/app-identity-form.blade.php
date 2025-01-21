<form class="form d-flex flex-column h-100" action="{{ route('updatelogo') }}" method="POST" enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group mb-3">
      <label class="form-label mb-2" for="logo">{{ __('settings.Logo') }}</label>
      <input id="logo" type="file" class="form-control" name="logo"
        onchange="previewImage(event, 'logoPreview')">
      <img id="logoPreview" src="{{ $Title->header ? asset('storage/uploads/' . $Title->Logo) : '/images/upload.jpeg' }}" alt="Logo" class="img-fluid mt-2 rounded"
        style="width: 200px; height: 100px;">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-2" for="header">{{ __('settings.header') }}</label>
      <input id="header" type="file" class="form-control" name="header"
        onchange="previewImage(event, 'headerPreview')">
        <img 
        id="headerPreview" 
        src="{{ $Title->header ? asset('storage/uploads/' . $Title->header) : '/images/upload.jpeg' }}" 
        alt="Header" 
        class="img-fluid mt-2 rounded" 
        style="width: 200px; height: 100px;">
    </div>

    <div class="form-group mb-3">
      <label class="form-label mb-2" for="footer">{{ __('settings.footer') }}</label>
      <input id="footer" type="file" class="form-control" name="footer"
        onchange="previewImage(event, 'footerPreview')">
      <img id="footerPreview" src="{{ $Title->header ? asset('storage/uploads/' . $Title->footer) : '/images/upload.jpeg' }}" alt="Footer" class="img-fluid mt-2 rounded"
        style="width: 200px; height: 100px;">
    </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>

<script>
  function previewImage(event, previewId) {
    const reader = new FileReader();
    reader.onload = function() {
      const output = document.getElementById(previewId);
      output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }
</script>
