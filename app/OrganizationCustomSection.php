<?php

namespace App;

use App\Services\HtmlSanitizer;
use Illuminate\Database\Eloquent\Model;

class OrganizationCustomSection extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization_custom_sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'title',
        'content',
        'visibility',
        'bg_type',
        'bg_color',
        'bg_gradient_start',
        'bg_gradient_end',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the organization that owns the custom section.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the background style CSS for this section.
     *
     * @return string
     */
    public function getBackgroundStyle()
    {
        if ($this->bg_type === 'gradient') {
            $start = $this->bg_gradient_start ?? '#ffffff';
            $end = $this->bg_gradient_end ?? '#f8f9fa';
            return "background: linear-gradient(135deg, {$start} 0%, {$end} 100%);";
        }

        $color = $this->bg_color ?? '#ffffff';
        return "background-color: {$color};";
    }

    /**
     * Get the visibility label for display.
     *
     * @return string
     */
    public function getVisibilityLabel()
    {
        $labels = [
            'public' => __('admin.public'),
            'participants' => __('admin.participants'),
            'admins_only' => __('admin.admins_only'),
        ];

        return $labels[$this->visibility] ?? $this->visibility;
    }

    /** Rich-text body for {!! !!} output; also cleans rows saved before sanitize-on-write. */
    public function getSafeContentAttribute(): string
    {
        return HtmlSanitizer::clean($this->content);
    }
}
