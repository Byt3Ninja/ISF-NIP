<?php

namespace App;

use App\Services\HtmlSanitizer;
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

    /** Rich-text bodies for {!! !!} output; also clean rows saved before sanitize-on-write. */
    public function getSafeContentAttribute(): string
    {
        return HtmlSanitizer::clean($this->content);
    }

    public function getSafeContentArAttribute(): string
    {
        return HtmlSanitizer::clean($this->content_ar);
    }
}
