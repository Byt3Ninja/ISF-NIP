@extends('layouts.admin')

@section('title', __('admin.quizzes') )
@section('subTitle', __('admin.quizzes List'))

@php
$user = Auth::user();
if ($user->hasRole('Admin')) {
	$organizations = App\Organization::all();
	$cycles = App\Cycle::all();
} elseif ($user->hasRole('organizationManager') || $user->isSubAdmin()) {
	$orgIds = $user->organizations_ids;
	$organizations = App\Organization::whereIn('id', $orgIds)->get();
	$cycles = App\Cycle::whereIn('organization_id', $orgIds)->get();
} else {
	$organizations = collect();
	$cycles = collect();
}
@endphp

@section('content')
	<!-- BEGIN: Content-->
	<div class="">
		<div class="">
			@php
				$tabs = [
					['title' => __('admin.quizzes'), 'url' => 'dashboard/quizzes', 'icon' => 'clock-bolt'],
					['title' => __('admin.quizzes_results'), 'url' => 'dashboard/quizResults', 'icon' => 'checklist'],
				]
			@endphp
			<x-navigation-pills :tabs="$tabs" />

			@if( session()->has('success') )
				<div class="w-100">
					<div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
						{{ __('admin.'. session()->get('success')) }} 
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
			@endif
			@if( session()->has('error') )
				<div class="w-100">
					<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
						{{ __('admin.'.session()->get('error')) }}
						<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
					</div>
				</div>
			@endif

			<div class="card">
				<div class="card-header d-flex flex-wrap justify-content-between align-items-center border-bottom">
					<h5 class="card-title mb-4 mb-md-0">{{__('admin.quizzes')}}</h4>
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
	
						@if($_user->ableToAny(['quizzes.create', 'quizzes.store'], $permissions))
							<a href="{{ route('quizzes.create') }}" class="btn btn-block btn-primary"> 
								<i class="ti ti-plus me-1"></i> {{ __('admin.Add New')}}
							</a>
						@endif
					</div>
				</div>

				<div class="card-datatable datatable-basic table-responsive">
					<table class="table quizzes-table">
						<thead class="table-light">
						<tr>
							<th>#</th>
							<th>{{__('admin.quizTitle')}}</th>
							<th class="text-center">{{__('admin.Organization')}}</th>
							<th class="text-center">{{__('admin.Program')}}</th>
							<th class="text-center">{{__('admin.quizTraining')}}</th>
							<th class="text-center">{{__('admin.quizStatus')}}</th>
							<th class="text-center">{{__('admin.quizDuration')}}</th>
							<th class="text-center">{{__('admin.quizParticipants')}}</th>
							<th class="text-center">{{__('admin.quizPassPercentage')}}</th>
							@if($_user->ableToAny(['quizzes.questions_manage', 'quizzes.questions_update', 'quizzes.edit', 'quizzes.update', 'quizzes.destroy'], $permissions))<th class="text-center">{{__('admin.Actions')}}</th>@endif
						</tr>
						</thead>
						<tbody>
							@foreach( $quizzes as $key => $quiz )
								@php
									$participates = collect($quiz->results)->groupBy('user_id');
									$passed_participates = $participates->filter(function( $item ) use ( $quiz ) {
										$passed = $item->where('score', '>=', $quiz->passing_criteria);
										$failed = $item->where('score', '<', $quiz->passing_criteria);
										if( $passed->count() >= 1 ) { return true; }
										else
										{
											if( $failed->count() >= $quiz->attempts ) { return false; }
											if( $quiz->end_at && now()->gte($quiz->end_at) ) { return false; }
											return true;
										}
									});
								@endphp
								<tr>
									<td>{{ $key + 1 }}</td>
									<td class="text-wrap" style="max-width: 200px;">
										<a href="{{ URL::to('dashboard/quizzes/' . $quiz->id . '/edit') }}">
											<small class="d-inline-block text-break">
												{!! $quiz->title !!}
											</small>
										</a>
									</td>

									<td class="text-wrap" style="max-width: 200px;">
										<a href="{{ URL::to('dashboard/organizations/' . $quiz->organization->id . '/edit') }}">
											<small class="d-inline-block text-break">{{ $quiz->organization->{'org_name_'.$l} }}</small>
										</a>
									</td>
									<td class="text-wrap" style="max-width: 200px;">
										<small class="d-inline-block text-break">{{ $quiz->cycle->cycle_title ?? '-' }}</small>
									</td>
									<td class="text-wrap" style="max-width: 200px;">
										<small class="d-inline-block text-break">{{ $quiz->training->title ?? '-' }}</small>
									</td>
									<td class="text-center">
										@if( $quiz->status == 'draft' )
											<small class="badge bg-label-warning">{{__('admin.Draft')}}</small>
										@elseif( $quiz->status == 'published' )
											<small class="badge bg-label-success">{{__('admin.Published')}}</small>
										@endif
									</td>
									<td class="text-center">
										<small title="{{ $quiz->start }}" class="badge bg-label-primary"> {{ __('admin.From') }} {{ FormatDate($quiz->start, 'Y-m-d') }} </small> <br />
										<small title="{{ $quiz->end }}" class="badge bg-label-secondary mt-1"> {{ __('admin.To') }} {{ FormatDate($quiz->end, 'Y-m-d') }}</small>
									</td>
									<td class="text-center"> 
										<span class="badge bg-label-info">{{ $participates->count() }}</span>
									</td>
									<td class="text-center">
										@if( $participates->count() >= 1 )
										<span>
											{{ ( round( floatval( $passed_participates->count() / $participates->count() ), 2 ) * 100 ) . '%' }}
										</span>
										@else
											-
										@endif
									</td>
									@if($_user->ableToAny(['quizzes.questions_manage', 'quizzes.questions_update', 'quizzes.edit', 'quizzes.update', 'quizzes.destroy'], $permissions))
									<td class="text-center">
										<div class="dropdown">
											<button class="btn btn-sm btn-light btn-icon rounded" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
												<i class="ti ti-dots-vertical"></i>
											</button>
											<div class="dropdown-menu">
												@if($_user->ableToAny(['quizzes.questions_manage', 'quizzes.questions_update'], $permissions))
												<a href="{{ route('quizzes.questions_manage', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-list-ul"></i> {{ __('admin.manageQuestions') }} </a>
												@endif
												@if($_user->ableToAny(['quizzes.edit', 'quizzes.update'], $permissions))
												<a href="{{ route('quizzes.edit', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="bx bx-edit"></i> {{ __('admin.Edit') }}</a>
												@endif
												@if($_user->ableTo('quizzes.destroy', $permissions))
												<a id='deleteAction' data-id='{{ $quiz->id }}' data-url="{{ route('quizzes.destroy', ['quiz' => $quiz->id]) }}" href="{{ route('quizzes.destroy', ['quiz' => $quiz->id]) }}" class="dropdown-item"> <i class="ti ti-trash text-danger"></i> {{ __('admin.Delete') }} </a>
												@endif
											</div>
										</div>
									</td>
									@endif
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@stop

