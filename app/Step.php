<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Step values explained
 * 
 * 1. Custom Form
 * 2. Screening
 * 3. Mentorship
 * 4. Training
 * 5. Final Screening
*/

class Step extends Model
{
	// use Auditable;
	protected $casts = ['from'  => 'date:Y-m-d', 'to' => 'date:Y-m-d',];
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
		return ( $this->belongsToMany('App\Field', 'form_fields')->withPivot('id', 'options'));
	}

	public function formData(){
		return $this->hasMany(Formdata::class,'step_id');
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
}
