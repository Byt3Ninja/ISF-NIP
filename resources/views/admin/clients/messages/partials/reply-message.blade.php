 <!-- Message View : Reply Message-->
 <div id="reply-message-container" class="email-reply card mt-4 mx-sm-6 mx-3 mb-4 d-none">
  <h6 class="card-header border-bottom fw-normal pb-4 mb-3">{{ __('messages.Add Reply') }}</h6>
  <div class="card-body pt-0 ps-3">
    
      <input type="hidden" name="message_id" class="message_id" value="{{ $message['id'] }}">
      <input type="hidden" name="message_receiver" class="message_receiver" value="{{ $message['sender'] }}">
      <input type="hidden" name="message_subject" class="message_subject" value="{{ $message['subject'] }}">
    <div class="d-flex justify-content-start">
      <div class="email-reply-toolbar border-0 w-100 ps-0 pb-4">
        <span class="ql-formats me-0">
          <button class="ql-bold"></button>
          <button class="ql-italic"></button>
          <button class="ql-underline"></button>
          <button class="ql-list" value="ordered"></button>
          <button class="ql-list" value="bullet"></button>
          <button class="ql-link"></button>
          <button class="ql-image"></button>
        </span>
      </div>
    </div>
    <div class="email-reply-editor"></div>
    <div class="d-flex justify-content-end align-items-center mt-4">
      {{-- <div class="cursor-pointer btn btn-text-secondary text-secondary me-4">
        <i class="ti ti-paperclip ti-16px text-heading me-2"></i>
        <span class="align-middle">Attachments</span>
      </div> --}}
      <input type="text" name="message_message" class="message_message" value="" placeholder="Type your message here..." />
      <button class="btn btn-primary SendReplyMessage" data-id="{{ $message['id'] }}">
        <span class="align-middle">{{ __('messages.Send') }}</span>
        <i class="ti ti-send ti-16px ms-2 scaleX-n1-rtl"></i>
      </button>
    </div>
    
  </div>
</div>
<!--/ Message View : Reply Message-->