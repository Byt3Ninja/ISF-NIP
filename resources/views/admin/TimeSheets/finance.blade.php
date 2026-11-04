@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <div class="">
    <div class="">
      <div class="content-overlay"></div>
      <div class="content-header mb-4">
        <h4 class="card-title">{{ __('admin.all TimeSheets') }}</h4>
      </div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div 
              class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible" 
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
              
              <div class="card-body">


                <form method="GET" action="{{ route('time_sheets.reports.finance.view') }}">


                  <div class="mb-6 width20">
                    <label for="start_date">{{ __('admin.Number') }}</label>
                    <input type="text" id="hours" name="hours" value="{{ request('hours') }}" class="hours form-control">
                </div>
            
                <div class="mb-6 width20">
                    <label class="form-label fs-6">
                      {{ __('admin.Condition') }}
                      
                    </label>
                    <select class=" form-select condition" name="condition">
                      <option value="" disabled selected>
                        {{ $l == 'en' ? 'Choose condition ' : ' اختر شرط ' }}
                      </option>
                      <option value="" {{ request('condition') == "" ?"selected":"" }} >{{ __('admin.SelectAll') }}</option>
                      <option value="=" {{ request('condition') == "=" ?"selected":"" }} >{{ __('admin.Equal') }}</option>
                      <option value=">" {{ request('condition') == ">" ?"selected":"" }} >{{ __('admin.Morethan') }}</option>
                      <option value="<" {{ request('condition') == "<" ?"selected":"" }} >{{ __('admin.lessthan') }}</option>
                      <option value="<=" {{ request('condition') == "<=" ?"selected":"" }} >{{ __('admin.lessthanOREqual') }}</option>
                      <option value=">=" {{ request('condition') == ">=" ?"selected":"" }} >{{ __('admin.MorethanOREqual') }}</option>
                     
                    </select>
                  </div>
              <br>
                  <div class="mb-6 width20">
                    <label for="start_date">{{ __('admin.StartDate') }}</label>
                    <input type="text" id="start_date" name="start_date" value="{{ request('start_date') }}" class="form-control datewithouttime" placeholder="{{ __('admin.StartDate') }}">
                </div>
            
                <div class="mb-6 width20">
                    <label for="end_date">{{ __('admin.EndDate') }}</label>
                    <input type="text" id="end_date" name="end_date" value="{{ request('end_date') }}" class="form-control datewithouttime" placeholder="{{ __('admin.EndDate') }}">
                </div>
                <br> 

                      <div class="mb-6 width25">
                        <label class="form-label fs-6">
                          {{ __('admin.Program') }}
                        </label>
                        <select class="cycle_name form-select" name="cycle_name">
                          <option value="" disabled selected>
                            {{ $l == 'en' ? 'Choose program' : 'اختر البرنامج' }}
                          </option>
                          <option value="">{{ __('admin.SelectAll') }}</option>
                          @foreach ($cycles as $cycle)
                            <option value="{{ $cycle->id }}" {{ $cycle->id == request('cycle_name') ? "selected" : "" }} >
                              {{ $cycle->title }}
                            </option>
                          @endforeach
                        </select>
                      </div>

                      
                      @hasanyrole('Admin|organizationManager|manager')
                                          <div class="mb-6 width25">
                                            <label class="form-label fs-6">
                                              {{ __('admin.Users') }}
                                              
                                            </label>
                                            <select class="user_id form-select" name="user_id">
                                              <option value="" disabled selected>
                                                {{ $l == 'en' ? 'Choose User' : 'اختر المستخدم' }}
                                              </option>
                                              <option value="">{{ __('admin.SelectAll') }}</option>
                                              @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ $user->id == request('user_id') ? "selected" : "" }} >{{ $user->{'first_name_'. $l} ." ". $user->{'last_name_'. $l} }}</option>
                                              @endforeach
                                            </select>
                                          </div>
                        @endhasanyrole

                        @hasanyrole('Admin')
                                          <div class="mb-6 width25">
                                            <label class="form-label fs-6">
                                              {{ __('admin.organizations') }}
                                              
                                            </label>
                                            <select class="org_id form-select" name="org_id">
                                              <option value="" disabled selected>
                                                {{ $l == 'en' ? 'Choose Organization' : 'اختر المؤسسة' }}
                                              </option>
                                              <option value="">{{ __('admin.SelectAll') }}</option>
                                              @foreach ($organization as $org)
                                                <option value="{{ $org->id }}" {{ $org->id == request('org_id') ? "selected" : "" }} >{{ $org->{'name_'. $l} }}</option>
                                              @endforeach
                                            </select>
                                          </div>
                        @endhasanyrole
                  
                        <br>
                  <button type="submit" class="btn btn-primary">{{ __('admin.Search') }}</button>
              </form>
