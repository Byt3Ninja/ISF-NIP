@extends('layouts.admin')

@section('title', __('admin.Users'))
@section('index_url', route('users.index'))
@section('subTitle', __('admin.Add new user'))

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      <!-- BuilderForm start -->
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title pb-4 border-bottom">{{ __('admin.Add new user') }}</h4>
              </div>
              <div class="card-content">
                <div class="card-body">
                  @if (session()->has('msg'))
                  <div class="row">
                    <div class="col-md-12">
                      <div class="alert alert-warning alert-danger mb-2" role="alert">
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                  <form method="post" action="{{ route('users.store') }}" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="row">
                      <div class="col-md-12">
                        <fieldset>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="first_name_en">{{ __('admin.First Name') }}</label>
                                <input name="first_name_en" type="text" class="form-control" id="first_name_en"
                                  value="{{ old('first_name_en') }}" placeholder="{{ __('admin.First Name') }}" required
                                  autocomplete="first_name_en" autofocus />
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="last_name_en">{{ __('admin.Last Name') }}</label>
                                <input value="{{ old('last_name_en') }}" name="last_name_en" type="text"
                                  class="form-control" id="last_name_en" placeholder="{{ __('admin.Last Name') }}"
                                  required autocomplete="last_name_en" />
                              </div>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset>
                          <div class="row">
                            <div class="col-md-6 col-12 mb-4">
                              <label class="fs-6 fw-semibold form-label mb-1" for="gender">{{ __('admin.Gender') }}</label>
                              <select class="form-select" id="gender" name="gender" required>
                                <option value="">{{ __('admin.Select Gender') }}</option>
                                <option value="Male" {{ old('gender')=='Male' ? 'selected' : '' }}>{{ __('Male') }}
                                </option>
                                <option value="Female" {{ old('gender')=='Female' ? 'selected' : '' }}>{{ __('Female')
                                  }}</option>
                              </select>
                            </div>
                            <div class="col-md-6 col-12 mb-4">
                              <label class="fs-6 fw-semibold form-label mb-1" for="role">{{ __('admin.Role') }}</label>
                              <select class="form-select" required id="role" name='role'>
                                <option disabled value="" selected>{{ __('admin.Select_Role') }}</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role')==$role->name ? 'selected' : '' }}>
                                  {{ __('admin.' . $role->name) }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset>
                          <div class="row">
                            <div class="col-md-6 col-12">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="email">{{ __('admin.Email') }}</label>
                                <input value="{{ old('email') }}" name="email" type="email" class="form-control"
                                  id="email" placeholder="{{ __('admin.Email') }}" required autocomplete="email">
                              </div>
                            </div>
                            <div class="col-md-6 col-12">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="phone">{{ __('admin.Phone') }}</label>
                                <input type="text" class="form-control" id="phone" placeholder="{{ __('admin.Phone') }}"
                                  value="{{ old('phone') }}" name="phone" required autocomplete="new-phone">
                              </div>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset class="form-group mb-3">
                          <div class="row">
                            <div class="col-12 col-md-6 mb-4 form-password-toggle">
                              <label class="fs-6 fw-semibold form-label mb-1" for="password">{{ __('admin.Password') }}</label>
                              <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="password"
                                  placeholder="{{ __('admin.Password') }}" name="password" required
                                  autocomplete="new-password">
                                <span class="input-group-text cursor-pointer border border-light"><i
                                    class="ti ti-eye-off"></i></span>
                              </div>
                            </div>
                            <div class="col-12 col-md-6 mb-4 form-password-toggle">
                              <label class="fs-6 fw-semibold form-label mb-1" for="password-confirm">{{ __('admin.Confirm Password') }}</label>
                              <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="password-confirm"
                                  placeholder="{{ __('admin.Confirm Password') }}" name="password_confirmation" required
                                  autocomplete="new-password">
                                <span class="input-group-text cursor-pointer border border-light"><i
                                    class="ti ti-eye-off"></i></span>
                              </div>

                            </div>
                          </div>
                        </fieldset>
                      </div>
                    </div>
                    <div class="row mt-6">
                      <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-1 mb-1">{{ __('admin.Save') }}</button>
                      </div>
                    </div>
                  </form>
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
<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('description_ar', {
      language: 'ar'
    });
    CKEDITOR.replace('description_en', {
      language: 'en'
    });
</script>
@stop