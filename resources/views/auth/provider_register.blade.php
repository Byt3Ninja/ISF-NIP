<html 
	lang="{{ $l }}" 
	dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" 
	data-theme="theme-default"
	data-assets-path="/vuexy/assets/"
	data-template="vertical-menu-template" 
	data-style="light"
>

<head>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
	<meta name="description" content="VI Login">
	<meta name="author" content="info@growthlabs.app">
	<title>
		{{ $l == 'en' ? 'Register New Account' : 'تسجيل حساب جديد'}}
	</title>
	<link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
	<link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">


	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

	<!-- Icons -->
	<link rel="stylesheet" href="/vuexy/assets/vendor/fonts/fontawesome.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/fonts/flag-icons.css" />
	<!-- End Icons -->

	<!-- Core CSS -->

	<link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />

	<link rel="stylesheet" href="/vuexy/assets/css/demo.css" />

	<!-- Vendors CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
	
	<!-- Page CSS -->
	<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

	<style>
		/* div[data-validator] {
			width: 100% !important;
			text-align: {{ $l == 'ar' ? 'end' : 'start' }} !important;
		} */

		#switcher-container {
			position: absolute;
			top: 1rem;
			width: 100px;
		}

		.iti__country-list {
			direction: ltr !important;
      text-align: left !important;
			position: absolute;
			left: 1rem;
			/* right: 0; */
			z-index: 9999;
			list-style: none;
			padding: 0;
			margin: 0 0 0 -1px;
			box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
			background-color: white;
			border: 1px solid #CCC;
			white-space: nowrap;
			max-height: 200px;
			width: 350px;
			overflow-y: scroll;
			overflow-x: auto -webkit-overflow-scrolling: touch;
			background-color: #fff;
		}
		.uploader {
			border: 2px dashed #dee2e6;
			border-radius: .75rem;
			padding: 1.25rem;
			text-align: center;
			cursor: pointer;
			transition: background-color .15s, border-color .15s;
		}
			.uploader.dragover {
			background-color: #f8fafc;
			border-color: #86b7fe;
			box-shadow: 0 0 0 .25rem rgba(13,110,253,.08);
		}
		.avatar-preview {
			width: 120px;
			height: 120px;
			border-radius: 50%;
			object-fit: cover;
			border: 2px solid #e9ecef;
			background: #f8f9fa;
		}
		.file-input {
			display: none;
		}

		.register-card-wrapper {
			width: 100%;
		}

		@media screen and (min-width: 900px) {
			.register-card-wrapper {
				width: 50%;
			}
		}
	</style>

	<!-- Helpers -->
	<script src="/vuexy/assets/vendor/js/helpers.js"></script>
	<!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

	<!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.	-->
	<script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

	<!--? Config:	Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.	-->
	<script src="/vuexy/assets/js/config.js"></script>
</head>
<!-- END: Head-->
<!-- BEGIN: Body-->

