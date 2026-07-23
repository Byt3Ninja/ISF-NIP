@extends('layouts.admin')

@section('title', __('admin.Questions Categories') )
@section('subTitle', __('admin.Questions Categories` List'))

@section('content')
<div class="">
	<div class="">
		@php
			$tabs = [
				['title' => __('admin.Questions Bank'), 'url' => 'dashboard/questions-bank', 'icon' => 'question-mark'],
				['title' => __('admin.Categories'), 'url' => 'dashboard/question-categories', 'icon' => 'category-2'],
				['title' => __('admin.weights'), 'url' => 'dashboard/weights', 'icon' => 'weight'],
			]
		@endphp
		<x-navigation-pills :tabs="$tabs" />

		@if( session()->has('success') )
			<div class="col-md-12 mb-6">
				<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					<div class="d-flex align-items-center"> <i class="ti ti-thumb-up me-3 ti-md"></i> <span> {{ __('admin.'. session()->get('success')) }} </span> </div>
				</div>
			</div>
		@endif
		@if( session()->has('error') )
			<div class="col-md-12 mb-6">
				<div class="alert alert-danger alert-dismissible mb-2" role="alert">
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span>{{ __('admin.'.session()->get('error')) }} </span> </div>
				</div>
			</div>
		@endif

			<div class="content-body">
				<section class="organizations-list-wrapper">
					<div class="organizations-list-table">
						<div class="card">
							<div class="card-content">
                <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center">
                  <h5 class="card-title mb-3 mb-md-0">{{__('admin.Questions Categories` List')}}</h5>
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
										<a href="{{ route('question-categories.create') }}" class="btn btn-primary">
											<i class="ti ti-plus me-2"></i>
											{{ __('admin.Add New')}} 
										</a>
									</div>
                </div>
								<!-- datatable start -->
								<div class="card-datatable table-responsive datatable-basic">
									<table id="categoriesTable" class="table datatables-ajax">
										<thead>
										<tr class="table-light">
											<th>#</th>
											<th>{{__('admin.categoryTitle')}}</th>
											<th class="text-center">{{__('admin.Organization')}}</th>
											<th class="text-center">{{__('admin.categoryQuestions')}}</th>
											<th class="text-center">{{__('admin.Actions')}}</th>
										</tr>
										</thead>
										<tbody>
											@foreach( $categories as $category )
											<tr id="row_{{ $category->id }}">
												<td class="text-center">
													<a href="{{ route('question-categories.edit', ['question_category' => $category->id]) }}">{{ $category->id }}</a></td>
												<td class="text-center">
													{{ $category->title }}</td>
												<td class="text-center">
													<a href="{{ $_user->ableToAny(['organization.wizard.edit', 'organizations.edit'], $permissions) ? route('organization.wizard.edit', $category->organization->id) . '?step=1' : 'javascript:void(0);' }}"> {{ $category->organization->{'name_'.$l} }}</a>
												</td>
												<td class="text-center">
													 {{ $category->questions->count() }} </td>
												<td class="text-center">
													<a 
														href="{{ route('question-categories.edit', ['question_category' => $category->id]) }}" 
														class="btn btn-label-primary btn-sm"
													>
														<i class="ti ti-edit ti-xs me-2"></i> {{ __('admin.Edit') }}</a>
													<button 
														id='deleteAction' 
														data-id='{{ $category->id }}' 
														data-url="{{ route('question-categories.destroy', ['question_category' => $category->id]) }}" 
														class="btn btn-label-danger btn-sm"
													> 
														<i class="ti ti-trash ti-sm me-2"></i> {{ __('admin.Delete') }}
													</button>
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
			const table = $('#categoriesTable').DataTable({
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
				dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
				displayLength: 10,
				lengthMenu: [10, 25, 50, 75, 100],
				order: [[0, 'desc']],
				columnDefs: [
					{
						targets: [4], // Actions column
						orderable: false,
						searchable: false
					}
				],
				buttons: [
					{
						extend: 'excel',
						title: isArabic ? 'فئات الأسئلة' : 'Question Categories',
						exportOptions: { columns: [0, 1, 2, 3] }
					},
					{
						extend: 'csv',
						title: isArabic ? 'فئات الأسئلة' : 'Question Categories',
						exportOptions: { columns: [0, 1, 2, 3] }
					},
				],
			});

			// Export button handlers
			$('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
			$('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

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
								table.row('#row_' + id).remove().draw();
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
