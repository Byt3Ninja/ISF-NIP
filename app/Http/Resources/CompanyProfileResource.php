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
                    $sdgs[] = [
                        'id' => $sdgMap[$sdgKey]['id'],
                        'name' => $sdgMap[$sdgKey][$locale] ?? $sdgMap[$sdgKey]['en'],
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->{'name_'.$locale},
            'logo' => $this->logo ? url($this->logo) : null,
            'sector' => is_numeric($this->sector) ? optional($this->sectoR)->{"name_$locale"} : $this->sector,
            'businessCategories' => is_string($this->business_categories)? json_decode($this->business_categories, true): $this->business_categories,
            'state' => $this->state?->{'name_'.$locale},
            'city' => $this->city?->{'name_'.$locale},
            'country' => $this->country?->{'name_'.$locale},
            'establishmentDate' => $this->est_date,
            'yearsOfOperations' => $this->years_of_operations,
            'infoCompany' => $this->info_company,
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
            'PreMoneyValuation' => $this->Pre_money_Valuation,
            'targetedMarket' => collect(json_decode($this->targeted_market, true))
                ->filter()
                ->map(function ($countryId) use ($locale) {
                 $country = Country::find($countryId);
                return $country ? ['id' => $country->id, 'name' => $country->{"name_$locale"}] : null;
            })
            ->filter()
            ->values(),
            'Industries' => json_decode($this->industries, true),
            'founders' => $this->teamMembers
                ->where('TeamMember', 'Founder')
                ->map(fn($f) => ['name' => $f->Name, 'role' => $f->TeamMember])
                ->values(),
            'website' => $this->website,
            'linkedin' => $this->linkedin,
            'twitter' => $this->twitter,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address_en,
            'pitchDeckSummary' => $this->pitch_deck_summary,
            'businessModelDescription' => $this->business_model_description,
            'SustainableDevelopment' => $sdgs,
        ];
    }
}
