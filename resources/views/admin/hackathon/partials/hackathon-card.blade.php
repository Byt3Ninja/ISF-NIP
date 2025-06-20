<div class="card h-100">
  <div class="card-header p-0">
    @php($color = $hackathon['status'] == 'Upcoming' ? '696cff' : '28c76f')
    <img 
      src="https://placehold.co/600x250/{{ $color }}/ffffff?text={{ $hackathon['title'] }}" 
      class="card-img-top" 
      alt="{{ $hackathon['title'] }}"
      style="height: 200px; object-fit: cover;"
    />
    <div class="position-absolute top-0 end-0 m-3">
      <span class="badge bg-label-{{ $hackathon['status'] == 'Upcoming' ? 'primary' : 'success' }} hackathon-status-badge">
        {{ $hackathon['status'] }}
      </span>
    </div>
  </div>
  <div class="card-body d-flex flex-column mt-4">
    <h5 class="card-title fw-semibold">{{ $hackathon['title'] }}</h5>
    <div class="mb-3">
      <small class="text-muted d-block">
        <i class="ti ti-calendar me-1"></i> {{ $hackathon['date'] }}
      </small>
      <small class="text-muted d-block">
        <i class="ti ti-map-pin me-1"></i> {{ $hackathon['location'] }}
      </small>
    </div>
    <p class="card-text text-muted">{{ $hackathon['description'] }}</p>
    <div class="mb-3">
      @foreach ($hackathon['tags'] as $tag)
        <span class="badge bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger', 'secondary'][array_rand(['primary', 'success', 'info', 'warning', 'danger', 'secondary'])] }} me-1">
          {{ $tag }}
        </span>
      @endforeach
    </div>
    @php( $isUpcoming = $hackathon['status'] == 'Upcoming' )
    <div class="mt-auto">
      <div class="d-flex w-100 mb-2" role="group">
        <a href="#" class="btn btn-outline-primary me-2" style="flex: 1;">
          <i class="ti ti-info-circle me-1"></i>
          {{ $l == 'en' ? 'Details' : 'التفاصيل' }}
        </a>
        <button type="button" class="btn btn-{{ $hackathon['status'] == 'Upcoming' ? 'primary' : 'success'}}" style="flex: 1;" data-bs-toggle="modal"
          data-bs-target="#applyModal{{ $loop->index + 1 }}">
          <i class="ti ti-{{  $hackathon['status'] == 'Upcoming' ? 'send' : 'users' }} me-1"></i>
          {{ $hackathon['status'] == 'Upcoming' ? __('admin.ApplyHackathon') : __('admin.Manage Team') }}
        </button>
      </div>
      @if ($hackathon['status'] == 'Upcoming')
        <small class="text-muted text-center d-block fw-medium mt-2">
          {{ $l == 'en' ? 'Applications open until ' . date('F j, Y', strtotime('+1 week')) : 'التقديم مفتوح حتى ' . date('j F Y', strtotime('+1 week')) }}
        </small>
      @else
        <small class="text-warning text-center d-block fw-medium mt-2">
          {{ $l == 'en' ? 'Applications closed - Hackathon in progress' : 'التقديم مغلق - الهاكثون جاري' }}
        </small>
      @endif
    </div>
  </div>
</div>
