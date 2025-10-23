@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="content-body">
			<section id="multiple-column-form">
				@if (session()->has('message'))
				<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
					{{ __('admin.' . session()->get('message')) }}
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
				@endif

				@if ($errors->any())
				<div class="alert alert-danger">
					<ul class="mb-0">
						@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
				@endif
			</section>

			<section class="row">
				<div class="col-xl-12 col-md-12 col-sm-12">
					<div class="card">
						@if( $_user->ableToAny(['TimeSheet.create', 'TimeSheet.store'], $permissions) )
						<div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between border-bottom">
							<h4 class="card-title mb-3 mb-lg-0">{{ __('admin.all TimeSheets') }}</h4>
							<div class="d-flex align-items-center gap-2">
								<div class="dropdown">
									<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-file-export me-2"></i>
										<span class="d-none d-md-inline">{{ __('admin.Export') }}</span>
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
								<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateTimeSheet">
									<i class="ti ti-plus"></i>
									<span class="d-none d-md-inline ms-2">{{ __('admin.Add_New') }}</span>
								</button>
							</div>
						</div>
						@endif
						<div class="card-body">
							<div class="card-text">
								<div class="table-responsive">
									<table class="table mb-0" id="userstable">
										<thead class="table-light">
											<tr>
												<th>#</th>
												@if( $_user->hasAnyRole('Admin|organizationManager|manager') ) <th>{{ __('admin.user') }}</th>
												@endif
												<th>{{ __('admin.Title') }} / {{ __('admin.Description') }}</th>
												<th>{{ __('admin.Date') }}</th>
												<th>{{ __('admin.hours') }}</th>
												<th>{{ __('admin.Program') }}</th>
												<th>{{ __('admin.Status') }}</th>
												<th>{{ __('admin.Created At') }}</th>
												@if( $_user->ableToAny(['TimeSheet.show', 'TimeSheet.edit', 'TimeSheet.update',
												'TimeSheet.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
											</tr>
										</thead>
										<tbody>
											@php $c=1; @endphp
											@foreach ($timeSheets as $timeSheet)
											<tr>
												<td>{{$c++}}</td>
												@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
												<td>{{ $timeSheet->user ? $timeSheet->user->{'first_name_'.$l} ." ".
													$timeSheet->user->{'last_name_'.$l} : 'N/A' }}</td>
												@endif
												<td>{{$timeSheet->title}} <br> {{$timeSheet->description}} </td>
												<td>{{$timeSheet->date}}</td>
												<td>{{$timeSheet->hours_worked}}</td>
												<td>{{$timeSheet->cycle->title}}</td>
												<td>{!! $timeSheet->status == 0 ?'<span class="badge bg-label-danger">Draft</span>' : '<span
														class="badge bg-label-success">Published</span>' !!}</td>
												<td>{{$timeSheet->created_at->format('Y-m-d')}}</td>
												@if( $_user->ableToAny(['TimeSheet.show', 'TimeSheet.edit', 'TimeSheet.update',
												'TimeSheet.destroy'], $permissions) )
												<td>
													<div class="d-flex align-items-center gap-2">
														@if( $_user->ableToAny(['TimeSheet.edit', 'TimeSheet.update'], $permissions) )
														<div class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="modal"
															data-bs-target="#EditTimeSheet{{ $timeSheet->id }}">
															<i class="ti ti-edit text-info"></i>
														</div>
														@endif
														@if( $_user->ableTo('TimeSheet.destroy', $permissions) )
														<form action="{{ route('TimeSheet.destroy', $timeSheet->id) }}" method="POST"
															style="display: inline-block;">
															@csrf
															@method('DELETE')
															<button class="btn btn-label-danger btn-icon rounded-circle" type="submit"
																onclick="return confirm('Are you sure you want to delete this post?')">
																<span class="align-middle ml-25"> <i class="ti ti-trash text-danger"></i> </span>
															</button>
														</form>
														@endif
													</div>
												</td>
												@endif
											</tr>
											@if( $_user->ableToAny(['TimeSheet.edit', 'TimeSheet.update'], $permissions) )
											<!-- Edit Timesheet Modal -->
											<div class="modal fade" id="EditTimeSheet{{ $timeSheet->id }}" tabindex="-1" aria-hidden="true">
												<div class="modal-dialog modal-lg modal-simple modal-edit-user">
													<div class="modal-content">
														<div class="modal-body">
															<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
															<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Edit')}}</h4> </div>
															<form class="add-new-user pt-0" action="{{ route('TimeSheet.update', $timeSheet->id) }}" method="POST">
																@csrf
																@method('PUT')
																<div class="ecommerce-customer-add-basic mb-4" >
																	<div class="col-md-12">
																		<div class="mb-6 width49">
																			<div> <label for="TimeSheet_title" class="form-label" >{{__('admin.Title')}}</label> </div>
																			<input class="form-control @error('TimeSheet_title') is-invalid @enderror"
																				id="TimeSheet_title"
																				type="text"
																				name="title"
																				autocomplete="off"
																				value="{{ $timeSheet->title }}"
																			/>
																		</div>
																		<div class="mb-6 width49">
																			<div> <label for="TimeSheet_date" class="form-label" >{{__('admin.Date')}}</label> </div>
																			<input class="datewithouttime form-control @error('TimeSheet_date') is-invalid @enderror"
																				id="TimeSheet_date"
																				type="text"
																				name="TimeSheet_date"
																				autocomplete="off"
																				value="{{ $timeSheet->date }}"
																			/>
																		</div>
																		<div class="mb-6 width49">
																			<div> <label for="hours_worked" class="form-label" >{{__('admin.HoursWorked')}}</label> </div>
																			<input class="form-control @error('hours_worked') is-invalid @enderror"
																				id="hours_worked"
																				type="text"
																				name="hours_worked"
																				autocomplete="off"
																				value="{{ $timeSheet->hours_worked }}"
																			/>
																		</div>
																		<div class="mb-6 width49">
																			<div> <label for="description" class="form-label" >{{__('admin.Description')}}</label> </div>
																			<textarea id="description" class="form-control @error('description') is-invalid @enderror"
																			type="text"
																			name="description"
																			autocomplete="off">{{ trim($timeSheet->description) }}</textarea>
																		</div>
																		<div class="mb-6 width49">
																			<label class="form-label fs-6"> {{ __('admin.Category') }} </label>
																			<select class=" form-select" name="category">
																				<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }} </option>
																				@foreach ($categories as $category)
																					<option value="{{ $category->id }}" {{ $category->id == $timeSheet->category_id ? "selected" : "" }} >{{ $category->{'category_name_'. $l} }}</option>
																				@endforeach
																			</select>
																		</div>
																		<div class="mb-6 width49">
																			<label class="form-label fs-6"> {{ __('admin.Program') }} </label>
																			<select class=" form-select" name="program">
																				<option value="" disabled selected> {{ $l == 'en' ? 'Choose program' : 'اختر البرنامج' }} </option>
																				@foreach ($cycles as $cycle)
																				<option value="{{ $cycle->id }}" {{ $cycle->id == $timeSheet->program_id ? "selected" : "" }}> {{ $cycle->title }} </option>
																				@endforeach
																			</select>
																		</div>
																		@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
																		<div class="mb-6 width49">
																			<label class="form-label fs-6"> {{ __('admin.Users') }} </label>
																			<select class=" form-select" name="user_id">
																				<option value="" disabled selected> {{ $l == 'en' ? 'Choose User' : 'اختر المستخدم' }} </option>
																				@foreach ($users as $user)
																				<option value="{{ $user->id }}" {{ $user->id == $timeSheet->user_id ? "selected" : "" }} >{{ $user->{'first_name_'. $l} ." ". $user->{'last_name_'. $l} }}</option>
																				@endforeach
																			</select>
																		</div>
																		@endif
																		<div class="form-group mb-3 mt-4">
																			<label class="form-label fs-6"> {{ __('admin.Status') }}</label>
																			<div style="height: 45px; width: fit-content; background-color: #F5F3FF; padding: 0 0.5rem;" class="row align-items-center rounded-pill">
																				<button class="rounded-pill border-none Val0 fw-bold mx-1 {{ $timeSheet->status == 0 ? "bg-primary text-white" : "text-primary" }} "
																					id="statusButton"
																					type="button"
																					style="width: 140px; height: 30px; font-size: 12px;"
																				>
																					{{ __('cycles.Draft') }}
																				</button>
																				<button class="rounded-pill border-none Val1 fw-bold {{ $timeSheet->status == 1 ? "bg-primary text-white" : "text-primary" }}"
																					id="statusButton"
																					type="button"
																					style="width: 140px; height: 30px; font-size: 12px;"
																				>
																					{{ __('cycles.Published') }}
																				</button>
																				<input class="getVal" name="status" type="hidden" value="{{ $timeSheet->status }}" >
																			</div>
																		</div>
																	</div>
																</div>
																<div class="text-center">
																	@if( $_user->ableToAny(['TimeSheet.edit', 'TimeSheet.update'], $permissions) )
																	<button type="submit" class="btn btn-primary me-3 data-submit">{{__('admin.submit')}}</button>
																	@endif
																	<button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
											<!--/ Edit Timesheet Modal -->
											@endif
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			@if( $_user->ableToAny(['TimeSheet.create', 'TimeSheet.store'], $permissions) )
			<!-- Create Timesheet Modal -->
			<div class="modal fade" id="CreateTimeSheet" tabindex="-1" aria-hidden="true" dir="rtl">
				<div class="modal-dialog modal-lg modal-simple modal-edit-user">
					<div class="modal-content">
						<div class="modal-body">
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.TimeSheet')}}</h4> </div>
							<form method="POST" action="{{route('TimeSheet.store')}}" class="pt-0" >
								@csrf
								<div class="ecommerce-customer-add-basic mb-4" >
									<div class="col-md-12">
										<div class="mb-6 width49">
											<div> <label for="TimeSheet_title" class="form-label" >{{__('admin.Title')}}</label> </div>
											<input class="form-control @error('TimeSheet_title') is-invalid @enderror"
												id="TimeSheet_title"
												type="text"
												name="title"
												autocomplete="off"
											/>
										</div>
										<div class="mb-6 width49">
											<div> <label for="TimeSheet_date" class="form-label" >{{__('admin.Date')}}</label> </div>
											<input class="datewithouttime form-control @error('TimeSheet_date') is-invalid @enderror"
												id="TimeSheet_date"
												type="text"
												name="TimeSheet_date"
												autocomplete="off"
											/>
										</div>
										<div class="mb-6 width49">
											<div> <label for="hours_worked" class="form-label" >{{__('admin.HoursWorked')}}</label> </div>
											<input class="form-control @error('hours_worked') is-invalid @enderror"
												id="hours_worked"
												type="text"
												name="hours_worked"
												autocomplete="off"
											/>
										</div>
										<div class="mb-6 width49">
											<div> <label for="description" class="form-label" >{{__('admin.Description')}}</label> </div>
											<textarea id="description" class="form-control @error('description') is-invalid @enderror"
											type="text"
											name="description"
											autocomplete="off"></textarea>
										</div>
										<div class="mb-6 width49">
											<label class="form-label fs-6"> {{ __('admin.Category') }} </label>
											<select class="select2 form-select" id="category" name="category" dropdownParent="#CreateTimeSheet">
												<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }} </option>
												@foreach ($categories as $category)
												<option value="{{ $category->id }}">{{ $category->{'category_name_'. $l} }}</option>
												@endforeach
											</select>
										</div>
										<div class="mb-6 width49">
											<label class="form-label fs-6"> {{ __('admin.Program') }} </label>
											<select class="select2 form-select" id="program" name="program" dropdownParent="#CreateTimeSheet">
												<option value="" disabled selected> {{ $l == 'en' ? 'Choose program' : 'اختر البرنامج' }} </option>
												@foreach ($cycles as $cycle)
													<option value="{{ $cycle->id }}"> {{ $cycle->title }} </option>
												@endforeach
											</select>
										</div>
										@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
										<div class="mb-6 width49">
											<label class="form-label fs-6"> {{ __('admin.Users') }} </label>
											<select class="select2 form-select" id="user_id" name="user_id" dropdownParent="#CreateTimeSheet">
												<option value="" disabled selected> {{ $l == 'en' ? 'Choose User' : 'اختر المستخدم' }} </option>
												@foreach ($users as $user)
													<option value="{{ $user->id }}">{{ $user->{'first_name_'. $l} ." ". $user->{'last_name_'. $l} }}</option>
												@endforeach
											</select>
										</div>
										@endif
										<div class="form-group mb-3 mt-4">
											<label class="form-label fs-6"> {{ __('admin.Status') }}</label>
											<div style="height: 45px; width: fit-content; background-color: #F5F3FF; padding: 0 0.5rem;" class="row align-items-center rounded-pill">
												<button class="rounded-pill border-none Val0 text-primary fw-bold mx-1"
													id="statusButton"
													type="button"
													style="width: 140px; height: 30px; font-size: 12px;"
												>
													{{ __('cycles.Draft') }}
												</button>
												<button class="rounded-pill border-none Val1 text-primary fw-bold"
													id="statusButton"
													type="button"
													style="width: 140px; height: 30px; font-size: 12px;"
												>
													{{ __('cycles.Published') }}
												</button>
												<input class="getVal" name="status" type="hidden" >
											</div>
										</div>
									</div>
								</div>
								<br /><br /><br />	
								<div>
									@if( $_user->ableToAny(['TimeSheet.create', 'TimeSheet.store'], $permissions) )
									<button type="submit" class="btn btn-primary me-sm-4 data-submit">{{__('admin.submit')}}</button>
									@endif
									<button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">{{__('admin.Cancel')}}</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!--/ Create Timesheet Modal -->
			@endif

			@if( $_user->ableToAny(['TimeSheet.create', 'TimeSheet.store'], $permissions) )
			<!-- Create TimeSheet Modal -->
			<div class="modal fade" id="CreateTimeSheet" tabindex="-1" aria-labelledby="CreateTimeSheetLabel" aria-hidden="true">
				<div class="modal-dialog modal-md">
					<div class="modal-content">
						<form method="POST" action="{{route('TimeSheet.store')}}">
							@csrf
							<div class="modal-header">
								<h5 class="modal-title" id="CreateTimeSheetLabel">
									<i class="ti ti-plus me-2"></i>
									{{ __('admin.TimeSheet') }}
								</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>

							<div class="modal-body">
								<div class="row">
									<div class="col-12 mb-3">
										<label for="TimeSheet_title" class="form-label">{{__('admin.Title')}}</label>
										<input class="form-control @error('TimeSheet_title') is-invalid @enderror" id="TimeSheet_title"
											type="text" name="title" autocomplete="off" placeholder="{{ __('admin.Title') }}" required />
									</div>
									<div class="col-12 mb-3">
										<label for="description" class="form-label">{{__('admin.Description')}}</label>
										<textarea id="description" class="form-control @error('description') is-invalid @enderror"
											type="text" name="description" autocomplete="off" placeholder="{{ __('admin.Description') }}" required></textarea>
									</div>
									<div class="col-12 mb-3">
										<label for="program" class="form-label">{{ __('admin.Program') }}</label>
										<select class="select2 form-select" id="program" name="program"
											dropdownParent="#CreateTimeSheet" required>
											<option value="" disabled selected> {{ $l == 'en' ? 'Select program' : 'اختر البرنامج' }}
											</option>
											@foreach ($cycles as $cycle)
											<option value="{{ $cycle->id }}"> {{ $cycle->title }} </option>
											@endforeach
										</select>
									</div>
									@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
									<div class="col-12 mb-3">
										<label for="user_id" class="form-label">{{ __('admin.User') }}</label>
										<select class="select2 form-select" id="user_id" name="user_id"
											dropdownParent="#CreateTimeSheet">
											<option value="" disabled selected> {{ $l == 'en' ? 'Select User' : 'اختر المستخدم' }}
											</option>
											@foreach ($users as $user)
											<option value="{{ $user->id }}">{{ $user->{'first_name_'. $l} ." ". $user->{'last_name_'. $l}
												}}</option>
											@endforeach
										</select>
									</div>
									@endif
									<div class="col-md-6 mb-3">
										<label for="TimeSheet_date" class="form-label">{{__('admin.Date')}}</label>
										<input class="datewithouttime form-control @error('TimeSheet_date') is-invalid @enderror"
											id="TimeSheet_date" type="text" name="TimeSheet_date" autocomplete="off" placeholder="{{ __('admin.Date') }}" required />
									</div>
									<div class="col-md-6 mb-3">
										<label for="hours_worked" class="form-label">{{__('admin.HoursWorked')}}</label>
										<input class="form-control @error('hours_worked') is-invalid @enderror" id="hours_worked"
											type="number" name="hours_worked" autocomplete="off" min="0" placeholder="{{ __('admin.HoursWorked') }}" required />
									</div>
									<div class="col-md-6 mb-3">
										<label for="category" class="form-label">{{ __('admin.Category') }}</label>
										<select class="select2 form-select" id="category" name="category"
											dropdownParent="#CreateTimeSheet" data-placeholder="{{ $l == 'en' ? 'Select category' : 'اختر الفئة' }}">
											<option value="" disabled selected> {{ $l == 'en' ? 'Select category' : 'اختر الفئة' }}
											</option>
											@foreach ($categories as $category)
											<option value="{{ $category->id }}">{{ $category->{'category_name_'. $l} }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-6 mb-3">
										<label for="status" class="form-label">{{ __('admin.Status') }}</label>
										<select class="form-select" id="status" name="status" required>
											<option value="" disabled selected> {{ $l == 'en' ? 'Select status' : 'اختر الحالة' }} </option>
											<option value="0">{{ __('cycles.Draft') }}</option>
											<option value="1">{{ __('cycles.Published') }}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="modal-footer">
								@if( $_user->ableToAny(['TimeSheet.create', 'TimeSheet.store'], $permissions) )
								<button type="submit" class="btn btn-primary me-3 data-submit">
									{{__('admin.submit')}}
								</button>
								@endif
								<button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">
									{{__('admin.Cancel')}}
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!--/ Create TimeSheet Modal -->
			@endif
		</div>
	</div>
	<!-- END: Content-->
@stop

@section('pagescripts')
	<script>
	$(document).ready(function() {
		// Initalize datepicker
		$('.datepicker').flatpickr({
			monthSelectorType: 'static',
			allowInput: true,
			onReady: function (selectedDates, dateStr, instance) {
				let btn = document.createElement("button");
				btn.type = "button";
				btn.innerHTML = "Clear";
				btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
				btn.addEventListener("click", function () {
					instance.clear(); // <-- clear flatpickr value
					instance.close();
					$('#userstable').DataTable().draw(); // refresh filter
				});
				instance.calendarContainer.appendChild(btn);
			}
		});

		// Initalize date table
		let timeSheetsTable = $('#userstable').DataTable({
			responsive: true,
			pageLength: 10,
			columnDefs: [{
				orderable: false,
				targets: [6]
			}],
			order: [[7, 'desc']],
			dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
			displayLength: 7,
			lengthMenu: [7, 10, 25, 50, 75, 100],
			buttons: [
				{
					extend: 'csv',
					text: '<i class="ti ti-file-text me-1"></i>CSV',
					className: 'btn btn-label-secondary buttons-csv d-none',
					title: `TimeSheets_Export_${new Date().toISOString().slice(0, 10)}`,
					exportOptions: { 
						columns: [0, 1, 2, 3, 4, 5, 6]
					}
				},
				{
					extend: 'excel',
					text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
					className: 'btn btn-label-secondary buttons-excel d-none',
					title: `TimeSheets_Export_${new Date().toISOString().slice(0, 10)}`,
					exportOptions: { 
						columns: [0, 1, 2, 3, 4, 5, 6]
					}
				}
			],
			@if ($l == 'ar')
				language: {
					sEmptyTable: "ليست هناك بيانات متاحة في الجدول",
					sLoadingRecords: "جارٍ التحميل...",
					sProcessing: "جارٍ التحميل...",
					sLengthMenu: "أظهر _MENU_ مدخلات",
					sZeroRecords: "لم يعثر على أية سجلات",
					sInfo: "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					sInfoEmpty: "يعرض 0 إلى 0 من أصل 0 سجل",
					sInfoFiltered: "(منتقاة من مجموع _MAX_ مُدخل)",
					sInfoPostFix: "",
					sSearch: "ابحث:",
					sUrl: "",
					oPaginate: {
						sFirst: "الأول",
						sPrevious: "السابق",
						sNext: "التالي",
						sLast: "الأخير"
					},
					oAria: {
						sSortAscending: ": تفعيل لترتيب العمود تصاعدياً",
						sSortDescending: ": تفعيل لترتيب العمود تنازلياً"
					}
				}
			@endif,
			initComplete: function (settings, json) {
				@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
				// User filter
				const userFilter = $(`<select id="user-filter" class="form-select form-select-sm mb-2 mt-2 mt-md-0 mb-lg-0 ms-md-6 me-1" style="width: 150px; display: inline-block;"><option value="">{{ __('admin.Select_User') }}</option></select>`)
					.insertAfter('.dataTables_length label')
					.on('change', function () {
						const val = $.fn.dataTable.util.escapeRegex($(this).val());
						timeSheetsTable.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
					});

				const l = "{{ $l }}";

				// Populate user filter dynamically
				const users = {!! json_encode($users->map(function($user) { return ['id' => $user->id, 'first_name_en' => $user->first_name_en, 'first_name_ar' => $user->first_name_ar, 'last_name_en' => $user->last_name_en, 'last_name_ar' => $user->last_name_ar]; })) !!};
				users.forEach(user => {
					const name = user['first_name_' + l] + ' ' + user['last_name_' + l];
					userFilter.append('<option value="' + user.id + '">' + name + '</option>');
				});
				@endif

				// Program filter
				const programFilter = $(`<select id="program-filter" class="form-select form-select-sm mb-2 mb-lg-0 me-1" style="width: 150px; display: inline-block;"><option value="">{{ __('admin.Select Program') }}</option></select>`)
					@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
					.insertAfter('#user-filter')
					@else
					.insertAfter('.dataTables_length label')
					@endif
					.on('change', function () {
						const val = $.fn.dataTable.util.escapeRegex($(this).val());
						@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
						timeSheetsTable.column(5).search(val ? '^' + val + '$' : '', true, false).draw();
						@else
						timeSheetsTable.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
						@endif
					});

				// Status filter
				const statusFilter = $(`<select id="status-filter" class="form-select form-select-sm mb-2 mb-lg-0" style="width: 150px; display: inline-block;"><option value="">{{ __('admin.Select Status') }}</option><option value="Draft">{{ __('cycles.Draft') }}</option><option value="Published">{{ __('cycles.Published') }}</option></select>`)
					.insertAfter('#program-filter')
					.on('change', function () {
						const val = $.fn.dataTable.util.escapeRegex($(this).val());
						@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
						timeSheetsTable.column(6).search(val ? '^' + val + '$' : '', true, false).draw();
						@else
						timeSheetsTable.column(5).search(val ? '^' + val + '$' : '', true, false).draw();
						@endif
					});

				// Populate program filter dynamically
				const programs = {!! json_encode($cycles->pluck('title')->unique()->values()) !!};
				programs.forEach(program => {
					programFilter.append('<option value="' + program + '">' + program + '</option>');
				});

				@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
				// Date filter
				const dateFilter = $(`<input type="text" id="date-filter" class="form-control form-control-sm datepicker me-1" style="width: 150px; display: inline-block;" placeholder="{{ __('admin.Date') }}">`)
					.insertAfter('#status-filter');

				// Initialize datepicker for the filter
				dateFilter.flatpickr({
					monthSelectorType: 'static',
					allowInput: true,
					onChange: function(selectedDates, dateStr, instance) {
						timeSheetsTable.draw();
					},
					onReady: function (selectedDates, dateStr, instance) {
						let btn = document.createElement("button");
						btn.type = "button";
						btn.innerHTML = "Clear";
						btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
						btn.addEventListener("click", function () {
							instance.clear();
							instance.close();
							timeSheetsTable.draw();
						});
						instance.calendarContainer.appendChild(btn);
					}
				});
				@endif
			}
		});

		// Date filter
		$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
			// Get the selected date from the filter input
			let filterDate = $('#date-filter').val();

			// If no date is selected, show all records
			if (!filterDate) {
				return true;
			}

			// Determine the correct column index for the 'Date' field based on the user's role
			@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
			// For Admins/Managers, the 'user' column is visible, so 'Date' is at index 3
			let dateColumnIndex = 3;
			@else
			// For other users, 'Date' is at index 2
			let dateColumnIndex = 2;
			@endif

			// Get the date from the correct table column
			let tableDate = data[dateColumnIndex] || "";

			// Perform a direct comparison between the table date and the filtered date
			return tableDate === filterDate;
		});

		// Export functionality
		$('#export-csv').on('click', function(e) {
			e.preventDefault();
			timeSheetsTable.button('.buttons-csv').trigger();
		});

		$('#export-excel').on('click', function(e) {
			e.preventDefault();
			timeSheetsTable.button('.buttons-excel').trigger();
		});
	});

	const levelButton = document.querySelectorAll('#levelButton');

	// Date Time Picker
	$('.date').flatpickr({
		enableTime: true,
		dateFormat: 'Y-m-d H:i'
	});

	$('.datewithouttime').flatpickr({
		dateFormat: 'Y-m-d'
	});

	// Initialize select2 for form dropdowns
	$('.select2').select2({
		dropdownParent: function() {
			return $(this).closest('.modal');
		}
	});
</script>
@stop