@php($crumbs = breadcrumbs())

<style>
  .hide-before::before {
    display: none;
  }
</style>

@if($crumbs)
  @if(!Route::is('core.dashboard_view'))
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb align-items-center bg-card rounded p-2 shadow-xs">
        {{-- Back Button --}}
        <li class="breadcrumb-item active">
          <a 
            @if ($crumbs['child_group'] && !Route::is($crumbs['child_group'] . '.index'))
              href="{{ route($crumbs['child_group'] . '.index') }}"
            @else
              href="{{ route('core.dashboard_view') }}"
            @endif
            class="btn btn-icon btn-text-secondary rounded-pill waves-effect waves-light mb-0"
          >
            <i class="ti ti-arrow-left"></i>
          </a>
        </li>

        {{-- Dashboard Link --}}
        <li class="breadcrumb-item active hide-before">
          <a href="{{ route('core.dashboard_view') }}">
            {{ __('admin.Dashboard') }}
          </a>
        </li>

        {{-- Parent --}}
        <li class="breadcrumb-item active">
          {{ __('settings.navigation.' . $crumbs['parent']) }}
        </li>
        
        {{-- Child Group --}}
        @if ($crumbs['child_group'] && !Route::is($crumbs['child_group'] . '.index'))
          <li class="breadcrumb-item active">
            <a 
              href="{{ route($crumbs['child_group'] . '.index') }}"
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
