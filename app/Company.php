<?php

namespace App;

use App\User;
use App\Like;
use App\Step;
use App\Traits\Auditable;
use App\Traits\HasFileCleanup;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\ProtectedFileSet;
use App\Services\ProtectedFiles\ProtectedFiles;
use App\Services\ProtectedFiles\LegacyReferences;
use App\Services\ProtectedFiles\FileAccess;

class Company extends Model
{
    use HasFactory, HasFileCleanup;
    // use Auditable;
    public static $module = 'Company';
    
    /**
     * Get the user that owns the company.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'company_id');
    }

    public function resolveUser()
    {
        return $this->user ? : $this->users->first();
    }

    protected $fillable = [
        'user_id',
        'country_id',
        'state_id',
        'city_id',
        'name_en',
        'name_ar',
        'address_en',
        'sector',
        'Funding_Target',
        'info_company',
        'business_categories',
        'business_phase',
        'address_ar',
        'years_of_operations',
        'product_or_service',
        'occupation',
        'annual_revenue',
        'phone',
        'fax',
        'email',
        'product_photo',
        'product_video',
        'website',
        'est_date',
        'idea',
        'problem',
        'solution',
        'team_num',
        'stage',
        'raised_fund',
        'equity_offered',
        'investors',
        'founder',
        'employees',
        'collaborators',
        'hear_about_us',
        'facebook',
        'twitter',
        'linkedin',
        'youtube',
        'google_map',
        'instagram',
        'tiktok',
        'logo',
        'cycle',
        'step',
        'approved',
        'approved_by',
        'business_type',
        'institutions',
        'development_stage',
        'revenue_generating',
        'generate_revenue',
        'average_annual_revenues',
        'currently_fundraising',
        'desired_funding_amount',
        'business_model',
        'business_plan',
        'pitch_deck',
        'financial_plan',
        'business_model_description',
        'mvp_prototype_link',
        'business_plan_description',
        'country_code',
        'pitch_deck_description',
        'financial_plan_description',
        'about_problem',
        'about_market_opportunity',
        'about_competitors',
        'about_monetize',
        'about_challenges',
        'Sustainable_development',
        'documents',
        'CreatedTax',
        'Commercial_Register',
        'Tax_Card',
        'Country_Register_id',
        'targeted_market',
        'industries',
        'technologies_used',
        'previous_incubation',
        'previous_incubator',
        'company_registration_type',
        'products_services_description',
        'achievements',
        'mission',
        'why_this_startup',
        'startup_expertise',
        'total_employees',
        'male_employees',
        'female_employees',
    ];


    // Confidential files (PROTECTED_FILE_FIELDS) are not listed here: they are retired
    // through their protected_files records when the company is deleted (see booted()),
    // never unlinked by a path taken from a column. `business_model` is text, not a file.
    protected array $fileFields = [
        'logo' => 'companies',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Company $company) {
            app(ProtectedFiles::class)->retireWhere(['company_id' => $company->id]);
        });

        static::created(function (Company $company) {
            $company->assignProjectCode();
        });

        // The project number is given once. An edit that carries another value keeps the original.
        static::updating(function (Company $company) {
            if ($company->getOriginal('project_code') && $company->isDirty('project_code')) {
                $company->project_code = $company->getOriginal('project_code');
            }
        });

        // Keep the enrollment history in step with the cycle / step pointer. `saved` runs
        // before the original values are replaced, so the cycle the pointer held is still known.
        static::saved(function (Company $company) {
            if ($company->isDirty(['cycle', 'step'])) {
                CompanyEnrollment::syncFromPointer($company, $company->getOriginal('cycle'));
            }
        });
    }

    /**
     * Give this company its project number, unless it already has one. Written with a
     * direct query so it can run inside the `created` hook without a second save.
     */
    public function assignProjectCode(): void
    {
        if ($this->project_code || ! $this->exists) {
            return;
        }

        $code = self::formatProjectCode($this->id, $this->created_at);

        static::query()->toBase()->where('id', $this->id)->update(['project_code' => $code]);

        $this->setAttribute('project_code', $code);
        $this->syncOriginalAttribute('project_code');
    }

