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

                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="mentorSurveys" id="rad5" class="type">
                                    <label for="rad5">{{ __('admin.Surveys_Mentor') }}</label>
                                  </div>
                                </fieldset>
                              </li>

                              <li class="d-inline-block mx-2 mb-1">
                                <fieldset>
                                  <div class="radio">
                                    <input type="radio" name="type" value="startupSurveys" id="rad6" class="type">
                                    <label for="rad6">{{ __('admin.Surveys_Startup') }}</label>
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



var responses = @json($available_surveys)

		$(document).on('click', '.open-responses', function() {
			var id = $(this).data('id');
			var responses = @json($available_surveys);
			var response = responses.filter(function(oneResponse) {
				return oneResponse.id == id;
			});
			var responses = response[0].responses;
			var html = '';
			responses.forEach(function(oneResponse) {
				html += '<tr data-id="' + id + '" data-qid="' + oneResponse.id + '" class="cursor-pointer open-question-details">';
				html += '<td>' + oneResponse.question + '</td>';
				html += '<td class="text-center">' + ( oneResponse.type == 'radio' ? "{{ __('admin.surveyTypeRadioButtons') }}" : ( oneResponse.type == 'answers' ? "{{ __('admin.surveyQuestionAndAnswer') }}" : "{{ __('admin.surveyTypeStarRating') }}" ) ) + '</td>';
				html += '</tr>';
			});
			$("#modelPreviewArea").html('<div class="text-center"> {{ __("admin.previewAreaPlaceholder") }} </div>');
			$('#question-picker-tbody').html(html);
			$('#questionsModal').modal('show');
		});

		$(document).on('click', '.open-question-details', function() {
			var id = $(this).data('id');
			var question_id = $(this).data('qid');
			var responses = @json($available_surveys);
			var response = responses.filter(function(oneResponse) {
				return oneResponse.id == id;
			});
			var responses = response[0].responses;
			var question = responses.filter(function(oneResponse) {
				return oneResponse.id == question_id;
			});
			var question = question[0];
			var html = '';
			if( question.type == 'radio' )
			{
				question.answers.forEach( function(item, key) {
					var count = key + 1;
					var checked = parseInt( question.answer ) == key ? 'btn-outline-success' : 'btn-outline-secondary';
					html += '<div class="custom-py-1"> <button class="w-100 text-left btn ' + checked + '">' + count + '. ' + item.text + '</button> </div>';
				});
			}
			else if( question.type == 'answers' )
			{
				question.answers.forEach( function(item, key) {
					var count = key + 1;
					var checked = parseInt( question.answer ) == key ? 'btn-outline-success' : 'btn-outline-secondary';
					html += '<div class="custom-py-1"> '+ question.answer +' </div>';
				});
			}
			else if( question.type == 'stars' )
			{
				var answer = parseInt( question.answer );
				html += '<ul class="custom-star-rating text-center p-0 my-1">';
					for( var i = 0; i < question.answers.length; i++ )
					{
						if( i < answer )
						{
							html += '<li><i class="ti ti-star text-warning"></i> </li>';
						}
						else
						{
							html += '<li><i class="ti ti-star text-warning"></i> </li>';
						}
					}
				html += '</ul>';
			}
			$("#modelPreviewArea").html(html);
		});



  </script>

  {{-- <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script> --}}
@stop
