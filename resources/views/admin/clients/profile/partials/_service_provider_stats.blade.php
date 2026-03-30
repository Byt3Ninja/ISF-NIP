@php
    $org = $client->service_provider ?? null;
    $showStats = $org ? $org->show_stats : true;
@endphp
<div class="row g-4" id="step-3-stats">
  <div class="col-12">
    <div class="card shadow-none border">
      <div class="p-3">
        <h5 class="card-title">{{ __('admin.Visibility') }}</h5>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="stats_visibility" name="stats_visibility" {{ $showStats ? 'checked' : '' }}>
          <label class="form-check-label" for="stats_visibility">{{ __('admin.public') }}</label>
        </div>
        <small class="text-muted d-block mt-2" id="stats_visibility_desc">
          {{ __('admin.visibility_public_desc') ?? 'Stats are currently visible to everyone.' }}
        </small>
      </div>
    </div>
  </div>

  <!-- Stat 1 -->
  <div class="col-md-6" id="stat1">
    <div class="card shadow-none border">
      <div class="card-header px-4 pb-2">
        <h6 class="card-title mb-0">{{ __('admin.stat') }} 1</h6>
      </div>
      <div class="card-body px-4 pb-2">
        <div class="mb-4">
          <label for="value1" class="form-label">{{ __('admin.Value') }}</label>
          <input type="number" class="form-control" min="0" id="value1" name="value1" value="{{ $org ? $org->stat1_value : '' }}" placeholder="{{ __('admin.Value') }}" maxlength="20" onkeydown="return !['-','.','e','E'].includes(event.key)"
       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
        <div class="mb-4">
          <label for="label1" class="form-label">{{ __('admin.label') }}</label>
          <input type="text" class="form-control" id="label1" name="label1" value="{{ $org ? $org->stat1_label : '' }}" placeholder="{{ __('admin.label') }}" maxlength="50">
        </div>
      </div>
    </div>
  </div>

  <!-- Stat 2 -->
  <div class="col-md-6" id="stat2">
    <div class="card shadow-none border">
      <div class="card-header px-4 pb-2">
        <h6 class="card-title mb-0">{{ __('admin.stat') }} 2</h6>
      </div>
      <div class="card-body px-4 pb-2">
        <div class="mb-4">
          <label for="value2" class="form-label">{{ __('admin.Value') }}</label>
          <input type="number" class="form-control" min="0" id="value2" name="value2" value="{{ $org ? $org->stat2_value : '' }}" placeholder="{{ __('admin.Value') }}" maxlength="20" onkeydown="return !['-','.','e','E'].includes(event.key)"
       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
        <div class="mb-4">
          <label for="label2" class="form-label">{{ __('admin.label') }}</label>
          <input type="text" class="form-control" id="label2" name="label2" value="{{ $org ? $org->stat2_label : '' }}" placeholder="{{ __('admin.label') }}" maxlength="50">
        </div>
      </div>
    </div>
  </div>

  <!-- Stat 3 -->
  <div class="col-md-6" id="stat3">
    <div class="card shadow-none border">
      <div class="card-header px-4 pb-2">
        <h6 class="card-title mb-0">{{ __('admin.stat') }} 3</h6>
      </div>
      <div class="card-body px-4 pb-2">
        <div class="mb-4">
          <label for="value3" class="form-label">{{ __('admin.Value') }}</label>
          <input type="number" class="form-control" min="0" id="value3" name="value3" value="{{ $org ? $org->stat3_value : '' }}" placeholder="{{ __('admin.Value') }}" maxlength="20" onkeydown="return !['-','.','e','E'].includes(event.key)"
       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
        <div class="mb-4">
          <label for="label3" class="form-label">{{ __('admin.label') }}</label>
          <input type="text" class="form-control" id="label3" name="label3" value="{{ $org ? $org->stat3_label : '' }}" placeholder="{{ __('admin.label') }}" maxlength="50">
        </div>
      </div>
    </div>
  </div>

  <!-- Stat 4 -->
  <div class="col-md-6" id="stat4">
    <div class="card shadow-none border">
      <div class="card-header px-4 pb-2">
        <h6 class="card-title mb-0">{{ __('admin.stat') }} 4</h6>
      </div>
      <div class="card-body px-4 pb-2">
        <div class="mb-4">
          <label for="value4" class="form-label">{{ __('admin.Value') }}</label>
          <input type="number" class="form-control" min="0" id="value4" name="value4" value="{{ $org ? $org->stat4_value : '' }}" placeholder="{{ __('admin.Value') }}" maxlength="20" onkeydown="return !['-','.','e','E'].includes(event.key)"
       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>
        <div class="mb-4">
          <label for="label4" class="form-label">{{ __('admin.label') }}</label>
          <input type="text" class="form-control" id="label4" name="label4" value="{{ $org ? $org->stat4_label : '' }}" placeholder="{{ __('admin.label') }}" maxlength="50">
        </div>
      </div>
    </div>
  </div>
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
