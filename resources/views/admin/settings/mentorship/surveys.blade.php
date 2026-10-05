@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<style type="text/css">
		/* .surveys-list {
				min-height: 45vh !important;
		} */
		.program-overview {
				background: linear-gradient(135deg, #9e95f5 0%, #7367f0 100%);
				box-shadow: 0 4px 10px rgba(115, 103, 240, 0.4);
		}
		.step-timeline {
				position: relative;
		}
		.step-timeline::before {
				content: '';
				position: absolute;
				top: 0;
				bottom: 0;
				left: 15px;
				width: 2px;
				background: #e3f2fd;
		}
		.step-item {
				position: relative;
				padding-left: 40px;
				margin-bottom: 20px;
		}
		.step-item::before {
				content: '';
				position: absolute;
				left: 8px;
				top: 8px;
				width: 14px;
				height: 14px;
				background: #2196f3;
				border-radius: 50%;
		}
		.step-item.active::before {
				background: #4caf50;
		}
		.engagement-badge {
				font-size: 0.875rem;
				padding: 0.5rem 1rem;
				border-radius: 1rem;
		}
		.trigger-status {
				font-size: 0.75rem;
				padding: 0.25rem 0.75rem;
				border-radius: 0.5rem;
		}
		.survey-type-badge {
				background: #f8f9fa;
				color: #495057;
				padding: 0.5rem 1rem;
				border-radius: 1rem;
				font-weight: 500;
		}
		.action-dropdown .dropdown-menu {
				box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
		}
		.program-stats {
				background: rgba(255, 255, 255, 0.1);
				backdrop-filter: blur(10px);
				border-radius: 1rem;
		}
	</style>
@endsection

@section('title', __('admin.mentorshipSurveys'))

@section('subTitle', __('admin.surveysList'))

@section('content')
	<div class="">
		<!-- Enhanced Program Overview -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card program-overview text-white overflow-hidden">
					<div class="card-body">
						<div class="row align-items-center">
							<div class="col-lg-8">
								<h3 class="text-white mb-3">
									<i class="ti ti-target me-2"></i>{{ $cycle->title }}
								</h3>
								<div class="row">
									<div class="col-md-6">
										<div class="program-stats p-3 mb-3">
											<h6 class="text-white mb-2">{{ __('admin.Program Duration') }}</h6>
											<p class="mb-1">
												<i class="ti ti-calendar-event me-2"></i>{{ $cycle->start ? $cycle->start->format('M d, Y') : 'N/A' }}
											</p>
											<p class="mb-0">
												<i class="ti ti-calendar-check me-2"></i>{{ $cycle->end ? $cycle->end->format('M d, Y') : 'N/A' }}
											</p>
										</div>
									</div>
									<div class="col-md-6">
										<div class="program-stats p-3 mb-3">
											<h6 class="text-white mb-2">{{ __('admin.Organization') }}</h6>
											<p class="mb-0">
												<i class="ti ti-building me-2"></i>{{ $cycle->organization->{'name_' . app()->getLocale()} }}
											</p>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-4 text-center">
								@if($cycle->organization->img_url)
									<img
										src="{{ route('organizations.logo', $cycle->organization->img_url ?? '') }}"
										alt="Organization Logo"
										class="img-fluid rounded"
										style="max-width: 120px; max-height: 120px; object-fit: contain; background: rgba(255,255,255,0.1); padding: 1rem;"
									/>
								@else
									<span class="avatar-initial rounded bg-label-secondary">
										{{ substr($cycle->organization->name, 0, 2) }}
									</span>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Enhanced Mentorship Timeline -->
		<div class="row mb-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header d-flex align-items-center">
						<h5 class="mb-0">
							<i class="ti ti-timeline me-2 text-primary"></i>{{ __('admin.Mentorship Timeline') }}
						</h5>
					</div>
					<div class="card-body">
						<div class="step-timeline">
							@php
								// GV-9566: steps.from/to are inclusive DATEs in the organization's platform zone.
								$platformToday = \App\Services\TimezoneService::platformToday(\App\Services\TimezoneService::platform($cycle->organization_id ?? null));
							@endphp
							@foreach($cycle->steps as $index => $step)
								@php
									$isActive = $step->from && $step->to && $step->from->toDateString() <= $platformToday && $platformToday <= $step->to->toDateString();
									$isPast = $step->to && $step->to->toDateString() < $platformToday;
								@endphp
									<div class="step-item {{ $isActive ? 'active' : ($isPast ? 'completed' : '') }}">
										<div class="d-flex justify-content-between align-items-start">
											<div>
												<h6 class="mb-1 {{ $isActive ? 'text-success' : ($isPast ? 'text-muted' : 'text-primary') }}">
													{{ $step?->title ?? 'N/A' }}
													@if($isActive)
														<span class="badge bg-success ms-2">{{ __('admin.Active') }}</span>
													@elseif($isPast)
														<span class="badge bg-secondary ms-2">{{ __('admin.Completed') }}</span>
													@else
														<span class="badge bg-primary ms-2">{{ __('admin.Upcoming') }}</span>
													@endif
												</h6>
												<small class="text-muted">
													<i class="ti ti-calendar me-1"></i>
													{{ $step?->from ? $step->from->format('M d, Y') : 'N/A' }} -
													{{ $step?->to ? $step->to->format('M d, Y') : 'N/A' }}
												</small>
											</div>
											<div class="text-end">
												@if($step->from && $step->to)
													<small class="text-muted">
														{{ $step->from->diffInDays($step->to) }} {{ __('admin.days') }}
													</small>
												@endif
											</div>
										</div>
									</div>
							@endforeach
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Alert Messages -->
		@if(session()->has('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="ti ti-check-circle me-2"></i>
				{{ __('admin.'. session()->get('success')) }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if(session()->has('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<i class="ti ti-alert-triangle me-2"></i>
				{{ __('admin.'.session()->get('error')) }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if(session()->has('msg'))
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<i class="ti ti-alert-triangle me-2"></i>
				{{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if(count($errors) > 0)
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<i class="ti ti-alert-circle me-2"></i>
				<strong>{{ __('admin.ValidationErrors') }}</strong>
				<ul class="mb-0 mt-2">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		<!-- Enhanced Surveys Management Section -->
		<div class="card">
			<div class="card-header d-flex flex-column flex-md-row gap-4 justify-content-between align-items-md-center">
				<div>
					<h5 class="mb-0">
						<i class="ti ti-clipboard-list me-2 text-primary"></i>{{ __('admin.mentorshipSurveys') }}
					</h5>
					<small class="text-muted">{{ __('admin.Manage and monitor mentorship surveys') }}</small>
				</div>
				<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSurveyModal">
					<i class="ti ti-plus me-1"></i>{{ __('admin.AddNewSurvey') }}
				</button>
			</div>
			<div class="card-datatable table-responsive">
				<table id="surveys-list-datatable" class="table">
					<thead class="table-light">
						<tr>
							<th class="text-center" style="width: 50px;">#</th>
							<th>{{ __('admin.surveyTitle') }}</th>
							<th class="text-center">{{ __('admin.Organization') }}</th>
							<th class="text-center">{{ __('admin.Type') }}</th>
							<th class="text-center">{{ __('admin.Triggers') }}</th>
							<th class="text-center">{{ __('admin.engagementRate') }}</th>
							<th class="text-center" style="width: 120px;">{{ __('admin.Actions') }}</th>
						</tr>
					</thead>
					<tbody>
						@forelse($surveys as $index => $survey_scope)
							@php $survey = $survey_scope->survey; @endphp
							<tr>
								<td class="text-center">
									<span class="badge bg-light text-dark">{{ $index + 1 }}</span>
								</td>
								<td>
									<div class="d-flex align-items-center">
										<div class="flex-grow-1">
											<h6 class="mb-0">
												<a href="{{ URL::to('dashboard/surveys/' . $survey->id . '/edit') }}" class="text-decoration-none">
													{!! $survey->title !!}
												</a>
											</h6>
											<small class="text-muted">
												{{ __('admin.Survey ID') }}: {{ $survey->id }}
											</small>
										</div>
									</div>
								</td>
								<td class="text-center">
									<a href="{{ URL::to('dashboard/organizations/' . $survey->organization->id . '/edit') }}" class="text-decoration-none">
										<span class="badge bg-label-info">
											{{ $survey->organization->{'name_'.$l} }}
										</span>
									</a>
								</td>
								<td class="text-center">
									<span class="survey-type-badge">
										<i class="ti ti-{{ $survey_scope->type == 'mentor' ? 'user-star' : ($survey_scope->type == 'startup' ? 'rocket' : 'users') }} me-1"></i>
										{{ ucfirst($survey_scope->type) }}
									</span>
								</td>
								<td class="text-center">
									<div class="d-flex flex-column gap-1">
										@if($survey_scope->trigger_by_session)
											<span class="trigger-status bg-lalbel-success">
												<i class="ti ti-video me-2"></i>{{ __('admin.Session') }}
											</span>
										@endif
										@if($survey_scope->trigger_by_due)
											<span class="trigger-status bg-label-warning">
												<i class="ti ti-calendar-due me-2"></i>{{ __('admin.Cycle End') }}
											</span>
										@endif
										@if(!$survey_scope->trigger_by_session && !$survey_scope->trigger_by_due)
											<span class="trigger-status bg-label-secondary">
												<i class="ti ti-x me-1"></i>{{ __('admin.No Triggers') }}
											</span>
										@endif
									</div>
								</td>
								<td class="text-center">
									<div class="engagement-info">
										<div
											class="engagement-badge bg-label-primary text-white"
											data-bs-toggle="tooltip"
											data-bs-placement="top"
											title="{{ __('admin.total') }}: {{ $survey_scope->eligibleUsers ?? 0 }} | {{ __('admin.engaged') }}: {{ $survey_scope->engagedUsers ?? 0 }} | {{ __('admin.notEngaged') }}: {{ $survey_scope->notEngagedUsers ?? 0 }}"
										>
											<i class="ti ti-chart-pie me-1"></i>
											{{ ($survey_scope->engagementRate ?? '0.00') . '%' }}
										</div>
									</div>
								</td>
								<td class="text-center">
									<div class="dropdown action-dropdown">
										<button
											class="btn btn-outline-secondary btn-sm dropdown-toggle"
											type="button"
											data-bs-toggle="dropdown"
											aria-expanded="false"
										>
											<i class="ti ti-dots-vertical"></i>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a href="{{ route('surveys.questions_manage', ['survey' => $survey->id]) }}" target="_blank" class="dropdown-item">
													<i class="ti ti-list-check me-2"></i>{{ __('admin.manageQuestions') }}
												</a>
											</li>
											<li>
												<a href="{{ route('surveys.results_view', ['survey' => $survey->id]) }}?target=mentorship" target="_blank" class="dropdown-item">
													<i class="ti ti-chart-bar me-2"></i>{{ __('admin.surveyResults') }}
												</a>
											</li>
											<li><hr class="dropdown-divider" /></li>
											<li>
												<a href="{{ route('mentorships.surveys_edit', ['id' => $cycle->id, 'survey' => $survey_scope->id]) }}" class="dropdown-item">
													<i class="ti ti-edit me-2"></i>{{ __('admin.Edit') }}
												</a>
											</li>
											<li>
												<a
													id="deleteAction"
													data-id="{{ $survey_scope->id }}"
													data-url="{{ route('mentorships.surveys_delete', ['id' => $cycle->id, 'survey' => $survey_scope->id]) }}"
													href="#"
													class="dropdown-item text-danger"
												>
													<i class="ti ti-trash me-2"></i>{{ __('admin.Delete') }}
												</a>
											</li>
										</ul>
									</div>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="7" class="text-center py-5">
									<div class="empty-state">
										<i class="ti ti-clipboard-off display-4 text-muted"></i>
										<h5 class="mt-3 text-muted">{{ __('admin.No surveys found') }}</h5>
										<p class="text-muted">{{ __('admin.Start by adding your first survey') }}</p>
										<button
											class="btn btn-primary"
											data-bs-toggle="modal"
											data-bs-target="#createSurveyModal"
										>
											<i class="ti ti-plus me-1"></i>{{ __('admin.AddNewSurvey') }}
										</button>
									</div>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<!-- Enhanced Create Survey Modal -->
		<div class="modal fade" id="createSurveyModal" tabindex="-1" aria-labelledby="createSurveyModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-label-secondary pb-4 text-white">
						<h5 class="modal-title" id="createSurveyModalLabel">
							<i class="ti ti-plus-circle me-2"></i>{{ __('admin.AddNewSurvey') }}
						</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form id="createSurveyForm" action="{{ route('mentorships.surveys_store', ['id' => $cycle->id]) }}" method="POST">
							@csrf
							<div class="row g-6">
								<!-- Survey Selection -->
								<div class="col-12">
									<label for="survey_id" class="form-label">
										<i class="ti ti-clipboard-list me-1"></i>{{ __('admin.Survey') }}
										<span class="text-danger">*</span>
									</label>
									<select class="form-select" name="survey_id" id="survey_id" required>
										<option value="" selected disabled>
											{{ $l == 'ar' ? 'اختر استطلاع' : 'Select Survey' }}
										</option>
										@foreach($available_surveys as $survey)
											<option value="{{ $survey->id }}">{{ $survey->title }}</option>
										@endforeach
									</select>
									<div class="form-text">{{ __('admin.Choose the survey to add to this mentorship cycle') }}</div>
								</div>

								<!-- Survey Type -->
								<div class="col-12">
									<label for="type" class="form-label">
										<i class="ti ti-users me-1"></i>{{ __('admin.Target Audience') }}
										<span class="text-danger">*</span>
									</label>
									<select class="form-select" id="type" name="type" required>
										<option value="" selected disabled> {{ $l == 'ar' ? 'اختر الجمهور المستهدف' : 'Select Target Audience' }} </option>
										<option value="mentor"> {{ $l == 'ar' ? 'مرشد' : 'Mentor' }} </option>
										<option value="startup"> {{ $l == 'ar' ? 'مؤسس' : 'StartUp' }} </option>
										<option value="both"> {{ $l == 'ar' ? 'الاثنين' : 'Both' }} </option>
									</select>
									<div class="form-text">{{ __('admin.Select who should receive this survey') }}</div>
								</div>

								<!-- Survey Triggers -->
								<div class="col-12">
									<label class="form-label">
										<i class="ti ti-settings me-1"></i>{{ __('admin.Survey Triggers') }}
									</label>
									<div class="card bg-light-subtle shadow-none border">
										<div class="card-body p-3">
											<div class="form-check mb-2">
												<input class="form-check-input" type="checkbox" id="triggerBySession" name="trigger_by_session" />
												<label class="form-check-label" for="triggerBySession">
													<i class="ti ti-video me-1 text-success"></i>{{ __('admin.surveyTriggerAfterSession') }}
												</label>
												<div class="form-text ms-4">
													{{ __('admin.Survey will be sent after each mentorship session') }}
												</div>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="triggerByDue" name="trigger_by_due" />
												<label class="form-check-label" for="triggerByDue">
													<i class="ti ti-calendar-due me-1 text-warning"></i>{{ __('admin.surveyTriggerAfterCycle') }}
												</label>
												<div class="form-text ms-4">
													{{ __('admin.Survey will be sent at the end of the mentorship cycle') }}
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
							<i class="ti ti-circle-x me-1"></i>{{ __('admin.Cancel') }}
						</button>
						<button type="submit" form="createSurveyForm" class="btn btn-primary" id="createSurveySubmitBtn">
							<span id="createSurveyBtnIcon"><i class="ti ti-circle-check me-2"></i></span>
							<span id="createSurveyBtnSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
							{{ __('admin.Add Survey') }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
	<script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
	<script src="/app-assets/js/scripts/modal/components-modal.js"></script>

	<script type="text/javascript">
		// Initialize tooltips
		$(function () {
			$('[data-bs-toggle="tooltip"]').tooltip();
		});

		// Initialize DataTable
		$(document).ready(function () {
			$('#surveys-list-datatable').DataTable({
				responsive: true,
				searching: true,
				lengthChange: false,
				paging: true,
				info: false,
				order: [[0, 'asc']],
				columnDefs: [
					{
						orderable: false,
						targets: [4, 6], // Triggers and Actions columns
					},
					{
						className: 'text-center',
						targets: [0, 2, 3, 4, 5, 6],
					},
				],
				dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
				@if($l == 'ar')
					language: {
						sEmptyTable: 'ليست هناك بيانات متاحة في الجدول',
						sLoadingRecords: 'جارٍ التحميل...',
						sProcessing: 'جارٍ التحميل...',
						sLengthMenu: 'أظهر _MENU_ مدخلات',
						sZeroRecords: 'لم يعثر على أية سجلات',
						sInfo: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
						sInfoEmpty: 'يعرض 0 إلى 0 من أصل 0 سجل',
						sInfoFiltered: '(منتقاة من مجموع _MAX_ مُدخل)',
						sInfoPostFix: '',
						sSearch: 'ابحث:',
						sUrl: '',
						oPaginate: {
							sFirst: 'الأول',
							sPrevious: 'السابق',
							sNext: 'التالي',
							sLast: 'الأخير',
						},
						oAria: {
							sSortAscending: ': تفعيل لترتيب العمود تصاعدياً',
							sSortDescending: ': تفعيل لترتيب العمود تنازلياً',
						},
					},
				@endif
			});
		});

		// Enhanced delete confirmation
		$(document).on('click', '#deleteAction', function (e) {
			e.preventDefault();
			var url = $(this).data('url');
			var token = '{{ csrf_token() }}';

			releaseAlert('warning', {
			  title: "{{__('admin.SureDelete')}}",
			  message: "{{__('admin.Recover')}}",
			  confirmText: "{{ __('admin.DYes') }}",
			  cancelText: "{{ __('admin.No') }}",
			  confirmColor: 'danger'
			}).then((ok) => {
				if (ok) {
					// Show loading
					Swal.fire({
						title: '{{ __('admin.Deleting') }}...',
						allowOutsideClick: false,
						allowEscapeKey: false,
						showConfirmButton: false,
						didOpen: () => {
							Swal.showLoading();
						},
					});

					$.post(url, { _method: 'DELETE', _token: token })
					.done(function (response) {
						releaseAlert('success', {
						  title: "{{ __('admin.Deleted') }}!",
						  message: "{{ __('admin.FileDeleted') }}",
						  confirmText: "{{ __('admin.OK') }}",
						  confirmColor: 'primary',
						  autoClose: 1500
						}).then(() => {
							location.reload();
						});
					})
					.fail(function (xhr, status, error) {
						console.error('Delete failed:', error);
						releaseAlert('danger', {
						  title: '{{ __('admin.Error') }}',
						  message: '{{ __('admin.DeleteFailed') }}',
						  confirmText: '{{ __('admin.OK') }}',
						  confirmColor: 'primary'
						});
					});
				}
			});
		});

		// Form validation for modal
		$('#createSurveyForm').on('submit', function (e) {
			var surveyId = $('#survey_id').val();
			var type = $('#type').val();
			var triggerBySession = $('#triggerBySession').is(':checked');
			var triggerByDue = $('#triggerByDue').is(':checked');

			if (!surveyId || !type) {
				e.preventDefault();
				releaseAlert('warning', {
				  title: '{{ __('admin.ValidationError') }}',
				  message: '{{ __('admin.PleaseFillRequiredFields') }}',
				  confirmText: '{{ __('admin.OK') }}',
				  confirmColor: 'warning',
				  hideCancel: true
				});
				return false;
			}

			if (!triggerBySession && !triggerByDue) {
				e.preventDefault();
				releaseAlert('info', {
				  title: '{{ __('admin.NoTriggersSelected') }}',
				  message: '{{ __('admin.SelectAtLeastOneTrigger') }}',
				  confirmText: '{{ __('admin.OK') }}',
				  confirmColor: 'info'
				});
				return false;
			}

			// Show spinner and disable submit button
			var $btn = $('#createSurveySubmitBtn');
			$btn.prop('disabled', true);
			$('#createSurveyBtnIcon').addClass('d-none');
			$('#createSurveyBtnSpinner').removeClass('d-none');
		});
	</script>
@endsection
