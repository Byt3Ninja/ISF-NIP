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
			<!-- Breadcrumb -->
          <nav aria-label="breadcrumb"> 
    		<ol class="breadcrumb">
        		<li class="breadcrumb-item"><a href="{{ route('mentorships.requests_view',['id' => $cycle->id]) }}">{{ __('admin.Mentorships') }}</a></li>
        		<li class="breadcrumb-item active">{{ __('admin.mentorshipSurveys') }}</li>
   			 </ol>
		 </nav>

            <!-- Mentorship Stage Info -->
            <div class="card mb-3">
				  <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.Step Title') }}</th>
                                    <th>{{ __('admin.StartDate') }}</th>
                                    <th>{{ __('admin.EndDate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cycle->steps as $step)
                                    <tr>
                                        <td>{{ $step->title ?? 'N/A' }}</td>
                                        <td>{{ $step->from ? $step->from->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $step->to ? $step->to->format('Y-m-d') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
            </div>

            <!-- Program Info -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5>{{ __('admin.Program Details') }}</h5>
                    <p><strong>{{ __('admin.Program Name') }}:</strong> {{ $cycle->title }}</p>
                    <p><strong>{{ __('admin.StartDate') }}:</strong> {{ $cycle->start ? $cycle->start->format('Y-m-d') : 'N/A' }}</p>
                    <p><strong>{{ __('admin.EndDate') }}:</strong> {{ $cycle->end ? $cycle->end->format('Y-m-d') : 'N/A' }}</p>
                    <p><strong>{{ __('admin.Organization') }}:</strong> {{ $cycle->organization->{'name_' . app()->getLocale()} }}</p>
                        @if (!is_null($cycle->organization))
                    @php
                    $imageUrl = $cycle->organization->image_url;

                    $imagePath = parse_url($imageUrl, PHP_URL_PATH);

                    $fileN = ltrim(str_replace('/dashboard/uploads/files/', '', $imagePath), '/');

                    @endphp

                    <img src="{{ $imageUrl }}" alt="Logo" class="img-fluid"
                      style="max-width: 20%; max-height: 20%; object-fit: contain;" />
                    @else
                    @php $setting = \App\Settings::where('id', 1)->first(); @endphp
                    @if (!empty($setting->Logo))
                    <img src="/images/logo/{{ $setting->Logo }}" alt="Logo" class="img-fluid"
                      style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @else
                    <img src="/images/background/logo.png" alt="Logo" class="img-fluid"
                      style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @endif
                    @endif
                </div>
            </div>
			<div class="content-overlay"></div>
			<div class="content-header">
				<h4 class="content-title">{{__('admin.mentorshipSurveys')}}</h4>
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
									<div class="row pb-1">
										<div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
											<button class="btn btn-block btn-primary" data-bs-toggle="modal" data-bs-target="#createSurveyModal">
                                                <i class="ti ti-plus me-1"></i> {{ __('admin.AddNewSurvey') }}
                                            </button>
										</div>
									</div>
									<div class="table-responsive surveys-list">
										<table id="surveys-list-datatable" class="table">
											<thead>
											<tr class="table-light">
												<th>#</th>
												<th>{{ __('admin.surveyTitle') }}</th>
												<th class="text-center">{{ __('admin.Organization') }}</th>
												<th class="text-center">{{ __('admin.surveyTriggerAfterSession') }}</th>
												<th class="text-center">{{ __('admin.surveyTriggerAfterCycle') }}</th>
												<th class="text-center">{{ __('admin.engagementRate') }}</th>
												<th class="text-center">{{ __('admin.type') }}</th>
												<th style="width: 100px;" class="text-center">{{__('admin.Actions')}}</th>
											</tr>
											</thead>
											<tbody>
												@foreach( $surveys as $index => $survey_scope )
													@php $survey = $survey_scope->survey; @endphp
													<tr>
														<td>{{ $index + 1 }}</td>
														<td><a href="{{ URL::to('dashboard/surveys/' . $survey->id . '/edit') }}">{!! $survey->title !!}</a></td>
														<td class="text-center"><a href="{{ URL::to('dashboard/organizations/' . $survey->organization->id . '/edit') }}"> {{ $survey->organization->{'name_'.$l} }}</a></td>
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
																{{ $survey_scope->type }}
														</td>
														<td class="text-center">
															<div class="btn-group w-100">
																<button class="btn btn-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> {{ __('admin.Actions') }} </button>
																<div class="dropdown-menu">
																	<a href="{{ route('surveys.questions_manage', ['survey' => $survey->id]) }}" target="_blank" class="dropdown-item"> <i class="ti ti-list-check"></i> {{ __('admin.manageQuestions') }} </a>
																	<a href="{{ route('surveys.results_view', ['survey' => $survey->id]) }}?target=mentorship" target="_blank" class="dropdown-item"> <i class="ti ti-checklist"></i> {{ __('admin.surveyResults') }} </a>
																	<a href="{{ route('mentorships.surveys_edit', ['id' => $cycle->id, 'survey' => $survey_scope->id]) }}" class="dropdown-item"> <i class="ti ti-edit"></i> {{ __('admin.Edit') }}</a>
																	<a id='deleteAction' data-id='{{ $survey_scope->id }}' data-url="{{ route('mentorships.surveys_delete', ['id' => $cycle->id, 'survey' => $survey_scope->id]) }}" href="{{ route('mentorships.surveys_delete', ['id' => $cycle->id, 'survey' => $survey_scope->id]) }}" class="dropdown-item bg-danger text-white"> <i class="ti ti-trash text-white"></i> {{ __('admin.Delete') }} </a>
																</div>
															</div>
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
				<!-- Create Survey Modal -->
                <div class="modal fade" id="createSurveyModal" tabindex="-1" aria-labelledby="createSurveyModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createSurveyModalLabel">{{ __('admin.AddNewSurvey') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="createSurveyForm" action="{{ route('mentorships.surveys_store', ['id' => $cycle->id]) }}" method="POST">
                                    @csrf
                                   <div class="row">
									<div class="col-md-12 col-sm-9">
												<fieldset class="form-group">
													<label for="survey_id">{{ __('admin.Survey') }}</label>
													<select class="form-control form-select" name="survey_id" id="survey_id" required>
														<option value="" selected disabled>{{ $l == 'ar' ? 'اختر' : 'Select' }} </option>
														@foreach($available_surveys as $survey )
															<option value="{{ $survey->id }}">{{ $survey->title }}</option>
														@endforeach
													</select>
												</fieldset>
									</div>
									</div>
                                    <div class="mt-3 col-12">
                                        <label for="targetUserType" class="form-label">{{ __('admin.Type_Survey') }}</label>
                                        <select class="form-control" id="type" name="type">
											<option value="" selected disabled>{{ $l == 'ar' ? 'اختر' : 'Select' }} </option>
											<option value="mentor">{{ $l == 'ar' ? 'مرشد' : 'Mentor' }}</option>
											<option value="startup">{{ $l == 'ar' ? 'مؤسس' : 'StartUp' }}</option>
											<option value="both">{{ $l == 'ar' ? 'الاثنين' : 'Both' }}</option>
                                        </select>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label">{{ __('admin.SurveyTriggers') }}</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="triggerBySession" name="trigger_by_session">
                                            <label class="form-check-label" for="triggerBySession">{{ __('admin.surveyTriggerAfterSession') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="triggerByDue" name="trigger_by_due">
                                            <label class="form-check-label" for="triggerByDue">{{ __('admin.surveyTriggerAfterCycle') }}</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">{{ __('admin.Submit') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
