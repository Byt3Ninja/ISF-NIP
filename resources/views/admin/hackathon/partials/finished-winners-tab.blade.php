{{-- Finished Hackathon Winners Tab --}}
<div class="card">
  <div class="card-header">
    <h5 class="mb-0 d-flex align-items-center">
      <i class="ti ti-trophy me-2"></i>
      {{ $l == 'en' ? 'Winners & Results' : 'الفائزون والنتائج' }}
    </h5>
  </div>
  
  <div class="card-body">
    @php 
      // Get teams with winner_status for display 
      $winningTeams = $teams->filter(function($team) {
        return $team->winner && $team->winner > 0;
      })->sortBy('winner');
    @endphp
    
    @if($winningTeams->isNotEmpty())
      {{-- Winners Announcement Banner --}}
      <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
        <i class="ti ti-speakerphone fs-4 me-3"></i>
        <div>
          <h6 class="alert-heading mb-1">{{ $l == 'en' ? 'Winners Announced!' : 'تم الإعلان عن الفائزين!' }}</h6>
          <p class="mb-0">{{ $l == 'en' ? 'Congratulations to all the winning teams of this amazing hackathon.' : 'تهانينا لجميع الفرق الفائزة في هذا الهاكاثون الرائع.' }}</p>
        </div>
      </div>

      {{-- Winners Podium --}}
      <div class="winners-podium mb-5">
        <div class="row justify-content-center align-items-end g-3">
          {{-- 2nd Place (Left) --}}
          @php $secondPlace = $winningTeams->where('winner', 2)->first(); @endphp
          @if($secondPlace)
            <div class="col-md-4">
              <div class="podium-position text-center">
                <div class="position-card bg-gradient-secondary text-white p-4 rounded mb-3" style="min-height: 200px;">
                  <div class="position-number mb-3">
                    <i class="ti ti-award display-4"></i>
                  </div>
                  <h4 class="mb-2">{{ $secondPlace->name }}</h4>
                  <p class="mb-2">{{ $secondPlace->project->name ?? ($l == 'en' ? 'Project Name' : 'اسم المشروع') }}</p>
                </div>
                <div class="podium-base bg-secondary text-white text-center py-2 rounded">
                  <strong>{{ $l == 'en' ? '2nd Place' : 'المركز الثاني' }}</strong>
                </div>
              </div>
            </div>
          @endif

          {{-- 1st Place (Center) --}}
          @php $firstPlace = $winningTeams->where('winner', 1)->first(); @endphp
          @if($firstPlace)
            <div class="col-md-4">
              <div class="podium-position text-center">
                <div class="position-card bg-gradient-warning text-white p-4 rounded mb-3" style="min-height: 240px;">
                  <div class="crown mb-3">
                    <i class="ti ti-trophy display-3"></i>
                  </div>
                  <h3 class="mb-2">{{ $firstPlace->name }}</h3>
                  <p class="mb-2">{{ $firstPlace->project->name ?? ($l == 'en' ? 'Project Name' : 'اسم المشروع') }}</p>
                </div>
                <div class="podium-base bg-warning text-dark text-center py-3 rounded">
                  <strong>{{ $l == 'en' ? '1st Place' : 'المركز الأول' }}</strong>
                </div>
              </div>
            </div>
          @endif

          {{-- 3rd Place (Right) --}}
          @php $thirdPlace = $winningTeams->where('winner', 3)->first(); @endphp
          @if($thirdPlace)
            <div class="col-md-4">
              <div class="podium-position text-center">
                <div class="position-card bg-gradient-info text-white p-4 rounded mb-3" style="min-height: 180px;">
                  <div class="position-number mb-3">
                    <i class="ti ti-award display-5"></i>
                  </div>
                  <h5 class="mb-2">{{ $thirdPlace->name }}</h5>
                  <p class="mb-2">{{ $thirdPlace->project->name ?? ($l == 'en' ? 'Project Name' : 'اسم المشروع') }}</p>
                </div>
                <div class="podium-base bg-info text-white text-center py-2 rounded">
                  <strong>{{ $l == 'en' ? '3rd Place' : 'المركز الثالث' }}</strong>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- Detailed Winners List --}}
      <div class="detailed-results">
        <h5 class="mb-4">{{ $l == 'en' ? 'Detailed Results' : 'النتائج التفصيلية' }}</h5>
        
        <div class="row g-4">
          @foreach($winningTeams->sortBy('winner') as $winner)
            @php
              $color = '';
              $icon = '';
              $positionText = '';
              
              if($winner->winner == 1) {
                $color = 'warning';
                $icon = 'trophy';
                $positionText = $l == 'en' ? '1st Place Winner' : 'الفائز بالمركز الأول';
              } elseif($winner->winner == 2) {
                $color = 'secondary';
                $icon = 'award';
                $positionText = $l == 'en' ? '2nd Place Winner' : 'الفائز بالمركز الثاني';
              } elseif($winner->winner == 3) {
                $color = 'info';
                $icon = 'award';
                $positionText = $l == 'en' ? '3rd Place Winner' : 'الفائز بالمركز الثالث';
              }
            @endphp
            
            <div class="col-lg-6">
              <div class="card winner-detail-card border-start border-2 border-{{ $color }}">
                
                <div class="card-body">
                  <div class="d-flex align-items-start">
                    {{-- Position Badge --}}
                    <div class="position-badge me-3">
                      <span class="badge bg-label-{{ $color }} fs-4 p-2">
                        <i class="ti ti-{{ $icon }}"></i>
                      </span>
                    </div>

                    {{-- Winner Details --}}
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0">{{ $winner->name }}</h5>
                        <span class="badge bg-label-{{ $color }}">{{ $positionText }}</span>
                      </div>
                        {{-- Team Members --}}
                        <div class="team-members mb-2">
                          <small class="text-muted d-block mb-1">
                            {{ $l == 'en' ? 'Team Members:' : 'أعضاء الفريق:' }}
                          </small>
                          <div class="d-flex flex-wrap gap-1">
                            @foreach($winner->members as $member)
                              <span class="badge bg-light text-dark">{{ $member->name }}</span>
                            @endforeach
                          </div>
                        </div>
                        
                        {{-- Project Name --}}
                        @if($winner->project)
                          <div class="project-info">
                            <small class="text-muted d-block">{{ $l == 'en' ? 'Project:' : 'المشروع:' }}</small>
                            <strong class="text-primary">{{ $winner->project->name ?? 'Project Name' }}</strong>
                          </div>
                        @endif

                        {{-- Winner Position Info --}}
                        <div class="position-info mt-2">
                          <div class="d-flex align-items-center">
                            <i class="ti ti-{{ $icon }} text-{{ $color }} me-2"></i>
                            <span class="text-{{ $color }} fw-semibold">{{ $positionText }}</span>
                          </div>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

    @else
      {{-- No Winners Announced Yet --}}
      <div class="text-center py-5">
        <i class="ti ti-hourglass text-muted" style="font-size: 4rem;"></i>
        <h4 class="text-muted mt-3 mb-2">{{ $l == 'en' ? 'Results Pending' : 'النتائج معلقة' }}</h4>
        <p class="text-muted">{{ $l == 'en' ? 'Winners and results will be announced soon.' : 'سيتم الإعلان عن الفائزين والنتائج قريباً.' }}</p>
      </div>
    @endif
  </div>
</div>