<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialProjection extends Model
{
    use HasFactory;
   
    protected $fillable = [
        'company_id',
        'user_id',
        'valuation_date',
        'year',
        'currency',
        'revenues',
        'cogs',
        'salaries',
        'other_op_expenses',
        'ebitda',
        'depreciation_amortization',
        'ebit',
        'interest',
        'taxes',
        'net_profit',
        'receivables',
        'inventory',
        'payables',
        'working_capital',
        'change_in_wc',
        'capital_expenditures',
        'debt_end_year',
        'debt_change',
        'free_cash_flow',
    ];

    protected $casts = [
        'valuation_date' => 'date',
        'year' => 'integer',
        'revenues' => 'float',
        'cogs' => 'float',
        'salaries' => 'float',
        'other_op_expenses' => 'float',
        'ebitda' => 'float',
        'depreciation_amortization' => 'float',
        'ebit' => 'float',
        'interest' => 'float',
        'taxes' => 'float',
        'net_profit' => 'float',
        'receivables' => 'float',
        'inventory' => 'float',
        'payables' => 'float',
        'working_capital' => 'float',
        'change_in_wc' => 'float',
        'capital_expenditures' => 'float',
        'debt_end_year' => 'float',
        'debt_change' => 'float',
        'free_cash_flow' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class); // FIXED: should be belongsTo, not hasMany
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
