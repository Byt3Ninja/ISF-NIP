@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible mb-2" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible mb-2" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          @endif
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header" style="padding-bottom: 15px;">
                  <h4 class="card-title">{{ __('cycles.Portal Final Screening list') }}</h4>
                </div>
                <hr>
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-lg-6">
                      <label>{{ __('cycles.Select step from list to manage') }}</label>
                      <select id="cstep" class="select2 form-select"
                        onchange="window.open('/dashboard/final/results/'+this.options[this.selectedIndex].value,'_self');">
                        @foreach ($steps as $step)
                          <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                            ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}" - {{ __('cycles.Start') }}
                            {{ $step->from->format('d-m-Y') ?? '-' }})
                            - {{ optional(optional($step->cycle)->organization)->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-12 col-lg-6">
                      <div class="h-100 d-flex justify-content-end align-items-end gap-4">
                        <button class="btn btn-label-primary"
                          onclick="window.location.href='/dashboard/{{ $id }}/final'">
                          <i class="ti ti-settings"></i>
                          <span class="mx-1">{{ __('cycles.Manage') }}</span>
                        </button>
                        <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id ?? null }}">
                          <i class="ti ti-pencil"></i>
                          <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <br>
                  <span class="mb-2">{{ __('cycles.Select registered company') }}:</span>
                  <fieldset class="input-group mt-2">
                    <select id="cstep" class="select form-control"
                      onchange="window.open('/dashboard/final/results/{{ $id }}?c='+this.options[this.selectedIndex].value,'_self');">
                      <option value="0">{{ __('cycles.Select Company') }}</option>
                      @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ $c ? ($company->id == $c->id ? 'selected' : '') : '' }}>
                          {{ $company?->{'name_' . $l} }} ({{ __('cycles.Added By') }}
                          {{ $company?->user?->{'first_name_' . $l} }} {{ $company?->user?->{'last_name_' . $l} }})</option>
                      @endforeach
                    </select>
                  </fieldset>
                  <br />
                </div>
              </div>

              @if (!$cstep)
                <div class="card">
                  <div class="card-header border-bottom">
                    <h4 class="card-title">{{ __('cycles.Error') }}</h4>
                  </div>
                  <div class="card-body">
                    {{ __('cycles.No records Add final') }} <a href="/dashboard/cycles">{{ __('cycles.Cycle') }}</a>.
                  </div>
                </div>
              @endif

            </div>
          </div>
        </section>
        <section class="row">
          <div class="col-12">
            @if ($c)
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title secondary">{{ $cstep->title }} <small class="text-muted">
                      {{ __('cycles.from') }} {{ $cstep->from->format('d-m-Y') ?? '-' }} {{ __('cycles.to') }}
                      {{ $cstep->to->format('d-m-Y') ?? '-' }}</small>
                    {{ __('cycles.for') }} :
                    <a href="/dashboard/companyProfile/{{ $c->id }}">
                      <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle" height="40"
                        width="40">
                      {{ $c->{'name_' . $l} }}</a>
                  </h5>
                  <p>{{ $cstep->description }}</p>
                </div>
                <hr style="margin:-20px 0;">
                <div class="card-body">
                  @if (count($cj))
                    <div class="table-responsive">
                      <table class="table  table-hover">
                        <thead class="thead-dark">
                          <tr>
                            <th>#</th>
                            <th>Question</th>
                            @foreach ($cj as $judge)
                              <th>{{ $judge->judge->name }} </th>
                            @endforeach
                          </tr>
                        </thead>
                        <tbody>
                          @foreach (\App\FinalData::where('step_id', $cstep->id)->where('company_id', $c->id)->distinct('field_id')->get(['field_id']) as $key => $question)
                            <tr>
                              <td>{{ $key + 1 }}</td>
                              <td>
                                {{ \App\FinalData::where('step_id', $cstep->id)->where('company_id', $c->id)->where('field_id', $question->field_id)->first(['question'])->question }}
                              </td>
                              @foreach ($cj as $judge)
                                @foreach (\App\FinalData::where('step_id', $cstep->id)->where('judge_id', $judge->judge_id)->where('field_id', $question->field_id)->where('company_id', $c->id)->get(['answer']) as $answer)
                                  <td>
                                    {{ $answer->answer }}
                                  </td>
                                @endforeach
                              @endforeach
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  @else
                    <br>
                    <p>{{ __('cycles.No answers submitted yet') }}.</p>
                  @endif
                </div>
              </div>
            @else
              <div class="card">
                <div class="card-body">
                  {{ __('cycles.Select company from menu above') }}.
                </div>
              </div>
          </div>
          @endif
      </div>
      </section>
    </div>
  </div>
  <!-- END: Content-->
@stop

@section('pagescripts')
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script>
    $(document).ready(function() {
      $('.table').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
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
@stop
