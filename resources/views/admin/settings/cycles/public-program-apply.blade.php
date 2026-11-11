<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	class="light-style layout-navbar-fixed layout-compact" data-theme="theme-default" data-assets-path="/vuexy/assets/"
	data-template="vertical-menu-template" data-style="light">

<head>
	@include('admin.includes.head')

	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css" />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
	@if ($educationFields)
	{{-- GV-10440 (ISF): mobile number input, same widget as the Founder Profile --}}
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
	<style>
		.iti { width: 100%; }
		/* intl-tel-input wraps the input, so its .invalid-feedback is no longer the input's sibling. */
		.iti:has(.is-invalid) ~ .invalid-feedback { display: block; }
	</style>
	@endif

	<style>
		:root {
			--primary: {{ $program->main_color }};
			/* The program's own color wins over Platform Branding: re-point the branding variables
			   (admin/includes/branding-styles) so every themed element follows the program. */
			--theme-main: var(--primary);
			@if ($program->main_rgb)
			--theme-main-rgb: {{ $program->main_rgb }};
			@endif
			--on-primary: #ffffff;
			--background: #f8fafc;
			--surface: #ffffff;
			--surface-variant: #f1f5f9;
			--on-surface: #0f172a;
			--on-surface-variant: #64748b;
			--secondary: #0891b2;
			--tertiary: #d97706;
			--outline: #e2e8f0;
			/* Strength of the program's gradient start/end blobs in the hero. */
			--hero-tint: 0.3;
		}

		/* Override default theme purple/primary colors with the customized main color */
		.text-primary {
			color: var(--primary) !important;
		}
		.bg-primary {
			background-color: var(--primary) !important;
		}
		.bg-label-primary {
			background-color: color-mix(in srgb, var(--primary) 14%, transparent) !important;
			color: var(--primary) !important;
		}
		.btn-primary {
			background-color: var(--primary) !important;
			border-color: var(--primary) !important;
		}
		.spinner-grow.text-primary {
			color: var(--primary) !important;
		}
		.bs-stepper .step.active .step-trigger .bs-stepper-circle {
			background-color: var(--primary) !important;
			color: #fff !important;
		}
		.bs-stepper .step.active .step-trigger .bs-stepper-label {
			color: var(--primary) !important;
		}

		body {
			background-color: var(--background);
			color: var(--on-surface);
			font-family: 'Inter', sans-serif;
			overflow-x: hidden;
			display: flex;
			flex-direction: column;
			min-height: 100vh;
			margin: 0;
		}
		.page-container { flex: 1; }

		.glass-card {
			background: rgba(255, 255, 255, 0.8);
			backdrop-filter: blur(10px);
			border: 1px solid var(--outline);
			box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
			transition: transform 0.3s ease, border-color 0.3s ease;
		}
		.glass-card:hover {
			border-color: var(--primary);
			transform: translateY(-2px);
		}

		.hero-gradient {
			background: radial-gradient(circle at 20% 30%, rgba({{ $program->start_rgb }}, var(--hero-tint)) 0%, transparent 80%),
						radial-gradient(circle at 80% 70%, rgba({{ $program->end_rgb }}, var(--hero-tint)) 0%, transparent 80%);
		}

		.btn-primary-custom {
			background-color: var(--primary);
			color: var(--on-primary);
			border: none;
			font-weight: 700;
			padding: 12px 32px;
			border-radius: 10px;
			transition: all 0.3s ease;
		}
		.btn-primary-custom:hover {
			background-color: var(--primary);
			opacity: 0.9;
			box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
			color: white;
		}

		.btn-outline-custom {
			border: 2px solid var(--primary);
			color: var(--primary);
			font-weight: 700;
			padding: 12px 32px;
			border-radius: 10px;
		}
		.btn-outline-custom:hover {
			background-color: var(--primary);
			color: white;
		}

		.navbar-custom {
			background: rgba(255, 255, 255, 0.8) !important;
			backdrop-filter: blur(15px);
			border-bottom: 1px solid var(--outline);
		}
		.navbar-custom .navbar-brand {
			color: var(--on-surface) !important;
			font-weight: 900;
			letter-spacing: -1px;
		}
		.navbar-custom .nav-link {
			color: var(--on-surface-variant) !important;
			font-weight: 500;
		}
		.navbar-custom .nav-link.active {
			color: var(--primary) !important;
		}

		.footer-custom {
			background-color: #f1f5f9;
			border-top: 1px solid var(--outline);
			padding: 80px 0 30px;
		}

		.countdown-box {
			padding: 1.5rem;
			border-radius: 1rem;
		}
		.countdown-number {
			font-size: 2.5rem;
			font-weight: 800;
			color: var(--on-surface);
		}
	</style>
</head>

@php
$authUser = auth()->user();
$isLoggedIn = $authUser ? true : false;

$ranges = $program->ranges;
$dateParts = preg_split('/[–-]/', $ranges);
$endDatePart = trim(end($dateParts));
preg_match('/(\d{4})/', $ranges, $yearMatch);
$year = $yearMatch[0] ?? date('Y');
if (preg_match('/[a-zA-Z]+/', $endDatePart)) {
	$endDateForCountdown = $endDatePart;
} else {
	preg_match('/^[a-zA-Z]+/', trim($dateParts[0]), $monthMatch);
	$month = $monthMatch[0] ?? '';
	$endDateForCountdown = trim($month . ' ' . $endDatePart);
}
@endphp