<br><br><br>
 <div class="col-12 col-sm-3 col-lg-3 col-mg-3 offset-sm-9 offset-lg-9 offset-md-9 d-flex align-items-center">
                      <div class="btn btn-success ExportFinanceTS">{{ __('cycles.Export') }}</div>
                    </div> 
                    
                <div class="card-text">
                  <div class="table-responsive">
                    <table class="table mb-0" id="userstable">
                      <thead class="thead-dark">
                        <tr>
                          <th>#</th>
                          @hasanyrole('Admin|organizationManager|manager')
                          <th>{{ __('admin.user') }}</th>
                          @endhasanyrole
                          <th>{{ __('admin.CountTotalHours') }}</th>
                          <th>{{ __('admin.TotalAmount') }} {{ $setting->Currency }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php
                        $c=1;
                       
                        $groupedTimeSheets = $timeSheets->groupBy('user_id'); // استخدم user_id أو المفتاح المناسب حسب هيكل البيانات

                        $hours = request('hours');
                        $condition = request('condition');

                        if(!empty($hours)){
                            $hours = request('hours');
                            $condition = request('condition');
                        }else{
                            $hours = 9999999;
                            $condition= "<";
                        }

                    @endphp
                        @foreach ($groupedTimeSheets as $userId => $timesheets)
                        @php
        $user = $timesheets->first()->user; // الحصول على أول مستخدم من المجموعة
        $totalHours = $timesheets->sum('hours_worked'); // جمع الساعات لكل مستخدم
        $totalPayment = $user->houre_rate * $totalHours; // حساب الدفع الإجمالي
    @endphp
     @if (
     ($condition == '>' && $totalPayment > $hours) ||
     ($condition == '<' && $totalPayment < $hours) ||
     ($condition == '=' && $totalPayment == $hours) ||
     ($condition == '>=' && $totalPayment == $hours) ||
     ($condition == '<=' && $totalPayment == $hours)
     ) <!-- هنا يمكنك تعديل الشرط حسب ما تحتاج -->
                        <tr>
                          <td>{{$c++}}</td>
                          <td>{{$user->{'first_name_'.$l} ." ". $user->{'last_name_'.$l} }}</td>
                          <td>{{$totalHours}}</td>
                          <td>{{$totalPayment}} {{ $setting->Currency }}</td>
                          
                        </tr>
                        @endif
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
@section('scripts')
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

<!-- Page JS -->
<script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop
@section('pagescripts')
    <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="/js/jquery.datetimepicker.full.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userstable').DataTable({
                "responsive": true, "searching": true, "lengthChange": false, "paging": true, "bInfo": false,
                "columnDefs": [
                    {"orderable": false, "targets":  [3]},
                ],
                @if ($l == 'ar')
                "language": {
                    "sEmptyTable":     "ليست هناك بيانات متاحة في الجدول",
                    "sLoadingRecords": "جارٍ التحميل...", "sProcessing":   "جارٍ التحميل...",
                    "sLengthMenu":   "أظهر _MENU_ مدخلات", "sZeroRecords":  "لم يعثر على أية سجلات",
                    "sInfo":         "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                    "sInfoEmpty":    "يعرض 0 إلى 0 من أصل 0 سجل", "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                    "sInfoPostFix":  "", "sSearch":       "ابحث:", "sUrl":          "",
                    "oPaginate": {"sFirst":    "الأول", "sPrevious": "السابق", "sNext":     "التالي", "sLast":     "الأخير"},
                    "oAria": {"sSortAscending":  ": تفعيل لترتيب العمود تصاعدياً", "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"}} @endif
                });
            });
        </script>
        <script>
     

          const statusButton = document.querySelectorAll('#statusButton');
          const levelButton = document.querySelectorAll('#levelButton');
      
          statusButton.forEach((button) => {
            button.addEventListener('click', () => {
              statusButton.forEach((btn) => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-white', 'text-primary');
              });
              button.classList.remove('bg-white', 'text-primary');
              button.classList.add('bg-primary', 'text-white');
            });
          });


          // Date Time Picker
      $('.date').datetimepicker({
        timepicker: true,
        format: 'Y-m-d H:i'
      });
      $('.datewithouttime').datetimepicker({
        format: 'Y-m-d'
      });


      const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
    };

        </script>
@stop
