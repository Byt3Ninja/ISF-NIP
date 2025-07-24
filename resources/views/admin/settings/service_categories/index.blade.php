@extends('layouts.admin')

@section('title', __('admin.Services Categories'))
@section('subTitle', __('admin.Service`s Categories List'))

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-3 mb-6">
      <h3 class="mb-0 fw-bold">{{ __('admin.Services Categories') }}</h3>
      @if( $_user->ableToAny(['serviceCategories.create', 'serviceCategories.store'], $permissions) )
      <div class="invoice-create-btn">
        <a href="{{ route('serviceCategories.create') }}" class="btn btn-primary glow">
          <i class="ti ti-plus me-2"></i> {{ __('admin.Add New') }}
        </a>
      </div>
      @endif
    </div>
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
              <div class="card-content">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table zero-configuration" id="organizations-list-datatable"
                      style="font-size: 0.92rem;">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th class="text-center">{{__('admin.Organization')}}</th>
                          <th class="text-center">{{ __('admin.English Name') }}</th>
                          <th class="text-center">{{ __('admin.Arabic Name') }}</th>
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
                                    <i class="ti ti-pencil me-1"></i> {{ __('Edit') }}
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
      // Initialize DataTable
      $('#organizations-list-datatable').DataTable({
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
            targets: [5], // Actions column
            orderable: false,
            searchable: false
          }
        ]
      });

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
                Swal.fire({
                  icon: 'success',
                  title: "{{ __('admin.Deleted') }}!",
                  html: "{{ __('admin.DeletedSuccessfully') }}.",
                  showCancelButton: false,
                  showConfirmButton: true,
                  confirmButtonText: "{{ __('admin.OK') }}",
                  customClass: {
                      confirmButton: 'btn btn-primary',
                  },
                  buttonsStyling: false,
                });
                // Remove the row from DataTable if still on same page
                $('#organizations-list-datatable').DataTable().row($(e.target).closest('tr')).remove().draw();
              }
            ).fail(function(error) { 
              console.log(error);
              Swal.fire({
                icon: 'error',
                title: "{{ __('admin.Error') }}!",
                text: "{{ __('admin.SomethingWentWrong') }}",
              });
            });
          }
        });
      });
    });
</script>
@stop