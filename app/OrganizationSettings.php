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
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'smtp_from_address',
        'smtp_from_name',
        'accept_email',
        'accept_whatsapp',
        'accept_pusher',
    ];

    protected $casts = [
        'accept_email' => 'boolean',
        'accept_whatsapp' => 'boolean',
        'accept_pusher' => 'boolean',
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
        return [
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
