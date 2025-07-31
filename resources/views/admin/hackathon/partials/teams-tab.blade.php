@php 
// dd($teamsData);
  // $teamsData = $teams ?? [];
  // [
  //   [
  //     'name' => 'Hoda Test',
  //     'photo' => 'https://placehold.co/400x180?text=Team+Hello+world',
  //     'desc' => 'We are testing our awesome idea together.',
  //     'members' => [
  //       ['img_url' => 'https://placehold.co/40', 'name' => 'Hoda M'],
  //       ['img_url' => null, 'name' => 'Mohamed S'],
  //       ['img_url' => null, 'name' => 'Mohamed S'],
  //       ['img_url' => null, 'name' => 'Mohamed S'],
  //       ['img_url' => null, 'name' => 'Mohamed S'],
  //       ['img_url' => null, 'name' => 'Mohamed S'],
  //     ],
  //     'max_members' => 10,
  //     'your_team' => false,
  //     'request_sent' => false,
  //   ],
  //   [
  //     'name' => 'Tech Builders',
  //     'photo' => 'https://placehold.co/400x180?text=Tech+Builders',
  //     'desc' => 'Building next-gen tools for the future.',
  //     'members' => [
  //       ['img_url' => null, 'name' => 'Ali K'],
  //       ['img_url' => 'https://placehold.co/40', 'name' => 'Rana J'],
  //       ['img_url' => null, 'name' => 'Sara T'],
  //     ],
  //     'max_members' => 4,
  //     'your_team' => false,
  //     'request_sent' => false,
  //   ],
  //   [
  //     'name' => 'Future Hackers',
  //     'photo' => 'https://placehold.co/400x180?text=Future+Hackers',
  //     'desc' => 'Exploring AI, robotics, and edge tech.',
  //     'members' => [
  //       ['img_url' => null, 'name' => 'Mona A'],
  //       ['img_url' => null, 'name' => 'Ziad A'],
  //       ['img_url' => 'https://placehold.co/40', 'name' => 'Omar F'],
  //       ['img_url' => 'https://placehold.co/40', 'name' => 'Lina W'],
  //     ],
  //     'max_members' => 4,
  //     'your_team' => false,
  //     'request_sent' => false,
  //   ],
  // ];
@endphp

