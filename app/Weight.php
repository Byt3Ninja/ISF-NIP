<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weight extends Model
{
    use HasFactory;
    protected $fillable = [
        'stage_id',
        'category_id',
        'subcategory_id',
        'question_id',
        'weight',
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    public function question()
    {
        return $this->belongsTo(QuestionsBank::class, 'question_id');
    }
}
