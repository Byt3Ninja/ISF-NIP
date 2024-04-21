<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Formfield extends Model
{
    protected $table ='form_fields';
    public function step () {
        return $this->belongsTo(Step::class);
    }

    public function getOptionsAttribute($value)
    {
        return json_decode($value);
    }
}
