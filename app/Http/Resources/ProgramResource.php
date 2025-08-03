<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'programGuidelineFile' => $this->program_guideline_file ? Storage::disk('public_uploads')->url($this->program_guideline_file) : null,
            'financialPolicyPdf' => $this->financial_guideline_file ? Storage::disk('public_uploads')->url($this->financial_guideline_file) : null,
            'legalPolicyPdf' => $this->legal_guideline_file ? Storage::disk('public_uploads')->url($this->legal_guideline_file) : null,
            'delegationPdf' => $this->delegation_file ? Storage::disk('public_uploads')->url($this->delegation_file) : null,
         ];
    }

     protected function getStatus(): string
    {
        $now = Carbon::now();
        $start = $this->start ? Carbon::parse($this->start) : null;
        $end = $this->end ? Carbon::parse($this->end) : null;

        if (!$start || !$end) {
            return 'Unknown';
        }

        if ($now->lt($start)) {
            return 'Upcoming';
        } elseif ($now->between($start, $end)) {
            return 'In Progress';
        } elseif ($now->gt($end)) {
            return 'Completed';
        }

        return 'Unknown';
    }

    protected function getProgress(): int
    {
        $now = Carbon::now();

        $steps = $this->steps;

        if ($steps->count() === 0) {
          return 0;
        }

        $finishedSteps = $steps->filter(function ($step) use ($now) {
             return $step->to && Carbon::parse($step->to)->lte($now);
        });

        $progress = ($finishedSteps->count() / $steps->count()) * 100;

        return round($progress, 2); 
    }
}
