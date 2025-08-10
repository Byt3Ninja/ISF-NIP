@use('Namu\WireChat\Facades\WireChat')

<ul wire:loading.delay.long.remove wire:target="search" class="p-2 list-unstyled w-100">
    @foreach ($conversations as $key => $conversation)
        @php
            $group = $conversation->isGroup() ? $conversation->group : null;
            $receiver = $conversation->isGroup() ? null : ($conversation->isPrivate() ? $conversation->peer_participant?->participantable : $this->auth);
            $lastMessage = $conversation->lastMessage;
            $isReadByAuth = $conversation?->readBy($conversation->auth_participant ?? $this->auth) || $selectedConversationId == $conversation->id;
            $belongsToAuth = $lastMessage?->belongsToAuth();
        @endphp

        <li x-data="{
            conversationID: @js($conversation->id),
            showUnreadStatus: @js(!$isReadByAuth),
            handleChatOpened(event) {
                if (event.detail.conversation == this.conversationID) {
                    this.showUnreadStatus = false;
                }
                $wire.selectedConversationId = event.detail.conversation;
            },
            handleChatClosed(event) {
                $wire.selectedConversationId = null;
                selectedConversationId = null;
            },
            handleOpenChat(event) {
                if (this.showUnreadStatus == event.detail.conversation == this.conversationID) {
                    this.showUnreadStatus = false;
                }
            }
        }"  
        id="conversation-{{ $conversation->id }}" 
        wire:key="conversation-em-{{ $conversation->id }}-{{ $conversation->updated_at->timestamp }}"
        x-on:chat-opened.window="handleChatOpened($event)"
        x-on:chat-closed.window="handleChatClosed($event)"
        class="mb-2">

            <a 
                @if ($widget) 
                    tabindex="0" 
                    role="button" 
                    dusk="openChatWidgetButton"
                    @click="$dispatch('open-chat', { conversation: @js($conversation->id) })"
                    @keydown.enter="$dispatch('open-chat', { conversation: @js($conversation->id) })"
                @else
                    wire:navigate href="{{ route(WireChat::viewRouteName(), $conversation->id) }}" 
                @endif
                class="d-flex align-items-center p-2 rounded bg-light bg-opacity-50 hover-shadow-sm text-decoration-none position-relative"
                style="{{ $selectedConversationId == $conversation?->id ? 'background-color: var(--wc-light-secondary); border-right: 4px solid var(--wc-brand-primary);' : '' }}">

                {{-- Avatar --}}
                <div class="flex-shrink-0 me-3">
                    <x-wirechat::avatar disappearing="{{ $conversation->hasDisappearingTurnedOn() }}"
                        group="{{ $conversation->isGroup() }}"
                        :src="$group ? $group?->cover_url : $receiver?->cover_url ?? null" 
                        class="rounded-circle" style="width:48px; height:48px;" />
                </div>

                {{-- Chat Info --}}
                <aside class="d-flex flex-column flex-grow-1 border-bottom pb-2" style="border-color: var(--wc-light-border);">

                    {{-- Name --}}
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <h6 class="mb-0 text-truncate fw-medium text-dark">
                            {{ $group ? $group?->name : $receiver?->display_name }}
                        </h6>
                        @if ($conversation->isSelfConversation())
                            <span class="fw-medium">({{ __('wirechat::chats.labels.you') }})</span>
                        @endif
                    </div>

                    {{-- Message body --}}
                    @if ($lastMessage != null)
                        @include('wirechat::livewire.chats.partials.message-body')
                    @endif
                </aside>

                {{-- Unread dot --}}
                @if ($lastMessage != null && !$lastMessage?->ownedBy($this->auth) && !$isReadByAuth)
                    <div x-show="showUnreadStatus" dusk="unreadMessagesDot" class="ms-2 my-auto text-center">
                        <span dusk="unreadDotItem" class="visually-hidden">unread dot</span>
                        <svg style="color: var(--wc-brand-primary);" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                            fill="currentColor" class="bi bi-dot" viewBox="0 0 16 16">
                            <path d="M8 9.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z" />
                        </svg>
                    </div>
                @endif
            </a>
        </li>
    @endforeach
</ul>
