{{-- For a founder already in a program: the open programs their company can still apply to. --}}
@php
  $otherPrograms = $company ? \App\Cycle::joinableBy($company) : collect();
@endphp
@if ($otherPrograms->isNotEmpty())
  <div class="card mb-6 mt-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ __('startup.other_programs_title') }}</h5>
    </div>
    <div class="list-group list-group-flush">
      @foreach ($otherPrograms as $program)
        <div class="list-group-item d-flex align-items-center justify-content-between gap-4">
          <div>
            <h6 class="mb-1">{{ $program->title }}</h6>
            @if ($program->organization)
              <small class="text-muted">{{ $program->organization->{'name_' . app()->getLocale()} ?? $program->organization->name_en }}</small>
            @endif
          </div>
          <a href="{{ url('/dashboard/cycles/view/' . $program->id) }}" class="btn btn-primary btn-sm text-nowrap">
            {{ __('startup.other_programs_apply') }}
          </a>
        </div>
      @endforeach
    </div>
  </div>
@endif
