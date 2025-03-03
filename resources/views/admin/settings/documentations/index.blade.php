@extends('layouts.admin')

@section('title', __('admin.Documentations'))
@section('subTitle', __('admin.documentation`s List'))

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      {{-- 
      <div class="invoice-create-btn mb-4">
      </div> --}}
      <div class="content-body">
        <section id="basic-datatable">
          <div class="row">
            <div class="col-12">
              <div class="card">
                @if($_user->ableToAny(['documentation.create', 'documentation.store'], $permissions))
                <div class="card-header">
                  <a href="{{ route('documentation.create') }}" class="btn btn-primary glow">
                    <i class="ti ti-plus me-1"></i>
                    {{ __('admin.Add New') }}
                  </a>
                </div>
                @endif
                <div class="card-content">
                  <div class="card-body card-dashboard">
                    @if (session()->has('msg'))
                      <div class="alert alert-success alert-dismissible" role="alert">
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    @endif
                    <div class="table-responsive">
                      <table class="table zero-configuration">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>{{ __('admin.Arabic Title') }}</th>
                            <th>{{ __('admin.English Title') }}</th>
                            <th>{{ __('admin.Rolefor') }}</th>
                            @if( $_user->ableToAny(['documentation.show', 'documentation.edit', 'documentation.update', 'documentation.destroy'], $permissions) )
                              <th> {{ __('admin.Actions') }} </th>
                            @endif
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($docs as $doc)
                            <tr>
                              <td>{{ $doc->arr }}</td>
                              <td>{{ $doc->menu_ar }}</td>
                              <td>{{ $doc->menu_en }}</td>
                              <td>{{ __('admin.role'. ucFirst($doc->role)) }}</td>
                              @if( $_user->ableToAny(['documentation.show', 'documentation.edit', 'documentation.update', 'documentation.destroy'], $permissions) )
                              <td>
                                @if( $_user->ableTo('documentation.show', $permissions) )
                                <a title="View" href="/dashboard/documentation/{{ $doc->id }}"
                                  class="btn btn-icon rounded-circle btn-label-primary">
                                  <i class="ti ti-eye"></i>
                                </a>
                                @endif
                                @if( $_user->ableToAny(['documentation.edit', 'documentation.update'], $permissions) )
                                  <a title="Edit" href="{{ route('documentation.edit', ['documentation' => $doc->id]) }}"
                                    class="btn btn-icon rounded-circle btn-label-info">
                                    <i class="ti ti-edit text-info"></i>
                                  </a>
                                @endif
                                @if( $_user->ableTo('documentation.destroy', $permissions) )
                                  <button id='deleteAction' data-id='{{ $doc->id }}'
                                    data-url="{{ route('documentation.destroy', ['documentation' => $doc->id]) }}"
                                    class="btn btn-icon rounded-circle btn-label-danger">
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
  @else
    <script type="text/javascript">
      $(document).ready(function() {
        $('.zero-configuration').DataTable({
          "language": {
            "sEmptyTable": "No data available in table",
            "sLoadingRecords": "Loading...",
            "sProcessing": "Processing...",
            "sLengthMenu": "Show _MENU_ entries",
            "sZeroRecords": "No matching records found",
            "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
            "sInfoEmpty": "Showing 0 to 0 of 0 entries",
            "sInfoFiltered": "(filtered from _MAX_ total entries)",
            "sInfoPostFix": "",
            "sSearch": "Search:",
            "sUrl": "",
            "oPaginate": {
              "sFirst": "First",
              "sPrevious": "Previous",
              "sNext": "Next",
              "sLast": "Last"
            },
            "oAria": {
              "sSortAscending": ": activate to sort column ascending",
              "sSortDescending": ": activate to sort column descending"
            }
          }
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
