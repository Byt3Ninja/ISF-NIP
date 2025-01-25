<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',          // رقم الهاتف
        'otp_code',       // كود OTP
        'is_verified',    // حالة التحقق
        'expires_at',     // وقت انتهاء صلاحية الكود
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
