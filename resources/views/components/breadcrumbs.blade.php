@php
    $hideBreadcrumbRoutes = ['core.dashboard_view'];
    $hideBreadcrumbPaths = ['dashboard', 'dashboard/dashboard'];
    $currentRoute = Route::currentRouteName();
    $currentPath = request()->path();
    $shouldHide = in_array($currentRoute, $hideBreadcrumbRoutes) || in_array($currentPath, $hideBreadcrumbPaths);
@endphp
@if(isset($breadcrumbs) && count($breadcrumbs) > 1 && !$shouldHide)
<nav class="d-flex align-items-center mb-4 flex-wrap gap-2" aria-label="{{ __('breadcrumb.breadcrumb_navigation') ?? 'Breadcrumb' }}">
    {{-- Back Button --}}
    @if(isset($backUrl) && $backUrl)
    <a href="{{ $backUrl }}"
       class="btn btn-icon btn-outline-secondary rounded-circle flex-shrink-0 {{ $l == 'ar' ? 'ms-2' : 'me-2' }}"
       title="{{ __('breadcrumb.back') }}"
       aria-label="{{ __('breadcrumb.back') }}"
       data-bs-toggle="tooltip"
       data-bs-placement="{{ $l == 'ar' ? 'left' : 'right' }}">
        <i class="ti ti-arrow-{{ $l == 'ar' ? 'right' : 'left' }} ti-md"></i>
    </a>
    @endif

    {{-- Breadcrumb Trail --}}
    <ol class="breadcrumb breadcrumb-style1 mb-0 flex-wrap">
        @foreach($breadcrumbs as $index => $crumb)
            @if($crumb['active'] ?? false)
                <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 200px;">
                    {{ $crumb['label'] }}
                </li>
            @else
                <li class="breadcrumb-item text-truncate" style="max-width: 200px;">
                    <a href="{{ $crumb['url'] }}" class="text-decoration-none">
                        @if($index === 0)
                            <i class="ti ti-home-2 {{ $l == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                        @endif
                        {{ $crumb['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
