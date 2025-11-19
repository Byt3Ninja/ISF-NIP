<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

return new class extends Migration
{
    public function up(): void
    {
        // Define inline anonymous models instead of declaring classes
        $organizationModel = new class extends Model {
            protected $table = 'organizations';
            protected $guarded = [];
            public $timestamps = false;
        };

        $organizationSettingModel = new class extends Model {
            protected $table = 'organizations_settings';
            protected $guarded = [];
            public $timestamps = true;
        };

        // Copy data in chunks
        $organizationModel->newQuery()->chunk(100, function ($organizations) use ($organizationSettingModel) {
            foreach ($organizations as $org) {
                $organizationSettingModel->newQuery()->create([
                    'organization_id'             => $org->id,
                    'smtp_host'                  => $org->smtp_host,
                    'smtp_port'                  => $org->smtp_port,
                    'smtp_encryption'            => $org->smtp_encryption,
                    'smtp_username'              => $org->smtp_username,
                    'smtp_password'              => $org->smtp_password,
                    'smtp_from_address'          => $org->smtp_from_address,
                    'smtp_from_name'             => $org->smtp_from_name,
                    'accept_email'               => true,
                    'accept_whatsapp'            => false,
                    'accept_pusher'              => false,
                ]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('organizations_settings')) {
            \DB::table('organizations_settings')->truncate();
        }
    }
};
