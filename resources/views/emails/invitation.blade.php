<p>Hello {{ $invitation->first_name }},</p>
<p>You've been invited to join the Organization {{ $invitation->organization->name }}.</p>
<p>Click the Link below to set your Password and complete your Registration:</p>
<a href="{{ route('set-password-form', $invitation->id) }}">Set Your Password</a>
<p>This invitation will expire in 2 days.</p>
