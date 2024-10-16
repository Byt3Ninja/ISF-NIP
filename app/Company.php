<?php

namespace App;

use App\Like;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
//    use Auditable;
    protected $fillable = ['organization_id', 'country_id','state_id', 'city_id', 'name_en', 'name_ar', 'address_en', 'sector', 'address_ar', 'phone', 'fax', 'email', 'website',
        'est_date','idea','problem','solution','team_num', 'stage','raised_fund','investors','founder','employees','facebook', 'twitter',
        'linkedin', 'youtube', 'google_map', 'logo','cycle','step','approved','approved_by','business_type','institutions',
        'development_stage','revenue_generating','currently_fundraising', 'desired_funding_amount','business_model','business_plan','pitch_deck','financial_plan',
        'business_model_description','business_plan_description','pitch_deck_description','financial_plan_description',
        'about_problem','about_market_opportunity','about_competitors','about_monetize','about_challenges','Sustainable_development','documents'
    ];

    // ===========> Attributes <==============
    public function getLogoUrlAttribute()
    {
        $image = $this->logo;
        if (!$image) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return $image;
    }
    public function getBusinessModelUrlAttribute()
    {
        $file = $this->business_model;
        if (!$file) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return $file;
    }
    public function getBusinessPlanUrlAttribute()
    {
        $file = $this->business_plan;
        if (!$file) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return $file;
    }
    public function getPitchDeckUrlAttribute()
    {
        $file = $this->pitch_deck;
        if (!$file) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return $file;
    }
    public function getFinancialPlanUrlAttribute()
    {
        $file = $this->financial_plan;
        if (!$file) {
            return '/app-assets/images/logo_placeholder.png';
        }
        return $file;
    }

    public function getNameAttribute()
    {
        return $this->{"name_".app()->getLocale()};
    }
    public function getInstitutionsAttribute()
    {
        return json_decode($this->attributes['institutions']);
    }

    // ==========> Relations <==============
    public function user()
    {
        return $this->hasOne(User::class);
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
        return $this->belongsTo(Step::class,'step');
    }
    public function cycledata()
    {
        return $this->belongsTo(Cycle::class,'cycle');
    }
    public function companyWeight($c,$f,$u)
    {
        $weight = Formdata::where('company_id',$c)->where('form_id',$f)->pluck("screening");
        $total = $j = 0;
        if(count($weight)>0) {
            if ($u>0) {
                foreach ($weight as $item) {if (isset($item[$u])) {
                    $total = $item[$u] + $total;
                }}
                $weight = $total/count($weight)*10;
            } else {
                foreach ($weight as $item) {
                    if($item){
                    $j=count($item);
                    foreach($item as $key => $value){$total = $total+ $value;}}}
                $weight=$total/count($weight)*10;
                if ($j>1){$weight = $weight/$j;}
            }
        } else {$weight=0;}
        return ((int)$weight);
    }
    public function companyJudge($c,$f,$u)
    {
        $weight = Formdata::where('company_id',$c)->where('form_id',$f)->pluck("screening");
        $all = count($weight);$count = 0;
        if($weight)
        {foreach ($weight as $item)
        if(isset($item[$u])){
        {if($item[$u]){$count++;}}}}
        if($all == 0){return (0);}
        return ((int)$count/$all*100);
    }
    public function judges($c,$f)
    {
        $list = Formdata::where('company_id',$c)->where('form_id',$f)->pluck("screening")->first();
        $judges = [];
        if ($list) {
            foreach($list as $key => $value ){$judges[] = $key ;}
        }
        return $judges;
    }
    public function finals($s)
    {
        $list = Finalscreening::where('company_id',$this->id)->where('step_id',$s)->pluck("judges")->first();
        $judges = [];
        if ($list) {
            foreach($list as $key => $value ){$judges[] = $value ;}
        }
        return $judges;
    }
    public function fsession($s)
    {
        return (Finalscreening::where('company_id',$this->id)->where('step_id',$s)->first());
    }

    public function finalScreening()
    {
        return $this->hasMany(Finalscreening::class,'company_id');
    }

    public function finalData(){
        return $this->hasMany(FinalData::class,'company_id');
    }
    public function formData(){
        return $this->hasMany(Formdata::class,'company_id');
    }

    public function fundingRounds(){
        return $this->hasMany(FundingRound::class,'company_id');
    }

    public function sustainableIcons()
    {
        return $this->hasMany(SustainableIcon::class); // تأكد من أن العلاقة صحيحة
    }



}
