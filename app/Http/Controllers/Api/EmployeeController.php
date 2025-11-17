<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
        $this->middleware('auth:api');
    }

    /**
     * Get all employees for the authenticated user's company
     */
    public function index(): JsonResponse
    {
        $companyId = Auth::user()->company_id;
        $employees = $this->employeeService->getCompanyEmployees($companyId);
        return response()->json($employees);
    }

    /**
     * Store a new employee
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->only([
                'full_name',
                'job_title',
                'gender',
                'employment_type',
                'hiring_date'
            ]);
            
            $data['company_id'] = Auth::user()->company_id;
            
            $documents = $request->file('documents', []);
            
            $employee = $this->employeeService->createEmployee($data, $documents);
            
            return response()->json([
                'message' => __('Employee added successfully.'),
                'data' => $employee
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => __('Validation Error'),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an employee
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->employeeService->deleteEmployee($id);
            
            return response()->json([
                'message' => __('Employee deleted successfully.')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
