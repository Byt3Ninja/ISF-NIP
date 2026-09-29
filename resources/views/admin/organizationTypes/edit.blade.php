@extends('layouts.admin')
@section('title', __('admin.Organization Types'))
@section('index_url', route('organization-types.index'))
@section('subTitle', __('admin.Edit organization Type'))

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
										{{ __('admin.Edit organization Type') }}
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
                      action="{{ route('organization-types.update', ['organization_type' => $orgType->id]) }}"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <input name="_method" type="hidden" value="PATCH" />
                      <div class="row">
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="title_en">{{ __('admin.English Organization Type Name') }}</label>
                            <input value="{{ $orgType->title_en }}" name="title_en" type="text" class="form-control" id="title_en" placeholder="{{ __('admin.English Organization Type Name') }}" required />
                          </fieldset>
                        </div>
                        <div class="col-md-12 mb-4">
                          <fieldset class="form-group">
                            <label class="form-label fw-semibold fs-6 mb-1" for="title_ar">{{ __('admin.Arabic Organization Type Name') }}</label>
                            <input value="{{ $orgType->title_ar }}" name="title_ar" type="text" class="form-control" id="title_ar" placeholder="{{ __('admin.Arabic Organization Type Name') }}" required />
                          </fieldset>
                        </div>
                      </div>

                      @if ($_user->ableToAny(['organization-types.edit', 'organization-types.update'], $permissions))
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
    
  </script>
@stop
