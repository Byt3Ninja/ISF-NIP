<?php

namespace App\Http\Resources;

use App\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $sdgMap = [
            'no_poverty' => ['en' => 'No Poverty', 'ar' => 'القضاء على الفقر', 'id' => 1],
            'zero_hunger' => ['en' => 'Zero Hunger', 'ar' => 'القضاء التام على الجوع', 'id' => 2],
            'good_health' => ['en' => 'Good Health and Well-being', 'ar' => 'الصحة الجيدة والرفاه', 'id' => 3],
            'quality_education' => ['en' => 'Quality Education', 'ar' => 'جودة التعليم', 'id' => 4],
            'gender_equality' => ['en' => 'Gender Equality', 'ar' => 'المساواة بين الجنسين', 'id' => 5],
            'clean_water' => ['en' => 'Clean Water and Sanitation', 'ar' => 'المياه النظيفة والنظافة الصحية', 'id' => 6],
        ];

      $sdgs = [];
       if (is_array(json_decode($this->Sustainable_development))) {
          foreach (json_decode($this->Sustainable_development) as $sdgKey) {
             if (isset($sdgMap[$sdgKey])) {
                $sdgs[] = $sdgKey;
              }
          }
     }

     $categoryMap = [
        'Food & Beverage' => 'categories.food_beverage',
        'Technology' => 'categories.technology',
        'App / Website' => 'categories.app_website',
        'Housewares / Home Design' => 'categories.housewares_home_design',
        'Health / Wellness / Nutrition' => 'categories.health_wellness_nutrition',
        'Fitness' => 'categories.fitness',
        'Sports' => 'categories.sports',
        'Beauty' => 'categories.beauty',
        'Clothing / Fashion' => 'categories.clothing_fashion',
        'Toys / Games' => 'categories.toys_games',
        'Children' => 'categories.children',
        'Entertainment / Experiential' => 'categories.entertainment_experiential',
        'Pets' => 'categories.pets',
        'Music' => 'categories.music',
        'Holiday' => 'categories.holiday',
        'Other' => 'categories.other',
     ];

        return [
            'id' => $this->id,
            'project_code' => $this->project_code,
            'name' => $this->{'name_'.$locale},
            'logo' => $this->logo ? url($this->logo) : null,
            'sector' => is_numeric($this->sector) && $this->sectoR ? [
                 'id' => $this->sectoR->id,
                 'name' => $this->sectoR->{"name_$locale"},
                ] : ['id' => null, 'name' => $this->sector],
            'categories' =>  collect(
                is_string($this->business_categories)
                ? json_decode($this->business_categories, true)
                : $this->business_categories
                )->map(function ($value) {
                 $categoryMap = [
                    'food_beverage' => 'Food & Beverage',
                    'technology' => 'Technology',
                    'app_website' => 'App / Website',
                    'housewares_home_design' => 'Housewares / Home Design',
                    'health_wellness_nutrition' => 'Health / Wellness / Nutrition',
                    'fitness' => 'Fitness',
                    'sports' => 'Sports',
                    'beauty' => 'Beauty',
                    'clothing_fashion' => 'Clothing / Fashion',
                    'toys_games' => 'Toys / Games',
                    'children' => 'Children',
                    'entertainment_experiential' => 'Entertainment / Experiential',
                    'pets' => 'Pets',
                    'music' => 'Music',
                    'holiday' => 'Holiday',
                    'other' => 'Other',
                ];

             $reverseMap = array_flip($categoryMap);

            return $reverseMap[$value] ?? null;
            })->filter()->values(),
            'state' => $this->state ? [
                'id' => $this->state->id,
                'name' => $this->state->{"name_$locale"},
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->{"name_$locale"},
                ] : null,
            'country' =>  $this->country ? [
                 'id' => $this->country->id,
                 'name' => $this->country->{"name_$locale"},
                ] : null,
            'address' => $this->{"address_{$locale}"},
            'establishmentDate' => $this->est_date,
            'yearsOfOperations' => $this->years_of_operations,
            'productOrService' => $this->product_or_service,
            'infoCompany' => $this->info_company,
            'aboutProblem' => $this->about_problem,
            'aboutMarketOpportunity' => $this->about_market_opportunity,
            'aboutCompetitors' => $this->about_competitors,
            'aboutMonetization' => $this->about_monetize,
            'aboutChallenges' => $this->about_challenges,
            'idea'   => $this->idea,
            'problem' => $this->problem,
            'solution'=> $this->solution,
            'teamNum' => $this->team_num,
            'workingTeamSummary' => $this->working_team_summary,
            'stage' => [
                'id' => $this->stagE?->id,
                'name' => $this->stagE?->{"name_$locale"},
            ],
            'fundingStage' => $this->development_stage,
            'revenueGenerating' => (bool) $this->revenue_generating,
            'currentlyFundraising' => (bool) $this->currently_fundraising,
            'desiredFundingAmount' => $this->desired_funding_amount,
            'raisedFund' => $this->raised_fund,
            'equityOffered' => $this->equity_offered,
            'currency' => $this->Currency,
            'preMoneyValuation' => $this->Pre_money_Valuation,
            'targetedMarket' => collect(json_decode($this->targeted_market, true))
                ->filter()
                ->map(function ($countryId) use ($locale) {
                 $country = Country::find($countryId);
                return $country ? ['id' => $country->id, 'title' => $country->{"name_$locale"}] : null;
            })
            ->filter()
            ->values(),
            'businessType' => $this->business_type,
            'financialPlan' => $this->financial_plan_description,
            // Confidential files: links to the authorized API file route (bearer token), never a public URL.
            'financialPlanAttachment' => $this->resource->protectedFileUrl('financial_plan', true),
            'achievedMilestones' => $this->milestones
                ->where('plan', 'Achieved_Milestones')
                ->where('private', false)
                 ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'description' => $m->description,
                ])->values(),
            'plannedMilestones' => $this->milestones
                ->where('plan', 'Planned_Milestones')
                ->where('private', false)
                ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'description' => $m->description,
                ])->values(),
            'archivedMilestones' => $this->milestones
                ->where('plan', 'Archived_Milestones')
                ->where('private', false)
                ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'description' => $m->description,
                ])->values(),
            'industries' => json_decode($this->industries, true),
            'founders' => $this->teamMembers
                ->where('TeamMember', 'Founder')
                ->map(fn($f) => ['id' => $f->id, 'name' => $f->Name, 'role' => $f->TeamMember, 'email' => $f->Email,'linkedin' => $f->LinkedIn,])
                ->values(),
            'shareholders' => $this->teamMembers
                ->whereNotNull('PresentagShereholder')
                ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->Name,
                    'role' => $m->RoleShereholder,
                    'ownership' => $m->PresentagShereholder,
                ])->values(),
            'investors' => $this->teamMembers
                ->whereNotNull('investorType')
                ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->Name,
                    'investmentType' => $m->investorType,
                ])->values(),
            'email' => $this->email,
            'phone' => $this->phone,
            'countryCode' => $this->country_code,
            'whatsapp' => $this->whatsapp,
            'countryCodeWA' => $this->country_code_w_a,
            'website' => $this->website,
            'googleMapUrl' => $this->google_map,
            'linkedin' => $this->linkedin,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'youtube' => $this->youtube,
            'instagram' => $this->instagram,
            'tiktok' => $this->tiktok,
            'productPhoto' => $this->product_photo,
            'pitchDeckSummary' => $this->pitch_deck_summary,
            'businessModel' => $this->business_model,
            'businessModelDescription' => $this->business_model_description,
            'businessPlan' => $this->resource->protectedFileUrl('business_plan', true),
            'sustainableDevelopment' => $sdgs,
            'pitchDeck' => $this->resource->protectedFileUrl('pitch_deck', true),
            'pitchDeckVideo' => $this->pitch_deck_video,
            'documents' => collect($this->resource->protectedDocuments(true))->pluck('url')->filter()->values(),
             'createdTax' => (bool) $this->CreatedTax,
             'commercialRegister' => $this->resource->protectedFileUrl('Commercial_Register', true),
             'taxCard' => $this->resource->protectedFileUrl('Tax_Card', true),
             'countryRegister' => $this->Country_Register_id
            ? [
              'id' => $this->Country_Register_id,
              'name' => optional(\App\Country::find($this->Country_Register_id))->{"name_$locale"},
            ]
             : null,       
        ];
    }
}
