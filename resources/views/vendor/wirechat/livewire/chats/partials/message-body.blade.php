<div class="d-flex align-items-center gap-2">

    {{-- فقط إذا كان المستخدم الحالي صاحب الرسالة --}}
    @if ($belongsToAuth)
        <span class="fw-bold small text-dark">
            @lang('wirechat::chats.labels.you'):
        </span>
    @elseif(!$belongsToAuth && $group !== null)
        <span class="fw-bold small text-dark">
            {{ $lastMessage->sendable?->display_name }}:
        </span>
    @endif

    <p @class([
        'text-truncate small d-flex align-items-center gap-2',
        'fw-semibold text-dark' =>
            !$isReadByAuth && !$lastMessage?->ownedBy($this->auth),
        'fw-normal text-secondary' =>
            $isReadByAuth && !$lastMessage?->ownedBy($this->auth),
        'fw-normal text-secondary' =>
            $isReadByAuth && $lastMessage?->ownedBy($this->auth),
    ])>
        {{ $lastMessage->body != '' 
            ? $lastMessage->body 
            : ($lastMessage->isAttachment() 
                ? '📎 '.__('wirechat::chats.labels.attachment') 
                : '') }}
    </p>

    <span class="fw-medium px-1 small text-muted">
        @if ($lastMessage->created_at->diffInMinutes(now()) < 1)
            @lang('wirechat::chats.labels.now')
        @else
            {{ $lastMessage->created_at->shortAbsoluteDiffForHumans() }}
        @endif
    </span>

</div>
