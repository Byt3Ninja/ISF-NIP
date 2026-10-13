<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Formfield extends Model
{
    protected $table ='form_fields';
    // step_id, field_id and builder_forms_id are owner foreign keys: never mass-assignable, set them explicitly.
    protected $fillable = [
        'options',
    ];
    public function step () {
        return $this->belongsTo(Step::class);
    }

    public function getOptionsAttribute($value)
    {
        return json_decode($value);
    }
}
