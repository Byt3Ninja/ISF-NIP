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
        // ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'],
      ];
      if( $_user->ableTo('serviceCategories.index', $permissions) )
      {
        $tabs[] = ['title' => __('admin.Services Categories'), 'route' => 'serviceCategories.index', 'icon' => 'layout-grid'];
      }
    @endphp
    @if( !$_user->hasRole('registered') && !$_user->hasRole('serviceProvider') )
      <x-navigation-pills :tabs="$tabs" />
    @endif
    
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
          @if ($_user->ableToAny(['services.create', 'services.store', 'services.edit', 'services.update'], $permissions))
            <p class="mb-0">{{ __('admin.services_subtitle') }}</p>
          @endif
        </div>
        <div class="d-flex align-items-center gap-3">
          @if ($_user->ableToAny(['services.export_excel', 'services.export_csv'], $permissions))
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
              </button>
              <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                <li><a class="dropdown-item" href="{{ route('services.export_excel', request()->query()) }}" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                <li><a class="dropdown-item" href="{{ route('services.export_csv', request()->query()) }}" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
              </ul>
            </div>
          @endif
          @if( $_user->ableToAny(['services.create', 'services.store'], $permissions) )
            <a href="{{ route('services.create') }}" class="btn btn-primary text-nowrap">
              <i class="ti ti-plus me-2"></i> {{ __('cycles.Add New') }}
            </a>
          @endif
        </div>
      </div>

      <!-- Filters -->
      <div class="card shadow-none rounded-0 border-bottom">
        <div class="card-body py-4">
          <form method="GET" action="{{ route('services.index') }}" id="filter-form" data-ignore-dirty>
            <div class="row g-3 align-items-end">
              <div class="col-md-6 col-lg-3">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                  <input
                    type="search"
                    id="debounced-search"
                    class="form-control"
                    placeholder="{{ __('admin.Search') }}..."
                    name="search"
                    aria-label="Search services"
                    aria-describedby="search-services"
                    value="{{ request('search') ? request('search') : '' }}"
                  />
                </div>
              </div>

              @if (!$_user->hasRole('registered'))
              <div class="col-md-6 col-lg-2">
                <select class="form-select select2" name="org-id" id="organization-filter" data-placeholder="{{ __('admin.All_Organizations') }}">
                  <option value="all">{{ __('admin.All_Organizations') }}</option>
                  @foreach($organizations as $org)
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
              @endif

              @if ($_user->hasRole('registered'))
                <div class="col-md-6 col-lg-3">
                  <select class="form-select select2" name="rating" data-placeholder="{{ __('admin.all_ratings') }}">
                    <option value="all">{{ __('admin.all_ratings') }}</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>★★★★★ (5 {{ __('admin.stars') }})</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>★★★★☆ (4 {{ __('admin.stars') }})</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>★★★☆☆ (3 {{ __('admin.stars') }})</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>★★☆☆☆ (2 {{ __('admin.stars') }})</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>★☆☆☆☆ (1 {{ __('admin.star') }})</option>
                  </select>
                </div>
              @endif

              <div class="col-md-6 col-lg-2">
                <div class="d-flex justify-content-lg-end gap-2">
                  @if(request('search') || request('rating') || request('status') || request('org-id'))
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

      @if (!empty($services) && $services->count() > 0)
      <div class="card-body">
        <div class="row gy-6" id="services-container">
          @foreach ($services as $service)
            <div class="col-sm-6 col-lg-4 service-card" data-service-id="{{ $service->id }}" data-orgs="{{ $service->organizations ? $service->organizations->pluck('id')->join(',') : '' }}">
              <div class="card h-100 border overflow-hidden">
                <div class="bg-light text-center position-relative w-100 h-px-200 flex-shrink-0">
                  @if (isset($service->img_url))
                    <img 
                      src="{{ Storage::disk('public_uploads')->url($service->img_url) }}" 
                      alt="{{ $service->name_en }}" 
                      class="w-100 h-100"
                      onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none')"
                    />
                  @endif

                  <!-- Placeholder -->
                  <div class="d-flex align-items-center justify-content-center h-100 w-100 {{ isset($service->img_url) ? 'd-none' : '' }}">
                    <i class="ti ti-photo-off text-muted" style="font-size: 3rem;"></i>
                  </div>
             
                  <div class="d-flex align-items-center gap-2 position-absolute top-0 end-0 p-4">
                    @if ($_user->ableToAny(['services.edit', 'services.update'], $permissions))
                      <a
                        href="{{ route('services.edit', ['service' => $service->id]) }}"
                        class="btn btn-sm btn-icon btn-label-secondary rounded-pill border shadow-xs"
                        title="{{ __('admin.Edit') }}"
                      >
                        <i class="ti ti-edit text-dark"></i>
                      </a>
                    @endif
  
                    @if($_user->ableTo('services.destroy', $permissions))
                      <button
                        class="btn btn-sm btn-icon btn-label-secondary rounded-pill border shadow-xs delete-service-action"
                        title="{{ __('admin.Delete') }}"
                        data-id="{{ $service->id }}"
                        data-url="{{ route('services.destroy', ['service' => $service->id]) }}"
                      >
                        <i class="ti ti-trash text-dark"></i>
                      </button>
                    @endif
                  </div>
                </div>


                <div class="card-body d-flex flex-column">
                  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">{{ $service->name }}</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                      @if( !( $service->hide_vendor_info == 1 && !$_user->hasAnyRole(['admin', 'organizationManager', 'subadmin', 'serviceProvider']) ) )
                      <a
                        href="{{ isset($service->service_provider_id) ? '/dashboard/service-provider/' . $service->provider->id . '/profile?tab=reviews&service_id=' . $service->id : 'javascript:void(0)' }}"
                        target="_blank"
                      >
                      @endif
                      <div class="badge bg-light-subtle text-dark py-1 px-2">
                        <span class="text-warning">
                          <i class="ti ti-star-filled ti-16px me-1"></i>
                        </span>
                        {{ number_format($service->rating ?? 0.0, 1) }}
                      </div>
                      <span class="fw-normal">({{ $service->review_count ?? '0' }})</span>
                      @if( !( $service->hide_vendor_info == 1 && !$_user->hasAnyRole(['admin', 'organizationManager', 'subadmin', 'serviceProvider']) ) )
                      </a>
                      @endif
                    </div>
                  </div>
                  @if ($service->vendor_name)
                    @if( ( $service->hide_vendor_info == 1 && !$_user->hasAnyRole(['admin', 'organizationManager', 'subadmin']) ) || $service->service_provider_id == $_user->service_provider?->id)
                      <div></div>
                    @else
                      <a
                        href="{{ isset($service->service_provider_id) ? '/dashboard/service-provider/' . $service->provider->id . '/profile' : 'javascript:void(0)' }}"
                        class="d-flex align-items-center gap-1 mb-4 text-dark"
                      >
                        <i class="ti ti-building"></i>
                        {{ $service->vendor_name }}
                        {{-- <div>{{ __('admin.provided_by') }}:</div> --}}
                      </a>
                    @endif
                  @else
                    <div></div>
                  @endif

                  <p class="mt-1">{!! Str::limit($service->description ?? 'No description available', 100) !!}</p>
                  @if($_user->hasAnyRole(['admin', 'serviceProvider', 'organizationManager', 'subadmin']))
                  @php
                    $visibleOrgs = $_user->hasAnyRole(['admin', 'serviceProvider'])
                      ? $service->organizations
                      : $service->organizations->whereIn('id', $_user->organizations_ids);
                  @endphp
                  <div class="d-flex align-items-center gap-2 mb-4">
                    <!-- Organizations Badges -->
                    @if($visibleOrgs->count())
                      @foreach($visibleOrgs->take(2) as $organization)
                        <div class="badge bg-label-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $organization->{'name_' . $l } }}">
                          {{ Str::limit($organization->{'name_' . $l}, 25) }}
                        </div>
                      @endforeach
                      @if($visibleOrgs->count() > 2)
                        <div class="badge bg-label-secondary orgs-modal-trigger" style="cursor:pointer;" data-orgs='@json($visibleOrgs->map(function($o) use($l) { return ["id"=>$o->id, "name"=>$o->{"name_".$l}]; }))'>+{{ $visibleOrgs->count() - 2 }}</div>
                      @endif
                    @else
                      <div class="badge bg-label-secondary">{{ __('admin.No Organizations') }}</div>
                    @endif
                  </div>
                  @endif

                  <div class="d-flex flex-column flex-grow-1">
                    <!-- Service Price -->
                    @if($service->price && $service->hide_price == 1 && !$_user->hasAnyRole(['admin', 'organizationManager', 'subadmin', 'serviceProvider']))
                      {{-- Price hidden from non-management roles --}}
                    @else
                      <div class="d-flex align-items-center gap-2 mb-4">
                        <div>{{ __('admin.Price') }}:</div>
                        @if($service->price)
                          <span class="fw-medium">{{ $service->price }} {{ $service->currency }}</span>
                        @else
                          <span class="fw-medium text-muted">{{ __('admin.free') }}</span>
                        @endif
                      </div>
                    @endif

                    <!-- Service Category & Status -->
                    {{--  @if (!$_user->hasRole('registered'))  --}}
                      <div class="d-flex justify-content-between align-items-center mt-auto {{ $_user->hasRole('registered') ? 'mb-4' : '' }}">
                        <div 
                          class="badge bg-label-secondary text-dark me-2" 
                          data-bs-toggle="tooltip"  
                          data-bs-placement="top"
                          title="{{ __('admin.service_category') }}"
                        >
                          <i class="ti ti-briefcase ti-md me-2"></i>
                          {{ $service->serviceCategory?->name ?? '' }}
                        </div>
  
                        @if (!$_user->hasRole('registered'))
                        <div 
                          class="badge bg-label-{{ $service->status == 1 ? 'success' : 'danger' }}"
                          data-bs-toggle="tooltip"  
                          data-bs-placement="top"
                          title="{{ __('admin.status') }}"
                        >
                          <i class="ti ti-point-filled text-{{ $service->status == 1 ? 'success' : 'danger' }}"></i>
                          {{ $service->status == 1 ? __('admin.Active') : __('admin.Inactive') }}
                        </div>
                        @endif
                      </div>
                    {{--  @endif  --}}
                  </div>

                  <!-- Request Service Button -->
                  @if ($_user->hasRole('registered'))
                    <button 
                      class="btn btn-primary w-100 mt-auto"
                      data-service-id="{{ $service->id }}"
                      data-bs-toggle="modal"
                      data-bs-target="#requestServiceModal"
                    >
                      <i class="ti ti-affiliate me-2"></i>
                      {{ __('admin.request_service') }}
                    </button>
                  @endif

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
          ['message' => request('search') || request('rating') || request('status') || request('org-id') ? __('admin.noResultsMatch') :__('admin.No_Data_Found')]
        )
      @endif
    </div>
  </div>
</div>

<!-- Request Service Modal -->
@if ($_user->hasRole('registered'))
  @include('partials.request-service-modal')
@endif

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
                message: "{{ __('admin.DeletedSuccessfully') }}.",
              });
              // Remove the organization card from DOM
              $('div[data-service-id="' + id + '"]').fadeOut(300, function() {
                $(this).remove();
              });
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
