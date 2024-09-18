<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'tag_training','tag_id', 'training_id');
    }
}
