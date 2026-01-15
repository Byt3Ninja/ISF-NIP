<nav class="{{ $card ? 'card shadow-xs px-4 py-3 mb-4' : 'nav-align-top' }}">
    <ul class="nav nav-pills gap-1" role="tablist">
        @foreach ($tabs as $tab)
            <li class="nav-item {{ isset($tab['route']) && $_user->ableTo($tab['route']) ? '' : 'd-none' }}" role="presentation">
                <a
                    @if (isset($tab['route'])) 
                        href="{{ route($tab['route']) }}" 
                    @elseif (isset($tab['url']))
                        href="/{{ $tab['url'] }}"
                    @else 
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $tab['id'] }}"
                    @endif

                    @if (isset($tab['route']))
                        class="nav-link {{ Route::is($tab['route']) ? 'active' : '' }}"
                    @elseif (isset($tab['url']) && !isset($tab['show']))
                        class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}"
                    @elseif (isset($tab['show']))
                        class="nav-link {{ Request::get('show') == $tab['show'] ? 'active' : '' }}"
                    @endif
                >
                    @isset($tab['icon'])
                        <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
                    @endisset
                    {{ $tab['title'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
