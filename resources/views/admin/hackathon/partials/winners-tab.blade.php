@php
$winners = [
  (object)[
    'name' => 'Innovative Minds',
    'description' => 'A team of forward-thinkers dedicated to creating impactful solutions.',
    'logo' => '/images/background/logo.png',
    'project' => 'Smart Medical Diagnosis System',
    'award_date' => 'August 4, 2025',
    'teamName' => 'Team Alpha',
    'teamLeader' => (object)[
      'name' => 'Alice Johnson',
      'role' => 'Project Manager',
      'image' => '/vuexy/assets/img/avatars/1.png'
    ],
    'teamMembers' => [
      (object)[
        'name' => 'Alice Johnson',
        'role' => 'Project Manager',
        'image' => '/vuexy/assets/img/avatars/1.png',
        'isLeader' => true
      ],
      (object)[
        'name' => 'Bob Smith',
        'role' => 'Lead Developer',
        'image' => null,
        'isLeader' => false
      ],
      (object)[
        'name' => 'Charlie Brown',
        'role' => 'UX Designer',
        'image' => null,
        'isLeader' => false
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
      'teamLeader' => (object)[
        'name' => 'David Wilson',
        'role' => 'Data Scientist',
        'image' => '/images/team/david.jpg'
      ],
      'teamMembers' => [
        (object)[
        'name' => 'David Wilson',
        'role' => 'Data Scientist',
        'image' => '/vuexy/assets/img/avatars/1.png',
        'isLeader' => true
        ],
        (object)[
        'name' => 'Eva Green',
        'role' => 'AI Engineer',
        'image' => '/vuexy/assets/img/avatars/2.png',
        'isLeader' => false
        ],
        (object)[
        'name' => 'Frank Wright',
        'role' => 'Business Analyst',
        'image' => '/vuexy/assets/img/avatars/3.png',
        'isLeader' => false
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
      'teamLeader' => (object)[
        'name' => 'Grace Lee',
        'role' => 'Environmental Scientist',
        'image' => '/images/team/grace.jpg'
      ],
      'teamMembers' => [
        (object)[
        'name' => 'Grace Lee',
        'role' => 'Environmental Scientist',
        'image' => '/vuexy/assets/img/avatars/4.png',
        'isLeader' => true
        ],
        (object)[
        'name' => 'Henry Kim',
        'role' => 'Renewable Energy Engineer',
        'image' => '/vuexy/assets/img/avatars/5.png',
        'isLeader' => false
        ],
        (object)[
        'name' => 'Isabella Martinez',
        'role' => 'Sustainability Consultant',
        'image' => '/vuexy/assets/img/avatars/6.png',
        'isLeader' => false
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
          <img src="{{ $winner->logo ?? '/images/logo-placeholder.png' }}" class="rounded-circle me-3 border shadow-xs" alt="Startup Logo" width="45" height="45">
          <div>
            <h6 class="mb-0 fw-bold text-dark">{{ $winner->name }}</h6>
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
                  <div class="bg-white border rounded p-2 {{ $member->isLeader ? 'border-primary bg-opacity-10' : '' }}">
                    <div class="d-flex align-items-center">
                      <img src="{{ $member->image ?? '/icons/account.svg' }}" class="rounded-circle me-3 border" alt="{{ $member->name }}" width="40" height="40">
                      <div class="flex-grow-1">
                        <div class="fw-medium text-dark small mb-1 d-flex align-items-center">
                          {{ $member->name }}
                        </div>
                        <small class="text-muted">{{ $member->role }}</small>
                      </div>
                      @if($member->isLeader)
                        <span class="badge bg-label-primary text-primary fw-semibold px-2 py-1 small">
                          <i class="bi bi-award me-1"></i> {{ $l == 'ar' ? 'قائد' : 'Leader' }}
                        </span>
                      @endif
                    </div>
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
                    <div class="d-flex align-items-center py-1 {{ $member->isLeader ? 'bg-warning bg-opacity-10 rounded px-2' : '' }}">
                      <img src="{{ $member->image ?? '/icons/account.svg' }}" class="rounded-circle me-3 border" alt="{{ $member->name }}" width="32" height="32">
                      <div class="flex-grow-1">
                        <div class="fw-medium text-dark small d-flex align-items-center">
                          {{ $member->name }}
                          @if($member->isLeader)
                            <i class="bi bi-star-fill text-warning ms-2" title="Team Leader"></i>
                          @endif
                        </div>
                        <small class="text-muted">{{ $member->role }}</small>
                      </div>
                      @if($member->isLeader)
                        <span class="badge bg-warning text-dark fw-semibold px-2 py-1 small">
                          <i class="bi bi-award me-1"></i>Leader
                        </span>
                      @endif
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
      </div>
    </div>
    @endforeach
  </div>
</div>