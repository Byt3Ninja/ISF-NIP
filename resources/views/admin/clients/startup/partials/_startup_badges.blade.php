@php
$achievemenLabels = [
  'team_builder' => __('startup.badges.added_members_count'),
  'growth_achiever' => __('startup.badges.completed_milestones_count'),
  'mentor_connect' => __('startup.badges.make_mentorships_count'),
];

$unlockHints = [
  __('startup.badges.not_graduated_yet'),
  __('startup.badges.participate_in_hackathon_to_unlock'),  // Index 1: winner/hackathon badge
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
        {{-- Skip the hackathon/winner badge (index 1) --}}
        @if ($loop->index === 1 || $loop->index === 3)
          @continue
        @endif
        <div class="col-md-4">
          @php
            $badgeData = [
              'key' => $key,
              'locked' => $item['locked'],
              'name' => $item['locked'] ? __('startup.badges.locked') : __('startup.badges.' . $badgeLabels[$loop->index]),
              'description' => $key === 'alumni' ? __('startup.badges.alumni_description', ['program' => isset($item['title']) ? $item['title'] : __('startup.badges.incubator_program')]) : __('startup.badges.' . $key . '_description'),
              'icon' => $badgeIcons[$loop->index],
              'color' => $badgeColors[$loop->index],
              'unlockHint' => $item['locked'] ? $unlockHints[$loop->index] : '',
              'dateEarned' => !$item['locked'] && isset($item['date']) ? \Carbon\Carbon::parse($item['date'])->format('F Y') : '',
              'criteriaMet' => !$item['locked'] ? ($key === 'alumni' ? __('startup.badges.graduated_from_program', ['program' => isset($item['title']) ? $item['title'] : __('startup.badges.incubator_program')]) : (isset($item['count']) ? __($achievemenLabels[$key], ['count' => $item['count']]) : ($key === 'profile_complete' ? __('startup.badges.100_profile_complete') : ''))) : '',
            ];
          @endphp
          
          <div class="card h-100 border-2 border-{{ $item['locked'] ? 'light' : $badgeColors[$loop->index] }} opacity-{{ $item['locked'] ? '50' : '100' }} shadow-sm badge-card"
            {{-- Tooltip Logic: Only show tooltip if locked --}}
            @if($item['locked'])
              data-bs-toggle="tooltip"
              data-bs-placement="top"
              data-bs-title="{{ $unlockHints[$loop->index] }}"
            @endif
            data-badge="{{ json_encode($badgeData) }}"
            style="cursor: pointer;"
            >
            
            <div class="card-body text-center position-relative">
              @if ($item['locked'])
                <div class="position-absolute top-0 mt-3 {{ app()->getLocale() === 'en' ? 'end-0 me-3' : 'start-0 ms-3' }}">
                  <i class="ti ti-lock"></i>
                </div>
              @endif
              
              <div class="d-flex flex-column justify-content-center align-items-center">
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
                  <small class="text-muted text-truncate w-100 d-block">{{ $unlockHints[$loop->index] }}</small>
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
  </div>
</div>

<!-- Badge Details Modal -->
<div class="modal fade" id="badgeModal" tabindex="-1" aria-labelledby="badgeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="badgeModalLabel">{{ __('startup.badges.badge_details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('startup.badges.close') }}"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-column align-items-center text-center mb-4">
          <div class="avatar avatar-xl mb-3">
            <div class="avatar-initial rounded-circle bg-primary text-white" id="modalBadgeIcon">
              <i class="fa-solid fa-medal"></i>
            </div>
          </div>
          <h4 id="modalBadgeName"></h4>
          <p class="text-muted" id="modalBadgeDescription"></p>
        </div>

        <div id="earnedBadgeContent" style="display: none;">
          <hr>
          <div class="row">
            <div class="col-6">
              <strong>{{ __('startup.badges.date_earned') }}:</strong>
              <p id="modalDateEarned"></p>
            </div>
            <div class="col-6">
              <strong>{{ __('startup.badges.criteria_met') }}:</strong>
              <p id="modalCriteriaMet"></p>
            </div>
          </div>
        </div>

        <div id="lockedBadgeContent" style="display: none;">
          <hr>
          <div class="alert alert-warning">
            <strong>{{ __('startup.badges.requirements_to_unlock') }}:</strong>
            <p id="modalRequirements"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('startup.badges.close') }}</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(function () {
  // Handle badge card clicks
  $('.badge-card').on('click', function () {
    // Get data from clicked card
    const $card = $(this);
    const badgeData = $card.data('badge');

    updateModalContent(badgeData || null);
  });

  function updateModalContent(data) {
    if (data) {
      const isLocked = data.locked || false;

      // Update modal icon & color
      $('#modalBadgeIcon')
        .attr(
          'class',
          'avatar-initial rounded-circle bg-' + (isLocked ? 'light' : data.color) +
          ' text-' + data.color
        )
        .html('<i class="fa-solid ' + data.icon + ' text-white"></i>');
  
      // Update badge name & description
      $('#modalBadgeName').text(data.name);
      $('#modalBadgeDescription').text(data.description);
  
      if (isLocked) {
        $('#earnedBadgeContent').hide();
        $('#lockedBadgeContent').show();
        $('#modalRequirements').text(data.unlockHint);
      } else {
        $('#lockedBadgeContent').hide();
        $('#earnedBadgeContent').show();
        $('#modalDateEarned').text(data.dateEarned || '{{ __("startup.badges.not_available") }}');
        $('#modalCriteriaMet').text(data.criteriaMet || '{{ __("startup.badges.not_available") }}');
      }
  
     $('#badgeModal').modal('show');
    }
  }
});
</script>
@endpush