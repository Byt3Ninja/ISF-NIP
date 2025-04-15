@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          <div class="card-header mb-4">
            <h4 class="card-title">{{ __('cycles.Portal Cycles Users') }}</h4>
          </div>
          <div class="row match-height">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card mb-4 py-4">
                <div class="card-body">
                  <label class="form-label fs-5 mb-2">{{ __('cycles.Select cycle from list') }}</label>
                  <fieldset class="d-flex flex-column flex-md-row gap-3">
                    <select id="cstep" class="select2 form-control"
                      style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 60%;"
                      onchange="window.open('/dashboard/mentorship/assign/'+this.options[this.selectedIndex].value,'_self');">
                      @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>{{ $cycle->title }}
                          {{ __('cycles.from') }} {{ $cycle->start->format('d-m-Y') }} {{ __('cycles.to') }}
                          {{ $cycle->end->format('d-m-Y') }} - {{ optional($cycle->organization)->name }}</option>
                      @endforeach
                      @if (!$id)
                        <option> {{ __('cycles.No records saved') }} - ({{ __('cycles.Manage Cycle') }}).</option>
                      @endif
                    </select>
                    <div class="input-group-append">
                      <a class="btn btn-label-success" href="/dashboard/cycles/{{ $id }}">
                        <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Manage Cycle') }}</span>
                      </a>
                      <a class="btn btn-label-primary" href="/dashboard/mentorship/view/{{ $id }}">
                        <i class="ti ti-settings"></i> <span class="mx-1">{{ __('cycles.Manage sessions') }}</span>
                      </a>
                      <a class="btn btn-label-dark" href="/dashboard/mentorship/{{ $id }}/surveys">
                        <i class="ti ti-question-mark"></i> <span class="mx-1">{{ __('cycles.Manage Surveys') }}</span>
                      </a>
                    </div>
                  </fieldset>
                  <br />
                </div>
              </div>
            </div>
          </div>
        </section>

        <section>
          <div class="row">
            <div class="col-md-12">
              <div class="card card-body">
                <div class="border-bottom">
                  <h4 class="card-title">{{ __('cycles.Mentors') }}</h4>
                </div>

                <br>
                @php
                    $specializations = [];
                    $TMentors = App\User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
                    foreach ($TMentors as $mentor) {
                            $specialization = $mentor->bio->{'specialization_' . $l} ?? null;
                            if ($specialization && !in_array($specialization, $specializations)) {
                                $specializations[] = $specialization;
                            }
                    }
                @endphp
                
                <form id="filter-form" method="GET" action="/dashboard/mentorship/assign/{{ $id }}">
                  <div class="row">
                    <div class="col-8">
                      <div class="row">
                        <div class="col-auto col-md-4">
                          <select name="specialization" class="form-select select2" id="specializationFilter" data-placeholder="{{ __('cycles.Specialization') }}">
                              <option value="">{{ __('cycles.Specialization') }}</option>
                              @php
                                  $specializations = array_unique($specializations);
                              @endphp
                            <option value="0">All</option>
                              @foreach ($specializations as $specialization)
                                  <option value="{{ $specialization }}" {{ request('specialization') == $specialization ? 'selected' : '' }}>{{ $specialization }}</option>
                              @endforeach
                          </select>    
                        </div>
                        <div class="col-auto col-md-4">
                          <select name="rate" class="form-select select2">
                            <option value="0" {{ request('rate') == 0 ? 'selected' : '' }} >All</option>
                            <option value="1" {{ request('rate') == 1 ? 'selected' : '' }} > 1 </option>
                            <option value="2" {{ request('rate') == 2 ? 'selected' : '' }} > 2 </option>
                            <option value="3" {{ request('rate') == 3 ? 'selected' : '' }} > 3 </option>
                            <option value="4" {{ request('rate') == 4 ? 'selected' : '' }} > 4 </option>
                            <option value="5" {{ request('rate') == 5 ? 'selected' : '' }} > 5 </option>
                          </select>
                        </div>
                        <div class="col-auto col-md-4">
                          <select name="assigning" class="form-select select2">
                            <option value="0" {{ request('assigning') == 0 ? 'selected' : '' }} >All</option>
                            <option value="checked" {{ request('assigning') == 'checked' ? 'selected' : '' }} > {{ __('admin.checked') }} </option>
                            <option value="Notchecked" {{ request('assigning') == 'Notchecked' ? 'selected' : '' }} > {{ __('admin.Notchecked') }} </option>
                          </select>  
                        </div>
                      </div>
                    </div>
                  </div>
                </form>

                <hr>

                <form action= "/dashboard/mentorship/assign/{{ $id }}" method="POST"
                  enctype="multipart/form-data">
                  {!! csrf_field() !!}
                  <input type="hidden" name="cycle" value="{{ $id }}">
                  <div class="table-responsive">
                    <table class="table" id="mentorstable">
                      <thead class="bg-label-light">
                        <tr>
                          <th class="text-nowrap fw-bold">{{ __('cycles.Name/Title') }}</th>
                          <th class="text-nowrap fw-bold">{{ __('cycles.Bio') }}</th>
                          <th class="text-nowrap fw-bold">{{ __('cycles.Specialization') }}</th>
                          <th class="text-nowrap fw-bold">{{ __('admin.Linkedin') }}</th>
                          <th class="text-nowrap fw-bold">{{ __('cycles.Rate') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($mentors as $mentor)
                          <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <input type='checkbox' id="checkbox{{ $mentor->id }}" class="checkbox-input"
                                  {{ in_array($mentor->id, $cList) ? 'checked' : '' }} name='users[]'
                                  value="{{ $mentor->id }}">
                                <label for="checkbox{{ $mentor->id }}">
                                  <div class="d-flex align-items-center mx-2" style="height: 40px;">
                                    <a class="">
                                      <img src="{{ $mentor->image_url }}" alt="avatar" class="rounded-circle"
                                        height="40" width="40">
                                    </a>
                                    <div class="mx-2 d-flex flex-column">
                                      <strong class="text-nowrap">{{ $mentor->name }} </strong>
                                      <small class="font-small-1 text-nowrap text-muted">
                                        {{ $mentor->title }}
                                      </small>
                                    </div>
                                  </div>
                                </label>
                              </div>
                            </td>
                            <td>
                              {{ $mentor->bio->{'bio_' . $l} ?? '-' }}
                            </td>
                            <td>
                              {{ $mentor->bio->{'specialization_' . $l} ?? '-' }}
                            </td>
                            <td>
                              @if ($mentor->bio->linkedin ?? 0)
                                <a title="Linkedin URL" target="_blank" href="{{ $mentor->bio->linkedin }}"
                                  class="btn btn-icon rounded-circle btn-label-primary">
                                  <i class="fa-brands fa-linkedin-in"></i>
                                </a>
                              @endif
                            </td>
                            <td>
                              <span>{!! str_repeat('<i class="ti ti-star" style="color: #fc960f;"></i>', $mentor->mrating()) !!}</span>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <div class="d-flex justify-content-between mt-6 card-body">
                    <div class="checkbox">
                      <input type='checkbox' id="checkAll" class="checkbox-input">
                      <label for="checkAll">
                        {{ __('cycles.Select All') }}
                      </label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                      <i class="ti ti-device-floppy"></i>
                      <span class="mx-2">{{ __('cycles.Save') }}</span>
                    </button>
                  </div>
                </form>
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

  <script type="text/javascript">
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": true,
          "targets": [3]
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

      $('#checkAll').click(function() {
        $("input[type=checkbox]").prop('checked', $(this).prop('checked'));
      });
    });
  </script>

  <script>
    $('.date').datetimepicker({
      timepicker: true,
      format: 'Y-m-d H:i'
    });

    $('#filter-form .form-select').on('change', function() {
      $('#filter-form').submit();
    });
  </script>
@stop
