@extends('layouts.admin')

@section('vendorstyle')
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/jkanban/jkanban.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
	<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@php $user = auth()->user(); @endphp

@section('style')
	<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-kanban.css" />
	<style>
		#card-header {
			display: flex;
			gap: 10px;
			width: 100%;
		}
		.text-xs {
			font-size: 12px;
		}

		@media screen and (min-width: 768px) {
			#card-header {
				width: 50%;
			}
		}
		.kanban-container,
		.kanban-wrapper {
			display: flex !important;
			width: 100% !important;
			flex-grow: 1 !important;
			padding-inline-start: 0.7rem;
		}

		.kanban-item,
		.kanban-board {
			padding: 0 !important;
			width: 300px !important;
		}

		.kanban-board-header {
			padding: 0 !important;
			background-color: var(--bs-light) !important;
			margin-bottom: 1rem !important;
			width: 100% !important;
			border-radius: 0.25rem !important;
		}

		.kanban-title-board {
			font-size: 1rem !important;
			font-weight: 600 !important;
			padding: 0.3rem 1rem !important;
			letter-spacing: 0.5px !important;
		}
		p,
		span {
			font-size: 12px;
		}
	</style>
@stop

@section('content')
	<!-- Content wrapper -->
	<div class="">
		<!-- Content -->
		<div class="">
			<div class="card">
				<!-- Startup Info Header -->
				<div id="startup-info-header"
					class="card-header mx-6 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom mb-4">
					<div class="d-flex align-items-center">
						@if (!empty($data['company_name']))
						<div class="d-flex align-items-center">
							<div
								class="p-2 w-px-75 h-px-75 border shadow-sm rounded d-flex justify-content-center align-items-center me-4">
								<img src="{{ $data['start_up_logo'] ?? '/images/logo-placeholder.png' }}"
									alt="{{ $data['company_name'] }} Logo" class="rounded h-100 w-100 object-fit-contain overflow-hidden"
									onerror="this.onerror=null; this.src='/images/logo-placeholder.png';">
							</div>
							<div>
								<h4 class="mb-0">{{ $data['company_name'] }}</h4>
								<small class="text-muted">{{ $data['program_name'] }}</small>
							</div>
						</div>
						@endif
					</div>
					<div class="d-flex flex-column gap-3">
						<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
							<span class="fw-bold me-6">{{ __('admin.Current_Step') }}</span>
							<div class="badge bg-label-primary" style="width: fit-content;">{{ $data['step_name'] }}</div>
						</div>
						<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
							<span class="fw-bold me-6">{{ __('admin.Mentor_Session_Appointment') }}</span>
							@if (!empty($data['mentor_zoom_date']))
								<div class="badge bg-label-secondary" style="width: fit-content;">
									<i class="ti ti-calendar ti-16px me-1"></i>
									<span>{{ \Carbon\Carbon::parse($data['mentor_zoom_date'])->format('M d, Y H:i') }}</span>
								</div>
							@endif
						</div>
					</div>
				</div>
				<!-- / Startup Info Header -->

				<div class="card-body">
					<div class="d-flex justify-content-end w-100 border-bottom pb-3">
						<div id="card-header">
							<div class="ms-md-8 d-flex flex-grow-1 position-relative">
								<input id="search-task" type="text" class="form-control px-8" placeholder="{{ __('admin.Search') }}" />
								<i class="ti ti-search position-absolute top-50 translate-middle-y mx-2"></i>
							</div>
							@role('Mentor')
							<button id="addTaskButton" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formTaskModal">
								<div class="d-none d-md-inline me-2">
									{{ __('admin.Add_Task') }}
								</div>
								<i class="ti ti-plus"></i>
							</button>
							@endrole
						</div>
					</div>
				</div>

				<div class="app-kanban">
					<!-- Kanban Wrapper -->
					@if (!empty($tasks) && count($tasks) > 0)
						<div class="kanban-wrapper"></div>
					@else
						<div class="d-flex flex-column justify-content-center align-items-center gap-4 my-10">
							<img src="/icons/not-found.svg" width="337" height="234" />
							<h4 class="mb-0 text-center">
								 {{ __('admin.No_Tasks') }}
							</h4>
							<button id="addTaskButton" class="btn btn-md btn-primary" data-bs-toggle="modal" data-bs-target="#formTaskModal">
								<i class="ti ti-plus me-2"></i>
								{{ __('admin.Add_Task') }}
							</button>
						</div>
					@endif
				</div>		
			</div>
		</div>
		<!-- / Content -->

		<!-- View Task Modal -->
		<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content rounded overflow-hidden bg-label-light p-4">
					<div class="mb-4 p-4 p-md-0">
						<div class="d-flex justify-content-between align-items-center w-100">
							<div class="d-flex align-items-center gap-3">
								
								<div class="cursor-pointer" id="editTakButton" data-id="" data-task="">
									@role('Mentor')
									<i class="ti ti-edit text-primary ti-20px"></i>
									@endrole
								</div>
								
								<div class="cursor-pointer" id="confirmDeleteTask" data-task="">
									@role('Mentor')
									<i class="ti ti-trash text-danger ti-20px"></i>
									@endrole
								</div>
								
							</div>
							<h6 class="modal-title" id="viewTaskModalLabel">{{ __('admin.View_Task') }}</h6>
							<div id="closeViewTask" class="cursor-pointer border rounded" data-bs-dismiss="modal" aria-label="Close">
								<i class="ti ti-x ti-20px text-primary"></i>
							</div>
						</div>
					</div>
					<div class="bg-white rounded border border-light p-2 p-md-4">
						<h4 id="task-details-name" class="fw-medium">{{ __('admin.Task_Name') }}</h4>
						<div class="bg-label-secondary p-2 rounded mb-3" style="max-width: 100%;">
							<h6 class="fw-semibold mb-1">{{ __('admin.Description') }}</h6>
							<p id="task-details-contents" class="text-secondary" style="white-space: normal; word-break: break-word; overflow-wrap: break-word;">
							</p>
						</div>
						<div
							class="mb-3 w-100 d-flex flex-wrap flex-md-nowrap justify-content-between gap-2 align-items-center pb-2 border-bottom">
							<div class="d-flex justify-content-center align-items-center gap-2">
								<div class="d-flex justify-content-center align-items-center gap-1">
									<i class="ti ti-flag text-primary ti-24px"></i>
									<span class="text-dark">{{ __('admin.Priority') }}:</span>
								</div>
								<div id="task-details-priority" class="badge bg-label-warning d-flex align-items-center justify-content-center">
									<div style="width: 9px; height: 9px;" class="bg-warning rounded-circle me-1"></div>
									<span class="fw-semibold">{{ __('admin.Medium') }}</span>
								</div>
							</div>
							<div class="d-flex justify-content-center align-items-center gap-2">
								<div class="p-2 border rounded d-flex flex-column">
									<span class="text-dark fw-bold">{{ __('admin.Created') }}</span>
									<span class="text-muted"><i class="ti ti-clock me-1 ti-16px"></i><span id="task-details-reported-at">24 Feb 04:20 PM</span> </span>
								</div>
								<div class="p-2 border rounded d-flex flex-column">
									<span class="text-dark fw-bold">{{ __('admin.Updated') }}</span>
									<span class="text-muted"><i class="ti ti-clock me-1 ti-16px"></i><span id="task-details-updated-at">24 Feb 04:20 PM</span> </span>
								</div>
							</div>
						</div>
						<div class="bg-label-secondary p-2 rounded mb-3 border">
							<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-white">
								<div class="d-flex align-items-center justify-content-center gap-2">
									<h6 class="fw-semibold mb-0"><i class="ti ti-paperclip text-primary ti-24px me-2"></i>{{ __('admin.Attachments') }}</h6>
									<h6 class="mb-0"><span class="badge bg-white text-dark" id="attachmentCount">0</span></h6>
								</div>
								<input type="file" id="fileInput" style="display: none;" multiple onchange="handleFileUpload(event)" data-task="" />
								<button type="button" class="btn btn-sm btn-outline-info" style="width: 24px;"
									onclick="document.getElementById('fileInput').click();">
									<i class="ti ti-plus ti-16px"></i>
								</button>
							</div>
							<div class="d-flex flex-wrap gap-2" id="fileList"></div>
						</div>
						<div class="d-flex gap-10 mb-3 pb-3">
							<div class="d-flex flex-column gap-4">
								<h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-circle-dot text-primary ti-20px me-2"></i>{{ __('admin.Status') }}</h6>
								<h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-users text-primary ti-20px me-2"></i>{{ __('admin.Assignee') }}</h6>
								<h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-users text-primary ti-20px me-2"></i>{{ __('admin.Reporter') }}</h6>
								<h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-calendar text-primary ti-20px me-2"></i>{{ __('admin.Due_Date') }}</h6>
								<h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-calendar text-primary ti-20px me-2"></i>{{ __('admin.Complete_Date') }}</h6>
								<h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-tag text-primary ti-20px me-2"></i>{{ __('admin.Tags') }}</h6>
							</div>
							<div class="d-flex flex-column gap-3 overflow-x-auto">
								<div id="task-details-status" class="badge bg-label-secondary d-flex align-items-center" style="width:fit-content;">
									<div style="width:10px; height:10px;" class="rounded-circle me-2"></div>
									<span class="fw-semibold">{{ __('admin.In_Progress') }}</span>
								</div>
								<div id="task-details-assignee" class="d-flex align-items-center">
									<img src="#" class="rounded-circle" title="#" alt="#" width="24" height="24" style="{{ $lang == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
								</div>
								<div id="task-details-reporter" class="d-flex align-items-center">
									<img src="#" class="rounded-circle" title="#" alt="#" width="24" height="24" style="{{ $lang == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
								</div>
								<div id="task-details-due-date" class="d-flex align-items-center gap-3 py-0">
									<div class="badge bg-label-secondary radius-10 date">
										<i class="ti ti-calendar-time ti-18px text-info"></i>
										<span class="fw-semibold text-xs text-dark mx-2">2024-03-01</span>
									</div>
								</div>
								<div id="task-details-done-date" class="d-flex align-items-center gap-3 py-0">
									<div class="badge bg-label-secondary radius-10 date">
										<i class="ti ti-calendar-time ti-18px text-info"></i>
										<span class="fw-semibold text-xs text-dark mx-2">2024-03-01</span>
									</div>
								</div>
								<div id="task-details-labels" class="d-flex align-items-center justify-content-center gap-3"></div>
							</div>
						</div>
						<div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
							<div id="commentsTab" class="pb-2 border-bottom border-primary">
								<button id="commentsButton" style="border-radius: 15px" class="btn btn-outline-primary">{{ __('admin.Comments') }}</button>
							</div>
							<div id="activityTab" class="pb-2 ">
								<button id="activityButton" style="border-radius: 15px" class="btn">{{ __('admin.Activity') }}</button>
							</div>
						</div>
						<div id="commentsContainer">
							<div id="commentList" class="">
								<!-- Rendered comments will appear here -->
							</div>
							<div class="bg-label-secondary p-2 rounded mb-3 border">
								<div class="d-flex align-items-center gap-2">
									<input type="text" id="commentInput" class="form-control bg-white" placeholder="{{ __('admin.Add_Comment') }}" />
									<button id="commentButton" data-task="" type="button" class="btn btn-info">{{ __('admin.Publish') }}</button>
								</div>
							</div>
						</div>
						<div id="activityContainer" class="d-none">
							<div id="activityList" class=""></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- / View Task Modal -->

		<!-- Add Task Modal -->
		<div class="modal fade" id="formTaskModal" tabindex="-1" aria-labelledby="formTaskModal" aria-hidden="true">
			<div class="modal-dialog modal-md">
				<div class="modal-content rounded overflow-hidden bg-label-light p-md-4">
					<div class="mb-4 p-4 p-md-0">
						<div class="d-flex justify-content-between align-items-center w-100">
							<h6 class="modal-title" id="form-modal-title">{{ __('admin.Create_New_Task') }}</h6>
							<div id="closeViewTask" class="cursor-pointer border rounded" data-bs-dismiss="modal" aria-label="Close">
								<i class="ti ti-x ti-20px text-primary"></i>
							</div>
						</div>
					</div>
					<div class="bg-white rounded border border-light p-4">
						<form action="#" method="POST">
							<div class="mb-3">
								<label for="task-name" class="form-label">{{ __('admin.Task_Name') }}</label>
								<input type="text" class="form-control" id="task-name" name="task-name" placeholder="{{ __('admin.Task_Name') }}" />
							</div>
							<div class="mb-3">
								<label for="task-content" class="form-label">{{ __('admin.Description') }}</label>
								<div style="height: 150px; overflow-y: auto;" class="border border-light rounded">
									<div id="task-content-editor" class="form-control border-none"></div>
								</div>
								<textarea class="form-control d-none" id="task-content" name="task-content" rows="3"></textarea>
							</div>
							<div class="bg-label-secondary p-2 rounded mb-3 border">
								<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-white">
									<div class="d-flex align-items-center justify-content-center gap-2">
										<h6 class="fw-semibold mb-0"><i class="ti ti-paperclip text-primary ti-24px me-2"></i>{{ __('admin.Attachments') }}</h6>
										<h6 class="mb-0"><span class="badge bg-white text-dark" id="createAttachmentCount">0</span></h6>
									</div>
									<input type="file" id="createFileInput" style="display: none;" multiple onchange="handleAttachmentCreate(event)" data-task="" />
									<button type="button" class="btn btn-sm btn-outline-info" style="width: 24px;"
										onclick="document.getElementById('createFileInput').click();">
										<i class="ti ti-plus ti-16px"></i>
									</button>
								</div>
								<div class="d-flex flex-wrap gap-2" id="createFileList"></div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-status" class="form-label">
										<i class="ti ti-flag text-primary ti-24px"></i>
										{{ __('admin.Priority') }}
									</label>
								</div>
								<div class="col-4">
									<select class="form-select select2" id="task-priority" name="task-priority">
										<option value="low">{{ __('admin.Low') }}</option>
										<option value="medium">{{ __('admin.Medium') }}</option>
										<option value="high">{{ __('admin.High') }}</option>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-status" class="form-label">
										<i class="ti ti-circle-dot text-primary ti-20px me-2"></i>
										{{ __('admin.Status') }}
									</label>
								</div>
								<div class="col-4">
									<select class="form-select select2" id="task-status" name="task-status">
										<option value="pending">{{ __('admin.Pending') }}</option>
										<option value="progress">{{ __('admin.In_Progress') }}</option>
										<option value="review">{{ __('admin.Review') }}</option>
										<option value="completed">{{ __('admin.Completed') }}</option>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-assignee" class="form-label">
										<i class="ti ti-users text-primary ti-20px me-2"></i>
										{{ __('admin.Assignee') }}
									</label>
								</div>
								<div class="col-9">
									{{-- <select class="form-select select2" id="task-assignee" name="task-assignee">
										<option value="1">User 1</option>
										<option value="2">User 2</option>
										<option value="3">User 3</option>
									</select> --}}
									<input id="task-assignee" name="task-assignee" class="form-control" placeholder="{{ __('admin.Select_Users') }}" value="" />
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-due-date" class="form-label">
										<i class="ti ti-calendar text-primary ti-20px me-2"></i>
										{{ __('admin.Due_Date') }}
									</label>
								</div>
								<div class="col-4">
									<input type="text" class="form-control flatpickr" id="task-due-date" name="task-due-date" placeholder="{{ $lang == 'en' ? 'Select due date' : 'اختر تاريخ الانتهاء' }}" 
										style="font-size: 12px;"
									/>
								</div>
								<div class="col-4">
									{{--  <input type="date" class="form-control" id="task-due-date" name="task-due-date" />  --}}
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="tags" class="form-label">
										<i class="ti ti-tag text-primary ti-20px me-2"></i>
										{{ __('admin.Tags') }}
									</label>
								</div>
								<div class="col-9">
									<input id="task-labels" name="task-labels" class="form-control" placeholder="{{ __('admin.Select_Labels') }}" value="" />
								</div>
							</div>
						</form>
					</div>
					<div class="mt-md-4 d-flex align-items-center gap-3 p-4 p-md-0">
						<button type="button" class="btn btn-label-secondary w-px-100" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
						<button id="createTask" type="button" class="btn btn-primary w-px-100">
							{{ __('admin.Create') }}
							<span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
						</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Edit Task Modal -->
		<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModal" aria-hidden="true">
			<div class="modal-dialog modal-md">
				<div class="modal-content rounded overflow-hidden bg-label-light p-4">
					<div class="mb-4 p-4 p-md-0">
						<div class="d-flex justify-content-between align-items-center w-100">
							<h6 class="modal-title" id="form-modal-title">{{ __('admin.Edit_Task') }}</h6>
							<div id="closeViewTask" class="cursor-pointer border rounded" data-bs-dismiss="modal" aria-label="Close">
								<i class="ti ti-x ti-20px text-primary"></i>
							</div>
						</div>
					</div>
					<div class="bg-white rounded border border-light p-2 p-md-4">
						<form action="#" method="POST" id="editTaskForm">
							<div class="mb-3">
								<label for="task-name" class="form-label">{{ __('admin.Task_Name') }}</label>
								<input type="text" class="form-control" id="edit-task-name" name="edit-task-name" placeholder="{{ __('admin.Task_Name') }}" />
							</div>
							<div class="mb-3">
								<label class="form-label">{{ __('admin.Description') }}</label>
								<div style="height: 150px; overflow-y: auto;" class="border border-light rounded">
									<div id="edit-task-content-editor" class="form-control border-none"></div>
								</div>
								<textarea class="form-control d-none" id="edit-task-content" name="edit-task-content" rows="3"></textarea>
							</div>
							<div class="bg-label-secondary p-2 rounded mb-3 border">
								<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-white">
									<div class="d-flex align-items-center justify-content-center gap-2">
										<h6 class="fw-semibold mb-0"><i class="ti ti-paperclip text-primary ti-24px me-2"></i>{{ __('admin.Attachments') }}</h6>
										<h6 class="mb-0"><span class="badge bg-white text-dark" id="editAttachmentCount">0</span></h6>
									</div>
									<input type="file" id="editFileInput" style="display: none;" multiple onchange="handleAttachmentEdit(event)" data-task="" />
									<button type="button" class="btn btn-sm btn-outline-info" style="width: 24px;"
										onclick="document.getElementById('editFileInput').click();">
										<i class="ti ti-plus ti-16px"></i>
									</button>
								</div>
								<div class="d-flex flex-wrap gap-2" id="editFileList"></div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-status" class="form-label">
										<i class="ti ti-flag text-primary ti-24px"></i>
										{{ __('admin.Priority') }}
									</label>
								</div>
								<div class="col-4">
									<select class="form-select select2" id="edit-task-priority" name="edit-task-priority">
										<option value="low">{{ __('admin.Low') }}</option>
										<option value="medium">{{ __('admin.Medium') }}</option>
										<option value="high">{{ __('admin.High') }}</option>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-status" class="form-label">
										<i class="ti ti-circle-dot text-primary ti-20px me-2"></i>
										{{ __('admin.Status') }}
									</label>
								</div>
								<div class="col-4">
									<select class="form-select select2" id="edit-task-status" name="edit-task-status">
										<option value="pending">{{ __('admin.Pending') }}</option>
										<option value="progress">{{ __('admin.In_Progress') }}</option>
										<option value="review">{{ __('admin.Review') }}</option>
										<option value="completed">{{ __('admin.Completed') }}</option>
									</select>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-assignee" class="form-label">
										<i class="ti ti-users text-primary ti-20px me-2"></i>
										{{ __('admin.Assignee') }}
									</label>
								</div>
								<div class="col-9">
									{{-- <select class="form-select select2" id="task-assignee" name="task-assignee">
										<option value="1">User 1</option>
										<option value="2">User 2</option>
										<option value="3">User 3</option>
									</select> --}}
									<input
										id="edit-task-assignee"
										name="edit-task-assignee"
										class="form-control"
										placeholder="{{ __('admin.Select_Users') }}"
										value=""
									/>
								</div>
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="task-due-date" class="form-label">
										<i class="ti ti-calendar text-primary ti-20px me-2"></i>
										{{ __('admin.Due_Date') }}
									</label>
								</div>
								<div class="col-4">
									<input type="text" class="form-control flatpickr" id="edit-task-due-date" name="edit-task-due-date" placeholder="{{ $lang == 'en' ? 'Select due date' : 'اختر تاريخ الانتهاء' }}" 
										style="font-size: 12px;"
									/>
								</div>
								{{--  <div class="col-4">
									<input type="date" class="form-control" id="edit-task-due-date" name="edit-task-due-date" />
								</div>  --}}
							</div>
							<div class="row mb-3">
								<div class="col-3 d-flex align-items-center">
									<label for="tags" class="form-label">
										<i class="ti ti-tag text-primary ti-20px me-2"></i>
										{{ __('admin.Tags') }}
									</label>
								</div>
								<div class="col-9">
									<input
										id="edit-task-labels"
										name="edit-task-labels"
										class="form-control"
										placeholder="{{ __('admin.Select_Tags') }}"
										value=""
									/>
								</div>
							</div>
						</form>
					</div>
					<div class="mt-md-4 d-flex align-items-center gap-3 p-4 p-md-0">
						<input type="hidden" id="edit-task-id" name="edit-task-id" value="0" />
						<button type="button" class="btn btn-label-secondary w-px-100" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
						<button id="editTaskBtn" type="button" class="btn btn-primary w-px-100">
							{{ __('admin.Edit') }}
							<span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<div class="content-backdrop fade"></div>
		<div style="display: none;" class="offcanvas-backdrop fade show"></div>
	</div>
	<!-- Content wrapper -->
