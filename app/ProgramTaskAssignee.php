<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasProtectedDocuments;
use App\Enums\ProtectedFileSet;

class ProgramTaskAssignee extends Model
{
    use HasProtectedDocuments;

    protected $table = 'program_task_assignees';

    protected $fillable = ['program_task_id', 'company_id', 'status', 'last_updated', 'attachment'];

    protected $casts = ['attachment' => 'array'];

    /**
     * The startup's submitted files as [{name, path, url, …}], with `url` recomputed
     * from each file's record (authorized route, or null for a viewer who may not open it).
     */
    public function getAttachmentAttribute($value)
    {
        return $this->protectedDocumentList($value, ProtectedFileSet::TASK_ATTACHMENT, 'task_assignee');
    }

    public function task()
    {
        return $this->belongsTo(ProgramTask::class, 'program_task_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
