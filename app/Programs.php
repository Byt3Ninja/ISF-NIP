<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasFileCleanup;

class Programs extends Model
{
    use HasFactory, HasFileCleanup;

    protected array $fileFields = [
        'img_url' => 'public_uploads',
        'program_guideline_file' => 'public_uploads',
        'financial_guideline_file' => 'public_uploads',
        'legal_guideline_file' => 'public_uploads',
        'delegation_file' => 'public_uploads',
    ];

    public function getImgUrlUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->img_url, 'public_uploads');
    }

    public function getProgramGuidelineFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->program_guideline_file, 'public_uploads');
    }

    public function getFinancialGuidelineFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->financial_guideline_file, 'public_uploads');
    }

    public function getLegalGuidelineFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->legal_guideline_file, 'public_uploads');
    }

    public function getDelegationFileUrlAttribute()
    {
        return app(\App\Services\FileManagerService::class)->getUrl($this->delegation_file, 'public_uploads');
    }
}
