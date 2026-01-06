<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialProjectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyId = 1;
        // Try to find a user for this company, or default to 1
        $userId = DB::table('users')->where('company_id', $companyId)->value('id') ?? 1;
        $valuationDate = Carbon::now()->format('Y-m-d');
        $currentYear = Carbon::now()->year;

        $years = [
            $currentYear - 1, // Past
            $currentYear,     // Year 1
            $currentYear + 1, // Year 2
            $currentYear + 2  // Year 3
        ];

        $data = [];

        foreach ($years as $index => $year) {
            // Generate some realistic-looking growing numbers
            $growthFactor = 1 + ($index * 0.2); // 20% growth per year

            $revenue = 1000000 * $growthFactor;
            $cogs = $revenue * 0.4; // 40% COGS
            $grossProfit = $revenue - $cogs;
            
            $salaries = 200000 * $growthFactor;
            $opex = 100000 * $growthFactor;
            $ebitda = $grossProfit - $salaries - $opex;
            
            $da = 50000; // Depreciation
            $ebit = $ebitda - $da;
            
            $interest = 20000;
            $taxes = $ebit > 0 ? $ebit * 0.2 : 0; // 20% tax
            $netProfit = $ebit - $interest - $taxes;
            
            $receivables = $revenue * 0.1;
            $inventory = $cogs * 0.1;
            $payables = $cogs * 0.08;
            $workingCapital = $receivables + $inventory - $payables;
            
            // Simple change in WC calculation (assuming previous year was slightly less)
            $prevWC = $workingCapital / 1.1; 
            $changeInWC = $workingCapital - $prevWC;
            
            $capex = 50000 * $growthFactor;
            $fcf = $netProfit + $da - $changeInWC - $capex;
            
            $debt = 500000 - ($index * 50000); // Paying down debt

            $data[] = [
                'company_id' => $companyId,
                'user_id' => $userId,
                'year' => $year,
                'date_validate' => $valuationDate,
                'valuation_date' => $valuationDate,
                'revenues' => (string)$revenue,
                'cogs' => (string)$cogs,
                'salaries' => (string)$salaries,
                'other_op_expenses' => (string)$opex,
                'ebitda' => (string)$ebitda,
                'depreciation_amortization' => (string)$da,
                'ebit' => (string)$ebit,
                'interest' => (string)$interest,
                'taxes' => (string)$taxes,
                'net_profit' => (string)$netProfit,
                'receivables' => (string)$receivables,
                'inventory' => (string)$inventory,
                'payables' => (string)$payables,
                'working_capital' => (string)$workingCapital,
                'change_in_wc' => (string)$changeInWC,
                'capital_expenditures' => (string)$capex,
                'debt_end_year' => (string)$debt,
                'debt_change' => '-50000',
                'free_cash_flow' => (string)$fcf,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('financial_projections')->insert($data);
    }
}
