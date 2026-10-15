<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $judgeIds = collect($this->steps)
            ->flatMap(function ($step) {
                return json_decode($step->list, true) ?? [];
            })
            ->unique();

        $responsible = $this->mentors->map(function ($mentor) {
            $initials = $mentor->first_name_en . ' ' . $mentor->last_name_en;
            return [
                'initials' => $initials,
                'userId' => $mentor->id,
            ];
        });

        if ($responsible->count() > 3) {
            $extra = $responsible->splice(3);
            $responsible->push([
                'initials' => '+' . $extra->count(),
                'user_id' => null,
            ]);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start' => optional($this->start)->format('Y-m-d'),
            'end' => optional($this->end)->format('Y-m-d'),
            'type' => $this->type,
            'organization' => $this->organization ? [
                 'id' => $this->organization->id,
                'name' => $this->organization->localized_name,
            ] : null,          
            'imgUrl' => $this->img_url ?  Storage::disk('public_uploads')->url($this->img_url) : null,
            'status' => $this->getStatus(),
            'progress' => $this->getProgress(),
            'startupCount' => $this->companies->count(),
            'stepCount' => $this->steps->count(),
            'mentorCount' => $this->mentors->count(),
            'pendingCount' => $judgeIds->count(),
            'responsibleUsers' => $responsible,
            'budgetAllocation' => $this->budget_allocation,
            'currencyType' => $this->currency_type,
            'programGuideline' => $this->program_guideline,
            'financialGuideline' => $this->financial_guideline,
            'legalGuideline' => $this->legal_guideline,
            'allowNonCompany' => (bool) $this->allow_non_company,
            'autoApproveMentorshipRequests' => (bool) $this->auto_approve_mentorship_requests,
            'private' => (bool) $this->private,
            'programGuidelineFile' => $this->resource->guidelineFileUrl('program_guideline_file', true),
            'financialPolicyPdf' => $this->resource->guidelineFileUrl('financial_guideline_file', true),
            'legalPolicyPdf' => $this->resource->guidelineFileUrl('legal_guideline_file', true),
            'delegationPdf' => $this->resource->guidelineFileUrl('delegation_file', true),
         ];
    }

     protected function getStatus(): string
    {
        // GV-9566: start/end are inclusive days in the program's platform zone;
        // comparing now() with their midnights completed it on its last morning.
        $today = TimezoneService::platformToday(TimezoneService::platform($this->organization_id));
        $start = $this->start ? Carbon::parse($this->start)->format('Y-m-d') : null;
        $end = $this->end ? Carbon::parse($this->end)->format('Y-m-d') : null;

        if (!$start || !$end) {
            return 'Unknown';
        }

        if ($today < $start) {
            return 'Upcoming';
        } elseif ($today <= $end) {
            return 'In Progress';
        }

        return 'Completed';
    }

}
