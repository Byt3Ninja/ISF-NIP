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
        <h3 class="content-title fw-bold mb-4">{{ __('cycles.Services requests') }}</h3>

        <!-- Zero configuration table -->
        <section id="basic-datatable">
          <div class="row">
            @if (count($received) > 0)
              <div class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-end border-bottom border-light mb-2">
                    @if ($countold)
                      <a href="/dashboard/requests/old" class="btn btn-primary">
                        {{ __('cycles.Show old records') }}
                        <i class="ti ti-eye ms-2"></i>
                      </a>
                    @endif
                  </div>
                  <div class="card-content">
                    <div class="card-body card-dashboard">
                      <div class="table-responsive">
                        <table class="table zero-configuration">
                          <thead>
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
                                $user = $rec->user->{'first_name_' . $l} . ' ' . $rec->user->{'last_name_' . $l};
                              @endphp
                              <tr>
                                <td>{{ $n + 1 }}</td>
                                <td>
                                  @if (!empty($rec->user->company))
                                    <div class="d-flex align-items-center gap-1">
                                      <a class="media-left mr-50"
                                        href="/dashboard/companyProfile/{{ $rec->user->company->id }}">
                                        <img src="/191014/{{ $rec->user->img_url ?? '../images/placeholder.jpg' }}"
                                          alt="avatar" class="rounded-circle" height="40" width="40">
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
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="col-sm-12">
                <div class="card">
                  <div class="card-header">
                    <h4 class="card-title">{{ __('cycles.No Received Requests') }}</h4>
                    @if ($countold)
                      <br>
                      <a href="/dashboard/requests/old"
                        class="btn btn-primary glow">{{ __('cycles.Show old records') }}</a>
                    @endif
                  </div>
                </div>
              </div>
            @endif
          </div>
        </section>
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
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
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
