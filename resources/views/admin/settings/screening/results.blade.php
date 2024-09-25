@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
  @php $types = ['onetoall'=>'Assign One to all users','grouptoall'=>'Assign Group to all users','grouptogroup'=>'Assign Group to group of users'] 
  @endphp
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        @if ($cstep)
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
            <div class="row match-height">
              <div class="col-xl-12 col-md-12 col-sm-12">
                <div class="card mb-4">
                  <div class="card-header border-bottom border-light mb-4">
                    <h4 class="card-title">{{ __('cycles.Screening results') }}</h4>
                  </div>
                  <div class="card-body">
                    <label class="form-label fs-5 my-2">{{ __('cycles.Select screening from list') }}</label>
                    <fieldset class="d-flex flex-column flex-md-row gap-3">
                      <select id="cstep" 
                        class="select2 form-control rounded"
                        style="margin-left: {{ $l == 'ar' ? '10px' : '0' }}; margin-right: {{ $l == 'ar' ? '0' : '10px' }}; width: 60%;"
                        onchange="window.open('/dashboard/screening/results/'+this.options[this.selectedIndex].value,'_self');">
                        @foreach ($steps as $step)
                          <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                            ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}"
                            - {{ __('cycles.Start') }} {{ $step->from->format('d-m') }})
                            - {{ optional(optional($step->cycle)->organization)->name }}</option>
                        @endforeach
                      </select>
                      <div class="input-group-append">
                        <button class="btn btn-label-secondary"
                          onclick="window.location.href='/dashboard/cycles/{{ $cstep->cycle_id }}/registered'">
                          <i class="ti ti-user"></i> <span class="mx-1">{{ __('cycles.Users') }}</span>
                        </button>
                        <a class="btn btn-label-primary" href="/dashboard/{{ $id }}/screening">
                          <i class="ti ti-settings"></i> <span class="mx-1">{{ __('cycles.Manage') }}</span>
                        </a>
                        <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id }}">
                          <i class="ti ti-pencil"></i> <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                        </a>
                      </div>
                    </fieldset>
                    <br />
                  </div>
                </div>
              </div>
            </div>
          </section>
          @php
            $clist = [$cstep->id];
            if ($form) {
                $clist = [$form->id, $cstep->id];
            }
            $companies = \App\Company::whereIn('step', $clist)->get();
          @endphp

          @if ($companies->count() == 0)
            <input type="hidden" class="stepsExcel" value="0">
          @else
            <input type="hidden" class="stepsExcel" value="{{ request()->route('id') }}">
          @endif
          <section class="row">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card">
                <div class="card-header border-bottom border-light mb-4">
                  <h4 class="card-title success">{{ $cstep->title }} 
                  </h4>
                  <p>{{ $cstep->description }}</p>
                  <small class="text-muted"> 
                    {{ __('cycles.from') }}
                  {{ $cstep->from->format('d-m') }} {{ __('cycles.to') }} {{ $cstep->to->format('d-m-Y') }}</small>
                </div>
                <div class="card-body">
                  <div class="card-text">
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Cycle') }}</dt>
                      <dd class="col-sm-9">{{ $cstep->cycle->title }} {{ __('cycles.from') }}
                        "{{ $cstep->cycle->start->format('d-m') }} {{ __('cycles.to') }}
                        {{ $cstep->cycle->end->format('d-m-Y') }}
                        "
                      </dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Form title') }}</dt>
                      <dd class="col-sm-9">{!! $form->title ?? '<span class="danger">' . __('cycles.NoForm') . '</span>' !!}</dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Screening type') }}</dt>
                      <dd class="col-sm-9">
                        {{ __('cycles.' . $types[$cstep->data]) }}
                      </dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Total registered') }}</dt>
                      <dd class="col-sm-9">{{ $companies->count() }}</dd>
                    </dl>
                    <dl class="row">
                      <dt class="col-sm-3 text-right">{{ __('cycles.Judges') }}</dt>
                      <dd class="col-sm-9">
                        @foreach ($judges as $judge)
                          <div class="chip mr-1" data-toggle="tooltip" data-placement="bottom" title=""
                            data-original-title="{{ $judge->phone }} {{ $judge->email }}">
                            <div class="d-flex align-items-center">
                              <div class="mx-2">
                                <img class="img-fluid" src="{{ $judge->image_url }}" alt="click to view"
                                  height="20" width="20">
                              </div>
                              <span class="chip-text">{{ $judge->name }}</span>
                            </div>
                          </div>
                        @endforeach

                      </dd>
                    </dl>
                  </div>

                  <div class="card border-light">
                  </div>
                  <div class="table-responsive">
                    <!-- we get value from Select box Ely fo2 class name stepsExcel -->
                    <div class="btn btn-label-info ExportExcelScreesRes">{{ __('cycles.Export') }}</div>

                    <table id="result-list-datatable" class="table mb-0">
                      <thead class="bg-label-secondary">
                        <tr>
                          <th>{{ __('cycles.Company') }}</th>
                          <th>{{ __('cycles.User/Title') }}</th>
                          <th>{{ __('cycles.Judges') }}</th>
                          <th>{{ __('cycles.AVG degree') }}</th>
                          <th>{{ __('cycles.Current Step') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($companies as $company)
                          <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <a class="media-left mr-50" href="/dashboard/companyProfile/{{ $company->id }}">
                                 <div class="avatar">
                                  <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle"
                                  height="40" width="40">
                                 </div>
                                </a>
                                <a href="/dashboard/companyProfile/{{ $company->id }}">
                                  <div class="d-flex align-items-center">
                                    {{ $company->name }}
                                  </div>
                                </a>
                              </div>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                <a class="media-left mr-50" href="/dashboard/users/{{ $company->user->id }}/edit">
                                  <img src="{{ $company->user->image_url }}" alt="avatar" class="rounded-circle"
                                    height="40" width="40">
                                </a>
                                <a href="/dashboard/users/{{ $company->user->id }}/edit">
                                  <div class="media-body">
                                    <h6 class="media-heading mb-0">{{ $company->user->name }} </h6>
                                    <span class="font-small-2">{{ $company->user->title }}</span>
                                  </div>
                                </a>
                              </div>
                            </td>
                            <td>

                              @if ($form->id)
                                <input type="hidden" class="formid" value="{{ $form->id }}">
                                @foreach (\App\User::whereIn('id', $company->judges($company->id, $form->id))->get() as $judger)
                                  <div class="d-flex align-items-center mr-1">
                                    <div class="d-flex align-items-center">
                                      <div class="avatar">
                                        <img class="img-fluid rounded-circle" src="{{ $judger->image_url }}" alt="click to view"
                                          height="20" width="20">
                                      </div>
                                      <span class="chip-text">{{ $judger->name }}</span>
                                    </div>
                                  </div>
                                @endforeach
                              @endif
                            </td>

                            <td>
                              @php($weight = $company->companyWeight($company->id, $form->id ?? '', 0))
                              <span
                                class="badge badge-light-{{ $weight < 25 ? 'danger' : ($weight < 50 ? 'warning' : 'success') }}">
                                {{ $weight }} %</span>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                <select id="cstep" class="select form-control exportcurrentstep"
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
        @else
          <div class="card">
            <div class="card-header border-bottom">
              <h4 class="card-title">{{ __('cycles.Error') }}</h4>
            </div>
            <div class="card-body">
              {{ __('cycles.No records screening from') }} <a href="/dashboard/cycles">{{ __('cycles.Cycles') }}</a>.
            </div>
          </div>
        @endif
      </div>

    </div>
  </div>
  <!-- END: Content-->

@stop
@section('pagescripts')
  <script src="/app-assets/js/scripts/pages/page-users.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
  <script src="/app-assets/js/scripts/modal/components-modal.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [2, 4]
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

    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
      exportNotSuccess: "{{ __('cycles.export_Notsuccess') }}",
    };
  </script>

  <script>
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
            console.log(response);
            location.href = '/dashboard/screening/results/{{ $id }}';
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, arrange failed");
          }
        });
      } else {
        location.href = '/dashboard/screening/results/{{ $id }}';
      }
    }
  </script>
@stop
