<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CycleBenefit extends Model
{
    protected $table = 'cycle_benefits';

    protected $fillable = [
        'cycle_id',
        'label_en',
        'label_ar',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }
}
