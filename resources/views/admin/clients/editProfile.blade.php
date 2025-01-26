@extends('layouts.admin')
@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('vendorstyle')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@section('style')
<style>
  .iti__country-list {
    position: absolute;
    left: 1rem;
    /* right: 0; */
    z-index: 9999;
    list-style: none;
    text-align: left;
    padding: 0;
    margin: 0 0 0 -1px;
    box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
    background-color: white;
    border: 1px solid #CCC;
    white-space: nowrap;
    max-height: 200px;
    width: 350px;
    overflow-y: scroll;
    overflow-x: auto -webkit-overflow-scrolling: touch;
    background-color: #fff;
  }
</style>
@stop

@php
$phone_codes = App\Country::all();

$tabs = [
    ['account', 'accountSection', 'users'],
    ['security', 'securitySection', 'lock'],
    ['billing', 'billingSection', 'bookmark'],
    ['notifications', 'notificationsSection', 'bell'],
    ['connections', 'connectionsSection', 'link'],
];

// إضافة الشرط بشكل منفصل
if (auth()->user()->hasRole('Mentor')) {
  array_splice($tabs, 2, 0, [['info_profile', 'InfoProfileSection', 'users']]);
}

$tabsList = collect($tabs)->map(function ($tab) {
  return ['label' => __('admin.' . $tab[0]), 'target' => $tab[1], 'icon' => $tab[2]];
})->toArray();

