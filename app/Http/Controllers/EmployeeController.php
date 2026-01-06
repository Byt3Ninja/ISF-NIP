<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->middleware('auth');
        $this->employeeService = $employeeService;
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'employment_type' => 'required|in:full-time,part-time,contractor,intern',
            'hiring_date' => 'required|date',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240',
            'remove_documents' => 'nullable|array',
            'remove_documents.*' => 'integer'
        ]);

        $data['company_id'] = Auth::user()->company_id;
        $documents = $request->file('documents', []);
        
        try {
            $employee = $this->employeeService->createEmployee($data, $documents);
            
            return response()->json([
                'success' => true,
                'message' => __('Employee added successfully.'),
                'data' => $employee->load('documents')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        // Verify ownership
        if ($employee->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $data = $request->validate([
            'full_name' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'employment_type' => 'required|in:full-time,part-time,contractor,intern',
            'hiring_date' => 'required|date',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240',
            'remove_documents' => 'nullable|array',
            'remove_documents.*' => 'integer'
        ]);

        $documents = $request->file('documents', []);
        
        try {
            $this->employeeService->updateEmployee($employee, $data, $documents);
            
            return response()->json([
                'success' => true,
                'message' => __('Employee updated successfully.'),
                'data' => $employee->fresh()->load('documents')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        // Verify ownership
        if ($employee->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        try {
            $this->employeeService->deleteEmployee($employee->id);
            
            return response()->json([
                'success' => true,
                'message' => __('Employee deleted successfully.')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all employees for the authenticated user's company
     */
    public function index()
    {
        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $employees->load('documents')
        ]);
    }

    /**
     * Get a single employee
     */
    public function show(Employee $employee)
    {
        // Verify ownership
        if ($employee->company_id !== Auth::user()->company_id) {
            abort(403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }
}