    /**
     * PREFIX-YY-NNNNNN: the configured prefix, the two-digit year the company was
     * created, and its id padded to at least six digits. The id makes it unique.
     *
     * @param  \DateTimeInterface|null  $createdAt
     */
    public static function formatProjectCode(int $id, $createdAt = null): string
    {
        return sprintf(
            '%s-%s-%s',
            config('app.project_code_prefix', 'PRJ'),
            ($createdAt ?? now())->format('y'),
            str_pad((string) $id, 6, '0', STR_PAD_LEFT)
        );
    }

    /** Number every company that has no project number yet. Used by the migration. */
    public static function backfillProjectCodes(): void
    {
        DB::update(
            "UPDATE companies
                SET project_code = CONCAT(?, '-', DATE_FORMAT(COALESCE(created_at, NOW()), '%y'), '-',
                    LPAD(id, GREATEST(6, CHAR_LENGTH(id)), '0'))
              WHERE project_code IS NULL",
            [config('app.project_code_prefix', 'PRJ')]
        );
    }

    protected $casts = [
        'business_categories' => 'array',
        'product_photo' => 'array',
        'total_employees' => 'integer',
        'male_employees' => 'integer',
        'female_employees' => 'integer',
    ];
    public static function logo_handler( $img )
    {
        if( !$img ) { return '/app-assets/images/logo_placeholder.png'; }
        return app(\App\Services\FileManagerService::class)->getUrl($img, 'companies');
    }
    // ===========> Attributes <==============
    public function getLogoUrlAttribute()
    {
        return self::logo_handler($this->logo);
    }
    /**
     * Confidential file columns and the protected set each belongs to
     * (`documents` is a JSON list of paths in the pitch set).
     */
    public const PROTECTED_FILE_FIELDS = [
        'Commercial_Register' => ProtectedFileSet::COMPANY_LEGAL,
        'Tax_Card' => ProtectedFileSet::COMPANY_LEGAL,
        'pitch_deck' => ProtectedFileSet::COMPANY_PITCH,
        'financial_plan' => ProtectedFileSet::COMPANY_PITCH,
        'business_plan' => ProtectedFileSet::COMPANY_PITCH,
        'documents' => ProtectedFileSet::COMPANY_PITCH,
    ];

    /**
     * Link to one of this company's confidential files, or null when the column
     * holds no file. Always goes through the authorized file route; the stored
     * value is only used to find this company's own file record.
     */
    public function protectedFileUrl(string $field, bool $api = false): ?string
    {
        return $this->protectedPathUrl($field, $this->getRawOriginal($field), $api);
    }

    /** Same, for one entry of the `documents` list (or any stored path of a known field). */
    public function protectedPathUrl(string $field, $path, bool $api = false): ?string
    {
        return app(ProtectedFiles::class)->urlFor(self::PROTECTED_FILE_FIELDS[$field], $path, ['company_id' => $this->id], $api);
    }

    /**
     * The link to show the CURRENT viewer, or null when there is no file or the viewer
     * would be refused by the file route. Views use this so that a link is never offered
     * to someone who cannot open it; it asks the same FileAccess the route asks.
     */
    public function viewerFileUrl(string $field): ?string
    {
        if (!app(FileAccess::class)->canViewCompanySet(auth()->user(), $this->id, self::PROTECTED_FILE_FIELDS[$field])) {
            return null;
        }

        return $this->protectedFileUrl($field);
    }

    /** protectedDocuments() for the current viewer (empty when they may not open them). */
    public function viewerDocuments(): array
    {
        return app(FileAccess::class)->canViewCompanySet(auth()->user(), $this->id, ProtectedFileSet::COMPANY_PITCH)
            ? $this->protectedDocuments()
            : [];
    }

