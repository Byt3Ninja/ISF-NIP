@extends('layouts.admin')

@section('title', __('audit.Portal Log'))
@section('subTitle', __('audit.Log viewer') . (count($auditLogs) > 0 ? " (" . $TotalRec . __('audit.record of') . count($auditLogs) . ")" : ""))

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('audit.Portal Log') }}</h5>
      <div class="dropdown">
        <button class="btn btn-label-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
          aria-expanded="false">
          <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i
                class="ti ti-file-type-csv me-2"></i>CSV</a></li>
          <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i
                class="ti ti-file-type-xls me-2"></i>Excel</a></li>
        </ul>
      </div>
      @if($_user->ableTo('manage_log', $permissions) && count($auditLogs) > 0)
        <div class="d-flex flex-wrap align-items-center gap-3 mt-3 mt-md-0">
          <a href="/reports/audit-log" class="btn btn-primary text-capitalize">
            <i class="ti ti-download me-2"></i>
            {{__('audit.Download all records')}}
          </a>
          <a href="#" id="deleteAll" data-url="/dashboard/audit-logs/0" class="btn btn-danger text-capitalize">
            <i class="ti ti-trash me-2"></i>
            {{__('audit.Empty Log table')}}
          </a>
        </div>
      @endif
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="table" id="audit-logs-list-datatable">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>{{ __('audit.description')}}</th>
          <th>{{ __('audit.subject_type')}}</th>
          <th>{{ __('audit.user_id')}}</th>
          <th>{{ __('audit.host')}}</th>
          <th>{{ __('audit.created_at')}}</th>
          <th>{{ __('audit.More')}}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($auditLogs as $key => $auditLog)
          <tr>
            <td>{{ $auditLog->id ?? '' }}</td>
            <td>{{ $auditLog->description ?? '' }}</td>
            <td>{{ $auditLog->instance ?? '' }}</td>
            <td>
              @if($auditLog->user_id > 0 && $auditLog->user)
                @if($_user->ableTo('users.edit', $permissions)) <a target="_blank"
                href="{{ route('users.edit', ['user' => $auditLog->user->id]) }}">@endif
                  {{ $auditLog->user->name ?? '' }}
                  @if($_user->ableTo('users.edit', $permissions))</a>@endif
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>{{ $auditLog->host ?? '' }}</td>
            <td>{{ $auditLog->created_at ?? '' }}</td>
            <td>
              <a class="btn btn-xs btn-primary" href="{{ route('audit-logs.show', $auditLog->id) }}">
                {{ __('audit.View') }}
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

    // Initialize DataTable
    const table = $('#audit-logs-list-datatable').DataTable({
      ...getSharedDataTableConfig({ title: 'Portal Log', descIndex: 0, actionsIndex: 6, withoutFilter: true }),
      buttons: [
        { extend: 'csv', text: 'CSV', className: 'd-none', title: 'Portal_Log_Export_' + new Date().toISOString().slice(0, 10), exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
        { extend: 'excel', text: 'Excel', className: 'd-none', title: 'Portal_Log_Export_' + new Date().toISOString().slice(0, 10), exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
      ],
    });

    $('#exportCsv').on('click', function (e) { e.preventDefault(); table.button('.buttons-csv').trigger(); });
    $('#exportExcel').on('click', function (e) { e.preventDefault(); table.button('.buttons-excel').trigger(); });

    // Handle delete all action
    $(document).on("click", "#deleteAll", function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      releaseAlert('warning', {
        title: '{{__('Are you sure?')}}',
        message: '{{__("You will not be able to recover records again")}}!',
        confirmText: "{{__('Yes, delete it')}}",
        cancelText: "{{__('No')}}!",
        confirmColor: 'danger'
      })
        .then((ok) => {
          if (ok) {
            $.post(url, { '_method': 'DELETE', '_token': token },
              function (response) {
                releaseAlert('success', {
                  title: '{{__('Deleted!')}}',
                  message: '{{__('All log records has been deleted.')}}',
                  confirmText: "{{ __('admin.OK') }}",
                  confirmColor: 'primary'
                });
                location.reload();
              }
            ).fail(function (error) {
              releaseAlert('danger', {
                title: "{{ __('admin.Error') }}!",
                message: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
              });
            });
          }
        });
    });
  });
</script>
@stop
