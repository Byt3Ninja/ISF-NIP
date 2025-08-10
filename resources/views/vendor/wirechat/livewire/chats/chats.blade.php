@use('Namu\WireChat\Facades\WireChat')

<div x-data="{ selectedConversationId: '{{ request()->conversation ?? $selectedConversationId }}' }"
    x-on:open-chat.window="selectedConversationId= $event.detail.conversation; $wire.selectedConversationId= $event.detail.conversation;"
    x-init=" setTimeout(() => {
         conversationElement = document.getElementById('conversation-' + selectedConversationId);
    
         // Scroll to the conversation element
         if (conversationElement) {
             conversationElement.scrollIntoView({ behavior: 'smooth' });
         }
     }, 200);"
    class="d-flex flex-column bg-light transition-all h-100 overflow-hidden w-100 p-3">

    @php
        /* Show header if any of these conditions are true  */
        $showHeader = $showNewChatModalButton || $allowChatsSearch || $showHomeRouteButton || !empty($title);
    @endphp

    {{-- include header --}}
    @includeWhen($showHeader, 'wirechat::livewire.chats.partials.header')

    <main x-data
        @scroll.self.debounce="
            scrollTop = $el.scrollTop;
            scrollHeight = $el.scrollHeight;
            clientHeight = $el.clientHeight;

            if ((scrollTop + clientHeight) >= (scrollHeight - 1) && $wire.canLoadMore) {
                await $nextTick();
                $wire.loadMore();
            }
        "
        class="overflow-auto py-2 flex-grow position-relative"
        style="contain:content">

        {{-- loading indicator --}}

        @if (count($conversations) > 0)
            {{-- include list item --}}
            @include('wirechat::livewire.chats.partials.list')

            {{-- include load more if true --}}
            @includeWhen($canLoadMore, 'wirechat::livewire.chats.partials.load-more-button')
        @else
            <div class="w-100 d-flex align-items-center justify-content-center h-100">
                <h6 class="fw-bold text-muted">{{ __('wirechat::chats.labels.no_conversations_yet') }}</h6>
            </div>
        @endif
    </main>
</div>
