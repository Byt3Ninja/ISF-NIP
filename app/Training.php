<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = ['organization_id', 'type', 'image', 'title', 'dsc', 'user_id',];

    public function user() { return $this->belongsTo(User::class); }

    public function contents()
    {
        return $this->hasMany(TrainingContent::class, 'training_id');
    }

    public function done()
    {
        return $this->hasMany(TrainingCompleted::class, 'course_id');
    }

    public function progress()
    {
        $contents = $this->contents;
        $ids = $contents->pluck('id')->toArray();
        $completed = $this->done->where('user_id', auth()->id());
        $_completed = $completed->pluck('training_id')->toArray();
        $pending = array_diff($ids, $_completed);
        return collect(['completed' => $_completed, 'pending' => $pending, 'percentage' => count($_completed) / count($ids) * 100 ]);
    }
}
