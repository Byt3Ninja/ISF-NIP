<!-- resources/views/admin/organizations/partials/team_management.blade.php -->
<div class="card">
    <div class="card-header">
        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteUserModal">{{__('admin.Invite_org_managers')}}</a>
    </div>

    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">{{__('admin.First Name')}}</th>
                    <th class="text-center">{{__('admin.Last Name')}}</th>
                    <th class="text-center">{{__('admin.Email')}}</th>
                    <th class="text-center">{{__('admin.Role')}}</th>
                    <th class="text-center">{{__('admin.Actions')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invitations as $invitation)
                    <tr>
                        <td class="text-center">{{ $invitation->first_name }}</td>
                        <td class="text-center">{{ $invitation->last_name }}</td>
                        <td class="text-center">{{ $invitation->email }}</td>
                        <td class="text-center">{{ $invitation->role }}</td>
                        <td class="text-center">
                            @if($invitation->status === 'pending')
                            <form action="{{ route('organization.users_invitations_resend', $invitation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" title="Resend Invitation">
                                    <i class="fas fa-redo-alt"></i>
                                </button>
                            </form>
                            @endif
                        
                            <form action="{{ route('organization.users_invitations_cancel', $invitation->id) }}" method="POST" style="display: inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Cancel Invitation">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for inviting a user -->
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-labelledby="inviteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteUserModalLabel">{{__('admin.Invite_org_managers')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('organization.users_invite') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">{{__('admin.First Name')}}</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">{{__('admin.Last Name')}}</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">{{__('admin.Email')}}</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">{{__('admin.Role')}}</label>
                        <select class="form-control" id="role" name="role" required>
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('admin.Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('admin.send_invitation')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
