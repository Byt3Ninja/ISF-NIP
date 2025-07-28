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
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
	<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">

	<style>
		.mb-20 { margin-bottom: 5rem !important; }
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
					<p class="lead" id="hackathon-dates-location">
						<i class="ti ti-calendar-event me-2"></i>
						{{ $program->ranges }}
						<span class="mx-3">|</span>
						<i class="ti ti-map-pin me-2"></i>
						{{ $program->org }}
					</p>
				</div>
			</div>

			<div class="container-fluid mb-20">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div id="initial-step">
									<div class="form-floating">
										<input type="email" class="form-control" id="initial-email" name="initial-email" placeholder="" required>
										<label for="email">Email</label>
										<div id="initial-email-feedback" class="invalid-feedback"></div>
									</div>
									<div class="mt-3">
										<button id="check-email" type="submit" class="btn w-100 btn-primary">Continue</button>
									</div>
								</div>
								<div class="d-none" id="login-step">
									<div id="login-alert" class="alert alert-info alert-dismissible fade show mb-3" role="alert">
										<strong>{{ 'Please use your registered email and password.' }}</strong>
										<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
									</div>

									<div class="form-floating mb-3">
										<input type="email" class="form-control" id="login-email" name="login-email" placeholder="" disabled readonly required>
										<label for="login-email">Email</label>
										<div id="login-email-feedback" class="invalid-feedback"></div>
									</div>
									<div class="form-floating mb-3">
										<input type="password" class="form-control" id="login-password" name="login-password" placeholder="" required>
										<label for="login-password">Password</label>
										<div id="login-password-feedback" class="invalid-feedback"></div>
									</div>
									<div class="mt-3">
										<button id="login-btn" type="submit" class="btn w-100 btn-primary">Continue</button>
									</div>
								</div>
								<div class="d-none" id="register-step">
									<form id="register-form" method="post" action="/{{ $org_slug }}/program/{{ $program_slug }}/register" enctype="multipart/form-data">
										{!! csrf_field() !!}
										<div class="form-floating mb-3">
											<input type="email" class="form-control" id="register-email" name="register-email" placeholder="" disabled required>
											<label for="register-email">Email</label>
											<div id="register-email-feedback" class="invalid-feedback"></div>
										</div>
										<div class="form-floating mb-3">
											<input type="email" class="form-control" id="register-first-name" name="register-first-name" placeholder="" required>
											<label for="register-first-name">First Name</label>
											<div id="register-first-name-feedback" class="invalid-feedback"></div>
										</div>
										<div class="form-floating mb-3">
											<input type="email" class="form-control" id="register-last-name" name="register-last-name" placeholder="" required>
											<label for="register-last-name">Last Name</label>
											<div id="register-last-name-feedback" class="invalid-feedback"></div>
										</div>
										<hr />
										<div id="register-fields"></div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
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
	<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
	<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
  	<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>

	<!-- Custom JS -->
	<script src="/vuexy/assets/js/forms-selects.js"></script>
	<script>
		alertify.defaults.transition = 'zoom';
		alertify.defaults.theme.ok = 'btn btn-md btn-success';
		alertify.defaults.theme.cancel = 'btn btn-md btn-danger';
		alertify.defaults.theme.input = 'form-control';
		$(document).on('click', '#check-email', function(e) {
			e.preventDefault();
			let email = $('#initial-email').val();
			if( email.trim() == '' )
			{
				$('#initial-email-feedback').html('Email is required.');
				$('#initial-email').addClass('is-invalid');
				return;
			}
			else
			{
				if( validateEmail(email) )
				{
					$.ajaxSetup({
						headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
					});
					$.ajax({
						type: "POST",
						url: "/{{ $org_slug }}/program/{{ $program_slug }}/validate_email",
						data: {email: email},
						success: function(response) {
							if( response.status )
							{
								if( response.step == 'login' )
								{
									$("#initial-step").addClass('d-none');
									$("#login-step").removeClass('d-none');
									$("#login-email").val( $("#initial-email").val() );
									$("#login-email").prop('disabled', true);
									$("#login-email").prop('readonly', true);
								}
								else if( response.step == 'register' )
								{
									$("#initial-step").addClass('d-none');
									$("#login-step").addClass('d-none');
									$("#register-step").removeClass('d-none');
									$("#register-email").val( $("#initial-email").val() );
									$("#register-email").prop('disabled', true);
									$("#register-email").prop('readonly', true);

									var fields = '';
									$.each( response.fields, function( key, value ) {
										fields += `<div class="mb-3">`;
											switch( value.type )
											{
												case 'select':
												{
													fields += `<div class="form-group mb-6">`;
														if( value.options.label )
														{
															fields += `<label for="${value.id_for}" class="form-label fw-bold">${value.options.label}</label>`;
														}
														fields += `<select class="form-select" id="${value.id_for}" name='${value.field_name}' required>`;
															fields += `<option value="">{{$l=='ar'?'اختر':'Choose'}}...</option>`;
															
															$.each( value.options.values.split(','), function( key, option ) {
																fields += `<option value="${option}">${option}</option>`;
															});
														fields += `</select>`;
														fields += `<div class="invalid-feedback">${value.options.label} required</div>`;
													fields += `</div>`;
													break;
												}
												case 'textarea':
												{
													fields += `<div class="form-group mb-6">`;
														if( value.options.label )
														{
															fields += `<label for="${value.id_for}" class="form-label fw-bold">${value.options.label}</label>`;
														}
														fields += `<textarea class="form-control" id="${value.id_for}" name="${value.field_name}" rows="${value.options.rows}" required></textarea>`;
														fields += `<div class="invalid-feedback">${value.options.label} required</div>`;
													fields += `</div>`;
													break;
												}
												case 'file':
												{
													fields += `<div class="form-group mb-6">`;
														if( value.options.label )
														{
															fields += `<label for="${value.id_for}" class="form-label fw-bold">${value.options.label}</label>`;
														}
														fields += `<div class="d-flex flex-column">`;
															fields += `<input type="file" class="form-control" id="${value.id_for}" name="${value.field_name}" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" required>`;
															fields += `<div class="invalid-feedback">${value.options.label} required</div>`;
														fields += `</div>`;
													fields += `</div>`;
													break;
												}
												default:
												{
													fields += `<div class="form-group mb-6">`;
														if( value.options.label )
														{
															fields += `<label for="${value.id_for}" class="form-label fw-bold">${value.options.label}</label>`;
														}
														fields += `<input type="${ value.options.type == 'date' ? 'text': value.options.type }" 
																class="form-control ${value.options.type == 'date' ? 'datepicker' : ''}" 
																id="${value.id_for}"
																name="${value.field_name}"
																value="" required />`;
														fields += `<div class="invalid-feedback">${value.options.label} required</div>`;
													fields += `</div>`;
												}
											}
										fields += `</div>`;
									});
									if( response.fields.length > 0 )
									{
										fields += `<div class="d-flex justify-content-end">`;
											fields += `<button id="join_btn" type="submit" class="btn btn-primary w-px-120">Join</button>`;
										fields += `</div>`;
									}
									$("#register-fields").html( fields );
									datePicker();
								} else { alertify.alert(response.message); }
							} else { alertify.alert(response.message); }
						},
						error: function(error) {
							console.log(error);
							alertify.alert("Error fetching cycle data. Please try again.");
						}
					});
				}
				else
				{
					$('#initial-email-feedback').html('Invalid email.');
					$('#initial-email').addClass('is-invalid');
					return;
				}
			}
		});

		$(document).on('click', '#login-btn', function(e) {
			e.preventDefault();
			let email = $('#initial-email').val();
			let login_email = $('#login-email').val();
			let password = $('#login-password').val();
			if( email == login_email )
			{
				$("#login-email").removeClass("is-invalid");
				if( validateEmail(login_email) )
				{
					if( password.trim() == '' )
					{
						$('#login-password-feedback').html('Password is required.');
						$('#login-password').addClass('is-invalid');
					}
					else
					{
						$.ajaxSetup({
							headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
						});
						$.ajax({
							type: "POST",
							url: "/{{ $org_slug }}/program/{{ $program_slug }}/authenticate",
							data: {email: email, password: password},
							success: function(response) {
								if( response.status )
								{
									window.location.href = response.url;
								} else { alertify.alert(response.message); }
							},
							error: function(error) {
								console.log(error);
								alertify.alert("Error fetching cycle data. Please try again.");
							}
						});
					}
				}
				else
				{
					$('#login-email-feedback').html('E-mail has invalid format.');
					$('#login-email').addClass('is-invalid');
				}
			}
			else
			{
				$("#login-email-feedback").html("Email and login email do not match.");
				$("#login-email").addClass("is-invalid");
			}
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

			// Custom Validation
			$form.find('[required]').each(function () {
				const $field = $(this);
				const type = $field.attr('type');
				const name = $field.attr('name');
				const value = $field.val();

				if( type === 'checkbox' && !$field.is(':checked') )
				{
					$field.addClass('is-invalid');
					isValid = false;
				}
				else if (type === 'file')
				{
					if( this.files.length === 0 )
					{
						$field.addClass('is-invalid');
						isValid = false;
					} else {
						// Optional: validate file size/type here
					}
				} else if (!value) {
					$field.addClass('is-invalid');
					isValid = false;
				}
			});

			if (!isValid) {
				alertify.alert('Please fill all required fields.');
				return;
			}

			$.ajaxSetup({
				headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
			});
			formData.append('register-email', $('#register-email').val());
			$.ajax({
				type: "POST",
				url: "/{{ $org_slug }}/program/{{ $program_slug }}/register",
				data: formData,
				contentType: false,
				processData: false,
				success: function( response )
				{
					if( response.status )
					{
						window.location.href = response.url;
					} else { alertify.alert(response.message); }
				},
				error: function(xhr, status, error) {
					console.log(error);
					alertify.alert("Error fetching cycle data. Please try again.");
				}
			});
		});

		validateEmail = (email) => {
			var re =  /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
			return re.test(email);
		};

		function datePicker() { $('.datepicker').flatpickr({dateFormat: 'Y-m-d'}); }
	</script>
</body>

</html>