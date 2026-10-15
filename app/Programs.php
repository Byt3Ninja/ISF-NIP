<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasFileCleanup;

class Programs extends Model
{
    use HasFactory, HasFileCleanup;

    // The guideline documents are confidential and handled through protected_files (see App\Cycle).
    protected array $fileFields = [
        'img_url' => 'public_uploads',
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
