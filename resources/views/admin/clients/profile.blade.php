@extends('layouts.admin')
@section('title', __('admin.Profile') )
@section('subTitle', __('admin.Edit your profile information'))
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            @include('admin.includes.heading')
            <div class="content-body">
                <form method="post" action="{{route('update_my_profile')}}"
                      enctype="multipart/form-data">
                    {{--                ================> Section Main Profile <==================--}}
                    <section id="basic-input">
                        <div class="row">
                            <div class="col-md-{{$client->img_url?9:12}}">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">{{__('admin.Edit your profile information')}}</h4>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body">
                                            @if(session()->has('msg'))
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-{{Session::get('class')}} alert-dismissible  mb-2"
                                                             role="alert">
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

                                            {!! csrf_field() !!}
                                            <div class="row mt-75">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group mt-75">
                                                        <input value="{{ $client->first_name_en}}"
                                                               name="first_name_en" type="text" class="form-control"
                                                               id="first_name_en" style="direction: ltr;"
                                                               placeholder="{{ __('admin.First Name') }}" required
                                                               autocomplete="first_name_en" autofocus/>
                                                        <label for="first_name_en">{{__('admin.English UserName')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group mt-75">
                                                        <input value="{{ $client->last_name_en }}" name="last_name_en"
                                                               type="text" class="form-control" id="last_name_en"
                                                               style="direction: ltr;"
                                                               placeholder="{{ __('admin.Last Name') }}" required
                                                               autocomplete="last_name_en"/>
                                                        <label for="last_name_en">{{__('admin.Last Name')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-12">
                                                    <div class="form-label-group">
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="gender" value="Male"
                                                                               id="g1" {{ $client->gender=='Male' ? 'checked=""' :''}} >
                                                                        <label for="g1">{{__('Male')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="gender" value="Female"
                                                                               id="g2" {{ $client->gender=='Female' ? 'checked=""' :''}}>
                                                                        <label for="g2">{{__('Female')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group">
                                                        <input value="{{ $client->first_name_ar }}"
                                                               name="first_name_ar" type="text" class="form-control"
                                                               id="first_name_ar" style="direction: rtl;"
                                                               placeholder="{{ __('admin.First Name') }}" required
                                                               autocomplete="first_name_ar" autofocus/>
                                                        <label for="first_name_ar">{{__('admin.Arabic UserName')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group">
                                                        <input value="{{ $client->last_name_ar }}" name="last_name_ar"
                                                               type="text" class="form-control" id="last_name_ar"
                                                               style="direction: rtl;"
                                                               placeholder="{{ __('admin.Last Name') }}" required
                                                               autocomplete="last_name_ar"/>
                                                        <label for="last_name_ar">{{__('admin.Last Name')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group mt-75">
                                                        <input value="{{ $client->title_ar }}" name="title_ar"
                                                               type="title_ar"
                                                               class="form-control" id="title_ar"
                                                               style="direction: rtl;"
                                                               placeholder="{{ __('admin.JobTitleAr') }}" required
                                                               autocomplete="title_ar">
                                                        <label for="title_ar">{{__('admin.JobTitleAr')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group mt-75">
                                                        <input value="{{ $client->title_en }}" name="title_en"
                                                               type="title_en"
                                                               class="form-control" id="title_en"
                                                               style="direction: ltr;"
                                                               placeholder="{{ __('admin.JobTitleEn') }}" required
                                                               autocomplete="title_en">
                                                        <label for="title_en">{{__('admin.JobTitleEn')}}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group mt-75">
                                                        <input value="{{ $client->email }}" name="email" type="email"
                                                               class="form-control" id="email" style="direction: ltr;"
                                                               placeholder="{{ __('admin.E-mail') }}" required
                                                               autocomplete="email">
                                                        <label for="email">{{ __('admin.E-mail') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-label-group mt-75">
                                                        <input type="text" class="form-control" id="phone"
                                                               style="direction: ltr;"
                                                               placeholder="{{ __('admin.Phone') }}"
                                                               value="{{ $client->phone }}" name="phone"
                                                               required autocomplete="new-phone">
                                                        <label for="phone">{{ __('admin.Phone') }}</label>
                                                    </div>
                                                </div>
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
                                                        <label for="state_id">{{ __('admin.State')}}</label>

                                                        <select class="form-control" required
                                                                id="state_id" name='state_id'>
                                                            <option value="">{{ __('admin.State')}}</option>
                                                            @php $name = 'name_'.$l; @endphp
                                                            @if($client->state_id)
                                                                <option value="{{$client->state_id}}"
                                                                        selected>{{$client->state->{'name_'.$l} }}</option>
                                                            @endif
                                                        </select>

                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-12">
                                                    <div class="form-group">
                                                        <label for="city_id">{{ __('admin.City')}}</label>
                                                        <select class="form-control" required
                                                                id="city_id" name='city_id'>
                                                            <option value="">{{ __('admin.City')}}</option>
                                                            @php $name = 'name_'.$l; @endphp
                                                            @if($client->city_id)
                                                                <option value="{{$client->city_id}}"
                                                                        selected>{{$client->city->{'name_'.$l} }}</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group">
                                                        <label for="basicInput">{{ __('admin.Image')}}</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                            <span class="input-group-text"
                                                                  id="inputGroupFileAddon01">{{ __('admin.Upload')}}</span>
                                                            </div>
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input"
                                                                       id="img_url"
                                                                       name="img_url" accept="image/*"
                                                                       style="direction: ltr;"
                                                                       aria-describedby="inputGroupFileAddon00">
                                                                <label class="custom-file-label"
                                                                       for="inputGroupFile00">{{ __('admin.SelectFile')}}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 col-12">
                                                    <div class="form-group mt-75">
                                                        <label for="basicInput">{{ __('auth.DoYouHaveCompany')}}</label>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="HaveCompany" value="1"
                                                                               id="h1" {{$client->HaveCompany?'checked=""':''}}>
                                                                        <label for="h1">{{__('Yes')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="HaveCompany" value=""
                                                                               id="h2" {{$client->HaveCompany?'':'checked=""'}}>
                                                                        <label for="h2">{{__('No')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-12 d-flex justify-content-end">
                                                    <button type="submit"
                                                            class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            @if($client->img_url)
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <img style="width: 100%;" class="img-responsive pad"
                                                     src="/191014/{{$client->img_url ??'../images/placeholder.jpg' }}"
                                                     alt="{{ $client->first_name_en}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                    {{--                ================> Section Mentor Bio <====================--}}
                    @role('Mentor')
                    <input type="hidden" name="mentor_section" value="1">
                    <a id="bio">
                        <section id="basic-input">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">{{__('admin.addbio')}}</h4>
                                        </div>
                                        <div class="card-content">
                                            <div class="card-body">

                                                <div class="row">

                                                    <div class="col-md-12">
                                                        <div class="col-md-12 col-12">
                                                            <label for="linkedin">{{ __('admin.Linkedin') }}</label>
                                                            <div class="form-group">
                                                                <input value="{{ $client->bio?$client->bio->linkedin:'' }}"
                                                                       name="linkedin" type="text"
                                                                       class="form-control" id="linkedin"
                                                                       style="direction: ltr;"
                                                                       placeholder="{{ __('admin.Linkedin')}} - https://linkedin.com/in/yourname"
                                                                       autocomplete="linkedin">

                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 col-12">
                                                            <label>{{__('admin.arbio')}}: </label>
                                                            <div class="form-group">
                                                                <textarea class="form-control" id="bio_ar" name='bio_ar'
                                                                          rows="3" dir="rtl"
                                                                          placeholder="{{__('admin.writebioar')}}">{{$client->bio->bio_ar??''}}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 col-12">
                                                            <label>{{__('admin.enbio')}}: </label>
                                                            <div class="form-group">
                                                                <textarea class="form-control" id="bio_en" name='bio_en'
                                                                          rows="3" dir="ltr"
                                                                          placeholder="{{__('admin.writebioen')}}">{{$client->bio->bio_en??''}}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-12 col-12">
                                                    <div class="form-label-group">
                                                        <h6 for="selectExpertises">{{__('admin.SelectExpertises')}}</h6>
                                                        <p>{{__('admin.SelectMultiExpertises')}}.</p>
                                                        <div class="form-group">
                                                            <select name="expertises[]" class="select2 form-control"
                                                                    multiple="multiple" id="selectExpertises">
                                                                @foreach( $expertises as $expertise_group )
                                                                    <optgroup label="{{ $expertise_group->title }}">
                                                                        @foreach( $expertise_group->children as $expertise )
                                                                            <option value="{{ $expertise->id }}"  @if(in_array($expertise->id,$client->mentorExpertises()->pluck('expertises.id')->toArray())) selected @endif>{{ $expertise->title }}</option>
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 col-12">
                                                    <div class="form-group mt-75">
                                                        <label for="basicInput">{{ __('admin.Entrepreneurial Experience')}}</label>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio"
                                                                               name="entrepreneurial_experience"
                                                                               value="1"
                                                                               class="entrepreneurial_experience"
                                                                               id="e1" {{$client->bio->entrepreneurial_experience?'checked=""':''}}>
                                                                        <label for="e1">{{__('Yes')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio"
                                                                               name="entrepreneurial_experience"
                                                                               value="0"
                                                                               class="entrepreneurial_experience"
                                                                               id="e2" {{$client->bio->entrepreneurial_experience?'':'checked=""'}}>
                                                                        <label for="e2">{{__('No')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <br>
                                                <div class="col-md-12 col-12" style="display: none;"
                                                     id="entrepreneurial_experience_brief">
                                                    <div class="form-label-group">
                                                        <p>{{__('admin.Entrepreneurial Experience desc')}}.</p>
                                                        <textarea class="form-control" id="basicTextarea" rows="8"
                                                                  name="entrepreneurial_experience_brief"
                                                                  required>{{$client->bio?$client->bio->entrepreneurial_experience_brief:old('entrepreneurial_experience_brief')}} </textarea>
                                                        <label for="tcontent">{{__('admin.Entrepreneurial Experience Brief')}}</label>

                                                    </div>
                                                </div>

                                                <div class="col-md-12 col-12">
                                                    <div class="form-group mt-75">
                                                        <label for="basicInput">{{ __('admin.Profile Privacy')}}</label>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio"
                                                                               name="profile_privacy"
                                                                               value="1"
                                                                               class="profile_privacy"
                                                                               id="p1" {{$client->bio->profile_privacy?'checked=""':''}}>
                                                                        <label for="p1">{{__('Yes')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio"
                                                                               name="profile_privacy"
                                                                               value="0"
                                                                               class="profile_privacy"
                                                                               id="e2" {{$client->bio->profile_privacy?'':'checked=""'}}>
                                                                        <label for="p2">{{__('No')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 col-12">
                                                    <label for="approach">{{ __('admin.mentorship approach') }} :</label>
                                                    <div class="form-group" id="approach">
                                                        <div class="checkbox mr-5">
                                                            <input type='checkbox' id="checkbox1" class="checkbox-input"
                                                                   name='mentorship_approach[]' {{$client->bio && in_array('E-mail',$client->bio->mentorship_approaches)?'checked=""':''}} value="E-mail">
                                                            <label for="checkbox1">
                                                                {{__('admin.E-mail')}}
                                                            </label>
                                                        </div>
                                                        <div class="checkbox mr-5">
                                                            <input type='checkbox' id="checkbox2" class="checkbox-input"
                                                                   name='mentorship_approach[]' {{$client->bio && in_array('phone calls',$client->bio->mentorship_approaches)?'checked=""':''}} value="phone calls">
                                                            <label for="checkbox2">
                                                                {{__('admin.phone calls')}}
                                                            </label>
                                                        </div>
                                                        <div class="checkbox mr-5">
                                                            <input type='checkbox' id="checkbox3" class="checkbox-input"
                                                                   name='mentorship_approach[]' {{$client->bio && in_array('video calls',$client->bio->mentorship_approaches)?'checked=""':''}} value="video calls">
                                                            <label for="checkbox3">
                                                                {{__('admin.video calls')}}
                                                            </label>
                                                        </div>
                                                        <div class="checkbox mr-5">
                                                            <input type='checkbox' id="checkbox4" class="checkbox-input"
                                                                   name='mentorship_approach[]' {{$client->bio && in_array('in-person meetings',$client->bio->mentorship_approaches)?'checked=""':''}} value="in-person meetings">
                                                            <label for="checkbox4">
                                                                {{__('admin.in-person meetings')}}
                                                            </label>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="row">
                                                    <div class="col-12 d-flex justify-content-end">
                                                        <button type="submit"
                                                                class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                        </section>
                    </a>
                    @endrole

                </form>

                {{--         =====================> Section Password <=======================--}}
                <section id="basic-input">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{__('admin.Change your account password')}}</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form method="post" action="{{route('update_my_profile_password')}}"
                                              enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <div class="row">
                                                <div class="col-md-12">

                                                    <fieldset class="form-group">
                                                        <label class="text-bold-600"
                                                               for="password">{{ __('admin.Password') }}</label>
                                                        <input type="password" class="form-control" id="password"
                                                               placeholder="{{ __('admin.Password') }}" name="password"
                                                               required autocomplete="new-password">
                                                    </fieldset>

                                                    <fieldset class="form-group">
                                                        <label class="text-bold-600"
                                                               for="password-confirm">{{ __('admin.Confirm') }}</label>
                                                        <input type="password" class="form-control"
                                                               id="password-confirm"
                                                               placeholder="{{ __('admin.Password') }}"
                                                               name="password_confirmation"
                                                               required autocomplete="new-password">
                                                    </fieldset>
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
                </section>
            </div>
        </div>
        <!-- END: Content-->
        @stop
        @section('pagescripts')
            <script>
                $(document).ready(function () {
                    getStates($('#country_id').val());
                    $('#country_id').change(function () {
                        getStates($(this).val());
                    });
                    $('#state_id').change(function () {
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

                function getCities(state_id) {
                    var $city = $('#city_id');
                    var currentCity = '{{ $client->city_id }}';
                    var selected = '';
                    $.ajax({
                        url: "{{ route('cities.index') }}",
                        data: {
                            state_id: state_id
                        },
                        success: function (data) {
                            $city.html('<option value="" >{{ __('admin.City')}}</option>');
                            $.each(data, function (id, value) {
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
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js"
                    referrerpolicy="origin"></script>
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
                    images_upload_url: "{{ route('training.upload', ['_token' => csrf_token() ])}}",
                    file_picker_types: 'image',
                    convert_urls: true,
                    file_picker_callback: function (cb, value, meta) {
                        var input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        input.onchange = function () {
                            var file = this.files[0];
                            var reader = new FileReader();
                            reader.readAsDataURL(file);
                            reader.onload = function () {
                                var id = 'blobid' + (new Date()).getTime();
                                var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                                var base64 = reader.result.split(',')[1];
                                var blobInfo = blobCache.create(id, file, base64);
                                blobCache.add(blobInfo);
                                cb(blobInfo.blobUri(), {title: file.name});
                            };
                        };
                        input.click();
                    }
                });
                @if($l=='ar')
                    tinyMCE.settings['directionality'] = 'rtl';
                @endif
            </script>
            <script>
                $(document).ready(function () {
                    @if($client->bio)
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

                    $('.entrepreneurial_experience').change(function () {
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

@stop
