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
    ['target' => 'sent', 'icon' => 'ti-send', 'label' => 'Sent'],
    ['target' => 'draft', 'icon' => 'ti-edit', 'label' => 'Draft', 'badge' => ['count' => 2, 'class' => 'bg-label-warning']],
    ['target' => 'starred', 'icon' => 'ti-star', 'label' => 'Starred'],
    ['target' => 'spam', 'icon' => 'ti-alert-octagon', 'label' => 'Spam', 'badge' => ['count' => 4, 'class' => 'bg-label-danger']],
    ['target' => 'trash', 'icon' => 'ti-trash', 'label' => 'Trash']
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
                <hr class="mx-n3 emails-list-header-hr mb-2" />
                <!-- Message List: Actions -->
                @include('admin.clients.messages.partials.messeges-actions')
                <!-- Message List: Actions -->
              </div>
              <hr class="container-m-nx m-0" />
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
                  @foreach ($sentMessages as $message)
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
  <script>
    $('#reply-btn').on('click', function() {
      console.log('reply button clicked');
      $('#reply-message-container').toggleClass('d-none');
    });
  </script>
@stop
