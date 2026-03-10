<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	class="light-style layout-navbar-fixed layout-compact" data-theme="theme-default" data-assets-path="/vuexy/assets/"
	data-template="vertical-menu-template" data-style="light">

<head>
	@include('admin.includes.head')

	<!-- Additional Styles -->
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
	<!-- BS-Stepper CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css" />

	<style>
		.hackathon-banner {
			background: linear-gradient(45deg, #7367f0, #9c88ff);
			padding: 40px 20px;
			border-radius: 15px;
			color: #fff;
			margin-bottom: 30px;
		}

		.hackathon-banner .banner-content {
			max-width: 800px;
			margin: auto;
		}

		#hackathon-main-title {
			font-weight: 700;
			font-size: 3.5rem;
		}

		#hackathon-dates-location {
			font-size: 1.2rem;
			opacity: 0.9;
		}

		#countdown-timer {
			margin: 30px 0;
		}

		.timer-box {
			background: rgba(255, 255, 255, 0.2);
			padding: 20px;
			border-radius: 10px;
			margin: 0 10px;
		}

		.timer-box .timer-number {
			font-size: 2.5rem;
			font-weight: 700;
		}

		.timer-box .timer-label {
			font-size: 1rem;
			opacity: 0.8;
		}

		#action-buttons-placeholder .btn {
			font-size: 1.2rem;
		}

		/* Custom BS-Stepper Styles */
		.bs-stepper .step-trigger {
			padding: 1rem 0.5rem;
		}

		.bs-stepper-label {
			font-weight: 500;
		}

		.bs-stepper-circle {
			width: 40px;
			height: 40px;
			font-size: 1.2rem;
		}
	</style>
</head>

@php
$authUser = auth()->user();
$isLoggedIn = $authUser ? true : false;

// --- Logic to Parse End Date from the 'ranges' string ---
$ranges = $program->ranges; // e.g., "July 1 – August 15, 2025"

// Split the string by '–' or '-'
$dateParts = preg_split('/[–-]/', $ranges);

// Get the end date part (e.g., " August 15, 2025")
$endDatePart = trim(end($dateParts));

// Extract the year from the original string to handle cases where the year is only at the end.
preg_match('/(\d{4})/', $ranges, $yearMatch);
$year = $yearMatch[0] ?? date('Y'); // Use current year as fallback

// Check if the end date part contains a month name (e.g., "August")
if (preg_match('/[a-zA-Z]+/', $endDatePart)) {
// If it has a month, it's a complete date string
$endDateForCountdown = $endDatePart;
} else {
// If it only has a day (e.g., "15, 2025"), get the month from the start of the range
preg_match('/^[a-zA-Z]+/', trim($dateParts[0]), $monthMatch);
$month = $monthMatch[0] ?? '';
// Combine month, day, and year
$endDateForCountdown = trim($month . ' ' . $endDatePart);
}
@endphp

