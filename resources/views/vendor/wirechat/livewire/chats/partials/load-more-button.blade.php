<section wire:loading.remove wire:target="search" class="w-100 d-flex justify-content-center my-3">
    <button 
        wire:loading.remove 
        wire:target="loadMore" 
        wire:loading.attr="disabled"
        dusk="loadMoreButton" 
        @click="$wire.loadMore()"
        class="btn btn-link btn-sm text-decoration-none text-secondary disabled:hover:cursor-not-allowed"
    >
        @lang('wirechat::chats.labels.load_more')
    </button>

    <div wire:loading wire:target="loadMore" class="ms-2">
        <div class="spinner-border text-primary spinner-border-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</section>
