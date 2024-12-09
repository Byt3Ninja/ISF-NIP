<?php

namespace App\Http\Controllers;

use App\QuestionsBank;
use App\User;
use App\Company;
use App\Category;
use App\UserScore;
use App\Assessment;
use App\SubCategory;
use App\Weight;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assessments = Assessment::with('user')->get();
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

    public function assessment(Request $request)
    {
        
         
        $l = app()->getLocale();
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();

         $categories = Category::where('category_type', 'Assesment')->with(['subcategory', 'question'])->get();

         $user_scores = $categories->map(function ($category) {
             $questions = $category->question;
             $subcategories = $category->subcategory;
             $subcategories = $subcategories->map(function ($subcategory) use ($questions) {
                 $subcategory->questions = $questions->where('subcategory_id', $subcategory->id);
                 return $subcategory;
             });
             $category->subcategories = $subcategories;
             return $category;
         });
        
        
        // $subcategories1 = SubCategory::where('Type', 'Assesment');
        // if ($request->has('category')) {
        //     $subcategories1->where('category_id', $request->category);
        // }else{
        //     $category4 = Category::where('category_type', 'Assesment')->first();
        //     $subcategories1->where('category_id', $category4->id);
        // }
        // $subcategories = $subcategories1->get();


        if($assessment_user != null){
        //     $query = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
        //     ->where('user_id', $userid)
        //     ->where('assessment_id', $assessment_user->id);
        // // إضافة فلترة الفئة إذا تم تمرير category_id
        // if ($request->has('category')) {
        //     $query->where('category_id', $request->category);
        // }else{
        //     $category2 = Category::where('category_type', 'Assesment')->orderBy('id', 'asc')->first();
        //     $query->where('category_id', $category2->id);
        // }
        // // إضافة فلترة الفئة الفرعية إذا تم تمرير subcategory_id
        // if ($request->has('subcategory')) {
        //     $query->where('subcategory_id', $request->subcategory);
        // }else{
        //     $category3 = Category::where('category_type', 'Assesment')->orderBy('id', 'asc')->first();
        //     $subcategories21 = SubCategory::where('Type', 'Assesment');
        //     $subcategories21->where('category_id', $category3->id)->orderBy('id', 'asc');
        //     $subcategories2 = $subcategories21->first();
        //     $query->where('subcategory_id', $subcategories2->id);
        // }
        // // تنفيذ الاستعلام
        // $user_scores = $query->get();


    // استخراج الإجابات
    $answers = $user_scores->pluck('Answers')->map(function ($answer) {
        return json_decode($answer, true);
    })->filter()->toArray();

    $countanswered = $user_scores->whereNotNull('Result_assessment')->count();
    $countNotanswered = $user_scores->whereNull('Result_assessment')->count();
    $totalQuestions = $countanswered + $countNotanswered;

    $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
    
    // return [
    //     'title' => $user_scores->first()->category_name_ar ?? 'N/A',
    //     'progress' => round($progress, 2),
    //     'answered' => $countanswered,
    //     'status' => $progress == 100 ? 'completed' : ($progress > 0 ? 'in-progress' : 'not-started'),
    // ];
            
        
           

            
    
        }else{
            return redirect()->route('analysis-assessment');
        }
        

        return view('admin/clients/assessment/assessment',compact('user_scores','answers'));
    }

public function MakeAssessment()
    {
        $l = app()->getLocale();
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();
        
        if($assessment_user != null){
            $user_score = UserScore::where('user_id', $userid)->where('assessment_id', $assessment_user->id)->get();
        }else{
            $user_score = '';
        }
        
        if(empty($user_score)){
            $user = User::find($userid);
            if($user->HaveCompany != null){
                $companyid = $user->company_id;
                $company = Company::find($companyid);
                $title = $company->{'name_'. $l } ." - ". now()->format('Y-m-d H:i:s');
               
                $assessment = Assessment::create([
                    'user_id' => $userid,
                    'title' => $title,
                    'start_date' => now(),
                    'is_published' => 0,
                ]);

                $assessmentId = $assessment->id;
                
                $questions = QuestionsBank::where('group', 'assessment')->get();
                foreach($questions as $question){
                    $weight = Weight::where('stage_id', $company->stage)->where('category_id', $question->ascategory_id)->where('subcategory_id', $question->subcategory_id)->first();
                    
                    $userscoreadded = UserScore::create([
                        'user_id' => $userid,
                        'category_id' => $question->ascategory_id,
                        'subcategory_id' => $question->subcategory_id,
                        'stage_id' => $company->stage,
                        'weight' => $weight->weight,
                        'assessment_id' => $assessmentId,
                        'QuestionBank' => $question->question, // اسم السؤال نفسه
                        'explanation_QA_ar' => $question->explanation_QA_ar, 
                        'explanation_QA_en' => $question->explanation_QA_en, 
                        'Answers' => $question->answers, 
                        'company_id' => $companyid,
                    ]);
                }


                return redirect()->route('question-assessment')->with(['class'=>'success', 'message'=>__('AssessmentCreated')]);
            }else{
                return redirect()->back()->with(['class'=>'danger', 'message'=>__('YouHaveNotCompany')]);
            }

        }else{
            return redirect()->route('question-assessment')->with(['class'=>'danger', 'message'=>__('AlreadyHaveAssessment')]);
        }
    }


    public function analysisAssessment()
    {
        $l = app()->getLocale();
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();

         $categories = Category::where('category_type', 'Assesment')->with('subcategory')->get();

        $user_scores = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
        ->where('user_id', $userid)
        ->where('assessment_id', $assessment_user->id)->get();
        return view('admin/clients/assessment/analysis-assessment', compact('user_scores','categories'));
    }








}
