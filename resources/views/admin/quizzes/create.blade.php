@extends('layouts.admin')
@section('title', __('admin.quizzes') )
@section('index_url', route('quizzes.index') )
@section('subTitle', __('admin.Add new Quiz'))

@section('content')
<!-- BEGIN: Content-->
<div class="">
	<div class="">
		<div class="content-overlay"></div>
		
		<div class="content-body">
			<!-- Quiz Creation Form start -->
			<div class="row">
				<div class="col-12">
					<div class="card">
						<div class="card-body">
						<div class=" border-bottom mb-8 pb-4">
							<h5 class="card-title mb-0">
								{{__('admin.Add new Quiz')}}
							</h5>
							<p class="card-text text-muted mt-1 mb-0">
								{{ $l == 'en' ? 'Fill out the form below to create a new quiz' : 'أكمل النموذج أدناه لإنشاء اختبار جديد' }}
							</p>
						</div>
							@if( session()->has('msg') )
							<div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
								<i class="ti ti-alert-triangle me-3"></i>
								<div>
									{{ __('admin.'.session()->get('msg')) }} 
									{{ Session::forget('msg') }}
								</div>
								<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif
							
							<!-- Validation messages -->
							@if(count($errors) >0)
							<div class="alert alert-danger d-flex align-items-start mb-4" role="alert">
								<i class="ti ti-alert-circle me-3 mt-1"></i>
								<div class="flex-grow-1">
									<ul class="mb-0">
										@foreach($errors->all() as $error)
										<li>{{ $error }}</li>
										@endforeach
									</ul>
								</div>
								<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
							</div>
							@endif
							
							<form method="post" action="{{ route('quizzes.store') }}" class="needs-validation" novalidate>
								{!! csrf_field() !!}
								
								<!-- Quiz Title - Full Width -->
								<div class="row g-4 mb-4">
									<div class="col-12">
										<div class="form-group">
											<label class="form-label mb-1" for="title">{{ __('admin.Title')}}</label>
											<input value="" name="title" type="text" class="form-control" id="title" placeholder="{{ __('admin.Title') }}" required />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a quiz title.' : 'يرجى إدخال عنوان الاختبار.' }}</div>
										</div>
									</div>
								</div>

								<!-- Description - Full Width -->
								<div class="row g-4 mb-4">
									<div class="col-12">
										<div class="form-group">
											<label class="form-label mb-1" for="description">{{ __('admin.Description')}}</label>
											<textarea name="description" class="form-control" id="description" placeholder="{{ __('admin.Description') }}" style="height: 80px" required></textarea>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a description.' : 'يرجى إدخال وصف.' }}</div>
										</div>
									</div>
								</div>

								<!-- Start and End Dates - Same Row -->
								<div class="row g-4 mb-4">
									<div class="col-lg-6">
										<div class="form-group">
											<label class="form-label mb-1" for="start">{{ __('admin.startsFrom')}}</label>
											<input value="" name="start" type="text" class="form-control" id="flatpickr-datetime" placeholder="{{ __('admin.startsFrom') }}" required autocomplete="off" />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a start date and time.' : 'يرجى تحديد تاريخ ووقت البداية.' }}</div>
										</div>
									</div>
									<div class="col-lg-6">
										<div class="form-group">
											<label class="form-label mb-1" for="end">{{ __('admin.endsAt')}}</label>
											<input value="" name="end" type="text" class="form-control" id="flatpickr-datetimeLimit" placeholder="{{ __('admin.endsAt') }}" required autocomplete="off" />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select an end date and time.' : 'يرجى تحديد تاريخ ووقت النهاية.' }}</div>
										</div>
									</div>
								</div>

								<div class="row g-4">
									<!-- Left Column -->
									<div class="col-lg-6">
										<!-- Organization Selection -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="organization_id">{{ __('admin.Organization')}}</label>
											<select class="form-control form-select" name="organization_id" id="organization_id" required>
												<option value="">{{ $l == 'en' ? 'Choose organization...' : 'اختر المنظمة...' }}</option>
												@foreach( $organizations as $organization )
													<option value="{{ $organization->id }}">{{ $organization->{'name_' . $l} }}</option>
												@endforeach
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select an organization.' : 'يرجى اختيار منظمة.' }}</div>
										</div>

										<!-- Training Selection -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="training_id">{{ __('admin.Training')}}</label>
											<select class="form-control form-select" name="training_id" id="training_id" required>
												<option value="">{{ $l == 'en' ? 'Choose training...' : 'اختر التدريب...' }}</option>
												@foreach( $trainings as $training )
													<option value="{{ $training->id }}">{{ $training->title }}</option>
												@endforeach
											</select>
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please select a training.' : 'يرجى اختيار تدريب.' }}</div>
										</div>

										<!-- Time Limit -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="time_limit">{{ __('admin.TimeLimit')}}</label>
											<input value="" name="time_limit" type="number" class="form-control" id="time_limit" placeholder="{{ __('admin.TimeLimitPlaceholder') }}" required />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid time limit.' : 'يرجى إدخال حد زمني صحيح.' }}</div>
										</div>

										<!-- Shuffle Questions -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="shuffle">{{ __('admin.shuffleQuestions')}}</label>
											<select class="form-control form-select" name="shuffle" id="shuffle">
												<option value="shuffle_true">{{ __('admin.shuffle_true') }}</option>
												<option value="shuffle_false">{{ __('admin.shuffle_false') }}</option>
											</select>
										</div>
									</div>

									<!-- Right Column -->
									<div class="col-lg-6">
										<!-- Quiz Attempts -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="attempts">{{ __('admin.quizAttempts')}}</label>
											<input value="" name="attempts" type="number" min="1" class="form-control" id="attempts" placeholder="{{ __('admin.quizAttemptsPlaceholder') }}" required />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid number of attempts (minimum 1).' : 'يرجى إدخال عدد صحيح من المحاولات (الحد الأدنى 1).' }}</div>
										</div>

										<!-- Passing Criteria -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="passing_criteria">{{ __('admin.passingCriteria')}}</label>
											<input value="" name="passing_criteria" type="number" class="form-control" id="passing_criteria" placeholder="{{ __('admin.passingCriteriaPlaceholder') }}" required />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid passing criteria.' : 'يرجى إدخال معايير نجاح صحيحة.' }}</div>
										</div>

										<!-- Quiz Status -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="status">{{ __('admin.quizStatus')}}</label>
											<select class="form-control form-select" name="status" id="status">
												<option value="draft">{{ __('admin.Draft') }}</option>
												<option value="published">{{ __('admin.Published') }}</option>
											</select>
										</div>

										<!-- Retry After -->
										<div class="form-group mb-4">
											<label class="form-label mb-1" for="retry_after">{{ __('admin.retryAfter')}}</label>
											<input value="" name="retry_after" type="text" class="form-control" id="retry_after" placeholder="{{ __('admin.retryAfterPlaceholder') }}" required />
											<div class="invalid-feedback">{{ $l == 'en' ? 'Please enter the retry after time.' : 'يرجى إدخال وقت إعادة المحاولة.' }}</div>
										</div>
									</div>
								</div>

								<!-- Form Actions -->
								<div class="row mt-4">
									<div class="col-12">
										<div class="d-flex justify-content-end gap-3">
											<button type="submit" class="btn btn-primary">
												<i class="ti ti-device-floppy me-1"></i>
												{{__('admin.Save')}}
											</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- Quiz Creation Form end -->
		</div>
	</div>
