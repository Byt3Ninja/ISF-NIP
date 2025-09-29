<div class="alert alert-{{ $type }} alert-dismissible" role="alert">
  {{ $message }} {{ Session::forget('msg') }}
  <button 
    type="button" 
    class="btn-close" 
    data-bs-dismiss="alert" 
    aria-label="Close">
  </button>
</div>
