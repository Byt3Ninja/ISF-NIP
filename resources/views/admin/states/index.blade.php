@extends('layouts.admin')

@section('title', __('admin.states'))

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-header row">
        @if (session()->has('msg'))
          <div class="alert alert-success alert-dismissible" role="alert">
            {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
      </div>

      <div class="content-body">
        <section class="organizations-list-wrapper">
          {{-- Breadcrumb --}}
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('country-management.index') }}">{{ __('admin.countries') }}</a></li>
              <li class="breadcrumb-item active">{{ $l == 'ar' ? $country->name_ar : $country->name_en }}</li>
            </ol>
          </nav>

          <div class="organizations-list-table">
            <div class="card">
              <div class="card-content">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-md-center border-bottom">
                  <h5 class="card-title mb-3 mb-md-0">
                    {{ __('admin.states') }} — {{ $l == 'ar' ? $country->name_ar : $country->name_en }}
                  </h5>
                  <a href="{{ route('state-management.create', $country->id) }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i>
                    {{ __('admin.add_state') }}
                  </a>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="organizations-list-datatable" class="table">
                      <thead>
                        <tr class="table-light">
                          <th class="text-center">#</th>
                          <th class="text-center">{{ __('admin.name_en') }}</th>
                          <th class="text-center">{{ __('admin.name_ar') }}</th>
                          <th class="text-center">{{ __('admin.cities') }}</th>
                          <th class="text-center">{{ __('admin.Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($states as $state)
                          <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">{{ $state->name_en }}</td>
                            <td class="text-center">{{ $state->name_ar }}</td>
                            <td class="text-center">
                              <a href="{{ route('city-management.index', $state->id) }}" class="btn btn-sm btn-label-primary">
                                {{ $state->cities_count }} {{ __('admin.cities') }}
                              </a>
                            </td>
                            <td class="text-center">
                              <div class="d-flex align-items-center justify-content-center gap-2">
                                <a href="{{ route('state-management.edit', $state->id) }}" class="btn btn-label-info btn-icon rounded-circle">
                                  <i class="ti ti-edit"></i>
                                </a>
                                <form action="{{ route('state-management.destroy', $state->id) }}" method="POST" style="display:inline-block;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-label-danger btn-icon rounded-circle" onclick="return confirm('{{ __('admin.are_you_sure') }}')">
                                    <i class="ti ti-trash"></i>
                                  </button>
                                </form>
                              </div>
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
        </section>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
@stop
