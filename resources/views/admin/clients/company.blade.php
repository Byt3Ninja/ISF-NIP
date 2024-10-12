@php
  $name = 'name_' . $l;
@endphp

@extends('layouts.admin')

@section('title', __('admin.My company'))
@section('subTitle', __('admin.Edit the Company information'))

@section('style')
  <link rel="stylesheet" href="/tabs/dist/jquery.tabs.css">
  {{-- <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/select/select2.min.css"> --}}
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <style>
    .form-label-group>label {
      display: contents !important;
    }
    .Red {
    color: red;
}

.Green {
    color: green;
}

.Yellow {
    color: yellow;
}

.Gray {
    color: rgb(214, 200, 200);
}
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      
      <div class="content-body">
        <h4 class="content-title">{{ __('admin.Edit the Company information') }}</h4>
        <!-- BuilderForm start -->
        <section id="basic-input">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <div class="d-flex align-items-center">
                    <button id="infoButton" class="btn btn-primary">
                      {{ __('admin.company Information') }}
                    </button>
                    {{-- <div class="jq-tab-title" data-tab="2"><i class="ti ti-download text-secondary"></i> {{ __('admin.elevator Pitch') }}</div> --}}
                    {{-- <div class="jq-tab-title" data-tab="3"><i class="bx bx-bar-chart-alt"></i> {{ __('admin.Funding Data') }}</div> --}}
                    {{-- <div class="jq-tab-title" data-tab="4"><i class="bx bxs-slideshow"></i> {{ __('admin.about') }}</div> --}}
                    <button id="contactButton" class="btn btn-label-secondary mx-3">
                      {{ __('admin.social & contact') }}
                    </button>
                  </div>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    @if (session()->has('msg'))
                      <div class="row">
                        <div class="col-md-12">
                          <div
                            class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible"
                            role="alert"
                          >
                            {{ __('admin.' . session()->get('msg')) }}
                            {{ Session::forget('msg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </button>
                          </div>
                        </div>
                      </div>
                    @endif
                    <!-- Validation messages -->
                    @if (count($errors) > 0)
                      <div class="row">
                        <div class="col-md-12">
                          <div class="alert alert-danger">
                            <ul>
                              @foreach ($errors->all() as $error)
                                <li>{{ $error }} </li>
                              @endforeach
                            </ul>
                          </div>
                        </div>
                      </div>
                    @endif

                    <form id="infoForm" method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <div class="row">
                        <div class="col-md-12 col-12 mb-3">
                          @if (isset($client->company->logo))
                            <img 
                              id="company-logo" 
                              src="{{ $client->company->logo_url }}" 
                              alt="Company logo" 
                              style="width: 130px; height: 100px; border-radius: 10px;"
                              class="mb-2"
                            />
                          @endif
                          <div class="custom-file mb-3" style="width: 300px;">
                            <label class="fs-6 mb-2">{{ __('admin.Logo') }}</label>
                            <input 
                              type="file" 
                              class="form-control"
                              id="img_url" 
                              name="img_url"
                              accept="image/*" 
                              aria-describedby="inputGroupFileAddon00"
                            />
                            {{-- <label for="img_url" class="custom-file-label">
                              {{ __('admin.Select image file') }}
                            </label> --}}
                          </div>
                        </div>
                       <div class="mb-3 row">
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="name_en">{{ __('admin.English Name') }} *</label>
                            <input style="direction: ltr"
                              value="{{ $client->company ? $client->company->name_en : old('name_en') }}" name="name_en"
                              type="text" class="form-control mt-2" id="name_en"
                              placeholder="{{ __('admin.English Name') }} *" required autocomplete="name_en" />
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="name_ar">{{ __('admin.Arabic Name') }} *</label>
                            <input style="direction: rtl;"
                              value="{{ $client->company ? $client->company->name_ar : old('name_ar') }}" name="name_ar"
                              type="text" class="form-control mt-2" id="name_ar"
                              placeholder="{{ __('admin.Arabic Name') }} *" required autocomplete="name_ar" />
                          </div>
                        </div>
                       </div>

                        @php
                          if ($client->company) {
                              $stateID = optional($client->company)->state_id;
                          } else {
                              $stateID = 0;
                          }
                        @endphp
                        <div class="col-md-4 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="state_id">{{ __('admin.Country') }}</label>

                            <select class="form-control select2" required id="country_id" name='country_id'>
                              <option value="">{{ __('admin.Country') }}</option>
                              @foreach ($countries as $country)
                                <option value="{{ $country->id }}"
                                  {{ $country->id == $client->country_id ? 'selected' : '' }}>
                                  {{ $country->{'name_' . $l} }}</option>
                              @endforeach
                            </select>

                          </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="state_id">{{ __('admin.State') }} *</label>
                            <select class="form-control select2" required id="state_id" name='state_id'>
                              <option value="">{{ __('admin.State') }}</option>
                              @foreach ($states as $state)
                                <option value="{{ $state->id }}" {{ $state->id == $stateID ? 'selected' : '' }}>
                                  {{ $state->$name }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="city_id">{{ __('admin.City') }} *</label>
                            <select class="form-control select2" required id="city_id" name='city_id'>
                              <option value="">{{ __('admin.City') }} </option>
                              @if (isset($client->company) && $client->company->city_id != null)
                                <option value="{{ $client->company->city_id }}" selected>
                                  {{ $client->company->city->{$name} }}</option>
                              @endif
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="sector">{{ __('admin.Sector') }} *</label>
                            <select id="selectsector" class="form-control mt-2" required
                              onchange="ssector(this.options[this.selectedIndex].value)" name='sector'>
                              @foreach ($sectors as $sector_group)
                                <optgroup label="{{ $sector_group->name }}">
                                  @foreach ($sector_group->children as $sector)
                                    <option value="{{ $sector->name_en }}">{{ $sector->name }}</option>
                                  @endforeach
                                </optgroup>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="address_en">{{ __('admin.English Address') }}
                              *</label>
                            <textarea style="direction: ltr" class="form-control mt-2" id="address_en" name='address_en' required
                              placeholder="{{ __('admin.English Address') }} *">{{ $client->company ? $client->company->address_en : old('address_en') }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="address_ar">{{ __('admin.Arabic Address') }}
                              *</label>
                            <textarea style="direction: rtl;" class="form-control mt-2" id="address_ar" name='address_ar' required
                              placeholder="{{ __('admin.Arabic Address') }} *">{{ $client->company ? $client->company->address_ar : old('address_ar') }}</textarea>
                            <br>
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="phone">{{ __('admin.Phone') }}*</label>
                            <input type="text" class="form-control mt-2" id="phone"
                              placeholder="{{ __('admin.Phone') }}*"
                              value="{{ $client->company ? $client->company->phone : old('phone') }}" name="phone"
                              required autocomplete="new-phone">
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="email">{{ __('admin.E-mail') }} *</label>
                            <input style="direction: ltr" type="email" class="form-control mt-2" id="email"
                              placeholder="{{ __('admin.E-mail') }} *"
                              value="{{ $client->company ? $client->company->email : old('email') }}" name="email"
                              required autocomplete="new-email">

                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="est_date">{{ __('admin.est_date') }}</label>
                            <input type="text" class="form-control mt-2" id="est_date"
                              value="{{ $client->company ? $client->company->est_date : old('est_date') }}"
                              name="est_date">
                          </div>
                        </div>
                       
                        <div class="form-group mb-3">
                          <label class="form-label fs-6" for="CoursCategories">
                            {{ __('admin.Sustainable_development') }}
                          </label>
                          
                          <select name="Sustainable_development[]" class="select2 form-control" multiple="multiple" id="CoursCategories">
                            <option value="no_poverty" @if($client->company->Sustainable_development && in_array('no_poverty', json_decode($client->company->Sustainable_development, true))) selected @endif  >{{ __('admin.no_poverty') }}</option>
    <option value="zero_hunger" @if($client->company->Sustainable_development && in_array('zero_hunger', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.zero_hunger') }}</option>
    <option value="good_health" @if($client->company->Sustainable_development && in_array('good_health', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.good_health') }}</option>
    <option value="quality_education" @if($client->company->Sustainable_development && in_array('quality_education', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.quality_education') }}</option>
    <option value="gender_equality" @if($client->company->Sustainable_development && in_array('gender_equality', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.gender_equality') }}</option>
    <option value="clean_water" @if($client->company->Sustainable_development && in_array('clean_water', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.clean_water') }}</option>
    <option value="clean_energy" @if($client->company->Sustainable_development && in_array('clean_energy', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.clean_energy') }}</option>
    <option value="decent_work" @if($client->company->Sustainable_development && in_array('decent_work', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.decent_work') }}</option>
    <option value="industry_innovation" @if($client->company->Sustainable_development && in_array('industry_innovation', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.industry_innovation') }}</option>
    <option value="reduced_inequalities" @if($client->company->Sustainable_development && in_array('reduced_inequalities', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.reduced_inequalities') }}</option>
    <option value="sustainable_cities" @if($client->company->Sustainable_development && in_array('sustainable_cities', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.sustainable_cities') }}</option>
    <option value="responsible_consumption" @if($client->company->Sustainable_development && in_array('responsible_consumption', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.responsible_consumption') }}</option>
    <option value="climate_action" @if($client->company->Sustainable_development && in_array('climate_action', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.climate_action') }}</option>
    <option value="life_below_water" @if($client->company->Sustainable_development && in_array('life_below_water', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.life_below_water') }}</option>
    <option value="life_on_land" @if($client->company->Sustainable_development && in_array('life_on_land', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.life_on_land') }}</option>
    <option value="peace_justice" @if($client->company->Sustainable_development && in_array('peace_justice', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.peace_justice') }}</option>
    <option value="partnership_goals" @if($client->company->Sustainable_development && in_array('partnership_goals', json_decode($client->company->Sustainable_development, true))) selected @endif >{{ __('admin.partnership_goals') }}</option>
                          </select>
                        </div>
                        
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="stage">{{ __('admin.stage') }}*</label>
                            <select class="form-control mt-2" required id="stage" name='stage'>
                              <option value="">{{ __('admin.stage') }}</option>
                              <option value="idea"
                                {{ !empty($client->company) && $client->company->stage == 'idea' ? 'selected' : '' }}>
                                {{ __('admin.stage_idea') }}</option>
                              <option value="prototype"
                                {{ !empty($client->company) && $client->company->stage == 'prototype' ? 'selected' : '' }}>
                                {{ __('admin.stage_prototype') }}</option>
                              <option value="ready_product"
                                {{ !empty($client->company) && $client->company->stage == 'ready_product' ? 'selected' : '' }}>
                                {{ __('admin.stage_ready_product') }}</option>
                              <option value="company_in_market"
                                {{ !empty($client->company) && $client->company->stage == 'company_in_market' ? 'selected' : '' }}>
                                {{ __('admin.stage_company_in_market') }}</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="stage">{{ __('admin.Business Type') }}*</label>
                            <select class="form-control mt-2" required id="business_type" name='business_type'>
                              <option value="">{{ __('admin.Business Type') }}</option>
                              <option value="B2C"
                                {{ !empty($client->company) && $client->company->business_type == 'B2C' ? 'selected' : '' }}>
                                B2C</option>
                              <option value="B2B"
                                {{ !empty($client->company) && $client->company->business_type == 'B2B' ? 'selected' : '' }}>
                                B2B</option>
                              <option value="B2B2C"
                                {{ !empty($client->company) && $client->company->business_type == 'B2B2C' ? 'selected' : '' }}>
                                B2B2C</option>
                              <option value="C2C"
                                {{ !empty($client->company) && $client->company->business_type == 'C2C' ? 'selected' : '' }}>
                                C2C</option>
                              <option value="B2G"
                                {{ !empty($client->company) && $client->company->business_type == 'B2G' ? 'selected' : '' }}>
                                B2G</option>
                              <option value="Platform"
                                {{ !empty($client->company) && $client->company->business_type == 'Platform' ? 'selected' : '' }}>
                                Platform</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="raised_fund">{{ __('admin.raised_fund_egp') }}
                              {{ $Setting->Currency }}</label>
                            <input type="number" class="form-control mt-2" id="raised_fund"
                              placeholder="{{ __('admin.raised_fund_egp') }} {{ $Setting->Currency }}"
                              value="{{ $client->company ? $client->company->raised_fund : old('raised_fund') }}"
                              name="raised_fund" autocomplete="raised_fund">
                          </div>
                        </div>


                        <div class="col-md-6 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="investors">{{ __('admin.investors') }}</label>
                            <input type="text" class="form-control mt-2" id="investors"
                              placeholder="{{ __('admin.investors') }}"
                              value="{{ $client->company ? $client->company->investors : old('investors') }}"
                              name="investors" autocomplete="new-investors">
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="founder">{{ __('admin.founder') }}</label>
                            <input type="text" class="form-control mt-2" id="founder"
                              placeholder="{{ __('admin.founder') }}"
                              value="{{ $client->company ? $client->company->founder : old('founder') }}"
                              name="founder" autocomplete="new-founder">
                          </div>
                        </div>
                        {{--                                                                    <div class="col-md-12 col-12 mb-3"> --}}
                        {{--                                                                        <div class="form-group"> --}}
                        {{--                                                                            <label class="fs-6 mb-2" for="idea">{{ __('admin.idea')}}</label> --}}
                        {{--                                                                            <textarea class="form-control mt-2" id="idea" name='idea' placeholder="{{__('admin.idea')}}">{{($client->company)? $client->company->idea:old('idea')}}</textarea> --}}
                        {{--                                                                        </div> --}}
                        {{--                                                                    </div> --}}
                        {{--                                                                    <div class="col-md-12 col-12 mb-3"> --}}
                        {{--                                                                        <div class="form-group"> --}}
                        {{--                                                                            <label class="fs-6 mb-2" for="problem">{{ __('admin.Problem Solution')}}</label> --}}
                        {{--                                                                            <textarea class="form-control mt-2" id="problem" name='problem' placeholder="{{ __('admin.Problem Solution')}}">{{($client->company)? $client->company->problem:old('problem')}}</textarea> --}}
                        {{--                                                                        </div> --}}
                        {{--                                                                    </div> --}}
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="team_num">{{ __('admin.team_num') }}</label>
                            <input type="number" class="form-control mt-2" id="team_num"
                              placeholder="{{ __('admin.team_num') }}"
                              value="{{ $client->company ? $client->company->team_num : old('team_num') }}"
                              name="team_num" autocomplete="new-team_num">
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="problem">{{ __('admin.solution') }}</label>
                            <textarea class="form-control mt-2" id="solution" name='solution' placeholder="{{ __('admin.solution') }}">{{ $client->company ? $client->company->solution : old('solution') }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="institutions">{{ __('admin.institutions_question') }}</label>
                            <select id="institutions" name="institutions[]" multiple="multiple"
                              class="form-control mt-2">
                              @if ($client->company && !is_null($client->company->institutions))
                                @foreach ($client->company->institutions as $key)
                                  <option value="{{ $key }}" selected>{{ $key }}</option>
                                @endforeach
                              @endif
                            </select>
                          </div>
                        </div>

                      </div>

                      <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary mr-1 mb-1" id="submit-button">
                            <span id="button-text">{{ __('admin.Update') }}</span>
                            <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                          </button>
                        </div>
                      </div>
                    </form>

                    <form id="contactForm" method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <div class="row">
                        <div class="col-md-6 col-12 mb-3 ">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="facebook">{{ __('admin.Website') }}</label>
                            <input style="direction: ltr" type="text" class="form-control mt-2" id="facebook"
                              placeholder="{{ __('admin.Website') }}"
                              value="{{ $client->company ? $client->company->website : old('website') }}"
                              name="website" autocomplete="website">
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3 ">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="facebook">{{ __('admin.Facebook') }}</label>
                            <input style="direction: ltr" type="text" class="form-control mt-2" id="facebook"
                              placeholder="{{ __('admin.Facebook') }}"
                              value="{{ $client->company ? $client->company->facebook : old('facebook') }}"
                              name="facebook" autocomplete="facebook">
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3 ">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="twitter">{{ __('admin.Twitter') }}</label>
                            <input style="direction: ltr" type="text" class="form-control mt-2" id="twitter"
                              placeholder="{{ __('admin.Twitter') }}"
                              value="{{ $client->company ? $client->company->twitter : old('twitter') }}"
                              name="twitter" autocomplete="twitter">
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3 ">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="linkedin">{{ __('admin.Linkedin') }}</label>
                            <input style="direction: ltr" type="text" class="form-control mt-2" id="linkedin"
                              placeholder="{{ __('admin.Linkedin') }}"
                              value="{{ $client->company ? $client->company->linkedin : old('linkedin') }}"
                              name="linkedin" autocomplete="new-linkedin">
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3 ">
                          <div class="form-label-group">
                            <label class="fs-6 mb-2" for="youtube">{{ __('admin.Youtube') }}</label>
                            <input style="direction: ltr" type="text" class="form-control mt-2" id="youtube"
                              placeholder="{{ __('admin.Youtube') }}"
                              value="{{ $client->company ? $client->company->youtube : old('youtube') }}"
                              name="youtube" autocomplete="new-youtube">
                          </div>
                        </div>
                        <div class="col-md-6 col-12 mb-3 ">
                          <div class="form-group">
                            <label class="fs-6 mb-2" for="google_map">{{ __('admin.Google Map') }}</label>
                            <textarea style="direction: ltr" class="form-control height-100" id="google_map" name='google_map'
                              placeholder="{{ __('admin.Google Map full URL') }}">{{ $client->company ? $client->company->google_map : old('google_map') }}</textarea>
                          </div>
                        </div>

                      </div>
                      <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                        </div>
                      </div>
                    </form>

                    <div class="row">
                      <div class="col-12">
                        <div class="jq-tab-wrapper">
                          <div class="jq-tab-content-wrapper">
                            <div class="jq-tab-content" data-tab="2">
                              <div class="col-12">
                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="card">
                                      <div class="card-header">
                                        <h4 class="card-title">{{ __('admin.elevator Pitch') }}</h4>
                                      </div>
                                      <div class="card-content">
                                        <div class="card-body">
                                          <form method="post" action="{{ route('update_my_company') }}"
                                            enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <div class="row">
                                              <div class="col-md-6 col-12 mb-3">

                                                <div class="form-group mt-75">
                                                  <label class="fs-6 mb-2"
                                                    for="basicInput">{{ __('Business Model') }}</label>
                                                  <div class="input-group">
                                                    <div class="input-group-prepend">
                                                      <span class="input-group-text"
                                                        id="inputGroupFileAddonP01">{{ __('admin.Upload') }}</span>
                                                    </div>
                                                    <div class="custom-file">
                                                      <input type="file" class="custom-file-input"
                                                        id="business_model" name="business_model"
                                                        accept=".pdf,.xlsx,.docx" style="direction: ltr;"
                                                        aria-describedby="inputGroupFileAddonP00">
                                                      <label class="fs-6 mb-2" class="custom-file-label"
                                                        for="inputGroupFileP00">{{ __('admin.Select file') }}</label>
                                                    </div>
                                                  </div>
                                                  @if (isset($client->company->business_model))
                                                    <a href="{{ $client->company->business_model_url }}"
                                                      target="_blank" class="btn btn-warning mt-1">
                                                      <i class="bx bx-search-alt"></i> {{ __('Preview') }}
                                                    </a>
                                                  @endif
                                                </div>
                                                <div class="form-group">
                                                  <label class="fs-6 mb-2"
                                                    for="business_model_description">{{ __('Business Model Description') }}</label>
                                                  <textarea class="form-control " id="business_model_description" name='business_model_description'
                                                    placeholder="{{ __('Business Model Description') }}">{{ $client->company ? $client->company->business_model_description : old('business_model_description') }}</textarea>
                                                </div>
                                              </div>

                                              <div class="col-md-6 col-12 mb-3">

                                                <div class="form-group mt-75">
                                                  <label class="fs-6 mb-2"
                                                    for="basicInput">{{ __('Business Plan') }}</label>
                                                  <div class="input-group">
                                                    <div class="input-group-prepend">
                                                      <span class="input-group-text"
                                                        id="inputGroupFileAddonP02">{{ __('admin.Upload') }}</span>
                                                    </div>
                                                    <div class="custom-file">
                                                      <input type="file" class="custom-file-input"
                                                        id="business_plan" name="business_plan"
                                                        accept=".pdf,.xlsx,.docx" style="direction: ltr;"
                                                        aria-describedby="inputGroupFileAddonP01">
                                                      <label class="fs-6 mb-2" class="custom-file-label"
                                                        for="inputGroupFileP01">{{ __('admin.Select file') }}</label>
                                                    </div>
                                                  </div>
                                                  @if (isset($client->company->business_plan))
                                                    <a href="{{ $client->company->business_plan_url }}"
                                                      target="_blank" class="btn btn-warning mt-1">
                                                      <i class="bx bx-search-alt"></i> {{ __('Preview') }}
                                                    </a>
                                                  @endif
                                                </div>
                                                <div class="form-group">
                                                  <label class="fs-6 mb-2"
                                                    for="business_plan_description">{{ __('Business Plan Description') }}</label>
                                                  <textarea class="form-control " id="business_plan_description" name='business_plan_description'
                                                    placeholder="{{ __('Business Plan Description') }}">{{ $client->company ? $client->company->business_plan_description : old('business_plan_description') }}</textarea>
                                                </div>
                                              </div>
                                              <div class="col-md-6 col-12 mb-3">

                                                <div class="form-group mt-75">
                                                  <label class="fs-6 mb-2"
                                                    for="basicInput">{{ __('Pitch Deck') }}</label>
                                                  <div class="input-group">
                                                    <div class="input-group-prepend">
                                                      <span class="input-group-text"
                                                        id="inputGroupFileAddonP03">{{ __('admin.Upload') }}</span>
                                                    </div>
                                                    <div class="custom-file">
                                                      <input type="file" class="custom-file-input" id="pitch_deck"
                                                        name="pitch_deck" accept=".pdf,.xlsx,.docx"
                                                        style="direction: ltr;"
                                                        aria-describedby="inputGroupFileAddonP02">
                                                      <label class="fs-6 mb-2" class="custom-file-label"
                                                        for="inputGroupFileP02">{{ __('admin.Select file') }}</label>
                                                    </div>
                                                  </div>
                                                  @if (isset($client->company->pitch_deck))
                                                    <a href="{{ $client->company->pitch_deck_url }}" target="_blank"
                                                      class="btn btn-warning mt-1">
                                                      <i class="bx bx-search-alt"></i> {{ __('Preview') }}
                                                    </a>
                                                  @endif
                                                </div>
                                                <div class="form-group">
                                                  <label class="fs-6 mb-2"
                                                    for="pitch_deck_description">{{ __('Pitch Deck Description') }}</label>
                                                  <textarea class="form-control " id="pitch_deck_description" name='pitch_deck_description'
                                                    placeholder="{{ __('Pitch Deck Description') }}">{{ $client->company ? $client->company->pitch_deck_description : old('pitch_deck_description') }}</textarea>
                                                </div>
                                              </div>
                                              <div class="col-md-6 col-12 mb-3">
                                                <div class="form-group mt-75">
                                                  <label class="fs-6 mb-2"
                                                    for="basicInput">{{ __('Financial Plan') }}</label>
                                                  <div class="input-group">
                                                    <div class="input-group-prepend">
                                                      <span class="input-group-text"
                                                        id="inputGroupFileAddonP04">{{ __('admin.Upload') }}</span>
                                                    </div>
                                                    <div class="custom-file">
                                                      <input type="file" class="custom-file-input"
                                                        id="financial_plan" name="financial_plan"
                                                        accept=".pdf,.xlsx,.docx" style="direction: ltr;"
                                                        aria-describedby="inputGroupFileAddonP03">
                                                      <label class="fs-6 mb-2" class="custom-file-label"
                                                        for="inputGroupFileP03">{{ __('admin.Select file') }}</label>
                                                    </div>
                                                  </div>
                                                  @if (isset($client->company->financial_plan))
                                                    <a href="{{ $client->company->financial_plan_url }}"
                                                      target="_blank" class="btn btn-warning mt-1">
                                                      <i class="bx bx-search-alt"></i> {{ __('Preview') }}
                                                    </a>
                                                  @endif
                                                </div>
                                                <div class="form-group">
                                                  <label class="fs-6 mb-2"
                                                    for="financial_plan_description">{{ __('Financial Plan Description') }}</label>
                                                  <textarea class="form-control " id="financial_plan_description" name='financial_plan_description'
                                                    placeholder="{{ __('Financial Plan Description') }}">{{ $client->company ? $client->company->financial_plan_description : old('financial_plan_description') }}</textarea>
                                                </div>
                                              </div>

                                            </div>
                                            <div class="row">
                                              <div class="col-12 d-flex justify-content-end">
                                                <button type="submit"
                                                  class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                                              </div>
                                            </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="jq-tab-content" data-tab="3">
                              <div class="row">
                                <div class="col-12">
                                  <div class="row">
                                    <div class="col-md-12">
                                      <div class="card">
                                        <div class="card-header">
                                          <h4 class="card-title">{{ __('admin.Funding Overview') }}</h4>
                                        </div>
                                        <div class="card-content">
                                          <div class="card-body">
                                            <form method="post" action="{{ route('update_my_company') }}"
                                              enctype="multipart/form-data">
                                              {!! csrf_field() !!}
                                              <div class="row">
                                                <div class="col-md-6">
                                                  <fieldset class="form-group">
                                                    <label class="fs-6 mb-2" class="text-bold-600"
                                                      for="development_stage">{{ __('admin.development_stage') }}
                                                      *</label>
                                                    <select class="form-control mt-2" required id="development_stage"
                                                      name='development_stage'>
                                                      <option value="Idea Exploration"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Idea Exploration' ? 'selected' : '' }}>
                                                        Idea Exploration</option>
                                                      <option value="Idea Development"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Idea Development' ? 'selected' : '' }}>
                                                        Idea Development</option>
                                                      <option value="Prototype testing"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Prototype testing' ? 'selected' : '' }}>
                                                        Prototype testing</option>
                                                      <option value="Launch"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Launch' ? 'selected' : '' }}>
                                                        Launch</option>
                                                      <option value="SEED"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'SEED' ? 'selected' : '' }}>
                                                        SEED</option>
                                                      <option value="Growth"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Growth' ? 'selected' : '' }}>
                                                        Growth</option>
                                                      <option value="Maturity"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Maturity' ? 'selected' : '' }}>
                                                        Maturity</option>
                                                      <option value="Acquired"
                                                        {{ !empty($client->company) && $client->company->development_stage == 'Acquired' ? 'selected' : '' }}>
                                                        Acquired</option>
                                                    </select>
                                                  </fieldset>
                                                </div>
                                                <div class="col-md-6">
                                                  <fieldset class="form-group">
                                                    <label class="fs-6 mb-2" class="text-bold-600"
                                                      for="revenue_generating">{{ __('admin.revenue_generating') }}
                                                      *</label>
                                                    <select class="form-control mt-2" required id="revenue_generating"
                                                      name='revenue_generating'>
                                                      <option value="No"
                                                        {{ !empty($client->company) && $client->company->revenue_generating == 'No' ? 'selected' : '' }}>
                                                        No</option>
                                                      <option value="Yes"
                                                        {{ !empty($client->company) && $client->company->revenue_generating == 'Yes' ? 'selected' : '' }}>
                                                        Yes</option>
                                                    </select>
                                                  </fieldset>
                                                </div>
                                                <div class="col-md-6">
                                                  <fieldset class="form-group">
                                                    <label class="fs-6 mb-2" class="text-bold-600"
                                                      for="currently_fundraising">{{ __('admin.currently_fundraising') }}
                                                      *</label>
                                                    <select class="form-control mt-2" required
                                                      id="currently_fundraising" name='currently_fundraising'>
                                                      <option value="No"
                                                        {{ !empty($client->company) && $client->company->currently_fundraising == 'No' ? 'selected' : '' }}>
                                                        No</option>
                                                      <option value="Yes"
                                                        {{ !empty($client->company) && $client->company->currently_fundraising == 'Yes' ? 'selected' : '' }}>
                                                        Yes</option>
                                                    </select>
                                                  </fieldset>
                                                </div>
                                                <div class="col-md-6">
                                                  <fieldset class="form-group">
                                                    <label class="fs-6 mb-2" class="text-bold-600"
                                                      for="desired_funding_amount">{{ __('admin.desired_funding_amount') }}
                                                      *</label>
                                                    <select class="form-control mt-2" required
                                                      id="desired_funding_amount" name='desired_funding_amount'>
                                                      <option value="No Funding"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == 'No Funding' ? 'selected' : '' }}>
                                                        No Funding</option>
                                                      <option value="0-25k"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '0-25k' ? 'selected' : '' }}>
                                                        0-25k</option>
                                                      <option value="26K-50k"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '26K-50k' ? 'selected' : '' }}>
                                                        26K-50k</option>
                                                      <option value="51K-100k"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '51K-100k' ? 'selected' : '' }}>
                                                        51K-100k</option>
                                                      <option value="101K-250k"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '101K-250k' ? 'selected' : '' }}>
                                                        101K-250k</option>
                                                      <option value="251K-500k"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '251K-500k' ? 'selected' : '' }}>
                                                        251K-500k</option>
                                                      <option value="501K-1m"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '501K-1m' ? 'selected' : '' }}>
                                                        501K-1m</option>
                                                      <option value="1m-2.5m"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '1m-2.5m' ? 'selected' : '' }}>
                                                        1m-2.5m</option>
                                                      <option value="5m-10m"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '5m-10m' ? 'selected' : '' }}>
                                                        5m-10m</option>
                                                      <option value="10m-25m"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '10m-25m' ? 'selected' : '' }}>
                                                        10m-25m</option>
                                                      <option value="25m+"
                                                        {{ !empty($client->company) && $client->company->desired_funding_amount == '25m+' ? 'selected' : '' }}>
                                                        25m+</option>
                                                    </select>
                                                  </fieldset>
                                                </div>
                                              </div>
                                              <div class="row">
                                                <div class="col-12 d-flex justify-content-end">
                                                  <button type="submit"
                                                    class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                                                </div>
                                              </div>
                                            </form>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-12">
                                  <section class="row">
                                    <div class="col-xl-12 col-md-12 col-sm-12">
                                      <div class="card">
                                        <div class="card-header border-bottom">
                                          <h4 class="card-title align-items-center">
                                            <i
                                              class='bx bx-card font-medium-4 mr-1'></i>{{ __('admin.Funding Data') }}:
                                            <a href="#" class="btn btn-icon  btn-success float-right roundBtn"
                                              data-value="create"
                                              data-url="{{ route('startup.fundingRounds.store') }}">
                                              <i class="bx bx-plus"></i> {{ __('admin.Add new funding round') }}</a>
                                          </h4>
                                        </div>
                                        <div class="card-body">
                                          <div class="card-text">
                                            <div class="table-responsive">
                                              <table class="table mb-0" id="userstable">
                                                <thead class="thead-dark">
                                                  <tr>
                                                    <th class="text-center">{{ __('admin.Funding Stage') }}</th>
                                                    <th class="text-center">{{ __('admin.Amount') }}</th>
                                                    <th class="text-center">{{ __('admin.Post Money Valuation') }}</th>
                                                    <th class="text-center">{{ __('admin.Date Raised') }}</th>
                                                    <th class="text-center">{{ __('admin.Investment Vehicle') }}</th>
                                                    <th class="text-center">{{ __('admin.Amount Raised') }}</th>
                                                    <th class="text-center">{{ __('cycles.Actions') }}</th>
                                                  </tr>
                                                </thead>
                                                <tbody>
                                                  @foreach ($funding_rounds as $round)
                                                    <tr id="row_{{ $round->id }}">
                                                      <td class="text-center">
                                                        {{ $round->funding_stage }}
                                                      </td>
                                                      <td class="text-center">
                                                        {{ $round->amount }}
                                                      </td>
                                                      <td class="text-center">
                                                        {{ $round->post_money_valuation }}
                                                      </td>
                                                      <td class="text-center">
                                                        {{ $round->date_raised }}
                                                      </td>
                                                      <td class="text-center">
                                                        {{ $round->investment_vehicle }}
                                                      </td>
                                                      <td class="text-center">
                                                        {{ $round->amount_raised }}
                                                      </td>

                                                      <td class="text-center">
                                                        <a href="#" title="Edit"
                                                          class="btn btn-icon rounded-circle btn-light-primary roundBtn"
                                                          data-value="edit"
                                                          data-url="{{ route('startup.fundingRounds.update', ['fundingRound' => $round->id]) }}"
                                                          data-id="{{ $round->id }}">
                                                          <i class="bx bxs-edit-alt"></i></a>
                                                        <a onclick="sweet_delete( '{{ route('startup.fundingRounds.destroy', ['fundingRound' => $round->id]) }} ' , '{{ trans('admin.deleted_msg_confirm') }}' ,{{ $round->id }} )"
                                                          title="Delete"
                                                          class="btn btn-icon rounded-circle btn-light-danger">
                                                          <i class="bx bxs-x-square"></i></a>
                                                      </td>
                                                    </tr>
                                                  @endforeach

                                                </tbody>
                                              </table>
                                            </div>
                                          </div>

                                        </div>
                                      </div>
                                    </div>



                                  </section>
                                </div>
                              </div>
                            </div>
                            <div class="jq-tab-content" data-tab="4">
                              <form method="post" action="{{ route('update_my_company') }}"
                                enctype="multipart/form-data">
                                {!! csrf_field() !!}
                                <div class="row">
                                  <div class="col-md-12 col-12 mb-3 ">
                                    <div class="form-group">
                                      <label class="fs-6 mb-2" for="about_problem">What problem are you solving, or what
                                        value are you creating?</label>
                                      <textarea class="form-control height-100" id="about_problem" name='about_problem'>{{ $client->company ? $client->company->about_problem : old('about_problem') }}</textarea>
                                    </div>
                                  </div>
                                  <div class="col-md-12 col-12 mb-3 ">
                                    <div class="form-group">
                                      <label class="fs-6 mb-2" for="about_market_opportunity">What's your market
                                        opportunity and how will you acquire customers?</label>
                                      <textarea class="form-control height-100" id="about_market_opportunity" name='about_market_opportunity'>{{ $client->company ? $client->company->about_market_opportunity : old('about_market_opportunity') }}</textarea>
                                    </div>
                                  </div>
                                  <div class="col-md-12 col-12 mb-3 ">
                                    <div class="form-group">
                                      <label class="fs-6 mb-2" for="about_competitors">Who are your competitors and what
                                        makes you different?</label>
                                      <textarea class="form-control height-100" id="about_competitors" name='about_competitors'>{{ $client->company ? $client->company->about_competitors : old('about_competitors') }}</textarea>
                                    </div>
                                  </div>
                                  <div class="col-md-12 col-12 mb-3 ">
                                    <div class="form-group">
                                      <label class="fs-6 mb-2" for="about_monetize">How will you monetize?</label>
                                      <textarea class="form-control height-100" id="about_monetize" name='about_monetize'>{{ $client->company ? $client->company->about_monetize : old('about_monetize') }}</textarea>
                                    </div>
                                  </div>
                                  <div class="col-md-12 col-12 mb-3 ">
                                    <div class="form-group">
                                      <label class="fs-6 mb-2" for="about_challenges">What are your top three
                                        challenges?</label>
                                      <textarea class="form-control height-100" id="about_challenges" name='about_challenges'>{{ $client->company ? $client->company->about_challenges : old('about_challenges') }}</textarea>
                                    </div>
                                  </div>

                                </div>
                                <div class="row">
                                  <div class="col-12 d-flex justify-content-end">
                                    <button type="submit"
                                      class="btn btn-primary mr-1 mb-1">{{ __('admin.Update') }}</button>
                                  </div>
                                </div>
                              </form>
                            </div>
                            <div class="jq-tab-content" data-tab="5">

                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- BuilderForm end -->


      </div>
    </div>
  </div>
  <!-- END: Content-->

  <div class="modal fade text-left" id="SendRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form method="post" class="fundingForm" action="{{ route('startup.fundingRounds.store') }}"
          enctype="multipart/form-data" autocomplete="off">
          <div class="modal-header">
            <h4 class="modal-title">{{ __('admin.Add new funding round') }}
            </h4>
            
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" name="_method" value="PUT" class="fundingMethod">
            <div class="row">
              <div class="form-group col-6">
                <label class="fs-6 mb-2">{{ __('admin.Funding Stage') }} * :</label>
                <select class="form-control mt-2" required id="funding_stage" name='funding_stage'>
                  <option value="Undisclosed"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Undisclosed' ? 'selected' : '' }}>
                    Undisclosed</option>
                  <option value="Friends and Family"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Friends and Family' ? 'selected' : '' }}>
                    Friends and Family</option>
                  <option value="SEED"
                    {{ !empty($client->company) && $client->company->funding_stage == 'SEED' ? 'selected' : '' }}>
                    SEED</option>
                  <option value="Pre-Series A"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Pre-Series A' ? 'selected' : '' }}>
                    Pre-Series A</option>
                  <option value="Series A"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Series A' ? 'selected' : '' }}>
                    Series A</option>
                  <option value="Series B"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Series B' ? 'selected' : '' }}>
                    Series B</option>
                  <option value="Angel Investment"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Angel Investment' ? 'selected' : '' }}>
                    Angel Investment</option>
                  <option value="Pre-SEED"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Pre-SEED' ? 'selected' : '' }}>
                    Pre-SEED</option>
                  <option value="Series C"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Series C' ? 'selected' : '' }}>
                    Series C</option>
                  <option value="Series D"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Series D' ? 'selected' : '' }}>
                    Series D</option>
                  <option value="Bridge"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Bridge' ? 'selected' : '' }}>
                    Bridge</option>
                  <option value="Convertible Note"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Convertible Note' ? 'selected' : '' }}>
                    Convertible Note</option>
                  <option value="Merger and Acquisition (M&A)"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Merger and Acquisition (M&A)' ? 'selected' : '' }}>
                    Merger and Acquisition (M&A)</option>
                  <option value="Series E"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Series E' ? 'selected' : '' }}>
                    Series E</option>
                  <option value="Series F"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Series F' ? 'selected' : '' }}>
                    Series F</option>
                  <option value="Crowdfunding"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Crowdfunding' ? 'selected' : '' }}>
                    Crowdfunding</option>
                  <option value="Private Equity"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Private Equity' ? 'selected' : '' }}>
                    Private Equity</option>
                  <option value="Pre-Series B"
                    {{ !empty($client->company) && $client->company->funding_stage == 'Pre-Series B' ? 'selected' : '' }}>
                    Pre-Series B</option>

                </select>
              </div>
              <div class="form-group col-6">
                <label class="fs-6 mb-2">{{ __('admin.Amount') }} * :</label>
                <input value="{{ $client->company ? $client->company->amount : old('amount') }}" name="amount"
                  type="number" class="form-control mt-2" id="amount" placeholder="{{ __('admin.Amount') }} *"
                  required autocomplete="amount" />
              </div>
              <div class="form-group col-6">
                <label class="fs-6 mb-2">{{ __('admin.Post Money Valuation') }} * :</label>
                <input
                  value="{{ $client->company ? $client->company->post_money_valuation : old('post_money_valuation') }}"
                  name="post_money_valuation" type="number" class="form-control mt-2" id="post_money_valuation"
                  placeholder="{{ __('admin.Post Money Valuation') }} *" required
                  autocomplete="post_money_valuation" />
              </div>
              <div class="form-group col-6">
                <label class="fs-6 mb-2">{{ __('admin.Date Raised') }} * :</label>
                <div class="input-group">
                  <input type="date" name="date_raised" id="date_raised"
                    placeholder="{{ __('admin.selectDate') }}" class="form-control mt-2">
                </div>
              </div>
              <div class="form-group col-6">
                <label class="fs-6 mb-2">{{ __('admin.Investment Vehicle') }} * :</label>
                <div class="input-group">
                  <select class="form-control mt-2" required id="investment_vehicle" name='investment_vehicle'>
                    <option value="Equity"
                      {{ !empty($client->company) && $client->company->investment_vehicle == 'Equity' ? 'selected' : '' }}>
                      Equity</option>
                    <option value="Venture Debt"
                      {{ !empty($client->company) && $client->company->investment_vehicle == 'Venture Debt' ? 'selected' : '' }}>
                      Venture Debt</option>
                    <option value="Conventional Debt"
                      {{ !empty($client->company) && $client->company->investment_vehicle == 'Conventional Debt' ? 'selected' : '' }}>
                      Conventional Debt</option>
                    <option value="Grant"
                      {{ !empty($client->company) && $client->company->investment_vehicle == 'Grant' ? 'selected' : '' }}>
                      Grant</option>
                    <option value="Non-equity Assistance"
                      {{ !empty($client->company) && $client->company->investment_vehicle == 'Non-equity Assistance' ? 'selected' : '' }}>
                      Non-equity Assistance</option>
                    <option value="Line of Credit"
                      {{ !empty($client->company) && $client->company->investment_vehicle == 'Line of Credit' ? 'selected' : '' }}>
                      Line of Credit</option>

                  </select>
                </div>
              </div>
              <div class="form-group col-6">
                <label class="fs-6 mb-2">{{ __('admin.Amount Raised') }} * :</label>
                <input value="{{ $client->company ? $client->company->post_money_valuation : old('amount_raised') }}"
                  name="amount_raised" type="number" class="form-control mt-2" id="amount_raised"
                  placeholder="{{ __('admin.Amount Raised') }} *" required autocomplete="amount_raised" />
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary ml-1">
              <i class="bx bx-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script src="/tabs/dist/jquery.tabs.min.js"></script>
  <script src="/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
  <script>
    const contactButton = document.getElementById('contactButton');
    const infoButton = document.getElementById('infoButton');

    const infoForm = document.getElementById('infoForm');
    const contactForm = document.getElementById('contactForm');

    contactForm.style.display = 'none';

    infoButton.addEventListener('click', function() {
      console.log('clicked');
      infoForm.style.display = 'block';
      contactForm.style.display = 'none';
      infoButton.classList.remove('btn-label-secondary');
      infoButton.classList.add('btn-primary');
      contactButton.classList.remove('btn-primary');
      contactButton.classList.add('btn-label-secondary');
    });

    contactButton.addEventListener('click', function() {
      console.log('clicked');
      infoForm.style.display = 'none';
      contactForm.style.display = 'block';
      contactButton.classList.remove('btn-label-secondary');
      contactButton.classList.add('btn-primary');
      infoButton.classList.remove('btn-primary');
      infoButton.classList.add('btn-label-secondary');
    });
  </script>

  <script>
    document.getElementById('img_url').addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('company-logo').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
  <script>
    document.getElementById('update-form').addEventListener('submit', function(event) {
    var button = document.getElementById('submit-button');
    var buttonText = document.getElementById('button-text');
    var buttonSpinner = document.getElementById('button-spinner');

    button.disabled = true;
    buttonText.classList.add('d-none');
    buttonSpinner.classList.remove('d-none');
  });
  </script>

  <script>
    $(document).ready(function() {
      $('.roundBtn').click(function() {
        if ($(this).data('value') === 'create') {
          $('.fundingMethod').attr('name', '');
        } else {
          $('.fundingMethod').attr('name', '_method');
          $.ajax({
            url: "/dashboard/startup/fundingRounds/" + $(this).data('id'),
            success: function(data) {
              $('#funding_stage').val(data['funding_stage']);
              $('#amount').val(data['amount']);
              $('#post_money_valuation').val(data['post_money_valuation']);
              $('#date_raised').val(data['date_raised']);
              $('#investment_vehicle').val(data['investment_vehicle']);
              $('#amount_raised').val(data['amount_raised']);

            }
          });
        }
        $('.fundingForm').attr('action', $(this).data('url'));
        $('#SendRequest').modal('show');
      });

      $('#institutions').select2({
        tags: true,
        placeholder: 'Type and press enter to add',
        allowClear: true
      });
    });
    $(function() {
      $('#verticalTab').jqTabs();
      $('#horizontalTab').jqTabs({
        direction: 'horizontal'
      });
    });
  </script>
  <script>
    $(document).ready(function() {
      $('#est_date').datetimepicker({
        timepicker: false,
        format: 'Y-m-d'
      });
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
      var currentState = '{{ $client->company ? optional($client->company)->state_id : 0 }}';
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
      var currentCity = '{{ $client->company ? optional($client->company)->city_id : 0 }}';
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
  <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
  <script>
    $('#logo').filemanager(['image']);

    function ssector(selection) {
      if (selection === 'other') {
        let other = prompt('{{ __('Enter your company sector name') }}');
        if (other) {
          let newItem = "<option selected value='" + other + "'>" + other + "</option>";
          $('#selectsector').append(newItem);
        } else {
          $('#selectsector').val('Industrial');
        }
      }
    }

    
  </script>
@stop
