<?php

namespace App\Services\Importer;

use App\User;
use App\Organization;
use Illuminate\Database\Eloquent\Model;

class ImporterSession extends Model
{
    protected $table = 'importer_sessions';

    public const STATUS_UPLOADED          = 'uploaded';
    public const STATUS_VALIDATED         = 'validated';
    public const STATUS_CONFLICTS_REVIEWED= 'conflicts_reviewed';
    public const STATUS_IMPORTING         = 'importing';
    public const STATUS_COMPLETED         = 'completed';
    public const STATUS_FAILED            = 'failed';
    public const STATUS_DISCARDED         = 'discarded';

    public const TERMINAL_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_DISCARDED,
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'original_filename',
        'file_path',
        'file_size',
        'file_hash',
        'row_count',
        'headers',
        'mappings',
        'organization_id',
        'conflict_warnings',
        'import_result',
        'expires_at',
    ];

    protected $casts = [
        'headers'           => 'array',
        'mappings'          => 'array',
        'conflict_warnings' => 'array',
        'import_result'     => 'array',
        'expires_at'        => 'datetime',
        'file_size'         => 'integer',
        'row_count'         => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }
}
