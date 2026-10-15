<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** One row per attempt to open a confidential file (see ProtectedFileController). */
class FileAccessLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'protected_file_id', 'action', 'set', 'requested', 'ip', 'user_agent'];
}
