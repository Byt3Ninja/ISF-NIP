<!-- Mentor Card -->
@if(isset($mentor) && $mentor)
<div class="col-md-4 rounded mb-6">
  <div class="position-relative">
      <img 
        src="{{ $mentor?->image_url ?? '/images/placeholder.jpg' }}" 
        onerror="this.onerror=null;this.src='/images/placeholder.jpg';"
        alt="Mentor Image" 
        class="img-fluid rounded mb-2" 
        style="height: 293px; width: 100%; object-fit: cover;"
      >

      <!-- Absolute Badge -->
      <span class="position-absolute top-0 start-0 m-2 badge bg-label-{{ $mentor?->is_public ? 'primary' : 'info' }}">
        {{ $mentor?->is_public ? __('admin.Public') : __('admin.Organization') }}
      </span>
  </div>
  <div class="card card-body mx-2 p-4"
    style="height: 380px; z-index: 1; margin-top: -25px; border-radius: 15px;">
    <div
      class="bg-white shadow-sm border border-label-secondary rounded px-2 py-1 d-flex justify-content-between align-items-center mb-2"
      style="height: 55px;">
      <a href="{{ route('mentors.profile_view', $mentor?->id ?? 0) }}" class="d-flex flex-column">
        <span class="text-black fw-bold fs-6">{{ $mentor?->name }}</span>
        <small class="text-secondary">{{ $mentor?->title }}</small>
      </a>
      @if ($mentor?->bio->linkedin ?? 0)
      <a title="Linkedin URL" target="_blank" href="{{ $mentor?->bio->linkedin }}">
        <img src="/icons/linkedin.svg" width="30" height="30" />
      </a>
      @endif
    </div>
    
    <!-- Mentor Bio -->
    <div class="mb-3">
      <div class="d-flex align-items-center mb-2">
        <i class="ti ti-bubble-text ti-sm me-2"></i>
        <p class="fs-6 fw-bold text-black mb-0">{{ __('dashboard.Bio') }}</p>
      </div>
      @if ($mentor?->bio && !empty(trim($mentor?->bio->{'bio_' . $l})))
      <div style="max-height: 80px; overflow-y: auto;">
        <small class="text-muted line-clamp-3">
          {{ $mentor?->bio->{'bio_' . $l} }}
        </small>
      </div>
      @else
      <div class="text-muted">
        <small>{{ __('admin.N/A') }}</small>
      </div>
      @endif
    </div>
    <!-- / Mentor Bio -->

    <!-- Mentor Expertises -->
    <div class="mb-3">
      <div class="d-flex align-items-center mb-2">
        <i class="ti ti-bulb ti-sm me-2"></i>
        <p class="fs-6 fw-bold text-dark mb-0">{{ __('admin.Expertises') }}</p>
      </div>
      @if ($mentor?->mentorExpertises->isNotEmpty())
      <div class="d-flex flex-wrap gap-1" style="max-height: 70px; overflow-y: auto;">
        @php
        $expertises = $mentor?->mentorExpertises;
        @endphp
        @foreach ($expertises as $expert)
        <span class="badge bg-label-warning rounded-pill px-2 py-1 small">
          {{ $expert->title }}
        </span>
        @endforeach
      </div>
      @else
      <div class="text-muted">
        <small>{{ __('admin.N/A') }}</small>
      </div>
      @endif
    </div>
    <!-- / Mentor Expertises -->
    <!-- Badge Public / Private -->
    {{-- <div class="mb-3">
      <div class="d-flex align-items-center mb-2">
        <i class="ti ti-lock ti-sm me-2"></i>
        <p class="fs-6 fw-bold text-dark mb-0">{{ __('admin.Visibility') }}</p>
      </div>
      <div class="d-flex flex-wrap gap-1" style="max-height: 70px; overflow-y: auto;">
        <span class="badge bg-label-info rounded-pill px-2 py-1 small">
          {{ $mentor?->is_public ? __('admin.Public') : __('admin.Organization') }}
        </span>
      </div>
    </div> --}}
    <!-- Stats Section -->
    <div class="row g-2 mb-3">
      <div class="col-6">
        <div class="bg-label-secondary rounded p-2 text-center">
          <div class="d-flex align-items-center text-dark">
            <i class="ti ti-books ti-sm me-1"></i>
            <small class="fw-semibold me-2">{{ __('cycles.Cycles') }}</small>
            <span class="fw-bold text-dark">{{ $mentor?->mentorCycles->count() }}</span>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="bg-label-secondary rounded p-2 text-center">
          <div class="d-flex align-items-center text-dark">
            <i class="ti ti-star ti-sm text-dark me-1"></i>
            <small class="fw-semibold me-2">{{ __('admin.Rating') }}</small>
            <span class="fw-bold text-dark">{{ number_format($mentor?->avg_rating ?: 0, 1) }}</span>
          </div>
        </div>
      </div>
    </div>
    <!-- / Stats Section -->

    <!-- Action Button -->
    <div class="row px-4 mt-auto">
      @if(isset($getCycle) && $getCycle?->id != 0 || isset($getCycle) && $getCycle?->id != null)
        @if ($canRequestMentorship && auth()->user()?->company?->id && $mentor?->id)
          @if($mentor?->schedule->isNotEmpty())
            <button 
              type="button" 
              class="btn btn-primary" 
              onclick="if (typeof fetchSlots === 'function') { fetchSlots({{ $mentor?->id }}, {{ $getCycle?->id }}, {{ $getCycle?->organization_id }}); }"
            >
              {{ __('cycles.Request_Mentorship') }}
              <i class="ti ti-calendar-plus ms-2"></i>
            </button>
            
          @else
            <button type="button" class="btn btn-primary" disabled>
              {{ __('cycles.Not_Available_Request_Mentorship') }}
              <i class="ti ti-calendar-plus ms-2"></i>
            </button>
          @endif
        @else
          <a href="{{ route('mentors.profile_view', $mentor?->id ?? 0) }}" class="btn btn-primary">
            {{ __('admin.View Profile') }}
          </a>
        @endif
        @else
            <button type="button" class="btn btn-primary" disabled>
              {{ __('cycles.Join_Cycle_First') }}
              <i class="ti ti-calendar-plus ms-2"></i>
            </button>
          @endif
    </div>
  </div>
</div>
<!--/ Mentor Card -->
@endif