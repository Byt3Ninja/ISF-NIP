@extends('layouts.admin')

@section('title', __('admin.Organization Types'))
@section('subTitle', __('admin.Organization Types`s List'))

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('content')
  <div class="">
    <div class="">
      @if (session()->has('msg'))
        <x-alert type="success" message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}" />
      @endif
      @if (session()->has('message2'))
        <x-alert type="danger"
          message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
      @endif

      @if ($organizationTypes->isEmpty())
        <div class="row h-100">
          <div class="col-12 d-flex flex-column justify-content-center align-items-center">
            <img 
              src="/icons/not-found.svg"
              width="337"
              height="234"
            />
            <h3 class="text-center mt-4">{{ __('admin.No_Data_Found') }}</h3>
            <!-- Add New Org Button -->
            @if ($_user->ableTo('organization-types.create', $permissions))
              <a href="{{ route('organization-types.create') }}" class="btn btn-lg btn-primary" style="width: 200px;">
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
                  <div class="col-12 col-sm-12 mb-3 mb-sm-0">
                    <div class="badge bg-label-secondary d-flex align-items-center py-2" style="height: auto;">
                      <div class="badge card">
                        <i class="ti ti-layout-board fs-2 text-primary"></i>
                      </div>
                      <div class="ms-2 py-1">
                        <h6 class="mb-0 fw-bold text-primary">{{ __('admin.Organization Types') }}</h6>
                        <h5 class="mb-0 text-start">{{ $organizationTypes->count() }}</h5>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-4 mt-3 mt-lg-0">
                <div class="w-100 h-100 d-flex justify-content-lg-end justify-content-center align-items-center">
                  @if ($_user->ableTo('organization-types.create', $permissions))
                    <a href="{{ route('organization-types.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus me-2"></i> {{ __('admin.Add_New') }}
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Organization Types List -->
        <div class="card mb-6">
          <div class="card-body">
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12 mb-6">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th style="width: 50px;">#</th>
                        <th>{{ __('admin.Name') }}</th>
                        <th style="width: 180px;">{{ __('admin.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach( $organizationTypes as $index => $orgType )
                        <tr>
                          <td> {{ $index + 1 }} </td>
                          <td> {{ $orgType->name }} </td>
                          <td>
                            @if( $_user->ableToAny(['organization-types.edit', 'organization-types.update'], $permissions) )
                            <a href="{{ route('organization-types.edit', ['organization_type' => $orgType->id]) }}" class="btn btn-label-info btn-sm">
                              <i class="ti ti-edit text-info"> </i>
                            </a>
                            @endif
                            @if( $_user->ableTo('organization-types.destroy', $permissions) )
                            <button class="btn btn-label-danger btn-sm delete-org-type" data-url="{{ route('organization-types.destroy', ['organization_type' => $orgType->id]) }}">
                              <i class="ti ti-trash text-danger"> </i>
                            </button>
                            @endif
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
        <!--/ Organization Types List -->
      @endif
    </div>
  </div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  
  @if ($_user->ableTo('organization-types.destroy', $permissions))
    <script>
      // Delete Organization
      $(document).on("click", ".delete-org-type", function(e) {
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
      }).then((result) => {
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
