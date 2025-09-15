@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('content')
<!-- BEGIN: Content-->
    <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
            
<div class="content-body">
                <section id="multiple-column-form">
				    @if(session()->has('message'))
				    <div class="alert alert-success alert-dismissible mb-2" role="alert">
                                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-check"></i>
                                                <span>
                                                    {{ __('admin.'.session()->get('message')) }}
                                                </span>
                                            </div>
                    </div>
					@endif
					@if(session()->has('message2'))
					 <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-error"></i>
                                                <span>
                                                    {{ __('admin.'.session()->get('message2')) }}
                                                </span>
                                            </div>
                     </div>
					 @endif

                </section>
        


        <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title primary">{{__('admin.Display_all_reminder_logs')}}</h4>
                </div>
                <hr style="margin:-10px 0 5px 0;">
                <div class="card-body">

                  <form id="filter-programs-form" method="GET" action="{{ route('organization.ReminderLogs') }}">
                    <div class="row g-3">
                      @if(auth()->user()->hasAnyRole(['Admin']))
                      <div class="col-12 col-md-6 col-lg-4">
                        <select name="organization_id" class="form-select select2"
                          data-placeholder="{{ __('admin.Organization') }}">
                          <option value="">{{ __('admin.Organization') }}</option>
                          @if(auth()->user()->hasAnyRole(['Admin']))
                          <option value="0">{{ __('admin.All') }}</option>
                          @endif
                          @php
                            if(auth()->user()->hasAnyRole(['organizationManager'])) {
                          $organizations = App\Organization::where('id',auth()->user()->organization_id)->get();
                        }else{
                          $organizations = App\Organization::all();
                        }
                          @endphp
                          @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}"
                              {{ $organization->id == request('organization_id') ? 'selected' : '' }}>
                              {{ $organization->{'name_' . $l} }}</option>
                          @endforeach
                        </select>
                      </div>
                      @endif


                      <div class="col-6 col-md-3 col-lg-2">
                        <input class="form-control datepicker" placeholder="{{ __('admin.StartDate') }}" name="date_from"
                          value="{{ request('date_from') }}" />
                      </div>

                      <div class="col-6 col-md-3 col-lg-2">
                        <input class="form-control datepicker" placeholder="{{ __('admin.EndDate') }}" name="date_to"
                          value="{{ request('date_to') }}" />
                      </div>

                      <div class="col-12 col-md-6 col-lg-2">
                        <select name="channel" class="form-select select2" data-placeholder="{{ __('admin.Channel') }}">
                          
                          <option value="">{{ __('admin.All') }}</option>
                          <option value="whatsapp" {{ 'whatsapp' == request('channel') ? 'selected' : '' }}> {{ __('admin.Whatsapp') }}
                          </option>
                          <option value="email" {{ 'email' == request('channel') ? 'selected' : '' }}> {{ __('admin.Email') }}
                          </option>
                          <option value="in-app" {{ 'in-app' == request('channel') ? 'selected' : '' }}> {{ __('admin.in-app') }}
                          </option>
                        </select>
                      </div>

                      <div class="col-12 col-md-6 col-lg-2">
                        <select name="user" class="form-select" data-placeholder="{{ __('admin.All') }}">
                        <option value="">{{ __('admin.All') }}</option>
                          @foreach (\App\User::role(['mentor', 'registered'])->get() as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                {{ $user->{'first_name_' . app()->getLocale()} }} {{ $user->{'last_name_' . app()->getLocale()} }}
                              </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-2">
                        <select name="program" class="form-select select2" data-placeholder="{{ __('admin.Program') }}">
                          
                          <option value="">{{ __('admin.All') }}</option>
                          @foreach(\App\Cycle::WhereIn('id', $ReminderLogs->pluck('cycle_id'))->get() as $program)
                          <option value="{{ $program->id }}" {{ $program->id == request('program') ? 'selected' : '' }}> {{ $program->title }}
                          </option>
                          @endforeach
                        
                        </select>
                      </div>
                      <div class="col-12 col-md-6 col-lg-2">
                          <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> {{ __('admin.Search') }}</button>
                      </div>
                    </div>
                  </form>

                  <div class="table-responsive">
                  <table class="table" id="userstable">
                    <thead class="table-light">
                      <tr>
                        <th>{{ __('admin.User') }}</th>
                        <th>{{ __('admin.Type') }}</th>
                        <th>{{ __('admin.Program') }}</th>
                        <th>{{ __('admin.Channel') }}</th>
                        <th>{{ __('admin.Status') }}</th>
                        <th>{{ __('admin.Date') }}</th>
                        <th>{{ __('cycles.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                        
                      @foreach ($ReminderLogs as $ReminderLog)
                        <tr>
                          <td>
                            <small>{{ $ReminderLog->user->{'first_name_' . app()->getLocale()} }} {{ $ReminderLog->user->{'last_name_' . app()->getLocale()} }}</small>
                          </td>
                          <td>
                            <small>{{ $ReminderLog->type }}</small>
                          </td>
                          <td>
                            <small>{{ $ReminderLog->cycle->title }}</small> 
                          </td>
                          <td>
                            <small>{{ $ReminderLog->channel }}</small>
                          </td>
                          <td>
                            <small>{{ __('admin.' . $ReminderLog->status) }}</small>
                          </td>
                         
                          <td>{{ $ReminderLog->created_at }}</td>
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
@stop
@section('pagescripts')
        <script type="text/javascript">
            $(document).ready(function() {
                $('.table').DataTable({
                    @if ($l=='ar')
                    "language": {
                        "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول", "sLoadingRecords": "جارٍ التحميل...",
                        "sProcessing":   "جارٍ التحميل...", "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
                        "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل", "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل",
                        "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)", "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
                        "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
                        "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}}
                    @endif
                });
            });
        </script>
    <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
@stop