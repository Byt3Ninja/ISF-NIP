@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
  <link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-header mb-4">
        <h4 class="card-title">{{ __('admin.Users') }}</h4>
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


                


                <div class="card-text">
                  <div class="table-responsive">
                    <table class="table mb-0" id="userstable">
                      <thead class="thead-dark">
                        <tr>
                          <th>#</th>
                          @hasanyrole('Admin|organizationManager|manager')
                          <th>{{ __('admin.user') }}</th>
                          @endhasanyrole
                          <th>{{ __('admin.Price') }}  {{ $setting->Currency }}</th>
                          <th>{{ __('admin.Actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php
                        $c=1;
                       
                        $groupedTimeSheets = $timeSheets->groupBy('user_id'); // استخدم user_id أو المفتاح المناسب حسب هيكل البيانات
                    @endphp
                        @foreach ($groupedTimeSheets as $userId => $timesheets)
                        @php
        $user = $timesheets->first()->user; // الحصول على أول مستخدم من المجموعة

    @endphp
                        <tr>
                          <td>{{$c++}}</td>
                          @hasanyrole('Admin|organizationManager|manager')
                          <td>{{$user->{'first_name_'.$l} ." ". $user->{'last_name_'.$l} }}</td>
                          @endhasanyrole
                          <form method="post" action="{{ route('time_sheets.clock.update.mine', $user->id) }}" >
                          <td><div class="input-group" style="width: 20%;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">{{ $setting->Currency }}</span>
                            </div>
                            <input type="text" name="pricetime" class="form-control" value="{{ $user->houre_rate }}">
                        </div>  </td>
                         
                          <td>
                             @csrf
                            <button type="submit" class="btn btn-icon btn-label-info rounded-circle" >
                              <i class="ti ti-check text-info"></i>
                            </button>
                          </td>
                        </form>
                        </tr>
                        <!-- Edit Customer Modal -->
                        
                      
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
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
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

        </script>
@stop
