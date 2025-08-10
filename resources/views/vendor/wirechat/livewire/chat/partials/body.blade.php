
<main x-data="{
    height: 0,
    previousHeight: 0,
    updateScrollPosition: function() {
        newHeight = $el.scrollHeight;
        heightDifference = newHeight - height;
        $el.scrollTop += heightDifference;
        height = newHeight;
    }
}"  
    x-init="
        setTimeout(() => {
            requestAnimationFrame(() => {
                this.height = $el.scrollHeight;
                $el.scrollTop = this.height;
            });
        }, 300);
    "
    @scroll="
        scrollTop = $el.scrollTop;
        if ((scrollTop <= 0) && $wire.canLoadMore) {
            $wire.loadMore();
        }
    "
    @update-height.window="
        requestAnimationFrame(() => {
            updateScrollPosition();
        });
    "
    @scroll-bottom.window="
        requestAnimationFrame(() => {
            $el.style.overflowY='hidden';
            $el.scrollTop = $el.scrollHeight;
            $el.style.overflowY='auto';
        });
    "
    x-cloak
    class="d-flex flex-column h-100 position-relative gap-2 p-3 p-md-4 p-lg-5 flex-grow-1 overflow-x-hidden w-100 my-auto"
    style="contain: content;"
>
    <div x-cloak 
         wire:loading.delay.class.remove="invisible" 
         wire:target="loadMore" 
         class="invisible transition-all duration-300">
        <x-wirechat::loading-spin />
    </div>

    @php
        $previousMessage = null;
    @endphp


    <!--Message-->
    @if ($loadedMessages)
        {{-- @dd($loadedMessages) --}}
        @foreach ($loadedMessages as $date => $messageGroup)

            {{-- Date  --}}
            <div class="position-sticky top-0 text-uppercase p-2 shadow-sm px-3 z-3 rounded border border-primary-subtle text-sm d-flex text-center justify-content-center bg-light-subtle text-dark w-auto mx-auto">
                {{ $date }}
            </div>


            @foreach ($messageGroup as $key => $message)
                {{-- @dd($message) --}}
                @php
                    $belongsToAuth = $message->belongsToAuth();
                    $parent = $message->parent ?? null;
                    $attachment = $message->attachment ?? null;
                    $isEmoji = $message->isEmoji();


                    // keep track of previous message
                    // The ($key -1 ) will get the previous message from loaded
                    // messages since $key is directly linked to $message
                    if ($key > 0) {
                        $previousMessage = $messageGroup->get($key - 1);
                    }

                    // Get the next message
                    $nextMessage = $key < $messageGroup->count() - 1 ? $messageGroup->get($key + 1) : null;
                @endphp


                <div class="flex gap-2" wire:key="message-{{ $key }}"  >

                    {{-- Message user Avatar --}}
                    {{-- Hide avatar if message belongs to auth --}}
                    @if (!$belongsToAuth && !$isPrivate)
                        <div @class([
                            'flex-shrink-0 mb-auto',
                            'invisible' =>
                                $previousMessage &&
                                $message?->sendable?->is($previousMessage?->sendable),
                        ]) style="margin-bottom: -0.5rem;">
                            <x-wirechat::avatar src="{{ $message->sendable?->cover_url ?? null }}" class="rounded-circle" style="width: 32px; height: 32px;" />
                        </div>
                    @endif

                    {{-- we use w-[95%] to leave space for the image --}}
                    <div style="width: 95%; margin: 0 auto;">
                    <div @class([
                        'd-flex flex-column',
                        'ms-auto' => $belongsToAuth
                    ]) style="max-width: 85%; gap: 0.5rem;">

                        {{-- Show parent/reply message --}}
                        @if ($parent != null)
                            <div @class([
                                'd-flex flex-column',
                                'ms-auto' => $belongsToAuth
                            ]) style="max-width: fit-content; gap: 0.5rem;">

                            @php
                                $sender = $message?->ownedBy($this->auth) 
                                    ? __('wirechat::chat.labels.you') 
                                    : ($message->sendable?->display_name ?? __('wirechat::chat.labels.user'));

                                $receiver = $parent?->ownedBy($this->auth) 
                                    ? __('wirechat::chat.labels.you') 
                                    : ($parent->sendable?->display_name ?? __('wirechat::chat.labels.user'));
                            @endphp

                            <h6 class="small text-secondary px-2">
                                @if ($parent?->ownedBy($this->auth) && $message?->ownedBy($this->auth))
                                    {{ __('wirechat::chat.labels.you_replied_to_yourself') }}
                                @elseif ($parent?->ownedBy($this->auth))
                                    {{ __('wirechat::chat.labels.participant_replied_to_you', ['sender' => $sender]) }}
                                @elseif ($message?->ownedBy($parent->sendable))
                                    {{ __('wirechat::chat.labels.participant_replied_to_themself', ['sender' => $sender]) }}
                                @else
                                    {{ __('wirechat::chat.labels.participant_replied_other_participant', ['sender' => $sender, 'receiver' => $receiver]) }}
                                @endif
                            </h6>



                                  <div 
                                        class="px-1 overflow-hidden" 
                                        style="border-color: var(--wc-light-secondary); {{ $belongsToAuth ? 'border-right: 4px solid; margin-left: auto;' : 'border-left: 4px solid; margin-right: auto;' }}"
                                    >
                                        <p 
                                            class="text-black small rounded-pill px-3 py-1 text-truncate" 
                                            style="background-color: var(--wc-light-secondary); color: var(--wc-dark-secondary); max-width: fit-content;"
                                        >
                                            {{ $parent?->body != '' ? $parent?->body : ($parent->hasAttachment() ? __('wirechat::chat.labels.attachment') : '') }}
                                        </p>
                                    </div>



                                </div>
                            @endif



                            {{-- Body section --}}
                            <div class="d-flex gap-1 gap-md-4 group" 
                                style="{{ $belongsToAuth ? 'justify-content: flex-end;' : '' }}" >


                                {{-- Message Actions --}}
                                @if (($isGroup && $conversation->group?->allowsMembersToSendMessages()) || $authParticipant->isAdmin())
                                <div dusk="message_actions" 
                                    class="my-auto d-flex w-auto align-items-center gap-2 {{ !$belongsToAuth ? 'order-1' : '' }}">
                                    
                                    {{-- reply button --}}
                                    <button wire:click="setReply('{{ encrypt($message->id) }}')"
                                        class="invisible group-hover-visible transition-transform"
                                        style="transition: transform 0.2s; transform-origin: center;"
                                        onmouseover="this.style.transform='scale(1.1)'" 
                                        onmouseout="this.style.transform='scale(1)'">
                                        
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-reply-fill text-white"
                                            viewBox="0 0 16 16">
                                            <path
                                                d="M5.921 11.9 1.353 8.62a.72.72 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z" />
                                        </svg>
                                    </button>

                                    {{-- Dropdown actions button --}}
                                    <x-wirechat::dropdown class="w-40" align="{{ $belongsToAuth ? 'right' : 'left' }}" width="48">
                                        <x-slot name="trigger">
                                            {{-- Dots --}}
                                            <button class="invisible group-hover-visible transition-transform"
                                                style="transition: transform 0.2s; transform-origin: center;"
                                                onmouseover="this.style.transform='scale(1.1)'" 
                                                onmouseout="this.style.transform='scale(1)'">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor"
                                                    class="bi bi-three-dots text-secondary"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                                                </svg>
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            @if ($message->ownedBy($this->auth) || ($authParticipant->isAdmin() && $isGroup))
                                                <button dusk="delete_message_for_everyone"
                                                    wire:click="deleteForEveryone('{{ encrypt($message->id) }}')"
                                                    wire:confirm="{{ __('wirechat::chat.actions.delete_for_everyone.confirmation_message') }}"
                                                    class="w-100 text-start btn btn-link p-0">
                                                    <x-wirechat::dropdown-link>
                                                        @lang('wirechat::chat.actions.delete_for_everyone.label')
                                                    </x-wirechat::dropdown-link>
                                                </button>
                                            @endif

                                            @if (!$isGroup) 
                                                <button dusk="delete_message_for_me"
                                                    wire:click="deleteForMe('{{ encrypt($message->id) }}')"
                                                    wire:confirm="{{ __('wirechat::chat.actions.delete_for_me.confirmation_message') }}"
                                                    class="w-100 text-start btn btn-link p-0">
                                                    <x-wirechat::dropdown-link>
                                                        @lang('wirechat::chat.actions.delete_for_me.label')
                                                    </x-wirechat::dropdown-link>
                                                </button>
                                            @endif

                                            <button dusk="reply_to_message_button"
                                                wire:click="setReply('{{ encrypt($message->id) }}')"
                                                class="w-100 text-start btn btn-link p-0">
                                                <x-wirechat::dropdown-link>
                                                    @lang('wirechat::chat.actions.reply.label')
                                                </x-wirechat::dropdown-link>
                                            </button>
                                        </x-slot>
                                    </x-wirechat::dropdown>
                                </div>

                                @endif


                                {{-- Message body --}}
                                <div class="d-flex flex-column gap-2 position-relative" style="max-width:95%;">
    
                                    {{-- عرض اسم المرسل إذا كانت الرسالة لا تخص المستخدم الحالي وفي محادثة جماعية --}}
                                    @if ($attachment)
                                        @if (!$belongsToAuth && $isGroup)
                                            <div style="color: var(--wc-brand-primary);"
                                                class="flex-shrink-0 fw-medium small {{ $message?->sendable?->is($previousMessage?->sendable) ? 'd-none' : '' }}">
                                                {{ $message->sendable?->display_name }}
                                            </div>
                                        @endif

                                        {{-- المرفقات - ملفات --}}
                                        @if (str()->startsWith($attachment->mime_type, 'application/'))
                                            @include('wirechat::livewire.chat.partials.file', ['attachment' => $attachment])
                                        @endif

                                        {{-- المرفقات - فيديو --}}
                                        @if (str()->startsWith($attachment->mime_type, 'video/'))
                                            <x-wirechat::video height="max-h-400px" :cover="false" source="{{ $attachment?->url }}" />
                                        @endif

                                        {{-- المرفقات - صور --}}
                                        @if (str()->startsWith($attachment->mime_type, 'image/'))
                                            @include('wirechat::livewire.chat.partials.image', [
                                                'previousMessage' => $previousMessage, 
                                                'message' => $message, 
                                                'nextMessage' => $nextMessage, 
                                                'belongsToAuth' => $belongsToAuth, 
                                                'attachment' => $attachment
                                            ])
                                        @endif
                                    @endif

                                    {{-- إذا كانت الرسالة عبارة عن إيموجي --}}
                                    @if ($isEmoji)
                                        <p class="fs-1 text-white">
                                            {{ $message->body }}
                                        </p>
                                    @endif

                                    {{-- نص الرسالة إذا لم تكن إيموجي --}}
                                    @if ($message->body && !$isEmoji)
                                        @include('wirechat::livewire.chat.partials.message', [
                                            'previousMessage' => $previousMessage,
                                            'message' => $message,
                                            'nextMessage' => $nextMessage,
                                            'belongsToAuth' => $belongsToAuth,
                                            'isGroup' => $isGroup,
                                            'attachment' => $attachment
                                        ])
                                    @endif

                                </div>


                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        @endforeach


    @endif

</main>
