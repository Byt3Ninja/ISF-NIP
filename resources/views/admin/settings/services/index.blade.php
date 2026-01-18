@extends('layouts.admin')

@section('title', __('cycles.Services'))
@section('subTitle', __('cycles.Service list'))

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
    @php
      $tabs = [
        ['title' => __('admin.All Services'), 'route' => 'services.index', 'icon' => 'arrows-move'],
        ['title' => __('admin.Services Requests'), 'route' => 'service_requests.list_all', 'icon' => 'ti ti-briefcase'],
        ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'],
      ]
    @endphp
    <x-navigation-pills :tabs="$tabs" />
    
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

    <!-- Services List -->
    <div class="card mb-6">
      <div class="card-header d-flex flex-wrap justify-content-between gap-4 border-bottom">
        <div class="card-title mb-0 me-1">
          <h5 class="mb-0">{{ __('admin.Services') }}</h5>
          <p class="mb-0">{{ __('admin.services_subtitle') }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="dropdown">
            <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
            </button>
            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
              <li><a class="dropdown-item" href="{{ route('services.export_excel') }}" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
              <li><a class="dropdown-item" href="{{ route('services.export_csv') }}" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
            </ul>
          </div>
          @if( $_user->ableToAny(['services.create', 'services.store'], $permissions) )
            <a href="{{ route('services.create') }}" class="btn btn-primary text-nowrap">
              <i class="ti ti-plus me-2"></i> {{ __('cycles.Add New') }}
            </a>
          @endif
        </div>
      </div>

      <!-- Filters -->
      @if (!empty($services) && $services->count() > 0)
        <div class="card shadow-none rounded-0 border-bottom">
          <div class="card-body py-4">
            <form method="GET" action="{{ route('services.index') }}" id="filter-form" data-ignore-dirty>
              <div class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-3">
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.Search') }}...">
                  </div>
                </div>
  
                <div class="col-md-6 col-lg-3">
                  <select class="form-select select2" name="org-id" id="organization-filter" data-placeholder="{{ __('admin.All_Organizations') }}">
                    <option value="all">{{ __('admin.All_Organizations') }}</option>
                    @foreach($services->pluck('organizations')->flatten()->unique('id') as $org)
                      <option value="{{ $org->id }}" {{ request('org-id') == $org->id ? 'selected' : '' }}>{{ $org->{'name_' . $l} }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6 col-lg-2">
                  <select class="form-select select2" name="status" data-placeholder="{{ __('admin.All_statuses') }}">
                    <option value="all">{{ __('admin.All_statuses') }}</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('admin.Active') }}</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('admin.Inactive') }}</option>
                  </select>
                </div>

                <div class="col-md-6 col-lg-4">
                  <div class="d-flex justify-content-lg-end gap-2">
                    @if(request('search') || request('status') || request('org-id'))
                      <a href="{{ route('services.index') }}" class="btn bg-card border btn-icon">
                        <i class="ti ti-refresh"></i>
                      </a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-icon" disabled>
                      <i class="ti ti-filter"></i>
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      @endif

      @if (!empty($services) && $services->count() > 0)
        <div class="card-body">
          <div class="row gy-6" id="services-container">
            @foreach ($services as $service)
              <div class="col-sm-6 col-lg-4 service-card" data-service-id="{{ $service->id }}" data-orgs="{{ $service->organizations ? $service->organizations->pluck('id')->join(',') : '' }}">
                <div class="card shadow-none pt-2 px-2 h-100 border">
                  <div class="rounded-2 text-center mb-4 position-relative w-100 h-px-150 flex-shrink-0">
                    @if ($service->img_url)
                      <img src="{{ Storage::disk('public_uploads')->url($service->img_url) }}" alt="{{ $service->name_en }}" class="rounded-2 border w-100 h-100">
                    @else
                      <div class="d-flex align-items-center justify-content-center bg-light h-px-150 rounded-2">
                        <i class="ti ti-photo-off text-muted" style="font-size: 3rem;"></i>
                      </div>
                    @endif
                    <div class="position-absolute top-0 end-0 p-2">
                      <span class="badge bg-{{ $service->status == 1 ? 'success' : 'danger' }} fw-semibold">
                        {{ $service->status == 1 ? __('admin.Active') : __('admin.Inactive') }}
                      </span>
                    </div>
                  </div>
                  <div class="card-body p-4 pt-2">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                      @if ($service->vendor_name)
                        <div class="d-flex align-items-center gap-1">
                          <div>{{ __('admin.provided_by') }}:</div>
                          <div class="badge bg-label-primary">{{ $service->vendor_name }}</div>
                        </div>
                      @else
                        <div></div>
                      @endif
                    <p class="d-flex align-items-center justify-content-center fw-medium gap-1 mb-0">
                        {{ number_format($service->rating ?? 0.0, 1) }} 
                        <span class="text-warning">
                          <i class="ti ti-star-filled ti-lg me-1"></i>
                        </span>
                        <span class="fw-normal">({{ $service->review_count ?? '0' }})</span>
                      </p>
                    </div>
                    <h5 class="card-title mb-0">{{ $service->name }}</h5>
                    <p class="mt-1">{{ Str::limit($service->description ?? 'No description available', 100) }}</p>
                    <div class="d-flex align-items-center gap-2 mb-4">
                      <!-- Organizations Badges -->
                      @if($service->organizations && $service->organizations->count())
                        @foreach($service->organizations->take(3) as $organization)
                          <div class="badge bg-label-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $organization->{'name_' . $l } }}">
                            {{ Str::limit($organization->{'name_' . $l}, 15) }}
                          </div>
                        @endforeach
                        @if($service->organizations->count() > 3)
                          <div class="badge bg-label-secondary orgs-modal-trigger" style="cursor:pointer;" data-orgs='@json($service->organizations->map(function($o) use($l) { return ["id"=>$o->id, "name"=>$o->{"name_".$l}]; }))'>+{{ $service->organizations->count() - 3 }}</div>
                        @endif
                      @else
                        <div class="badge bg-label-secondary">{{ __('admin.No Organizations') }}</div>
                      @endif
                    </div>

                    <!-- Service Price -->
                    <div class="d-flex align-items-center gap-2">
                      <div>{{ __('admin.Price') }}:</div>
                      @if($service->price)
                        <span class="fw-medium">{{ $service->price }} {{ $service->currency }}</span>
                      @else
                        <span class="fw-medium text-muted">{{ __('admin.free') }}</span>
                      @endif
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-2">
                        <span>
                          <i class="ti ti-calendar ti-sm me-1"></i>
                          {{ __('admin.Created At') }}
                        </span>
                        <span class="fw-medium text-muted">{{ $service->created_at->format('Y-m-d') }}</span>
                      </div>

                      @if($_user->ableToAny(['services.show', 'services.edit', 'services.update', 'services.destroy'], $permissions))
                      <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill btn-icon btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                          <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          @if($_user->ableToAny(['services.show', 'services.edit', 'services.update'], $permissions))
                          <li>
                            <a class="dropdown-item" href="{{ route('services.edit', ['service' => $service->id]) }}">
                              <i class="ti ti-edit me-2"></i> {{ __('admin.Edit') }}
                            </a>
                          </li>
                          @endif
                          @if($_user->ableTo('services.destroy', $permissions))
                          <li>
                            <button
                              class="dropdown-item text-danger d-flex align-items-center delete-service-action"
                              data-id="{{ $service->id }}"
                              data-url="{{ route('services.destroy', ['service' => $service->id]) }}">
                              <i class="ti ti-trash me-2"></i> {{ __('admin.Delete') }}
                            </button>
                          </li>
                          @endif
                        </ul>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endforeach

            <div class="col-12 mt-12">
              {{ $services->links('pagination::bootstrap-5') }}
            </div>
          </div>
        </div>
      @else
        @include(
          'includes.no-result',
          ['message' => request('search') || request('status') || request('org-id') ? __('admin.noResultsMatch') :__('admin.No_Data_Found')]
        )
      @endif
    </div>
  </div>