@stop

@section('scripts')
	<script src="/vuexy/assets/vendor/libs/jkanban/jkanban.js"></script>
	<script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script>
	<script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
	<script src="/vuexy/assets/vendor/libs/bloodhound/bloodhound.js"></script>
	<script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>

	<script>
		// Handle attachments upload
		function handleFileUpload(event) {
			const files = event.target.files;
			const task_id = $('#fileInput').attr('data-task');
			validateAndUploadFiles(files, task_id, 'view');
		}

		// Handle attachments create
		function handleAttachmentCreate(event) {
			const files = event.target.files;
			validateAndUploadFiles(files, null, 'create');
		}

		// Handle attachments edit
		function handleAttachmentEdit(event) {
			const files = event.target.files;
			const task_id = $('#editFileInput').attr('data-task');
			validateAndUploadFiles(files, task_id, 'edit');
		}

		// Validate and upload files
		function validateAndUploadFiles(files, task_id, mode) {
			const allowedTypes = [
				'application/pdf', 
				'image/jpeg', 
				'image/png', 
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
			];
			const maxFileSize = 10 * 1024 * 1024; // 10 MB

			for (const file of files) {
				if (!allowedTypes.includes(file.type)) {
					const errorMessage = lang === 'ar' 
						? 'يسمح فقط بملفات PDF و DOCX و XLSX و JPG و JPEG و PNG.' 
						: 'Only PDF, DOCX, XLSX, JPG, JPEG, and PNG files are allowed.';
					releaseFailureAlert({ message: errorMessage });
					return;
				}

				if (file.size > maxFileSize) {
					const errorMessage = lang === 'ar' 
						? 'الملف كبير جدًا. الحد الأقصى المسموح به هو 10 ميجابايت.' 
						: 'File too large. Maximum size allowed is 10 MB.';
					releaseFailureAlert({ message: errorMessage });
					return;
				}
			}
			
			// For create mode, store files for later upload
			if (mode === 'create') {
				pendingCreateAttachments = Array.from(files);
				renderFiles(files, mode);
			} else {
				uploadFiles(files, task_id, mode);
			}
		}

		// Upload files handler
		function uploadFiles(files, task_id, mode) {
			const formData = new FormData();

			for (const file of files) {
				formData.append('attachments[]', file);
			}
			
			formData.append('mentorship_request_id', {{ $session_id }});
			if (task_id) {
				formData.append('task_id', task_id);
			}
			formData.append('_token', '{{ csrf_token() }}');

			$.ajax({
				url: "{{ URL::to('dashboard/mentor/mentorshipTasks/upload-attachments') }}",
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.status) {
						// Update the board_tasks array with updated task data
						const taskIndex = board_tasks.findIndex(task => task.id == task_id);
						if (taskIndex !== -1) {
							board_tasks[taskIndex] = response.task;
						}
						
						// Only render files for edit mode after upload, not for create mode since modal is closing
						if (mode !== 'create') {
							renderFiles(response.task.files, mode);
						} else {
							// For create mode, reload kanban to update attachment count in task item
							reloadKanban();
						}
					} else {
						releaseFailureAlert({ message: response.message });
					}
				},
				error: function(error) {
					releaseFailureAlert({
						title: "{{ __('admin.Error') }}!",
						message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
					});
				}
			});
		}

		// Render files handler
		function renderFiles(files, mode) {
			let fileList, attachmentCount;
			if (mode === 'view') {
				fileList = document.getElementById('fileList');
				attachmentCount = document.getElementById('attachmentCount');
			} else if (mode === 'create') {
				fileList = document.getElementById('createFileList');
				attachmentCount = document.getElementById('createAttachmentCount');
			} else if (mode === 'edit') {
				fileList = document.getElementById('editFileList');
				attachmentCount = document.getElementById('editAttachmentCount');
				// Clear the file list before rendering new files
				fileList.innerHTML = '';
			}

			// Clear the file list only if mode is not 'edit'
			if (mode !== 'edit') {
				fileList.innerHTML = '';
			}

			Array.from(files).forEach(file => {
				const fileDiv = document.createElement('div');
				fileDiv.className = 'border rounded p-2 d-flex align-items-center bg-white';
				if (mode === 'create') {
					fileDiv.innerHTML = `
						<i class="ti ti-file text-info ti-20px me-2"></i>
						<span class="text-dark fw-bold">${file.name}</span>
						<div class="cursor-pointer me-2" onclick="deleteFileFromCreate(this.parentElement);">
							<i class="ti ti-trash text-danger ti-16px ms-2"></i>
						</div>
						<a href="${URL.createObjectURL(file)}" download="${file.name}" class="ms-auto">
							<i class="ti ti-download text-primary ti-16px"></i>
						</a>
					`;
				} else {
					fileDiv.innerHTML = `
						<i class="ti ti-file text-info ti-20px me-2"></i>
						<span class="text-dark fw-bold">${file.name}</span>
						<div class="cursor-pointer me-2" onclick="deleteFileHandler('${file.name}', '${file.id}', this.parentElement);">
							<i class="ti ti-trash text-danger ti-16px ms-2"></i>
						</div>
						<a href="${file.url}" download="${file.name}" class="ms-auto">
							<i class="ti ti-download text-primary ti-16px"></i>
						</a>
					`;
				}
				fileList.appendChild(fileDiv);
			});
			attachmentCount.textContent = files.length;
		}

		// Delete file handler
		function deleteFileHandler(fileName, fileId, ele) {
			$('#viewTaskModal').modal('hide');
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
					$('#viewTaskModal').modal('show');
					$.ajax({
						url: "{{ URL::to('dashboard/mentor/mentorshipTasks/delete-attachments') }}",
						type: 'POST',
						data: { file: fileId, _token: '{{ csrf_token() }}' },
						success: function(response) {
							if (response.status) {
								ele.remove();
								const attachmentCount = document.getElementById('attachmentCount');
								attachmentCount.textContent = document.getElementById('fileList').children.length;
							} else {
								releaseFailureAlert({ message: response.message });
							}
						},
						error: function(error) {
							releaseFailureAlert({
								title: "{{ __('admin.Error') }}!",
								message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						}
					});
					return;
				}
				$('#viewTaskModal').modal('show');
			});
		}

		// Delete file from create modal
		function deleteFileFromCreate(ele) {
			ele.remove();
			const attachmentCount = document.getElementById('createAttachmentCount');
			attachmentCount.textContent = document.getElementById('createFileList').children.length;
		}
	</script>
