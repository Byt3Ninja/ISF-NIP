@extends('layouts.admin')
@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<style type="text/css">
		.surveys-list { min-height: 45vh !important; }
	</style>
@stop
@section('title', __('admin.mentorshipSurveys') )
@section('subTitle', __('admin.surveysList'))
@section('content')
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header">
				<h4 class="content-title">{{__('admin.Survey_Results')}}</h4>
			</div>
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
						<div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
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
							<div class="card-content">
								<div class="card-body">
									@if( session()->has('msg') )
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger mb-2" role="alert">
												<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
												<div class="d-flex align-items-center"> <i class="ti ti-alert-triangle me-3 ti-md"></i> <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span> </div>
											</div>
										</div>
									</div>
									@endif
									@if( count( $errors ) > 0 )
									<div class="row">
										<div class="col-md-12">
											<div class="alert alert-danger">
												<ul>
													@foreach($errors->all() as $error)
													<li>{{ $error }} </li>
													@endforeach
												</ul>
											</div>
										</div>
									</div>
									@endif
									
									<div class="table-responsive surveys-list">
										<table id="surveys-list-datatable" class="table">
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
													@php $survey = $survey_scope->survey;  @endphp
													<tr>
														<td>{{ $index + 1 }}</td>
                                                        <td class="text-center">{{ ($survey_scope->scope ?? '') }}</td>
														<td>
                                                            @role('Admin')
                                                            <a href="{{ URL::to('dashboard/surveys/' . $survey->id . '/edit') }}">{!! $survey->title !!}</a>
                                                            @endrole
                                                            @role('organizationManager')
                                                            {{ $survey->title }}
                                                            @endrole
                                                        </td>
                                                        @role('Admin')
                                                        <td class="text-center"><a href="{{ URL::to('dashboard/organizations/' . $survey_scope->organization->id . '/edit') }}"> {{ $survey_scope->organization->{'name_'.$l} }}</a></td>
														@endrole
                                                        <td class="text-center">
															@if( $survey_scope->trigger_by_session )
															<span class="btn btn-xs btn-outline-success"> <i class="ti ti-check me-1"></i> {{ __('admin.surveyTriggerAllowed') }} </span>
															@else
															<span class="btn btn-xs btn-outline-danger"> <i class="ti ti-x me-1"></i> {{ __('admin.surveyTriggerDenied') }} </span>
															@endif
														</td>
														<td class="text-center">
															@if( $survey_scope->trigger_by_due )
															<span class="btn btn-xs btn-outline-success"> <i class="ti ti-check me-1"></i> {{ __('admin.surveyTriggerAllowed') }} </span>
															@else
															<span class="btn btn-xs btn-outline-danger"> <i class="ti ti-x me-1"></i> {{ __('admin.surveyTriggerDenied') }} </span>
															@endif
														</td>
														<td class="text-center">
															<span class="cursor-pointer" title="{{ __('admin.total') . ' : ' . $survey_scope->eligibleUsers ?? 0 }} | {{ __('admin.engaged') . ' : ' . $survey_scope->engagedUsers ?? 0 }} | {{ __('admin.notEngaged') . ' : ' . $survey_scope->notEngagedUsers ?? 0 }}" data-toggle="tooltip" data-placement="top">
																{{ ($survey_scope->engagementRate ?? '0.00') . '%' }}
															</span>
														</td>
                                                        
														<td class="text-center">
															
																
																	<a href="{{ route('surveys.results', ['survey' => $survey->id]) }}?target=mentorship" target="_blank" class="btn btn-xs btn-outline-info"> <i class="ti ti-checklist"></i> {{ __('admin.surveyResults') }} </a>
																
															
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
	<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
	<script src="/app-assets/js/scripts/modal/components-modal.js"></script>

	<script type="text/javascript">
		$(function () {
			$('[data-toggle="tooltip"]').tooltip()
		});
		$(document).ready(function() {
			$('#surveys-list-datatable').DataTable({
				'responsive': true,
				'searching': true,
				'lengthChange': false,
				'paging': true,
				'bInfo': false,
				'columnDefs': [
					{
						"orderable": false,
						"targets": [3]
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
		});
		$(document).on("click", "#deleteAction", function (e) {
			e.preventDefault();
			var url = $(this).data('url');
			var token = '{{ csrf_token() }}';
			Swal.fire({
				title: "{{__('admin.SureDelete')}}",
				text: "{{__('admin.Recover')}}",
				icon: "warning",
				buttonsStyling: true,
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "{{__('admin.DYes')}}",
				cancelButtonText: "{{__('admin.No')}}!",
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
								html: "{{ __('admin.FileDeleted') }}.",
							});
							location.reload();
						}
					).fail(function(error) { console.log(error); } );
				}
			})
		});
	</script>
@stop
