<?php

namespace App\Http\Controllers;

use App\Question;
use App\AnswerOption;
use Illuminate\Http\Request;

class AnswerOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $answerOptions = AnswerOption::with('question')->get();
        $questions = Question::all();
        return view('admin.questions.answer_option', compact('answerOptions','questions'));
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
        
        $request->validate([
            'score' => 'required',
            'question_id' => 'required|exists:questions,id',
            'option_text_ar' => 'required|string',
            'option_text_en' => 'required|string',
        ]);
        
        $question = new AnswerOption();
        $question->option_text_ar = $request->option_text_ar;
        $question->option_text_en = $request->option_text_en;
        $question->option_info_ar = $request->option_info_ar;
        $question->option_info_en = $request->option_info_en;
        $question->Score = $request->score;
        $question->question_id = $request->question_id;
        $question->save();

        return redirect()->route('answerOption.index')->with('class', 'success')->with('message', 'Answer_created_successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(AnswerOption $answerOption)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnswerOption $answerOption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnswerOption $answerOption)
    {
        
        $request->validate([
            'score' => 'required',
            'question_id' => 'required|exists:questions,id',
            'option_text_ar' => 'required|string',
            'option_text_en' => 'required|string',
        ]);
        
        $question = AnswerOption::findOrFail($answerOption->id);
        $question->option_text_ar = $request->option_text_ar;
        $question->option_text_en = $request->option_text_en;
        $question->option_info_ar = $request->option_info_ar;
        $question->option_info_en = $request->option_info_en;
        $question->Score = $request->score;
        $question->question_id = $request->question_id;
        $question->save();

        return redirect()->route('answerOption.index')->with('class', 'success')->with('message', 'Answer_updated_successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnswerOption $answerOption)
    {
        $answerOption->delete();

        return redirect()->route('answer_options.index')->with('success', 'Answer Option deleted successfully.');
    }
}
