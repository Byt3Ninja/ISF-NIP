<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersBio extends Model
{
    protected $table = 'users_bio';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $fillable = [
        'industries',
    ];
    protected $casts = [
        'industries' => 'array',
    ];

    // ==============> Attributes <===================
    public function getMentorshipApproachesAttribute()
    {

        return $this->mentorship_approach=="null"|| is_null($this->mentorship_approach)?[]:json_decode($this->mentorship_approach);
    }
    public function user()
    {
      return $this->belongsTo(User::class, 'user_id');
    }
    public function industry($ids = null)
    {
      return StaticCategory::whereIn('id', $ids ?? [])->get();
    }  

}
