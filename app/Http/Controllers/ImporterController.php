<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
use App\State;
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

            $headers = array_map('trim', $headers);
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

            foreach ($data as $index => $row) {
                $rowErrors = [];

                // Cycle validations
                if (isset($row['Program Name']) && empty(trim($row['Program Name']))) {
                    $rowErrors[] = "Missing program name";
                } elseif (isset($row['Program Name']) && strlen(trim($row['Program Name'])) > 255) {
                    $rowErrors[] = "Program name exceeds 255 characters";
                }

                $startDate = null;
                $endDate = null;

                if (isset($row['Program Start Date']) && !empty($row['Program Start Date'])) {
                    try {
                        $startDate = Carbon::createFromFormat('m/d/Y', $row['Program Start Date']);
                        if (!$startDate) {
                            $rowErrors[] = "Invalid program start date format (use MM/DD/YYYY)";
                        }
                    } catch (\Exception $e) {
                        $rowErrors[] = "Invalid program start date format (use MM/DD/YYYY)";
                    }
                } else {
                    $rowErrors[] = "Missing program start date";
                }

                if (isset($row['Program End Date']) && !empty($row['Program End Date'])) {
                    try {
                        $endDate = Carbon::createFromFormat('m/d/Y', $row['Program End Date']);
                        if (!$endDate) {
                            $rowErrors[] = "Invalid program end date format (use MM/DD/YYYY)";
                        }
                    } catch (\Exception $e) {
                        $rowErrors[] = "Invalid program end date format (use MM/DD/YYYY)";
                    }
                } else {
                    $rowErrors[] = "Missing program end date";
                }

                if ($startDate && $endDate && $startDate->gt($endDate)) {
                    $rowErrors[] = "Program start date must be before end date";
                }

                // Founder validations
                if (isset($row['Founder Name']) && empty(trim($row['Founder Name']))) {
                    $rowErrors[] = "Missing founder first name";
                } elseif (isset($row['Founder Name']) && strlen(trim($row['Founder Name'])) > 255) {
                    $rowErrors[] = "Founder first name exceeds 255 characters";
                }

                // Startup validations
                if (isset($row['Startup Name']) && empty(trim($row['Startup Name']))) {
                    $rowErrors[] = "Missing Startup Name";
                }

                if (isset($row['Founder Last Name']) && empty(trim($row['Founder Last Name']))) {
                    $rowErrors[] = "Missing founder last name";
                } elseif (isset($row['Founder Last Name']) && strlen(trim($row['Founder Last Name'])) > 255) {
                    $rowErrors[] = "Founder last name exceeds 255 characters";
                }

                if (isset($row['E-Mail']) && empty(trim($row['E-Mail']))) {
                    $rowErrors[] = "Missing founder email";
                } elseif (isset($row['E-Mail']) && !filter_var($row['E-Mail'], FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "Invalid founder email format";
                }

              if (empty($row['Phone'])) {
                    $rowErrors[] = "Phone is required";
                 } else {
                   $phone = trim($row['Phone']);
                    Log::info('Phone validation', [
                    'row_number' => $index + 2,
                    'original_phone' => $row['Phone'],
                    'trimmed_phone' => $phone
                 ]);
                if (!preg_match('/^\d{10,}$/', $phone)) {
                  $rowErrors[] = "Phone must contain only numbers (at least 11 digits, no hyphens or special characters)";
                }
              }

                if (!empty($rowErrors)) {
                    $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $rowErrors);
                }
            }

            session(['csv_file_path' => $path]);

            Log::info('CSV uploaded successfully', ['headers' => $headers, 'row_count' => count($data), 'validation_errors' => $errors]);

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
                        $country = Country::firstOrCreate(
                            ['name_en' => $countryName],
                            [
                                'name_ar' => $countryName,
                                'created_at' => Carbon::now(),
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
                            $state = State::firstOrCreate(
                                ['country_id' => $countryId, 'name_en' => $stateName],
                                [
                                    'name_ar' => $stateName,
                                    'created_at' => Carbon::now(),
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
                    $startDate = Carbon::parse(trim($row[$rowKeys[$mappings['start']]]))->toDateString();
                    $endDate = Carbon::parse(trim($row[$rowKeys[$mappings['end']]]))->toDateString();

                    $cycleKey = $cycleTitle . ':' . $startDate . ':' . $endDate;
                    if (!isset($cycleCache[$cycleKey])) {
                        $cycle = Cycle::firstOrCreate(
                            [
                                'organization_id' => $organizationId,
                                'title' => $cycleTitle,
                                'start' => $startDate,
                                'end' => $endDate
                            ],
                            [
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]
                        );
                        $cycleCache[$cycleKey] = $cycle->id;
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
                    $email = trim($row[$rowKeys[$mappings['email']]]);
                }

                if (empty($email)) {
                    $results['skipped']++;
                    $results['skipped_rows'][] = "Row " . ($index + 2) . ": Missing or invalid email";
                    continue;
                }

                // Check for duplicate email 
                $cycleId = null;
                if (isset($mappings['title']) && isset($mappings['start']) && isset($mappings['end']) &&
                    !empty($row[$rowKeys[$mappings['title']]]) &&
                    !empty($row[$rowKeys[$mappings['start']]]) &&
                    !empty($row[$rowKeys[$mappings['end']]])) {
                    $cycleTitle = trim($row[$rowKeys[$mappings['title']]]);
                    $startDate = Carbon::parse(trim($row[$rowKeys[$mappings['start']]]))->toDateString();
                    $endDate = Carbon::parse(trim($row[$rowKeys[$mappings['end']]]))->toDateString();
                    $cycleKey = $cycleTitle . ':' . $startDate . ':' . $endDate;
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

                $company = Company::firstOrCreate(
                    [
                        'cycle' => $cycleId,
                        'name_en' => $nameEn
                    ],
                    [
                        'country_id' => $countryId,
                        'state_id' => $stateId,
                        'name_ar' => $nameEn,
                        'address_en' => isset($mappings['address_en']) ? trim($row[$rowKeys[$mappings['address_en']]]) : null,
                        'address_ar' => isset($mappings['address_en']) ? trim($row[$rowKeys[$mappings['address_en']]]) : null,
                        'industries' => isset($mappings['industries']) ? trim($row[$rowKeys[$mappings['industries']]]) : null,
                        'sector' => isset($mappings['sector']) ? trim($row[$rowKeys[$mappings['sector']]]) : null,
                        'years_of_operations' => isset($mappings['years_of_operations']) ? trim($row[$rowKeys[$mappings['years_of_operations']]]) : null,
                        'product_or_service' => isset($mappings['product_or_service']) ? trim($row[$rowKeys[$mappings['product_or_service']]]) : null,
                        'targeted_market' => isset($mappings['targeted_market']) ? trim($row[$rowKeys[$mappings['targeted_market']]]) : null,
                        'est_date' => $estDate,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                );

                // Process Founder
                $userData = [
                    'organization_id' => $organizationId,
                    'company_id' => $company->id,
                    'email' => $email,
                    'password' => bcrypt('password'),
                    'first_name_en' => isset($mappings['first_name_en']) ? trim($row[$rowKeys[$mappings['first_name_en']]]) : null,
                    'first_name_ar' => isset($mappings['first_name_en']) ? trim($row[$rowKeys[$mappings['first_name_en']]]) : null,
                    'last_name_en' => isset($mappings['last_name_en']) ? trim($row[$rowKeys[$mappings['last_name_en']]]) : null,
                    'last_name_ar' => isset($mappings['last_name_en']) ? trim($row[$rowKeys[$mappings['last_name_en']]]) : null,
                    'gender' => isset($mappings['gender']) ? trim($row[$rowKeys[$mappings['gender']]]) : null,
                    'phone' => isset($mappings['phone']) && isset($mappings['country_code'])? trim($row[$rowKeys[$mappings['country_code']]]) . trim($row[$rowKeys[$mappings['phone']]]): null,
                    'country_code' => isset($mappings['country_code']) ? trim($row[$rowKeys[$mappings['country_code']]]) : null,
                    'HaveCompany' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];

                User::firstOrCreate(
                    ['email' => $email],
                    $userData
                );

                $results['imported']++;
            }


            DB::commit();

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
            } elseif ($user->hasRole('organizationManager')) {
                if ($user->organization_id) {
                    $organization = Organization::select('id', 'name_en', 'name_ar')
                        ->where('id', $user->organization_id)
                        ->first();
                    if ($organization) {
                        $organizations = collect([$organization]);
                        Log::info('Organization manager fetched their organization', [
                            'user_id' => $user->id,
                            'organization_id' => $user->organization_id
                        ]);
                    } else {
                        Log::warning('Organization manager has no associated organization', [
                            'user_id' => $user->id,
                            'organization_id' => $user->organization_id
                        ]);
                    }
                } else {
                    Log::warning('Organization manager has no organization_id', ['user_id' => $user->id]);
                }
            } else {
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