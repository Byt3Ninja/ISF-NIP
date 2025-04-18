@php
  $type = request()->query('type');
@endphp

<li 
  id="message-item" 
  data-id="{{ $message->id }}"
  class="d-flex align-items-center cursor-pointer border-bottom message-item px-4" 
  {{-- data-starred="true" --}}
  data-bs-toggle="sidebar" 
  data-message="{{ json_encode($message) }}"
  style="height: 56px; cursor: pointer; opacity: {{ $message->read === null ? '1' : ($message->read == 0 ? '0.5' : '1') }};" 
  onmouseover="this.style.backgroundColor='#f8f9fa';" 
  onmouseout="this.style.backgroundColor='';"
  >
  <div class="d-flex align-items-center w-100 h-100">
    <div class="d-flex align-items-center">
      <div class="form-check mb-0 ms-2">
        <input 
          class="email-list-item-input form-check-input" 
          type="checkbox" 
          id="message-select" 
          value="{{ $message->id }}"
        />
        <label class="form-check-label" for="message-select"></label>
      </div>
      @if ($type != 'draft')
        <button type="button" class="btn btn-icon p-0 StarredToggele" data-id="{{ $message->id }}">
          <i class="{{ $message->starred == 1 ? 'fas text-warning' : 'far' }} fa-star fs-5"></i>
        </button>
      @endif
    </div>

    <div 
      class="w-100 h-100 d-flex align-items-center justify-between"
      @if($type == 'draft')
        onclick="getDraftMessageDetails({{ json_encode($message) }}, event);"
      @else
        onclick="getMessageDetails({{ json_encode($message) }}, event)"
      @endif
    >
      <div class="d-flex align-items-center">
        <img 
          id="message-item-image-src" 
          src="{{ $message->sender->img_url ? '/191014/' . $message->sender->img_url : '/icons/account.svg' }}" 
          alt="user-avatar"
          class="d-block flex-shrink-0 rounded-circle me-sm-2 me-0" 
          height="32" 
          width="32"
        />
        <div class="email-list-item-content ms-2 ms-sm-0 me-2">
          <span id="message-item-createdBy" class="email-list-item-username me-2 text-heading">
            {{ $message->sender->name }}
          </span>
          <small id="message-item-subject" class="email-list-item-subject d-xl-inline-block d-block">
            {{ $message->subject }}
          </small>
        </div>
      </div>

      <div class="text-end" style="flex: 1;">
        {{-- <span class="email-list-item-label badge badge-dot bg-danger d-none d-md-inline-block me-2"
          data-label="private">
        </span> --}}
        <small
          id="message-item-createdAt"
          class="text-muted text-nowrap"
        >
          {{ \Carbon\Carbon::parse($message->created_at)->format('F jS Y, g:i A') }}
        </small>
        {{-- <ul class="list-inline email-list-item-actions">
          <li class="list-inline-item email-read btn btn-icon btn-text-secondary rounded-pill">
            <i class="ti ti-mail ti-md"></i>
          </li>
  
          <li class="list-inline-item email-delete btn btn-icon btn-text-secondary rounded-pill" onclick="delme({{ $message->id }}, event)">
            <i class="ti ti-trash ti-md"></i>
          </li>
  
          <li class="list-inline-item btn btn-icon btn-text-secondary rounded-pill">
            <i class="ti ti-info-circle ti-md"></i>
          </li>
        </ul> --}}
      </div>
    </div>
  </div>
</li>
