<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use App\Weight;
use App\Company;
use App\Category;
use App\UserScore;
use App\Assessment;
use App\SubCategory;
use App\QuestionsBank;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
	public function index(Request $request)
	{
		$_user = Auth::user();
		$assessments = Assessment::with(['user', 'user.company']);

		// فلترة حسب startup_id
		if ($request->startup_id && $request->startup_id != 0) {
			$founder = User::where('company_id', $request->startup_id)->get();
			$assessments->whereIn('user_id', $founder->pluck('id'));
		}

		// فلترة حسب founder_id
		if ($request->founder_id && $request->founder_id != 0) {
			$assessments->where('user_id', $request->founder_id);
		}

		// فلترة حسب التاريخ (من)
		if ($request->datefrom && !empty($request->datefrom)) {
			$assessments->whereDate('created_at', '>=', $request->datefrom);
		}

		// فلترة حسب التاريخ (إلى)
		if ($request->dateto && !empty($request->dateto)) {
			$assessments->whereDate('created_at', '<=', $request->dateto);
		}

		// الحصول على النتائج بعد التصفية
		$assessments = $assessments->get();

		$users = Role::where('default_for', 'registered')->with(['users', 'users.roles', 'users.organizations', 'users.company', 'users.company.cycledata'])->get();
		$users = $users->flatMap(function ($role) {
			return $role->users;
		})->values();
		if( $_user->hasRole('organizationManager') || $_user->hasRole('subadmin') )
		{
			$OrgIds = $_user->organizations_ids;
			if( !is_array( $OrgIds ) ) { $OrgIds = [$OrgIds]; }
			/* $users = $users->whereHas('company.cycledata', function ($q) use ($OrgIds) {
				$q->where('organization_id', $OrgIds);
			}); */
			$users = $users->filter(function ($user) use ($OrgIds) {
				return array_intersect( $OrgIds, $user->organizations_ids );
			})->values();
		}

		$startups = Company::all();
		$founders = $users;
		return view('admin.Assessment.assessment', compact('assessments', 'users', 'startups', 'founders'));
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

        // GV-9566: "today" is the platform zone's date, not the server's.
        if( \Carbon\Carbon::parse($request->start_date)->format('Y-m-d') < TimezoneService::platformToday() )
        {
            return redirect()->back()->with(['class'=>'danger', 'message'=>__('admin.StartDateCannotBeInPast'), 'message_resolved' => true]);
        }

        $userid = $request->user_id;
        $l = app()->getLocale();
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();
        
        $user_score = '';
        if( $assessment_user != null) { $user_score = UserScore::where('user_id', $userid)->where('assessment_id', $assessment_user->id)->get(); }
        
        if(empty($user_score))
        {
            $user = User::find($userid);
            if( $user->HaveCompany != null )
            {
                $companyid = $user->company_id;
                $company = Company::find($companyid);
                
                $title = $company?->{'name_'. $l } ." - ". now()->format('Y-m-d H:i:s');

                if( $company?->stage == null || !is_numeric($company->stage) )
                {
                    $founderName = trim($user->{'first_name_'.$l} .' '. $user->{'last_name_'.$l});

                    return redirect()->back()->with([
                        'class' => 'danger',
                        'message' => __('admin.ChooseStageForCompany', [
                            'founder' => $founderName,
                        ]),
                        'message_resolved' => true,
                    ]);
                }

                $company_weights = Weight::where('stage_id', $company->stage)->get();
                $weightsByKey = $company_weights->keyBy(fn($w) => $w->category_id . '-' . $w->subcategory_id);
                $questions = QuestionsBank::where('group', 'assessment')->whereIn('ascategory_id', $company_weights->pluck('category_id'))->get();

                if( $questions->isEmpty() )
                {
                    return redirect()->back()->with(['class'=>'danger', 'message'=>__('please_correct_stage_or_category_or_subcategory_in_your_company')]);
                }

                try {
                    DB::beginTransaction();

                    $assessment = Assessment::create([
                        'user_id' => $userid,
                        'title' => $title,
                        'start_date' => $request->start_date,
                        'is_published' => 0,
                    ]);

                    $userScores = [];
                    foreach( $questions as $question )
                    {
                        $weight = $weightsByKey->get($question->ascategory_id . '-' . $question->subcategory_id);

                        if( $weight == null ){
                            DB::rollBack();
                            return redirect()->back()->with(['class'=>'danger', 'message'=>__('please_correct_stage_or_category_or_subcategory_in_your_company')]);
                        }

                        $userScores[] = [
                            'user_id' => $userid,
                            'category_id' => $question->ascategory_id,
                            'subcategory_id' => $question->subcategory_id,
                            'stage_id' => $company->stage,
                            'weight' => $weight->weight,
                            'assessment_id' => $assessment->id,
                            'QuestionBank' => $question->question,
                            'explanation_QA_ar' => $question->explanation_QA_ar,
                            'explanation_QA_en' => $question->explanation_QA_en,
                            'Answers' => $question->answers,
                            'company_id' => $companyid,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    UserScore::insert($userScores);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error creating assessment: ' . $e->getMessage());
                    return redirect()->back()->with(['class'=>'danger', 'message'=>__('Failed to create assessment. Please try again.')]);
                }

                return redirect()->back()->with('class', 'success')->with('message', 'Assessment_Created_successfully');
            } else { return redirect()->back()->with(['class'=>'danger', 'message'=>__('YouHaveNotCompany')]); }

        } else { return redirect()->back()->with(['class'=>'danger', 'message'=>__('AlreadyHaveAssessment')]); }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (Auth::user()->hasAnyRole(['Admin', 'organizationManager', 'subadmin']))
        {
            $l = app()->getLocale();
            $get_assessment = Assessment::where('id', $id)->first();
            $assessment_user = $get_assessment;
            $assessment_data = $this->loadAssessmentData( $get_assessment?->user_id ?? null, $id );

            $user_scores = $assessment_data['user_scores'];
            $categories = $assessment_data['categories'];
            $Analysis_user_scores = $assessment_data['Analysis_user_scores'];
            $weightedSubCategories = $assessment_data['weightedSubCategories'];
            $weightedPreviousSubCategories = $assessment_data['weightedPreviousSubCategories'];

            return view('admin/Assessment/analysis-assessment', compact('assessment_user', 'user_scores', 'categories', 'Analysis_user_scores', 'weightedSubCategories', 'weightedPreviousSubCategories'));
        } else { return redirect()->route('core.dashboard_view')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]); }
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
        foreach ($user_scores as $user_score) { $user_score->delete(); }

        return redirect()->back()->with('class', 'success')->with('message', 'Assessment_deleted_successfully');
    }

    public function assessment(Request $request)
    {
        if( Auth::user()->hasRole('Registered') ){
            $l = app()->getLocale();
            $userid = auth()->user()->id;
            $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();

            if( $assessment_user != null )
            {
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
            } else { return redirect()->route('assessments.results_analysis_view'); }
            return view('admin/clients/assessment/assessment',compact('user_scores','answers', 'assessment_user'));
        } else { return redirect()->route('core.dashboard_view')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]); }
    }

    /**
     * Check whether the given user may start a new assessment.
     *
     * Single source of truth for both the pre-flight check the view uses to
     * enable/disable the "start" button and the guard MakeAssessment() applies
     * to the POST. Failures carry a 'reason' the view can map to helper text,
     * plus the 'message'/'redirect'/'class' the POST path needs.
     */
    protected function canStartAssessment($user)
    {
        // Check if user has a company
        if ($user->HaveCompany === null) {
            return [
                'can_proceed' => false,
                'reason' => 'no_company',
                'redirect' => route('company.profile_view_mine'),
                'message' => __('admin.YouHaveNotCompany'),
                'class' => 'danger'
            ];
        }

        // Check if company has a stage set
        $company = Company::find($user->company_id);
        if ($company === null) {
            return [
                'can_proceed' => false,
                'reason' => 'no_company',
                'redirect' => route('company.profile_view_mine'),
                'message' => __('admin.YouHaveNotCompany'),
                'class' => 'danger'
            ];
        }

        if ($company->stage === null || !is_numeric($company->stage) || empty($company->stage)) {
            return [
                'can_proceed' => false,
                'reason' => 'no_stage',
                'redirect' => route('company.profile_view_mine'),
                'message' => __('admin.ChooseYourStageFirst'),
                'class' => 'danger'
            ];
        }

        // Check if user already has an active assessment
        $activeAssessment = Assessment::where('user_id', $user->id)
            ->whereNull('end_date')
            ->first();

        if ($activeAssessment) {
            return [
                'can_proceed' => false,
                'reason' => 'assessment_in_progress',
                'redirect' => route('assessments.take'),
                'message' => __('admin.AlreadyHaveAssessment'),
                'class' => 'warning'
            ];
        }

        // Every question we are about to copy needs a matching weight row for
        // this stage. Without one MakeAssessment() would roll back mid-way, so
        // detect it here instead and keep the button disabled.
        $companyWeights = Weight::where('stage_id', $company->stage)->get();
        $questions = QuestionsBank::where('group', 'assessment')
            ->whereIn('ascategory_id', $companyWeights->pluck('category_id'))
            ->get();

        if ($questions->isEmpty() || $this->hasMissingWeights($questions, $companyWeights)) {
            return [
                'can_proceed' => false,
                'reason' => 'not_configured',
                'redirect' => route('assessments.results_analysis_view'),
                'message' => __('admin.AssessmentNotConfiguredForStage'),
                'class' => 'danger'
            ];
        }

        return [
            'can_proceed' => true,
            'company' => $company,
            'company_weights' => $companyWeights,
            'questions' => $questions,
        ];
    }

    /**
     * True when any question lacks a weight for its category/subcategory pair.
     */
    protected function hasMissingWeights($questions, $companyWeights)
    {
        foreach ($questions as $question) {
            $weight = $companyWeights
                ->where('category_id', $question->ascategory_id)
                ->where('subcategory_id', $question->subcategory_id)
                ->first();

            if (!$weight) {
                return true;
            }
        }

        return false;
    }

    public function MakeAssessment()
    {
        if (!Auth::user()->hasRole('Registered')) {
            return redirect()->route('core.dashboard_view')
                ->with(['class' => 'danger', 'message' => __('You are not authorized to perform this action.')]);
        }

        $user = Auth::user();
        $check = $this->canStartAssessment($user);

        if (!$check['can_proceed']) {
            // For AJAX requests, return JSON response
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $check['message'],
                    'redirect' => $check['redirect']
                ], 422);
            }
            // For regular requests, redirect with message
            return redirect($check['redirect'])
                ->with(['class' => $check['class'], 'message' => $check['message']]);
        }

        $company = $check['company'];
        $company_weights = $check['company_weights'];
        $questions = $check['questions'];
        $l = app()->getLocale();
        
        try {
            DB::beginTransaction();

            // Create new assessment
            $assessment = Assessment::create([
                'user_id' => $user->id,
                'title' => $company->{'name_'. $l} . " - " . now()->format('Y-m-d H:i:s'),
                'start_date' => now(),
                'is_published' => 0,
            ]);

            // Questions and weights were loaded and validated by canStartAssessment().
            $userScores = [];

            // Prepare user scores
            foreach ($questions as $question) {
                
                $weight = $company_weights
                    ->where('category_id', $question->ascategory_id)
                    ->where('subcategory_id', $question->subcategory_id)
                    ->first();


                if (!$weight) {
                    DB::rollBack();

                    $message = __('admin.AssessmentNotConfiguredForStage');

                    if (request()->ajax() || request()->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $message
                        ], 500);
                    }

                    return redirect()->route('assessments.results_analysis_view')
                        ->with(['class' => 'danger', 'message' => $message]);
                }

                $userScores[] = [
                    'user_id' => $user->id,
                    'category_id' => $question->ascategory_id,
                    'subcategory_id' => $question->subcategory_id,
                    'stage_id' => $company->stage,
                    'weight' => $weight->weight,
                    'assessment_id' => $assessment->id,
                    'QuestionBank' => $question->question,
                    'explanation_QA_ar' => $question->explanation_QA_ar,
                    'explanation_QA_en' => $question->explanation_QA_en,
                    'Answers' => $question->answers,
                    'company_id' => $company->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
               
            }
                
            // Bulk insert user scores for better performance
            UserScore::insert($userScores);
            DB::commit();

            // For AJAX requests, return JSON response
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('assessments.take'),
                    'message' => __('admin.AssessmentCreated')
                ]);
            }

            // For regular requests, redirect to assessment
            return redirect()->route('assessments.take')
                ->with(['class' => 'success', 'message' => __('admin.AssessmentCreated')]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating assessment: ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.AssessmentCreateFailed')
                ], 500);
            }

            return redirect()->back()
                ->with(['class' => 'danger', 'message' => __('admin.AssessmentCreateFailed')]);
        }
    }

    public function analysisAssessment()
    {
        if (Auth::user()->hasRole('Registered')) {
            $l = app()->getLocale();
            $userid = auth()->user()->id;

            $assessment_data = $this->loadAssessmentData( $userid );
            $user_scores = $assessment_data['user_scores'];
            $categories = $assessment_data['categories'];
            $Analysis_user_scores = $assessment_data['Analysis_user_scores'];
            $weightedSubCategories = $assessment_data['weightedSubCategories'];
            $weightedPreviousSubCategories = $assessment_data['weightedPreviousSubCategories'];

            // Pre-flight the same guard the POST applies, so the view can
            // disable the start button and say what is missing.
            $startCheck = $this->canStartAssessment(Auth::user());

            return view('admin/clients/assessment/analysis-assessment', compact('user_scores', 'categories', 'Analysis_user_scores', 'weightedSubCategories', 'weightedPreviousSubCategories', 'startCheck'));
        } else {
            return redirect()->route('core.dashboard_view')->with(['class' => 'danger', 'success' => __('YouHaveNotCompany')]);
        }
    }

    public function historyAssessment()
    {
        if( Auth::user()->hasRole('Registered') )
        {
            $userid = auth()->user()->id;
            $assessmentsopen = Assessment::where('user_id', $userid)->whereNull('end_date')->orderBy('id')->get();
            $assessments = Assessment::where('user_id', $userid)->whereNotNull('end_date')->orderBy('end_date')->orderBy('id')->get();

            // Total weighted score per assessment, in one query, so the view
            // does not run a UserScore lookup per rendered card.
            $weightedScores = UserScore::whereIn('assessment_id', $assessmentsopen->pluck('id')->merge($assessments->pluck('id')))
                ->groupBy('assessment_id')
                ->selectRaw('assessment_id, SUM(weighted_score) as total')
                ->pluck('total', 'assessment_id');

            // Compare chart: one point per completed assessment, ordered oldest
            // to newest, spanning the real dates of those assessments.
            $historySeries = $assessments->map(function ($assessment) use ($weightedScores) {
                return round((float) ($weightedScores[$assessment->id] ?? 0), 2);
            })->values();

            $historyRangeStart = optional($assessments->first())->start_date
                ?: optional($assessments->first())->created_at;
            $historyRangeEnd = optional($assessments->last())->end_date;

            return view('admin/clients/assessment/assessment-history', compact(
                'assessmentsopen',
                'assessments',
                'weightedScores',
                'historySeries',
                'historyRangeStart',
                'historyRangeEnd'
            ));
        } else { return redirect()->route('core.dashboard_view')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]); }
    }

    public function AnswerAssessment(Request $request)
    {
        $update = [];
        $userid = auth()->user()->id;
        $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();
        if( $assessment_user != null )
        {
            $validatedData = $request->validate([
                'questionId' => 'required|integer|exists:user_scores,id',
                'answer' => 'required|string',
            ]);
       
            try {
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
                if( $assessment_user == 0 )
                {
                    $assessment_user = Assessment::where('id', $user_score->assessment_id)->first();
                    $assessment_user->end_date = now();
                    $assessment_user->save();
                    return response()->json(['success' => true, 'message' => '1']);
                }

                $l = app()->getLocale();
                $userid = auth()->user()->id;
                $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();
        
                if( $assessment_user != null )
                {
                    $categories = Category::where('category_type', 'Assesment')->with(['subcategory', 'question'])->get();
        
                    $user_scores = $categories->map(function ($category) use ($assessment_user) {
                        $questions = $category->question->where('assessment_id', $assessment_user->id);

                        $category->contAnswered = $questions->where('score', '!=', '0')->count();
                        $category->contNotAnswered = $questions->where('score', '=', '0')->count();

                        $subcategories = $category->subcategory->map(function ($subcategory) use ($questions) {
                            $subcategory_questions = $questions->where('subcategory_id', $subcategory->id);
                            $subcategory->contAnswered = $subcategory_questions->where('score', '!=', '0')->count();
                            $subcategory->contNotAnswered = $subcategory_questions->where('score', '=', '0')->count();
                            $subcategory->questions = $subcategory_questions;
                            return $subcategory;
                        });
                        $category->subcategories = $subcategories;
                        return $category;
                    });

                    $answers = $user_scores->pluck('Answers')->map(function ($answer) {
                        return json_decode($answer, true);
                    })->filter()->toArray();

                    $countanswered = $user_scores->whereNotNull('Result_assessment')->count();
                    $countNotanswered = $user_scores->whereNull('Result_assessment')->count();
                    $totalQuestions = $countanswered + $countNotanswered;

                    $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
                } else { return redirect()->route('assessments.results_analysis_view'); }

                $countanswered = UserScore::where('assessment_id', $user_score->assessment_id)->where('score', '!=', '0')->count();
                $countNotanswered = UserScore::where('assessment_id', $user_score->assessment_id)->where('score', '=', '0')->count();
                $totalQuestions = $countanswered + $countNotanswered;
                $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
                $update['countanswered'] = $countanswered;
                $update['countNotanswered'] = $countNotanswered;
                $update['totalQuestions'] = $totalQuestions;
                $update['progress'] = $progress;
                
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث الإجابة بنجاح',
                    'user_scores' => $user_scores,
                    'answers' => $answers,
                    'assessment_user' => $assessment_user,
                ]);
            } catch (\Exception $e) { return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الإجابة']); }
        } else { return redirect()->route('assessments.results_analysis_view'); }
    }
    
    public function assessmentAdmin($id)
    {
        if( Auth::user()->hasRole('Admin') )
        {
            $l = app()->getLocale();
            $assessment_user = Assessment::where('id', $id)->first();
            if( $assessment_user != null )
            {
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

                $answers = $user_scores->pluck('Answers')->map(function ($answer) {
                    return json_decode($answer, true);
                })->filter()->toArray();

                $countanswered = $user_scores->whereNotNull('Result_assessment')->count();
                $countNotanswered = $user_scores->whereNull('Result_assessment')->count();
                $totalQuestions = $countanswered + $countNotanswered;

                $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
            } else { return redirect()->route('assessments.results_analysis_view'); }
            return view('admin/Assessment/assessment-history',compact('user_scores','answers', 'assessment_user'));
        } else { return redirect()->route('core.dashboard_view')->with(['class'=>'danger', 'success'=>__('YouHaveNotCompany')]); }
    }

    /* public function generatePDF()
    {
        $l = app()->getLocale();
                $userid = auth()->user()->id;
                $assessment_user = Assessment::where('user_id', $userid)->whereNull('end_date')->first();

                if( $assessment_user != null )
                {
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
                } else
                {
                    $assessment_user = Assessment::where('user_id', $userid)->latest();
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
                    
                }
                // تحميل الـ View وتحويله إلى PDF
        $pdf = Pdf::loadView('admin.clients.assessment.assessment_pdf', ['user_scores' => $user_scores, 'answers' => $answers, 'assessment_user' => $assessment_user]);

        // إرجاع ملف الـ PDF للتحميل
        return $pdf->download('assessment_questions.pdf');
                // return view('admin/clients/assessment/assessment_pdf',compact('user_scores','answers', 'assessment_user'));

    
    } */

    public function generatePDF()
    {
        $request = request();
        $user = auth()->user();
        $userid = $user->id;
        if( $request->has('assessment_id') && $request->assessment_id != null )
        {
            $assessment_user = Assessment::where('id', $request->assessment_id)->with(['user'])->first();
        }
        else
        {
            $assessment_user = Assessment::where('user_id', $userid)->with(['user'])->latest()->first();
        }

        if (!$assessment_user) {
            return redirect()->back()->with('error', 'No completed Assessment Found.');
        }

        $user = $assessment_user->user;
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

        $answers = $user_scores->pluck('Answers')->map(function ($answer) {
            return json_decode($answer, true);
        })->filter()->toArray();

        // Get total weighted score
        $scoresTotal = UserScore::where('user_id', $userid)->where('assessment_id', $assessment_user->id)->get();
        $scoresTotals = $scoresTotal->sum('weighted_score');

        $start_date = $assessment_user->start_date;
        $end_date = $assessment_user->end_date;

        $companyName = $user->company->name_en;

        $view_data = [
            'user_scores' => $user_scores,
            'answers' => $answers,
            'assessment_user' => $assessment_user,
            'scoresTotals' => $scoresTotals,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'companyName' => $companyName
        ];

        return view('admin.clients.assessment.assessment_pdf', $view_data);
        // Generate PDF
        $pdf = Pdf::loadView('admin.clients.assessment.assessment_pdf', $view_data);

        return $pdf->download('completed_assessment.pdf');
    }

    private function loadAssessmentData( $userid, $id = null )
    {
        $user_scores = [];
        $Analysis_user_scores = [];
        $weightedSubCategories = [];
        $weightedPreviousSubCategories = [];
        $categories = Category::where('category_type', 'Assesment')->with('subcategory')->get();

        $previous_assessment = null;
        $user_assessments = Assessment::where('user_id', $userid)
        ->when($id, function ($query) use ($id) {
            return $query->where('id', '>=',  $id);
        })
        ->orderBy('id', 'desc')->get()->take(2);

        if( $user_assessments->count() == 1 ) { $assessment_user = $user_assessments->first(); }
        elseif( $user_assessments->count() == 0 ) { $assessment_user = null; }
        else
        {
            $assessment_user = $user_assessments->first();
            $previous_assessment = $user_assessments->last();
        }

        if( $assessment_user != null )
        {
            $categories = Category::where('category_type', 'Assesment')
                ->with([
                    'subcategory',
                    'question' => function ($query) use ($assessment_user) {
                        $query->where('assessment_id', $assessment_user->id);
                    }
                ])
                ->get();

            $user_scores = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                ->where('user_id', $userid)
                ->where('assessment_id', $assessment_user->id)
                ->get();

            $Analysis_user_scores = $categories->map(function ($category) {
                $questions = $category->question;
                return $category->subcategory->map(function ($subcategory) use ($questions) {
                    $subcategory_questions = $questions->where('subcategory_id', $subcategory->id);
                    $total_questions = $subcategory_questions->count();
                    $answered_questions = $subcategory_questions->where('score', '!=', 0)->count();
                    return (object) [
                        'name_ar' => $subcategory->name_ar,
                        'name_en' => $subcategory->name_en,
                        'total' => $total_questions,
                        'totalAnswered' => $answered_questions,
                    ];
                });
            })->flatten()->toArray();

            $weightedSubCategories = $categories->flatMap(function ($category) use ($userid, $assessment_user) {
                return $category->subcategory->map(function ($subcategory) use ($userid, $assessment_user) {
                    $scoresTotalsub = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                        ->where('user_id', $userid)
                        ->where('assessment_id', $assessment_user->id)
                        ->where('subcategory_id', $subcategory->id)
                        ->get();

                    $scoresTotalssub = $scoresTotalsub->sum('weighted_score');
                    $countanswered = $scoresTotalsub->whereNotNull('Result_assessment')->count();
                    $countNotanswered = $scoresTotalsub->whereNull('Result_assessment')->count();
                    $totalQuestions = $countanswered + $countNotanswered;
                    $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;

                    return (object) [
                        'subcategory_id' => $subcategory->id,
                        'subcategory_name_ar' => $subcategory->name_ar,
                        'subcategory_name_en' => $subcategory->name_en,
                        'weighted_score' => $scoresTotalssub,
                        'progress' => $progress,
                    ];
                });
            });

            if( $previous_assessment != null )
            {
                $prev_categories = Category::where('category_type', 'Assesment')
                    ->with([
                        'subcategory',
                        'question' => function ($query) use ($previous_assessment) {
                            $query->where('assessment_id', $previous_assessment->id);
                        }
                    ])
                    ->get();

                $user_scores = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                    ->where('user_id', $userid)
                    ->where('assessment_id', $previous_assessment->id)
                    ->get();

                $Analysis_user_scores = $prev_categories->map(function ($category) {
                    $questions = $category->question;
                    return $category->subcategory->map(function ($subcategory) use ($questions) {
                        $subcategory_questions = $questions->where('subcategory_id', $subcategory->id);
                        $total_questions = $subcategory_questions->count();
                        $answered_questions = $subcategory_questions->where('score', '!=', 0)->count();
                        return (object) [
                            'name_ar' => $subcategory->name_ar,
                            'name_en' => $subcategory->name_en,
                            'total' => $total_questions,
                            'totalAnswered' => $answered_questions,
                        ];
                    });
                })->flatten()->toArray();

                $weightedPreviousSubCategories = $prev_categories->flatMap(function ($category) use ($userid, $previous_assessment) {
                    return $category->subcategory->map(function ($subcategory) use ($userid, $previous_assessment) {
                        $scoresTotalsub = UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                            ->where('user_id', $userid)
                            ->where('assessment_id', $previous_assessment->id)
                            ->where('subcategory_id', $subcategory->id)
                            ->get();

                        $scoresTotalssub = $scoresTotalsub->sum('weighted_score');
                        $countanswered = $scoresTotalsub->whereNotNull('Result_assessment')->count();
                        $countNotanswered = $scoresTotalsub->whereNull('Result_assessment')->count();
                        $totalQuestions = $countanswered + $countNotanswered;
                        $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;

                        return (object) [
                            'subcategory_id' => $subcategory->id,
                            'subcategory_name_ar' => $subcategory->name_ar,
                            'subcategory_name_en' => $subcategory->name_en,
                            'weighted_score' => $scoresTotalssub,
                            'progress' => $progress,
                        ];
                    });
                });
            }
        }

        return [
            'user_scores' => $user_scores,
            'categories' => $categories,
            'Analysis_user_scores' => $Analysis_user_scores,
            'weightedSubCategories' => $weightedSubCategories,
            'weightedPreviousSubCategories' => $weightedPreviousSubCategories
        ];
    }
}
