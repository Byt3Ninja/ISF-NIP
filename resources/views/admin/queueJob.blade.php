@extends('layouts.admin')

@section('style')
	<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
	<div class="content-wrapper">
		<div class="container-xxl flex-grow-1 container-p-y">
			<div class="content-overlay"></div>
			<div class="content-header mb-4"> <h4 class="card-title">{{ __('admin.all_Jobs') }}</h4> </div>
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
                            
							
							<div class="card-body">
								<div class="card-text">
									<br>
                                    <a href="{{ route('queue.run.once') }}" class="btn btn-success">{{ __('admin.RunQueueOnce') }}</a>
                  <!-- Filter -->
									<div class="table-responsive">
										<table class="table mb-0" id="userstable">
											<thead class="thead-dark">
												<tr>
													<th>#</th>
													<th>{{ __('admin.Job') }}</th>
													<th>{{ __('admin.Data') }}</th>
													<th>{{ __('admin.TryCount') }}</th>
													<th>{{ __('admin.Created at') }}</th>
													
												</tr>
											</thead>
											<tbody>
												@php $c = 1; @endphp
												@foreach ($jobs as $job)
													<tr>
														<td>{{$c++}}</td>
														<td>{{ json_decode($job->payload)->displayName ?? 'N/A' }}</td>
														<td><pre>{{ json_encode(json_decode($job->payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
														<td>{{ $job->attempts }}</td>
														<td>{{$job->created_at}}</td>
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
					{"orderable": true, "targets":	[3]},
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