<div class="card">
  <div class="card-header">
    <h5 class="card-title">
      @if(count($teamsData) == 0)
      {{ $l == 'en' ? 'Team Formation and Joining' : 'تكوين الفرق والانضمام إليها' }}
      @else
      {{ $l == 'en' ? 'Your Teams' : 'الفرق التي أنت فيها' }}
    </h5>
    @endif
    <p class="text-muted">
      @if(count($teamsData) == 0)
      {{ $l == 'en' ? 'You can now create a new team or join an existing team. Maximum team members is '.
      $count_team . '.' : 'يمكنك الآن تكوين فريق جديد أو الانضمام إلى فريق قائم. الحد الأقصى لأعضاء الفريق هو'.
      $count_team .'.' }}
      @else
      {{ $l == 'en' ? 'You are currently part of the following team(s). You can leave a team or join another
      one.' : 'أنت حالياً جزء من الفريق(الفرق) التالية. يمكنك مغادرة فريق أو الانضمام إلى آخر.' }}
      @endif
    </p>
  </div>

  <div class="card-body">
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
      <div class="col-md-4">
        <div class="card shadow-sm">
          <img src="{{ $team->photo ? asset($team->photo) : 'https://placehold.co/400x180?text='.$team->name }}" class="team-photo" alt="Team Photo">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="card-title mb-0">{{ $team->name }}</h5>
              @if(!empty($team->your_team))
                <span class="badge bg-label-success text-sm">
                  {{ $l == 'en' ? 'Your Team' : 'فريقك' }}
                </span>
              @endif
            </div>
            <p class="card-text small">{{ $team->desc }}</p>
            <p class="text-muted mb-2">
              {{ count($team->members) }} / {{ $team->max_members }} {{ $l == 'en' ? 'Members' : 'أعضاء' }}
            </p>
            <div class="d-flex align-items-center gap-2 mb-3">
              @php
              $maxAvatars = 4;
              $memberCount = count($team->members);
              $displayMembers = array_slice($team->members, 0, $maxAvatars);
              @endphp
              @foreach($displayMembers as $member)
              @if(!empty($member->img_url))
                {{-- <img src="{{ $member->img_url }}" class="w-px-40 h-px-40 rounded-circle" alt="{{ $member->name }}"> --}}
                <img src="{{ $member->img_url ?? '/images/user-placeholder.jpeg' }}" class="w-px-40 h-px-40 rounded-circle" alt="img" onerror="this.onerror=null; this.src='/images/user-placeholder.jpeg';" >
              @else
                <div class="w-px-40 h-px-40 rounded-circle bg-light d-flex align-items-center justify-content-center">
                {{ strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $member->name), 0, 2)) }}
                </div>
              @endif
              @endforeach
              @if($memberCount > $maxAvatars)
              <div class="w-px-40 h-px-40 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center">
                +{{ $memberCount }}
              </div>
              @endif
            </div>

            <div class="d-flex justify-content-between">
              <a href="/dashboard/hackathon/team/{{ $team->id }}" class="btn btn-outline-primary btn-sm">
                {{ $l == 'en' ? 'View Team' : 'عرض الفريق' }}
              </a>
              @if(!auth()->user()->hasRole('Admin'))
              @php
                // Simulate dynamic action logic as if from DB/user state
                $isFull = count($team->members) >= $team->max_members;
                $isYourTeam = !empty($team->your_team);
                $requestSent = !empty($team->request_sent);
                $alreadyJoined = !empty($team->already_joined);
                

              @endphp
              {{-- {{ var_dump($team->your_team) }} --}}
              @if($isYourTeam)
                <a href="/dashboard/hackathon/{{ $team->id }}/manage" class="btn btn-outline-success " >
                    {{ $l == 'en' ? 'Manage My Team' : 'إدارة فريقي' }}
                </a>
              @elseif($team->request_sent == true)
                 <button class="btn btn-outline-secondary btn-sm" disabled>
                  {{ $l == 'en' ? 'Request Sent' : 'تم إرسال الطلب' }}
                </button>
              @elseif($team->request_sent == false)
              @if($isExistingTeam == false)
                <button onclick="joinTeam('{{ $team->id }}','{{ $team->name }}')" class="btn btn-success btn-sm">{{ $l == 'en' ? 'Request to Join' : 'طلب الانضمام' }}</button>
              @endif
              @elseif($isFull)
                <button class="btn btn-secondary btn-sm" disabled>{{ $l == 'en' ? 'Full' : 'ممتلئ' }}</button>
              @elseif($isExistingTeam == false)
              <form action="/dashboard/hackathon/RequestJoin" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $team->id }}">
                <button type="submit" class="btn btn-success btn-sm">{{ $l == 'en' ? 'Request to Join' : 'طلب الانضمام' }}</button>
              </form>
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


