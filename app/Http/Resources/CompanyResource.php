<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
                        'name' => $sdgMap[$sdgKey][$locale] ?? $sdgMap[$sdgKey]['en'],
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'project_code' => $this->project_code,
            'logo' => $this->logo ? url($this->logo) : null,
            'name' => $locale === 'ar' ? $this->name_ar : $this->name_en,
            'sector' => is_numeric($this->sector) ? [
                'id' => $this->sectoR?->id,
                'name' => $this->sectoR?->{'name_' . $locale},
                ] : [
                    'id' => null,
                    'name' => $this->sector,
                ],
            'state' => $this->state ? [
                'id' => $this->state->id,
                'name' => $this->state->{'name_'.$locale},
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->{'name_'.$locale},
            ] : null,
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->{'name_'.$locale},
            ] : null,
            'establishmentDate' => $this->est_date,
            'founders' => $this->teamMembers
                ->where('TeamMember', 'Founder')
                ->map(fn($f) => ['name' => $f->Name, 'email' => $f->Email,'role' => $f->TeamMember,'linkedin' => $f->LinkedIn])
                ->values(),
            'sdgs' => $sdgs,
            'socialLinks' => [
                'website' => $this->website,
                'linkedin' => $this->linkedin,
                'x' => $this->twitter,
                'facebook' => $this->facebook,
                'youtube' => $this->youtube,
                'instagram' => $this->instagram,
                'tiktok' => $this->tiktok,
            ],
            'organization' => $this->relationLoaded('cycleRelation') && $this->cycleRelation?->organization
             ? [
                'id' => $this->cycleRelation->organization->id,
                'name' => $this->cycleRelation->organization->{"name_{$locale}"}
               ]
           : null,
        ];
    }
}
