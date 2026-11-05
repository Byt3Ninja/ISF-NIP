@extends('layouts.admin')
@section('title', __('admin.Organizations'))
@section('index_url', route('organizations.index'))
@section('subTitle', __('admin.Add new organization'))
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
								<h5 class="card-title fw-bold pb-3 border-bottom text-capitalize">{{ __('admin.Add new organization') }}</h5>
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
								<form method="post" action="{{ route('organizations.store') }}" enctype="multipart/form-data">
									{!! csrf_field() !!}
									<div class="row g-3">
										<div class="col-12 mb-6">
											<label for="file1" class="d-flex justify-content-center align-items-center position-relative rounded"
												style="height: 140px; cursor: pointer; width: 200px; margin: 0 auto;">
												<img 
													src="/images/logo-placeholder.png"
													alt="{{ __('admin.Organization Logo') }}" 
													class="rounded img-fluid border shadow-sm"
													style="height: 140px; width: 200px; object-fit: cover;" 
												/>
												<input type="file" class="form-control d-none" id="file1" name="img_url">
												<i class="ti ti-camera-plus position-absolute fs-3 text-dark end-0 bottom-0 m-4 z-1"></i>
											</label>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="name_en" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.English Organization Name') }}</label>
												<input value="{{ old('name_en') }}" name="name_en" type="text" class="form-control" id="name_en" placeholder="{{ __('admin.English Organization Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="name_ar" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Arabic Organization Name') }}</label>
												<input value="{{ old('name_ar') }}" name="name_ar" type="text" class="form-control" id="name_ar" placeholder="{{ __('admin.Arabic Organization Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="status" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Organization Status') }}</label>
												<select class="form-select" name="status" id="status">
													<option value="1">{{ __('admin.Active') }}</option>
													<option value="0">{{ __('admin.Not Active') }}</option>
												</select>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="color" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.color') }}</label>
												<input value="{{ old('color') }}" name="color" type="text" class="form-control" id="color" placeholder="{{ __('admin.color') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="first_name_en" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Organization Manager English First Name') }}</label>
												<input name="first_name_en" type="text" class="form-control" id="first_name_en" value="{{ old('first_name_en') }}" placeholder="{{ __('admin.First Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="last_name_en" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Organization Manager English Last Name') }}</label>
												<input name="last_name_en" type="text" class="form-control" id="last_name_en" value="{{ old('last_name_en') }}" placeholder="{{ __('admin.Last Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="first_name_ar" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Organization Manager Arabic First Name') }}</label>
												<input name="first_name_ar" type="text" class="form-control" id="first_name_ar" value="{{ old('first_name_ar') }}" placeholder="{{ __('admin.First Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="last_name_ar" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Organization Manager Arabic Last Name') }}</label>
												<input name="last_name_ar" type="text" class="form-control" id="last_name_ar" value="{{ old('last_name_ar') }}" placeholder="{{ __('admin.Last Name') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label class="form-label fw-semibold fs-6 mb-2">{{ __('admin.Gender') }}</label>
												<div class="d-flex align-items-center gap-4">
													<div class="form-check">
														<input class="form-check-input" type="radio" name="gender" value="Male" id="g1" {{ old('gender') == 'Male' ? 'checked' : '' }}>
														<label class="form-check-label" for="g1">{{ __('Male') }}</label>
													</div>
													<div class="form-check">
														<input class="form-check-input" type="radio" name="gender" value="Female" id="g2" {{ old('gender') == 'Female' ? 'checked' : '' }}>
														<label class="form-check-label" for="g2">{{ __('Female') }}</label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="email" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Email') }}</label>
												<input value="{{ old('email') }}" name="email" type="email" class="form-control" id="email" placeholder="{{ __('admin.Email') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="phone" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Phone') }}</label>
												<input value="{{ old('phone') }}" name="phone" type="text" class="form-control" id="phone" placeholder="{{ __('admin.Phone') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="password" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Password') }}</label>
												<input name="password" type="password" class="form-control" id="password" placeholder="{{ __('admin.Password') }}" required>
											</div>
										</div>
										<div class="col-12">
											<div class="mb-3">
												<label for="password-confirm" class="form-label fw-semibold fs-6 mb-1">{{ __('admin.Confirm') }}</label>
												<input name="password_confirmation" type="password" class="form-control" id="password-confirm" placeholder="{{ __('admin.Password') }}" required>
											</div>
										</div>
									</div>
									@if($_user->ableToAny(['organizations.create', 'organizations.store'], $permissions))
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
	<script>
		const fileInput = document.getElementById('file1');
		const previewImage = document.querySelector('label[for="file1"] img');

		fileInput.addEventListener('change', (event) => {
			const file = event.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = (event) => {
					previewImage.src = event.target.result;
				};
				reader.onerror = () => {
					alert('Error loading file');
				};
				reader.readAsDataURL(file);
			}
		});

		document.getElementById('company-logo').addEventListener('click', () => {
			document.getElementById('file1').click();
		});
	</script>
@stop
