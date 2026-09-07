<div class="card shadow-lg border-0 mb-4">
  <div class="card-header">
   
    <div class="d-flex align-items-center gap-3">
      {{-- <div class="bg-white bg-opacity-20 rounded-circle p-2">
        <i class="fas fa-users fs-4"></i>
      </div> --}}
      <div>
        <h5 class="card-title fw-bold mb-1">
          @if(count($teamsData) == 0)
          {{ $l == 'en' ? 'Team Formation and Joining' : 'تكوين الفرق والانضمام إليها' }}
          @else
          {{ $l == 'en' ? 'Teams' : 'الفرق' }}
          @endif
        </h5>
        <p class="mb-0 small">
          @if(count($teamsData) == 0)
          {{ $l == 'en' ? 'You can now create a new team or join an existing team. Maximum team members is '.
          $count_team . '.' : 'يمكنك الآن تكوين فريق جديد أو الانضمام إلى فريق قائم. الحد الأقصى لأعضاء الفريق هو'.
          $count_team .'.' }}
          @else
          {{ $l == 'en' ? 'You can leave a team or join another
          one.' : 'يمكنك مغادرة فريق أو الانضمام إلى آخر' }}
          @endif
        </p>
      </div>
    </div>
  </div>

  <div class="card-body p-4">
    <div class="row g-4">
      @php
        $authUser = auth()->user();
        $authUserInAnyTeam = false;
        $member_ids = [];
        foreach ($teamsData as $team) {
        $member_ids = collect($team->members)->pluck('id')->toArray();
        if (in_array($authUser->id, $member_ids)) {
        $authUserInAnyTeam = true;
        break;
        }
        }

        $isExistingTeam = in_array($authUser->id, $member_ids);
      @endphp

      @foreach($teamsData as $team)
        {{-- {{ dd($team) }} --}}
      <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 h-100 team-card position-relative overflow-hidden" 
             style="transition: all 0.3s ease; cursor: pointer;" 
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,0.1)'">
          
          <!-- Team Photo with Overlay -->
          <div class="position-relative overflow-hidden bg-light" style="height: 200px;">
            @php
            $teamImageSrc = 'https://placehold.co/400x200?text='.$team->name;
            if ($team->photo) {
                // Already a full URL from the controller (authorized logo route).
                $teamImageSrc = $team->photo;
            }
            @endphp
            <img 
              src="{{ $teamImageSrc }}"
              class="w-100 h-100 object-fit-contain p-2" 
              alt="Team Photo" 
              style="transition: transform 0.3s ease;"
              onerror="this.onerror=null; this.src='https://placehold.co/400x200?text={{ urlencode($team->name) }}';"
            />
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-flex align-items-center justify-content-center opacity-0" 
                 style="transition: opacity 0.3s ease;" 
                 onmouseover="this.style.opacity='1'" 
                 onmouseout="this.style.opacity='0'">
              <i class="fas fa-eye text-white fs-2"></i>
            </div>
            @if(!empty($team->your_team))
            <div class="position-absolute top-0 end-0 m-3">
              <span class="badge bg-success text-white px-3 py-2 rounded-pill shadow-sm">
                <i class="fas fa-crown me-1"></i>
                {{ $l == 'en' ? 'Your Team' : 'فريقك' }}
              </span>
            </div>
            @endif
          </div>
          
          <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <h5 class="card-title mb-0 fw-bold text-dark">{{ $team->name }}</h5>
            </div>
            
            <p class="card-text text-muted mb-3 small lh-base">{{ $team->desc }}</p>
            
            <!-- Progress Bar for Team Capacity -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">
                  <i class="fas fa-users me-1"></i>
                  {{ $l == 'en' ? 'Team Members' : 'أعضاء الفريق' }}
                </span>
                <span class="fw-semibold small">
                  {{ count($team->members) }} / {{ $team->max_members }}
                </span>
              </div>
              <div class="progress bg-light" style="height: 6px;">
                <div class="progress-bar bg-gradient {{ count($team->members) == $team->max_members ? 'bg-danger' : 'bg-primary' }}" 
                     style="width: {{ $team->max_members > 0 ? (count($team->members) / $team->max_members) * 100 : 0 }}%; transition: width 0.3s ease;"></div>
              </div>
            </div>
            
            <!-- Team Members Avatars -->
            <div class="d-flex align-items-center gap-2 mb-4">
              @php
              $maxAvatars = 4;
              $memberCount = count($team->members);
              $displayMembers = array_slice($team->members, 0, $maxAvatars);
              @endphp
              @foreach($displayMembers as $member)
              @if(!empty($member->img_url))
              <div class="position-relative" title="{{ $member->name }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $member->name }}">
                <img src="/191014/{{$member->img_url}}"
                  class="rounded-circle border-2 border-white shadow-sm" 
                  style="width: 40px; height: 40px; object-fit: cover;" 
                  alt="img"
                  title="{{ $member->name }}"
                  onerror="this.onerror=null; this.src='/icons/account.svg';">
              </div>
              @else
                <div class="rounded-circle bg-gradient bg-primary text-white d-flex align-items-center justify-content-center border-2 border-white shadow-sm fw-semibold" 
                   style="width: 40px; height: 40px;" 
                   title="{{ $member->name }}"
                   data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ $member->name }}">
                {{ strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $member->name), 0, 2)) }}
                </div>
              @endif
              @endforeach
              @if($memberCount > $maxAvatars)
              <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center border-2 border-white shadow-sm fw-semibold" 
                   style="width: 40px; height: 40px;">
                +{{ $memberCount - $maxAvatars }}
              </div>
              @endif
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-sm-row gap-2">
              <a href="/dashboard/hackathon/team/{{ $team->id }}" 
                 class="btn btn-outline-primary btn-sm flex-fill d-flex align-items-center justify-content-center gap-2 fw-semibold"
                 style="transition: all 0.3s ease; border-radius: 8px;">
                <i class="fas fa-eye"></i>
                {{ $l == 'en' ? 'View Team' : 'عرض الفريق' }}
              </a>
              
              @if(!auth()->user()->hasAnyRole('Admin|organizationManager'))
              @php
              // Simulate dynamic action logic as if from DB/user state
              $isFull = count($team->members) >= $team->max_members;
              $isYourTeam = !empty($team->your_team);
              $requestSent = !empty($team->request_sent);
              $alreadyJoined = !empty($team->already_joined);
              @endphp
              
              @if($isYourTeam)
              <a href="/dashboard/hackathon/{{ $team->id }}/manage" 
                 class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center gap-2 fw-semibold"
                 style="transition: all 0.3s ease; border-radius: 8px;">
                <i class="fas fa-cog"></i>
                {{ $l == 'en' ? 'Manage My Team' : 'إدارة فريقي' }}
              </a>
              @elseif($team->request_sent == true)
              <button class="btn btn-outline-warning btn-sm flex-fill d-flex align-items-center justify-content-center gap-2 fw-semibold" 
                      disabled style="border-radius: 8px;">
                <i class="fas fa-clock"></i>
                {{ $l == 'en' ? 'Request Sent' : 'تم إرسال الطلب' }}
              </button>
              @elseif($team->already_joined == true)
              <a href="/dashboard/hackathon/{{ $team->id }}/manage" 
                 class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center gap-2 fw-semibold"
                 style="transition: all 0.3s ease; border-radius: 8px;">
                <i class="fas fa-users"></i>
                {{ $l == 'en' ? 'View My Team' : 'مشاهدة فريقي' }}
              </a>
              @elseif($team->request_sent == false)
              
              @if($isExistingTeam == false)
                @if($maxAvatars >= $memberCount)
                  <button onclick="joinTeam('{{ $team->id }}','{{ $team->name }}')" 
                          class="btn btn-primary btn-sm flex-fill d-flex align-items-center justify-content-center gap-2 fw-semibold"
                          style="transition: all 0.3s ease; border-radius: 8px;">
                    <i class="fas fa-plus"></i>
                    {{ $l == 'en' ? 'Request to Join' : 'طلب الانضمام' }}
                  </button>
                @else

                @endif
              @endif
              @elseif($isFull)
                <button class="btn btn-secondary btn-sm flex-fill d-flex align-items-center justify-content-center gap-2 fw-semibold" 
                        disabled style="border-radius: 8px;">
                  <i class="fas fa-ban"></i>
                  {{ $l == 'en' ? 'Full' : 'ممتلئ' }}
                </button>
              @elseif($isExistingTeam == false)
                @if($memberCount >= $maxAvatars)
                  <form action="/dashboard/hackathon/RequestJoin" method="POST" class="flex-fill">
                    @csrf
                    <input type="hidden" name="id" value="{{ $team->id }}">
                    <button type="submit" 
                            class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2 fw-semibold"
                            style="transition: all 0.3s ease; border-radius: 8px;">
                      <i class="fas fa-plus"></i>
                      {{ $l == 'en' ? 'Request to Join' : 'طلب الانضمام' }}
                    </button>
                  </form>
                @else

                @endif
              @endif
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach

    </div>
  </div>
</div>