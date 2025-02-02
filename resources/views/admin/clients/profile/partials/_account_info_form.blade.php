<div id="accountSection">
  <form method="post" action="{{ route('update_my_profile') }}" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <div class="card-body">
      <!-- Upload Avatar -->
      <div class="d-flex align-items-start align-items-sm-center gap-6">
        <img id="uploadedAvatar" class="d-block w-px-100 h-px-100" alt="user-avatar"
          src="{{ $client->image_url ? $client->image_url : '/images/user-placeholder.jpeg' }}"
          style="border-radius: 10px;" onerror="this.src='/images/user-placeholder.jpeg'" />
        <div class="button-wrapper">
          <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
            <span class="d-none d-sm-block">{{ __('admin.Upload_New_Image') }}</span>
            <i class="ti ti-upload d-block d-sm-none"></i>
            <input hidden type="file" name="img_url" class="account-file-input" id="upload"
              accept="image/png, image/jpg, image/jpeg" max="2048" />
          </label>
          <button type="button" class="btn btn-label-secondary account-image-reset mb-4">
            <i class="ti ti-refresh-dot d-block d-sm-none"></i>
            <span class="d-none d-sm-block">{{ __('admin.Reset') }}</span>
          </button>
          <div>{{ __('admin.Image_Format') }}</div>
        </div>
      </div>
      <!-- /Upload Avatar -->

      <!-- Personal Information -->
      <div class="row mt-8">
        <div class="mb-4 col-md-6">
          <label for="first_name_en" class="form-label fs-6 fw-bold">{{ __('admin.English UserName')
            }}</label>
          <input value="{{ $client->first_name_en }}" name="first_name_en" type="text" class="form-control"
            id="first_name_en" style="direction: ltr;" placeholder="{{ __('admin.First Name') }}" required
            autocomplete="first_name_en" autofocus max="255" min="2" />
        </div>
        <div class="mb-4 col-md-6">
          <label for="last_name_en" class="form-label fs-6 fw-bold">{{ __('admin.Last Name') }}</label>
          <input value="{{ $client->last_name_en }}" name="last_name_en" type="text" class="form-control"
            id="last_name_en" style="direction: ltr;" placeholder="{{ __('admin.Last Name') }}" required
            autocomplete="last_name_en" max="255" min="2" />
        </div>
        {{-- <div class="mb-4 col-md-6">
          <label for="first_name_ar" class="form-label fs-6 fw-bold">{{ __('admin.Arabic First Name')
            }}</label> <input value="{{ $client->first_name_ar }}" name="first_name_ar" type="text" class="form-control"
            id="first_name_ar" style="direction: rtl;" placeholder="{{ __('admin.First Name') }}" required
            autocomplete="first_name_ar" max="255" min="2" autofocus />
        </div> --}}
        {{-- <div class="mb-4 col-md-6">
          <label for="last_name_ar" class="form-label fs-6 fw-bold">{{ __('admin.Arabic Last Name')
            }}</label>
          <input value="{{ $client->last_name_ar }}" name="last_name_ar" type="text" class="form-control"
            id="last_name_ar" style="direction: rtl;" placeholder="{{ __('admin.Last Name') }}" required
            autocomplete="last_name_ar" max="255" min="2" />
        </div> --}}
        <div class="mb-4 col-md-6">
          <label for="email" class="form-label fs-6 fw-bold">{{ __('admin.E-mail') }}</label>
          <input value="{{ $client->email }}" name="email" type="email" class="form-control" id="email"
            style="direction: ltr;" placeholder="{{ __('admin.E-mail') }}" required autocomplete="email">
        </div>
        <div class="mb-4 col-md-6">
          <label for="gender" class="form-label fs-6 fw-bold">{{ __('admin.Gender') }}</label>
          <select name="gender" id="gender" class="select2 form-select">
            <option value="Male" {{ $client->gender == 'Male' ? 'selected' : '' }}>{{ __('Male') }}
            </option>
            <option value="Female" {{ $client->gender == 'Female' ? 'selected' : '' }}>{{ __('Female') }}
            </option>
          </select>
        </div>
        <div class="mb-4 col-md-6">
          <label for="title_ar" class="form-label fs-6 fw-bold">{{ __('admin.JobTitleAr') }}</label>
          <input value="{{ $client->title_ar }}" name="title_ar" type="title_ar" class="form-control" id="title_ar"
            style="direction: rtl;" placeholder="{{ __('admin.JobTitleAr') }}" required autocomplete="title_ar">
        </div>
        <div class="mb-4 col-md-6">
          <label for="title_en" class="form-label fs-6 fw-bold">{{ __('admin.JobTitleEn') }}</label>
          <input value="{{ $client->title_en }}" name="title_en" type="title_en" class="form-control" id="title_en"
            style="direction: ltr;" placeholder="{{ __('admin.JobTitleEn') }}" required autocomplete="title_en">
        </div>

        <hr class="w-100 my-3" />

        <!-- Phone -->
        <div class="mb-6 col-12 col-md-6">
          {{-- <div class="w-100"> --}}
            <label class="form-label fs-6 fw-bold" for="phone">{{ __('auth.Phone') }}</label>
            <div class="d-flex align-items-center flex-wrap gap-4">
              <input type="hidden" name="country_code" id="country_code" value="{{ $client->country_code }}" />
              <input id="phone" type="tel" class="form-control" style="width: 350px;"
                placeholder="{{ __('auth.Phone') }}" value="{{ $client->phone }}" name="phone" autocomplete="phone"
                oninput="this.value = this.value.trim()" />
              {{-- <button type="button" id="sendButton" onclick="sendCode('phone')" class="btn btn-sm btn-primary">
                {{ __('auth.Verify') }}
              </button> --}}
            </div>
            <span class="text-danger text-start" id="phone-error" style="display: none;"></span>
            {{--
          </div> --}}
        </div>
        <!--/ Phone -->

        {{--
        <hr class="w-75" /> --}}

        <!-- Whatsapp -->
        <div class="mb-6 col-12 col-md-6">
          {{-- <div class="w-100"> --}}
            <label class="form-label fs-6 fw-bold" for="whatsapp">{{ __('admin.WhatsApp') }}</label>
            <div class="d-flex align-items-center flex-wrap gap-4">
              <input type="hidden" name="country_code_w_a" id="country_code_w_a"
                value="{{ $client->country_code_w_a }}" />
              <input id="whatsapp" name="whatsapp" type="tel" class="form-control" style="width: 350px;"
                placeholder="{{ __('admin.WhatsApp') }}"
                value="{{ $client->whatsapp ? $client->whatsapp : old('whatsapp') }}" autocomplete="whatsapp"
                oninput="this.value = this.value.trim()" />
              <button type="button" id="sendButton" onclick="sendCode('whatsapp')" class="btn btn-sm btn-primary">
                {{ __('auth.Verify') }}
              </button>
            </div>
            <span class="text-danger" id="whatsapp-error" style="display: none;"></span>
            {{--
          </div> --}}
        </div>
        <!--/ Whatsapp -->

        <hr class="w-100" />

        {{-- <div class="mb-4 col-12 d-grid">
          <label class="form-label fs-6 fw-bold" for="whatsapp">{{ __('admin.WhatsApp') }}</label>
          <input class="form-control whatsapp-input" id="whatsapp" name="whatsapp" type="tel"
            value="{{ $client->whatsapp ? $client->whatsapp : old('whatsapp') }}"
            placeholder="{{ __('admin.WhatsApp') }}" autocomplete="whatsapp" oninput="this.value = this.value.trim()" />
          @if($client->whatsapp_verified == 0)
          <button type="button" id="sendButton" onclick="sendCode('whatsapp')" class="btn btn-primary"
            style="position: absolute; right: 10px;">
            {{ __('Send Code') }}
          </button>
          <div id="error" class="alert alert-danger" style="display: none"></div>
          <div id="sentMessage" class="alert alert-success" style="display: none"></div>
          <div class="form-group form-focus" style="position: relative; display: flex; align-items: center;">

            <input id="OTP" type="text" class="form-control floating @error('OTP') is-invalid @enderror" name="otp"
              value="{{ old('otp') }}" required autocomplete="otp" autofocus>
            <label for="OTP" class="focus-label">{{ __('Add OTP') }}</label>
            <button type="button" onclick="verifyCode()" class="btn btn-primary"
              style="position: absolute; right: 10px;">
              {{ __('Verify Code') }}
            </button>
            @error('OTP')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
            @enderror

          </div>
          <div id="errorotp" class="alert alert-danger" style="display: none"></div>
          <div id="sentMessageotp" class="alert alert-success" style="display: none"></div>
          @endif

          <span class="text-danger" id="whatsapp-error" style="display: none;"></span>
        </div> --}}


        <div class="mb-4 mt-8 col-md-6">
          <label for="address" class="form-label fs-6 fw-bold">{{ __('admin.Address') }}</label>
          <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('admin.Address') }}"
            value="{{ $client->address }}" />
        </div>
        <div class="mb-4 mt-8 col-md-6">
          <label for="zip_code" class="form-label fs-6 fw-bold">{{ __('admin.Zip_Code') }}</label>
          <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="231465" maxlength="6"
            value="{{ $client->zip_code }}" />
        </div>
        <div class="mb-4 col-md-6">
          <label for="country_id" class="form-label fs-6 fw-bold">{{ __('admin.Country') }}</label>
          <select id="country_id" name='country_id' class="select2 form-select" required>
            <option value="">{{ __('admin.Country') }}</option>
            @foreach ($countries as $country)
            <option value="{{ $country->id }}" {{ $country->id == $client->country_id ? 'selected' : '' }}>
              {{ $country->emoji . ' ' . $country->{'name_' . $l} }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-4 col-md-6">
          <label for="state_id" class="form-label fs-6 fw-bold">{{ __('admin.State') }}</label>
          <select class="select2 form-select" required id="state_id" name='state_id'>
            <option value="">{{ __('admin.State') }}</option>
            @php $name = 'name_'.$l; @endphp
            @if ($client->state_id)
            <option value="{{ $client->state_id }}" selected>{{ $client->state->{'name_' . $l} }}</option>
            @endif
          </select>
        </div>
        <div class="mb-4 col-md-6">
          <label for="HaveCompany" class="form-label fs-6 fw-bold">{{ __('auth.DoYouHaveCompany') }}</label>
          <select name="HaveCompany" id="HaveCompany" class="select2 form-select">
            <option value="1" {{ $client->HaveCompany == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
            <option value="0" {{ $client->HaveCompany == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
          </select>
        </div>
        <div class="mb-4 col-md-6">
          <label for="language" class="form-label fs-6 fw-bold">{{__('admin.Language')}}</label>
          <select id="language" name="language" class="select2 form-select">
            <option value="">{{ __('admin.Select Language') }}</option>
            <option value="en" {{ $client->language == 'en' ? 'selected' : '' }} >English</option>
            <option value="ar" {{ $client->language == 'ar' ? 'selected' : '' }} >Arabic</option>
          </select>
        </div>
        <div class="mb-4 col-md-6">
          <label for="timeZones" class="form-label fs-6 fw-bold">{{ __('admin.Timezone') }}</label>
          <select id="timeZones" name="timezone" class="select2 form-select">
            <option value="">{{ __('admin.Select Timezone') }}</option>
            @foreach (timezone_identifiers_list() as $timezone)
            <option value="{{ $timezone }}" {{ $client->timezone == $timezone ? 'selected' : '' }}>
              {{ '(GMT' . \Carbon\Carbon::now($timezone)->format('P') . ') ' . $timezone }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="mb-4 col-12 col-md-6">
          <label for="currency" class="form-label fs-6 fw-bold">{{ __('admin.Currency') }}</label>
          <select id="currency" name="currency" class="select2 form-select">
            <option value="">{{ __('admin.Select Currency') }}</option>
            @foreach ($Currencies as $Currency)
            <option value="{{ $Currency->currency_code }}" {{ $client->currency && $client->currency ==
              $Currency->currency_code ? 'selected' : '' }}> {{ $Currency->currency_name }} </option>
            @endforeach
          </select>
        </div>


      </div>
      <!-- /Personal Information -->

      <div class="mt-4">
        <button type="submit" class="btn btn-primary me-3">{{ __('admin.Save_Changes') }}</button>
        <button type="reset" class="btn btn-label-secondary">{{ __('admin.Cancel') }}</button>
      </div>
    </div>
  </form>
</div>