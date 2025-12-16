@php
$achievements = [
  'alumni' => [
    'locked'  => false,
    'title'   => 'Y Combinator',
    'date' => '20-11-2022', // Alumni date
  ],
  'hackathon' => [
    'locked'  => true,
    'title'   => 'Global Hack Week',
    'date' => '20-11-2022', // Winning date
  ],
  'profile_complete' => [
    'locked'  => false,
    'subtitle'=> '100% Completed',
  ],
  'team_builder' => [
    'locked'  => true,
    'count' => 3, // Count of team members
  ],
  'growth_achiever' => [
    'locked'  => true,
    'count' => 4, // Count of milestones
  ],
  'mentor_connect' => [
    'locked'  => false,
    'count' => 5, // Count of mentorship sessions
  ],
];

$achievemenLabels = [
  'team_builder' => __('startup.badges.added_members_count'),
  'growth_achiever' => __('startup.badges.completed_milestones_count'),
  'mentor_connect' => __('startup.badges.make_mentorships_count'),
];

$unlockHints = [
  __('startup.badges.not_graduated_yet'),
  __('startup.badges.participate_in_hackathon_to_unlock'),
  __('startup.badges.complete_profile_to_unlock'),
  __('startup.badges.create_join_team'),
  __('startup.badges.reach_growth_milestones'),
  __('startup.badges.no_mentorhip_sessions'),
];

$badgeLabels = ['alumni', 'winner', 'completed', 'active', 'achiever', 'connected'];
$badgeIcons = ['fa-medal', 'fa-trophy', 'fa-user-check', 'fa-users', 'fa-arrow-up-right-dots', 'fa-handshake'];
$badgeColors = ['primary', 'secondary', 'info', 'warning', 'danger', 'success'];
@endphp

<div class="card mb-6">
  <div class="card-header">
    <h5 class="card-title mb-1">{{ __('startup.badges.badges_recognition') }}</h5>
    <small class="text-muted">{{ __('startup.badges.achievements_and_progress') }}</small>
  </div>
  <div class="card-body">
    <!-- Achievement Badges -->
    <div class="row gy-6">
      @foreach ($achievements as $key => $item)
        <div class="col-md-4">
          <div class="card h-100 border-2 border-{{ $item['locked'] ? 'light' : $badgeColors[$loop->index] }} opacity-{{ $item['locked'] ? '50' : '100' }} shadow-sm">
            <div class="card-body text-center position-relative">
              @if ($item['locked'])
                <div
                  class="position-absolute top-0 mt-3 {{ $l === 'en' ? 'end-0 me-3' : 'start-0 ms-3' }}">
                  <i class="ti ti-lock"></i>
                </div>
              @endif
              
              <div class="d-flex flex-column justify-content-center align-items-center end">
                <div class="avatar avatar-lg mb-3">
                  <div class="avatar-initial rounded-circle bg-{{ $item['locked'] ? 'light' : $badgeColors[$loop->index] }} text-{{ $badgeColors[$loop->index] }}">
                    <i class="fa-solid {{ $badgeIcons[$loop->index] }} text-white"></i>
                  </div>
                </div>
    
                <div class="badge bg-label-{{ $item['locked'] ? 'secondary' : $badgeColors[$loop->index] }} {{ $item['locked'] ? 'text-dark' : '' }} rounded-pill text-uppercase mb-2">
                  {{ $item['locked'] ? __('startup.badges.locked') : $badgeLabels[$loop->index] }}
                </div>
    
                <h6 class="mb-1 text-dark fw-semibold">
                  @if (isset($item['title']) && !$item['locked'])
                    {{ $item['title'] }}
                  @else
                    {{ __('startup.badges.' . $key) }}
                  @endif
                </h6>
                
                @if ($item['locked'])
                  <small class="text-muted">{{ $unlockHints[$loop->index] }}</small>
                @else
                  <small class="text-muted">
                    @if (isset($item['date']))
                      {{ \Carbon\Carbon::parse($item['date'])->format('F Y') }}
                    @elseif (isset($item['count']))
                      {{ __($achievemenLabels[$key], ['count' => $item['count']]) }}
                    @elseif ($key === 'profile_complete')
                      {{ __('startup.badges.100_profile_complete') }}
                    @endif
                  </small>
                @endif
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <!--/ Achievement Badges -->
  </div>
</div>