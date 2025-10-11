<a 
  href="/dashboard/hackathon/details/{{ $hackathon['id'] }}"
  class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4"
>
  <div>
    <h6 class="mb-1 fw-semibold">{{ $hackathon['title'] }}</h6>
    <small class="text-muted">
      <i class="ti ti-calendar me-1"></i> {{ $hackathon['date'] }}
    </small>
  </div>
  <div class="d-flex align-items-center">
    <span class="badge bg-label-success me-2">
      <i class="ti ti-check"></i>
      {{ $l == 'en' ? 'Completed' : 'مكتمل' }}
    </span>
    {{-- <span class="badge bg-label-info">
      <i class="ti ti-trophy me-1"></i>
      {{ $l == 'en' ? 'View Results' : 'عرض النتائج' }}
    </span> --}}
  </div>
</a>