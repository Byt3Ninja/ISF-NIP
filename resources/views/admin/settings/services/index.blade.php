@extends('layouts.admin')

@section('title', __('cycles.Services'))
@section('subTitle', __('cycles.Service list'))

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center my-3">
      <h1 class="h3">{{ __('cycles.Services') }}</h1>
      @if( $_user->ableToAny(['serviceCategories.create', 'serviceCategories.store'], $permissions) )
      <div class="invoice-create-btn mb-4">
        <a href="{{ route('services.create') }}" class="btn btn-primary glow">
          <i class="ti ti-plus me-2"></i> {{ __('cycles.Add New') }}
        </a>
      </div>
      @endif
      @if (session()->has('msg'))
      <div class="row">
        <div class="col-md-12">
          <div class="alert alert-success alert-dismissible mb-2" role="alert">
            {{ __('admin.' . session()->get('msg')) }}
            {{ Session::forget('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      </div>
      @endif
    </div>

    <div class="content-body">
      <section id="basic-datatable">
        <?php $index = 1; ?>

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-content">
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table zero-configuration">
                      <thead class="table-light">
                        <tr>
                          <th>#</th>
                          <th class="text-center">{{__('admin.Organization')}}</th>
                          <th class="text-center">{{ __('cycles.English Name') }}</th>
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
                          <td class="text-center">
                            @if($service->organizations && $service->organizations->count())
                            <div class="d-flex flex-wrap justify-content-center gap-1 w-px-100">
                              @foreach($service->organizations as $organization)
                              <span class="badge bg-label-primary rounded-pill"
                                title="{{ $organization->{'name_'.$l} }}">
                                <a href="{{ URL::to('dashboard/organizations/' . $organization->id . '/edit') }}"
                                  class="text-decoration-none">
                                  {{ Str::limit($organization->{'name_'.$l}, 15) }}
                                </a>
                              </span>
                              @endforeach
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                          </td>
                          <td class="text-center">{{ $service->name_en }}</td>
                          <td class="text-center">{{ $service->price > 0 ? $service->price . $Setting->Currency : 'Free'
                            }}</td>
                          <td class="text-center">
                            @if($service->vendor_name || $service->vendor_email || $service->vendor_phone)
                            <div class="vendor-info d-flex flex-column align-items-start">
                              @if($service->vendor_name)
                              <div class="d-flex align-items-center mb-1 vendor-name">
                                <i class="ti ti-user me-1"></i>
                                <span class="fw-medium">{{ $service->vendor_name }}</span>
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
                                <a href="tel:{{ $service->vendor_phone }}" class="text-decoration-none">
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
                            <img src="{{ Storage::disk('public_uploads')->url($service->img_url) }}" alt="img"
                              style="width: 50px; height: 50px;">
                            @else
                            -
                            @endif
                          </td>
                          <td class="text-center">{{ $service->created_at }}</td>
                          @if($_user->ableToAny(['services.show', 'services.edit', 'services.update',
                          'services.destroy'], $permissions))
                          <td class="text-center">
                            <div class="d-flex justify-content-center">
                              @if($_user->ableToAny(['services.show', 'services.edit', 'services.update'],
                              $permissions))
                              <a href="{{ route('services.edit', ['service' => $service->id]) }}"
                                class="btn btn-label-primary mx-1" title="Edit Service">
                                <i class="ti ti-pencil"></i>
                              </a>
                              @endif

                              @if($_user->ableTo('services.destroy', $permissions))
                              <button id="deleteAction" data-id="{{ $service->id }}"
                                data-url="{{ route('services.destroy', ['service' => $service->id]) }}" type="button"
                                class="btn btn-label-danger mx-1" title="Delete Service">
                                <i class="ti ti-trash"></i>
                              </button>
                              @endif
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
@if ($l == 'ar')
<script type="text/javascript">
  $(document).ready(function() {
        $('.zero-configuration').DataTable({
          "order": [[ 6, "desc" ]], // Order by Created At column (index 6) descending
          "language": {
            "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
            "sLoadingRecords": "جارٍ التحميل...",
            "sProcessing": "جارٍ التحميل...",
            "sLengthMenu": "أظهر _MENU_ مدخلات",
            "sZeroRecords": "لم يعثر على أية سجلات",
            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
            "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
            "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
            "sInfoPostFix": "",
            "sSearch": "ابحث:",
            "sUrl": "",
            "oPaginate": {
              "sFirst": "الأول",
              "sPrevious": "السابق",
              "sNext": "التالي",
              "sLast": "الأخير"
            },
            "oAria": {
              "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
              "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
            }
          }
        });
      });
</script>
@else
<script type="text/javascript">
  $(document).ready(function() {
        $('.zero-configuration').DataTable({
          "order": [[ 6, "desc" ]] // Order by Created At column (index 6) descending
        });
      });
</script>
@endif

<script>
  $(document).on("click", "#deleteAction", function(e) {
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
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
                Swal.fire(
                  '{{ __('admin.Deleted') }}!',
                  '{{ __('admin.FileDeleted') }}.',
                  'success'
                );

                location.reload();
              }
            ).fail(
              function(error) {
                console.log(error);
              }
            );
          } else {
            console.log('Canceled')
          }
        })
    });
</script>
@stop