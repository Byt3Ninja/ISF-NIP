<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'start_date', 'end_date', 'is_published'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
