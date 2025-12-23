@extends('layouts.admin')

@section('title', __('admin.Questions Bank') )
@section('subTitle', __('admin.Questions Bank List'))
@section('content')

<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-header row">
				<!-- Navigation Tabs -->
				<nav class="nav-align-top mb-6">
					@php
						$tabs = [
							['title' => __('admin.Categories'), 'url' => 'dashboard/question-categories', 'icon' => 'category-2'],
							['title' => __('admin.Questions'), 'url' => 'dashboard/questions-bank', 'icon' => 'question-mark'],
							['title' => __('admin.weights'), 'url' => 'dashboard/weights', 'icon' => 'weight'],
						]
					@endphp
					<ul class="nav nav-pills">
						@foreach($tabs as $tab)
							<li class="nav-item">
								<a href="/{{ $tab['url'] }}" class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}">
									<i class="ti ti-{{ $tab['icon'] }} me-2"></i>
									{{ $tab['title'] }}
								</a>
							</li>
						@endforeach
					</ul>
				</nav>
				<!--/ Navigation Tabs -->

				@if( session()->has('success') )
					<div class="col-md-12 mb-6">
						<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
              {{ __('admin.'. session()->get('success')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
				@if( session()->has('error') )
					<div class="col-md-12 mb-6">
						<div class="alert alert-danger alert-dismissible mb-2" role="alert">
              {{ __('admin.'.session()->get('error')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
			</div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
								<h5 class="card-title mb-3 mb-md-0">{{__('admin.Questions Bank List')}}</h5>
								<div class="d-flex align-items-center gap-3">
									<div class="dropdown">
										<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
										</button>
										<ul class="dropdown-menu" aria-labelledby="exportDropdown">
											<li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
											<li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
										</ul>
									</div>
									<a 
										href="{{ route('questions-bank.create') }}"
										class="btn btn-primary">
										<i class="ti ti-plus me-2"></i>
										{{ __('admin.Add New')}} 
									</a>
								</div>
							</div>
							<div class="card-content">				
								<div class="card-body">
									<!-- Filter Controls -->
									<div class="row mb-8 mt-8">
										<div class="col-md-1 mb-3 mb-md-0">
											<label class="form-label">{{ __('admin.Show') }}</label>
											<select id="showEntries" class="form-select form-select-sm">
												<option value="10">10</option>
												<option value="25">25</option>
												<option value="50">50</option>
												<option value="100">100</option>
												<option value="-1">{{ __('admin.All') }}</option>
											</select>
										</div>
										<div class="col-md-2 mb-3 mb-md-0">
											<label class="form-label">{{ __('admin.Organization') }}</label>
											<select id="organizationFilter" class="form-select form-select-sm">
												<option value="">{{ __('admin.All') }}</option>
											</select>
										</div>
										<div class="col-md-2 mb-3 mb-md-0">
											<label class="form-label">{{ __('admin.questionGroup') }}</label>
											<select id="questionGroupFilter" class="form-select form-select-sm">
												<option value="">{{ __('admin.All') }}</option>
											</select>
										</div>
										<div class="col-md-2 mb-3 mb-md-0">
											<label class="form-label">{{ __('admin.question_Type') }}</label>
											<select id="questionTypeFilter" class="form-select form-select-sm">
												<option value="">{{ __('admin.All') }}</option>
											</select>
										</div>
										<div class="col-md-2 mb-3 mb-md-0">
											<label class="form-label">{{ __('admin.question_difficulty') }}</label>
											<select id="difficultyFilter" class="form-select form-select-sm">
												<option value="">{{ __('admin.All') }}</option>
											</select>
										</div>
										<div class="col-md-3 ps-md-8 mb-3 mb-md-0">
											<label class="form-label">{{ __('admin.Search') }}</label>
											<div class="input-group input-group-sm">
												<input type="text" id="globalSearch" class="form-control" placeholder="{{ __('admin.Search') }}...">
											</div>
										</div>
									</div>
								</div>
								<!-- datatable start -->
								<div class="card-datatable table-responsive">
									<table id="organizations-list-datatable" class="table datatables-ajax">
										<thead>
										<tr class="table-light">
											<th>#</th>
											<th>{{__('admin.question')}}</th>
											<th class="text-center">{{__('admin.Organization')}}</th>
											<th class="text-center">{{__('admin.questionGroup')}}</th>
											<th class="text-center">{{__('admin.question_Type')}}</th>
											<th class="text-center">{{__('admin.question_difficulty')}}</th>
											<th class="text-center">{{__('admin.Actions')}}</th>
										</tr>
										</thead>
										<tbody>
											@foreach( $questions as $question )
												<tr id="row_{{ $question->id }}" data-group="{{ $question->group }}">
													<td><a href="{{ URL::to('dashboard/questions-bank/' . $question->id . '/edit') }}">{{ $question->id }}</a></td>
													<td>{{ $question->question }}</td>
													<td>
														@foreach( $question->organizations as $organization )
															<div>
																<a href="{{ URL::to('dashboard/organizations/' . optional($organization)->id . '/edit') }}">
																	{{ optional($organization)->name }}
																</a>
															</div>
														@endforeach
													</td>
													<td class="text-center">
														@if( $question->group == 'quiz' )
															<span class="badge bg-label-primary rounded-pill">{{ __('admin.Quiz') }}</span>
														@elseif( $question->group == 'survey' )
															<span class="badge bg-label-info rounded-pill">{{ __('admin.Survey') }}</span>
														@elseif( $question->group == 'assessment' )
															<span class="badge bg-label-warning rounded-pill">{{ __('admin.Assessment') }}</span>
														@else
															<span class="badge bg-label-secondary rounded-pill">{{ ucfirst($question->group) }}</span>
														@endif
													</td>
													<td class="text-center">
														@if( $question->group == 'quiz' )
														<span class="badge bg-label-secondary rounded-pill">
															{{ $question->type == 'mcq' ? __('admin.type_mcq') : ( $question->type == 'tfq' ? __('admin.type_tfq') : '-') }}
														</span>
														@elseif( $question->group == 'survey' )
														<span class="badge bg-label-success rounded-pill">
															{{ $question->survey_type == 'stars' ? __('admin.survey_type_stars') : ( $question->survey_type == 'radio' ? __('admin.survey_type_radio') : __('admin.surveyQuestionAndAnswer')) }}
														</span>

														@elseif( $question->group == 'assessment' )
														<span class="badge bg-label-success rounded-pill">
															{{ ( $question->survey_type == null ? __('admin.Assessment') : '-') }}
														</span>
														@endif
													</td>
													<td class="text-center">
														@if( $question->group == 'quiz' )
															@if( $question->difficulty == 'easy' )
															<span class="badge bg-label-success rounded-pill"> {{ __('admin.difficulty_easy') }}</span>
															@elseif( $question->difficulty == 'medium' )
															<span class="badge bg-label-warning rounded-pill">{{ __('admin.difficulty_medium') }}</span>
															@elseif( $question->difficulty == 'hard' )
															<span class="badge bg-label-danger rounded-pill">{{ __('admin.difficulty_hard') }}</span>
															@endif
														@else
															-
														@endif
													</td>
													<td class="d-flex align-items-center justify-content-center gap-3">
														<a href="{{ route('questions-bank.edit', ['questions_bank' => $question->id]) }}" class="btn btn-sm btn-label-info btn-icon rounded-pill"> <i class="ti ti-edit"></i></a>
														<button id='deleteAction' data-id='{{ $question->id }}' data-url="{{ route('questions-bank.destroy', ['questions_bank' => $question->id]) }}" class="btn btn-sm btn-label-danger btn-icon rounded-circle"> <i class="ti ti-trash"></i></button>
													</td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
		</div>
	</div>
@stop
@section('scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			const isArabic = "{{ $l }}" === "ar";
			// Initialize DataTable
			var table = $('#organizations-list-datatable').DataTable({
				responsive: true,
				pageLength: 10,
				lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
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
				dom: '<"row"<"col-sm-12"t>><"row"<"col-sm-5"i><"col-sm-7"p>>',
				order: [[0, 'desc']],
				columnDefs: [
					{
						targets: [6], // Actions column (updated from 5 to 6 due to new column)
						orderable: false,
						searchable: false
					}
				],
				buttons: [
					{
						extend: 'excel',
						title: isArabic ? 'بنك الأسئلة' : 'Questions Bank',
						exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
					},
					{
						extend: 'csv',
						title: isArabic ? 'بنك الأسئلة' : 'Questions Bank',
						exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
					},
				],
			});

			// Export button handlers
			$('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
			$('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

			// Populate filter dropdowns based on current table data
			function populateFilters() {
				var organizationOptions = [];
				var questionGroupOptions = [];
				var questionTypeOptions = [];
				var difficultyOptions = [];

				// Get unique values from table
				table.rows().every(function() {
					var rowNode = this.node();
					var organization = $(rowNode).find('td:eq(2)').text().trim();
					var questionGroup = $(rowNode).find('td:eq(3)').text().trim();
					var questionType = $(rowNode).find('td:eq(4)').text().trim();
					var difficulty = $(rowNode).find('td:eq(5)').text().trim();

					if (organization && organizationOptions.indexOf(organization) === -1) {
						organizationOptions.push(organization);
					}
					if (questionGroup && questionGroupOptions.indexOf(questionGroup) === -1) {
						questionGroupOptions.push(questionGroup);
					}
					if (questionType && questionTypeOptions.indexOf(questionType) === -1) {
						questionTypeOptions.push(questionType);
					}
					if (difficulty && difficulty !== '-' && difficultyOptions.indexOf(difficulty) === -1) {
						difficultyOptions.push(difficulty);
					}
				});

				// Sort options
				organizationOptions.sort();
				questionGroupOptions.sort();
				questionTypeOptions.sort();
				difficultyOptions.sort();

				// Populate dropdowns
				var organizationSelect = $('#organizationFilter');
				var questionGroupSelect = $('#questionGroupFilter');
				var questionTypeSelect = $('#questionTypeFilter');
				var difficultySelect = $('#difficultyFilter');

				organizationSelect.empty().append('<option value="">{{ __('admin.All') }}</option>');
				questionGroupSelect.empty().append('<option value="">{{ __('admin.All') }}</option>');
				questionTypeSelect.empty().append('<option value="">{{ __('admin.All') }}</option>');
				difficultySelect.empty().append('<option value="">{{ __('admin.All') }}</option>');

				$.each(organizationOptions, function(index, value) {
					organizationSelect.append('<option value="' + value + '">' + value + '</option>');
				});

				$.each(questionGroupOptions, function(index, value) {
					questionGroupSelect.append('<option value="' + value + '">' + value + '</option>');
				});

				$.each(questionTypeOptions, function(index, value) {
					questionTypeSelect.append('<option value="' + value + '">' + value + '</option>');
				});

				$.each(difficultyOptions, function(index, value) {
					difficultySelect.append('<option value="' + value + '">' + value + '</option>');
				});
			}

			// Initialize filters
			populateFilters();

			// Show entries change
			$('#showEntries').on('change', function() {
				var value = $(this).val();
				table.page.len(value).draw();
			});

			// Filter functionality
			$('#organizationFilter').on('change', function() {
				var value = $(this).val();
				table.column(2).search(value).draw();
			});

			$('#questionGroupFilter').on('change', function() {
				var value = $(this).val();
				table.column(3).search(value).draw();
			});

			$('#questionTypeFilter').on('change', function() {
				var value = $(this).val();
				table.column(4).search(value).draw();
			});

			$('#difficultyFilter').on('change', function() {
				var value = $(this).val();
				table.column(5).search(value).draw();
			});

			// Global search functionality
			$('#globalSearch').on('keyup', function() {
				table.search(this.value).draw();
			});

			// Type filter functionality
			$('.Type').change(function () {
				var selectedGroup = $(this).val();
				const url = new URL(window.location.origin + window.location.pathname);
				if (selectedGroup !== 'all') {
					url.searchParams.set('search', selectedGroup);
				} else {
					url.searchParams.delete('search');
				}
				window.location.href = url.toString();
			});

			// Client-side filtering for immediate visual feedback
			$('#filterGroup').on('change', function() {
				var selectedGroup = $(this).val();
				var table = $('#organizations-list-datatable').DataTable();
				
				if (selectedGroup === 'all') {
					table.rows().show();
				} else {
					table.rows().every(function() {
						var rowData = this.data();
						var rowNode = this.node();
						var groupValue = $(rowNode).attr('data-group');
						
						if (groupValue === selectedGroup) {
							$(rowNode).show();
						} else {
							$(rowNode).hide();
						}
					});
				}
				table.draw();
			});

			// Handle delete action
			$(document).on("click", "#deleteAction", function (e) {
				e.preventDefault();
				var url = $(this).data('url');
				var id = $(this).data('id');
				var token = '{{ csrf_token() }}';
				
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
				})
				.then((result) => {
					if (result.value) {
						$.post(url, {'_method': 'DELETE' , '_token': token},
							function(response) {
								Swal.fire({
									icon: 'success',
									title: "{{ __('admin.Deleted') }}!",
									html: "{{ __('admin.DeletedSuccessfully') }}.",
									showCancelButton: false,
									showConfirmButton: true,
									confirmButtonText: "{{ __('admin.OK') }}",
									customClass: {
											confirmButton: 'btn btn-primary',
									},
									buttonsStyling: false,
								});
								// Remove the row from DataTable
								table.row($(e.target).closest('tr')).remove().draw();
								// Repopulate filters after row removal
								populateFilters();
							}
						).fail(function(error) { 
							releaseFailureAlert({
								title: "{{ __('admin.Error') }}!",
								message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						});
					}
				});
			});
		});
	</script>
@stop
