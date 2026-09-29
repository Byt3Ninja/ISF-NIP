<?php

namespace App;

use App\User;
use App\Like;
use App\Step;
use App\Traits\Auditable;
use App\Traits\HasFileCleanup;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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


    protected array $fileFields = [
        'logo' => 'companies',
        'pitch_deck' => 'pitch_deck',
        'business_plan' => 'pitch_deck',
        'financial_plan' => 'pitch_deck',
        'business_model' => 'pitch_deck',
        'documents' => 'pitch_deck',
        'Commercial_Register' => 'document',
        'Tax_Card' => 'document',
    ];

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
    public function getBusinessModelUrlAttribute()
    {
        if (!$this->business_model) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return app(\App\Services\FileManagerService::class)->getUrl($this->business_model, 'pitch_deck');
    }
    public function getBusinessPlanUrlAttribute()
    {
        if (!$this->business_plan) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return app(\App\Services\FileManagerService::class)->getUrl($this->business_plan, 'pitch_deck');
    }
    public function getPitchDeckUrlAttribute()
    {
        if (!$this->pitch_deck) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return app(\App\Services\FileManagerService::class)->getUrl($this->pitch_deck, 'pitch_deck');
    }
    public function getFinancialPlanUrlAttribute()
    {
        if (!$this->financial_plan) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return app(\App\Services\FileManagerService::class)->getUrl($this->financial_plan, 'pitch_deck');
    }
    public function getDocumentsUrlsAttribute()
    {
        $docs = json_decode($this->documents ?? '[]', true);
        if (empty($docs) || !is_array($docs)) {
            return [];
        }
        $fileManager = app(\App\Services\FileManagerService::class);
        return array_map(fn($doc) => $fileManager->getUrl($doc, 'pitch_deck'), $docs);
    }

    public function getCommercialRegisterUrlAttribute()
    {
        if (!$this->Commercial_Register || $this->Commercial_Register === 'null') {
            return null;
        }
        return app(\App\Services\FileManagerService::class)->getUrl($this->Commercial_Register, 'document');
    }

    public function getTaxCardUrlAttribute()
    {
        if (!$this->Tax_Card || $this->Tax_Card === 'null') {
            return null;
        }
        return app(\App\Services\FileManagerService::class)->getUrl($this->Tax_Card, 'document');
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
