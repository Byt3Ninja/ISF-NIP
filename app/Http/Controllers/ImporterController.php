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

            while (($row = fgetcsv($fileHandle, 0, ',')) !== false) {
                $row = array_map('trim', $row);
                $data[] = array_combine($headers, array_pad($row, count($headers), null));
            }

            fclose($fileHandle);

            if (empty($data)) {
                Storage::disk('public')->delete($path);
                Log::error('No data found in CSV file', ['path' => $fullPath]);
                return response()->json(['success' => false, 'message' => 'No data found in CSV file.'], 400);
            }

            session(['csv_file_path' => $path]);

            Log::info('CSV uploaded successfully', ['headers' => $headers, 'row_count' => count($data)]);

            return response()->json([
                'success' => true,
                'headers' => $headers,
                'data' => $data
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
            $request->validate([
                'mappings' => 'required|array',
                'data' => 'required|array',
                'organization_id' => 'required|integer|exists:organizations,id'
            ]);

            $mappings = $request->input('mappings');
            $data = $request->input('data');
            $organizationId = $request->input('organization_id');
            $filePath = session('csv_file_path');

            if (!$filePath || !Storage::disk('public')->exists($filePath)) {
                Log::error('Import failed: No uploaded file found', ['file_path' => $filePath]);
                return response()->json(['success' => false, 'message' => 'No uploaded file found.'], 400);
            }

            $fieldEntities = [
                'title' => 'Cycle',
                'start' => 'Cycle',
                'end' => 'Cycle',
                'name_en' => 'Company',
                'country_id' => 'Company',
                'state_id' => 'Company',
                'industries' => 'Company',
                'sector' => 'Company',
                'address_en' => 'Company',
                'years_of_operations' => 'Company',
                'product_or_service' => 'Company',
                'targeted_market' => 'Company',
                'est_date' => 'Company',
                'first_name_en' => 'Founder',
                'last_name_en' => 'Founder',
                'gender' => 'Founder',
                'phone' => 'Founder',
                'email' => 'Founder',
            ];

            DB::beginTransaction();
            $importedCount = 0;
            $skippedRows = [];

            foreach ($data as $index => $row) {
                $companyData = [];
                $founderData = [];
                $cycleData = [];

                foreach ($mappings as $field => $srcIndex) {
                    $entity = $fieldEntities[$field] ?? 'Unknown';
                    $header = array_keys($row)[$srcIndex] ?? null;
                    $value = $header ? ($row[$header] ?? null) : null;

                    if ($entity === 'Cycle') {
                        $cycleData[$field] = $value;
                    } elseif ($entity === 'Company') {
                        $companyData[$field] = $value;
                    } elseif ($entity === 'Founder') {
                        $founderData[$field] = $value;
                    } else {
                        Log::warning('Unknown field entity', ['field' => $field, 'row_number' => $index + 2]);
                    }
                }

                $requiredFields = ['name_en', 'address_en'];
                $missingFields = array_filter($requiredFields, fn($field) => !isset($companyData[$field]) || trim($companyData[$field]) === '');
                if (!empty($missingFields)) {
                    $skippedRows[] = "Row " . ($index + 2) . ": Missing required fields: " . implode(', ', $missingFields);
                    Log::warning('Row skipped due to missing required fields', [
                        'row_number' => $index + 2,
                        'missing_fields' => $missingFields
                    ]);
                    continue;
                }

                $companyData['name_ar'] = $companyData['name_en'] ?? '';
                $companyData['address_ar'] = $companyData['address_en'] ?? '';

               if (isset($companyData['country_id']) && !empty($companyData['country_id']) && !is_numeric($companyData['country_id'])) {
                    $countryName = trim($companyData['country_id']);
                    $country = Country::where('name_en', $countryName)
                    ->orWhere('name_ar', $countryName)
                    ->first();
                     if (!$country) {
                        $country = Country::create([
                            'name_en' => $countryName,
                            'name_ar' => $countryName,
                        ]);
                    }
                        $companyData['country_id'] = $country->id;
                } else {
                    $companyData['country_id'] = null;
                }   

                if (isset($companyData['state_id']) && !empty($companyData['state_id']) && !is_numeric($companyData['state_id'])) {
                    $stateName = trim($companyData['state_id']);
                    $query = State::where('name_en', $stateName)->orWhere('name_ar', $stateName);
                    if (!empty($companyData['country_id'])) {
                          $query->where('country_id', $companyData['country_id']);
                        }
                         $state = $query->first();
                    if (!$state && !empty($companyData['country_id'])) {
                         $state = State::create([
                        'name_en' => $stateName,
                        'name_ar' => $stateName,
                        'country_id' => $companyData['country_id'],
                    ]);
                 }
                     $companyData['state_id'] = $state ? $state->id : null;
            } else {
                    $companyData['state_id'] = null;
                }


                if (isset($companyData['est_date']) && !empty($companyData['est_date'])) {
                    try {
                        $companyData['est_date'] = Carbon::createFromFormat('m/d/Y', $companyData['est_date'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $skippedRows[] = "Row " . ($index + 2) . ": Invalid establishment date format.";
                        Log::warning('Invalid est_date', ['row_number' => $index + 2, 'est_date' => $companyData['est_date']]);
                        continue;
                    }
                } else {
                    $companyData['est_date'] = null;
                }

                if (!empty($cycleData['title'])) {
                    try {
                        $cycleData['start'] = !empty($cycleData['start']) ? Carbon::createFromFormat('m/d/Y', $cycleData['start'])->format('Y-m-d') : null;
                        $cycleData['end'] = !empty($cycleData['end']) ? Carbon::createFromFormat('m/d/Y', $cycleData['end'])->format('Y-m-d') : null;
                    } catch (\Exception $e) {
                        $skippedRows[] = "Row " . ($index + 2) . ": Invalid cycle date format.";
                        Log::warning('Invalid cycle date', ['row_number' => $index + 2, 'start' => $cycleData['start'], 'end' => $cycleData['end']]);
                        continue;
                    }

                    $cycle = Cycle::updateOrCreate(
                        ['title' => $cycleData['title']],
                        [
                            'start' => $cycleData['start'],
                            'end' => $cycleData['end'],
                            'organization_id' => $organizationId,
                        ]
                    );
                    $companyData['cycle'] = $cycle->id;
                } else {
                    $companyData['cycle'] = null;
                }

                $companyData = array_filter($companyData, fn($value) => !is_null($value));

                try {
                    $company = Company::create($companyData);
                } catch (\Exception $e) {
                    $skippedRows[] = "Row " . ($index + 2) . ": Failed to create company: " . $e->getMessage();
                    Log::warning('Company creation failed', [
                        'row_number' => $index + 2,
                        'error' => $e->getMessage(),
                        'companyData' => $companyData
                    ]);
                    continue;
                }

                if (!empty($founderData['email']) && filter_var($founderData['email'], FILTER_VALIDATE_EMAIL)) {
                    $founderData['company_id'] = $company->id;
                    try {
                        $user = User::updateOrCreate(
                            ['email' => $founderData['email']],
                            array_merge($founderData, [
                                'password' => bcrypt('password'),
                            ])
                        );
                    } catch (\Exception $e) {
                        $skippedRows[] = "Row " . ($index + 2) . ": Failed to create/update founder: " . $e->getMessage();
                        Log::warning('Founder creation failed', [
                            'row_number' => $index + 2,
                            'error' => $e->getMessage(),
                            'founderData' => $founderData
                        ]);
                    }
                } else {
                    Log::warning('No valid founder email', [
                        'row_number' => $index + 2,
                        'founderData' => $founderData
                    ]);
                }

                $importedCount++;
            }

            Storage::disk('public')->delete($filePath);
            session()->forget('csv_file_path');

            DB::commit();

            $response = ['success' => true, 'imported' => $importedCount];
            if (!empty($skippedRows)) {
                $response['warnings'] = $skippedRows;
            }


            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mappings' => $request->input('mappings'),
                'data_sample' => array_slice($request->input('data', []), 0, 2)
            ]);
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