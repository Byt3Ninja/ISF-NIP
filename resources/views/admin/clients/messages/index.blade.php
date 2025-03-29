@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-email.css" />
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="app-email card">
        <div class="row g-0">
          @include('admin.clients.messages.partials.sidebar')

          <!-- Messages List -->
          <div class="col app-emails-list">
            <div class="card shadow-none border-0 rounded-0">
              <div class="card-body emails-list-header p-3 py-2">
                <!-- Message List: Search -->
                <div class="d-flex justify-content-between align-items-center px-3 mt-2">
                  <div class="d-flex align-items-center w-100">
                    <i class="ti ti-menu-2 ti-lg cursor-pointer d-block d-lg-none me-4 mb-4" data-bs-toggle="sidebar"
                      data-target="#app-email-sidebar" data-overlay></i>
                    <div class="mb-4 w-100">
                      <div class="input-group input-group-merge shadow-none">
                        <span class="input-group-text border-0 ps-0 py-0" id="email-search">
                          <i class="ti ti-search ti-lg"></i>
                        </span>
                        <input type="text" class="form-control email-search-input border-0 py-0"
                          placeholder="{{ __('admin.Search Messages') }}" aria-label="Search mail"
                          aria-describedby="email-search" />
                      </div>
                    </div>
                  </div>
                </div>
                <hr class="mx-n3 emails-list-header-hr mb-2" />
                <!-- Message List: Actions -->
                {{-- <div class="d-flex justify-content-between align-items-center ps-1">
                  <div class="d-flex align-items-center">
                    <div class="form-check mb-0 ms-2">
                      <input class="form-check-input" type="checkbox" id="email-select-all" />
                      <label class="form-check-label" for="email-select-all"></label>
                    </div>
                    <div class="btn btn-text-secondary btn-icon rounded-pill me-1">
                      <i class="ti ti-trash ti-md email-list-delete cursor-pointer"></i>
                    </div>
                    <div class="btn btn-text-secondary btn-icon rounded-pill me-1">
                      <i class="ti ti-mail-opened ti-md email-list-read cursor-pointer"></i>
                    </div>
                    <div class="dropdown me-1">
                      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button"
                        id="dropdownMenuFolderOne" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-folder ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuFolderOne">
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-info-circle ti-sm me-1"></i>
                          <span class="align-middle">Spam</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-file ti-sm me-1"></i>
                          <span class="align-middle">Draft</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="ti ti-trash ti-sm me-1"></i>
                          <span class="align-middle">Trash</span>
                        </a>
                      </div>
                    </div>
                    <div class="dropdown mx-1">
                      <button class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                        data-bs-toggle="dropdown" aria-expanded="true" id="dropdownLabelOne">
                        <i class="ti ti-tag ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownLabelOne">
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-success me-1"></i>
                          <span class="align-middle">Workshop</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-primary me-1"></i>
                          <span class="align-middle">Company</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-warning me-1"></i>
                          <span class="align-middle">Important</span>
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="badge badge-dot bg-danger me-1"></i>
                          <span class="align-middle">Private</span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    <span class="btn btn-icon btn-text-secondary rounded-pill me-1">
                      <i class="ti ti-refresh ti-md scaleX-n1-rtl cursor-pointer email-refresh"></i>
                    </span>
                    <div class="dropdown">
                      <button class="btn btn-icon btn-text-secondary rounded-pill p-0" type="button" id="emailsActions"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="emailsActions">
                        <a class="dropdown-item" href="javascript:void(0)">Mark as read</a>
                        <a class="dropdown-item" href="javascript:void(0)">Mark as unread</a>
                        <a class="dropdown-item" href="javascript:void(0)">Delete</a>
                        <a class="dropdown-item" href="javascript:void(0)">Archive</a>
                      </div>
                    </div>
                  </div>
                </div> --}}
              </div>
              {{-- <hr class="container-m-nx m-0" /> --}}
              <!-- Message List: Items -->
              <div class="email-list pt-0" id="receivedMessages">
                <ul class="list-unstyled m-0">
                  @foreach ($receivedMessages as $message)
                    @include('admin.clients.messages.partials.message-item', ['message' => $message])
                  @endforeach
                </ul>
                <ul class="list-unstyled m-0">
                  <li class="email-list-empty text-center d-none">{{ __('admin.No Messages') }}.</li>
                </ul>
              </div>
              <div class="email-list pt-0" id="sentMessages">
                <ul class="list-unstyled m-0">
                  @foreach ($receivedMessages as $message)
                    @include('admin.clients.messages.partials.message-item', ['message' => $message])
                  @endforeach
                </ul>
                <ul class="list-unstyled m-0">
                  <li class="email-list-empty text-center d-none">{{ __('admin.No Messages') }}.</li>
                </ul>
              </div>
            </div>
            <div class="app-overlay"></div>
          </div>
          <!-- /Messages List -->

          @include('admin.clients.messages.partials.message-details')
        </div>

        @include('admin.clients.messages.partials.send-message')
      </div>
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
  </div>
  <!-- Content wrapper -->