</div>
<!-- END: Content-->
@stop
@section('pagescripts')
	<script type="text/javascript">
		// Custom form validation with date picker support
		(function() {
			'use strict';
			
			// Translation messages
			const messages = {
				startDateRequired: {!! json_encode($l == 'en' ? 'Please select a start date and time.' : 'يرجى تحديد تاريخ ووقت البداية.') !!},
				endDateRequired: {!! json_encode($l == 'en' ? 'Please select an end date and time.' : 'يرجى تحديد تاريخ ووقت النهاية.') !!},
				endDateAfterStart: {!! json_encode($l == 'en' ? 'End date must be after start date.' : 'يجب أن يكون تاريخ النهاية بعد تاريخ البداية.') !!}
			};
			
			// Custom validation function for date fields
			function validateDateFields() {
				const startField = document.querySelector('#flatpickr-datetime');
				const endField = document.querySelector('#flatpickr-datetimeLimit');
				let isValid = true;

				// Validate start date
				if (!startField.value || startField.value.trim() === '') {
					startField.setCustomValidity(messages.startDateRequired);
					startField.classList.add('is-invalid');
					isValid = false;
				} else {
					startField.setCustomValidity('');
					startField.classList.remove('is-invalid');
					startField.classList.add('is-valid');
				}

				// Validate end date
				if (!endField.value || endField.value.trim() === '') {
					endField.setCustomValidity(messages.endDateRequired);
					endField.classList.add('is-invalid');
					isValid = false;
				} else {
					endField.setCustomValidity('');
					endField.classList.remove('is-invalid');
					endField.classList.add('is-valid');
				}

				// Validate that end date is after start date (not equal or before)
				if (startField.value && endField.value) {
					const startDate = new Date(startField.value);
					const endDate = new Date(endField.value);
					
					if (endDate <= startDate) {
						endField.setCustomValidity(messages.endDateAfterStart);
						endField.classList.add('is-invalid');
						endField.classList.remove('is-valid');
						isValid = false;
					}
				}

				return isValid;
			}

			window.addEventListener('load', function() {
				var forms = document.getElementsByClassName('needs-validation');
				var validation = Array.prototype.filter.call(forms, function(form) {
					form.addEventListener('submit', function(event) {
						// Custom validation for date fields
						const dateFieldsValid = validateDateFields();
						
						if (form.checkValidity() === false || !dateFieldsValid) {
							event.preventDefault();
							event.stopPropagation();
						}
						form.classList.add('was-validated');
					}, false);
				});
			}, false);
		})();

		// Custom datetime picker initialization
		document.addEventListener('DOMContentLoaded', function() {
			// Start datetime picker
			const startPicker = document.querySelector('#flatpickr-datetime');
			if (startPicker) {
				flatpickr(startPicker, {
					enableTime: true,
					dateFormat: 'Y-m-d H:i',
					minDate: 'today',
					time_24hr: true,
					onChange: function(selectedDates, dateStr, instance) {
						// Update end date minimum when start date changes
						const endPicker = document.querySelector('#flatpickr-datetimeLimit');
						if (endPicker && endPicker._flatpickr) {
							endPicker._flatpickr.set('minDate', selectedDates[0]);
						}
						
						// Clear custom validity when user selects a date
						startPicker.setCustomValidity('');
						startPicker.classList.remove('is-invalid');
						
						// Revalidate end date if it exists
						if (endPicker.value) {
							const startDate = new Date(dateStr);
							const endDate = new Date(endPicker.value);
							if (endDate <= startDate) {
								endPicker.setCustomValidity(messages.endDateAfterStart);
								endPicker.classList.add('is-invalid');
							} else {
								endPicker.setCustomValidity('');
								endPicker.classList.remove('is-invalid');
							}
						}
					}
				});
			}

			// End datetime picker
			const endPicker = document.querySelector('#flatpickr-datetimeLimit');
			if (endPicker) {
				flatpickr(endPicker, {
					enableTime: true,
					dateFormat: 'Y-m-d H:i',
					minDate: 'today',
					time_24hr: true,
					onChange: function(selectedDates, dateStr, instance) {
						// Clear custom validity when user selects a date
						endPicker.setCustomValidity('');
						endPicker.classList.remove('is-invalid');
						
						// Validate that end date is after start date (not equal or before)
						const startField = document.querySelector('#flatpickr-datetime');
						if (startField.value) {
							const startDate = new Date(startField.value);
							const endDate = new Date(dateStr);
							if (endDate <= startDate) {
								endPicker.setCustomValidity(messages.endDateAfterStart);
								endPicker.classList.add('is-invalid');
							}
						}
					}
				});
			}
		});

		// Add some interactive feedback
		document.querySelectorAll('.form-control, .form-select').forEach(function(element) {
			element.addEventListener('focus', function() {
				this.closest('.form-group').classList.add('focused');
			});
			
			element.addEventListener('blur', function() {
				this.closest('.form-group').classList.remove('focused');
			});
		});
	</script>
@stop
