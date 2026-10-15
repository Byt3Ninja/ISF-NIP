<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasProtectedDocuments;
use App\Enums\ProtectedFileSet;

class ProgramTask extends Model
{
    use HasFactory, SoftDeletes, HasProtectedDocuments;

    protected $fillable = [
        'title',
        'description',
        'program_id',
        'priority',
        'status',
        'due_date',
        'tags',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'attachments' => 'array',
        'due_date' => 'date',
    ];

    /**
     * Attachments as [{name, path, url, status, …}], with `url` recomputed from each
     * file's record (authorized route, or null for a viewer who may not open it).
     */
    public function getAttachmentsAttribute($value)
    {
        return $this->protectedDocumentList($value, ProtectedFileSet::TASK_ATTACHMENT, 'task');
    }

    /**
     * Get the program that this task belongs to
     */
    public function program()
    {
        return $this->belongsTo(Cycle::class, 'program_id');
    }

    /**
     * Get the user who created this task
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the companies assigned to this task
     */
    public function comments()
    {
        return $this->hasMany(ProgramTaskComment::class, 'program_task_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(ProgramTaskFeedback::class, 'program_task_id');
    }

    public function activities()
    {
        return $this->hasMany(ProgramTaskActivity::class, 'program_task_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(Company::class, 'program_task_assignees', 'program_task_id', 'company_id')
                    ->withPivot('status', 'last_updated', 'attachment')
                    ->withTimestamps();
    }

    /**
     * Get the companies for a program
     */
    public static function getProgramCompanies($programId)
    {
        return Company::whereHas('cycledata', function ($q) use ($programId) {
            $q->where('id', $programId);
        })->get();
    }
}
