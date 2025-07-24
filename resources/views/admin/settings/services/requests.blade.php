@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-6 mt-3">
        <h3 class="mb-0 fw-bold">{{ __('cycles.Services requests') }}</h3>
        @if (count($received) > 0 && $countold)
          <a href="/dashboard/requests/old" class="btn btn-primary">
            {{ __('cycles.Show old records') }}
            <i class="ti ti-eye ms-2"></i>
          </a>
        @endif
      </div>

      <div class="card card-body">
        <form id="filter-form" method="GET" action="{{ route('service_requests.list_all') }}">
          <div class="row border-bottom mb-4 pb-3">
            <div class="col-10">
              <div class="row">
                <div class="col-3">
                  <select name="Service_id" class="form-select select2"
                    data-placeholder="{{ __('admin.Select Service') }}">
                    <option value="">{{ __('admin.Select Service') }}</option>
                    <option value="0">All</option>
                    @php
                      $Services = App\Service::all();
                    @endphp
                    @foreach ($Services as $Service)
                      <option value="{{ $Service->id }}" {{ $Service->id == request('Service_id') ? 'selected' : '' }}>
                        {{ $Service->{'name_' . $l} }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-3">
                  <select name="status" class="form-select select2" data-placeholder="{{ __('admin.Select Status') }}">
                    <option value="">{{ __('admin.Select Status') }}</option>
                    <option value="0">All</option>
                    <option value="Pending" {{ 'Pending' == request('status') ? 'selected' : '' }}>
                      {{ __('admin.Pending') }}</option>
                    <option value="Done" {{ 'Done' == request('status') ? 'selected' : '' }}>
                      {{ __('admin.Done') }}</option>
                  </select>
                </div>
                <div class="col-3">
                  <input type="text" class="form-control datepicker" placeholder="{{ __('admin.StartDate') }}"
                    name="start_date" value="{{ request('start_date') }}" />
                </div>
                <div class="col-3">
                  <input type="text" name="end_date" class="form-control datepicker"
                    placeholder="{{ __('admin.EndDate') }}" value="{{ request('end_date') }}" />
                </div>
              </div>
            </div>
          </div>
        </form>
        
        @if (count($received) > 0)
          <div class="table-responsive">
            <table class="table zero-configuration">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>{{ __('cycles.SenderName') }}</th>
                  <th>{{ __('cycles.Service') }}</th>
                  <th>{{ __('cycles.Comments') }}</th>
                  <th>{{ __('cycles.Date/Time') }}</th>
                  <th>{{ __('cycles.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($received as $n => $rec)
                  @php
                    $user = $rec->user?->{'first_name_' . $l} . ' ' . $rec->user?->{'last_name_' . $l};
                  @endphp
                  <tr>
                    <td>{{ $n + 1 }}</td>
                    <td>
                      @if (!empty($rec->user->company))
                        <div class="d-flex align-items-center gap-2">
                          <a href="/dashboard/companyProfile/{{ $rec->user->company->id }}">
                            <img 
                              src="/191014/{{ $rec->user->img_url ?? '/images/logo-placeholder.png' }}" 
                              alt="avatar"
                              class="rounded-circle border shadow-sm" 
                              height="40" 
                              width="40"
                              onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"  
                            />
                          </a>
                          <a href="/dashboard/companyProfile/{{ $rec->user->company->id }}">
                            <div class="media-body">
                              <h6 class="media-heading mb-0">{{ $user }}</h6>
                              <span class="font-small-2">{{ $rec->user->{'title_' . $l} }}
                                @if ($rec->user->company)
                                  - {{ $rec->user->company->{'name_' . $l} }}
                                @endif
                              </span>
                            </div>
                          </a>
                        </div>
                      @else
                        No Company
                      @endif
                    </td>
                    <td>{{ $rec->service->{'name_' . $l} }}</td>
                    <td>{{ $rec->comment ?? '-' }}</td>
                    <td>{{ $rec->date_time }}</td>
                    <td>
                      <button
                        onclick="reply(
                          {{ $rec->id }}, 
                          '{{ addslashes($user) }}', 
                          '{{ addslashes($rec->service->{'name_' . $l} ?? '-') }}',
                          '{{ addslashes($rec->service->vendor_name ?? '-') }}',
                          '{{ addslashes($rec->service->vendor_email ?? '-') }}',
                          '{{ addslashes($rec->service->vendor_phone ?? '-') }}'
                      );"
                        type="button" class="btn btn-icon rounded-circle btn-label-info mr-1">
                        <i class="ti ti-edit text-info"></i>
                      </button>
                      <button
                        onclick="OpenModal({{ $rec->user_id }},'{{ $user }}','{{ $rec->service->{'name_' . $l} }}');"
                        type="button" class="btn btn-icon rounded-circle btn-label-primary mr-1">
                        <i class="ti ti-brand-telegram"></i></button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          @include('includes.no-result')
        @endif
      </div>
    </div>
  </div>
  <!-- END: Content-->

  <div class="modal fade text-left" id="MessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">
            {{ __('messages.SendMessageTo') }} <span id="uname"></span>
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
            <input type="hidden" name="receiver" id="rid" value="">
            <div class="form-group mb-3">
              <label for="subject" class="fs-6 fw-semibold form-label mb-2">{{ __('messages.Subject') }}:</label>
              <input id="subject" name="subject" type="text" placeholder="{{ __('messages.Subject') }}"
                class="form-control" required>
            </div>
            <div class="form-group mb-3">
              <label for="message" class="fs-6 fw-semibold form-label mb-2">{{ __('messages.Message') }}: </label>
              <textarea class="form-control" id="message" name='message' rows="8"
                placeholder="{{ __('messages.Write your message') }}" required></textarea>
            </div>
            <div class="w-100 d-flex justify-content-end alitn-items-center gap-4 mt-4">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                {{ __('messages.Close') }}
              </button>
              <button type="submit" class="btn btn-primary">
                {{ __('messages.Send') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Send response -->
  <div class="modal fade text-left" id="ReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel33">
            {{ __('cycles.Response to') }} <span id="rname"></span>
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
          <input type="hidden" name="receiver" id="resid" value="">

          <div class="mb-3">
            <label class="form-label fs-6 fw-semibold mb-2">{{ __('admin.Vendor_Name') }}:</label>
            <p id="vendor_name" class="form-control-plaintext">-</p>
          </div>

          <div class="mb-3">
            <label class="form-label fs-6 fw-semibold mb-2">{{ __('admin.Vendor_Email') }}:</label>
            <p id="vendor_email" class="form-control-plaintext">-</p>
          </div>

          <div class="mb-3">
            <label class="form-label fs-6 fw-semibold mb-2">{{ __('admin.Vendor_Phone') }}:</label>
            <p id="vendor_phone" class="form-control-plaintext">-</p>
          </div>

          <div class="mb-3">
            <label class="form-label fs-6 fw-semibold mb-2">{{ __('cycles.Add a comment') }}:</label>
            <textarea class="form-control" id="admin_comment" name='admin_comment' rows="4"
              placeholder="{{ __('cycles.Write a response comment') }}"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-primary" data-bs-dismiss="modal" onclick="approve('Yes')">
            <span class="d-none d-sm-block">{{ __('cycles.Approve') }}</span>
          </button>
          <button type="button" class="btn btn-label-danger ml-1" onclick="approve('No')">
            <span class="d-none d-sm-block">{{ __('cycles.Disapprove') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script>
    $('.datepicker').flatpickr({
      monthSelectorType: 'static',
    });

    $('#filter-form .form-select, #filter-form .form-control').on('change', function() {
      $('#filter-form').submit();
    });
  </script>

  @if ($l == 'ar')
    <script type="text/javascript">
      $(document).ready(function() {
        $('.zero-configuration').DataTable({
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
        });
      });
    </script>
  @endif
  <script>
    function OpenModal(id, name, title) {
      $('#uname').html(name);
      $('#rid').val(id);
      $('#subject').val('{{ __('cycles.Your request') }} - ' + title);
      $('#MessageModal').modal('show');
    }

    function reply(id, name, title, vendorName, vendorEmail, vendorPhone) {
      $('#rname').html('"' + title + '" {{ __('cycles.Request from') }} ' + name);
      $('#resid').val(id);
      // Update vendor details dynamically
      $('#vendor_name').text(vendorName || '-');
      $('#vendor_email').text(vendorEmail || '-');
      $('#vendor_phone').text(vendorPhone || '-');
      $('#ReplyModal').modal('show');
    }

    function approve(stat) {
      let comment = $('#admin_comment').val();
      let id = $('#resid').val();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/requests/approve",
        data: {
          "id": id,
          "stat": stat,
          "comment": comment
        },
        success: function(response) {
          location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  </script>
  <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
  <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
  <script src="/app-assets/js/scripts/datatables/datatable.js"></script>
@stop
