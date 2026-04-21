<?php

namespace App\Services\Importer;

use App\Country;
use App\Cycle;
use App\Organization;
use App\Repositories\CompanyRepository;
use App\Services\CompanyService;
use App\Services\HackathonEnrollmentService;
use App\State;
use App\Sector;
use Carbon\Carbon;

/**
 * Single-row write path. Wrapped by the orchestrator in its own DB::transaction.
 * Delegates all persistence to canonical services.
 */
class RowImporter
{
    private CompanyService $companyService;
    private CompanyRepository $companyRepo;
    private FinancialProjectionService $finService;
    private UserProvisioner $userProv;
    private HackathonEnrollmentService $hackService;

    public function __construct(
        CompanyService $companyService,
        CompanyRepository $companyRepo,
        FinancialProjectionService $finService,
        UserProvisioner $userProv,
        HackathonEnrollmentService $hackService
    ) {
        $this->companyService = $companyService;
        $this->companyRepo    = $companyRepo;
        $this->finService     = $finService;
        $this->userProv       = $userProv;
        $this->hackService    = $hackService;
    }

    /**
     * @param array<string,string>  $row row resolved as field_key => value
     * @param int                   $rowNumber 1-based CSV row number (incl. header offset = +2)
     * @param Cycle                 $cycle pre-created by orchestrator
     * @param Organization          $org
     * @param array<int,array>      $acceptedWarnings rows to skip for conflict reasons
     * @return array
     */
    public function import(array $row, int $rowNumber, Cycle $cycle, Organization $org, array $acceptedWarnings): array
    {
        $skipReason = $this->skipReasonFromWarnings($rowNumber, $acceptedWarnings);
        if ($skipReason !== null) {
            return ['imported' => false, 'reason' => $skipReason];
        }

        // --- lookups ---
        $countryName = trim($row['country'] ?? '');
        $country = $countryName !== ''
            ? Country::firstOrCreate(['name_en' => $countryName], ['name_ar' => $countryName])
            : null;
        $countryId = $country?->id;

        $stateName = trim($row['governorate'] ?? '');
        $stateId = null;
        if ($stateName !== '' && $countryId) {
            $st = State::firstOrCreate(
                ['country_id' => $countryId, 'name_en' => $stateName],
                ['name_ar' => $stateName]
            );
            $stateId = $st->id;
        }

        $sectorName = trim($row['sector'] ?? '');
        $sectorId = null;
        if ($sectorName !== '') {
            $sec = Sector::firstOrCreate(['name_en' => $sectorName], ['name_ar' => $sectorName]);
            $sectorId = $sec->id;
        }

        // Target markets -> JSON of country ids
        $targeted = null;
        if (!empty(trim($row['target_markets'] ?? ''))) {
            $ids = [];
            foreach (array_map('trim', explode(',', $row['target_markets'])) as $m) {
                if ($m === '') continue;
                $c = Country::firstOrCreate(['name_en' => $m], ['name_ar' => $m]);
                $ids[] = (string) $c->id;
            }
            if (!empty($ids)) {
                $targeted = json_encode($ids);
            }
        }

        // --- company ---
        $companyData = array_filter([
            'name_en'                => trim($row['startup_name'] ?? ''),
            'name_ar'                => trim($row['startup_name'] ?? ''),
            'country_id'             => $countryId,
            'state_id'               => $stateId,
            'sector'                 => $sectorId,
            'address_en'             => $row['address'] ?? null,
            'address_ar'             => $row['address'] ?? null,
            'targeted_market'        => $targeted,
            'est_date'               => $this->parseMdy($row['establishment_date'] ?? null),
            'email'                  => $row['company_email'] ?? null,
            'website'                => $row['company_website'] ?? null,
            'facebook'               => $row['company_facebook'] ?? null,
            'logo'                   => $row['company_logo'] ?? null,
            'mission'                => $row['mission'] ?? null,
            'mvp_prototype_link'     => $row['mvp_link'] ?? null,
            'total_employees'        => $this->intOrNull($row['total_employees']  ?? null),
            'male_employees'         => $this->intOrNull($row['male_employees']   ?? null),
            'female_employees'       => $this->intOrNull($row['female_employees'] ?? null),
            'cycle'                  => $cycle->id,
        ], fn($v) => $v !== null && $v !== '');

        $company = $this->companyService->upsertFromImport($companyData);

        // --- founder user + team_member ---
        $founderEmail = trim($row['founder_email'] ?? '');
        if ($founderEmail === '') {
            throw new \RuntimeException('founder_email missing after validation');
        }

        $prov = $this->userProv->upsertFromImport([
            'email'        => $founderEmail,
            'first_name'   => $row['founder_first_name'] ?? null,
            'last_name'    => $row['founder_last_name']  ?? null,
            'gender'       => $row['founder_gender']     ?? null,
            'phone'        => $row['founder_phone']      ?? null,
            'country_code' => $row['founder_country_code'] ?? null,
            'country_id'   => $countryId,
            'state_id'     => $stateId,
        ], $company->id);

        $this->companyRepo->upsertFounderFromImport($company, [
            'first_name'         => $row['founder_first_name'] ?? null,
            'last_name'          => $row['founder_last_name']  ?? null,
            'email'              => $prov['user']->email,
            'phone'              => $row['founder_phone'] ?? null,
            'join_date'          => $this->parseMdy($row['founder_join_date'] ?? null),
            'working_commitment' => $row['founder_working_commitment'] ?? null,
            'national_id'        => $row['founder_national_id'] ?? null,
            'educational_status' => $row['founder_educational_status'] ?? null,
            'date_of_birth'      => $this->parseMdy($row['founder_date_of_birth'] ?? null),
            'founder_type'       => 'primary',
        ], $prov['user']->id);

        // --- co-founder (optional) ---
        $coName = trim($row['cofounder_name'] ?? '');
        $coFounderTm = null;
        if ($coName !== '') {
            $coFounderTm = $this->companyRepo->upsertCoFounderFromImport($company, [
                'name'               => $coName,
                'email'              => $row['cofounder_email'] ?? null,
                'phone'              => $row['cofounder_phone'] ?? null,
                'working_commitment' => $row['cofounder_working_commitment'] ?? null,
                'educational_status' => $row['cofounder_educational_status'] ?? null,
            ]);
        }

        // --- hackathon enrollment ---
        if ($cycle->type === 'Hackathon') {
            $this->hackService->enrollAsTeamLeader($cycle, $prov['user'], $company);
            if ($coFounderTm) {
                try {
                    $this->hackService->enrollAsTeamMember($cycle, $coFounderTm, $company);
                } catch (\Throwable $e) {
                    // Non-fatal: co-founder attachment failure is logged by the caller.
                }
            }
        }

        // --- financials ---
        $this->finService->upsertYearSeriesFromImport($company, [
            'currency'                     => $row['currency'] ?? null,
            'current_operating_income'     => $row['current_operating_income']     ?? null,
            'forecast_operating_income_y1' => $row['forecast_operating_income_y1'] ?? null,
            'forecast_operating_income_y2' => $row['forecast_operating_income_y2'] ?? null,
            'forecast_operating_income_y3' => $row['forecast_operating_income_y3'] ?? null,
            'current_revenue'              => $row['current_revenue']              ?? null,
            'forecast_revenue_y1'          => $row['forecast_revenue_y1']          ?? null,
            'forecast_revenue_y2'          => $row['forecast_revenue_y2']          ?? null,
            'forecast_revenue_y3'          => $row['forecast_revenue_y3']          ?? null,
        ]);

        return [
            'imported'       => true,
            'user'           => $prov['user'],
            'company'        => $company,
            'was_created'    => $prov['was_created'],
            'plain_password' => $prov['plain_password'],
        ];
    }

    private function skipReasonFromWarnings(int $rowNumber, array $warnings): ?string
    {
        foreach ($warnings as $w) {
            if (($w['row'] ?? 0) === $rowNumber && in_array($w['type'] ?? '', [
                'founder_email_already_enrolled',
                'founder_phone_already_enrolled',
                'founder_email_exists_different_name',
                'founder_phone_exists_different_name',
            ], true)) {
                return $w['type'];
            }
        }
        return null;
    }

    private function intOrNull($v): ?int
    {
        return is_numeric($v) ? (int) $v : null;
    }

    private function parseMdy(?string $s): ?string
    {
        if (!$s) return null;
        if (!preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $s, $m)) return null;
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[1], (int) $m[2]);
    }
}
