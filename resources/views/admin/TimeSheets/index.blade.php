@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header mb-4"> <h4 class="card-title">{{ __('admin.all TimeSheets') }}</h4> </div>
			<div class="content-body">
				<section id="multiple-column-form">
					@if (session()->has('message'))
						<div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" role="alert">
							{{ __('admin.' . session()->get('message')) }}
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					@endif
				</section>

				<section class="row">
					<div class="col-xl-12 col-md-12 col-sm-12">
						<div class="card">
							@if( $_user->ableToAny(['TimeSheet.create', 'TimeSheet.store'], $permissions) )
							<div class="card-header">
								<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateTimeSheet">
									{{ __('admin.Add new Time') }}
									<i class="ti ti-plus mx-2"></i>
								</button>
							</div>
							@endif
							<div class="card-body">
								@if( $_user->ableTo('TimeSheet.index', $permissions) )
								<form method="GET" action="{{ route('TimeSheet.index') }}">
									<div class="mb-6 width20">
										<label for="start_date">{{ __('admin.StartDate') }}</label>
										<input type="text" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control datewithouttime" placeholder="Select Start Date">
									</div>
									<div class="mb-6 width20">
										<label for="end_date">{{ __('admin.EndDate') }}</label>
										<input type="text" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control datewithouttime" placeholder="Select End Date">
									</div>
									<div class="mb-6 width25">
										<label class="form-label fs-6"> {{ __('admin.Program') }} </label>
										<select class=" form-select" name="cycle_name">
											<option value="" disabled selected> {{ $l == 'en' ? 'Choose program' : 'اختر البرنامج' }} </option>
											<option value="">{{ __('admin.SelectAll') }}</option>
											@foreach ($cycles as $cycle)
											<option value="{{ $cycle->id }}" {{ $cycle->id == request('cycle_name') ? "selected" : "" }} > {{ $cycle->title }} </option>
											@endforeach
										</select>
									</div>
									<button type="submit" class="btn btn-primary">{{ __('admin.Search') }}</button>
								</form>
								@endif
								<div class="card-text">
									<div class="table-responsive">
										<table class="table mb-0" id="userstable">
											<thead class="thead-dark">
												<tr>
													<th>#</th>
													@if( $_user->hasAnyRole('Admin|organizationManager|manager') ) <th>{{ __('admin.user') }}</th> @endif
													<th>{{ __('admin.Title') }} / {{ __('admin.Description') }}</th>
													<th>{{ __('admin.Date') }}</th>
													<th>{{ __('admin.hours') }}</th>
													<th>{{ __('admin.Program') }}</th>
													<th>{{ __('admin.Status') }}</th>
													<th>{{ __('admin.Created At') }}</th>
													@if( $_user->ableToAny(['TimeSheet.show', 'TimeSheet.edit', 'TimeSheet.update', 'TimeSheet.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
												</tr>
											</thead>
											<tbody>
												@php $c=1; @endphp
												@foreach ($timeSheets as $timeSheet)
												<tr>
													<td>{{$c++}}</td>
													@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
													<td>{{$timeSheet->user->{'first_name_'.$l} ." ". $timeSheet->user->{'last_name_'.$l} }}</td>
													@endif
													<td>{{$timeSheet->title}} <br> {{$timeSheet->description}} </td>
													<td>{{$timeSheet->date}}</td>
													<td>{{$timeSheet->hours_worked}}</td>
													<td>{{$timeSheet->cycle->title}}</td>
													<td>{!! $timeSheet->status == 0 ?'<span class="alert alert-danger">Draft</span>' : '<span class="alert alert-success">Published</span>' !!}</td>
													<td>{{$timeSheet->created_at}}</td>
													@if( $_user->ableToAny(['TimeSheet.show', 'TimeSheet.edit', 'TimeSheet.update', 'TimeSheet.destroy'], $permissions) )
													<td>
														@if( $_user->ableToAny(['TimeSheet.edit', 'TimeSheet.update'], $permissions) )
														<div class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="modal" data-bs-target="#EditTimeSheet{{ $timeSheet->id }}">
															<i class="ti ti-edit text-info"></i>
														</div>
														@endif
														@if( $_user->ableTo('TimeSheet.destroy', $permissions) )
														<form action="{{ route('TimeSheet.destroy', $timeSheet->id) }}" method="POST" style="display: inline-block;">
															@csrf
															@method('DELETE')
															<button class="btn btn-label-danger btn-icon rounded-circle" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">
																<span class="align-middle ml-25"> <i class="ti ti-trash text-danger"></i> </span>
															</button>
														</form>
														@endif
													</td>
													@endif
												</tr>
												@if( $_user->ableToAny(['TimeSheet.edit', 'TimeSheet.update'], $permissions) )
												<!-- Edit Customer Modal -->
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
												<!--/ Edit JO Modal -->
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
				<!-- Create JobOrder Modal -->
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
				<!--/ Create JobOrder Modal -->
				@endif
			</div>
		</div>
	</div>
	<!-- END: Content-->
@stop

@section('scripts')
	<!-- Vendors JS -->
	<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
	<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
	<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
	<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
	<!-- Page JS -->
	<script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop
@section('pagescripts')
	<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
	<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
	<script src="/js/jquery.datetimepicker.full.min.js"></script>
	<script>
		$(document).ready(function() {
			$('#userstable').DataTable({
				"responsive": true, "searching": true, "lengthChange": false, "paging": true, "bInfo": false,
				"columnDefs": [
					{"orderable": false, "targets":	[3]},
				],
				@if ($l == 'ar')
					"language": {
						"sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
						"sLoadingRecords": "جارٍ التحميل...", "sProcessing":	 "جارٍ التحميل...",
						"sLengthMenu": "أظهر _MENU_ مدخلات", "sZeroRecords":	"لم يعثر على أية سجلات",
						"sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
						"sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
						"sInfoPostFix": "",
						"sSearch": "ابحث:",
						"sUrl": "",
						"oPaginate": {
							"sFirst": "الأول",
							"sPrevious": "السابق",
							"sNext": "التالي",
							"sLast": "الأخير"
						},
						"oAria": {
							"sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
							"sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
						}
					}
				@endif
			});
		});

		const statusButton = document.querySelectorAll('#statusButton');
		const levelButton = document.querySelectorAll('#levelButton');

		statusButton.forEach((button) => {
			button.addEventListener('click', () => {
				statusButton.forEach((btn) => {
					btn.classList.remove('bg-primary', 'text-white');
					btn.classList.add('bg-white', 'text-primary');
				});
				button.classList.remove('bg-white', 'text-primary');
				button.classList.add('bg-primary', 'text-white');
			});
		});

		// Date Time Picker
		$('.date').datetimepicker({
			timepicker: true,
			format: 'Y-m-d H:i'
		});

		$('.datewithouttime').datetimepicker({
			format: 'Y-m-d'
		});
	</script>
@stop
