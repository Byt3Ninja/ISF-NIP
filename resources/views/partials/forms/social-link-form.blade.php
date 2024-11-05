<form class="form d-flex flex-column h-100" id="basicForm" action="{{ URL::to('dashboard/update_settings') }}"
  method="POST" enctype="multipart/form-data">
  {!! csrf_field() !!}

  <div class="form-content flex-grow-1">
    @foreach ($Icons as $Icon)
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="{{ $Icon->img_url }}">{{ __('settings.URLOF') }} -
          {{ $l == 'ar' ? $Icon->ar_title : $Icon->title }}</label>
        <input type="text" class="form-control" value="{{ $Icon->url }}" name="{{ $Icon->img_url }}">
      </div>
    @endforeach
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
