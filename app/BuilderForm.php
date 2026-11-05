<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BuilderForm extends Model
{
    use Auditable;
    // organization_id is the tenant key: never mass-assignable, set it explicitly.
    protected $fillable = [
        'form_name',
    ];
    public static $module = 'Builder Form';

    public function fields()
    {
        return ( $this->belongsToMany('App\Field', 'builder_fields')->withPivot('id', 'options', 'sort_order'));
    }

    /**
     * @return BelongsToMany
     **/
    public function cycles()
    {
        // One row per step that uses the form; a program with several such
        // steps is still one program.
        return $this->belongsToMany(Cycle::class, Step::class, 'data', 'cycle_id')->distinct();
    }

    public function organization(){
        return $this->belongsTo(Organization::class,'organization_id');
    }

    public function steps()
{
    return $this->hasMany(Step::class, 'data', 'id');
}
}
