<?php

namespace App\Http\Controllers;

use App\Question;
use App\Category;
use App\SubCategory;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = Question::with('category', 'subcategory')->get();
        $categories = Category::where('category_type', 'Assesment')->get();
        $subcategories = SubCategory::where('Type', 'Assesment')->get();
       
        return view('admin.questions.index', compact('questions','categories','subcategories'));
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
        abort_unless(auth()->user()->ableTo('questions.store'), 403);
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'questions_title_ar' => 'required|string',
            'questions_title_en' => 'required|string',
            'question_info_ar' => 'required|string',
            'question_info_en' => 'required|string',
        ]);

        $question = new Question();
        $question->text_ar = $request->questions_title_ar;
        $question->text_en = $request->questions_title_en;
        $question->info_ar = $request->question_info_ar;
        $question->info_en = $request->question_info_en;
        $question->category_id = $request->category_id;
        $question->subcategory_id = $request->subcategory_id;
        $question->save();

        return redirect()->route('questions.index')->with('class', 'success')->with('message', 'Question_created_successfully');
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
        abort_unless(auth()->user()->ableTo('questions.update'), 403);
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id',
            'questions_title_ar' => 'required|string',
            'questions_title_en' => 'required|string',
            'question_info_ar' => 'required|string',
            'question_info_en' => 'required|string',
        ]);

        $question = Question::findOrFail($id);
        $question->text_ar = $request->questions_title_ar;
        $question->text_en = $request->questions_title_en;
        $question->info_ar = $request->question_info_ar;
        $question->info_en = $request->question_info_en;
        $question->category_id = $request->category_id;
        $question->subcategory_id = $request->subcategory_id;
        $question->save();

        return redirect()->route('questions.index')->with('class', 'success')->with('message', 'Question_updated_successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function category()
    {
      $states = Subcategory::where('category_id', request()->input('category_id',0))->pluck(lang('name')." as name",'id');
      return response()->json($states);
    }
}
