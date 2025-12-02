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
                            <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>
                              {{ $cycle->title }}
                              ({{ __('cycles.Start') }} {{ $cycle->start->format('d-m-Y') ?? '-' }})
                              -
                              {{ optional($cycle->organization)->name }} </option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="col-12 col-md-6 mt-4 mt-md-0">
                      <div class="d-flex align-items-center gap-4">
                        <button class="btn btn-sm btn-label-secondary"
                          onclick="window.location.href='/dashboard/cycles/{{ $id }}'">
                          <i class="ti ti-circles"></i> <span class="align-middle mx-2">{{ __('cycles.steps') }}</span>
                        </button>
                        <button class="btn btn-sm btn-label-info" onclick="DoChange(1)">
                          <i class="ti ti-edit text-info"></i> <span
                            class="align-middle mx-2">{{ __('cycles.Edit') }}</span>
                        </button>
                        <button class="btn btn-sm btn-label-success" onclick="DoChange(2)">
                          <i class="ti ti-plus"></i> <span class="align-middle mx-2">{{ __('cycles.New') }}</span>
                        </button>
                        <button class="btn btn-sm btn-label-danger" onclick="DoChange(3)">
                          <i class="ti ti-trash text-danger"></i> <span
                            class="align-middle mx-2">{{ __('cycles.Delete') }}</span>
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
              <div class="card-header d-flex justify-content-between align-items-center w-100">
                <h4 class="card-title">
                  <i class="ti ti-user ti-28px"></i>
                  {{ __('cycles.Cycle Registered Users') }}
                </h4>
                <button class="btn btn-label-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                  <i class="ti ti-filter me-2"></i>{{ __('cycles.Filter') }}
                </button>
              </div>
              <hr style="margin:-10px 0 5px 0;">
              <div class="card-body">
                <div class="table-responsive">
                  <!-- we get value from Select box Ely fo2 class name stepsExcel -->
                  <div class="d-flex align-items-center gap-4">
                    <div class="media align-items-center col-md-3">
                      <select class="form-select select2 ChangeAllBox" data-placeholder="{{ __('cycles.Select Step') }}">
                        <option value="" selected disabled>{{ __('cycles.Select Step') }}</option>
                        <option value="0">{{ __('cycles.Disapprove') }}</option>
                        @foreach ($steps as $step)
                          <option value="{{ $step->id }}">{{ $step->title }}</option>
                        @endforeach
                      </select>
                    </div>
                    <button type="button"
                      class="btn btn-sm btn-label-primary ExportCycleComp">{{ __('cycles.Export') }}</button>
                  </div>
                  <table class="table mt-3">
                    <thead class="bg-label-light">
                      <tr>
                        <th>
                          <input type="checkbox" class="form-check-input checkAll">
                        </th>
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
                            <div class="form-check d-flex align-items-center gap-1">
                              <input type="checkbox" class="form-check-input chboxcyclesReg" do="{{ $company->id }}"
                              userid="{{ $company->user->id ?? '' }}" value="1" name="chboxcyclesReg[]">
                            </div>
                          </td>
                          <td>
                            <a 
                              class="d-flex align-items-center gap-2"
                              href="/dashboard/companyProfile/{{ $company->id }}"
                            >
                              <img 
                                src="{{ $company->logo ?? '/app-assets/images/logo_placeholder.png' }}"
                                onerror="this.src='/app-assets/images/logo_placeholder.png'"
                                alt="avatar" height="40" width="40"
                                class="rounded-circle border shadow-xs"
                              >
                              <span>{{ $company->{'name_' . $l} }}</span>
                            </a>
                          </td>
                          <td>
                           
                            <a class="d-flex align-items-center gap-2" href="/dashboard/users/{{ $company->user->id ?? '0'  }}/edit">
                              <img 
                                src="/191014/{{ $company->user->img_url ?? '/icons/account.svg' }}"
                                onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                                alt="avatar" 
                                class="rounded-circle border shadow-xs" 
                                height="40" 
                                width="40"
                              >
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold">
                                  {{ $company->user->{'first_name_' . $l} ?? '' }}
                                  {{ $company->user->{'last_name_' . $l} ?? '' }}
                                </h6>
                                <span class="font-small-2 text-muted fst-italic">
                                  {{ $company->user->{'title_' . $l} ?? '' }}
                                </span>
                              </div>
                            </a>
                          </td>
                          <td>
                            {{ $company->user->phone ?? ''  }}
                          </td>
                          <td>
                            {{ $company->user->email ?? ''  }}
                          </td>
                          <td>
                            <div class="d-flex align-items-center gap-1">
                              <select id="cstep" class="select form-control"
                                onchange="changestep({{ $company->id }},this.options[this.selectedIndex].value)">
                                <option value="0">{{ __('cycles.Disapprove') }}</option>
                                @foreach (\App\Step::where('cycle_id', $company->cycle)->orderBy('arr')->where('stype', '!=', '7')->get() as $step)
                                  <option value="{{ $step->id }}"
                                    {{ $step->id == $company->step ? 'selected' : '' }}>
                                    {{ $step->title }}</option>
                                @endforeach
                              </select>
                              <form action= "{{ Route('company.addCompanyAlumni', $company->id) }}" method="POST"
                                enctype="multipart/form-data" autocomplete="off">
                                {!! csrf_field() !!}
                              <button type="submit" class="btn btn-sm btn-label-primary" 
                                >{{ __('admin.Almuni') }}
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
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->

  <!-- Step Modal -->
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
              <label for="bs-rangepicker-basic" class="form-label">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
              <input type="text" id="bs-rangepicker-basic" class="form-control" name="date_range" />
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

  <!-- Filter Modal -->
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content bg-label-secondary p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="modal-title fw-semibold mb-0 mt-2" id="filterModalLabel">
            <i class="ti ti-filter ti-24px me-2"></i> {{ __('cycles.Filter_Startups') }}
          </h4>
          <button type="button" class="btn btn-xs btn-icon btn-outline-primary rounded" data-bs-dismiss="modal" aria-label="Close">
            <i class="ti ti-x ti-16px"></i>
          </button>
        </div>
        <form class="bg-label-secondary" method="GET" action="{{ route('cycles.users_manage', $id) }}">
          <div class="card">
            <div class="card-body">
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Step') }}</label>
                <select name="step_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @foreach ($steps as $step)
                    <option value="{{ $step->id }}" {{ $step->id == request('step_id') ? 'selected' : '' }}>
                      {{ $step->title }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Country') }}</label>
                <select name="country_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @php
                    $countries = App\Country::all();
                  @endphp
                  @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                      {{ $country->id == request('country_id') ? 'selected' : '' }}>{{ $country->{'name_' . $l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Sector') }}</label>
                <select name="sector_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @php
                    $sectors = App\Sector::all();
                  @endphp
                  @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}"
                      {{ $sector->id == request('sector_id') ? 'selected' : '' }}>{{ $sector->{'name_' . $l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Stage') }}</label>
                <select name="Dstage_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @php
                    $stages = App\Stage::all();
                  @endphp
                  @foreach ($stages as $stage)
                    <option value="{{ $stage->id }}"
                      {{ $stage->id == request('Dstage_id') ? 'selected' : '' }}>{{ $stage->{'name_' . $l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Funding_Stage') }}</label>
                <select class="form-select select2" name="funding_stage" data-placeholder="{{ __('cycles.Select_Funding_Stage') }}">
                  <option value="">{{ __('cycles.All') }}</option>
                  <option value="Pre_seed_funding_stage"
                    {{ request('funding_stage') == 'Pre_seed_funding_stage' ? 'selected' : '' }}>
                    {{ __('admin.Pre_seed_funding_stage') }}
                  </option>
                  <option value="Seed_funding_stage "
                    {{ request('funding_stage') == 'Seed_funding_stage' ? 'selected' : '' }}>
                    {{ __('admin.Seed_funding_stage') }}
                  </option>
                  <option value="Series_A_funding"
                    {{ request('funding_stage') == 'Series_A_funding' ? 'selected' : '' }}>
                    {{ __('admin.Series_A_funding') }}
                  </option>
                  <option value="Series_B_funding"
                    {{ request('funding_stage') == 'Series_B_funding' ? 'selected' : '' }}>
                    {{ __('admin.Series_B_funding') }}
                  </option>
                  <option value="Series_C_funding"
                    {{ request('funding_stage') == 'Series_C_funding' ? 'selected' : '' }}>
                    {{ __('admin.Series_C_funding') }}
                  </option>
                </select>
              </div>
              <div class="form-group mb-4">
                <label for="targeted_market" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Targeted_Market') }}</label>
                <select
                  class="form-select select2"
                  id="targeted_market"
                  name="targeted_market[]"
                  multiple
                  autocomplete="targeted_market"
                >
                  @php
                    $countries = App\Country::all();
                  @endphp
                  @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                      {{ $country->id == request('country_id') ? 'selected' : '' }}>
                      {{$country->emoji . ' ' . $country->{'name_'.$l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Business_Type') }}</label>
                <select class="form-select select2" name="BType">
                  <option value="0">{{ __('admin.All') }}</option>
                  <option value="B2C" {{ request('BType') == 'B2C' ? 'selected' : '' }}>
                    B2C</option>
                  <option value="B2B" {{ request('BType') == 'B2B' ? 'selected' : '' }}>
                    B2B</option>
                  <option value="B2B2C" {{ request('BType') == 'B2B2C' ? 'selected' : '' }}>
                    B2B2C</option>
                  <option value="C2C" {{ request('BType') == 'C2C' ? 'selected' : '' }}>
                    C2C</option>
                  <option value="B2G" {{ request('BType') == 'B2G' ? 'selected' : '' }}>
                    B2G</option>
                  <option value="Platform" {{ request('BType') == 'Platform' ? 'selected' : '' }}>
                    Platform</option>
                </select>
              </div>
            </div>
          </div>
           <!-- Footer -->
           <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" 
            onclick="window.location.href='/dashboard/cycles/{{ $id }}/registered'">
              {{ __('cycles.Reset') }}
              <i class="ti ti-circle-x ms-2"></i>
            </button>
            <button type="submit" class="btn btn-primary">
              {{ __('cycles.Apply') }}
              <i class="ti ti-circle-check ms-2"></i>
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
    $(document).ready(function () {
      $('.table').DataTable({
      "responsive": true,
      "searching": true,
      "lengthChange": true,
      "paging": true,
      "bInfo": false,
      "pageLength": 10, // Default selected length is 10
      "lengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "{{ __('admin.All') }}"]],
      "columnDefs": [{
        "orderable": false,
        "targets": [4]
      }],
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
            // alert(response.ddddd);
            console.log(response);
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
