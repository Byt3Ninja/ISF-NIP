@extends('layouts.admin')
@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <i class="ti ti-check"></i>
                <span>{{ __('admin.' . session()->get('message')) }}</span>
              </div>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <i class="ti ti-error"></i>
                <span>{{ __('admin.' . session()->get('message2')) }}</span>
              </div>
            </div>
          @endif
          <div class="row g-3">
            <div class="col-12">
              <div class="card px-4">
                <div class="card-header border-bottom mb-4 mx-4" style="height: 70px;">
                  <h4 class="card-title">
                    {{ __('settings.ContactSettings') }}
                  </h4>
                </div>
                <div class="card-body">
                  <form class="form" id="basicForm" action="{{ URL::to('dashboard/updatecontactus') }}" method="POST"
                    enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="form-body">
                      <div class="row g-3">
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="title" class="form-label">{{ __('settings.MainEmail') }}</label>
                            <input type="text" class="form-control" style="direction:ltr;"
                              value="{{ $allcontact->title }}" name="title" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_title" class="form-label">{{ __('settings.MainPhone') }}</label>
                            <input type="text" class="form-control" style="direction:ltr;"
                              value="{{ $allcontact->ar_title }}" name="ar_title" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_dsc" class="form-label">{{ __('settings.Address') }}
                              {{ __('settings.InArabic') }}</label>
                            <textarea class="form-control" style="direction:rtl;" id="ar_dsc" rows="4" name="ar_dsc" required>{{ $allcontact->ar_dsc }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="dsc" class="form-label">{{ __('settings.Address') }}
                              {{ __('settings.InEnglish') }}</label>
                            <textarea class="form-control" style="direction:ltr;" id="dsc" rows="4" name="dsc" required>{{ $allcontact->dsc }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="set1" class="form-label">{{ __('settings.CallusNumbers') }}</label>
                            <textarea class="form-control" style="direction:ltr;" id="set1" rows="4" name="set1" required>{{ $allcontact->set1 }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_set2" class="form-label">{{ __('settings.OpeningTime') }}
                              {{ __('settings.InArabic') }}</label>
                            <textarea class="form-control" style="direction:rtl;" id="ar_set2" rows="4" name="ar_set2" required>{{ $allcontact->ar_set2 }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="set2" class="form-label">{{ __('settings.OpeningTime') }}
                              {{ __('settings.InEnglish') }}</label>
                            <textarea class="form-control" style="direction:ltr;" id="set2" rows="4" name="set2" required>{{ $allcontact->set2 }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_set1" class="form-label">{{ __('settings.GoogleMapRef') }}</label>
                            <textarea class="form-control" style="direction:ltr;" id="ar_set1" rows="4" name="ar_set1" required>{{ $allcontact->ar_set1 }}</textarea>
                          </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary">{{ __('settings.Submit') }}</button>
                        </div>
                      </div>
                    </div>
                  </form>
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
