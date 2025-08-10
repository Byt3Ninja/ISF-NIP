@use('Namu\WireChat\Facades\WireChat')

@php
    $group = $conversation->group;
@endphp

<header class="w-100 sticky-top bg-light border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
    {{-- Return Button --}}
    <a 
        @if ($this->isWidget()) 
            @click="$dispatch('close-chat',{conversation: {{ json_encode($conversation->id) }} })"
            dusk="return_to_home_button_dispatch"
        @else
            href="{{ route(WireChat::indexRouteName(), $conversation->id) }}"
            dusk="return_to_home_button_link" 
        @endif
        class="text-dark me-2 {{ !$this->isWidget() ? 'd-lg-none' : '' }}"
        id="chatReturn"
    >
        <i class="bi bi-arrow-left fs-5"></i>
    </a>

    {{-- Receiver Info --}}
    <div class="flex-grow-1 d-flex align-items-center">
        @if ($conversation->isGroup())
            <x-wirechat::actions.show-group-info conversation="{{ $conversation->id }}" widget="{{ $this->isWidget() }}">
                <div class="d-flex align-items-center gap-2 cursor-pointer">
                    <x-wirechat::avatar disappearing="{{ $conversation->hasDisappearingTurnedOn() }}"
                        :group="true" :src="$group?->cover_url ?? null"
                        class="rounded-circle" style="width: 40px; height: 40px;" />
                    <h6 class="fw-bold text-truncate mb-0">
                        {{ $group?->name }}
                    </h6>
                </div>
            </x-wirechat::actions.show-group-info>
        @else
            <x-wirechat::actions.show-chat-info conversation="{{ $conversation->id }}" widget="{{ $this->isWidget() }}">
                <div class="d-flex align-items-center gap-2 cursor-pointer">
                    <x-wirechat::avatar disappearing="{{ $conversation->hasDisappearingTurnedOn() }}"
                        :group="false" :src="$receiver?->cover_url ?? null"
                        class="rounded-circle" style="width: 40px; height: 40px;" />
                    <h6 class="fw-bold text-truncate mb-0">
                        {{ $receiver?->display_name }}
                        @if ($conversation->isSelfConversation())
                            ({{ __('wirechat::chat.labels.you') }})
                        @endif
                    </h6>
                </div>
            </x-wirechat::actions.show-chat-info>
        @endif
    </div>

    {{-- Dropdown Actions --}}
    <div class="dropdown">
        <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical fs-5"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @if ($conversation->isGroup())
                <li>
                    <x-wirechat::actions.show-group-info conversation="{{ $conversation->id }}" widget="{{ $this->isWidget() }}">
                        <button class="dropdown-item">
                            {{ __('wirechat::chat.actions.open_group_info.label') }}
                        </button>
                    </x-wirechat::actions.show-group-info>
                </li>
            @else
                <li>
                    <x-wirechat::actions.show-chat-info conversation="{{ $conversation->id }}" widget="{{ $this->isWidget() }}">
                        <button class="dropdown-item">
                            {{ __('wirechat::chat.actions.open_chat_info.label') }}
                        </button>
                    </x-wirechat::actions.show-chat-info>
                </li>
            @endif

            @if ($this->isWidget())
                <li>
                    <button class="dropdown-item" @click="$dispatch('close-chat',{conversation: {{ json_encode($conversation->id) }} })">
                        @lang('wirechat::chat.actions.close_chat.label')
                    </button>
                </li>
            @else
                <li>
                    <a class="dropdown-item" href="{{ route(WireChat::indexRouteName()) }}">
                        @lang('wirechat::chat.actions.close_chat.label')
                    </a>
                </li>
            @endif

            @if (!$conversation->isGroup())
                <li>
                    <button class="dropdown-item" wire:click="clearConversation" wire:confirm="{{ __('wirechat::chat.actions.clear_chat.confirmation_message') }}">
                        @lang('wirechat::chat.actions.clear_chat.label')
                    </button>
                </li>
                <li>
                    <button class="dropdown-item text-danger" wire:click="deleteConversation" wire:confirm="{{ __('wirechat::chat.actions.delete_chat.confirmation_message') }}">
                        @lang('wirechat::chat.actions.delete_chat.label')
                    </button>
                </li>
            @endif

            @if ($conversation->isGroup() && !$this->auth->isOwnerOf($conversation))
                <li>
                    <button class="dropdown-item text-danger" wire:click="exitConversation" wire:confirm="{{ __('wirechat::chat.actions.exit_group.confirmation_message') }}">
                        @lang('wirechat::chat.actions.exit_group.label')
                    </button>
                </li>
            @endif
        </ul>
    </div>
</header>
