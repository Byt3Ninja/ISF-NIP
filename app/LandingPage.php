<?php

namespace App;

use App\Services\FileManagerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'logo',
        'slug',
        'status',
        'description',
        'description_ar',
        // General — social links
        'linkedin',
        'facebook',
        'instagram',
        'x',
        'tiktok',
        'youtube',
        // Hero
        'hero_title',
        'hero_title_ar',
        'hero_title_tag',
        'hero_title_tag_ar',
        'hero_description',
        'hero_description_ar',
        'hero_image',
        // About Us
        'about_is_enabled',
        'about_title',
        'about_title_ar',
        'about_title_tag',
        'about_title_tag_ar',
        'about_description',
        'about_description_ar',
        'about_cards',
        // How It Works
        'how_is_enabled',
        'how_title',
        'how_title_ar',
        'how_title_tag',
        'how_title_tag_ar',
        'how_description',
        'how_description_ar',
        'how_steps',
        // Programs
        'programs_is_enabled',
        'programs_title',
        'programs_title_ar',
        'programs_title_tag',
        'programs_title_tag_ar',
        'programs_description',
        'programs_description_ar',
    ];

    protected $casts = [
        'status' => 'boolean',
        'about_is_enabled' => 'boolean',
        'about_cards' => 'array',
        'how_is_enabled' => 'boolean',
        'how_steps' => 'array',
        'programs_is_enabled' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cycles()
    {
        return $this->belongsToMany(Cycle::class, 'landing_page_cycles');
    }

    public function customSections()
    {
        return $this->hasMany(LandingPageCustomSection::class)->orderBy('sort_order');
    }

    public function getPublicUrlAttribute()
    {
        if (!$this->slug) {
            return null;
        }

        return url('/lp/' . $this->slug);
    }

    public function getLogoUrlAttribute()
    {
        return app(FileManagerService::class)->getUrl($this->logo, 'public_uploads');
    }


    public function getHeroImageUrlAttribute()
    {
        return app(FileManagerService::class)->getUrl($this->hero_image, 'public_uploads');
    }

    /**
     * Whether the About Us section should render on the public landing page
     * for the given language. It must be enabled and have all mandatory
     * fields (title, title tag, description) filled for that language.
     *
     * @param bool $isAr Render language is Arabic
     * @return bool
     */
    public function isAboutRenderable($isAr = false)
    {
        if (!$this->about_is_enabled) {
            return false;
        }

        $mandatory = $isAr
            ? [$this->about_title_ar, $this->about_title_tag_ar, $this->about_description_ar]
            : [$this->about_title, $this->about_title_tag, $this->about_description];

        foreach ($mandatory as $value) {
            if (trim((string) $value) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a unique slug from a landing page title.
     *
     * @param string $title
     * @param int|null $excludeId Landing page ID to exclude from uniqueness check
     * @return string
     */
    public static function generateSlug($title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
