@extends('layouts.admin')
@section('title', __('admin.quizzes') )
@section('index_url', route('quizzes.index') )
@section('subTitle', __('admin.Edit Quiz'))
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
<div class="app-content content">
	<div class="content-overlay"></div>
	<div class="content-wrapper">
		@include('admin.includes.heading')
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">{{__('admin.Edit Quiz')}}</h4>
							</div>
							<div class="card-content">
								<div class="card-body">
									@if(session()->has('msg'))
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-warning alert-danger mb-2" role="alert">
												<button type="button" class="close" data-dismiss="alert"
													aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
												<div class="d-flex align-items-center">
													<i class="bx bx-error"></i>
													<span>
														{{ __('admin.'.session()->get('msg')) }}
														{{ Session::forget('msg') }}
													</span>
												</div>
											</div>
										</div>
									</div>
									@endif
									<!-- Validation messages -->
									@if(count($errors) >0)
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger">
												<ul>
													@foreach($errors->all() as $error)
													<li>{{ $error }} </li>
													@endforeach
												</ul>
											</div>
										</div>
									</div>
									@endif
									<form method="post" action="{{ route('quizzes.update', ['quiz' => $quiz->id]) }}">
										{!! csrf_field() !!}
										<input name="_method" type="hidden" value="PATCH" />
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="organization_id">{{ __('admin.Organization')}}</label>
													<select class="custom-select" name="organization_id" id="organization_id" required>
														@foreach( $organizations as $organization )
															<option value="{{ $organization->id }}" {{ $quiz->organization_id == $organization->id ? 'selected' : '' }}>{{ $organization->{'name_' . $l} }}</option>
														@endforeach
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="title">{{ __('admin.Title')}}</label>
													<input value="{{ $quiz->title }}" name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="start">{{ __('admin.startsFrom')}}</label>
													<input value="{{ $quiz->start->format('Y-m-d H:i') }}" name="start" type="text" class="form-control" id="start" placeholder="{{ __('admin.startsFrom') }}" required autocomplete="off" />
												</fieldset>
												<fieldset class="form-group">
													<label for="time_limit">{{ __('admin.TimeLimit')}}</label>
													<input value="{{ $quiz->time_limit }}" name="time_limit" type="number" class="form-control" id="time_limit" placeholder="{{ __('admin.TimeLimitPlaceholder') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="shuffle">{{ __('admin.shuffleQuestions')}}</label>
													<select class="custom-select" name="shuffle" id="shuffle">
														<option value="shuffle_true" {{ $quiz->shuffle == true ? 'selected' : '' }}>{{ __('admin.shuffle_true') }}</option>
														<option value="shuffle_false" {{ $quiz->shuffle == false ? 'selected' : '' }}>{{ __('admin.shuffle_false') }}</option>
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="attempts">{{ __('admin.quizAttempts')}}</label>
													<input value="{{ $quiz->attempts }}" name="attempts" type="number" min="1" class="form-control" id="attempts" placeholder="{{ __('admin.quizAttemptsPlaceholder') }}" required />
												</fieldset>
											</div>

											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="training_id">{{ __('admin.Training')}}</label>
													<select class="custom-select" name="training_id" id="training_id" required>
														@foreach( $trainings as $training )
															<option value="{{ $training->id }}">{{ $training->title }}</option>
														@endforeach
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="description">{{ __('admin.Description')}}</label>
													<input value="{{ $quiz->description }}" name="description" type="text" class="form-control" id="description" placeholder="{{ __('admin.Description') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="end">{{ __('admin.endsAt')}}</label>
													<input value="{{ $quiz->end->format('Y-m-d H:i') }}" name="end" type="text" class="form-control" id="end" placeholder="{{ __('admin.endsAt') }}" required autocomplete="off" />
												</fieldset>
												<fieldset class="form-group">
													<label for="passing_criteria">{{ __('admin.passingCriteria')}}</label>
													<input value="{{ $quiz->passing_criteria }}" name="passing_criteria" type="number" class="form-control" id="passing_criteria" placeholder="{{ __('admin.passingCriteriaPlaceholder') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="status">{{ __('admin.quizStatus')}}</label>
													<select class="custom-select" name="status" id="status">
														<option value="draft" {{ $quiz->status == 'draft' ? 'selected' : '' }}>{{ __('admin.Draft') }}</option>
														<option value="published" {{ $quiz->status == 'published' ? 'selected' : '' }}>{{ __('admin.Published') }}</option>
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="retry_after">{{ __('admin.retryAfter')}}</label>
													<input value="{{ $quiz->retry_after }}" name="retry_after" type="text" class="form-control" id="retry_after" placeholder="{{ __('admin.retryAfterPlaceholder') }}" />
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