<body>
	<!-- Enhanced Navbar -->
	<nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm py-3">
		<div class="container">
			<a class="navbar-brand fw-bold fs-4 mb-0 d-flex align-items-center" href="/dashboard">
				<i class="ti ti-planet ti-lg text-primary me-2"></i>
				<span class="line-clamp-1">{{ $program->org }}</span>
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
				aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav flex-row align-items-center ms-auto">
					<!-- Language -->
					<li class="nav-item dropdown-language dropdown">
						<a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
							href="javascript:void(0);" data-bs-toggle="dropdown">
							<i class="ti ti-language rounded-circle ti-md"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li class="dropdown dropdown-language nav-item">
								@if ($l == 'ar')
								<a class="dropdown-item" href="/lang/en" data-language="en" data-text-direction="ltr">
									<i class="flag-icon flag-icon-us me-2"></i>
									<span class="selected-language">English</span>
								</a>
								@else
								<a class="dropdown-item" href="/lang/ar" data-language="ar" data-text-direction="rtl">
									<i class="flag-icon flag-icon-sa me-2"></i>
									<span class="selected-language">عربي</span>
								</a>
								@endif
							</li>
						</ul>
					</li>
					<!--/ Language -->

					@if ($isLoggedIn)
					<!-- User -->
					<li class="nav-item navbar-dropdown dropdown-user dropdown ms-8">
						<a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
							<div class="avatar avatar-online">
								@if (auth()->user()->image_url)
								<img class="rounded-circle" src="{{ auth()->user()->image_url }}"
									onerror="this.src='/images/user-placeholder.jpeg'" />
								@else
								<img class="rounded-circle" src="/images/user-placeholder.jpeg" alt="avatar" />
								@endif
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li>
								<a class="dropdown-item mt-0" href="/dashboard/show/myProfile">
									<div class="d-flex align-items-center">
										<div class="flex-shrink-0 me-2">
											<div class="avatar avatar-online">
												@if (auth()->user()->image_url)
												<img class="rounded-circle" src="{{ auth()->user()->image_url }}"
													onerror="this.src='/images/user-placeholder.jpeg'" />
												@else
												<img class="rounded-circle" src="/images/user-placeholder.jpeg" alt="avatar" />
												@endif
											</div>
										</div>
										<div class="flex-grow-1">
											<h6 class="mb-0">{{ Auth::user()->name }}</h6>
											<small class="text-muted">{{ Auth::user()->title }}</small>
										</div>
									</div>
								</a>
							</li>
							<li>
								<div class="dropdown-divider my-1 mx-n2"></div>
							</li>
							<li>
								<a class="dropdown-item" href="/dashboard">
									<i class="ti ti-dashboard me-3 ti-md"></i>
									<span class="align-middle">{{ __('admin.Dashboard') }}</span>
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="/dashboard/show/myProfile">
									<i class="ti ti-user me-3 ti-md"></i>
									<span class="align-middle">{{ __('admin.Profile') }}</span>
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="/dashboard/myProfile">
									<i class="ti ti-settings me-3 ti-md"></i>
									<span class="align-middle">
										{{$l=='ar'?'الاعدادات':'Settings'}}
									</span>
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="/dashboard/documentation">
									<i class="ti ti-question-mark me-3 ti-md"></i><span class="align-middle">FAQ</span>
								</a>
							</li>
							<li>
								<div class="d-grid px-2 pt-2 pb-1">
									<a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}"
										onclick="event.preventDefault();document.getElementById('logout-form').submit();">
										<small class="align-middle">{{ __('admin.Logout') }}</small>
										<i class="ti ti-logout ms-2 ti-14px"></i>
									</a>
									<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
										@csrf
									</form>
								</div>
							</li>
						</ul>
					</li>
					<!--/ User -->
					@else
					<!-- Login Button -->
					<li class="nav-item ms-8">
						<a class="btn btn-outline-primary" href="/register">
							<i class="ti ti-user-plus ti-md me-2"></i>
							{{ $l == 'en' ? 'Register' : 'تسجيل' }}
						</a>
					</li>
					<li class="nav-item ms-3">
						<a class="btn btn-primary" href="/login">
							<i class="ti ti-login ti-md me-2"></i>
							{{ $l == 'en' ? 'Login' : 'تسجيل الدخول' }}
						</a>
					</li>
					<!--/ Login Button -->
					@endif
				</ul>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<div class="page-container mb-12">
		<main class="content">

			<div class="container mt-8" style="direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }};">

				<div class="hackathon-banner text-center shadow">
					<div class="banner-content">
						<h1 class="display-5 fw-bold text-black" id="hackathon-main-title"> {{ $program->title }} </h1>
						<p class="lead fw-semibold">{{ $program->description }}</p>
						<p class="lead fw-semibold" id="hackathon-dates-location">
							<i class="ti ti-calendar-event me-2"></i>
							{{ $program->ranges }}
							<span class="mx-3">|</span>
							<i class="ti ti-map-pin me-2"></i>
							{{ $program->org }}
						</p>
					</div>
				</div>

				<div class="card">
					<div class="row mt-4">
						<div class="col-md-12 col-lg-10 mx-auto">
							<div class="card-header bg-white border-bottom-0">
								<h3 class="mb-0 fw-bold">
									{{ $l == 'ar' ? 'التقديم على البرنامج' : 'Apply to the Program' }}
								</h3>
							</div>
							<div class="card-body" style="direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }};">
								<div id="stepper-form" class="bs-stepper">
									<!-- Wizard Header -->
									<div class="bs-stepper-header" role="tablist">
										<!-- Email Step -->
										<div class="step" data-target="#initial-step-pane">
											<button type="button" class="step-trigger" role="tab" id="initial-step-trigger" aria-controls="initial-step-pane" aria-selected="true">
												<span class="bs-stepper-circle"><i class="bi bi-envelope-check"></i></span>
												<span class="bs-stepper-label">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}</span>
											</button>
										</div>
										<div class="line"></div>
										<!-- Login/Register Step -->
										<div class="step" data-target="#auth-step-pane">
											<button type="button" class="step-trigger" role="tab" id="auth-step-trigger" aria-controls="auth-step-pane" aria-selected="false">
												<span class="bs-stepper-circle"><i class="bi bi-shield-lock"></i></span>
												<span class="bs-stepper-label">{{ $l == 'ar' ? 'المصادقة' : 'Authentication' }}</span>
											</button>
										</div>
										<div class="line"></div>
										<!-- Application Step -->
										<div class="step" data-target="#application-step-pane">
											<button type="button" class="step-trigger" role="tab" id="application-step-trigger" aria-controls="application-step-pane" aria-selected="false">
												<span class="bs-stepper-circle"><i class="bi bi-file-text"></i></span>
												<span class="bs-stepper-label">{{ $l == 'ar' ? 'الطلب' : 'Application' }}</span>
											</button>
										</div>
									</div>

									<!-- Wizard Content -->
									<div class="bs-stepper-content">
										<!-- Email Pane -->
										<div id="initial-step-pane" class="content" role="tabpanel" aria-labelledby="initial-step-trigger">
											<div class="mb-3">
												<label for="initial-email" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
												<input type="email" class="form-control" id="initial-email" name="initial-email" placeholder="{{ $l == 'ar' ? 'أدخل بريدك الإلكتروني' : 'Enter your email' }}" required>
												<div id="initial-email-feedback" class="invalid-feedback"></div>
											</div>
											<div class="mt-8 d-flex justify-content-end">
												<button id="check-email" type="button" class="btn btn-primary w-px-150">
													{{ $l == 'ar' ? 'متابعة' : 'Continue' }}
													<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'right' : 'right' }} ms-2"></i>
												</button>
											</div>
										</div>

										<!-- Auth Pane -->
										<div id="auth-step-pane" class="content" role="tabpanel" aria-labelledby="auth-step-trigger">
											<!-- This pane will be populated by either the login or register form -->
										</div>

										<!-- Application Pane -->
										<div id="application-step-pane" class="content" role="tabpanel" aria-labelledby="application-step-trigger">
											<form id="register-form" method="post" action="/{{ $org_slug }}/program/{{ $program_slug }}/register" enctype="multipart/form-data">
												{!! csrf_field() !!}
												<div class="row">
													<div class="col-md-6 mb-3">
														<label for="register-email" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
														<input type="email" class="form-control" id="register-email" name="register-email" placeholder="{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}" disabled>
														<div id="register-email-feedback" class="invalid-feedback"></div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-6 mb-3">
														<label for="register-first-name" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'الاسم الأول' : 'First Name' }}</label>
														<input type="text" class="form-control" id="register-first-name" name="register-first-name" placeholder="{{ $l == 'ar' ? 'أدخل الاسم الأول' : 'Enter first name' }}">
														<div id="register-first-name-feedback" class="invalid-feedback"></div>
													</div>
													<div class="col-md-6 mb-3">
														<label for="register-last-name" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'اسم العائلة' : 'Last Name' }}</label>
														<input type="text" class="form-control" id="register-last-name" name="register-last-name" placeholder="{{ $l == 'ar' ? 'أدخل اسم العائلة' : 'Enter last name' }}">
														<div id="register-last-name-feedback" class="invalid-feedback"></div>
													</div>
												</div>
												<hr />
												<div id="register-fields" class="row"></div>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>

	<!-- Enhanced Footer -->
	<footer id="contact-section" class="text-white pt-5 pb-4" style="background-color: var(--bs-primary-text-emphasis);">
		<div class="container text-center text-md-start">
			<div class="row text-center text-md-start">
				<div class="col-md-4 col-lg-5 mt-3">
					<h5 class="text-white text-uppercase mb-4 fw-bold">{{ $l == 'en' ? 'About Us' : 'معلومات عنا' }}</h5>
					<p>{{ $l == 'en' ? 'We are dedicated to fostering innovation and creativity through our hackathon events. Join us to build, learn, and connect with like-minded individuals.' : 'نحن ملتزمون بتعزيز الابتكار والإبداع من خلال فعاليات الهاكاثون التي ننظمها. انضم إلينا للبناء والتعلم والتواصل مع الأفراد ذوي التفكير المماثل.' }}</p>
				</div>

				<div class="col-md-4 col-lg-2 mx-auto mt-3">
					<h5 class="text-white text-uppercase mb-4 fw-bold">{{ $l == 'en' ? 'Follow Us' : 'تابعنا' }}</h5>
					<div class="d-flex justify-content-center justify-content-md-start">
						<a href="#" class="text-white me-3">
							<i class="bi bi-facebook fs-4"></i>
						</a>
						<a href="#" class="text-white me-3">
							<i class="bi bi-twitter fs-4"></i>
						</a>
						<a href="#" class="text-white me-3">
							<i class="bi bi-linkedin fs-4"></i>
						</a>
						<a href="#" class="text-white">
							<i class="bi bi-instagram fs-4"></i>
						</a>
					</div>
				</div>
			</div>

			<hr class="mb-4">

			<div class="row align-items-center">
				<div class="col-md-7 col-lg-8">
					<p class="text-center text-md-start mb-3 mb-md-0">
						© 2025 {{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات. جميع الحقوق محفوظة.' }}
					</p>
				</div>
			</div>
		</div>
	</footer>

	<!-- Core JS -->
	<script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
	<script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
	<script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
	<script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
	<script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
	<script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
	<script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
	<script src="/vuexy/assets/vendor/js/menu.js"></script>
	<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
	<script src="/vuexy/assets/js/main.js"></script>
	<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
	<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
	<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
  	<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
	<!-- BS-Stepper JS -->
	<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>

	<!-- Custom JS -->
	<script src="/vuexy/assets/js/forms-selects.js"></script>
	<script>
		var stepper;
		document.addEventListener('DOMContentLoaded', function() {
			stepper = new Stepper(document.querySelector('#stepper-form'), {
				linear: true, // Enforce a linear progression
				animation: true
			});
		});

		$(document).on('click', '#check-email', function(e) {
			e.preventDefault();
			let email = $('#initial-email').val();
			if (email.trim() == '') {
				$('#initial-email-feedback').html('{{ $l == "ar" ? "يرجى إدخال بريد إلكتروني صالح." : "Please enter a valid email." }}');
				$('#initial-email').addClass('is-invalid');
				return;
			} else {
				if (validateEmail(email)) {
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						}
					});
					$.ajax({
						type: "POST",
						url: "/{{ $org_slug }}/program/{{ $program_slug }}/validate_email",
						data: {
							email: email
						},
						success: function(response) {
							if (response.status) {
								if (response.step == 'login') {
									let loginHtml = `
										<div id="login-alert" class="alert alert-info alert-dismissible fade show mb-3" role="alert">
											<strong>{{ $l == 'ar' ? 'يرجى استخدام بريدك الإلكتروني المسجل وكلمة المرور.' : 'Please use your registered email and password.' }}</strong>
											<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
										</div>
										<div class="mb-3">
											<label for="login-email" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
											<input type="email" class="form-control" id="login-email" name="login-email" value="${email}" disabled readonly required>
											<div id="login-email-feedback" class="invalid-feedback"></div>
										</div>
										<div class="mb-3">
											<label for="login-password" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'كلمة المرور' : 'Password' }}</label>
											<input type="password" class="form-control" id="login-password" name="login-password" placeholder="{{ $l == 'ar' ? 'أدخل كلمة المرور' : 'Enter password' }}" required>
											<div id="login-password-feedback" class="invalid-feedback"></div>
										</div>
										<div class="mt-8 d-flex justify-content-between">
											<button type="button" class="btn btn-secondary" onclick="stepper.previous()">
												<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'left' : 'left' }} me-2"></i>
												{{ $l == 'ar' ? 'السابق' : 'Previous' }}
											</button>
											<button id="login-btn" type="button" class="btn btn-primary w-px-150">
												{{ $l == 'ar' ? 'متابعة' : 'Continue' }}
												<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'right' : 'right' }} ms-2"></i>
											</button>
										</div>
									`;
									$("#auth-step-pane").html(loginHtml);
									stepper.next();
								} else if (response.step == 'register') {
									$("#register-email").val(email);
									var fieldsHtml = '';
									const reqText = '{{ $l == "ar" ? "مطلوب" : "required" }}';
									const chooseText = '{{ $l == "ar" ? "اختر" : "Choose" }}';
									const otherText = '{{ $l == "ar" ? "أخرى" : "Other" }}';

									// Group fields by section
									var sections = {};
									$.each(response.fields, function(key, value) {
										var sectionName = (value.options && value.options.section) ? value.options.section : '';
										if (!sections[sectionName]) {
											sections[sectionName] = {
												title: sectionName,
												description: (value.options && value.options.description) ? value.options.description : '',
												fields: []
											};
										}
										sections[sectionName].fields.push(value);
									});

									// Render each section
									$.each(sections, function(sectionName, section) {
										fieldsHtml += `<div class="section-wrapper mb-4">`;
										if (section.title || section.description) {
											fieldsHtml += `<div class="section-header mb-3">`;
											if (section.title) {
												fieldsHtml += `<h5 class="section-title text-primary mb-1">${section.title}</h5>`;
											}
											if (section.description) {
												fieldsHtml += `<p class="section-description text-muted mb-0">${section.description}</p>`;
											}
											fieldsHtml += `</div>`;
										}
										fieldsHtml += `<div class="card border shadow-sm"><div class="card-body p-4"><div class="row g-4">`;

										$.each(section.fields, function(key, value) {
											var builderType = (value.options && value.options.type) ? value.options.type : 'short_answer';
											var isRequired = value.options && value.options.validation && value.options.validation.required == 1;
											var requiredAttr = isRequired ? 'required' : '';
											var requiredStar = isRequired ? '<span class="text-danger ms-1">*</span>' : '';
											var label = (value.options && value.options.label) ? value.options.label : '';
											var valuesStr = (value.options && value.options.values) ? value.options.values : '';
											var optionsList = valuesStr ? valuesStr.split(',') : [];

											fieldsHtml += `<div class="col-12"><div class="form-group">`;
											if (label) {
												fieldsHtml += `<label for="${value.id_for}" class="form-label fw-semibold mb-2">${label} ${requiredStar}</label>`;
											}

											switch (builderType) {
												case 'dropdown':
													fieldsHtml += `<select class="form-select" id="${value.id_for}" name="${value.field_name}" ${requiredAttr}>`;
													fieldsHtml += `<option value="">${chooseText}...</option>`;
													$.each(optionsList, function(i, option) {
														var optLabel = option === '__OTHER__' ? otherText : option;
														fieldsHtml += `<option value="${option}">${optLabel}</option>`;
													});
													fieldsHtml += `</select>`;
													break;

												case 'paragraph':
													var rows = (value.options && value.options.rows) ? value.options.rows : 3;
													fieldsHtml += `<textarea class="form-control" id="${value.id_for}" name="${value.field_name}" rows="${rows}" placeholder="${label}" ${requiredAttr}></textarea>`;
													break;

												case 'multiple_choice':
													fieldsHtml += `<div class="options-group">`;
													$.each(optionsList, function(i, option) {
														var optLabel = option === '__OTHER__' ? otherText : option;
														fieldsHtml += `<div class="form-check mb-2">`;
														fieldsHtml += `<input class="form-check-input" type="radio" name="${value.field_name}" id="${value.id_for}_${i}" value="${option}" ${requiredAttr} />`;
														fieldsHtml += `<label class="form-check-label" for="${value.id_for}_${i}">${optLabel}</label>`;
														fieldsHtml += `</div>`;
													});
													fieldsHtml += `</div>`;
													break;

												case 'checkboxes':
													fieldsHtml += `<div class="options-group">`;
													$.each(optionsList, function(i, option) {
														var optLabel = option === '__OTHER__' ? otherText : option;
														fieldsHtml += `<div class="form-check mb-2">`;
														fieldsHtml += `<input class="form-check-input" type="checkbox" name="${value.field_name}[]" id="${value.id_for}_${i}" value="${option}" />`;
														fieldsHtml += `<label class="form-check-label" for="${value.id_for}_${i}">${optLabel}</label>`;
														fieldsHtml += `</div>`;
													});
													fieldsHtml += `</div>`;
													break;

												case 'linear_scale':
													fieldsHtml += `<select class="form-select" id="${value.id_for}" name="${value.field_name}" ${requiredAttr}>`;
													fieldsHtml += `<option value="">${chooseText}...</option>`;
													for (var i = 1; i <= 5; i++) {
														fieldsHtml += `<option value="${i}">${i}</option>`;
													}
													fieldsHtml += `</select>`;
													break;

												case 'date':
													fieldsHtml += `<input type="text" class="form-control datepicker" id="${value.id_for}" name="${value.field_name}" placeholder="${label}" ${requiredAttr} />`;
													break;

												case 'time':
													fieldsHtml += `<input type="text" class="form-control timepicker" id="${value.id_for}" name="${value.field_name}" placeholder="${label}" ${requiredAttr} />`;
													break;

												case 'file':
													fieldsHtml += `<input type="file" class="form-control" id="${value.id_for}" name="${value.field_name}" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" ${requiredAttr} />`;
													break;

												case 'short_answer':
												default:
													fieldsHtml += `<input type="text" class="form-control" id="${value.id_for}" name="${value.field_name}" placeholder="${label}" ${requiredAttr} />`;
													break;
											}

											fieldsHtml += `<div class="invalid-feedback">${label} ${reqText}</div>`;
											fieldsHtml += `</div></div>`;
										});

										fieldsHtml += `</div></div></div>`;
										fieldsHtml += `</div>`;
									});

									$("#register-fields").html(fieldsHtml);
									let registerNavButtons = `
										<div class="col-12 mt-8 d-flex justify-content-between">
											<button type="button" class="btn btn-secondary" onclick="stepper.to(1)">
												<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'left' : 'left' }} me-2"></i>
												{{ $l == 'ar' ? 'السابق' : 'Previous' }}
											</button>
											<button id="join_btn" type="button" class="btn btn-primary w-px-120">
												{{ $l == 'ar' ? 'انضم' : 'Join' }}
												<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'right' : 'right' }} ms-2"></i>
											</button>
										</div>
									`;
									$("#register-fields").append(registerNavButtons);
									datePicker();
									stepper.to(3); // Jump to the third step (Application)
								} else {
									Swal.fire({ 
										icon: 'error',
										title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
										text: response.message,
										showCancelButton: false,
										showConfirmButton: true,
										customClass: {
											confirmButton: 'btn btn-primary',
											cancelButton: 'btn btn-label-secondary'
										},
										buttonsStyling: false,
									});
								}
							} else {
								Swal.fire({ 
									icon: 'error',
									title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
									text: response.message,
									showCancelButton: false,
									showConfirmButton: true,
									customClass: {
										confirmButton: 'btn btn-primary',
									},
									buttonsStyling: false,
								});
							}
						},
						error: function(error) {
							console.log(error);
							Swal.fire({
								icon: 'error',
								title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
								text: 'Error validating email. Please try again.',
								showCancelButton: false,
								showConfirmButton: true,
								customClass: {
									confirmButton: 'btn btn-primary',
								},
								buttonsStyling: false,
							});
						}
					});
				} else {
					$('#initial-email-feedback').html('{{ $l == "ar" ? "بريد إلكتروني غير صالح." : "Invalid email." }}');
					$('#initial-email').addClass('is-invalid');
					return;
				}
			}
		});

		$(document).on('click', '#login-btn', function(e) {
			e.preventDefault();
			let email = $('#login-email').val();
			let password = $('#login-password').val();

			if (password.trim() == '') {
				$('#login-password-feedback').html('{{ $l == "ar" ? "كلمة المرور مطلوبة." : "Password is required." }}');
				$('#login-password').addClass('is-invalid');
				return;
			}
			
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				type: "POST",
				url: "/{{ $org_slug }}/program/{{ $program_slug }}/authenticate",
				data: {
					email: email,
					password: password
				},
				success: function(response) {
					if (response.status) {
						window.location.href = response.url;
					} else {
						Swal.fire({
							title: '{{ $l == "ar" ? "خطأ" : "Authentication Failed" }}',
							text: response.message,
							icon: 'error',
							showCancelButton: false,
							showConfirmButton: true,
							confirmButtonText: '{{ $l == "ar" ? "حسناً" : "OK" }}',
							customClass: {
								confirmButton: 'btn btn-primary',
							},
							buttonsStyling: false,
						});
					}
				},
				error: function(error) {
					console.log(error);
					Swal.fire({ 
						title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
					 	text: '{{ $l == "ar" ? "حدث خطأ أثناء المصادقة. يرجى المحاولة مرة أخرى." : "An error occurred during authentication. Please try again." }}',
					  icon: 'error',
						showCancelButton: false,
						showConfirmButton: true,
						confirmButtonText: '{{ $l == "ar" ? "حسناً" : "OK" }}',
						customClass: {
							confirmButton: 'btn btn-primary',
						},
						buttonsStyling: false,
					});
				}
			});
		});

		$(document).on('keyup', '#initial-email', function() {
			$('#initial-email').removeClass('is-invalid');
			$('#initial-email-feedback').html('');
		});

		$(document).on('click', '#join_btn', function(e) {
			e.preventDefault();
			let $form = $('#register-form');
			let formData = new FormData($form[0]);
			let isValid = true;

			// Clear previous error highlights
			$form.find('.is-invalid').removeClass('is-invalid');

			// Custom Validation - Only validate fields that have the required attribute
			$form.find('[required]').each(function() {
				const $field = $(this);
				if ($field.is(':visible') && $field.attr('required') !== undefined) { // Only validate visible required fields
					const type = $field.attr('type');
					const value = $field.val();

					if (type === 'checkbox' && !$field.is(':checked')) {
						$field.addClass('is-invalid');
						isValid = false;
					} else if (type === 'file') {
						if (this.files.length === 0) {
							$field.addClass('is-invalid');
							isValid = false;
						}
					} else if (!value || value.trim() === '') {
						$field.addClass('is-invalid');
						isValid = false;
					}
				}
			});

			if (!isValid) {
				Swal.fire({ 
					title: '{{ $l == "ar" ? "نموذج غير مكتمل" : "Incomplete Form" }}',
					text: '{{ $l == "ar" ? "يرجى ملء جميع الحقول المطلوبة." : "Please fill out all required fields." }}',
					icon: 'warning',
					showCancelButton: false,
					showConfirmButton: true,
					confirmButtonText: "{{ __('admin.OK') }}",
					customClass: {
						confirmButton: 'btn btn-primary',
					},
					buttonsStyling: false,
				});
				return;
			}
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			formData.append('register-email', $('#register-email').val());
			$.ajax({
				type: "POST",
				url: "/{{ $org_slug }}/program/{{ $program_slug }}/register",
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.status) {
						window.location.href = response.url;
					} else {
						Swal.fire({ 
							title: '{{ $l == "ar" ? "خطأ في التسجيل" : "Registration Error" }}',
							text: '{{ $l == "ar" ? "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى." : "An error occurred during registration. Please try again." }}',
							icon: 'error',
							showCancelButton: false,
							showConfirmButton: true,
							confirmButtonText: '{{ $l == "ar" ? "حسناً" : "OK" }}',
							customClass: {
								confirmButton: 'btn btn-primary',
							},
							buttonsStyling: false,
						});
					}
				},
				error: function(xhr, status, error) {
					console.log(error);
					Swal.fire({ 
						title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
						text: '{{ $l == "ar" ? "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى." : "An error occurred during registration. Please try again." }}',
						icon: 'error',
						showCancelButton: false,
						showConfirmButton: true,
						confirmButtonText: "{{ __('admin.OK') }}",
						customClass: {
							confirmButton: 'btn btn-primary',
						},
						buttonsStyling: false,
					});
				}
			});
		});

		validateEmail = (email) => {
			var re = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
			return re.test(email);
		};

		function datePicker() {
			$('.datepicker').flatpickr({
				dateFormat: 'Y-m-d'
			});
		}
	</script>
</body>

</html>