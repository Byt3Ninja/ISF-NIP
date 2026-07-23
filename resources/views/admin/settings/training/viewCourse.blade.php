@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop
@section('content')

  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-header">
        <h5 class="content-header-title mb-4">{{ __('cycles.all courses') }}</h5>
      </div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
              role="alert">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center"> <i class="ti ti-check"></i> <span>
                  {{ __('admin.' . session()->get('message')) }} </span> </div>
            </div>
          @endif
        </section>
        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header d-flex align-items-center">
                <h4 class="card-title">{{ __('cycles.Training courses') }} ( {{ $course->title }} )</h4>
                @if (isset($course))
                  <a class="btn btn-primary mx-3"
                    href="/dashboard/training/addcontent/{{ $id }}">{{ __('cycles.Add new content') }}</a>
                @endif
              </div>
              <div class="card-body">
                <div class="card-text">
                  <div class="table-responsive">
                    <table class="table mb-0" id="userstable">
                      <thead class="thead-dark">
                        <tr>
                          <th>#</th>
                          <th>{{ __('cycles.Title') }}</th>
                          <th>{{ __('cycles.Created at') }}</th>
                          <th>{{ __('cycles.Added By') }}</th>
                          <th>{{ __('cycles.Edit/Delete') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($contents as $content)
                          <tr>
                            <td>{{ $content->arr }}</td>
                            <td><a title="view" href="/dashboard/training/viewcontent/{{ $content->id }}">
                                {{ $content->title }} </a></td>
                            <td>{{ $content->created_at }}</td>
                            <td>{{ optional($content->user)->{'first_name_' . $l} }}
                              {{ optional($content->user)->{'last_name_' . $l} }}</td>

                            <td>
                              <a title="Edit" href="/dashboard/training/editcontent/{{ $content->id }}"
                                class="btn btn-icon btn-label-info rounded-circle">
                                <i class="ti ti-edit text-info"></i></a>

                              @if (isset($content))
                                <button title="Delete" class="btn btn-label-danger btn-icon rounded-circle"
                                  onclick="deleteme({{ $content->id ?? 0 }},0)">
                                  <span class="align-middle ml-25">
                                    <i class="ti ti-trash text-danger"></i>
                                  </span>
                                </button>
                              @endif


                              <a title="Show" href="/dashboard/training/viewcontent/{{ $content->id }}"
                                class="btn btn-icon btn-label-primary rounded-circle">
                                <i class="ti ti-eye"></i></a>
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
  <!-- END: Content-->


@stop
@section('pagescripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/js/jquery.datetimepicker.full.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#userstable').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [4]
        }, ],
        @if ($l == 'ar')
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
        @endif
      });
    });
  </script>
  <script>
    $('.date').datetimepicker({
      timepicker: true,
      format: 'Y-m-d H:i'
    });

    function edit(id) {
      if (id === 0) {
        $('#id').val('');
        $('#title').val('');
        $('#dsc').val('');
        $('#showimage').html('')
        $('#edittraining').modal('show');
      } else {
        $.ajax({
          type: "GET",
          url: "/dashboard/training/edit/" + id,
          success: function(response) {
            $('#id').val(id);
            $('#title').val(response.title);
            $('#dsc').val(response.dsc);
            if (response.image) {
              $('#showimage').html('<img style="height: 200px;" class="img-responsive" src="/191014/' + response
                .image + '" >');
            }
            $('#edittraining').modal('show');
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      }
    }

    function deleteme(id, type) {
      let url = '/dashboard/training/delcontent/' + id;
      if (type === 1) {
        url = '/dashboard/training/delete/' + id
      }
      let token = '{{ csrf_token() }}';
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
                '_method': 'POST',
                '_token': token
              },
              function(response) {
                Swal.fire(
                  '{{ __('admin.Deleted') }}!',
                  '{{ __('admin.Course Deleted') }}.',
                  'success'
                );
                if (type === 1) {
                  window.location.href = '/dashboard/training/all';
                } else {
                  location.reload();
                }
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
    }
  </script>
@stop
