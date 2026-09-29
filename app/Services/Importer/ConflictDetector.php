<?php

namespace App\Services\Importer;

use Illuminate\Support\Facades\DB;

/**
 * Batched conflict detection. One query per warning type — NOT per row.
 *
 * Input:
 *   $rows     : positional string[][] from CsvParser
 *   $mappings : field_key => int|null
 *   $orgId    : organization id (required for program + enrollment scoping)
 *
 * Output: ConflictWarning[]
 */
class ConflictDetector
{
    /** @return ConflictWarning[] */
    public function detect(array $rows, array $mappings, int $orgId): array
    {
        $warnings = [];

        // Pre-extract lookup values per row
        $emails      = []; // [rowNumber => lowercase email]
        $phones      = []; // [rowNumber => ['phone' => ..., 'cc' => ...]]
        $fullNames   = []; // [rowNumber => 'first last' lowercase]
        $programKeys = []; // [rowNumber => ['name', 'type', 'start', 'end']]

        // In-upload duplicate trackers: first occurrence wins; 2nd+ get warnings.
        $emailFirstSeen = []; // [email_lc => rowNumber]
        $phoneFirstSeen = []; // [phone:cc => rowNumber]

        $getCol = function (array $row, ?int $idx): ?string {
            if ($idx === null) return null;
            return isset($row[$idx]) ? trim((string) $row[$idx]) : null;
        };

        foreach ($rows as $i => $row) {
            $rowNumber = $i + 2;

            $email = strtolower((string) $getCol($row, $mappings['founder_email'] ?? null));
            if ($email !== '') {
                $emails[$rowNumber] = $email;
                if (isset($emailFirstSeen[$email])) {
                    $warnings[] = new ConflictWarning(
                        row: $rowNumber, type: 'founder_email_duplicate_in_upload',
                        fieldKey: 'founder_email', value: $email,
                        extras: ['first_row' => $emailFirstSeen[$email]]
                    );
                } else {
                    $emailFirstSeen[$email] = $rowNumber;
                }
            }

            $phone = (string) $getCol($row, $mappings['founder_phone'] ?? null);
            $cc    = (string) $getCol($row, $mappings['founder_country_code'] ?? null);
            if ($phone !== '') {
                $phones[$rowNumber] = ['phone' => $phone, 'cc' => $cc];
                $phoneKey = $phone . ':' . $cc;
                if (isset($phoneFirstSeen[$phoneKey])) {
                    $warnings[] = new ConflictWarning(
                        row: $rowNumber, type: 'founder_phone_duplicate_in_upload',
                        fieldKey: 'founder_phone', value: $phone,
                        extras: ['first_row' => $phoneFirstSeen[$phoneKey]]
                    );
                } else {
                    $phoneFirstSeen[$phoneKey] = $rowNumber;
                }
            }

            $fn = (string) $getCol($row, $mappings['founder_first_name'] ?? null);
            $ln = (string) $getCol($row, $mappings['founder_last_name']  ?? null);
            if ($fn !== '' || $ln !== '') $fullNames[$rowNumber] = strtolower(trim("{$fn} {$ln}"));

            $pname  = (string) $getCol($row, $mappings['program_name'] ?? null);
            $ptype  = (string) $getCol($row, $mappings['program_type'] ?? null);
            $pstart = (string) $getCol($row, $mappings['program_start_date'] ?? null);
            $pend   = (string) $getCol($row, $mappings['program_end_date'] ?? null);
            if ($pname !== '' && $ptype !== '') {
                $programKeys[$rowNumber] = [
                    'name'  => strtolower($pname),
                    'type'  => $ptype,
                    'start' => $this->normaliseDate($pstart),
                    'end'   => $this->normaliseDate($pend),
                ];
            }
        }

        // Warning 1: founder email already enrolled in a program in THIS organization
        if (!empty($emails)) {
            $lowerEmails = array_values(array_unique(array_values($emails)));
            $rows1 = DB::table('users')
                ->join('companies', 'companies.id', '=', 'users.company_id')
                ->join('cycles', 'cycles.id', '=', 'companies.cycle')
                ->whereIn(DB::raw('LOWER(users.email)'), $lowerEmails)
                ->where('cycles.organization_id', $orgId)
                ->select(['users.id as user_id', DB::raw('LOWER(users.email) as email_lc')])
                ->get();
            $enrolled = collect($rows1)->pluck('user_id', 'email_lc')->toArray();
            foreach ($emails as $rn => $em) {
                if (isset($enrolled[$em])) {
                    $warnings[] = new ConflictWarning(
                        row: $rn, type: 'founder_email_already_enrolled',
                        fieldKey: 'founder_email', value: $em,
                        extras: ['existing_user_id' => $enrolled[$em]]
                    );
                }
            }
        }

        // Warning 2: founder phone already enrolled
        if (!empty($phones)) {
            $phoneValues = array_values(array_unique(array_map(fn($p) => $p['phone'], $phones)));
            $rows2 = DB::table('users')
                ->join('companies', 'companies.id', '=', 'users.company_id')
                ->join('cycles', 'cycles.id', '=', 'companies.cycle')
                ->whereIn('users.phone', $phoneValues)
                ->where('cycles.organization_id', $orgId)
                ->select(['users.id as user_id', 'users.phone', 'users.country_code'])
                ->get();
            $phoneIndex = [];
            foreach ($rows2 as $r) {
                $phoneIndex[$r->phone . ':' . ($r->country_code ?? '')] = $r->user_id;
            }
            foreach ($phones as $rn => $p) {
                $k = $p['phone'] . ':' . ($p['cc'] ?? '');
                if (isset($phoneIndex[$k])) {
                    $warnings[] = new ConflictWarning(
                        row: $rn, type: 'founder_phone_already_enrolled',
                        fieldKey: 'founder_phone', value: $p['phone'],
                        extras: ['existing_user_id' => $phoneIndex[$k]]
                    );
                }
            }
        }

        // Warning 3: email matches existing user, name differs
        if (!empty($emails)) {
            $lowerEmails = array_values(array_unique(array_values($emails)));
            $rows3 = DB::table('users')
                ->whereIn(DB::raw('LOWER(users.email)'), $lowerEmails)
                ->select([
                    'users.id as user_id',
                    DB::raw('LOWER(users.email) as email_lc'),
                    DB::raw("LOWER(CONCAT(COALESCE(first_name_en,''),' ',COALESCE(last_name_en,''))) as full_name_lc"),
                ])
                ->get();
            $nameIndex = [];
            foreach ($rows3 as $r) {
                $nameIndex[$r->email_lc] = ['user_id' => $r->user_id, 'full_name' => trim($r->full_name_lc)];
            }
            foreach ($emails as $rn => $em) {
                if (isset($nameIndex[$em])) {
                    $rowFull = $fullNames[$rn] ?? '';
                    if ($rowFull !== '' && $rowFull !== $nameIndex[$em]['full_name']) {
                        $warnings[] = new ConflictWarning(
                            row: $rn, type: 'founder_email_exists_different_name',
                            fieldKey: 'founder_email', value: $em,
                            extras: ['existing_user_id' => $nameIndex[$em]['user_id']]
                        );
                    }
                }
            }
        }

        // Warning 4: phone matches existing user, name differs
        if (!empty($phones)) {
            $phoneValues = array_values(array_unique(array_map(fn($p) => $p['phone'], $phones)));
            $rows4 = DB::table('users')
                ->whereIn('users.phone', $phoneValues)
                ->select([
                    'users.id as user_id', 'users.phone', 'users.country_code',
                    DB::raw("LOWER(CONCAT(COALESCE(first_name_en,''),' ',COALESCE(last_name_en,''))) as full_name_lc"),
                ])
                ->get();
            $idx = [];
            foreach ($rows4 as $r) {
                $idx[$r->phone . ':' . ($r->country_code ?? '')] = [
                    'user_id' => $r->user_id, 'full_name' => trim($r->full_name_lc),
                ];
            }
            foreach ($phones as $rn => $p) {
                $k = $p['phone'] . ':' . ($p['cc'] ?? '');
                if (isset($idx[$k])) {
                    $rowFull = $fullNames[$rn] ?? '';
                    if ($rowFull !== '' && $rowFull !== $idx[$k]['full_name']) {
                        $warnings[] = new ConflictWarning(
                            row: $rn, type: 'founder_phone_exists_different_name',
                            fieldKey: 'founder_phone', value: $p['phone'],
                            extras: ['existing_user_id' => $idx[$k]['user_id']]
                        );
                    }
                }
            }
        }

        // Warning 5: program same name+type exists but different dates
        if (!empty($programKeys)) {
            $names = array_values(array_unique(array_map(fn($p) => $p['name'], $programKeys)));
            $rows5 = DB::table('cycles')
                ->where('cycles.organization_id', $orgId)
                ->whereIn(DB::raw('LOWER(cycles.title)'), $names)
                ->select(['cycles.id', DB::raw('LOWER(cycles.title) as title_lc'), 'cycles.type', 'cycles.start', 'cycles.end'])
                ->get();
            $existingByKey = [];
            foreach ($rows5 as $r) {
                $existingByKey[$r->title_lc . ':' . $r->type] = $r;
            }
            foreach ($programKeys as $rn => $pk) {
                $k = $pk['name'] . ':' . $pk['type'];
                if (!isset($existingByKey[$k])) continue;
                $existing = $existingByKey[$k];
                $existingStart = $existing->start ? substr((string) $existing->start, 0, 10) : null;
                $existingEnd   = $existing->end   ? substr((string) $existing->end,   0, 10) : null;
                if ($existingStart !== $pk['start'] || $existingEnd !== $pk['end']) {
                    $warnings[] = new ConflictWarning(
                        row: $rn, type: 'program_exists_different_dates',
                        fieldKey: 'program_name', value: $pk['name'],
                        extras: ['existing_program_id' => $existing->id]
                    );
                }
            }
        }

        return $warnings;
    }

    private function normaliseDate(string $mdy): ?string
    {
        if (!preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $mdy, $m)) return null;
        return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[1], (int) $m[2]);
    }
}
