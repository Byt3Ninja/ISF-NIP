@extends('layouts.admin')

@section('title', __('admin.Questions Categories'))
@section('index_url', route('question-categories.index'))
@section('subTitle', __('admin.Add new category'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <style>
    .custom-container-ar {
      padding-left: 8rem;
      padding-right: 2rem;
    }

    .custom-container-en {
      padding-left: 2rem;
      padding-right: 8rem;
    }

    @media screen and (max-width: 768px) {
      .custom-container-ar,
      .custom-container-en {
        padding: 0;
      }
    }
  </style>
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-body">
        <!-- BuilderForm start -->
        <section id="basic-input">
          <div class="row">
            <div class="col-md-12">
              <div class="card {{ $l == 'ar' ? 'custom-container-ar' : 'custom-container-en' }}">
                <div class="card-header border-bottom mb-4" style="height: 70px;">
                  <h4 class="card-title">{{ __('admin.Add new category') }}</h4>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    @if (session()->has('msg'))
                      <div class="row">
                        <div class="col-md-12">
                          <div class="alert alert-danger mb-2" role="alert">
                            {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>
                        </div>
                      </div>
                    @endif
                    <!-- Validation messages -->
                    @if (count($errors) > 0)
                      <div class="row">
                        <div class="col-md-12">
                          <div class="alert alert-danger mb-2" role="alert">
                            <ul>
                              @foreach ($errors->all() as $error)
                                <li>{{ $error }} </li>
                              @endforeach
                            </ul>
                          </div>
                        </div>
                      </div>
                    @endif
                    <form method="post" action="{{ route('question-categories.store') }}">
                      {!! csrf_field() !!}
                      <fieldset class="form-group mb-3">
                        <label class="form-label mb-2 fs-6" for="organization_id">{{ __('admin.Organization') }}</label>
                        <select class="form-select select2" name="organization_id" id="organization_id" required>
                          @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
                          @endforeach
                        </select>
                      </fieldset>
                      <fieldset class="form-group">
                        <label class="form-label mb-2 fs-6" for="title">{{ __('admin.Title') }}</label>
                        <input value="" name="title" type="text" class="form-control" id="title"
                          placeholder="{{ __('admin.Title') }}" required />
                      </fieldset>
                      <div class="row mt-4">
                        <div class="col-12 d-flex justify-content-end">
                          <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
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

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
@stop