{{-- <div class="card">
  <div class="card-body">
    @if(count($teams) == 0)
    <h5 class="card-title">
      {{ $l == 'en' ? 'Team Formation and Joining' : 'تكوين الفرق والانضمام إليها' }}
    </h5>
    <p>
      {{ $l == 'en' ? 'You can now create a new team or join an existing team. Maximum team members is '.
      $count_team . '.' : 'يمكنك الآن تكوين فريق جديد أو الانضمام إلى فريق قائم. الحد الأقصى لأعضاء الفريق هو'.
      $count_team .'.' }}
    </p>
    @else
    <h5 class="card-title">
      {{ $l == 'en' ? 'Your Teams' : 'الفرق التي أنت فيها' }}
    </h5>
    <p>
      {{ $l == 'en' ? 'You are currently part of the following team(s). You can leave a team or join another
      one.' : 'أنت حالياً جزء من الفريق(الفرق) التالية. يمكنك مغادرة فريق أو الانضمام إلى آخر.' }}
    </p>
    @endif
    <div id="my-current-team" class="mb-4 p-3 border rounded bg-light" style="display:none;">
      <h6>
        {{ $l == 'en' ? 'Your Current Team:' : 'فريقك الحالي:' }}
        <span id="current-team-name" class="fw-bold">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}</span>
      </h6>
      <ul id="current-team-members" class="list-unstyled">
        <li>
          <i class="bi bi-person-check-fill text-success me-2"></i>
          {{ $l == 'en' ? 'You (Team Leader)' : 'أنت (قائد الفريق)' }}
        </li>
        <li>
          <i class="bi bi-person-fill me-2"></i>
          {{ $l == 'en' ? 'Team Member 2' : 'عضو الفريق 2' }}
        </li>
      </ul>
      <a href="collaboration-space.html?hackathonId=1&teamId=123" class="btn btn-success btn-sm">
        <i class="bi bi-chat-dots-fill me-2"></i>
        {{ $l == 'en' ? 'Team Workspace' : 'مساحة عمل الفريق' }}
      </a>
      <button class="btn btn-danger btn-sm" onclick="leaveTeam()">
        <i class="bi bi-box-arrow-left me-2"></i>
        {{ $l == 'en' ? 'Leave Team' : 'مغادرة الفريق' }}
      </button>
    </div>

    <div id="team-actions">
      @if(count($teams) > 0)
      @php
      $isteamleader = $teams->where('tm_isTeamLeader', 1)->first();
      $teamsMembers = $teams->where('tm_isTeamLeader', 0);
      $teamUser = $teams->where('tm_user_id', Auth::user()->id);
      $teams_with_user = $teams->where('tm_team_formation_id', $teamUser->first()?->tm_team_formation_id);
      $isleader = $teams_with_user->where('tm_isTeamLeader', 1)->first();
      $countMembers = $teams_with_user->count();
      @endphp
      @if($teamUser->count() > 0)
      @if($teams_with_user != null)
      <h6 class="mt-4">{{ $l == 'en' ? 'Your Current Team:' : 'فريقك الحالي:' }} <span
          class="fw-bold">{{$teamUser->first()->tf_name}}</span></h6>

      <ul class="list-unstyled">

        <hr>
        @foreach($teams_with_user as $team)
        <div>
          <span>name: {{$team->tf_name}}</span><br>
          <span>id: {{$team->tm_team_formation_id}}</span><br>
          <span>is leader: {{$team->tm_isTeamLeader}}</span><br>
          @foreach($users as $user)
          <span>user id: {{$team->tm_user_id}}</span><br>
          <span>user name: {{$team->user_name_en}}</span><br>
          @endforeach
          <span>count member : {{ $countMembers }}</span><br>

        </div>
        <hr>

        <li>
          <div class="d-flex align-items-center gap-2">
            <a class="media-left" href="/dashboard/users/{{ $team->tm_user_id }}/edit">
              <img src="{{ $team->u_img_url }}" alt="avatar" class="rounded-circle me-3" height="40" width="40">
            </a>
            <a href="/dashboard/users/{{ $team->tm_user_id }}/edit">
              <div class="media-body">
                <h6 class="media-heading mb-0 text-nowrap">{{ $team->{'user_name_'.$l} }}</h6>
                <span class="font-small-2 text-nowrap">{{$team->tf_name }}</span>
              </div>
            </a>
            @if($team->tm_isTeamLeader == 1)
            {{ $l == 'en' ? '(Team Leader)' : '(قائد الفريق)' }}
            @endif
          </div>

        </li>
        @endforeach
      </ul>

      @endif

      <a href="collaboration-space.html?hackathonId=1&teamId=123" class="btn btn-success btn-sm">
        <i class="bi bi-chat-dots-fill me-2"></i>
        {{ $l == 'en' ? 'Team Workspace' : 'مساحة عمل الفريق' }}
      </a>
      @endif

      @else
      @if(( $allteams->first() ? $allteams->first()['tm_acceptInvition'] : null ) == null)
      <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createTeamModal">
        <i class="bi bi-plus-circle-fill me-2"></i>
        {{ $l == 'en' ? 'Create New Team' : 'تكوين فريق جديد' }}
      </button>
      @endif
      @endif

      <h6 class="mt-4">{{ $l == 'en' ? 'Join Existing Team:' : 'الانضمام إلى فريق قائم:' }}</h6>

      <div class="list-group" id="available-teams-list">

        @foreach($allteams as $team)

        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong>{{ $team['tf_name'] }}</strong>
            ({{ $team['members_count'] }}/{{ $count_team }} {{ $l == 'en' ? 'members' : 'أعضاء' }})
            <br>
            <small class="text-muted">
              {{ $team['tf_description'] }}
            </small>
          </div>

          @if($team['members_count'] < $count_team) @if($team['tm_user_id']!=Auth::user()->id)
            @php
            $hasJoin = $team_invition->contains('tm_acceptInvition', 'join');
            $hasAccepted = $team_invition->contains('tm_acceptInvition', 'accepted');
            $allRejected = $team_invition->every(function($item) {
            return $item->tm_acceptInvition === 'rejected';
            });
            @endphp
            @if(!$hasJoin && !$hasAccepted && $allRejected)

            <button class="btn btn-outline-primary btn-sm"
              onclick="joinTeam('{{ $team['tf_id'] }}','{{ $team['tf_name'] }}')">
              {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
              <i class="bi bi-person-plus-fill ms-2"></i>
            </button>
            @else
            @endif
            @endif
            @else
            <button class="btn btn-outline-primary btn-sm" disabled>
              {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
              <i class="bi bi-person-plus-fill ms-2"></i>
            </button>
            @endif
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div> --}}