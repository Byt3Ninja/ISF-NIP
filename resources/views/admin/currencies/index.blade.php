@extends('layouts.admin')

@section('title', __('admin.Currencies') )

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
			<div class="content-header row">
			@if(session()->has('msg'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }}
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
				@endif
					@if(session()->has('message2'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ __('admin.'.session()->get('message2')) }}
                         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
						</div>
					@endif
			</div>

      <div class="row mb-6">
        <div class="d-flex align-items-start w-100">
            <a href="{{ route('currencies.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i></i>                 
                 <span class="mx-2">{{ __('admin.Add New')}} </span>
              </a>
        </div>
      </div>

			<div class="content-body">
				<section class="organizations-list-wrapper">
          <div class="organizations-list-table">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title mb-0">{{__('admin.Currencies')}}</h4>
                <span class="btn btn-success UpdateAllCurrencies"> {{ __('admin.update_currencies') }}</span>
              </div>
              
              <div class="card-content">
                <div class="card-body">
									<!-- datatable start -->
									<div class="table-responsive">
                                        <table id="organizations-list-datatable" class="table">
                                            <thead>
                                                <tr class="table-light">
                                                    <th class="text-center">#</th>
                                                    <th class="text-center">{{__('admin.Code')}}</th>
                                                    <th class="text-center">{{__('admin.Name')}}</th>
                                                    <th class="text-center">{{__('admin.Symbol')}}</th>
                                                    <th class="text-center">{{__('admin.Exchange_Rate')}}</th>
                                                    <th class="text-center">{{__('admin.Flag')}}</th>
                                                    <th class="text-center">{{__('admin.Actions')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($currencies as $currency)
                                                <tr>
                                                    <td class="text-center">{{ $loop->iteration }}</td>
                                                    <td class="text-center">{{ $currency->currency_code }}</td>
                                                    <td class="text-center">{{ $currency->currency_name }}</td>
                                                    <td class="text-center">{{ $currency->currency_symbol }}</td>
                                                    <td class="text-center">{{ $currency->exchange_rate_to_usd }}</td>
                                                    <td class="text-center">
                                                        @if(!empty($currency->flag) && file_exists(public_path($currency->flag)))
                                                        <img src="{{ asset($currency->flag) }}" width="30" alt="Flag">
                                                        @else
                                                        {{ $currency->flag }}
                                                        @endif
                                                        
                                                    </td>
                                                    <td class="text-center">
                                                      <a href="{{ route('currencies.edit',$currency->id) }}" class="btn btn-label-warning btn-sm">
                                                        <i class="ti ti-edit text-warning"></i>
                                                    </a>
                                                    <form action="{{ route('currencies.destroy',$currency->id) }}" method="POST" style="display:inline-block;">
                                                      @csrf
                                                      @method('DELETE')
                                                      <button type="submit" class="btn btn-label-danger btn-sm">
                                                          <i class="ti ti-trash text-danger"></i>
                                                      </button>
                                                  </form>
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
          url: "{{ route('currencies.UpdateAllCurrency') }}",
          type: "GET",
          success: function(response) {
            alert(response.success)
            location.reload();
          },
          error: function() {
            alert(error);
          }
      });
    });

    });
</script>

@stop
