@extends('layouts.admin')
@section('title', __('admin.quizzes') )
@section('index_url', route('quizzes.index') )
@section('subTitle', __('admin.Add new Quiz'))
@section('style')
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
	<style type="text/css">
		.btn i.fa { top: 0px !important; }
		.btn { padding: 0px 10px !important; }
	</style>
@stop
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
		@include('admin.includes.heading')
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">{{__('admin.Add new Quiz')}}</h4>
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
									<!-- Validation messages -->
									@if(count($errors) >0)
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
									<form method="post" action="{{ route('quizzes.store') }}">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="organization_id">{{ __('admin.Organization')}}</label>
													<select class="form-control form-select" name="organization_id" id="organization_id" required>
														@foreach( $organizations as $organization )
															<option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
														@endforeach
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="title">{{ __('admin.Title')}}</label>
													<input value="" name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="start">{{ __('admin.startsFrom')}}</label>
													<input value="" name="start" type="text" class="form-control" id="start" placeholder="{{ __('admin.startsFrom') }}" required autocomplete="off" />
												</fieldset>
												<fieldset class="form-group">
													<label for="time_limit">{{ __('admin.TimeLimit')}}</label>
													<input value="" name="time_limit" type="number" class="form-control" id="time_limit" placeholder="{{ __('admin.TimeLimitPlaceholder') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="shuffle">{{ __('admin.shuffleQuestions')}}</label>
													<select class="form-control form-select" name="shuffle" id="shuffle">
														<option value="shuffle_true">{{ __('admin.shuffle_true') }}</option>
														<option value="shuffle_false">{{ __('admin.shuffle_false') }}</option>
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="attempts">{{ __('admin.quizAttempts')}}</label>
													<input value="" name="attempts" type="number" min="1" class="form-control" id="attempts" placeholder="{{ __('admin.quizAttemptsPlaceholder') }}" required />
												</fieldset>
											</div>

											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="training_id">{{ __('admin.Training')}}</label>
													<select class="form-control form-select" name="training_id" id="training_id" required>
														@foreach( $trainings as $training )
															<option value="{{ $training->id }}">{{ $training->title }}</option>
														@endforeach
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="description">{{ __('admin.Description')}}</label>
													<input value="" name="description" type="text" class="form-control" id="description" placeholder="{{ __('admin.Description') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="end">{{ __('admin.endsAt')}}</label>
													<input value="" name="end" type="text" class="form-control" id="end" placeholder="{{ __('admin.endsAt') }}" required autocomplete="off" />
												</fieldset>
												<fieldset class="form-group">
													<label for="passing_criteria">{{ __('admin.passingCriteria')}}</label>
													<input value="" name="passing_criteria" type="number" class="form-control" id="passing_criteria" placeholder="{{ __('admin.passingCriteriaPlaceholder') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="status">{{ __('admin.quizStatus')}}</label>
													<select class="form-control form-select" name="status" id="status">
														<option value="draft">{{ __('admin.Draft') }}</option>
														<option value="published">{{ __('admin.Published') }}</option>
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="retry_after">{{ __('admin.retryAfter')}}</label>
													<input value="" name="retry_after" type="text" class="form-control" id="retry_after" placeholder="{{ __('admin.retryAfterPlaceholder') }}" />
												</fieldset>
											</div>
										</div>
										<div class="row">
											<div class="col-12 d-flex justify-content-end">
												<button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
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
@section('pagescripts')
	<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
	<script src="/js/jquery.datetimepicker.full.min.js"></script>
	<script type="text/javascript">
		var dtOptions = {
			format:'Y-m-d H:i',onGenerate:function( ct ){
				jQuery(this).find('.xdsoft_date').toggleClass('xdsoft_disabled');
			},
			maxDate:'{{ date("Y/m/d", strtotime("-1 days")) }}',
			timepicker:true
		};
		$('#start').datetimepicker( dtOptions );
		$('#end').datetimepicker( dtOptions );
	</script>
@stop
