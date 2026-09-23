{{-- Pending team invitations for a single hackathon, opened from the card bell. (GV-7377) --}}
<div class="modal fade" id="hackathonInvitationsModal{{ $hackathon['id'] }}" tabindex="-1"
  aria-labelledby="hackathonInvitationsModalLabel{{ $hackathon['id'] }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hackathonInvitationsModalLabel{{ $hackathon['id'] }}">
          <i class="ti ti-bell me-1"></i>
          {{ __('admin.Hackathon_Invitations') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-4">
          {{ __('admin.You_have_been_invited_to_join') }}
          <span class="fw-semibold">{{ $hackathon['title'] }}</span>
        </p>

        @forelse($cardInvitations as $invitation)
          <div class="d-flex align-items-center justify-content-between border rounded p-3 mb-3">
            <div class="me-3">
              <div class="fw-semibold">{{ $invitation->team_name }}</div>
              <small class="text-muted">
                <i class="ti ti-users me-1"></i>
                {{ __('admin.Invited_by_team') }}
              </small>
            </div>
            <div class="d-flex flex-shrink-0">
              <form action="{{ route('hackathon.acceptInvitation', $invitation->id) }}" method="post" class="me-2">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-success">
                  <i class="ti ti-check me-1"></i>
                  {{ __('admin.Accept_Invitation') }}
                </button>
              </form>
              <form action="{{ route('hackathon.rejectInvitation', $invitation->id) }}" method="post">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="ti ti-x me-1"></i>
                  {{ __('admin.Reject_Invitation') }}
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="alert alert-info mb-0 text-center">
            {{ __('admin.No_pending_invitations') }}
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
