@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-email.css" />
@stop

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="">
    <!-- Content -->
    <div class="">
      <div class="app-email card">
        <div class="row">
          <!-- Inbox List -->
          <div class="col app-emails-list">
            <div class="card shadow-none border-0 rounded-0">
              <div class="card-body emails-list-header p-3 py-2">
                <h4 class="px-4 mt-4">
                  {{ $l == 'ar' ? 'الرسائل الواردة' : 'Received Messages' }}
                </h4>
                <!-- Inbox List: Search -->
                {{-- <div class="d-flex justify-content-between align-items-center px-3 mt-2">
                  <div class="d-flex align-items-center w-100">
                    <i class="ti ti-menu-2 ti-lg cursor-pointer d-block d-lg-none me-4 mb-4" data-bs-toggle="sidebar"
                      data-target="#app-email-sidebar" data-overlay></i>
                    <div class="mb-4 w-100">
                      <div class="input-group input-group-merge shadow-none">
                        <span class="input-group-text border-0 ps-0 py-0" id="email-search">
                          <i class="ti ti-search ti-lg"></i>
                        </span>
                        <input type="text" class="form-control email-search-input border-0 py-0"
                          placeholder="{{ $l == 'ar' ? 'ابحث في الرسايل' : 'Search messages' }}" aria-label="Search mail"
                          aria-describedby="email-search" id="searchInput" />
                      </div>
                    </div>
                  </div>
                </div> --}}
                {{-- <hr class="mx-n3 emails-list-header-hr mb-2" /> --}}
              </div>
              <hr class="container-m-nx m-0" />
              <!-- Inbox List: Items -->
              <div class="email-list pt-0">
                @php($n = 1)
                @if (count($received) > 0)
                  <ul class="list-unstyled m-0" id="messagesList">
                    @foreach ($received as $received)
                      <li class="email-list-item d-flex align-items-center message-row">
                        <div class="d-flex align-items-center w-100 px-4">
                          {{-- <div class="form-check mb-0 ms-2">
                            <input class="email-list-item-input form-check-input" type="checkbox" id="email-4" />
                            <label class="form-check-label" for="email-4"></label>
                          </div> --}}
                          <div class="email-list-item-content ms-2 ms-sm-0 me-2">
                            <span
                              class="email-list-item-username me-2 h6">{{ $received->{'first_name_' . $l} . ' ' . $received->{'last_name_' . $l} }}</span>
                            <small class="email-list-item-subject d-xl-inline-block d-block">
                              {{ $received->subject }}
                            </small>
                          </div>
                          <div class="email-list-item-meta ms-auto d-flex align-items-center justify-content-end"
                            style="width: 200px;">
                            <small class="email-list-item-time text-muted mx-6"
                              style="width: 70px;">{{ $received->created_at }}
                            </small>

                            <button type="button" id="msg" onclick="OpenModal({{ $received->id }});"
                              class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#MessageModal">
                              <i class="ti ti-send"></i>
                            </button>
                          </div>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <ul class="list-unstyled mt-10">
                    <li class="email-list-empty text-center">
                      {{ __('messages.No Received Messages') }}
                    </li>
                  </ul>
                @endif
              </div>
            </div>
            <div class="app-overlay"></div>
          </div>
          <!-- /Emails List -->
        </div>
      </div>
      <!-- / Content -->
      <div class="content-backdrop fade"></div>
    </div>
    <!-- Content wrapper -->

    <!-- Modal -->
    <div class="modal fade" id="MessageModal" tabindex="-1" aria-labelledby="exampleModalScrollableTitle">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalScrollableTitle">(<span id="from"></span>)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p><b>{{ __('messages.Date') }}:</b> <span id="date"></span></p>
            <p><b>{{ __('messages.Message') }}:</b></p> <span id="message"></span>
          </div>
          <form method="post" action="/dashboard/send_message" enctype="multipart/form-data">
            <div class="modal-body">
              <p><b>{{ __('messages.SendReply') }}:</b></p>
              {!! csrf_field() !!}
              <input type="hidden" name="sender" value="{{ Auth::user()->id }}">
              <input type="hidden" id="receiver" name="receiver">
              <input type="hidden" id="messgeId" name="messgeId">
              <input type="hidden" id="subject" name="subject" value="">
              <div class="mb-3">
                <textarea class="form-control" name="message" rows="4" placeholder="{{ __('messages.Write your message') }}"
                  required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">
                
                <span class="d-none d-sm-block">{{ __('messages.Send') }}</span>
              </button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                
                <span class="d-none d-sm-block">{{ __('messages.Close') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal -->
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>

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
    function OpenModal(id) {
      $.ajax({
        type: " GET",
        url: "/dashboard/openmsg",
        data: {
          "id": id,
          "type": 1
        },
        success: function(response) {
          var trrow = $("#" + 'read' + id);
          var trno = $("#" + 'No' + id);
          trrow.css({
            "font-weight": "unset"
          });
          trno.html('{{ __('messages.Yes') }}');
          $('#from').text("'" + response['subject'] +
            "' {{ __('messages.From') }}: " + response['first_name_{{ $l == 'ar' ? 'ar' : 'en' }}'] + " " +
            response['last_name_{{ $l == 'ar' ? 'ar' : 'en' }}']);
          $('#date').text(response['created_at']);
          $('#message').text(response['message']);
          $('#receiver').val(response['sender']);
          $('#messgeId').val(response['id']);
          $('#subject').val("Reply to: " + (response['subject']).replace('Reply to:', ''));
        },
        error: function(error) {
          console.log(error);
          alert(" Error Occurred, Try again!");
        },
      });
    }
  </script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-email.js"></script>
@stop
