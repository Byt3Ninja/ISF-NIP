<div class="row text-center mb-6">
  @php
    $colors = ['success', 'danger', 'warning', 'primary'];
    $icons = ['brand-my-oppo', 'book', 'gavel', 'device-watch-check'] 
  @endphp
  @foreach ($programsSummary as $label => $value)
    <div class="col-md-3 mb-3">
      <div class="card card-border-shadow-{{ $colors[$loop->index] }} h-100">
        <div class="card-body text-center">
          <div class="d-flex align-items-center justify-content-center mb-2">
            <div class="avatar me-4">
              <div class="avatar-initial rounded bg-label-{{ $colors[$loop->index] }}">
                <i class="ti ti-{{ $icons[$loop->index] }}"></i>
              </div>
            </div>
            <h5 class="mb-0 fw-semibold">{{ $value }}</h5>
          </div>
          <small class="text-center">{{ __('startup.' . $label) }}</small>
        </div>
      </div>
    </div>
  @endforeach
</div>

@php
$cycles = [
    (object)[
        'id' => 1,
        'name' => 'Startup Hackathon 2024',
        'img_url' => 'https://via.placeholder.com/80',
        'type' => 'hackathon',
        'status' => 'completed',
        'start_date' => \Carbon\Carbon::parse('2024-01-10'),
        'completion_date' => \Carbon\Carbon::parse('2024-01-20'),
        'rank' => 1,
        'screening_score' => 95,
        'final_screening_score' => 98,
        'organization' => (object)[
            'name' => 'Tech Hub'
        ],
        'cohort' => 'Cohort 5'
    ],

    (object)[
        'id' => 2,
        'name' => 'General Innovation Program 2025',
        'img_url' => 'https://via.placeholder.com/80',
        'type' => 'general',
        'status' => 'ongoing',
        'start_date' => \Carbon\Carbon::parse('2025-02-01'),
        'completion_date' => \Carbon\Carbon::parse('2025-07-01'),
        'rank' => null,
        'screening_score' => 88,
        'final_screening_score' => 90,
        'organization' => (object)[
            'name' => 'Startup Egypt'
        ],
        'cohort' => 'Cohort 8',
    ],

    (object)[
        'id' => 3,
        'name' => 'AI Hackathon',
        'img_url' => 'https://via.placeholder.com/80',
        'type' => 'hackathon',
        'status' => 'ongoing',
        'start_date' => \Carbon\Carbon::parse('2025-03-15'),
        'completion_date' => \Carbon\Carbon::parse('2025-03-25'),
        'rank' => 2,
        'screening_score' => 90,
        'final_screening_score' => 92,
        'organization' => (object)[
            'name' => 'AI Labs'
        ],
        'cohort' => 'Cohort 9'
    ],

    (object)[
        'id' => 4,
        'name' => 'General Startup Bootcamp',
        'img_url' => 'https://via.placeholder.com/80',
        'type' => 'general',
        'status' => 'completed',
        'start_date' => \Carbon\Carbon::parse('2024-05-10'),
        'completion_date' => \Carbon\Carbon::parse('2024-09-10'),
        'rank' => null,
        'screening_score' => 82,
        'final_screening_score' => 85,
        'organization' => (object)[
            'name' => 'Innovation Hub'
        ],
        'cohort' => 'Cohort 6'
    ],
];
@endphp

