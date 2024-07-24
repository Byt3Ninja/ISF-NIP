<?php

namespace App\Http\Requests\Startup;

use Illuminate\Foundation\Http\FormRequest;

class FundingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'funding_stage'=>'required|in:Undisclosed,Friends and Family,SEED,
            Pre-Series A,Series A,Series B,Angel Investment,Series C,Pre-SEED,Series D,Bridge,Convertible Note,Merger and Acquisition (M&A),
            Series E,Series F,Crowdfunding,Private Equity,Pre-Series B',
            'amount'=>'required|numeric',
            'post_money_valuation'=>'required|numeric',
            'date_raised'=>'required|date',
            'investment_vehicle'=>'required|in:Equity,Venture Debt,Conventional Debt,Grant,Non-equity Assistance,Line of Credit',
            'amount_raised'=>'required|numeric',
        ];
    }
}
