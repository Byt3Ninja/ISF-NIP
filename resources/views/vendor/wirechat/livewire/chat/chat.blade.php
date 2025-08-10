{{-- Import helper function to use in chatbox --}}
@use('Namu\WireChat\Helpers\Helper')
@use('Namu\WireChat\Facades\WireChat')

@php
    $primaryColor = WireChat::getColor();
@endphp

@assets
<style>
    emoji-picker {
        width: 100% !important;
        height: 100%;
        --background: none !important;
        --border-radius: 12px;
        --input-border-color: rgb(229 229 229);
        --input-padding: 0.45rem;
        --outline-color: none;
        --outline-size: 1px;
        --num-columns: 8;
        --emoji-padding: 0.7rem;
        --emoji-size: 1.5rem;
        --border-color: none;
        --indicator-color: #9ca3af;
    }

    @media screen and (min-width: 600px) {
        emoji-picker {
            --num-columns: 10;
            --emoji-size: 1.8rem;
        }
    }

    @media screen and (min-width: 900px) {
        emoji-picker {
            --num-columns: 16;
            --emoji-size: 1.9rem;
        }
    }

    @media (prefers-color-scheme: dark) {
        emoji-picker {
            --background: none !important;
            --input-border-color: var(--wc-dark-border);
            --outline-color: none;
            --outline-size: 1px;
            --border-color: none;
            --input-font-color: white;
            --indicator-color: var(--wc-dark-accent);
            --button-hover-background: var(--wc-dark-accent);
        }
    }

    .dark emoji-picker {
        --background: none !important;
        --input-border-color: var(--wc-dark-border);
        --outline-color: none;
        --outline-size: 1px;
        --border-color: none;
        --input-font-color: white;
        --indicator-color: var(--wc-dark-accent);
        --button-hover-background: var(--wc-dark-accent);
    }
</style>
@endassets

<div 
    x-data="{
        initializing: true,
        conversationId:@js($conversation->id),
        conversationElement: document.getElementById('conversation'),
        loadEmojiPicker() {
            if (!document.head.querySelector('script[src=\'https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js\']')) {
                let script = document.createElement('script');
                script.type = 'module';
                script.async = true;
                script.src = 'https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js';
                document.head.appendChild(script);
            }
        },
        get isWidget() {
            return $wire.widget == true;
        }
    }" 
    x-init="setTimeout(() => {
        requestAnimationFrame(() => {
            initializing = false;
            $wire.dispatch('focus-input-field');
            loadEmojiPicker();
            $wire.dispatch('chat-opened', {conversation:conversationId});
        });
    }, 120);"
    class="w-100 bg-light bg-gradient text-dark overflow-hidden h-100 position-relative"
    style="contain:content"
>
    <div class="d-flex flex-column flex-grow-1 h-100 position-relative">
        {{-- Header --}}
        @include('wirechat::livewire.chat.partials.header', [ 'conversation' => $conversation, 'receiver' => $receiver])

        {{-- Body --}}
        @include('wirechat::livewire.chat.partials.body', [ 
            'conversation' => $conversation, 
            'authParticipant' => $authParticipant, 
            'loadedMessages' => $loadedMessages, 
            'isPrivate' => $conversation->isPrivate(), 
            'isGroup' => $conversation->isGroup(), 
            'receiver' => $receiver
        ])

        {{-- Footer --}}
        @include('wirechat::livewire.chat.partials.footer', [ 
            'conversation' => $conversation, 
            'authParticipant' => $authParticipant, 
            'media' => $media, 
            'files' => $files, 
            'replyMessage' => $replyMessage
        ])
    </div>

    <livewire:wirechat.chat.drawer />
</div>
