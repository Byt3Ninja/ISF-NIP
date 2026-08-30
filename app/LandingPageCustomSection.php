<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingPageCustomSection extends Model
{
    protected $fillable = [
        'landing_page_id',
        'title',
        'title_ar',
        'title_tag',
        'title_tag_ar',
        'content',
        'content_ar',
        'visibility',
        'button_text',
        'button_link',
        'sort_order',
    ];

    protected $casts = [
        'visibility' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }
}
