{{-- Shown to a founder whose company is active in more than one cycle: choose the one the screens work in. --}}
@php
  $switchable = $company->activeEnrollments()->with('cycle')->orderBy('id')->get()->filter(fn ($enrollment) => $enrollment->cycle);
@endphp
@if ($switchable->count() > 1)
  <li class="w-100 px-3 mb-2 pt-2">
    <div class="dropdown">
      <small class="text-muted d-block mb-1">{{ __('startup.program_switcher_label') }}</small>
      <button type="button" class="btn btn-label-primary btn-sm w-100 dropdown-toggle text-truncate" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $switchable->firstWhere('cycle_id', (int) $company->cycle)?->cycle->title }}
      </button>
      <ul class="dropdown-menu w-100">
        @foreach ($switchable as $enrollment)
          <li>
            @if ($enrollment->cycle_id === (int) $company->cycle)
              <span class="dropdown-item active text-truncate">{{ $enrollment->cycle->title }}</span>
            @else
              <form method="POST" action="{{ route('company.switch_program', $enrollment->id) }}">
                @csrf
                <button type="submit" class="dropdown-item text-truncate">{{ $enrollment->cycle->title }}</button>
              </form>
            @endif
          </li>
        @endforeach
      </ul>
    </div>
  </li>
@endif
