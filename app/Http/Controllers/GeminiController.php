<?php

namespace App\Http\Controllers;

use App\SwotAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;

class GeminiController extends Controller
{
    public function showSwotAnalysis()
    {
        $user = auth()->user();
        $company = $user->company;
        $swotAnalysis = SwotAnalysis::where('company_id', $company->id)->first();

        return view('admin.swot', compact('swotAnalysis'));
    }

    public function generateSwotAnalysis(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $requiredFields = [
            'info_company',
            'about_problem',
            'about_market_opportunity',
            'about_competitors',
            'about_challenges',
            'country_id',
            'targeted_market',
            'business_type',
            'development_stage',
            'sector'
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (empty($company->$field)) {
                $missingFields[] = ucfirst(str_replace('_', ' ', $field));
            }
        }

        if (!empty($missingFields)) {
            return response()->json([
                'error' => 'Please complete the Following Fields From Your Profile Before Generating the SWOT Analysis:',
                'missing_fields' => $missingFields
            ], 400);
        }


        $targetedMarketIds = json_decode($company->targeted_market, true);
        $targetedMarketNames = DB::table('countries')
            ->whereIn('id', $targetedMarketIds)
            ->pluck('name_en')
            ->toArray();
        $targetedMarket = implode(', ', $targetedMarketNames);

        // Prepare the Prompt for Gemini API
        $prompt = "
        You are an experienced business consultant specializing in strategic analysis. Based on the following inputs, generate a detailed SWOT analysis for a startup. Use the provided data to identify internal strengths and weaknesses, as well as external opportunities and threats.

        Company Name: {$company->name_en}
        1. **Startup Overview:**  {$company->info_company}
        2. **About Problem:** {$company->about_problem}
        3. **About Market Opportunity:** {$company->about_market_opportunity}
        4. **About Competitors:** {$company->about_competitors}
        5. **About Challenges:** {$company->about_challenges}
        6. **Startup HQ:** {$company->country->name_en}
        7. **Targeted Market:** $targetedMarket
        8. **Business Type:** {$company->business_type}
        9. **Development Stage:** {$company->stagE->name_en}
        10. **Sector:** {$company->sectoR->name_en}

        ### Instructions:
        Generate a SWOT analysis with strengths, weaknesses, opportunities, and threats. Provide actionable recommendations for each category to guide the startup's strategic planning.
        **Strengths:**  
        - [List the internal advantages the startup has, such as unique technology, skilled team, or market differentiation.]

        **Weaknesses:**  
        - [List internal limitations, such as funding constraints, operational gaps, or skills shortages.]

        **Opportunities:**  
        - [List external opportunities, such as growing markets, emerging trends, or unmet customer needs.]

        **Threats:**  
        - [List external risks, such as competition, regulatory challenges, or economic changes.]

        ### Output Format:
        ```json
        {
          \"Strengths\": [\"...\"],
          \"Weaknesses\": [\"...\"],
          \"Opportunities\": [\"...\"],
          \"Threats\": [\"...\"],
           \"Recommendations\": {
            \"Strengths\": [\"...\"],
            \"Weaknesses\": [\"...\"],
            \"Opportunities\": [\"...\"],
            \"Threats\": [\"...\"]
          }
        }
        ```
        Keep the analysis concise, practical, and aligned with the provided inputs.
    ";

        $apiKey = env("GEMINI_API_KEY");

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$apiKey}", $data);

        if ($response->successful()) {
            $responseData = $response->json();
            $content = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($content) {
                $cleanContent = trim(preg_replace('/^```json|```$/m', '', $content));
                $swotAnalysis = json_decode($cleanContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $swot =  SwotAnalysis::updateOrCreate(
                        ['company_id' => $company->id],
                        [
                            'strength' => json_encode($swotAnalysis['Strengths'] ?? []),
                            'weakness' => json_encode($swotAnalysis['Weaknesses'] ?? []),
                            'opportunity' => json_encode($swotAnalysis['Opportunities'] ?? []),
                            'threat' => json_encode($swotAnalysis['Threats'] ?? []),
                            'srecommendation' => json_encode($swotAnalysis['Recommendations']['Strengths'] ?? []),
                            'wrecommendation' => json_encode($swotAnalysis['Recommendations']['Weaknesses'] ?? []),
                            'orecommendation' => json_encode($swotAnalysis['Recommendations']['Opportunities'] ?? []),
                            'trecommendation' => json_encode($swotAnalysis['Recommendations']['Threats'] ?? [])
                        ]
                    );

                    return response()->json($swot);
                }
            }
        }
        return redirect()->back()->with('error', 'Failed to generate SWOT analysis. Please try again.');
    }

    public function saveSwotAnalysis(Request $request)
    {
        $companyId = $request->company_id;

        $swotAnalysis = SwotAnalysis::firstOrNew(['company_id' => $companyId]);

        $swotAnalysis->strength = $request->strength;
        $swotAnalysis->weakness = $request->weakness;
        $swotAnalysis->opportunity = $request->opportunity;
        $swotAnalysis->threat = $request->threat;
        $swotAnalysis->srecommendation = $request->srecommendation;
        $swotAnalysis->wrecommendation = $request->wrecommendation;
        $swotAnalysis->orecommendation = $request->orecommendation;
        $swotAnalysis->trecommendation = $request->trecommendation;
        $swotAnalysis->save();

        return redirect()->back()->with('success', __('admin.stored'));
    }

    public function exportToPdf()
    {
        $user = auth()->user();
        $company = $user->company;
        $swotAnalysis = SwotAnalysis::where('company_id', $company->id)->first();
        $pdf = Pdf::loadView('admin.swot-pdf', [
            'strength' => $swotAnalysis->strength,
            'srecommendation' => $swotAnalysis->srecommendation,
            'weakness' => $swotAnalysis->weakness,
            'wrecommendation' => $swotAnalysis->wrecommendation,
            'opportunity' => $swotAnalysis->opportunity,
            'orecommendation' => $swotAnalysis->orecommendation,
            'threat' => $swotAnalysis->threat,
            'trecommendation' => $swotAnalysis->trecommendation
        ]);

        return $pdf->download("SWOT-Analysis.pdf");
    }
}
