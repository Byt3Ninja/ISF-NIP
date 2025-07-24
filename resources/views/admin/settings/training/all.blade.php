@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="multiple-column-form" class="mb-4">
          <h4 class="card-title">{{ __('cycles.all courses') }}</h4>
          @if (session()->has('message'))
            <div 
              class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
              role="alert"
            >
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
        </section>
        <section class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header">

                @if ($_user->hasAnyRole('organizationManager|Mentor|Admin'))
                  <a class="btn btn-primary"
                    href="{{ route('trainings.create') }}">
                    <i class="ti ti-plus me-1"></i>
                    {{ __('admin.Add_new_Courses') }}
                  </a>
                @endif
              </div>
              <div class="card-body">
                <div class="card-text">
                  <div class="table-responsive">
                    <br>

										<form method="GET" action="/dashboard/training/all">
										  <div class="d-flex align-items-center mx-2">
											<div style="display: flex; align-items: center;">
											<button
											  class="btn bg-primary p-3 d-none d-md-flex justify-content-center align-items-center" 
											  style="border-radius: 17px; width: 40px; height: 40px;"
											   type="submit"
											>
											  <i class="ti ti-filter text-white ti-24px"></i>
											</button>
										  </div>
										  <!-- Search Bar -->
										 
							
										  <div class="position-relative me-3 d-none d-md-flex">
											<select name="catTr_id" class="form-control">
											  <option value="0">{{ __('admin.All') }}</option>
											  @php
											  $expertises = App\Expertise::all();
											  @endphp
											  @foreach ($expertises as $expert) 
											  <option value="{{$expert->id}}" {{ $expert->id == request('catTr_id') ? 'selected' : '' }}> {{ $expert->title }}</option>
											@endforeach
											</select>
										  </div>
										  <div class="position-relative me-3 d-none d-md-flex">
											<select name="status" class="form-control">
											  <option value="0">{{ __('admin.All') }}</option>
											  <option value="draft" {{ 'draft' == request('status') ? 'selected' : '' }}>{{ __('admin.Draft') }}</option>
											  <option value="published" {{ 'published' == request('status') ? 'selected' : '' }}>{{ __('admin.Published') }}</option>
											</select>
										  </div>

										</div>
										</form>
										<hr>
                    <table class="table mb-0" id="userstable">
                      <thead class="bg-label-light">
                        <tr>
                          <th>#</th>
                          <th>{{ __('cycles.Title') }}</th>
                          <th>{{ __('cycles.Created at') }}</th>
                          <th>{{ __('cycles.Added By') }}</th>
                          <th>{{ __('admin.quizStatus') }}</th>
                          <th>{{ __('cycles.Tags') }}</th>
                          <th>{{ __('admin.Categories') }}</th>
                          <th>{{ __('admin.CountProgram') }}</th>
                          <th>{{ __('cycles.Edit/Delete') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($courses as $course)
                          @php
                            $trainingTags = App\Training::with('tags')->find($course->id);
                            $tags = $trainingTags->tags;

                            $trainingCategories = App\Training::with('categories')->find($course->id);
                            $Categories = $trainingCategories->categories;
                            $StatusDraft = $l == 'ar' ? 'محفوظة' : 'Draft';
                            $Statuspublished = $l == 'ar' ? 'تم النشر' : 'Published';

                            $contents = App\TrainingContent::where('training_id', $course->id)
                                ->orderBy('arr', 'ASC')
                                ->get();

                            $count_content = $contents->count();

                          @endphp
                          <tr>
                            <td>{{ $course->arr }}</td>
                            <td><a title="view" href="/dashboard/training/viewCourse/{{ $course->id }}">
                                {{ $course->title }} </a></td>
                            <td>{{ $course->created_at }}</td>
                            <td>{{ optional($course->user)->{'first_name_' . $l} }}
                              {{ optional($course->user)->{'last_name_' . $l} }}</td>
                            <td>

                              {{ $course->Status == '0' ? $StatusDraft : $Statuspublished }}
                            </td>
                            <td>
                              @foreach ($tags as $tag)
                                {{ $tag->name }}
                              @endforeach
                            </td>
                            <td>
                              @foreach ($Categories as $Cat)
                                {{ $Cat->title . ' - ' }}
                              @endforeach
                            </td>
                            <th>{{ $count_content }}</th>
                            @if ($_user->hasAnyRole('organizationManager|Mentor|Admin'))
                              <td>
                                <a title="Edit" href="/dashboard/training/EditTR/{{ $course->id }}"
                                  class="btn btn-icon btn-label-info rounded-circle">
                                  <i class="ti ti-edit text-info"></i></a>

                                @if (isset($course))
                                  <button title="Delete" class="btn btn-label-danger btn-icon rounded-circle"
                                    onclick="deleteme({{ $course->id ?? 0 }},1)">
                                    <span class="align-middle ml-25">
                                      <i class="ti ti-trash text-danger"></i>
                                    </span>
                                  </button>
                                @endif


                                <a title="Show" href="/dashboard/training/viewCourse/{{ $course->id }}"
                                  class="btn btn-icon btn-label-primary rounded-circle">
                                  <i class="ti ti-eye"></i></a>
                              </td>
                            @else
                              <td>

                                <a title="Show" href="/dashboard/training/viewCourse/{{ $course->id }}"
                                  class="btn btn-label-primary">{{ __('admin.Enroll') }}</a>
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
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
  <div class="modal fade text-left" id="edittraining" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form method="post" action="/dashboard/training/store" enctype="multipart/form-data">
          <div class="modal-body">
            @csrf
            <input type="hidden" name="id" value="" id="id">
            <div style="text-align: center;" class="form-group mb-3" id="showimage">
            </div>
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Course Title') }}</label>
              <input id="title" name="title" type="text" placeholder="Title" class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Add a description') }}:</label>
              <textarea class="form-control" id="dsc" name='dsc' rows="4"
                placeholder="{{ __('cycles.Write your description here') }}."></textarea>
            </div>
            <div class="form-group mb-3">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Image') }}</label>
              <div class="custom-file">
                <input type="file" class="form-control" name="image" id="image" accept='image/*'>
                <label class="custom-file-label" for="image">{{ __('cycles.Browse') }}</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
            </button>
            <button type="submit" class="btn btn-primary ml-1">
              <span class="d-none d-sm-block">{{ __('Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
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
