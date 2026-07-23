@extends('layouts.admin')

@section('title', __('admin.Currencies'))

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
        @if (session()->has('message2'))
          <div class="alert alert-danger alert-dismissible" role="alert">
            {{ __('admin.' . session()->get('message2')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      </div>
      @endif
    </div>

    <div class="content-body">
      <section class="organizations-list-wrapper">
        <div class="organizations-list-table">
          <div class="card">
            <div class="card-content">
              <div class="card-header d-flex flex-wrap justify-content-between align-items-md-center border-bottom">
                <h5 class="card-title mb-3 mb-md-0">{{ __('admin.Currencies') }}</h5>
                <div class="d-flex flex-wrap align-items-center gap-3">
                  <button type="button" class="btn btn-label-primary UpdateAllCurrencies">
                    <i class="ti ti-refresh me-2"></i>
                    {{ __('admin.update_currencies') }}
                  </button>
                  <a href="{{ route('currencies.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i>
                    {{ __('admin.Add New') }}
                  </a>
                </div>
              </div>
              <div class="card-body">
                <!-- datatable start -->
                <div class="table-responsive">
                  <table id="organizations-list-datatable" class="table">
                    <thead>
                      <tr class="table-light">
                        <th class="text-center">#</th>
                        <th class="text-center">{{ __('admin.Code') }}</th>
                        <th class="text-center">{{ __('admin.Name') }}</th>
                        <th class="text-center">{{ __('admin.Symbol') }}</th>
                        <th class="text-center">{{ __('admin.Exchange_Rate') }}</th>
                        <th class="text-center">{{ __('admin.Flag') }}</th>
                        <th class="text-center">{{ __('admin.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($currencies as $currency)
                        <tr>
                          <td class="text-center">{{ $loop->iteration }}</td>
                          <td class="text-center">{{ $currency->currency_code }}</td>
                          <td class="text-center">{{ $currency->name }}</td>
                          <td class="text-center">{{ $currency->currency_symbol }}</td>
                          <td class="text-center">{{ $currency->exchange_rate_to_usd }}</td>
                          <td class="text-center">
                            @if (!empty($currency->flag) && file_exists(public_path($currency->flag)))
                              <img src="{{ asset($currency->flag) }}" width="30" alt="Flag">
                            @else
                              {{ $currency->flag }}
                            @endif

                          </td>
                          <td class="text-center">
                            <div class="d-flex align-items-center gap-2">
                              <a href="{{ route('currencies.edit', $currency->id) }}" class="btn btn-label-info btn-icon rounded-circle">
                                <i class="ti ti-edit"></i>
                              </a>
                              <form action="{{ route('currencies.destroy', $currency->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-label-danger btn-icon rounded-circle">
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
  <script src="/app-assets/js/scripts/pages/page-organizations.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
  <script src="/app-assets/js/scripts/modal/components-modal.js"></script>

  <script>
    $(document).ready(function() {

      // عند الضغط على زر "تيم تحديث العملات"
      $('.UpdateAllCurrencies').click(function() {
        // جلب العملات من API
        var currenciesData = {};

        // جلب العملات من API
        $.ajax({
          url: "{{ route('currencies.rates_update_all') }}",
          type: "PATCH",
          headers: {
           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           },
          success: function(response) {
            alert(response.success)
            location.reload();
          },
          error: function() {
             alert('An error occurred while updating currencies.');
          }
        });
      });

    });
  </script>

@stop
