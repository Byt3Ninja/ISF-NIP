<li id="message-item" class="email-list-item email-marked-read d-flex align-items-center" data-starred="true"
  data-bs-toggle="sidebar" 
  data-target="#app-email-view"
>
  <div class="d-flex align-items-center w-100">
    <div class="form-check mb-0 ms-2">
      <input class="email-list-item-input form-check-input" type="checkbox" id="email-{{ $message['id'] }}" />
      <label class="form-check-label" for="email-{{ $message['id'] }}"></label>
    </div>
    <span class="ms-sm-3 me-4 d-sm-inline-block d-none"><i
        class="email-list-item-bookmark ti ti-star ti-md cursor-pointer"></i></span>
    <img id="message-item-image-src" src="/icons/account.svg" alt="user-avatar"
      class="d-block flex-shrink-0 rounded-circle me-sm-2 me-0" height="32" width="32" />
    <div class="email-list-item-content ms-2 ms-sm-0 me-2">
      <span id="message-item-createdBy" class="email-list-item-username me-2 text-heading">
        {{ $message['first_name_' . $l] }}
        {{ $message['last_name_' . $l] }}
      </span>
      <small id="message-item-subject" class="email-list-item-subject d-xl-inline-block d-block">
        {{ $message['subject'] }}
      </small>
    </div>
    <div class="email-list-item-meta ms-auto d-flex align-items-center">
      <span class="email-list-item-label badge badge-dot bg-danger d-none d-md-inline-block me-2"
        data-label="private"></span>
      <small
        id="message-item-createdAt"
        class="email-list-item-time text-muted"
      >
        {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
      </small>
      <ul class="list-inline email-list-item-actions">
        <li class="list-inline-item email-read btn btn-icon btn-text-secondary rounded-pill">
          <i class="ti ti-mail ti-md"></i>
        </li>
        <li class="list-inline-item email-delete btn btn-icon btn-text-secondary rounded-pill">
          <i class="ti ti-trash ti-md"></i>
        </li>
        <li class="list-inline-item btn btn-icon btn-text-secondary rounded-pill">
          <i class="ti ti-info-circle ti-md"></i>
        </li>
      </ul>
    </div>
  </div>
</li>
