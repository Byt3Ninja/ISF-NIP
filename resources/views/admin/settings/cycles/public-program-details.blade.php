<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	class="light-style layout-navbar-fixed layout-compact" data-theme="theme-default" data-assets-path="/vuexy/assets/"
	data-template="vertical-menu-template" data-style="light">

<head>
	@include('admin.includes.head')

	<!-- Additional Styles -->
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

	<style>
		body {
			display: flex;
			flex-direction: column;
			min-height: 100vh;
			margin: 0;
		}
		.page-container {
			flex: 1;
		}
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
	<div class="page-container">
		<main class="content">

			<div class="container mt-8" style="direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }};">

				<div class="hackathon-banner text-center shadow">
					<div class="banner-content">
						<h1 class="display-5 fw-bold text-black" id="hackathon-main-title"> {{ $program->title }} </h1>
						<p class="lead fw-semibold line-clamp-5">{{ $program->description }}</p>
						<p class="lead fw-semibold" id="hackathon-dates-location">
							<i class="ti ti-calendar-event me-2"></i>
							{{ $program->ranges }}
							<span class="mx-3">|</span>
							<i class="ti ti-map-pin me-2"></i>
							{{ $program->org }}
						</p>

						<!-- Countdown Timer -->
						<div id="countdown-timer" class="d-flex justify-content-center">
							<div class="timer-box">
								<div id="days" class="timer-number">00</div>
								<div class="timer-label">{{ $l == 'en' ? 'Days' : 'أيام' }}</div>
							</div>
							<div class="timer-box">
								<div id="hours" class="timer-number">00</div>
								<div class="timer-label">{{ $l == 'en' ? 'Hours' : 'ساعات' }}</div>
							</div>
							<div class="timer-box">
								<div id="minutes" class="timer-number">00</div>
								<div class="timer-label">{{ $l == 'en' ? 'Minutes' : 'دقائق' }}</div>
							</div>
							<div class="timer-box">
								<div id="seconds" class="timer-number">00</div>
								<div class="timer-label">{{ $l == 'en' ? 'Seconds' : 'ثواني' }}</div>
							</div>
						</div>

						<div id="action-buttons-placeholder" class="mt-4">
							<a href="{{ URL::to( $org_slug . '/program/' . $program_slug . '/apply') }}" type="button"
								class="btn btn-white text-primary btn-lg" style="flex: 1;">
								<i class="ti ti-send me-2"></i> {{ $l == 'en' ? 'Apply Now' : 'تقدم الآن' }}
							</a>
						</div>
					</div>
				</div>

				@if( $pages->count() > 0 )
				<ul class="nav nav-pills flex-column flex-md-row mb-6 gap-2 gap-lg-0 px-0" role="tablist" id="hackathonTab">
					@foreach( $pages as $tab )
					<li class="nav-item {{ $l == 'en' ? 'me-3' : '' }}" role="presentation">
						<button class="nav-link rounded {{ $loop->first ? 'active' : '' }}" id="{{ $tab->code }}-tab"
							data-bs-toggle="tab" data-bs-target="#{{ $tab->code }}" type="button" role="tab"
							aria-controls="{{ $tab->code }}" aria-selected="false">
							{{-- <i class="bi bi-list me-2"></i> --}}
							{{ $tab->title }}
						</button>
					</li>
					@endforeach
					@if( $program->announced )
					<li class="nav-item {{ $l == 'en' ? 'me-3' : '' }}" role="presentation">
						<button class="nav-link rounded" id="results-winners-tab" data-bs-toggle="tab"
							data-bs-target="#results-winners" type="button" role="tab" aria-controls="results-winners"
							aria-selected="false">
							{{ $l == 'en' ? 'Results & winners' : 'النتائج والفائزون' }}
						</button>
					</li>
					@endif
				</ul>

				<div class="tab-content p-0 mb-12" id="hackathonTabContent">
					<!-- Pages Tab -->
					@foreach( $pages as $tab )
					<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab->code }}" role="tabpanel"
						aria-labelledby="{{ $tab->code }}-tab">
						<div class="card">
							<div class="card-body"> {!! $tab->content !!} </div>
						</div>
					</div>
					@endforeach
					@if( $program->announced )
					<div class="tab-pane fade" id="results-winners" role="tabpanel" aria-labelledby="results-winners-tab">
						<!-- Enhanced Results & Winners Section -->
						<div>
							<!-- Winners Content Area -->
							@if( $program->visibility == 'public' )
								<!-- Public Winners Display -->
								<div class="card card-body shadow-none">
									<div class="row mb-4">
										<div class="col-12 border-bottom pb-3">
											<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
												<div class="d-flex flex-column align-items-start mb-4 mb-md-0">
													<h3 class="fw-bold text-primary mb-1">
														{{ $l == 'en' ? 'Competition Winners' : 'الفائزون في المسابقة' }}
													</h3>
													<p class="text-muted mb-0">
														{{ $l == 'en' ? 'Innovative solutions that stood out from the competition' : 'الحلول المبتكرة التي تميزت في المسابقة' }}
													</p>
												</div>
												<!-- Social Share Section -->
												<div class="d-flex align-items-center gap-3">
													<h6 class="mb-0 opacity-90 d-none d-md-flex">{{ $l == 'en' ? 'Share the Results via:' : 'شارك النتائج عبر:' }}</h6>
													<div class="d-flex justify-content-center flex-wrap gap-3">
														<!-- Facebook Share Button -->
														<a
															class="btn d-flex align-items-center gap-2 program-share-btn"
															style="background-color: #3b5998; color: white;"
															href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
															target="_blank"
															data-platform="facebook">
															<i class="ti ti-brand-facebook"></i>
															{{ $l == 'en' ? 'Facebook' : 'فيسبوك' }}
														</a>

														<a
															class="btn d-flex align-items-center gap-2 program-share-btn"
															style="background-color: #0077B5; color: white;"
															href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
															target="_blank"
															data-platform="linkedin">
															<i class="ti ti-brand-linkedin"></i>
															{{ $l == 'en' ? 'LinkedIn' : 'لينكد إن' }}
														</a>

														<a
															class="btn btn-dark d-flex align-items-center gap-2 program-share-btn"
															href="https://twitter.com/intent/tweet?text={{ urlencode($program->title . ' - Results Announced! ' . url()->current()) }}"
															target="_blank"
															data-platform="twitter">
															<i class="ti ti-brand-x"></i>
															{{ $l == 'en' ? 'Tweet' : 'تغريدة' }}
														</a>

														<button
															class="btn btn-light d-flex align-items-center gap-2"
															type="button"
															onclick="copyLink('{{url()->current()}}')"
															id="copy-link-btn">
															<i class="bi bi-link-45deg"></i>
															{{ $l == 'en' ? 'Copy Link' : 'نسخ الرابط' }}
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Winners Grid (from partial) -->
									@include('admin.hackathon.partials.winners-tab', ['winners' => $winnersData, 'currentHackathon' => $currentHackathon])
								</div>
							@else
								<!-- Private/Login Required Message -->
								<div class="card border-0 shadow-sm">
									<div class="card-body text-center py-5">
										<div class="mb-4">
											<i class="bi bi-lock-fill text-muted" style="font-size: 3rem;"></i>
										</div>
										<h4 class="fw-semibold text-dark mb-3">
											{{ $l == 'en' ? 'Results Restricted' : 'النتائج مقيدة' }}
										</h4>
										<p class="text-muted mb-4 lead">
											{{ $l == 'en' 
												? 'This program results have been announced, please login to view the winners and detailed results.' 
												: 'تم الإعلان عن نتائج هذا البرنامج، يرجى تسجيل الدخول لمشاهدة الفائزين والنتائج التفصيلية.' 
											}}
										</p>
										<div class="d-flex justify-content-center gap-3">
											<a href="/login" class="btn btn-primary">
												<i class="bi bi-box-arrow-in-right me-1"></i>
												{{ $l == 'en' ? 'Login to View Results' : 'تسجيل الدخول لمشاهدة النتائج' }}
											</a>
											<a href="/register" class="btn btn-outline-primary">
												<i class="bi bi-person-plus me-1"></i>
												{{ $l == 'en' ? 'Register' : 'التسجيل' }}
											</a>
										</div>
									</div>
								</div>
							@endif
						</div>
					</div>
					@endif
				</div>
				@endif
			</div>
		</main>
	</div>

	<!-- Enhanced Footer -->
	<footer id="contact-section" class="text-white pt-5 pb-4" style="background-color: var(--bs-primary-text-emphasis);">
		<div class="container text-center text-md-start">
			<div class="row text-center text-md-start">
				<div class="col-md-4 col-lg-5 mt-3">
					<h5 class="text-white text-uppercase mb-4 fw-bold">{{ $l == 'en' ? 'About Us' : 'معلومات عنا' }}</h5>
					<p>{{ $program?->bio ?: '' }}</p>
				</div>

				<div class="col-md-4 col-lg-2 mx-auto mt-3">
					@if( $program?->facebook || $program?->twitter || $program?->linkedin || $program?->instagram || $program?->youtube || $program?->tiktok )
					<h5 class="text-white text-uppercase mb-4 fw-bold">{{ $l == 'en' ? 'Follow Us' : 'تابعنا' }}</h5>
					<div class="d-flex justify-content-center justify-content-md-start">
						@if( $program?->facebook )
						<a href="{{ $program?->facebook ?: '#' }}" class="text-white me-3">
							<i class="bi bi-facebook fs-4"></i>
						</a>
						@endif
						@if( $program?->twitter )
						<a href="{{ $program?->twitter ?: '#' }}" class="text-white me-3">
							<i class="bi bi-twitter fs-4"></i>
						</a>
						@endif
						@if( $program?->linkedin )
						<a href="{{ $program?->linkedin ?: '#' }}" class="text-white me-3">
							<i class="bi bi-linkedin fs-4"></i>
						</a>
						@endif
						@if( $program?->instagram )
						<a href="{{ $program?->instagram ?: '#' }}" class="text-white">
							<i class="bi bi-instagram fs-4"></i>
						</a>
						@endif
						@if( $program?->youtube )
						<a href="{{ $program?->youtube ?: '#' }}" class="text-white">
							<i class="bi bi-youtube fs-4"></i>
						</a>
						@endif
						@if( $program?->tiktok )
						<a href="{{ $program?->tiktok ?: '#' }}" class="text-white">
							<i class="bi bi-tiktok fs-4"></i>
						</a>
						@endif
					</div>
					@endif
				</div>
			</div>

			<hr class="mb-4">

			<div class="row align-items-center">
				<div class="col-md-7 col-lg-8">
					<p class="text-center text-md-start mb-3 mb-md-0">
						© {{ date('Y') }} {{ $platformName }} {{ $l == 'en' ? '. All rights reserved.' : '. جميع الحقوق محفوظة.' }}
					</p>
				</div>
			</div>
		</div>
	</footer>

	<!-- Core JS -->
	<script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
	<script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
	<script src="/vuexy/assets/js/main.js"></script>

	@include('includes.winnerModal', ['winners' => $winnersData])
	
	<script src="https://platform.linkedin.com/in.js" type="text/javascript">
		lang: en_US
	</script>


	<script>
		$(document).on('click', '.like-project-action', function(e) {
			e.preventDefault();
			let element = $(this);
			let teamProjectId = element.data('project');
			$.ajaxSetup({
				headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),},
			});

			$.ajax({
				type: "POST",
				url: "/dashboard/hackathon/likeProject",
				data: {
					teamProjectId: teamProjectId,
				},
				success: function (response) {
					element.parent().find('.likes-count').text(response.total);
					if( element.hasClass('fas') )
					{
						element.removeClass('fas');
						element.addClass('far');
					}
					else if( element.hasClass('far') )
					{
						element.removeClass('far');
						element.addClass('fas');
					}
				},
				error: function (error) {
					console.log("Error Occurred, like failed");
				},
			});
		});
		function copyLink(url) {
			const btn = document.getElementById('copy-link-btn');
			const originalText = btn.innerHTML;

			// Show success feedback
			function showSuccess() {
				// Track successful copy
				trackProgramShare('copy_link');

				btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>' +
					('{{ $l }}' === 'en' ? 'Copied!' : 'تم النسخ!');
				btn.classList.add('btn-success');
				btn.classList.remove('btn-light');

				// Reset after 2 seconds
				setTimeout(() => {
					btn.innerHTML = originalText;
					btn.classList.remove('btn-success');
					btn.classList.add('btn-light');
				}, 2000);
			}

			// Fallback copy method for HTTP or older browsers
			function fallbackCopy() {
				const textArea = document.createElement('textarea');
				textArea.value = url;
				textArea.style.position = 'fixed';
				textArea.style.left = '-9999px';
				document.body.appendChild(textArea);
				textArea.select();
				try {
					document.execCommand('copy');
					showSuccess();
				} catch (err) {
					alert('{{ $l == "en" ? "Failed to copy link" : "فشل نسخ الرابط" }}');
				}
				document.body.removeChild(textArea);
			}

			// Check if clipboard API is available (requires HTTPS)
			if (typeof navigator !== 'undefined' && navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
				try {
					navigator.clipboard.writeText(url).then(() => {
						showSuccess();
					}).catch(() => {
						fallbackCopy();
					});
				} catch (e) {
					fallbackCopy();
				}
			} else {
				fallbackCopy();
			}
		}

		// Track program share events (Google Analytics + Database)
		function trackProgramShare(platform) {
			// Google Analytics tracking
			if (typeof gtag === 'function') {
				gtag('event', 'share', {
					method: platform,
					content_type: 'program_results',
					item_id: '{{ $program->id ?? "unknown" }}'
				});
			}

			// Database tracking
			fetch('{{ route("hackathon.trackProgramShare") }}', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': '{{ csrf_token() }}'
				},
				body: JSON.stringify({
					cycle_id: {{ $program->id ?? 0 }},
					platform: platform
				})
			}).catch(function(err) {
				console.error('Failed to track program share:', err);
			});
		}

		// Add click handlers for social share buttons
		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.program-share-btn').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var platform = this.getAttribute('data-platform');
					if (platform) {
						trackProgramShare(platform);
					}
				});
			});
		});

		// Update total winners count dynamically if needed
		document.addEventListener('DOMContentLoaded', function() {
			const winnersContainer = document.querySelector('.winners-grid-container .row');
			if (winnersContainer) {
				const winnerCards = winnersContainer.querySelectorAll('.col-md-6, .col-lg-4');
				const totalWinnersSpan = document.getElementById('total-winners');
				if (totalWinnersSpan && winnerCards.length > 0) {
					totalWinnersSpan.textContent = winnerCards.length;
				}
			}
		});
	</script>
	<script>
		// Set the date we're counting down to using the parsed PHP variable
		var countDownDate = new Date("{{ $endDateForCountdown }}").getTime();

		// Update the count down every 1 second
		var x = setInterval(function () {

			// Get today's date and time
			var now = new Date().getTime();

			// Find the distance between now and the count down date
			var distance = countDownDate - now;

			// Time calculations for days, hours, minutes and seconds
			var days = Math.floor(distance / (1000 * 60 * 60 * 24));
			var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			var seconds = Math.floor((distance % (1000 * 60)) / 1000);

			// Display the result, ensuring two digits
			document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
			document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
			document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
			document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');

			// If the count down is finished, write some text
			if (distance < 0) {
				clearInterval(x);
				document.getElementById("countdown-timer").innerHTML = "<h4>{{ $l == 'en' ? 'The event has ended' : 'انتهى الحدث' }}</h4>";
			}
		}, 1000);
	</script>
</body>

</html>