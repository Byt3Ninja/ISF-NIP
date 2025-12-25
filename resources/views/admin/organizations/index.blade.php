@extends('layouts.admin')

@section('title', __('admin.Organizations'))
@section('subTitle', __('admin.Organizations`s List'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
<style>
  .draft-ribbon {
    position: absolute;
    top: 12px;
    left: -35px;
    background: #0d6efd;
    color: white;
    padding: 5px 40px;
    font-size: 11px;
    font-weight: 600;
    transform: rotate(-45deg);
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }

  .org-card {
    position: relative;
    overflow: hidden;
  }
</style>
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      @if (session()->has('msg'))
        <x-alert type="success" message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
      @endif
      @if (session()->has('message2'))
        <x-alert type="danger"
          message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
      @endif

      @if ($organizations->isEmpty())
        <div class="row h-100">
          <div class="col-12 d-flex flex-column justify-content-center align-items-center">
            <img 
              src="/icons/not-found.svg"
              width="337"
              height="234"
            />
            <h3 class="text-center mt-4">{{ __('admin.No_Data_Found') }}</h3>
            <!-- Add New Org Button -->
            @if ($_user->ableTo('organizations.create', $permissions))
              <a href="{{ route('organizations.create') }}" class="btn btn-lg btn-primary" style="width: 200px;">
                <i class="ti ti-plus me-2"></i> {{ __('admin.Add_New') }}
              </a>
            @endif
          </div>
        </div>
      @else
        <div class="card mb-6">
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-lg-8">
                <div class="row">
                  <div class="col-12 col-sm-3 mb-3 mb-sm-0">
                    <div class="badge bg-label-secondary d-flex align-items-center py-2" style="height: auto;">
                      <div class="badge card">
                        <i class="ti ti-layout-board fs-2 text-primary"></i>
                      </div>
                      <div class="ms-2 py-1">
                        <h6 class="mb-0 fw-bold text-primary">{{ __('admin.Organizations') }}</h6>
                        <h5 class="mb-0 text-start">{{ $organizations->count() }}</h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-sm-3 mb-3 mb-sm-0">
                    <div class="badge bg-label-secondary d-flex align-items-center py-2" style="height: auto;">
                      <div class="badge card">
                        <i class="ti ti-circle-dot fs-2 text-success"></i>
                      </div>
                      <div class="ms-2 py-1">
                        <h6 class="mb-0 fw-bold text-success">{{ __('admin.Active') }}</h6>
                        <h5 class="mb-0 text-start">{{ $organizations->where('status', 1)->count() }}</h5>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-sm-3 mb-3 mb-sm-0">
                    <div class="badge bg-label-secondary d-flex align-items-center py-2" style="height: auto;">
                      <div class="badge card">
                        <i class="ti ti-circle-dot fs-2 text-warning"></i>
                      </div>
                      <div class="ms-2 py-1">
                        <h6 class="mb-0 fw-bold text-warning">{{ __('admin.Inactive') }}</h6>
                        <h5 class="mb-0 text-start">{{ $organizations->where('status', 0)->count() }}</h5>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-4 mt-3 mt-lg-0">
                <div class="w-100 h-100 d-flex justify-content-lg-end justify-content-center align-items-center gap-2">
                  @if ($_user->ableToAny(['organization.wizard.create', 'organization.wizard.store'], $permissions))
                    <a href="{{ route('organization.wizard.create') }}" class="btn btn-outline-primary">
                      <i class="ti ti-wand me-2"></i> {{ __('admin.Create with Wizard') }}
                    </a>
                    <a href="{{ route('organizations.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus me-2"></i> {{ __('admin.Add_New') }}
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-6">
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-3 mb-3 mb-md-0">
                <form id="status-form" method="GET" action="{{ route('organizations.index') }}">
                  <select id="status-select" class="form-select" name="status">
                    <option value="" selected>{{ __('admin.Status') }}</option>
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>
                      {{ __('admin.All') }}
                    </option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                      {{ __('admin.Active') }}
                    </option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>
                      {{ __('admin.Inactive') }}
                    </option>
                  </select>
                </form>
              </div>
              <div class="col-12 col-md-6"></div>
              <div class="col-12 col-md-3">
                <div class="position-relative w-100 h-100 d-flex justify-content-end align-items-center">
                  <input type="text" id="search-org" class="form-control" placeholder="{{ __('admin.Search') }}" />
                  <i class="ti ti-search position-absolute top-50 mx-2 translate-middle-y"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Organizations List -->
        <div class="row">
          @foreach ($organizations as $organization)
            <div class="col-12 col-md-6 col-lg-4 mb-6">
              <div class="card org-card">
                @if($organization->is_draft)
                  <div class="draft-ribbon">{{ __('admin.Draft') }}</div>
                @endif
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-primary fw-semibold mb-0">
                      {{ $organization->{'name_' . $l} }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                      <!-- Mail Icon -->
                      @if (optional($organization->user)->email)
                        <a href="mailto:{{ $organization->user->email }}" class="text-decoration-none">
                          <i class="ti ti-mail fs-4 text-primary"></i>
                        </a>
                      @endif
                      <!-- Phone Icon -->
                      @if (optional($organization->user)->phone)
                        <a href="tel:{{ $organization->user->phone }}" class="text-decoration-none">
                          <i class="ti ti-phone fs-4 text-primary"></i>
                        </a>
                      @endif
                    </div>
                  </div>
                  <!-- Organization Image -->
                  <div class="card mt-3 d-flex justify-content-center align-items-center position-relative"
                    style="height: 150px; width: 100%;">
                    <img
                      src="{{ $organization->imageUrl }}"
                      onerror="this.src='/images/logo-placeholder.png'" alt="Organization Image"
                      class="rounded img-fluid"
                      style="height: 120px; min-width: 200px; object-fit: cover;"
                    />
                    <!-- Status Badge -->
                    <div
                      class="bg-{{ $organization->status == 1 ? 'success' : 'warning' }} shadow rounded-circle position-absolute bottom-0 end-0 m-2"
                      style="width: 15px; height: 15px;" data-bs-toggle="tooltip" data-bs-placement="top"
                      title="{{ $organization->status == 1 ? __('admin.Active') : __('admin.Inactive') }}">
                    </div>
                  </div>
                  <!-- Organization Mangaer -->
                  <div class="d-flex flex-column mt-3">
                    <p class="mb-1">{{ __('admin.organization_manager') }}</p>
                    <div class="d-flex justify-content-center align-items-center rounded border p-2">
                      <img src="{{ $organization->user->img_url ?? '/images/placeholder.png' }}"
                        onerror="this.src='/images/user-placeholder.jpeg'" alt="Manager Image"
                        class="rounded-circle img-fluid border" style="height: 35px; width: 35px; object-fit: cover;" />
                      <span class="ms-3">{{ $organization->user->name ?? '' }}</span>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('users.view_companies', ['orgnization' => $organization->id]) }}" >
                    <div class="d-flex align-items-center gap-2">
                      <div class="badge bg-label-warning px-2">
                        <i class="ti ti-building-skyscraper fs-4"></i>
                      </div>
                      <p class="fw-semibold mb-0">{{ __('admin.Startups') }}</p>
                      <p
                        class="fw-semibold mb-0 bg-light rounded-circle w-px-20 h-px-20 d-flex justify-content-center align-items-center">
                        {{ $organization->total_startups }}
                      </p>
                    </div>
                  </a>
                    <a href="{{ route('programlist.index', ['organization_id' => $organization->id]) }}" >
                      <div class="d-flex align-items-center gap-2">
                        <div class="badge bg-label-info px-2 py-1">
                          <i class="ti ti-components fs-4"></i>
                        </div>
                        <p class="fw-semibold mb-0">{{ __('admin.Cycles') }}</p>
                        <p
                          class="fw-semibold mb-0 bg-light rounded-circle w-px-20 h-px-20 d-flex justify-content-center align-items-center">
                          {{ $organization->cycles->count() }}
                        </p>
                      </div>
                    </a>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-icon rounded-circle shadow" type="button"
                        id="dropdownMenuButton{{ $organization->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu p-0" aria-labelledby="dropdownMenuButton{{ $organization->id }}">
                        @if ($_user->ableToAny(['organizations.edit', 'organizations.update'], $permissions))
                          <li>
                            <a class="dropdown-item"
                              href="{{ route('organizations.edit', ['organization' => $organization->id]) }}">
                              <i class="ti ti-edit text-info me-2"></i>{{ __('admin.Edit') }}
                            </a>
                          </li>
                        @endif
                        @if ($_user->ableToAny(['organization.wizard.edit'], $permissions))
                          <li>
                            <a class="dropdown-item"
                              href="{{ route('organization.wizard.edit', ['id' => $organization->id]) }}">
                              <i class="ti ti-wand text-primary me-2"></i>{{ __('admin.Edit with Wizard') }}
                            </a>
                          </li>
                        @endif
                        @if ($_user->ableTo('organizations.destroy', $permissions))
                          <li>
                            <button class="dropdown-item" id="deleteAction" data-id="{{ $organization->id }}"
                              data-url="{{ route('organizations.destroy', ['organization' => $organization->id]) }}">
                              <i class="ti ti-trash text-danger me-2"></i>{{ __('admin.Delete') }}
                            </button>
                          </li>
                        @endif
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <!--/ Organizations List -->
      @endif
    </div>
  </div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

  <script>
    // Filter Organizations
    $(document).on("change", "#status-select", function(e) {
      $("#status-form").submit();
    });

    // Search Organizations
    $(document).on("keyup", "#search-org", function(e) {
      var value = $(this).val().toLowerCase();
      $(".org-card").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
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
                  // Swal.fire({
                  //   type: 'success',
                  //   title: "{{ __('admin.Deleted') }}!",
                  //   html: "{{ __('admin.DeletedSuccessfully') }}.",
                  // });
                  location.reload();
                }
              ).fail(function(error) {
                console.log(error);
              });
            }
          })
      });
    </script>
  @endif
@stop
