<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScore extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'category_id',
        'subcategory_id',
        'stage_id',
        'score',
        'weight',
        'weighted_score',
        'assessment_id',
        'QuestionBank',
        'explanation_QA_ar',
        'explanation_QA_en',
        'Answers',
        'Result_assessment',
        'company_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function questionbank()
    {
        return $this->belongsTo(QuestionsBank::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


}
