@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible mb-2" role="alert">
      {{ __('admin.' . session()->get('message')) }}
      <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    @endif
    @if (session()->has('message2'))
    <div class="alert alert-danger alert-dismissible mb-6" role="alert">
      {{ __('admin.' . session()->get('message2')) }}
      <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    @endif

    <!-- Navigation Tabs -->
    <nav class="nav-align-top mb-6">
      @php
        $tabs = [
          ['title' => __('admin.training_sessions'), 'url' => 'dashboard/training/sessions', 'icon' => 'book'],
          ['title' => __('admin.training_courses'), 'url' => 'dashboard/training/all', 'icon' => 'certificate'],
          ['title' => __('admin.training_results'), 'url' => 'dashboard/training/results', 'icon' => 'report-analytics'],
        ]
      @endphp
      <ul class="nav nav-pills">
        @foreach($tabs as $tab)
          <li class="nav-item">
            <a href="/{{ $tab['url'] }}" class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}">
              <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
              {{ $tab['title'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>
    <!--/ Navigation Tabs -->

    @if (count($sessions) > 0)
    <div class="card">
      <div class="card-header border-bottom">
        <h4 class="card-title">{{ __('cycles.Portal training results') }}</h4>
      </div>
      <div class="card-body">
        <div class="row my-6">
          <div class="col-12 col-md-6">
            <div class="form-group w-100">
              <label class="form-label">{{ __('cycles.Select training from list') }}</label>
              <select id="cstep" class="form-select select2 w-100 cyclesExcel"
                onchange="window.open('/dashboard/training/results/'+this.options[this.selectedIndex].value,'_self');">
                @foreach ($sessions as $step)
                <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }} -
                  {{ $step->step->title }} ({{ __('cycles.Cycle') }} "{{ $step->step->cycle->title }}" -
                  {{ __('cycles.Start') }} {{ $step->step->from->format('d-m-Y') ?? '-' }}) -
                  {{ optional(optional(optional($step->step)->cycle)->organization)->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-12 col-md-6">
             <div class="d-flex align-items-end gap-4 h-100 mt-3 mt-md-0">
              <a href="javascript:void(0)" class="btn btn-label-secondary"
                onclick="window.location.href='/dashboard/cycles/{{ $csession->step->cycle->id }}/registered'">
                <i class="ti ti-user me-2"></i> <span class="align-middle">{{ __('cycles.Users') }}</span>
              </a>
              <a class="btn btn-label-primary" href="/dashboard/cycles/{{ $csession->step->cycle->id }}">
                <i class="ti ti-pencil me-2"></i> <span class="align-middle ml-25">{{ __('cycles.Cycle') }}</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="row mt-6">
      <div class="col-xl-12 col-md-12 col-sm-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">
              {{ $step->title }} - {{ $csession->step->title }}
            </h4>
            <small class="text-muted">
              <i class="ti ti-calendar-event me-2"></i>
              {{ __('cycles.from') }} {{ $csession->step->from->format('d-m-Y') ?? '-' }}
              {{ __('cycles.to') }} {{ $csession->step->to->format('d-m-Y') ?? '-' }}
            </small>
            <p>{{ $csession->step->description }}</p>
          </div>
          <hr style="margin:-10px 0 5px 0;">
          <div class="card-body">

            <div class="table-responsive">
              <!-- we get value from Select box Ely fo2 class name stepsExcel -->
              <input type="hidden" class="step_id" value="{{ $csession->step_id }}">
              <table class="table">
                <div class="row border-bottom pb-3">
                  <div class="col-12 col-md-6">
                    <div class="row">
                      <div class="col-12 col-md-8">
                        <select class="form-select select2 ChangeAllBoxTR">
                          <option value="0">Disapprove</option>
                          @foreach ($steps as $step)
                          <option value="{{ $step->id }}">{{ $step->title }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-12 col-md-4">
                        <a href="javascript:void(0)" class="btn btn-primary mt-3 mt-md-0 ExportExcelTresult">
                          <i class="ti ti-brand-abstract me-2"></i>
                          {{ __('cycles.Export') }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row align-items-center my-6 border-bottom pb-3">
                  <form id="status-form" method="GET" action="{{ route('trainings.results_view', $csession->id) }}"
                    class="d-flex flex-wrap gap-3">
                    <div class="col-md-4">
                      <label for="company_id" class="form-label">{{ __('admin.Select Company') }}</label>
                      <select class="form-select select2" id="company_id" name="company_id" onchange="updateUrl()" data-placeholder="Select Company">
                        <option value="">{{ __('admin.Select Company') }}</option>
                        @php
                        $companiesselect = \App\Company::where('step', $csession->step_id)->get();
                        @endphp
                        @foreach ($companiesselect as $company)
                        <option value="{{ $company->id }}" {{ $company->id == request('company_id') ? 'selected' : ''
                          }}>
                          {{ $company->{'name_' . $l} }}
                        </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="status" class="form-label">{{ __('admin.Status') }}</label>
                      <select class="form-select select2" id="status" name="status" onchange="updateUrl()" data-placeholder="{{ __('admin.Status') }}">
                        <option value="">{{ __('admin.Status') }}</option>
                        <option value="completed" {{ 'completed'==request('status') ? 'selected' : '' }}>
                          {{ __('admin.Completed') }}
                        </option>
                        <option value="Notcompleted" {{ 'Notcompleted'==request('status') ? 'selected' : '' }}>
                          {{ __('admin.NotStarted') }}
                        </option>
                      </select>
                    </div>
                  </form>

                  <script>
                    function updateUrl() {
                      const companyId = document.getElementById('company_id').value;
                      const status = document.getElementById('status').value;
                      const url = new URL(window.location.href);
                      url.searchParams.set('company_id', companyId);
                      url.searchParams.set('status', status);
                      window.location.href = url.toString();
                    }
                  </script>
                </div>
                <thead class="bg-label-light">
                  <tr>
                    <th><input type="checkbox" class="checkAllTR"></th>
                    <th>{{ __('cycles.Company') }}</th>
                    <th>{{ __('cycles.User/Title') }}</th>
                    <th>{{ __('cycles.Completed') }}</th>
                    <th>{{ __('cycles.Current Step') }}</th>
                  </tr>
                </thead>
                <tbody>

                  @php

                  $companies = \App\Company::where('step', $csession->step_id);
                  if(!empty(request('company_id'))){
                  $companies = $companies->where('id', request('company_id'));
                  }
                  $companies = $companies->get();
                  $status = request('status');

                  @endphp
                  @foreach ($companies as $company)
                  @php
                  $cc = \App\TrainingCompleted::where('user_id', $company?->user?->id)
                  ->where('course_id', $csession->training_Address)
                  ->count();
                  $per = $contents ? ($cc / $contents) * 100 : '0';
                  $show = true;
                  if ($status == 'completed' && (int)$per != 100) {
                  $show = false;
                  } elseif ($status == 'notcompleted' && (int)$per == 100) {
                  $show = false;
                  }
                  @endphp
                  @if ($show)
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <input type="checkbox" class="chboxTresult" do="{{ $company->id }}"
                          userid="{{ $company?->user?->id }}" value="1" name="chboxcyclesReg[]">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <a href="/dashboard/companyProfile/{{ $company->id }}"
                          class="w-px-40 h-px-40 rounded-circle bg-label-light border d-flex align-items-center justify-content-center overflow-hidden">
                          @if ($company->logo)
                          <img src="/191014/{{ $company->logo ?? '/app-assets/images/logo_placeholder.png' }}"
                            onerror="this.src='/app-assets/images/logo_placeholder.png'" class="img-fluid w-100 h-100"
                            alt="logo" />
                          @else
                          <i class="ti ti-building text-secondary"></i>
                          @endif
                        </a>
                        <a href="/dashboard/companyProfile/{{ $company->id }}">
                          <div class="d-flex align-items-center gap-1">
                            {{ $company->{'name_' . $l} }}
                          </div>
                        </a>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <a class="w-px-40 h-px-40 rounded-circle border d-flex align-items-center justify-content-center overflow-hidden"
                          href="/dashboard/users/{{ $company?->user?->id ?? '0' }}/edit">
                          @if ($company?->user?->img_url)
                          <img src="/191014/{{ $company?->user?->img_url }}"
                            alt="avatar" class="img-fluid w-100 h-100">
                          @else
                          <img src="/icons/account.svg" class="img-fluid" />
                          @endif
                        </a>
                        <a href="/dashboard/users/{{ $company?->user?->id ?? '0' }}/edit">
                          <div class="text-black d-flex flex-column">
                            <strong>
                              {{ $company?->user?->{'first_name_' . $l} }}
                              {{ $company?->user?->{'last_name_' . $l} }}
                            </strong>
                            <small class="text-muted">{{ $company?->user?->{'title_' . $l} }}</small>
                          </div>
                        </a>
                      </div>
                    </td>

                    <input type="hidden" class="location" value="{{ $csession->training_Address }}">


                    <td>
                      <span class="badge bg-label-{{ $per >= 50 ? 'success' : 'danger' }}">
                        {{ (int) $per }}%
                      </span>

                      {{--
                    <td>
                      <span class="badge bg-label-{{ $per > 50 ? 'success' : 'danger' }}">
                        {{ (int) $per }}
                        %</span>
                    </td> --}}

                    <td>
                      <div class="d-flex align-items-center gap-1">
                        <select id="cstep" class="form-select"
                          onchange="changestep({{ $company->id }},this.options[this.selectedIndex].value)">
                          <option value="0">Disapprove</option>
                          @foreach (\App\Step::where('cycle_id', $company->cycle)->orderBy('arr')->get() as $step)
                          <option value="{{ $step->id }}" {{ $step->id == $company->step ? 'selected' : '' }}>
                            {{ $step->title }}</option>
                          @endforeach
                        </select>
                      </div>
                    </td>
                  </tr>
                  @endif
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
    @else
    <div class="row">
      <div class="col-12">
        <div class="card card-body h-100 d-flex justify-content-center align-items-center">
          <div class="d-flex flex-column align-items-center">
            <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
            <h6 class="text-center text-secondary w-75">
              {{ __('admin.No_Session_Message') }}
            </h6>
            <div class="text-center">
              <a href="/dashboard/cycles" class="btn btn-primary">
                <span class="mx-1">{{ __('admin.Go to Steps') }}</span>
                <i class="ti ti-arrow-{{$l == 'en' ? 'right' : 'left'}} ms-2"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
@stop

@section('pagescripts')
<script src="/app-assets/js/1.js"></script>
<script>
  $(document).on("change", "#status-select", function(e) {
      $("#status-form").submit();
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
    });

    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
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
            location.href = '/dashboard/training/results/{{ $id }}';
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, arrange failed");
          }
        });
      } else {
        location.href = '/dashboard/training/results/{{ $id }}';
      }
    }
</script>
@stop