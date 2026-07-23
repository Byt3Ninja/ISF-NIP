@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
	<div class="">
		<div class="">
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
							@if( $_user->ableToAny(['questions.create', 'questions.store'], $permissions) )
							<div class="card-header border-bottom d-flex justify-content-between align-items-center">
								<h5 class="card-title mb-0 text-capitalize">{{ __('admin.all Questions') }}</h5>
								<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CreateCategory">
									{{ __('admin.Add_New') }}
									<i class="ti ti-plus mx-2"></i>
								</button>
							</div>
							@endif
							<div class="card-body">
								<div class="card-text">
									<div class="table-responsive">
										<table class="table mb-0" id="userstable">
											<thead class="table-light">
												<tr>
													<th>#</th>
													<th>{{ __('admin.Question') }}</th>
													<th>{{ __('admin.Category') }}</th>
													<th>{{ __('admin.SubName') }}</th>
													<th>{{ __('cycles.Created at') }}</th>
													@if( $_user->ableToAny(['questions.show', 'questions.edit', 'questions.update', 'questions.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
												</tr>
											</thead>
											<tbody>
												@php $c = 1; @endphp
												@foreach ($questions as $question)
													<tr>
														<td>{{$c++}}</td>
														<td>{{$question->{'text_' . $l} }}</td>
														<td>{{$question->category->{'category_name_' . $l} }}</td>
														<td>{{$question->subcategory->{'name_' . $l} }}</td>
														<td>{{$question->created_at}}</td>
														@if( $_user->ableToAny(['questions.show', 'questions.edit', 'questions.update', 'questions.destroy'], $permissions) )
														<td>
															@if( $_user->ableTo('questions.show', $permissions) )<div class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ShowQuestion{{ $question->id }}" >{{__('admin.Show')}}</div>@endif
															@if( $_user->ableToAny(['questions.edit', 'questions.update'], $permissions) )<div class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#EditQuestion{{ $question->id }}">{{__('admin.Edit')}}</div>@endif
															@if( $_user->ableTo('questions.destroy', $permissions) )
															<form action="{{ route('questions.destroy', $question->id) }}" method="POST" style="display: inline-block;">
																@csrf
																@method('DELETE')
																<button class="btn btn-danger" type="submit" onclick="return confirm('Are you sure you want to delete this post?')">{{__('admin.Delete')}}</button>
															</form>
															@endif
														</td>
														@endif
													</tr>

													@if( $_user->ableToAny(['questions.edit', 'questions.update'], $permissions) )
													<!-- Edit Customer Modal -->
													<div class="modal fade" id="EditQuestion{{ $question->id }}" tabindex="-1" aria-hidden="true">
														<div class="modal-dialog modal-lg modal-simple modal-edit-user">
															<div class="modal-content">
																<div class="modal-body">
																	<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																	<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Edit')}} ({{$question->{'text_'.$l }	 }})</h4> </div>
																	<form class="add-new-user pt-0" action="{{ route('questions.update', $question->id) }}" method="POST">
																		@csrf
																		@method('PUT')
																		<input type="hidden" value="{{ $question->id }}" name="question_id">
																		<div class="ecommerce-customer-add-basic mb-4" >
																			<div class="col-md-12">
																				<div class="mb-6 width49">
																					<div> <label for="questions_title_ar" class="form-label" >{{ __('admin.Question') }} ( {{ __('admin.Arabic') }} )</label> </div>
																					<input class="form-control @error('questions_title_ar') is-invalid @enderror"
																						id="questions_title_ar"
																						type="text"
																						name="questions_title_ar"
																						autocomplete="off"
																						value="{{$question->text_ar}}"
																					/>
																				</div>
																				<div class="mb-6 width49">
																					<div> <label for="questions_title_en" class="form-label" >{{ __('admin.Question') }} ( {{ __('admin.English') }} )</label> </div>
																					<input class="form-control @error('questions_title_en') is-invalid @enderror"
																						id="questions_title_en"
																						type="text"
																						name="questions_title_en"
																						autocomplete="off"
																						value="{{$question->text_en}}"
																					/>
																				</div>
																				<div class="mb-6 width49">
																					<div> <label for="question_info_ar" class="form-label" >{{__('admin.Question_Info')}} ( {{ __('admin.Arabic') }} )</label> </div>
																					<textarea id="question_info_ar" class="form-control @error('question_info_ar') is-invalid @enderror"
																						type="text"
																						name="question_info_ar"
																						autocomplete="off">{{$question->info_ar}}</textarea>
																				</div>
																				<div class="mb-6 width49">
																					<div> <label for="question_info_en" class="form-label" >{{__('admin.Question_Info')}} ( {{ __('admin.English') }} )</label> </div>
																					<textarea id="question_info_en" class="form-control @error('question_info_en') is-invalid @enderror"
																						type="text"
																						name="question_info_en"
																						autocomplete="off">{{$question->info_en}}</textarea>
																				</div>
																				<div class="mb-6 width49">
																					<label class="form-label fs-6"> {{ __('admin.Category') }} </label>
																					<select class="category_id form-select" name="category_id">
																						<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }} </option>
																						@foreach ($categories as $category)
																							<option value="{{ $category->id }}" {{ $category->id == $question->category_id ? 'selected' : '' }}	>{{ $category->{'category_name_'. $l} }}</option>
																						@endforeach
																					</select>
																				</div>
																				<div class="mb-6 width49">
																					<label class="form-label fs-6"> {{ __('admin.SubName') }} </label>
																					<select class="subcategory_id form-select" name="subcategory_id">
																						<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم الفرعي' }} </option>
																						@foreach ($subcategories as $subcategory)
																							<option value="{{ $subcategory->id }}" {{ $subcategory->id == $question->subcategory_id ? 'selected' : '' }}	>{{ $subcategory->{'name_'. $l} }} {{ $subcategory->id }}</option>
																						@endforeach
																					</select>
																				</div>
																			</div>
																		</div>
																		<div class="text-center">
																			@if( $_user->ableToAny(['questions.edit', 'questions.update'], $permissions) )
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
													@if( $_user->ableTo('questions.show', $permissions) )
													<!-- Show Customer Modal -->
													<div class="modal fade" id="ShowQuestion{{ $question->id }}" tabindex="-1" aria-hidden="true">
														<div class="modal-dialog modal-lg modal-simple modal-edit-user">
															<div class="modal-content" dir="ltr">
																<div class="modal-body">
																	<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																	<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.Show')}} ( {{ $question->{'text_' . $l} }} )</h4> </div>
																	<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																		<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{ $question->{'text_ar'} }} <br> {{ $question->{'text_en'} }}</h6> </div>
																		<p class="m-0 d-none d-sm-block">{{ __('admin.Question') }}</p>
																	</div>
																	<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																		<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{$question->{'info_ar'} }}<br>{{ $question->{'text_en'} }}</h6> </div>
																		<p class="m-0 d-none d-sm-block">{{__('admin.Question_Info')}}</p>
																	</div>
																	<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																		<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{$question->category->{'category_name_'. $l} }}</h6> </div>
																		<p class="m-0 d-none d-sm-block">{{ __('admin.Category') }}</p>
																	</div>
																	<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																		<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{$question->subcategory->{'name_'. $l} }}</h6> </div>
																		<p class="m-0 d-none d-sm-block">{{ __('admin.SubName') }}</p>
																	</div>
																	<div class="d-flex justify-content-between align-items-center border-bottom py-4 mb-4">
																		<div class="d-flex gap-4 align-items-center"> <h6 class="m-0">{{ $question->created_at}}</h6> </div>
																		<p class="m-0 d-none d-sm-block">{{__('admin.Created At')}}</p>
																	</div>
																</div>
														</div>
														</div>
													</div>
													<!--/ Show Customer Modal -->
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

				@if( $_user->ableToAny(['questions.create', 'questions.store'], $permissions) )
				<!-- Create JobOrder Modal -->
				<div class="modal fade" id="CreateCategory" tabindex="-1" aria-hidden="true" dir="rtl">
					<div class="modal-dialog modal-lg modal-simple modal-edit-user">
						<div class="modal-content">
							<div class="modal-body">
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								<div class="text-center mb-6"> <h4 class="mb-2">{{__('admin.SubName')}}</h4> </div>
								<form method="POST" action="{{route('questions.store')}}" class="pt-0" >
									@csrf
									<div class="ecommerce-customer-add-basic mb-4">
										<div class="col-md-12">
											<div class="mb-6 width49">
												<div> <label for="questions_title_ar" class="form-label" >{{ __('admin.Question') }} ( {{ __('admin.Arabic') }} )</label> </div>
												<input class="form-control @error('questions_title_ar') is-invalid @enderror"
													id="questions_title_ar"
													type="text"
													name="questions_title_ar"
													autocomplete="off"
												/>
											</div>
											<div class="mb-6 width49">
												<div> <label for="questions_title_en" class="form-label" >{{ __('admin.Question') }} ( {{ __('admin.English') }} )</label> </div>
												<input class="form-control @error('questions_title_en') is-invalid @enderror"
													id="questions_title_en"
													type="text"
													name="questions_title_en"
													autocomplete="off"
												/>
											</div>
											<div class="mb-6 width49">
												<div> <label for="question_info_ar" class="form-label" >{{__('admin.Question_Info')}} ( {{ __('admin.Arabic') }} )</label> </div>
												<textarea id="question_info_ar" class="form-control @error('question_info_ar') is-invalid @enderror"
													type="text"
													name="question_info_ar"
													autocomplete="off"></textarea>
											</div>
											<div class="mb-6 width49">
												<div> <label for="question_info_en" class="form-label" >{{__('admin.Question_Info')}} ( {{ __('admin.English') }} )</label> </div>
												<textarea id="question_info_en" class="form-control @error('question_info_en') is-invalid @enderror"
													type="text"
													name="question_info_en"
													autocomplete="off"></textarea>
											</div>
											<div class="mb-6 width49">
												<label class="form-label fs-6"> {{ __('admin.Category') }} </label>
												<select class="category_id form-select" name="category_id">
													<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم' }} </option>
													@foreach ($categories as $category)
														<option value="{{ $category->id }}">{{ $category->{'category_name_'. $l} }}</option>
													@endforeach
												</select>
											</div>
											<div class="mb-6 width49">
												<label class="form-label fs-6"> {{ __('admin.SubName') }} </label>
												<select class="subcategory_id form-select" name="subcategory_id">
													<option value="" disabled selected> {{ $l == 'en' ? 'Choose category' : 'اختر القسم الفرعي' }} </option>
													@foreach ($subcategories as $subcategory)
														<option value="{{ $subcategory->id }}">{{ $subcategory->{'name_'. $l} }}</option>
													@endforeach
												</select>
											</div>
										</div>
									</div>
									<div>
										@if( $_user->ableToAny(['questions.create', 'questions.store'], $permissions) )
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
					url: "{{ route('subcategories.list_by_category') }}",
					method: 'GET',
					data: {
						category_id: category_id
					},
					success: function(data) {
						$state.html('<option value="">{{ __('admin.SubName') }}</option>');
						$.each(data, function(id, value) { $state.append('<option value="' + id + '" >' + value + '</option>'); });
					},
					error: function(xhr, status, error) { console.error('Error:', error); }
				});
			});
		});
	</script>					
@stop
