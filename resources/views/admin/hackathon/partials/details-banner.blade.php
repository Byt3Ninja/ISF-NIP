<div class="text-center hackathon-banner">
<div class="banner-content">
  <div class="hackathon-status-badge">
    {{ $currentHackathon['status'] == 'Finished' ? __('cycles.Completed') : $currentHackathon['status'] }}
  </div>
  <h1 class="display-5 fw-bold" id="hackathon-main-title">
    {{ $l == 'en' ? $currentHackathon['title'] : $currentHackathon['title'] }}
  </h1>
  <p class="lead"> {{ $l == 'en' ? $currentHackathon['description'] : $currentHackathon['description'] }} </p>
  <p class="lead" id="hackathon-dates-location">
    <i class="ti ti-calendar-event me-2"></i>
    {{ $l == 'en' ? $currentHackathon['date'] : $currentHackathon['date'] }}
    <span class="mx-3">|</span>
    <i class="ti ti-building me-2"></i>
    {{ $l == 'en' ? $currentHackathon['location'] : $currentHackathon['location'] }}
  </p>
  <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
    @foreach(($l == 'en' ? $currentHackathon['tags'] : $currentHackathon['tags']) as $tag)
    <span class="badge bg-light text-dark px-3 py-2"
      style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; border: 1px solid rgba(255, 255, 255, 0.3);">
      {{ $tag }}
    </span>
    @endforeach
  </div>
  @php
  if($user->team_member != null){
    $user_cycle_id = $user->team_member?->teamFormation?->user?->company?->cycle ?? null;
  }else{
    $user_cycle_id = $user->company?->cycle ?? null;
  }
  @endphp
  @if($currentHackathon['cycle_id'] == $user_cycle_id)
    <div id="action-buttons-placeholder" class="mt-4">
      <!-- Buttons will be injected by JS -->
        @php
        $teamcreated = App\TeamFormation::where('user_id', auth()->user()->id)->first();
        $teamJoin = App\Team_member::where('Email', auth()->user()->email)->where('acceptInvition',
        'pending')->first();
        $teamaccept = App\Team_member::where('Email', auth()->user()->email)->where('acceptInvition',
        'accepted')->first();

        @endphp
      @if(!auth()->user()->hasRole('admin'))
        @if($teamcreated != null)
          @if( $teamcreated->cycle_id == $currentHackathon['cycle_id'] || $showStep == true )
            <a href="/dashboard/hackathon/{{ $teamcreated->id }}/manage" class="btn btn-light btn-lg px-4 py-2 me-3"
              style="border-radius: 50px; font-weight: 600;">
              <i class="ti ti-settings me-2 me-2"></i>
              {{ $l == 'en' ? 'Manage My Team' : 'إدارة فريقي' }}
            </a>
          @else
            <button type="button" class="btn btn-primary" style="flex: 1;"
              onclick="window.location.href='/dashboard/apply/{{ $currentHackathon['step_id'] }}/hackathon'">
              <i class="ti ti-send me-1"></i>
              {{__('admin.ApplyHackathon') }}
            </button>
          @endif

        @else


          @if( $companyStep?->stype == 6 || $companyStep == null || $showStep == true )
            {{-- @if($teamJoin != null)
              <form action="{{ route('hackathon.acceptInvitation', $teamJoin->id) }}" class="d-inline-block" method="post">
                @csrf
                @method('PATCH')
                <button class="btn btn-success btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
                  <i class="ti ti-users me-2 me-2"></i>
                  {{ $l == 'en' ? 'Accept Invitation' : ' قبول الدعوة ' }}
                </button>
              </form>
              <form action="{{ route('hackathon.rejectInvitation', $teamJoin->id) }}" class="d-inline-block" method="post">
                @csrf
                @method('PATCH')
                <button class="btn btn-danger btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
                  <i class="ti ti-users me-2 me-2"></i>
                  {{ $l == 'en' ? 'Reject Invitation' : ' رفض الدعوة ' }}
                </button>
              </form>
            @else --}}

              @if($teamaccept == null)
                @if( $showStep == true )
                  <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                    data-bs-toggle="modal" data-bs-target="#createTeamModal">
                    <i class="ti ti-settings me-2 me-2"></i>
                    {{ $l == 'en' ? 'Create New Team' : 'كون فريقك' }}
                  </a>
                @elseif($companyStep?->stype != 6 && $companyStep?->stype == 1)
                  <button type="button" class="btn btn-primary" style="flex: 1;"
                    onclick="window.location.href='/dashboard/apply/{{ $currentHackathon['step_id'] }}/hackathon'">
                    <i class="ti ti-send me-1"></i>
                    {{__('admin.ApplyHackathon') }}
                  </button>
                @else
                  <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                    data-bs-toggle="modal" data-bs-target="#createTeamModal">
                    <i class="ti ti-settings me-2 me-2"></i>
                    {{ $l == 'en' ? 'Create New Team' : 'كون فريقك' }}
                  </a>
                @endif
              @else
                <a href="/dashboard/hackathon/{{ $teamaccept->team_formation_id }}/manage"
                  class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
                  <i class="ti ti-users me-2 me-2"></i>
                  {{ $l == 'en' ? 'View My Team' : 'مشاهدة الفريق' }}
                </a>
              @endif
            {{-- @endif --}}
          @else
            @if($companyStep->stype == 1)
              <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="ti ti-send me-2 me-2"></i>
                {{ $l == 'en' ? ' Wait for Approval' : ' انتظر موافقة المسئول' }}
              </a>
              @elseif($companyStep->stype == 2)
              <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="ti ti-send me-2 me-2"></i>
                {{ $l == 'en' ? ' Screening Step ' : ' خطة التقييم ' }}
              </a>
              @elseif($companyStep->stype == 3)
              <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="ti ti-send me-2 me-2"></i>
                {{ $l == 'en' ? ' MentorShip Step ' : ' خطة المشورة ' }}
              </a>
              @elseif($companyStep->stype == 4)
              <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="ti ti-send me-2 me-2"></i>
                {{ $l == 'en' ? ' Training Step ' : ' خطة التدريب ' }}
              </a>
              @elseif($companyStep->stype == 5)
              <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;"
                data-bs-toggle="modal" data-bs-target="#applyModal">
                <i class="ti ti-send me-2 me-2"></i>
                {{ $l == 'en' ? ' Final Evaluation Step ' : ' خطة التقييم النهاية ' }}
              </a>
              @else
              
            @endif
          @endif
        @endif

      @else
      
      @endif
    </div>
  @endif
             
            @if($teamJoins->count() > 0)
            @foreach($teamJoins as $teamJoin)
              {{-- @php
              $teamformation = App\TeamFormation::where('id', $teamJoin->team_formation_id)->first();
              @endphp --}}
              <div class="d-flex align-items-center">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }} {{ $teamJoin->tf_name }}</div>
              @if($teamJoin->tf_cycle_id == $id)
                <form action="{{ route('hackathon.acceptInvitation', $teamJoin->tm_id) }}" class="d-inline-block" method="post">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-success btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
                    <i class="ti ti-users me-2 me-2"></i>
                    {{ $l == 'en' ? 'Accept Invitation' : ' قبول الدعوة ' }}
                  </button>
                </form>
                <form action="{{ route('hackathon.rejectInvitation', $teamJoin->tm_id) }}" class="d-inline-block" method="post">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-danger btn-lg px-4 py-2 me-3" style="border-radius: 50px; font-weight: 600;">
                    <i class="ti ti-users me-2 me-2"></i>
                    {{ $l == 'en' ? 'Reject Invitation' : ' رفض الدعوة ' }}
                  </button>
                </form>
              @endif
              <br>
            @endforeach
            @endif
</div>
</div>
