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
      @if (session()->has('msg'))
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="d-flex align-items-center">
                <i class="ti ti-error"></i>
                <span>
                  {{ __('admin.' . session()->get('msg')) }}
                  {{ Session::forget('msg') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      @endif
      <div class="app-email card">
        <div class="row g-0">
          <!-- Messages List -->
          <div class="col-12 app-emails-list">
            <div class="card shadow-none border-0 rounded-0">
              <div class="card-header pb-0 mb-0">
                <h5 class="card-title mb-0">
                  {{ $l == 'ar' ? 'رسائل الزوار' : 'Visitors Messages' }}
                </h5>
              </div>
              <div class="card-body emails-list-header p-3 py-2">
                <!-- Messages List: Search -->
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
                          placeholder="{{ $l == 'ar' ? 'ابحث في الرسايل' : 'Search messages' }}"
                          aria-label="Search mail" aria-describedby="email-search" />
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="mx-n3 emails-list-header-hr mb-2" /> --}}
              </div>
              <hr class="container-m-nx m-0" />
              <!-- Messages List: Items -->
              <div class="email-list pt-0">
                @php($n = 1)
                @if (count($getmsgs) > 0)
                  <ul class="list-unstyled m-0">
                    @foreach ($getmsgs as $getmsg)
                      <li class="email-list-item d-flex align-items-center flex-wrap flex-md-nowrap" data-starred="true" data-bs-toggle="sidebar"
                        data-target="#app-email-view">
                        <div class="d-flex align-items-center w-100">


                          <img src="/vuexy/assets/img/avatars/3.png" alt="user-avatar"
                            class="d-block flex-shrink-0 rounded-circle me-2" height="32" width="32" />
                          <div class="email-list-item-content flex-grow-1 me-2">
                            <span class="email-list-item-username me-2 h6 d-block">
                              @if ($getmsg->internal_id)
                                <a href="/dashboard/users/{{ $getmsg->internal_id }}/edit">{{ $getmsg->name }}
                                  ({{ __('admin.user') }})
                                </a>
                              @else
                                {{ $getmsg->name }}
                              @endif
                            </span>
                            <small class="email-list-item-subject d-block text-truncate">
                              {{ $getmsg->subject }}
                            </small>
                          </div>
                          <div class="email-list-item-meta d-flex align-items-center justify-content-end flex-shrink-0">
                            <small class="email-list-item-time text-muted d-none d-lg-block me-2"
                              style="min-width: 70px;">{{ $getmsg->created_at }}
                            </small>

                            <a href="javascript:void(0);" onclick="makeread({{ $getmsg->id }});"
                              class="btn btn-icon btn-sm btn-label-secondary rounded-circle me-1"
                              data-bs-toggle="tooltip" title="{{ $getmsg->read ? __('Mark as unread') : __('Mark as read') }}">
                              <i id="ico{{ $getmsg->id }}"
                                class='ti ti-{{ $getmsg->read ? 'square-rounded-check' : 'x' }}'>
                              </i>
                            </a>

                            <button type="button" id="msg" onclick="OpenModal({{ $getmsg->id }});"
                              class="btn btn-icon btn-sm btn-label-info rounded-circle me-1" data-bs-toggle="modal"
                              data-bs-target="#MessageModal" title="{{ __('View message') }}">
                              <i class="ti ti-eye"></i>
                            </button>

                            @include('partials.forms.confirm-action', ['formId' => 'delete-msg-' . $getmsg->id, 'action' => route('contact_messages.delete', $getmsg->id), 'message' => __('Are you sure?'), 'label' => '<i class="ti ti-trash text-danger"></i>', 'class' => 'btn btn-icon btn-sm btn-label-danger rounded-circle'])
                          </div>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                @else
                  <ul class="list-unstyled mt-10">
                    <li class="email-list-empty text-center">
                      {{ __('messages.NoMessages') }}
                    </li>
                  </ul>
                @endif
              </div>
            </div>
            <div class="app-overlay"></div>
          </div>
          <!-- /Messages List -->
        </div>
      </div>
      <!-- / Content -->
      <div class="content-backdrop fade"></div>
    </div>
    <!-- Content wrapper -->

    <!-- Modal -->
    <div class="modal fade" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="Title"></h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ URL::to('dashboard/msgreply') }}" method="POST" enctype="multipart/form-data"
            autocomplete="off">
            <div class="modal-body">
              <p class="warning" id="replyby"></p>
              <p><b>{{ __('messages.Phone') }}: </b> <span id="Phone"></span></p>
              <p><b>{{ __('messages.Email') }}: </b> <span id="Email"></span></p>
              <p><b>{{ __('messages.Date') }}: </b> <span id="Date"></span></p>
              <p><b>{{ __('messages.IP') }}: </b><span id="UserIp"></span></p>
              <p><b>{{ __('messages.Message') }}:</b></p> <span id="Message"></span>

              <hr>
              <h6>{{ __('messages.Sendreply') }}:</h6>

              {!! csrf_field() !!}
              <input type="hidden" name="remail" value="" id="remail">
              <input type="hidden" name="rname" value="" id="rname">
              <input type="hidden" name="mid" value="" id="mid">
              <div class="form-group mb-4">
                <label class="form-label">{{ __('messages.Subject') }}</label>
                <input name="subject" type="text" placeholder="{{ __('messages.Subject') }}" class="form-control"
                  required="">
              </div>
              <div class="form-group">
                <label class="form-label">{{ __('messages.yourMessage') }}:</label>
                <textarea class="form-control" name='message' rows="3" placeholder="{{ __('messages.Write your message') }}"
                  required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                {{ __('messages.Close') }}
              </button>
              <button type="submit" class="btn btn-primary send-btn">
                <span> {{ __('messages.Send') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Modal -->
  @stop

  @section('scripts')
    <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
    <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
    <script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>

    <script>
      function OpenModal(id) {
        var tno = $("#" + 'read' + id);
        var tyes = $("#" + 'unread' + id);
        var ico = $("#" + 'ico' + id);
        tno.css({
          "font-weight": "unset"
        });
        tno.attr('id', 'read' + id);
        tyes.css({
          "font-weight": "unset"
        });
        tyes.attr('id', 'read' + id);
        ico.attr('class', 'bx bxs-inbox');
        $.ajax({
          type: "GET",
          url: "/dashboard/openonemsg/" + id,
          success: function(response) {
            console.log(response);
            $('#Title').text(response[0]['subject'] + " {{ __('messages.From') }}: " + response[0]['name']);
            $('#Email').text(response[0]['email']);
            $('#remail').val(response[0]['email']);
            $('#rname').val(response[0]['name']);
            $('#mid').val(response[0]['id']);
            $('#Phone').text(response[0]['phone']);
            $('#Date').text(response[0]['created_at']);
            $('#Message').text(response[0]['message']);
            $('#UserIp').text(response[0]['userip']);
            if (response[1]['first_name_en']) {
              $('#replyby').html('<b>' + response[1]['first_name_{{ $l }}'] + ' ' + response[1][
                'last_name_{{ $l }}'
              ] + ' {{ __('messages.replied with') }} (' + response[0]['reSubject'] + ' - ' + response[0][
                'reMessage'
              ] + ')</b>');
            } else {
              $('#replyby').html('');
            }
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          },
        });
      }

      function makeread(id) {
        var trrow = $("#" + 'read' + id);
        var trno = $("#" + 'unread' + id);
        var ico = $("#" + 'ico' + id);
        var lnk = 'makeunread';
        if (trno.length) {
          var lnk = 'makeread';
        }
        $.ajax({
          type: 'POST',
          url: lnk + '/' + id,
          data: {
            "_token": "{{ csrf_token() }}",
            "id": id
          },
          success: function() {
            $('#id');
            if (trno.length) {
              trno.css({
                "font-weight": "unset"
              });
              trno.attr('id', 'read' + id);
              ico.attr('class', 'bx bxs-inbox');
            } else {
              trrow.css({
                "font-weight": 600
              });
              trrow.attr('id', 'unread' + id);
              ico.attr('class', 'bx bxs-envelope');
            }
          },
          error: function() { // if error occurred
            alert("DB Error occurred, please try again");
          },
        });
      }
    </script>
  @stop

  @section('pagescripts')
    <script src="/vuexy/assets/js/app-email.js"></script>
  @stop
