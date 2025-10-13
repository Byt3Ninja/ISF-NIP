{{-- Finished Hackathon Overview Tab --}}
<div class="card">
   <div class="card-header">
    <h5 class="mb-0 d-flex align-items-center">
      <i class="ti ti-file-text me-2"></i>
      {{ $l == 'en' ? 'Overview' : 'نظرة عامة' }}
    </h5>
  </div>

  <div class="card-body">
    <div class="row">
      <!-- Hackathon Summary -->
      <div class="col-lg-8">
        <div class="hackathon-overview">
          <div class="d-flex flex-column flex-md-row align-items-md-center mb-4">
            <div class="avatar avatar-xl me-3">
              @if($hackathon['img'])
                <img src="{{ $hackathon['img'] }}" alt="hac-img" class="rounded border shadow-xs">
              @else
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti ti-trophy text-primary fs-2"></i>
                </span>
              @endif
            </div>
            <div>
              <h4 class="mb-1 line-clamp-1">{{ $hackathon['title'] }}</h4>
              <p class="d-flex align-items-center text-muted mb-2">
                <i class="ti ti-calendar me-2"></i>
                {{ $hackathon['start'] }} {{ __('admin.To') }} {{ $hackathon['end'] }}
              </p>
              <span class="badge bg-label-success">
                <i class="ti ti-check me-1"></i>
                {{ $l == 'en' ? 'Completed' : 'مكتمل' }}
              </span>
            </div>
          </div>

          <div class="overview-description mb-2">
            <h5 class="mb-1">{{ $l == 'en' ? 'About This Hackathon' : 'حول هذا الهاكاثون' }}</h5>
            <p class="text-muted">{{ $hackathon['description'] }}</p>
          </div>

          <!-- Statistics Cards -->
          <div class="row g-3 mb-6">
            <div class="col-md-4">
              <div class="card card-border-shadow-primary">
                <div class="card-body d-flex flex-column align-items-center text-center">
                  <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                      <span class="avatar-initial rounded bg-label-primary">
                        <i class="ti ti-users-group fs-3"></i>
                      </span>
                    </div>
                    <h3 class="mb-0">{{ $hackathon['teams']->count() }}</h3>
                  </div>
                  <h6 class="mb-0">{{ $l == 'en' ? 'Total Teams' : 'إجمالي الفرق' }}</h6>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-border-shadow-success">
                <div class="card-body d-flex flex-column align-items-center text-center">
                  <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                      <span class="avatar-initial rounded bg-label-success">
                        <i class="ti ti-users fs-3"></i>
                      </span>
                    </div>
                    <h3 class="mb-0">{{ $hackathon['teams']->sum(function($team) { return $team->members->count(); }) }}</h3>
                  </div>
                  <h6 class="mb-0">{{ $l == 'en' ? 'Total Participants' : 'إجمالي المشاركين' }}</h6>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-border-shadow-info">
                <div class="card-body d-flex flex-column align-items-center text-center">
                  <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                      <span class="avatar-initial rounded bg-label-info">
                        <i class="ti ti-trophy fs-3"></i>
                      </span>
                    </div>
                    @php
                      $winnersCount = $hackathon['teams']->filter(function($team) {
                        return $team->winner && $team->winner > 0;
                      })->count();
                    @endphp
                    <h3 class="mb-0">{{ $winnersCount }}</h3>
                  </div>
                  <h6 class="mb-0">{{ $l == 'en' ? 'Winners Announced' : 'الفائزون المعلنون' }}</h6>
                </div>
              </div>
            </div>
          </div>

          <!-- Highlights Section -->
          <div class="highlights-section">
            <h5 class="mb-3">{{ $l == 'en' ? 'Hackathon Highlights' : 'أبرز ما في الهاكاثون' }}</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="highlight-item d-flex align-items-center p-3 border rounded">
                  <i class="ti ti-bolt text-warning fs-4 me-3"></i>
                  <div>
                    <h6 class="mb-1">{{ $l == 'en' ? 'Innovation Focus' : 'التركيز على الابتكار' }}</h6>
                    <small class="text-muted">{{ $l == 'en' ? 'AI & Technology Solutions' : 'حلول الذكاء الاصطناعي والتقنية' }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="highlight-item d-flex align-items-center p-3 border rounded">
                  <i class="ti ti-clock text-info fs-4 me-3"></i>
                  <div>
                    <h6 class="mb-1">{{ $l == 'en' ? 'Duration' : 'المدة' }}</h6>
                    <small class="text-muted">
                      @php
                        $start = \Carbon\Carbon::parse($hackathon['start']);
                        $end = \Carbon\Carbon::parse($hackathon['end']);
                        $duration = $end->diffInDays($start);
                      @endphp
                      {{ $duration }} {{ $l == 'en' ? 'days of intensive development' : 'أيام من التطوير المكثف' }}
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Winners Summary Sidebar -->
      <div class="col-lg-4">
        <div class="winners-sidebar">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient-primary">
              <h5 class="mb-0 text-white d-flex align-items-center">
                <i class="ti ti-trophy me-2"></i>
                {{ $l == 'en' ? 'Final Results' : 'النتائج النهائية' }}
              </h5>
            </div>
            <div class="card-body p-0">
              @php
                $winningTeams = $hackathon['teams']->filter(function($team) {
                  return $team->winner && $team->winner > 0;
                })->sortBy('winner');
              @endphp
              
              @if($winningTeams->isNotEmpty())
                @foreach($winningTeams as $winner)
                  @php
                    $color = '';
                    $icon = '';
                    $positionText = '';
                    
                    if($winner->winner == 1) {
                      $color = 'warning';
                      $icon = 'trophy';
                      $positionText = $l == 'en' ? '1st Place' : 'المركز الأول';
                    } elseif($winner->winner == 2) {
                      $color = 'secondary';
                      $icon = 'award';
                      $positionText = $l == 'en' ? '2nd Place' : 'المركز الثاني';
                    } elseif($winner->winner == 3) {
                      $color = 'info';
                      $icon = 'award';
                      $positionText = $l == 'en' ? '3rd Place' : 'المركز الثالث';
                    }
                  @endphp
                  <div class="winner-item d-flex align-items-center p-3 border-bottom">
                    <div class="winner-position me-3">
                      <i class="ti ti-{{ $icon }} text-{{ $color }} fs-3"></i>
                    </div>
                    <div class="winner-info flex-grow-1">
                      <h6 class="mb-1">{{ $winner->name }}</h6>
                      <small class="text-muted">{{ $winner->project->name ?? ($l == 'en' ? 'Project Name' : 'اسم المشروع') }}</small>
                      <div class="mt-1">
                        <span class="badge bg-label-{{ $color }}">{{ $positionText }}</span>
                      </div>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="text-center p-4">
                  <i class="ti ti-hourglass text-muted fs-1 mb-2"></i>
                  <p class="text-muted mb-0">{{ $l == 'en' ? 'Winners will be announced soon' : 'سيتم الإعلان عن الفائزين قريباً' }}</p>
                </div>
              @endif
            </div>
          </div>

          <button class="btn btn-outline-success mt-4 w-100" onclick="$('#winners-tab').click()">
            <i class="ti ti-trophy me-2"></i>
            {{ $l == 'en' ? 'See Full Results' : 'عرض النتائج الكاملة' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>