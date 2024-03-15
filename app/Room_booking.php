<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room_booking extends Model
{
    protected $fillable = ['organization_id', 'fromDate','toDate','room_id','user_id', 'approved','user_added'];

    public function room()
    {
        return $this->belongsTo(Room::class,'room_id','id');
    }
    public function client()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function User()
    {
        return $this->belongsTo(User::class,'user_added','id');
    }
}
