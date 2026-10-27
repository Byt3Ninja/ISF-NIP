<div class="card h-100">
  <div class="card-header p-0">
    {{-- {{ dd(asset($hackathon['image'])) }} --}}
    <img
      src="{{ $hackathon['image'] ? asset($hackathon['image']) : 'https://placehold.co/600x250/' . $hackathon['placeholder_color'] . '/ffffff?text=' . urlencode($hackathon['title']) }}"
      onerror="this.onerror=null;this.src='https://placehold.co/600x250/' + '{{ $hackathon['placeholder_color'] }}' + '/ffffff?text=' + '{{ $hackathon['title'] }}';"
      class="card-img-top" alt="{{ $hackathon['title'] }}" style="height: 200px; object-fit: cover;" />
    <div class="position-absolute top-0 end-0 m-3">
      @if($hackathon['show_enrolled_badge'] ?? false)
        <span class="badge bg-success me-1">
          <i class="ti ti-circle-check me-1"></i>{{ __('admin.Enrolled') }}
        </span>
      @endif
      <span
        class="badge bg-label-{{ $hackathon['status'] == 'Upcoming' ? 'primary' : 'success' }} hackathon-status-badge">
        {{ $hackathon['status'] }}
      </span>
    </div>
  </div>
  <div class="card-body d-flex flex-column mt-4">
    <h5 class="card-title fw-semibold">{{ $hackathon['title'] }}</h5>
    <div class="mb-3">
      <small class="text-muted d-block">
        <i class="ti ti-calendar me-1"></i> {{ $hackathon['date'] }}
      </small>
      <small class="text-muted d-block">
        <i class="ti ti-map-pin me-1"></i> {{ $hackathon['location'] }}
      </small>
    </div>
    <p class="card-text text-muted">{{ $hackathon['description'] }}</p>
    <div class="mb-3">
      @foreach ($hackathon['tags'] as $tag)
      <span
        class="badge bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger', 'secondary'][array_rand(['primary', 'success', 'info', 'warning', 'danger', 'secondary'])] }} me-1">
        {{ $tag }}
      </span>
      @endforeach
    </div>

    @if($hackathon['show_enrolled_badge'] ?? false)
      <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3" role="alert">
        <i class="ti ti-circle-check me-2"></i>
        <small class="fw-medium">
          {{ ($hackathon['enrolled_as'] ?? null) === 'leader'
            ? __('admin.You_are_the_team_leader_here')
            : __('admin.You_are_enrolled_in_this_hackathon') }}
        </small>
      </div>
    @endif

    @if($hackathon['show_invitations'] ?? false)
      <div class="alert alert-warning d-flex align-items-center py-2 px-3 mb-3" role="alert">
        <i class="ti ti-mail-opened me-2"></i>
        <small class="fw-medium">
          {{ __('admin.You_have_been_invited_to_join') }}
          {{ $hackathon['single_invitation']
            ? $hackathon['single_invitation']->team_name
            : $hackathon['invitation_count'] . ' ' . __('admin.Pending_Invitations') }}
        </small>
      </div>
    @endif

    <div class="mt-auto">
      <div class="d-flex w-100 mb-2" role="group">
        <a href="/dashboard/hackathon/details/{{ $hackathon['id'] }}" class="btn btn-outline-primary me-2"
          style="flex: 1;">
          <i class="ti ti-info-circle me-1"></i>
          {{ $l == 'en' ? 'Details' : 'التفاصيل' }}
        </a>
          {{-- Gated on the permission that guards the destination route rather than
               on hardcoded role names: the role check silently excluded Sub-Admins,
               who do hold hackathon.viewAdminHackaton by default, and it ignored any
               custom role an admin grants the permission to. (GV-3798 / GV-3799) --}}
          @if($_user->ableTo('hackathon.viewAdminHackaton', $permissions))

            <a href="hackathon/dashboard/{{$hackathon['id']}}" class="btn btn-outline-success me-2" style="flex: 1;">
              <i class="ti ti-edit me-1"></i>
              <span class="text-nowrap text-sm">{{ $l == 'en' ? 'Manage Hackathon' : 'ادارة الهاكثون' }}</span>
            </a>
          @elseif($hackathon['show_invitations'] ?? false)
            {{-- A pending invitation owns the card: Accept/Reject are the only primary
                 actions, so Apply is withheld until the invitation is resolved. It comes
                 back on its own once the Founder rejects. (GV-7377) --}}
          @elseif($hackathon['show_enrolled_badge'] ?? false)
            {{-- Already holds a seat in this hackathon, so Apply would be a second path to
                 an outcome they have already reached. (GV-7377) --}}
            @if($hackathon['team_formation_id'] != null)
              <a href="/dashboard/hackathon/{{ $hackathon['team_formation_id'] }}/manage" class="btn btn-success"
                style="flex: 1;">
                <i class="ti ti-users me-1"></i>{{ $l == 'en' ? 'Manage Team' : 'ادارة الفريق' }}
              </a>
            @elseif($hackathon['joined_team_id'] != null)
              <a href="/dashboard/hackathon/{{ $hackathon['joined_team_id'] }}/manage" class="btn btn-success"
                style="flex: 1;">
                <i class="ti ti-users me-1"></i>{{ $l == 'en' ? 'View My Team' : 'مشاهدة الفريق' }}
              </a>
            @endif
          @else

             @if($isExistinghackathon == true )
           
                @if($apply && $apply->isEmpty())
                
                  @if($hackathon['exist_step'] == true)
                    <button type="button"
                      class="btn btn-primary"
                      style="flex: 1;" data-bs-toggle="modal" 
                      onclick="window.location.href='/dashboard/apply/{{ $hackathon['step_id'] }}/hackathon'" >
                      <i class="ti ti-send me-1"></i>
                      {{ __('admin.ApplyHackathon') }}
                    </button>
                  @else
                    @if($hackathon['exist_step'] = false)
                    <button type="button" class="btn btn-primary" style="flex: 1;" disabled>
                      <i class="ti ti-send me-1"></i>
                      <span class="text-nowrap">{{ __('admin.ApplyHackathon') }} - {{ $l == 'en' ? 'No Forms' : ' لا يوجد نموذج' }}</span>
                    </button>
                    @else

                    @endif
                  @endif
                @else
                
                  @if($team && $team->isEmpty() )
                   
                      @if($user->team_member == null && ($stypeStep?->first()->stype == 1 && $hackathon['cycle_id'] == $user_cycle_id ))

                        <button type="button" class="btn btn-warning" style="flex: 1;" disabled><i class="ti ti-users me-1"></i>{{ $l ==
                          'en' ? ' Wait for Approval' : ' انتظر موافقة المسئول' }}</button>
                      @else


                      @endif
                  @else
                  
                  @if($hackathon['team_formation_id'] != null)
                    <a href="/dashboard/hackathon/{{ $hackathon['team_formation_id'] }}/manage" class="btn btn-success"
                      style="flex: 1;"><i class="ti ti-users me-1"></i>{{ $l == 'en' ? 'Manage Team' : 'ادارة الفريق' }}</a>
                      @endif
                  @endif
                @endif
              @else

                  @if($hackathon['exist_step'] == true)
                    <button type="button"
                      class="btn btn-primary"
                      style="flex: 1;" data-bs-toggle="modal" 
                      onclick="window.location.href='/dashboard/apply/{{ $hackathon['step_id'] }}/hackathon'" >
                      <i class="ti ti-send me-1"></i>
                      {{ __('admin.ApplyHackathon') }}
                    </button>
                  @else
                  {{-- @dd($hackathon['cycle_id'], $user_cycle_id)
                    @if($hackathon['cycle_id'] == $user_cycle_id) --}}
                      <button type="button" class="btn btn-primary" style="flex: 1;" disabled>
                        <i class="ti ti-send me-1"></i>
                        <span class="text-nowrap">
                          {{ __('admin.ApplyHackathon') }} - {{ $l == 'en' ? 'No Forms' : ' لا يوجد نموذج' }}
                        </span>
                      </button>
                    {{-- @else --}}

                    {{-- @endif --}}
                  @endif

                  
              @endif

          @endif
        <!-- If with Admin Role  -->


      </div>
      
      @if($hackathon['show_invitations'] ?? false)
        <div class="d-flex w-100 mb-2" role="group">
          @if($hackathon['single_invitation'])
            <form action="{{ route('hackathon.acceptInvitation', $hackathon['single_invitation']->id) }}" method="post"
              class="me-2" style="flex: 1;">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn-success w-100">
                <i class="ti ti-check me-1"></i>
                {{ __('admin.Accept_Invitation') }}
              </button>
            </form>
            <form action="{{ route('hackathon.rejectInvitation', $hackathon['single_invitation']->id) }}" method="post"
              class="me-2" style="flex: 1;">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn-outline-danger w-100">
                <i class="ti ti-x me-1"></i>
                {{ __('admin.Reject_Invitation') }}
              </button>
            </form>
          @else
            <button type="button" class="btn btn-success me-2" style="flex: 1;" data-bs-toggle="modal"
              data-bs-target="#hackathonInvitationsModal{{ $hackathon['id'] }}">
              <i class="ti ti-users me-1"></i>
              {{ __('admin.Pending_Invitations') }}
            </button>
          @endif

          <button type="button" class="btn btn-outline-warning position-relative" data-bs-toggle="modal"
            data-bs-target="#hackathonInvitationsModal{{ $hackathon['id'] }}"
            title="{{ __('admin.Hackathon_Invitations') }}">
            <i class="ti ti-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              {{ $hackathon['invitation_count'] }}
            </span>
          </button>
        </div>
      @endif

      {{-- GV-9566: open through its last (inclusive) day in the platform zone; `status`
           is computed that way in the controller (isBefore() closed it on its last morning). --}}
      @if (($hackathon['status'] ?? null) !== 'Finished')
        <small class="text-muted text-center d-block fw-medium mt-2">
          {{ $l == 'en' ? 'Applications open until ' . $hackathon['cycle_end']->format('F j, Y') : 'التقديم مفتوح حتى ' .
          $hackathon['cycle_end']->format('F j, Y') }}
        </small>
      @else
        <small class="text-warning text-center d-block fw-medium mt-2">
          {{ $l == 'en' ? 'Applications closed - Hackathon in progress' : 'التقديم مغلق - الهاكثون جاري' }}
        </small>
      @endif

    </div>
  </div>
</div>

@if($hackathon['show_invitations'] ?? false)
  @include('admin.hackathon.partials.hackathon-invitations-modal', [
    'hackathon' => $hackathon,
    'cardInvitations' => $hackathon['invitations'],
    'l' => $l,
  ])
@endif