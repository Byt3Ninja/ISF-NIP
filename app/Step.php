<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Step values explained
 *
 * 1. Custom Form
 * 2. Screening
 * 3. Mentorship
 * 4. Training
 * 5. Final Screening
 * 6. Team Formation
*/

class Step extends Model
{
	use HasFactory;
	// use Auditable;
	public static $module = 'Program Step';
	protected $casts = ['from'  => 'date:Y-m-d', 'to' => 'date:Y-m-d',];
    protected $guarded = [];
	public function cycle()
	{
		return $this->belongsTo(Cycle::class,'cycle_id');
	}

	public function form()
	{
		return $this->belongsTo(BuilderForm::class, "data", "id");
	}

	public function fields()
	{
		return ( $this->belongsToMany('App\Field', 'form_fields')->withPivot('id', 'options', 'builder_forms_id'));
	}

	public function formData(){
		return $this->hasMany(Formdata::class,'step_id');
	}

    public function formBuilder()
    {
        return $this->belongsTo(BuilderForm::class, "form_id", "id");
    }

	public function stepFiltrationJudges(){
		return $this->belongsToMany(User::class,Screening::class,'step_id','user_id');
	}

	public function companies(){
		return $this->hasMany(Company::class,'step');
	}

	public function CalenderSync()
	{
		return $this->morphMany(CalenderSync::class, 'calenderable');
	}

	// Accessor for judgers
    public function getJudgersAttribute()
    {
        if ($this->data === 'grouptogroup') {
            $judgeIds = collect($this->list ?? [])->flatten()->unique()->filter()->values();
            return $judgeIds->count();
        } elseif ($this->data === 'onetoall') {
            $judgeIds = is_array($this->list) ? collect($this->list)->filter()->values() : collect([$this->list])->filter();
            return $judgeIds->count();
        } elseif ($this->data === 'grouptoall') {
            $judgeIds = collect($this->list ?? [])->flatten()->unique()->filter()->values();
            return $judgeIds->count();
        }

        $judgeIds = Screening::where('step_id', $this->id)
            ->whereNotNull('users')
            ->pluck('users')
            ->map(function ($users) {
                return is_array($users) ? $users : json_decode($users, true) ?? [];
            })
            ->flatten()
            ->unique()
            ->filter()
            ->values();

        return $judgeIds->count();
    }

	 public function screenings()
    {
        return $this->hasMany(Screening::class, 'step_id');
    }

	public function getStypeLabelAttribute()
{
    return match ($this->stype) {
        1 => __('cycles.Custom Form'),
        2 => __('cycles.Screening'),
        3 => __('cycles.Mentorship'),
        4 => __('cycles.Training'),
        5 => __('cycles.Final evaluation'),
        6 => __('admin.Team Formation'),
        default => null,
    };
}
}
