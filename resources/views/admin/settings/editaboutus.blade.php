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
                <span>{{ __('admin.' . session()->get('message')) }}</span>
              </div>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <span>{{ __('admin.' . session()->get('message2')) }}</span>
              </div>
            </div>
          @endif
          <div class="row g-3">
            <div class="col-12">
              <div class="card px-4">
                <div class="card-header border-bottom mb-4 mx-4" style="height: 70px;">
                  <h4 class="card-title">
                    {{ $l == 'en' ? 'About Settings' : 'إعدادات من نحن' }}
                  </h4>
                </div>
                <div class="card-body">
                  <form class="form" id="basicForm" action="{{ URL::to('dashboard/updateaboutus') }}" method="POST"
                    enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="form-body">
                      <div class="row g-3">
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_title" class="form-label">{{ __('settings.ARABICTITLE') }}</label>
                            <input type="text" style="direction: rtl;" class="form-control"
                              value="{{ $allabout->ar_title }}" name="ar_title" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="title" class="form-label">{{ __('settings.ENGLISHTITLE') }}</label>
                            <input type="text" style="direction: ltr;" class="form-control"
                              value="{{ $allabout->title }}" name="title" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_dsc" class="form-label">{{ __('settings.ARABICDESCRIPTION') }}</label>
                            <textarea style="direction: rtl;" class="form-control" id="ar_dsc" rows="6" name="ar_dsc" required>{{ $allabout->ar_dsc }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="dsc" class="form-label">{{ __('settings.ENGLISHDESCRIPTION') }}</label>
                            <textarea style="direction: ltr;" class="form-control" id="dsc" rows="6" name="dsc" required>{{ $allabout->dsc }}</textarea>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_set1" class="form-label">{{ __('settings.Mission') }}
                              {{ __('settings.InArabic') }}</label>
                            <input style="direction: rtl;" type="text" class="form-control"
                              value="{{ $allabout->ar_set1 }}" name="ar_set1" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="set1" class="form-label">{{ __('settings.Mission') }}
                              {{ __('settings.InEnglish') }}</label>
                            <input style="direction: ltr;" type="text" class="form-control"
                              value="{{ $allabout->set1 }}" name="set1" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="ar_set2" class="form-label">{{ __('settings.Vision') }}
                              {{ __('settings.InArabic') }}</label>
                            <input style="direction: rtl;" type="text" class="form-control"
                              value="{{ $allabout->ar_set2 }}" name="ar_set2" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="mb-3">
                            <label for="set2" class="form-label">{{ __('settings.Vision') }}
                              {{ __('settings.InEnglish') }}</label>
                            <input style="direction: ltr;" type="text" class="form-control"
                              value="{{ $allabout->set2 }}" name="set2" required>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-check form-switch mb-3">
                            <input name="url" type="checkbox" class="form-check-input"
                              {{ $allabout->url ? 'checked' : '' }} id="customSwitch1">
                            <label class="form-check-label"
                              for="customSwitch1">{{ __('settings.EnablePress') }}</label>
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
