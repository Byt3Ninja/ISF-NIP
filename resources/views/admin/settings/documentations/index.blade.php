@extends('layouts.admin')

@section('title', __('admin.Documentations'))
@section('subTitle', __('admin.documentation`s List'))

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      @if (session()->has('msg'))
      <div class="row mb-6">
        <div class="col-md-12">
          <div class="alert alert-success alert-dismissible mb-2" role="alert">
            {{ __('admin.' . session()->get('msg')) }}
            {{ Session::forget('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      </div>
      @endif
      <div class="card">
        <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
          <h5 class="card-title mb-0">{{ __('admin.Documentations') }}</h5>
          <div class="d-flex gap-2">
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-type-csv me-2"></i>CSV</a></li>
                <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-type-xls me-2"></i>Excel</a></li>
              </ul>
            </div>
            @if($_user->ableToAny(['documentation.create', 'documentation.store'], $permissions))
            <a href="{{ route('documentation.create') }}" class="btn btn-primary glow">
              <i class="ti ti-plus me-2"></i> {{ __('admin.Add New') }}
            </a>
            @endif
          </div>
        </div>
        <div class="card-datatable table-responsive">
          <table class="table datatable-documentations">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>{{ __('admin.Arabic Title') }}</th>
                <th>{{ __('admin.English Title') }}</th>
                <th>{{ __('admin.Rolefor') }}</th>
                @if($_user->ableToAny(['documentation.show', 'documentation.edit', 'documentation.update', 'documentation.destroy'], $permissions))
                  <th class="text-center">{{ __('admin.Actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @foreach ($docs as $doc)
                <tr>
                  <td>{{ $doc->arr }}</td>
                  <td>{{ $doc->menu_ar }}</td>
                  <td>{{ $doc->menu_en }}</td>
                  <td>{{ __('admin.role' . ucFirst($doc->role)) }}</td>
                  @if($_user->ableToAny(['documentation.show', 'documentation.edit', 'documentation.update', 'documentation.destroy'], $permissions))
                  <td class="text-center">
                    <div class="dropdown">
                      <button class="btn btn-label-secondary p-1 px-2" type="button"
                        id="actionsDropdown{{ $doc->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $doc->id }}">
                        @if($_user->ableTo('documentation.show', $permissions))
                        <li>
                          <a class="dropdown-item d-flex align-items-center" title="View" href="/dashboard/documentation/{{ $doc->id }}">
                            <i class="ti ti-eye me-1"></i> {{ __('View') }}
                          </a>
                        </li>
                        @endif
                        @if($_user->ableToAny(['documentation.edit', 'documentation.update'], $permissions))
                        <li>
                          <a class="dropdown-item d-flex align-items-center" title="Edit" href="{{ route('documentation.edit', ['documentation' => $doc->id]) }}">
                            <i class="ti ti-pencil me-1"></i> {{ __('admin.Edit') }}
                          </a>
                        </li>
                        @endif
                        @if($_user->ableTo('documentation.destroy', $permissions))
                        <li>
                          <button class="dropdown-item text-danger d-flex align-items-center delete-doc-action"
                            data-id="{{ $doc->id }}"
                            data-url="{{ route('documentation.destroy', ['documentation' => $doc->id]) }}">
                            <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                          </button>
                        </li>
                        @endif
                      </ul>
                    </div>
                  </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
      // Initialize DataTable
      const hasDocActions = {{ $_user->ableToAny(['documentation.show', 'documentation.edit', 'documentation.update', 'documentation.destroy'], $permissions) ? 'true' : 'false' }};
      const table = $('.datatable-documentations').DataTable({
        ...getSharedDataTableConfig({ actionsIndex: hasDocActions ? 4 : null, withoutFilter: true }),
        buttons: [
          { extend: 'csv', text: 'CSV', className: 'd-none', title: 'Documentations_Export_' + new Date().toISOString().slice(0, 10), exportOptions: { columns: [0, 1, 2, 3] } },
          { extend: 'excel', text: 'Excel', className: 'd-none', title: 'Documentations_Export_' + new Date().toISOString().slice(0, 10), exportOptions: { columns: [0, 1, 2, 3] } },
        ],
      });

      $('#exportCsv').on('click', function(e) { e.preventDefault(); table.button('.buttons-csv').trigger(); });
      $('#exportExcel').on('click', function(e) { e.preventDefault(); table.button('.buttons-excel').trigger(); });
  
      // Handle delete action
      $(document).on("click", ".delete-doc-action", function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var id = $(this).data('id');
        var token = '{{ csrf_token() }}';
        
        releaseAlert('warning', {
          title: "{{__('admin.SureDelete')}}",
          message: "{{__('admin.Recover')}}",
          confirmText: "{{ __('admin.DYes') }}",
          cancelText: "{{ __('admin.No') }}",
          confirmColor: 'danger'
        })
        .then((ok) => {
          if (ok) {
            $.post(url, {'_method': 'DELETE' , '_token': token},
              function(response) {
                releaseAlert('success', {
                  title: "{{ __('admin.Deleted') }}!",
                  message: "{{ __('admin.FileDeleted') }}.",
                  confirmText: "{{ __('admin.OK') }}",
                  confirmColor: 'primary'
                });
                // Remove the row from DataTable if still on same page
                $('#organizations-list-datatable').DataTable().row($(e.target).closest('tr')).remove().draw();
              }
            ).fail(function(error) { 
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
