@extends('layouts.admin')

@section('title', __('admin.mentorshipSurveys') )
@section('subTitle', __('admin.surveysList'))

@php
	$isAdmin = auth()->user()->hasRole('Admin');
	$scopeCol = 1;
	$orgCol = $isAdmin ? 3 : -1;
	$sessionCol = $isAdmin ? 4 : 3;
	$cycleCol = $isAdmin ? 5 : 4;
	$orderableTargets = $isAdmin ? '[4,5,7]' : '[3,4,6]';
	$scopes = collect($surveys)->pluck('scope')->unique()->filter();
	$organizations = $isAdmin ? collect($surveys)->pluck('organization')->unique()->filter()->map(function($org) use ($l) { return ['id' => $org->id, 'name' => $org->{'name_' . $l}]; }) : collect();
@endphp

@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			@if( session()->has('success') )
			<div class="col-md-12 mb-2">
				<div role="alert" class="alert alert-success alert-dismissible">
					{{ __('admin.'. session()->get('success')) }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
			@endif
			@if( session()->has('error') )
			<div class="col-md-12 mb-2">
				<div role="alert" class="alert alert-danger alert-dismissible">
					{{ __('admin.'.session()->get('error')) }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			</div>
			@endif
		</div>

		<div class="card">
			<div class="card-header border-bottom d-flex justify-content-between align-items-center">
				<h4 class="card-title mb-0">{{__('admin.Survey_Results')}}</h4>
				<div class="dropdown">
					<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
					</button>
					<ul class="dropdown-menu" aria-labelledby="exportDropdown">
						<li><a class="dropdown-item" href="javascript:void(0)" id="export-csv"><i class="ti ti-file-text me-1"></i> CSV</a></li>
						<li><a class="dropdown-item" href="javascript:void(0)" id="export-excel"><i class="ti ti-file-spreadsheet me-1"></i> Excel</a></li>
					</ul>
				</div>
			</div>
			<div class="card-body">
				<div class="row mt-6">
					<div class="col-12 col-md-2">
						<select id="scope-filter" class="form-select form-select-sm">
							<option value="">{{ __('admin.Scope') }}</option>
							<option value="">{{ __('admin.All') }}</option>
							@foreach($scopes as $scope)
							<option value="{{ $scope }}">{{ $scope }}</option>
							@endforeach
						</select>
					</div>
					@if($isAdmin)
					<div class="col-12 col-md-2">
						<select id="org-filter" class="form-select form-select-sm">
							<option value="">{{ __('admin.Organization') }}</option>
							<option value="">{{ __('admin.All') }}</option>
							@foreach($organizations as $org)
							<option value="{{ $org['name'] }}">{{ $org['name'] }}</option>
							@endforeach
						</select>
					</div>
					@endif
					<div class="col-12 col-md-2">
						<select id="session-filter" class="form-select form-select-sm">
							<option value="">{{ __('admin.surveyTriggerAfterSession') }}</option>
							<option value="">{{ __('admin.All') }}</option>
							<option value="1">{{ __('admin.surveyTriggerAllowed') }}</option>
							<option value="0">{{ __('admin.surveyTriggerDenied') }}</option>
						</select>
					</div>
					<div class="col-12 col-md-2">
						<select id="cycle-filter" class="form-select form-select-sm">
							<option value="">{{ __('admin.surveyTriggerAfterCycle') }}</option>
							<option value="">{{ __('admin.All') }}</option>
							<option value="1">{{ __('admin.surveyTriggerAllowed') }}</option>
							<option value="0">{{ __('admin.surveyTriggerDenied') }}</option>
						</select>
					</div>
					<div class="col-12 col-md-2 d-none align-items-end">
						<button id="clear-filters" class="btn btn-sm btn-outline-secondary">{{ __('admin.Clear') }}</button>
					</div>
				</div>
				<table id="table table-hover surveys-datatable" class="table">
					<thead>
						<tr class="table-light">
							<th>#</th>
							<th class="text-center">{{ __('admin.Scope') }}</th>
							<th>{{ __('admin.surveyTitle') }}</th>
							@role('Admin')
							<th class="text-center">{{ __('admin.Organization') }}</th>
							@endrole
							<th class="text-center">{{ __('admin.surveyTriggerAfterSession') }}</th>
							<th class="text-center">{{ __('admin.surveyTriggerAfterCycle') }}</th>
							<th class="text-center">{{ __('admin.engagementRate') }}</th>
	
							<th style="width: 100px;" class="text-center">{{__('admin.Actions')}}</th>
						</tr>
					</thead>
					<tbody>
						@foreach( $surveys as $index => $survey_scope )
						@php $survey = $survey_scope->survey; @endphp
						<tr>
							<td>{{ $index + 1 }}</td>
							<td class="text-center">{{ ($survey_scope->scope ?? '') }}</td>
							<td>
								@role('Admin')
								<a href="{{ URL::to('dashboard/surveys/' . $survey->id . '/edit') }}">{!! $survey->title
									!!}</a>
								@endrole
								@role('organizationManager')
								{{ $survey->title }}
								@endrole
							</td>
							@role('Admin')
							<td class="text-center"><a
									href="{{ URL::to('dashboard/organizations/' . $survey_scope->organization?->id . '/edit') }}">
									{{ $survey_scope->organization?->{'name_'.$l} }}</a></td>
							@endrole
							<td class="text-center">
								@if( $survey_scope->trigger_by_session )
								<span class="btn btn-xs btn-outline-success"> <i class="ti ti-check me-1"></i> {{
									__('admin.surveyTriggerAllowed') }} </span>
								@else
								<span class="btn btn-xs btn-outline-danger"> <i class="ti ti-x me-1"></i> {{
									__('admin.surveyTriggerDenied') }} </span>
								@endif
							</td>
							<td class="text-center">
								@if( $survey_scope->trigger_by_due )
								<span class="btn btn-xs btn-outline-success"> <i class="ti ti-check me-1"></i> {{
									__('admin.surveyTriggerAllowed') }} </span>
								@else
								<span class="btn btn-xs btn-outline-danger"> <i class="ti ti-x me-1"></i> {{
									__('admin.surveyTriggerDenied') }} </span>
								@endif
							</td>
							<td class="text-center">
								<span class="cursor-pointer"
									title="{{ __('admin.total') . ' : ' . $survey_scope->eligibleUsers ?? 0 }} | {{ __('admin.engaged') . ' : ' . $survey_scope->engagedUsers ?? 0 }} | {{ __('admin.notEngaged') . ' : ' . $survey_scope->notEngagedUsers ?? 0 }}"
									data-toggle="tooltip" data-placement="top">
									{{ ($survey_scope->engagementRate ?? '0.00') . '%' }}
								</span>
							</td>
	
							<td class="text-center">
								<a href="{{ route('surveys.results_view', ['survey' => $survey->id]) }}?target={{ $survey_scope->scope }}"
									class="btn btn-xs btn-outline-info"> <i class="ti ti-checklist"></i> {{
									__('admin.surveyResults') }}
								</a>
							</td>
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
<script type="text/javascript">
		$(document).ready(function() {
			$('[data-toggle="tooltip"]').tooltip()

			var table = $('.table').DataTable({
				responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
				'buttons': [
					{
						extend: 'csv',
						text: 'CSV',
						className: 'd-none'
					},
					{
						extend: 'excel',
						text: 'Excel',
						className: 'd-none'
					}
				],
				'columnDefs': [
					{
						"orderable": false,
						"targets": {{ $orderableTargets }}
					}
				],
				@if( $l == 'ar' )
				'language': {
					'sEmptyTable': "ليست هناك بيانات متاحة في الجدول",
					'sLoadingRecords': "جارٍ التحميل...",
					'sProcessing': "جارٍ التحميل...",
					'sLengthMenu': "أظهر _MENU_ مدخلات",
					'sZeroRecords': "لم يعثر على أية سجلات",
					'sInfo': "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					'sInfoEmpty': "يعرض 0 إلى 0 من أصل 0 سجل",
					'sInfoFiltered': "(منتقاة من مجموع _MAX_ مُدخل)",
					'sInfoPostFix': "",
					'sSearch': "ابحث:",
					'sUrl': "",
					'oPaginate': {
						'sFirst': "الأول",
						'sPrevious': "السابق",
						'sNext': "التالي",
						'sLast': "الأخير"
					},
					'oAria': {
						'sSortAscending': ": تفعيل لترتيب العمود تصاعدياً",
						'sSortDescending': ": تفعيل لترتيب العمود تنازلياً"
					}
				}
				@endif
			});

			// Filter events
			$('#scope-filter').on('change', function() {
				var val = this.value;
				table.column({{ $scopeCol }}).search(val).draw();
				toggleClearButton();
			});
			@if($isAdmin)
			$('#org-filter').on('change', function() {
				var val = this.value;
				table.column({{ $orgCol }}).search(val).draw();
				toggleClearButton();
			});
			@endif
			$('#session-filter').on('change', function() {
				var text = $('#session-filter option:selected').text().trim();
				table.column({{ $sessionCol }}).search(text, true, false).draw();
				toggleClearButton();
			});
			$('#cycle-filter').on('change', function() {
				var text = $('#cycle-filter option:selected').text().trim();
				table.column({{ $cycleCol }}).search(text, true, false).draw();
				toggleClearButton();
			});

			// Toggle clear button visibility
			function toggleClearButton() {
				var hasFilter = $('#scope-filter').val() @if($isAdmin) || $('#org-filter').val() @endif || $('#session-filter').val() || $('#cycle-filter').val();
				if (hasFilter) {
					$('#clear-filters').parent().removeClass('d-none').addClass('d-flex');
				} else {
					$('#clear-filters').parent().removeClass('d-flex').addClass('d-none');
				}
			}

			// Clear filters
			$('#clear-filters').on('click', function() {
				$('#scope-filter, #session-filter, #cycle-filter').val('');
				@if($isAdmin)
				$('#org-filter').val('');
				@endif
				table.columns().search('').draw();
				$(this).parent().removeClass('d-flex').addClass('d-none');
			});

			// Export buttons
			$('#export-csv').on('click', function(e) {
				e.preventDefault();
				table.button(0).trigger();
			});

			$('#export-excel').on('click', function(e) {
				e.preventDefault();
				table.button(1).trigger();
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
				if (result.value)
				{
					$.post(url, {'_method': 'DELETE' , '_token': token},
						function(response)
						{
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
							location.reload();
						}
					).fail(function(error) { console.log(error); } );
				}
			})
		});
</script>
@stop