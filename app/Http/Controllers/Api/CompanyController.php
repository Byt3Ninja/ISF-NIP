<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function search(Request $request)
    {
        $query = Company::query();
        
        // Filter by program if program_id is provided
        if ($request->program_id) {
            $query->where('cycle', $request->program_id);
        } else {
            // If no program selected, get companies that are in any program
            $query->whereNotNull('cycle');
        }

        // Search by name if search term is provided
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name_en', 'like', '%' . $request->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $request->search . '%');
            });
        }

        $companies = $query->paginate(10);

        return response()->json([
            'data' => $companies->items(),
            'meta' => [
                'total' => $companies->total(),
                'per_page' => $companies->perPage(),
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
            ]
        ]);
    }
}