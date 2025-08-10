@php
    $isSameAsNext = ($message?->sendable_id === $nextMessage?->sendable_id) 
        && ($message?->sendable_type === $nextMessage?->sendable_type);

    $isSameAsPrevious = ($message?->sendable_id === $previousMessage?->sendable_id) 
        && ($message?->sendable_type === $previousMessage?->sendable_type);

    $isNotSameAsNext = !$isSameAsNext;
    $isNotSameAsPrevious = !$isSameAsPrevious;
@endphp

<img 
    src="{{ $attachment?->url }}" 
    alt="{{ __('wirechat::chat.labels.attachment') }}" 
    loading="lazy"
    class="img-fluid bg-light object-fit-contain rounded-4"
    style="
        max-width: max-content;
        height: 200px;
        min-height: 210px;
        background-color: {{ $belongsToAuth ? 'var(--wc-brand-primary)' : 'var(--wc-light-secondary)' }};
        {{ $isSameAsNext && $isNotSameAsPrevious && $belongsToAuth ? 'border-bottom-right-radius: .25rem; border-top-right-radius: 1rem;' : '' }}
        {{ $isSameAsPrevious && $belongsToAuth ? 'border-right-radius: .25rem;' : '' }}
        {{ $isNotSameAsPrevious && $isNotSameAsNext && $belongsToAuth ? 'border-radius: 1rem;' : '' }}
        {{ $isNotSameAsNext && $belongsToAuth ? 'border-bottom-right-radius: 1rem;' : '' }}
        
        {{ $isSameAsNext && $isNotSameAsPrevious && !$belongsToAuth ? 'border-bottom-left-radius: .25rem; border-top-left-radius: 1rem;' : '' }}
        {{ $isSameAsPrevious && !$belongsToAuth ? 'border-left-radius: .25rem;' : '' }}
        {{ $isNotSameAsPrevious && $isNotSameAsNext && !$belongsToAuth ? 'border-radius: 1rem;' : '' }}
        {{ $isNotSameAsNext && !$belongsToAuth ? 'border-bottom-left-radius: 1rem;' : '' }}
    "
>
