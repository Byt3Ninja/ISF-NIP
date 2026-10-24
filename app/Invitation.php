<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'role',
        'organization_id',
        'user_id',
        'code',
        'expires_at',
        'status'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Link mailed to the invitee: signed, and valid only until the invitation expires. */
    public function setPasswordUrl(): string
    {
        return URL::temporarySignedRoute('invitations.set_password_view_form', $this->linkExpiry(), ['invitationId' => $this->id]);
    }

    /** Action of the set-password form, signed the same way. */
    public function acceptUrl(): string
    {
        return URL::temporarySignedRoute('invitations.accept', $this->linkExpiry(), ['invitationId' => $this->id]);
    }

    private function linkExpiry(): Carbon
    {
        return Carbon::parse($this->expires_at);
    }
}
