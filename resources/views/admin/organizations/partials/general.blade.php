<form class="form" action= "{{ route('settings.organization.general.manage') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <input type="hidden" name="id" value="{{ $organization?->id }}">
    
  <div class="row">

    <div class="col-6">
        <div class="form-group mb-3">
            <label class="form-label" for="name_en">{{__('admin.Organization')}}</label>
            <input id="name_en" type="text" class="form-control @error('organization_name') is-invalid @enderror" name="organization_name" value="{{ old('organization_name', $organization?->name_en) }}" required>
            @error('organization_name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-6">
        <div class="form-group mb-3">
            <label class="form-label" for="contact_email">{{__('admin.Email')}}</label>
            <input id="contact_email" type="email" class="form-control @error('contact_email') is-invalid @enderror" name="contact_email" value="{{ old('contact_email', $organization?->contact_email) }}" required>
            @error('contact_email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-12 col-12">
        <div class="form-group mb-3">
            <label class="form-label" for="img_url">{{__('admin.Image')}}</label>
            <input type="file" class="form-control @error('img_url') is-invalid @enderror" name="img_url" id="img_url" accept="image/*" onchange="previewImage(event)">
            @error('img_url')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div class="mt-3">
                <img id="preview-image" src="{{ $organization?->imageUrl }}" alt="Preview Image" width="100" style="display: {{ $organization?->img_url ? 'block' : 'none' }};">
            </div>            
        </div>
    </div>

 

    <div class="col-6">
        <div class="form-group mb-3">
            <label class="form-label" for="contact_phone">{{__('admin.Phone')}}</label>
            <input id="contact_phone" type="text" class="form-control @error('contact_phone') is-invalid @enderror" name="contact_phone" value="{{ old('contact_phone', $organization?->contact_phone) }}" required>
            @error('contact_phone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-6">
        <div class="form-group mb-3">
            <label class="form-label" for="address">{{__('admin.Address')}}</label>
            <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $organization?->address) }}" required>
            @error('address')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-6">
        <div class="form-group mb-3">
            <label class="form-label" for="time_zone">{{__('admin.Timezone')}}</label>
            <select class="form-control @error('time_zone') is-invalid @enderror" name="time_zone" id="time_zone" required>
                <option value="">{{__('admin.Select Timezone')}}</option>
                @foreach ($timezones as $timezoneKey => $timezoneName)
                    <option value="{{ $timezoneKey }}" {{ $timezoneKey == old('time_zone', $organization?->time_zone) ? 'selected' : '' }}>
                        {{ $timezoneName }}
                    </option>
                @endforeach
            </select>
            @error('time_zone')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label" for="Currency">{{ __('settings.Currency') }} *</label>
            <select class="form-control @error('currency_id') is-invalid @enderror" id="currency_id" name='currency_id'>
              <option value="">{{ __('settings.Currency') }}</option>
                @foreach ($currencies as $currency)
                    <option value="{{ $currency->id }}" {{ $currency->id == old('currency_id', $organization?->currency_id) ? 'selected' : '' }}>
                        {{ $currency->currency_name }}
                    </option>
                @endforeach
            </select>
            @error('currency_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

    <div class="col-12 d-flex justify-content-end mt-6">
      <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
    </div>
    
</form>
  
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var preview = document.getElementById('preview-image');
            preview.src = reader.result;
            preview.style.display = "block";
        }
        reader.readAsDataURL(event.target.files[0]);
    }

</script>