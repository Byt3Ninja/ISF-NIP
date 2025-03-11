@extends('layouts.admin')
@section('title', __('admin.Organizations') )
@section('index_url', route('organizations.index') )
@section('subTitle', __('admin.Add new organization'))
@section('style')
	<style>
		.progress-bar-container {
		width: 100%;
		background-color: #f3f3f3;
		border: 1px solid #ccc;
		border-radius: 5px;
		overflow: hidden;
		margin-top: 10px;
		}
		.progress-bar {
		height: 20px;
		background-color: #4caf50;
		width: 0%;
		transition: width 0.3s;
		}
	</style>
@stop
@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
<div class="container-xxl flex-grow-1 container-p-y">
	<div class="content-overlay"></div>
		<div class="content-body">
			<!-- BuilderForm start -->
			<section id="basic-input">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header"> <h4 class="card-title">{{__('admin.Add new organization')}}</h4> </div>
							<div class="card-content">
								<div class="card-body">
									@if(session()->has('msg'))
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-warning alert-danger mb-2" role="alert">
												<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
												<div class="d-flex align-items-center">
													<i class="ti ti-error"></i>
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
									<form method="post" action="{{ route('organizations.store') }}" enctype="multipart/form-data">
										{!! csrf_field() !!}
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="name_en">{{ __('admin.English Organization Name')}}</label>
													<input value="{{old('name_en')}}" name="name_en" type="text" class="form-control" id="name_en" placeholder="{{ __('admin.English Organization Name') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<label for="name_ar">{{ __('admin.Arabic Organization Name')}}</label>
													<input value="{{old('name_ar')}}" name="name_ar" type="text" class="form-control" id="name_ar" placeholder="{{ __('admin.Arabic Organization Name') }}" required />
												</fieldset>
												<fieldset class="form-group mt-2">
													<label for="status">{{ __('admin.Organization Status')}}</label>
													<select class="custom-select" name="status" id="status">
														<option value="1">{{ __('admin.Active') }}</option>
														<option value="0">{{ __('admin.Not Active') }}</option>
													</select>
												</fieldset>
												<fieldset class="form-group">
													<label for="name_en">{{ __('admin.color')}}</label>
													<input value="{{old('color')}}" name="color" type="text" class="form-control" id="color" placeholder="{{ __('admin.color') }}" required />
												</fieldset>
												<fieldset class="form-group">
													<div class="input-group">
														<!-- Upload Company Logo -->
														<div class="d-flex flex-column gap-1 justify-content-between align-items-center picker-container">
															<span class="text-center text-black fw-medium">{{ __('admin.Image')}}</span>
															<div class="position-relative">
																@if (isset($client->company->logo))
																<div id="holder">
																	<img id="company-logo holder" src="{{ $client->company->logo }}" alt="Company logo" class="mb-2 radius-15" />
																</div>
																@else
																<div>
																	<img class="mb-2" id="company-logo" 
																		src="/images/background/logo.png"
																		alt="Default logo"
																		style="width: 90px; height: 90px; border-radius: 10px; cursor: pointer;"
																	/>
																</div>
																@endif
																<input type="file" class="form-control" id="file1" name="img_url" />
																<!-- right bottom-icon -->
																<div class="progress-bar-container">
																	<div id="progress-bar" class="progress-bar"></div>
																</div>
															</div>
															{{-- <div class="btn btn-outline-primary rounded-pill btn-sm lfm" data-input="file1" data-preview="holder" > {{ $l == 'en' ? 'Reset' : 'إعادة تعيين' }} </div> --}}
														</div>
														<!-- / Upload Company Logo -->
													</div>
												</fieldset>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-12">
												<fieldset class="form-group">
													<label for="basicInput">{{__('admin.Organization Manager English Name')}}</label>
													<div class="row">
														<div class="col-md-6">
															<input name="first_name_en" type="text" class="form-control" id="first_name_en" value="{{old('first_name_en')}}" placeholder="{{ __('admin.First Name') }}" required autocomplete="first_name_en" autofocus />
														</div>
														<div class="col-md-6">
															<input value="{{old('last_name_en')}}" name="last_name_en" type="text" class="form-control" id="last_name_en" placeholder="{{ __('admin.Last Name') }}" required autocomplete="last_name_en" />
														</div>
													</div>
												</fieldset>
												<fieldset class="form-group">
													<label for="basicInput">{{__('admin.Organization Manager Arabic Name')}}</label>
													<div class="row">
														<div class="col-md-6">
															<input value="{{old('first_name_ar')}}" name="first_name_ar" type="text" class="form-control" id="first_name_ar" placeholder="{{ __('admin.First Name') }}" required autocomplete="first_name_ar" autofocus />
														</div>
														<div class="col-md-6">
															<input value="{{old('last_name_ar')}}" name="last_name_ar" type="text" class="form-control" id="last_name_ar" placeholder="{{ __('admin.Last Name') }}" required autocomplete="last_name_ar" />
														</div>
													</div>
												</fieldset>
												<fieldset>
													<ul class="list-unstyled mb-0">
														<li class="d-inline-block mr-2 mb-1">
															<fieldset>
																<div class="radio">
																	<input type="radio" name="gender" value="Male" id="g1" {{ old('gender')=='Male' ? 'checked=""' :''}} >
																	<label for="g1">{{__('Male')}}</label>
																</div>
															</fieldset>
														</li>
														<li class="d-inline-block mr-2 mb-1">
															<fieldset>
																<div class="radio">
																	<input type="radio" name="gender" value="Female" id="g2" {{ old('gender')=='Female' ? 'checked=""' :''}}>
																	<label for="g2">{{__('Female')}}</label>
																</div>
															</fieldset>
														</li>
													</ul>
												</fieldset>
												<fieldset class="form-group">
													<label class="text-bold-600" for="exampleInputEmail1">{{ __('admin.Email') }}</label>
													<input value="{{old('email')}}" name="email" type="email" class="form-control" id="email" placeholder="{{ __('admin.Email') }}" required autocomplete="email">
												</fieldset>
												<fieldset class="form-group">
													<label class="text-bold-600" for="phone">{{ __('admin.Phone') }}</label>
													<input type="text" class="form-control" id="phone" placeholder="{{ __('admin.Phone') }}" value="{{old('phone')}}" name="phone" required autocomplete="new-phone">
												</fieldset>
												<fieldset class="form-group">
													<label class="text-bold-600" for="password">{{ __('admin.Password') }}</label>
													<input type="password" class="form-control" id="password" placeholder="{{ __('admin.Password') }}" name="password" required autocomplete="new-password">
												</fieldset>
												<fieldset class="form-group">
													<label class="text-bold-600" for="password-confirm">{{ __('admin.Confirm') }}</label>
													<input type="password" class="form-control" id="password-confirm" placeholder="{{ __('admin.Password') }}" name="password_confirmation" required autocomplete="new-password">
												</fieldset>
											</div>
										</div>
										@if( $_user->ableToAny(['organizations.create', 'organizations.store'], $permissions) )
										<div class="row">
											<div class="col-12 d-flex justify-content-end">
												<button type="submit" class="btn btn-primary mr-1 mb-1">{{__('admin.Save')}}</button>
											</div>
										</div>
										@endif
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
	<script>
		$('.lfm').filemanager('file');
		const fileInput = document.getElementById('file1');
		const previewImage = document.getElementById('company-logo');
		const progressBar = document.getElementById('progress-bar');

		fileInput.addEventListener('change', (event) => {
			const file = event.target.files[0];
			if (file)
			{
				const reader = new FileReader();
				reader.onloadstart = () => { progressBar.style.width = '0%'; };
				reader.onprogress = (event) => {
					if (event.lengthComputable)
					{
						const percentLoaded = Math.round((event.loaded / event.total) * 100);
						progressBar.style.width = `${percentLoaded}%`;
					}
				};
				reader.onload = (event) => {
					previewImage.src = event.target.result;
					progressBar.style.width = '100%';
				};
				reader.onerror = () => { alert('Error loading file'); };
				reader.readAsDataURL(file);
			}
		});
	</script>
@stop
