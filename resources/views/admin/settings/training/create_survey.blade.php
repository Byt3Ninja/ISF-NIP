@extends('layouts.admin')
@section('title', __('admin.surveys') )
@section('index_url', route('surveys.index') )
@section('subTitle', __('admin.AddNewSurvey'))
@section('style')
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
	<style type="text/css">
		.btn i.fa { top: 0px !important; }
		.btn { padding: 0px 10px !important; }
	</style>
@stop
@section('content')
<div class="">
	<div class="">
		<div class="content-overlay"></div>
		<div class="content-header row">
			@if( session()->has('success') )
				<div class="col-md-12">
					<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
						{{ __('admin.'. session()->get('success')) }} 
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
			@endif
			@if( session()->has('error') )
				<div class="col-md-12">
					<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
						{{ __('admin.'.session()->get('error')) }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
			@endif
		</div>
		<div class="content-body">
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">{{__('admin.AddNewSurvey')}}</h4>
							</div>
							<div class="card-content">
								<div class="card-body">
									@if( session()->has('msg') )
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger mb-2" role="alert">
												<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
												<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
											</div>
										</div>
									</div>
									@endif
									@if( count( $errors ) > 0 )
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger mb-2" role="alert">
												<ul>
													@foreach($errors->all() as $error)
													<li>{{ $error }} </li>
													@endforeach
												</ul>
											</div>
										</div>
									</div>
									@endif
									<form method="post" action="{{ route('trainings.surveys_store', ['id' => $cycle->id]) }}">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12">
												<fieldset class="form-group">
													<label for="survey_id">{{ __('admin.Survey') }}</label>
													<select class="form-control form-select" name="survey_id" id="survey_id" required>
														@foreach( $surveys as $survey )
															<option value="{{ $survey->id }}">{{ $survey->title }}</option>
														@endforeach
													</select>
												</fieldset>
											</div>
										</div>
										<div class="row my-6">
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div> {{ __('admin.SurveyTriggers') }} </div>
												<fieldset class="form-group my-3">
													<input type="checkbox" class="checkbox-input" id="trigger_by_session" name="trigger_by_session">
													<label for="trigger_by_session"> {{ __('admin.surveyTriggerAfterSession') }} </label>
												</fieldset>
												<fieldset class="form-group my-3">
													<input type="checkbox" class="checkbox-input" id="trigger_by_due" name="trigger_by_due">
													<label for="trigger_by_due"> {{ __('admin.surveyTriggerAfterCycle') }} </label>
												</fieldset>
												<fieldset class="form-group my-3">
													<input type="checkbox" class="checkbox-input" id="trigger_by_quiz" name="trigger_by_quiz">
													<label for="trigger_by_quiz"> {{ __('admin.surveyTriggerAfterQuiz') }} </label>
												</fieldset>
											</div>
										</div>
										<div class="row">
											<div class="col-12 d-flex justify-content-end">
												<input type="hidden" name="organization_id" value="{{ $cycle->organization_id }}">
												<button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
												<a href="{{ route('trainings.surveys_list', ['id' => $cycle->id]) }}" class="btn btn-secondary mb-1 mx-2">{{__('admin.Cancel')}}</a>
											</div>
										</div>
									</form>
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
	<script src="/js/jquery.datetimepicker.full.min.js"></script>
	<script type="text/javascript">
		//
	</script>
@stop