<body>
	<nav class="navbar navbar-expand-lg navbar-custom sticky-top py-3">
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

					@if ($isLoggedIn)
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
							<li><div class="dropdown-divider my-1 mx-n2"></div></li>
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
									<span class="align-middle">{{$l=='ar'?'الاعدادات':'Settings'}}</span>
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
									<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
								</div>
							</li>
						</ul>
					</li>
					@else
					<li class="nav-item ms-8">
						<a class="btn btn-outline-custom btn-sm" href="/register">
							<i class="ti ti-user-plus ti-md me-2"></i>
							{{ $l == 'en' ? 'Register' : 'تسجيل' }}
						</a>
					</li>
					<li class="nav-item ms-3">
						<a class="btn btn-primary-custom btn-sm" href="/login">
							<i class="ti ti-login ti-md me-2"></i>
							{{ $l == 'en' ? 'Login' : 'تسجيل الدخول' }}
						</a>
					</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>

	<div class="page-container mb-12">
		<main class="content">
			<div class="container mt-8" style="direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }};">

				<section class="hero-gradient pt-5 pb-4 mb-4" style="border-radius:20px;">
					<div class="text-center px-3">
						<div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4"
							style="background:white;border:1px solid var(--outline);box-shadow:0 4px 6px rgba(0,0,0,0.02);">
							<span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
							<span class="small fw-bold text-uppercase text-primary" style="font-size:11px;letter-spacing:1px;">
								{{ $l == 'en' ? 'Registration Open' : 'التسجيل مفتوح' }}
							</span>
						</div>

						<h1 class="display-5 fw-bold mb-3" id="hackathon-main-title">{{ $program->title }}</h1>
						<p class="lead fw-semibold mx-auto mb-4" style="max-width:700px;color:var(--on-surface-variant);">
							{{ $program->description }}
						</p>
						<p class="fw-semibold mb-4" id="hackathon-dates-location" style="color:var(--on-surface-variant);">
							<i class="ti ti-calendar-event me-2"></i>{{ $program->ranges }}
							<span class="mx-3">|</span>
							<i class="ti ti-map-pin me-2"></i>{{ $program->org }}
						</p>

						<div class="row g-3 justify-content-center mb-5 mx-auto" style="max-width:700px;" id="countdown-timer">
							<div class="col-6 col-md-3">
								<div class="glass-card countdown-box text-center">
									<div class="countdown-number" id="days">00</div>
									<div class="small fw-bold text-uppercase" style="color:var(--on-surface-variant);">{{ $l == 'en' ? 'Days' : 'أيام' }}</div>
								</div>
							</div>
							<div class="col-6 col-md-3">
								<div class="glass-card countdown-box text-center">
									<div class="countdown-number" id="hours">00</div>
									<div class="small fw-bold text-uppercase" style="color:var(--on-surface-variant);">{{ $l == 'en' ? 'Hours' : 'ساعات' }}</div>
								</div>
							</div>
							<div class="col-6 col-md-3">
								<div class="glass-card countdown-box text-center">
									<div class="countdown-number" id="minutes">00</div>
									<div class="small fw-bold text-uppercase" style="color:var(--on-surface-variant);">{{ $l == 'en' ? 'Minutes' : 'دقائق' }}</div>
								</div>
							</div>
							<div class="col-6 col-md-3">
								<div class="glass-card countdown-box text-center">
									<div class="countdown-number" id="seconds">00</div>
									<div class="small fw-bold text-uppercase" style="color:var(--on-surface-variant);">{{ $l == 'en' ? 'Seconds' : 'ثواني' }}</div>
								</div>
							</div>
						</div>
					</div>
				</section>

				<div class="glass-card p-4 p-lg-5">
					<h3 class="mb-4 fw-bold">
						{{ __('admin.apply_to_the_program') }}
					</h3>
					<div id="stepper-form" class="bs-stepper">
						<div class="bs-stepper-header" role="tablist">
							<div class="step" data-target="#initial-step-pane">
								<button type="button" class="step-trigger" role="tab" id="initial-step-trigger" aria-controls="initial-step-pane" aria-selected="true">
									<span class="bs-stepper-circle"><i class="bi bi-envelope-check"></i></span>
									<span class="bs-stepper-label">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}</span>
								</button>
							</div>
							<div class="line"></div>
							<div class="step" data-target="#auth-step-pane">
								<button type="button" class="step-trigger" role="tab" id="auth-step-trigger" aria-controls="auth-step-pane" aria-selected="false">
									<span class="bs-stepper-circle"><i class="bi bi-shield-lock"></i></span>
									<span class="bs-stepper-label">{{ $l == 'ar' ? 'المصادقة' : 'Authentication' }}</span>
								</button>
							</div>
							<div class="line"></div>
							<div class="step" data-target="#application-step-pane">
								<button type="button" class="step-trigger" role="tab" id="application-step-trigger" aria-controls="application-step-pane" aria-selected="false">
									<span class="bs-stepper-circle"><i class="bi bi-file-text"></i></span>
									<span class="bs-stepper-label">{{ $l == 'ar' ? 'الطلب' : 'Application' }}</span>
								</button>
							</div>
							@if($VFEmail)
							<div class="line"></div>
							<div class="step" data-target="#verification-step-pane">
								<button type="button" class="step-trigger" role="tab" id="verification-step-trigger" aria-controls="verification-step-pane" aria-selected="false">
									<span class="bs-stepper-circle"><i class="bi bi-shield-check"></i></span>
									<span class="bs-stepper-label">{{ $l == 'ar' ? 'التحقق' : 'Verification' }}</span>
								</button>
							</div>
							@endif
						</div>

						<div class="bs-stepper-content">
							<div id="initial-step-pane" class="content" role="tabpanel" aria-labelledby="initial-step-trigger">
								<div class="mb-3">
									<label for="initial-email" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}<span class="text-danger ms-1">*</span></label>
									<input type="email" class="form-control" id="initial-email" name="initial-email" placeholder="{{ $l == 'ar' ? 'أدخل بريدك الإلكتروني' : 'Enter your email' }}" required>
									<div id="initial-email-feedback" class="invalid-feedback"></div>
								</div>
								<div class="mt-8 d-flex justify-content-end">
									<button id="check-email" type="button" class="btn btn-primary-custom w-px-150">
										{{ $l == 'ar' ? 'متابعة' : 'Continue' }}
										<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'right' : 'right' }} ms-2"></i>
									</button>
								</div>
							</div>

							<div id="auth-step-pane" class="content" role="tabpanel" aria-labelledby="auth-step-trigger"></div>

							<div id="application-step-pane" class="content" role="tabpanel" aria-labelledby="application-step-trigger">
								<form id="register-form" method="post" action="/{{ $org_slug }}/program/{{ $program_slug }}/register" enctype="multipart/form-data">
									{!! csrf_field() !!}
									{{-- The applicant's own details sit above every question section; each section's Next validates them too. --}}
									<div id="applicant-fields">
									<div class="row">
										<div class="col-md-6 mb-3">
											<label for="register-email" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}@if ($educationFields)<span class="text-danger ms-1">*</span>@endif</label>
											<input type="email" class="form-control" id="register-email" name="register-email" placeholder="{{ $l == 'ar' ? 'البريد الإلكتروني' : 'Email' }}" @if ($educationFields) readonly @else disabled @endif>
											<div id="register-email-feedback" class="invalid-feedback"></div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6 mb-3">
											<label for="register-first-name" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'الاسم الأول' : 'First Name' }}<span class="text-danger ms-1">*</span></label>
											<input type="text" class="form-control" id="register-first-name" name="register-first-name" placeholder="{{ $l == 'ar' ? 'أدخل الاسم الأول' : 'Enter first name' }}" required
												@if ($educationFields) maxlength="50" minlength="2" data-pattern="^[\p{L}\s]+$" data-pattern-message="{{ __('admin.name_letters_only_msg') }}" data-length-message="{{ __('admin.name_length_msg') }}" data-required-message="{{ __('admin.first_name_required_msg') }}" @endif>
											<div id="register-first-name-feedback" class="invalid-feedback">{{ $l == 'ar' ? 'الاسم الأول مطلوب.' : 'First name is required.' }}</div>
										</div>
										<div class="col-md-6 mb-3">
											<label for="register-last-name" class="form-label fw-bold mb-2">{{ $l == 'ar' ? 'اسم العائلة' : 'Last Name' }}@if ($educationFields)<span class="text-danger ms-1">*</span>@endif</label>
											<input type="text" class="form-control" id="register-last-name" name="register-last-name" placeholder="{{ $l == 'ar' ? 'أدخل اسم العائلة' : 'Enter last name' }}"
												@if ($educationFields) required maxlength="50" minlength="2" data-pattern="^[\p{L}\s]+$" data-pattern-message="{{ __('admin.name_letters_only_msg') }}" data-length-message="{{ __('admin.name_length_msg') }}" data-required-message="{{ __('admin.last_name_required_msg') }}" @endif>
											<div id="register-last-name-feedback" class="invalid-feedback"></div>
										</div>
									</div>
									@if ($educationFields)
									{{-- GV-10440 (ISF): mobile + education fields; they land on the Founder Profile. --}}
									<div class="row">
										<div class="col-md-6 mb-3 form-group">
											<label for="phone" class="form-label fw-bold mb-2">{{ __('auth.Phone') }}<span class="text-danger ms-1">*</span></label>
											<input type="hidden" name="country_code" id="country_code" value="20">
											<input type="tel" class="form-control" id="phone" name="phone" maxlength="20" autocomplete="tel" required
												data-required-message="{{ __('admin.mobile_required_msg') }}" data-invalid-message="{{ __('admin.mobile_invalid_msg') }}">
											<div class="invalid-feedback"></div>
										</div>
										@include('partials.forms.isf-education-fields', [
											'values' => [],
											'wrapperClass' => 'col-md-6 mb-3 form-group',
											'labelClass' => 'form-label fw-bold mb-2',
											'selectClass' => 'form-select',
											'entityRequiredKey' => 'admin.entity_required_msg',
											'withReferral' => true,
											'referralRequired' => true,
											'withFeedback' => true,
										])
									</div>
									@endif
									</div>
									<hr />
									<div id="register-fields" class="row"></div>
								</form>
							</div>

							@if($VFEmail)
							<div id="verification-step-pane" class="content" role="tabpanel" aria-labelledby="verification-step-trigger">
								<div class="text-center p-5">
									<div class="mb-4">
										<i class="bi bi-envelope-paper-pulse text-primary" style="font-size: 4rem;"></i>
									</div>
									<h3 class="mb-3">{{ $l == 'ar' ? 'تحقق من بريدك الإلكتروني' : 'Verify Your Email' }}</h3>
									<p class="text-muted mb-4 fs-5">
										{{ $l == 'ar' 
											? 'لقد أرسلنا رابط تحقق إلى بريدك الإلكتروني. يرجى التحقق من بريدك وتفعيل حسابك للمتابعة.' 
											: 'We have sent a verification link to your email. Please check your inbox and verify your account to continue.' }}
									</p>
									<div class="d-flex justify-content-center gap-3">
										<button type="button" class="btn btn-primary-custom btn-lg px-5" onclick="window.location.href='/dashboard'">
											{{ $l == 'ar' ? 'انتقل إلى لوحة التحكم' : 'Go to Dashboard' }}
										</button>
									</div>
								</div>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>

	<footer class="footer-custom">
		<div class="container text-center text-md-start">
			<div class="row text-center text-md-start">
				<div class="col-md-4 col-lg-5 mt-3">
					<h5 class="fw-bold text-uppercase mb-4">{{ $l == 'en' ? 'About Us' : 'معلومات عنا' }}</h5>
					<p style="color:var(--on-surface-variant);">{{ $program?->bio ?: '' }}</p>
				</div>
				<div class="col-md-4 col-lg-2 mx-auto mt-3">
					@if( $program?->facebook || $program?->twitter || $program?->linkedin || $program?->instagram || $program?->youtube || $program?->tiktok )
					<h5 class="fw-bold text-uppercase mb-4">{{ $l == 'en' ? 'Follow Us' : 'تابعنا' }}</h5>
					<div class="d-flex justify-content-center justify-content-md-start gap-3">
						@if( $program?->facebook )
						<a href="{{ $program->facebook }}" class="text-reset text-decoration-none">
							<i class="ti ti-brand-facebook ti-lg text-primary"></i>
						</a>
						@endif
						@if( $program?->twitter )
						<a href="{{ $program->twitter }}" class="text-reset text-decoration-none">
							<i class="ti ti-brand-x ti-lg text-primary"></i>
						</a>
						@endif
						@if( $program?->linkedin )
						<a href="{{ $program->linkedin }}" class="text-reset text-decoration-none">
							<i class="ti ti-brand-linkedin ti-lg text-primary"></i>
						</a>
						@endif
						@if( $program?->instagram )
						<a href="{{ $program->instagram }}" class="text-reset text-decoration-none">
							<i class="ti ti-brand-instagram ti-lg text-primary"></i>
						</a>
						@endif
						@if( $program?->youtube )
						<a href="{{ $program->youtube }}" class="text-reset text-decoration-none">
							<i class="ti ti-brand-youtube ti-lg text-primary"></i>
						</a>
						@endif
						@if( $program?->tiktok )
						<a href="{{ $program->tiktok }}" class="text-reset text-decoration-none">
							<i class="ti ti-brand-tiktok ti-lg text-primary"></i>
						</a>
						@endif
					</div>
					@endif
				</div>
			</div>
			<hr class="my-5" style="opacity:0.1;">
			<div class="text-center small" style="color:var(--on-surface-variant);">
				© {{ date('Y') }} {{ $platformName }} {{ $l == 'en' ? '. All rights reserved.' : '. جميع الحقوق محفوظة.' }}
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
	@include('partials.flatpickr-locale')
	<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
	<script src="/vuexy/assets/js/forms-selects.js"></script>

	<script>
		var stepper;
		// Shared validation message strings (GV QA fix): these must be visible to
		// BOTH the dynamic field renderer (inside the #check-email AJAX success
		// closure) and the separate #join_btn submit handler below. Declaring
		// them only inside the renderer closure caused a `numberMinText is not
		// defined` ReferenceError on submit, which aborted the whole validation
		// pass silently (nothing got validated).
		const numericText = '{{ $l == "ar" ? "يرجى إدخال رقم صحيح." : "Please enter a valid number." }}';
		// GV-3679: the message must describe the actual bound. "Please enter a
		// valid positive number" is only true when the floor is 0 (the default);
		// when an author sets min="5" it misleads, so fall back to a templated
		// "no less than N". numberMinText mirrors the server's message verbatim
		// for the min=0 case so both layers read identically.
		const numberMinText = '{{ $l == "ar" ? "يرجى إدخال رقم موجب صحيح." : "Please enter a valid positive number." }}';
		// NOTE: blade trims whitespace inside an echo tag, so a trailing space in
		// the literal is lost. Concatenate the separator explicitly instead.
		const numberMinTplText = '{{ $l == "ar" ? "يرجى إدخال رقم لا يقل عن" : "Please enter a number no less than" }}' + ' ';
		const numberMaxTplText = '{{ $l == "ar" ? "يرجى إدخال رقم لا يتجاوز" : "Please enter a number no greater than" }}' + ' ';
		const emailFormatText = '{{ $l == "ar" ? "يرجى إدخال بريد إلكتروني صحيح (مثال: name@example.com)." : "Please enter a valid email address (e.g. name@example.com)." }}';
		const urlFormatText = '{{ $l == "ar" ? "يرجى إدخال رابط صحيح (مثال: https://example.com)." : "Please enter a valid URL (e.g. https://example.com)." }}';
		const requiredText = '{{ $l == "ar" ? "هذا الحقل مطلوب." : "This field is required." }}';
		const chooseOneText = '{{ $l == "ar" ? "يرجى اختيار خيار واحد على الأقل." : "Please select at least one option." }}';

		function numberMinMessage(min) {
			return min === 0 ? numberMinText : numberMinTplText + min;
		}

		// ---------------------------------------------------------------------
		// GV-3679: single-submit guard.
		//
		// Every action button here fires an AJAX request, so nothing stops a
		// second click while the first is in flight — that produced duplicate
		// registrations. lockButton() disables the button and swaps in a
		// spinner; unlockButton() restores it. The `data-locked` flag is checked
		// by the handlers themselves because `disabled` alone does not stop a
		// delegated jQuery handler in every browser.
		//
		// The lock is released on failure and on validation rejection, but NOT
		// on a success path that navigates away or advances the stepper —
		// re-enabling there would just re-open the duplicate-submit window.
		// ---------------------------------------------------------------------
		function lockButton($btn) {
			if ($btn.data('locked')) { return false; }
			$btn.data('locked', true);
			$btn.data('original-html', $btn.html());
			$btn.prop('disabled', true).addClass('disabled');
			$btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
				+ '{{ $l == "ar" ? "جارٍ المعالجة..." : "Processing..." }}');
			return true;
		}

		function unlockButton($btn) {
			if (!$btn.data('locked')) { return; }
			$btn.data('locked', false);
			$btn.prop('disabled', false).removeClass('disabled');
			const html = $btn.data('original-html');
			if (html !== undefined) { $btn.html(html); }
		}
		// ---------------------------------------------------------------------
		// Application wizard engine.
		//
		// #register-fields is rebuilt from scratch every time the email step
		// succeeds (see the `validate_email` success handler), so this state
		// lives at page scope and is reset by `initApplicationWizard()` each
		// time that HTML is (re)rendered — never assume it survives across
		// renders.
		// ---------------------------------------------------------------------
		var sectionHistory = [0];

		function applicationSections() {
			return document.querySelectorAll('#register-fields .section-wrapper[data-section-index]');
		}

		// Mirrors the admin form-builder preview's getNextLogic(): the LAST
		// branching input with a non-"continue" logic wins, falling back to the
		// section's own default logic when every branching input reads "continue"
		// (or there are none).
		function getSectionNextLogic(sectionEl) {
			var logic = 'continue';
			sectionEl.querySelectorAll('.question-logic').forEach(function(input) {
				var questionLogic = 'continue';
				if (input.type === 'radio') {
					if (input.checked) { questionLogic = input.dataset.logic || 'continue'; }
				} else if (input.tagName === 'SELECT') {
					var opt = input.options[input.selectedIndex];
					questionLogic = opt ? (opt.dataset.logic || 'continue') : 'continue';
				}
				if (questionLogic !== 'continue') { logic = questionLogic; }
			});
			if (logic === 'continue') { logic = sectionEl.dataset.sectionLogic || 'continue'; }
			return logic;
		}

		function updateNextButtonLabel(sectionEl) {
			var nextBtn = sectionEl.querySelector('.btn-next');
			if (!nextBtn) { return; }
			var logic = getSectionNextLogic(sectionEl);
			var textSpan = nextBtn.querySelector('.btn-next-text');
			if (logic === 'submit' && textSpan) {
				textSpan.textContent = '{{ $l == "ar" ? "إرسال" : "Submit" }}';
			} else if (textSpan) {
				textSpan.textContent = '{{ $l == "ar" ? "التالي" : "Next" }}';
			}
		}

		function showApplicationSection(index) {
			var sections = applicationSections();
			sections.forEach(function(s) { s.style.display = 'none'; });
			var submitStep = document.getElementById('submit-step');
			if (submitStep) { submitStep.style.display = 'none'; }

			if (index === 'submit' || index === null || index >= sections.length) {
				if (submitStep) {
					submitStep.style.display = 'block';
					submitStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
				return;
			}

			var section = sections[index];
			section.style.display = 'block';
			updateNextButtonLabel(section);
			section.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}

		function doApplicationNavigate(sectionEl) {
			var currentIndex = parseInt(sectionEl.dataset.sectionIndex, 10);
			var sections = applicationSections();
			var logic = getSectionNextLogic(sectionEl);
			var next;

			if (logic === 'submit') {
				next = 'submit';
			} else if (logic.indexOf('section-') === 0) {
				var target = parseInt(logic.replace('section-', ''), 10);
				if (sectionHistory.indexOf(target) === -1 && target > currentIndex && sections[target]) {
					next = target;
				} else if (sections[currentIndex + 1]) {
					next = currentIndex + 1;
				} else {
					next = 'submit';
				}
			} else {
				next = sections[currentIndex + 1] ? currentIndex + 1 : 'submit';
			}

			if (next === 'submit') {
				showApplicationSection('submit');
				return;
			}

			// Re-entering this section on a fresh path invalidates anything that
			// was visited beyond it on a previous route — the founder may have
			// gone Back and changed a branching answer.
			var pos = sectionHistory.indexOf(currentIndex);
			if (pos !== -1) { sectionHistory = sectionHistory.slice(0, pos + 1); }
			if (sectionHistory.indexOf(next) === -1) { sectionHistory.push(next); }
			showApplicationSection(next);
		}

		// Validates the fields inside `container`, using the same synchronous
		// validators the #join_btn handler already uses (this page never loads
		// FormValidation.js, unlike the admin builder's preview).
		function validateFieldsIn(container) {
			var $section = $(container);
			var isValid = true;

			$section.find('input, select, textarea').each(function() {
				var $field = $(this);
				var type = ($field.attr('type') || '').toLowerCase();
				if (type === 'radio' || type === 'checkbox') { return; }
				if ($field.is(':disabled')) { return; }
				if ($field.hasClass('other-field')) { return; }
				$field.data('gv-touched', true);
				if (!applyFieldState($field, validateField($field))) { isValid = false; }
			});

			$section.find('.options-group').each(function() {
				if (!validateGroup($(this))) { isValid = false; }
			});

			$section.find('.other-wrapper').each(function() {
				if (!validateOtherWrapper($(this))) { isValid = false; }
			});

			return isValid;
		}

		// GV-10440: the applicant's own details (#applicant-fields) sit above every
		// section, so each Next checks them along with the section's questions —
		// not only the final Join.
		function validateApplicationSection(sectionEl) {
			var applicantValid = validateFieldsIn(document.getElementById('applicant-fields'));
			var sectionValid = validateFieldsIn(sectionEl);
			return applicantValid && sectionValid;
		}

		function navigateApplicationNext(sectionEl) {
			if (!validateApplicationSection(sectionEl)) {
				var $firstError = $('#applicant-fields, #register-fields').find('.is-invalid').filter(':visible').first();
				if ($firstError.length) { $firstError.trigger('focus'); }
				return;
			}
			doApplicationNavigate(sectionEl);
		}

		function navigateApplicationPrev() {
			if (sectionHistory.length <= 1) { return; }
			sectionHistory.pop();
			showApplicationSection(sectionHistory[sectionHistory.length - 1]);
		}

		// Rebinds navigation for the freshly-rendered #register-fields markup and
		// shows section 0. Call this right after replacing #register-fields' HTML.
		function initApplicationWizard() {
			sectionHistory = [0];
			var sections = applicationSections();
			sections.forEach(function(section) {
				section.style.display = 'none';
				var prevBtn = section.querySelector('.btn-prev');
				if (prevBtn) {
					prevBtn.classList.toggle('d-none', parseInt(section.dataset.sectionIndex, 10) === 0);
				}
			});
			if (sections.length) { showApplicationSection(0); }
		}

		// Delegated (the section markup is injected dynamically): Next / Back /
		// changing a branching answer.
		$(document).on('click', '#register-fields .btn-next', function() {
			var section = this.closest('.section-wrapper');
			if (section) { navigateApplicationNext(section); }
		});
		$(document).on('click', '#register-fields .btn-prev', function() {
			navigateApplicationPrev();
		});
		$(document).on('click', '#submit-step .btn-prev-submit', function() {
			navigateApplicationPrev();
		});
		$(document).on('change', '#register-fields .question-logic', function() {
			var section = this.closest('.section-wrapper');
			if (section) { updateNextButtonLabel(section); }
		});

		document.addEventListener('DOMContentLoaded', function() {
			stepper = new Stepper(document.querySelector('#stepper-form'), {
				linear: true,
				animation: true
			});
			(function() {
				var end = new Date("{{ $endDateForCountdown }}").getTime();
				function tick() {
					var now = new Date().getTime();
					var dist = end - now;
					if (dist < 0) {
						document.getElementById("countdown-timer").innerHTML =
							'<span class="badge bg-label-secondary rounded-pill px-4 py-2 fs-6">{{ $l == 'en' ? 'The event has ended' : 'انتهى الحدث' }}</span>';
						return;
					}
					document.getElementById("days").innerText = Math.floor(dist / 864e5).toString().padStart(2,'0');
					document.getElementById("hours").innerText = Math.floor(dist / 36e5 % 24).toString().padStart(2,'0');
					document.getElementById("minutes").innerText = Math.floor(dist / 6e4 % 60).toString().padStart(2,'0');
					document.getElementById("seconds").innerText = Math.floor(dist / 1e3 % 60).toString().padStart(2,'0');
				}
				tick();
				setInterval(tick, 1000);
			})();
		});

		$(document).on('click', '#check-email', function(e) {
			e.preventDefault();
			const $btn = $(this);
			if ($btn.data('locked')) { return; }
			let email = $('#initial-email').val();
			if (email.trim() == '') {
				$('#initial-email-feedback').html('{{ $l == "ar" ? "يرجى إدخال بريد إلكتروني صحيح." : "Please enter a valid email address." }}');
				$('#initial-email').addClass('is-invalid');
				return;
			} else {
				if (validateEmail(email)) {
					lockButton($btn);
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						}
					});
					$.ajax({
						type: "POST",
						url: "/{{ $org_slug }}/program/{{ $program_slug }}/validate_email",
						data: { email: email },
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
											<button id="login-btn" type="button" class="btn btn-primary-custom w-px-150">
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
									const otherValueText = '{{ $l == "ar" ? "يرجى التحديد" : "Please specify" }}';
									const otherRequiredText = '{{ $l == "ar" ? "هذا الحقل مطلوب عند اختيار \"أخرى\"." : "This field is required when \"Other\" is selected." }}';
									// numericText/numberMinText/emailFormatText are declared once at the
									// top-level script scope (near `var stepper`) so both this renderer
									// and the separate #join_btn submit handler can see them.

									// GV-3679 placeholders. The form builder has no author-supplied
									// placeholder field, and echoing the label back into the box (the
									// previous behaviour) is redundant with the label right above it.
									// So typed fields get a hint describing the EXPECTED FORMAT, which
									// is the actual guidance the ticket asks for; free-text fields,
									// where only the label carries meaning, keep a label-derived
									// prompt phrased as an instruction.
									// Format examples are locale-independent by design — a date
									// mask or a sample address reads the same either way.
									const phEmail = 'name@example.com';
									const phUrl = 'https://example.com';
									const phDate = 'YYYY-MM-DD';
									const phTime = 'HH:MM';
									// Trailing/leading spaces are appended outside the echo tag —
									// blade trims whitespace within it.
									const phEnterTpl = '{{ $l == "ar" ? "أدخل" : "Enter" }}' + ' ';
									const phNumberPlain = '{{ $l == "ar" ? "أدخل رقماً" : "Enter a number" }}';
									const phNumberRangeTpl = '{{ $l == "ar" ? "أدخل رقماً بين" : "Enter a number between" }}' + ' ';
									const phNumberMinTpl = '{{ $l == "ar" ? "أدخل رقماً لا يقل عن" : "Enter a number from" }}' + ' ';
									const phAndText = ' ' + '{{ $l == "ar" ? "و" : "and" }}' + ' ';

									// Free-text fields carry no format to hint at, so the label —
									// the only thing that describes them — becomes an instruction.
									// A label-less field has nothing to say, so leave the box bare
									// rather than showing a dangling verb ("Enter").
									function phText(label) {
										return label ? phEnterTpl + label : '';
									}
									// Describe the accepted range so the bound is visible BEFORE the
									// user trips the validation message, not only after.
									function numberPlaceholder(options, minVal) {
										var min = Number(minVal);
										var hasMax = options && options.max !== undefined && options.max !== '';
										if (hasMax) { return phNumberRangeTpl + min + phAndText + options.max; }
										// Only worth stating when the floor is a real constraint;
										// min <= 0 is the unremarkable default.
										if (min > 0) { return phNumberMinTpl + min; }
										return phNumberPlain;
									}

									// Free-text input revealed when the user picks the "Other" option.
									// Hidden by default; toggled by the change handler; its value is
									// merged into the field on submit (see normalizeOtherFields).
									function otherWrapperHtml(fieldName, idFor) {
										return `<div class="other-wrapper mt-2 d-none" data-other-for="${fieldName}">`
											+ `<input type="text" class="form-control other-field" id="${idFor}_other" name="${fieldName}_other" placeholder="${otherValueText}" />`
											+ `<div class="invalid-feedback other-required-feedback">${otherRequiredText}</div>`
											+ `</div>`;
									}

									// The server already groups/orders questions into sections and
									// resolves each question's branching `logic` (GV wizard rework) —
									// render directly from response.sections instead of re-grouping
									// a flat field list here, so the client can never derive a
									// different section order than the server uses to recompute the
									// visited path on submit.
									var sections = response.sections || [];
									var totalSections = sections.length;

									$.each(sections, function(sectionIndex, section) {
										var sectionLogic = section.logic || 'continue';
										fieldsHtml += `<div class="section-wrapper mb-4" data-section-index="${sectionIndex}" data-section-logic="${sectionLogic}" style="${sectionIndex === 0 ? '' : 'display:none;'}">`;
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

										$.each(section.questions, function(key, value) {
											var builderType = value.type || 'short_answer';
											// "Dropdown" was retired as an authorable type (GV-3828);
											// existing dropdown fields render as multiple choice (radio).
											// Both store a single scalar answer — render-time coercion,
											// no data migration.
											if (builderType === 'dropdown') { builderType = 'multiple_choice'; }
											var isRequired = !!value.is_required;
											var requiredAttr = isRequired ? 'required' : '';
											var requiredStar = isRequired ? '<span class="text-danger ms-1">*</span>' : '';
											var label = value.label || '';
											var optionsList = value.options || [];
											var idFor = 'input-fld-' + value.id;
											// Only multiple_choice/dropdown questions carry branching
											// logic (an object map of option -> target), matching what
											// the admin form builder's logic panel authors.
											var logicMap = (value.logic && typeof value.logic === 'object') ? value.logic : {};

											fieldsHtml += `<div class="col-12"><div class="form-group">`;
											if (label) {
												fieldsHtml += `<label for="${idFor}" class="form-label fw-semibold mb-2">${label} ${requiredStar}</label>`;
											}

											switch (builderType) {
												case 'dropdown':
													fieldsHtml += `<select class="form-select" id="${idFor}" name="${value.field_name}" ${requiredAttr}>`;
													fieldsHtml += `<option value="">${chooseText}...</option>`;
													$.each(optionsList, function(i, option) {
														var optLabel = option === '__OTHER__' ? otherText : option;
														fieldsHtml += `<option value="${option}">${optLabel}</option>`;
													});
													fieldsHtml += `</select>`;
													if (optionsList.indexOf('__OTHER__') !== -1) {
														fieldsHtml += otherWrapperHtml(value.field_name, idFor);
													}
													break;

												case 'paragraph':
													var rows = (value.raw_options && value.raw_options.rows) ? value.raw_options.rows : 3;
													fieldsHtml += `<textarea class="form-control" id="${idFor}" name="${value.field_name}" rows="${rows}" placeholder="${phText(label)}" ${requiredAttr}></textarea>`;
													break;

												case 'multiple_choice':
													fieldsHtml += `<div class="options-group" data-group-name="${value.field_name}">`;
													$.each(optionsList, function(i, option) {
														var optLabel = option === '__OTHER__' ? otherText : option;
														var optLogic = logicMap[option] || 'continue';
														fieldsHtml += `<div class="form-check mb-2">`;
														fieldsHtml += `<input class="form-check-input question-logic" type="radio" name="${value.field_name}" id="${idFor}_${i}" value="${option}" data-logic="${optLogic}" ${requiredAttr} />`;
														fieldsHtml += `<label class="form-check-label" for="${idFor}_${i}">${optLabel}</label>`;
														fieldsHtml += `</div>`;
													});
													if (optionsList.indexOf('__OTHER__') !== -1) {
														fieldsHtml += otherWrapperHtml(value.field_name, idFor);
													}
													fieldsHtml += `</div>`;
													break;

												case 'checkboxes':
													fieldsHtml += `<div class="options-group" data-required-group="${isRequired ? '1' : '0'}" data-group-name="${value.field_name}">`;
													$.each(optionsList, function(i, option) {
														var optLabel = option === '__OTHER__' ? otherText : option;
														fieldsHtml += `<div class="form-check mb-2">`;
														fieldsHtml += `<input class="form-check-input" type="checkbox" name="${value.field_name}[]" id="${idFor}_${i}" value="${option}" />`;
														fieldsHtml += `<label class="form-check-label" for="${idFor}_${i}">${optLabel}</label>`;
														fieldsHtml += `</div>`;
													});
													if (optionsList.indexOf('__OTHER__') !== -1) {
														fieldsHtml += otherWrapperHtml(value.field_name, idFor);
													}
													fieldsHtml += `</div>`;
													break;

												case 'linear_scale':
													// GV-8349: render the ordered scale itself rather than a
													// dropdown, matching the portal form. A dropdown hides the
													// ordering and duplicates what multiple_choice already does.
													var scaleOpts = value.raw_options || {};
													var scaleMin = (scaleOpts.min !== undefined && scaleOpts.min !== '' && !isNaN(scaleOpts.min)) ? parseInt(scaleOpts.min, 10) : 1;
													var scaleMax = (scaleOpts.max !== undefined && scaleOpts.max !== '' && !isNaN(scaleOpts.max)) ? parseInt(scaleOpts.max, 10) : 5;
													if (scaleMax <= scaleMin) { scaleMin = 1; scaleMax = 5; }
													var scaleLow = (scaleOpts.label_low || '').trim();
													var scaleHigh = (scaleOpts.label_high || '').trim();
													// The `options-group` class is what wires this into the
													// page's group validation (validateGroup) and its
													// .invalid-feedback placement — radios are skipped by the
													// per-field loop, so without it a required scale is never
													// validated on the client.
													fieldsHtml += `<div class="options-group linear-scale-group d-flex align-items-center flex-wrap gap-2" data-required-group="${isRequired ? '1' : '0'}" data-group-name="${value.field_name}">`;
													if (scaleLow !== '') {
														fieldsHtml += `<span class="text-muted small me-1">${scaleLow}</span>`;
													}
													for (var i = scaleMin; i <= scaleMax; i++) {
														fieldsHtml += `<input type="radio" class="btn-check" name="${value.field_name}" id="${idFor}_${i}" value="${i}" autocomplete="off" ${requiredAttr} />`;
														fieldsHtml += `<label class="btn btn-outline-primary rounded-pill px-3" for="${idFor}_${i}">${i}</label>`;
													}
													if (scaleHigh !== '') {
														fieldsHtml += `<span class="text-muted small ms-1">${scaleHigh}</span>`;
													}
													fieldsHtml += `</div>`;
													break;

												case 'date':
													fieldsHtml += `<input type="text" class="form-control datepicker" id="${idFor}" name="${value.field_name}" placeholder="${phDate}" ${requiredAttr} />`;
													break;

												case 'time':
													fieldsHtml += `<input type="text" class="form-control timepicker" id="${idFor}" name="${value.field_name}" placeholder="${phTime}" ${requiredAttr} />`;
													break;

												case 'file':
													fieldsHtml += `<input type="file" class="form-control" id="${idFor}" name="${value.field_name}" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" ${requiredAttr} />`;
													break;

												case 'number':
													// Non-negative by default (GV-3831/GV-2814): when the field author
													// set no min, default to 0 so the native stepper can't go negative.
													var numMinVal = (value.raw_options && value.raw_options.min !== undefined && value.raw_options.min !== '') ? value.raw_options.min : 0;
													var numMin = `min="${numMinVal}"`;
													var numMax = (value.raw_options && value.raw_options.max !== undefined && value.raw_options.max !== '') ? `max="${value.raw_options.max}"` : '';
													var numStep = (value.raw_options && value.raw_options.step !== undefined && value.raw_options.step !== '') ? `step="${value.raw_options.step}"` : '';
													// GV-3679: `type="number"` natively accepts "e"/"E" (scientific
													// notation) plus "+"/"-". The keydown guard blocks them at entry
													// and the paste guard strips them (keydown never fires on paste).
													// A negative floor is still honoured: "-" is only blocked when
													// the effective min is >= 0.
													var allowNeg = Number(numMinVal) < 0 ? '1' : '0';
													fieldsHtml += `<input type="number" inputmode="decimal" class="form-control number-field" data-allow-negative="${allowNeg}" id="${idFor}" name="${value.field_name}" placeholder="${numberPlaceholder(value.raw_options, numMinVal)}" ${numMin} ${numMax} ${numStep} ${requiredAttr} />`;
													break;

												case 'email':
													fieldsHtml += `<input type="email" class="form-control" id="${idFor}" name="${value.field_name}" placeholder="${phEmail}" ${requiredAttr} />`;
													break;

												case 'url':
													fieldsHtml += `<input type="url" class="form-control" id="${idFor}" name="${value.field_name}" placeholder="${phUrl}" ${requiredAttr} />`;
													break;

												case 'short_answer':
												default:
													fieldsHtml += `<input type="text" class="form-control" id="${idFor}" name="${value.field_name}" placeholder="${phText(label)}" ${requiredAttr} />`;
													break;
											}

											fieldsHtml += `<div class="invalid-feedback">${label} ${reqText}</div>`;
											fieldsHtml += `</div></div>`;
										});

										fieldsHtml += `</div></div></div>`;

										var sectionCounterText = '{{ $l == "ar" ? "قسم" : "Section" }}';
										fieldsHtml += `
											<div class="section-footer d-flex justify-content-between align-items-center mt-3">
												<div class="text-muted small">${sectionCounterText} ${sectionIndex + 1} / ${totalSections}</div>
												<div class="d-flex gap-2">
													<button type="button" class="btn btn-outline-custom btn-prev ${sectionIndex === 0 ? 'd-none' : ''}">
														<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'left' : 'left' }} me-2"></i>
														{{ $l == 'ar' ? 'السابق' : 'Back' }}
													</button>
													<button type="button" class="btn btn-primary-custom btn-next">
														<span class="btn-next-text">{{ $l == 'ar' ? 'التالي' : 'Next' }}</span>
														<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'right' : 'right' }} ms-2"></i>
													</button>
												</div>
											</div>
										`;
										fieldsHtml += `</div>`;
									});

									fieldsHtml += `
										<div class="section-wrapper" id="submit-step" style="display:none;">
											<div class="card border shadow-sm"><div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
												<div>
													<h5 class="mb-1">{{ $l == 'ar' ? 'جاهز للإرسال؟' : 'Ready to submit?' }}</h5>
													<p class="text-muted mb-0">{{ $l == 'ar' ? 'يرجى مراجعة إجاباتك قبل الإرسال.' : 'Please review your answers before submitting.' }}</p>
												</div>
												<div class="d-flex gap-2">
													<button type="button" class="btn btn-outline-custom btn-prev-submit">
														<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'left' : 'left' }} me-2"></i>
														{{ $l == 'ar' ? 'السابق' : 'Back' }}
													</button>
													<button id="join_btn" type="button" class="btn btn-primary-custom w-px-120">
														{{ $l == 'ar' ? 'انضم' : 'Join' }}
														<i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'right' : 'right' }} ms-2"></i>
													</button>
												</div>
											</div></div>
										</div>
									`;

									$("#register-fields").html(fieldsHtml);
									datePicker();
									initApplicationWizard();
									stepper.to(3);
								} else {
									releaseAlert('danger', {
									  title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
									  message: response.message,
									  confirmColor: 'primary'
									});
								}
							} else {
								releaseAlert('danger', {
								  title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
								  message: response.message,
								  confirmColor: 'primary'
								});
							}
						},
						error: function(error) {
							console.log(error);
							// A 422 from validate_email carries the field validation
							// error (e.g. email:dns rejecting an unresolvable domain).
							// Surface it inline on the email field so the user can
							// correct it here, at the Email step.
							var validationMsg = error && error.responseJSON && error.responseJSON.errors
								&& error.responseJSON.errors.email && error.responseJSON.errors.email[0];
							if (error && error.status === 422 && validationMsg) {
								$('#initial-email-feedback').html(validationMsg);
								$('#initial-email').addClass('is-invalid');
								return;
							}
							releaseAlert('danger', {
							  title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
							  message: 'Error validating email. Please try again.',
							  confirmColor: 'primary'
							});
						},
						// Always release the lock: this step advances the stepper on
						// success, but "Previous" brings the user back to this button,
						// so it must be usable again either way.
						complete: function() {
							unlockButton($btn);
						}
					});
				} else {
					$('#initial-email-feedback').html('{{ $l == "ar" ? "يرجى إدخال بريد إلكتروني صحيح." : "Please enter a valid email address." }}');
					$('#initial-email').addClass('is-invalid');
					return;
				}
			}
		});

		$(document).on('click', '#login-btn', function(e) {
			e.preventDefault();
			const $btn = $(this);
			if ($btn.data('locked')) { return; }
			let email = $('#login-email').val();
			let password = $('#login-password').val();

			if (password.trim() == '') {
				$('#login-password-feedback').html('{{ $l == "ar" ? "كلمة المرور مطلوبة." : "Password is required." }}');
				$('#login-password').addClass('is-invalid');
				return;
			}

			lockButton($btn);
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			$.ajax({
				type: "POST",
				url: "/{{ $org_slug }}/program/{{ $program_slug }}/authenticate",
				data: { email: email, password: password },
				success: function(response) {
					if (response.status) {
						// Navigating away — deliberately stay locked so the button
						// cannot be re-fired during the page transition.
						window.location.href = response.url;
					} else {
						unlockButton($btn);
						releaseAlert('danger', {
						  title: '{{ $l == "ar" ? "خطأ" : "Authentication Failed" }}',
						  message: response.message,
						  confirmText: '{{ $l == "ar" ? "حسناً" : "OK" }}',
						  confirmColor: 'primary'
						});
					}
				},
				error: function(error) {
					console.log(error);
					unlockButton($btn);
					releaseAlert('danger', {
					  title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
					  message: '{{ $l == "ar" ? "حدث خطأ أثناء المصادقة. يرجى المحاولة مرة أخرى." : "An error occurred during authentication. Please try again." }}',
					  confirmText: '{{ $l == "ar" ? "حسناً" : "OK" }}',
					  confirmColor: 'primary'
					});
				}
			});
		});

		$(document).on('keyup', '#initial-email', function() {
			$('#initial-email').removeClass('is-invalid');
			$('#initial-email-feedback').html('');
		});

		function validateEmail(email) {
			const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return re.test(String(email).toLowerCase());
		}

		// ---------------------------------------------------------------------
		// GV-3679: real-time validation.
		//
		// One `validateField` is the single source of truth for BOTH the live
		// (input/change/blur) pass and the #join_btn submit pass. Keeping them
		// as one function is the point: the earlier split meant submit could
		// reject something the live pass had just cleared. It returns an error
		// message string, or null when the field is acceptable.
		//
		// Live feedback is deliberately gentle-first: a field the user has not
		// finished with yet is only marked once it has been blurred ("touched"),
		// so errors do not flash on the very first keystroke of an empty box.
		// Once a field is already marked invalid, it revalidates on every input
		// so the red state clears the instant the value becomes good.
		// ---------------------------------------------------------------------
		function fieldFeedbackEl($field) {
			// Every rendered field has a sibling .invalid-feedback; grouped
			// (radio/checkbox) fields carry it after the .options-group wrapper.
			const $group = $field.closest('.options-group');
			if ($group.length) {
				const $after = $group.nextAll('.invalid-feedback').first();
				if ($after.length) { return $after; }
			}
			const $sibling = $field.nextAll('.invalid-feedback').first();
			if ($sibling.length) { return $sibling; }
			return $field.closest('.form-group').find('.invalid-feedback').first();
		}

		function validateField($field) {
			const el = $field[0];
			if (!el) { return null; }
			const type = ($field.attr('type') || '').toLowerCase();
			const isRequired = $field.is('[required]');
			const rawVal = $field.val();
			const value = (rawVal === null || rawVal === undefined) ? '' : rawVal.toString();
			const trimmed = value.trim();

			if (type === 'file') {
				if (isRequired && (!el.files || el.files.length === 0)) { return requiredText; }
				return null;
			}

			// MUST precede the empty check: a number input holding un-parseable
			// text (e.g. "12e", "--") reports value === "", which would otherwise
			// be reported as "required" — or silently accepted when optional.
			// badInput is the only signal that the box has junk in it.
			if (type === 'number' && el.validity && el.validity.badInput) {
				return numericText;
			}

			if (!trimmed) {
				return isRequired ? ($field.attr('data-required-message') || requiredText) : null;
			}

			// GV-10440 (ISF): optional length / format / phone checks, set per field through
			// minlength, data-pattern(-message), data-length-message and an intl-tel-input instance.
			const minLength = parseInt($field.attr('minlength'), 10);
			if (minLength && trimmed.length < minLength) { return $field.attr('data-length-message') || requiredText; }
			const pattern = $field.attr('data-pattern');
			if (pattern && !new RegExp(pattern, 'u').test(trimmed)) { return $field.attr('data-pattern-message') || requiredText; }
			const iti = $field.data('iti');
			if (iti && !isValidPhone(iti, trimmed)) { return $field.attr('data-invalid-message') || requiredText; }

			if (type === 'number') {
				const num = Number(trimmed);
				if (isNaN(num)) { return numericText; }
				const minAttr = $field.attr('min');
				const maxAttr = $field.attr('max');
				const min = (minAttr !== undefined && minAttr !== '') ? Number(minAttr) : 0;
				const max = (maxAttr !== undefined && maxAttr !== '') ? Number(maxAttr) : null;
				if (num < min) { return numberMinMessage(min); }
				if (max !== null && num > max) { return numberMaxTplText + max; }
				return null;
			}

			if (type === 'email') {
				if (!/.+@.+\..+/.test(trimmed)) { return emailFormatText; }
				return null;
			}

			if (type === 'url') {
				// Mirror the browser's own `type="url"` verdict rather than
				// inventing a second, stricter rule.
				if (el.validity && el.validity.typeMismatch) { return urlFormatText; }
				return null;
			}

			return null;
		}

		// Same rule as the Founder Profile (app-edit-profile.js buildPhoneValidator).
		function isValidPhone(iti, value) {
			if (!/^[\d\s\-\(\)]+$/.test(value)) { return false; }
			if (iti.getSelectedCountryData().iso2 === 'eg') {
				const clean = value.replace(/[\s\-\(\)]/g, '');
				return clean.startsWith('0')
					? (clean.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(clean))
					: (clean.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(clean));
			}
			return iti.isValidNumber();
		}

		function applyFieldState($field, message) {
			const $feedback = fieldFeedbackEl($field);
			if (message) {
				$field.addClass('is-invalid');
				if ($feedback.length) { $feedback.html(message); }
			} else {
				$field.removeClass('is-invalid');
			}
			return !message;
		}

		// Required radio / checkbox groups: validated as a unit, marked on every
		// option so the whole group reads as invalid.
		function validateGroup($group) {
			const isRequired = $group.data('required-group') === 1
				|| $group.data('required-group') === '1'
				|| $group.find('input[required]').length > 0;
			if (!isRequired) { return true; }
			const $inputs = $group.find('input[type="radio"], input[type="checkbox"]');
			const ok = $inputs.filter(':checked').length > 0;
			const $feedback = $group.nextAll('.invalid-feedback').first();
			if (ok) {
				$inputs.removeClass('is-invalid');
			} else {
				$inputs.addClass('is-invalid');
				if ($feedback.length) { $feedback.html(chooseOneText); }
			}
			return ok;
		}

		// "Other" free-text is mandatory whenever its wrapper is visible.
		function validateOtherWrapper($wrapper) {
			const $input = $wrapper.find('.other-field');
			if ($wrapper.hasClass('d-none')) {
				$input.removeClass('is-invalid');
				return true;
			}
			const val = ($input.val() || '').trim();
			if (!val) {
				$input.addClass('is-invalid');
				return false;
			}
			$input.removeClass('is-invalid');
			return true;
		}

		const TOUCHED = 'gv-touched';

		function liveValidate($field) {
			// Only speak up once the user has left the field, or if we are already
			// showing an error on it (so corrections clear immediately).
			if (!$field.data(TOUCHED) && !$field.hasClass('is-invalid')) { return; }
			applyFieldState($field, validateField($field));
		}

		// Blur marks the field as "touched" and validates it for the first time.
		$(document).on('blur', '#register-form input, #register-form select, #register-form textarea', function() {
			const $field = $(this);
			const type = ($field.attr('type') || '').toLowerCase();
			if (type === 'radio' || type === 'checkbox') { return; }
			$field.data(TOUCHED, true);
			applyFieldState($field, validateField($field));
		});

		// Typing revalidates a touched/invalid field so the error disappears as
		// soon as the value becomes valid.
		$(document).on('input', '#register-form input, #register-form select, #register-form textarea', function() {
			liveValidate($(this));
		});

		// Selecting an option validates immediately — there is no "still typing"
		// state for a choice, so instant feedback is correct here.
		$(document).on('change', '#register-form select', function() {
			const $field = $(this);
			$field.data(TOUCHED, true);
			applyFieldState($field, validateField($field));
		});

		$(document).on('change', '#register-form input[type="radio"], #register-form input[type="checkbox"]', function() {
			const $group = $(this).closest('.options-group');
			if ($group.length) { validateGroup($group); }
		});

		$(document).on('change', '#register-form input[type="file"]', function() {
			const $field = $(this);
			$field.data(TOUCHED, true);
			applyFieldState($field, validateField($field));
		});

		$(document).on('input blur', '#register-form .other-field', function() {
			const $wrapper = $(this).closest('.other-wrapper');
			if ($wrapper.length && ($(this).hasClass('is-invalid') || $(this).data(TOUCHED))) {
				validateOtherWrapper($wrapper);
			}
		});
		$(document).on('blur', '#register-form .other-field', function() {
			$(this).data(TOUCHED, true);
		});

		// The two static base fields live outside the dynamic renderer but should
		// behave identically.
		$(document).on('blur', '#register-first-name, #register-last-name', function() {
			$(this).data(TOUCHED, true);
			applyFieldState($(this), validateField($(this)));
		});

		// -- Number input hygiene (GV-3679) -----------------------------------
		// `type="number"` accepts "e"/"E" (scientific notation) and "+"/"-" by
		// design. Block them on keydown; "-" only when the field's floor is >= 0.
		$(document).on('keydown', '#register-form input[type="number"]', function(e) {
			// Never swallow shortcuts (Ctrl+V, Ctrl+A, Cmd+C, Ctrl+E, ...) — the
			// guard is only meant to stop literal character entry.
			if (e.ctrlKey || e.metaKey || e.altKey) { return; }

			// Navigation / editing keys are always allowed. Anything with a
			// multi-character e.key name (Backspace, ArrowUp, Tab, F5, Enter...)
			// is a control key, never a typed character.
			if (e.key === undefined || e.key.length !== 1) { return; }

			// ALLOWLIST, not a denylist. `type="number"` silently discards every
			// letter — the browser refuses it from .value but still PAINTS it in
			// the box, so the user sees "abcdef" while the field reads "". A
			// denylist of e/E/+/- left every other letter in that broken state
			// (GV-3679 reopen). Permit only what can build a number and reject
			// the rest outright, so nothing unusable ever appears.
			const allowNeg = $(this).data('allow-negative') === 1 || $(this).data('allow-negative') === '1';
			const isDigit = e.key >= '0' && e.key <= '9';
			// "." only — a number input rejects a comma-containing value outright
			// (it reads back as empty), so letting one be typed would blank the
			// field silently. "-" is allowed only where the floor is negative.
			const isSeparator = e.key === '.';
			const isMinus = e.key === '-' && allowNeg;

			if (!isDigit && !isSeparator && !isMinus) {
				e.preventDefault();
			}
		});

		// keydown never fires for a paste or a drop, so strip the same characters
		// from pasted content instead of letting the browser blank the field.
		$(document).on('paste drop', '#register-form input[type="number"]', function(e) {
			const $field = $(this);
			const allowNeg = $field.data('allow-negative') === 1 || $field.data('allow-negative') === '1';
			const ev = e.originalEvent || e;
			const clip = ev.clipboardData || ev.dataTransfer;
			if (!clip) { return; }
			const text = clip.getData('text') || '';
			// Mirror the keydown allowlist: keep digits and the sign/decimal point
			// only, so a pasted "12abc" cannot land the field in the same
			// invisible-text state that plain typing used to produce.
			//
			// Commas are DROPPED rather than kept: a number input rejects
			// "1,299.00" outright (its .value reads back empty), so preserving
			// the comma would leave a silently blank box. Dropping it yields
			// "1299.00", which is what a pasted thousands-separated figure means.
			let cleaned = text.replace(/,/g, '').replace(/[^0-9.-]/g, '');
			cleaned = allowNeg ? cleaned.replace(/(?!^)-/g, '') : cleaned.replace(/-/g, '');
			if (cleaned !== text) {
				e.preventDefault();
				$field.val(cleaned).trigger('input');
			}
		});

		// Show/hide the "Other" free-text input based on whether __OTHER__ is
		// currently selected for that field (dropdown, radio group, checkbox group).
		function toggleOtherWrapper(fieldName) {
			const $form = $('#register-form');
			const $wrapper = $form.find(`.other-wrapper[data-other-for="${fieldName}"]`);
			if (!$wrapper.length) { return; }

			let show = false;
			const $select = $form.find(`select[name="${fieldName}"]`);
			if ($select.length) {
				show = $select.val() === '__OTHER__';
			} else {
				// radio uses name="field_x", checkbox uses name="field_x[]"
				show = $form.find(`input[name="${fieldName}"]:checked[value="__OTHER__"], input[name="${fieldName}[]"]:checked[value="__OTHER__"]`).length > 0;
			}

			$wrapper.toggleClass('d-none', !show);
			if (!show) {
				$wrapper.find('.other-field').val('').removeClass('is-invalid');
			}
		}

		$(document).on('change', '#register-form select, #register-form input[type="radio"], #register-form input[type="checkbox"]', function() {
			let fieldName = $(this).attr('name') || '';
			fieldName = fieldName.replace(/\[\]$/, ''); // checkbox groups post as field_x[]
			toggleOtherWrapper(fieldName);
		});

		$(document).on('click', '#join_btn', function(e) {
			e.preventDefault();
			const $btn = $(this);
			// Guard before any work: a second click while the first POST is in
			// flight was creating duplicate registrations.
			if ($btn.data('locked')) { return; }
			let $form = $('#register-form');
			let isValid = true;

			$form.find('.is-invalid').removeClass('is-invalid');

			// Sections the founder never actually reached (skipped by a branching
			// answer, or beyond the resolved path) must contribute neither
			// validation errors nor answers. Disabling their inputs makes both
			// the validation loops below (which already skip `:disabled`) and
			// the native FormData(form) construction further down exclude them
			// automatically — no separate field-stripping logic needed.
			let $allSections = $form.find('.section-wrapper[data-section-index]');
			let $skippedInputs = $allSections
				.filter(function() { return sectionHistory.indexOf(parseInt(this.dataset.sectionIndex, 10)) === -1; })
				.find('input, select, textarea');
			let previouslyDisabled = $skippedInputs.map(function() { return this.disabled; }).get();
			$skippedInputs.prop('disabled', true);

			function isInVisitedSection(el) {
				const sec = el.closest('.section-wrapper[data-section-index]');
				if (!sec) { return true; }
				return sectionHistory.indexOf(parseInt(sec.dataset.sectionIndex, 10)) !== -1;
			}

			// Submit runs the SAME per-field rules as the live pass (validateField),
			// so the two layers can never disagree. Grouped inputs (radio /
			// checkbox) and the "Other" free-text boxes are unit-validated
			// separately, since neither maps onto a single input's value.
			$form.find('input, select, textarea').each(function() {
				const $field = $(this);
				const type = ($field.attr('type') || '').toLowerCase();
				if (type === 'radio' || type === 'checkbox') { return; }
				if ($field.is(':disabled') || !$field.is(':visible')) { return; }
				if ($field.hasClass('other-field')) { return; }

				// Mark everything touched: after an attempted submit the user has
				// "finished" with every field, so all errors should now be visible.
				$field.data(TOUCHED, true);
				if (!applyFieldState($field, validateField($field))) {
					isValid = false;
				}
			});

			// Required radio / checkbox groups: at least one option must be chosen.
			$form.find('.options-group').each(function() {
				const $group = $(this);
				if (!$group.is(':visible')) { return; }
				if (!validateGroup($group)) { isValid = false; }
			});

			// When "Other" is selected, its free-text input must be filled in.
			$form.find('.other-wrapper').each(function() {
				if (!isInVisitedSection(this)) { return; }
				if (!validateOtherWrapper($(this))) { isValid = false; }
			});

			if (!isValid) {
				$skippedInputs.each(function(i) { this.disabled = previouslyDisabled[i]; });
				releaseAlert('warning', {
				  title: '{{ $l == "ar" ? "نموذج غير مكتمل" : "Incomplete Form" }}',
				  message: '{{ $l == "ar" ? "يرجى ملء جميع الحقول المطلوبة." : "Please fill out all required fields." }}',
				  confirmText: "{{ __('admin.OK') }}",
				  confirmColor: 'primary',
				  hideCancel: true
				});
				return;
			}
			// Build the payload, replacing any "__OTHER__" marker with the typed
			// free-text value so the server stores the real answer (matches portal).
			// $skippedInputs are still disabled here on purpose — FormData(form)
			// natively omits disabled controls, which is what excludes answers
			// from un-visited sections from the submission.
			let formData = new FormData($form[0]);
			$skippedInputs.each(function(i) { this.disabled = previouslyDisabled[i]; });

			$form.find('.other-wrapper').each(function() {
				// Inputs were just re-enabled above, so this must re-check the
				// visited path itself rather than rely on :disabled — otherwise
				// a skipped section's live DOM value could get merged back into
				// formData below, undoing the exclusion FormData(form) just did.
				if (!isInVisitedSection(this)) { return; }
				const $wrapper = $(this);
				const fieldName = $wrapper.data('other-for');
				const otherVal = ($wrapper.find('.other-field').val() || '').trim();

				// The helper "field_x_other" input is never sent to the server.
				formData.delete(fieldName + '_other');

				const $select = $form.find(`select[name="${fieldName}"]`);
				const $checkboxes = $form.find(`input[name="${fieldName}[]"]`);

				if ($select.length) {
					if ($select.val() === '__OTHER__' && otherVal) {
						formData.set(fieldName, otherVal);
					}
				} else if ($checkboxes.length) {
					// Rebuild the checkbox array, swapping __OTHER__ for the typed value.
					const checked = $checkboxes.filter(':checked').map(function() { return this.value; }).get();
					if (checked.indexOf('__OTHER__') !== -1) {
						formData.delete(fieldName + '[]');
						checked.forEach(function(v) {
							formData.append(fieldName + '[]', v === '__OTHER__' ? otherVal : v);
						});
					}
				} else {
					// radio: name="field_x"
					if (formData.get(fieldName) === '__OTHER__' && otherVal) {
						formData.set(fieldName, otherVal);
					}
				}
			});

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			formData.append('register-email', $('#register-email').val());
			// Lock only once the payload is built and validation has passed —
			// a rejected submit above leaves the button usable.
			lockButton($btn);
			$.ajax({
				type: "POST",
				url: "/{{ $org_slug }}/program/{{ $program_slug }}/register",
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.status) {
						// Registration succeeded — stay locked. The stepper moves on
						// (or we navigate away), and re-enabling would re-open the
						// duplicate-registration window this guard exists to close.
						@if($VFEmail)
							stepper.next();
						@else
							window.location.href = response.url;
						@endif
					} else {
						unlockButton($btn);
						releaseAlert('danger', {
						  title: '{{ $l == "ar" ? "خطأ في التسجيل" : "Registration Error" }}',
						  message: '{{ $l == "ar" ? "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى." : "An error occurred during registration. Please try again." }}',
						  confirmText: '{{ $l == "ar" ? "حسناً" : "OK" }}',
						  confirmColor: 'primary'
						});
					}
				},
				error: function(xhr, status, error) {
					console.log(error);
					// The request failed, so nothing was created — let the user retry.
					unlockButton($btn);
					if( xhr.status == 422 )
					{
						var response = xhr.responseJSON || {};
						var messagesList = [];

						// Base-field errors use Laravel's default shape: { errors: { field: [msg] } }
						if (response.errors && typeof response.errors === 'object') {
							$.each(response.errors, function(field, messages) {
								var fieldName = field.replace(/\./g, '_');
								var $input = $(`#${fieldName}`);
								$input.addClass('is-invalid');
								// Not .next(): the phone input is wrapped by intl-tel-input.
								fieldFeedbackEl($input).html(messages[0]);
								messagesList.push(messages[0]);
							});
						}

						// Dynamic form-field errors use { error: [ "msg", ... ] } (a flat array).
						if (Array.isArray(response.error)) {
							messagesList = messagesList.concat(response.error);
						}

						swal.fire({
							title: '{{ $l == "ar" ? "خطأ في التحقق من البيانات" : "Validation Error" }}',
							html: messagesList.length
								? '<ul class="text-start mb-0">' + messagesList.map(function(m){ return '<li>' + m + '</li>'; }).join('') + '</ul>'
								: '{{ $l == "ar" ? "يرجى تصحيح الأخطاء في النموذج." : "Please correct the errors in the form." }}',
							icon: 'warning',
							showCancelButton: false,
							showConfirmButton: true,
							confirmButtonText: "{{ __('admin.OK') }}",
							customClass: {
								confirmButton: 'btn btn-primary',
							},
							buttonsStyling: false,
						});
					}
					else
					{
						releaseAlert('danger', {
						  title: '{{ $l == "ar" ? "خطأ" : "Error" }}',
						  message: '{{ $l == "ar" ? "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى." : "An error occurred during registration. Please try again." }}',
						  confirmText: "{{ __('admin.OK') }}",
						  confirmColor: 'primary'
						});
					}
				}
			});
		});

		function datePicker() {
			// Match the portal form's flatpickr configuration so date/time fields
			// render an actual picker (not a plain text box) and store consistent values.
			flatpickr('.datepicker', { dateFormat: 'Y-m-d', allowInput: true });
			flatpickr('.timepicker', { enableTime: true, noCalendar: true, dateFormat: 'H:i', allowInput: true });
		}
	</script>
	@if ($educationFields)
	<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
	<script src="/vuexy/assets/js/isf-education-fields.js?v={{ @filemtime(public_path('vuexy/assets/js/isf-education-fields.js')) ?: 1 }}"></script>
	<script>
		// GV-10440 (ISF): mobile number widget (as on the Founder Profile) and the dependent education fields.
		$(function () {
			const phone = document.getElementById('phone');
			const iti = window.intlTelInput(phone, {
				preferredCountries: ['eg', 'sa', 'ae', 'kw', 'qa'],
				initialCountry: 'eg',
				separateDialCode: true,
				autoPlaceholder: 'polite',
				utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.8/js/utils.js',
			});
			$(phone).data('iti', iti);
			const syncDialCode = () => $('#country_code').val(iti.getSelectedCountryData().dialCode || '20');
			syncDialCode();
			phone.addEventListener('countrychange', function () {
				syncDialCode();
				if ($(phone).hasClass('is-invalid')) { applyFieldState($(phone), validateField($(phone))); }
			});

			window.IsfEducationFields.init('#register-form');
		});
	</script>
	@endif
</body>
</html>
