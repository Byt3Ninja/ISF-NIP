@extends('layouts.admin')

@section('title', __('admin.Questions Bank') )
@section('subTitle', __('admin.Questions Bank List'))
@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-overlay"></div>
			<div class="content-header row">
				@if( session()->has('success') )
					<div class="col-md-12">
						<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
              {{ __('admin.'. session()->get('success')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
				@if( session()->has('error') )
					<div class="col-md-12">
						<div class="alert alert-danger alert-dismissible mb-2" role="alert">
              {{ __('admin.'.session()->get('error')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					</div>
				@endif
			</div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="row mb-4">
						<div class="col-12">
							<a 
                href="{{ route('questions-bank.create') }}"
                class="btn btn-primary float-end">
                <i class="ti ti-plus me-2"></i>
                {{ __('admin.Add New')}} 
              </a>
						</div>
					</div>
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-content">
								<div class="card-header row border-bottom mb-4">
									<div class="col-6 col-md-8">
										<h4 class="card-title">{{__('admin.Questions Bank List')}}</h4>
									</div>
									<div class="col-6 col-md-4">
										<select id="filterGroup" class="form-select Type wight-40">
											<option value="all">{{ __('admin.All_Types') }}</option>
											<option value="quiz">{{ __('admin.Quiz') }}</option>
											<option value="survey">{{ __('admin.Survey') }}</option>
											<option value="assessment">{{ __('admin.Assessment') }}</option>
										</select>
									</div>
								</div>
				
								<div class="card-body">
									<!-- datatable start -->
									<div class="table-responsive">
										<table id="organizations-list-datatable" class="table datatables-ajax">
											<thead>
											<tr class="table-light">
												<th>#</th>
												<th>{{__('admin.question')}}</th>
												<th class="text-center">{{__('admin.Organization')}}</th>
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
																<span class="badge ba-label-danger rounded-pill">{{ __('admin.difficulty_hard') }}</span>
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
					</div>
				</section>
			</div>
		</div>
	</div>
@stop
@section('scripts')
	<script type="text/javascript">
		$(document).ready(function() {
			// Initialize DataTable
			$('#organizations-list-datatable').DataTable({
				responsive: true,
				pageLength: 10,
				lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
				language: {
					@if($l == 'ar')
					"url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
					@else
					"search": "{{ __('admin.Search') }}:",
					"lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
					"info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
					"infoEmpty": "{{ __('admin.no_entries_available') }}",
					"infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
					"paginate": {
						"first": "{{ __('admin.First') }}",
						"last": "{{ __('admin.Last') }}",
						"next": "{{ __('admin.Next') }}",
						"previous": "{{ __('admin.Previous') }}"
					},
					"emptyTable": "{{ __('admin.No_data_available_in_table') }}",
					"zeroRecords": "{{ __('admin.No_matching_records_found') }}"
					@endif
				},
				dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
				displayLength: 10,
				lengthMenu: [10, 25, 50, 75, 100],
				order: [[0, 'desc']],
				columnDefs: [
					{
						targets: [5], // Actions column
						orderable: false,
						searchable: false
					}
				]
			});

			// Type filter functionality
			$('.Type').change(function () {
				var selectedGroup = $(this).val();
				const url = new URL(window.location.origin + window.location.pathname);
				if (selectedGroup !== 'all') {
					url.searchParams.set('search', selectedGroup);
				}
				window.location.href = url.toString();
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
								// Remove the row from DataTable if still on same page
								$('#organizations-list-datatable').DataTable().row($(e.target).closest('tr')).remove().draw();
							}
						).fail(function(error) { 
							console.log(error);
							Swal.fire({
								icon: 'error',
								title: "{{ __('admin.Error') }}!",
								text: "{{ __('admin.SomethingWentWrong') }}",
							});
						});
					}
				});
			});
		});
	</script>
@stop
