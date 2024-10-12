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
                    <ul class="nav nav-tabs flex-wrap" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="infoButton-tab" data-bs-toggle="tab" href="#infoButton" role="tab" aria-controls="infoButton" aria-selected="true">
                          {{ __('admin.company Information') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Contact_Information-tab" data-bs-toggle="tab" href="#Contact_Information" role="tab" aria-controls="Contact_Information" aria-selected="false">
                          {{ __('admin.Contact_Information') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Company_Description-tab" data-bs-toggle="tab" href="#Company_Description" role="tab" aria-controls="Company_Description" aria-selected="false">
                          {{ __('admin.Company_Description') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Financial_Information-tab" data-bs-toggle="tab" href="#Financial_Information" role="tab" aria-controls="Financial_Information" aria-selected="false">
                          {{ __('admin.Financial_Information') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Team_Information-tab" data-bs-toggle="tab" href="#Team_Information" role="tab" aria-controls="Team_Information" aria-selected="false">
                          {{ __('admin.Team_Information') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Pitch_Deck_&_Supporting_Documents-tab" data-bs-toggle="tab" href="#Pitch_Deck_&_Supporting_Documents" role="tab" aria-controls="Pitch_Deck_&_Supporting_Documents" aria-selected="false">
                          {{ __('admin.Pitch_Deck_&_Supporting_Documents') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Milestones-tab" data-bs-toggle="tab" href="#Milestones" role="tab" aria-controls="Milestones" aria-selected="false">
                          {{ __('admin.Milestones') }}
                        </a>
                      </li>
                      <li class="nav-item" role="presentation">
                        <a class="nav-link" id="Profile_Picture_and_Social_Links-tab" data-bs-toggle="tab" href="#Profile_Picture_and_Social_Links" role="tab" aria-controls="Profile_Picture_and_Social_Links" aria-selected="false">
                          {{ __('admin.Profile_Picture_and_Social_Links') }}
                        </a>
                      </li>
                      {{-- <li class="nav-item" role="presentation">
                        <a class="nav-link" id="contactButton-tab" data-bs-toggle="tab" href="#contactButton" role="tab" aria-controls="contactButton" aria-selected="false">
                          {{ __('admin.social & contact') }}
                        </a>
                      </li> --}}
                    </ul>
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


                    <div class="tab-content">

                      
                      <div class="tab-pane fade show active" id="infoButton" role="tabpanel" aria-labelledby="infoButton-tab">
                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}

                      <input type="hidden" value="1" name="CompanyN">
                      <div class="row">
                        
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
                       
                        
                        <div class="col-md-12 col-12 mb-3">
                          <div class="form-label-group mt-75">
                            <label class="fs-6 mb-2" for="est_date">{{ __('admin.est_date') }}</label>
                            <input type="text" class="form-control mt-2" id="est_date"
                              value="{{ $client->company ? $client->company->est_date : old('est_date') }}"
                              name="est_date">
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

                      </div>
                       <!-- Close Form Here  infoForm -->
                      </div>

                  <!-- Open Form Here Contact_Information -->
                  <div class="tab-pane fade" id="Contact_Information" role="tabpanel" aria-labelledby="Contact_Information-tab">

                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}

                      <input type="hidden" value="2" name="CompanyN">
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
                          <label class="fs-6 mb-2" for="WhatsApp">{{ __('admin.WhatsApp') }}*</label>
                          <input type="text" class="form-control mt-2" id="WhatsApp"
                            placeholder="{{ __('admin.WhatsApp') }}*"
                            value="{{ $client->company ? $client->company->WhatsApp : old('WhatsApp') }}" name="WhatsApp"
                            required autocomplete="WhatsApp">
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




                      <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary mr-1 mb-1" id="submit-button">
                            <span id="button-text">{{ __('admin.Update') }}</span>
                            <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                          </button>
                        </div>
                      </div>

                    </form>

                  </div>
                    <!-- Close Form Here  Contact_Information -->
                    <!-- Open Form Here  Company_Description -->
                    <div class="tab-pane fade" id="Company_Description" role="tabpanel" aria-labelledby="Company_Description-tab">

                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}


                      <input type="hidden" value="3" name="CompanyN">


                      <div class="col-md-6 col-12 mb-3">
                        <div class="form-label-group">
                          <label class="fs-6 mb-2" for="About_the_Company">{{ __('admin.About_the_Company') }} </label>   
                          <textarea class="form-control mt-2" id="About_the_Company" maxlength="500" name='company_description' placeholder="{{ __('admin.About_the_Company') }}" >{{ $client->company->info_company }}</textarea>
                        </div>
                      </div>

                      <div class="form-group mb-3">
                        <label class="form-label fs-6">
                          {{ __('admin.Sustainable_development') }}
                        </label>
                        
                        <select name="Sustainable_development[]" class="select2 form-control" multiple="multiple">
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


                      <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary mr-1 mb-1" id="submit-button">
                            <span id="button-text">{{ __('admin.Update') }}</span>
                            <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                          </button>
                        </div>
                      </div>

                    </form>

                    </div>
                    <!-- Close Form Here  Company_Description -->
                    <!-- Open Form Here  Financial_Information -->
                    
                    <div class="tab-pane fade" id="Financial_Information" role="tabpanel" aria-labelledby="Financial_Information-tab">

                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}

                      <input type="hidden" value="4" name="CompanyN">
                      
                      <div class="row">
                      <div class="col-md-6 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="Currency">{{ __('settings.Currency') }}</label>
                          <select class="form-control" name="Currency">
                            <option value="">{{ __('settings.Currency') }}</option>
                            @foreach ($Currencies as $Currency)
                            <option value="{{ $Currency->currency_code }}" {{ $client->company && $client->company->Currency == $Currency->currency_code ? 'selected' : '' }}> {{ $Currency->currency_name }} </option>
                            @endforeach
                           
                        </select>
                          
                        </div>
                      </div>


                      <div class="col-md-6 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="pre_money_valuation">{{ __('admin.Pre_money_Valuation') }}</label>
                          <input type="number" class="form-control mt-2" id="pre_money_valuation"
                            placeholder="{{ __('admin.Pre_money_Valuation') }}"
                            value="{{ $client->company ? $client->company->pre_money_valuation : old('pre_money_valuation') }}"
                            name="pre_money_valuation" autocomplete="pre_money_valuation">
                        </div>
                      </div>

                      
                      <div class="col-md-6 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="raised_fund">{{ __('admin.raised_fund_egp') }}{{ $Setting->Currency }}</label>
                          <input type="number" class="form-control mt-2" id="raised_fund"
                            placeholder="{{ __('admin.raised_fund_egp') }} "
                            value="{{ $client->company ? $client->company->raised_fund : old('raised_fund') }}"
                            name="raised_fund" autocomplete="raised_fund">
                        </div>
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
                          <label class="fs-6 mb-2" for="targeted_market">{{ __('admin.Targeted_Market') }}
                            </label>
                          <input type="number" class="form-control mt-2" id="targeted_market"
                            placeholder="{{ __('admin.Targeted_Market') }}"
                            value="{{ $client->company ? $client->company->targeted_market : old('Targeted Market') }}"
                            name="targeted_market" autocomplete="targeted_market">
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



                      

                      <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary mr-1 mb-1" id="submit-button">
                            <span id="button-text">{{ __('admin.Update') }}</span>
                            <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                          </button>
                        </div>
                      </div>

                      </div>
                    </form>


                    </div>
                    <!-- Close Form Here  Financial_Information -->
                    <!-- Open Form Here  Team_Information -->


                    <div class="tab-pane fade" id="Team_Information" role="tabpanel" aria-labelledby="Team_Information-tab">
                      
              <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data">
                      {!! csrf_field() !!}

                      <input type="hidden" value="5" name="CompanyN">

                      <div class="row">

                     


                      <h2>Founders</h2>
                    <div class="show_Founders row">
                      <div class="col-md-4 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="founder_name">{{ __('admin.founder_name') }}</label>
                          <input type="text" class="form-control mt-2 founder_name" id="founder_name"
                            placeholder="{{ __('admin.founder_name') }}"
                            value="{{ $client->company ? $client->company->founder_name : old('founder_name') }}"
                            name="founder_name" autocomplete="founder_name">
                        </div>
                      </div>

                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="founder_email">{{ __('admin.founder_email') }}</label>
                          <input type="text" class="form-control mt-2 founder_email" id="founder_email"
                            placeholder="{{ __('admin.founder_email') }}"
                            value="{{ $client->company ? $client->company->founder_email : old('founder_email') }}"
                            name="founder_email" autocomplete="founder_email">
                        </div>
                      </div>
                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="founder_linkedin">{{ __('admin.founder_linkedin') }}</label>
                          <input type="text" class="form-control mt-2 founder_linkedin" id="founder_linkedin"
                            placeholder="{{ __('admin.founder_linkedin') }}"
                            value="{{ $client->company ? $client->company->founder_linkedin : old('founder_linkedin') }}"
                            name="founder_linkedin" autocomplete="founder_linkedin">
                        </div>
                      </div>

                      <div class="col-md-2 col-12 mb-3">
                        <div class="form-label-group mt-75 text-center">
                          <br>
                          <div class="btn btn-success AddFounders"> {{ __('admin.Add') }}</div>
                        </div>
                      </div>
                      
                    </div>

                    <h2>Shareholders</h2>
                              <!-- ===================== Shareholders ===========================-->
                    <div class="show_Shareholders row">
                      <div class="col-md-4 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="shareholder_name">{{ __('admin.shareholder_name') }}</label>
                          <input type="text" class="form-control mt-2 shareholder_name" id="shareholder_name"
                            placeholder="{{ __('admin.shareholder_name') }}"
                            value="{{ $client->company ? $client->company->shareholder_name : old('shareholder_name') }}"
                            name="shareholder_name" autocomplete="shareholder_name">
                        </div>
                      </div>

                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="shareholder_role">{{ __('admin.shareholder_role') }}</label>
                          <input type="text" class="form-control mt-2 shareholder_role" id="shareholder_role"
                            placeholder="{{ __('admin.shareholder_role') }}"
                            value="{{ $client->company ? $client->company->shareholder_role : old('shareholder_role') }}"
                            name="shareholder_role" autocomplete="shareholder_role">
                        </div>
                      </div>
                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="shareholder_ownership_percentage">{{ __('admin.shareholder_ownership_percentage') }}</label>
                          <input type="number" class="form-control mt-2 shareholder_ownership_percentage" id="shareholder_ownership_percentage"
                            placeholder="{{ __('admin.shareholder_ownership_percentage') }}"
                            value="{{ $client->company ? $client->company->shareholder_ownership_percentage : old('shareholder_ownership_percentage') }}"
                            name="shareholder_ownership_percentage" autocomplete="shareholder_ownership_percentage">
                        </div>
                      </div>


                      <div class="col-md-2 col-12 mb-3">
                        <div class="form-label-group mt-75 text-center">
                          <br>
                          <div class="btn btn-success AddSherHolders"> {{ __('admin.Add') }}</div>
                        </div>
                      </div>


                      </div>


                      <h2>Investors</h2>
                      <!-- ===================== Investors  ===========================-->
                    
                      <div class="show_investor row">

                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="investor_name">{{ __('admin.investor_name') }}</label>
                          <input type="text" class="form-control mt-2 investor_name" id="investor_name"
                            placeholder="{{ __('admin.investor_name') }}"
                            value="{{ $client->company ? $client->company->investor_name : old('investor_name') }}"
                            name="investor_name" autocomplete="investor_name">
                        </div>
                      </div>

                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="investment_type">{{ __('admin.investment_type') }}</label>
                          <input type="text" class="form-control mt-2 investment_type" id="investment_type"
                            placeholder="{{ __('admin.investment_type') }}"
                            value="{{ $client->company ? $client->company->investment_type : old('investment_type') }}"
                            name="investment_type" autocomplete="investment_type">
                        </div>
                      </div>

                      <div class="col-md-2 col-12 mb-3">
                        <div class="form-label-group mt-75 text-center">
                          <br>
                          <div class="btn btn-success AddInvistor"> {{ __('admin.Add') }}</div>
                        </div>
                        <br><br><br><br>
                      </div>


                    </div>


<div class=" row">
 
                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-label-group mt-75">
                          <label class="fs-6 mb-2" for="team_num">{{ __('admin.team_num') }}</label>
                          <input type="number" class="form-control mt-2" id="team_num"
                            placeholder="{{ __('admin.team_num') }}"
                            value="{{ $client->company ? $client->company->team_num : old('team_num') }}"
                            name="team_num" autocomplete="new-team_num">
                        </div>
                      </div>


                      <div class="col-md-3 col-12 mb-3">
                        <div class="form-group">
                          <label class="fs-6 mb-2" for="working_team">{{ __('admin.Working_Team_Details') }}
                            </label>
                          <textarea class="form-control mt-2" id="working_team" name='working_team' required
                            placeholder="{{ __('admin.Working_Team_Details') }} *" maxlength="150" >{{ $client->company ? $client->company->working_team_summary : old('working_team') }}</textarea>
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



                    </div>

                      <div class="ShowTableResults">
                        
                        <table class="table border">
                          <thead>
                              <tr>
                                  <th>{{ __('admin.TypeTeam') }}</th>
                                  <th>{{ __('admin.Name') }}</th>
                                  <th>{{ __('admin.Email') }}</th>
                                  <th>{{ __('admin.Linkedin') }}</th>
                                  <th>{{ __('admin.shareholder_role') }}</th>
                                  <th>{{ __('admin.shareholder_ownership_percentage') }}</th>
                                  <th>{{ __('admin.investment_type') }}</th>
                                  <th>{{ __('admin.Created At') }}</th>
                                  <th>{{ __('admin.Actions') }}</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach($teamMembers as $member)
                                  <tr>
                                      <td>{{ $member->TeamMember }}</td>
                                      <td>{{ $member->Name }}</td>
                                      <td>{{ $member->Email }}</td>
                                      <td><a href="{{ $member->LinkedIn }}" target="_blank">{{ $member->LinkedIn }}</a></td>
                                      <td>{{ $member->RoleShereholder }}</td>
                                      <td>{{ $member->PresentagShereholder }}</td>
                                      <td>{{ $member->investorType }}</td>
                                      <td>{{ $member->created_at }}</td>
                                      <td><div class="btn btn-success">Edit</div></td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>


                      </div>

                      </div>
              </form>

                    </div>
                    <!-- Close Form Here  Team_Information -->

                    <div class="tab-pane fade" id="Pitch_Deck_&_Supporting_Documents" role="tabpanel" aria-labelledby="Pitch_Deck_&_Supporting_Documents-tab">
                      
                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <input type="hidden" value="6" name="CompanyN">

                      <div class="col-md-12 col-12 mb-3">
                      <label class="fs-6 mb-2">{{ __('admin.Pitch_Deck') }}</label>
                      <div class="input-group">
                        
                        <span class="input-group-btn">
                          
                          <a  data-input="file" data-preview="holderfile" class="btn btn-primary lfm">
                            <i class="fa fa-picture-o"></i> {{ __('admin.Choose') }}
                          </a>
                        </span>
                        <input id="file" class="form-control" type="text" name="pitch_deck" >
                      </div>
                      
                    </div>

                      
                    <div class="col-md-12 col-12 mb-3">
                      <label class="fs-6 mb-2">{{ __('admin.Pitch_Deck_Video') }}</label>
                      <div class="input-group">
                        
                        <span class="input-group-btn">
                          
                          <a  data-input="video" data-preview="holdervideo" class="btn btn-primary lfm">
                            <i class="fa fa-picture-o"></i> {{ __('admin.Choose') }}
                          </a>
                        </span>
                        <input id="video" class="form-control" type="text" name="pitch_deck_video" >
                      </div>
                      
                    </div>

                      
                      

                          <!-- ===================== Pitch_Deck_Information  ===========================-->
                          <div class="col-md-6 col-12 mb-3">
                            <label  class="fs-6 mb-2"> {{ __('admin.Pitch_Deck_Information') }} </label>
                            <select name="pitch_deck_info" class="select2 form-control">
                              <option value="" >{{ __('admin.Choose') }}</option>
                              <option value="yes " >{{ __('admin.Yes') }}</option>
                              <option value="no " >{{ __('admin.No') }}</option>
                            </select>
                          </div>


                          <div class="col-md-6 col-12 mb-3">
                            <div class="form-label-group mt-75">
                              <label class="fs-6 mb-2" for="pitch_deck_summary">{{ __('admin.Pitch_Deck_Summary') }}</label>
                                <textarea name="pitch_deck_summary" autocomplete="pitch_deck_summary" class="form-control mt-2" id="pitch_deck_summary" placeholder="{{ __('admin.Pitch_Deck_Summary') }}">{{ $client->company ? $client->company->pitch_deck_summary : old('pitch_deck_summary') }}</textarea>
                            </div>
                          </div>

                          <div class="col-md-6 col-12 mb-3">
                            <div class="form-label-group mt-75">
                              <label class="fs-6 mb-2" for="problem_facing">{{ __('admin.Problem_We_Are_Facing') }}</label>
                                <textarea name="problem_facing" autocomplete="problem_facing" class="form-control mt-2" id="problem_facing" placeholder="{{ __('admin.problem_facing') }}">{{ $client->company ? $client->company->problem_facing : old('problem_facing') }}</textarea>
                            </div>
                          </div>


                          <div class="col-md-6 col-12 mb-3">
                            <div class="form-label-group mt-75">
                              <label class="fs-6 mb-2" for="solution_problem">{{ __('admin.solution_problem') }}</label>

                                <textarea name="solution_problem" autocomplete="solution_problem" class="form-control mt-2" id="solution_problem" placeholder="{{ __('admin.solution_problem') }}">{{ $client->company ? $client->company->solution_problem : old('solution_problem') }}</textarea>
                            </div>
                          </div>


                          {{-- <div class="col-md-12 col-12 mb-3">
                            @if (isset($client->company->documents))
                              
                            @endif
                            <div class="custom-file mb-3" style="width: 300px;">
                              <label class="fs-6 mb-2">{{ __('admin.Documents') }}</label>
                              <input 
                                type="file" 
                                class="form-control"
                                id="documents"
                                name="documents[]"
                                accept="application/pdf,application/vnd.openxmlformats-officedocument.presentationml.presentation"
                              />
                            </div>
                          </div> --}}


                          <div class="col-md-12 col-12 mb-3">
                            <label class="fs-6 mb-2">{{ __('admin.Documents') }}</label>
                            <div class="input-group">
                              
                              <span class="input-group-btn">
                                
                                <a  data-input="documents" data-preview="holderdocuments" class="btn btn-primary lfm">
                                  <i class="fa fa-picture-o"></i> {{ __('admin.Choose') }}
                                </a>
                              </span>
                              <input id="documents" class="form-control" type="text" name="documents[]" >
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
                    
                    
                    </div>
                    <!-- Close Form Here  Team_Information -->
                  <div class="tab-pane fade" id="Milestones" role="tabpanel" aria-labelledby="Milestones-tab">
                      
                      
                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}

                      <input type="hidden" value="7" name="CompanyN">

                      <div class="row">
                          <!-- ===================== Achieved_Milestones  ===========================-->
                            <div class="col-md-3 col-12 mb-3">
                              <label  class="fs-6 mb-2"> {{ __('admin.Milestones') }} </label>
                              <select name="achieved_milestones" class="select2 form-control achieved_milestones">
                                <option value="" >{{ __('admin.Choose') }}</option>
                                <option value="Achieved_Milestones " >{{ __('admin.Achieved_Milestones') }}</option>
                                <option value="Planned_Milestones " >{{ __('admin.Planned_Milestones') }}</option>
                                <option value="Archived_Milestones " >{{ __('admin.Archived_Milestones') }}</option>
                              </select>
                            </div>


                            <div class="col-md-3 col-12 mb-3 ">
                              <div class="form-label-group mt-75">
                                <label class="fs-6 mb-2" for="achieved_milestone_name">{{ __('admin.Milestone_Name') }}</label>
                                <input type="text" class="form-control mt-2 achieved_milestone_name" id="achieved_milestone_name"
                                placeholder="{{ __('admin.Milestone_Name') }}"
                                value="{{ $client->company ? $client->company->achieved_milestone_name : old('achieved_milestone_name') }}"
                                name="achieved_milestone_name" autocomplete="achieved_milestone_name">
                              </div>
                            </div>

                            <div class="col-md-3 col-12 mb-3 ">
                              <div class="form-label-group mt-75">
                                <label class="fs-6 mb-2" for="achieved_milestone_description">{{ __('admin.Description') }}</label>
                                <input type="text" class="form-control mt-2 achieved_milestone_description" id="achieved_milestone_description"
                                placeholder="{{ __('admin.Description') }}"
                                value="{{ $client->company ? $client->company->achieved_milestone_description : old('achieved_milestone_description') }}"
                                name="achieved_milestone_description" autocomplete="new-achieved_milestone_description">
                              </div>
                            </div>

                            
                            <div class="col-md-3 col-12 mb-3">
                              <div class="form-label-group mt-75 text-center">
                                <br>
                                <div class="btn btn-success AddMileStone"> {{ __('admin.Add') }}</div>
                              </div>
                            </div>



                            <div class="ShowTableResultsMil">
                        
                            <table class="table border">
                                <thead>
                                    <tr>
                                        <th>{{ __('admin.MileStone') }}</th>
                                        <th>{{ __('admin.Name') }}</th>
                                        <th>{{ __('admin.Description') }}</th>
                                        <th>{{ __('admin.Created At') }}</th>
                                        <th>{{ __('admin.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($MileStones as $milestone)
                                        <tr>
                                            <td>{{ __('admin.'. $milestone->plan) }}</td>
                                            <td>{{ $milestone->name }}</td>
                                            <td>{{ $milestone->description }}</td>
                                            <td>{{ $milestone->created_at }}</td>
                                            <td><div class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#Editmilestone{{ $milestone->id }}">{{ __('admin.Edit') }}</div></td>
                                        </tr>


                                        <!-- Edit Customer Modal -->
                                  <div class="modal fade" id="Editmilestone{{ $milestone->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                <div class="text-center mb-6">
                                                    <h4 class="mb-2"> {{ __('admin.Edit') }} ({{$milestone->name}})</h4>
                                                  
                                                </div>

                                                <form class="add-new-user pt-0"  method="POST">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="ecommerce-customer-add-basic mb-4" >

                                                        <div class="col-md-12">

                                                            <div class="mb-6 width49">
                                                                <div>
                                                                  <label  class="fs-6 mb-2"> {{ __('admin.Milestones') }} </label>
                                                                </div>
                                                                
                                                                  <select name="achieved_milestones" class="select2 form-control achieved_milestones">
                                                                    <option value="" >{{ __('admin.Choose') }}</option>
                                                                    <option value="Achieved_Milestones" {{ $milestone->plan == "Achieved_Milestones" ? 'selected' : "" }} >{{ __('admin.Achieved_Milestones') }}</option>
                                                                    <option value="Planned_Milestones" {{ $milestone->plan == "Planned_Milestones" ? 'selected' : "" }} >{{ __('admin.Planned_Milestones') }}</option>
                                                                    <option value="Archived_Milestones" {{ $milestone->plan == "Archived_Milestones" ? 'selected' : "" }} >{{ __('admin.Archived_Milestones') }}</option>
                                                                  </select>
                                                            </div>

                                                            <div class="mb-6 width49">
                                                              <label class="fs-6 mb-2" for="achieved_milestone_name">{{ __('admin.Milestone_Name') }}</label>
                                                              <input type="text" class="form-control mt-2 achieved_milestone_name" id="achieved_milestone_name"
                                                              placeholder="{{ __('admin.Milestone_Name') }}"
                                                              value="{{ $milestone->name ? $milestone->name : old('achieved_milestone_name') }}"
                                                              name="achieved_milestone_name" autocomplete="achieved_milestone_name">
                                                            </div>

                                                            <div class="mb-6 width49">
                                                              <label class="fs-6 mb-2" for="achieved_milestone_description">{{ __('admin.Description') }}</label>
                                                                <input type="text" class="form-control mt-2 achieved_milestone_description" id="achieved_milestone_description"
                                          placeholder="{{ __('admin.Description') }}"
                                          value="{{ $milestone->description ? $milestone->description : old('achieved_milestone_description') }}"
                                          name="achieved_milestone_description" autocomplete="new-achieved_milestone_description">
                                                            </div>

                                                            

                                                        </div>
                                                    </div>


                                                    <div class="text-center">
                                                        <div  class="btn btn-primary me-3 AddMileStone ">{{ __('admin.Update') }}</div>
                                                        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
                                                    </div>
                                                </form>



                                            </div>
                                        </div>
                                    </div>
                                </div>
                                  <!--/ Edit JO Modal -->
                                    @endforeach
                                </tbody>
                            </table>
      
      
                            </div>
                            
                            
                      </div>
                    </form>
                  </div>


                    <div class="tab-pane fade" id="Profile_Picture_and_Social_Links" role="tabpanel" aria-labelledby="Profile_Picture_and_Social_Links-tab">

                    <form method="post" action="{{ route('update_my_company') }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}

                      <input type="hidden" value="8" name="CompanyN">

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


                        <div class="input-group">
                          <span class="input-group-btn">
                            <a data-input="thumbnail" data-preview="holderimg" class="btn btn-primary lfm">
                              <i class="fa fa-picture-o"></i> {{ __('admin.Choose') }}
                            </a>
                          </span>
                          <input id="thumbnail" class="form-control" type="text" name="img_url" >
                        </div>
                        <div id="holderimg" style="margin-top:15px;max-height:100px;"></div>





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

                    </div>

                  </div>
                  </div>
                  </div>
                    
                      <!-- Close Form Here -->



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
  <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
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

    
    
  document.addEventListener('DOMContentLoaded', function () {
    // الاستماع لتغيير التبويبات
    document.querySelectorAll('.nav-link').forEach(tab => {
      tab.addEventListener('shown.bs.tab', function (e) {
        // تحديد التبويب النشط
        const activeTab = e.target.getAttribute('href');
        // تعطيل الحقول المطلوبة للتبويبات غير النشطة
        document.querySelectorAll('.tab-pane').forEach(pane => {
          if (pane.getAttribute('id') === activeTab.substring(1)) {
            pane.querySelectorAll('input').forEach(input => input.removeAttribute('disabled'));
          } else {
            pane.querySelectorAll('input').forEach(input => input.setAttribute('disabled', 'disabled'));
          }
        });
      });
    });
  });







 // Function to upload file with progress bar
 function uploadFile(inputId, progressBarId, route) {
        var formData = new FormData();
        var file = document.getElementById(inputId).files[0];
        formData.append(inputId, file);

        $.ajax({
            url: route,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        var percentComplete = Math.round((e.loaded / e.total) * 100);
                        $('#' + progressBarId).css('width', percentComplete + '%');
                        $('#' + progressBarId).attr('aria-valuenow', percentComplete);
                        $('#' + progressBarId).text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function (data) {
                if (data.success) {
                    console.log('File uploaded successfully: ' + data.path);
                }
            },
            error: function (xhr, status, error) {
                console.log('Error uploading file: ' + error);
            }
        });
    }

    // Upload for Pitch Deck
    $('#pitch_deck').on('change', function () {
        uploadFile('pitch_deck', 'progress-bar-pitch_deck', '{{ route('update_my_company') }}');
    });

    // Upload for Pitch Deck Video
    $('#pitch_deck_video').on('change', function () {
        uploadFile('pitch_deck_video', 'progress-bar-pitch_deck_video', '{{ route('update_my_company') }}');
    });

    // Upload for Pitch Deck Video
    $('#img_url').on('change', function () {
        uploadFile('img_url', 'progress-bar-Logo', '{{ route('update_my_company') }}');
    });


  </script>
@stop
