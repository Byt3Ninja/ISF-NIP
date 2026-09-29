@extends('layouts.admin')

@section('title', __('admin.Organizations'))
@section('subTitle', __('admin.Organizations`s List'))

@php
$colors = ['primary', 'info', 'danger', 'warning'];
@endphp

@section('content')
  <div class="">
    <div class="">
      @if (session()->has('msg'))
        <x-alert 
          type="success" 
          message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" 
        />
      @endif
      @if (session()->has('message2'))
        <x-alert
          type="danger"
          message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" 
        />
      @endif

      <div class="row g-6 mb-6">
        @foreach($stats as $key => $stat)
          <div class="col-6 col-xl-3">
            <div class="card h-100">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                  <h5 class="mb-1 me-2">{{ $stat['value'] ?? 0 }}</h5>
                  <p class="mb-0">{{ $stat['label'] }}</p>
                </div>
                <div class="card-icon">
                  <span class="badge bg-label-{{ $colors[$key] }} rounded p-2">
                    <i class="ti ti-{{ $stat['icon'] }} ti-26px"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="card mb-6">
        <div class="card-body">
          <div class="row gy-4">
            <div class="col-lg-3">
              <div class="card-title mb-0">
                <h5 class="mb-0">{{ __('admin.Manage Organizations') }}</h5>
                <p class="mb-0">{{ __('admin.manage_organizations_description') }}</p>
              </div>
            </div>
            <div class="col-lg-9">
              <form class="row gy-4 gx-3 justify-content-end align-items-center" id="status-form" method="GET" action="{{ route('organizations.index') }}" data-ignore-dirty>
                <div class="col-12 col-lg-4">
                  <!-- Search Input -->
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                    <input type="search" id="debounced-search" class="form-control h-px-40" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.Search') }}..." aria-label="Search" aria-describedby="basic-addon-search31">
                  </div>
                </div>
                <div class="col-6 col-lg-2">
                  <select class="form-select" id="status-select" name="status">
                    <option value="">{{ __('admin.All_statuses') }}</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                      {{ __('admin.Active') }}
                    </option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>
                      {{ __('admin.Inactive') }}
                    </option>
                  </select>
                </div>
                <div class="col-6 col-lg-2">
                  <select class="form-select" id="visibility-select" name="visibility">
                    <option value="">{{ __('admin.all_visiblity') }}</option>
                    <option value="published" {{ request('visibility') == 'published' ? 'selected' : '' }}>
                      {{ __('admin.Published') }}
                    </option>
                    <option value="draft" {{ request('visibility') == 'draft' ? 'selected' : '' }}>
                      {{ __('admin.Draft') }}
                    </option>
                  </select>
                </div>
                <div class="col-6 col-lg-2">
                  <select class="form-select" id="sort-select" name="sort">
                    <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>
                      {{ __('admin.Newest First') }}
                    </option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>
                      {{ __('admin.Oldest First') }}
                    </option>
                  </select>
                </div>
                <div class="col-lg-auto d-flex justify-content-end">
                  @if ($_user->ableTo('organization.wizard.create', $permissions))
                    <a href="{{ route('organization.wizard.create') }}?step=1" class="btn btn-primary text-nowrap">
                      <i class="ti ti-plus me-2"></i> {{ __('admin.Add_New') }}
                    </a>
                  @endif
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      @if (!empty($organizations) && $organizations->count() > 0)
        <div class="row g-6 mb-6">
          @foreach ($organizations as $org)
            <div class="col-lg-4" data-org-id="{{ $org->id }}">
              <div class="card h-100">
                <div class="card-body flex-fill d-flex flex-column">
                  <!-- Header -->
                  <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar avatar-md me-2 d-flex flex-shrink-0">
                        @if ($org->img_url)
                          <img src="{{ route('organizations.logo', $org->img_url ?? '') }}" alt="logo" class="rounded object-fit-contain" />
                        @else
                          <span class="avatar-initial rounded bg-label-secondary">
                            {{ substr($org->name, 0, 2) }}
                          </span>
                        @endif
                      </div>
                      <h5 class="card-title line-clamp-2 mb-0 w-px-{{ $org->is_draft ? '150' : '200' }}">{{ $org->{'name_' . $l} }}</h5>
                    </div>

                    <div class="d-flex align-items-center ms-2">
                      <!-- Status Badge -->
                      @if($org->is_draft)
                        <span class="badge bg-label-warning rounded-pill">
                          {{ __('admin.Draft') }}
                        </span>
                      @endif
      
                      <!-- Dropdown -->
                      <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill btn-icon btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                          <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          @if ($_user->ableTo('organization.wizard.edit', $permissions))
                            <li>
                              <a class="dropdown-item" href="{{ route('organization.wizard.edit', $org->id) }}?step=1">
                                <i class="ti ti-edit me-2"></i> {{ __('admin.Edit') }}
                              </a>
                            </li>
                          @endif
                          @if ($_user->ableTo('organization.wizard.edit', $permissions))
                          <li>
                            <a class="dropdown-item" href="{{ route('organization.wizard.landing', $org->id) }}">
                              <i class="ti ti-layout me-2"></i> {{ __('admin.Manage') }} {{ __('admin.Landing Page') }}
                            </a>
                          </li>
                          @endif
                          @if ($org->slug && $_user->ableTo('organization.wizard.preview', $permissions))
                            <li>
                              <a class="dropdown-item" href="{{ route('organization.wizard.preview', $org->id) }}" target="_blank">
                                <i class="ti ti-eye me-2"></i> {{ __('admin.Preview') }} {{ __('admin.Landing Page') }}
                              </a>
                            </li>
                          @endif
                          @if ($_user->ableTo('organizations.destroy', $permissions))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <a class="dropdown-item text-danger" id="deleteAction" data-id="{{ $org->id }}"
                                  data-url="{{ route('organizations.destroy', ['organization' => $org->id]) }}">
                                <i class="ti ti-trash me-2"></i> {{ __('admin.Delete') }}
                              </a>
                            </li>
                          @endif
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div class="flex-grow-1">
                    <!-- Status Badge -->
                    <div class="badge bg-label-{{ $org->status == 1 ? 'info' : 'danger' }} mb-3">
                      {{ $org->status == 1 ? __('admin.Active') : __('admin.Inactive') }}
                    </div>

                    <!-- Description -->
                    <p class="mb-4 line-clamp-3">{{ $org->bio }}</p>

    
                    <!-- Managers -->
                    <div class="mb-4">
                      <div class="text-secondary mb-2">{{ __('admin.Managers') }}</div>
                      <div class="d-flex align-items-center avatar-group my-4">
                        @if($org->assignedUsers->isNotEmpty())
                          @foreach($org->assignedUsers->take(3) as $manager)
                            <div class="avatar" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $manager->name }}">
                              <img src="{{ $manager->image_url }}" alt="Avatar" class="rounded-circle pull-up">
                            </div>
                          @endforeach
                          @if($org->assignedUsers->count() > 3)
                            <div class="avatar" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $org->assignedUsers->count() - 3 }} more">
                              <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $org->assignedUsers->count() - 3 }} more">+{{ $org->assignedUsers->count() - 3 }}</span>
                            </div>
                          @endif
                        @else
                          <div class="avatar" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $org->user->name ?? 'N' }}">
                            <span class="avatar-initial rounded-circle pull-up text-heading">{{ substr($org->user->name ?? 'N', 0, 2) }}</span>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                  
                  <hr>

                  <!-- Stats -->
                  <div class="row g-3">
                    <div class="col-6">
                      <a href="{{ route('users.view_companies', ['orgnization' => $org->id]) }}" class="d-flex align-items-center">
                        <div class="badge rounded bg-label-info me-4 p-2">
                          <i class="ti ti-building-skyscraper ti-lg"></i>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">{{ $org->total_startups }}</h5>
                          <small class="text-secondary">{{ __('admin.Startups') }}</small>
                        </div>
                      </a>
                    </div>

                    <div class="col-6">
                      <a href="{{ route('programlist.index', ['organization_id' => $org->id]) }}" class="d-flex align-items-center">
                        <div class="badge rounded bg-label-primary me-4 p-2">
                          <i class="ti ti-components ti-lg"></i>
                        </div>
                        <div class="card-info">
                          <h5 class="mb-0">{{ $org->cycles->count() }}</h5>
                          <small class="text-secondary">{{ __('admin.Cycles') }}</small>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach

          <div class="col-12 mt-12">
            {{ $organizations->links('pagination::bootstrap-5') }}
          </div>
        </div>
      @else
        @include('includes.no-result', ['message' => __('admin.No_Data_Found')])
      @endif
    </div>
  </div>
