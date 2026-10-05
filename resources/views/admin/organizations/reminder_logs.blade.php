@extends('layouts.admin')

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
    <div class="content-overlay"></div>

    <div class="content-body">
      <section id="multiple-column-form">
        @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible mb-2" role="alert">
          <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="d-flex align-items-center">
            <i class="ti ti-check"></i>
            <span>
              {{ __('admin.'.session()->get('message')) }}
            </span>
          </div>
        </div>
        @endif
        @if(session()->has('message2'))
        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
          <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <div class="d-flex align-items-center">
            <i class="ti ti-error"></i>
            <span>
              {{ __('admin.'.session()->get('message2')) }}
            </span>
          </div>
        </div>
        @endif
      </section>

      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="card-title primary">{{__('admin.ReminderLogs')}}</h5>
              <div class="card-title-elements">
                <div class="dropdown">
                  <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="javascript:void(0)" id="export-excel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" id="export-csv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <hr style="margin:-10px 0 5px 0;">
            <div class="card-body">

              <form id="filter-programs-form" method="GET" action="{{ route('organization.ReminderLogs') }}">
                <div class="row g-3">
                  @if(auth()->user()->hasAnyRole(['Admin']))
                  <div class="col-12 col-md-6 col-lg-4">
                    <select name="organization_id" class="form-select select2" data-placeholder="{{ __('admin.Organization') }}">
                      <option value="">{{ __('admin.Organization') }}</option>
                      @if(auth()->user()->hasAnyRole(['Admin']))
                        <option value="0">{{ __('admin.All') }}</option>
                      @endif
                      @foreach ($organizations as $organization)
                        <option value="{{ $organization->id }}" {{ $organization->id == request('organization_id') ? 'selected' : '' }}>
                          {{ $organization->{'name_' . $l} }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  @endif

                  <div class="col-6 col-md-3 col-lg-2">
                    <input class="form-control datepicker" placeholder="{{ __('admin.StartDate') }}" name="date_from" value="{{ request('date_from') }}" />
                  </div>

                  <div class="col-6 col-md-3 col-lg-2">
                    <input class="form-control datepicker" placeholder="{{ __('admin.EndDate') }}" name="date_to" value="{{ request('date_to') }}" />
                  </div>

                  <div class="col-12 col-md-6 col-lg-2">
                    <select name="channel" class="form-select select2" data-placeholder="{{ $l == 'en' ? 'Select channel' : 'اختر قناة' }}">
                      <option value="">{{ $l == 'en' ? 'All Channels' : 'جميع القنوات' }}</option>
                      <option value="whatsapp" {{ 'whatsapp'==request('channel') ? 'selected' : '' }}>
                        {{ __('admin.Whatsapp') }}
                      </option>
                      <option value="email" {{ 'email'==request('channel') ? 'selected' : '' }}>
                        {{ __('admin.Email') }}
                      </option>
                      <option value="in-app" {{ 'in-app'==request('channel') ? 'selected' : '' }}> 
                        {{ __('admin.in-app') }}
                      </option>
                    </select>
                  </div>

                  <div class="col-12 col-md-6 col-lg-2">
                    <select name="user" class="form-select select2" data-placeholder="{{ $l == 'en' ? 'Select user' : 'اختر مستخدمًا' }}">
                      <option value="">{{ $l == 'en' ? 'All Users' : 'جميع المستخدمين' }}</option>
                      @foreach ($users as $user)
                      <option value="{{ $user->id }}" {{ request('user')==$user->id ? 'selected' : '' }}>
                        {{ $user->{'first_name_' . app()->getLocale()} }} {{ $user->{'last_name_' . app()->getLocale()} }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-12 col-md-6 col-lg-2">
                    <select name="program" class="form-select select2" data-placeholder="{{ $l == 'en' ? 'Select program' : 'اختر برنامجًا' }}">
                      <option value="">{{ $l == 'en' ? 'All Programs' : 'جميع البرامج' }}</option>
                      @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $program->id == request('program') ? 'selected' : '' }}> {{ $program->title }} </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-md-6 col-lg-2">
                    <div class="d-flex flex-wrap flex-md-nowrap gap-2 align-items-center">
                      <button class="btn btn-primary" type="submit">
                        <i class="ti ti-search me-2"></i> {{ __('admin.Search') }}
                      </button>
                      @if(request()->query())
                      <button class="btn btn-label-secondary" type="button" onclick="window.location.href='{{ request()->url() }}'">
                        <i class="ti ti-refresh me-2"></i> {{ __('admin.Reset') }}
                      </button>
                      @endif
                    </div>
                  </div>
                </div>
              </form>

              <div class="table-responsive">
                <table class="table" id="reminder-logs-table">
                  <thead class="table-light">
                    <tr>
                      <th>{{ __('admin.User') }}</th>
                      <th>{{ __('admin.Type') }}</th>
                      <th>{{ __('admin.Program') }}</th>
                      <th>{{ __('admin.Channel') }}</th>
                      <th>{{ __('admin.Status') }}</th>
                      <th>{{ __('admin.Date') }}</th>
                      <th>{{ __('cycles.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($ReminderLogs as $ReminderLog)
                    <tr id="row_{{ $ReminderLog->id }}">
                      <td>
                        <small>
                          {{ $ReminderLog->user->{'first_name_' . app()->getLocale()} }}
                          {{ $ReminderLog->user->{'last_name_' . app()->getLocale()} }}
                        </small>
                      </td>
                      <td>
                        <small>{{ $ReminderLog->type }}</small>
                      </td>
                      <td>
                        <small>{{ $ReminderLog->cycle->title }}</small>
                      </td>
                      <td>
                        <small>{{ $ReminderLog->channel }}</small>
                      </td>
                      <td>
                        <small>{{ __('admin.' . $ReminderLog->status) }}</small>
                      </td>

                      <td>{{ $ReminderLog->created_at }}</td>
                      <td>
                        <button type="submit" class="btn btn-sm btn-label-danger item-delete" data-id="{{ $ReminderLog->id }}">
                          <i class="ti ti-trash"></i>
                        </button>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
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

@section('pagescripts')

<script>
  // Initialize DataTable
  $(document).ready(function() {
    flatpickr(".datepicker", {
      dateFormat: "Y-m-d",
      allowInput: true,
    });

    var table = $('.table').DataTable({
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'csvHtml5',
          text: '<i class="ti ti-file-text"></i> CSV',
          className: 'd-none',
          filename: 'reminder_logs_' + new Date().toISOString().slice(0, 10),
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5] // Exclude the Actions column
          }
        },
        {
          extend: 'excelHtml5',
          text: '<i class="ti ti-file-spreadsheet"></i> Excel',
          className: 'd-none',
          filename: 'reminder_logs_' + new Date().toISOString().slice(0, 10),
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5] // Exclude the Actions column
          }
        }
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

    // Connect custom dropdown buttons to DataTable export buttons
    $('#export-csv').on('click', function(e) {
      e.preventDefault();
      table.button('.buttons-csv').trigger();
    });

    $('#export-excel').on('click', function(e) {
      e.preventDefault();
      table.button('.buttons-excel').trigger();
    });

    // Delete reminder log
    $('#reminder-logs-table tbody').on('click', '.item-delete', function() {
      var logId = $(this).data('id');
      console.log('Deleting log ID:', logId); // Debugging line
      var row = table.row('#row_' + logId);
  
      releaseAlert('warning', {
        title: "{{__('admin.SureDelete')}}",
        message: "{{__('admin.Recover')}}",
        confirmText: "{{ __('admin.DYes') }}",
        cancelText: "{{ __('admin.No') }}",
        confirmColor: 'danger'
      }).then((ok) => {
        if (!ok) return;
        
        // Make AJAX request to delete the reminder log
        $.ajax({
          url: '{{ route("organization.destroyReminderLogs") }}',
          type: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            id: logId
          },
          success: function(response) {
            if (response.success) {
              row.remove().draw(false); // Use false to stay on the same page
              releaseAlert('success', {
                message: "{{__('admin.DeletedSuccessfully')}}",
                confirmText: "{{ __('admin.OK') }}",
                confirmColor: 'primary'
              });
            } else {
              releaseAlert('danger', {
                title: response.message || '{{ __("admin.DeleteFailed") }}',
                confirmText: '{{ __('admin.OK') }}',
                confirmColor: 'primary'
              });
            }
          },
          error: function(xhr, status, error) {
            let errorMessage = '{{ __("admin.DeleteFailed") }}';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
              errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 401) {
              errorMessage = '{{ __("admin.UnauthorizedAccess") }}';
            } else if (xhr.status === 404) {
              errorMessage = '{{ __("admin.NotFound") }}';
            }
            
            releaseAlert('danger', {
              title: errorMessage,
              confirmText: '{{ __('admin.OK') }}',
              confirmColor: 'primary'
            });
          }
        });
      }).catch((error) => {
        releaseAlert('danger', {
          title: error?.message || '{{ __("admin.DeleteFailed") }}',
          confirmText: '{{ __('admin.OK') }}',
          confirmColor: 'primary'
        });
      });
    });
  });
</script>
@stop