@endsection

@section('scripts')
  <script>
    const receivedMessages = @json($receivedMessages);
    const sentMessages = @json($sentMessages);
    console.log('Received Messages:', receivedMessages);
    console.log('Sent Messages:', sentMessages);
  </script>
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-email.js"></script>
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  <script>
    $('#inbox-tab').on('click', () => {
      console.log('inbox tab clicked');
      $('#receivedMessages').show();
      $('#sentMessages').hide();
      $('#inbox-tab').addClass('active');
      $('#sent-tab').removeClass('active');
    });

    $('#sent-tab').on('click', () => {
      console.log('sent tab clicked');
      $('#receivedMessages').hide();
      $('#sentMessages').show();
      $('#inbox-tab').removeClass('active');
      $('#sent-tab').addClass('active');
    });
  </script>

  <script>
    const typeSelect = document.getElementById('select-type');
    typeSelect.addEventListener('change', function() {
      let type = +this.value;
      console.log('type', type);
      menus(type);
    });

    function menus(menu) {
      if (menu === 0) {
        $('#users, #mentors, #organizations').hide();
      } else if (menu === 1) {
        $('#users').show();
        $('#mentors, #organizations').hide();
      } else if (menu === 2) {
        $('#mentors').show();
        $('#users, #organizations').hide();
      } else if (menu === 3) {
        $('#organizations').show();
        $('#users, #mentors').hide();
      } else {
        $('#users, #mentors, #organizations').hide();
      }
    }

    function filterUsersByCycle(cycleId) {
      if (!cycleId) return null;
      $.ajax({
        url: "{{ route('getUsersByProgram') }}",
        method: "GET",
        data: {
          cycle_id: cycleId
        },
        success: function(response) {
          let userSelect = $('select[name="users[]"]');
          userSelect.empty();
          response.users.forEach(user => {
            userSelect.append(`<option value="${user.id}">${user.name}</option>`);
          });
          userSelect.trigger('change');
        }
      });
    }
  </script>
  
  <script>
    $('#message-item').on('click', function() {
      console.log('message item clicked');
      const messageImgSrc = $(this).find('#message-item-image-src').attr('src');
      const messageCreatedBy = $(this).find('#message-item-createdBy').text();
      const messageSubject = $(this).find('#message-item-subject').text();
      const messageText = $(this).find('#message-item-text').text();
      const messageCreatedAt = $(this).find('#message-item-createdAt').text();
      console.log('messageCreatedBy', messageCreatedBy);
      $('#message-details-subject').text(messageSubject);
      $('#message-details-text').text(messageText);
      $('#message-details-image').attr('src', messageImgSrc);
      $('#message-details-createdBy').text(messageCreatedBy);
      $('#message-details-createdAt').text(messageCreatedAt);
    });
  </script>
  
  <script>
    window.delme = function(id, event) {
      event.stopPropagation();
      console.log('delete message clicked');
      console.log('id', id);
      console.log('element', event.target);
      
      var url = '/dashboard/messages/delete';
      var token = '{{ csrf_token() }}';
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}!",
        })
        .then((result) => {
          if (result.value) {
            $.post(url, {
                'id': id,
                '_token': token
              },
              function(response) {
                Swal.fire(
                  '{{ __('admin.Deleted') }}!',
                  '{{ __('admin.FileDeleted') }}.',
                  'success'
                );
                location.reload();
              }
            ).fail(
              function(error) {
                Swal.fire(
                  'Error!',
                  'Delete fail.',
                  'error'
                );
                console.log(error);
              }
            );
          } else {
            console.log('Canceled')
          }
        })
    }
  </script>
@stop