@stop

@section('pagescripts')
	<script src="/vuexy/assets/js/forms-typeahead.js"></script>
	<script>
		let data = @json($data);
		var kanban;
		var boards = @json($kanban);
		var lang = '{{ $l }}';

		// Global variable to store files selected during task creation
		let pendingCreateAttachments = [];

		if (lang === 'en') {
			boards.forEach(board => {
				if (board.id === 'progress') {
					board.title = 'In Progress';
				} else if (board.id) {
					board.title = board.id.charAt(0).toUpperCase() + board.id.slice(1);
				}
			});
		}

		var board_tasks = @json($tasks);
		function getElement(el) { 
			let id = $(el).data('eid');
			let element = board_tasks.filter(item => item.id == id)[0];
			return element;
		}

		// Render Kanban board
		const renderKanbanBoard = () => {
			const element = document.querySelector('.kanban-wrapper');
			if (!element) return;
			kanban = new jKanban({
				element: '.kanban-wrapper',
				gutter: '15px',
				widthBoard: '250px',
				boards,
				addItemButton: false,
				dragBoards: true,
				dragItems: true,
				click: (el) => {
					let element = getElement(el);
					let id = element.id;
					let url = "{{ URL::to('dashboard/mentor/mentorshipTasks/display') }}/" + id;
					$('#editTakButton').attr('data-id', '');
					$('#editTakButton').attr('task', 'id');
					$.ajax({
						url: url,
						type: "GET",
						dataType: "json",
						success: function( element ) {
							let bg = '';
							$('#editTakButton').attr('data-id', id);
							$('#editTakButton').attr('data-task', id);
							$('#confirmDeleteTask').attr('data-task', id);
							$('#commentButton').attr('data-task', id);
							$('#fileInput').attr('data-task', id);
							
							if( element.priority == 'high' ) { bg = 'danger'; }
							else if( element.priority == 'medium' ) { bg = 'warning'; }
							else { bg = 'success'; }
							let priority = lang === 'en' ? (element.priority.charAt(0).toUpperCase() + element.priority.slice(1)) : element.priority_visible;
							let reported_at = new Date(element.reported_at);
							let updated_at = new Date(element.updated_at);
		
							let reported_month = reported_at.toLocaleString('default', { month: 'short' });
							let reported_day = `0${reported_at.getDate()}`.slice(-2);
							let reported_hours = `0${reported_at.getHours()}`.slice(-2);
							let reported_minutes = `0${reported_at.getMinutes()}`.slice(-2);
							let reported_AmPm = reported_at.getHours() >= 12 ? 'PM' : 'AM';
							let reported_at_pretty = `${reported_day} ${reported_month} ${reported_hours}:${reported_minutes} ${reported_AmPm}`;
		
							let updated_month = updated_at.toLocaleString('default', { month: 'short' });
							let updated_day = `0${updated_at.getDate()}`.slice(-2);
							let updated_hours = `0${updated_at.getHours()}`.slice(-2);
							let updated_minutes = `0${updated_at.getMinutes()}`.slice(-2);
							let updated_AmPm = updated_at.getHours() >= 12 ? 'PM' : 'AM';
							let updated_at_pretty = `${updated_day} ${updated_month} ${updated_hours}:${updated_minutes} ${updated_AmPm}`;
							
							$('#task-details-name').html(element.name);
							$('#task-details-contents').html(element.content);
		
							$('#task-details-priority').removeClass('bg-label-warning bg-label-danger bg-label-success');
							$('#task-details-priority .rounded-circle').removeClass('bg-warning bg-danger bg-success');
							$('#task-details-priority').addClass(`bg-label-${bg}`);
							$('#task-details-priority .rounded-circle').addClass(`bg-${bg}`);
							$('#task-details-priority .fw-semibold').html(priority);
		
							$('#task-details-reported-at').html(reported_at_pretty);
							$('#task-details-updated-at').html(updated_at_pretty);
							$('#attachmentCount').html(element.attachments);

							$('#task-details-status .rounded-circle').removeClass('bg-secondary bg-warning bg-info bg-success');
							if( element.status.toLowerCase() == 'pending' ) { $('#task-details-status .rounded-circle').addClass('bg-secondary'); }
							else if( element.status.toLowerCase() == 'progress' ) { $('#task-details-status .rounded-circle').addClass('bg-info'); }
							else if( element.status.toLowerCase() == 'review' ) { $('#task-details-status .rounded-circle').addClass('bg-warning'); }
							else if( element.status.toLowerCase() == 'completed' ) { $('#task-details-status .rounded-circle').addClass('bg-success'); }
		
							var due_date = '-';
							if( element.due_date != null )
							{
								due_date = new Date(element.due_date);
								due_date = due_date.toISOString().split('T')[0];
							}
							var done_date = '-';
							if( element.done_date != null )
							{
								done_date = new Date(element.done_date);
								done_date = done_date.toISOString().split('T')[0];
							}
		
							let statusText = element.visible_status;
							if (lang === 'en') {
								if (element.status === 'progress') {
									statusText = 'In Progress';
								} else {
									statusText = element.status.charAt(0).toUpperCase() + element.status.slice(1);
								}
							}
							$('#task-details-status .fw-semibold').html( statusText );
		
							$('#task-details-assignee img.rounded-circle').prop({src: element.assignee.photo, title: element.assignee.name, alt: element.assignee.name });
							$('#task-details-reporter img.rounded-circle').prop({src: element.reporter.photo, title: element.reporter.name, alt: element.reporter.name });
							
							if (due_date != '-') {
								$('#task-details-due-date .fw-semibold').html(due_date);
							} else {
								$('#task-details-due-date .fw-semibold').html('N / A');
							}

							console.log('done_date', done_date);
							if (done_date != '-') {
								$('#task-details-done-date .fw-semibold').html(done_date);
							} else {
								$('#task-details-done-date .fw-semibold').html('N / A');
							}
				
							var labels = element.labels.map(label => `<h6 class="mb-0"><span class="badge bg-label-info">${label.name}</span></h6>`).join('');
							$('#task-details-labels').html(labels);
							let commentList = document.getElementById('commentList');
							commentList.innerHTML = '';
							if( element.comments.length ) { commentList.classList.add('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border'); }
							else { commentList.classList.remove('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border'); }
		
							element.comments.forEach(comment => {
								commentList.innerHTML += `
									<div class="border rounded p-2 mb-2 bg-white">
										<div class="d-flex justify-content-between comment-element">
											<div class="d-flex align-items-center gap-2 mb-2">
												<img src="${comment.commenter.photo}" class="rounded-circle" title="${comment.commenter.name}" alt="${comment.commenter.name}" width="34" height="34">
												<div class="d-flex align-items-center gap-2">
													<h6 class="mb-0 text-dark fw-bold">${comment.commenter.name}</h6>
													<span title="${comment.created_at}" class="text-secondary text-xs">${comment.created_since}</span>
													${comment.edited ? '<span class="text-dark"<i class="fas fa-pencil" style="font-size: 10px;"></i> </span>' : ''}
												</div>
											</div>
											<div class="cursor-pointer" onclick="this.parentElement.parentElement.remove(); if( $('#commentList .comment-element').length == 0 ) { commentList.classList.remove('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border'); }">
												<i class="ti ti-trash text-danger ti-16px"></i>
											</div>
										</div>
										<div class="comment-text text-muted mb-0 text-wrap px-2 text-${lang == 'ar' ? 'right' : 'left'}" style="overflow-wrap: break-word;">
											${comment.comment}
										</div>
									</div>
								`;
							});
		
							let activityList = document.getElementById('activityList');
							activityList.innerHTML = '';
		
							if( element.logs.length > 0 ) {
								activityList.classList.add('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border'); 
								element.logs.forEach(log => {
									activityList.innerHTML += `
										<div class="border rounded p-2 mb-2 bg-white">
											<div class="d-flex justify-content-between">
												<div class="d-flex align-items-center gap-2 mb-2">
													<img src="${log.user.photo}" class="rounded-circle" title="${log.user.name}" alt="${log.user.name}" width="34" height="34">
													<div class="d-flex align-items-center gap-2">
														<h6 class="mb-0 text-dark fw-bold">${log.user.name}</h6>
														<span title="${log.created_at}" class="text-secondary text-xs">${log.created_since}</span>
													</div>
												</div>
											</div>
											<div class="text-muted mb-0" style="font-size: 14px; direction: {{ $lang == 'ar' ? 'rtl' : 'ltr' }};">${log.footprint}</div>
										</div>
									`;
								});
							}
							else {
								activityList.classList.remove('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border'); 
								activityList.innerHTML = `
									<div class="border rounded p-2 mb-2 bg-white">
										<div class="text-muted mb-0 text-center">
											{{ __('admin.No_Activity_Found') }}
										</div>
								`;
							}
		

							// Handle view attachments
							renderFilesHandler(element.files, 'view');
		
							$('#viewTaskModal').modal('show');
						},
						error: function(error) {
							console.log('error', error);
							releaseFailureAlert({
								title: "{{ __('admin.Error') }}!",
								message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						}
					});
				},
				dragEl: (el, source) => {
					// 
				},
				dragendEl: el => {
					// Perform any additional actions here
				},
				dropEl: (el, target, source, sibling) => {
					var element = getElement(el);

					var target_board_elements = target.parentElement.getElementsByClassName('kanban-item');
					var target_board_elements_array = Array.from(target_board_elements);

					var ids = target_board_elements_array.map((board_element, index) => { return $(board_element).data('eid'); });

					// Perform any additional actions here
					var send_data = {
						_token: "{{ csrf_token() }}",
						board_ids: ids,
						moved_task_id: element.id,
						mentorship_request_id: '{{ $session_id }}',
						old_board: source.parentElement.dataset.id,
						new_board: target.parentElement.dataset.id
					}
					$.ajax({
						url: "{{ URL::to('dashboard/mentor/mentorshipTasks/move-task') }}",
						type: "POST",
						data: send_data,
						dataType: "json",
						success: function(response) {
							if( response.status )
							{
								var data = response.data;
								boards = data.kanban;
								board_tasks = data.tasks;
								labels = data.labels;
							}
							else
							{
								releaseFailureAlert({ message: response.message });
							}
							reloadKanban();
						},
						error: function(error) {
							reloadKanban();
							if( error.status == 422 ) {
								for( const [key, messages] of Object.entries(error.responseJSON.errors) ) { messages.forEach(message => { releaseFailureAlert({ message }); }); }
								return;
							} 
							releaseFailureAlert({
								title: "{{ __('admin.Error') }}!",
								message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						}
					});
				}
			});
		};

		// Render kanban item
		const renderKanbanItem = () => {
			const tasks = document.querySelectorAll('.kanban-item');
			tasks.forEach(item => {
				var element = getElement(item);

				const labels = element?.labels.map(label => {
				// Check if `name` is a valid JSON string or a simple string
				if (label.name.startsWith("[") && label.name.endsWith("]")) {
					try {
						const parsed = JSON.parse(label.name); // Parse the stringified JSON
						return parsed.map(item => item.value); // Extract `value` properties
					} catch (e) {
						console.error("Error parsing JSON:", e);
						return []; // Handle invalid JSON gracefully
					}
				} else {
					// If `name` is a simple string, wrap it in an array
					return [label.name];
				}
			}).flat(); // Flatten the result into a single array if needed
				let taskContent = element.content;
				let taskName = element.name;
				let comments = element.comments;
				let reported_at = new Date(element.reported_at);
				let month = reported_at.toLocaleString('default', { month: 'short' });
				let day = `0${reported_at.getDate()}`.slice(-2);
				let reported_at_pretty = `${day} ${month}`;
				let bg = '';
				if( element.priority == 'high' ) { bg = 'danger'; }
				else if( element.priority == 'medium' ) { bg = 'warning'; }
				else { bg = 'success'; }
				let priority = lang === 'en' ? (element.priority.charAt(0).toUpperCase() + element.priority.slice(1)) : element.priority_visible;

				let content = `
					<div class="card shadow-sm p-3 d-flex flex-column">
						<div class="d-flex align-items-center gap-1 mb-2">
							${labels?.slice(0, 2)?.map(label => `<div class="badge bg-label-info">
								<div class="fw-bold" style="font-size: 11px;">${label}</div>
							</div>`).join('')}
							<div class="badge bg-label-${bg} d-flex align-items-center justify-content-center">
								<div style="width: 7px; height: 7px;" class="bg-${bg} rounded-circle me-1"></div>
								<div class="fw-bold" style="font-size: 11px;">${priority}</div>
							</div>
						</div>
						<div class="d-flex flex-column gap-2 border-bottom mb-2">
							<h6 class="mb-0 fw-bold mb-3">${taskName}</h6>
							<!-- <p class="text-muted mb-1 text-xs">${taskContent}</p> -->
						</div>
						<div class="d-flex justify-content-between">
							<div class="d-flex justify-content-center align-items-center">
								<img src="${element.assignee.photo}" class="rounded-circle" title="${element.assignee.name}" alt="${element.assignee.name}" width="24" height="24" style="{{ $lang == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
							</div>
							<div class="d-flex justify-content-center align-items-center gap-2">
								<span class="text-xs"><i class='ti ti-paperclip ti-16px me-1'></i>${element.attachments}</span>
								<span class="text-xs"><i class="ti ti-clock ti-16px me-1"></i>${reported_at_pretty}</span>
								<span class="text-xs"><i class="ti ti-message-2 ti-16px me-1"></i>${comments.length}</span>
							</div>
						</div>
					</div>
				`;

				item.innerHTML = content;
			})
		};

		const reloadKanban = () => {
			if (kanban) {
				kanban = null;
			}
			var kanbanWrapper = document.querySelector('.kanban-wrapper');
			if (kanbanWrapper) {
				var parent = kanbanWrapper.parentNode;
				parent.removeChild(kanbanWrapper);
				kanbanWrapper = document.createElement('div');
				kanbanWrapper.className = 'kanban-wrapper';
				parent.appendChild(kanbanWrapper);
			} else {
				// If the kanban wrapper doesn't exist (e.g., when adding the first task)
				const appKanban = document.querySelector('.app-kanban');
				if (appKanban) {
					// Remove the 'no tasks' message
					const noTasksMessage = appKanban.querySelector('.d-flex.flex-column.justify-content-center');
					if (noTasksMessage) {
						noTasksMessage.remove();
					}
					// Create and append the kanban wrapper
					kanbanWrapper = document.createElement('div');
					kanbanWrapper.className = 'kanban-wrapper';
					appKanban.appendChild(kanbanWrapper);
				}
			}
			renderKanbanBoard();
			setTimeout(() => {
				renderKanbanItem();
			}, 10);
		}

		// Filter tasks on search
		const searchTask = () => {
			const searchInput = document.getElementById('search-task');
			if (!searchInput) return;
			searchInput.addEventListener('input', (e) => {
				const searchValue = e.target.value;
				const tasks = document.querySelectorAll('.kanban-item');
				tasks.forEach(task => {
					const title = task.getAttribute('data-name');
					if (title.toLowerCase().includes(searchValue.toLowerCase())) {
						task.style.display = 'block';
					} else {
						task.style.display = 'none';
					}
				});
			});
		}

		if (board_tasks && board_tasks.length > 0) {
			renderKanbanBoard();
			renderKanbanItem();
			searchTask();
		}
	</script>
	<script>
		// Handle attachments upload
		function handleFileUpload(event) {
			const files = event.target.files;
			const task_id = $('#fileInput').attr('data-task');
			validateAndUploadFiles(files, task_id, 'view');
		}
		
		// Handle attachments create
		function handleAttachmentCreate(event) {
			const files = event.target.files;
			validateAndUploadFiles(files, null, 'create');
		}

		// Render files handler
		const renderFilesHandler = (files, mode) => {
			
			if (mode == 'view')
			{
				console.log('view mode');
				const fileList = document.getElementById('fileList');
				const attachmentCount = document.getElementById('attachmentCount');
				fileList.innerHTML = '';
				Array.from(files).forEach(file => {
					const fileDiv = document.createElement('div');
					fileDiv.className = 'border rounded p-2 d-flex align-items-center bg-white';
					fileDiv.innerHTML = `
						<i class="ti ti-file text-info ti-20px me-2"></i>
						<span class="text-dark fw-bold">${file.name}</span>
						<div 
							class="cursor-pointer me-2" 
							onclick="deleteFileHandlerFunc('${file.name}', '${file.id}', this.parentElement);"
						>
							<i class="ti ti-trash text-danger ti-16px ms-2"></i>
						</div>
						<a href="${file.url}" download="${file.url}" class="ms-auto">
							<i class="ti ti-download text-primary ti-16px"></i>
						</a>
					`;
					fileList.appendChild(fileDiv);
				});
				attachmentCount.textContent = files.length;
			} else {
				const fileList = mode === 'create' ? document.getElementById('createFileList') : document.getElementById('editFileList');
				const attachmentCount = mode === 'create' ? document.getElementById('createAttachmentCount') : document.getElementById('editAttachmentCount');
				fileList.innerHTML = '';
				Array.from(files).forEach(file => {
					const fileDiv = document.createElement('div');
					fileDiv.className = 'border rounded p-2 d-flex align-items-center bg-white';
					fileDiv.innerHTML = `
						<i class="ti ti-file text-info ti-20px me-2"></i>
						<span class="text-dark fw-bold">${file.name}</span>
						<div 
							class="cursor-pointer me-2" 
							onclick="deleteFileHandlerFunc('${file.name}', '${file.id}', this.parentElement);"
						>
							<i class="ti ti-trash text-danger ti-16px ms-2"></i>
						</div>
						<a href="${file.url}" download="${file.url}" class="ms-auto">
							<i class="ti ti-download text-primary ti-16px"></i>
						</a>
					`;
					fileList.appendChild(fileDiv);
				});
				attachmentCount.textContent = files.length;
			}
		}

		// Delete file handler
		const deleteFileHandlerFunc = (fileName, fileId, ele) => {
			$('#viewTaskModal').modal('hide');
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
					$('#viewTaskModal').modal('show');
					$.ajax({
						url: "{{ URL::to('dashboard/mentor/mentorshipTasks/delete-attachments') }}",
						type: 'POST',
						data: { file: fileId, _token: '{{ csrf_token() }}' },
						success: function(response) {
							if( response.status )
							{
								ele.remove();
								const attachmentCount = document.getElementById('attachmentCount');
								attachmentCount.textContent = document.getElementById('fileList').children.length;
								
								// Update the task attachment count in board_tasks and reload kanban
								const taskId = $('#fileInput').attr('data-task');
								const taskIndex = board_tasks.findIndex(task => task.id == taskId);
								if (taskIndex !== -1) {
									board_tasks[taskIndex].attachments = document.getElementById('fileList').children.length;
									reloadKanban();
								}
							} else { releaseFailureAlert({ message: response.message }); }
						},
						error: function(error) {
							releaseFailureAlert({
								title: "{{ __('admin.Error') }}!",
								message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						}
					});
					return;
				}

				$('#viewTaskModal').modal('show');
			});
		};
	</script>
	<script>
		const commentsTab = document.getElementById('commentsTab');
		const activityTab = document.getElementById('activityTab');

		const commentsContainer = document.getElementById('commentsContainer');
		const activityContainer = document.getElementById('activityContainer');

		const commentsButton = document.getElementById('commentsButton');
		const activityButton = document.getElementById('activityButton');

		commentsButton.addEventListener('click', () => {
			activityButton.classList.remove('btn-outline-primary');
			commentsButton.classList.add('btn-outline-primary');
			commentsTab.classList.add('border-bottom', 'border-primary');
			activityTab.classList.remove('border-bottom', 'border-primary');
			commentsContainer.classList.remove('d-none');
			activityContainer.classList.add('d-none');
		});

		activityButton.addEventListener('click', () => {
			activityButton.classList.add('btn-outline-primary');
			commentsButton.classList.remove('btn-outline-primary');
			activityTab.classList.add('border-bottom', 'border-primary');
			commentsTab.classList.remove('border-bottom', 'border-primary');
			activityContainer.classList.remove('d-none');
			commentsContainer.classList.add('d-none');
		});
	</script>
	<script type="text/javascript">
		var labels = @json($labels);
		var selected_tags = [];
		var edit_selected_tags = [];
		var selected_user = null;
		var edit_selected_user = null;
		var TagsCustomInlineSuggestion;
		var AssigneeCustomInlineSuggestion;
		var EditTagsCustomInlineSuggestion;
		var EditAssigneeCustomInlineSuggestion;

		const tagsInput = document.getElementById('task-labels');
		const assigneeInput = document.getElementById('task-assignee');
		const editTagsInput = document.getElementById('edit-task-labels');
		const editAssigneeInput = document.getElementById('edit-task-assignee');
		const editTakButton = document.getElementById('editTakButton');
		const DeleteTaskButton = document.getElementById('confirmDeleteTask');
		const commentButton = document.getElementById('commentButton');

		const refreshLabelsForCreate = () => {
			TagsCustomInlineSuggestion.off('change');
			TagsCustomInlineSuggestion.destroy();
			$('#task-labels').val('');
			TagsCustomInlineSuggestion = new Tagify(tagsInput, {
				whitelist: labels,
				maxTags: 10,
				dropdown: {
					maxItems: 20,
					classname: 'tags-inline',
					enabled: 0,
					closeOnSelect: false
				}
			}).on('change', function(e) { selected_tags =  e.detail.value.trim() ? selected_tags = JSON.parse( e.detail.value ) : []; });
		};

		const refreshLabelsForEdit = () => {
			EditTagsCustomInlineSuggestion.off('change');
			EditTagsCustomInlineSuggestion.destroy();
			$('#edit-task-labels').val('');
			EditTagsCustomInlineSuggestion = new Tagify(editTagsInput, {
				whitelist: labels,
				maxTags: 10,
				dropdown: {
					maxItems: 20,
					classname: 'tags-inline',
					enabled: 0,
					closeOnSelect: false
				}
			}).on('change', function(e) { edit_selected_tags =  e.detail.value.trim() ? edit_selected_tags = JSON.parse( e.detail.value ) : []; });
		}

		document.addEventListener('DOMContentLoaded', function() {
			$("#task-labels").val('');
			$("#task-assignee").val('');
			TagsCustomInlineSuggestion = new Tagify(tagsInput, {
				whitelist: labels,
				maxTags: 10,
				dropdown: {
					maxItems: 20,
					classname: 'tags-inline',
					enabled: 0,
					closeOnSelect: false
				}
			}).on('change', function(e) { selected_tags =  e.detail.value.trim() ? selected_tags = JSON.parse( e.detail.value ) : []; });

			AssigneeCustomInlineSuggestion = new Tagify(assigneeInput, {
				whitelist: @json($users),
				maxTags: 1,
				dropdown: {
					maxItems: 20,
					classname: 'tags-inline',
					enabled: 0,
					closeOnSelect: true
				}
			}).on('change', function(e) {
				if( e.detail.value.trim() )
				{
					selected_user = JSON.parse( e.detail.value ).map(item => item.id)[0];
				} else { selected_user = null; }
			});
		});

		editTakButton.addEventListener('click', function(e) {
			console.log('edit task button clicked');
			e.preventDefault();
			let task_id = $(this).attr('data-id');
			let url = "{{ URL::to('dashboard/mentor/mentorshipTasks/display') }}/" + task_id;
			$.ajax({
				url: url,
				type: "GET",
				dataType: "json",
				success: function( element ) {
					$('#edit-task-id').val( element.id );
					$('#edit-task-name').val( element.name );
					$('#edit-task-content').val( element.content );
					editQuill.root.innerHTML = element.content;
					$('#edit-task-priority').val( element.priority );
					$('#edit-task-priority').trigger('change');
					$('#edit-task-status').val( element.status );
					$('#edit-task-status').trigger('change');
					$('#edit-task-due-date').val( element.due_date );
					$('#editFileInput').attr('data-task', element.id);
					renderFilesHandler(element.files, 'edit');
					if( typeof( EditAssigneeCustomInlineSuggestion ) !== 'undefined' )
					{
						EditAssigneeCustomInlineSuggestion.off('change');
						EditAssigneeCustomInlineSuggestion.destroy();
					}
					let assignee = [];
					assignee.push({"id":element.assignee.id,"value":element.assignee.name});
					$('#edit-task-assignee').val(JSON.stringify(assignee));
					edit_selected_user = element.assignee.id;
					EditAssigneeCustomInlineSuggestion = new Tagify(editAssigneeInput, {
						whitelist: @json($users),
						maxTags: 1,
						dropdown: {
							maxItems: 20,
							classname: 'tags-inline',
							enabled: 0,
							closeOnSelect: true
						}
					}).on('change', function(e) {
						if( e.detail.value.trim() )
						{
							edit_selected_user = JSON.parse( e.detail.value ).map(item => item.id)[0];
						} else { edit_selected_user = null; }
					});

					if( typeof( EditTagsCustomInlineSuggestion ) !== 'undefined' )
					{
						EditTagsCustomInlineSuggestion.off('change');
						EditTagsCustomInlineSuggestion.destroy();
					}
					let _labels = [];
					element.labels.map(label => _labels.push({"id":label.id,"value":label.name}));
					$('#edit-task-labels').val(JSON.stringify(_labels));

					EditTagsCustomInlineSuggestion = new Tagify(editTagsInput, {
						whitelist: labels,
						maxTags: 10,
						dropdown: {
							maxItems: 20,
							classname: 'tags-inline',
							enabled: 0,
							closeOnSelect: false
						}
					}).on('change', function(e) { edit_selected_tags =  e.detail.value.trim() ? edit_selected_tags = JSON.parse( e.detail.value ) : []; });
					element.labels.map(label => edit_selected_tags.push({id: label.id, value: label.name}));
					$('#viewTaskModal').modal('hide');
					$('#editTaskModal').modal('show');
				},
				error: function(error) {
					releaseFailureAlert({
						title: "{{ __('admin.Error') }}!",
						message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
					});
				}
			});
		});

		DeleteTaskButton.addEventListener('click', function(e) {
			e.preventDefault();
			let task_id = $(this).attr('data-task');
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
					var send_data = {
						_token: "{{ csrf_token() }}",
						mentorship_request_id: '{{ $session_id }}',
						task_id: task_id,
					}
					$.ajax({
						url: "{{ URL::to('dashboard/mentor/mentorshipTasks/remove-task') }}",
						type: "POST",
						data: send_data,
						dataType: "json",
						success: function(response) {
							if( response.status )
							{
								$('#viewTaskModal').modal('hide');
								var data = response.data;
								boards = data.kanban;
								board_tasks = data.tasks;
								releaseSuccessAlert({ message: response.message, title: response.title });
								reloadKanban();
							} else { releaseFailureAlert({ message: response.message }); }
						},
						error: function(error) {
							if( error.status == 422 ) {
								for( const [key, messages] of Object.entries(error.responseJSON.errors) ) { messages.forEach(message => { releaseFailureAlert({message: message}); }); }
								return;
							}

							releaseFailureAlert({
								title: "{{ __('admin.Error') }}!",
								message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
							});
						}
					});
				}
			});
		})

		commentButton.addEventListener('click', function(e) {
			e.preventDefault();
			const commentInput = document.getElementById('commentInput');
			const commentList = document.getElementById('commentList');

			const allComments = commentList.querySelectorAll('div .comment-text');
			
			const commentText = commentInput.value.trim();

			let alreadyExists = false;
			
			if (commentText) {
				allComments.forEach(comment => {
					console.log('comment.textContent.trim()', comment.textContent.trim());
					if (comment.textContent.trim() === commentText.trim()) {
						alreadyExists = true;
					}
				});

				if (alreadyExists) {
					commentInput.value = '';
					releaseWarningAlert({ message: "{{ __('mentor.mentorshipTasks.comment_already_exists') }}" });
					return;
				}

				let task_id = $(this).attr('data-task');
				var send_data = {
					_token: "{{ csrf_token() }}",
					mentorship_request_id: '{{ $session_id }}',
					task_id: task_id,
					comment: commentText
				}
				$.ajax({
					url: "{{ URL::to('dashboard/mentor/mentorshipTasks/post-comment') }}",
					type: "POST",
					data: send_data,
					dataType: "json",
					success: function(response) {
						if( response.status )
						{
							commentList.classList.add('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border');
							const commentDiv = document.createElement('div');
							commentDiv.className = 'border rounded p-2 mb-2 bg-white';
							commentDiv.innerHTML = `
								<div class="d-flex justify-content-between comment-element">
									<div class="d-flex align-items-center gap-2 mb-2">
										<img src="{{ URL::to( $user->image_url ) }}" class="rounded-circle" title="{{ $user->name }}" alt="{{ $user->name }}" width="34" height="34">
										<div class="d-flex align-items-center gap-2">
											<h6 class="mb-0 text-dark fw-bold">{{ $user->name }}</h6>
											<span class="text-secondary text-xs">Just Now</span>
										</div>
									</div>
									<div class="cursor-pointer" onclick="this.parentElement.parentElement.remove(); if( $('#commentList .comment-element').length == 0 ) { commentList.classList.remove('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border'); }">
										<i class="ti ti-trash text-danger ti-16px"></i>
									</div>
								</div>
								<div class="comment-text text-muted mb-0 text-wrap px-2 text-${lang == 'ar' ? 'right' : 'left'}" style="overflow-wrap: break-word;">
									${commentText}
								</div>
							`;
							commentList.appendChild(commentDiv);
							commentInput.value = '';
						} else { releaseFailureAlert({ message: response.message }); }
					},
					error: function(error) {
						if( error.status == 422 ) {
							for( const [key, messages] of Object.entries(error.responseJSON.errors) ) { messages.forEach(message => { releaseFailureAlert({ message }); }); }
							return;
						}

						releaseFailureAlert({
							title: "{{ __('admin.Error') }}!",
							message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
						});
					}
				});
			}
		})

		$(document).on('click', '#createTask', function(e) {
			e.preventDefault();
			var send_data = {
				_token: "{{ csrf_token() }}",
				mentorship_request_id: '{{ $session_id }}',
				title: $('#task-name').val(),
				description: $('#task-content').val(),
				due_date: $("#task-due-date").val(),
				status: $('#task-status').val(),
				priority: $("#task-priority").val(),
				assignee: selected_user,
				labels: selected_tags
			};
			var $button = $(this);
			var $spinner = $button.find('.spinner-border');
			$spinner.removeClass('d-none');
			$.ajax({
				url: "{{ URL::to('dashboard/mentor/mentorshipTasks/add-task') }}",
				type: "POST",
				data: send_data,
				dataType: "json",
				success: function(response) {
					$spinner.addClass('d-none');
					if( response.status )
					{
						var data = response.data;
						boards = data.kanban;
						board_tasks = data.tasks;
						labels = data.labels;
						
						// Upload any pending attachments that were selected during task creation
						if (pendingCreateAttachments.length > 0) {
							const taskId = data.task.id;
							uploadFiles(pendingCreateAttachments, taskId, 'create');
							pendingCreateAttachments = []; // Clear the pending attachments
						}
						
						$('#formTaskModal').modal('hide');
						refreshLabelsForCreate();
						$('#task-name').val('');
						$('#task-content').val('');
						quill.root.innerHTML = '';
						$("#task-due-date").val('');
						$('#task-status').val('pending');
						$("#task-priority").val('low');
						$("#task-labels").val('');
						$("#task-assignee").val('');
						// Reset attachments
						$('#createFileList').empty();
						$('#createAttachmentCount').text('0');
						$('#createFileInput').val('');
						releaseSuccessAlert({ message: response.message, title: response.title });
						reloadKanban();
					} else { releaseFailureAlert({ message: response.message }); }
				},
				error: function(error) {
					$spinner.addClass('d-none');
					
					if( error.status == 422 ) {
						for( const [key, messages] of Object.entries(error.responseJSON.errors) ) { messages.forEach(message => { releaseFailureAlert({ message }); }); }
						return;
					}

					releaseFailureAlert({
						title: "{{ __('admin.Error') }}!",
						message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
					});
				}
			});
		});

		$(document).on('click', '#editTaskBtn', function(e) {
			e.preventDefault();
			var send_data = {
				_token: "{{ csrf_token() }}",
				mentorship_request_id: '{{ $session_id }}',
				task_id: $('#edit-task-id').val(),
				title: $('#edit-task-name').val(),
				description: $('#edit-task-content').val(),
				due_date: $("#edit-task-due-date").val(),
				status: $('#edit-task-status').val(),
				priority: $("#edit-task-priority").val(),
				assignee: edit_selected_user,
				labels: edit_selected_tags
			};
			var $button = $(this);
			var $spinner = $button.find('.spinner-border');
			$spinner.removeClass('d-none');
			$.ajax({
				url: "{{ URL::to('dashboard/mentor/mentorshipTasks/edit-task') }}",
				type: "POST",
				data: send_data,
				dataType: "json",
				success: function(response) {
					$spinner.addClass('d-none');
					if( response.status )
					{
						var data = response.data;
						boards = data.kanban;
						board_tasks = data.tasks;
						labels = data.labels;
						$('#editTaskModal').modal('hide');
						refreshLabelsForEdit();
						$('#edit-task-name').val('');
						$('#edit-task-content').val('');
						editQuill.root.innerHTML = '';
						$("#edit-task-due-date").val('');
						$('#edit-task-status').val('pending');
						$("#edit-task-priority").val('low');
						$("#edit-task-labels").val('');
						$("#edit-task-assignee").val('');
						releaseSuccessAlert({ message: response.message, title: response.title });
						reloadKanban();
					} else { releaseFailureAlert({ message: response.message }); }
				},
				error: function(error) {
					$spinner.addClass('d-none');
					if( error.status == 422 ) {
						for( const [key, messages] of Object.entries(error.responseJSON.errors) ) { messages.forEach(message => { releaseFailureAlert({ message }); }); }
						return;
					}

					releaseFailureAlert({
						title: "{{ __('admin.Error') }}!",
						message: error.statusText == "Unauthorized" ? "{{ __('admin.dont_have_permission') }}" : "{{ __('admin.An unexpected error occurred, please try again later') }}",
					});
				}
			});
		});
	</script>
	<script>
		// Initialize Quill editor for task content
		var quill = new Quill('#task-content-editor', {
			theme: 'snow',
			placeholder: lang == 'en' ? 'Write task content here...' : 'اكتب محتوى المهمة هنا...',
		});
		quill.on('text-change', function() {
			document.getElementById('task-content').value = quill.root.innerHTML;
		});

		// Initialize Quill editor for edit task content
		var editQuill = new Quill('#edit-task-content-editor', {
			theme: 'snow'
		});
		editQuill.on('text-change', function() {
			document.getElementById('edit-task-content').value = editQuill.root.innerHTML;
		});

		// // Set initial content for edit task
		// $('#editTakButton').on('click', function() {
		// 	var content = $('#edit-task-content').val();
		// 	editQuill.root.innerHTML = content;
		// });

		// Initialize Flatpickr for due date fields
		document.addEventListener('DOMContentLoaded', function() {
			// Initialize Flatpickr for create task modal
			flatpickr("#task-due-date", {
				dateFormat: "Y-m-d",
				minDate: "today",
			});

			// Initialize Flatpickr for edit task modal
			flatpickr("#edit-task-due-date", {
				dateFormat: "Y-m-d",
				minDate: "today",
			});

			// Clear task description field when opening add task modal
			$('#formTaskModal').on('show.bs.modal', function() {
				quill.root.innerHTML = '';
				document.getElementById('task-content').value = '';
				// Reset attachments when modal opens
				$('#createFileList').empty();
				$('#createAttachmentCount').text('0');
				$('#createFileInput').val('');
				pendingCreateAttachments = [];
			});
		});
	</script>
@stop
