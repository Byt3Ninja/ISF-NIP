<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProgramTaskAssignee extends Model
{
    protected $table = 'program_task_assignees';

    protected $fillable = ['program_task_id', 'company_id', 'status', 'last_updated', 'attachment'];

    protected $casts = ['attachment' => 'array'];

    public function task()
    {
        return $this->belongsTo(ProgramTask::class, 'program_task_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
