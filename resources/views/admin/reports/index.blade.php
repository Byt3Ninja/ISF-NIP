@extends('layouts.admin')

@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/apex-charts/apex-charts.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header row">
      </div>
      <div class="content-body">
        <section id="basic-input">
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title d-flex align-items-center">
                    <i class="ti ti-report-analytics ti-24px"></i>
                    <span class="mx-2">{{ __('admin.Reports') }}</span>
                  </h4>
                  <hr>
                </div>
                <div class="card-content">
                  <div class="card-body">
                    @if (session()->has('msg'))
                      <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
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

                    <div class="col-md-12 col-12">
                      <div class="form-group">
                        <div class="card-content">
                          <div class="px-2">
                            <h5>{{ __('admin.Select Report Kind') }}</h5>
                            <p></p>
                            <ul class="list-unstyled mb-0">
                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="mentorship" id="rad0" class="type">
                                    <label for="rad0">{{ __('admin.Mentorship') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="training" id="rad1" class="type">
                                    <label for="rad1">{{ __('admin.Training sessions') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="judge" id="rad2" class="type">
                                    <label for="rad2">{{ __('admin.judge Sessions') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="cycles" id="rad3" class="type">
                                    <label for="rad3">{{ __('admin.Cycles') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="companies" id="rad4" class="type">
                                    <label for="rad4">{{ __('admin.Companies') }}</label>
                                  </div>
                                </fieldset>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 col-12 " id="cyclesSelectSection" style="display: none;">
                      <div class="form-group mt-4 px-2">
                        <h5 id="selectTitle">{{ __('admin.SelectCycle') }}</h5>
                        <p id="selectSubtitle">{{ __('admin.SelectOrSearchCycle') }}.</p>
                        <div class="form-group">
                          <select class="form-control select2" id="cyclesSelectOptions">
                            @foreach ($cycles as $cycle)
                              <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div id="companiesSelectSection" style="display: none;">
                      <div class="form-group mt-4 px-2">
                        <h5>{{ __('admin.SelectCompany') }}</h5>
                        <p>{{ __('admin.SelectOrSearchCompany') }}.</p>
                        <div class="form-group">
                          <select class="form-control select2" id="companiesSelectOptions">
                            @foreach ($companies as $company)
                              <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row px-8 pb-8">
                    <div class="col-12 d-flex">
                      <button type="button" id="showReport"
                        class="btn btn-primary mr-1 mb-1">{{ __('admin.ViewReport') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="cart my-10">
          <div id="reportContent" class="card-body">

          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
@stop
@section('scripts')
  {{-- <script src="/app-assets/js/core/dashboards-analytics.js"></script>
    <script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script> --}}
  <script>
    $(document).ready(function() {
      $('#companiesSelectSection').hide();
      $('#cyclesSelectSection').hide();
      $('.type').change(function() {
        if ($(this).val() === 'cycles') {
          $('#cyclesSelectSection').show();
          $('#companiesSelectSection').hide();
        } else if ($(this).val() === 'companies') {
          $('#cyclesSelectSection').hide();
          $('#companiesSelectSection').show();
        } else {
          $('#companiesSelectSection').hide();
          $('#cyclesSelectSection').hide();
        }
      });
      $('#showReport').click(function() {
        var value = $('.type:checked').val();
        if (value === 'cycles' || value === 'companies') {
          var selectValue = $('#' + value + 'SelectOptions').val();
          if (selectValue === '' || selectValue === undefined) {
            alert('برجاء اختيار القيمة اولا');
          } else {
            getAjaxReport(value, selectValue);
          }

        } else {

          getAjaxReport(value)
        }
      });
    });

    function getAjaxReport(value, id = 0) {
      $.ajax({
        url: "{{ route('reports.view.ajax') }}/" + value,
        data: {
          id: id
        },
        success: function(data) {
          if (data['value'] === 1) {
            $('#reportContent').html(data['view']);
          }
        }
      });
    }
  </script>

  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
@stop
