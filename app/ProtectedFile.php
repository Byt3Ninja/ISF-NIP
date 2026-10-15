<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A confidential uploaded file. Rows are created only by
 * App\Services\ProtectedFiles\ProtectedFiles (uploads and the migration);
 * the owner recorded here, not a path stored elsewhere, decides who may open it.
 */
class ProtectedFile extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'uuid'];

    protected $casts = [
        'owner_id' => 'integer',
        'company_id' => 'integer',
        'cycle_id' => 'integer',
        'size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProtectedFile $file) {
            $file->uuid = $file->uuid ?: (string) Str::uuid();
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }
}
