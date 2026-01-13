@php
if(isset($crumbs)){
  $crumbs = breadcrumbs();
}
@endphp

<style>
  .hide-before::before {
    display: none;
  }
</style>

@if(isset($crumbs))
  @if(!Route::is('core.dashboard_view') && !Route::is('core.admin_access'))
      <ol class="breadcrumb align-items-center bg-card rounded p-2 shadow-xs">
        {{-- Back Button --}}
        <li class="breadcrumb-item active">
          <a 
            @if (isset($crumbs['group_route']) && !Route::is($crumbs['group_route']))
              href="{{ route($crumbs['group_route']) }}"
            @elseif (isset($crumbs['child_group']) && !Route::is($crumbs['child_group'] . '.index'))
              href="{{ (Route::has($crumbs['child_group'] . '.index')) ? route($crumbs['child_group'] . '.index') : url()->previous() }}"
            @else
              href="/dashboard"
            @endif
            class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light mb-0"
          >
            <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }}"></i>
          </a>
        </li>

        {{-- Dashboard Link --}}
        <li class="breadcrumb-item active hide-before">
          <a href="/dashboard">
            {{ __('admin.Dashboard') }}
          </a>
        </li>

        {{-- Parent --}}
        @if (isset($crumbs['parent']))
          <li class="breadcrumb-item active">
            {{ __('settings.navigation.' . $crumbs['parent']) }}
          </li>
        @endif
        
        {{-- Child Group --}}
        @if (isset($crumbs['child_group']) && !Route::is($crumbs['child_group'] . '.index'))
          <li class="breadcrumb-item active">
            <a 
              @if (isset($crumbs['group_route']) && !Route::is($crumbs['group_route']))
                href="{{ route($crumbs['group_route']) }}"
              @else
                href="{{ (Route::has($crumbs['child_group'] . '.index')) ? route($crumbs['child_group'] . '.index') : url()->previous() }}"
              @endif
            >
              {{ __('settings.navigation.' . $crumbs['child_group']) }}
            </a>
          </li>
        @endif

        {{-- Child --}}
        <li class="breadcrumb-item" aria-current="page">
          {{ __('settings.navigation.' . $crumbs['child']) }}
        </li>
      </ol>
    </nav>
  @endif
@endif