@section('scripts')
	<script>
		// Initialize DataTable with newest to oldest ordering
		$(document).ready(function() {
			var table = $('.quizzes-table').DataTable({
				responsive: true,
        searching: true,
        lengthChange: true,
        pageLength: 10,
				order: [[ 6, "desc" ]],
				dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
				  buttons: [
          {
            extend: 'excel',
            title: '{{ $l == "en" ? "Quizzes List" : "قائمة الاختبارات" }}',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
            }
          },
          {
            extend: 'csv',
            title: '{{ $l == "en" ? "Quizzes List" : "قائمة الاختبارات" }}',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
            }
          },
        ],
        columnDefs: [
          { orderable: false, targets: [0, 9] },
        ],
				initComplete: function() {
					// Create and insert organization filter
					$('<select id="orgFilter" class="form-select form-select-sm ms-md-8 my-3 my-md-0" style="width: auto; max-width: 150px;">' +
            '<option value="">{{ $l == "en" ? "All Organizations" : "كل المنظمات" }}</option>' +
            '@foreach ($organizations as $organization)' +
            '<option value="{{ $organization->{'name_' . $l} }}">{{ $organization->{'name_' . $l} }}</option>' +
            '@endforeach' +
            '</select>')
            .insertAfter('.dataTables_length label')
            .on('change', function() {
              const value = $(this).val();
              table.column(2).search(value).draw();
            });

					// Create and insert program filter
					$('<select id="programFilter" class="form-select form-select-sm mb-3 mb-md-0" style="width: auto; max-width: 150px;">' +
            '<option value="">{{ $l == "en" ? "All Programs" : "كل البرامج" }}</option>' +
            '@foreach ($cycles as $cycle)' +
            '<option value="{{ $cycle->title }}">{{ $cycle->title }}</option>' +
            '@endforeach' +
            '</select>')
            .insertAfter('#orgFilter')
            .on('change', function() {
              const value = $(this).val();
              table.column(3).search(value).draw();
            });

					// Create and insert status filter
					$('<select id="statusFilter" class="form-select form-select-sm" style="width: auto; min-width: 150px;">' +
            '<option value="">{{ $l == "en" ? "All Statuses" : "كل الحالات" }}</option>' +
            '<option value="{{ __("admin.Draft") }}">{{ __("admin.Draft") }}</option>' +
            '<option value="{{ __("admin.Published") }}">{{ __("admin.Published") }}</option>' +
            '</select>')
            .insertAfter('#programFilter')
            .on('change', function() {
              const value = $(this).val();
              console.log('value', value);
              table.column(5).search(value).draw();
            });
				},
				@if ($l === 'ar')
					language: {
						url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
					},
        @endif
			});

			// Export button click handlers
      $('#exportExcel').on('click', function() {
        table.button('.buttons-excel').trigger();
      });

      $('#exportCsv').on('click', function() {
        table.button('.buttons-csv').trigger();
      });
		});

		$(document).on("click", "#deleteAction", function (e) {
			e.preventDefault();
			var url = $(this).data('url');
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
							releaseSuccessAlert({
								title: "{{ __('admin.Deleted') }}!",
								message: "{{ __('admin.DeletedSuccessfully') }}.",
							});
							location.reload();
						}
					).fail(function(error) { 
						releaseFailureAlert({
							title: "{{ __('admin.Error') }}!",
							message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
						});
					});
				}
			})
		});
	</script>
@stop
