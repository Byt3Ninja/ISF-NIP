@use('Namu\WireChat\Facades\WireChat')

<header class="px-3 position-sticky top-0 w-100 py-2 bg-light border-bottom" dusk="header">

    {{-- Title/name and Icon --}}
    <section class="d-flex justify-content-between align-items-center pb-2">

        @if (isset($title))
            <div class="d-flex align-items-center gap-2 text-truncate" wire:ignore>
                <h2 class="h4 fw-bold mb-0">{{ $title }}</h2>
            </div>
        @endif

        <div class="d-flex align-items-center gap-3">
            @if ($showNewChatModalButton)
                <x-wirechat::actions.new-chat widget="{{ $this->isWidget() }}">
                    <button id="open-new-chat-modal-button" class="btn btn-link p-0 d-flex align-items-center">
                        <svg class="text-secondary hover:text-dark" xmlns="http://www.w3.org/2000/svg"
                            width="32" height="32" viewBox="0 0 24 24">
                            <g fill="none" stroke="currentColor">
                                <path
                                    d="M12.875 5C9.225 5 7.4 5 6.242 6.103a4 4 0 0 0-.139.139C5 7.4 5 9.225 5 12.875V17c0 .943 0 1.414.293 1.707S6.057 19 7 19h4.125c3.65 0 5.475 0 6.633-1.103a4 4 0 0 0 .139-.139C19 16.6 19 14.775 19 11.125" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 10h6m-6 4h3m7-6V2m-3 3h6" />
                            </g>
                        </svg>
                    </button>
                </x-wirechat::actions.new-chat>
            @endif

            @if ($showHomeRouteButton)
                <a id="redirect-button" href="{{ config('wirechat.home_route', '/') }}"
                    class="d-flex align-items-center">
                    <svg class="text-secondary hover:text-dark" xmlns="http://www.w3.org/2000/svg" width="32"
                        height="32" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor">
                            <path
                                d="M5 12.76c0-1.358 0-2.037.274-2.634c.275-.597.79-1.038 1.821-1.922l1-.857C9.96 5.75 10.89 4.95 12 4.95s2.041.799 3.905 2.396l1 .857c1.03.884 1.546 1.325 1.82 1.922c.275.597.275 1.276.275 2.634V17c0 1.886 0 2.828-.586 3.414S16.886 21 15 21H9c-1.886 0-2.828 0-3.414-.586S5 18.886 5 17z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.5 21v-5a1 1 0 0 0-1-1h-3a1 1 0 0 0-1 1v5" />
                        </g>
                    </svg>
                </a>
            @endif
        </div>
    </section>

    {{-- Search input --}}
    @if ($allowChatsSearch)
        <section class="mt-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="text-secondary" style="width: 1.25rem; height: 1.25rem;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <input id="chats-search-field" name="chats_search" maxlength="100" type="search"
                    wire:model.live.debounce='search'
                    placeholder="{{ __('wirechat::chats.inputs.search.placeholder') }}"
                    autocomplete="off" class="form-control border-0 shadow-none">
            </div>
        </section>
    @endif
</header>
