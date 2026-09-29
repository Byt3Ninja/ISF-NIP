@extends('layouts.admin')
@section('title', __('admin.Organization Types'))
@section('index_url', route('organization-types.index'))
@section('subTitle', __('admin.Add new Organization Type'))
@section('style')
	<style>
		#basic-input {
			width: 70%;
			margin: 2rem auto 4rem auto;
		}

		@media (max-width: 768px) {
			#basic-input {
				width: 100%;
			}
		}
	</style>
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="">
	<div class="">
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h5 class="card-title fw-bold pb-3 border-bottom text-capitalize">{{ __('admin.Add new Organization Type') }}</h5>
							</div>
							<div class="card-body">
								@if(session()->has('msg'))
								<div class="alert alert-warning alert-dismissible fade show" role="alert">
									<i class="bi bi-exclamation-triangle-fill"></i>
									{{ __('admin.'.session()->get('msg')) }}
									{{ Session::forget('msg') }}
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
								@endif
								@if($errors->any())
								<div class="alert alert-danger">
									<ul class="mb-0">
										@foreach($errors->all() as $error)
										<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
								@endif
								<form method="post" action="{{ route('organization-types.store') }}" enctype="multipart/form-data">
									{!! csrf_field() !!}
									<div class="row g-3">
										<div class="col-12">
											<div class="mb-3">
												<label for="title_en" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.English Organization Type Name') }}</label>
												<input value="{{ old('title_en') }}" name="title_en" type="text" class="form-control" id="title_en" placeholder="{{ __('admin.English Organization Type Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="title_ar" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Arabic Organization Type Name') }}</label>
												<input value="{{ old('title_ar') }}" name="title_ar" type="text" class="form-control" id="title_ar" placeholder="{{ __('admin.Arabic Organization Type Name') }}" required>
											</div>
										</div>
									</div>
									@if($_user->ableToAny(['organization-types.create', 'organization-types.store'], $permissions))
									<div class="d-flex justify-content-end">
										<button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
									</div>
									@endif
								</form>
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