<div class="row g-4">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-1">{{ __('admin.Programs') }}</h5>
      <div class="small text-secondary">
        <span class="mb-0">8 {{ __('admin.Programs') }}</span>
        <i class="ti ti-point-filled text-dark"></i>
        <span class="mb-0">4 {{ __('admin.Active') }}</span>
      </div>
    </div>
    <div class="card-body">
      @foreach ($cycles as $program)
        <div class="col-12">
          <div class="card shadow-none border overflow-hidden program-card mb-4">
    
            <!-- Card Header -->
            <div class="card-header py-3">
              <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                
                <!-- Left: Logo + Name + Organization -->
                <div class="d-flex align-items-center gap-3 mb-4 mb-md-0">
                  <div class="bg-light d-flex align-items-center justify-content-center rounded-3 border" 
                       style="width: 58px; height: 58px; overflow: hidden; flex-shrink: 0;">
                    @if(isset($program->img_url) && $program->img_url)
                      <img src="{{ $program->img_url }}" 
                           alt="{{ $program->name }}" 
                           class="img-fluid p-1" 
                           style="object-fit: contain; max-height: 100%;">
                    @else
                      <i class="ti ti-rocket fs-2 text-secondary"></i>
                    @endif
                  </div>
                  
                  <div>
                    <h6 class="mb-1 text-dark text-truncate">{{ $program->name }}</h6>
                    <p class="mb-0 text-muted small d-flex align-items-center gap-1">
                      <i class="ti ti-building"></i>
                      {{ $program->organization->name ?? 'N/A' }}
                    </p>
                  </div>
                </div>
    
                <!-- Right: Program Type & Status -->
                <div class="d-flex align-items-center gap-3 text-end">
                  <!-- You can customize "Program Type" - replace with actual field if available -->
                  @if(isset($program->type) && $program->type)
                    <span class="badge bg-badge bg-label-{{ $program->type == 'hackathon' ? 'primary' : 'secondary' }} py-2 text-capitalize">
                      {{ __('startup.' . $program->type) }}
                    </span>
                  @endif
    
                  <!-- Status -->
                  <span class="badge py-2
                    @if($program->status === 'completed') bg-label-success 
                    @elseif($program->status === 'ongoing') bg-label-info 
                    @else bg-label-secondary @endif">
                    {{ __('startup.' . $program->status) }}
                  </span>
                </div>
              </div>
            </div>
    
            <!-- Card Body -->
            <div class="card-body">
              <div class="row g-4 align-items-center">
    
                <!-- Start Date -->
                <div class="col-6 col-md-4 col-lg-2">
                  <small class="text-muted d-block text-uppercase fw-semibold mb-1">
                    {{ __('startup.start_date') }}
                  </small>
                  <span class="fw-medium text-dark">
                    {{ $program->start_date?->format('M d, Y') ?? '—' }}
                  </span>
                </div>
                
                <!-- End Date -->
                <div class="col-6 col-md-4 col-lg-2">
                  <small class="text-muted d-block text-uppercase fw-semibold mb-1">
                    {{ __('startup.completion_date') }}
                  </small>
                  <span class="fw-medium text-dark">
                    {{ $program->completion_date?->format('M d, Y') ?? '—' }}
                  </span>
                </div>
    
                <!-- Screening Score -->
                <div class="col-6 col-md-4 col-lg-2">
                  <small class="text-muted d-block text-uppercase fw-semibold mb-1">
                    {{ __('startup.screening_score') }}
                  </small>
                  <span class="fw-bold fs-5">
                    {{ isset($program->screening_score) ? $program->screening_score : '0' }} / 100
                  </span>
                </div>
                
                <!-- Final Screening Score -->
                @if ($program->type == 'hackathon')
                  <div class="col-6 col-md-2 col-lg-2">
                    <small class="text-muted d-block text-uppercase fw-semibold mb-1">
                      {{ __('startup.rank') }}
                    </small>
                    <span class="fw-bold fs-5">
                      <i class="ti ti-medal fs-3 text-dark"></i>
                      {{ $program->rank ?? 'ـ' }}
                    </span>
                  </div>
                @else
                  <div class="col-6 col-md-2 col-lg-2">
                    <small class="text-muted d-block text-uppercase fw-semibold mb-1">
                      {{ __('startup.final_screening_score') }}
                    </small>
                    <span class="fw-bold fs-5">
                      {{ $program->final_screening_score ?? '0' }} / 100
                    </span>
                  </div>
                @endif
    
                <!-- Cohort -->
                <div class="col-6 col-md-2 col-lg-2">
                  <small class="text-muted d-block text-uppercase fw-semibold mb-1">
                    {{ __('startup.cohort') }}
                  </small>
                  <span class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-1 fw-medium">
                    {{ $program->cohort ?? '—' }}
                  </span>
                </div>
                
                <!-- Action -->
                <div class="col-6 col-md-2 col-lg-2">
                  <div class="text-end">
                    <a href="{{ route('company.alumni_view', ['id' => $company->id, 'cycle_id' => $program->id]) }}"
                      class="btn btn-primary text-nowrap">
                      <i class="ti ti-eye me-2"></i>
                      {{ __('startup.view_details') }}
                    </a>
                  </div>
                </div>
    
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>