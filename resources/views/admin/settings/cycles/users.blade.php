@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
  <style>
    .drp-calendar {
      background-color: #fff;
      margin-top: -3rem !important;
    }
    @media screen and (max-width: 768px) {
      #mob {
        margin-top: 10px;
        justify-content: flex-start;
      }
    }
  </style>
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
              <button type="button" class="btn-label-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-label-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @php
            $com = \App\Company::where('cycle', $id)->get();
          @endphp
          @if ($com->count() == 0)
            <input type="hidden" class="cyclesExcel" value="0">
          @else
            <input type="hidden" class="cyclesExcel" value="{{ request()->route('id') }}">
          @endif

          <div class="row match-height">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">
                    <i class="ti ti-circles ti-28px"></i>
                    <span class="mx-2">{{ __('cycles.Portal Cycles') }}</span>
                  </h4>
                </div>
                <hr style="margin:-10px 0 5px 0;">
                <div class="card-body">
                  <div class="row d-flex align-items-end">
                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label fs-5 fw-semibold mb-1">{{ __('cycles.Select cycle from list') }}</label>
                        <select id="ccycle" class="form-select select2"
                          onchange="window.open('/dashboard/cycles/'+this.options[this.selectedIndex].value+'/registered','_self');">
                          @foreach ($cycles as $cycle)
                            <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>{{ $cycle->title }}
                              ({{ __('cycles.Start') }} {{ $cycle->start->format('d-m') }}) -
                              {{ optional($cycle->organization)->name }} </option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-12 col-md-6 mt-4 mt-md-0">
                      <div class="d-flex align-items-center gap-4">
                        <button class="btn btn-sm btn-label-secondary"
                          onclick="window.location.href='/dashboard/cycles/{{ $id }}'">
                          <i class="ti ti-circles"></i> <span
                            class="align-middle mx-2">{{ __('cycles.steps') }}</span>
                        </button>
                        <button class="btn btn-sm btn-label-info" onclick="DoChange(1)">
                          <i class="ti ti-edit text-info"></i> <span class="align-middle mx-2">{{ __('cycles.Edit') }}</span>
                        </button>
                        <button class="btn btn-sm btn-label-success" onclick="DoChange(2)">
                          <i class="ti ti-plus"></i> <span class="align-middle mx-2">{{ __('cycles.New') }}</span>
                        </button>
                        <button class="btn btn-sm btn-label-danger" onclick="DoChange(3)">
                          <i class="ti ti-trash text-danger"></i> <span class="align-middle mx-2">{{ __('cycles.Delete') }}</span>
                        </button>
                      </div>
                    </div>
                  </div>                  
                </div>
              </div>
            </div>
          </div>
        </section>
        <section class="row mt-6">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title primary">
                  <i class="ti ti-user ti-28px"></i>
                  <span class="mx-2">{{ __('cycles.Cycle Registered Users') }}</span>
                </h4>
              </div>
              <hr style="margin:-10px 0 5px 0;">
              <div class="card-body">
                <div class="table-responsive">
                  <!-- we get value from Select box Ely fo2 class name stepsExcel -->
                  <div class="d-flex align-items-center gap-4">
                    <div class="media align-items-center col-md-3">
                      <select class="form-select select2 ChangeAllBox">
                        <option value="0">{{ __('cycles.Disapprove') }}</option>
                        @foreach ($steps as $step)
                        <option value="{{ $step->id }}">{{ $step->title }}</option>
                        @endforeach
                      </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-label-primary ExportCycleComp">{{ __('cycles.Export') }}</button>
                  </div>
                  <table class="table mt-3">
                    <thead class="bg-label-light">
                      <tr>
                        <th><input type="checkbox" class="checkAll"></th>
                        <th>{{ __('cycles.Company') }}</th>
                        <th>{{ __('cycles.User/Title') }}</th>
                        <th>{{ __('cycles.Mobile') }}</th>
                        <th>{{ __('cycles.E-Mail') }}</th>
                        <th>{{ __('cycles.Current Step') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                    {{-- @php --}}
                    {{--  $companies = \App\Company::where('cycle',$id)->get(); --}}
                    {{-- @endphp --}}
                      @foreach ($companies as $company)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center gap-1">
                              <input type="checkbox" class="chboxcyclesReg" do="{{ $company->id }}"
                                userid="{{ $company->user->id }}" value="1" name="chboxcyclesReg[]">
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <a class="media-left mr-50" href="/dashboard/companyProfile/{{ $company->id }}">
                                <img src="/191014/{{ $company->logo ?? '../app-assets/images/logo_placeholder.png' }}"
                                  alt="avatar" class="rounded-circle" height="40" width="40">
                              </a>
                              <a href="/dashboard/companyProfile/{{ $company->id }}">
                                <div class="d-flex align-items-center gap-1">
                                  {{ $company->{'name_' . $l} }}
                                </div>
                              </a>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <a class="media-left mr-50" href="/dashboard/users/{{ $company->user->id }}/edit">
                                <img src="/191014/{{ $company->user->img_url ?? '../images/placeholder.jpg' }}"
                                  alt="avatar" class="rounded-circle" height="40" width="40">
                              </a>
                              <a href="/dashboard/users/{{ $company->user->id }}/edit">
                                <div class="media-body">
                                  <h6 class="media-heading mb-0">{{ $company->user->{'first_name_' . $l} }}
                                    {{ $company->user->{'last_name_' . $l} }}</h6>
                                  <span class="font-small-2">{{ $company->user->{'title_' . $l} }}</span>
                                </div>
                              </a>
                            </div>
                          </td>
                          <td>
                            {{ $company->user->phone }}
                          </td>
                          <td>
                            {{ $company->user->email }}
                          </td>
                          <td>
                            <div class="d-flex align-items-center gap-1">
                              <select id="cstep" class="select form-control"
                                onchange="changestep({{ $company->id }},this.options[this.selectedIndex].value)">
                                <option value="0">{{ __('cycles.Disapprove') }}</option>
                                @foreach (\App\Step::where('cycle_id', $company->cycle)->orderBy('arr')->get() as $step)
                                  <option value="{{ $step->id }}" {{ $step->id == $company->step ? 'selected' : '' }}>
                                    {{ $step->title }}</option>
                                @endforeach
                              </select>
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
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
  <div class="modal fade" id="cycleForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <form class="form" action= "{{ URL::to('dashboard/cycles/modify') }}" method="POST"
          enctype="multipart/form-data" autocomplete="off">
          {!! csrf_field() !!}
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Cycle data') }}
            </h4>
          </div>
          <div class="modal-body">
            <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Cycle title') }}: </label>
              <input type="hidden" name="type" id="type">
              <input type="hidden" name="cycleid" id="cycleid">
              <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}"
                class="form-control" required>
            </div>
            <div class="col-12 mb-4">
              <label for="bs-rangepicker-basic" class="form-label">{{ __('cycles.Valid_from_to') }}:</label>
              <input type="text" id="bs-rangepicker-basic" class="form-control" />
            </div>
            {{-- <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Valid from') }}: </label>
              <input id='start' name='start' type="text" placeholder="{{ now()->format('Y-m-d') }}"
                class="form-control date" required>
            </div>
            <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Valid to') }}: </label>
              <input id='end' name='end' type="text"
                placeholder="{{ now()->addDays(15)->format('Y-m-d') }}" class="form-control date" required>
            </div> --}}
            <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Description') }}: </label>
              <textarea id='description' name='description' placeholder="{{ __('cycles.Description') }}" class="form-control"></textarea>
            </div>
            <div class="form-group mb-4 custom-switch">
              <span>{{ __('settings.Private') }}</span>&nbsp;
              <label class="form-label" for="customSwitch3"></label>
              <input name="private" type="checkbox" class="custom-control-input" id="customSwitch3">
              <a id="cyclelink" target="_blank"></a>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti ti-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
            </button>
            <button type="submit" value="submit" class="btn btn-sm btn-primary ml-1">
              <i class="ti ti-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop

@section('pagescripts')
<script src="/app-assets/js/1.js"></script>
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script>
  var bsRangePickerBasic = $('#bs-rangepicker-basic');
  if (bsRangePickerBasic.length) {
    bsRangePickerBasic.daterangepicker({
      opens: isRtl ? 'left' : 'right',
    });
  }
</script>

  <script type="text/javascript">
   document.addEventListener('DOMContentLoaded', () => {
      console.log('loaded');
    });

    $(document).ready(function() {
      $('.table').DataTable({
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
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script>
    function DoChange(set) {
      var cycle = $('#ccycle').find(":selected").val();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      switch (set) {
        case 1:
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/modify",
            data: {
              "cycleid": cycle,
            },
            success: function(response) {
              $('#title').val(response.title);
              $('#description').val(response.description);
              $('#cycleid').val(cycle);
              $('#type').val(1);
              $('#start').val(response.start);
              $('#end').val(response.end);
              if (response.private) {
                $('#customSwitch3').attr('checked', 'checked');
                $('#cyclelink').html('{{ __('cycles.Join URL') }}');
                $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
              }
              $('#cycleForm').modal('show');
            },
            error: function(error) {
              console.log(error);
              alert("Error Occurred, Try again!");
            }
          });
          break;
        case 2:
          $('#title').val('');
          $('#description').val('');
          $('#cycleid').val('');
          $('#type').val(2);
          $('#start').val('');
          $('#end').val('');
          $('#customSwitch3').removeAttr('checked');
          $('#cycleForm').modal('show');
          break;
        case 3:
          if (confirm('{{ __('cycles.sure delete cycle') }}')) {
            $.ajax({
              type: "POST",
              url: "/dashboard/cycles/modify",
              data: {
                "cycleid": cycle,
                "type": 3,
              },
              success: function(response) {
                console.log(response);
                location.href = '/dashboard/cycles';
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
          break;
        case 4:
          let step = $('#StepSelect').find(":selected").val();
          let steptxt = $('#StepSelect').find(":selected").text();
          let menu = $('#menu' + step);
          $('#menu1,#menu2,#menu5').hide();
          if (menu !== null) {
            menu.show();
          }
          $('#stype').val(step);
          $('#formtitle').text(steptxt);
          $('#stepForm').modal('show');
          break;
      }
    }

    function changestep(company, step) {
      if (confirm("{{ __('cycles.sure change step') }}")) {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $.ajax({
          type: "POST",
          url: "/dashboard/screening/changestep",
          data: {
            "company": company,
            "step": step,
          },
          success: function(response) {
            // console.log(response);
            location.href = '/dashboard/cycles/{{ $id }}/registered';
          },
          error: function(error) {
            console.log("Error Occurred, arrange failed");
          }
        });
      } else {
        location.reload();
      }
    }

    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
      exportNotSuccess: "{{ __('cycles.export_Notsuccess') }}",
    };
  </script>

@stop
