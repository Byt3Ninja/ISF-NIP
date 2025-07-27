<div class="card h-100">
  <div class="card-header p-0">
    @php($color = $hackathon['status'] == 'Upcoming' ? '696cff' : '28c76f')
    <img 
      src="{{ $hackathon['image'] ? $hackathon['image'] : 'https://placehold.co/600x250/' . $color . '/ffffff?text=' . urlencode($hackathon['title']) }}"
      class="card-img-top" 
      alt="{{ $hackathon['title'] }}"
      style="height: 200px; object-fit: cover;"
    />
    <div class="position-absolute top-0 end-0 m-3">
      <span class="badge bg-label-{{ $hackathon['status'] == 'Upcoming' ? 'primary' : 'success' }} hackathon-status-badge">
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
        <span class="badge bg-label-{{ ['primary', 'success', 'info', 'warning', 'danger', 'secondary'][array_rand(['primary', 'success', 'info', 'warning', 'danger', 'secondary'])] }} me-1">
          {{ $tag }}
        </span>
      @endforeach
    </div>
    
    @php( $isUpcoming = $hackathon['status'] == 'Upcoming' )
    <div class="mt-auto">
      <div class="d-flex w-100 mb-2" role="group">
        <a href="/dashboard/hackathon/details/{{ $hackathon['id'] }}" class="btn btn-outline-primary me-2" style="flex: 1;">
          <i class="ti ti-info-circle me-1"></i>
          {{ $l == 'en' ? 'Details' : 'التفاصيل' }}
        </a>
        @if($user->hasRole('admin') || $user->hasRole('organizationmanager'))
       
          <a href="hackathon/dashboard/{{$hackathon['id']}}"  class="btn btn-outline-success me-2" style="flex: 1;" >
            <i class="ti ti-edit me-1"></i>
            {{ $l == 'en' ? 'Manage Hackathon' : 'ادارة الهاكثون' }}
          </a>
          
        


        @else
          @if($apply && $apply->isEmpty())
        <button 
          type="button" 
          class="btn btn-{{ now()->isBefore($hackathon['step_end']) == 'Upcoming' ? 'primary' : 'success'}}" style="flex: 1;" data-bs-toggle="modal"
          @if(now()->isBefore($hackathon['step_end']))
          {{-- data-bs-target="#applyModal{{ $loop->index + 1 }}" --}}
          onclick="window.location.href='/dashboard/apply/{{ $hackathon['step_id'] }}/hackathon'"
          @else
          onclick="window.location.href='/dashboard/hackathon/{{ $hackathon['team_formation_id'] }}/manage'"
          {{-- onclick="window.location.href='{{ route('admin.hackathon.manage', $hackathon['id']) }}'" --}}
          @endif
        >
          <i class="ti ti-{{  now()->isBefore($hackathon['step_end']) == 'Upcoming' ? 'send' : 'users' }} me-1"></i>
          {{ now()->isBefore($hackathon['step_end']) ? __('admin.ApplyHackathon') : __('admin.Manage Team') }}
        </button>
        @else
        @if($team && $team->isEmpty())
       
        @if($stypeStep->first()->stype != 6)

        <button type="button" class="btn btn-warning" style="flex: 1;" disabled ><i class="ti ti-users me-1"></i>{{ $l == 'en' ? ' Wait for Approval' : ' انتظر موافقة المسئول' }}</button>
        @else
        

        @endif
        @else
        <a href="/dashboard/hackathon/{{ $hackathon['team_formation_id'] }}/manage" class="btn btn-success" style="flex: 1;"><i class="ti ti-users me-1"></i>{{ $l == 'en' ? 'Manage Team' : 'ادارة الفريق' }}</a>
        @endif
        @endif

        @endif <!-- If with Admin Role  -->


      </div>
      @if (now()->isBefore($hackathon['step_end']))
        <small class="text-muted text-center d-block fw-medium mt-2">
          {{ $l == 'en' ? 'Applications open until ' . date('F j, Y', strtotime('+1 week')) : 'التقديم مفتوح حتى ' . date('j F Y', strtotime('+1 week')) }}
        </small>
      @else
        <small class="text-warning text-center d-block fw-medium mt-2">
          {{ $l == 'en' ? 'Applications closed - Hackathon in progress' : 'التقديم مغلق - الهاكثون جاري' }}
        </small>
      @endif
    </div>
  </div>
</div>