@stop

@section('scripts')
  <script>
    // Filter Organizations
    $(document).on("change", "#status-select", function(e) {
      $("#status-form").submit();
    });
    $(document).on("change", "#visibility-select", function(e) {
      $("#status-form").submit();
    });
    $(document).on("change", "#sort-select", function(e) {
      $("#status-form").submit();
    });
  </script>
  
  @if ($_user->ableTo('organizations.destroy', $permissions))
    <script>
      // Delete Organization
      $(document).on("click", "#deleteAction", function(e) {
        var url = $(this).data('url');
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
              $.post(url, {
                  '_method': 'DELETE',
                  '_token': token
                },
                function(response) {
                  if (response.success) {
                    releaseSuccessAlert({
                      title: "{{ __('admin.Deleted') }}!",
                      message: "{{ __('admin.DeletedSuccessfully') }}.",
                    });

                    // Remove the organization card from DOM
                    $('div[data-org-id="' + $(this).data('id') + '"]').fadeOut(300, function() {
                      $(this).remove();
                    });
                    
                    // Reload the page
                    location.reload();

                  } else {
                    releaseFailureAlert({
                      title: "{{ __('admin.Error') }}",
                      message: response.message ? response.message : "{{ __('admin.DeleteFailed') }}"
                    });
                  }
                }.bind(this)
              ).fail(function(xhr) {
                let errorMessage = "{{ __('admin.DeleteFailed') }}";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                  errorMessage = xhr.responseJSON.message;
                }
                releaseFailureAlert({
                  title: "{{ __('admin.Error') }}",
                  message: errorMessage
                })
              });
            }
          })
      });
    </script>
  @endif
@stop