    /** True when the column refers to an actual file (not empty, "null" or free text). */
    public function hasProtectedFile(string $field): bool
    {
        return app(ProtectedFiles::class)->normalize(self::PROTECTED_FILE_FIELDS[$field], $this->getRawOriginal($field)) !== null;
    }

    /** The supporting documents as [['path' => stored value, 'name' => display name, 'url' => link|null], …]. */
    public function protectedDocuments(bool $api = false): array
    {
        $files = app(ProtectedFiles::class);
        $list = [];
        foreach (app(LegacyReferences::class)->entries($this->getRawOriginal('documents')) as $path) {
            if (!is_string($path)) {
                continue;
            }
            $record = $files->find(ProtectedFileSet::COMPANY_PITCH, $path, ['company_id' => $this->id]);
            $list[] = [
                'path' => $path,
                'name' => $record->original_name ?? basename($path),
                'url' => $files->urlFor(ProtectedFileSet::COMPANY_PITCH, $path, ['company_id' => $this->id], $api),
            ];
        }

        return $list;
    }

    public function getNameAttribute()
    {
        return $this->{"name_" . app()->getLocale()};
    }
    public function getInstitutionsAttribute()
    {
        return json_decode($this->attributes['institutions']);
    }

    // ==========> Relations <==============

    public function sectoR()
    {
        return $this->belongsTo(Sector::class, 'sector');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function state()
    {
        return $this->belongsTo(State::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function stepdata()
    {
        return $this->belongsTo(Step::class, 'step');
    }
    public function cycledata()
    {
        return $this->belongsTo(Cycle::class, 'cycle');
    }

    public function stagE()
    {
        return $this->belongsTo(Stage::class, 'stage');
    }

    public function companyWeight($c, $f, $u)
    {
        $weight = Formdata::where('company_id', $c)->where('form_id', $f)->pluck("screening");
        $total = $j = 0;
        if (count($weight) > 0) {
            if ($u > 0) {
                foreach ($weight as $item) {
                    if (isset($item[$u])) {
                        $total = $item[$u] + $total;
                    }
                }
                $weight = $total / count($weight) * 10;
            } else {
                foreach ($weight as $item) {
                    if ($item) {
                        $j = count($item);
                        foreach ($item as $key => $value) {
                            $total = $total + $value;
                        }
                    }
                }
                $weight = $total / count($weight) * 10;
                if ($j > 1) {
                    $weight = $weight / $j;
                }
            }
        } else {
            $weight = 0;
        }
        return ((int)$weight);
    }
    public function companyJudge($c, $f, $u)
    {
        $weight = Formdata::where('company_id', $c)->where('form_id', $f)->pluck("screening");
        $all = count($weight);
        $count = 0;
        if ($weight) {
            foreach ($weight as $item)
                if (isset($item[$u])) { {
                        if ($item[$u]) {
                            $count++;
                        }
                    }
                }
        }
        if ($all == 0) {
            return (0);
        }
        return ((int)$count / $all * 100);
    }
    public function judges($c, $f)
    {
        $list = Formdata::where('company_id', $c)->where('form_id', $f)->pluck("screening")->first();
        $judges = [];
        if ($list) {
            foreach ($list as $key => $value) {
                $judges[] = $key;
            }
        }
        return $judges;
    }
    public function finals($s)
    {
        $list = Finalscreening::where('company_id', $this->id)->where('step_id', $s)->pluck("judges")->first();
        $judges = [];
        if ($list) {
            foreach ($list as $key => $value) {
                $judges[] = $value;
            }
        }
        return $judges;
    }
    public function fsession($s)
    {
        return (Finalscreening::where('company_id', $this->id)->where('step_id', $s)->first());
    }

    public function finalScreening()
    {
        return $this->hasMany(Finalscreening::class, 'company_id');
    }

    public function finalData()
    {
        return $this->hasMany(FinalData::class, 'company_id');
    }
    public function formData()
    {
        return $this->hasMany(Formdata::class, 'company_id');
    }

    public function fundingRounds()
    {
        return $this->hasMany(FundingRound::class, 'company_id');
    }

    public function sustainableIcons()
    {
        return $this->hasMany(SustainableIcon::class); // تأكد من أن العلاقة صحيحة
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle', 'id');
    }
    public function cycleRelation()
    {
        return $this->belongsTo(Cycle::class, 'cycle');
    }

    /** Every time this company entered a cycle, current and past. See CompanyEnrollment. */
    public function enrollments()
    {
        return $this->hasMany(CompanyEnrollment::class, 'company_id');
    }

    public function activeEnrollments()
    {
        return $this->enrollments()->active();
    }

    public static function static_categories()
    {
        $industries = StaticCategory::all();
        $categories = [];

        foreach ($industries as $industry) {
            $categories[ $industry->{'name_'. App::getLocale() } ] = (bool) $industry->is_other;
        }
        
        return $categories;

        // return [
        //     __('admin.categories.food_beverage') => false,
        //     __('admin.categories.technology') => false,
        //     __('admin.categories.app_website') => false,
        //     __('admin.categories.housewares_home_design') => false,
        //     __('admin.categories.health_wellness_nutrition') => false,
        //     __('admin.categories.fitness') => false,
        //     __('admin.categories.sports') => false,
        //     __('admin.categories.beauty') => false,
        //     __('admin.categories.clothing_fashion') => false,
        //     __('admin.categories.toys_games') => false,
        //     __('admin.categories.children') => false,
        //     __('admin.categories.entertainment_experiential') => false,
        //     __('admin.categories.pets') => false,
        //     __('admin.categories.music') => false,
        //     __('admin.categories.holiday') => false,
        //     __('admin.categories.other') => true,
        // ];
    }

    public static function business_phase()
    {
        return [
            '1' => 'Idea',
            '2' => 'Research & Development',
            '3' => 'Prototype / Beta Testing',
            '4' => 'Crowdfunding',
            '5' => 'Operating / Shipping',
        ];
    }

    public function teamMembers()
    {
        return $this->hasMany(Team_member::class, 'Company_id');
    }


    public function employees()
    {
        return $this->hasMany(Employee::class, 'company_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'Company_id');
    }

    public static function form( $company )
    {
        $current_step = $company->stepdata;
        if( $current_step->stype == 2 )
        {
            $programSteps = $company->cycledata->steps;
            $previousSteps = $programSteps->where('arr', '<', $current_step->arr)->sortBy('arr')->where('stype', 1)->values();
            $form = $previousSteps->first();
            // $form = Step::where('cycle_id', $current_step?->cycle_id)->where('arr', $current_step?->arr - 1)->first();
        }
        else
        {
            $form = Step::where('cycle_id', $current_step?->cycle_id)->where('arr', $current_step?->arr)->first();
        }

        if( !empty( $form ) )
        {
            $formData = $form?->formData;
            if( !empty( $formData ) ) { $formData = $formData->where('company_id', $company->id)->values(); }
            $formData = $formData->map(function ($item) {
                $field = $item->field()->first();
                $field_type = '';
                if( isset( $field?->options?->type ) ) { $field_type = $field?->options?->type; }
                $item->field_type = $field_type;
                // Link to an uploaded answer, built here once so every view gets the authorized URL.
                $item->file_url = Formdata::fileAnswer($item->answer, $item->company_id)['file_url'] ?? null;
                $item->comment = '';
                return $item;
            });
            $form->formData = $formData;
        }
        return $form;
    }
    public function companyRelations()
    {
        return $this->hasMany(CompanyRelation::class, 'company_id', 'id');
    }

    public function expertises()
    {
        return $this->belongsToMany(
            \App\Expertise::class,
            'company_expertise',
            'company_id',
            'expertise_id'
        )->withTimestamps();
    }
}
