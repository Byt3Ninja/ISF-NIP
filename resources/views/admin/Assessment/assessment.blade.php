@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header mb-4">
				<h4 class="card-title">{{ __('admin.all Assessments') }}</h4>
			</div>
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
							@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
							<div class="card-header">
								<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateAssessment">
									{{ __('admin.Add new Assessment') }}
									<i class="ti ti-plus mx-2"></i>
								</button>
							</div>
							@endif
							<div class="card-body">
								<div class="card-text">
									<div class="table-responsive">
										<table class="table mb-0" id="userstable">
											<thead class="thead-dark">
												<tr>
													<th>#</th>
													<th>{{ __('admin.Start') }}</th>
													<th>{{ __('admin.End') }}</th>
													<th>{{ __('admin.user') }}</th>
													<th>{{ __('admin.Created at') }}</th>
													@if( $_user->ableToAny(['assessments.show', 'question-assessment_admin', 'assessments.edit', 'assessments.update', 'assessments.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
												</tr>
											</thead>
											<tbody>
												@php $c = 1; @endphp
												@foreach ($assessments as $assessment)
													<tr>
														<td>{{$c++}}</td>
														<td>{{$assessment->start_date }}</td>
														<td>{{$assessment->end_date }}</td>
														<td>{{$assessment->user->name }}</td>
														<td>{{$assessment->created_at}}</td>
														@if( $_user->ableToAny(['assessments.show', 'question-assessment_admin', 'assessments.edit', 'assessments.update', 'assessments.destroy'], $permissions) )
														<td>
															@if( $_user->ableTo('assessments.show', $permissions) )<a href="{{ route('assessments.show', $assessment->id) }}"><div class="btn btn-info" >{{ __('admin.Show') }}</div></a>@endif
															@if( $_user->ableTo('question-assessment_admin', $permissions) )<a href="{{ route('question-assessment_admin', $assessment->id) }}"><div class="btn btn-primary" >{{ __('admin.Assessment') }}</div></a>@endif
															@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )<div class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#EditAssessment{{ $assessment->id }}">{{__('admin.Edit')}}</div>@endif
															@if( $_user->ableTo('assessments.destroy', $permissions) )
															<form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST" style="display: inline-block;">
																@csrf
																@method('DELETE')
																<button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">{{__('admin.Delete')}}</button>
															</form>
															@endif
														</td>
														@endif
													</tr>
													@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )
													<!-- Edit Customer Modal -->
													<div class="modal fade" id="EditAssessment{{ $assessment->id }}" tabindex="-1" aria-hidden="true">
														<div class="modal-dialog modal-lg modal-simple modal-edit-user">
															<div class="modal-content">
																<div class="modal-body">
																	<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																	<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Edit')}} ({{$assessment->user->name}})</h4> </div>
																	<form class="add-new-user pt-0" action="{{ route('assessments.update', $assessment->id) }}" method="POST">
																		@csrf
																		@method('PUT')
																		<div class="ecommerce-customer-add-basic mb-4" >
																			<div class="col-md-12">
																				<div class="mb-6 width49">
																					<label class="form-label fs-6"> {{ __('admin.Name') }} </label>
																					<select class=" form-select" name="user_id">
																						<option value="" disabled selected> {{ $l == 'en' ? 'Choose User' : 'اختر المستخدم ' }} </option>
																						@foreach ($users as $user)
																							<option value="{{ $user->id }}" {{ $user->id == $assessment->user_id ? 'selected' : '' }}	>{{ $user->name }}</option>
																						@endforeach
																					</select>
																				</div>
																				<div class="mb-6 width49">
																					<div>
																						<label for="start_date" class="form-label" >{{__('admin.start_date')}}</label>
																					</div>
																					<input class="form-control date"
																						id="start_date"
																						type="text"
																						name="start_date"
																						autocomplete="off"
																						value="{{ $assessment->start_date }}"
																					/>
																				</div>
																				<div class="mb-6 width49">
																					<div> <label for="end_date" class="form-label" >{{__('admin.end_date')}}</label>
																					</div>
																					<input class="form-control date"
																						id="end_date"
																						type="text"
																						name="end_date"
																						autocomplete="off"
																						value="{{ $assessment->end_date }}"
																					/>
																				</div>
																			</div>
																		</div>
																		<div class="text-center">
																			@if( $_user->ableToAny(['assessments.edit', 'assessments.update'], $permissions) )
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
			</div>
		</div>
	</div>
	<!-- END: Content-->
	@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
	<!-- Create JobOrder Modal -->
	<div class="modal fade" id="CreateAssessment" tabindex="-1" aria-hidden="true" dir="rtl">
		<div class="modal-dialog modal-lg modal-simple modal-edit-user">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Add new Assessment')}}</h4> </div>
					<form method="POST" action="{{route('assessments.store')}}" class="pt-0" >
						@csrf
						<div class="ecommerce-customer-add-basic mb-4">
							<div class="col-md-12">
								<div class="mb-6 width49">
									<label class="form-label fs-6"> {{ __('admin.user') }} </label>
									<select class="form-select" name="user_id">
										<option value="" disabled selected> {{ $l == 'en' ? 'Choose User' : 'اختر مستخدم ' }} </option>
										@foreach ($users as $user)
											<option value="{{ $user->id }}">{{ $user->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="mb-6 width49">
									<div> <label for="start_date" class="form-label" >{{__('admin.start_date')}}</label> </div>
									<input class="form-control date"
										id="start_date"
										type="text"
										name="start_date"
										autocomplete="off"
									/>
								</div>
							</div>
						</div>
						<div>
							@if( $_user->ableToAny(['assessments.create', 'assessments.store'], $permissions) )
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
		$('.date').datetimepicker({
			timepicker: false,
			format: 'Y-m-d'
		});
		$(document).ready(function() {
			$('#userstable').DataTable({
				"responsive": true,
				"searching": true,
				"lengthChange": false,
				"paging": true,
				"bInfo": false,
				"columnDefs": [
					{"orderable": false, "targets":	[3]},
				],
				@if ($l == 'ar')
				"language": {
					"sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
					"sLoadingRecords": "جارٍ التحميل...",
					"sProcessing": "جارٍ التحميل...",
					"sLengthMenu": "أظهر _MENU_ مدخلات",
					"sZeroRecords": "لم يعثر على أية سجلات",
					"sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
					"sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
					"sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
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
			$('.category_id').change(function() {
				var category_id = $(this).val();
				var $state = $('.subcategory_id');
				$.ajax({
					url: "{{ route('get_subcategory_by_category') }}",
					method: 'GET',
					data: {
						category_id: category_id
					},
					success: function(data) {
						$state.html('<option value="">{{ __('admin.SubName') }}</option>');
						$.each(data, function(id, value) {
							$state.append('<option value="' + id + '" >' + value + '</option>');
						});
					},
					error: function(xhr, status, error) { console.error('Error:', error); }
				});
			});
		});
	</script>
@stop
