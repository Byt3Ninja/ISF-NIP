@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('sub_index_url', route('surveys.edit', ['survey' => $survey->id]) )
@section('subTitle', __('admin.manageQuestions'))
@section('style')
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
	<style type="text/css">
		.custom-px-1 { padding-inline: 0.25rem; }
		.custom-py-1 { padding-block: 0.25rem; }
		.custom-px-2 { padding-inline: 0.5rem; }
		.custom-py-2 { padding-block: 0.5rem; }
		.btn i.fa { top: 0px !important; }
		.btn { padding: 5px 10px !important; }
		#previewArea, #modelPreviewArea { border: 1px solid #a8a3a3; border-radius: 5px; }
		.custom-star-rating, .custom-star-rating-done { list-style: none; display: block ruby; }
		.custom-star-rating i.fas { cursor: pointer; }
		.custom-star-rating li { margin-inline: 3px; }
		.text-gold { color: #fd7e14; font-size: 1.2rem !important; }
	</style>
@stop
@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>		
		<div class="content-header row">
			@if( session()->has('success') )
				<div class="col-md-12">
					<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex align-items-center"> <i class="ti ti-thumb-up me-3 ti-md"></i> <span> {{ __('admin.'. session()->get('success')) }} </span> </div>
					</div>
				</div>
			@endif
			@if( session()->has('error') )
				<div class="col-md-12">
					<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>{{ __('admin.'.session()->get('error')) }} </span> </div>
					</div>
				</div>
			@endif
		</div>
		<div class="content-body">
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header row">
								<div class="col-md-12 col-lg-12 col-sm-12"> <h4 class="card-title">{{__('admin.surveyResults')}}</h4> </div>
							</div>
							<div class="card-content">
								<div class="card-body">
									<div class="container-fluid px-0">
										<div class="table-responsive">
											<table class="table table-bordered table-hover">
												<thead class="bg-label-light">
													<tr>
														<th class="text-center" style="width: 5%;">#</th>
														<th>{{__('admin.user')}}</th>
														<th>{{__('admin.Cycle informations')}}</th>
														<th>{{__('admin.surveyAbout')}}</th>
														<th class="text-center" style="width: 10%;">{{__('admin.Status')}}</th>
													</tr>
												</thead>
												<tbody>
													@foreach( $responses as $index => $response )
														<tr>
															<td class="text-center">{{ $index + 1 }}</td>
															<td><a target="_blank" href="/dashboard/users/{{ $response->user->id }}/edit"> {{ $response->user->name }}</a></td>
															<td>
																<a target="_blank" href="/dashboard/programlist/show/{{ $response->cycle->id }}/"> {{ $response->cycle->title }} </a>
															</td>
															<td>
																@if( $response->triggered_on == 'mentorship' )
																<i class="ti ti-bulb mx-1"></i> 
																@elseif( $response->triggered_on == 'training' )
																<i class="ti ti-presentation mx-1"></i> 
																@endif
																{{ __('dashboard.surveyAbout') . ' ' . __('dashboard.surveyTriggerBy' . $target . ucFirst($response->triggered_by) ) }} 
																@php
																	$middleware = collect([]);
																	if( $response->triggered_on == 'mentorship' )
																	{
																	if( $response->triggered_by == 'session' ) { $middleware = $response->mentorship_session; }
																	if( $response->triggered_by == 'due' ) { $middleware = $response->mentorship_step; }
																	}
																	elseif( $response->triggered_on == 'training' )
																	{
																	if( $response->triggered_by == 'session' ) { $middleware = $response->training_session; }
																	if( $response->triggered_by == 'due' ) { $middleware = $response->training_step; }
																	if( $response->triggered_by == 'quiz' ) { $middleware = $response->training_quiz; }
																	}
																@endphp
																@if( $response->triggered_by == 'due' ) {{ __('dashboard.surveyStepName' . ucFirst($response->triggered_on) ) }} @endif
																@if( $response->triggered_on == 'mentorship' )
																	@if( $response->triggered_by == 'session' )
																	{{ __('dashboard.with') }} 
																	@php $mentor = $middleware?->mentor; @endphp
																	{{ $mentor->name }}
																	@elseif( $response->triggered_by == 'due' )
																	- {{ $middleware?->title }}
																	@else
																	-
																	@endif
																@elseif( $response->triggered_on == 'training' )
																	@if( $response->triggered_by == 'session' )
																	{{ '(' . __('cycles.' . $middleware?->type ) . ')' }} {{ $middleware?->title }}
																	@elseif( $response->triggered_by == 'due' )
																	- {{ $middleware?->title }}
																	@elseif( $response->triggered_by == 'quiz' )
																	- {{ $middleware?->title }}
																	@else
																	-
																	@endif
																@endif
															</td>
															<td class="text-center">
																@if( is_null( $response->responses ) || count( $response->responses ) == 0 )
																	<button class="btn btn-sm btn-label-danger"> {{ __('admin.notAnswered') }} </button>
																@else
																	<button data-id="{{ $response->id }}" class="btn btn-sm btn-label-success open-responses"> {{ __('admin.answered') }} </button>
																@endif
															</td>
														</tr>
													@endforeach
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>
<div class="modal fade" id="questionsModal" tabindex="-1" role="dialog" aria-labelledby="modalScrollableTitle" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalScrollableTitle"> {{ __('admin.surveyAnswersDetails') }} </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-8 col-lg-8 col-sm-8">
							<div class="table-responsive">
								<table class="table table-bordered table-hover">
									<thead>
										<tr>
											<th> {{__('admin.question')}} </th>
											<th class="w-25 text-center px-0"> {{__('admin.question_Type')}} </th>
										</tr>
									</thead>
									<tbody id="question-picker-tbody"></tbody>
								</table>
							</div>
						</div>
						<div class="col-md-4 col-lg-4 col-sm-4">
							<div class="py-1 px-2" id="modelPreviewArea">
								<div class="text-center"> {{ __('admin.previewAreaPlaceholder') }} </div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-md btn-dark" data-bs-dismiss="modal">
					<span class="d-none d-sm-block">{{__('messages.Close')}}</span>
				</button>
			</div>
		</div>
	</div>
</div>
@stop
@section('pagescripts')
	<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
	<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js" integrity="sha256-Fb0zP4jE3JHqu+IBB9YktLcSjI1Zc6J2b6gTjB0LpoM=" crossorigin="anonymous"></script>
	<script type="text/javascript">
		var responses = @json($responses)

		$(document).on('click', '.open-responses', function() {
			var id = $(this).data('id');
			
			var responses = @json($responses);
			
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
			var responses = @json($responses);
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
							html += '<li><i class="fas fa-star text-gold"></i> </li>';
						}
						else
						{
							html += '<li><i class="far fa-star text-gold"></i> </li>';
						}
					}
				html += '</ul>';
			}
			$("#modelPreviewArea").html(html);
		});
	</script>
@stop
