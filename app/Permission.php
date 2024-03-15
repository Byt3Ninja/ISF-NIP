<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Permission extends Model
{
    protected $table = 'permissions';
    use Auditable;

    protected $fillable = [
        'name',
        'organization_id',
        'created_at',
        'updated_at',
    ];
}
