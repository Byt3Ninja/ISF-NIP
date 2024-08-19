@extends('layouts.admin')
@section('title', __('admin.Questions Categories') )
@section('index_url', route('question-categories.index') )
@section('subTitle', __('admin.Add new category'))
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
								<h4 class="card-title">{{__('admin.Add new category')}}</h4>
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
									<form method="post" action="{{ route('question-categories.store') }}">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="organization_id">{{ __('admin.Organization')}}</label>
													<select class="custom-select" name="organization_id" id="organization_id" required>
														@foreach( $organizations as $organization )
															<option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
														@endforeach
													</select>
												</fieldset>
											</div>

											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="title">{{ __('admin.Title')}}</label>
													<input value="" name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
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
@stop
