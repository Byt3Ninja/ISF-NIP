<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SustainableIcon extends Model
{
    use HasFactory;
    
    protected $table = 'sustainable_icon';

    // إذا كانت لديك حقول معينة، يمكنك تحديدها هنا
    protected $fillable = ['title', 'icon_ar', 'icon_en'];
}
