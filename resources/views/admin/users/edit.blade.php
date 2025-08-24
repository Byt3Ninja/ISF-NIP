@extends('layouts.admin')
@section('title', __('admin.Users') )
@section('index_url', route('users.index') )
@section('subTitle', __('admin.Edit user'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@php
  $navPills = [
    'info' => [
      'title' => $l == 'en' ? 'Info' : 'المعلومات',
      'active' => true,
      'id' => 'navs-pills-info',
      'icon' => 'ti ti-user',
    ],
    'security' => [
      'title' => $l == 'en' ? 'Security' : 'الأمان',
      'active' => false,
      'id' => 'nav-pills-security',
      'icon' => 'ti ti-lock',
    ],
  ];
@endphp

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="nav-align-top mb-6">
      <div class="row">
        <div class="col-8 col-lg-4">
          <ul class="nav nav-pills mb-4 nav-fill" role="tablist">
            @foreach($navPills as $key => $navPill)
              <li class="nav-item mb-1 mb-sm-0 me-4">
                <button type="button" class="nav-link {{ $navPill['active'] ? 'active' : '' }}" role="tab"
                  data-bs-toggle="tab" data-bs-target="#{{ $navPill['id'] }}" aria-controls="{{ $navPill['id'] }}"
                  aria-selected="{{ $navPill['active'] }}">
                  <span>
                    <i class="tf-icons {{ $navPill['icon'] }} ti-sm me-1_5 align-text-bottom"></i> 
                    {{ $navPill['title'] }}
                  </span>
                </button>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-10">
          <div class="tab-content bg-white rounded shadow">
            <div class="tab-pane fade show active" id="navs-pills-info" role="tabpanel">
              <div class="d-flex align-items-center justify-content-between gap-4 border-bottom mb-6">
                <h4>{{__('admin.Edit user')}}</h4>
                  <button data-bs-toggle="modal" data-bs-target="#MessageModal"
                    class="btn btn-label-primary btn-sm border">
                    <i class="ti ti-message font-small-3"></i>
                  </button>
              </div>
              @if(session()->has('msg'))
                <x-alert 
                  type="danger"
                  message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
                />
              @endif
              <!-- Validation messages -->
              @if(count($errors) >0)
                @foreach($errors->all() as $error)
                  <x-alert type="danger" message="{{ $error }}" />
                @endforeach
              @endif
              <form method="post" action="{{route('users.update', ['user' => $user->id])}}"
                enctype="multipart/form-data">
                {!! csrf_field() !!}
                @method('patch')
                <div class="row">
                  <div class="col-md-12">
                    <div for="upload" class="d-flex align-items-center gap-4 mb-6">
                      <label 
                        for="upload"
                        id="upload-label"
                        class="w-px-100 h-px-100 position-relative border-2 border-light border-dashed rounded-circle bg-label-light cursor-pointer"
                        style="background-image: url('/191014/{{ $user->img_url }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
                      >
                        <input hidden type="file" name="img_url" class="account-file-input" id="upload"
                          accept="image/*" />
                        <div class="position-absolute top-50 start-50 translate-middle z-1">
                          <i class="ti ti-camera-plus ti-28px text-light">
                          </i>
                        </div>
                      </label>
                      <span class="fs-6 fw-semibold">
                        {{ $l == 'en' ? 'Update User Logo' : 'تحديث صورة المستخدم' }}
                      </span>
                    </div>
                    <div>
                      <div class="row">
                        <div class="col-md-6">
                          <label for="first_name" class="form-label">{{ __(key: 'admin.First Name')}}</label>
                          <input value="{{ $user->first_name_en}}" name="first_name" type="text" class="form-control mb-3"
                            id="first_name" style="direction: ltr;" placeholder="{{ __('admin.First Name') }}"
                            required autocomplete="first_name" autofocus />
                        </div>
                        <div class="col-md-6">
                          <label for="last_name" class="form-label">{{ __('admin.Last Name')}}</label>
                          <input value="{{ $user->last_name_en }}" name="last_name" type="text" class="form-control"
                            id="last_name" style="direction: ltr;" placeholder="{{ __('admin.Last Name') }}" required
                            autocomplete="last_name" />
                        </div>
                      </div>
                    </div>
         
                    <div>
                      <label class="form-label">{{ __('admin.Gender')}}</label>
                      <div class="d-flex">
                        <div class="form-check me-3">
                          <input class="form-check-input mb-3" type="radio" name="gender" value="Male" id="g1" {{
                            $user->gender=='Male' ? 'checked' :''}} >
                          <label class="form-check-label" for="g1">{{__('Male')}}</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input mb-3" type="radio" name="gender" value="Female" id="g2" {{
                            $user->gender=='Female' ? 'checked' :''}}>
                          <label class="form-check-label" for="g2">{{__('Female')}}</label>
                        </div>
                      </div>
                    </div>
                    <div>
                      <div class="row">
                        <div class="col-md-6">
                          <label for="email" class="form-label">{{ __('admin.Email') }}</label>
                          <input value="{{ $user->email }}" name="email" type="email" class="form-control mb-3" id="email"
                            style="direction: ltr;" placeholder="{{ __('admin.E-Mail') }}" required autocomplete="email">
                        </div>
                        <div class="col-md-6">
                          <label for="phone" class="form-label">{{ __('admin.Phone') }}</label>
                          <input type="text" class="form-control mb-3" id="phone" style="direction: ltr;"
                            placeholder="{{ __('admin.Phone') }}" value="{{ $user->phone }}" name="phone" required
                            autocomplete="new-phone">
                        </div>
                      </div>
                    </div>
                    <div>
                      <div class="row">
                        {{-- <div class="col-md-6">
                          <label for="title_ar" class="form-label">{{ __('admin.JobTitleAr') }}</label>
                          <input type="text" class="form-control mb-3" id="title_ar" 
                            placeholder="{{ __('admin.JobTitleAr') }}" value="{{ $user->title_ar }}" name="title_ar"
                            autocomplete="title_ar">
                        </div> --}}
                        <div class="col-md-6">
                          <label for="title_en" class="form-label">{{ __('admin.JobTitleEn') }}</label>
                          <input type="text" class="form-control mb-3" id="title_en" style=""
                            placeholder="{{ __('admin.JobTitleEn') }}" value="{{ $user->title_en }}" name="title_en"
                            autocomplete="title_en">
                        </div>
                      </div>
                    </div>
                    <div>
                      <div class="row">
                        <div class="col-md-4">
                          <label for="country_id" class="form-label">{{ __('admin.Country')}}</label>
                          <select class="form-select select2 mb-3" required id="country_id" name='country_id'>
                            <option value="">{{ __('admin.Country')}}</option>
                            @foreach($countries as $country)
                            <option value="{{$country->id}}" {{($country->id==$user->country_id)? 'selected':''}}>
                              {{ $country->emoji . ' ' . $country->{'name_'.$l} }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label for="state_id" class="form-label">{{ __('admin.State')}}</label>
                          <select class="form-select select2 mb-3" required id="state_id" name='state_id'>
                            <option value="">{{ __('admin.State')}}</option>
                            @php $name = 'name_'.$l; @endphp
                            @if($user->state_id)
                            <option value="{{$user->state_id}}" selected>{{$user->state->{'name_'.$l} }}</option>
                            @endif
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label for="city_id" class="form-label">{{ __('admin.City')}}</label>
                          <select class="form-select select2 mb-3" required id="city_id" name='city_id'>
                            <option value="">{{ __('admin.City')}}</option>
                            @php $name = 'name_'.$l; @endphp
                            @if($user->city_id)
                            <option value="{{$user->city_id}}" selected>{{$user->city->{'name_'.$l} }}</option>
                            @endif
                          </select>
                        </div>
                      </div>
                    </div>
                    <div>
                      <label for="role" class="form-label">{{ __('admin.Role')}}</label>
                      @if(isset($user->roles->first()->name))
                      @php $currentRole = $user->roles->first()->name; @endphp
                      @else
                      @php $currentRole = ''; @endphp
                      @endif
                      <select class="form-select select2 mb-3" required id="role" name='role'>
                        <option value="">{{ __('admin.Roles')}}</option>
                        @foreach($roles as $role)
                        <option value="{{$role->id}}" {{ ($role->name == $currentRole)? "selected":"" }} >{{
                          __('admin.'.$role->name) }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div>
                          <label for="houre_rate" class="form-label">{{ __('admin.HouresRate') }}</label>
                          <input value="{{ $user->houre_rate }}" name="houre_rate" type="number" class="form-control mb-3"
                            id="houre_rate" placeholder="{{ __('admin.HouresRate') }}" autocomplete="HouresRate">
                        </div>
                      </div>
                    </div>
                    <div>
                      <div class="d-flex align-items-center justify-content-between gap-4 mb-2">
                        <label class="form-label">{{ __('admin.HaveCompany')}}</label>
                        @if ($user->company)
                        <a href="/dashboard/companyProfile/{{$user->company->id}}"
                          class="btn btn-sm btn-label-primary">
                          <i class="ti ti-building me-2 text-sm"></i>
                          {{ __('admin.CompanyProfile') }}
                        </a>
                        @endif
                      </div>
                      <div class="d-flex">
                        <div class="form-check me-3">
                          <input class="form-check-input mb-3" type="radio" name="HaveCompany" value="1" id="h1"
                            {{$user->HaveCompany?'checked':''}}>
                          <label class="form-check-label" for="h1">{{__('Yes')}}</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input mb-3" type="radio" name="HaveCompany" value="" id="h2"
                            {{$user->HaveCompany?'':'checked'}}>
                          <label class="form-check-label" for="h2">{{__('No')}}</label>
                        </div>
                      </div>
                    </div>
                    <div>
                      <label class="form-label">{{ __('admin.Verified')}}</label>
                      <div class="d-flex">
                        <div class="form-check me-3">
                          <input class="form-check-input mb-3" type="radio" name="email_verified_at"
                            value="{{$user->email_verified_at?$user->email_verified_at:now()}}" id="v1" {{
                            $user->email_verified_at?'checked':''}}>
                          <label class="form-check-label" for="v1">{{__('Yes')}}</label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input mb-3" type="radio" name="email_verified_at" value="" id="v2" {{
                            $user->email_verified_at?'':'checked'}}>
                          <label class="form-check-label" for="v2">{{__('No')}}</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                      <i class="ti ti-send-2 me-2"></i>
                      {{__('admin.Update')}}
                    </button>
                  </div>
                </div>
              </form>
            </div>
            <div class="tab-pane fade" id="nav-pills-security" role="tabpanel">
              <div class="d-flex align-items-center justify-content-between gap-4 border-bottom mb-6">
                <h4>{{__('admin.Change The account`s password')}}</h4>
              </div>
              <form 
                method="post" 
                action="{{route('user.profile_update_password')}}" 
                enctype="multipart/form-data"
              >
                {!! csrf_field() !!}
                <fieldset>
                  <div class="form-group mb-3">
                    <label class="form-label" for="password">{{ __('admin.Password') }}</label>
                    <input type="password" class="form-control" id="password"
                      placeholder="{{ __('admin.Password') }}" name="password" required
                      autocomplete="new-password">
                    <input type="hidden" name='id' value='{{$user->id}}'>
                  </div>
                  <div class="form-group mb-3">
                    <label class="form-label" for="password-confirm">{{ __('admin.Confirm') }}</label>
                    <input type="password" class="form-control" id="password-confirm"
                      placeholder="{{ __('admin.Password') }}" name="password_confirmation" required
                      autocomplete="new-password">
                  </div>
                </fieldset>
                <div class="row mt-3">
                  <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary mr-1 mb-1">
                      <i class="ti ti-send-2 me-2"></i>
                      {{__('admin.Update')}}
                    </button>
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
<!-- END: Content-->
<div class="modal fade text-left" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true"
>
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel33">{{ __('messages.SendMessageTo') }} {{$user->{'first_name_'.$l} }}
          {{ $user->{'last_name_'.$l} }}.</h4>
      </div>
      <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" name="sender" value="{{Auth::user()->id}}">
        <input type="hidden" name="receiver" value="{{$user->id}}">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label class="form-label">{{ __('messages.Subject') }}:</label>
            <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">{{ __('messages.Message') }}: </label>
            <textarea class="form-control" id="message" name='message' rows="8"
              placeholder="{{__('messages.Write your message')}}" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{__('messages.Close')}}
          </button>
          <button type="submit" class="btn btn-primary ml-1">
            <i class="ti ti-send me-2"></i>
            {{__('messages.Send')}}
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

<script>
  console.log(@json($user));
  const uploadInput = document.getElementById('upload');
  const uploadIcon = document.getElementById('upload-icon');

  uploadInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    
    if (file) {
      const reader = new FileReader();

      reader.onload = (e) => {
        const uploadLabel = document.getElementById('upload-label');
        uploadLabel.style.backgroundImage = `url(${e.target.result})`;
        uploadLabel.style.backgroundSize = 'cover';
        uploadLabel.style.backgroundPosition = 'center';
        uploadLabel.style.backgroundRepeat = 'no-repeat';
        uploadLabel.classList.remove('border-2', 'border-light', 'border-dashed');
        uploadIcon.classList.replace('text-secondary', 'text-dark');
      }
      reader.readAsDataURL(file);
    }
  });
</script>

<script>

  $(document).ready(function() {
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
      var currentState = '{{ $user->state_id }}';
      var selected = '';

      $.ajax({
        url: "{{ route('utils.locations_list_states_by_country') }}",
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
    var currentCity = '{{ $user->city_id }}';
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
@stop