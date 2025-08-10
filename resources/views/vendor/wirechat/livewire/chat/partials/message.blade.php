@use('Namu\WireChat\Facades\WireChat')

@php
    $isSameAsNext = ($message?->sendable_id === $nextMessage?->sendable_id) 
        && ($message?->sendable_type === $nextMessage?->sendable_type);

    $isSameAsPrevious = ($message?->sendable_id === $previousMessage?->sendable_id) 
        && ($message?->sendable_type === $previousMessage?->sendable_type);

    $isNotSameAsNext = !$isSameAsNext;
    $isNotSameAsPrevious = !$isSameAsPrevious;
@endphp

<div 
    style="{{ $belongsToAuth ? 'background-color:var(--wc-brand-primary);' : '' }}"
    class="d-flex flex-wrap max-w-fit border border-light-subtle rounded-4 p-2 flex-column {{ $belongsToAuth ? 'text-white' : 'bg-light text-dark' }}"

    {{-- شكل الفقاعات بناءً على مكان الرسالة --}}
    @if ($isSameAsNext && $isNotSameAsPrevious && $belongsToAuth) style="border-bottom-right-radius:.25rem; border-top-right-radius:1rem;" @endif
    @if ($isSameAsPrevious && $belongsToAuth) style="border-right-radius:.25rem;" @endif
    @if ($isNotSameAsPrevious && $isNotSameAsNext && $belongsToAuth) style="border-radius:1rem;" @endif
    @if ($isNotSameAsNext && $belongsToAuth) style="border-bottom-right-radius:1rem;" @endif

    @if ($isSameAsNext && $isNotSameAsPrevious && !$belongsToAuth) style="border-bottom-left-radius:.25rem; border-top-left-radius:1rem;" @endif
    @if ($isSameAsPrevious && !$belongsToAuth) style="border-left-radius:.25rem;" @endif
    @if ($isNotSameAsPrevious && $isNotSameAsNext && !$belongsToAuth) style="border-radius:1rem;" @endif
    @if ($isNotSameAsNext && !$belongsToAuth) style="border-bottom-left-radius:1rem;" @endif
>

    {{-- اسم المرسل في المحادثات الجماعية --}}
    @if (!$belongsToAuth && $isGroup)
        <div class="flex-shrink-0 fw-medium text-primary {{ $isSameAsPrevious ? 'd-none' : '' }}">
            {{ $message?->sendable?->display_name }}
        </div>
    @endif

    {{-- نص الرسالة --}}
    <pre class="text-wrap fs-6" style="white-space: pre-line; font-family: inherit;">
        {{ $message?->body }}
    </pre>

    {{-- الوقت --}}
    <span class="fs-7 ms-auto {{ $belongsToAuth ? 'text-light' : 'text-muted' }}">
        {{ $message?->created_at->format('H:i') }}
    </span>
</div>
