@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('cycles.Services'))
@section('subTitle', __('cycles.Service list'))
@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="basic-datatable">
          <h4 class="card-title mb-3">{{ __('cycles.Service list') }}</h4>
          <?php $index = 1; ?>

          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-content">
                  <div class="card-body card-dashboard">
                    @if( $_user->ableToAny(['serviceCategories.create', 'serviceCategories.store'], $permissions) )
                    <div class="invoice-create-btn mb-4">
                      <a href="{{ route('services.create') }}" class="btn btn-primary glow">
                        {{ __('cycles.Add New') }} <i class="ti ti-plus mx-1"></i>
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
                    <div class="table-responsive">
                      <table class="table zero-configuration">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>{{ __('cycles.English Name') }}</th>
                            <th>{{ __('cycles.Arabic Name') }}</th>
                            <th>{{ __('cycles.Price') }}</th>
                            <th>{{ __('cycles.Created At') }}</th>
                            @if( $_user->ableToAny(['services.show', 'services.edit', 'services.update', 'services.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($services as $service)
                            <tr>
                              <td>{{ $index++ }}</td>
                              <td>{{ $service->name_en }}</td>
                              <td>{{ $service->name_ar }}</td>
                              <td>{{ $service->price > 0 ? $service->price . $Setting->Currency : 'Free' }}</td>
                              <td>{{ $service->created_at }}</td>
                              @if( $_user->ableToAny(['services.show', 'services.edit', 'services.update', 'services.destroy'], $permissions) )
                              <td>
                                @if( $_user->ableToAny(['services.show', 'services.edit', 'services.update'], $permissions) )
                                  <a href="{{ route('services.edit', ['service' => $service->id]) }}"
                                    class="btn btn-label-primary">
                                    <i class="ti ti-pencil"></i>
                                  </a>
                                @endif

                                @if( $_user->ableTo('services.destroy', $permissions) )
                                  <button id='deleteAction' data-id='{{ $service->id }}'
                                    data-url="{{ route('services.destroy', ['service' => $service->id]) }}" type="button"
                                    class="btn btn-label-danger">
                                    <i class="ti ti-trash text-danger"></i>
                                  </button>
                                @endif
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
      </div>
      </section>
    </div>
  </div>
@stop
@section('scripts')
  @if ($l == 'ar')
    <script type="text/javascript">
      $(document).ready(function() {
        $('.zero-configuration').DataTable({
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
  @endif
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
  <script src="/app-assets/js/scripts/modal/components-modal.js"></script>
  <script>
    $(document).on("click", "#deleteAction", function(e) {
      var url = $(this).data('url');
      var token = '{{ csrf_token() }}';
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          type: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}!",
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
