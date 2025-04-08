@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-email.css" />
@stop

@php
   $folders = [
    ['target' => 'inbox', 'icon' => 'ti-inbox', 'label' => 'Inbox', 'badge' => ['count' => $receivedMessagesCount, 'class' => 'bg-label-primary']],
    ['target' => 'sent', 'icon' => 'ti-send', 'label' => 'Sent', 'badge' => ['count' => count($sentMessages), 'class' => 'bg-label-success']],
    ['target' => 'draft', 'icon' => 'ti-edit', 'label' => 'Draft', 'badge' => ['count' => 0, 'class' => 'bg-label-secondary']],
    ['target' => 'starred', 'icon' => 'ti-star', 'label' => 'Starred', 'badge' => ['count' => 0, 'class' => 'bg-label-warning']],
    ['target' => 'trash', 'icon' => 'ti-trash', 'label' => 'Trash', 'badge' => ['count' => 0, 'class' => 'bg-label-danger']]
  ];
@endphp

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
                @include('admin.clients.messages.partials.search-messages')
                <!--/ Message List: Search -->
              </div>
              <hr class="container-m-nx m-0" />
              <!-- Message List: Items -->
              <div class="email-list pt-0" id="receivedMessages" style="overflow-y: auto;">
                @if (count($receivedMessages) > 0)
                  @include('admin.clients.messages.partials.messeges-actions', ['ids' => $receivedMessages->pluck('id')])
                  <ul id="message-list" class="list-unstyled m-0">
                    @foreach ($receivedMessages as $message)
                      @include('admin.clients.messages.partials.message-item', ['message' => $message])
                    @endforeach
                  </ul>
                @else
                  @include('includes.no-result', ['message' => __('admin.No Messages')])
                @endif
              </div>
              <div class="email-list pt-0" id="sentMessages" style="display: none; overflow-y: auto;">
                @if (count($sentMessages) > 0)
                  @include('admin.clients.messages.partials.messeges-actions', ['ids' => $sentMessages->pluck('id')])
                  <hr class="mx-n3 emails-list-header-hr mb-2" />
                  <ul id="message-list" class="list-unstyled m-0">
                    @foreach ($sentMessages as $message)
                      @include('admin.clients.messages.partials.message-item', ['message' => $message])
                    @endforeach
                  </ul>
                @else
                  @include('includes.no-result', ['message' => __('admin.No Messages')])
                @endif
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
  {{-- <script>
    const receivedMessages = @json($receivedMessages);
    const sentMessages = @json($sentMessages);
    console.log('Received Messages:', receivedMessages);
    console.log('Sent Messages:', sentMessages);
  </script> --}}
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="/vuexy/assets/vendor/libs/block-ui/block-ui.js"></script>
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
      $('#sent-tab').removeClass('active');
      $('#starred-tab').removeClass('active');
      $('#trash-tab').removeClass('active');
      $('#draft-tab').removeClass('active');
      $('#inbox-tab').addClass('active');
    });

    $('#sent-tab').on('click', () => {
      console.log('sent tab clicked');
      $('#receivedMessages').hide();
      $('#sentMessages').show();
      $('#inbox-tab').removeClass('active');
      $('#starred-tab').removeClass('active');
      $('#trash-tab').removeClass('active');
      $('#draft-tab').removeClass('active');
      $('#sent-tab').addClass('active');
    });

    $('#draft-tab').on('click', () => {
      console.log('draft tab clicked');
      $('#receivedMessages').hide();
      $('#sentMessages').hide();
      $('#inbox-tab').removeClass('active');
      $('#sent-tab').removeClass('active');
      $('#starred-tab').removeClass('active');
      $('#trash-tab').removeClass('active');
      $('#draft-tab').addClass('active');
    });
    $('#starred-tab').on('click', () => {
      console.log('starred tab clicked');
      $('#receivedMessages').hide();
      $('#sentMessages').hide();
      $('#inbox-tab').removeClass('active');
      $('#sent-tab').removeClass('active');
      $('#draft-tab').removeClass('active');
      $('#trash-tab').removeClass('active');
      $('#starred-tab').addClass('active');
    });
    $('#trash-tab').on('click', () => {
      console.log('trash tab clicked');
      $('#receivedMessages').hide();
      $('#sentMessages').hide();
      $('#inbox-tab').removeClass('active');
      $('#sent-tab').removeClass('active');
      $('#draft-tab').removeClass('active');
      $('#starred-tab').removeClass('active');
      $('#trash-tab').addClass('active');
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
    function filterMessages(value) {
      const searchInput = value.toLowerCase();
      const messageItems = $('#message-list #message-item');

      $('#message-list #message-item').each(function() {
        const msgSubject = $(this).find('#message-item-subject').text().toLowerCase();
        const msgCreatedBy = $(this).find('#message-item-createdBy').text().toLowerCase();
        const msgContent = $(this).find('#message-item-text').text().toLowerCase();
        if (msgSubject.includes(searchInput) || msgCreatedBy.includes(searchInput) || msgContent.includes(searchInput)) {
          $(this).removeClass('d-none').addClass('d-flex');
        } else {
            $(this).removeClass('d-flex').addClass('d-none');
        }
      });
    };

    function getMessageDetails(messageData, event) {
      const {
        created_at , first_name_en, id, last_name_ar, 
        last_name_en, read, receiver, subject 
      } = messageData || {};

      if (subject) $('#message-details-subject').text(subject);
      if (created_at) $('#message-details-createdAt').text(moment(created_at).format('MMMM Do YYYY, h:mm A'));
      if (first_name_en || first_name_en) $('#message-details-createdBy').text(`${first_name_en} ${last_name_en}`);
      if (receiver) $('#message-details-image').attr('src', receiver.image);

      $('#message-details-text').text('lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
      $('#message-details-subject').text(subject);
    };
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
  <script>
    $('#reply-btn').on('click', function() {
      console.log('reply button clicked');
      $('#reply-message-container').toggleClass('d-none');
    });
  </script>
@stop
