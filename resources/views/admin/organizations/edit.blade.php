@php $user = $organization->user; @endphp
@extends('layouts.admin')
@section('title', __('admin.Organizations'))
@section('index_url', route('organizations.index'))
@section('subTitle', __('admin.Edit organization'))

@section('style')
  <style>
    #basic-input {
      width: 70%;
      margin: 2rem auto 4rem auto;
    }

    @media (max-width: 768px) {
      #basic-input {
        width: 100%;
      }
    }
  </style>

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-body">
        <!-- BuilderForm start -->
        <section id="basic-input">
          <div class="row">
            <div class="col-md-12 mb-4">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title fw-bold pb-3 border-bottom text-capitalize">
										{{ __('admin.Edit organization') }}
                  </h5>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    @if (session()->has('msg'))
                      <div class="row">
                        <div class="col-md-12 mb-4">
                          <div class="alert alert-warning alert-danger mb-2" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            <div class="d-flex align-items-center">
                              <i class="ti ti-error"></i>
                              <span>
                                {{ __('admin.' . session()->get('msg')) }}
                                {{ Session::forget('msg') }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                    <!-- Validation messages -->
                    @if (count($errors) > 0)
                      <div class="row">
                        <div class="col-md-12 mb-4">
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
                    <form method="post"
                      action="{{ route('organizations.update', ['organization' => $organization->id]) }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <input name="_method" type="hidden" value="PATCH" />
                      <div class="row">
												<div class="col-12 mb-6">
													<label for="file1" class="d-flex justify-content-center align-items-center position-relative rounded"
														style="height: 140px; cursor: pointer; width: 200px; margin: 0 auto;">
														<img
															src="{{ $organization->imageUrl }}"
															onerror="this.src='/images/logo-placeholder.png'"
															alt="Organization Logo"
															class="rounded img-fluid border shadow-sm"
															style="height: 140px; width: 200px; object-fit: cover;"
														/>
														<input type="file" class="form-control d-none" id="file1" name="img_url">
														<i class="ti ti-camera-plus position-absolute fs-3 text-dark end-0 bottom-0 m-4 z-1"></i>
													</label>
												</div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="name_en">{{ __('admin.English Organization Name') }}</label>
                            <input value="{{ $organization->name_en }}" name="name_en" type="text" class="form-control"
                              id="name_en" placeholder="{{ __('admin.English Organization Name') }}" required />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="name_ar">{{ __('admin.Arabic Organization Name') }}</label>
                            <input value="{{ $organization->name_ar }}" name="name_ar" type="text" class="form-control"
                              id="name_ar" placeholder="{{ __('admin.Arabic Organization Name') }}" required />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <div class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="status">{{ __('admin.Organization Status') }}</label>
														<br />
                            <select class="form-select" name="status" id="status">
                              <option value="1"
                                {{ intval($organization->status) == 1 || $organization->status === true ? 'selected' : '' }}>
                                {{ __('admin.Active') }}</option>
                              <option value="0"
                                {{ intval($organization->status) == 0 || $organization->status === false ? 'selected' : '' }}>
                                {{ __('admin.Not Active') }}</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="color">{{ __('admin.color') }}</label>
                            <input value="{{ $organization->color }}" name="color" type="text" class="form-control"
                              id="color" placeholder="{{ __('admin.color') }}" required />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="first_name_en">{{ __('admin.Organization Manager English First Name') }}</label>
                            <input name="first_name_en" type="text" class="form-control" id="first_name_en"
                              value="{{ $user->first_name_en ?? '' }}" placeholder="{{ __('admin.First Name') }}" required
                              autocomplete="first_name_en" autofocus />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="last_name_en">{{ __('admin.Organization Manager English Last Name') }}</label>
                            <input value="{{ $user->last_name_en ?? '' }}" name="last_name_en" type="text"
                              class="form-control" id="last_name_en" placeholder="{{ __('admin.Last Name') }}"
                              required autocomplete="last_name_en" />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="first_name_ar">{{ __('admin.Organization Manager Arabic First Name') }}</label>
                            <input value="{{ $user->first_name_ar ?? '' }}" name="first_name_ar" type="text"
                              class="form-control" id="first_name_ar" placeholder="{{ __('admin.First Name') }}"
                              required autocomplete="first_name_ar" autofocus />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="last_name_ar">{{ __('admin.Organization Manager Arabic Last Name') }}</label>
                            <input value="{{ $user->last_name_ar ?? '' }}" name="last_name_ar" type="text"
                              class="form-control" id="last_name_ar" placeholder="{{ __('admin.Last Name') }}"
                              required autocomplete="last_name_ar" />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset>
                            <ul class="list-unstyled mb-0">
                              <li class="d-inline-block mr-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="gender" value="Male" id="g1"
                                      {{ optional($user)->gender == 'Male' ? 'checked=""' : '' }}>
                                    <label class="form-label fw-semibold fs-6 mb-1" for="g1">{{ __('Male') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                              <li class="d-inline-block mr-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="gender" value="Female" id="g2"
                                      {{ optional($user)->gender == 'Female' ? 'checked=""' : '' }}>
                                    <label class="form-label fw-semibold fs-6 mb-1" for="g2">{{ __('Female') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                            </ul>
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" class="text-bold-600" for="exampleInputEmail1">{{ __('admin.Email') }}</label>
                            <input value="{{ $user->email ?? '' }}" name="email" type="email" class="form-control"
                              id="email" placeholder="{{ __('admin.Email') }}" required autocomplete="email">
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" class="text-bold-600" for="phone">{{ __('admin.Phone') }}</label>
                            <input type="text" class="form-control" id="phone"
                              placeholder="{{ __('admin.Phone') }}" value="{{ $user->phone ?? '' }}" name="phone"
                              required autocomplete="new-phone">
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" class="text-bold-600" for="password">{{ __('admin.Password') }}</label>
                            <input type="password" class="form-control" id="password"
                              placeholder="{{ __('admin.Password') }}" name="password" autocomplete="new-password">
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" class="text-bold-600" for="password-confirm">{{ __('admin.Confirm') }}</label>
                            <input type="password" class="form-control" id="password-confirm"
                              placeholder="{{ __('admin.Password') }}" name="password_confirmation"
                              autocomplete="new-password">
                          </fieldset>
                        </div>
                      </div>

                      @if ($_user->ableToAny(['organizations.edit', 'organizations.update'], $permissions))
                        <div class="row">
                          <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('admin.Save') }}</button>
                          </div>
                        </div>
                      @endif
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
<script>
	const fileInput = document.getElementById('file1');
	const previewImage = document.querySelector('label[for="file1"] img');

	fileInput.addEventListener('change', (event) => {
		const file = event.target.files[0];
		if (file) {
			const reader = new FileReader();
			reader.onload = (event) => {
				previewImage.src = event.target.result;
			};
			reader.onerror = () => {
				alert('Error loading file');
			};
			reader.readAsDataURL(file);
		}
	});

	document.getElementById('company-logo').addEventListener('click', () => {
		document.getElementById('file1').click();
	});
</script>
@stop
