<?php

namespace App\Http\Controllers;

use App\User;
use App\Weight;
use App\Company;
use App\Category;
use App\UserScore;
use App\Assessment;
use App\SubCategory;
use App\QuestionsBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assessments = Assessment::with('user')->get();
        $users = User::role('Registered')->get();
        return view('admin.Assessment.assessment', compact('assessments', 'users'));
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
       
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // يجب أن يكون موجودًا في جدول المستخدمين
            'start_date' => 'required|date', // تاريخ صالح وأقل أو يساوي end_date
        ]);
        
        $userid = $request->user_id;
        $l = app()->getLocale();
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
                    'start_date' => $request->start_date,
                    'is_published' => 0,
                ]);

                $assessmentId = $assessment->id;
                
                $questions = QuestionsBank::where('group', 'assessment')->get();

                if($company->stage == null){
                    $assessment = Assessment::find($assessmentId);
                    $assessment->delete();
                    return redirect()->back()->with(['class'=>'danger', 'message'=>__('NotStage')]);
                }

                foreach($questions as $question){
                    $weight = Weight::where('stage_id', $company->stage)->where('category_id', $question->ascategory_id)->where('subcategory_id', $question->subcategory_id)->first();
                   
                    $userscoreadded = UserScore::create([
                        'user_id' => $userid,
                        'category_id' => $question->ascategory_id,
                        'subcategory_id' => $question->subcategory_id,
                        'stage_id' => $company->stage,
                        'weight' => $weight->weight,
                        'assessment_id' => $assessmentId,
                        'QuestionBank' => $question->question,
                        'explanation_QA_ar' => $question->explanation_QA_ar,
                        'explanation_QA_en' => $question->explanation_QA_en,
                        'Answers' => $question->answers,
                        'company_id' => $companyid,
                    ]);
                }


                return redirect()->back()->with('class', 'success')->with('message', 'Assessment_Created_successfully');
            }else{
                return redirect()->back()->with(['class'=>'danger', 'message'=>__('YouHaveNotCompany')]);
            }

            }else{
                return redirect()->back()->with(['class'=>'danger', 'message'=>__('AlreadyHaveAssessment')]);
            }

        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
        if (Auth::user()->hasRole('Admin')) {
            
            $l = app()->getLocale();
            
            $assessment_user = Assessment::where('id', $id)->first();
             
            if($assessment_user != null){
             $categories = Category::where('category_type', 'Assesment')->with('subcategory')->get();
    
            $user_scores = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
            ->where('user_id', $assessment_user->user_id)
            ->where('assessment_id', $assessment_user->id)->get();
         
            return view('admin/Assessment/analysis-assessment', compact('user_scores','categories','assessment_user'));
            }else{
               
                $user_scores = [];
                $categories = Category::where('category_type', 'Assesment')->with('subcategory')->get();
                return view('admin/clients/assessment/analysis-assessment', compact('user_scores','categories'));
            }
    
            }else{
                return redirect()->route('dashboard')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]);
            }
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
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // يجب أن يكون موجودًا في جدول المستخدمين
            'start_date' => 'required|date', // تاريخ صالح وأكبر أو يساوي start_date
        ]);
        
        $assessment = Assessment::find($id);
        $assessment->user_id = $request->user_id;
        $assessment->start_date = $request->start_date;
        $assessment->end_date = $request->end_date;
        $assessment->save();

        return redirect()->back()->with('class', 'success')->with('message', 'Assessment_updated_successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $assessment = Assessment::find($id);
        $assessment->delete();

        $user_scores = UserScore::where('assessment_id', $id)->get();
        foreach ($user_scores as $user_score) {
            $user_score->delete();
        }

        return redirect()->back()->with('class', 'success')->with('message', 'Assessment_deleted_successfully');
    }

    public function assessment(Request $request)
    {
        
        if (Auth::user()->hasRole('Registered')) {
        $l = app()->getLocale();
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();

        if($assessment_user != null){

         $categories = Category::where('category_type', 'Assesment')->with(['subcategory', 'question'])->get();

         $user_scores = $categories->map(function ($category) use ($assessment_user) {
             $questions = $category->question->where('assessment_id', $assessment_user->id);
             $subcategories = $category->subcategory;
             $subcategories = $subcategories->map(function ($subcategory) use ($questions, $assessment_user) {
                 $subcategory->questions = $questions->where('subcategory_id', $subcategory->id)->where('assessment_id', $assessment_user->id);
                 return $subcategory;
             });
             $category->subcategories = $subcategories;
             return $category;
         });

    // استخراج الإجابات
    $answers = $user_scores->pluck('Answers')->map(function ($answer) {
        return json_decode($answer, true);
    })->filter()->toArray();

    $countanswered = $user_scores->whereNotNull('Result_assessment')->count();
    $countNotanswered = $user_scores->whereNull('Result_assessment')->count();
    $totalQuestions = $countanswered + $countNotanswered;

    $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
         
    
        }else{
            return redirect()->route('analysis-assessment');
        }
        

        return view('admin/clients/assessment/assessment',compact('user_scores','answers', 'assessment_user'));

    }else{
        return redirect()->route('dashboard')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]);
    }
    }

