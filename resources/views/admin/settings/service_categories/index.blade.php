@extends('layouts.admin')

@section('title', __('admin.Services Categories'))
@section('subTitle', __('admin.Service`s Categories List'))

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
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

    <div class="content-body">
      <section id="basic-datatable">
        <?php $index = 1; ?>
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
                <h5 class="card-title mb-3 mb-md-0">{{ __('admin.Services Categories') }}</h5>
                <div class="d-flex align-items-center gap-3">
                  <div class="dropdown">
                    <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                      <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                      <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
                    </ul>
                  </div>
                  @if( $_user->ableToAny(['serviceCategories.create', 'serviceCategories.store'], $permissions) )
                  <div class="invoice-create-btn">
                    <a href="{{ route('serviceCategories.create') }}" class="btn btn-primary glow">
                      <i class="ti ti-plus me-2"></i> {{ __('admin.Add_New') }}
                    </a>
                  </div>
                  @endif
                </div>
              </div>
              <div class="card-content">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table zero-configuration" id="services-categories-table"
                      style="font-size: 0.92rem;">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th class="text-center">{{__('admin.Organization')}}</th>
                          <th class="text-center">{{ $l == 'en' ? 'Service Name (EN)' : 'اسم الخدمة بالانجليزية' }}</th>
                          <th class="text-center">{{ $l == 'en' ? 'Service Name (AR)' : 'اسم الخدمة بالعربية' }}</th>
                          <th class="text-center">{{ __('admin.Created At') }}</th>
                          @if( $_user->ableToAny(['serviceCategories.show', 'serviceCategories.edit',
                          'serviceCategories.update', 'serviceCategories.destroy'], $permissions) )<th
                            class="text-center">{{ __('admin.Actions') }}</th>@endif
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($serviceCategories as $category)
                        <tr>
                          <td style="font-size: 0.92rem;">{{ $index++ }}</td>
                          <td class="text-center" style="font-size: 0.92rem;">
                            <a href="{{ URL::to('dashboard/organizations/' . $category->organization_id . '/edit') }}">
                              {{ $category->organization ? $category->organization->{'name_'.$l} : '-' }}
                            </a>
                          </td>
                          <td class="text-center" style="font-size: 0.92rem;">{{ $category->name_en }}</td>
                          <td class="text-center" style="font-size: 0.92rem;">{{ $category->name_ar }}</td>
                          <td class="text-center" style="font-size: 0.92rem;">{{ $category->created_at }}</td>
                          @if( $_user->ableToAny(['serviceCategories.show', 'serviceCategories.edit',
                          'serviceCategories.update', 'serviceCategories.destroy'], $permissions) )
                          <td class="text-center" style="font-size: 0.92rem;">
                            <div class="dropdown">
                              <button class="btn btn-label-secondary p-1 px-2" type="button"
                                id="actionsDropdown{{ $category->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                              </button>
                              <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $category->id }}">
                                @if( $_user->ableToAny(['serviceCategories.edit', 'serviceCategories.update'],
                                $permissions) )
                                <li>
                                  <a class="dropdown-item d-flex align-items-center"
                                    href="{{ route('serviceCategories.edit', ['serviceCategory' => $category->id]) }}">
                                    <i class="ti ti-pencil me-1"></i> {{ __('admin.Edit') }}
                                  </a>
                                </li>
                                @endif
                                @if( $_user->ableTo('serviceCategories.destroy', $permissions) )
                                <li>
                                  <button
                                    class="dropdown-item text-danger d-flex align-items-center delete-service-action"
                                    data-id="{{ $category->id }}"
                                    data-url="{{ route('serviceCategories.destroy', ['serviceCategory' => $category->id]) }}">
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
      const isArabic = "{{ $l }}" === "ar";
      // Initialize DataTable
      const table = $('#services-categories-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
        language: isArabic ? { url : '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' } : {},
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[4, 'desc']],
        columnDefs: [
          {
            targets: [5], // Actions column
            orderable: false,
            searchable: false
          }
        ],
        buttons: [
          {
            extend: 'excel',
            title: isArabic ? 'فئات الخدمات' : 'Service Categories',
            exportOptions: { columns: [0, 1, 2, 3, 4] }
          },
          {
            extend: 'csv',
            title: isArabic ? 'فئات الخدمات' : 'Service Categories',
            exportOptions: { columns: [0, 1, 2, 3, 4] }
          },
        ],
      });

      // Export button handlers
      $('#exportExcel').on('click', () => table.button('.buttons-excel').trigger());
      $('#exportCsv').on('click', () => table.button('.buttons-csv').trigger());

      // Handle delete action
      $(document).on("click", ".delete-service-action", function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var id = $(this).data('id');
        var token = '{{ csrf_token() }}';
        
        Swal.fire({
          title: "{{__('admin.SureDelete')}}",
          text: "{{__('admin.Recover')}}",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}",
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-label-secondary',
          },
          buttonsStyling: false,
        })
        .then((result) => {
          if (result.value) {
            $.post(url, {'_method': 'DELETE' , '_token': token},
              function(response) {
                releaseSuccessAlert({
                  title: "{{ __('admin.Deleted') }}!",
                  html: "{{ __('admin.DeletedSuccessfully') }}.",
                });
                // Remove the row from DataTable if still on same page
                $('#services-categories-table').DataTable().row($(e.target).closest('tr')).remove().draw();
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