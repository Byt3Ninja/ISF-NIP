<?php

namespace App\Http\Controllers;

use App\User;
use App\Category;
use App\Assessment;
use App\SubCategory;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assessments = Assessment::with('user');
        $users = User::role('Registered')->get();
        return view('admin.assessment', compact('assessments', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function assessment()
    {
        $categories = Category::where('category_type', 'Assesment')->get();
        $subcategories = SubCategory::where('Type', 'Assesment')->get();
        return view('admin/clients/assessment/assessment',compact('categories','subcategories'));
    }




}
