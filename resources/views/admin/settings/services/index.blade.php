@extends('layouts.admin')

@section('title', __('cycles.Services'))
@section('subTitle', __('cycles.Service list'))

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

    <!-- Navigation Tabs -->
    <nav class="nav-align-top mb-6">
      @php
        $tabs = [
          ['title' => __('admin.All Services'), 'url' => 'dashboard/services', 'icon' => 'arrows-move'],
          ['title' => __('admin.Services Requests'), 'url' => 'dashboard/requests/all', 'icon' => 'ti ti-briefcase'],
          ['title' => __('admin.Services Categories'), 'url' => 'dashboard/serviceCategories', 'icon' => 'layout-grid'],
        ]
      @endphp
      <ul class="nav nav-pills">
        @foreach($tabs as $tab)
          <li class="nav-item">
            <a href="/{{ $tab['url'] }}" class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}">
              <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
              {{ $tab['title'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>
    <!--/ Navigation Tabs -->
  
    <div class="content-body">
      <section id="basic-datatable">
        <?php $index = 1; ?>
        
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex justify-content-between flex-wrap align-items-center border-bottom">
                <h5 class="card-title mb-3 mb-md-0">{{ __('cycles.Services') }}</h5>
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
                  @if( $_user->ableToAny(['services.create', 'services.store'], $permissions) )
                  <div class="invoice-create-btn">
                    <a href="{{ route('services.create') }}" class="btn btn-primary glow">
                      <i class="ti ti-plus me-2"></i> {{ __('cycles.Add New') }}
                    </a>
                  </div>
                  @endif
                </div>
              </div>
              <div class="card-content">
                <div class="card-datatable table-responsive">
                  <table class="table zero-configuration" id="services-table" style="font-size: 0.92rem;">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th class="text-center">{{__('admin.Organization')}}</th>
                        <th class="text-center">{{ __('admin.Service_Name') }}</th>
                        <th class="text-center">{{ __('cycles.Price') }}</th>
                        <th class="text-center">{{ __('admin.Vendor Info') }}</th>
                        <th class="text-center">{{ __('admin.Image') }}</th>
                        <th class="text-center">{{ __('cycles.Created At') }}</th>
                        @if( $_user->ableToAny(['services.show', 'services.edit', 'services.update',
                        'services.destroy'], $permissions) )<th class="text-center">{{ __('admin.Actions') }}</th>
                        @endif
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($services as $service)
                      <tr>
                        <td>{{ $index++ }}</td>
                        <td class="text-center" style="min-width: 200px; max-width: 300px; font-size: 0.92rem;">
                          @if($service->organizations && $service->organizations->count())
                          <div class="d-flex flex-wrap justify-content-center gap-1 w-100">
                            @php $orgs = $service->organizations; $orgCount = $orgs->count(); @endphp
                            @foreach($orgs->take(3) as $organization)
                            <span class="badge bg-label-primary rounded-pill"
                              title="{{ $organization->{'name_'.$l} }}">
                              <a href="{{ URL::to('dashboard/organizations/' . $organization->id . '/edit') }}"
                                class="text-decoration-none">
                                {{ Str::limit($organization->{'name_'.$l}, 15) }}
                              </a>
                            </span>
                            @endforeach
                            @if($orgCount > 3)
                            <span class="badge bg-label-secondary rounded-pill orgs-modal-trigger"
                              data-orgs='@json($orgs->map(function($o) use($l) { return ["id"=>$o->id, "name"=>$o->{"name_".$l}]; }))'
                              style="cursor:pointer;">
                              +{{ $orgCount - 3 }}
                            </span>
                            @endif
                          </div>
                          @else
                          <span class="text-muted">{{ __('admin.No Organizations') }}</span>
                          @endif
                        </td>
                        <td class="text-center" style="font-size: 0.92rem;">{{ $service->name_en }}</td>
                        <td class="text-center" style="font-size: 0.92rem;">{{ $service->price > 0 ? $service->price . $Setting->Currency : 'Free' }}</td>
                        <td class="text-center" style="font-size: 0.92rem;">
                          @if($service->vendor_name || $service->vendor_email || $service->vendor_phone)
                          <div class="vendor-info d-flex flex-column align-items-start">
                            @if($service->vendor_name)
                            <div class="d-flex align-items-center mb-1 vendor-name">
                              <i class="ti ti-user me-1"></i>
                              <span class="fw-medium text-xs">{{ $service->vendor_name }}</span>
                            </div>
                            @endif
                            @if($service->vendor_email)
                            <div class="vendor-email d-flex align-items-center mb-1">
                              <i class="ti ti-mail me-1"></i>
                              <a href="mailto:{{ $service->vendor_email }}" class="text-decoration-none text-xs">
                                {{ $service->vendor_email }}
                              </a>
                            </div>
                            @endif
                            @if($service->vendor_phone)
                            <div class="vendor-phone d-flex align-items-center">
                              <i class="ti ti-phone me-1"></i>
                              <a href="tel:{{ $service->vendor_phone }}" class="text-decoration-none text-xs">
                                {{ $service->vendor_phone }}
                              </a>
                            </div>
                            @endif
                          </div>
                          @else
                          <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td class="text-center">
                          @if ($service->img_url)
                          <img class="rounded" src="{{ Storage::disk('public_uploads')->url($service->img_url) }}" alt="img"
                            style="width: 50px; height: 50px;">
                          @else
                          -
                          @endif
                        </td>
                        <td class="text-center" style="font-size: 0.92rem;">{{ $service->created_at }}</td>
                        @if($_user->ableToAny(['services.show', 'services.edit', 'services.update',
                        'services.destroy'], $permissions))
                        <td class="text-center" style="font-size: 0.92rem;">
                          <div class="dropdown">
                            <button class="btn btn-label-secondary p-1 px-2" type="button"
                              id="actionsDropdown{{ $service->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="actionsDropdown{{ $service->id }}">
                              @if($_user->ableToAny(['services.show', 'services.edit', 'services.update'], $permissions))
                              <li>
                                <a class="dropdown-item d-flex align-items-center" href="{{ route('services.edit', ['service' => $service->id]) }}">
                                  <i class="ti ti-pencil me-1"></i> {{ __('admin.Edit') }}
                                </a>
                              </li>
                              @endif
                              @if($_user->ableTo('services.destroy', $permissions))
                              <li>
                                <button
                                  class="dropdown-item text-danger d-flex align-items-center delete-service-action"
                                  data-id="{{ $service->id }}"
                                  data-url="{{ route('services.destroy', ['service' => $service->id]) }}">
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
      </section>
    </div>
  </div>
</div>

<!-- Modal for all organizations -->
<div class="modal fade" id="orgsModal" tabindex="-1" aria-labelledby="orgsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orgsModalLabel">{{ __('admin.Organization') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="orgsModalBody">
        <!-- Organizations will be loaded here -->
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
      const isArabic = "{{ $l }}" === 'ar';
      // Initialize DataTable
      const table = $('#services-table').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('admin.All') }}"]],
        language: isArabic ? { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json' } : {},
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        order: [[6, 'desc']],
        columnDefs: [{ orderable: false, targets: [0, 7] }],
        buttons: [
          {
            extend: 'excel',
            title: isArabic ? 'الخدمات' : 'Services',
            exportOptions: { columns: [0, 1, 2, 3, 4, 6] }
          },
          {
            extend: 'csv',
            title: isArabic ? 'الخدمات' : 'Services',
            exportOptions: { columns: [0, 1, 2, 3, 4, 6] }
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
                  message: "{{ __('admin.DeletedSuccessfully') }}.",
                });
                // Remove the row from DataTable if still on same page
                $('#services-table').DataTable().row($(e.target).closest('tr')).remove().draw();
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
<script>
  $(document).on('click', '.orgs-modal-trigger', function() {
    var orgs = $(this).data('orgs');
    var html = '<ul class="list-group">';
    orgs.forEach(function(org) {
      html += '<li class="list-group-item">'
        + '<a href="/dashboard/organizations/' + org.id + '/edit" target="_blank">' + org.name + '</a>'
        + '</li>';
    });
    html += '</ul>';
    $('#orgsModalBody').html(html);
    var modal = new bootstrap.Modal(document.getElementById('orgsModal'));
    modal.show();
  });
</script>
@stop
