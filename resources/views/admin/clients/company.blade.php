@php
    $name = 'name_' . $l;
@endphp
@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" href="/tabs/dist/jquery.tabs.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/select/select2.min.css">

@stop
@section('title', __('admin.My company') )
@section('subTitle', __('admin.Edit the Company information'))
@section('content')
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
<style> .form-label-group > label { display: contents !important; } </style>
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        @include('admin.includes.heading')
        <div class="content-body">

            <!-- BuilderForm start -->
            <section id="basic-input">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{__('admin.Edit the Company information')}}</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    @if(session()->has('msg'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-{{Session::get('class')}} alert-dismissible  mb-2" role="alert">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-error"></i>
                                                    <span>
                                                        {{ __('admin.'.session()->get('msg')) }}
                                                        {{ Session::forget('msg') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <!-- Validation messages -->
                                    @if(count($errors) >0)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                    <li>{{ $error }} </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="jq-tab-wrapper" id="verticalTab">
                                                    <div class="jq-tab-menu">
                                                        <div class="jq-tab-title active" data-tab="1"><i class="bx bx-info-circle"></i> {{ __('admin.company Information') }}</div>
                                                        <div class="jq-tab-title" data-tab="2"><i class="bx bx-download"></i> {{ __('admin.elevator Pitch') }}</div>
                                                        <div class="jq-tab-title" data-tab="3"><i class="bx bxs-contact"></i> {{ __('admin.social & contact') }}</div>
                                                    </div>
                                                    <div class="jq-tab-content-wrapper">
                                                        <div class="jq-tab-content active" data-tab="1">
                                                            <form method="post" action="{{route('update_my_company')}}" enctype="multipart/form-data">
                                                                {!! csrf_field() !!}
                                                                <div class="row">
                                                                    <div class="col-md-12 col-12">
                                                                        @if (isset($client->company->logo))
                                                                            <img src="/191014/{{$client->company->logo}}" alt="Company logo" height="80px">
                                                                        @endif
                                                                        <div class="form-group mt-75">
                                                                            <label for="basicInput">{{ __('admin.Logo')}}</label>
                                                                            <div class="input-group">
                                                                                <div class="input-group-prepend">
                                                                <span class="input-group-text"
                                                                      id="inputGroupFileAddon01">{{ __('admin.Upload')}}</span>
                                                                                </div>
                                                                                <div class="custom-file">
                                                                                    <input type="file" class="custom-file-input" id="img_url"
                                                                                           name="img_url" accept="image/*" style="direction: ltr;"
                                                                                           aria-describedby="inputGroupFileAddon00">
                                                                                    <label class="custom-file-label"
                                                                                           for="inputGroupFile00">{{ __('admin.Select image file')}}</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="name_en">{{ __('admin.English Name')}} *</label>
                                                                            <input style="direction: ltr" value="{{($client->company)? $client->company->name_en:old('name_en')}}"
                                                                                   name="name_en" type="text" class="form-control"
                                                                                   id="name_en"
                                                                                   placeholder="{{ __('admin.English Name') }} *" required
                                                                                   autocomplete="name_en" />
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="name_ar">{{__('admin.Arabic Name')}} *</label>
                                                                            <input style="direction: rtl;" value="{{($client->company)? $client->company->name_ar:old('name_ar')}}"
                                                                                   name="name_ar" type="text" class="form-control"
                                                                                   id="name_ar"
                                                                                   placeholder="{{ __('admin.Arabic Name') }} *" required
                                                                                   autocomplete="name_ar" />
                                                                        </div>
                                                                    </div>
                                                                    @php
                                                                        if($client->company) {$stateID = optional($client->company)->state_id;} else {$stateID=0;}
                                                                    @endphp
                                                                    <div class="col-md-4 col-12">
                                                                        <div class="form-group">
                                                                            <label for="state_id">{{ __('admin.Country')}}</label>

                                                                            <select class="form-control" required
                                                                                    id="country_id" name='country_id'>
                                                                                <option value="">{{ __('admin.Country')}}</option>
                                                                                @foreach($countries as $country)
                                                                                    <option value="{{$country->id}}" {{($country->id==$client->country_id)? 'selected':''}}>{{$country->{'name_'.$l} }}</option>
                                                                                @endforeach
                                                                            </select>

                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4 col-12">
                                                                        <div class="form-group">
                                                                            <label for="state_id">{{ __('admin.State')}} *</label>
                                                                            <select class="form-control" required
                                                                                    id="state_id" name='state_id'>
                                                                                <option value="">{{ __('admin.State')}}</option>
                                                                                @foreach($states as $state)
                                                                                    <option value="{{$state->id}}"  {{($state->id==$stateID)? 'selected':''}}>{{$state->$name}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4 col-12">
                                                                        <div class="form-group">
                                                                            <label for="city_id">{{ __('admin.City')}} *</label>
                                                                            <select class="form-control" required
                                                                                    id="city_id" name='city_id'>
                                                                                <option value="">{{ __('admin.City')}} </option>
                                                                                @if(isset($client->company) &&  $client->company->city_id != null)
                                                                                    <option value="{{$client->company->city_id}}" selected> {{$client->company->city->{$name} }}</option>
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="sector">{{ __('admin.Sector')}} *</label>
                                                                            <select id="selectsector" class="form-control" required onchange="ssector(this.options[this.selectedIndex].value)" name='sector'>
                                                                                @foreach( $sectors as $sector_group )
                                                                                    <optgroup label="{{ $sector_group->name }}">
                                                                                        @foreach( $sector_group->children as $sector )
                                                                                            <option value="{{ $sector->name_en }}">{{ $sector->name }}</option>
                                                                                        @endforeach
                                                                                    </optgroup>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="address_en">{{ __('admin.English Address')}} *</label>
                                                                            <textarea style="direction: ltr" class="form-control" id="address_en" name='address_en'
                                                                                      required placeholder="{{__('admin.English Address')}} *"
                                                                            >{{($client->company)? $client->company->address_en:old('address_en')}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="address_ar">{{ __('admin.Arabic Address')}} *</label>
                                                                            <textarea style="direction: rtl;" class="form-control" id="address_ar" name='address_ar'
                                                                                      required placeholder="{{__('admin.Arabic Address')}} *"
                                                                            >{{($client->company)? $client->company->address_ar:old('address_ar')}}</textarea>
                                                                            <br>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group">
                                                                            <label for="phone">{{ __('admin.Phone') }}*</label>
                                                                            <input type="text" class="form-control" id="phone"
                                                                                   placeholder="{{ __('admin.Phone') }}*" value="{{($client->company)? $client->company->phone : old('phone')}}" name="phone" required
                                                                                   autocomplete="new-phone">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group">
                                                                            <label for="email">{{ __('admin.E-mail') }} *</label>
                                                                            <input style="direction: ltr" type="email" class="form-control" id="email"
                                                                                   placeholder="{{ __('admin.E-mail') }} *"
                                                                                   value="{{($client->company)? $client->company->email:old('email')}}"
                                                                                   name="email"
                                                                                   required autocomplete="new-email">

                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="est_date">{{ __('admin.est_date') }}</label>
                                                                            <input type="text" class="form-control" id="est_date"
                                                                                   value="{{($client->company)? $client->company->est_date:old('est_date')}}" name="est_date" >
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="team_num">{{ __('admin.team_num') }}</label>
                                                                            <input type="number" class="form-control" id="team_num"
                                                                                   placeholder="{{ __('admin.team_num') }}" value="{{($client->company)? $client->company->team_num:old('team_num')}}" name="team_num"
                                                                                   autocomplete="new-team_num">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="stage">{{ __('admin.stage') }}*</label>
                                                                            <select class="form-control" required
                                                                                    id="stage" name='stage'>
                                                                                <option value="">{{ __('admin.stage')}}</option>
                                                                                <option value="idea" {{ ( !empty( $client->company ) && $client->company->stage == 'idea' ) ? 'selected' : ''}}>{{ __('admin.stage_idea') }}</option>
                                                                                <option value="prototype" {{ ( !empty( $client->company ) && $client->company->stage == 'prototype' ) ? 'selected' : ''}}>{{ __('admin.stage_prototype') }}</option>
                                                                                <option value="ready_product" {{ ( !empty( $client->company ) && $client->company->stage == 'ready_product' ) ? 'selected' : ''}}>{{ __('admin.stage_ready_product') }}</option>
                                                                                <option value="company_in_market" {{ ( !empty( $client->company ) && $client->company->stage == 'company_in_market' ) ? 'selected' : ''}}>{{ __('admin.stage_company_in_market') }}</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="stage">{{ __('admin.Business Type') }}*</label>
                                                                            <select class="form-control" required
                                                                                    id="business_type" name='business_type'>
                                                                                <option value="">{{ __('admin.Business Type')}}</option>
                                                                                <option value="B2C" {{ ( !empty( $client->company ) && $client->company->business_type == 'B2C' ) ? 'selected' : ''}}>B2C</option>
                                                                                <option value="B2B" {{ ( !empty( $client->company ) && $client->company->business_type == 'B2B' ) ? 'selected' : ''}}>B2B</option>
                                                                                <option value="B2B2C" {{ ( !empty( $client->company ) && $client->company->business_type == 'B2B2C' ) ? 'selected' : ''}}>B2B2C</option>
                                                                                <option value="C2C" {{ ( !empty( $client->company ) && $client->company->business_type == 'C2C' ) ? 'selected' : ''}}>C2C</option>
                                                                                <option value="B2G" {{ ( !empty( $client->company ) && $client->company->business_type == 'B2G' ) ? 'selected' : ''}}>B2G</option>
                                                                                <option value="Platform" {{ ( !empty( $client->company ) && $client->company->business_type == 'Platform' ) ? 'selected' : ''}}>Platform</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="raised_fund">{{ __('admin.raised_fund_egp') }}</label>
                                                                            <input type="number" class="form-control" id="raised_fund"
                                                                                   placeholder="{{ __('admin.raised_fund_egp') }}" value="{{($client->company)? $client->company->raised_fund:old('raised_fund')}}" name="raised_fund"
                                                                                   autocomplete="raised_fund">
                                                                        </div>
                                                                    </div>


                                                                    <div class="col-md-6 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="investors">{{ __('admin.investors') }}</label>
                                                                            <input type="text" class="form-control" id="investors"
                                                                                   placeholder="{{ __('admin.investors') }}"
                                                                                   value="{{($client->company)? $client->company->investors:old('investors')}}"
                                                                                   name="investors"
                                                                                   autocomplete="new-investors">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-label-group mt-75">
                                                                            <label for="founder">{{ __('admin.founder') }}</label>
                                                                            <input type="text" class="form-control" id="founder"
                                                                                   placeholder="{{ __('admin.founder') }}"
                                                                                   value="{{($client->company)? $client->company->founder:old('founder')}}"
                                                                                   name="founder"
                                                                                   autocomplete="new-founder">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="idea">{{ __('admin.idea')}}</label>
                                                                            <textarea class="form-control" id="idea" name='idea' placeholder="{{__('admin.idea')}}">{{($client->company)? $client->company->idea:old('idea')}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="problem">{{ __('admin.Problem Solution')}}</label>
                                                                            <textarea class="form-control" id="problem" name='problem' placeholder="{{ __('admin.Problem Solution')}}">{{($client->company)? $client->company->problem:old('problem')}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="problem">{{ __('admin.solution')}}</label>
                                                                            <textarea class="form-control" id="solution" name='solution' placeholder="{{__('admin.solution')}}">{{($client->company)? $client->company->solution:old('solution')}}</textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-12">
                                                                        <div class="form-group">
                                                                            <label for="institutions">Have you been in any Institutions Associated with your Startup (e.g. Accelerator Programs, Investors etc.) *</label>
                                                                            <select id="institutions" name="institutions[]" multiple="multiple" class="form-control">
                                                                                @if(($client->company && !is_null($client->company->institutions)))
                                                                                    @foreach($client->company->institutions as $key)
                                                                                        <option value="{{ $key }}" selected>{{ $key }}</option>
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-12 d-flex justify-content-end">
                                                                        <button type="submit"
                                                                                class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
                                                                    </div>
                                                                </div>
                                                            </form>

                                                        </div>
                                                        <div class="jq-tab-content" data-tab="2">Lorem ipsum dolor sit amet consectetur adipisicing elit. Ratione, magnam mollitia consequuntur modi sit nostrum
                                                            molestiae sunt nihil, quisquam eligendi expedita quae, id natus provident labore odio! Necessitatibus, odit.
                                                            Iure. Delectus magnam eius, facere ratione est dicta, placeat labore possimus, praesentium ex quos similique
                                                            veritatis illum velit minima dolor beatae.</div>
                                                        <div class="jq-tab-content" data-tab="3">
                                                            <form method="post" action="{{route('update_my_company')}}" enctype="multipart/form-data">
                                                                {!! csrf_field() !!}
                                                                <div class="row">


                                                                    <div class="col-md-6 col-12 ">
                                                                        <div class="form-label-group">
                                                                            <label for="facebook">{{ __('admin.Website') }}</label>
                                                                            <input style="direction: ltr" type="text" class="form-control" id="facebook"
                                                                                   placeholder="{{ __('admin.Website') }}"
                                                                                   value="{{($client->company)? $client->company->website:old('website')}}"
                                                                                   name="website"
                                                                                   autocomplete="website">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 ">
                                                                        <div class="form-label-group">
                                                                            <label for="facebook">{{ __('admin.Facebook') }}</label>
                                                                            <input style="direction: ltr" type="text" class="form-control" id="facebook"
                                                                                   placeholder="{{ __('admin.Facebook') }}"
                                                                                   value="{{($client->company)? $client->company->facebook:old('facebook')}}"
                                                                                   name="facebook"
                                                                                   autocomplete="facebook">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 ">
                                                                        <div class="form-label-group">
                                                                            <label for="twitter">{{ __('admin.Twitter') }}</label>
                                                                            <input style="direction: ltr" type="text" class="form-control" id="twitter"
                                                                                   placeholder="{{ __('admin.Twitter') }}"
                                                                                   value="{{($client->company)? $client->company->twitter:old('twitter')}}"
                                                                                   name="twitter"
                                                                                   autocomplete="twitter">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 ">
                                                                        <div class="form-label-group">
                                                                            <label for="linkedin">{{ __('admin.Linkedin') }}</label>
                                                                            <input style="direction: ltr" type="text" class="form-control" id="linkedin"
                                                                                   placeholder="{{ __('admin.Linkedin') }}"
                                                                                   value="{{($client->company)? $client->company->linkedin:old('linkedin')}}"
                                                                                   name="linkedin"
                                                                                   autocomplete="new-linkedin">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 ">
                                                                        <div class="form-label-group">
                                                                            <label for="youtube">{{ __('admin.Youtube') }}</label>
                                                                            <input style="direction: ltr" type="text" class="form-control" id="youtube"
                                                                                   placeholder="{{ __('admin.Youtube') }}"
                                                                                   value="{{($client->company)? $client->company->youtube:old('youtube')}}"
                                                                                   name="youtube"
                                                                                   autocomplete="new-youtube">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6 col-12 ">
                                                                        <div class="form-group">
                                                                            <label for="google_map">{{ __('admin.Google Map') }}</label>
                                                                            <textarea style="direction: ltr" class="form-control height-100" id="google_map" name='google_map'
                                                                                      placeholder="{{__('admin.Google Map full URL')}}"
                                                                            >{{($client->company)? $client->company->google_map:old('google_map')}}</textarea>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12 d-flex justify-content-end">
                                                                        <button type="submit"
                                                                                class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
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
                        </div>
                    </div>
                </div>
            </section>
            <!-- BuilderForm end -->


        </div>
    </div>
</div>
<!-- END: Content-->
@stop
@section('pagescripts')
<script src="/js/jquery.datetimepicker.full.min.js"></script>
<script src="/tabs/dist/jquery.tabs.min.js"></script>
<script src="/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script>
    $(document).ready(function() {
        $('#institutions').select2({
            tags: true,
            placeholder: 'Type and press enter to add',
            allowClear: true
        });
    });
    $(function () {
        $('#verticalTab').jqTabs();
    });
</script>
<script>
$(document).ready(function() {
  	$('#est_date').datetimepicker({timepicker:false,format:'Y-m-d'});
    getStates($('#country_id').val());
    $('#country_id').change(function () {
        getStates($(this).val());
    });
    $('#state_id').change(function () {
        getCities($(this).val());
    });
});
function getStates(country_id){
    var $state = $('#state_id');
    var currentState = '{{ $client->company?optional($client->company)->state_id:0 }}';
    var selected = '';
    $.ajax({
        url: "{{ route('get_states_by_country') }}",
        data: {
            country_id: country_id
        },
        success: function (data) {
            $state.html('<option value="" >{{ __('admin.State')}}</option>');
            $.each(data, function (id, value) {
                // selected = '';
                if (currentState === id) {
                    selected = 'selected';
                    getCities(id)
                } else {
                    selected = '';
                }
                $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
                $('#city_id').html('<option value="" >{{ __('admin.City')}}</option>');
            });
            $('#state').show(150);
        }
    });
}
function getCities(state_id){
    var $city = $('#city_id');
    var currentCity = '{{ $client->company? optional($client->company)->city_id:0 }}';
    var selected = '';
    $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
            state_id: state_id
        },
        success: function(data) {
            $city.html('<option value="" >{{ __('admin.City')}}</option>');
            $.each(data, function(id, value) {
                if(currentCity == id){
                    selected = 'selected';
                }else{
                    selected = '';
                }
                $city.append('<option value="'+id+'" '+selected+' >'+value+'</option>');
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
            let other = prompt('{{__('Enter your company sector name')}}');
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
