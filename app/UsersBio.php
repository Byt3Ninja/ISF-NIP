<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersBio extends Model
{
    protected $table = 'users_bio';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // ==============> Attributes <===================
    public function getMentorshipApproachesAttribute()
    {

        return $this->mentorship_approach=="null"|| is_null($this->mentorship_approach)?[]:json_decode($this->mentorship_approach);
    }

}
