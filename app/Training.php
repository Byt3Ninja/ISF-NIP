<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Training extends Model
{
    protected $fillable = ['organization_id', 'is_public', 'type', 'image', 'title', 'dsc', 'user_id', 'code', 'CoursLevel', 'Quizs', 'Instractor', 'start_date', 'end_date', 'Status',];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function organization() { return $this->belongsTo(Organization::class); }

    public function contents()
    {
        return $this->hasMany(TrainingContent::class, 'training_id');
    }

    public function done()
    {
        return $this->hasMany(TrainingCompleted::class, 'course_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Expertise::class, 'categories_training','training_id', 'categories_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'tag_training','training_id', 'tag_id');
    }

    public function scopeVisibleToUser(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        $orgIds = $user->organizations_ids;

        return $query->where(function ($q) use ($orgIds) {
            $q->where('is_public', true);
            if (!empty($orgIds)) {
                $q->orWhereIn('organization_id', $orgIds);
            }
        });
    }

    public function progress()
    {
        $contents = $this->contents;
        $ids = $contents->pluck('id')->toArray();
        $completed = $this->done->where('user_id', auth()->id());
        $_completed = $completed->pluck('training_id')->toArray();
        $pending = array_diff($ids, $_completed);
        $percentage = count($ids) > 0 ? ( ( count($_completed) / count($ids) ) * 100 ) : 0;
        return collect(['completed' => $_completed, 'pending' => $pending, 'percentage' => $percentage * 100 ]);
    }
}
