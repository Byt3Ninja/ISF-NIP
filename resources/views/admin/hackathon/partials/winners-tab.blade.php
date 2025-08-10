@php
$winners = [
  (object)[
    'name' => 'Innovative Minds',
    'description' => 'A team of forward-thinkers dedicated to creating impactful solutions.',
    'logo' => '/images/background/logo.png',
    'project' => 'Smart Medical Diagnosis System',
    'award_date' => 'August 4, 2025',
    'teamName' => 'Team Alpha',
    'teamMembers' => [
      (object)[
        'name' => 'Alice Johnson',
        'role' => 'Project Manager'
      ],
      (object)[
        'name' => 'Bob Smith',
        'role' => 'Lead Developer'
      ],
      (object)[
        'name' => 'Charlie Brown',
        'role' => 'UX Designer'
      ]
    ],
    'projectTitle' => 'Smart Medical Diagnosis System',
    ],
    (object)[
      'name' => 'InnovateTech',
      'description' => 'Revolutionary AI-powered solutions that set a new industry standard for innovation and efficiency.',
      'logo' => '/images/background/logo.png',
      'projectTitle' => 'AI-Powered Business Analytics',
      'award_date' => 'August 4, 2025',
      'teamName' => 'Team Beta',
      'teamMembers' => [
        (object)[
        'name' => 'David Wilson',
        'role' => 'Data Scientist'
        ],
        (object)[
        'name' => 'Eva Green',
        'role' => 'AI Engineer'
        ],
        (object)[
        'name' => 'Frank Wright',
        'role' => 'Business Analyst'
        ]
      ],
    ],
    (object)[
      'name' => 'GreenFuture',
      'description' => 'Sustainable technology solutions focused on environmental conservation and renewable energy systems.',
      'logo' => '/images/background/logo.png',
      'projectTitle' => 'Eco-Friendly Smart Grid',
      'award_date' => 'August 4, 2025',
      'teamName' => 'Team Gamma',
      'teamMembers' => [
        (object)[
        'name' => 'Grace Lee',
        'role' => 'Environmental Scientist'
        ],
        (object)[
        'name' => 'Henry Kim',
        'role' => 'Renewable Energy Engineer'
        ],
        (object)[
        'name' => 'Isabella Martinez',
        'role' => 'Sustainability Consultant'
        ]
      ],
    ],
  ];
@endphp


<div class="container bg-white p-8">
  <div class="row ">
    @foreach ($winners as $winner)
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card h-100 border-0 shadow">
        <!-- Header -->
        <div class="card-header bg-light border-0 d-flex align-items-center py-3">
          <img src="{{ $winner->logo }}" class="rounded-circle me-3 border" alt="Startup Logo" width="45" height="45">
          <div>
            <h6 class="mb-0 fw-semibold text-dark">{{ $winner->name }}</h6>
            <small class="text-dark">{{ $winner->teamName }}</small>
          </div>
        </div>
        
        <div class="card-body px-4 py-4">
          <!-- Project Title -->
          <h5 class="card-title text-dark mb-3 fw-bold lh-base">{{ $winner->projectTitle }}</h5>
          
          <!-- Project Description -->
          <p class="card-text text-muted mb-4 lh-lg">{{ $winner->description }}</p>
          
          <!-- Team Members -->
          <div class="mb-0">
            <h6 class="text-dark mb-3 fw-semibold d-flex align-items-center">
              <span class="me-2">Team Members</span>
              <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 small">{{ count($winner->teamMembers) }}</span>
            </h6>
            
            @if(count($winner->teamMembers) <= 4)
              <!-- Full width layout for 4 or fewer members -->
              <div class="row g-2">
                @foreach ($winner->teamMembers as $member)
                <div class="col-12">
                  <div class="bg-white border rounded p-2">
                    <div class="fw-medium text-dark small mb-1">{{ $member->name }}</div>
                    <small class="text-muted">{{ $member->role }}</small>
                  </div>
                </div>
                @endforeach
              </div>
            @else
              <!-- Compact list layout for more than 4 members -->
              <div class="bg-light rounded p-3">
                <div class="row g-2">
                  @foreach ($winner->teamMembers as $index => $member)
                  <div class="col-12">
                    <div class="d-flex align-items-center py-1">
                      <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 24px; height: 24px; min-width: 24px;">
                        <small class="text-white fw-bold">{{ $index + 1 }}</small>
                      </div>
                      <div class="flex-grow-1">
                        <div class="fw-medium text-dark small">{{ $member->name }}</div>
                        <small class="text-muted">{{ $member->role }}</small>
                      </div>
                    </div>
                    @if(!$loop->last)
                    <hr class="my-2 opacity-25">
                    @endif
                  </div>
                  @endforeach
                </div>
                
                @if(count($winner->teamMembers) > 6)
                <div class="text-center mt-2">
                  <small class="text-muted">{{ count($winner->teamMembers) }} team members total</small>
                </div>
                @endif
              </div>
            @endif
          </div>
        </div>
        
        <!-- Footer -->
        <div class="card-footer bg-white border-0 pt-0 pb-3 px-4 text-center">
          <div class="d-flex align-items-center justify-content-center">
            <i class="bi bi-trophy text-warning me-2"></i>
            <small class="text-muted fw-medium">Awarded on {{ $winner->award_date }}</small>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
</div>