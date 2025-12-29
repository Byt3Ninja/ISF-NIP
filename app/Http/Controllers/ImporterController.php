<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
use App\State;
use App\Sector;
use App\Company;
use App\Country;
use Carbon\Carbon;
use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImporterController extends Controller
{
    public function index()
    {
        $templateUrl = asset('csv/Growthlabs Import Startup Sheet.csv');
        return view('admin.importer', ['templateUrl' => $templateUrl]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('file');
            Log::info('Received file upload', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            $path = $file->store('csv_uploads', 'public');
            $fullPath = storage_path('app/public/' . $path);
            $fileHandle = fopen($fullPath, 'r');
            if ($fileHandle === false) {
                Storage::disk('public')->delete($path);
                Log::error('Failed to open CSV file', ['path' => $fullPath]);
                return response()->json(['success' => false, 'message' => 'Unable to open CSV file.'], 400);
            }

            $headers = fgetcsv($fileHandle, 0, ',');
            if ($headers === false || empty($headers)) {
                fclose($fileHandle);
                Storage::disk('public')->delete($path);
                Log::error('Empty or invalid CSV file', ['path' => $fullPath]);
                return response()->json(['success' => false, 'message' => 'Empty or invalid CSV file.'], 400);
            }

            $headers = array_map(function ($header) {
                return preg_replace('/^[\x{FEFF}\x{200B}]+/u', '', trim($header));
            }, $headers);
            $normalizedHeaders = array_map('strtolower', $headers);
            $data = [];
            $errors = [];

            Log::info('CSV Headers', ['headers' => $headers, 'count' => count($headers)]);

            while (($row = fgetcsv($fileHandle, 0, ',')) !== false) {
                $row = array_map('trim', $row);
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), null);
                } elseif (count($row) > count($headers)) {
                    $row = array_slice($row, 0, count($headers));
                }
                $data[] = array_combine($headers, $row);
            }

            fclose($fileHandle);

            if (empty($data)) {
                Storage::disk('public')->delete($path);
                Log::error('No data found in CSV file', ['path' => $fullPath]);
                return response()->json(['success' => false, 'message' => 'No data found in CSV file.'], 400);
            }

            Log::info('Sample Row', ['row' => $data[0] ?? 'No data']);
            $typeColumnIndex = array_search('type', $normalizedHeaders);
            if ($typeColumnIndex === false) {
                $errors[] = __('admin.missing_program_type');
                return response()->json([
                    'success' => false,
                    'message' => __('admin.missing_program_type'),
                    'errors' => $errors
                ], 400);
            }
            foreach ($data as $index => $row) {
                $rowErrors = [];

                // Cycle validations
                if (empty(trim($row['Program Name']))) {
                    $rowErrors[] = __('admin.missing_program_name');
                } elseif (isset($row['Program Name']) && strlen(trim($row['Program Name'])) > 255) {
                    $rowErrors[] = __('admin.program_name_too_long');
                }

               $typeValue = $row[$headers[$typeColumnIndex]] ?? null;
                if (is_null($typeValue) || empty(trim($typeValue))) {
                    $rowErrors[] = __('admin.missing_program_type');
                } elseif (!in_array(trim($typeValue), ['General_Program', 'Hackathon'])) {
                    $rowErrors[] = __('admin.invalid_program_type', ['valid_types' => 'General_Program, Hackathon']);
                }


                $startDate = null;
                $endDate = null;

                $datePattern = '/^(0?[1-9]|1[0-2])\/(0?[1-9]|[12][0-9]|3[01])\/\d{4}$/';
                if (isset($row['Program Start Date']) && !empty($row['Program Start Date'])) {
                if (!preg_match($datePattern, $row['Program Start Date'])) {
                    $rowErrors[] = __('admin.invalid_program_start_date_format');
                } else {
                 try {
                    $startDate = Carbon::createFromFormat('m/d/Y', $row['Program Start Date']);
                } catch (\Exception $e) {
                 $rowErrors[] = __('admin.invalid_program_start_date');
                }
                }
                } else {
                    $rowErrors[] = __('admin.missing_program_start_date');
                }

                if (isset($row['Program End Date']) && !empty($row['Program End Date'])) {
                if (!preg_match($datePattern, $row['Program End Date'])) {
                     $rowErrors[] = __('admin.invalid_program_end_date_format');
                  } else {
                try {
                    $endDate = Carbon::createFromFormat('m/d/Y', $row['Program End Date']);
                } catch (\Exception $e) {
                    $rowErrors[] = __('admin.invalid_program_end_date');
                }
                }
                } else {
                    $rowErrors[] = __('admin.missing_program_end_date');
                }

                if ($startDate && $endDate && $startDate->gt($endDate)) {
                    $rowErrors[] = __('admin.program_start_date_after_end_date');
                }

                // Founder validations
                if (isset($row['Founder Name']) && empty(trim($row['Founder Name']))) {
                    $rowErrors[] = __('admin.missing_founder_first_name');
                } elseif (isset($row['Founder Name']) && strlen(trim($row['Founder Name'])) > 255) {
                    $rowErrors[] = __('admin.founder_first_name_too_long');
                }

                // Startup validations
                if (isset($row['Startup Name']) && empty(trim($row['Startup Name']))) {
                    $rowErrors[] = __('admin.missing_startup_name');
                }

                if (isset($row['Founder Last Name']) && empty(trim($row['Founder Last Name']))) {
                    $rowErrors[] = __('admin.missing_founder_last_name');
                } elseif (isset($row['Founder Last Name']) && strlen(trim($row['Founder Last Name'])) > 255) {
                    $rowErrors[] = __('admin.founder_last_name_too_long');
                }

                if (isset($row['E-Mail']) && empty(trim($row['E-Mail']))) {
                    $rowErrors[] = __('admin.missing_founder_email');
                } elseif (isset($row['E-Mail']) && !filter_var($row['E-Mail'], FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = __('admin.invalid_founder_email_format');
                }

              if (empty($row['Phone'])) {
                    $rowErrors[] = __('admin.missing_phone');
                 } else {
                   $phone = trim($row['Phone']);
                    Log::info('Phone validation', [
                    'row_number' => $index + 2,
                    'original_phone' => $row['Phone'],
                    'trimmed_phone' => $phone
                 ]);
              }

                if (!empty($rowErrors)) {
                    $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $rowErrors);
                }
            }

            session(['csv_file_path' => $path]);


            return response()->json([
                'success' => true,
                'headers' => $headers,
                'data' => $data,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : 'none'
            ]);
            return response()->json(['success' => false, 'message' => 'Error processing file: ' . $e->getMessage()], 500);
        }
    }

   public function import(Request $request)
    {
        try {
            $input = $request->all();
            $mappings = $input['mappings'] ?? [];
            $data = $input['data'] ?? [];
            $organizationId = $input['organization_id'] ?? null;

            Log::info('Import started', [
                'organization_id' => $organizationId,
                'mappings' => $mappings,
                'data_count' => count($data)
            ]);

            if (empty($mappings) || empty($data) || !$organizationId) {
                return response()->json(['success' => false, 'message' => 'Missing required parameters'], 400);
            }

            $results = [
                'total_rows' => count($data),
                'attempted' => 0,
                'imported' => 0,
                'failed' => 0,
                'skipped' => 0,
                'duplicates' => 0,
                'failed_rows' => [],
                'skipped_rows' => [],
                'duplicates_found' => []
            ];

            DB::beginTransaction();

            $countryCache = [];
            foreach ($data as $index => $row) {
                $rowKeys = array_keys($row);
                $results['attempted']++;

                foreach ($mappings as $field => $srcIndex) {
                    if (!isset($rowKeys[$srcIndex])) {
                        Log::error('Invalid mapping index', [
                            'field' => $field,
                            'srcIndex' => $srcIndex,
                            'rowKeys' => $rowKeys,
                            'row_number' => $index + 2
                        ]);
                        $results['failed']++;
                        $results['failed_rows'][] = "Row " . ($index + 2) . ": Missing column for field '$field' (mapped to index $srcIndex)";
                        continue 2;
                    }
                }

                if (isset($mappings['country_id']) && !empty($row[$rowKeys[$mappings['country_id']]])) {
                    $countryName = trim($row[$rowKeys[$mappings['country_id']]]);
                    if (!isset($countryCache[$countryName])) {
                        Log::info('Processing country', ['country_name' => $countryName]);
                        $country = Country::updateOrCreate(
                            ['name_en' => $countryName],
                            [
                                'name_ar' => $countryName,
                                'updated_at' => Carbon::now()
                            ]
                        );
                        $countryCache[$countryName] = $country->id;
                    }
                }
            }

            $stateCache = [];
            foreach ($data as $index => $row) {
                $rowKeys = array_keys($row);

                if (isset($mappings['country_id']) && isset($mappings['state_id']) &&
                    !empty($row[$rowKeys[$mappings['country_id']]]) && !empty($row[$rowKeys[$mappings['state_id']]])) {
                    $countryName = trim($row[$rowKeys[$mappings['country_id']]]);
                    $stateName = trim($row[$rowKeys[$mappings['state_id']]]);
                    $countryId = $countryCache[$countryName] ?? null;

                    if ($countryId) {
                        $stateKey = $countryId . ':' . $stateName;
                        if (!isset($stateCache[$stateKey])) {
                            Log::info('Processing state', ['country_id' => $countryId, 'state_name' => $stateName]);
                            $state = State::updateOrCreate(
                                ['country_id' => $countryId, 'name_en' => $stateName],
                                [
                                    'name_ar' => $stateName,
                                    'updated_at' => Carbon::now()
                                ]
                            );
                            $stateCache[$stateKey] = $state->id;
                        }
                    } else {
                        $results['skipped']++;
                        $results['skipped_rows'][] = "Row " . ($index + 2) . ": Missing country for state '$stateName'";
                        continue;
                    }
                }
            }

            $cycleCache = [];
            foreach ($data as $index => $row) {
                $rowKeys = array_keys($row);

                if (isset($mappings['title']) && isset($mappings['start']) && isset($mappings['end']) &&
                    !empty($row[$rowKeys[$mappings['title']]]) &&
                    !empty($row[$rowKeys[$mappings['start']]]) &&
                    !empty($row[$rowKeys[$mappings['end']]])) {
                    $cycleTitle = trim($row[$rowKeys[$mappings['title']]]);
                    $startDateRaw = trim($row[$rowKeys[$mappings['start']]]);
                    $endDateRaw = trim($row[$rowKeys[$mappings['end']]]);
                    $type = trim($row[$rowKeys[$mappings['type']]]);
                    try {
                        $startDate = Carbon::createFromFormat('m/d/Y', $startDateRaw)->toDateString();
                        $endDate = Carbon::createFromFormat('m/d/Y', $endDateRaw)->toDateString();
                    } catch (\Exception $e) {
                        $results['skipped']++;
                        $results['skipped_rows'][] = "Row " . ($index + 2) . ": Invalid date format for start ($startDateRaw) or end ($endDateRaw) (use MM/DD/YYYY)";
                        continue;
                    }

                 if (!in_array($type, ['General_Program','Hackathon'])) {
                    $results['skipped']++;
                    $results['skipped_rows'][] = "Row " . ($index + 2) . ": Invalid program type '$type'; must be one of: General_Program, Hackathon";
                    continue;
                }

                    $cycleKey = $cycleTitle; 
                    if (!isset($cycleCache[$cycleKey])) {
                        $existingCycles = Cycle::whereRaw('LOWER(title) = ?', [strtolower($cycleTitle)])
                            ->get(['id', 'title', 'start', 'end', 'organization_id'])
                            ->toArray();
                        Log::info('Checking existing cycles', [
                            'raw_title' => $row[$rowKeys[$mappings['title']]],
                            'normalized_title' => $cycleTitle,
                            'existing_cycles' => $existingCycles
                        ]);

                        Log::info('Processing cycle', [
                            'organization_id' => $organizationId,
                            'title' => $cycleTitle,
                            'start' => $startDate,
                            'end' => $endDate
                        ]);

                        try {
                            $cycle = Cycle::updateOrCreate(
                                [
                                    'title' => $cycleTitle
                                ],
                                [
                                    'organization_id' => $organizationId,
                                    'start' => $startDate,
                                    'end' => $endDate,
                                    'type' => $type
                                ]
                            );
                            

                            Log::info('Cycle processed', [
                                'cycle_id' => $cycle->id,
                                'was_created' => $cycle->wasRecentlyCreated,
                                'title' => $cycleTitle,
                                'start' => $startDate,
                                'end' => $endDate,
                                'organization_id' => $organizationId
                            ]);

                            $cycleCache[$cycleKey] = $cycle->id;
                        } catch (\Exception $e) {
                            Log::error('Cycle update failed', [
                                'title' => $cycleTitle,
                                'organization_id' => $organizationId,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            $results['failed']++;
                            $results['failed_rows'][] = "Row " . ($index + 2) . ": Failed to process cycle: " . $e->getMessage();
                            continue;
                        }
                    }
                } else {
                    $results['skipped']++;
                    $results['skipped_rows'][] = "Row " . ($index + 2) . ": Missing or invalid cycle data";
                    continue;
                }
            }

            foreach ($data as $index => $row) {
                $rowKeys = array_keys($row);

                $email = null;
                if (isset($mappings['email']) && isset($rowKeys[$mappings['email']])) {
                    $email = trim(strtolower($row[$rowKeys[$mappings['email']]]));
                }

                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $results['skipped']++;
                    $results['skipped_rows'][] = "Row " . ($index + 2) . ": Missing or invalid email";
                    continue;
                }

                $cycleId = null;
                if (isset($mappings['title']) && !empty($row[$rowKeys[$mappings['title']]])) {
                    $cycleTitle = trim($row[$rowKeys[$mappings['title']]]);
                    $cycleKey = $cycleTitle;
                    $cycleId = $cycleCache[$cycleKey] ?? null;
                }

                if (!$cycleId) {
                    $results['skipped']++;
                    $results['skipped_rows'][] = "Row " . ($index + 2) . ": Unable to link cycle";
                    continue;
                }

                $countryId = null;
                $stateId = null;
                if (isset($mappings['country_id']) && !empty($row[$rowKeys[$mappings['country_id']]])) {
                    $countryName = trim($row[$rowKeys[$mappings['country_id']]]);
                    $countryId = $countryCache[$countryName] ?? null;
                }

                if (isset($mappings['state_id']) && !empty($row[$rowKeys[$mappings['state_id']]]) && $countryId) {
                    $stateName = trim($row[$rowKeys[$mappings['state_id']]]);
                    $stateKey = $countryId . ':' . $stateName;
                    $stateId = $stateCache[$stateKey] ?? null;
                }

                $estDate = null;
                if (isset($mappings['est_date']) && !empty($row[$rowKeys[$mappings['est_date']]])) {
                    try {
                        $estDateRaw = trim($row[$rowKeys[$mappings['est_date']]]);
                        $estDate = Carbon::createFromFormat('m/d/Y', $estDateRaw)->toDateString();
                    } catch (\Exception $e) {
                        $results['skipped']++;
                        $results['skipped_rows'][] = "Row " . ($index + 2) . ": Invalid establishment date format for '$estDateRaw' (use MM/DD/YYYY)";
                        continue;
                    }
                }

                // Process Company
                $nameEn = isset($mappings['name_en']) ? trim($row[$rowKeys[$mappings['name_en']]]) : null;
                if (empty($nameEn)) {
                    $results['skipped']++;
                    $results['skipped_rows'][] = "Row " . ($index + 2) . ": Missing company name";
                    continue;
                }

                $sectorId = null;
                if (isset($mappings['sector']) && !empty($row[$rowKeys[$mappings['sector']]])) {
                    $sectorName = trim($row[$rowKeys[$mappings['sector']]]);
                    if (!empty($sectorName)) {
                        Log::info('Processing sector for company', ['sector_name' => $sectorName, 'row_number' => $index + 2]);
                        $sector = Sector::updateOrCreate(
                            ['name_en' => $sectorName],
                            [
                                'name_ar' => $sectorName,
                                'updated_at' => Carbon::now()
                            ]
                        );
                        $sectorId = $sector->id;
                        $sectorCache[$sectorName] = $sectorId;
                    } else {
                        $results['skipped']++;
                        $results['skipped_rows'][] = "Row " . ($index + 2) . ": Empty sector name";
                        continue;
                    }
                }

                $targetedMarket = null;
                if (isset($mappings['targeted_market']) && !empty($row[$rowKeys[$mappings['targeted_market']]])) {
                    $marketNames = array_map('trim', explode(',', $row[$rowKeys[$mappings['targeted_market']]]));
                    $marketIds = [];
                    foreach ($marketNames as $marketName) {
                        if (!empty($marketName)) {
                            if (!isset($countryCache[$marketName])) {
                                Log::info('Processing targeted market country', ['country_name' => $marketName]);
                                $country = Country::updateOrCreate(
                                    ['name_en' => $marketName],
                                    [
                                        'name_ar' => $marketName,
                                        'updated_at' => Carbon::now()
                                    ]
                                );
                                $countryCache[$marketName] = $country->id;
                            }
                            $marketIds[] = (string) $countryCache[$marketName];
                        }
                    }
                    if (!empty($marketIds)) {
                        $targetedMarket = json_encode($marketIds);
                    }
                }

                $companyData = [
                    'cycle' => $cycleId,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'name_ar' => $nameEn,
                    'address_en' => isset($mappings['address_en']) ? trim($row[$rowKeys[$mappings['address_en']]]) : null,
                    'address_ar' => isset($mappings['address_en']) ? trim($row[$rowKeys[$mappings['address_en']]]) : null,
                    'industries' => isset($mappings['industries']) ? trim($row[$rowKeys[$mappings['industries']]]) : null,
                    'sector' => $sectorId,
                    'years_of_operations' => isset($mappings['years_of_operations']) ? trim($row[$rowKeys[$mappings['years_of_operations']]]) : null,
                    'product_or_service' => isset($mappings['product_or_service']) ? trim($row[$rowKeys[$mappings['product_or_service']]]) : null,
                    'targeted_market' => $targetedMarket,
                    'est_date' => $estDate,
                ];

                Log::info('Processing company', [
                    'cycle' => $cycleId,
                    'name_en' => $nameEn,
                    'data' => $companyData
                ]);

                $company = Company::updateOrCreate(
                    [
                        'name_en' => $nameEn
                    ],
                    $companyData
                );

                // Process Founder
                $userData = [
                    'company_id' => $company->id,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'email' => $email,
                    'first_name_en' => isset($mappings['first_name_en']) ? trim($row[$rowKeys[$mappings['first_name_en']]]) : null,
                    'first_name_ar' => isset($mappings['first_name_en']) ? trim($row[$rowKeys[$mappings['first_name_en']]]) : null,
                    'last_name_en' => isset($mappings['last_name_en']) ? trim($row[$rowKeys[$mappings['last_name_en']]]) : null,
                    'last_name_ar' => isset($mappings['last_name_en']) ? trim($row[$rowKeys[$mappings['last_name_en']]]) : null,
                    'gender' => isset($mappings['gender']) ? trim($row[$rowKeys[$mappings['gender']]]) : null,
                    'phone' => isset($mappings['phone']) ? trim($row[$rowKeys[$mappings['phone']]]) : null,
                    'country_code' => isset($mappings['country_code']) ? trim($row[$rowKeys[$mappings['country_code']]]) : null,
                    'HaveCompany' => 1,
                    'email_verified_at' => now(),
                ];

                $existingUser = User::where('email', $email)->first();
                if (!$existingUser) {
                    $userData['password'] = bcrypt('123456789');
                }

                $user = User::updateOrCreate(
                    ['email' => $email],
                    $userData
                );

                $user->allotRole('Registered');

                $results['imported']++;
            }

            DB::commit();

            // Clean up the stored CSV file
            if (session('csv_file_path')) {
                Storage::disk('public')->delete(session('csv_file_path'));
                session()->forget('csv_file_path');
            }

            return response()->json([
                'success' => true,
                'message' => __('admin.imported'),
                'imported' => $results['imported'],
                'total_rows' => $results['total_rows'],
                'attempted' => $results['attempted'],
                'failed' => $results['failed'],
                'skipped' => $results['skipped'],
                'duplicates' => $results['duplicates'],
                'failed_rows' => $results['failed_rows'],
                'skipped_rows' => $results['skipped_rows'],
                'duplicates_found' => $results['duplicates_found']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
   

     public function getOrganizations()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: User not authenticated'
                ], 401);
            }

            $organizations = collect();

            if ($user->hasRole('Admin')) {
                $organizations = Organization::select('id', 'name_en', 'name_ar')->get();
            } else {
                $orgIds = $user->organizations_ids;
                if (!empty($orgIds)) {
                    $organizations = Organization::select('id', 'name_en', 'name_ar')
                        ->whereIn('id', $orgIds)
                        ->get();
                    Log::info('User fetched their organizations', [
                        'user_id' => $user->id,
                        'organization_ids' => $orgIds
                    ]);
                } else {
                    Log::warning('User has no associated organizations', ['user_id' => $user->id]);
                }
            }

            if ($organizations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: User lacks required role'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'organizations' => $organizations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch organizations'
            ], 500);
        }
    }
}