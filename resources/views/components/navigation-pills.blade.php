<nav class="{{ $card ? 'card shadow-xs px-4 py-3 mb-4' : 'nav-align-top' }}">
    <ul class="nav nav-pills gap-1" role="tablist">
        @foreach ($tabs as $index => $tab)
            <li class="nav-item flex-fill flex-md-grow-0 {{ isset($tab['url']) ? '' : (isset($tab['route']) && $_user->ableTo($tab['route']) ? '' : '') }}" role="presentation">

                <a
                    @if (isset($tab['route'])) 
                        href="{{ route($tab['route'], $tab['params'] ?? []) }}" 
                    @elseif (isset($tab['url']))
                        href="/{{ $tab['url'] }}"
                    @else 
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $tab['id'] }}"
                        type="button"
                        role="tab"
                    @endif

                    class="nav-link w-100 text-center
                        @if (isset($tab['route']))
                            {{ isset($tab['params']) 
                                ? (Route::is($tab['route']) && request()->route('status') == $tab['params'] ? 'active' : '') 
                                : (Route::is($tab['route']) ? 'active' : '') }}
                        @elseif (isset($tab['url']) && !isset($tab['show']))
                            {{ Request::is($tab['url'] . '*') ? 'active' : '' }}
                        @elseif (isset($tab['show']))
                            {{ Request::get('show') == $tab['show'] ? 'active' : '' }}
                        @elseif (isset($tab['id']))
                            {{ $index === 0 ? 'active' : '' }}
                        @endif
                    "
                >
                    @isset($tab['icon'])
                        <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
                    @endisset

                    {{ $tab['title'] }}

                    @if ($tab['count'] ?? 0 > 0)
                        <small class="w-px-20 h-px-20 d-flex align-items-center justify-content-center bg-danger rounded-circle ms-3 text-white">
                            {{ $tab['count'] }}
                        </small>
                    @endif
                </a>

            </li>
        @endforeach
    </ul>
</nav>