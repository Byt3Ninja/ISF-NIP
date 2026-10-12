<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	class="light-style" data-theme="theme-default" data-assets-path="/vuexy/assets/"
	data-template="vertical-menu-template" data-style="light">

<head>
	@include('admin.includes.head')

	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

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
			transform: translateY(-5px);
		}

		.hero-gradient {
			background: radial-gradient(circle at 20% 30%, {{ $program->start_color }} 0%, transparent 50%),
						radial-gradient(circle at 80% 70%, {{ $program->end_color }} 0%, transparent 50%);
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

		.nav-tabs-custom { border: none; }
		.nav-tabs-custom .nav-link {
			border: none;
			color: var(--on-surface-variant);
			border-bottom: 3px solid transparent;
			margin: 0 10px;
			background: transparent;
		}
		.nav-tabs-custom .nav-link.active {
			background: transparent !important;
			color: var(--primary) !important;
			border-bottom-color: var(--primary);
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

		.footer-custom {
			background-color: #f1f5f9;
			border-top: 1px solid var(--outline);
			padding: 80px 0 30px;
		}

		.navbar-custom .dropdown-menu {
			background: rgba(255,255,255,0.95);
			backdrop-filter: blur(15px);
			border: 1px solid var(--outline);
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
// GV-9566: ISO-8601 instants with offset, so the browser does not read the
// window in its own zone.
$endDateForCountdown = $program->registration_ends_at?->toIso8601String();
$startDateForCountdown = $program->registration_starts_at?->toIso8601String();
@endphp

<body>
	<nav class="navbar navbar-expand-md navbar-custom fixed-top py-3">
		<div class="container">
			<a class="navbar-brand fs-3 d-flex align-items-center gap-2" href="/dashboard">
				<i class="ti ti-planet ti-lg text-primary"></i>
				{{ $program->org }}
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav mx-auto">
					@foreach( $pages as $tab )
					<li class="nav-item">
						<a class="nav-link {{ $loop->first ? 'active' : '' }}" href="#{{ $tab->code }}">{{ $tab->title }}</a>
					</li>
					@endforeach
					@if( $program->announced )
					<li class="nav-item">
						<a class="nav-link" href="#results-winners">{{ $l == 'en' ? 'Winners' : 'الفائزون' }}</a>
					</li>
					@endif
				</ul>
				<div class="d-flex align-items-center gap-2">
					@if ($isLoggedIn)
					<div class="dropdown ms-1">
						<a class="dropdown-toggle hide-arrow p-0 d-block" href="javascript:void(0);" data-bs-toggle="dropdown">
							<div class="avatar avatar-online" style="width:36px;height:36px;">
								@if (auth()->user()->image_url)
								<img class="rounded-circle" src="{{ auth()->user()->image_url }}"
									onerror="this.src='/images/user-placeholder.jpeg'" width="36" height="36" />
								@else
								<img class="rounded-circle" src="/images/user-placeholder.jpeg" alt="avatar" width="36" height="36" />
								@endif
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li>
								<a class="dropdown-item mt-0" href="/dashboard/show/myProfile">
									<div class="d-flex align-items-center gap-2">
										<div class="avatar avatar-online" style="width:36px;height:36px;">
											@if (auth()->user()->image_url)
											<img class="rounded-circle" src="{{ auth()->user()->image_url }}"
												onerror="this.src='/images/user-placeholder.jpeg'" width="36" height="36" />
											@else
											<img class="rounded-circle" src="/images/user-placeholder.jpeg" alt="avatar" width="36" height="36" />
											@endif
										</div>
										<div>
											<h6 class="mb-0">{{ Auth::user()->name }}</h6>
											<small style="color:var(--on-surface-variant);">{{ Auth::user()->title }}</small>
										</div>
									</div>
								</a>
							</li>
							<li><hr class="dropdown-divider"></li>
							<li><a class="dropdown-item" href="/dashboard"><i class="ti ti-dashboard me-2"></i>{{ __('admin.Dashboard') }}</a></li>
							<li><a class="dropdown-item" href="/dashboard/show/myProfile"><i class="ti ti-user me-2"></i>{{ __('admin.Profile') }}</a></li>
							<li><a class="dropdown-item" href="/dashboard/myProfile"><i class="ti ti-settings me-2"></i>{{$l=='ar'?'الاعدادات':'Settings'}}</a></li>
							<li><a class="dropdown-item" href="/dashboard/documentation"><i class="ti ti-question-mark me-2"></i>FAQ</a></li>
							<li><hr class="dropdown-divider"></li>
							<li>
								<a class="dropdown-item text-danger" href="{{ route('logout') }}"
									onclick="event.preventDefault();document.getElementById('logout-form').submit();">
									<i class="ti ti-logout me-2"></i>{{ __('admin.Logout') }}
								</a>
								<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
							</li>
						</ul>
					</div>
					@else
					<button class="btn btn-sm fw-semibold" onclick="location.href='/login'" style="color:var(--on-surface);">{{ $l == 'en' ? 'Login' : 'تسجيل الدخول' }}</button>
					<button class="btn btn-primary-custom btn-sm" onclick="location.href='/register'">{{ $l == 'en' ? 'Register' : 'تسجيل' }}</button>
					@endif
					<a href="/lang/{{ $l == 'ar' ? 'en' : 'ar' }}" class="btn btn-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
						<span class="material-symbols-outlined fs-5">language</span>
					</a>
				</div>
			</div>
		</div>
	</nav>

	<div class="page-container">
		<main>
			<section class="hero-gradient text-center" style="padding-top:140px;padding-bottom:80px;">
				<div class="container">
					<div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4"
						style="background:white;border:1px solid var(--outline);box-shadow:0 4px 6px rgba(0,0,0,0.02);">
						<span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
						<span class="small fw-bold text-uppercase text-primary" style="font-size:11px;letter-spacing:1px;">
							{{ $l == 'en' ? 'Registration Open' : 'التسجيل مفتوح' }}
						</span>
					</div>

					<h1 class="display-2 fw-bolder mb-3">{{ $program->title }}</h1>
					<p class="lead mx-auto mb-5" style="max-width:700px;color:var(--on-surface-variant);">
						{{ $program->description }}
					</p>

					<div class="d-flex align-items-center justify-content-center gap-3 flex-wrap mb-5">
						<span class="badge bg-label-primary rounded-pill px-3 py-2">
							<i class="ti ti-calendar-event me-1"></i>{{ $program->ranges }}
						</span>
						<span class="badge bg-label-secondary rounded-pill px-3 py-2">
							<i class="ti ti-building me-1"></i>{{ $program->org }}
						</span>
					</div>

					<div class="row g-4 justify-content-center mb-5 mx-auto" style="max-width:900px;" id="countdown-timer">
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

					@if( $program->registration_starts_at && $program->registration_ends_at && now()->gt( $program->registration_starts_at ) && now()->lt( $program->registration_ends_at ) )
					<div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
						<a href="{{ URL::to( $org_slug . '/program/' . $program_slug . '/apply') }}" class="btn btn-primary-custom px-5 py-3 fs-5">
							<i class="ti ti-send me-2"></i>{{ $l == 'en' ? 'Apply Now' : 'تقدم الآن' }}
						</a>
					</div>
					@endif
				</div>
			</section>

			@if( $pages->count() > 0 )
			<section class="py-5 bg-white border-top border-bottom">
				<div class="container py-4">
					<h2 class="text-center mb-5 fw-bold display-6">{{ $l == 'en' ? 'Competition Details' : 'تفاصيل المسابقة' }}</h2>

					<ul class="nav nav-tabs-custom justify-content-center mb-5" id="detailsTab">
						@foreach( $pages as $tab )
						<li class="nav-item">
							<button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#{{ $tab->code }}">
								{{ $tab->title }}
							</button>
						</li>
						@endforeach
						@if( $program->announced )
						<li class="nav-item">
							<button class="nav-link" data-bs-toggle="tab" data-bs-target="#results-winners">
								<i class="ti ti-trophy me-1"></i>{{ $l == 'en' ? 'Winners' : 'الفائزون' }}
							</button>
						</li>
						@endif
					</ul>

					<div class="tab-content">
						@foreach( $pages as $tab )
						<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab->code }}">
							<div class="glass-card p-4 p-lg-5 mx-auto" style="max-width:900px;">
								{!! $tab->content !!}
							</div>
						</div>
						@endforeach

						@if( $program->announced )
						<div class="tab-pane fade" id="results-winners">
							@if( $program->visibility == 'public' )
							<div class="glass-card p-4 p-lg-5 mx-auto" style="max-width:1100px;">
								<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-5">
									<div>
										<h4 class="fw-bold mb-1">
											<i class="ti ti-trophy text-primary me-2"></i>
											{{ $l == 'en' ? 'Competition Winners' : 'الفائزون في المسابقة' }}
										</h4>
										<p style="color:var(--on-surface-variant);" class="mb-0">
											{{ $l == 'en' ? 'Innovative solutions that stood out from the competition' : 'الحلول المبتكرة التي تميزت في المسابقة' }}
										</p>
									</div>
									<div class="d-flex align-items-center gap-2 flex-wrap">
										<span class="small fw-semibold" style="color:var(--on-surface-variant);">{{ $l == 'en' ? 'Share:' : 'شارك:' }}</span>
										<a class="btn btn-sm rounded-pill d-flex align-items-center gap-1 program-share-btn"
											style="background-color:#3b5998;color:#fff;"
											href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
											target="_blank" data-platform="facebook">
											<i class="ti ti-brand-facebook"></i> Facebook
										</a>
										<a class="btn btn-sm rounded-pill d-flex align-items-center gap-1 program-share-btn"
											style="background-color:#0077B5;color:#fff;"
											href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
											target="_blank" data-platform="linkedin">
											<i class="ti ti-brand-linkedin"></i> LinkedIn
										</a>
										<a class="btn btn-sm rounded-pill d-flex align-items-center gap-1 program-share-btn btn-dark"
											href="https://twitter.com/intent/tweet?text={{ urlencode($program->title . ' - Results Announced! ' . url()->current()) }}"
											target="_blank" data-platform="twitter">
											<i class="ti ti-brand-x"></i> Tweet
										</a>
										<button class="btn btn-sm rounded-pill d-flex align-items-center gap-1 btn-outline-secondary"
											type="button" onclick="copyLink('{{url()->current()}}')" id="copy-link-btn">
											<i class="ti ti-link"></i> {{ $l == 'en' ? 'Copy Link' : 'نسخ الرابط' }}
										</button>
									</div>
								</div>
								@include('admin.hackathon.partials.winners-tab', ['winners' => $winnersData, 'currentHackathon' => $currentHackathon])
							</div>
							@else
							<div class="glass-card p-5 mx-auto text-center" style="max-width:600px;">
								<div class="mb-4">
									<span class="material-symbols-outlined" style="font-size:48px;color:var(--on-surface-variant);">lock</span>
								</div>
								<h4 class="fw-bold mb-2">{{ $l == 'en' ? 'Results Restricted' : 'النتائج مقيدة' }}</h4>
								<p style="color:var(--on-surface-variant);" class="mb-4">
									{{ $l == 'en'
										? 'This program results have been announced, please login to view the winners and detailed results.'
										: 'تم الإعلان عن نتائج هذا البرنامج، يرجى تسجيل الدخول لمشاهدة الفائزين والنتائج التفصيلية.'
									}}
								</p>
								<div class="d-flex justify-content-center gap-3">
									<a href="/login" class="btn btn-primary-custom"><i class="ti ti-login me-1"></i>{{ $l == 'en' ? 'Login to View Results' : 'تسجيل الدخول لمشاهدة النتائج' }}</a>
									<a href="/register" class="btn btn-outline-custom"><i class="ti ti-user-plus me-1"></i>{{ $l == 'en' ? 'Register' : 'التسجيل' }}</a>
								</div>
							</div>
							@endif
						</div>
						@endif
					</div>
				</div>
			</section>
			@endif
		</main>
	</div>

	<footer class="footer-custom">
		<div class="container">
			<div class="row g-4">
				<div class="col-lg-5">
					<h3 class="fw-black mb-3">{{ $program->org }}</h3>
					<p style="color:var(--on-surface-variant);max-width:400px;" class="mb-4">{{ $program?->bio ?: '' }}</p>
					<div class="d-flex gap-3">
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
				</div>
				<div class="col-lg-3">
					<h6 class="fw-bold text-uppercase mb-4">{{ $l == 'en' ? 'Quick Links' : 'روابط سريعة' }}</h6>
					<ul class="list-unstyled" style="color:var(--on-surface-variant);">
						<li class="mb-2"><a href="/dashboard" class="text-reset text-decoration-none">{{ __('admin.Dashboard') }}</a></li>
						<li class="mb-2"><a href="{{ setting('privacy_url', '#') }}" class="text-reset text-decoration-none" {{ setting('privacy_url') ? 'target="_blank"' : '' }}>{{ $l == 'en' ? 'Privacy Policy' : 'سياسة الخصوصية' }}</a></li>
					</ul>
				</div>
				<div class="col-lg-4">
					<h6 class="fw-bold text-uppercase mb-4">{{ $l == 'en' ? 'Newsletter' : 'النشرة البريدية' }}</h6>
					<div class="input-group">
						<input type="email" class="form-control" placeholder="{{ $l == 'en' ? 'Enter email' : 'أدخل البريد الإلكتروني' }}" style="border-color:var(--outline);">
						<button class="btn btn-primary-custom">{{ $l == 'en' ? 'Join' : 'انضمام' }}</button>
					</div>
				</div>
			</div>
			<hr class="my-5" style="opacity:0.1;">
			<div class="text-center small" style="color:var(--on-surface-variant);">
				© {{ date('Y') }} {{ $platformName }}. {{ $l == 'en' ? 'All rights reserved.' : 'جميع الحقوق محفوظة.' }}
			</div>
		</div>
	</footer>

	<script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
	<script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
	<script src="/vuexy/assets/js/main.js"></script>

	@include('includes.winnerModal', ['winners' => $winnersData])

	<script>
		$(document).on('click', '.like-project-action', function(e) {
			e.preventDefault();
			var $el = $(this);
			$.ajax({
				headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
				type: "POST",
				url: "/dashboard/hackathon/likeProject",
				data: { teamProjectId: $el.data('project') },
				success: function(r) {
					$el.parent().find('.likes-count').text(r.total);
					$el.toggleClass('fas far');
				},
				error: function() { console.log("Like failed"); }
			});
		});

		function copyLink(url) {
			var btn = document.getElementById('copy-link-btn');
			var orig = btn.innerHTML;
			var done = '{{ $l }}' === 'en' ? 'Copied!' : 'تم النسخ!';
			function show() {
				trackProgramShare('copy_link');
				btn.innerHTML = '<i class="ti ti-check me-1"></i>' + done;
				btn.classList.add('btn-success');
				btn.classList.remove('btn-outline-secondary');
				setTimeout(function() {
					btn.innerHTML = orig;
					btn.classList.remove('btn-success');
					btn.classList.add('btn-outline-secondary');
				}, 2000);
			}
			function fallback() {
				var ta = document.createElement('textarea');
				ta.value = url;
				ta.style.cssText = 'position:fixed;left:-9999px';
				document.body.appendChild(ta);
				ta.select();
				try { document.execCommand('copy'); show(); }
				catch (e) { alert('{{ $l == "en" ? "Failed to copy link" : "فشل نسخ الرابط" }}'); }
				document.body.removeChild(ta);
			}
			if (navigator && navigator.clipboard && typeof navigator.clipboard.writeText === 'function')
				navigator.clipboard.writeText(url).then(show).catch(fallback);
			else fallback();
		}

		function trackProgramShare(platform) {
			if (typeof gtag === 'function')
				gtag('event', 'share', { method: platform, content_type: 'program_results', item_id: '{{ $program->id ?? "unknown" }}' });
			fetch('{{ route("hackathon.trackProgramShare") }}', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
				body: JSON.stringify({ cycle_id: {{ $program->id ?? 0 }}, platform: platform })
			}).catch(function(e) { console.error('Track share failed:', e); });
		}

		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.program-share-btn').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var p = this.getAttribute('data-platform');
					if (p) trackProgramShare(p);
				});
			});
			var wc = document.querySelector('.winners-grid-container .row');
			if (wc) {
				var cards = wc.querySelectorAll('.col-md-6, .col-lg-4');
				var span = document.getElementById('total-winners');
				if (span && cards.length) span.textContent = cards.length;
			}
		});

		(function() {
			var end = new Date("{{ $endDateForCountdown }}").getTime();
			var start = new Date("{{ $startDateForCountdown }}").getTime();
			function tick() {
				var now = new Date().getTime();
				var dist = end - now;
				if (dist < 0) {
					document.getElementById("countdown-timer").innerHTML =
						'<span class="badge bg-label-secondary rounded-pill px-4 py-2 fs-6">{{ $l == 'en' ? 'The event has ended' : 'انتهى الحدث' }}</span>';
					return;
				}
				if( now < start ) {
					document.getElementById("countdown-timer").innerHTML =
						'<span class="badge bg-label-secondary rounded-pill px-4 py-2 fs-6">{{ $l == 'en' ? 'Coming soon' : 'قريبا' }}</span>';
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
	</script>
</body>
</html>
