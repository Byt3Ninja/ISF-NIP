<div class="card">
  <div class="card-body">
    <div class="border-bottom mb-4">
      <h5 class="card-title">{{ $tab['label'] }}</h5>
    </div>
    @if($_user->hasAnyRole('Admin|organizationManager'))
    <form method="post" action="{{ route('company.profile_update_mine', $client->company->id) }}"
      enctype="multipart/form-data">
@else
<form method="post" action="{{ route('company.profile_update_mine') }}"
  enctype="multipart/form-data">
@endif
      {!! csrf_field() !!}
      <input type="hidden" value="{{ \App\Enums\CompanyFormStep::SocialWebsite->value }}" name="CompanyN">
      <div class="row">
        <!-- Upload Company Logo -->
        <div class="col-12 col-md-2 justify-content-center d-flex">
          <div class="d-flex flex-column" style="width: fit-content;">
            <div class="w-px-100 text-center">
              <label class="fs-6 form-label fw-semibold mb-1" class="form-label fw-medium">{{ __('admin.Logo') }}</label>
            </div>
            <div id="holder" class="company-picker-container">
              <div class="position-relative w-px-100 h-px-100 mb-3 cursor-pointer">
                
                <img 
                  id="company-logo" 
                  src="{{ $client->company ? asset($client->company->logo) : '/images/logo-placeholder.png' }}" 
                  onerror="this.src = '/images/logo-placeholder.png';"
                  alt="Company logo" 
                  class="img-fluid rounded border w-px-100 h-px-100" 
                />
                <i class="ti ti-camera-plus position-absolute bottom-0 end-0 p-2"></i>
              </div>
              <input 
                type="file" 
                class="form-control mt-2 d-none" 
                id="file1" 
                name="img_url" 
                accept="image/*" 
              />
            </div>

            @if ($client->company && $client->company->logo) 
              <div class="w-px-100 text-center">
                <button
                  type="button" 
                  id="reset-company-logo"
                  onclick="document.querySelector('#company-logo').src = '/images/logo-placeholder.png'; document.querySelector('#file1').value = '';"
                  class="btn btn-label-secondary btn-sm"
                >
                  {{ $l == 'en' ? 'Reset' : 'إعادة تعيين' }}
                </button>
              </div>
            @endif
          </div>
        </div>
        <!-- /Upload Company Logo -->

        <!-- Social Media Links -->
        <div class="col-md-10">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="linkedin">{{ __('admin.Linkedin') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="linkedin" 
                    name="linkedin" 
                    placeholder="{{ $l == 'en' ? 'LinkedIn URL' : 'رابط لينكد إن' }}" 
                    value="{{ $client->company->linkedin ?? old('linkedin') }}" 
                    pattern="https://www.linkedin.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid LinkedIn URL.' : 'يرجى إدخال رابط لينكد إن صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-linkedin"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid LinkedIn URL' : 'رابط لينكد إن غير صالح' }}</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="twitter">{{ __('admin.Twitter') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="twitter" 
                    name="twitter" 
                    placeholder="{{ $l == 'en' ? 'Twitter URL' : 'رابط تويتر' }}" 
                    value="{{ $client->company->twitter ?? old('twitter') }}" 
                    pattern="https://twitter.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Twitter URL.' : 'يرجى إدخال رابط تويتر صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-twitter"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Twitter URL' : 'رابط تويتر غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="facebook">{{ __('admin.Facebook') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="facebook" 
                    name="facebook" 
                    placeholder="{{ $l == 'en' ? 'Facebook URL' : 'رابط فيسبوك' }}" 
                    value="{{ $client->company->facebook ?? old('facebook') }}" 
                    pattern="https://www.facebook.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Facebook URL.' : 'يرجى إدخال رابط فيسبوك صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-facebook"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Facebook URL' : 'رابط فيسبوك غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="youtube">{{ __('admin.Youtube') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="youtube" 
                    name="youtube" 
                    placeholder="{{ $l == 'en' ? 'YouTube URL' : 'رابط يوتيوب' }}" 
                    value="{{ $client->company->youtube ?? old('youtube') }}" 
                    pattern="https://www.youtube.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid YouTube URL.' : 'يرجى إدخال رابط يوتيوب صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-youtube"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid YouTube URL' : 'رابط يوتيوب غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="website">{{ __('admin.Website') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="website" 
                    name="website" 
                    placeholder="{{ $l == 'en' ? 'Website URL' : 'رابط الموقع' }}" 
                    value="{{ $client->company->website ?? old('website') }}" 
                    pattern="https?://.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid website URL.' : 'يرجى إدخال رابط موقع صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-world"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Website URL' : 'رابط الموقع غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="google_map">{{ __('admin.Google Map full URL') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="google_map" 
                    name="google_map" 
                    placeholder="{{ $l == 'en' ? 'Google Map URL' : 'رابط خريطة جوجل' }}" 
                    value="{{ $client->company->google_map ?? old('google_map') }}" 
                    pattern="https://maps.app.goo.gl/LmV6vEbR9obgCYn3A/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Google Map URL.' : 'يرجى إدخال رابط خريطة جوجل صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-map-pin"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Google Map URL' : 'رابط خريطة جوجل غير صالح' }}.</div>
              </div>
            </div>


            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="instagram">{{ __('admin.Instagram') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="instagram" 
                    name="instagram" 
                    placeholder="{{ $l == 'en' ? 'Instagram URL' : 'رابط انستجرام' }}" 
                    value="{{ $client->company->instagram ?? old('instagram') }}" 
                    pattern="https://www.instagram.com.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Instagram URL.' : 'يرجى إدخال رابط انستجرام صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-map-pin"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid Instagram URL.' : 'يرجى إدخال رابط انستجرام صالح.' }}.</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="tiktok">{{ __('admin.Tiktok') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="tiktok" 
                    name="tiktok" 
                    placeholder="{{ $l == 'en' ? 'Tiktok URL' : 'رابط انستجرام' }}" 
                    value="{{ $client->company->tiktok ?? old('tiktok') }}" 
                    pattern="https://www.tiktok.com.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Tiktok URL.' : 'يرجى إدخال رابط تيك توك صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-map-pin"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid Tiktok URL.' : 'يرجى إدخال رابط تيك توك صالح.' }}.</div>
              </div>
            </div>




          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="mt-12 text-end">
        <button type="submit" class="btn btn-primary">
          {{ __('admin.Update') }}
          <i class="ti ti-edit ms-2"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const pickerContainer = document.querySelector('.company-picker-container');
    const fileInput = document.querySelector('#file1');

    pickerContainer.addEventListener('click', () => {
      fileInput.click();
    });

    fileInput.addEventListener('change', (event) => {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.querySelector('#company-logo').src = e.target.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    });
  });
  
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#company-social-form');
    const inputs = form.querySelectorAll('#company-social-form input[type="url"]');

    inputs.forEach(input => {
      input.addEventListener('input', () => {
        const parent = input.closest('.input-group');
        if (input.checkValidity()) {
          parent.classList.remove('is-invalid');
        } else {
          parent.classList.add('is-invalid');
        }
      });
    });

    form.addEventListener('submit', (e) => {
      let isValid = true;
      inputs.forEach(input => {
        const parent = input.closest('.input-group');
        if (!input.checkValidity()) {
          e.preventDefault();
          parent.classList.add('is-invalid');
          isValid = false;
        }
      });
      if (!isValid) {
        e.preventDefault();
      }
    });
  });
</script>
