<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerOption extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'option_text', 'score'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
