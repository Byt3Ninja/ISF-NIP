<div id="accountSection">
  {{-- Required fields come from User::PROFILE_REQUIRED_FIELDS (the server rules read the same list). --}}
  @php($requiredFields = $client->requiredProfileFields())
  <form 
    id="account-info-form" 
    method="post" 
    action="{{ route('user.profile_update_basic') }}" 
    enctype="multipart/form-data"
  >
    {!! csrf_field() !!}
    <div class="card-body">

      <!-- Upload Avatar -->
      <div class="d-flex align-items-start align-items-sm-center gap-6">
        <img
          id="uploadedAvatar"
          class="d-block w-px-100 h-px-100"
          alt="user-avatar"
          src="{{ $client->image_url ? $client->image_url : '/images/user-placeholder.jpeg' }}"
          style="border-radius: 10px;"
          onerror="this.src='/images/user-placeholder.jpeg'"
        />
        <div class="button-wrapper">
          <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
            <span class="d-none d-sm-block">{{ __('admin.Upload_New_Image') }}</span>
            <i class="ti ti-upload d-block d-sm-none"></i>
            <input type="hidden" name="should_remove_image" id="should_remove_image" value="0" />
            <input
              hidden
              type="file"
              name="img_url"
              class="account-file-input"
              id="upload"
              accept="image/png, image/jpg, image/jpeg"
            />
          </label>
          <button type="button" class="btn btn-label-secondary account-image-reset mb-4">
            <i class="ti ti-refresh-dot d-block d-sm-none"></i>
            <span class="d-none d-sm-block">{{ __('admin.Reset') }}</span>
          </button>
          <div>{{ __('admin.Image_Format') }}</div>
        </div>
      </div>

      <!-- Personal Information -->
      <div class="row mt-8">

        <div class="mb-4 col-md-6">
          <label for="first_name_en" class="form-label fs-6 fw-bold">
            {{ __('admin.First Name') }} @if(in_array('first_name_en', $requiredFields))<span class="text-danger">*</span>@endif
          </label>
          <input value="{{ $client->first_name_en }}" name="first_name_en" type="text" class="form-control" id="first_name_en" placeholder="{{ __('admin.First Name') }}" @required(in_array('first_name_en', $requiredFields)) autocomplete="first_name_en" autofocus oninput="this.value = this.value.trimStart()" />
        </div>

        <div class="mb-4 col-md-6">
          <label for="last_name_en" class="form-label fs-6 fw-bold">
            {{ __('admin.Last Name') }} @if(in_array('last_name_en', $requiredFields))<span class="text-danger">*</span>@endif
          </label>
          <input value="{{ $client->last_name_en }}" name="last_name_en" type="text" class="form-control" id="last_name_en" style="direction: ltr;" placeholder="{{ __('admin.Last Name') }}" @required(in_array('last_name_en', $requiredFields)) autocomplete="last_name_en" oninput="this.value = this.value.trimStart()" />
        </div>

        <div class="mb-4 col-md-6">
          <label for="email" class="form-label fs-6 fw-bold">
            {{ __('admin.E-mail') }} <span class="text-danger">*</span>
          </label>
          <input value="{{ $client->email }}" name="email" type="email" class="form-control" id="email" style="direction: ltr;" placeholder="{{ __('admin.E-mail') }}" required autocomplete="email" oninput="this.value = this.value.trimStart()" />

          @if ($client->new_email)
            {{-- A change is staged: the address above stays active until the new one is confirmed. --}}
            <div class="alert alert-warning d-flex flex-column gap-2 mt-3 mb-0 py-3" role="status">
              <div>
                <i class="ti ti-mail-forward me-1"></i>
                <strong>{{ __('admin.PendingEmailChange') }}</strong>
              </div>
              <div class="small">
                {{ __('admin.PendingEmailChangeNotice', ['email' => $client->new_email]) }}
              </div>
              <div class="d-flex gap-2 flex-wrap">
                {{-- These buttons submit the sibling forms rendered after this one; they
                     cannot be nested here because forms may not contain forms. --}}
                <button type="button" data-form="resend-email-confirmation-form" class="btn btn-sm btn-outline-primary change-email-cta-button">
                  {{ __('admin.ResendConfirmation') }}
                </button>
                <button type="button" data-form="cancel-email-change-form" class="btn btn-sm btn-outline-secondary change-email-cta-button">
                  {{ __('admin.CancelEmailChange') }}
                </button>
              </div>
            </div>
          @endif
        </div>

        <div class="mb-4 col-md-6">
          <label for="gender" class="form-label fs-6 fw-bold">{{ __('admin.Gender') }}</label>
          <select name="gender" id="gender" class="select2 form-select">
            <option value="Male" {{ $client->gender == 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
            <option value="Female" {{ $client->gender == 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
          </select>
        </div>

        <div class="mb-4 col-md-6">
          <label for="title_en" class="form-label fs-6 fw-bold">
            {{ __('admin.JobTitleEn') }} @if(in_array('title_en', $requiredFields))<span class="text-danger">*</span>@endif
          </label>
          <input value="{{ $client->title_en }}" name="title_en" type="text" class="form-control" id="title_en" style="direction: ltr;" placeholder="{{ __('admin.JobTitleEn') }}" @required(in_array('title_en', $requiredFields)) autocomplete="title_en" oninput="this.value = this.value.trimStart()" />
        </div>

        <hr class="w-100 my-3" />

        <!-- Phone -->
        <div class="mb-6 col-12 col-md-6">
          <label class="form-label fs-6 fw-bold" for="phone">
            {{ __('auth.Phone') }} @if(in_array('phone', $requiredFields))<span class="text-danger">*</span>@endif
          </label>
          <div class="d-flex align-items-center flex-wrap gap-4">
            <input type="hidden" name="country_code" id="country_code" value="{{ $client->country_code ?? '20' }}" />
            <input
              id="phone"
              type="tel"
              class="form-control intl-tel-input"
              placeholder="{{ __('auth.Phone') }}"
              value="{{ $client->phone }}"
              name="phone"
              autocomplete="phone"
              @required(in_array('phone', $requiredFields))
            />
          </div>
          <span class="invalid-feedback" id="phone-error" style="display: none;"></span>
        </div>

        <!-- Whatsapp -->
        <div class="mb-6 col-12 col-md-6">
          <label class="form-label fs-6 fw-bold" for="whatsapp">
            {{ __('admin.WhatsApp') }}
          </label>
          <div class="d-flex align-items-center flex-wrap gap-4">
            <input type="hidden" name="country_code_w_a" id="country_code_w_a" value="{{ $client->country_code_w_a ?? '20' }}" />
            <input
              id="whatsapp"
              name="whatsapp"
              type="tel"
              class="form-control intl-tel-input"
              placeholder="{{ __('admin.WhatsApp') }}"
              value="{{ $client->whatsapp ?? old('whatsapp') }}"
              autocomplete="whatsapp"
            />
            <button type="button" id="sendWhatsappButton" class="btn btn-sm btn-primary">
              {{ __('auth.Verify') }}
            </button>
          </div>
          <span class="invalid-feedback" id="whatsapp-error" style="display: none;"></span>
        </div>

        <hr class="w-100" />

        <div class="mb-4 mt-8 col-md-6">
          <label for="address" class="form-label fs-6 fw-bold">{{ __('admin.Address') }} @if(in_array('address', $requiredFields))<span class="text-danger">*</span>@endif</label>
          <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('admin.Address') }}" value="{{ $client->address }}" oninput="this.value = this.value.trimStart()" @required(in_array('address', $requiredFields)) />
        </div>

        <div class="mb-4 mt-8 col-md-6">
          <label for="zip_code" class="form-label fs-6 fw-bold">{{ __('admin.Zip_Code') }}</label>
          <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="e.g. 231465" maxlength="6" value="{{ $client->zip_code }}" oninput="this.value = this.value.trimStart()" />
        </div>

        <div class="mb-4 col-md-6">
          <label for="country_id" class="form-label fs-6 fw-bold">
            {{ __('admin.Country') }} @if(in_array('country_id', $requiredFields))<span class="text-danger">*</span>@endif
          </label>
          <select id="country_id" name="country_id" class="select2 form-select" @required(in_array('country_id', $requiredFields))>
            <option value="" disabled {{ !$client->country_id ? 'selected' : '' }}>{{ __('admin.Country') }}</option>
            @foreach ($countries as $country)
            <option value="{{ $country->id }}" {{ $country->id == $client->country_id ? 'selected' : '' }}>
              {{ $country->emoji . ' ' . $country->{'name_' . $l} }}
            </option>
            @endforeach
          </select>
        </div>

        <div class="mb-4 col-md-6">
          <label for="state_id" class="form-label fs-6 fw-bold">
            {{ __('admin.State') }} @if(in_array('state_id', $requiredFields))<span class="text-danger">*</span>@endif
          </label>
          <select class="select2 form-select" @required(in_array('state_id', $requiredFields)) id="state_id" name="state_id">
            <option value="" disabled>{{ __('admin.State') }}</option>
            @if ($client->state_id)
            <option value="{{ $client->state_id }}" selected>{{ $client->state->{'name_' . $l} }}</option>
            @endif
          </select>
        </div>

        @if(Auth::user()->hasAnyRole(['Registered']))
          <div class="mb-4 col-md-6">
            <label for="HaveCompany" class="form-label fs-6 fw-bold">{{ __('auth.DoYouHaveCompany') }}</label>
            <select name="HaveCompany" id="HaveCompany" class="select2 form-select">
              <option value="1" {{ $client->HaveCompany == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
              <option value="0" {{ $client->HaveCompany == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
            </select>
          </div>
        @endif

        <div class="mb-4 col-md-6">
          <label for="language" class="form-label fs-6 fw-bold">{{ __('admin.Language') }}</label>
          <select id="language" name="language" class="select2 form-select">
            <option value="">{{ __('admin.Select Language') }}</option>
            <option value="en" {{ $client->language == 'en' ? 'selected' : '' }}> {{ $l == 'en' ? 'English' : 'الانجليزية' }} </option>
            <option value="ar" {{ $client->language == 'ar' ? 'selected' : '' }}> {{ $l == 'en' ? 'Arabic' : 'العربية' }} </option>
          </select>
        </div>
        
        <div class="mb-4 col-md-6">
          <label for="timeZones" class="form-label fs-6 fw-bold">{{ __('admin.Timezone') }} @if(in_array('timezone', $requiredFields))<span class="text-danger">*</span>@endif</label>
          <select id="timeZones" name="timezone" class="select2 form-select" @required(in_array('timezone', $requiredFields))>
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
              <option value="{{ $Currency->currency_code }}" {{ ($client->currency ?? optional($client->company)->Currency) == $Currency->currency_code ? 'selected' : '' }}>
                {{ $Currency->name }}
              </option>
            @endforeach
          </select>
        </div>

        @if(Auth::user()->hasAnyRole(['Registered']))
          <hr class="w-100 my-3" />

          <div class="mb-4 col-md-6">
            <label for="academic_stat" class="form-label fs-6 fw-bold">
              {{ __('admin.Education_Status') }} @if(in_array('academic_stat', $requiredFields))<span class="text-danger">*</span>@endif
            </label>
            <select name="academic_stat" id="academic_stat" class="select2 form-select" @required(in_array('academic_stat', $requiredFields))>
              <option value="" disabled {{ !$client->academic_stat ? 'selected' : '' }}>{{ __('admin.Select_Education_Status') }}</option>
              <option value="Graduate" {{ $client->academic_stat == 'Graduate' ? 'selected' : '' }}>{{ __('admin.Graduate') }}</option>
              <option value="Student" {{ $client->academic_stat == 'Student' ? 'selected' : '' }}>{{ __('admin.Student') }}</option>
            </select>
          </div>

          <div class="mb-4 col-md-6">
            <label for="dob" class="form-label fs-6 fw-bold">
              {{ __('admin.date_of_birth') }} @if(in_array('dob', $requiredFields))<span class="text-danger">*</span>@endif
            </label>
            <input type="text" class="form-control dob-picker" id="dob" name="dob" value="{{ $client->dob ? \Carbon\Carbon::parse($client->dob)->format('Y-m-d') : '' }}" placeholder="YYYY-MM-DD" @required(in_array('dob', $requiredFields)) />
          </div>

          <div class="mb-4 col-md-6">
            <label for="national_id" class="form-label fs-6 fw-bold">
              {{ __('admin.national_id') }} @if(in_array('national_id', $requiredFields))<span class="text-danger">*</span>@endif
            </label>
            <input type="text" class="form-control" id="national_id" name="national_id" placeholder="{{ __('admin.national_id') }}" value="{{ old('national_id', $client->national_id ?? '') }}" oninput="this.value = this.value.trim()" autocomplete="off" @required(in_array('national_id', $requiredFields)) />
            <span class="invalid-feedback" id="national-id-error" style="display: none;"></span>
          </div>
        @endif

      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary me-3">{{ __('admin.Save_Changes') }}</button>
        {{--  <button type="reset" class="btn btn-label-secondary" onclick="location.reload()">{{ __('admin.Reset') }}</button>  --}}
      </div>

    </div>
  </form>

  @if ($client->new_email)
    {{-- Targets of the buttons inside the pending-email banner above. Kept outside the
         account form because nested forms are invalid HTML. --}}
    <form id="resend-email-confirmation-form" method="post" action="{{ route('user.profile_email_resend') }}" class="d-none">
      {!! csrf_field() !!}
    </form>
    <form id="cancel-email-change-form" method="post" action="{{ route('user.profile_email_cancel') }}" class="d-none">
      {!! csrf_field() !!}
    </form>
  @endif
</div>
