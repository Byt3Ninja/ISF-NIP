<?php

namespace App\Http\Controllers;

use App\Services\ProtectedFiles\FileAccess;
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
        if (!empty($company)) {
        $swotAnalysis = SwotAnalysis::where('company_id', $company?->id)->first();
        } else {
        $swotAnalysis = null;
        }
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
                'error' => __('admin.swot_error'),
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
        - List the internal advantages the startup has, such as unique technology, skilled team, or market differentiation.

        **Weaknesses:**  
        - List internal limitations, such as funding constraints, operational gaps, or skills shortages.

        **Opportunities:**  
        - List external opportunities, such as growing markets, emerging trends, or unmet customer needs.

        **Threats:**  
        - List external risks, such as competition, regulatory challenges, or economic changes.

       ### Output Format:
    {
      \"Strengths\": \"<list the internal advantages the startup has>\",
      \"Weaknesses\": \"<list internal limitations>\",
      \"Opportunities\": \"<list external opportunities>\",
      \"Threats\": \"<list external risks>\",
      \"Recommendations\": {
        \"Strengths\": \"<actionable recommendations for strengths>\",
        \"Weaknesses\": \"<actionable recommendations for weaknesses>\",
        \"Opportunities\": \"<actionable recommendations for opportunities>\",
        \"Threats\": \"<actionable recommendations for threats>\"
      }
    }
        When returning the content, please ensure that no quotes appear around the values or the lists. Instead of returning text in double quotes, return plain, formatted text that can be directly read. Do not include any extra symbols such as bullet points or markdown formatting. The goal is to return clean and practical output.

    ";

        $apiKey = env("GEMINI_API_KEY");

        if (!$apiKey) {
            return response()->json(['error' => 'API key not Configured']);
        }

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
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'strength' => 'nullable|string',
            'weakness' => 'nullable|string',
            'opportunity' => 'nullable|string',
            'threat' => 'nullable|string',
            'srecommendation' => 'nullable|string',
            'wrecommendation' => 'nullable|string',
            'orecommendation' => 'nullable|string',
            'trecommendation' => 'nullable|string',
        ]);

        $companyId = $validated['company_id'];
        // H3: only someone who may manage this startup can overwrite its SWOT analysis.
        abort_unless(app(FileAccess::class)->canManageCompany(auth()->user(), (int) $companyId), 403);

        $swotAnalysis = SwotAnalysis::firstOrNew(['company_id' => $companyId]);

        $swotAnalysis->strength = $validated['strength'];
        $swotAnalysis->weakness = $validated['weakness'];
        $swotAnalysis->opportunity = $validated['opportunity'];
        $swotAnalysis->threat = $validated['threat'];
        $swotAnalysis->srecommendation = $validated['srecommendation'];
        $swotAnalysis->wrecommendation = $validated['wrecommendation'];
        $swotAnalysis->orecommendation = $validated['orecommendation'];
        $swotAnalysis->trecommendation = $validated['trecommendation'];
        $swotAnalysis->save();

        return redirect()->back()->with('success', __('admin.Saved'));
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
