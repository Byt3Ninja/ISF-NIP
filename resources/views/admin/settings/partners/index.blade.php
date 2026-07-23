@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.partners'))
@section('subTitle', __('admin.partner`s List'))
@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      @if( $_user->ableToAny(['partners.create', 'partners.store'], $permissions) )
      <div class="invoice-create-btn mb-4">
        <a href="{{ route('partners.create') }}" class="btn btn-primary glow">
          <i class="ti ti-plus me-2"></i> {{ __('admin.Add New') }}
        </a>
      </div>
      @endif
      <div class="content-body">
        <section id="basic-datatable">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">
                    {{ $l == 'en' ? 'Partners' : 'الشركاء' }}
                  </h4>
                </div>
                <div class="card-content">
                  <div class="card-body card-dashboard">
                    @if (session()->has('msg'))
                      <div class="row">
                        <div class="col-md-12">
                          <div class="alert alert-success alert-dismissible mb-2" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <div class="d-flex align-items-center">
                              <i class="ti ti-check"></i>
                              <span>
                                {{ __('admin.' . session()->get('msg')) }}
                                {{ Session::forget('msg') }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                    <div class="table-responsive">
                      <table class="table zero-configuration">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>{{ __('admin.English Title') }}</th>
                            <th>{{ __('admin.Arabic Title') }}</th>
                            <th>{{ __('admin.Created At') }}</th>
                            @if( $_user->ableToAny(['partners.show', 'partners.edit', 'partners.update', 'partners.destroy'], $permissions) )<th>{{ __('admin.Actions') }}</th>@endif
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($partners as $partner)
                            <tr>
                              <td>{{ $partner->order }}</td>
                              <td>{{ $partner->en_title }}</td>
                              <td>{{ $partner->ar_title }}</td>
                              <td>{{ $partner->created_at }}</td>
                              @if( $_user->ableToAny(['partners.show', 'partners.edit', 'partners.update', 'partners.destroy'], $permissions) )
                              <td>
                                @if( $_user->ableToAny(['partners.edit', 'partners.update'], $permissions) )
                                  <a href="{{ route('partners.edit', ['partner' => $partner->id]) }}"
                                    class="btn btn-label-info"><i class="ti ti-edit text-info"> </i></a>
                                @endif
                                @if( $_user->ableTo('partners.destroy', $permissions) )
                                  <button id='deleteAction' data-id='{{ $partner->id }}'
                                    data-url="{{ route('partners.destroy', ['partner' => $partner->id]) }}" type="button"
                                    class="btn btn-label-danger"><i class="ti ti-trash text-danger"> </i></button>
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
