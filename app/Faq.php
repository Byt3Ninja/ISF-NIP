<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Faq extends Model
{
    use Auditable;
    public static $module = 'FAQ';
    protected $fillable = [
        'title_ar',
        'title_en',
        'answer_ar',
        'answer_en',
        'published',
    ];
}
