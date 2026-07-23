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
@php
	$responses = $survey->responses;
	$eligibleUsers = $responses->count();
	$engagedUsers = $responses->whereNotNull('responses')->count();
	$notEngagedUsers = $responses->whereNull('responses')->count();
	$engagementRate = $eligibleUsers > 0 ? ( number_format( round( ($engagedUsers / $eligibleUsers * 100), 2 ), 2 ) ) : 0.00;

	$responses = collect([]);
	try {
		$responses = $survey->responses->whereNotNull('responses')->flatMap(function ($response) {
			$user_id = $response->user_id;
			$responses = $response->responses;
			if( !is_null ( $responses ) )
			{
				$responses = $responses->map(function ($item) use ($user_id) {
					return [
						'user_id' => $user_id,
						'question_id' => $item['id'],
						'type' => $item['type'],
						'answer' => intval( $item['answer'] ),
					];
				});
			}
			return $responses ?? [];
		});
	} catch (Exception $e) { }
@endphp
@section('content')
<div class="">
	<div class="">
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
							<div class="row">
								<div class="col-12">
									<div class="container-fluid pt-5">
										<table class="table table-hover">
											<tbody>
												<tr>
													<th class="w-25"> {{__('admin.surveyTitle') }} </th>
													<td>{!! $survey->title !!}</td>
												</tr>
												<tr>
													<th class="w-25"> {{__('admin.surveyDescription') }} </th>
													<td>{!! $survey->description !!}</td>
												</tr>
												<tr>
													<th class="w-25"> {{__('admin.engagementRate') }} </th>
													<td>{!! $engagementRate !!}</td>
												</tr>
												<tr>
													<th class="w-25"> {{__('admin.total') }} </th>
													<td>{{ $eligibleUsers }}</td>
												</tr>
												<tr>
													<th class="w-25"> {{__('admin.engaged') }} </th>
													<td>{{ $engagedUsers }}</td>
												</tr>
												<tr>
													<th class="w-25"> {{__('admin.notEngaged') }} </th>
													<td>{{ $notEngagedUsers }}</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<hr class="hr mx-5 mt-8 mb-3">
							<div class="card-header pt-0">
								<div class="row">
									<div class="col-md-12 col-lg-12 col-sm-12"> <h4 class="card-title">{{__('admin.surveyResults')}}</h4> </div>
								</div>
							</div>
							<div class="card-content">
								<div class="card-body">
									<div class="container-fluid px-0">
										<div class="table-responsive">
											<table class="table table-bordered table-hover">
												<thead>
													<tr>
														<th class="w-25 text-center">Question</th>
														<th class="text-center">Answers</th>
														<th class="text-center">Ratings</th>
													</tr>
												</thead>
												<tbody>
													@foreach( $survey->questions as $question )
														<tr>
															<td rowspan="{{ $question->answers->count() + 1 }}">{{ $question->question }}</td>
														</tr>
														@foreach( $question->answers as $index => $answer )
															@php
																$target_answer = $question->survey_type == 'stars' ? ( $index + 1 ) : $index;
																$star = $index + 1;
															@endphp
															<tr>
																<td>
																	@if( $question->survey_type == 'stars' )
																		@for( $i = 0; $i < $star; $i++ )
																			<i class="fas fa-fw fa-star text-warning"></i>
																		@endfor
																	@else
																		{{ $answer['text'] }}
																	@endif
																</td>
																<td>
																	{{ $responses->where('type', $question->survey_type)->where('question_id', $question->id)->where('answer', $target_answer)->count() }}
																</td>
															</tr>
														@endforeach
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
@stop
@section('pagescripts')
	<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
	<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js" integrity="sha256-Fb0zP4jE3JHqu+IBB9YktLcSjI1Zc6J2b6gTjB0LpoM=" crossorigin="anonymous"></script>
	<script type="text/javascript">
	</script>
@stop
