<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    use HasFactory;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function children(){
        return $this->hasMany(Expertise::class,'parent_id');
    }

}
