<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganizationLandingPage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization_landing_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'landing_enabled',
        // How It Works
        'how_it_works_enabled',
        'how_it_works_title',
        'how_it_works_subtitle',
        'how_it_works_description',
        'process_steps',
        // Achievements
        'achievements_enabled',
        'achievements_title',
        'achievements_subtitle',
        'achievements_description',
        'metrics',
        // Media & Recognition
        'media_enabled',
        'media_title',
        'media_subtitle',
        'media_description',
        'articles',
        // Partners
        'partners_enabled',
        'partners_title',
        'partners_subtitle',
        'partners_description',
        'partners_list',
        // Theme
        'landing_theme',
        // Section Backgrounds
        'hero_bg_type',
        'hero_bg_color',
        'hero_bg_gradient_start',
        'hero_bg_gradient_end',
        'how_it_works_bg_type',
        'how_it_works_bg_color',
        'how_it_works_bg_gradient_start',
        'how_it_works_bg_gradient_end',
        'achievements_bg_type',
        'achievements_bg_color',
        'achievements_bg_gradient_start',
        'achievements_bg_gradient_end',
        'media_bg_type',
        'media_bg_color',
        'media_bg_gradient_start',
        'media_bg_gradient_end',
        'partners_bg_type',
        'partners_bg_color',
        'partners_bg_gradient_start',
        'partners_bg_gradient_end',
        // Programs
        'programs_enabled',
        'programs_title',
        'programs_subtitle',
        'programs_description',
        'programs_order_by',
        'programs_bg_type',
        'programs_bg_color',
        'programs_bg_gradient_start',
        'programs_bg_gradient_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'landing_enabled' => 'boolean',
        'how_it_works_enabled' => 'boolean',
        'achievements_enabled' => 'boolean',
        'media_enabled' => 'boolean',
        'partners_enabled' => 'boolean',
        'programs_enabled' => 'boolean',
        'process_steps' => 'array',
        'metrics' => 'array',
        'articles' => 'array',
        'partners_list' => 'array',
    ];

    /**
     * Get the organization that owns the landing page.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get article image URL.
     *
     * @param string|null $filename
     * @return string|null
     */
    public function getArticleImageUrl($filename)
    {
        if (!$filename) {
            return null;
        }

        return route('organization.landing.asset', [
            'organizationId' => $this->organization_id,
            'type' => 'articles',
            'filename' => $filename
        ]);
    }

    /**
     * Get partner logo URL.
     *
     * @param string|null $filename
     * @return string|null
     */
    public function getPartnerLogoUrl($filename)
    {
        if (!$filename) {
            return null;
        }

        return route('organization.landing.asset', [
            'organizationId' => $this->organization_id,
            'type' => 'partners',
            'filename' => $filename
        ]);
    }

    /**
     * Get all articles with full image URLs.
     *
     * @return array
     */
    public function getArticlesWithUrls()
    {
        $articles = $this->articles ?? [];

        return array_map(function ($article) {
            $article['image_url'] = $this->getArticleImageUrl($article['image'] ?? null);
            return $article;
        }, $articles);
    }

    /**
     * Get all partners with full logo URLs.
     *
     * @return array
     */
    public function getPartnersWithUrls()
    {
        $partners = $this->partners_list ?? [];

        return array_map(function ($partner) {
            $partner['logo_url'] = $this->getPartnerLogoUrl($partner['logo'] ?? null);
            return $partner;
        }, $partners);
    }
}
