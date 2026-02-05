<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CompaniesExport;
use Carbon\Carbon;

class CompanyExportController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->middleware('auth');
        $this->companyService = $companyService;
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'xlsx');

        // Reuse the same filters accepted by the companies listing
        $filters = [];
        foreach (['search','orgnization','programFilter','stage','sectors'] as $key) {
            if ($request->has($key)) {
                $filters[$key] = $request->get($key);
            }
        }

        // The service expects programFilter as programId
        if (isset($filters['programFilter'])) {
            $filters['programId'] = $filters['programFilter'];
            unset($filters['programFilter']);
        }

        $companies = $this->companyService->getCompanies($filters, isApi: false);

        if (empty($companies) || (is_countable($companies) && count($companies) === 0)) {
            return redirect()->back()->with(['message2' => 'No_Startups_To_Export']);
        }

        // Prepare rows
        $rows = collect( collect( $companies )['data'] )->map(function($c){
            $company = (object) $c;
            return $company;
        })->map(function($c){
            $program = optional( (object) $c->cycle_relation );
            $org = optional( (object) $program->organization );
            $sector = optional( (object) $c->secto_r)?->{'name_' . app()->getLocale()} ?? '';
            $stage = optional( (object)  $c->stag_e)?->{'name_' . app()->getLocale()} ?? '';
            $programs = $program ? ($program->{'title'} ?? ($program->{'title_ar'} ?? '')) : '';
            return [
                'Company Name' => $c->{'name_' . app()->getLocale()} ?? $c->name_en ?? '',
                'Logo' => $c->logo ? url($c->logo) : '',
                'Sector' => $sector,
                'Stage' => $stage,
                'Organization' => $org?->{'name_' . app()->getLocale()} ?? '',
                'Program(s)' => $programs,
            ];
        })->toArray();

        $timestamp = Carbon::now()->format('d-m-Y_H-i'); // dd-mm-yyyy_HH-MM
        $filename = "startups_export_{$timestamp}";

        $export = new CompaniesExport(collect($rows));
        if ($format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }
        return Excel::download($export, $filename . '.xlsx');
    }
}
