@extends('layouts.admin')

<style>
  .form-label-group {
    margin-top: 1rem !important;
  }
</style>

@section('content')
  @php 
    $timezones = DateTimeZone::listIdentifiers(); 
                                                      
    //  $selectedTimezones = array_filter($timezones, function ($timezone) {
    //     return in_array($timezone, [
    //         'Africa/Cairo',     // مصر
    //         'Asia/Bahrain',     // البحرين   
    //         'Asia/Riyadh',      // السعودية
    //     ]);
    // }); 
  @endphp

  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      {{-- <div class="content-header row">
                <div class="content-header-left col-12 mb-2 mt-1">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h5 class="content-header-title float-left pr-1 mb-0">{{__('settings.HomeSettings')}}</h5>
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb p-0 mb-0">
                                    <li class="breadcrumb-item"><a href="/dashboard"><i class="bx bx-home-alt"></i></a>
                                    </li>
									<li class="breadcrumb-item active">{{__('settings.EditSettings')}}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <div class="row match-height">
            <div class="col-12">
              <!-- اعدادات الـ Tabs Start -->
              <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                  <li class="nav-item">
                      <a class="nav-link active" id="general-settings-tab" data-bs-toggle="tab" href="#general-settings" role="tab" aria-controls="general-settings" aria-selected="true">{{__('settings.General Settings')}}</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" id="app-identity-tab" data-bs-toggle="tab" href="#app-identity" role="tab" aria-controls="app-identity" aria-selected="false">{{__('settings.Application Identity')}}</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" id="google-Analysis-tab" data-bs-toggle="tab" href="#google-Analysis" role="tab" aria-controls="google-Analysis" aria-selected="false">{{__('settings.Analysis Settings')}}</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" id="SMTP-tab" data-bs-toggle="tab" href="#SMTP" role="tab" aria-controls="SMTP" aria-selected="false">{{__('settings.SMTP Configuration')}}</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" id="Notification-tab" data-bs-toggle="tab" href="#Notification" role="tab" aria-controls="Notification" aria-selected="false">{{__('settings.Push Notification Settings')}}</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" id="Meeting-tab" data-bs-toggle="tab" href="#MeetingTab" role="tab" aria-controls="Meeting" aria-selected="false">{{__('settings.Meeting Setting')}}</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" id="SocialMediaLinks-tab" data-bs-toggle="tab" href="#SocialMediaLinks" role="tab" aria-controls="SocialMediaLinks" aria-selected="false">{{__('settings.SocialMediaLinks')}}</a>
                  </li>

                  <li class="nav-item">
                    <a class="nav-link" id="Login-tab" data-bs-toggle="tab" href="#SocialMediaLogin" role="tab" aria-controls="SocialMediaLogin" aria-selected="false">{{__('settings.SocialMediaLogin')}}</a>
                </li>
              @if ($Title->HomePage == true)
                <li class="nav-item">
                  <a class="nav-link" id="Login-tab" data-bs-toggle="tab" href="#HomePage" role="tab" aria-controls="HomePage" aria-selected="false">{{__('admin.HomePageSetting')}}</a>
              </li>
              @endif
              </ul>
              <!-- اعدادات الـ Tabs End -->

              <!-- Start Tabs Data -->
              <div class="tab-content" id="settingsTabsContent">
                <!-- general settings tab -->
                <div class="tab-pane fade show active" id="general-settings" role="tabpanel" aria-labelledby="general-settings-tab">
                  <form class="form" action= "{{ route('updateone') }}" method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header" style="margin-bottom: -20px;">
                        <h3 class="card-title"> {{ __('settings.General Settings') }}</h3>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="site_ar_title">{{ __('settings.ARABICWEBSITETITLE') }}</label>
                              <input type="text" class="form-control"
                                value="{{ $Title->ar_title }}" name="site_ar_title">
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="site_title">{{ __('settings.ENGLISHWEBSITETITLE') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->title }}"
                                name="site_title">
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="custom-control custom-switch custom-control-inline mb-1 mt-6">
                              <span>{{ __('settings.Enable registration') }}</span>&nbsp;
                              <label class="form-label" class="custom-control-label mr-1" for="auto">
                              <input name="autoact" type="checkbox" class="custom-control-input" id="auto"
                                {{ $Title->set_order ? 'checked="checked"' : '' }}>
                              </label>
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Platform_Name">{{ __('settings.Platform_Name') }}</label>
                              <input id="Platform_Name" type="text" class="form-control"
                                value="{{ $Title->Platform_Name }}" name="Platform_Name" required>
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Platform_support_email">{{ __('settings.Platform_support_email') }}</label>
                              <input id="Platform_support_email" type="text" class="form-control"
                                value="{{ $Title->Platform_support_email }}" name="Platform_support_email" required>
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="state_id">{{ __('admin.Country') }}</label>

                              <select class="form-control" id="country_id" name='Country'>
                                <option value="">{{ __('admin.Country') }}</option>
                                @foreach ($countries as $country)
                                  <option value="{{ $country->id }}" {{ $country->id == $Title->Country ? 'selected' : '' }}>
                                    {{ $country->{'name_' . $l} }}</option>
                                @endforeach
                              </select>

                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="state_id">{{ __('admin.State') }} *</label>
                              <select class="form-control" id="state_id" name='State'>
                                <option value="">{{ __('admin.State') }}</option>
                                @foreach ($states as $state)
                                  <option value="{{ $state->id }}" {{ $state->id == $Title->State ? 'selected' : '' }}>
                                    {{ $state->{'name_' . $l} }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Default_Language">{{ __('settings.Default_Language') }} *</label>
                              <select class="form-control" id="Default_Language" name='Default_Language'>
                                <option value="">{{ __('settings.Default_Language') }}</option>

                                <option value="ar" {{ $Title->Default_Language == 'ar' ? 'selected' : '' }}> العربية
                                </option>
                                <option value="en" {{ $Title->Default_Language == 'en' ? 'selected' : '' }}> English
                                </option>

                              </select>
                            </div>
                          </div>


                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="timezone">{{ __('settings.timezone') }} *</label>

                              <select class="select form-control" id="timezone" name='timezone'>

                                <option value="">{{ __('settings.timezone') }}</option>

                                @foreach ($timezones as $timezone)
                                  @php
                                    // حساب فرق التوقيت للمنطقة الزمنية
                                    $time = new DateTime('now', new DateTimeZone($timezone));
                                    $offset = $time->format('P'); // عرض فرق التوقيت مثل +02:00 أو -05:00
                                  @endphp
                                  <option value="{{ $timezone }}" {{ $Title->timezone == $timezone ? 'selected' : '' }}>
                                    {{ $timezone }} ({{ $offset }}) </option>
                                @endforeach
                              </select>
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Currency">{{ __('settings.Currency') }} *</label>
                              <select class="form-control" id="Currency" name='Currency'>
                                <option value="">{{ __('settings.Currency') }}</option>

                                <option value="$" {{ $Title->Currency == '$' ? 'selected' : '' }}> US </option>
                                <option value="LE" {{ $Title->Currency == 'LE' ? 'selected' : '' }}> L.E </option>

                              </select>
                            </div>
                          </div>

<br>
                          <div class="col-md-12 col-12">
                          <div class="form-check form-switch mb-2">
                            <input class="form-check-input" name="HomePage" type="checkbox"  {{ $Title->HomePage == '1' ? 'checked' : '' }} />
                            <label class="form-check-label">{{ __('admin.HomePage') }}</label
                            >
                          </div>
                          </div>



                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>

                  </form>
                </div>
                <!-- End general settings tab -->

                <!-- app identity tab -->
                <div class="tab-pane fade" id="app-identity" role="tabpanel" aria-labelledby="app-identity-tab">
                  <form class="form" action= "{{ route('updatelogo') }}" method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title"> {{ __('settings.Application Identity') }}</h3>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="logo">{{ __('settings.Logo') }}</label>
                              <input id="logo" type="file" class="form-control"
                                value="{{ $Title->Logo }}" name="logo">
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="header">{{ __('settings.header') }}</label>
                              <input id="header" type="file" class="form-control" value="{{ $Title->header }}"
                                name="header">
                              </div>
                            </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="footer">{{ __('settings.footer') }}</label>
                              <input type="file" class="form-control" value="{{ $Title->footer }}" name="footer"
                                id="footer">
                            </div>
                          </div>

                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- app identity settings tab -->

                <!-- google Analysis tab -->
                <div class="tab-pane fade" id="google-Analysis" role="tabpanel" aria-labelledby="google-Analysis-tab">
                  <form class="form" action= "{{ route('updatetwo') }}" method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title"> {{ __('settings.Analysis Settings') }}</h3>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="gtag">{{ __('settings.Google Analytics Code') }}</label>
                              <input type="text" class="form-control"
                                value="{{ $Title->url }}" name="gtag" id="gtag">
                            </div>
                          </div>

                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- End google Analysis tab -->

                <!-- SMTP tab -->
                <div class="tab-pane fade" id="SMTP" role="tabpanel" aria-labelledby="SMTP-tab">
                  <form class="form" action= "{{ route('updateMail') }}" method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title"> {{ __('settings.SMTP Configuration') }}</h3>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="host">{{ __('settings.host') }}</label>
                              <input id="host" type="text" class="form-control" value="{{ $Title->host }}"
                                name="host">
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Mail">{{ __('settings.Mail') }}</label>
                              <input id="Mail" type="text" class="form-control" value="{{ $Title->Mail }}"
                                name="Mail">
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Mailpassword">{{ __('settings.Mailpassword') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->Mailpassword }}"
                                name="Mailpassword" id="Mailpassword">
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="Port">{{ __('settings.Port') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->Port }}" name="Port"
                                id="Port">
                            </div>
                          </div>

                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- End SMTP tab -->

                <!-- Notification tab -->
                <div class="tab-pane fade" id="Notification" role="tabpanel" aria-labelledby="Notification-tab">
                  <form class="form" action= "{{ route('updatePush') }}" method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title"> {{ __('settings.Push Notification Settings') }}</h3>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          <div class="col-md-12 col-12">
                            <label>{{ __('settings.Select BroadCast') }}</label>
                            <fieldset class="form-label-group">
                              <select class="form-control" id="BroadCast" name="BroadCast" required>
                                <option value="0" selected></option>
                                <option value="pusher" {{ $Title->BroadCast == 'pusher' ? 'selected' : '' }}>Pusher</option>
                              </select>
                            </fieldset>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="PUSHER_APP_KEY">{{ __('settings.PUSHER_APP_KEY') }}</label>
                              <input id="PUSHER_APP_KEY" type="text" class="form-control"
                                value="{{ $Title->PUSHER_APP_KEY }}" name="PUSHER_APP_KEY">
                            </div>
                          </div>
                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="PUSHER_APP_ID">{{ __('settings.PUSHER_APP_ID') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->PUSHER_APP_ID }}"
                                name="PUSHER_APP_ID" id="PUSHER_APP_ID">
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="PUSHER_APP_SECRET">{{ __('settings.PUSHER_APP_SECRET') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->PUSHER_APP_SECRET }}"
                                name="PUSHER_APP_SECRET" id="PUSHER_APP_SECRET">
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="PUSHER_APP_CLUSTER">{{ __('settings.PUSHER_APP_CLUSTER') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->PUSHER_APP_CLUSTER }}"
                                name="PUSHER_APP_CLUSTER" id="PUSHER_APP_CLUSTER">
                            </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="MIX_PUSHER_APP_KEY">{{ __('settings.MIX_PUSHER_APP_KEY') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->MIX_PUSHER_APP_KEY }}"
                                name="MIX_PUSHER_APP_KEY" id="MIX_PUSHER_APP_KEY">
                              </div>
                          </div>

                          <div class="col-md-12 col-12">
                            <div class="form-label-group">
                              <label class="form-label" for="MIX_PUSHER_APP_CLUSTER">{{ __('settings.MIX_PUSHER_APP_CLUSTER') }}</label>
                              <input type="text" class="form-control" value="{{ $Title->MIX_PUSHER_APP_CLUSTER }}"
                                name="MIX_PUSHER_APP_CLUSTER" id="MIX_PUSHER_APP_CLUSTER" required>
                            </div>
                          </div>

                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- End notification tab -->

                <!-- Meeting tab -->
                <div class="tab-pane fade" id="MeetingTab" role="tabpanel" aria-labelledby="Meeting-tab">
                  <form class="form" action= "{{ route('updateMeeting') }}" method="POST"
                    enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title"> {{ __('settings.Meeting Setting') }}</h3>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          <div class="col-md-12 col-12">

                            <label>{{ __('settings.Meeting') }}</label>
                            <fieldset class="form-label-group">
                              <select class="form-control Meeting" id="Meeting" name="Meeting" required>
                                <option value="0">{{ __('settings.Choose') }}</option>
                                <option value="Teams" {{ $Title->Meeting == 'Teams' ? 'selected' : '' }}>Teams</option>
                                <option value="Zoom" {{ $Title->Meeting == 'Zoom' ? 'selected' : '' }}>Zoom</option>
                              </select>
                            </fieldset>

                          </div>

                          


                          <div class="MICROSOFT_CLIENT" @if ($Title->Meeting != 'Teams') style="display: none;" @endif>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="MICROSOFT_CLIENT_ID">{{ __('settings.MICROSOFT_CLIENT_ID') }}</label>
                                <input id="MICROSOFT_CLIENT_ID" type="text" class="form-control"
                                  value="{{ $Title->MICROSOFT_CLIENT_ID }}" name="MICROSOFT_CLIENT_ID">
                              </div>
                            </div>
                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label
                                  for="MICROSOFT_CLIENT_SECRET_VALUE">{{ __('settings.MICROSOFT_CLIENT_SECRET_VALUE') }}</label>
                                <input type="text" class="form-control"
                                  value="{{ $Title->MICROSOFT_CLIENT_SECRET_VALUE }}" name="MICROSOFT_CLIENT_SECRET_VALUE"
                                  id="MICROSOFT_CLIENT_SECRET_VALUE">
                              </div>
                            </div>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="MICROSOFT_TENANT_ID">{{ __('settings.MICROSOFT_TENANT_ID') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->MICROSOFT_TENANT_ID }}"
                                  name="MICROSOFT_TENANT_ID" id="MICROSOFT_TENANT_ID">
                              </div>
                            </div>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label
                                  for="MICROSOFT_CLIENT_SECRET_ID">{{ __('settings.MICROSOFT_CLIENT_SECRET_ID') }}</label>
                                <input type="text" class="form-control"
                                  value="{{ $Title->MICROSOFT_CLIENT_SECRET_ID }}" name="MICROSOFT_CLIENT_SECRET_ID"
                                  id="MICROSOFT_CLIENT_SECRET_ID">
                              </div>
                            </div>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="MICROSOFT_APP_EMAIL">{{ __('settings.MICROSOFT_APP_EMAIL') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->MICROSOFT_APP_EMAIL }}"
                                  name="MICROSOFT_APP_EMAIL" id="MICROSOFT_APP_EMAIL">
                              </div>
                            </div>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="MICROSOFT_REDIRECT_URI">{{ __('settings.MICROSOFT_REDIRECT_URI') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->MICROSOFT_REDIRECT_URI }}"
                                  name="MICROSOFT_REDIRECT_URI" id="MICROSOFT_REDIRECT_URI">
                              </div>
                            </div>

                          </div>

                          <div class="ZOOM_CLIENT" @if ($Title->Meeting != 'Zoom') style="display: none;" @endif>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="ZOOM_CLIENT_ID">{{ __('settings.ZOOM_CLIENT_ID') }}</label>
                                <input id="ZOOM_CLIENT_ID" type="text" class="form-control"
                                  value="{{ $Title->ZOOM_CLIENT_ID }}" name="ZOOM_CLIENT_ID">
                                </div>
                            </div>
                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="ZOOM_CLIENT_SECRET">{{ __('settings.ZOOM_CLIENT_SECRET') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->ZOOM_CLIENT_SECRET }}"
                                  name="ZOOM_CLIENT_SECRET" id="ZOOM_CLIENT_SECRET">
                              </div>
                            </div>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="ZOOM_ACCOUNT_ID">{{ __('settings.ZOOM_ACCOUNT_ID') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->ZOOM_ACCOUNT_ID }}"
                                  name="ZOOM_ACCOUNT_ID" id="ZOOM_ACCOUNT_ID">
                              </div>
                            </div>

                          </div>

                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- End meeting tab -->

                <!-- Social media links tab -->
                <div class="tab-pane fade" id="SocialMediaLinks" role="tabpanel" aria-labelledby="SocialMediaLinks-tab">
                  <form class="form" id="basicForm" action= "{{ URL::to('dashboard/update_settings') }}"
                    method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="card">
                      <div class="card-header">
                        <h5 class="card-title">{{ __('settings.SocialMediaLinks') }}</h5>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          @foreach ($Icons as $Icon)
                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="{{ $Icon->img_url }}">{{ __('settings.URLOF') }} -
                                  {{ $l == 'ar' ? $Icon->ar_title : $Icon->title }}</label>
                                <input type="text" class="form-control"
                                  value="{{ $Icon->url }}" name="{{ $Icon->img_url }}">
                              </div>
                            </div>
                          @endforeach
                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- End social media links tab -->

                 <!-- Social media links tab -->
                 <div class="tab-pane fade" id="SocialMediaLogin" role="tabpanel" aria-labelledby="Login-tab">
                  <form class="form" action= "{{ route('updateSMLogin') }}"
                    method="POST" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $Title->id }}">
                    <div class="card">
                      <div class="card-header">
                        <h5 class="card-title">{{ __('settings.SocialMediaLogin') }}</h5>
                      </div>
                      <div class="card-content">
                        <div class="card-body">
                          
                        
                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="GOOGLE_CLIENT_ID">{{ __('settings.GOOGLE_CLIENT_ID') }}</label>
                                <input id="GOOGLE_CLIENT_ID" type="text" class="form-control"
                                  value="{{ $Title->GOOGLE_CLIENT_ID }}" name="GOOGLE_CLIENT_ID">
                              </div>
                            </div>
                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="GOOGLE_CLIENT_SECRET">{{ __('settings.GOOGLE_CLIENT_SECRET') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->GOOGLE_CLIENT_SECRET }}"
                                  name="GOOGLE_CLIENT_SECRET" id="GOOGLE_CLIENT_SECRET">
                              </div>
                            </div>

                            <div class="col-md-12 col-12">
                              <div class="form-label-group">
                                <label class="form-label" for="GOOGLE_REDIRECT">{{ __('settings.GOOGLE_REDIRECT') }}</label>
                                <input type="text" class="form-control" value="{{ $Title->GOOGLE_REDIRECT }}"
                                  name="GOOGLE_REDIRECT" id="GOOGLE_REDIRECT">
                              </div>
                            </div>



                          <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
                <!-- End social media links tab -->

              </div>
              <!-- End Tabs Data -->
<!-- Start Home Page -->
@if ($Title->HomePage == true)
<div class="tab-pane fade" id="HomePage" role="tabpanel" aria-labelledby="HomePage-tab">
              <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{__('settings.HomeWelcomeImages')}}</h5>
                </div>
                <div class="card-content">
                    <div class="card-body">
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
              <label>{{__('settings.1STIMAGE')}}</label>
                                        <div class="custom-file">
                                            <input type="file" class="form-control" name="img1" id="img1" accept='image/*'>
                                            <label class="form-label" class="custom-file-label" for="img1">{{__('settings.Chooseimagefile',['id'=>'1'])}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->ar_title}}" name="ar_img1title" required>
                                        <label class="form-label" for="ar_img1title">{{__('settings.Image')}} 1 - {{__('settings.ARABICTITLE')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->title}}" name="img1title" required>
                                        <label class="form-label" for="img1title">{{__('settings.Image')}} 1 - {{__('settings.ENGLISHTITLE')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->ar_set1}}" name="ar_img1title2" required>
                                        <label class="form-label" for="ar_img1title2">{{__('settings.Image')}} 1 - {{__('settings.ARABICECONDTITLE')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->set1}}" name="img1title2" required>
                                        <label class="form-label" for="img1title2">{{__('settings.Image')}} 1 - {{__('settings.ENGLISHSECONDTITLE')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->ar_dsc}}" name="ar_img1dsc" required>
                                        <label class="form-label" for="ar_img1dsc">{{__('settings.Image')}} 1 - {{__('settings.ARABICDESCRIPTION')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->dsc}}" name="img1dsc" required>
                                        <label class="form-label" for="img1dsc">{{__('settings.Image')}} 1 - {{__('settings.ENGLISHDESCRIPTION')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img1->url}}" name="img1url" required>
                                        <label class="form-label" for="img1url">{{__('settings.Image')}} 1 - {{__('settings.URLOF')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
              <label>{{__('settings.2NDIMAGE')}}</label>
                                        <div class="custom-file">
                                            <input type="file" class="form-control" name="img2" id="img2" accept='image/*'>
                                            <label class="form-label" class="custom-file-label" for="img2">{{__('settings.Chooseimagefile',['id'=>'2'])}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->ar_title}}" id="ar_img2title" name="ar_img2title" required>
                                        <label class="form-label" for="ar_img2title">{{__('settings.Image')}} 2 - {{__('settings.ARABICTITLE')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->title}}" name="img2title" id="img2title" required>
                                        <label class="form-label" for="img2title">{{__('settings.Image')}} 2 - {{__('settings.ENGLISHTITLE')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->ar_set1}}" name="ar_img2title2" id="ar_img2title2" required>
                                        <label class="form-label" for="ar_img2title2">{{__('settings.Image')}} 2 - {{__('settings.ARABICECONDTITLE')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->set1}}" name="img2title2" id="img2title2" required>
                                        <label class="form-label" for="img2title2">{{__('settings.Image')}} 2 - {{__('settings.ENGLISHSECONDTITLE')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->ar_dsc}}" name="ar_img2dsc" id="ar_img2dsc" required>
                                        <label class="form-label" for="ar_img2dsc">{{__('settings.Image')}} 2 - {{__('settings.ARABICDESCRIPTION')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->dsc}}" name="img2dsc" id="img2dsc" required>
                                        <label class="form-label" for="img2dsc">{{__('settings.Image')}} 2 - {{__('settings.ENGLISHDESCRIPTION')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img2->url}}" name="img2url" id="img2url" required>
                                        <label class="form-label" for="img2url">{{__('settings.Image')}} 2 - {{__('settings.URLOF')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
              <label>{{__('settings.3RDIMAGE')}}</label>
                                        <div class="custom-file">
                                            <input type="file" class="form-control" name="img3" id="img3" accept='image/*'>
                                            <label class="form-label" class="custom-file-label" for="img3">{{__('settings.Chooseimagefile',['id'=>'3'])}}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->ar_title}}" name="ar_img3title" id="ar_img3title" required>
                                        <label class="form-label" for="ar_img3title">{{__('settings.Image')}} 3 - {{__('settings.ARABICTITLE')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->title}}" name="img3title" id="img3title" required>
                                        <label class="form-label" for="img3title">{{__('settings.Image')}} 3 - {{__('settings.ENGLISHTITLE')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->ar_set1}}" name="ar_img3title2" id="ar_img3title2" required>
                                        <label class="form-label" for="ar_img3title2">{{__('settings.Image')}} 3 - {{__('settings.ARABICECONDTITLE')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->set1}}" name="img3title2" id="img3title2" required>
                                        <label class="form-label" for="img3title2">{{__('settings.Image')}} 3 - {{__('settings.ENGLISHSECONDTITLE')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->ar_dsc}}" name="ar_img3dsc" id="ar_img3dsc" required>
                                        <label class="form-label" for="ar_img3dsc">{{__('settings.Image')}} 3 - {{__('settings.ARABICDESCRIPTION')}}</label>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->dsc}}" name="img3dsc" id="img3dsc" required>
                                        <label class="form-label" for="img3dsc">{{__('settings.Image')}} 3 - {{__('settings.ENGLISHDESCRIPTION')}}</label>
                                    </div>
                                </div>
              <div class="col-md-12 col-12">
                                    <div class="form-label-group">
                                        <input type="text" class="form-control" value="{{$Img3->url}}" name="img3url" id="img3url" required>
                                        <label class="form-label" for="img3url">{{__('settings.Image')}} 3 - {{__('settings.URLOF')}}</label>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end mt-6">
                                    <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('settings.Submit')}}</button>
                                </div>
                            </div>
                        </div>

              </div> 
              <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{__('settings.Impact Icons')}}</h5>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        @foreach ($Counters as $num => $Counter)
                            <div class="col-md-3 col-3">
                                <label>{{__('settings.Select Icon')}}  {{$num+1}}</label>
                                <fieldset class="form-label-group">
                                    <select class="form-control" id="basicSelect" name="ct{{$Counter->id}}icon" required>
                                        <option value="{{$Counter->img_url}}" selected="">{{$Counter->img_url}}</option>
                                        <option value="office">office</option>
                                        <option value="man">man</option>
                                        <option value="cup">cup</option>
                                        <option value="professor">professor</option>
                                        <option value="multimedia">multimedia </option>
                                        <option value="people">people </option>
                                        <option value="science">science</option>
                                        <option value="technology">technology</option>
                                        <option value="book">book</option>
                                    </select>
                                </fieldset>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="form-label-group">
                                    <input  type="text" class="form-control" value="{{$Counter->title}}" name="ct{{$Counter->id}}title">
                                    <label class="form-label" for="ct{{$Counter->id}}title">{{__('settings.TITLEOF - ICON')}} {{$num+1}}</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="form-label-group">
                                    <input  type="text" class="form-control" value="{{$Counter->ar_title}}" name="ct{{$Counter->id}}titlear">
                                    <label class="form-label" for="ct{{$Counter->id}}titlear">{{__('settings.ARTITLEOF - ICON')}}  {{$num+1}}</label>
                                </div>
                            </div>

                            <div class="col-md-6 col-6">
                                <div class="form-label-group">
                                    <input  type="text" class="form-control" value="{{$Counter->dsc}}" name="ct{{$Counter->id}}num">
                                    <label class="form-label" for="ct{{$Counter->id}}num">{{__('settings.NUMBER - ICON')}} {{$num+1}}</label>
                                </div>
                            </div>
                            <hr style="padding: 5px;">
                        @endforeach
                        <div class="col-12 d-flex justify-content-end mt-6">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{__('settings.Submit')}}</button>
                        </div>
                    </div>
                </div>
              </div>
            </div>
            @endif
<!-- End Home Page -->


            </div>
          </div>
      </div>
    </div>
  </div>
  </section>
  <!-- // Basic multiple Column BuilderForm section end -->
  </div>
  </div>
  </div>
  <!-- END: Content-->
@stop
