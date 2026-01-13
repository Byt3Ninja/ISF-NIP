@extends('layouts.admin')

@section('title', __('audit.Portal Log') )
@section('subTitle', __('audit.Log viewer'). (count($auditLogs)>0 ? " (".$TotalRec.__('audit.record of').count($auditLogs).")":"") )

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
    <div class="content-body">
      <section id="basic-datatable">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                  <h5 class="card-title mb-0">{{ __('audit.Portal Log') }}</h5>
                  @if( $_user->ableTo('manage_log', $permissions) && count($auditLogs)>0 )
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
              <div class="card-content">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table zero-configuration" id="audit-logs-list-datatable" style="font-size: 0.92rem;">
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
                            @if( $auditLog->user_id > 0 && $auditLog->user )
                              @if( $_user->ableTo('users.edit', $permissions) ) <a target="_blank" href="{{ route('users.edit', ['user' => $auditLog->user->id]) }}">@endif
                              {{ $auditLog->user->name ?? '' }}
                              @if( $_user->ableTo('users.edit', $permissions) )</a>@endif
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
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
      const isRtl = {{ $l == 'ar' ? 'true' : 'false' }};

      // Initialize DataTable
      $('#audit-logs-list-datatable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
        language: isRtl ? { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' } : {},
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[0, 'desc']],
        columnDefs: [
          {
            targets: [6], // Actions column
            orderable: false,
            searchable: false
          }
        ]
      });

      // Handle delete all action
      $(document).on("click", "#deleteAll", function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var token = '{{ csrf_token() }}';
        Swal.fire({
          title: '{{__('Are you sure?')}}',
          text: '{{__("You will not be able to recover records again")}}!',
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "{{__('Yes, delete it')}}",
          cancelButtonText: "{{__('No')}}!",
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-label-secondary',
          },
          buttonsStyling: false,
        })
        .then((result) => {
          if (result.value) {
            $.post(url, {'_method':'DELETE', '_token': token},
              function(response){
                Swal.fire({
                  icon: 'success',
                  title: '{{__('Deleted!')}}',
                  html: '{{__('All log records has been deleted.')}}',
                  showCancelButton: false,
                  showConfirmButton: true,
                  confirmButtonText: "{{ __('admin.OK') }}",
                  customClass: {
                      confirmButton: 'btn btn-primary',
                  },
                  buttonsStyling: false,
                });
                location.reload();
              }
            ).fail(function(error) {
              releaseFailureAlert({
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
