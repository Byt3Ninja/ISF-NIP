<div class="row g-4" id="step-3-stats">
  <div class="col-12">
    <div class="card shadow-none border">
      <div class="p-3">
        <h5 class="card-title">{{ __('admin.Visibility') }}</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="stats_visibility" name="stats_visibility" checked>
          <label class="form-check-label" for="stats_visibility">{{ __('admin.public') }}</label>
        </div>
        <small class="text-muted d-block mt-2" id="stats_visibility_desc">
          {{ __('admin.visibility_public_desc') ?? 'Stats are currently visible to everyone.' }}
        </small>
      </div>
    </div>
  </div>
  
  @php($stats = range(1, 4)) 
  @foreach ($stats as $stat)
    <div class="col-md-6" id="stat{{ $stat }}">
      <div class="card shadow-none border">
        <div class="card-header px-4 pb-2">
          <h6 class="card-title mb-0">{{ __('admin.stat') }} {{ $stat }}</h6>
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

@push('scripts')
<script>
  $('#stats_visibility').change(function() {
    $('#stats_visibility_desc').text(
      $(this).is(':checked') 
        ? '{{ __("admin.visibility_public_desc") ?? "Stats are currently visible to everyone." }}' 
        : '{{ __("admin.visibility_private_desc") ?? "Stats are private and only visible to you." }}'
    );
  }).trigger('change');
</script>
@endpush
