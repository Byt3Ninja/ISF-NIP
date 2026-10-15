<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BuilderField extends Model
{
    public $timestamps = false;

    // builder_form_id and field_id are owner foreign keys: never mass-assignable, set them explicitly.
    protected $fillable = [
        'options',
        'sort_order',
    ];

    /**
     * Get the field's option.
     *
     * @param  string  $value
     * @return array
     */
    public function getOptionsAttribute($value)
    {
        return json_decode($value);
    }
}
