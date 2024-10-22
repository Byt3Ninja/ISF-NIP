<style>
  .picker-container {
    width: 100%;
  }
  .picker-container img {
    width: 100%;
    height: 120px;
    cursor: pointer;
  }

  @media screen and (min-width: 768px) {
    .picker-container {
      width: 13%;
    }
    .picker-container img {
      width: 90px;
      height: 90px;
    }
  }
</style>

<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title">{{ $tab['label'] }}</h5>
    </div>
    <form method="post" action="{{ route('update_my_company') }}"
      enctype="multipart/form-data">
      {!! csrf_field() !!}

      <input type="hidden" value="8" name="CompanyN">
      <div class="d-flex flex-column flex-md-row align-items-center gap-4">

        <!-- Upload Company Logo -->
        <div
          class="d-flex flex-column gap-1 justify-content-between align-items-center picker-container">
          <span class="text-center text-black fw-medium">{{ __('admin.Logo') }}</span>
          <div class="position-relative">
            @if (isset($client->company->logo))
            <div id="holder">
              <img 
                id="company-logo holder" 
                src="{{ $client->company->logo }}"
                alt="Company logo"
                class="mb-2 radius-15"
                 
              />
            </div>
            @else
            <div id="holder">
              <img 
                id="company-logo" 
                src="/images/background/logo.png"
                alt="Default logo"
                style="width: 90px; height: 90px; border-radius: 10px; cursor: pointer;"
                class="mb-2"
                 
              />
            </div>
            @endif
              <input type="text" class="form-control d-none" id="file1"
                name="img_url"  />
            <!-- right bottom-icon -->
            
          </div>
          <button 
            class="btn btn-outline-primary rounded-pill btn-sm lfm" data-input="file1" data-preview="holder" >
            {{ $l == 'en' ? 'Reset' : 'إعادة تعيين' }}
          </button>
        </div>
        <!-- / Upload Company Logo -->

        <div class="d-flex flex-column mt-8">
          <div class="row">
            <div class="col-md-4">
              <div class="position-relative">
                <input type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  {{-- style="height: 42px;" --}} id="linkedin"
                  placeholder="{{ __('admin.Linkedin') }}"
                  value="{{ $client->company ? $client->company->linkedin : old('linkedin') }}"
                  name="linkedin" autocomplete="new-linkedin">
                <img src="/icons/linkedin.svg"
                  style="height: 20px; width: 20px; left: 7px;"
                  class="position-absolute top-50 translate-middle-y me-3" />
              </div>
            </div>
            <div class="col-md-4">
              <div class="position-relative">
                <input type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  {{-- style="height: 42px;" --}} id="twitter"
                  placeholder="{{ __('admin.Twitter') }}"
                  value="{{ $client->company ? $client->company->twitter : old('twitter') }}"
                  name="twitter" autocomplete="twitter">
                <i class="fa-brands fa-twitter fs-5 position-absolute top-50 translate-middle-y me-3 text-info"
                  style="left: 7px;"></i>
              </div>
            </div>
            <div class="col-md-4">
              <div class="position-relative">
                <input type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  {{-- style="height: 42px;" --}} id="facebook"
                  placeholder="{{ __('admin.Facebook') }}"
                  value="{{ $client->company ? $client->company->facebook : old('facebook') }}"
                  name="facebook" autocomplete="facebook">
                <i class="fa-brands fa-facebook-f fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
            <div class="col-md-4">
              <div class="position-relative">
                <input type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  {{-- style="height: 42px;" --}} id="youtube"
                  placeholder="{{ __('admin.Youtube') }}"
                  value="{{ $client->company ? $client->company->youtube : old('youtube') }}"
                  name="youtube" autocomplete="new-youtube">
                <i class="fa-brands fa-youtube position-absolute top-50 translate-middle-y me-3 text-danger"
                  style="left: 7px;"></i>
              </div>
            </div>
            <div class="col-md-4">
              <div class="position-relative">
                <input type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  {{-- style="height: 42px;" --}} id="website"
                  placeholder="{{ __('admin.Website') }}"
                  value="{{ $client->company ? $client->company->website : old('website') }}"
                  name="website" autocomplete="website">
                <i class="ti ti-world position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
            <div class="col-md-4">
              <div class="position-relative">
                <input type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  {{-- style="height: 42px;" --}} id="google_map" name="google_map"
                  placeholder="{{ __('admin.Google Map full URL') }}"
                  value="{{ $client->company ? $client->company->google_map : old('google_map') }}">
                <i class="ti ti-map-pin position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
          </div>
          {{-- <div class="row">
          <div class="col-md-6 col-12">
            <div class="position-relative">
              <label class="fw-medium mb-1"
                for="name_en">{{ __('admin.English Name') }} *</label>
              <div class="position-relative">
                <input
                  value="{{ $client->company ? $client->company->name_en : old('name_en') }}"
                  name="name_en" type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  id="name_en" placeholder="{{ __('admin.English Name') }} *"
                  required autocomplete="name_en" />
                <i class="ti ti-building-skyscraper position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-12">
            <div class="position-relative">
              <label class="fw-medium mb-1"
                for="name_ar">{{ __('admin.Arabic Name') }} *</label>
              <div class="position-relative">
                <input style="direction: rtl;"
                  value="{{ $client->company ? $client->company->name_ar : old('name_ar') }}"
                  name="name_ar" type="text"
                  class="form-control px-10 input-shadow border-light mb-4"
                  id="name_ar" placeholder="{{ __('admin.Arabic Name') }} *"
                  required autocomplete="name_ar" />
                <i class="ti ti-building-skyscraper position-absolute top-50 translate-middle-y me-3 text-primary"
                  style="left: 7px;"></i>
              </div>
            </div>
          </div>
        </div> --}}
          <div class="row mt-4">
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary"
                style="border-radius: 10px;">
                {{ __('admin.Update') }}
              </button>
            </div>
          </div>
        </div>

      </div>


      {{-- <div class="input-group">
      <span class="input-group-btn">
        <a data-input="thumbnail" data-preview="holderimg"
          class="btn btn-primary lfm">
          <i class="fa fa-picture-o"></i> {{ __('admin.Choose') }}
        </a>
      </span>
      <input id="thumbnail" class="form-control" type="text" name="img_url">
    </div>
    <div id="holderimg" style="margin-top:15px;max-height:100px;"></div> --}}

      {{-- <div class="col-md-6 col-12 mb-3 ">
      <div class="form-label-group">
        <label class="fs-6 mb-2"
          for="facebook">{{ __('admin.Website') }}</label>
        <input style="direction: ltr" type="text" class="form-control mt-2"
          id="facebook" placeholder="{{ __('admin.Website') }}"
          value="{{ $client->company ? $client->company->website : old('website') }}"
          name="website" autocomplete="website">
      </div>
    </div> --}}
      {{-- <div class="col-md-6 col-12 mb-3 ">
      <div class="form-label-group">
        <label class="fs-6 mb-2"
          for="facebook">{{ __('admin.Facebook') }}</label>
        <input style="direction: ltr" type="text" class="form-control mt-2"
          id="facebook" placeholder="{{ __('admin.Facebook') }}"
          value="{{ $client->company ? $client->company->facebook : old('facebook') }}"
          name="facebook" autocomplete="facebook">
      </div>
    </div> --}}
      {{-- <div class="col-md-6 col-12 mb-3 ">
      <div class="form-label-group">
        <label class="fs-6 mb-2"
          for="twitter">{{ __('admin.Twitter') }}</label>
        <input style="direction: ltr" type="text" class="form-control mt-2"
          id="twitter" placeholder="{{ __('admin.Twitter') }}"
          value="{{ $client->company ? $client->company->twitter : old('twitter') }}"
          name="twitter" autocomplete="twitter">
      </div>
    </div> --}}
      {{-- <div class="col-md-6 col-12 mb-3 ">
      <div class="form-label-group">
        <label class="fs-6 mb-2"
          for="linkedin">{{ __('admin.Linkedin') }}</label>
        <input style="direction: ltr" type="text" class="form-control mt-2"
          id="linkedin" placeholder="{{ __('admin.Linkedin') }}"
          value="{{ $client->company ? $client->company->linkedin : old('linkedin') }}"
          name="linkedin" autocomplete="new-linkedin">
      </div>
    </div> --}}
      {{-- <div class="col-md-6 col-12 mb-3 ">
      <div class="form-label-group">
        <label class="fs-6 mb-2"
          for="youtube">{{ __('admin.Youtube') }}</label>
        <input style="direction: ltr" type="text" class="form-control mt-2"
          id="youtube" placeholder="{{ __('admin.Youtube') }}"
          value="{{ $client->company ? $client->company->youtube : old('youtube') }}"
          name="youtube" autocomplete="new-youtube">
      </div>
    </div> --}}
      {{-- <div class="col-md-6 col-12 mb-3 ">
      <div class="form-group">
        <label class="fs-6 mb-2"
          for="google_map">{{ __('admin.Google Map') }}</label>
        <textarea style="direction: ltr" class="form-control height-100" id="google_map"
          name='google_map' placeholder="{{ __('admin.Google Map full URL') }}">{{ $client->company ? $client->company->google_map : old('google_map') }}</textarea>
      </div>
    </div> --}}
    </form>

  </div>
</div>
