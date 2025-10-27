@extends('layouts.admin')

@section('title', __('audit.Portal Log') )
@section('subTitle', __('audit.Log viewer'). (count($auditLogs)>0 ? " (".$TotalRec.__('audit.record of').count($auditLogs).")":"") )

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-3 mb-6">
      <h3 class="mb-0 fw-bold">{{ __('audit.Portal Log') }}</h3>
      @if( $_user->ableTo('manage_log', $permissions) && count($auditLogs)>0 )
      <div class="invoice-create-btn d-flex gap-2">
        <a href="/reports/audit-log" class="btn btn-primary glow">{{__('audit.Download all records')}}</a>
        <a href="#" id="deleteAll" data-url="/dashboard/audit-logs/0" class="btn btn-danger glow">{{__('audit.Empty Log table')}}</a>
      </div>
      @endif
    </div>
    <div class="content-body">
      <section id="basic-datatable">
        <div class="row">
          <div class="col-12">
            <div class="card">
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
      $('#audit-logs-list-datatable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
        language: {
          @if($l == 'ar')
          "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
          @else
          "search": "{{ __('admin.Search') }}:",
          "lengthMenu": "{{ __('admin.Show') }} _MENU_ {{ __('admin.Entries') }}",
          "info": "{{ __('admin.showing') }} _START_ {{ __('admin.To') }} _END_ {{ __('admin.Of') }} _TOTAL_ {{ __('admin.Entries') }}",
          "infoEmpty": "{{ __('admin.no_entries_available') }}",
          "infoFiltered": "({{ __('admin.filtered from') }} _MAX_ {{ __('admin.total entries') }})",
          "paginate": {
            "first": "{{ __('admin.First') }}",
            "last": "{{ __('admin.Last') }}",
            "next": "{{ __('admin.Next') }}",
            "previous": "{{ __('admin.Previous') }}"
          },
          "emptyTable": "{{ __('admin.No_data_available_in_table') }}",
          "zeroRecords": "{{ __('admin.No_matching_records_found') }}"
          @endif
        },
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
                text: error?.message || "{{ __('admin.An unexpected error occurred, please try again later') }}",
              });
            });
          }
        });
      });
    });
</script>
@stop
