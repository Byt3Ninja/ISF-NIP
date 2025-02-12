<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