<body>
	<!-- Content -->
	<!-- Toggle theme and language -->
	<nav 
		class="container-xxl navbar navbar-expand-xl align-items-center"
		style="right: 1rem;"
		id="switcher-container"
	>
		<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
			<ul class="navbar-nav flex-row align-items-center ms-auto">
				<!-- Language -->
				<li class="nav-item dropdown-language dropdown" style="position: relative;">
					<a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
						href="javascript:void(0);" data-bs-toggle="dropdown">
						<i class="ti ti-language rounded-circle ti-md"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-end" 
						style="position: absolute; left: -8rem; top: 2rem;"
					>
						<li class="dropdown dropdown-language nav-item">
							@if ($l == 'ar')
								<a class="dropdown-item" href="/lang/en">
									<i class="flag-icon flag-icon-us"></i>
									<span class="selected-language">English</span>
								</a>
							@else
								<a class="dropdown-item" href="/lang/ar">
									<i class="flag-icon flag-icon-sa"></i>
									<span class="selected-language">عربي</span>
								</a>
							@endif
						</li>
					</ul>
				</li>
				<!--/ Language -->

				<!-- Style Switcher -->
				<li class="nav-item dropdown-style-switcher dropdown" style="position: relative;">
					<a class="nav-link btn-icon rounded-pill dropdown-toggle hide-arrow"
						href="javascript:void(0);" data-bs-toggle="dropdown">
						<i class="ti ti-md"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-end dropdown-styles" 
						style="position: absolute; left: -8rem; width: 150px;"
					>
						<li>
							<a class="dropdown-item" href="javascript:void(0);" data-theme="light">
								<span class="align-middle"><i class="ti ti-sun ti-md me-3"></i>Light</span>
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
								<span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>Dark</span>
							</a>
						</li>
						<li>
							<a class="dropdown-item" href="javascript:void(0);" data-theme="system">
								<span class="align-middle"><i class="ti ti-device-desktop-analytics ti-md me-3"></i>System</span>
							</a>
						</li>
					</ul>
				</li>
				<!-- / Style Switcher-->
			</ul>
		</div>
	</nav>
	
	<!-- Toggle theme and language -->	
	<div class="container-xxl">
		<div class="authentication-wrapper authentication-basic container-p-y">
			<div class="register-card-wrapper py-6">
				<!-- Register Card -->
				<div class="card mt-6 mt-lg-0">
					<div class="px-4 px-md-4 py-6">
						<!-- Logo -->
						<div class="app-brand justify-content-center mb-4">
							<a href="/dashboard" class="app-brand-link">
								<img src="{{$logo}}" alt="avatar" width="150">
							</a>
						</div>
						<!-- /Logo -->

						<h4 class="mb-1 text-center lh-base text-capitalize">{{ __('auth.formHeading') }} 🚀</h4>
						<p class="mb-6 text-center">{{ __('auth.formSubHeading') }}</p>

							@if( $setting->set_order )
								<form id="registerForm" class="mb-6 px-md-8" method="POST" action="{{ route('provider-registration') }}" enctype="multipart/form-data">
									@csrf
									<div class="row">
										<div>
											@if( session('error') )
												<div class="row">
													<div class="col-md-12">
														<div class="alert alert-danger">
															<ul>
																<li>{{ session('error') }} </li>
															</ul>
														</div>
													</div>
												</div>
											@endif
											@if( session('success') )
											<div class="row">
												<div class="col-md-12">
													<div role="alert" class="alert alert-success alert-dismissible mb-2">
														{{ __('auth.provider_registration_recorded') }}
														<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
													</div>
												</div>
											</div>
											@endif

											{{--  <div class="col-12 mb-6">
												<label class="form-label fs-6 mb-2 d-block text-{{ $l == 'ar' ? 'end' : 'start' }}">
													{{ $l == 'en' ? 'Profile photo' : 'الصورة الشخصية' }}
												</label>
												<div id="uploader" class="uploader" tabindex="0">
													<div class="d-flex gap-5 align-items-center justify-content-center">
														<img id="avatarPreview" class="avatar-preview" src="/images/user-avatar.png" alt="avatar preview">
														<div class="text-{{ $l == 'ar' ? 'end' : 'start' }}">
															<strong id="uploaderTitle">
																{{ $l == 'en' ? 'Drag & drop or click to upload' : 'اسحب وأفلت أو انقر للتحميل' }}
															</strong>
															<div class="small text-muted">
																{{ $l == 'en' ? 'PNG, JPG, JPEG up to 10 MB' : 'PNG، JPG، JPEG بحد أقصى 10 MB' }}
															</div>
															<div class="mt-2 text-start">
																<button type="button" id="chooseBtn" class="btn btn-sm btn-label-primary">
																	{{ $l == 'en' ? 'Upload' : 'تحميل' }}
																	<i class="ti ti-upload ti-xs ms-1"></i>
																</button>
																<button type="button" id="removeBtn" class="btn btn-sm btn-label-secondary d-none">
																	{{ $l == 'en' ? 'Reset' : 'حذف' }}
																	<i class="ti ti-reload ti-xs ms-1"></i>
																</button>
															</div>
														</div>
													</div>
												</div>

												<input id="fileInput" class="file-input" type="file" accept="image/png, image/jpeg" name="img_url">

												<div class="mt-3">
													<div class="progress" style="height:6px; display:none;">
														<div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
													</div>
													@error('img_url')
														<div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
													@enderror
												</div>
											</div>  --}}

											<div class="col-12 mb-3">
												<label for="company_name" class="form-label fs-6 mb-2 w-100">{{ __('auth.companyName') }}</label>
												<div class="input-group input-group-merge">
													<input name="company_name" type="text" class="form-control" id="company_name" placeholder="{{ __('auth.enterCompanyName') }}" required autofocus />
													@error('company_name')
														<div class="invalid-feedback">{{ $message }}</div>
													@enderror
												</div>
											</div>

											<div class="col-12 mb-3">
												<label for="contact_name" class="form-label fs-6 mb-2 w-100">{{ __("auth.contactName") }}</label>
												<div class="input-group input-group-merge">
													<input name="contact_name" type="text" class="form-control" id="contact_name" placeholder="{{ __('auth.enterContactName') }}" required />
												</div>
												@error('contact_name')
													<div class="invalid-feedback">{{ $message }}</div>
												@enderror
											</div>

											<div class="col-12 mb-3">
												<label for="email" class="form-label fs-6 mb-2 w-100">{{ __('auth.E-Mail') }}</label>
												<div class="input-group input-group-merge">
													<input name="email" type="email" class="form-control" placeholder="{{ __('auth.E-Mail') }}" required />
												</div>
												@error('email')
													<div class="invalid-feedback">{{ $message }}</div>
												@enderror
											</div>

											<div class="col-12 mb-4 d-grid">
												<label class="form-label fs-6 mb-2 w-100" for="phone">{{ __('auth.Phone') }}</label>
												<input id="phone" type="tel" class="form-control" placeholder="{{ __('auth.Phone') }}" name="phone" required />
												<small class="text-danger mt-1" id="phone-error" style="display: none;"></small>
												<input type="hidden" name="country_code" id="phone_code" value="20">
												@error('phone')
													<div class="invalid-feedback">{{ $message }}</div>
												@enderror
												@error('country_code')
													<div class="invalid-feedback">{{ $message }}</div>
												@enderror
											</div>

											<div class="mb-6 mt-2">
												@if( !empty( $termsUrl ) || !empty( $privacyUrl ) )
												<div class="form-check mb-3">
													<input class="form-check-input" type="checkbox" name="terms_agreement" id="terms_agreement" required value="agreed">
                  									<label class="form-check-label" for="terms_agreement">
														@if( !empty( $termsUrl ) && !empty( $privacyUrl ) )
															{!! __('auth.register_agreement', [
																'terms' => '<a href="'. $termsUrl .'" target="_blank">'. __('admin.terms') .'</a>',
																'privacy' => '<a href="'. $privacyUrl .'" target="_blank">'. __('admin.privacy') .'</a>'
															]) !!}
														@elseif( !empty( $termsUrl ) )
															{!! __('auth.register_agreement_terms', [
																'terms' => '<a href="'. $termsUrl .'" target="_blank">'. __('admin.terms') .'</a>'
															]) !!}
														@elseif( !empty( $privacyUrl ) )
															{!! __('auth.register_agreement_privacy', [
																'terms' => '<a href="'. $termsUrl .'" target="_blank">'. __('admin.terms') .'</a>'
															]) !!}
														@endif
                  									</label>
                								</div>
												@endif
											</div>
									
										<div class="col-12">
											<button type="submit" class="btn btn-primary w-100 mb-4"> {{ __('auth.Register') }}</button>
										</div>
									</div>
								</form>
							@else
								<div class="alert alert-warning text-center col-12">
									<span class="">{{ __('admin.Register_Disabled') }}</span>
								</div>
							@endif

						<p class="text-center">
							<span>{{ __('Already have account') }}{{ $l == 'ar' ? '؟' : '?' }}</span>
							<a href="{{ route('login') }}">
								<span>{{ $l == 'en' ? 'Sign in instead' : 'سجل الدخول' }}</span>
							</a>
						</p>
						@if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') ||(isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
						<div class="divider my-6 mx-auto" style="width: 80%;">
							<div class="divider-text">{{ __('auth.or') }}</div>
						</div>
						@endif
						@php
						$data = request('data') ?? '2521';
				@endphp
						<div class="d-flex justify-content-center">
						 @if(isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled')
							<a href="{{ route('integrations.auth_linkedin_login', $data) }}" class="btn btn-sm btn-icon rounded-circle btn-text-facebook me-3 shadow-sm" style="background-color: #0077b5;">
								<i class="fa-brands fa-linkedin-in text-white"></i>
							</a>
							@endif
							@if(isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled')
							<a href="{{ url('/auth/redirect/google') }}" class="btn btn-sm bg-white border btn-icon rounded-circle btn-text-google-plus shadow-sm">
								<img src="/icons/google-icon.svg" alt="google" width="20" height="20">
							</a>
							@endif
						</div>
					</div>
				</div>
				<!--/ Register Card -->
			</div>
		</div>
	</div>
	<!-- / Content -->


	<!-- Core JS -->
	<!-- build:js assets/vendor/js/core.js -->

	<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
	<script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
	{{-- <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script> --}}
	<script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
	<script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
	<script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
	<script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
	<script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
	<script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
	<script src="/vuexy/assets/vendor/js/menu.js"></script>
	<!-- endbuild -->

	<!-- Vendors JS -->
	<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
	<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
	{{-- <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script> --}}

	<!-- Main JS -->
	<script src="/vuexy/assets/js/main.js"></script>

	<!-- Page JS -->
	<script src="/vuexy/assets/js/pages-auth.js"></script>
	<script src="/vuexy/assets/js/forms-selects.js"></script>
	{{-- <script src="/vuexy/assets/js/pages-account-settings-account.js"></script> --}}

	<script>
		const lang = '{{ $l }}';

		// Initialize the phone input
		const phoneInput = document.getElementById("phone");
		const phoneError = document.getElementById('phone-error');
		const countryCodeInput = document.querySelector("#phone_code");

		const iti = intlTelInput(phoneInput, {
			initialCountry: 'eg',
			preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
			utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
			formatOnDisplay: false,
			separateDialCode: true,
			autoPlaceholder: "aggressive",
			
			customPlaceholder: (placeholder) => "e.g. " + placeholder,
		});

				// Set initial country code
		countryCodeInput.value = iti.getSelectedCountryData().dialCode;

		// Update country code when country changes
		phoneInput.addEventListener('countrychange', function() {
			const countryData = iti.getSelectedCountryData();
			countryCodeInput.value = countryData.dialCode;
		});

		// Optional: Handle validation
		phoneInput.addEventListener("input", (e) => {
			console.log(e.target.value);
			if (!iti.isValidNumber()) {
				phoneError.style.display = 'block';
				phoneError.innerText = lang == 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
			} else {
				phoneError.style.display = 'none';
				// console.error("Invalid number");
			}
		});
	</script>
	{{--  <script>
		// const uploader = document.getElementById('uploader');
		// const fileInput = document.getElementById('fileInput');
		// const chooseBtn = document.getElementById('chooseBtn');
		// const removeBtn = document.getElementById('removeBtn');
		// const avatarPreview = document.getElementById('avatarPreview');
		// const progressBarWrap = document.querySelector('.progress');
		// const progressBar = document.getElementById('progressBar');
		// const fileError = document.getElementById('fileError');

		// const MAX_SIZE = 3 * 1024 * 1024; // 3MB
		// const ALLOWED_TYPES = ['image/png', 'image/jpeg'];

		// Helpers
		function resetUploader() {
			avatarPreview.src = '/images/user-avatar.png';
			removeBtn.classList.add('d-none');
			progressBar.style.width = '0%';
			progressBarWrap.style.display = 'none';
			// fileError.style.display = 'none';
		}

		// function showError(msg) {
		//	 fileError.textContent = msg;
		//	 fileError.style.display = 'block';
		// }

		function handleFile(file) {
			// Validate
			// if (!ALLOWED_TYPES.includes(file.type)) {
			//	 showError('Unsupported file type — use PNG or JPG.');
			//	 return;
			// }
			// if (file.size > MAX_SIZE) {
			//	 showError('File too large — must be under 3 MB.');
			//	 return;
			// }

			// Read with progress
			const reader = new FileReader();
			progressBarWrap.style.display = 'block';
			progressBar.style.width = '0%';
			// fileError.style.display = 'none';

			reader.onprogress = (e) => {
				if (e.lengthComputable) {
					const percent = Math.round((e.loaded / e.total) * 100);
					progressBar.style.width = percent + '%';
				}
			};

			reader.onload = () => {
				// preview + save base64 hidden field
				avatarPreview.src = reader.result;
				removeBtn.classList.remove('d-none');
				progressBar.style.width = '100%';
				setTimeout(() => { progressBarWrap.style.display = 'none'; }, 600);
			};

			reader.onerror = () => {
				// showError('Error reading file. Try another image.');
				progressBarWrap.style.display = 'none';
			};

			reader.readAsDataURL(file);
		}

		// Click handlers
		uploader.addEventListener('click', () => fileInput.click());
		chooseBtn.addEventListener('click', (e) => { e.stopPropagation(); fileInput.click(); });

		removeBtn.addEventListener('click', (e) => {
			e.stopPropagation();
			fileInput.value = '';
			resetUploader();
		});

		fileInput.addEventListener('change', (e) => {
			if (e.target.files && e.target.files[0]) handleFile(e.target.files[0]);
		});

		// Drag & drop
		['dragenter','dragover'].forEach(evt => {
			uploader.addEventListener(evt, (e) => {
				e.preventDefault(); e.stopPropagation();
				uploader.classList.add('dragover');
			});
		});
		['dragleave','drop'].forEach(evt => {
			uploader.addEventListener(evt, (e) => {
				e.preventDefault(); e.stopPropagation();
				uploader.classList.remove('dragover');
			});
		});

		uploader.addEventListener('drop', (e) => {
			const dt = e.dataTransfer;
			if (!dt) return;
			const file = dt.files && dt.files[0];
			if (file) handleFile(file);
		});

		// Keyboard accessibility: allow Enter to open file dialog
		uploader.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
		});

		// init
		// resetUploader();
	</script>  --}}
</body>
</html>