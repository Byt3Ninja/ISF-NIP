@extends('layouts.admin')
@section('title', __('admin.Questions Categories') )
@section('index_url', route('question-categories.index') )
@section('subTitle', __('admin.Edit category'))
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
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">{{__('admin.Edit category')}}</h4>
							</div>
							<div class="card-content">
								<div class="card-body">
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
									<form method="post" action="{{ route('question-categories.update', ['question_category' => $category->id]) }}">
										{!! csrf_field() !!}
                                        <input name="_method" type="hidden" value="PATCH" />
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="organization_id">{{ __('admin.Organization')}}</label>
													<select class="form-control form-select" name="organization_id" id="organization_id" required>
														@foreach( $organizations as $organization )
															<option {{ $organization->id == $category->organization_id ? 'selected' : '' }} value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
														@endforeach
													</select>
												</fieldset>
											</div>

											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="title">{{ __('admin.Title')}}</label>
													<input value="{{ $category->title }}" name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
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
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
@stop
