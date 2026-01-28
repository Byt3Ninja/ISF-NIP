<div class="row g-4" id="step-3-stats">
  @php($stats = range(1, 4)) 
  @foreach ($stats as $stat)
    <div class="col-md-6" id="stat{{ $stat }}">
      <div class="card shadow-none border">
        <div class="card-header px-4 pb-2">
          <h5 class="card-title mb-0">{{ __('admin.stat') }} {{ $stat }}</h5>
        </div>
        <div class="card-body px-4 pb-2">
          <div class="mb-4">
            <label for="value{{ $stat }}" class="form-label">{{ __('admin.Value') }}</label>
            <input type="text" class="form-control" id="value{{ $stat }}" name="value{{ $stat }}" maxlength="20">
          </div>
          <div class="mb-4">
            <label for="label{{ $stat }}" class="form-label">{{ __('admin.label') }}</label>
            <input type="text" class="form-control" id="label{{ $stat }}" name="label{{ $stat }}" maxlength="50">
          </div>
        </div>
      </div>
    </div>
  @endforeach
</div>
