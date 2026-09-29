@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-email.css" />
@stop

{{-- @php
  $folders = collect([
    ['target' => 'inbox', 'icon' => 'ti-inbox', 'label' => 'Inbox', 'badge' => ['count' => count($receivedMessages), 'class' => 'bg-label-primary']],
    ['target' => 'sent', 'icon' => 'ti-send', 'label' => 'Sent', 'badge' => ['count' => count($sentMessages), 'class' => 'bg-label-success']],
    ['target' => 'draft', 'icon' => 'ti-edit', 'label' => 'Draft', 'badge' => ['count' => count($drafts), 'class' => 'bg-label-secondary']],
    ['target' => 'starred', 'icon' => 'ti-star', 'label' => 'Starred', 'badge' => ['count' => count($starred), 'class' => 'bg-label-warning']],
    ['target' => 'trash', 'icon' => 'ti-trash', 'label' => 'Trash', 'badge' => ['count' => count($trashedMessages), 'class' => 'bg-label-danger']]
  ]);
@endphp --}}

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->

  <div class="">
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

            <div class="email-list pt-0" id="all-messages" style="overflow-y: auto;">
              @if (count($messages) > 0)
                @include('admin.clients.messages.partials.messeges-actions', ['ids' => $messages->pluck('id')])
                <hr class="mx-n3 emails-list-header-hr mb-2" />
                <ul id="message-list" class="list-unstyled m-0">
                  @foreach ($messages as $message)
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
  <script>
    // const receivedMessages = @json($receivedMessages);
    // const messages = @json($messages);
    // console.log('messages:', messages);
    // console.log('Received Messages:', receivedMessages);
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
    const tabs = ['inbox', 'sent', 'draft', 'starred', 'trash'];

    // Get the 'type' parameter from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const typeParam = urlParams.get('type') || 'inbox'; // default to 'inbox' if not set

    tabs.forEach(tab => {
      // Bind the click event to change the URL
      $(`#${tab}-tab`).on('click', () => {
        if (typeParam === tab) {
          return; // Do nothing if the tab is already active
        }
        window.location.href = `/dashboard/messages?type=${tab}`;
      });

      // Apply the 'active' class if it matches the typeParam
      
      if (tab === typeParam) {
        $(`#${tab}-tab`).addClass('active');
      } else {
        $(`#${tab}-tab`).removeClass('active');
      }
    });

    // Sidebar toggle for mobile
    $('[data-bs-toggle="sidebar"]').on('click', function() {
      const target = $(this).data('target');
      $(target).toggleClass('show');
      if ($(this).data('overlay')) {
        $('.app-overlay').toggleClass('show');
      }
    });

    // Close sidebar when clicking overlay or close button
    $('.app-overlay, #sidebar-close').on('click', function() {
      $('.app-email-sidebar').removeClass('show');
      $('.app-overlay').removeClass('show');
    });
  </script>

  <script>
    const typeSelect = document.getElementById('select-type');

    $('#select-type').on('change', function() {
      let type = +this.value;
    // console.log('type', type);
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
          url: "{{ route('messages.recipients_list_by_program') }}",
          method: "GET",
          data: {
            id: cycleId
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

    function getDraftMessageDetails(draftMessageData, event) {
      console.log('draftMessageData',  draftMessageData)
      $('#message-details-view').removeClass('show');

    // console.log('draftMessageData', draftMessageData)
      const { receivers, type, subject, body, id } = draftMessageData || {};
      
      $('#sendMessageModal').modal('show');

      $('#select-type').val(type ?? 0);
      $('#send-message-subject').val(subject ?? '');
      $('#send-message-text').val(body ?? '');
      $('#draftsNumber').val(id ?? '');
      menus(type);

      if (type == 1) {
        $('#users').show();
        $('#mentors, #organizations').hide();
        $('#select-programs').val(receivers?.map(item => item?.cycle_id)).trigger('change');
        $('#select-companies').val(receivers?.map(item => item?.id)).trigger('change');
      } else if (type == 2) {
        $('#mentors').show();
        $('#users, #organizations').hide();
        $('#select-mentors').val(receivers?.map(item => item?.id)).trigger('change');
      } else if (type == 3) {
        $('#organizations').show();
        $('#users, #mentors').hide();
        $('#select-organizations').val(receivers?.map(item => item?.organization_id)).trigger('change');
      } else {
        $('#users, #mentors, #organizations').hide();
      }
    }

    function getMessageDetails(messageData, event) {
      $('#message-details-view').addClass('show');
    // console.log('messageData' , messageData)
      const {
        created_at , id, sender, replies,
        last_name_en, read, receiver, subject, body, starred
      } = messageData || {};

      if (id) {
        $('.StarredToggele').attr('data-id', id);
        $('.ToggleMarkRead').attr('do', id);
        $('.ToggleMarkUnRead').attr('do', id);
        $('.ToggleMarkRead').attr('mark', 'read');
        $('.ToggleMarkUnRead').attr('mark', 'unRead');
        $('#message-reply-id').val(id);
        $('#message-details-delete').attr('data-id', id);
      }
    // console.log('replies', replies)

      if (replies && replies.length > 0) {
        $('#message-replies-container').show();
        $('#message-details-replies').html('');
        replies.forEach(reply => {
          $('#message-details-replies').append(`
            <li class="list-group-item gap-3 p-2 border-bottom">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold fs-6">${reply.sender.name}</div>
                <div class="d-flex align-items-center">
                  <span class="me-2 text-muted">${moment(reply.created_at).format('MMMM Do YYYY, h:mm A')}</span>
                 
                </div>
              </div>
              <div class="pe-0 pe-md-12">
                <p class="mb-1 text-muted">${reply.body}</p>
              </div>
            </li>
          `);
        });
      } 
      // else {
      //   $('#message-details-replies').html('<p>{{ __('messages.No replies yet.') }}</p>');
      // }

      // <button class="btn btn-icon btn-text-secondary rounded-pill" onclick="delme(${reply.id}, event)">
      //               <i class="ti ti-trash ti-sm"></i>
      //             </button>
      if (sender) {
        $('#message-reply-receiver_id').val(sender.id);
        if (sender.name) {
          $('#message-details-createdBy').text(`${sender.name}`);
        }
      }
      
      if (subject) {
        $('#message-reply-subject').val(subject);
        $('#message-details-subject').text(subject);
      } 

      if (body) {
        $('#message-details-text').html(body);
      }

      if (receiver) {
        $('#message-details-image').attr('src', receiver.image);
      }
      
      if (created_at) {
        $('#message-details-createdAt').text(moment(created_at).format('MMMM Do YYYY, h:mm A'));
      }

      if (starred) {
        $('.StarredToggele i').removeClass('far').addClass('fas text-warning');
      }
    };

    $('#send-message-form').on('submit', function(e) {
      if (!$('#send-message-subject').val()) {
        e.preventDefault();

        $('.subject-error').show();
        $('#send-message-subject').addClass('is-invalid');
      // console.log('Subject is required');
      } else if (!$('#send-message-text').val()) {
        e.preventDefault();
        $('.subject-error').hide();
        $('#send-message-subject').removeClass('is-invalid');
      // console.log('Message is required');
        $('.message-error').show();
        $('#send-message-text').addClass('is-invalid');
      } else {
        $('.subject-error').hide();
        $('#send-message-subject').removeClass('is-invalid');
        $('.message-error').hide();
        $('#send-message-text').removeClass('is-invalid');
      // console.log('Form submitted');
      }
    });

    $('#send-message-subject').on('input', function() {
      if ($(this).val()) {
        $('.subject-error').hide();
        $(this).removeClass('is-invalid');
      } else {
        $('.subject-error').show();
        $(this).addClass('is-invalid');
      }
    });
  </script>

  <script>
    function deleteMessages(select = 'single', id, event) {
      const messageSelectAll = $('#message-select-all');
      const messageSelect = $('#message-select:checked'); // Only get checked checkboxes
      const selectedIds = messageSelect.map(function() {
      return $(this).val();
      }).get();

      event.stopPropagation();
    // console.log('delete message clicked');
    // console.log('id', id);
    // console.log('selectedIds', selectedIds);

      var url = '/dashboard/messages/delete';
      var token = '{{ csrf_token() }}';
      const idsToDelete = id && select == 'single' ? [id] : selectedIds; // Use selected IDs or fallback to single ID

      if (!idsToDelete || idsToDelete.length === 0) {
        Swal.fire({
          title: "{{ __('admin.No Messages Selected') }}",
          text: "{{ __('admin.Select at least one message') }}",
          icon: "warning",
          confirmButtonText: "{{ __('admin.OK') }}",
          showCancelButton: false,
          customClass: {
            confirmButton: 'btn btn-primary',
          },
          buttonsStyling: false,
        });
        return;
      }

      Swal.fire({
      	title: "{{__('admin.SureDelete')}}",
        text: "{{__('admin.Recover')}}",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "{{ __('admin.DYes') }}",
        cancelButtonText: "{{ __('admin.No') }}",
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-label-secondary',
        },
        buttonsStyling: false,
      })
        .then((result) => {
        if (result.value) {
          $.post(url, {
            'id': idsToDelete ,
            '_token': token
          },
          function(response) {
          // console.log('response', response);
            Swal.fire(
              '{{ __('admin.Deleted') }}!',
              '{{ __('admin.FileDeleted') }}.',
              'success',
            ).then(() => {
              if (response.state === 'success') {
                // Remove the deleted messages from the UI
                idsToDelete.forEach(id => {
                  $(`#message-item[data-id="${id}"]`).remove();
                });
                // Optionally, refresh the page or update the message list
                location.reload();
              }
            });
          }
          ).fail(
            function(error) {
              Swal.fire(
                'Error!',
                'Delete fail.',
                'error'
              );
            // console.log(error);
            }
          );
        } else {
        // console.log('Canceled');
        }
      });
    }
  </script>

  <script>
    $('#reply-btn').on('click', function() {
    // console.log('reply button clicked');
      $('#reply-message-container').toggleClass('d-none');
    });

    $(document).on('click', '.DeleteDraft', function() {
      $.ajax({
      url: '/dashboard/deleteDraft',
      type: 'POST',
      data: {
        draftsNumber: $('.draftsNumber').val(),
        _token: $('meta[name="csrf-token"]').attr('content') // مهم جداً للـ CSRF
      },
      success: function(response) {
      console.log(response); // أو أي بيانات جاية من السيرفر
      location.reload();
      },
      error: function(xhr) {
        console.error('Error:', xhr.responseText);
      }
    });


    });
    //   $(document).on('keyup', '.SubjectDraft, .messagedraft', function() {
    //   saveMessageAsDraft(); // استدعاء الفنكشن المشتركة
    // });

    // $(document).on('change', '.typedraft, .usersdraft, .mentorsdraft', function() {
    //     saveMessageAsDraft(); // نفس الفنكشن برضو
    // });
    
    function saveMessageAsDraft() {
   
      $.ajax({
        url: '/dashboard/draftMessage',
        type: 'POST',
        data: {
          type: $('.typedraft').val(),
          draftsNumber: $('.draftsNumber').val(),
          users: $('.usersdraft').val(),
          cycleId: $('.cycle_id').val(),
          mentors: $('.mentorsdraft').val(),
          organizations: $('.organizationsdraft').val(),
          subject: $('.SubjectDraft').val(),
          message: $('.messagedraft').val(),
          _token: $('meta[name="csrf-token"]').attr('content') // مهم جداً للـ CSRF
        },
        success: function(response) {
          // أو أي بيانات جاية من السيرفر
          // console.log(response); // أو أي بيانات جاية من السيرفر
            $('.draftsNumber').val(response.draftsNumber);
            location.reload();
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseText);
        }
      });
    }
    
    $(document).on('click', '.StarredToggele', function(event) {
    // console.log('event', event)
      event.stopPropagation();
      var button = $(this);
      var messageId = $(this).attr('data-id');
      $.ajax({
        url: '/dashboard/messages/toggleStarredStatus', // الرoutes المناسب
        type: 'POST',
        data: {
          id: messageId,
          _token: '{{ csrf_token() }}'
        },
        success: function (response) {
        // console.log(response);
          if (response.state === 'success') {
            var icon = button.find('i'); // استخدم العنصر المحفوظ
            if (response.starred == 1) {
              // console.log('starred');
                icon.removeClass('far').addClass('fas text-warning');
            } else {
              // console.log('Notstarred');
                icon.removeClass('fas text-warning').addClass('far');
            }
          }
        },
        error: function(xhr) {
          console.error('Error:', xhr.responseText);
        }
      });
    });

    $(document).on('click', '.ToggleMarkRead, .ToggleMarkUnRead', function() {
      var messageId = $(this).attr('do');
      var mark = $(this).attr('mark');
      alert(messageId);
      $.ajax({
        url: '/dashboard/messages/toggleMarkReadStatus', // الرoutes المناسب
        type: 'POST',
        data: {
          id: messageId,
          mark: mark,
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
        // console.log(response);
        },
        error: function(xhr) {
         console.error('Error:', xhr.responseText);
        }
      });
    });

    const messageReplyEditor = new Quill('.message-reply-editor', {
      modules: {
        toolbar: '.message-reply-toolbar'
      },
      placeholder: '{{ $l }}' == 'en' ? 'Write your message... ' : 'اكتب رسالتك... ',
      theme: 'snow',
    });

    const sendMessageEditor = new Quill('.send-message-editor', {
      modules: {
        toolbar: '.send-message-toolbar'
      },
      placeholder: '{{ $l }}' == 'en' ? 'Write your message... ' : 'اكتب رسالتك... ',
      theme: 'snow',
    });

    messageReplyEditor.on('text-change', function(e) {
    // console.log('$(this)', $(this))
    // console.log('value', messageReplyEditor.root.innerHTML);
      $('#message-reply-text').val(messageReplyEditor.root.innerHTML);
    });

    sendMessageEditor.on('text-change', function(e) {
    // console.log('$(this)', $(this))
    // console.log('value', sendMessageEditor.root.innerHTML);
      $('#send-message-text').val(sendMessageEditor.root.innerHTML);
      $('.message-error').hide();
      $('#send-message-text').removeClass('is-invalid');
    });

    $(document).on('click', '.SendReplyMessage', function() {
      // var id = $(this).attr('data-id');
      var message_id = $('#message-reply-id').val();
      var message_receiver = $('#message-reply-receiver_id').val();
      var message_subject = $('#message-reply-subject').val();
      var message_message = $('#message-reply-text').val();
      
      $.ajax({
        url: '/dashboard/sendReplymessage',
        type: 'POST',
        data: {
            message_id: message_id,
            message_receiver: message_receiver,
            message_subject: message_subject,
            message_message: message_message,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
        console.log('response', response)
        // console.log('Messages: ',response.messages);
          $('#message-reply-text').val('');
          messageReplyEditor.setText(''); // Clear the editor

          const { sender_name, created_at, id, message} = response.messages.reply || {};

          $('#message-replies-container').show();
          $('#message-details-replies').prepend(`
            <li class="list-group-item gap-3 p-2 border-bottom">
              <div class="d-flex justify-content-between align-items-center mb-2 ">
                <div class="fw-semibold fs-6">${sender_name}</div>
                <div class="d-flex align-items-center">
                  <span class="me-2 text-muted">${moment(created_at).format('MMMM Do YYYY, h:mm A')}</span>
                  <button class="btn btn-icon btn-text-secondary rounded-pill" onclick="delme(${id}, event)">
                    <i class="ti ti-trash ti-sm"></i>
                  </button>
                </div>
              </div>
              <div class="pe-0 pe-md-12">
                <p class="mb-1 text-muted">${message}</p>
              </div>
            </li>
          `);

          $('.app-email-view-content').animate({ scrollTop: 0 }, 'slow');
        },
        error: function(xhr) {
          console.error('Error:', xhr.responseText);
          Swal.fire({
            title: 'Error!',
            text: 'Failed to send the reply message.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        },
      });
    });

    // $(document).on('change', '.cycle_id', function() {
    //   var messageId = $(this).val();
      
    //   $.ajax({
    //     url: '/dashboard/get-users-by-cycle', // الرoutes المناسب
    //     type: 'get',
    //     data: {
    //       id: messageId,
    //       _token: '{{ csrf_token() }}'
    //     },
    //     success: function(response) {
    //       console.log(response);
    //       var users = response.users;
    //   var select = $('.usersdraft');
    //   select.empty(); // إزالة الخيارات القديمة

    //   if (users.length > 0) {
    //     users.forEach(function(user, index) {
    //       var isSelected = index === 0 ? 'selected' : '';
    //       var option = `<option value="${user.id}" ${isSelected}>${user.full_name}</option>`;
    //       select.append(option);
    //     });
    //   } else {
    //     select.append('<option disabled>لا يوجد مستخدمين</option>');
    //   }

    //   select.trigger('change'); // لإعادة تفعيل Select2 بعد التحديث
    
    //     },
    //     error: function(xhr) {
    //      console.error('Error:', xhr.responseText);
    //     }
    //   });
    // });


  </script>
@stop