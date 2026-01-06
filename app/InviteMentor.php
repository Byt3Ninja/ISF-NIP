<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteMentor extends Model
{
    use HasFactory;

    protected $table = 'invite_mentor';

    protected $fillable = [
        'mentor_id',
        'user_id',
        'email',
        'name',
        'organization_ids',
        'scope',
        'status',
        'subject',
        'message_content',
        'attachments',
    ];

    protected $casts = [
        'organization_ids' => 'array',
        'attachments'      => 'array',
    ];

    /* Relations */
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
