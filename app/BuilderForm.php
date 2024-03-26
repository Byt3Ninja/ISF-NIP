<?php

namespace App;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BuilderForm extends Model
{
    use Auditable;
    protected $guarded = [];

    public function fields()
    {
        return ( $this->belongsToMany('App\Field', 'builder_fields')->withPivot('id', 'options'));
    }

    /**
     * @return BelongsToMany
     **/
    public function cycles()
    {
        return $this->belongsToMany(Cycle::class, Step::class, 'data', 'cycle_id');
    }

    public function organization(){
        return $this->belongsTo(Organization::class,'organization_id');
    }
}