$passwordRules = [
  $l == 'en' ? 'Minimum 8 characters long - the more, the better' : 'الحد الأدنى 8 أحرف - كلما زادت الأحرف كلما كان أفضل',
  $l == 'en' ? 'At least one lowercase character' : 'على الأقل حرف صغير واحد',
  $l == 'en' ? 'At least one number, symbol, or whitespace character' : 'على الأقل رقم أو رمز أو مسافة واحدة',
];
@endphp

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <div class="col-md-12">
        <!-- Tabs -->
        <nav class="nav-align-top">
          <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0" role="tablist">
            @foreach ($tabsList as $key => $tab)
            <li class="nav-item">
              <a href="#{{ $tab['target'] }}" class="nav-link {{$key == 0 ? 'active' : ''}}" data-bs-toggle="tab"
                role="tab" aria-controls="{{ $tab['target'] }}" aria-selected="true"
                @if (auth()->user()->hasRole('Mentor')) 
                disabled="{{ $key > 2 ? 'true' : 'false' }}"
                @else
                disabled="{{ $key > 1 ? 'true' : 'false' }}"
                @endif
              >
                <i class="ti-sm ti ti-{{ $tab['icon'] }} me-1_5"></i>
                {{ $tab['label'] }}
              </a>
            </li>
            @endforeach
          </ul>
        </nav>
        <!-- /Tabs -->

        <!-- Alerts -->
        @if (session()->has('msg'))
          <x-alert 
            type="{{ session()->get('class') ?? 'danger' }}"
            message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
          />
        @endif
        @if (count($errors) > 0)
         @foreach ($errors->all() as $error)
            <x-alert type="danger" message="{{ $error }}" />
          @endforeach
        @endif
        
        <!-- /Alerts -->

        <!-- Tab Content -->
        <div class="tab-content card rounded mb-6">
          <!-- Account section -->
          <div class="tab-pane fade show active" id="accountSection">
            <div id="accountSection">
              <form method="post" action="{{ route('update_my_profile') }}" enctype="multipart/form-data">
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
                        <input hidden type="file" name="img_url" class="account-file-input" id="upload"
                          accept="image/png, image/jpg, image/jpeg"
                            max="2048" 
                          />
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
                      <label for="first_name_en" class="form-label fs-6 fw-bold">{{ __('admin.English UserName') }}</label>
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
                    <div class="mb-4 col-md-6">
                      <label for="first_name_ar" class="form-label fs-6 fw-bold">{{ __('admin.Arabic First Name') }}</label> <input
                        value="{{ $client->first_name_ar }}" name="first_name_ar" type="text" class="form-control"
                        id="first_name_ar" style="direction: rtl;" placeholder="{{ __('admin.First Name') }}" required
                        autocomplete="first_name_ar" max="255" min="2" autofocus />
                    </div>
                    <div class="mb-4 col-md-6">
                      <label for="last_name_ar" class="form-label fs-6 fw-bold">{{ __('admin.Arabic Last Name') }}</label>
                      <input value="{{ $client->last_name_ar }}" name="last_name_ar" type="text"
                        class="form-control" id="last_name_ar" style="direction: rtl;"
                        placeholder="{{ __('admin.Last Name') }}" required autocomplete="last_name_ar" max="255" min="2" />
                    </div>
                    <div class="mb-4 col-md-6">
                      <label for="email" class="form-label fs-6 fw-bold">{{ __('admin.E-mail') }}</label>
                      <input value="{{ $client->email }}" name="email" type="email" class="form-control"
                        id="email" style="direction: ltr;" placeholder="{{ __('admin.E-mail') }}" required
                        autocomplete="email">
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
                      <input value="{{ $client->title_ar }}" name="title_ar" type="title_ar" class="form-control"
                        id="title_ar" style="direction: rtl;" placeholder="{{ __('admin.JobTitleAr') }}" required
                        autocomplete="title_ar">
                    </div>
                    <div class="mb-4 col-md-6">
                      <label for="title_en" class="form-label fs-6 fw-bold">{{ __('admin.JobTitleEn') }}</label>
                      <input value="{{ $client->title_en }}" name="title_en" type="title_en" class="form-control"
                        id="title_en" style="direction: ltr;" placeholder="{{ __('admin.JobTitleEn') }}" required
                        autocomplete="title_en">
                    </div>

                    <hr class="w-75 my-3" />

                    <!-- Phone -->
                    <div class="mb-6 col-12 mt-3">
                      <div class="w-100">
                        <label class="form-label fs-6 fw-bold" for="phone">{{ __('auth.Phone') }}</label>
                        <div class="d-flex align-items-center flex-wrap gap-4">
                          <input  type="hidden"  name="country_code"  id="country_code"  value="{{ $client->country_code }}" />
                          <input 
                            id="phone" 
                            type="tel"
                            class="form-control" 
                            style="width: 300px;"
                            placeholder="{{ __('auth.Phone') }}"
                            value="{{ $client->phone }}" 
                            name="phone" 
                            autocomplete="phone" 
                            oninput="this.value = this.value.trim()" 
                            required 
                          />
                          <button type="button" id="sendButton" onclick="sendCode('phone')" class="btn btn-sm btn-primary">
                            {{ __('auth.Verify') }}
                          </button>
                        </div>
                        <span class="text-danger text-start" id="phone-error" style="display: none;"></span>
                      </div>
                    </div>
                    <!--/ Phone -->

                    <hr class="w-75" />
                    
                    <!-- Whatsapp -->
                    <div class="mb-6 col-12">
                      <div class="w-100">
                        <label class="form-label fs-6 fw-bold" for="whatsapp">{{ __('admin.WhatsApp') }}</label>
                        <div class="d-flex align-items-center flex-wrap gap-4">
                          <input type="hidden" name="country_code_w_a" id="country_code_w_a" value="{{ $client->country_code_w_a }}" />
                          <input 
                            id="whatsapp"
                            name="whatsapp" 
                            type="tel"
                            class="form-control" 
                            style="width: 300px;"
                            placeholder="{{ __('admin.WhatsApp') }}"
                            value="{{ $client->whatsapp ? $client->whatsapp : old('whatsapp') }}"
                            autocomplete="whatsapp" 
                            oninput="this.value = this.value.trim()" 
                            required 
                          />
                          <button type="button" id="sendButton" onclick="sendCode('whatsapp')" class="btn btn-sm btn-primary">
                            {{ __('auth.Verify') }}
                          </button>
                        </div>
                        <span class="text-danger" id="whatsapp-error" style="display: none;"></span>
                      </div>
                    </div>
                    <!--/ Whatsapp -->

                    <hr class="w-75" />

                    {{-- <div class="mb-4 col-12 d-grid">
                      <label class="form-label fs-6 fw-bold" for="whatsapp">{{ __('admin.WhatsApp') }}</label>
                      <input class="form-control whatsapp-input" id="whatsapp"
                        name="whatsapp" type="tel"
                        value="{{ $client->whatsapp ? $client->whatsapp : old('whatsapp') }}"
                        placeholder="{{ __('admin.WhatsApp') }}" autocomplete="whatsapp"
                        oninput="this.value = this.value.trim()"
                        />
                        @if($client->whatsapp_verified == 0)
                        <button type="button" id="sendButton"  onclick="sendCode('whatsapp')" class="btn btn-primary" style="position: absolute; right: 10px;">
                          {{ __('Send Code') }}
                      </button>
                        <div id="error" class="alert alert-danger" style="display: none"></div>
                        <div id="sentMessage" class="alert alert-success" style="display: none"></div>
                        <div class="form-group form-focus" style="position: relative; display: flex; align-items: center;">

                            <input id="OTP" type="text" class="form-control floating @error('OTP') is-invalid @enderror" name="otp" value="{{ old('otp') }}" required autocomplete="otp" autofocus>
                            <label for="OTP"  class="focus-label">{{ __('Add OTP') }}</label>
                            <button type="button" onclick="verifyCode()" class="btn btn-primary" style="position: absolute; right: 10px;">
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
                      <input type="text" class="form-control" id="address" name="address" placeholder="{{ __('admin.Address') }}" value="{{ $client->address }}" />
                    </div>
                    <div class="mb-4 mt-8 col-md-6">
                      <label for="zip_code" class="form-label fs-6 fw-bold">{{ __('admin.Zip_Code') }}</label>
                      <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="231465"
                        maxlength="6" value="{{ $client->zip_code }}" />
                    </div>
                    <div class="mb-4 col-md-6">
                      <label for="country_id" class="form-label fs-6 fw-bold">{{ __('admin.Country') }}</label>
                      <select id="country_id" name='country_id' class="select2 form-select" required>
                        <option value="">{{ __('admin.Country') }}</option>
                        @foreach ($countries as $country)
                          <option value="{{ $country->id }}"
                            {{ $country->id == $client->country_id ? 'selected' : '' }}>
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
                        <option value="1" {{ $client->HaveCompany ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        <option value="" {{ $client->HaveCompany ? '' : 'selected' }}>{{ __('No') }}</option>
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
                          <option value="{{ $Currency->currency_code }}" {{ $client->currency && $client->currency == $Currency->currency_code ? 'selected' : '' }}> {{ $Currency->currency_name }} </option>
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
          </div>
          <!-- /Account section -->

          <!-- Security section -->
          <div class="tab-pane fade" id="securitySection">
            <div id="securitySection">
              <!-- Change Password -->
              <h5 class="card-header">{{ __('admin.Change_Password') }}</h5>
              <div class="card-body pt-1">
                <form method="post" action="{{ route('update_my_profile_password') }}" enctype="multipart/form-data">
                  {!! csrf_field() !!}
                  <div class="row">
                    <div class="mb-6 col-md-6 form-password-toggle">
                      <label class="form-label fs-6 fw-bold" for="password">{{ __('admin.Password') }}
                      </label>
                      <div class="input-group input-group-merge">
                        <input type="password" class="form-control" id="password"
                          placeholder="{{ __('admin.Password') }}" name="password" required autocomplete="new-password">
                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                      </div>
                    </div>
                    <div class="mb-6 col-md-6 form-password-toggle">
                      <label class="form-label fs-6 fw-bold" for="password-confirm">{{ __('admin.Confirm') }}</label>
                      <div class="input-group input-group-merge">
                        <input type="password" class="form-control" id="password-confirm"
                          placeholder="{{ __('admin.Password') }}" name="password_confirmation" required
                          autocomplete="new-password">
                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                      </div>
                    </div>
                  </div>
                  <h6 class="text-body">{{ __('admin.Password_Requirements') }}:</h6>
                  <ul class="ps-4 mb-0">
                    @foreach ($passwordRules as $key => $rule)
                    <li class="{{ $key != array_key_last($passwordRules) ? 'mb-4' : '' }}">{{ $rule }}</li>
                    @endforeach
                  </ul>
                  <div class="mt-6">
                    <button type="submit" class="btn btn-primary me-3">{{ __('admin.Save_Changes') }}</button>
                    <button type="reset" class="btn btn-label-secondary">{{ __('admin.Reset') }}</button>
                  </div>
                </form>
              </div>
              <!--/ Change Password -->
            </div>
          </div>
          <!-- /Security section -->
          
          <!-- Mentor fields -->
          @role('Mentor')
          <!-- Security section -->
      <div class="tab-pane fade" id="InfoProfileSection">
        <div id="InfoProfileSection">
          <h5 class="card-header">{{ __('admin.addbio') }}</h5>
            <div class="card-body pt-1">
                
              <form method="post" action="{{ route('updatebio_my_profile') }}" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <input type="hidden" name="mentor_section" value="1">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="col-md-12 col-12">
                          <label for="linkedin" class="form-label fs-6 fw-bold">{{ __('admin.Linkedin') }}</label>
                          <div class="form-group">
                            <input value="{{ $client->bio ? $client->bio->linkedin : '' }}" name="linkedin"
                              type="text" class="form-control" id="linkedin" style="direction: ltr;"
                              placeholder="{{ __('admin.Linkedin') }} - https://linkedin.com/in/yourname"
                              autocomplete="linkedin">
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mt-4">
                          <label class="form-label fs-6 fw-bold">{{ __('admin.arbio') }}: </label>
                          <div class="form-group">
                            <textarea class="form-control" id="bio_ar" name='bio_ar' rows="3" dir="rtl"
                              placeholder="{{ __('admin.writebioar') }}">{{ $client->bio->bio_ar ?? '' }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mt-4">
                          <label class="form-label fs-6 fw-bold">{{ __('admin.enbio') }}: </label>
                          <div class="form-group">
                            <textarea class="form-control" id="bio_en" name='bio_en' rows="3" dir="ltr"
                              placeholder="{{ __('admin.writebioen') }}">{{ $client->bio->bio_en ?? '' }}</textarea>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 col-12 mt-4">
                      <div class="form-label-group">
                        <h6 for="selectExpertises">{{ __('admin.SelectExpertises') }}</h6>
                        <p>{{ __('admin.SelectMultiExpertises') }}.</p>
                        <div class="form-group">
                          <select name="expertises[]" class="select2 form-control" multiple="multiple"
                            id="selectExpertises">
                            @foreach ($expertises as $expertise_group)
                              <optgroup label="{{ $expertise_group->title }}">
                                @foreach ($expertise_group->children as $expertise)
                                  <option value="{{ $expertise->id }}"
                                    @if (in_array($expertise->id, $client->mentorExpertises()->pluck('expertises.id')->toArray())) selected @endif>{{ $expertise->title }}
                                  </option>
                                @endforeach
                              </optgroup>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="mt-4 col-md-6">
                      <label for="basicInput" class="form-label fs-6 fw-bold">
                        {{ __('admin.Entrepreneurial Experience') }}
                      </label>

                      <select name="entrepreneurial_experience" id="entrepreneurial_experience"
                        class="select2 form-select">
                        <option value="1"
                          {{ optional($client->bio)->entrepreneurial_experience ? 'selected' : '' }}>
                          {{ __('Yes') }}</option>
                        <option value="0"
                          {{ optional($client->bio)->entrepreneurial_experience ? '' : 'selected' }}>
                          {{ __('No') }}</option>
                      </select>
                    </div>
                    <br>
                    <div class="mt-4 col-md-6">
                      <label class="form-label fs-6 fw-bold">{{ __('admin.Entrepreneurial Experience desc') }}.</label>
                      <textarea class="form-control" id="basicTextarea" rows="8" name="entrepreneurial_experience_brief" required>{{ $client->bio ? $client->bio->entrepreneurial_experience_brief : old('entrepreneurial_experience_brief') }}</textarea>
                      <label for="tcontent"
                        class="mt-2">{{ __('admin.Entrepreneurial Experience Brief') }}</label>
                    </div>
                    <div class="my-4 col-md-6">
                      <label for="profile_privacy"
                        class="form-label fs-6 fw-bold">{{ __('admin.Profile Privacy') }}</label>
                      <select name="profile_privacy" id="profile_privacy" class="select2 form-select">
                        <option value="1"
                          {{ optional($client->bio)->profile_privacy ? 'selected' : '' }}>
                          {{ __('Yes') }}</option>
                        <option value="0"
                          {{ optional($client->bio)->profile_privacy ? '' : 'selected' }}>
                          {{ __('No') }}</option>
                      </select>
                    </div>
                    <div class="col-md-12 col-12">
                      <label for="approach" class="form-label fs-6 fw-bold">{{ __('admin.mentorship approach') }}
                        :</label>
                      <div class="form-group" id="approach">
                        <div class="checkbox mr-5">
                          <input type='checkbox' id="checkbox1" class="checkbox-input"
                            name='mentorship_approach[]'
                            {{ $client->bio && in_array('E-mail', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                            value="E-mail">
                          <label for="checkbox1" class="form-label fs-6 fw-bold">
                            {{ __('admin.E-mail') }}
                          </label>
                        </div>
                        <div class="checkbox mr-5">
                          <input type='checkbox' id="checkbox2" class="checkbox-input"
                            name='mentorship_approach[]'
                            {{ $client->bio && in_array('phone calls', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                            value="phone calls">
                          <label for="checkbox2" class="form-label fs-6 fw-bold">
                            {{ __('admin.phone calls') }}
                          </label>
                        </div>
                        <div class="checkbox mr-5">
                          <input type='checkbox' id="checkbox3" class="checkbox-input"
                            name='mentorship_approach[]'
                            {{ $client->bio && in_array('video calls', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                            value="video calls">
                          <label for="checkbox3" class="form-label fs-6 fw-bold">
                            {{ __('admin.video calls') }}
                          </label>
                        </div>
                        <div class="checkbox mr-5">
                          <input type='checkbox' id="checkbox4" class="checkbox-input"
                            name='mentorship_approach[]'
                            {{ $client->bio && in_array('in-person meetings', $client->bio->mentorship_approaches) ? 'checked=""' : '' }}
                            value="in-person meetings">
                          <label for="checkbox4" class="form-label fs-6 fw-bold">
                            {{ __('admin.in-person meetings') }}
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <br>
                  <div class="mt-6">
                    <button type="submit" class="btn btn-primary me-3">{{ __('admin.Save_Changes') }}</button>
                    <button type="reset" class="btn btn-label-secondary">{{ __('admin.Reset') }}</button>
                  </div>
              </form>
                         
            </div>
        </div>
      </div>
        @endrole
        <!-- /Mentor fields -->




        </div>
        <!-- /Tab Content -->

        

        <!-- Deactivate account -->
        <div class="card mb-6">
          <h5 class="card-header">{{ __('admin.Delete Account') }}</h5>
          <div class="card-body">
            <div class="mb-6 col-12 mb-0">
              <div class="alert alert-warning">
                <h5 class="alert-heading mb-1">{{ __('admin.Wanna_Delete_Account') }}</h5>
                <p class="mb-0">{{ __('admin.Delete_Account_Alert') }}</p>
              </div>
            </div>
            <form id="formAccountDeactivation" onsubmit="return false">
              <div class="form-check my-8">
                <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
                <label class="form-check-label" for="accountActivation">{{ __('admin.Delete_Account_Confirm') }}</label>
              </div>
              <button type="submit" class="btn btn-danger deactivate-account" disabled>
                {{ __('admin.Deactivate_Account') }}
              </button>
            </form>
          </div>
        </div>
        <!-- /Deactivate account -->
      </div>
    </div>
  </div>
  <!-- / Content -->

  <!-- Verfiy Code Modal -->
  <div class="modal fade" id="verifyCodeModal" tabindex="-1" aria-labelledby="verifyCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header d-grid gap-2">
          <h4 class="modal-title" id="verifyCodeModalLabel">{{ __('auth.Verify_Number_With_Code') }}</h4>
          <h6 class="text-secondary" id="verifyCodeModalLabel">{{ __('auth.Enter_Code_Sent') }}</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group form-focus">
            <input 
              id="OTP" 
              type="text" 
              class="form-control floating @error('OTP') is-invalid @enderror" 
              placeholder="{{ __('auth.Enter_Code') }}"
              name="otp"
              value="{{ old('otp') }}" 
              required 
              autocomplete="otp" 
              autofocus
            />
            {{-- <label for="OTP" class="focus-label">{{ __('auth.Add_OTP') }}</label> --}}
            @error('OTP')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>
          <div id="errorotp" class="alert alert-danger" style="display: none"></div>
          <div id="sentMessageotp" class="alert alert-success" style="display: none"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            {{ __('auth.Cancel') }}
          </button>
          <button type="button" onclick="verifyCode()" class="btn btn-primary">
            {{ __('auth.Verify_Code') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <!--/ Verfiy Code Modal -->
  
  <!-- Verfiy Whatsapp Code Modal -->
  <div class="modal fade" id="verifyWhatsappCodeModal" tabindex="-1" aria-labelledby="verifyCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header d-grid gap-2">
          <h4 class="modal-title" id="verifyCodeModalLabel">{{ __('auth.Verify_Number_With_Code') }}</h4>
          <h6 class="text-secondary" id="verifyCodeModalLabel">{{ __('auth.Enter_Code_Sent') }}</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="form-group form-focus">
            <input 
              id="OTP" 
              type="text" 
              class="form-control floating @error('OTP') is-invalid @enderror" 
              placeholder="{{ __('auth.Enter_Code') }}"
              name="otp"
              value="{{ old('otp') }}" 
              required 
              autocomplete="otp" 
              autofocus
            />
            {{-- <label for="OTP" class="focus-label">{{ __('auth.Add_OTP') }}</label> --}}
            @error('OTP')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>
          <div id="errorotp" class="alert alert-danger" style="display: none"></div>
          <div id="sentMessageotp" class="alert alert-success" style="display: none"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            {{ __('auth.Cancel') }}
          </button>
          <button type="button" onclick="verifyWhatsappCode()" class="btn btn-primary">
            {{ __('auth.Verify_Code') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <!--/ Verfiy Whatsapp Code Modal -->

  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->
@stop

@section('scripts')
<!-- Vendors JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
@stop

@section('pagescripts')
<!-- Page JS -->
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script src="/vuexy/assets/js/pages-account-settings-account.js"></script>

<script>
  $(document).ready(function() {
      getStates($('#country_id').val());
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      var $state = $('#state_id');
      var currentState = '{{ $client->state_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('get_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          $state.html('<option value="" >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            // selected = '';
            if (currentState === id) {
              selected = 'selected';
              getCities(id)
            } else {
              selected = '';
            }
            $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
            $('#city_id').html('<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }

    function getCities(state_id) {
      var $city = $('#city_id');
      var currentCity = '{{ $client->city_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
          state_id: state_id
        },
        success: function(data) {
          $city.html('<option value="" >{{ __('admin.City') }}</option>');
          $.each(data, function(id, value) {
            if (currentCity == id) {
              selected = 'selected';
            } else {
              selected = '';
            }
            $city.append('<option value="' + id + '" ' + selected + ' >' + value + '</option>');
          });
          $('#city').show(150);
        }
      });
    }
</script>
@role('Mentor')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  var useDarkMode = window.matchMedia('(prefers-color-scheme: light)').matches;

  tinymce.init({
    selector: '#basicTextarea',
    plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons',
    imagetools_cors_hosts: ['picsum.photos'],
    menubar: 'edit view insert format tools table help',
    toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview  print | insertfile image media template link anchor codesample | ltr rtl',
    toolbar_sticky: true,
    autosave_ask_before_unload: true,
    autosave_interval: '30s',
    autosave_prefix: '{path}{query}-{id}-',
    autosave_restore_when_empty: false,
    autosave_retention: '2m',
    image_advtab: true,
    importcss_append: true,
    template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
    template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
    height: 400,
    image_caption: true,
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
    noneditable_noneditable_class: 'mceNonEditable',
    toolbar_mode: 'sliding',
    contextmenu: 'link image imagetools table',
    skin: useDarkMode ? 'oxide-dark' : 'oxide',
    content_css: useDarkMode ? 'dark' : 'default',
    content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:14px }',
    image_title: true,
    automatic_uploads: true,
    media_live_embeds: true,
    relative_urls: false,
    remove_script_host: false,
    images_upload_url: "{{ route('training.upload', ['_token' => csrf_token()]) }}",
    file_picker_types: 'image',
    convert_urls: true,
    file_picker_callback: function(cb, value, meta) {
      var input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/*');
      input.onchange = function() {
        var file = this.files[0];
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function() {
          var id = 'blobid' + (new Date()).getTime();
          var blobCache = tinymce.activeEditor.editorUpload.blobCache;
          var base64 = reader.result.split(',')[1];
          var blobInfo = blobCache.create(id, file, base64);
          blobCache.add(blobInfo);
          cb(blobInfo.blobUri(), {
            title: file.name
          });
        };
      };
      input.click();
    }
  });
  
  @if ($l == 'ar')
    tinyMCE.settings['directionality'] = 'rtl';
  @endif
</script>
<script>
  $(document).ready(function() {
    @if ($client->bio)
      switch ('{{ $client->bio->entrepreneurial_experience }}') {
        case '1':
          $('#entrepreneurial_experience_brief').show();
          break;
        case '0':
          $('#entrepreneurial_experience_brief').hide();
          break;
        default:
          $('#entrepreneurial_experience_brief').hide();
          break;
      } 
    @endif

    $('.entrepreneurial_experience').change(function() {
      var value = $(this).val();
      switch (value) {
        case '1':
          $('#entrepreneurial_experience_brief').show();
          break;
        case '0':
          $('#entrepreneurial_experience_brief').hide();
          break;
        default:
          $('#entrepreneurial_experience_brief').hide();
          break;
      }
    });
  });
</script>
@endrole

<script>
  const lang = '{{ $l }}';

  // Initialize the phone input
const phoneInput = document.getElementById("phone");
const whatsappInput = document.getElementById("whatsapp");
const phoneError = document.getElementById('phone-error');
const whatsappError = document.getElementById('whatsapp-error');

const countryCodeFromDB = "+{{ $client->country_code ? $client->country_code : '' }}".trim();
const countryCodeWAFromDB = "+{{ $client->country_code_w_a ? $client->country_code_w_a : '' }}".trim();

// Get country code from dial code
function getCountryCode(code) {
  let dialCode = code.toString().trim();
  const countryData = intlTelInputGlobals.getCountryData();
  const country = countryData.find(country => `+${country.dialCode}` === dialCode);

  if (country) {
    console.log("Country Code Found: " + country.iso2);
    return country.iso2;
  }
  return "eg";
}

// Initialize intl-tel-input
const iti = intlTelInput(phoneInput, {
  initialCountry: getCountryCode(countryCodeFromDB),
  preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
  utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
  formatOnDisplay: false,
  separateDialCode: true,
  autoPlaceholder: "aggressive",
  customPlaceholder: (placeholder) => "e.g. " + placeholder,
});

// Access the phone number with code
phoneInput.addEventListener('input', function() {
  const fullPhoneNumber = iti.getNumber();
  const countryCode = iti.getSelectedCountryData().dialCode;
  const countryCodeInput = document.getElementById('country_code');
  countryCodeInput.value = countryCode;

  if (!iti.isValidNumber() && fullPhoneNumber.length !== 0) {
    phoneError.style.display = 'block';
    phoneError.innerText = '{{ __('auth.Invalid_Phone_Number') }}';
  } else {
    phoneError.style.display = 'none';
  }
});

// Reset phone input value on select another country code
phoneInput.addEventListener("countrychange", () => {
  phoneInput.value = '';
});

// Initialize intl-tel-input for WhatsApp
const whatsappIti = intlTelInput(whatsappInput, {
  initialCountry: getCountryCode(countryCodeWAFromDB),
  preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
  utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
  separateDialCode: true,
  formatOnDisplay: false,
  autoPlaceholder: "aggressive",
  customPlaceholder: (placeholder) => "e.g. " + placeholder,
});

// Access the WhatsApp number with code
whatsappInput.addEventListener('input', function() {
  const fullPhoneNumber = whatsappIti.getNumber();
  const countryCode = whatsappIti.getSelectedCountryData().dialCode;
  const countryCodeInput = document.getElementById('country_code_w_a');
  countryCodeInput.value = countryCode;

  if (!whatsappIti.isValidNumber() && fullPhoneNumber.length !== 0) {
    whatsappError.style.display = 'block';
    whatsappError.innerText = '{{ __('auth.Invalid_WhatsApp_Number') }}';
  } else {
    whatsappError.style.display = 'none';
  }
});

// Reset WhatsApp input value on select another country code
whatsappInput.addEventListener("countrychange", () => {
  whatsappInput.value = '';
});

// Send code
function sendCode(type) {
  console.log('type', type);
    if (type === 'phone' && !iti.isValidNumber()) {
      phoneError.style.display = 'block';
      phoneError.innerText = '{{ __('auth.Invalid_Phone_Number') }}';
      return;
    }
    
    if (type === 'whatsapp' && !whatsappIti.isValidNumber()) {
      whatsappError.style.display = 'block';
      whatsappError.innerText = '{{ __('auth.Invalid_WhatsApp_Number') }}';
      return;
    }
    
    $(type == 'phone' ? '#verifyCodeModal' : '#verifyWhatsappCodeModal').modal('show');

    $.ajax({
      url: '{{ route('send.code') }}',
      type: 'POST',
      data: {
        _token: '{{ csrf_token() }}'
      },
      success: function(response) {
        $('#sentMessage').text("Message Sent Successfully!");
        $('#sentMessage').show();
      },
      error: function(xhr, status, error) {
        console.log(error);
        $('#error').text('Not Successfully: ' + error);
        $('#error').show();
      }
    });
}

// Verify code of phone
function verifyCode() {
  var code = $('#OTP').val();
  $.ajax({
    url: '{{ route('verify.code') }}',
    type: 'POST',
    data: {
      code: code,
      _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      $('#sentMessageotp').text("Successfully!");
      $('#sentMessageotp').show();
      location.reload();
        
    },
    error: function(xhr, status, error) {
      $('#errorotp').text('حدث خطأ أثناء التحقق من الرمز: ' + error);
      $('#errorotp').show();
    }
  });
}

// Verify code of WhatsApp
function verifyWhatsappCode() {
  var code = $('#OTP').val();
  $.ajax({
    url: '{{ route('verify.code') }}',
    type: 'POST',
    data: {
        code: code,
        _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      $('#sentMessageotp').text("Successfully!");
      $('#sentMessageotp').show();
      location.reload();
    },
    error: function(xhr, status, error) {
      $('#errorotp').text('حدث خطأ أثناء التحقق من الرمز: ' + error);
      $('#errorotp').show();
    }
  });
}


// // حقل مخفي للكود الدولي للواتساب
// const countryCodeWAInput = document.createElement("input");
// countryCodeWAInput.type = "hidden";
// countryCodeWAInput.name = "country_code_w_a";
// countryCodeWAInput.value = "{{ $client->country_code_w_a ? ( $client->country_code_w_a ?? '' ) : '' }}";
// whatsappInput.parentNode.appendChild(countryCodeWAInput);

// // تحديث الحقل المخفي عند تغيير الدولة في الواتساب
// whatsappInput.addEventListener("countrychange", () => {
//     const dialCode = whatsappIti.getSelectedCountryData().dialCode;
//     countryCodeWAInput.value = dialCode; // تعيين الكود الدولي
//     console.log("WhatsApp Dial Code Updated: " + countryCodeWAInput.value);
// });

// // حقل مخفي للكود الدولي
// const countryCodeInput = document.createElement("input");
// countryCodeInput.type = "hidden";
// countryCodeInput.name = "country_code";
// countryCodeInput.value = "{{ $client->country_code ? ( $client->country_code ?? '' ) : '' }}";
// phoneInput.parentNode.appendChild(countryCodeInput);

// // ضبط الدولة الافتراضية
// if (countryCodeFromDB) {
//     iti.setCountry(countryCodeFromDB);
//     countryCodeInput.value = iti.getSelectedCountryData().dialCode;
// }

// // تحديث الحقل المخفي عند تغيير الدولة
// phoneInput.addEventListener("countrychange", () => {
//     const dialCode = iti.getSelectedCountryData().dialCode;
//     countryCodeInput.value =  dialCode;
// });

// whatsappInput.addEventListener("countrychange", () => {
//     const dialCode = whatsappIti.getSelectedCountryData().dialCode;
//     console.log("WhatsApp Dial Code: " + dialCode);
// });

// // التحقق من صحة الرقم
// phoneInput.addEventListener("input", () => {
//     if (iti.isValidNumber()) {
//         phoneError.style.display = 'none';
//     } else {
//         phoneError.style.display = 'block';
//         phoneError.innerText = "Invalid phone number";
//     }
// });

// whatsappInput.addEventListener("input", () => {
//     if (whatsappIti.isValidNumber()) {
//         whatsappError.style.display = 'none';
//     } else {
//         whatsappError.style.display = 'block';
//         whatsappError.innerText = "Invalid WhatsApp number";
//     }
// });


// $('#whatsapp').on('blur', function() {
// var number = $(this).val();
// var countryCode = whatsappIti.getSelectedCountryData().dialCode;
// if (number.startsWith('0')) {
//     number = number.substring(1); // إزالة أول حرف (الصفر)
// }
// var number = countryCode + number;

// $.ajax({
// url: '{{ route('save.phone.session') }}',
// type: 'POST',
// data: {
// phone: number,
// _token: '{{ csrf_token() }}'
// },
// success: function(response) {
  
// $('#whatsapp').prop('readonly', true);
// },
// error: function(xhr, status, error) {
//   console.log(error);
// alert('حدث خطأ أثناء التنفيذ: ' + error);
// }
// });
// });
</script>
@stop