public function MakeAssessment()
    {
        if (Auth::user()->hasRole('Registered')) {
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
              
                if($company == null){
                    return redirect()->route('my_company')->with(['class'=>'danger', 'message'=>__('YouHaveNotCompany')]);
                }
                $title = $company->{'name_'. $l } ." - ". now()->format('Y-m-d H:i:s');
                
                $assessment = Assessment::create([
                    'user_id' => $userid,
                    'title' => $title,
                    'start_date' => now(),
                    'is_published' => 0,
                ]);

                $assessmentId = $assessment->id;
                
                $questions = QuestionsBank::where('group', 'assessment')->get();

                if($company->stage == null && !is_numeric($company->stage) && !empty($company->stage)){
                    $assessment = Assessment::find($assessmentId);
                    $assessment->delete();
                    return redirect()->route('my_company')->with(['class'=>'danger', 'msg'=>__('ChooseYourStageFirst')]);
                }

                foreach($questions as $question){
                    $weight = Weight::where('stage_id', $company->stage)->where('category_id', $question->ascategory_id)->where('subcategory_id', $question->subcategory_id)->first();
                    if($weight != null){
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
                }else{
                    $assessment = Assessment::find($assessmentId);
                    $assessment->delete();
                    return redirect()->route('my_company')->with(['class'=>'danger', 'msg'=>__('ChooseYourStageFirst')]);
                }
                }


                return redirect()->route('question-assessment')->with(['class'=>'success', 'message'=>__('AssessmentCreated')]);
            }else{
                return redirect()->back()->with(['class'=>'danger', 'message'=>__('YouHaveNotCompany')]);
            }

        }else{
            return redirect()->route('question-assessment')->with(['class'=>'danger', 'message'=>__('AlreadyHaveAssessment')]);
        }
    }else{
        return redirect()->route('dashboard')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]);
    }
    }


    public function analysisAssessment()
    {
        if (Auth::user()->hasRole('Registered')) {
        $l = app()->getLocale();
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();
        if($assessment_user != null){
         $categories = Category::where('category_type', 'Assesment')->with('subcategory')->get();

        $user_scores = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
        ->where('user_id', $userid)
        ->where('assessment_id', $assessment_user->id)->get();
        
        return view('admin/clients/assessment/analysis-assessment', compact('user_scores','categories'));
        }else{
            $user_scores = [];
            $categories = Category::where('category_type', 'Assesment')->with('subcategory')->get();
            return view('admin/clients/assessment/analysis-assessment', compact('user_scores','categories'));
        }

        }else{
            return redirect()->route('dashboard')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]);
        }
    }


    public function historyAssessment()
    {
        if (Auth::user()->hasRole('Registered')) {
            $l = app()->getLocale();
            $userid = auth()->user()->id;
            $assessmentsopen = Assessment::where('user_id', $userid)->whereNull('end_date')->get();
            $assessments = Assessment::where('user_id', $userid)->whereNotNull('end_date')->get();
            
            return view('admin/clients/assessment/assessment-history', compact('assessmentsopen', 'assessments'));
            
    
            }else{
                return redirect()->route('dashboard')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]);
            }

        
    }

    public function AnswerAssessment(Request $request)
    {
        $update = [];
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();

        if($assessment_user != null){
        // تحقق من البيانات القادمة
        $validatedData = $request->validate([
            'questionId' => 'required|integer|exists:user_scores,id',
            'answer' => 'required|string',
        ]);
        
       
        // هنا من الممكن تنفيذ منطق تحديث الإجابة
        try {
            // افترض أن لديك موديل `Answer` لحفظ الإجابة
            $user_score = UserScore::find($request->questionId);

            $data = json_decode($user_score->Answers, true);
        $result = collect($data)->get($request->answer);

        $score = $result['score'];
        $text = $result['text'];
        $weight = $user_score->weight;
        $weighted_score = $score * $weight / 100;
            $user_score->score = $score;
            $user_score->Result_assessment = $text;
            $user_score->weighted_score = $weighted_score;
            $user_score->save();

            $assessment_user = UserScore::where('assessment_id', $user_score->assessment_id)->where('score', '=', '0')->count();
            if($assessment_user == 0){
                $assessment_user = Assessment::where('id', $user_score->assessment_id)->first();
                $assessment_user->end_date = now();
                $assessment_user->save();
                return response()->json(['success' => true, 'message' => '1']);
            }
            

           
                $l = app()->getLocale();
                $userid = auth()->user()->id;
                $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();
        
                if($assessment_user != null){
        
                 $categories = Category::where('category_type', 'Assesment')->with(['subcategory', 'question'])->get();
        
                 $user_scores = $categories->map(function ($category) use ($assessment_user) {
                    // تصفية الأسئلة بناءً على التقييم
                    $questions = $category->question->where('assessment_id', $assessment_user->id);
                
                    // حساب الأجوبة التي تم الرد عليها والتي لم يتم الرد عليها على مستوى الفئة
                    $category->contAnswered = $questions->where('score', '!=', '0')->count();
                    $category->contNotAnswered = $questions->where('score', '=', '0')->count();
                
                    // معالجة التصنيفات الفرعية
                    $subcategories = $category->subcategory->map(function ($subcategory) use ($questions) {
                        // تصفية الأسئلة حسب التصنيف الفرعي
                        $subcategory_questions = $questions->where('subcategory_id', $subcategory->id);
                
                        // حساب الأجوبة التي تم الرد عليها والتي لم يتم الرد عليها على مستوى التصنيف الفرعي
                        $subcategory->contAnswered = $subcategory_questions->where('score', '!=', '0')->count();
                        $subcategory->contNotAnswered = $subcategory_questions->where('score', '=', '0')->count();
                
                        // إرفاق الأسئلة المفلترة
                        $subcategory->questions = $subcategory_questions;
                
                        return $subcategory;
                    });
                
                    // إرفاق التصنيفات الفرعية المفلترة
                    $category->subcategories = $subcategories;
                
                    return $category;
               
                 });
        
            // استخراج الإجابات
            $answers = $user_scores->pluck('Answers')->map(function ($answer) {
                return json_decode($answer, true);
            })->filter()->toArray();
        
            $countanswered = $user_scores->whereNotNull('Result_assessment')->count();
            $countNotanswered = $user_scores->whereNull('Result_assessment')->count();
            $totalQuestions = $countanswered + $countNotanswered;
        
            $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
                 
            
                }else{
                    return redirect()->route('analysis-assessment');
                }


                $countanswered = UserScore::where('assessment_id', $user_score->assessment_id)->where('score', '!=', '0')->count();
                $countNotanswered = UserScore::where('assessment_id', $user_score->assessment_id)->where('score', '=', '0')->count();
                $totalQuestions = $countanswered + $countNotanswered;
                $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
                $update['countanswered'] = $countanswered;
                $update['countNotanswered'] = $countNotanswered;
                $update['totalQuestions'] = $totalQuestions;
                $update['progress'] = $progress;
                
                return response()->json(['success' => true,
                 'message' => 'تم تحديث الإجابة بنجاح',
                 'user_scores' => $user_scores,
                 'answers' => $answers,
                 'assessment_user' => $assessment_user,
            
            ]);
            
            

            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الإجابة']);
        }
    }else{
        return redirect()->route('analysis-assessment');
    }


    }
    
    public function assessmentAdmin($id)
    {
        
        if (Auth::user()->hasRole('Admin')) {
            
        $l = app()->getLocale();
       
        $assessment_user = Assessment::where('id', $id)->first();

        if($assessment_user != null){

         $categories = Category::where('category_type', 'Assesment')->with(['subcategory', 'question'])->get();

         $user_scores = $categories->map(function ($category) use ($assessment_user) {
             $questions = $category->question->where('assessment_id', $assessment_user->id);
             $subcategories = $category->subcategory;
             $subcategories = $subcategories->map(function ($subcategory) use ($questions, $assessment_user) {
                 $subcategory->questions = $questions->where('subcategory_id', $subcategory->id)->where('assessment_id', $assessment_user->id);
                 return $subcategory;
             });
             $category->subcategories = $subcategories;
             return $category;
         });

    // استخراج الإجابات
    $answers = $user_scores->pluck('Answers')->map(function ($answer) {
        return json_decode($answer, true);
    })->filter()->toArray();

    $countanswered = $user_scores->whereNotNull('Result_assessment')->count();
    $countNotanswered = $user_scores->whereNull('Result_assessment')->count();
    $totalQuestions = $countanswered + $countNotanswered;

    $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
         
    
        }else{
            return redirect()->route('analysis-assessment');
        }
        

        return view('admin/Assessment/assessment-history',compact('user_scores','answers', 'assessment_user'));

    }else{
        return redirect()->route('dashboard')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]);
    }
    }



}
