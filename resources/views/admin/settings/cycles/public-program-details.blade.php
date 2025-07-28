<!DOCTYPE html>
<html
	lang="{{ $l }}"
	dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
	class="light-style layout-navbar-fixed layout-compact"
	data-theme="theme-default"
	data-assets-path="/vuexy/assets/"
	data-template="vertical-menu-template"
	data-style="light">

<head>
	@include('admin.includes.head')

	<!-- Additional Styles -->
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

	<style>
		.hackathon-banner {
			background: linear-gradient(135deg, #7367f0 0%, #9c88ff 35%, #ffc107 100%);
			background-size: 400% 400%;
			animation: gradientShift 6s ease infinite;
			color: white;
			padding: 2.5rem 2rem;
			border-radius: 20px;
			position: relative;
			overflow: hidden;
			box-shadow: 0 20px 40px rgba(115, 103, 240, 0.3);
			margin-bottom: 2rem;
		}

		.hackathon-banner::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background:
				radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
				radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
				radial-gradient(circle at 40% 60%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
			z-index: 1;
		}

		.hackathon-banner .banner-content {
			position: relative;
			z-index: 2;
		}

		.hackathon-banner h1 {
			font-size: 3.5rem;
			font-weight: 800;
			margin-bottom: 1rem;
			text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
			letter-spacing: -0.02em;
		}

		.hackathon-banner .lead {
			font-size: 1.25rem;
			font-weight: 500;
			margin-bottom: 2rem;
			opacity: 0.95;
			text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
		}

		.hackathon-banner .lead i {
			font-size: 1.3rem;
			margin-right: 0.5rem;
			margin-left: 0.5rem;
		}

		.hackathon-status-badge {
			display: inline-block;
			background: rgba(255, 255, 255, 0.2);
			backdrop-filter: blur(10px);
			border: 1px solid rgba(255, 255, 255, 0.3);
			padding: 0.5rem 1.5rem;
			border-radius: 50px;
			font-weight: 600;
			font-size: 0.9rem;
			margin-bottom: 1rem;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		footer.footer
		{
			position: fixed;
			bottom: 0;
			width: 100%;
		}

		@keyframes gradientShift {
			0% {
				background-position: 0% 50%;
			}

			50% {
				background-position: 100% 50%;
			}

			100% {
				background-position: 0% 50%;
			}
		}

		@media (max-width: 768px) {
			.hackathon-banner {
				padding: 3rem 1rem;
			}

			.hackathon-banner h1 {
				font-size: 2.5rem;
			}
		}

		.nav-tabs .nav-link.active {
			background-color: #7367f0;
			color: white;
			border-color: #7367f0;
		}

		.nav-tabs .nav-link {
			color: #7367f0;
			border-radius: 10px !important;
			margin: 0 0.25rem;
			border: 2px solid transparent;
			transition: all 0.3s ease;
		}

		.nav-tabs .nav-link:hover {
			border-color: #7367f0;
			background-color: rgba(115, 103, 240, 0.1);
		}

		.timeline {
			list-style-type: none;
			padding-left: 0;
		}

		.timeline li {
			margin-bottom: 1.5rem;
			position: relative;
			padding-left: 25px;
		}

		.timeline li::before {
			content: '';
			background-color: #7367f0;
			width: 10px;
			height: 10px;
			border-radius: 50%;
			position: absolute;
			right: -15px;
			top: 5px;
			border: 2px solid white;
		}

		.timeline li .time {
			font-weight: bold;
			display: block;
			color: #6c757d;
		}

		.countdown-timer {
			font-size: 1.5rem;
			font-weight: bold;
		}
	</style>
</head>

<body>
	<!-- Include original navbar -->
	<nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm">
		<div class="container">
			<a class="navbar-brand fw-bold" href="/dashboard/hackathon">
				<i class="ti ti-tournament text-primary me-2 me-2"></i> {{ $l == 'en' ? 'Hackathon Platform' : 'منصة الهاكثونات' }}
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
				aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<div class="navbar-nav"></div>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<main class="overflow-x-hidden mx-auto" style="width: 96%;">

		<div class="container mt-4">

			<div class="hackathon-banner text-center">
				<div class="banner-content">
					<h1 class="display-5 fw-bold" id="hackathon-main-title"> {{ $program->title }} </h1>
					<p class="lead">{{ $program->description }}</p>
					<p class="lead" id="hackathon-dates-location">
						<i class="ti ti-calendar-event me-2"></i>
						{{ $program->ranges }}
						<span class="mx-3">|</span>
						<i class="ti ti-map-pin me-2"></i>
						{{ $program->org }}
					</p>
					<div id="action-buttons-placeholder" class="mt-4">
						<a href="{{ URL::to( $org_slug . '/program/' . $program_slug . '/apply') }}" type="button" class="btn btn-primary" style="flex: 1;">
                        	<i class="ti ti-send me-1"></i> Apply
                        </a>
					</div>
				</div>
			</div>

			@if( $pages->count() > 0 )
			<ul class="nav nav-tabs nav-fill mb-4 pb-4" id="hackathonTab" role="tablist">
				@foreach( $pages as $tab )
					<li class="nav-item me-1" role="presentation">
						<button class="nav-link rounded {{ $loop->first ? 'active' : '' }}" id="{{ $tab->code }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $tab->code }}" type="button" role="tab" aria-controls="{{ $tab->code }}" aria-selected="false">
							<i class="bi bi-list me-2"></i> {{ $tab->title }}
						</button>
					</li>
				@endforeach
			</ul>

			<div class="tab-content p-0 mb-12" id="hackathonTabContent">
				<!-- Pages Tab -->
				@foreach( $pages as $tab )
				<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab->code }}" role="tabpanel" aria-labelledby="{{ $tab->code }}-tab">
					<div class="card">
						<div class="card-body">
							{!! $tab->content !!}
						</div>
					</div>
				</div>
				@endforeach
			</div>
			@endif
		</div>
	</main>

	<!-- Include original footer -->
	<footer class="footer mt-auto py-3 bg-dark text-white">
		<div class="container text-center">
			<span>
				© 2024
				{{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات. جميع الحقوق محفوظة.' }}
			</span>
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

	<!-- Custom JS -->
	<script src="/vuexy/assets/js/forms-selects.js"></script>
	<script>
		//
	</script>
</body>

</html>