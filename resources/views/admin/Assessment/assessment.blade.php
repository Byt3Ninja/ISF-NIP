@extends('layouts.admin')

@section('content')
	@php
	$l = app()->getLocale();
	$startups = App\Company::all();
	$founders = App\User::role('Registered')->get();
	@endphp
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			@if (session()->has('message'))
				<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
					{{ __('admin.' . session()->get('message')) }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			@endif

			<section class="row">
				<div class="col-xl-12 col-md-12 col-sm-12">
					<div class="card">
						@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
						<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between border-bottom">
							<h5 class="card-title mb-3 mb-lg-0">{{ $l == 'en' ? 'Assessments' : 'التقييمات' }}</h5>
							<div class="d-flex align-items-center gap-2">
								<div class="dropdown">
									<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-file-export me-2"></i>
										<span>{{ __('admin.Export') }}</span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="exportDropdown">
										<li><a class="dropdown-item" href="javascript:void(0)" id="export-csv">
											<i class="ti ti-file-text me-2"></i>CSV
										</a></li>
										<li><a class="dropdown-item" href="javascript:void(0)" id="export-excel">
											<i class="ti ti-file-spreadsheet me-2"></i>Excel
										</a></li>
									</ul>
								</div>
								<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateAssessment">
									<i class="ti ti-plus"></i>
									<span class="ms-2">{{ __('admin.Add_New') }}</span>
								</button>
							</div>
						</div>
						@endif
						<div class="card-datatable table-responsive">
							<table class="table mb-0" id="assessments-table">
								<thead class="table-light">
									<tr>
										<th>#</th>
										<th>{{ __('admin.Company') }}</th>
										<th>{{ __('admin.Founder') }}</th>
										<th>{{ __('admin.start_date') }}</th>
										<th>{{ __('admin.end_date') }}</th>
										<th>{{ __('admin.Created at') }}</th>
										@if( $_user->ableToAny(['assessments.show', 'question-assessments.questions_manage', 'assessments.edit', 'assessments.update', 'assessments.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
									</tr>
								</thead>
								<tbody>
									@php $c = 1; @endphp
									@foreach ($assessments as $assessment)
										<tr data-founder-id="{{ $assessment->user_id }}" data-company-id="{{ optional($assessment->user->company)->id ?? '0' }}">
											<td>{{$c++}}</td>
											<td>
												@if($assessment->user->company)
												<a 
													href="{{ route('company.profile_view', $assessment->user->company->id) }}"
													class="d-flex align-items-center gap-2"
												>
													<img 
														src="{{ $assessment->user->company->logo ?? '/images/logo-placeholder.png' }}" 
														alt="logo" 
														class="rounded-circle border"
														height="40" width="40"
													/>
													<span class="fs-6 mb-0 text-dark">{{ $assessment->user->company->{'name_'.app()->getLocale()} }}</span>
												</a>
												@else
												<span class="text-muted">{{ $l == 'en' ? 'No Company' : 'لا توجد شركة' }}</span>
												@endif
											</td>
											<td>{{ $assessment->user->{'first_name_'.$l} }} {{ $assessment->user->{'last_name_'.$l} }}</td>
											<td>{{$assessment->start_date }}</td>
											<td>{{$assessment->end_date }}</td>
											<td>{{$assessment->created_at}}</td>
											@if( $_user->ableToAny(['assessments.show', 'assessments.questions_manage', 'assessments.edit', 'assessments.update', 'assessments.destroy'], $permissions) )
											<td>
												@if( $_user->ableTo('assessments.show', $permissions) )
												<a 
													class="btn btn-icon btn-label-primary rounded-circle" 
													href="{{ route('assessments.show', $assessment->id) }}"
													data-bs-toggle="tooltip"
													data-bs-placement="top"
													title="{{ __('admin.View') }}"
												>
													<i class="ti ti-eye"></i>
												</a>
												@endif
												@if( $_user->ableTo('assessments.questions_manage', $permissions) )
												<a 
													class="btn btn-icon btn-label-secondary rounded-circle"
													href="{{ route('assessments.questions_manage', $assessment->id) }}"
													data-bs-toggle="tooltip"
													data-bs-placement="top"
													title="{{ __('admin.manageQuestions') }}">
													<i class="ti ti-list-check"></i>
												</a>
												@endif
												@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )
													<button 
														class="btn btn-icon btn-label-info rounded-circle" 
														data-bs-toggle="modal" 
														data-bs-target="#EditAssessment{{ $assessment->id }}"
														data-bs-placement="top"
														title="{{ __('admin.Edit') }}"
													>
														<i class="ti ti-pencil"></i>
													</button>
												@endif
												@if( $_user->ableTo('assessments.destroy', $permissions) )
													<form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST" style="display: inline-block;">
														@csrf
														@method('DELETE')
														<button 
															class="btn btn-icon btn-label-danger rounded-circle" 
															type="submit" 
															onclick="return confirmDelete(event, {{ $assessment->id }})"
															data-bs-toggle="tooltip"
															data-bs-placement="top"
															title="{{ __('admin.Delete') }}"
														>
															<i class="ti ti-trash"></i>
														</button>
													</form>
												@endif
											</td>
											@endif
										</tr>
										@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )
										<!-- Edit Assessment Modal -->
										<div class="modal fade" id="EditAssessment{{ $assessment->id }}" tabindex="-1" aria-labelledby="EditAssessmentLabel{{ $assessment->id }}" aria-hidden="true" role="dialog" data-bs-backdrop="static">
											<div class="modal-dialog modal-md" role="document">
												<div class="modal-content" style="background-color: white;">
													<div class="modal-header">
														<h5 class="modal-title mb-0" id="EditAssessmentLabel{{ $assessment->id }}">{{__('admin.Edit')}} ({{$assessment->user->name}})</h5>
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
													</div>
													<div class="modal-body">
														<form class="pt-0" action="{{ route('assessments.update', $assessment->id) }}" method="POST">
															@csrf
															@method('PUT')
															<div class="row">
																<div class="col-md-6 mb-4">
																	<label class="form-label fs-6"> {{ $l == 'en' ? 'User' : 'المستخدم' }} </label>
																	<select class="form-select edit-select2" name="user_id">
																		<option value="" disabled selected> {{ $l == 'en' ? 'Select User' : 'اختر المستخدم ' }} </option>
																		@foreach ($users as $user)
																			<option value="{{ $user->id }}" {{ $user->id == $assessment->user_id ? 'selected' : '' }}	>{{ $user->name }}</option>
																		@endforeach
																	</select>
																</div>
																<div class="col-md-6 mb-4">
																	<label for="start_date" class="form-label" >{{__('admin.start_date')}}</label>
																	<input class="form-control date"
																		id="start_date"
																		type="text"
																		name="start_date"
																		autocomplete="off"
																		value="{{ $assessment->start_date }}"
																	/>
																</div>
																<div class="col-md-6 mb-4">
																	<label for="end_date" class="form-label" >{{__('admin.end_date')}}</label>
																	<input class="form-control date"
																		id="end_date"
																		type="text"
																		name="end_date"
																		autocomplete="off"
																		value="{{ $assessment->end_date }}"
																	/>
																</div>
															</div>
															<div class="d-flex align-items-center justify-content-end gap-3 mt-4">
																<button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">{{__('admin.Cancel')}}</button>
																@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )
																<button type="submit" class="btn btn-primary data-submit">{{__('admin.submit')}}</button>
																@endif
															</div>
														</form>
													</div>
												</div>
											</div>
										</div>
										<!--/ Edit Assessment Modal -->
										@endif
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>

	<!-- END: Content-->
	@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
		<!-- Create Assessment Modal -->
	<div class="modal fade" id="CreateAssessment" tabindex="-1" aria-labelledby="CreateAssessmentLabel" aria-hidden="true" role="dialog" data-bs-backdrop="static">
		<div class="modal-dialog modal-md" role="document">
			<div class="modal-content" style="background-color: white;">
				<div class="modal-header">
					<h5 class="modal-title mb-0" id="CreateAssessmentLabel">{{__('admin.Add new Assessment')}}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form method="POST" action="{{route('assessments.store')}}" class="pt-0" >
						@csrf
						<div class="row">
							<div class="col-md-6 mb-4">
								<label class="form-label fs-6"> {{ __('admin.user') }} </label>
								<select class="form-select select2" name="user_id">
									<option value="" disabled selected> {{ $l == 'en' ? 'Select User' : 'اختر مستخدم ' }} </option>
									@foreach ($users as $user)
										<option value="{{ $user->id }}">{{ $user->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="col-md-6 mb-4">
								<label for="start_date" class="form-label" >{{__('admin.start_date')}}</label>
								<input class="form-control date"
									id="start_date"
									type="text"
									name="start_date"
									autocomplete="off"
								/>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-end gap-3 mt-4">
							<button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">{{__('admin.Cancel')}}</button>
							@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
							<button type="submit" class="btn btn-primary me-sm-4 data-submit">{{__('admin.submit')}}</button>
							@endif
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!--/ Create Assessment Modal -->
	@endif
@stop

@section('pagescripts')
	<script>
		// Initalize datepicker
		$('.date').flatpickr({
			monthSelectorType: 'static',
			allowInput: true,
			onReady: function (selectedDates, dateStr, instance) {
				let btn = document.createElement("button");
				btn.type = "button";
				btn.innerHTML = "Clear";
				btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
				btn.addEventListener("click", function () {
					instance.clear();
					instance.close();
					$('#assessments-table').DataTable().draw();
				});
				instance.calendarContainer.appendChild(btn);
			}
		});

		$(document).ready(function() {
			// Register custom search BEFORE initializing DataTable
			$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
				// Only apply to our specific table
				if (settings.nTable.id !== 'assessments-table') {
					return true;
				}
				
				// Get the TR element using the internal DataTables data
				const api = new $.fn.dataTable.Api(settings);
				const rowNode = api.row(dataIndex).node();
				
				if (!rowNode) {
					return true;
				}
				
				// Company filter
				const companyVal = $('#company-filter').val();
				if (companyVal && companyVal !== '') {
					const companyId = $(rowNode).attr('data-company-id');
					console.log('Company Filter - Selected:', companyVal, 'Row Company ID:', companyId);
					if (!companyId || companyId === '0' || companyId != companyVal) {
						return false;
					}
				}
				
				// Founder filter
				const founderVal = $('#founder-filter').val();
				if (founderVal && founderVal !== '') {
					const founderId = $(rowNode).attr('data-founder-id');
					console.log('Founder Filter - Selected:', founderVal, 'Row Founder ID:', founderId);
					if (!founderId || founderId != founderVal) {
						return false;
					}
				}
				
				// Date filters
				const dateFrom = $('#date-from-filter').val();
				const dateTo = $('#date-to-filter').val();
				const startDate = data[3]; // start_date column
				const endDate = data[4]; // end_date column
				
				if (dateFrom && startDate < dateFrom) {
					return false;
				}
				if (dateTo && endDate > dateTo) {
					return false;
				}
				
				return true;
			});

			const table = $('#assessments-table').DataTable({
				responsive: true,
				pageLength: 10,
				columnDefs: [{
					orderable: false,
					targets: [5]
				}],
				order: [[5, 'desc']],
				dom: `
				<"row g-2 mb-8 mt-4"
						<"col-12 col-md-4 col-lg-2 d-flex align-items-center h-px-40"l>
						<"col-12 col-md-4 col-lg-2 company-filter d-flex align-items-center h-px-40">
						<"col-12 col-md-4 col-lg-2 founder-filter d-flex align-items-center h-px-40">
						<"col-12 col-md-4 col-lg-2 date-from d-flex align-items-center h-px-40">
						<"col-12 col-md-4 col-lg-2 date-to d-flex align-items-center h-px-40">
						<"col-12 col-md-4 col-lg-2 d-flex align-items-center h-px-40"f>
				>
				t
				<"row"
						<"col-sm-12 col-md-6"i>
						<"col-sm-12 col-md-6 d-flex justify-content-end"p>
				>
				`,
				displayLength: 7,
				lengthMenu: [7, 10, 25, 50, 75, 100],
				buttons: [
					{
						extend: 'csv',
						text: '<i class="ti ti-file-text me-1"></i>CSV',
						className: 'btn btn-label-secondary buttons-csv d-none',
						title: `Assessments_Export_${new Date().toISOString().slice(0, 10)}`,
						exportOptions: { 
							columns: [0, 1, 2, 3, 4, 5]
						}
					},
					{
						extend: 'excel',
						text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
						className: 'btn btn-label-secondary buttons-excel d-none',
						title: `Assessments_Export_${new Date().toISOString().slice(0, 10)}`,
						exportOptions: { 
							columns: [0, 1, 2, 3, 4, 5]
						}
					}
				],
				language: {
					@if($l == 'ar')
					url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json',
					search: "",
					searchPlaceholder: "ابحث هنا...",
					@else
					search: "",
					searchPlaceholder: "{{ __('admin.Search') }}:",
					lengthMenu: "{{ __('admin.show') }} _MENU_ {{ __('admin.Entries') }}",
					info: "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.of') }} _TOTAL_ {{ __('admin.Entries') }}",
					infoEmpty: "{{ __('admin.showing') }} 0 {{ __('admin.To') }} 0 {{ __('admin.of') }} 0 {{ __('admin.Entries') }}",
					infoFiltered: "({{ __('admin.filteredFrom') }} _MAX_ {{ __('admin.totalEntries') }})",
					paginate: {
							first: "{{ __('admin.First') }}",
							last: "{{ __('admin.Last') }}",
							next: "{{ __('admin.Next') }}",
							previous: "{{ __('admin.Previous') }}"
					},
					emptyTable: "{{ __('admin.noDataAvailable') }}",
					zeroRecords: "{{ __('admin.noMatchingRecords') }}"
					@endif
				},
				initComplete: function (settings, json) {
					const lang = '{{ $l }}';
					
					// Company filter
					const companyFilter = $(`<select id="company-filter" class="form-select form-select-sm" style="width: 100%;"><option value="">{{ __('cycles.Select Startup') }}</option></select>`)
						.appendTo('#assessments-table_wrapper .company-filter')
						.on('change', function () {
							console.log('Company filter changed to:', $(this).val());
							table.draw();
						});

					// Populate company filter with unique companies
					const companies = {!! json_encode($startups->mapWithKeys(function($c) use ($l) { return [$c->id => $c->{'name_'.$l}]; })) !!};
					console.log('Companies data:', companies);
					Object.entries(companies).forEach(([id, name]) => {
						companyFilter.append(`<option value="${id}">${name}</option>`);
					});

					// Founder filter
					const founderFilter = $(`<select id="founder-filter" class="form-select form-select-sm" style="width: 100%;"><option value="">{{ __('admin.Select_Founder') }}</option></select>`)
						.appendTo('#assessments-table_wrapper .founder-filter')
						.on('change', function () {
							console.log('Founder filter changed to:', $(this).val());
							table.draw();
						});

					// Populate founder filter
					const foundersData = @json($founders);
					console.log('Founders data:', foundersData);
					foundersData.forEach(founder => {
						const name = founder[`first_name_${lang}`] + ' ' + founder[`last_name_${lang}`];
						founderFilter.append(`<option value="${founder.id}">${name}</option>`);
					});

					// Date from filter
					const dateFromFilter = $(`<input type="text" id="date-from-filter" class="form-control form-control-sm" style="width: 100%;" placeholder="{{ __('cycles.Start_Date')}}">`)
						.appendTo('#assessments-table_wrapper .date-from');

					// Date to filter
					const dateToFilter = $(`<input type="text" id="date-to-filter" class="form-control form-control-sm" style="width: 100%;" placeholder="{{ __('cycles.End_Date')}}">`)
						.appendTo('#assessments-table_wrapper .date-to');

					// Initialize flatpickr for date filters
					dateFromFilter.flatpickr({
						monthSelectorType: 'static',
						allowInput: true,
						dateFormat: 'Y-m-d',
						onChange: function() {
							table.draw();
						},
						onReady: function (selectedDates, dateStr, instance) {
							let btn = document.createElement("button");
							btn.type = "button";
							btn.innerHTML = "Clear";
							btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
							btn.addEventListener("click", function () {
								instance.clear();
								instance.close();
								table.draw();
							});
							instance.calendarContainer.appendChild(btn);
						}
					});

					dateToFilter.flatpickr({
						monthSelectorType: 'static',
						allowInput: true,
						dateFormat: 'Y-m-d',
						onChange: function() {
							table.draw();
						},
						onReady: function (selectedDates, dateStr, instance) {
							let btn = document.createElement("button");
							btn.type = "button";
							btn.innerHTML = "Clear";
							btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
							btn.addEventListener("click", function () {
								instance.clear();
								instance.close();
								table.draw();
							});
							instance.calendarContainer.appendChild(btn);
						}
					});

					// Debug: Log all row data attributes
					console.log('=== Debugging Row Data ===');
					$('#assessments-table tbody tr').each(function(index) {
						const founderId = $(this).attr('data-founder-id');
						const companyId = $(this).attr('data-company-id');
						console.log(`Row ${index}: Founder ID = ${founderId}, Company ID = ${companyId}`);
					});
				}
			});
		});

		// Export functionality
		$('#export-csv').on('click', function(e) {
			e.preventDefault();
			$('#assessments-table').DataTable().button('.buttons-csv').trigger();
		});

		$('#export-excel').on('click', function(e) {
			e.preventDefault();
			$('#assessments-table').DataTable().button('.buttons-excel').trigger();
		});

		// Confirm delete function
		function confirmDelete(event, id) {
			event.preventDefault();
			Swal.fire({
				title: "{{__('admin.SureDelete')}}",
				text: "{{__('admin.Recover')}}",
				icon: "warning",
				showCancelButton: true,
				confirmButtonText: "{{ __('admin.DYes') }}",
				cancelButtonText: "{{ __('admin.No') }}",
				reverseButtons: true,
				customClass: {
					confirmButton: 'btn btn-danger me-2',
					cancelButton: 'btn btn-label-secondary',
				},
				buttonsStyling: false,
			}).then((result) => {
				if (result.isConfirmed) {
					$(event.target).closest('form').submit();
				}
			});
			return false;
		}

		// Initialize select2 for CREATE form dropdowns only
		$('#CreateAssessment .select2').select2({
			dropdownParent: $('#CreateAssessment')
		});

		// Initialize Select2 for EDIT modals when shown
		$(document).on('shown.bs.modal', '[id^="EditAssessment"]', function() {
			const $modal = $(this);
			
			// Destroy existing select2 instances
			$modal.find('.edit-select2').each(function() {
				if ($(this).hasClass('select2-hidden-accessible')) {
					$(this).select2('destroy');
				}
			});
			
			// Re-initialize with proper settings
			$modal.find('.edit-select2').select2({
				dropdownParent: $modal,
				width: '100%'
			});
			
			// Reinitialize flatpickr for date fields in this modal
			$modal.find('.date').flatpickr({
				enableTime: false,
				dateFormat: "Y-m-d",
			});
		});

		// Clean up when modal is hidden
		$(document).on('hidden.bs.modal', '[id^="EditAssessment"]', function() {
			const $modal = $(this);
			$modal.find('.edit-select2').each(function() {
				if ($(this).hasClass('select2-hidden-accessible')) {
					$(this).select2('destroy');
				}
			});
		});
	</script>
@stop