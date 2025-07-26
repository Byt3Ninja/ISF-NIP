<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start' => optional($this->start)->format('Y-m-d'),
            'end' => optional($this->end)->format('Y-m-d'),
            'type' => $this->type,
            'organization' => optional($this->organization)->localized_name,            
            'imgUrl' => $this->img_url ?  Storage::disk('public_uploads')->url($this->img_url) : null,
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
}
