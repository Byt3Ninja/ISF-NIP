<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationSettings extends Model
{
    use SoftDeletes;

    protected $table = 'organizations_settings';

    protected $fillable = [
        'organization_id',
        'email_provider',
        'resend_api_key',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_address',
        'smtp_from_name',
        'smtp_last_error',
        'smtp_failed_at',
        'accept_email',
        'accept_whatsapp',
        'accept_pusher',
    ];

    protected $casts = [
        'accept_email' => 'boolean',
        'accept_whatsapp' => 'boolean',
        'accept_pusher' => 'boolean',
        'smtp_failed_at' => 'datetime',
    ];

    /**
     * Get the organization that owns these settings.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Convert settings to array format expected by MailConfig service.
     */
    public function toMailConfigArray(): array
    {
        if ($this->email_provider === 'resend') {
            return [
                'transport' => 'resend',
                'api_key' => $this->resend_api_key,
                'from_address' => $this->smtp_from_address,
                'from_name' => $this->smtp_from_name,
            ];
        }

        return [
            'transport' => 'smtp',
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'username' => $this->smtp_username,
            'password' => $this->smtp_password,
            'encryption' => $this->smtp_encryption,
            'from_address' => $this->smtp_from_address,
            'from_name' => $this->smtp_from_name,
        ];
    }
}
