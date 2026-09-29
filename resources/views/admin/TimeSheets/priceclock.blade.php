@extends('layouts.admin')

@section('content')
<div class="">
	<div class="">
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
          <div class="card-header d-flex flex-wrap align-items-md-center justify-content-between border-bottom">
            <h5 class="card-title mb-0">{{ $l == 'en' ? 'Users Timesheet' : 'الجداول الزمنية للمستخدمين' }}</h5>
            <div class="d-flex align-items-center gap-2">
              <div class="dropdown">
                <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ti ti-file-export me-2"></i>
                  <span>{{ __('admin.Export') }}</span>
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
            </div>
          </div>
          <div class="card-body">
            <div class="card-text">
              <div class="table-responsive">
                <table class="table mb-0" id="users-timesheet-table">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      @hasanyrole('Admin|organizationManager|manager')
                      <th>{{ __('admin.user') }}</th>
                      @endhasanyrole
                      <th>{{ __('admin.Price') }}  {{ $setting->Currency }}</th>
                      <th>{{ __('admin.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                    $c=1;
                    $groupedTimeSheets = $timeSheets->groupBy('user_id');
                    @endphp
                    @foreach ($groupedTimeSheets as $userId => $timesheets)
                    @php
                    $user = $timesheets->first()->user;
                    @endphp
                    <tr>
                      <td>{{$c++}}</td>
                      @hasanyrole('Admin|organizationManager|manager')
                      <td>{{$user->{'first_name_'.$l} ." ". $user->{'last_name_'.$l} }}</td>
                      @endhasanyrole
                      <td>{{ $user->houre_rate }}</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="modal" data-bs-target="#EditPrice{{ $user->id }}">
                            <i class="ti ti-edit text-info"></i>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <!-- Edit Price Modal -->
                    <div class="modal fade" id="EditPrice{{ $user->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-md">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title mb-0">{{ $l == 'en' ? 'Edit Price' : 'تعديل السعر' }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <form class="pt-0" action="{{ route('time_sheets.clock.update.mine', $user->id) }}" method="POST">
                              @csrf
                              <div class="row">
                                <div class="col-12">
                                  <div class="form-group mb-4">
                                    <label for="edit_pricetime_{{ $user->id }}" class="form-label">{{__('admin.Price')}} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                      <span class="input-group-text">{{ $setting->Currency }}</span>
                                      <input class="form-control" id="edit_pricetime_{{ $user->id }}" type="text" name="pricetime" value="{{ $user->houre_rate }}" required />
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="d-flex align-items-center justify-content-end gap-3 mt-6">
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">{{__('admin.Cancel')}}</button>
                                <button type="submit" class="btn btn-primary">{{__('admin.submit')}}</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!--/ Edit Price Modal -->
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
@stop

@section('pagescripts')
	<script>
	$(document).ready(function() {
		// Destroy existing DataTable if it exists
		if ($.fn.DataTable.isDataTable('#users-timesheet-table')) {
			$('#users-timesheet-table').DataTable().destroy();
		}

		// Initialize data table
		let usersTable = $('#users-timesheet-table').DataTable({
			destroy: true,
			responsive: true,
			pageLength: 10,
			columnDefs: [{
				orderable: false,
				@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
				targets: [3]
				@else
				targets: [2]
				@endif
			}],
			dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
			displayLength: 7,
			lengthMenu: [7, 10, 25, 50, 75, 100],
			buttons: [
				{
					extend: 'csv',
					text: '<i class="ti ti-file-text me-1"></i>CSV',
					className: 'btn btn-label-secondary buttons-csv d-none',
					title: `Users_Export_${new Date().toISOString().slice(0, 10)}`,
					exportOptions: { 
						@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
						columns: [0, 1, 2]
						@else
						columns: [0, 1]
						@endif
					}
				},
				{
					extend: 'excel',
					text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
					className: 'btn btn-label-secondary buttons-excel d-none',
					title: `Users_Export_${new Date().toISOString().slice(0, 10)}`,
					exportOptions: { 
						@if( $_user->hasAnyRole('Admin|organizationManager|manager') )
						columns: [0, 1, 2]
						@else
						columns: [0, 1]
						@endif
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
				},
			@endif
		});

		// Export functionality
		$('#export-csv').on('click', function(e) {
			e.preventDefault();
			usersTable.button('.buttons-csv').trigger();
		});

		$('#export-excel').on('click', function(e) {
			e.preventDefault();
			usersTable.button('.buttons-excel').trigger();
		});
	});
	</script>
@stop
