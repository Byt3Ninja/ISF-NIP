<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSheet extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'user_id', 'date', 'hours_worked', 'description', 'category_id', 'program_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class,'program_id','id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }



}