</div>

<!-- Modal for all organizations -->
<div class="modal fade" id="orgsModal" tabindex="-1" aria-labelledby="orgsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orgsModalLabel">{{ __('admin.Organizations') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="orgsModalBody">
        <!-- Organizations will be loaded here -->
      </div>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  $(function() {
    // Toggle submit button disable state on form change
    $('#filter-form select, #filter-form input').on('change input', function () {
      $('#filter-form button[type="submit"]').prop('disabled', $(this).val() ? false : true);
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
              releaseSuccessAlert({
                title: "{{ __('admin.Deleted') }}!",
                message: "{{ __('admin.DeletedSuccessfully') }}.",
              });
              // Remove the organization card from DOM
              $('div[data-service-id="' + id + '"]').fadeOut(300, function() {
                $(this).remove();
              });
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

    // Handle organization modal trigger
    $(document).on('click', '.orgs-modal-trigger', function() {
      var orgs = $(this).data('orgs');
      var html = '<div class="d-flex flex-wrap gap-2">';
      orgs.forEach(function(org) {
        html += '<div class="badge bg-label-primary p-2">'
          + '<a href="/dashboard/organizations/' + org.id + '/edit" target="_blank" class="text-decoration-none text-primary">' + org.name + '</a>'
          + '</div>';
      });
      html += '</div>';
      $('#orgsModalBody').html(html);
      var modal = new bootstrap.Modal(document.getElementById('orgsModal'));
      modal.show();
    });
  });
</script>
@stop
