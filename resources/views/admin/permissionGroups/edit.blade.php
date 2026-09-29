@extends('layouts.admin')
@section('title', __('admin.Permissions') )
@section('index_url', route('permissions.index') )
@section('subTitle', __('admin.EditPermission'))
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
							<div class="card-header border-bottom">
								<h5 class="card-title mb-0">{{__('admin.EditPermission')}}</h5>
							</div>
							<div class="card-content mt-6">
								<div class="card-body">
									@if(session()->has('msg'))
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-warning alert-danger mb-2" role="alert">
												<button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
												<div class="d-flex align-items-center">
													<i class="ti ti-error"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span>
												</div>
											</div>
										</div>
									</div>
									@endif
									<!-- Validation messages -->
									@if( count( $errors ) > 0 )
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
									<form method="POST" action="{{ route('permission-groups.update', [$permission_group->id]) }}">
										@method('PUT')
										@csrf
										<div class="row">
											<div class="col-md-12">
												<fieldset class="form-group mb-8">
													<label class="form-label" for="name">{{ __('admin.Title')}}</label>
													<input type="text" class="form-control" id="name" name='name' required value="{{ $permission_group->name }}" placeholder="{{__('admin.Title')}}" />
												</fieldset>
												<fieldset class="form-group my-8">
													<label class="form-label" for="description">{{ __('admin.Description')}}</label>
													<input type="text" class="form-control" id="description" name='description' value="{{ $permission_group->description }}" placeholder="{{__('admin.Description')}}" />
												</fieldset>
											</div>
										</div>
										<div class="row mt-4">
											<div class="col-12 d-flex justify-content-end">
												<button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Update')}}</button>
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
