<?php

namespace App\Http\Controllers;

use App\City;
use App\Enums\CompanyFormStep;
use App\Like;
use App\Step;
use App\User;
use App\Cycle;
use App\Stage;
use App\State;
use App\Almuni;
use App\Sector;
use App\Company;
use App\Country;
use App\Quizzes;
use App\Service;
use App\Surveys;
use App\Currency;
use App\Employee;
use App\Expertise;
use App\Formdata;
use App\Settings;
use App\Training;
use App\Milestone;
use App\Screening;
use Carbon\Carbon;
use App\Technology;
use App\Team_member;
use App\FundingRound;
use App\Organization;
use App\Finalscreening;
use App\MentorshipTask;
use App\ProgramTask;
use App\StaticCategory;
use App\CompanyRelation;
use App\ServiceCategory;
use App\SustainableIcon;
use App\TrainingSession;
use App\ServicesRequests;
use App\Events\WebMessage;
use App\MentorshipRequest;
use App\FinancialProjection;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use App\StartupCustomerGrowth;
use Illuminate\Validation\Rule;
use App\Services\CompanyService;
use App\ScreeningEvaluationResults;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\ScreeningEvaluationCriteria;
use Illuminate\Support\Facades\Auth;
use Google\Service\PolyService\Format;
use App\ScreeningEvaluationCustomFields;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Resources\FounderResource;
use App\Http\Requests\StoreUpdateClientCompanyRequest;
use URL, File, DB, Exception, Session, Storage, stdClass;

use function Psy\debug;
use App\Services\FileManagerService;

class ClientCompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected CompanyService $companyService;
    public function __construct(CompanyService $companyService)
    {
        $this->middleware('auth');
        $this->companyService = $companyService;
    }
    public function show(Request $request, $id = null)
    {
        // try {
        $user = auth()->user();
        $user_id = $user->id;
        if( empty( $id ) && $user->hasRole('registered') )
        {
            if( empty( $user->company_id ) )
            {
                return redirect()->route('company.profile_view_mine');
            }
            $id = $user->company_id;
        }
        else
        {
            if( empty( $id ) ) { return abort(404); }
        }

        $company = $this->companyService->getCompanyRelation($id);
        if($company['type'] == 'not_found'){
            return redirect()->back()->with('message2', 'Company Not Found');
        }
        if (!empty($company->Sustainable_development)) {
            $sustainableDevelopment = json_decode($company->Sustainable_development, true);
            $icons = SustainableIcon::whereIn('title', $sustainableDevelopment)->get();
        } else {
            $icons = '';
        }
        $team = $this->companyService->getTeamMember($id);

        // Build virtual owner card (first card on read-only profile — see GV-1981).
        // Owner is the User whose company_id == $company->id. The card is not editable.
        $ownerCard = null;
        $owner = $company->resolveUser();
        if ($owner) {
            $ownerCard = [
                'id'                 => null,
                'virtual'            => true,
                'name'               => trim((string) ($owner->name ?? '')) !== '' ? $owner->name : $owner->email,
                'email'              => $owner->email,
                'phone'              => $owner->phone ?? null,
                'linkedin'           => null, // owner User has no linkedin column
                'join_date'          => optional($company->created_at)->format('Y-m-d'),
                'founder_type'       => 'Founder',
                'working_commitment' => null,
            ];
        }

        // Real team_members rows, mapped to the shape the blade expects (array-based).
        $realFounders = $team
            ->where('TeamMember', 'Founder')
            ->map(fn($f) => [
                'id'                 => $f->id,
                'virtual'            => false,
                'name'               => $f->Name,
                'email'              => $f->Email,
                'phone'              => $f->phone ?? null,
                'linkedin'           => $f->LinkedIn,
                'join_date'          => $f->join_date ? \Illuminate\Support\Carbon::parse($f->join_date)->format('Y-m-d') : null,
                'founder_type'       => $f->founder_type,
                'working_commitment' => $f->working_commitment,
            ])
            ->values();

        // Merge: virtual owner always appears first (if resolvable).
        $founders = collect($ownerCard ? [$ownerCard] : [])->merge($realFounders)->values();

        $Shareholders = $team->where('TeamMember', 'Shareholder');
        $Investors = $team->where('TeamMember', 'Investor');

        // Load all milestones for this company (new unified structure)
        if( $user?->company?->id == $id )
        {
            $milestones = Milestone::where('company_id', $id)->get();
        }
        else
        {
            $milestones = Milestone::where('company_id', $id)->public()->get();
        }

        // For backward compatibility, create empty collections for old template variables
        $Achieved_Milestones = collect();
        $Planned_Milestones = collect();
        $Currencies = Currency::where('enabled', 1)->get(); // جلب جميع الأعضاء

        $company_relation_get = $this->companyService->findCompanyRelation($id);
        // Startup Technologies
        $technologies = $company_relation_get['technologies'];
        // Startup Sectors
        $sectors = $company_relation_get['sectors'];
        // Startup Industry
        $industry = $company_relation_get['industry'];

        // مسار الملف
        $pathToFile = storage_path($company->pitch_deck);
        // تحقق من وجود الملف
        $fileExists = file_exists($pathToFile);
        $company_current_step = $company->step;
        $evaluationForm = ScreeningEvaluation::getEvaluationData($company->step);
        $isResponsesPublished = false;
        $final_comment = null;
        $formData = collect([]);
        $formTitle = '';

        if (!empty($evaluationForm)) {
            $current_step = $company->stepdata;
            $form = Company::form($company);
            if ($current_step->stype != 2 && empty($form)) {
                return redirect()->back()->with('message2', 'No Form Found');
            }

            $formData = $form?->formData;
            $formTitle = $form?->form?->form_name ?? '';
            /* if( !empty( $formData ) ) { $formData = $formData->where('company_id', $company->id)->values(); }
            $formData = $formData->map(function ($item) {
                $field = $item->field()->first();
                $field_type = '';
                if( isset( $field?->options?->type ) ) { $field_type = $field?->options?->type; }
                $item->field_type = $field_type;
                $item->comment = '';
                return $item;
            }); */

            $myResponses = ScreeningEvaluationResults::where('company_id', $company->id)->where('evaluation_id', $evaluationForm->id)->where('user_id', $user_id)->get();
            $isResponsesPublished = $myResponses->where('status', true)->count() > 0 ? true : false;
            $myComment = ScreeningEvaluation::getMyFinalComment($evaluationForm->id, $user_id, $company->id);
            $final_comment = $myComment->value;
            $evaluationForm->final_comment = $final_comment;
            $evaluationForm->is_approved = $myComment->is_approved;
            $evaluationForm->categories->map(function ($category) use ($myResponses) {
                $category->subCriteria->map(function ($subCategory) use ($myResponses) {
                    $checkResponse = $myResponses->where('criteria_id', $subCategory->id)->first();
                    $subCategory->result = new stdClass;
                    $subCategory->result->score = $checkResponse?->score;
                    $subCategory->result->comment = $checkResponse?->comment;
                    return $subCategory;
                });
                return $category;
            });
            $evaluationForm->isPublished = $isResponsesPublished;
            $form_data_ids = $formData ? $formData->pluck('id')->toArray() : [];
            if (count($form_data_ids) > 0) {
                $comments = DB::table('form_data_judges_comments')->where('user_id', $user_id)->whereIn('form_data_id', $form_data_ids)->get();
                $formData = $formData->map(function ($fd) use ($comments) {
                    $fd->question = trim($fd->question);
                    $fd->comment = $comments->where('form_data_id', $fd->id)->first() ?? '';
                    return $fd;
                });
            } else {
                $comments = collect([]);
            }
        }
        $current_year = date('Y');
        $years = array_merge(range($current_year - 10, $current_year), range($current_year + 1, $current_year + 10));
        $records_metadata = StartupCustomerGrowth::records( $company->id );
        $growth_records = $records_metadata['records'];
        $avgMonthlyGrowth = $records_metadata['avgMonthlyGrowth'];
        $growthSinceFirstRecord = $records_metadata['growthSinceFirstRecord'];
        $compoundAnnualGrowthRate = $records_metadata['compoundAnnualGrowthRate'];
        $growthSummary = $records_metadata['summary'];

        $programs = collect();
        $avgFinalScreenings = collect([]);

        $cycle = Cycle::with(['steps', 'organization'])->find($company->cycle);
        if( $cycle )
        {
            $currentScreeningScore = null;
            $screenSteps = Step::where('cycle_id', $cycle->id)->where('stype', 2)->pluck('id');
            if( $screenSteps->isNotEmpty() )
            {
                $screening_details = ScreeningEvaluationResults::results( $screenSteps->first(), [$company->id] )[$company->id];

                /* $screenEvals = ScreeningEvaluation::whereIn('step_id', $screenSteps)->pluck('id');
                if ($screenEvals->isNotEmpty()) {
                    $screenResults = ScreeningEvaluationResults::whereIn('evaluation_id', $screenEvals)
                        ->where('company_id', $company->id)->get();
                    $currentScreeningScore = $screenResults->isNotEmpty() ? round($screenResults->avg('score'), 2) : null;
                } */

                if( !empty( $screening_details ) )
                {
                    $currentScreeningScore = $screening_details['total'] * 100;
                }
            }

            $currentFinalScoreForProgram = null;
            $finalStepsForProgram = Step::where('cycle_id', $cycle->id)->where('stype', 5)->pluck('id');
            if( $finalStepsForProgram->isNotEmpty() )
            {
                $final_screening_details = ScreeningEvaluationResults::results( $finalStepsForProgram->first(), [$company->id] )[$company->id];

                /* $finalEvalsForProgram = ScreeningEvaluation::whereIn('step_id', $finalStepsForProgram)->pluck('id');
                 if ($finalEvalsForProgram->isNotEmpty()) {
                    $finalResultsForProgram = ScreeningEvaluationResults::whereIn('evaluation_id', $finalEvalsForProgram)
                        ->where('company_id', $company->id)->get();
                    $currentFinalScoreForProgram = $finalResultsForProgram->isNotEmpty() ? round($finalResultsForProgram->avg('score'), 2) : null;
                } */

                if( !empty( $final_screening_details ) )
                {
                    $currentFinalScoreForProgram = $final_screening_details['total'] * 100;
                    $avgFinalScreenings->push( $final_screening_details );
                }
            }

            $startDate = $cycle->start ? Carbon::parse($cycle->start) : null;
            // The company is still assigned to this cycle (hasn't moved to Almuni yet),
            // so it can only be 'upcoming' or 'ongoing' here — never 'completed'.
            // A cycle's calendar end date passing doesn't mean this company finished it.
            $cycleStatus = ($startDate && Carbon::now()->lessThan($startDate)) ? 'upcoming' : 'ongoing';

            $custom_form = $cycle->steps->sortBy('arr')->values()->where('stype', 1)->first();
            $submissions = Formdata::where('company_id', $company?->id)->where('step_id', $custom_form?->id)->orderBy('created_at', 'asc')->first();
            $programs->push((object)[
                'id' => $cycle->id,
                'name' => $cycle->title,
                'img_url' => $cycle->img_url ?? null,
                'type' => strtolower($cycle->type ?? ''),
                'organization' => (object)[
                    'name' => is_object($cycle->organization) ? ($cycle->organization->name ?? null) : $cycle->organization
                ],
                'cohort' => (object)[ 'name' => $cycle->cohort ?? null ],
                'start_date' => $submissions ? Carbon::parse($submissions->created_at) : null,
                'completion_date' => null,
                'screening_score' => $currentScreeningScore,
                'rank' => null,
                'final_screening_score' => $currentFinalScoreForProgram,
                'status' => $cycleStatus,
                'program_type' => 'running'
            ]);
        }
        $almunies = Almuni::where('company_id', $company->id)->with(['cycle', 'cycle.steps', 'cycle.organization'])->get();
        $mentorshipToSum = collect();
        $trainingToSum = collect();
        $finalScreenAvg = collect();
        // Cache ScreeningEvaluationResults::results() per step id so repeated
        // step ids across alumni cycle records only trigger one batch of
        // queries each, instead of a fresh set of queries per alumni row.
        $stepResultsCache = [];
        $resultsForStep = function ($stepId) use (&$stepResultsCache, $company) {
            if (!array_key_exists($stepId, $stepResultsCache)) {
                $stepResultsCache[$stepId] = ScreeningEvaluationResults::results($stepId, [$company->id])[$company->id] ?? null;
            }
            return $stepResultsCache[$stepId];
        };
        foreach ($almunies as $almuni) {
            if (!empty($almuni->mentorship)) {
                $mentorshipData = is_array($almuni->mentorship)
                    ? $almuni->mentorship
                    : json_decode($almuni->mentorship, true);
                $mentorshipToSum = $mentorshipToSum->merge($mentorshipData ?? []);
            }
            if (!empty($almuni->training)) {
                $trainingData = is_array($almuni->training)
                    ? $almuni->training
                    : json_decode($almuni->training, true);
                $trainingToSum = $trainingToSum->merge($trainingData ?? []);
            }
            if (!empty($almuni->final_screening)) {
                $finalScreenData = is_array($almuni->final_screening)
                    ? $almuni->final_screening
                    : json_decode($almuni->final_screening, true);
                $finalScreenAvg = $finalScreenAvg->merge($finalScreenData ?? []);
            }

            // Primary source: load cycle directly from DB via cycle_id
            if ($almuni->cycle_id) {
                $almuniCycle = $almuni->cycle;
                $custom_form = $almuniCycle->steps->sortBy('arr')->values()->where('stype', 1)->first();
                // $custom_form may be null when the cycle has no Custom Form step;
                // guard the step_id lookup so we don't read ->id on null.
                $submissions = $custom_form
                    ? Formdata::where('company_id', $company->id)->where('step_id', $custom_form->id)->orderBy('created_at', 'asc')->first()
                    : null;
                if ($almuniCycle)
                {
                    // screening / final_screening are JSON arrays of step entries.
                    // Decode defensively (the value may arrive as a JSON string)
                    // and read the first entry's step_id; missing/empty arrays
                    // previously threw "Undefined array key 0" (GV-3735).
                    $screeningEntries = is_array($almuni->screening) ? $almuni->screening : (json_decode($almuni->screening, true) ?: []);
                    $finalScreeningEntries = is_array($almuni->final_screening) ? $almuni->final_screening : (json_decode($almuni->final_screening, true) ?: []);

                    $almuniScreeningScore = null;
                    if( !empty( $almuni->screening ) )
                    {
                        $screeningStepId = data_get($screeningEntries, '0.step_id', 0);
                        $screening_details = $resultsForStep($screeningStepId);

                        $almuniScreeningScore = $screening_details ? $screening_details['total'] * 100 : null;
                    }

                    $alumniFinalScore = null;
                    if( !empty( $almuni->final_screening ) )
                    {
                        $finalScreeningStepId = data_get($finalScreeningEntries, '0.step_id', 0);
                        $final_screening_details = $resultsForStep($finalScreeningStepId);
                        if( !empty( $final_screening_details ) )
                        {
                            $avgFinalScreenings->push( $final_screening_details );
                        }
                        /* $fsArr = is_array($almuni->final_screening) ? $almuni->final_screening : json_decode($almuni->final_screening, true);
                        $fsScores = collect($fsArr ?? [])->pluck('overall_score')->filter();
                        $alumniFinalScore = $fsScores->isNotEmpty() ? round($fsScores->avg(), 2) : null; */

                        $alumniFinalScore = $final_screening_details ? $final_screening_details['total'] * 100 : null;
                    }

                    // Formdata
                    $programs->push((object)[
                        'id'                    => $almuniCycle->id,
                        'name'                  => $almuniCycle->title,
                        'img_url'               => $almuniCycle->img_url ?? null,
                        'type'                  => strtolower($almuniCycle->type ?? ''),
                        'organization'          => (object)['name' => is_object($almuniCycle->organization) ? ($almuniCycle->organization->name ?? null) : $almuniCycle->organization],
                        'cohort'                => (object)['name' => $almuniCycle->cohort ?? null],
                        'start_date'            => $submissions ? Carbon::parse($submissions->created_at) : null,
                        'completion_date'       => $almuni->created_at ? Carbon::parse($almuni->created_at) : null,
                        'screening_score'       => $almuniScreeningScore,
                        'rank'                  => null,
                        'final_screening_score' => $alumniFinalScore,
                        'status'                => 'completed',
                        'program_type'          => 'alumni'
                    ]);
                    continue;
                }
            }

            // Fallback: parse from program JSON column (legacy/external programs)
            $programsRaw = $almuni->program ?? [];
            if (!is_array($programsRaw)) continue;
            foreach ($programsRaw as $p) {
                if (is_array($p)) $p = (object)$p;
                $orgObj = null;
                if (isset($p->Organization)) {
                    if (is_array($p->Organization)) {
                        $l = app()->getLocale();
                        $org = (object)$p->Organization;
                        $orgObj = (object)[ 'name' => $org->{'name_' . $l} ?? $org->name_en ?? $org->name_ar ?? null ];
                    } elseif (is_object($p->Organization)) {
                        $l = app()->getLocale();
                        $orgObj = (object)[ 'name' => $p->Organization->{'name_' . $l} ?? $p->Organization->name_en ?? $p->Organization->name_ar ?? null ];
                    } else {
                        $orgObj = (object)[ 'name' => $p->Organization ];
                    }
                } elseif (isset($p->{'Organization_Name'})) {
                    if (is_object($p->{'Organization Name'})) {
                        $orgObj = (object)[ 'name' => $p->{'Organization_Name'}->name ?? null ];
                    } else {
                        $orgObj = (object)[ 'name' => $p->{'Organization_Name'} ];
                    }
                } else {
                    $orgObj = (object)[ 'name' => null ];
                }
                $programs->push((object)[
                    'id'                    => $p->ID ?? ($programs->count() + 1),
                    'name'                  => $p->{'Program_Name'} ?? $p->Program_Name ?? $p->title ?? null,
                    'img_url'               => $p->img_url ?? null,
                    'type'                  => strtolower($p->Type ?? $p->type ?? ''),
                    'organization'          => $orgObj,
                    'cohort'                => (object)[ 'name' => $p->Cohort ?? $p->{'Cohort_Name'} ?? null ],
                    'start_date'            => !empty($p->Start_Date) ? Carbon::parse($p->Start_Date) : null,
                    'completion_date'       => !empty($p->Completion_Date) ? Carbon::parse($p->Completion_Date) : null,
                    'screening_score'       => $p->Screening_Score ?? null,
                    'rank'                  => $p->Rank ?? null,
                    'final_screening_score' => $p->Final_Screening_Score ?? null,
                    'status'                => $p->Status ?? 'completed',
                ]);
            }
        }
        $programs = $programs->values()->all();
        $curentCycle = $company->cycledata?->mentorshipRequests?->where('user_id', $company?->user?->id)->sum('period');
        
        $totalMinutes = $mentorshipToSum->sum('duration') + $curentCycle;
        $hours   = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $totalMentorshipHours = sprintf('%02d:%02d', $hours, $minutes);
        // Training
        $curentCycleTraining = $company->cycledata?->trainingSessions?->where('user_id', $company?->user?->id)->sum('duration') ?? 0;
        $totalMinutesTraining = (collect($trainingToSum)->sum('duration') ?? 0) + $curentCycleTraining;
        $hoursTraining   = intdiv($totalMinutesTraining, 60);
        $minutesTraining = $totalMinutesTraining % 60;
        $totalMentorshipHoursTraining = sprintf('%02d:%02d', $hoursTraining, $minutesTraining);

        // Calculate avgFinalScreening from current cycle + alumni
        $currentFinalScreening = null;
        if ($company->cycle) {
            $finalSteps = Step::where('cycle_id', $company->cycle)
                ->where('stype', 5) // Final Screening
                ->pluck('id');

            if ($finalSteps->isNotEmpty()) {
                $evaluations = ScreeningEvaluation::whereIn('step_id', $finalSteps)->pluck('id');

                if ($evaluations->isNotEmpty()) {
                    $currentResults = ScreeningEvaluationResults::whereIn('evaluation_id', $evaluations)
                        ->where('company_id', $company->id)
                        ->where('status', 1)
                        ->get();

                    if ($currentResults->isNotEmpty()) {
                        $currentFinalScreening = $currentResults->avg('score');
                    }
                }
            }
        }

        /*
        // Get alumni final screening scores
        $alumniScores = collect();
        foreach ($almunies as $almuni) {
            if (!empty($almuni->final_screening)) {
                $fsRaw = is_array($almuni->final_screening)
                    ? $almuni->final_screening
                    : json_decode($almuni->final_screening, true);
                $finalScreeningData = collect($fsRaw ?? []);
                $overallScores = $finalScreeningData->pluck('overall_score')->filter();

                if ($overallScores->isNotEmpty()) {
                    $alumniScores->push($overallScores->avg());
                }
            }
        }

        // Combine current and alumni scores
        $allScores = collect();
        if ($currentFinalScreening !== null) {
            $allScores->push($currentFinalScreening);
        }
        if ($alumniScores->isNotEmpty()) {
            $allScores = $allScores->merge($alumniScores);
        }

        $avgFinalScreening = $allScores->isNotEmpty() ? round($allScores->avg(), 2) : 0;  */

        $avgFinalScreening = $avgFinalScreenings->avg('total') * 100 . '%';
        // Calculate avgAttendanceRate from mentorship sessions only
        $currentMentorships = collect();
        if ($company->cycledata) {
            $currentMentorships = $company->cycledata->mentorshipRequests()
                ->where('user_id', $company?->user?->id)
                ->get();
        }

        // Get alumni mentorship sessions
        $alumniMentorships = collect();
        foreach ($almunies as $almuni) {
            if (!empty($almuni->mentorship)) {
                $mRaw = is_array($almuni->mentorship)
                    ? $almuni->mentorship
                    : json_decode($almuni->mentorship, true);
                $alumniMentorships = $alumniMentorships->merge($mRaw ?? []);
            }
        }

        // Calculate attendance rate based on sessions that have been rated
        $currentAttended = $currentMentorships->whereNotNull('rating')->count();
        $currentTotal = $currentMentorships->count();

        $alumniAttended = $alumniMentorships->filter(function ($session) {
            return isset($session['rating']) && $session['rating'] !== null && $session['rating'] !== '';
        })->count();
        $alumniTotal = $alumniMentorships->count();

        // Calculate total
        $totalAttended = $currentAttended + $alumniAttended;
        $totalSessions = $currentTotal + $alumniTotal;

        $avgAttendanceRate = $totalSessions > 0
            ? round(($totalAttended / $totalSessions) * 100, 2)
            : 0;

        $programsSummary = [
            'totalMentorshipHours' => $totalMentorshipHours,
            'totalTrainingHours'   => $totalMentorshipHoursTraining,
            'avgFinalScreening'    => $avgFinalScreening,
            'avgAttendanceRate'    => $avgAttendanceRate . '%',
        ];
        // Financial Projection
        $availableValuationDates = FinancialProjection::where('company_id', $company->id)
            ->select('valuation_date')
            ->distinct()
            ->orderBy('valuation_date', 'desc')
            ->pluck('valuation_date')
            ->toArray();
        
            
        
        //$valuationDate = request('valuation_date');
        //if (!$valuationDate || !in_array($valuationDate, $availableValuationDates)) {
        //    $valuationDate = date('Y-m-01');
        //}

        
        
        
        //$financials = FinancialProjection::where('company_id', $company->id)
          //  ->where('valuation_date', $valuationDate)
         //   ->orderBy('year')
          //  ->get()
          //  ->keyBy('year');

        $periods = [];
        $valuation_dateSearch = $request->valuation_date ?? date('Y-m');
        $desc_valuation = FinancialProjection::where('company_id', $id)
            ->whereNotNull('valuation_date')
            ->orderBy('valuation_date', 'desc')
            ->first();
        if (empty($request->valuation_date) && !empty($desc_valuation?->valuation_date)) {
            $valuation_dateSearch = Carbon::parse($desc_valuation->valuation_date)->format('Y-m');
        }

        $valuationYear = (int) Carbon::parse($valuation_dateSearch . '-01')->format('Y');

        $Historyfinancialprojection = FinancialProjection::where('company_id', $id)
            ->orderBy('valuation_date', 'desc')
            ->get();
        
        $availableValuationDates = $Historyfinancialprojection->pluck('valuation_date')->unique();
        $financials = $Historyfinancialprojection->filter(fn($fp) => $fp->valuation_date->format('Y-m-d') === $valuation_dateSearch.'-01')->keyBy('year');
        
        $historicalValuationDates = $availableValuationDates->map(function ($date) {
            return Carbon::parse($date)->format('Y-m');
        });
        
        $valuationDate = $request->valuation_date ?? $valuation_dateSearch;

        $hasFinancials = !$financials->isEmpty() && $financials->count() === 4 && $financials->every(function ($fp) {
            return !is_null($fp->revenues) && !is_null($fp->cogs) && !is_null($fp->salaries) && !is_null($fp->other_op_expenses);
        });

        $firstRecordOverall = $Historyfinancialprojection->whereNotNull('currency')->first();
        $currencyCode = $firstRecordOverall?->currency ?? $company->Currency ?? 'USD';

        $map = function ($fp) {
            return [
                'revenue'        => (float) ($fp->revenues ?? 0),
                'cogs'           => (float) ($fp->cogs ?? 0),
                'grossProfit'    => (float) ($fp->revenues ?? 0) - (float) ($fp->cogs ?? 0),
                'salaries'       => (float) ($fp->salaries ?? 0),
                'opex'           => (float) ($fp->other_op_expenses ?? 0),
                'ebitda'         => (float) ($fp->ebitda ?? 0),
                'da'             => (float) ($fp->depreciation_amortization ?? 0),
                'ebit'           => (float) ($fp->ebit ?? 0),
                'interest'       => (float) ($fp->interest ?? 0),
                'taxes'          => (float) ($fp->taxes ?? 0),
                'netProfit'      => (float) ($fp->net_profit ?? 0),
                'fcf'            => (float) ($fp->free_cash_flow ?? 0),
                'capex'          => (float) ($fp->capital_expenditures ?? 0),
                'workingCapital' => (float) ($fp->working_capital ?? 0),
                'totalDebt'      => (float) ($fp->debt_end_year ?? 0),
                'receivables'    => (float) ($fp->receivables ?? 0),
                'inventory'      => (float) ($fp->inventory ?? 0),
                'payables'       => (float) ($fp->payables ?? 0),
            ];
        };

        $v = Carbon::parse($valuationDate . '-01');
        $dateRanges = [
            'past' => $v->copy()->subYear()->format('m/Y') . ' – ' . $v->copy()->subDay()->format('m/Y'),
            'year1' => $v->copy()->format('m/Y') . ' – ' . $v->copy()->addYear()->subDay()->format('m/Y'),
            'year2' => $v->copy()->addYear()->format('m/Y') . ' – ' . $v->copy()->addYears(2)->subDay()->format('m/Y'),
            'year3' => $v->copy()->addYears(2)->format('m/Y') . ' – ' . $v->copy()->addYears(3)->subDay()->format('m/Y'),
        ];

        $expectedYears = [
            $valuationYear - 1 => ['key' => 'past', 'label' => __('startup.financial.past_year'), 'sub' => $dateRanges['past']],
            $valuationYear => ['key' => 'year1', 'label' => __('startup.financial.year_1'), 'sub' => $dateRanges['year1']],
            $valuationYear + 1 => ['key' => 'year2', 'label' => __('startup.financial.year_2'), 'sub' => $dateRanges['year2']],
            $valuationYear + 2 => ['key' => 'year3', 'label' => __('startup.financial.year_3'), 'sub' => $dateRanges['year3']],
        ];
    
        foreach ($expectedYears as $_year => $meta) {
            $data = isset($financials[$_year]) ? $map($financials[$_year]) : $map(new FinancialProjection());
            $periods[$meta['key']] = [
                'label' => $meta['label'],
                'sub'   => $meta['sub'],
                'data'  => $data,
            ];
        }

        // Padges
        $profile = $company;
        $profile_page = collect([
            $profile->name_en,
            $profile->name_ar,
            $profile->country_id,
            $profile->state_id,
            $profile->business_categories,
            $profile->est_date,
            $profile->address_en,
            $profile->address_ar,
            $profile->years_of_operations,
            $profile->info_company,
            $profile->previous_incubation,
            $profile->product_or_service,
            $profile->about_problem,
            $profile->about_market_opportunity,
            $profile->about_competitors,
            $profile->about_monetize,
            $profile->about_challenges,
            $profile->business_model,
            $profile->business_model_description,
            $profile->company_registration_type,
            $profile->products_services_description,
            $profile->phone,
            $profile->email,
            $profile->achievements,
            $profile->mission,
            $profile->why_this_startup,
            $profile->startup_expertise,
            $profile->generate_revenue,
            $profile->development_stage,
            $profile->business_type,
            $profile->pitch_deck,
            $profile->documents,
            $profile->total_employees,
            $profile->male_employees,
            $profile->female_employees,
            $profile->mvp_prototype_link,
            $profile->logo,
            $profile->Currency,
            $profile->Pre_money_Valuation,
            $profile->raised_fund,
            $profile->Funding_Target,
            $profile->stage,
            $profile->website,
            $profile->facebook,
            $profile->twitter,
            $profile->linkedin,
            $profile->youtube,
            $profile->instagram,
            $profile->tiktok,
            $profile->google_map,
            $profile->whatsapp,
            $profile->Commercial_Register,
            $profile->Tax_Card,
            $profile->documents,
        ])->every(fn ($value) => !is_null($value));
        if($profile_page == false){
            $profile_page = true;
        }else{
            $profile_page = false;
        }
        $achievements = [
            'alumni' => [
                'locked'  => $almunies->count() > 0 ? false : true,
                'title'   => $almunies->sortByDesc('created_at')->first()?->cycle?->title,
                'date' => $almunies->sortByDesc('created_at')->first()?->created_at->format('d-m-Y') ?? null, // Alumni date
            ],
            'hackathon' => [
                'locked'  => true,
                'title'   => 'Global Hack Week',
                'date' => null, // Winning date
            ],
            'profile_complete' => [
                'locked'  =>   $profile_page,
                'subtitle'=> '100% Completed',
                'date' => $profile->created_at->format('d-m-Y'), // Profile completion date
            ],
            'team_builder' => [
                'locked'  => $profile->employees != null ? false : true,
                'count' => $profile->employees?->count() ?? 0, // Count of team members
            ],
            'growth_achiever' => [
                'locked'  => $profile->milestones->count() > 0 ? false : true,
                'count' => $profile->milestones?->count() ?? 0, // Count of milestones
                'date' => $profile->milestones->sortByDesc('created_at')->first()?->created_at->format('d-m-Y') ?? null, // Milestone achievement date
            ],
            'mentor_connect' => [
                'locked'  => $company->user?->mentorship?->whereNotNull('rating')->where('done', 1)->count() > 0 ? false : true,
                'count' => $company->user?->mentorship?->whereNotNull('rating')->where('done', 1)->count() ?? 0, // Count of mentorship sessions
                'date' => $company->user?->mentorship?->whereNotNull('rating')->where('done', 1)->sortByDesc('created_at')->first()?->created_at->format('d-m-Y') ?? null, // Date of first completed mentorship session with rating
            ],
        ];
        $totalEmployees  = $company?->total_employees;
        $maleEmployees   = $company?->male_employees;
        $femaleEmployees = $company?->female_employees;

        $hasTotal  = is_int($totalEmployees) && $totalEmployees > 0;
        $hasMale   = is_int($maleEmployees);
        $hasFemale = is_int($femaleEmployees);

        $malePct   = ($hasTotal && $hasMale)   ? (int) round(($maleEmployees   / $totalEmployees) * 100) : 0;
        $femalePct = ($hasTotal && $hasFemale) ? (int) round(($femaleEmployees / $totalEmployees) * 100) : 0;

        $showDistributionBar = $hasTotal && ($hasMale || $hasFemale);

        $naLabel = __('admin.N/A');

        $employeesKpis = [
            [
                'title'       => __('startup.total_employees'),
                'value'       => $hasTotal ? (string) $totalEmployees : $naLabel,
                'na'          => !$hasTotal,
                'icon'        => 'ti-users-group',
                'color'       => 'success',
                'type'        => 'split',
                'has_male'    => $hasTotal && $hasMale,
                'has_female'  => $hasTotal && $hasFemale,
                'male_value'  => $maleEmployees,
                'female_value'=> $femaleEmployees,
                'male_pct'    => $malePct,
                'female_pct'  => $femalePct,
                'show_bar'    => $showDistributionBar,
            ],
            [
                'title' => __('startup.male_employees'),
                'value' => ($hasTotal && $hasMale) ? (string) $maleEmployees : $naLabel,
                'na'    => !($hasTotal && $hasMale),
                'icon'  => 'ti-gender-male',
                'color' => 'primary',
                'type'  => 'pct',
                'pct'   => $malePct,
            ],
            [
                'title' => __('startup.female_employees'),
                'value' => ($hasTotal && $hasFemale) ? (string) $femaleEmployees : $naLabel,
                'na'    => !($hasTotal && $hasFemale),
                'icon'  => 'ti-gender-female',
                'color' => 'info',
                'type'  => 'pct',
                'pct'   => $femalePct,
            ],
        ];

        $registrationType = trim((string) ($company->company_registration_type ?? ''));
        $offeringType     = trim((string) ($company->business_type ?? ''));
        $productsDesc     = trim((string) ($company->products_services_description ?? ''));

        $registrationOfferingDetails = [
            'registration_type' => $registrationType !== '' ? $registrationType : null,
            'offering_type'     => $offeringType !== ''     ? $offeringType     : null,
            'products_desc'     => $productsDesc !== ''     ? $productsDesc     : null,
            'has_any'           => ($registrationType !== '' || $offeringType !== '' || $productsDesc !== ''),
        ];

        return view('admin/clients/startup/company_profile', compact('company',
        'employeesKpis',
        'registrationOfferingDetails',
        'formTitle',
        'formData',
        'evaluationForm', 
        'Currencies', 
        'milestones', 
        'Achieved_Milestones', 
        'Planned_Milestones', 
        'icons', 
        'founders', 
        'Shareholders', 
        'Investors', 
        'fileExists', 
        'pathToFile', 
        'technologies', 
        'sectors', 
        'industry', 
        'years',
        'growth_records',
        'growthSinceFirstRecord',
        'avgMonthlyGrowth',
        'compoundAnnualGrowthRate',
        'growthSummary',
        'programs',
        'programsSummary',
        'periods',
        'valuationDate',
        'historicalValuationDates',
        'hasFinancials',
        'currencyCode',
        'achievements'));
        // } catch (Exception $e) {
        //     //log what happend
        //     Log::channel('system_exceptions')->info('Exceptions:', [$e]);
        //     abort(500);
        // }
    }
    /**
     * Show the form for editing/Createing the specified resource.
     *
     * @param  \App\Company  $Type
     * @return \Illuminate\Http\Response
     */
    public function editOrCreate(Request $request, $id = null)
    {
       
        $user = auth()->user();
        try {

            if( $id == null )
            {
                if( !$user->hasRole('registered') )
                {
                    return redirect()->route('user.profile_view')->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
                }
                $client = User::where('HaveCompany', 1)->with('company')->with('company.city')->find($user->id);
                $id = $client?->company?->id ?? null;
            }
            $company_instance = null;
            if( $id == null )
            {
                $client = User::where('HaveCompany', 1)->with('company')->with('company.city')->find($user->id);
                if( empty( $client ) )
                {
                    if( !$user->hasRole('registered') )
                    {
                        return redirect()->route('user.profile_view')->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
                    }
                    else
                    {
                        $company = new company;
                        $company->name_en = '';
                        $company->name_ar = '';
                        $company->address_en = '';
                        $company->address_ar = '';
                        $company->save();

                        $user->company_id = $company->id;
                        $user->HaveCompany = 1;
                        $user->save();

                        $client = User::where('HaveCompany', 1)->with('company')->with('company.city')->find($user->id);
                    }
                }

                // Fresh registered users get a blank company auto-created above; make sure
                // $company_instance and $id are set so every tab below (industry/sectors,
                // employees, milestones, financials, ...) renders against that company
                // instead of falling back to empty data.
                $company_instance = $client?->company;
                if ($company_instance) {
                    $id = $company_instance->id;
                }

                $countries = Country::all();
                $states = State::all();
                $Setting = Settings::where('id', 1)->first();
                $sectors = Sector::whereNull('parent_id')->get();
                $funding_rounds = $user->company ? $user->company->fundingRounds : [];
                $teamMembers = Team_Member::all(); // جلب جميع الأعضاء
                if( $user->company )
                {
                    $company_members = Team_Member::where('Company_id', $user->company->id)->get();
                    $founders = $company_members->where('TeamMember', 'Founder')->values();
                    $shareholders = $company_members->where('TeamMember', 'Shareholder')->values();
                    $investors = $company_members->where('TeamMember', 'Investor')->values();
                    // For backward compatibility, create empty collections
                    $Achieveds = collect();
                    $Planneds = collect();
                    $Archiveds = collect();
                }
                else
                {
                    $founders = collect();
                    $shareholders = collect();
                    $investors = collect();
                    $milestones = collect();
                    $Achieveds = 'NA';
                    $Planneds = 'NA';
                    $Archiveds = 'NA';
                }
                $Stages = Stage::all();

                $Currencies = Currency::where('enabled', 1)->get(); // جلب جميع الأعضاء
                $json = File::get(base_path('database/dbFiles/countries.json'));
                $_countries = json_decode($json, true);
                $_countries = collect($_countries)->map(function ($country) {
                    $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
                    return $country;
                });
                $countries = $countries->map(function ($country) use ($_countries) {
                    $getCountry = $_countries->where('iso3', $country->iso3)->first();
                    $flag = null;
                    $emoji = null;
                    if (!empty($getCountry)) {
                        $flag = $getCountry['flag'];
                        $emoji = $getCountry['emoji'];
                    }
                    $country->flag = $flag;
                    $country->emoji = $emoji;
                    return $country;
                });
                
            }
            else
            {
                if( $user->hasRole('registered') )
                {
                    if( $user->company_id != $id )
                    {
                        return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                    }
                }
                   
                // if (!$user->hasAnyRole('Admin|organizationManager')) {
                //     return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                // }

                if ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
                    $orgIds = $user->organizations_ids;
                    $cycles = Cycle::whereIn('organization_id', $orgIds)->with('companies')->get();
                    foreach ($cycles as $cycle) {
                        foreach ($cycle->companies as $company) {
                            if ($company->id == $id) {
                                $id = $company->id;
                                break;
                            } else {
                                $id = null;
                            }
                        }
                    }
                }
                if ($id == null) {
                    return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                }
                $Company = Company::find($id);

                $client = User::where('company_id', $Company->id)->with('company')
                    ->with('company.city')
                    ->first();

                if (empty($client)) {
                    return redirect()->route('user.profile_view')->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
                }

                $company_instance = $client->company;
                $countries = Country::all();
                $states = State::all();
                $Setting = Settings::where('id', 1)->first();
                $sectors = Sector::whereNull('parent_id')->get();
                $funding_rounds = $company_instance ? $company_instance->fundingRounds : [];

                $teamMembers = Team_Member::where('Company_id', $company_instance->id)->get();

                if ($company_instance) {
                    $founders = $teamMembers->where('TeamMember', 'Founder')->values();
                    $shareholders = $teamMembers->where('TeamMember', 'Shareholder')->values();
                    $investors = $teamMembers->where('TeamMember', 'Investor')->values();

                    // For backward compatibility, create empty collections
                    $Achieveds = collect();
                    $Planneds = collect();
                    $Archiveds = collect();
                } else {
                    $founders = collect();
                    $shareholders = collect();
                    $investors = collect();
                    $milestones = collect();
                    $Achieveds = 'NA';
                    $Planneds = 'NA';
                    $Archiveds = 'NA';
                }

                $founders = $founders->map(function ($item) {
                    $member = new stdClass;
                    
                    $member->id = $item->id;
                    $member->name = $item->Name;
                    $member->email = $item->Email;
                    $member->type = $item->founder_type;
                    $member->phone = $item->phone;
                    $member->join_date = $item->join_date;
                    $member->linkedin = $item->LinkedIn;
                    $member->commitment = $item->working_commitment;
                    return $member;
                });
                $investors = $investors->map(function ($item) {
                    $member = new stdClass;
                    $member->id = $item->id;
                    $member->name = $item->Name;
                    $member->type = $item->investorType;
                    $member->investment_amount = $item->InvestmentAmount;
                    return $member;
                });
                $shareholders = $shareholders->map(function ($item) {
                    $member = new stdClass;
                    $member->id = $item->id;
                    $member->name = $item->Name;
                    $member->role = $item->RoleShereholder;
                    $member->percentage = $item->PresentagShereholder;
                    return $member;
                });
                $Stages = Stage::all();

                $Currencies = Currency::where('enabled', 1)->get(); // جلب جميع الأعضاء
                $json = File::get(base_path('database/dbFiles/countries.json'));
                $_countries = json_decode($json, true);
                $_countries = collect($_countries)->map(function ($country) {
                    $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
                    return $country;
                });
                $countries = $countries->map(function ($country) use ($_countries) {
                    $getCountry = $_countries->where('iso3', $country->iso3)->first();
                    $flag = null;
                    $emoji = null;
                    if (!empty($getCountry)) {
                        $flag = $getCountry['flag'];
                        $emoji = $getCountry['emoji'];
                    }
                    $country->flag = $flag;
                    $country->emoji = $emoji;
                    return $country;
                });
               
            }

            $l = app()->getLocale();
            if ($company_instance) {
                $companyRelations = CompanyRelation::where('company_id', $company_instance->id)->get();
                $selectedIndustryId = $companyRelations->pluck('industry_id')->filter()->first();
                $selectedSectorIds = $companyRelations->pluck('sector_id')->filter()->toArray();
                $selectedTechnologyIds = $companyRelations->pluck('technology_id')->filter()->toArray();

                // Get all technologies for the select2 dropdown
                $allTechnologies = DB::table('technologies')->get()->map(function ($tech) {
                    return [
                        'id' => $tech->id,
                        'name' => $tech->name,
                    ];
                })->values()->toArray();
                    
                // Get selected technologies as names (for display in tags)
                $selectedTechnologies = DB::table('technologies')
                    ->whereIn('id', $selectedTechnologyIds)
                    ->pluck('name')
                    ->toArray();

                $technologies = $allTechnologies; // All technologies for select2
                $technologiesSelected = $selectedTechnologies; // Selected tech names for tags

                $allSectors = DB::table('sectors')->get();
                
                $industries = StaticCategory::with('sectors')->get();
                $data = $industries->map(function ($industry) use ($l, $allSectors, $selectedIndustryId, $selectedSectorIds) {
                    if ($industry->sectors->isNotEmpty()) {
                        $sectorsList = $industry->sectors->map(function ($sector) use ($l, $selectedSectorIds) {
                            return [
                                'id' => $sector->id,
                                'name' => $sector->{"name_$l"} ?? $sector->name_en ?? 'N/A',
                                'selected' => in_array($sector->id, $selectedSectorIds),
                            ];
                        })->values()->toArray();
                    } else {
                        // When industry has no sectors, return empty array (not all sectors)
                        $sectorsList = [];
                    }
                    return [
                        'id' => $industry->id,
                        'name' => $industry->name ?? $industry->name_en,
                        'selected' => $industry->id == $selectedIndustryId,
                        'sectors' => $sectorsList,
                    ];
                });
               
            } else {
                $industries = StaticCategory::with('sectors')->get();
                $technologies = DB::table('technologies')->get()->map(function ($tech) {
                    return [
                        'id' => $tech->id,
                        'name' => $tech->name,
                    ];
                })->values()->toArray();
                $technologiesSelected = [];
                $data = [];
            }
            $industries = $industries->map(function ($industry) {
                if (empty($industry->name_ar) || is_null($industry->name_ar) || !trim($industry->name_ar)) {
                    $industry->name_ar = $industry->name_en;
                }
                return $industry;
            });
            $employees = Employee::where('company_id', $id)->get();

            // Load all milestones for this company (new unified structure)
            $milestones = Milestone::where('company_id', $id)
                ->orderBy('created_at')->get();

            $year = !empty($request->valuation_date)
                ? Carbon::parse($request->valuation_date)->year
                : (int) date('Y');

            $valuation_dateSearch = $request->valuation_date;

            $getCurrancy = '';
            $is_currency_disabled = false;
            $Historyfinancialprojection = FinancialProjection::where('company_id', $id)
                ->whereNotNull('valuation_date')
                ->orderBy('valuation_date', 'desc')
                ->get();

            if ($Historyfinancialprojection->isEmpty()) {
                $financialprojection = [];
                $valuation_date = null;
            } else {
                if ($valuation_dateSearch) {
                    $valuation_date_full = $valuation_dateSearch . '-01';
                } else {
                    $valuation_date_full = $Historyfinancialprojection->first()->valuation_date;
                }
                $filtered = $Historyfinancialprojection
                    ->where('valuation_date', $valuation_date_full)
                    ->values();

            
                $financialprojection = $filtered->keyBy('year')->toArray();

                $valuation_date = Carbon::parse($valuation_date_full)->format('Y-m');

                $getCurrancy = $Historyfinancialprojection->whereNotNull('currency')->first()?->currency;
                $is_currency_disabled = !is_null($getCurrancy);
            }

            $historicalValuationDates = $Historyfinancialprojection
                ->pluck('valuation_date')
                ->unique()
                ->map(fn($date) => Carbon::parse($date)->format('Y-m'));

            $current_year = date('Y');
            $years = array_merge(range($current_year - 10, $current_year), range($current_year + 1, $current_year + 10));
            $records_metadata = StartupCustomerGrowth::records( $company_instance?->id );
            $growth_records = $records_metadata['records'];
            $growthMetrics = [
                'growth_since_first_record' => $records_metadata['growthSinceFirstRecord'],
                'average_monthly_growth' => $records_metadata['avgMonthlyGrowth'],
                'compound_annual_growth_rate' => $records_metadata['compoundAnnualGrowthRate']
            ];
            $growthSummary = $records_metadata['summary'];
                // dd($financialprojection);
            $company = $company_instance; // For backward compatibility in the view

            $areasOfExpertiseQuery = Expertise::whereNull('parent_id');
            if (Schema::hasColumn('expertises', 'status')) {
                $areasOfExpertiseQuery->where('status', 'active');
            }
            $areasOfExpertise = $areasOfExpertiseQuery
                ->orderBy('title')
                ->get()
                ->map(function ($experty) use ( $l ) {
                    
                    $output = new stdClass;
                    $output->id = $experty->id;
                    $output->name = $l === 'en' ? $experty->title : ( $experty->title_ar ?? $experty->title);
                    return $output;
                })
                ->values()
                ->toArray();
            $hasAreasOfExpertise = count($areasOfExpertise) > 0;
            $companyExpertiseIds = $company_instance
                ? $company_instance->expertises()->pluck('expertises.id')->map(fn($v) => (int) $v)->values()->toArray()
                : [];

            return view('admin/clients/company', compact(
                'client',
                '_countries',
                'founders',
                'shareholders',
                'investors',
                'Currencies',
                'milestones',
                'Achieveds',
                'Planneds',
                'Archiveds',
                // 'teamMembers',
                'states',
                'countries',
                'sectors',
                'funding_rounds',
                'Setting',
                'Stages',
                'technologies',
                'technologiesSelected',
                'industries',
                'employees',
                'data',
                'financialprojection',
                'valuation_date',
                'getCurrancy',
                'historicalValuationDates',
                'growth_records',
                'years',
                'growthMetrics',
                'growthSummary',
                'company',
                'is_currency_disabled',
                'areasOfExpertise',
                'companyExpertiseIds',
                'hasAreasOfExpertise',
            ));
        } catch (Exception $e) {
            //log what happend
            dd($e);
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function deleteImage($id)
    {
        $user = Auth::user();
        $company = Company::where('id', $user->company_id)->first();
        $company->logo = null;
        $company->save();
        return response()->json(['status' => 'success']);
    }
    /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrStore(StoreUpdateClientCompanyRequest $request, $id = null)
    {
        
        try {
            $user = Auth::user();
            if (!is_null($id)) {
                if ($user->hasanyRole('Admin')) {
                    $id = $request->id;
                    // return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                }
                if ($user->hasanyRole('organizationManager')) {
                    $check = DB::select("SELECT
                            *
                        FROM
                            companies c
                        LEFT JOIN
                            cycles cy ON cy.id = c.cycle
                        LEFT JOIN
                            organizations o ON o.id = cy.organization_id
                        WHERE
                            c.id = $id 
                            AND o.user_id = " . $user->id);

                    $check = collect($check)->first();
                   
                    if (empty($check)) {
                        return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                    } else {
                        $id = $check->id;
                    }
                }
                if (is_null($id)) {
                    return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                }
                $companyId = intval($id);
                $company = Company::find($companyId);
            } else {
                $companyId = intval($user->company_id);
                $company = Company::find($companyId);
            }
            $data = $request->all();

            // Update $data with decoded values if they were decoded in validation
            if ($request->has('tech_tags') && is_array($request->tech_tags)) {
                $data['tech_tags'] = $request->tech_tags;
            }

            if ($companyId) {
                $result = Company::find($companyId);
                if (empty($result)) {
                    $result = new Company();
                    $msgtype = 'AddComp';
                } else {
                    $msgtype = 'UpdateComp';
                }
            } else {
                $result = new Company();
                $msgtype = 'AddComp';
            }

            // Every tab except Startup Info is locked until the startup itself
            // exists. editOrCreate() auto-creates a blank company row so the tab
            // partials have something to render against, which means $result can
            // be a stub with no name -- saving another tab onto it would attach
            // data to a startup the founder has not created yet (GV-9668).
            if ($data['CompanyN'] != CompanyFormStep::BasicInfo->value
                && trim((string) $result->name_en) === '') {
                // The classification tab posts over AJAX and treats any 2xx as a
                // success, so a redirect here would surface a false "saved" toast.
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('admin.please_complete_startup_info'),
                    ], 422);
                }

                return redirect()
                    ->route('company.profile_view_mine')
                    ->with(['msg' => 'please_complete_startup_info', 'class' => 'danger']);
            }

            if ($data['CompanyN'] == CompanyFormStep::BasicInfo->value) {

                $l = $request->input('locale', app()->getLocale());
                if ($l == 'en') {
                    $allCategories = [
                        'Food & Beverage',
                        'Technology',
                        'App / Website',
                        'Housewares / Home Design',
                        'Health / Wellness / Nutrition',
                        'Fitness',
                        'Sports',
                        'Beauty',
                        'Clothing / Fashion',
                        'Toys / Games',
                        'Children',
                        'Entertainment / Experiential',
                        'Pets',
                        'Music',
                        'Holiday',
                        'Other',
                    ];
                } else {
                    $allCategories = [
                        'الأطعمة والمشروبات',
                        'التكنولوجيا',
                        'تطبيق / موقع إلكتروني',
                        'الأدوات المنزلية / تصميم المنزل',
                        'الصحة / العافية / التغذية',
                        'اللياقة البدنية',
                        'الرياضة',
                        'الجمال',
                        'الملابس / الأزياء',
                        'الألعاب / الألعاب الإلكترونية',
                        'الأطفال',
                        'الترفيه / التجارب',
                        'الحيوانات الأليفة',
                        'الموسيقى',
                        'العطلات',
                        'أخرى',
                    ];
                }
                $validatedData = $request->validate([
                    'name_en' => 'required|string|max:255',
                    'country_id' => 'required|exists:countries,id',
                    'state_id' => 'required|exists:states,id',
                    'sector' => 'nullable|string|max:255',
                    'address_en' => 'required|string|max:500',
                    'est_date' => 'required|date',
                    'years_of_operations' => 'required|string',
                    'product_or_service' => 'required|string|in:Product,Service,Product + Service',
                    'business_categories' => 'nullable|array',
                    'other_category' => 'nullable|string|max:255',
                    'Commercial_Register' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                    'Tax_Card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                    'previous_incubation' => 'required|in:0,1',
                    'previous_incubator' => 'nullable|required_if:previous_incubation,1|string|max:150',
                    'company_registration_type' => 'required|in:Not registered yet,Individual (Sole Proprietorship),Limited Liability Company (LLC),One Person Company (OPC),Partnership,Shareholding,Other',
                    'products_services_description' => 'nullable|string|max:1000',
                ], [
                    'name_en.required' => __('admin.Name is required'),
                    'country_id.required' => __('admin.Country is required'),
                    'country_id.exists' => __('admin.Invalid Country selected'),
                    'state_id.required' => __('admin.State is required'),
                    'state_id.exists' => __('admin.Invalid State selected'),
                    'sector.required' => __('admin.Sector is required'),
                    'address_en.required' => __('admin.Address is required'),
                    'est_date.required' => __('admin.Established Date must be a valid date'),
                    'years_of_operations.required' => __('admin.Years of Operations is required'),
                    'product_or_service.required' => __('admin.Product or Service is required'),
                    'product_or_service.in' => __('admin.Invalid Product or Service selection'),
                    'Commercial_Register.file' => __('admin.invalid_file_format'),
                    'Commercial_Register.mimes' => __('admin.invalid_file_format'),
                    'Commercial_Register.max' => __('admin.file_size_exceeds'),
                    'Tax_Card.file' => __('admin.invalid_file_format'),
                    'Tax_Card.mimes' => __('admin.invalid_file_format'),
                    'Tax_Card.max' => __('admin.file_size_exceeds'),
                    'previous_incubation.required' => __('admin.this_field_is_required'),
                    'previous_incubation.in' => __('admin.this_field_is_required'),
                    'previous_incubator.required_if' => __('admin.this_field_is_required'),
                    'previous_incubator.max' => __('admin.previous_incubator_max_length'),
                    'company_registration_type.required' => __('admin.this_field_is_required'),
                    'company_registration_type.in' => __('admin.this_field_is_required'),
                    'products_services_description.max' => __('admin.products_services_description_max_length'),
                ]);

                // Custom Validation for Mandatory Files if "Have Registered Startup" is Yes
                if ($data['CreatedTax'] == 1) {
                    $hasCommercialRegister = $request->hasFile('Commercial_Register') || ($company->Commercial_Register && $company->Commercial_Register != 'null');
                    $hasTaxCard = $request->hasFile('Tax_Card') || ($company->Tax_Card && $company->Tax_Card != 'null');

                    if (!$hasCommercialRegister) {
                        throw ValidationException::withMessages(['Commercial_Register' => __('admin.commercial_register_required')]);
                    }
                    if (!$hasTaxCard) {
                        throw ValidationException::withMessages(['Tax_Card' => __('admin.tax_card_required')]);
                    }
                }

                // Handle "Other" category logic
                if (in_array('Other', $validatedData['business_categories'] ?? [])) {
                    $validatedData['business_categories'] = array_diff($validatedData['business_categories'], ['Other']);
                    if (!empty($validatedData['other_category'] ?? '')) {
                        $validatedData['business_categories'][] = 'Other';
                        $validatedData['business_categories'][] = $validatedData['other_category'];
                    }
                }

                $validatedData['business_categories'] = json_encode($validatedData['business_categories']);

                if ($data['CreatedTax'] == 0) {
                    $CreatedTax = 0;
                    $cuntryReg = 0;
                    $Commercial_Register = '';
                    $Tax_Card = '';
                } else {
                    // Validate both documents before replacing either of them.
                    app(\App\Services\FileManagerService::class)->assertAllAllowed([
                        [$request->file('Commercial_Register'), 'common'],
                        [$request->file('Tax_Card'), 'common'],
                    ]);
                    if ($request->hasFile('Commercial_Register')) {
                        $file = $request->file('Commercial_Register');
                        $Commercial_Register = app(\App\Services\FileManagerService::class)->update($file, $company->Commercial_Register, $companyId . '/documents', 'document', 'common');
                    } else {
                        $Commercial_Register = $company->Commercial_Register ?? 'null';
                    }
                    if ($request->hasFile('Tax_Card')) {
                        $file = $request->file('Tax_Card');
                        $Tax_Card = app(\App\Services\FileManagerService::class)->update($file, $company->Tax_Card, $companyId . '/documents', 'document', 'common');
                    } else {
                        $Tax_Card = $company->Tax_Card ?? 'null';
                    }
                    $CreatedTax = $data['CreatedTax'];
                    $cuntryReg = $data['Country_Register'];
                    // $Commercial_Register = $data['Commercial_Register'];
                    // $Tax_Card = $data['Tax_Card'];
                }
                $result->name_en = $data['name_en'];
                $result->name_ar = $data['name_en'];
                $result->country_id = $data['country_id'];
                $result->state_id = $data['state_id'];
                $result->sector = $data['sector'] ?? null;
                $result->address_en = $data['address_en'];
                $result->address_ar = $data['address_en'];
                $result->est_date = $data['est_date'];
                $result->years_of_operations = $data['years_of_operations'];
                $result->product_or_service = $data['product_or_service'];
                $result->business_categories = $validatedData['business_categories'];
                $result->CreatedTax = $CreatedTax;
                $result->Commercial_Register = $Commercial_Register;
                $result->Tax_Card = $Tax_Card;
                $result->Country_Register_id = $cuntryReg ?? 0;
                $result->previous_incubation = $data['previous_incubation'];
                $result->previous_incubator = $data['previous_incubation'] === '1'
                    ? ($data['previous_incubator'] ?? null)
                    : null;
                $result->company_registration_type = $data['company_registration_type'];
                $result->products_services_description = $data['products_services_description'] ?? null;
            }
            if ($data['CompanyN'] == CompanyFormStep::ContactInfo->value) {
                $validatedData = $request->validate([
                    'phone' => 'required|numeric|digits_between:8,15',
                    'WhatsApp' => 'nullable',
                    'email' => 'required|email|max:255',
                    'website' => 'nullable|url', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                    'img_url' => 'nullable', // يجب أن يكون رابط صالح
                    'facebook' => 'nullable|url', // يجب أن يكون إما نعم أو لا
                    'twitter' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'linkedin' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'youtube' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'google_map' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'instagram' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'tiktok' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                ], [
                    'phone.required' => __('admin.Phone is required'),
                    'phone.numeric' => __('admin.Phone must be a valid number'),
                    'phone.digits_between' => __('admin.Phone must be between 8 and 15 digits'),
                    'email.required' => __('admin.E-mail is required'),
                    'email.email' => __('admin.Invalid E-mail format'),
                    'email.max' => __('admin.E-mail must not exceed 255 characters'),
                    'website.url' => __('admin.Website_Not_URL'),
                    'facebook.url' => __('admin.facebook_Not_URL'),
                    'twitter.url' => __('admin.twitter_Not_URL'),
                    'linkedin.url' => __('admin.linkedin_Not_URL'),
                    'youtube.url' => __('admin.youtube_Not_URL'),
                    'google_map.url' => __('admin.google_map_Not_URL'),
                    'instagram.url' => __('admin.instagram_url_Not_URL'),
                    'tiktok.url' => __('admin.tiktok_url_Not_URL'),
                ]);

                // في الميثود التي تعالج الطلب
                if ($request->hasFile('img_url')) {
                    // الحصول على الصورة
                    $image = $request->file('img_url');
                    // حفظ الصورة داخل القرص 'public_uploads'
                    $img_path = app(FileManagerService::class)->upload($image, 'images/logo', 'public_uploads', 'image');

                    // إنشاء رابط الصورة
                    $img_url = Storage::disk('public_uploads')->url($img_path);
                } else {
                    $img_url = $company->logo ?? 'null';
                }

                $result->country_code = $data['country_code'];  // مثال لإضافة حقل الهاتف
                $result->phone = $data['phone'];  // مثال لإضافة حقل الهاتف
                $result->country_code_w_a = $data['country_code_w_a'];  // مثال لإضافة حقل الهاتف
                $result->whatsapp = $data['WhatsApp'];  // مثال لإضافة حقل الواتساب
                $result->email = $data['email'];  // مثال لإضافة حقل البريد الإلكتروني
                $result->website = $data['website'];
                $result->logo = $img_url;
                $result->facebook = $data['facebook'];
                $result->twitter = $data['twitter'];
                $result->linkedin = $data['linkedin'];
                $result->youtube = $data['youtube'];
                $result->google_map = $data['google_map'];
                $result->instagram = $data['instagram'];
                $result->tiktok = $data['tiktok'];
            }
            if ($data['CompanyN'] == CompanyFormStep::AboutDescription->value) {
                $validatedData = $request->validate([
                    'company_description' => 'nullable|string|max:500',
                    'Sustainable_development' => 'nullable|array',
                    'about_problem' => 'nullable',
                    'about_market_opportunity' => 'nullable',
                    'about_competitors' => 'nullable',
                    'about_monetize' => 'nullable',
                    'about_challenges' => 'nullable',
                    'achievements' => 'nullable|string|max:1000',
                    'mission' => 'nullable|string|max:1000',
                    'why_this_startup' => 'nullable|string|max:1000',
                    'startup_expertise' => 'nullable|string|max:1000',
                ]);
                if (isset($data['Sustainable_development'])) {
                    $result->Sustainable_development = $data['Sustainable_development'];
                } else {
                    $result->Sustainable_development = '';
                }

                $result->info_company = $data['company_description'];
                // $result->Sustainable_development = json_encode($data['Sustainable_development']);
                $result->about_problem = $data['about_problem'];
                $result->about_market_opportunity = $data['about_market_opportunity'];
                $result->about_competitors = $data['about_competitors'];
                $result->about_monetize = $data['about_monetize'];
                $result->about_challenges = $data['about_challenges'];
                $result->achievements = $data['achievements'] ?? null;
                $result->mission = $data['mission'] ?? null;
                $result->why_this_startup = $data['why_this_startup'] ?? null;
                $result->startup_expertise = $data['startup_expertise'] ?? null;
            }
            if ($data['CompanyN'] == CompanyFormStep::FinancialInfo->value) {
                $request->validate([
                    'Currency' => 'nullable',
                    'pre_money_valuation' => 'nullable|numeric|min:0',
                    'financial_plan_description' => 'nullable|max:255',
                    'raised_fund' => 'nullable|numeric|min:0',
                    'Funding_Target' => 'nullable|numeric|min:0',
                    'stage' => 'nullable',
                    'development_stage' => 'nullable',
                    'targeted_market.*' => 'nullable',
                    'business_type' => 'nullable',
                    'generate_revenue' => 'required|in:yes,no',
                    'average_annual_revenues' => 'required_if:generate_revenue,yes|nullable|integer|min:0|max:999999999999',
                    'financial_plan' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
                ], [
                    'pre_money_valuation.numeric' => __('admin.Pre-money valuation must be a number'),
                    'pre_money_valuation.min' => __('admin.Pre-money valuation must be a positive number'),
                    'raised_fund.numeric' => __('admin.Raised fund must be a number'),
                    'raised_fund.min' => __('admin.Raised fund must be a positive number'),
                    'financial_plan_description.max' => __('admin.financial_plan_description max length is 255 characters'),
                    'generate_revenue.required' => __('admin.this_field_is_required'),
                    'generate_revenue.in' => __('admin.this_field_is_required'),
                    'average_annual_revenues.required_if' => __('admin.this_field_is_required'),
                    'average_annual_revenues.integer' => __('admin.please_enter_a_valid_number'),
                    'average_annual_revenues.min' => __('admin.value_cannot_be_less_than_zero'),
                    'average_annual_revenues.max' => __('admin.please_enter_a_valid_number'),
                    'financial_plan.file' => __('admin.financial_plan_invalid_type'),
                    'financial_plan.mimes' => __('admin.financial_plan_invalid_type'),
                    'financial_plan.max' => __('admin.financial_plan_too_large'),
                ]);

                $data['Funding_Target'] = $data['Funding_Target'] == null ? 0 : $data['Funding_Target'];
                if (!isset($data['targeted_market']) || $data['targeted_market'] == null) {
                    $data['targeted_market'] = ['null'];
                }

                if ($request->hasFile('financial_plan')) {
                    $file = $request->file('financial_plan'); // التعامل مع ملف واحد فقط
                    if ($file->isValid()) {
                        $folder = match (true) {
                            str_starts_with($file->getMimeType(), 'image/') => 'images',
                            str_starts_with($file->getMimeType(), 'video/') => 'videos',
                            in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                            default => 'others',
                        };
                        $uploadedFile = app(\App\Services\FileManagerService::class)->update($file, $company?->financial_plan, $companyId . '/financial_plan/' . $folder, 'pitch_deck', 'document');
                    }
                } else {
                    $uploadedFile = $company?->financial_plan;
                }

                $data['financial_plan_description'] = $data['financial_plan_description'] == null ? '0' : $data['financial_plan_description'];

                $result->Currency = $data['Currency'];
                $result->Pre_money_Valuation = $data['pre_money_valuation'];
                $result->raised_fund = $data['raised_fund'];
                $result->Funding_Target = $data['Funding_Target'];
                $result->stage = array_key_exists('stage', $data) ? $data['stage'] : null;
                $result->development_stage = $data['development_stage'];
                $result->targeted_market = json_encode($data['targeted_market']);
                $result->business_type = $data['business_type'];
                $result->financial_plan = $uploadedFile;
                $result->financial_plan_description = $data['financial_plan_description'];
                $result->generate_revenue = $data['generate_revenue'];
                $result->average_annual_revenues = $data['generate_revenue'] === 'yes'
                    ? $data['average_annual_revenues']
                    : null;
            }
            if ($data['CompanyN'] == CompanyFormStep::TeamInfo->value) {
                $request->validate([
                    'team_num' => 'required|string',
                ], [
                    'team_num.numeric' => __('admin.Raised fund must be a number'),
                ]);

                $result->team_num = $data['team_num'];
                $result->working_team_summary = $data['working_team_summary'];
            }
            if ($data['CompanyN'] == CompanyFormStep::PitchDeckInfo->value) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck_info' => 'nullable', // يجب أن يكون إما نعم أو لا
                    'pitch_deck_summary' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
                    'problem_facing' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
                    'solution_problem' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
                ], [
                    'pitch_deck_info.required' => __('admin.Pitch_Deck_Information is required'),
                    'pitch_deck_summary.required' => __('admin.Pitch_Deck_Summary is required'),
                    'pitch_deck_summary.string' => __('admin.Pitch_Deck_Summary must be a string'),
                    'pitch_deck_summary.max' => __('admin.Pitch_Deck_Summary max length is 500 characters'),
                ]);

                $result->pitch_deck_description = $data['pitch_deck_info'];
                $result->pitch_deck_summary = $data['pitch_deck_summary'];
                $result->problem = $data['problem_facing'];
                $result->solution = $data['solution_problem'];
            }
            if ($data['CompanyN'] == CompanyFormStep::SocialWebsite->value) {
                $validator = Validator::make($request->all(), [
                    'website' => 'nullable|url', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                    'img_url' => 'nullable', // يجب أن يكون رابط صالح
                    'facebook' => 'nullable|url', // يجب أن يكون إما نعم أو لا
                    'twitter' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'linkedin' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'youtube' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'google_map' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'instagram' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'tiktok' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                ], [
                    'website.url' => __('admin.Website_Not_URL'),
                    'facebook.url' => __('admin.facebook_Not_URL'),
                    'twitter.url' => __('admin.twitter_Not_URL'),
                    'linkedin.url' => __('admin.linkedin_Not_URL'),
                    'youtube.url' => __('admin.youtube_Not_URL'),
                    'google_map.url' => __('admin.google_map_Not_URL'),
                    'instagram.url' => __('admin.instagram_url_Not_URL'),
                    'tiktok.url' => __('admin.tiktok_url_Not_URL'),
                ]);

                // في الميثود التي تعالج الطلب
                if ($request->hasFile('img_url')) {
                    // الحصول على الصورة
                    $image = $request->file('img_url');
                    // حفظ الصورة داخل القرص 'public_uploads'
                    $img_path = app(FileManagerService::class)->upload($image, 'images/logo', 'public_uploads', 'image');
                    // إنشاء رابط الصورة
                    $img_url = Storage::disk('public_uploads')->url($img_path);
                } else {
                    $img_url = $company->logo;
                }

                $result->website = $data['website'];
                $result->logo = $img_url;
                $result->facebook = $data['facebook'];
                $result->twitter = $data['twitter'];
                $result->linkedin = $data['linkedin'];
                $result->youtube = $data['youtube'];
                $result->google_map = $data['google_map'];
                $result->instagram = $data['instagram'];
                $result->tiktok = $data['tiktok'];
            }
            if ($data['CompanyN'] == CompanyFormStep::PitchDeckFile->value) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck' => 'nullable|mimes:pdf,ppt,pptx|max:10240', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                ], [
                    'pitch_deck.mimes' => __('admin.Pitch_Deck must be a pdf or ppt file'),
                    'pitch_deck.max' => __('admin.Pitch_Deck max size is 10MB'),
                ]);

                if ($request->hasFile('pitch_deck')) {
                    $file = $request->file('pitch_deck');
                    $pitch_deck = app(\App\Services\FileManagerService::class)->update($file, $company->pitch_deck, $companyId . '/pitch_decks', 'pitch_deck', 'document');
                } else {
                    return redirect()->back()->with(['msg' => 'No_Document_Found', 'class' => 'danger'])->withInput();
                }

                $result->pitch_deck = $pitch_deck;
            }
            if ($data['CompanyN'] == CompanyFormStep::PitchDeckVideo->value) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck_video' => 'nullable', // يجب أن يكون ملف PDF او PPT وحجمه لا يتجاوز 20MB
                ]);

                if ($request->pitch_deck_video == null) {
                    return redirect()->back()->with(['msg' => 'No_Video_Found', 'class' => 'danger'])->withInput();
                }
                $result->pitch_deck_video = $request->pitch_deck_video;
            }
            if ($data['CompanyN'] == CompanyFormStep::SupplementaryDocuments->value) {
                if ($request->hasFile('documents')) {
                    $files = $request->file('documents');
                    $oldDocs = json_decode($company->documents ?? '[]', true) ?? [];
                    $uploadedFiles = app(\App\Services\FileManagerService::class)->updateMultiple($files, $oldDocs, $companyId . '/pitch_decks', 'pitch_deck', 'common');
                } else {
                    return redirect()->back()->with(['msg' => 'No_Document_Found', 'class' => 'danger'])->withInput();
                }

                $result->documents = json_encode($uploadedFiles);
            }
            if ($data['CompanyN'] == CompanyFormStep::AboutProblemLegacy->value) {
                $request->validate([
                    'about_problem' => 'required',
                    'about_market_opportunity' => 'required',
                    'about_competitors' => 'required',
                    'about_monetize' => 'required',
                    'about_challenges' => 'required',
                ], [
                    'about_problem.required' => __('admin.about_problem is required'),
                    'about_market_opportunity.required' => __('admin.about_market_opportunity is required'),
                    'about_competitors.required' => __('admin.about_competitors is required'),
                    'about_monetize.required' => __('admin.about_monetize is required'),
                    'about_challenges.required' => __('admin.about_challenges is required'),
                ]);

                $result->about_problem = $data['about_problem'];
                $result->about_market_opportunity = $data['about_market_opportunity'];
                $result->about_competitors = $data['about_competitors'];
                $result->about_monetize = $data['about_monetize'];
                $result->about_challenges = $data['about_challenges'];
            }
            if ($data['CompanyN'] == CompanyFormStep::BusinessModel->value) {
                // Business plan attachment is mandatory only when the company has none on file yet;
                // once uploaded it can be kept without re-uploading on every save.
                $businessPlanRules = ['file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480']; // max limit to be defined later from admin panel settings
                $businessPlanRules[] = empty($company?->business_plan) ? 'required' : 'nullable';

                $request->validate([
                    'business_model' => 'required',
                    'business_model_description' => 'required|string|max:255',
                    'business_plan' => $businessPlanRules,
                    'mvp_prototype_link' => 'nullable|url|max:500',
                ], [
                    'business_model.required' => __('admin.business_model is required'),
                    'business_model_description.required' => __('admin.business_model_description is required'),
                    'business_plan.required' => __('admin.this_field_is_required'),
                    'mvp_prototype_link.url' => __('startup.mvp_prototype_link_invalid_url'),
                    'mvp_prototype_link.max' => __('startup.mvp_prototype_link_invalid_url'),
                ]);

                if ($request->hasFile('business_plan')) {
                    $file = $request->file('business_plan'); // التعامل مع ملف واحد فقط
                    if ($file->isValid()) {
                        $folder = match (true) {
                            str_starts_with($file->getMimeType(), 'image/') => 'images',
                            str_starts_with($file->getMimeType(), 'video/') => 'videos',
                            in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                            default => 'others',
                        };
                        $uploadedFile = app(\App\Services\FileManagerService::class)->update($file, $company->business_plan, $companyId . '/business_plan/' . $folder, 'pitch_deck', 'document');
                    }
                } else {
                    $uploadedFile = $result->business_plan;
                }

                $result->business_model = $data['business_model'];
                $result->other_business_model = $data['other_business_model'];
                $result->business_model_description = $data['business_model_description'];
                $result->business_plan = $uploadedFile;
                $result->mvp_prototype_link = $request->input('mvp_prototype_link');
            }
            if($result->name_en == null){
                return redirect()->back()->with(['msg' => 'name_is_required', 'class' => 'danger']);
            }
            $result->save();

            if ($data['CompanyN'] == CompanyFormStep::ExpertiseTags->value) {
                $rawTags = $request->input('expertise_tags');
                if (is_string($rawTags)) {
                    $decoded = json_decode($rawTags, true);
                    $rawTags = is_array($decoded) ? $decoded : [];
                }
                if (!is_array($rawTags)) {
                    $rawTags = [];
                }
                $expertiseIds = array_values(array_unique(array_filter(array_map('intval', $rawTags))));
                $request->merge(['expertise_tags' => $expertiseIds]);

                $validator = \Validator::make($request->all(), [
                    'expertise_tags'   => 'required|array|min:1',
                    'expertise_tags.*' => 'integer|exists:expertises,id',
                ], [
                    'expertise_tags.required' => __('admin.this_field_is_required'),
                    'expertise_tags.array'    => __('admin.this_field_is_required'),
                    'expertise_tags.min'      => __('admin.this_field_is_required'),
                    'expertise_tags.*.exists' => __('admin.this_field_is_required'),
                ]);

                if ($validator->fails()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $validator->errors()->first(),
                            'errors'  => $validator->errors(),
                        ], 422);
                    }
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                $activeIdsQuery = Expertise::whereNull('parent_id')->whereIn('id', $expertiseIds);
                if (Schema::hasColumn('expertises', 'status')) {
                    $activeIdsQuery->where('status', 'active');
                }
                $activeIds = $activeIdsQuery->pluck('id')->map(fn($v) => (int) $v)->toArray();

                $result->expertises()->sync($activeIds);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('admin.startup_profile_updated'),
                    ]);
                }

                return redirect()->route('company.profile_view_mine', $result->id)
                    ->with(['msg' => 'startup_profile_updated', 'class' => 'success']);
            }

            if ($data['CompanyN'] == CompanyFormStep::Classification->value) {
                // Set up debug logging using Laravel's Log facade
                Log::debug('Starting company update process', [
                    'company_id' => $id,
                    'user_id' => $user->id,
                    'request_data' => [
                        'industry_id' => $request->industry_id,
                        'sector_ids' => $request->sector_ids,
                        'tech_tags' => $request->tech_tags,
                        'CompanyN' => $request->CompanyN,
                        'other_sector' => $request->other_sector,
                        'other_tech' => $request->other_tech
                    ]
                ]);

                // Decode tech_tags if it's a JSON string
                $requestData = $request->all();
                if (!empty($requestData['tech_tags']) && is_string($requestData['tech_tags'])) {
                    try {
                        $decoded = json_decode($requestData['tech_tags'], true);
                        if (is_array($decoded)) {
                            $requestData['tech_tags'] = $decoded;
                        } else {
                            $requestData['tech_tags'] = [];
                        }
                    } catch (\Exception $e) {
                        $requestData['tech_tags'] = [];
                    }
                } elseif (empty($requestData['tech_tags'])) {
                    $requestData['tech_tags'] = [];
                }

                // Merge decoded data back into request
                $request->merge($requestData);

                $rules = [
                    'industry_id' => 'required|exists:static_categories,id',
                    'sector_ids' => 'required',
                    'tech_tags' => 'nullable|array',
                    'tech_tags.*' => 'nullable|string|max:50',
                    'other_sector' => 'nullable|regex:/^[A-Za-z0-9\s\-\.]+$/|max:255',
                    'other_tech' => 'nullable|regex:/^[A-Za-z0-9\s\-\.]+$/|max:50',
                ];

                $messages = [
                    'industry_id.required' => __('admin.industry is required'),
                    'industry_id.exists' => __('admin.invalid industry selected'),
                    'sector_ids.required' => __('admin.sectors is required'),
                    'tech_tags.array' => __('admin.technologies must be an array'),
                    'tech_tags.*.string' => __('admin.technology name must be text'),
                    'tech_tags.*.max' => __('admin.technology name too long'),
                    'other_sector.regex' => __('admin.other sector contains invalid characters'),
                    'other_tech.regex' => __('admin.other technology contains invalid characters'),
                    'other_sector.max' => __('admin.other sector name too long'),
                    'other_tech.max' => __('admin.other technology name too long'),
                ];

                $validator = \Validator::make($request->all(), $rules, $messages);

                if ($validator->fails()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $validator->errors()->first(),
                            'errors' => $validator->errors()
                        ], 422);
                    }
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                // Normalize incoming sector and technology inputs
                // Process sector IDs
                $sectorIds = [];
                if (!empty($data['sector_ids'])) {
                    Log::debug('Processing sector IDs', ['raw' => $data['sector_ids']]);

                    try {
                        if (is_string($data['sector_ids'])) {
                            $decoded = json_decode($data['sector_ids'], true);
                            if (is_array($decoded)) {
                                $sectorIds = array_filter($decoded);
                            }
                        } elseif (is_array($data['sector_ids'])) {
                            $sectorIds = array_filter($data['sector_ids']);
                        }

                        // Validate sectors exist
                        $validSectorIds = Sector::whereIn('id', $sectorIds)->pluck('id')->toArray();
                        $invalidSectorIds = array_diff($sectorIds, $validSectorIds);

                        if (!empty($invalidSectorIds)) {
                            Log::warning('Invalid sector IDs provided', ['invalid_ids' => $invalidSectorIds]);
                            throw new \Exception(__('admin.invalid_sectors_selected'));
                        }

                        $sectorIds = $validSectorIds;

                    } catch (\Exception $e) {
                        Log::error('Error processing sector IDs', [
                            'error' => $e->getMessage(),
                            'raw_data' => $data['sector_ids']
                        ]);
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.error_processing_sectors')
                            ], 400);
                        }
                        return redirect()->back()->with([
                            'msg' => __('admin.error_processing_sectors'),
                            'class' => 'danger'
                        ])->withInput();
                    }
                }

                // Process existing tech IDs
                $techIds = [];
                if (!empty($data['tech_ids'])) {
                    if (is_string($data['tech_ids'])) {
                        try {
                            $rawTechIds = json_decode($data['tech_ids'], true);
                            if (is_array($rawTechIds)) {
                                $validTechIds = Technology::whereIn('id', $rawTechIds)->pluck('id')->toArray();
                                $techIds = array_values(array_unique($validTechIds));
                            }
                        } catch (\Exception $e) {
                            Log::error('Error processing tech IDs', ['error' => $e->getMessage()]);
                        }
                    } elseif (is_array($data['tech_ids'])) {
                        $validTechIds = Technology::whereIn('id', $data['tech_ids'])->pluck('id')->toArray();
                        $techIds = array_values(array_unique($validTechIds));
                    }
                }

                // Process tech_tags if provided (from the form)
                if (isset($data['tech_tags'])) {
                    $techTags = [];
                    if (is_string($data['tech_tags'])) {
                        try {
                            $techTags = json_decode($data['tech_tags'], true);
                            if (!is_array($techTags)) {
                                $techTags = [$techTags];
                            }
                        } catch (\Exception $e) {
                            Log::error('Error decoding tech_tags', ['error' => $e->getMessage()]);
                            $techTags = [];
                        }
                    } elseif (is_array($data['tech_tags'])) {
                        $techTags = $data['tech_tags'];
                    }

                    // Process tech tags to get or create technologies
                    foreach ($techTags as $tag) {
                        if (empty(trim($tag)))
                            continue;

                        $tech = Technology::firstOrCreate(
                            ['name' => trim($tag)],
                            ['name' => trim($tag)]
                        );

                        if ($tech && !in_array($tech->id, $techIds)) {
                            $techIds[] = $tech->id;
                        }
                    }
                }

                // Require at least one technology selection
                if (empty($techIds) && empty($techTags) && empty($request->other_tech)) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('admin.please_select_technology')
                        ], 422);
                    }
                    return redirect()->back()->with([
                        'msg' => __('admin.please_select_technology'),
                        'class' => 'danger'
                    ])->withInput();
                }

                // Validate that we have valid arrays after decoding
                if (!is_array($sectorIds)) {
                    $sectorIds = [];
                }
                if (!is_array($techIds)) {
                    $techIds = [];
                }

                // Process "Other" sector if provided
                if (!empty(trim($data['other_sector'] ?? ''))) {
                    try {
                        $sectorName = trim($data['other_sector']);

                        // Check if sector with similar name already exists (case-insensitive)
                        $existingSector = Sector::whereRaw('LOWER(name_en) = ?', [strtolower($sectorName)])
                            ->orWhereRaw('LOWER(name_ar) = ?', [strtolower($sectorName)])
                            ->first();

                        if ($existingSector) {
                            $sectorIds[] = $existingSector->id;
                            Log::info('Using existing sector', [
                                'name' => $sectorName,
                                'id' => $existingSector->id
                            ]);
                        } else {
                            // Create new sector
                            $addSector = new Sector();
                            $addSector->name_en = $sectorName;
                            $addSector->name_ar = $sectorName; // Consider translation if needed
                            $addSector->save();

                            // Associate with industry
                            $industry = StaticCategory::findOrFail($data['industry_id']);
                            $industry->sectors()->attach($addSector->id);

                            $sectorIds[] = $addSector->id;

                            Log::info('Created new sector', [
                                'name' => $sectorName,
                                'id' => $addSector->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing other sector', [
                            'name' => $sectorName ?? null,
                            'error' => $e->getMessage()
                        ]);
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.error_creating_sector')
                            ], 400);
                        }
                        return redirect()->back()->with([
                            'msg' => __('admin.error_creating_sector'),
                            'class' => 'danger'
                        ])->withInput();
                    }
                }

                // Process "Other" technology if provided
                if (!empty(trim($data['other_tech'] ?? ''))) {
                    try {
                        $techName = trim($data['other_tech']);

                        // Check if technology with similar name exists
                        $existingTech = Technology::whereRaw('LOWER(name) = ?', [strtolower($techName)])
                            ->first();

                        if ($existingTech) {
                            $techIds[] = $existingTech->id;
                            Log::info('Using existing technology', [
                                'name' => $techName,
                                'id' => $existingTech->id
                            ]);
                        } else {
                            // Create new technology
                            $addTech = new Technology();
                            $addTech->name = $techName;
                            $addTech->save();

                            $techIds[] = $addTech->id;

                            Log::info('Created new technology', [
                                'name' => $techName,
                                'id' => $addTech->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing other technology', [
                            'name' => $techName ?? null,
                            'error' => $e->getMessage()
                        ]);
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.error_creating_technology')
                            ], 400);
                        }
                        return redirect()->back()->with([
                            'msg' => __('admin.error_creating_technology'),
                            'class' => 'danger'
                        ])->withInput();
                    }
                }

                // Process freeform tech tags (create Technology rows if needed)
                if (!empty($techTags)) {
                    foreach ($techTags as $tag) {
                        // enforce max length
                        if (mb_strlen($tag) > 50) {
                            return redirect()->back()->with(['msg' => __('admin.Technology_name_too_long'), 'class' => 'danger'])->withInput();
                        }
                        // try find existing technology by name (case-insensitive)
                        $existing = Technology::whereRaw('LOWER(name) = ?', [mb_strtolower($tag)])->first();
                        if ($existing) {
                            if( !in_array($existing->id, $techIds) ) {
                                $techIds[] = $existing->id;
                            }
                        } else {
                            $newTech = new Technology();
                            $newTech->name = $tag;
                            $newTech->save();
                            if( !in_array($newTech->id, $techIds) ) {
                                $techIds[] = $newTech->id;
                            }
                        }
                    }
                }

                // Ensure company is saved and has an ID
                if (empty($result->id)) {
                    $result->save();
                }

                if (empty($result->id)) {
                    Log::error('Company ID is missing', [
                        'company_data' => $result->toArray()
                    ]);
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('admin.error_company_not_saved')
                        ], 400);
                    }
                    return redirect()->back()
                        ->with('msg', __('admin.error_company_not_saved'))
                        ->with('class', 'danger')
                        ->withInput();
                }

                // Validate industry_id exists
                if (!empty($data['industry_id'])) {
                    $industryExists = \DB::table('static_categories')->where('id', $data['industry_id'])->exists();
                    if (!$industryExists) {
                        Log::error('Invalid industry_id', ['industry_id' => $data['industry_id']]);
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.invalid_industry_selected')
                            ], 400);
                        }
                        return redirect()->back()
                            ->with('msg', __('admin.invalid_industry_selected'))
                            ->with('class', 'danger')
                            ->withInput();
                    }
                }

                $validated = $request->validate(['industry_id' => ['required', 'integer', 'exists:static_categories,id']]);
                $industryId = (int) $validated['industry_id'];

                DB::beginTransaction();
                try {
                    $timestamp = now();
                    $relations = [];

                    // Get existing relations to determine what needs to be updated
                    $existingRelations = CompanyRelation::where('company_id', $result->id)
                        ->where('user_id', $user->id)
                        ->get();

                    // Process sector relations
                    $processedSectorIds = [];
                    foreach ($sectorIds as $sectorId) {
                        if (empty($sectorId))
                            continue;

                        // Validate sector exists
                        $sectorExists = \DB::table('sectors')->where('id', $sectorId)->exists();
                        if (!$sectorExists) {
                            Log::warning('Invalid sector_id skipped', ['sector_id' => $sectorId]);
                            continue;
                        }

                        // Check if this relation already exists
                        $existingRelation = $existingRelations->where('sector_id', $sectorId)->first();

                        if ($existingRelation) {
                            // Update existing relation
                            $existingRelation->update([
                                'industry_id' => $industryId,
                                'updated_at' => $timestamp
                            ]);
                        } else {
                            // Add new relation
                            $relations[] = [
                                'company_id' => $result->id,
                                'user_id' => $user->id,
                                'industry_id' => $industryId,
                                'sector_id' => $sectorId,
                                'technology_id' => null,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp
                            ];
                        }
                        $processedSectorIds[] = $sectorId;
                    }

                    // Process technology relations
                    $processedTechIds = [];
                    foreach ($techIds as $techId) {
                        if (empty($techId))
                            continue;

                        // Validate technology exists
                        $techExists = \DB::table('technologies')->where('id', $techId)->exists();
                        if (!$techExists) {
                            Log::warning('Invalid technology_id skipped', ['technology_id' => $techId]);
                            continue;
                        }

                        // Check if this relation already exists
                        $existingRelation = $existingRelations->where('technology_id', $techId)->first();

                        if ($existingRelation) {
                            // Update existing relation
                            $existingRelation->update([
                                'industry_id' => $industryId,
                                'updated_at' => $timestamp
                            ]);
                        } else {
                            // Add new relation
                            $relations[] = [
                                'company_id' => $result->id,
                                'user_id' => $user->id,
                                'industry_id' => $industryId,
                                'sector_id' => null,
                                'technology_id' => $techId,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp
                            ];
                        }
                        $processedTechIds[] = $techId;
                    }

                    // Remove relations that are no longer selected
                    $existingRelations->each(function ($relation) use ($processedSectorIds, $processedTechIds) {
                        if (
                            ($relation->sector_id && !in_array($relation->sector_id, $processedSectorIds)) ||
                            ($relation->technology_id && !in_array($relation->technology_id, $processedTechIds))
                        ) {
                            $relation->delete();
                        }
                    });

                    // Bulk insert all relations
                    if (!empty($relations)) {
                        CompanyRelation::insert($relations);
                    }

                    DB::commit();
                    Log::info('Company relations updated', [
                        'company_id' => $result->id,
                        'sector_count' => count($sectorIds),
                        'tech_count' => count($techIds),
                        'relations_count' => count($relations)
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to update company relations', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'company_id' => $result->id ?? null,
                        'sector_ids' => $sectorIds,
                        'tech_ids' => $techIds,
                        'industry_id' => $data['industry_id'] ?? null
                    ]);

                    if ($data['CompanyN'] == CompanyFormStep::Classification->value && $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('admin.error_updating_company_relations') . ': ' . $e->getMessage()
                        ], 400);
                    }

                    return redirect()->back()
                        ->with('msg', __('admin.error_updating_company_relations'))
                        ->with('class', 'danger')
                        ->withInput();
                }
            }
            if ($data['CompanyN'] == CompanyFormStep::FinancialProjection->value) {
               
                if($data['valuation_date'] == null){
                    return redirect()->back()->with(['msg' => 'Please enter the valuation date', 'class' => 'danger']);
                }
                
                $valuation_date= Carbon::parse($data['valuation_date'])->format('Y-m-d');
                $baseDate = Carbon::createFromFormat('Y-m', $data['valuation_date']);

                // من سنة قبل لحد سنتين بعد
                $startOffset = -1;
                $yearsCount  = 4; // -1, 0, +1, +2

                for ($i = 0; $i < $yearsCount; $i++) {

                    $yearOffset = $startOffset + $i;
                    $yearDate   = $baseDate->copy()->addYears($yearOffset);

                    $updateFinancial = FinancialProjection::where('company_id', $result->id)
                        ->where('valuation_date', $valuation_date)
                       ->where('year', $yearDate->year)
                       ->first();
                    if($updateFinancial != null){
                        $data['Currency'] = $updateFinancial->currency;
                    }
                    // if($user->company->currancy == $data['Currency']){
                    //     $data['Currency'] = $user->company->currancy;
                    // }
                    
                     

                       if($updateFinancial != null && $updateFinancial->currency != $data['Currency']){
                        return redirect()->back()->with(['msg' => 'You can not add more than one currency per year', 'class' => 'danger']);
                       }
                   
                    $addFinancial = $updateFinancial ?? new FinancialProjection();

                    // $addFinancial = new FinancialProjection();
                    $addFinancial->company_id = $result->id;
                    $addFinancial->user_id = auth()->id();
                    $addFinancial->valuation_date = $valuation_date;
                    $addFinancial->year = $yearDate->year;
                    $addFinancial->currency = $data['Currency'];

                    // البيانات الرقمية
                    $addFinancial->revenues = $data["rev_$i"];
                    $addFinancial->cogs = $data["cogs_$i"];
                    $addFinancial->salaries = $data["sal_$i"];
                    $addFinancial->other_op_expenses = $data["opex_$i"];
                    $addFinancial->ebitda = $data["ebitda_$i"];
                    $addFinancial->depreciation_amortization = $data["da_$i"];
                    $addFinancial->ebit = $data["ebit_$i"];
                    $addFinancial->interest = $data["int_$i"];
                    $addFinancial->taxes = $data["tax_$i"];
                    $addFinancial->net_profit = $data["np_$i"];

                    // Working Capital
                    $addFinancial->receivables = $data["rec_$i"];
                    $addFinancial->inventory = $data["inv_$i"];
                    $addFinancial->payables = $data["pay_$i"];
                    $addFinancial->working_capital = $data["wc_$i"];
                    $addFinancial->change_in_wc = $data["dwc_$i"];

                    $addFinancial->capital_expenditures = $data["capex_$i"];
                    $addFinancial->debt_end_year = $data["debt_$i"];
                    $addFinancial->debt_change = $data["ddebt_$i"];
                    $addFinancial->free_cash_flow = $data["fcf_$i"];

                    $addFinancial->save();
                }
            
               
            }
            if (is_null($id)) {
                User::where('id', $user->id)->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

            if ($result) {
                if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                    event(new WebMessage($msgtype, $user->first_name_en));
                }

                // Use the result's ID if $id is null (new company creation)
                $redirectId = $id ?: $result->id;
                if( !trim( $redirectId ) ) { $redirectId = $result->id; }

                // Return JSON response for AJAX requests (CompanyN == 14 is classification form)
                if ($data['CompanyN'] == CompanyFormStep::Classification->value && $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('admin.startup_info_updated')
                    ]);
                }

                return redirect()->route('company.profile_view_mine', ['id' => $redirectId])->with(['msg' => 'startup_info_updated', 'class' => 'success']);
            } else {
                if ($data['CompanyN'] == CompanyFormStep::Classification->value && $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('admin.Startup_Info_Updated_Failed')
                    ], 400);
                }
                return back()->withInput()->with(['msg' => 'Startup_Info_Updated_Failed', 'class' => 'danger']);
            }
        } catch (ValidationException $e) {
            // عند وجود خطأ في التحقق، إرجاع رسائل الأخطاء للمستخدم
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            dd($e);
            // تسجيل الأخطاء الأخرى
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            if ($request->ajax()) {
               
                return response()->json([
                    'success' => false,
                    'message' => __('admin.please_complete_startup_info')
                ], 500);
            }
            return redirect()->back()->with(['msg' => 'please_complete_startup_info', 'class' => 'danger']);
        }
    }

    /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function likeService(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|numeric',
                'service_id' => 'required|numeric',
                'current_state' => 'required',
                'rate' => 'numeric',
            ]);
            if ($validator->fails())
                return $this->validationErrorsResponse($validator->errors());
            $user = Auth::user()->id;
            $current_state = $request->current_state;
            $company_id = $request->company_id;
            $service_id = $request->service_id;
            if ($current_state == 'unliked') { //Like
                $result = Like::updateOrCreate(
                    $request->only(['company_id', 'service_id']) + ['user_id' => $user],
                    $request->all() + ['user_id' => $user]
                );
            } elseif ($current_state == 'liked') { //Unlike
                Like::where('company_id', $company_id)
                    ->where('service_id', $service_id)
                    ->where('user_id', $user)
                    ->delete();
            }
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been Deleted successfully";
            $dataContent = ['row_id' => $result->id];
            if ($result)
                return $this->returnApiResult($code, $status, $message, $dataContent);
            else
                return $this->unknownErrorHappenedMsg();
        } catch (Exception $e) {
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function AddTeams(Request $request, $id = null)
    {
        if (isset($id) && !is_null($id)) {
            $user = User::where('company_id', $id)->first();
        } else {
            $user = auth()->user();
        }
        // $user = Auth::user();
        $companyId = $user->company_id;
        if ($request->TeamsNo == "Founders") {
            $validator = Validator::make($request->all(), [
                'founder_name'       => 'required|string|max:100',
                'founder_email'      => 'required|email|max:254',
                'founder_phone'      => 'nullable|string|max:20',
                'founder_linkedin'   => 'nullable|url|max:500',
                'founder_join_date'  => 'nullable|date|before_or_equal:today',
                'founder_type'       => 'required|in:Founder,Co-founder',
                'founder_commitment' => 'nullable|in:Full-time,Part-time',
            ], [
                'founder_name.required'            => __('admin.founder_name_required'),
                'founder_email.required'           => __('admin.founder_email_required'),
                'founder_email.email'              => __('admin.founder_email_invalid'),
                'founder_phone.max'                => __('admin.founder_phone_invalid'),
                'founder_linkedin.url'             => __('admin.founder_linkedin_invalid'),
                'founder_join_date.before_or_equal'=> __('admin.founder_join_date_future'),
                'founder_type.required'            => __('admin.founder_type_required'),
                'founder_type.in'                  => __('admin.founder_type_required'),
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
            }

            $email = $request->input('founder_email');
            $phone = $request->input('founder_phone');

            $result = DB::transaction(function () use ($request, $email, $phone, $companyId) {
                $baseQuery = Team_member::where('Company_id', $companyId)
                    ->where('TeamMember', 'Founder')
                    ->lockForUpdate();

                if ((clone $baseQuery)->where('Email', $email)->exists()) {
                    return ['dup' => 'founder_email', 'key' => 'admin.founder_email_exist'];
                }

                if (!empty($phone) && (clone $baseQuery)->where('phone', $phone)->exists()) {
                    return ['dup' => 'founder_phone', 'key' => 'admin.founder_phone_exist'];
                }

                $teamMember = new Team_member();
                $teamMember->Name               = $request->input('founder_name');
                $teamMember->Email              = $email;
                $teamMember->phone              = $phone;
                $teamMember->LinkedIn           = $request->input('founder_linkedin');
                $teamMember->join_date          = $request->input('founder_join_date');
                $teamMember->founder_type       = $request->input('founder_type');
                $teamMember->working_commitment = $request->input('founder_commitment');
                $teamMember->Company_id         = $companyId;
                $teamMember->TeamMember         = 'Founder';
                $teamMember->save();

                return ['teamMember' => $teamMember];
            });

            if (isset($result['dup'])) {
                if ($request->ajax()) {
                    return response()->json(['errors' => [$result['dup'] => [__($result['key'])]]], 422);
                }
                return redirect()->back()->with('error', __($result['key']));
            }

            $teamMember = $result['teamMember'];

            if ($request->ajax()) {
                return response()->json([
                    'state'   => __('admin.founder_added'),
                    'founder' => new FounderResource($teamMember),
                ]);
            } else {
                return redirect()->back()->with('success', __('admin.founder_added'));
            }
        }
        if ($request->TeamsNo == "Shareholders") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'shareholder_name' => 'required|string|max:255',
                'shareholder_role' => 'required|max:255',
                'shareholder_ownership_percentage' => 'required',
            ], [
                'shareholder_name.required' => __('admin.Name is Required Shareholder'),
                'shareholder_role.required' => __('admin.Role is Required Shareholder'),
                'shareholder_ownership_percentage.required' => __('admin.Precent is required Shareholder'),
            ]);

            // تحقق من النسب المئوية الكلية
            $totalPercentage = Team_member::where('Company_id', $companyId)->sum('PresentagShereholder');

            $newPercentage = $request->shareholder_ownership_percentage;

            // تحقق من أن النسبة المئوية الجديدة لا تتجاوز 100%
            if ($totalPercentage + $newPercentage > 100) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => [
                            'shareholder_ownership_percentage' => [__('admin.Total ownership percentage cannot exceed 100%')],
                        ],
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors(__('admin.Total ownership percentage cannot exceed 100%'))->withInput();
                }
            }
            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
            }
            $founder = 'Shareholder';
            // إنشاء وحفظ بيانات العضو في قاعدة البيانات
            $teamMember = new Team_member(); // افترض أن لديك موديل اسمه TeamMember
            $teamMember->TeamMember = $founder;
            $teamMember->Name = $request->shareholder_name;
            $teamMember->RoleShereholder = $request->shareholder_role;
            $teamMember->PresentagShereholder = $request->shareholder_ownership_percentage;
            $teamMember->Company_id = $companyId;
            $teamMember->save();

            // استجابة بعد الحفظ
            if ($request->ajax()) {
                return response()->json(['state' => __('admin.Investor_added'),]);
            } else {
                return redirect()->back()->with('success', __('admin.founder_added'));
            }
        }

        if ($request->TeamsNo == "Investors") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'investor_name' => 'required|string|max:255',
                'investment_type' => 'required|max:255',
            ], [
                'investor_name.required' => __('admin.Name is Required Investor'),
                'investment_type.required' => __('admin.Type is Required Investor'),
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
            }

            $founder = 'Investor';
            // إنشاء وحفظ بيانات العضو في قاعدة البيانات
            $teamMember = new Team_member(); // افترض أن لديك موديل اسمه TeamMember
            $teamMember->TeamMember = $founder;
            $teamMember->Name = $request->investor_name;
            $teamMember->investorType = $request->investment_type;
            $teamMember->Company_id = $companyId;
            $teamMember->save();

            // استجابة بعد الحفظ 
            if ($request->ajax()) {
                return response()->json(['state' => __('admin.Investor_added'),]);
            } else {
                return response()->json(['state' => __('admin.Investor_added'),]);
            }
        }
    }

    public function EditTeams(Request $request, $id = null)
    {
        if (isset($id) && !is_null($id)) {
            $user = User::where('company_id', $id)->first();
        } else {
            $user = auth()->user();
        }
        // $user = Auth::user();
        $companyId = $user->company_id;

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $teamMember = Team_member::where('id', $request->id)->first(); // افترض أن لديك موديل اسمه TeamMember
        if ($teamMember->TeamMember == "Founder") {
            // Company-scope guard: prevent cross-company tampering.
            if ((int) $teamMember->Company_id !== (int) $companyId) {
                return response()->json(['errors' => ['founder_email' => [__('admin.founder_email_exist')]]], 422);
            }

            $validator = Validator::make($request->all(), [
                'founder_name'       => 'required|string|max:100',
                'founder_email'      => 'required|email|max:254',
                'founder_phone'      => 'nullable|string|max:20',
                'founder_linkedin'   => 'nullable|url|max:500',
                'founder_join_date'  => 'nullable|date|before_or_equal:today',
                'founder_type'       => 'required|in:Founder,Co-founder',
                'founder_commitment' => 'nullable|in:Full-time,Part-time',
            ], [
                'founder_name.required'             => __('admin.founder_name_required'),
                'founder_email.required'            => __('admin.founder_email_required'),
                'founder_email.email'               => __('admin.founder_email_invalid'),
                'founder_phone.max'                 => __('admin.founder_phone_invalid'),
                'founder_linkedin.url'              => __('admin.founder_linkedin_invalid'),
                'founder_join_date.before_or_equal' => __('admin.founder_join_date_future'),
                'founder_type.required'             => __('admin.founder_type_required'),
                'founder_type.in'                   => __('admin.founder_type_required'),
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $email = $request->input('founder_email');
            $phone = $request->input('founder_phone');
            $founderId = $teamMember->id;

            $result = DB::transaction(function () use ($request, $teamMember, $email, $phone, $companyId, $founderId) {
                $baseQuery = Team_member::where('Company_id', $companyId)
                    ->where('TeamMember', 'Founder')
                    ->where('id', '!=', $founderId)
                    ->lockForUpdate();

                if ((clone $baseQuery)->where('Email', $email)->exists()) {
                    return ['dup' => 'founder_email', 'key' => 'admin.founder_email_exist'];
                }

                if (!empty($phone) && (clone $baseQuery)->where('phone', $phone)->exists()) {
                    return ['dup' => 'founder_phone', 'key' => 'admin.founder_phone_exist'];
                }

                $teamMember->Name               = $request->input('founder_name');
                $teamMember->Email              = $email;
                $teamMember->phone              = $phone;
                $teamMember->LinkedIn           = $request->input('founder_linkedin');
                $teamMember->join_date          = $request->input('founder_join_date');
                $teamMember->founder_type       = $request->input('founder_type');
                $teamMember->working_commitment = $request->input('founder_commitment');
                $teamMember->save();

                return ['teamMember' => $teamMember];
            });

            if (isset($result['dup'])) {
                return response()->json(['errors' => [$result['dup'] => [__($result['key'])]]], 422);
            }

            return response()->json([
                'state'   => __('admin.founder_updated'),
                'founder' => new FounderResource($result['teamMember']),
            ]);
        } elseif ($teamMember->TeamMember == "Shareholder") {

            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'shareholder_name' => 'required|string|max:255',
                'shareholder_role' => 'required|max:255',
                'shareholder_ownership_percentage' => 'required',

            ], [
                'shareholder_name.required' => __('admin.Name is Required Shareholder'),
                'shareholder_role.required' => __('admin.Role is Required Shareholder'),
                'shareholder_ownership_percentage.required' => __('admin.Precent is required Shareholder'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }


            // تحقق من النسب المئوية الكلية
            $totalPercentage = Team_member::where('Company_id', $companyId)
                ->where('id', '!=', $request->id)
                ->sum('PresentagShereholder');

            $newPercentage = $request->shareholder_ownership_percentage;

            // تحقق من أن النسبة المئوية الجديدة لا تتجاوز 100%
            if ($totalPercentage + $newPercentage > 100) {
                return response()->json([
                    'errors' => [
                        'shareholder_ownership_percentage' => [__('admin.Total ownership percentage cannot exceed 100%')],
                    ],
                ], 422); // حالة الخطأ 422
            }
            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }
            $teamMember->Name = $request->shareholder_name;
            $teamMember->RoleShereholder = $request->shareholder_role;
            $teamMember->PresentagShereholder = $request->shareholder_ownership_percentage;
            $teamMember->save();
            // استجابة بعد الحفظ
            return response()->json([
                'state' => __('admin.Shareholder_Edited'),
            ]);
        } elseif ($teamMember->TeamMember == "Investor") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'investor_name' => 'required|string|max:255',
                'investment_type' => 'required|max:255',

            ], [
                'investor_name.required' => __('admin.Name is Required Investor'),
                'investment_type.required' => __('admin.Type is Required Investor'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }

            $teamMember->Name = $request->investor_name;
            $teamMember->investorType = $request->investment_type;
            $teamMember->save();
            // استجابة بعد الحفظ
            return response()->json([
                'state' => __('admin.Investor_Edited'),
            ]);
        }
    }


    public function DeleteTeams(Request $request, $id = null)
    {
        if (isset($id) && !is_null($id)) {
            $user = User::where('company_id', $id)->first();
        } else {
            $user = auth()->user();
        }

        if (!$user) {
            return response()->json([
                'state' => false,
                'message' => __('admin.no_Team_member')
            ], 403);
        }

        $companyId = $user->company_id;

        $teamMember = Team_member::where('id', $request->id)
            ->where('Company_id', $companyId)
            ->first();

        if (!$teamMember) {
            return response()->json([
                'state' => false,
                'message' => __('admin.no_Team_member')
            ], 404);
        }

        $isFounder = $teamMember->TeamMember === 'Founder';

        $teamMember->delete();

        return response()->json([
            'state' => true,
            'message' => $isFounder
                ? __('admin.founder_deleted')
                : __('admin.DeletedSuccessfully')
        ]);
    }


    public function AddMileStone(Request $request, $id = null)
    {
        
        try {
            if (!$id) {
                return response()->json([
                    'errors' => ['company' => [__('startup.startup_not_found')]],
                ], 422);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:300',
                'description' => 'required|string|max:1000',
                'completion_percentage' => 'required|integer|min:0|max:100',
                'category' => 'required',
                'status' => 'required',
                'date' => 'required|date',
                'visibility' => 'required',
                'cohort' => 'nullable',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx,ppt,pptx|max:10240', // 10MB
            ], [
                'name.required' => __('startup.milestone_title_required'),
                'name.max' => __('startup.milestone_title_max_length'),
                'description.required' => __('startup.milestone_description_required'),
                'description.max' => __('startup.milestone_description_max_length'),
                'completion_percentage.required' => __('startup.completion_percentage_required'),
                'completion_percentage.integer' => __('startup.completion_percentage_numeric'),
                'completion_percentage.min' => __('startup.completion_percentage_range'),
                'completion_percentage.max' => __('startup.completion_percentage_range'),
                'documents.*.file' => __('startup.document_invalid_file'),
                'documents.*.mimes' => __('startup.milestone_document_invalid_type'),
                'documents.*.max' => __('startup.document_too_large'),
                'category.required' => __('startup.Please_select_a_category'),
                'status.required' => __('startup.Please_select_a_status'),
                'date.required' => __('startup.milestone_date_required'),
                'visibility.required' => __('startup.visibility_required'),

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create new milestone
            $milestone = new Milestone();
            $milestone->name = $request->name;
            $milestone->description = $request->description;
            $milestone->completion_percentage = $request->completion_percentage;
            $milestone->category = $request->category;
            $milestone->status = $request->status ?? 'Not Started';
            $milestone->completed = $request->status == 'Completed' ? 1 : 0;
            $milestone->milestone_date = $request->date;
            $milestone->visibility = $request->visibility;
            $milestone->cohort = $request->cohort;
            $milestone->Company_id = $id;
            $milestone->save();

            // Handle file uploads if any
            $documents = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $filePath = app(\App\Services\FileManagerService::class)->upload($file, 'milestones/' . $id, 'public', 'common');

                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => app(\App\Services\FileManagerService::class)->getUrl($filePath, 'public'),
                        'path' => $filePath,
                    ];
                }

                // Store documents info in milestone (you might want to create a separate table for this)
                $milestone->documents = json_encode($documents);
                $milestone->save();
            }

            return response()->json([
                'state' => __('admin.MileStone_added'),
                'success' => true,
                'milestone' => [
                    'id' => $milestone->id,
                    'name' => $milestone->name,
                    'description' => $milestone->description,
                    'completion_percentage' => $milestone->completion_percentage,
                    'category' => $milestone->category,
                    'status' => $milestone->status,
                    'visibility' => $milestone->visibility,
                    'cohort' => $milestone->cohort,
                    'milestone_date' => $milestone->milestone_date,
                    'documents' => $documents,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding milestone: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => [__('An error occurred while saving the milestone.')]],
            ], 500);
        }
    }

    public function EditMileStone(Request $request, $id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'errors' => ['company' => [__('startup.startup_not_found')]],
                ], 422);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:milestones,id',
                'name' => 'required|string|max:300',
                'description' => 'required|string|max:1000',
                'completion_percentage' => 'required|integer|min:0|max:100',
                'category' => 'required',
                'status' => 'required',
                'date' => 'required|date',
                'visibility' => 'required',
                'cohort' => 'nullable',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx,ppt,pptx|max:10240', // 10MB
                'remove_documents' => 'nullable|string', // JSON string of indices to remove
            ], [
                'id.required' => __('startup.milestone_id_required'),
                'id.exists' => __('startup.milestone_not_found'),
                'name.required' => __('startup.milestone_title_required'),
                'name.max' => __('startup.milestone_title_max_length'),
                'description.required' => __('startup.milestone_description_required'),
                'description.max' => __('startup.milestone_description_max_length'),
                'completion_percentage.required' => __('startup.completion_percentage_required'),
                'completion_percentage.integer' => __('startup.completion_percentage_numeric'),
                'completion_percentage.min' => __('startup.completion_percentage_range'),
                'completion_percentage.max' => __('startup.completion_percentage_range'),
                'documents.*.file' => __('startup.document_invalid_file'),
                'documents.*.mimes' => __('startup.milestone_document_invalid_type'),
                'documents.*.max' => __('startup.document_too_large'),
                'category.required' => __('startup.category_required'),
                'status.required' => __('startup.status_required'),
                'milestone_date.required' => __('startup.milestone_date_required'),
                'visibility.required' => __('startup.visibility_required'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Find the milestone and ensure it belongs to the company
            $milestone = Milestone::where('id', $request->id)
                ->where('Company_id', $id)
                ->first();

            if (!$milestone) {
                return response()->json([
                    'errors' => ['milestone' => [__('Milestone not found or you do not have permission to edit it.')]],
                ], 404);
            }

            // Update milestone
            $milestone->name = $request->name;
            $milestone->description = $request->description;
            $milestone->completion_percentage = $request->completion_percentage;
            $milestone->category = $request->category;
            $milestone->status = $request->status ?? 'Not Started';
            $milestone->completed = $request->status == 'Completed' ? 1 : 0;
            $milestone->milestone_date = $request->date;
            $milestone->visibility = $request->visibility;
            $milestone->cohort = $request->cohort;
            $milestone->save();

            // Handle file uploads if any (append to existing documents)
            // Force model to refresh and apply casts
            $milestone->refresh();
            $existingDocuments = $milestone->documents;

            \Log::info('EditMileStone: Initial request info', [
                'milestone_id' => $milestone->id,
                'has_files' => $request->hasFile('documents'),
                'request_files' => $request->files->keys(),
                'all_input' => array_keys($request->all()),
                'files_in_documents' => $request->hasFile('documents') ? count($request->file('documents')) : 0
            ]);

            \Log::info('EditMileStone: Processing existing documents', [
                'milestone_id' => $milestone->id,
                'documents_type' => gettype($existingDocuments),
                'documents_value' => $existingDocuments,
                'has_files' => $request->hasFile('documents')
            ]);

            // Ensure existing documents is an array (handle JSON string or null)
            if (is_string($existingDocuments)) {
                try {
                    $decoded = json_decode($existingDocuments, true);
                    $existingDocuments = is_array($decoded) ? $decoded : [];
                } catch (\Exception $e) {
                    \Log::warning('Failed to decode milestone documents JSON', [
                        'milestone_id' => $milestone->id,
                        'documents_raw' => $existingDocuments,
                        'error' => $e->getMessage()
                    ]);
                    $existingDocuments = [];
                }
            } elseif (!is_array($existingDocuments)) {
                $existingDocuments = [];
            }

            // Handle document removals
            if ($request->has('remove_documents')) {
                $removeDocuments = json_decode($request->input('remove_documents'), true);
                if (is_array($removeDocuments)) {
                    \Log::info('EditMileStone: Removing documents', [
                        'remove_indices' => $removeDocuments,
                        'milestone_id' => $milestone->id
                    ]);

                    // Remove documents from file system and filter from existing documents
                    foreach ($removeDocuments as $index) {
                        if (isset($existingDocuments[$index])) {
                            $document = $existingDocuments[$index];

                            // Delete physical file if it exists
                            if (isset($document['path'])) {
                                app(\App\Services\FileManagerService::class)->delete($document['path'], 'public');
                                \Log::info('EditMileStone: Physical file deleted', [
                                    'path' => $document['path'],
                                    'milestone_id' => $milestone->id
                                ]);
                            }
                        }
                    }

                    // Remove documents from array by filtering out the specified indices
                    $existingDocuments = array_values(array_filter($existingDocuments, function ($doc, $index) use ($removeDocuments) {
                        return !in_array($index, $removeDocuments);
                    }, ARRAY_FILTER_USE_BOTH));
                }
            }

            $newDocuments = [];

            if ($request->hasFile('documents')) {
                \Log::info('EditMileStone: Files detected', [
                    'files_count' => count($request->file('documents')),
                    'milestone_id' => $milestone->id
                ]);

                foreach ($request->file('documents') as $index => $file) {
                    $filePath = app(\App\Services\FileManagerService::class)->upload($file, 'milestones/' . $id, 'public', 'common');

                    \Log::info('EditMileStone: File uploaded', [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $filePath,
                        'index' => $index
                    ]);

                    $newDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => app(\App\Services\FileManagerService::class)->getUrl($filePath, 'public'),
                        'path' => $filePath,
                    ];
                }

                // Merge existing and new documents
                $allDocuments = array_merge($existingDocuments, $newDocuments);
                $milestone->documents = json_encode($allDocuments);
                $milestone->save();

                \Log::info('EditMileStone: Documents saved', [
                    'existing_count' => count($existingDocuments),
                    'new_count' => count($newDocuments),
                    'total_count' => count($allDocuments),
                    'milestone_id' => $milestone->id
                ]);

                // Return the merged documents directly
                $finalDocuments = $allDocuments;
            } else {
                // No new files uploaded, use existing documents
                $finalDocuments = $existingDocuments;
            }

            \Log::info('EditMileStone: Final response', [
                'milestone_id' => $milestone->id,
                'final_documents_count' => count($finalDocuments),
                'final_documents' => $finalDocuments
            ]);

            return response()->json([
                'state' => __('admin.MileStone_Edited'),
                'success' => true,
                'milestone' => [
                    'id' => $milestone->id,
                    'name' => $milestone->name,
                    'description' => $milestone->description,
                    'completion_percentage' => $milestone->completion_percentage,
                    'category' => $milestone->category,
                    'status' => $milestone->status,
                    'visibility' => $milestone->visibility,
                    'cohort' => $milestone->cohort,
                    'milestone_date' => $milestone->milestone_date,
                    'documents' => $finalDocuments,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error editing milestone: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => [__('An error occurred while updating the milestone.')]],
            ], 500);
        }
    }
    public function DeleteMileStone(Request $request, $id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'errors' => ['company' => [__('startup.startup_not_found')]],
                ], 422);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:milestones,id',
            ], [
                'id.required' => __('Milestone ID is required.'),
                'id.exists' => __('Milestone not found.'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Find the milestone and ensure it belongs to the company
            $milestone = Milestone::where('id', $request->id)
                ->where('Company_id', $id)
                ->first();

            if (!$milestone) {
                return response()->json([
                    'errors' => ['milestone' => [__('startup.milestone_not_found')]],
                ], 404);
            }

            // Delete associated files if they exist
            if ($milestone->documents && is_array($milestone->documents)) {
                $documents = $milestone->documents;
                foreach ($documents as $document) {
                    if (is_array($document) && isset($document['path']) && \Storage::disk('public')->exists($document['path'])) {
                        \Storage::disk('public')->delete($document['path']);
                    }
                }
            }

            // Delete the milestone
            $milestone->delete();

            return response()->json([
                'state' => __('startup.milestone_delete_success'),
                'success' => true,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting milestone: ' . $e->getMessage(), [
                'milestone_id' => $request->id ?? 'unknown',
                'company_id' => $id ?? 'unknown',
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'errors' => ['general' => [__('startup.milestone_delete_failed')]],
            ], 500);
        }
    }


    public function deleteDocument(Request $request)
    {
        $index = $request->input('index');
        $file = $request->input('file');

        // جلب الشركة الخاصة بالمستخدم
        $company = $request->user()->company;  // تأكد من استخدام العلاقة الصحيحة
        $documents = json_decode($company->documents, true);

        // التأكد من وجود الملف على الفهرس المحدد
        if (isset($documents[$index])) {
            $document = $documents[$index];

            // حذف الملف من قاعدة البيانات
            unset($documents[$index]);
            $company->documents = json_encode($documents);
            $company->save();

            // حذف الملف من التخزين
            Storage::disk('pitch_deck')->delete($document);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'الملف غير موجود.']);
    }

    public function companies(Request $request)
    {
        // Map the form parameters to the expected filter parameters
        $filters = [];

        // Handle search
        if ($request->filled('search')) {
            $filters['search'] = $request->get('search');
        }

        // Handle organization filter (Admin only)
        if ($request->filled('orgnization')) {
            $filters['organization'] = $request->get('orgnization');
        }

        // Handle program/cycle filter
        if ($request->filled('programFilter')) {
            $programFilter = $request->get('programFilter');
            if ($programFilter !== 'all') {
                $filters['programId'] = $programFilter;
            }
        }

        // Handle stage filter
        if ($request->filled('stage')) {
            $filters['stage'] = $request->get('stage');
        }

        // Handle sectors filter (multiple sectors)
        if ($request->filled('sectors')) {
            $sectors = $request->get('sectors');
            if (is_array($sectors) && !empty($sectors)) {
                // For multiple sectors, we'll need to modify the repository to handle this
                $filters['sectors'] = $sectors;
            }
        }
        // Handle Alumni filter
        if ($request->filled('alumni')) {
            $alumni = $request->get('alumni');
            if ($alumni !== 'all') {
                $filters['is_alumni'] = $alumni;
            }
        }
        if($request->filled('status')){
            $filters['status'] = $request->get('status');
        }

        $companies = $this->companyService->getCompanies($filters, isApi: false);

        // Pass the language variable
        $user = auth()->user();
        $l = app()->getLocale();

        $cycles = Cycle::all();
        $SustainableIcons = SustainableIcon::all();
        $stages = Stage::all()->unique('name_' . $l);
        $sectors = Sector::whereNull('parent_id')->with('children')->get();
        $selectedSectors = $request->input('sectors', []);

        $hasActiveFilters = $request->hasAny(['programFilter', 'sector', 'stage', 'orgnization', 'status', 'alumni']) || ($request->has('sectors') && !empty($request->sectors));
        $orgs = $user->user_organizations();

        $cycles = $cycles->map(function ($cycle) use ($request) {
            $cycle->selected = $request->programFilter == $cycle->id;
            return $cycle;
        });

        $sectors = $sectors->map(function ($sector) use ($request, $selectedSectors) {
            $sector->selected = in_array($sector->id, $selectedSectors);
            return $sector;
        });

        $companies->through(function ($company) use ( $request, $SustainableIcons ) {
            $isAlumni = $company->alumni == '1' || $company->alumni == 1;
            $founders = $company->teamMembers->where('TeamMember', 'Founder');
            $founders = $founders->map(function ($founder) {
                $fName = $founder->Name ?? '';
                $fInitials = implode('', array_map(function ($w) {
                    return mb_substr($w, 0, 1);
                }, explode(' ', $fName)));
                $fInitials = mb_strtoupper(mb_substr($fInitials, 0, 2));
                $founder->initials = $fInitials;
                return $founder;
            });
            $sustainable_array = json_decode($company->Sustainable_development ?: '[]') ?? [];
            foreach ($sustainable_array as $key => $Sustainable)
            {
                $icon = $SustainableIcons->where('title', $Sustainable)->first();
                $sustainable_array[$key] = [
                    'title' => $Sustainable,
                    'icon' => $icon
                ];
            }
            $initials = implode('', array_map(function ($word) {
                return mb_substr($word, 0, 1);
            }, explode(' ', preg_replace('/[^\w\s]/', '', $company->name ?? ''))));
            $yearsOp = $company->est_date ? Carbon::parse($company->est_date)->diffInYears(now()) : null;

            $company->isAlumni = $isAlumni;
            $company->founders = $founders;
            $company->sustainable_array = $sustainable_array;
            $company->initials = $initials;
            $company->yearsOp = $yearsOp;
            return $company;
        });

        return view('admin/users/companies', compact('companies', 'SustainableIcons', 'hasActiveFilters', 'orgs', 'cycles', 'stages', 'sectors'));
    }

    public function companyAlumni(Request $request, $id, $cycle_id)
    {
        $almuni = Almuni::where('cycle_id', $cycle_id)->where('company_id', $id)->with(['company'])->first();
        if(empty($almuni)){
            return redirect()->back()->with('error', 'Alumni not found');
        }
        $basicInfo   = $almuni?->basic_startup_information ?? [];
        $programInfo = $almuni?->program ?? [];
        $leaderInfo  = $almuni?->startup_leader_information ?? [];
        $legal_information = $almuni?->legal_information ?? [];
        $pitch_deck  = $almuni?->pitch_deck ?? [];
        $founders    = $almuni?->founders ?? [];
        $employees   = $almuni?->employees ?? [];
        $shareholders= $almuni?->shareholders ?? [];
        $investors   = $almuni?->investors ?? [];
        $screening   = $almuni?->screening ?? [];
        $finalScreen = $almuni?->final_screening ?? [];
        $training    = $almuni?->training ?? [];
        $milestones  = $almuni?->milestones ?? [];

        $mentorships = $almuni?->mentorship ?? [];
        $tasks       = $almuni?->tasks ?? [];

        $quizzes     = $almuni?->quizzes ?? [];
        $surveys     = $almuni?->surveys ?? [];
        $services    = $almuni?->services ?? [];

        $company = $almuni->company;
        // Decode defensively and read the first entry's step_id; an empty/missing
        // screening array previously threw "Undefined array key 0" (GV-3735).
        $screeningEntries = is_array($almuni->screening) ? $almuni->screening : (json_decode($almuni->screening, true) ?: []);
        $finalScreeningEntries = is_array($almuni->final_screening) ? $almuni->final_screening : (json_decode($almuni->final_screening, true) ?: []);
        $screeningResultsData = ScreeningEvaluationResults::results( data_get($screeningEntries, '0.step_id', 0), $company->id )[$company->id] ?? [];
        $finalScreeningResultsData = ScreeningEvaluationResults::results( data_get($finalScreeningEntries, '0.step_id', 0), $company->id )[$company->id] ?? [];

        // ── Stats calculations ──────────────────────────────────────────
        $trainingTotalMin   = collect($training)->sum(fn($t) => (int) ($t['duration'] ?? 0));
        $mentorshipTotalMin = collect($mentorships)->sum(fn($m) => (int) ($m['duration'] ?? 0));
        $trainingHours      = ['hours' => (int) floor($trainingTotalMin / 60),  'minutes' => $trainingTotalMin % 60];
        $mentorshipHours    = ['hours' => (int) floor($mentorshipTotalMin / 60), 'minutes' => $mentorshipTotalMin % 60];

        // $screeningScore      = collect($screening)->avg('overall_score') ?? 0;
        // $finalScreeningScore = collect($finalScreen)->avg('overall_score') ?? 0;
        $screeningScore      = $screeningResultsData['total'] ?? 0;
        $finalScreeningScore = $finalScreeningResultsData['total'] ?? 0;
        $screeningScore      = ( $screeningScore * 100 ) . '%';
        $finalScreeningScore = ( $finalScreeningScore * 100 ) . '%';

        $buildScreeningPhase = function (array $phaseArray) {
            $first = collect($phaseArray)->filter(fn($e) => !empty($e['step_id']))->first();
            if (!$first) {
                return ['step_id' => '—', 'step_name' => '', 'overall_score' => 0, 'date' => '—', 'criteria' => [], 'judges' => []];
            }
            $criteria = collect($first['criteria_breakdown'] ?? [])->map(fn($c) => [
                'name'  => $c['criterion'] ?? '',
                'weight' => ($c['weight'] ?? 0) . '%',
                'score' => $c['score'] ?? 0,
            ])->toArray();
            $judges = collect($first['judges'] ?? [])->map(fn($j) => [
                'name'     => $j['judge_name'] ?? '',
                'link'     => $j['judge_link'] ?? null,
                'score'    => $j['individual_score'] ?? 0,
                'comments' => implode(' · ', array_filter((array) ($j['comments'] ?? []))),
            ])->toArray();
            return [
                'step_id'       => 'STP-' . ($first['step_id'] ?? ''),
                'step_name'     => $first['step_name'] ?? '',
                'overall_score' => $first['overall_score'] ?? 0,
                'date'          => $first['date'] ?? '—',
                'criteria'      => $criteria,
                'judges'        => $judges,
            ];
        };

        $screeningData = [
            'initial' => $buildScreeningPhase($screening),
            'final'   => $buildScreeningPhase($finalScreen),
        ];

        $mentorshipCollection = collect($mentorships)->filter(fn($m) => !empty($m['mentor_name']));
        $attendedCount        = $mentorshipCollection->filter(fn($m) => ($m['attended'] ?? '') === 'Attended')->count();
        $attendanceRate       = $mentorshipCollection->count() > 0
            ? round(($attendedCount / $mentorshipCollection->count()) * 100)
            : 0;

        $milestonesCollection = collect($milestones)->filter(fn($m) => !empty($m['Milestone_title']));
        $milestonesCompleted  = $milestonesCollection->filter(fn($m) => (int)($m['Completion_percentage'] ?? 0) >= 100)->count();

        $quizzesCollection  = collect($quizzes)->filter(fn($q) => !empty($q['Quiz_title']));
        $quizzesPassed      = $quizzesCollection->filter(fn($q) => ($q['Status'] ?? '') === 'passed')->count();
        $avgQuizScore       = $quizzesCollection->count() > 0
            ? round($quizzesCollection->avg(fn($q) => (float) ($q['Score'] ?? 0)), 1)
            : 0;

        $surveysCollection  = collect($surveys)->filter(fn($s) => !empty($s['Survey_title']));
        $surveysCompleted   = $surveysCollection->filter(fn($s) => !empty($s['Date_completed']))->count();

        $inKindServices = collect($services)->filter(fn($s) => !empty($s['Service_name']))->count();

        $tasksCollection  = collect($tasks)->filter(fn($t) => !empty($t['Task_title']));
        $tasksCompleted   = $tasksCollection->filter(fn($t) => ($t['Status'] ?? '') === 'completed')->count();
        // ────────────────────────────────────────────────────────────────
        $forms       = $almuni?->submitted_forms ?? [];
        $relations   = collect($basicInfo['Relations'] ?? []);
        $industries  = ['name' => $relations->whereNotNull('industry')->first()['industry'] ?? null];
        $sectors     = ['name' => $relations->whereNotNull('sector')->first()['sector'] ?? null];
        $technologies = ['name' => $relations->whereNotNull('technologies')->first()['technologies'] ?? null];
        
        
        $startup = [
            'name'           => $basicInfo['Startup_Name'] ?? null,
            'logo'           => $basicInfo['Logo'] ?? null,
            'is_alumni'      => (bool) $almuni->alumni,
            'description'    => __('startup.overview_description'),
            'organization'   => $almuni->cycle->organization->name_ar ?? null,
            'location'       => ($basicInfo['Country'] ?? '') . ' · ' . ($basicInfo['Location'] ?? ''),
            'established_at' => $almuni->established_date,
            'business_type'  => $basicInfo['Business_Type'] ?? null,
            'development_stage' => $basicInfo['development_stage'] ?? null,
            'funding_stage'  => $basicInfo['funding_stage'] ?? null,
            'industry'       =>  $industries['name'] ?? null,
            'sector'         => $sectors['name'] ?? null,
            'team_size'      => $basicInfo['team_size'] ?? null,
            'technologies'   => $technologies['name'] ?? null,

            'program' => [
                'name'   => $programInfo[0]['Program_Name'] ?? null,
                'cohort' => $programInfo[0]['Cohort'] ?? 'N/A',
                'start'  => $programInfo[0]['Start_Date'] ?? null,
                'end'    => $programInfo[0]['Completion_Date'] ?? null,
            ],

            'sdgs' => $almuni->sustainability_goals ?? [],

            'links' => [
                'website'   => $basicInfo['Website_Link'] ?? null,
                'linkedin'  => $basicInfo['Social Media Links']['Linkedin'] ?? null,
                'twitter'   => $basicInfo['Social Media Links']['Twitter'] ?? null,
                'facebook'  => $basicInfo['Social Media Links']['Facebook'] ?? null,
                'instagram' => $basicInfo['Social Media Links']['Instagram'] ?? null,
                'youtube'   => $basicInfo['Social Media Links']['Youtube'] ?? null,
            ],
        ];
        $leader = collect($leaderInfo)->first();
       
        $leaderData = [
            'id'      => 'LD-' . ($leader['ID'] ?? ''),
            'name'    => $leader['Full_Name'] ?? null,
            'country' => $leader['Country'] ?? null, // لو مش موجودة في الداتا
            'email'   => $leader['Email'] ?? null,
            'phone'   => $leader['Phone_Number'] ?? null,
        ];

        $foundersData = collect($founders)->map(function ($founder) {
            return [
                'name'     => $founder['Full_Name'] ?? null,
                'email'    => $founder['Email'] ?? null,
                'linkedin' => $founder['LinkedIn_Profile'] ?? null,
            ];
        })->values()->toArray();

        $employeesData = collect($employees)->map(function ($employee) {
            return [
                'name'       => $employee['Full_Name'] ?? null,
                'job'        => $employee['Job_Title'] ?? null,
                'gender'     => $employee['Gender'] ?? null,
                'employment' => $employee['Employment_Type'] ?? null,
                'start_date' => $employee['Start Date'] ?? null,
            ];
        })->values()->toArray();

        $teamData = [
            'leader'    => $leaderData,
            'founders'  => $foundersData,
            'employees' => $employeesData,
        ];

        $shareholdersData = collect($shareholders)->map(function ($shareholder) {
            return [
                'name'     => $shareholder['Full_Name'] ?? null,
                'Email'    => $shareholder['Email'] ?? null,
                'role'    => $shareholder['Role'] ?? null,
                'ownership' => $shareholder['Ownership_Percentage'] ?? null,
                'text_class' => isset($shareholder['Ownership_Percentage'])
            ? (
                (float) $shareholder['Ownership_Percentage'] <= 20
                    ? 'success'
                    : (
                        (float) $shareholder['Ownership_Percentage'] <= 40
                            ? 'info'
                            : 'primary'
                    )
            )
            : null,
                ];
        })->values()->toArray();

        $investorsData = collect($investors)->map(function ($investor) {
            return [
                'name'     => $investor['Full_Name'] ?? null,
                'type'     => $investor['Type'] ?? null,
            ];
        })->values()->toArray();

        $ownershipData = [
            'shareholders' => $shareholdersData,
            'investors'    => $investorsData,
        ];

        $mentorshipData = collect($mentorships)->map(function ($mentorship) {
            $minutes = (int) $mentorship['duration'];
            $formattedDuration = $minutes < 60
                ? $minutes . ' m'
                : floor($minutes / 60) . ' h';
            $attended = $mentorship['attended'] ?? null;
            return [
                'mentor'         => $mentorship['mentor_name'] ?? null,
                'step_name'      => 'Growth mentoring',
                'date'           => $mentorship['date'] ?? null,
                'duration'       => $formattedDuration,
                'founder_attended' => $mentorship['founder_name'] ?? null,
                'attended'       => $attended,
                'attended_badge' => $attended === 'Attended' ? 'success' : 'danger',
                'rating'         => $mentorship['rating'] ?? null,
                'comments'       => $mentorship['comments'] ?? null,
            ];
        })->values()->toArray();
    
        $milestones = collect($milestones)->map(function ($milestone) {
            $download = $milestone['download'] ?? null;
            $deliverables = [];

            if (!empty($download)) {

                $files = is_array($download) ? $download : json_decode($download, true);

                if (is_array($files)) {
                    foreach ($files as $file) {

                        $pathInfo = pathinfo($file['url']);

                        $deliverables[] = [
                            'name' => $pathInfo['filename'],           
                            'type' => $pathInfo['extension'] ?? null,  
                            'link' => $file['url'],                    
                        ];
                    }
                }
            }
    
            return [
                'title' => $milestone['Milestone_title'] ?? null,
                'description' => $milestone['Description'] ?? null,
                'due_date' => $milestone['Due_date'] ?? null,
                'completion' => $milestone['Completion_percentage'], // %
                'deliverables' => $deliverables ?? null,
            ];
        })->values()->toArray();

        $tasks = collect($tasks)->map(function ($task) {
            
            return [
                'title' => $task['Task_title'] ?? null,
                'task_id' => 'TSK-' . ($task['Task_ID'] ?? ''),
                'session_id' => 'TR-' . ($task['Session_ID'] ?? ''),
                'session_title' => 'Will be Deleted',
                'description' => $task['Description'] ?? null,
                'due_date' => $task['Due_date'] ?? null,
                'completion_date' => $task['Completion_date'] ?? null,
                'mentor' => $task['Assigned_mentor'] ?? null,
                'created_by' => $task['Who_created_task'] ?? null,
                'status' => $task['Status'] ?? null,
            ];
        })->values()->toArray();

        $quizzes = collect($quizzes)->filter(fn($q) => !empty($q['Quiz_title']))->map(function ($quizze) {
            $raw = $quizze['Status'] ?? null;
            // handle both old format (true/false/1/0/'') and new format ('passed'/'failed'/'not_started')
            if ($raw === 'passed' || $raw === true || $raw === 1) {
                $statusLabel = __('startup.passed');
                $statusBadge = 'success';
            } elseif ($raw === 'failed' || ($raw === false && $raw !== null && $raw !== '')) {
                $statusLabel = __('startup.failed');
                $statusBadge = 'danger';
            } else {
                $statusLabel = __('startup.not_started');
                $statusBadge = 'secondary';
            }
            $resultId = $quizze['Result_ID'] ?? null;
            $dateCompleted = !empty($quizze['Date_completed']) ? $quizze['Date_completed'] : null;
            $founder       = !empty($quizze['founder_completed_quiz']) ? $quizze['founder_completed_quiz'] : null;
            $score         = ($quizze['Score'] !== null && $quizze['Score'] !== '') ? $quizze['Score'] : null;
            return [
                'title'          => $quizze['Quiz_title'] ?? null,
                'result_url'     => $resultId ? route('quizzes.quiz_results_view_details', $resultId) : null,
                'date_completed' => $dateCompleted,
                'founder'        => $founder,
                'score'          => $score,
                'status_label'   => $statusLabel,
                'status_badge'   => $statusBadge,
            ];
        })->values()->toArray();

        $surveys = collect($surveys)->map(function ($survey, $index) {
            $rawAnswers = $survey['Survey_answers'] ?? null;
            $answers = is_array($rawAnswers) ? array_filter($rawAnswers, fn($a) => !empty($a['question'])) : [];
            $isAnswered = !empty($survey['Date_completed']);
            return [
                'survey_id'      => 'SV-' . ($survey['Survey_ID'] ?? ''),
                'title'          => $survey['Survey_title'] ?? null,
                'program_step'   => $survey['program_step'] ?? null,
                'date_completed' => $survey['Date_completed'] ?? null,
                'is_answered'    => $isAnswered,
                'answers'        => array_values($answers),
                'modal_id'       => 'surveyModal' . $index,
            ];
        })->values()->toArray();

        $services = collect($services)->map(function ($service) {
            return [
                'name'        => $service['Service_name'] ?? null,
                'description' => $service['Description'] ?? null,
            ];
        })->values()->toArray();

       $forms = collect($forms)
                ->groupBy('ID')
                ->map(function ($group, $formId) {

                    $first = $group->first();

                    return [
                        'title'           => $first['Form_Title'] ?? null,
                        'step_name'       => $first['step_title'] ?? null,
                        'submission_date' => $first['Submission_Date'],
                        'modal_id'        => 'formModal' . $formId,

                        'fields' => $group->map(function ($form) {
                            return [
                                'name' => $form['Field_Name'] ?? null,
                                'type' => $form['Field_Type'] ?? null,
                                'value' => $form['Submitted_Value'] ?? null,
                                'attachment' => $form['Attachment_Link'] ?? null,
                            ];
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();
            
                $pitch_deck = collect($pitch_deck)->map(function ($deck) {
                    return [
                        'pitch_deck' => $deck['pitch_deck'] ?? null,
                        'pitch_deck_video' => $deck['pitch_deck_video'] ?? null,
                        'description' => $deck['pitch_deck_description'] ?? null,
                        'summary' => $deck['pitch_deck_summary'] ?? null,
                    ];
                })->values()->toArray();
    

        return view('admin.clients.startup.company_alumni', compact(
                'startup',
                'teamData',
                'ownershipData',
                'mentorshipData',
                'milestones',
                'tasks',
                'quizzes',
                'surveys',
                'services',
                'forms',
                'legal_information',
                'pitch_deck',
                'training',
                'screeningData',
                'trainingHours',
                'mentorshipHours',
                'screeningScore',
                'finalScreeningScore',
                'attendanceRate',
                'milestonesCompleted',
                'milestonesCollection',
                'quizzesPassed',
                'quizzesCollection',
                'avgQuizScore',
                'surveysCompleted',
                'surveysCollection',
                'inKindServices',
                'tasksCompleted',
                'tasksCollection',
                'screeningResultsData',
                'finalScreeningResultsData',
            )
        );
    }


    public function addEmployee(Request $request, $id)
    {
        try {
            if ($id) {
                $companyId = $id;

                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager|registered')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'employment_type' => 'required|in:full-time,part-time,contractor,intern',
                'hiring_date' => 'required|date',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240'
            ], [
                'full_name.required' => 'Employee name is required',
                'job_title.required' => 'Job title is required',
                'gender.required' => 'Gender is required',
                'employment_type.required' => 'Employment type is required',
                'hiring_date.required' => 'Hiring date is required',
                'documents.*.mimes' => 'Documents must be PDF, JPG, PNG, or DOCX files',
                'documents.*.max' => 'Each document must be less than 10MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = new Employee();
            $employee->company_id = $companyId;
            $employee->full_name = $request->full_name;
            $employee->job_title = $request->job_title;
            $employee->gender = $request->gender;
            $employee->employment_type = $request->employment_type;
            $employee->hiring_date = $request->hiring_date;

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $documents = [];
                foreach ($request->file('documents') as $index => $file) {
                    $path = app(\App\Services\FileManagerService::class)->upload($file, 'employees/' . $id . '/documents', 'public', 'common');

                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => app(\App\Services\FileManagerService::class)->getUrl($path, 'public'),
                        'path' => $path
                    ];
                }
                $employee->documents = $documents;
            }

            $employee->save();
            return response()->json([
                'success' => true,
                'employee' => $employee,
                'state' => 'Employee added successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error adding employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while adding the employee.']]
            ], 500);
        }
    }

    public function editEmployee(Request $request, $id)
    {
        try {
            if ($id) {
                $companyId = $id;

                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager|registered')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }


            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:employees,id',
                'full_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'employment_type' => 'required|in:full-time,part-time,contractor,intern',
                'hiring_date' => 'required|date',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
                'remove_documents' => 'nullable|string'
            ], [
                'id.required' => 'Employee ID is required',
                'id.exists' => 'Employee not found',
                'full_name.required' => 'Employee name is required',
                'job_title.required' => 'Job title is required',
                'gender.required' => 'Gender is required',
                'employment_type.required' => 'Employment type is required',
                'hiring_date.required' => 'Hiring date is required',
                'documents.*.mimes' => 'Documents must be PDF, JPG, PNG, or DOCX files',
                'documents.*.max' => 'Each document must be less than 10MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::where('id', $request->id)
                ->where('company_id', $companyId)
                ->firstOrFail();

            $employee->full_name = $request->full_name;
            $employee->job_title = $request->job_title;
            $employee->gender = $request->gender;
            $employee->employment_type = $request->employment_type;
            $employee->hiring_date = $request->hiring_date;

            // Handle existing documents
            $existingDocs = $employee->documents ?: [];

            // Handle document removals
            if ($request->has('remove_documents')) {
                $removeDocuments = json_decode($request->input('remove_documents'), true);
                if (is_array($removeDocuments)) {
                    foreach ($removeDocuments as $index) {
                        if (isset($existingDocs[$index])) {
                            $document = $existingDocs[$index];
                            // Delete physical file
                            if (isset($document['path'])) {
                                app(\App\Services\FileManagerService::class)->delete($document['path'], 'public');
                            }
                        }
                    }
                    // Remove documents from array
                    $existingDocs = array_values(array_filter($existingDocs, function ($doc, $index) use ($removeDocuments) {
                        return !in_array($index, $removeDocuments);
                    }, ARRAY_FILTER_USE_BOTH));
                }
            }

            // Handle new document uploads
            if ($request->hasFile('documents')) {
                $newDocuments = [];
                foreach ($request->file('documents') as $index => $file) {
                    $path = app(\App\Services\FileManagerService::class)->upload($file, 'employees/' . $id . '/documents', 'public', 'common');

                    $newDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => app(\App\Services\FileManagerService::class)->getUrl($path, 'public'),
                        'path' => $path
                    ];
                }
                $existingDocs = array_merge($existingDocs, $newDocuments);
            }

            $employee->documents = $existingDocs;
            $employee->save();

            return response()->json([
                'success' => true,
                'employee' => $employee,
                'state' => 'Employee updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error editing employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while updating the employee.']]
            ], 500);
        }
    }

    public function deleteEmployee(Request $request, $id)
    {
        try {
            if ($id) {
                $companyId = $id;

                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager|registered')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            // Get employee ID from request - handle both JSON and form data
            $employeeId = null;

            // Try to get from JSON body first (for DELETE requests with JSON)
            $contentType = $request->header('Content-Type');
            if ($contentType && strpos($contentType, 'application/json') !== false) {
                $jsonData = json_decode($request->getContent(), true);
                if ($jsonData && isset($jsonData['id'])) {
                    $employeeId = $jsonData['id'];
                }
            }

            // Fallback to input/get methods (for form data)
            if (!$employeeId) {
                $employeeId = $request->input('id') ?? $request->get('id');
            }

            if (!$employeeId) {
                return response()->json([
                    'errors' => ['id' => ['Employee ID is required']]
                ], 422);
            }

            $validator = Validator::make(['id' => $employeeId], [
                'id' => 'required|exists:employees,id'
            ], [
                'id.required' => 'Employee ID is required',
                'id.exists' => 'Employee not found'
            ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //             'errors' => $validator->errors()
        //         ], 422);
        //     }

        //     $employee = Team_member::where('id', $request->id)
        //                          ->where('Company_id', $id)
        //                          ->where('TeamMember', 'Employee')
        //                          ->firstOrFail();

            // Delete associated documents
            if ($employee->documents && is_array($employee->documents)) {
                foreach ($employee->documents as $document) {
                    if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
                        Storage::disk('public')->delete($document['path']);
                    }
                }
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'state' => 'Employee deleted successfully'
            ]);
            
        } catch (Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while deleting the employee.']]
            ], 500);
        }
    }

    /**
     * Mark multiple startups as alumni in bulk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCompanyAlumniBulk(Request $request)
    {
        $request->validate([
            'company_ids' => 'required|array',
            'company_ids.*' => 'required|integer|exists:companies,id',
        ]);
        
        $companyIds = $request->input('company_ids');
        $processedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($companyIds as $id) {
                $company = Company::find($id);
                if (!$company) continue;
                if($company->cycleData->allow_non_company == 0){
                    // Validation: Must have country
                    if ($company->country_id == null) {
                        $errors[] = $company->name_en . ': ' . __('startup.You cannot add this startup to alumni because it has no country');
                        continue;
                    }
                }
                // Validation: Must be on Final Screening step (stype=5)
                $step = Step::where('id', $company->step)->first();
                if (!$step || $step->stype != 5) {
                    $errors[] = $company->name_en . ': ' . __('startup.You cannot add this startup to alumni because it not finale screening step');
                    continue;
                }

                // Skip if already alumni in this cycle
                $isAlreadyAlumni = Almuni::where('company_id', $id)->where('cycle_id', $company->cycle)->exists();
                if ($isAlreadyAlumni) {
                    continue;
                }

                $this->performMarkAsAlumni($company);
                $processedCount++;
            }

            if ($processedCount === 0 && !empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => implode("\n", $errors)
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('startup.mark_alumni_success')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Alumni Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('admin.unexpected_error') . ' (' . __('admin.Error') . ': ' . $e->getMessage() . ')'
            ], 500);
        }
    }

    public function addCompanyAlumni(Request $request, $id)
    {
        try {
            $company = Company::findOrFail($id);
            
            if($company->cycleData->allow_non_company == 0){
                // Validation: Must have country
                if ($company->country_id == null) {
                    return response()->json([
                        'success' => false,
                        'message' => __('startup.You cannot add this startup to alumni because it has no country')
                    ], 422);
                }
            }

            // Validation: Must be on Final Screening step (stype=5)
            $step = Step::where('id', $company->step)->first();
            if (!$step || $step->stype != 5) {
                return response()->json([
                    'success' => false,
                    'message' => __('startup.You cannot add this startup to alumni because it not finale screening step')
                ], 422);
            }

            // Skip if already alumni in this cycle
            $isAlreadyAlumni = Almuni::where('company_id', $id)->where('cycle_id', $company->cycle)->exists();
            if ($isAlreadyAlumni) {
                return response()->json([
                    'success' => false,
                    'message' => __('startup.already_alumni')
                ], 422);
            }

            DB::beginTransaction();
            $this->performMarkAsAlumni($company);
            DB::commit();

            // Check if AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('startup.mark_alumni_success')
                ], 200);
            }

            return redirect()->back()->with('success', __('startup.mark_alumni_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Single Alumni Error: ' . $e->getMessage());
            
            // Check if AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('startup.mark_alumni_error') . ' (' . __('admin.Error') . ': ' . $e->getMessage() . ')'
                ], 500);
            }

            return redirect()->back()->with('error', __('startup.mark_alumni_error'));
        }
    }

    /**
     * Internal logic to mark a company as alumni and create snapshots.
     */
    private function performMarkAsAlumni(Company $company)
    {
        $id = $company->id;
        try {
            $company_relations = CompanyRelation::where('company_id', $id)/*->where('user_id', $company?->user?->id)*/->get();
            
            $almuni = new Almuni();
            $almuni->user_id = $company?->user?->id;
            $almuni->cycle_id = $company->cycle;
            $almuni->company_id = $id;
            $almuni->alumni = '1';
            $almuni->established_date = date('Y-m-d H:i:s');
            $team_leaders = $this->companyService->getTeamMember($id);
            
            //Relations Data
            $relations_data = $company_relations->map(function ($relation) {
                                    return [
                                        'industry'    => $relation->industry?->name_en,
                                        'sector'      => $relation->sector?->name_en,
                                        'technologies'  => $relation->technology?->name_en,
                                    ];
                                })->values();
                                
            $basic_startup_information = [
                    'ID' => $company->id,
                    'Startup_Name' => $company->name_en,
                    'Logo' => $company->logo ?? null,
                    'Alumni' => 'Yes',
                    'Established_Date' => date('Y-m-d H:i:s'),
                    'Location' => implode(', ', array_filter([
                        $company->state->name_en ?? '',
                        $company->country->name_en ?? '',
                    ])),
                    'Country' => $company->country->name_en ?? '',
                    'Business_Type' => $company->business_type ?? '',
                    'Relations'     => $relations_data ?? [],
                    'Website_Link' => $company->website ?? '',
                    'development_stage' => $company->development_stage ?? '',
                    'funding_stage' => $company->funding_stage ?? '',
                    'team_size' => $team_leaders->count() ?? '',

                    'Social Media Links' => [
                        'Facebook' => $company->facebook ?? '',
                        'Twitter' => $company->twitter ?? '',
                        'Linkedin' => $company->linkedin ?? '',
                        'Instagram' => $company->instagram ?? '',
                        'Youtube' => $company->youtube ?? '',
                        'Google_Map' => $company->google_map ?? '',
                        'tiktok' => $company->tiktok ?? '',
                    ],
                ];
            $almuni->basic_startup_information = $basic_startup_information;

                $startup_leader_information = [];

                $hackathons = $team_leaders->where('TeamMember', 'hackathon')->values();
                foreach ($hackathons as $leader) {
                    
                    $startup_leader_information[] = [
                        'ID' => $leader->id ?? '',
                        'Full_Name' => $leader->Name ?? '',
                        'Country' => $company->country->name_en ?? '',
                        'Email' => $leader->Email ?? '',
                        'Phone_Number' => $company?->user?->phone ?? '',
                    ];
                }
                
                $almuni->startup_leader_information = $startup_leader_information;

                $getfounders = $team_leaders->where('TeamMember', 'Founder')->values();

                $founders = [];
                
                foreach ($getfounders as $founder) {
                    $founders[] = [
                        'ID'               => $founder->id ?? '',
                        'Full_Name'        => $founder->Name ?? '',
                        'Email'            => $founder->Email ?? '',
                        'LinkedIn_Profile' => $founder->LinkedIn ?? '',
                    ];
                }
                if(empty($founders)){
                    $founders[] = [
                        'ID'               => null,
                        'Full_Name'        => null,
                        'Email'            => null,
                        'LinkedIn_Profile' => null,
                    ];
                }
                $almuni->founders = $founders;
                
                $getemployees = \App\Employee::where('company_id', $company->id)->get();
                $employees = [];

                foreach ($getemployees as $employee) {
                    $employees[] = [
                        'ID'              => $employee->id ?? '',
                        'Full_Name'       => $employee->full_name ?? '',
                        'Job_Title'       => $employee->job_title ?? '',
                        'Gender'          => $employee->gender ?? '',
                        'Employment_Type' => $employee->employment_type ?? '',
                        'Start Date'      => $employee->hiring_date?->format('Y-m-d') ?? '',
                    ];
                }
                if(empty($employees)){
                    $employees[] = [
                        'ID'              => null,
                        'Full_Name'       => null,
                        'Job_Title'       => null,
                        'Gender'          => null,
                        'Employment_Type' => null,
                        'Start Date'      => null,
                    ];
                }
                $almuni->employees = $employees;

                $getShareholders = $team_leaders->where('TeamMember', 'Shareholder')->values();
                $shareholders = [];

                foreach ($getShareholders as $shareholder) {
                    $shareholders[] = [
                        'ID' => $shareholder->id,
                        'Full_Name' => $shareholder->Name,
                        // 'Country' => $company->country->name_en ?? '',
                        'Email' => $shareholder->Email,
                        // 'Phone_Number' => $shareholder->Phone,
                        'Role' => $shareholder->RoleShereholder,
                        'Ownership_Percentage' => $shareholder->PresentagShereholder
                    ];
                }
                if(empty($shareholders)) {
                    $shareholders[] = [
                        'ID' => null,
                        'Full_Name' => null,
                        // 'Country' => null,
                        'Email' => null,
                        // 'Phone_Number' => null,
                        'Role' => null,
                        'Ownership_Percentage' => null
                    ];
                }

                $almuni->shareholders = $shareholders;


                $getInvestors = $team_leaders->where('TeamMember', 'Investor')->values();
                $investors = [];

                foreach ($getInvestors as $investor) {
                    $investors[] = [
                        'ID' => $investor->id,
                        'Full_Name' => $investor->Name,
                       // 'Country' => $company->country->name_en ?? '',
                        'Email' => $investor->Email,
                      //  'Phone_Number' => $investor->Phone,
                        'Type' => $investor->investorType
                    ];
                }
                if(empty($investors)) {
                    $investors[] = [
                        'ID' => null,
                        'Full_Name' => null,
                       // 'Country' => null,
                        'Email' => null,
                      //  'Phone_Number' => null,
                        'Type' => null
                    ];
                }
                $almuni->investors = $investors;

                $almuni->sustainability_goals = json_decode($company->Sustainable_development, true) ?? [];


                    $pitch_deck[] = [
                        'pitch_deck' => $company->pitch_deck ?? '',
                        'pitch_deck_video' => $company->pitch_deck_video ?? '',
                        'pitch_deck_description' => $company->pitch_deck_description ?? '',
                        'pitch_deck_summary' => $company->pitch_deck_summary ?? '',
                    ];
                    if(empty($pitch_deck)) {
                        $pitch_deck[] = [
                            'pitch_deck' => null,
                            'pitch_deck_video' => null,
                            'pitch_deck_description' => null,
                            'pitch_deck_summary' => null,
                        ];
                    }
                $almuni->pitch_deck = $pitch_deck;


                $legal_information[] = [
                        'Registered' => $company->Commercial_Register  ? 'Registered' : 'Not Registered',
                        'Commercial_Register' => $company->Commercial_Register ?? '',
                        'Tax_Card' => $company->Tax_Card ?? '',
                        'Country_Register_id' => $company->Country_Register_id ?? '',
                    ];
                    if(empty($legal_information)) {
                        $legal_information[] = [
                            'Registered' => null,
                            'Commercial_Register' => null,
                            'Tax_Card' => null,
                            'Country_Register_id' => null,
                        ];
                    }
                $almuni->legal_information = $legal_information;

                    $cycle[] = [
                        'Program_Name' => $company->cycledata->title ?? '',
                        'ID' =>  $company->cycledata->id ?? '',
                        'Organization' =>  $company->cycledata->organization ?? '',
                        'Cohort' =>  $company->cycledata->cohort ?? '',
                        'Start_Date' => $company->cycledata?->start?->format('Y-m-d H:i') ?? '',
                        'Completion_Date' => $company->cycledata?->end?->format('Y-m-d H:i') ?? '',
                        'Type' => $company->cycledata->type ?? '',
                    ];
                    if(empty($cycle)) {
                        $cycle[] = [
                            'Program_Name' => null,
                            'ID' =>  null,
                            'Organization' =>  null,
                            'Cohort' =>  null,
                            'Start_Date' => null,
                            'Completion_Date' => null,
                            'Type' => null,
                        ];
                    }
                $almuni->program = $cycle;


            $company_id = $company->id;
            $cycle_id = $company->cycle;

            $step = Step::where('cycle_id', $cycle_id)
                ->orderBy('arr');
                
            $_stepsScreening = $step->where('stype', 2)->get();

            $screeningsEvaluations = ScreeningEvaluation::whereIn(
                    'step_id',
                    $_stepsScreening->pluck('id')
                )->get();

            $getCriteria = ScreeningEvaluationCriteria::whereIn(
                    'evaluation_id',
                    $screeningsEvaluations->pluck('id')
                )
                ->get()
                ->map(function ($criterion) {
                    $criterion->criteria_weight = 100;
                    return $criterion;
                });

            $getResult = ScreeningEvaluationResults::whereIn(
                    'evaluation_id',
                    $screeningsEvaluations->pluck('id')
                )->where('company_id', $company_id)->get();
               
            $stepsData = $screeningsEvaluations->map(function ($evaluation) use ($getResult) {
                $evaluationResults = $getResult->where('evaluation_id', $evaluation->id);

                $criteriaIds = $evaluationResults->pluck('criteria_id')->unique()->filter()->values();
                $criteriaMap = ScreeningEvaluationCriteria::whereIn('id', $criteriaIds)->get()->keyBy('id');
                $criteriaBreakdown = $evaluationResults->groupBy('criteria_id')
                    ->map(function ($criterionResults, $criteriaId) use ($criteriaMap) {
                        $criterion = $criteriaMap->get($criteriaId);
                        return [
                            'criterion' => $criterion?->title ?? 'N/A',
                            'weight'    => $criterion?->weight ?? 0,
                            'score'     => round($criterionResults->avg('score'), 2),
                        ];
                    })->values();

                $judgesData = $evaluationResults->groupBy('user_id')
                    ->map(function ($judgeResults) {
                        $judge = $judgeResults->first()->user;
                        return [
                            'judge_id'         => $judge->id ?? 0,
                            'judge_name'       => $judge->name ?? 'Unknown',
                            'judge_link'       => route('users.show', $judge->id ?? 0),
                            'individual_score' => round($judgeResults->avg('score'), 2),
                            'comments'         => $judgeResults->pluck('comment')->filter()->values()->all(),
                        ];
                    })->values();

                $overallScore = $evaluationResults->isEmpty() ? 0 : $evaluationResults->avg('score');
                $stepDate = $evaluation->step?->from
                    ? \Carbon\Carbon::parse($evaluation->step->from)->format('M Y')
                    : null;

                return [
                    'step_id'            => $evaluation->step_id ?? 0,
                    'step_name'          => $evaluation->step?->title ?? 'N/A',
                    'overall_score'      => round($overallScore, 2),
                    'date'               => $stepDate,
                    'criteria_breakdown' => $criteriaBreakdown,
                    'judges'             => $judgesData,
                ];
            });
            $screeningData = $stepsData;

                $almuni->screening = $screeningData->values()->toArray();

                    //Final Screening
                $cycle_id = $company->cycle;
                $_stepFinal = Step::where('cycle_id', $cycle_id)->where('stype', 5)->get();
                $screeningsEvaluations = ScreeningEvaluation::whereIn(
                        'step_id',
                        $_stepFinal->pluck('id')
                    )->get();

                $getCriteria = ScreeningEvaluationCriteria::whereIn(
                        'evaluation_id',
                        $screeningsEvaluations->pluck('id')
                    )
                    ->get()
                    ->map(function ($criterion) {
                        $criterion->criteria_weight = 100;
                        return $criterion;
                    });

                $getResult = ScreeningEvaluationResults::whereIn(
                        'evaluation_id',
                        $screeningsEvaluations->pluck('id')
                    )->where('company_id', $company_id)->get();
               
            $finalstepsData = $screeningsEvaluations->map(function ($evaluation) use ($getResult) {
                $evaluationResults = $getResult->where('evaluation_id', $evaluation->id);

                $criteriaIds = $evaluationResults->pluck('criteria_id')->unique()->filter()->values();
                $criteriaMap = ScreeningEvaluationCriteria::whereIn('id', $criteriaIds)->get()->keyBy('id');
                $criteriaBreakdown = $evaluationResults->groupBy('criteria_id')
                    ->map(function ($criterionResults, $criteriaId) use ($criteriaMap) {
                        $criterion = $criteriaMap->get($criteriaId);
                        return [
                            'criterion' => $criterion?->title ?? 'N/A',
                            'weight'    => $criterion?->weight ?? 0,
                            'score'     => round($criterionResults->avg('score'), 2),
                        ];
                    })->values();

                $judgesData = $evaluationResults->groupBy('user_id')
                    ->map(function ($judgeResults) {
                        $judge = $judgeResults->first()->user;
                        return [
                            'judge_id'         => $judge->id ?? 0,
                            'judge_name'       => $judge->name ?? 'Unknown',
                            'judge_link'       => route('users.show', $judge->id ?? 0),
                            'individual_score' => round($judgeResults->avg('score'), 2),
                            'comments'         => $judgeResults->pluck('comment')->filter()->values()->all(),
                        ];
                    })->values();

                $overallScore = $evaluationResults->isEmpty() ? 0 : $evaluationResults->avg('score');
                $stepDate = $evaluation->step?->from
                    ? \Carbon\Carbon::parse($evaluation->step->from)->format('M Y')
                    : null;

                return [
                    'step_id'            => $evaluation->step_id ?? 0,
                    'step_name'          => $evaluation->step?->title ?? 'N/A',
                    'overall_score'      => round($overallScore, 2),
                    'date'               => $stepDate,
                    'criteria_breakdown' => $criteriaBreakdown,
                    'judges'             => $judgesData,
                ];
            });
                $FinalscreeningData = $finalstepsData;

                $almuni->final_screening = $FinalscreeningData->values()->toArray();


                 // Training
                $steps = Step::where('cycle_id', $company->cycle)->get();

                $startup_training = TrainingSession::whereIn('step_id', $steps->pluck('id'))
                    ->get();

                $trainings = [];
                foreach ($startup_training as $training) {
                    $trainings[] = [
                        'ID' => $training->id,
                        'title' => $training->title,
                        'Trainer_course_ID' => $training->course->id ?? '',
                        'trainer_course_name' => $training->course->title ?? '',
                        'date' => $training->datetime?->format('Y-m-d H:i') ?? '',
                        'duration' => $training->duration,
                        'step' => $training->step->title,
                        'step_ID' => $training->step->id,
                        'attendance' => $training->attendance->where('user_id', $company?->user?->id)->first() ? 'Attended' : 'Not Attended',
                    ];
                }

                $almuni->training = $trainings;

                $startup_mentorship = MentorshipRequest::where('cycle_id', $company->cycle)
                ->where('user_id', $company?->user?->id)
                ->get();

                $mentorships = [];
                foreach ($startup_mentorship as $mentorship) {
                    $mentorships[] = [
                        'mentor_name' => $mentorship->mentor->first_name_en ." ". $mentorship->mentor->last_name_en,
                        'mentor_ID' => $mentorship->mentor->id ?? '',
                        'date' => $mentorship->zoom_date?->format('Y-m-d H:i') ?? '',
                        'duration' => $mentorship->period,
                        'attended' => $mentorship->done == '1' ? 'Attended' : 'Not Attended',
                        'founder_name' => $mentorship->user->first_name_en ." ". $mentorship->user->last_name_en,
                        'rating' => $mentorship->rating,
                        'comments' => $mentorship->rate_comment,
                    ];
                }
                if(empty($mentorships)) {
                    $mentorships[] = [
                        'mentor_name' => null,
                        'mentor_ID' => null,
                        'date' => null,
                        'duration' => null,
                        'attended' => null,
                        'founder_name' => null,
                        'rating' => null,
                        'comments' => null,
                    ];
                }

                $almuni->mentorship = $mentorships;


                $startup_milestones = Milestone::where('company_id', $company->id)->get();

                $milestones = [];
                foreach ($startup_milestones as $milestone) {
                    $milestones[] = [
                        'Milestone_title' => $milestone->name,
                        'Description' => $milestone->description?? '',
                        'Due_date' => $milestone->created_at?->format('Y-m-d H:i') ?? '',
                        'Completion_percentage' => $milestone->completion_percentage,
                        // 'Linked_deliverables' => $milestone->done == '1' ? 'Attended' : 'Not Attended',
                        'download' => is_array($milestone->documents) ? $milestone->documents : json_decode($milestone->documents, true),
                    ];
                }
                if(empty($milestones)) {
                    $milestones[] = [
                        'Milestone_title' => null,
                        'Description' => null,
                        'Due_date' => null,
                        'Completion_percentage' => null,
                        // 'Linked_deliverables' => null,
                        'download' => null,
                    ];
                }
                $almuni->milestones = $milestones;


                // Program Tasks
                $startup_ProgramTasks = ProgramTask::where('program_id', $company->cycle)
                    ->whereHas('assignees', function ($q) use ($company) {
                        $q->where('company_id', $company->id);
                    })->with(['assignees' => function ($q) use ($company) {
                        $q->where('company_id', $company->id);
                    }, 'creator', 'feedbacks' => function ($q) use ($company) {
                        $q->where(function($query) use ($company) {
                            $query->where('company_id', $company->id)
                                  ->orWhereNull('company_id');
                        })->orderBy('created_at', 'desc');
                    }, 'feedbacks.user'])->get();
                $MentorTasks = [];
                foreach ($startup_ProgramTasks as $ProgramTask) {
                    $pivot = $ProgramTask->assignees->first()?->pivot;
                    $isCompleted = ($pivot?->status === 'completed');
                    
                    $feedbacks = $ProgramTask->feedbacks->map(function ($fb) {
                        return [
                            'id'         => $fb->id,
                            'decision'   => $fb->decision,
                            'note'       => $fb->note,
                            'created_at' => $fb->created_at?->format('Y-m-d H:i:s') ?? '',
                            'user_name'  => $fb->user->name ?? '',
                        ];
                    })->toArray();

                    $MentorTasks[] = [
                        'Task_ID'         => $ProgramTask->id ?? '',
                        'Task_title'      => $ProgramTask->title ?? '',
                        'Description'     => $ProgramTask->description ?? '',
                        'Due_date'        => $ProgramTask->due_date?->format('Y-m-d') ?? '',
                        'Status'          => $pivot?->status ?? $ProgramTask->status ?? '',
                        'Completion_date' => $isCompleted ? ($pivot?->last_updated ?? '') : '',
                        'Assigned_mentor' => '',
                        'Who_created_task' => ($ProgramTask->creator?->first_name_en . ' ' . $ProgramTask->creator?->last_name_en) ?? '',
                        'feedbacks'       => $feedbacks,
                    ];
                }
                if(empty($MentorTasks)) {
                    $MentorTasks[] = [
                        'Task_ID' => null,
                        'Task_title' => null,
                        'Description' => null,
                        'Due_date' => null,
                        'Status' => null,
                        'Completion_date' => null,
                        'Assigned_mentor' => null,
                        'Who_created_task' => null,
                        'feedbacks' => [],
                    ];
                }
                $almuni->tasks = $MentorTasks;

                $startup_quizzes = Quizzes::where('organization_id', $company->cycledata->organization_id)->get();
                $quizzes = [];
                $companyUserId = $company?->user?->id;
                foreach ($startup_quizzes as $quizze) {
                    $result = $quizze->results()->where('user_id', $companyUserId)->first();
                    $user   = $result?->user;

                    $quizzes[] = [
                        'Quiz_ID'               => $quizze->id ?? '',
                        'Quiz_title'            => $quizze->title ?? '',
                        'Result_ID'             => $result?->id ?? '',
                        'Date_completed'        => $result?->ended_at?->format('Y-m-d H:i') ?? '',
                        'founder_completed_quiz'=> $user ? ($user->first_name_en . ' ' . $user->last_name_en) : '',
                        'Score'                 => $result?->score ?? '',
                        'Status'                => $result ? ($result->status == 1 ? 'passed' : 'failed') : 'not_started',
                    ];
                }
                if(empty($quizzes)) {
                    $quizzes[] = [
                        'Quiz_ID' => null,
                        'Quiz_title' => null,
                        'Date_completed' => null,
                        'founder_completed_quiz' => null,
                        'Score' => null,
                        'Status' => null,
                    ];
                }

                $almuni->quizzes = $quizzes;

                $surveyResponseRecords = \App\SurveyResponses::where('user_id', $company?->user?->id)
                    ->where('cycle_id', $company->cycle)
                    ->with('survey')
                    ->get();
                $surveys = [];
                foreach ($surveyResponseRecords as $response) {
                    $answered = !empty($response->responses);
                    $answers  = $answered
                        ? collect($response->responses)->map(fn($item) => [
                            'question' => $item['question'] ?? '',
                            'answer'   => $item['answer']   ?? '',
                          ])->filter()->values()
                        : collect();
                    $surveys[] = [
                        'Survey_ID'      => $response->survey_id ?? '',
                        'Survey_title'   => $response->survey?->title ?? '',
                        'program_step'   => $response->cycle?->title ?? '',
                        'Date_completed' => $answered ? $response->updated_at?->format('Y-m-d H:i') : '',
                        'Survey_answers' => $answers,
                    ];
                }
                if(empty($surveys)) {
                    $surveys[] = [
                        'Survey_ID' => null,
                        'Survey_title' => null,
                        'program_step' => null,
                        'Date_completed' => null,
                        'Survey_answers' => null,
                    ];
                }

                $almuni->surveys = $surveys;

                $startup_services= ServicesRequests::where('user_id', $company?->user?->id)->get();
                $services = [];

                foreach ($startup_services as $service) {
                    $services[] = [
                        'Service_name' => $service->service->name_en ?? '',
                        'Service_ID' => $service->service->id ?? '',
                        'Description' => $service->service->description_en ?? '',
                        'Price' => $service->service->price ?? '',
                    ];
                }
                if(empty($services)) {
                    $services[] = [
                        'Service_name' => null,
                        'Service_ID' => null,
                        'Description' => null,
                        'Price' => null,
                    ];
                }

                $almuni->services = $services;

                $formSubmissions = [];
                $l =  app()->getLocale();
                $forms = DB::table('form_data as FD')
                ->select([
                    'FD.company_id as FD_company_id',
                    'FD.user_id as FD_user_id',
                    'FD.step_id as FD_step_id',
                    'FD.question as FD_question',
                    'FD.answer as FD_answer',
                    'FD.created_at as FD_created_at',
                    'FD.updated_at as FD_updated_at',
                    'FD.deleted_at as FD_deleted_at',
                    'C.name_en as C_name_en',
                    'C.name_ar as C_name_ar',
                    'C.id as C_id',
                    DB::raw("CONCAT(U.first_name_en, ' ', U.last_name_en) as user_name_en"),
                    DB::raw("CONCAT(U.first_name_ar, ' ', U.last_name_ar) as user_name_ar"),
                    'U.id as U_id',
                    'S.title as S_title',
                    'S.id as S_id',
                    'S.cycle_id as S_cycle_id',
                    'CYC.title as CYC_title',
                    'CYC.id as CYC_id',
                    'CYC.organization_id as CYC_organization_id',
                    'O.name_en as O_name_en',
                    'O.name_ar as O_name_ar',
                    'O.id as O_id'
                ])
                ->leftJoin('companies as C', 'C.id', '=', 'FD.company_id')
                ->leftJoin('users as U', 'U.id', '=', 'FD.user_id')
                ->leftJoin('steps as S', 'S.id', '=', 'FD.step_id')
                ->leftJoin('cycles as CYC', 'CYC.id', '=', 'S.cycle_id')
                ->leftJoin('organizations as O', 'O.id', '=', 'CYC.organization_id')
                ->whereNull('FD.deleted_at')
                ->where('FD.user_id', $company?->user?->id)
                ->when($company->cycledata->organization_id != null, function ($query) use ($company) {
                    return $query->where('CYC.organization_id', $company->cycledata->organization_id);
                })
                ->get();

                $C = 1;
                $grouped = $forms->groupBy('FD_step_id');
                $data = $grouped->map(function ($group, $stepId) use ($l, &$C) {
                    $first = $group->first();

                    $item = new stdClass;
                    $item->submission_id = $C++;
                    $item->step_id = $stepId;
                    $item->startup_name = $first->{'C_name_' . $l};
                    $item->submitted_by = $group->pluck('user_name_' . $l)->unique()->implode(', ') ?? '';
                    $item->step_name = $first->S_title;
                    $item->program_name = $first->CYC_title;
                    $item->organization_name = $first->O_name_en;
                    $item->created_at = $first->FD_created_at;
                    $item->answers = $group->map(function ($item) use ($l) {
                        $value = $item->FD_answer;

                        if( is_string( $value ) && (strpos($value, '/') !== false || strpos($value, '\\') !== false) )
                        {
                            $filename = basename($value);
                            $value = [
                                'type' => 'file',
                                'path' => $value,
                                'file_name' => $filename,
                                'file_url' => asset($value),
                            ];
                        }

                        $submission = new stdClass;
                        $submission->user_id = $item->FD_user_id;
                        $submission->name = $item->{'user_name_' . $l};
                        $submission->label = trim( $item->FD_question );
                        $submission->value = $value;
                        $submission->created_at = $item->FD_created_at;
                        return $submission;
                    })->groupBy('user_id')->values();
                    return $item;
                })->values();

                foreach ($data as $item) {
                    $answers = $item->answers->collapse(); 

                    foreach ($answers as $answer) {
                        $fieldType = is_array($answer->value) ? 'file' : 'text';

                        $formSubmissions[] = [
                            'Form_Title'      => $item->step_name,
                            'ID'              => $item->submission_id,
                            'step_title'      => $item->step_name,
                            'Step_ID'         => $item->step_id,
                            'Submission_Date' => $item->created_at,

                            'Field_Name'      => $answer->label,
                            'Field_Type'      => $fieldType,
                            'Submitted_Value' => is_array($answer->value) ? $answer->value['file_name'] : $answer->value,
                            'Attachment_Link' => is_array($answer->value) ? $answer->value['file_url'] : null,

                            'Form_Inputs' => [
                                'Field_Name'      => $answer->label,
                                'Field_Type'      => $fieldType,
                                'Submitted_Value' => is_array($answer->value) ? $answer->value['file_name'] : $answer->value,
                                'Attachment_Link' => is_array($answer->value) ? $answer->value['file_url'] : null,
                            ],
                        ];
                    }
                }
                if(empty($formSubmissions)) {
                    $formSubmissions[] = [
                        'Form_Title'      => null,
                        'ID'              => null,
                        'step_title'      => null,
                        'Step_ID'         => null,
                        'Submission_Date' => null,

                        'Field_Name'      => null,
                        'Field_Type'      => null,
                        'Submitted_Value' => null,
                        'Attachment_Link' => null,

                        'Form_Inputs' => [
                            'Field_Name'      => null,
                            'Field_Type'      => null,
                            'Submitted_Value' => null,
                            'Attachment_Link' => null,
                        ],
                    ];
                }

                $almuni->submitted_forms = $formSubmissions;

            $almuni->save();

            $company->step = null;
            $company->cycle = null;
            $company->save();
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function companyPerks()
    {
        if (!auth()->user()->hasRole('Registered')) {
            abort(404);
        }

        return view('admin.clients.startup.company_perks');
    }

    public function getFinancialProjectionRealtime(Request $request, int $id)
    {
        try {
            // ----------------------------------------------------------
            // 1. Authorisation guard
            // ----------------------------------------------------------
            $user = auth()->user();

            if ($user->hasRole('registered') && $user->company_id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.NoHavePermission'),
                ], 403);
            }

            if ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
                $orgIds = $user->organizations_ids;
                $cycles = \App\Cycle::whereIn('organization_id', $orgIds)->with('companies')->get();
                $hasAccess = false;
                foreach ($cycles as $cycle) {
                    foreach ($cycle->companies as $company) {
                        if ($company->id == $id) {
                            $hasAccess = true;
                            break 2;
                        }
                    }
                }
                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => __('admin.NoHavePermission'),
                    ], 403);
                }
            }

            // ----------------------------------------------------------
            // 2. Resolve valuation date
            // ----------------------------------------------------------
            $allProjections = FinancialProjection::where('company_id', $id)
                ->whereNotNull('valuation_date')
                ->orderBy('valuation_date', 'desc')
                ->get();

            // Collect unique valuation dates (formatted as YYYY-MM)
            $historicalDates = $allProjections
                ->pluck('valuation_date')
                ->unique()
                ->map(fn ($d) => Carbon::parse($d)->format('Y-m'))
                ->unique()
                ->values()
                ->toArray();

            // Determine which valuation date to load
            $requestedDate = $request->get('valuation_date'); // e.g. "2024-01"

            if ($requestedDate) {
                $valuationDateStr = $requestedDate;
            } elseif (!empty($historicalDates)) {
                $valuationDateStr = $historicalDates[0]; // most recent
            } else {
                $valuationDateStr = date('Y-m');
            }

            $valuationYear = (int) Carbon::parse($valuationDateStr . '-01')->format('Y');

            // ----------------------------------------------------------
            // 3. Load the 4 projection rows for this date
            // ----------------------------------------------------------
            // Filter by matching year-month (ignoring day component)
            $projections = $allProjections->filter(function ($proj) use ($valuationDateStr) {
                return Carbon::parse($proj->valuation_date)->format('Y-m') === $valuationDateStr;
            })->keyBy('year');

            // Detect locked currency (if any record already has one globally for this company)
            $firstRecordOverall = $allProjections->whereNotNull('currency')->first();
            $lockedCurrency     = $firstRecordOverall?->currency ?? $user->company->Currency ?? $user->currency;
            $isCurrencyLocked   = !is_null($firstRecordOverall?->currency);

            // ----------------------------------------------------------
            // 4. Build the 4-year payload
            // ----------------------------------------------------------
            $yearOffsets = [
                0 => ['offset' => -1, 'label' => __('startup.past_year')],
                1 => ['offset' =>  0, 'label' => __('startup.forecast_year_1')],
                2 => ['offset' =>  1, 'label' => __('startup.year_2')],
                3 => ['offset' =>  2, 'label' => __('startup.year_3')],
            ];

            $yearsPayload = [];

            foreach ($yearOffsets as $index => $meta) {
                $year = $valuationYear + $meta['offset'];
                $fp   = $projections->get($year); // may be null → blank row

                $yearsPayload[$index] = [
                    'year'  => $year,
                    'label' => $meta['label'],
                    'data'  => [
                        // P&L
                        'rev'    => $fp?->revenues                ?? null,
                        'cogs'   => $fp?->cogs                    ?? null,
                        'sal'    => $fp?->salaries                ?? null,
                        'opex'   => $fp?->other_op_expenses       ?? null,
                        'ebitda' => $fp?->ebitda                  ?? null,
                        'da'     => $fp?->depreciation_amortization ?? null,
                        'ebit'   => $fp?->ebit                    ?? null,
                        'int'    => $fp?->interest                ?? null,
                        'tax'    => $fp?->taxes                   ?? null,
                        'np'     => $fp?->net_profit              ?? null,
                        // Balance sheet / Working capital
                        'rec'    => $fp?->receivables             ?? null,
                        'inv'    => $fp?->inventory               ?? null,
                        'pay'    => $fp?->payables                ?? null,
                        'wc'     => $fp?->working_capital         ?? null,
                        'dwc'    => $fp?->change_in_wc            ?? null,
                        // Cash flow
                        'capex'  => $fp?->capital_expenditures    ?? null,
                        'debt'   => $fp?->debt_end_year           ?? null,
                        'ddebt'  => $fp?->debt_change             ?? null,
                        'fcf'    => $fp?->free_cash_flow          ?? null,
                    ],
                ];
            }

            // ----------------------------------------------------------
            // 5. Return JSON
            // ----------------------------------------------------------
            return response()->json([
                'success'            => true,
                'valuation_date'     => $valuationDateStr,
                'currency'           => $lockedCurrency,
                'is_currency_locked' => $isCurrencyLocked,
                'historical_dates'   => $historicalDates,
                'years'              => $yearsPayload,
            ]);

        } catch (\Exception $e) {
            Log::error('getFinancialProjectionRealtime error: ' . $e->getMessage(), [
                'company_id' => $id,
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('admin.error_loading_financial_data'),
            ], 500);
        }
    }

    public function saveFinancialProjection(Request $request, int $id)
    {
        try {
            // ----------------------------------------------------------
            // 1. Auth guard
            // ----------------------------------------------------------
            $user = auth()->user();

            if ($user->hasRole('registered') && $user->company_id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.NoHavePermission'),
                ], 403);
            }

            if ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
                $orgIds = $user->organizations_ids;
                $cycles = \App\Cycle::whereIn('organization_id', $orgIds)->with('companies')->get();
                $hasAccess = false;
                foreach ($cycles as $cycle) {
                    foreach ($cycle->companies as $company) {
                        if ($company->id == $id) {
                            $hasAccess = true;
                            break 2;
                        }
                    }
                }
                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => __('admin.NoHavePermission'),
                    ], 403);
                }
            }

            // ----------------------------------------------------------
            // 2. Basic validation
            // ----------------------------------------------------------
            $request->validate([
                'valuation_date' => 'required|date_format:Y-m',
                'Currency' => 'required|string|max:10',
            ]);

            $valuationDateStr  = $request->input('valuation_date');
            $valuationDateFull = $valuationDateStr . '-01';
            $valuationYear     = (int) Carbon::parse($valuationDateFull)->year;
            $requestedCurrency = $request->input('Currency');

            // ADDED: Fail fast if currency is missing
            if (!$requestedCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.Currency is required'),
                ], 422);
            }
            
                   

            // ----------------------------------------------------------
            // 3. Map: index → year  (0 = past, 1 = Y0, 2 = Y+1, 3 = Y+2)
            // ----------------------------------------------------------
            $yearOffsets = [-1, 0, 1, 2];
            $fieldMap    = [
                'rev'   => 'revenues',
                'cogs'  => 'cogs',
                'sal'   => 'salaries',
                'opex'  => 'other_op_expenses',
                'ebitda'=> 'ebitda',
                'da'    => 'depreciation_amortization',
                'ebit'  => 'ebit',
                'int'   => 'interest',
                'tax'   => 'taxes',
                'np'    => 'net_profit',
                'rec'   => 'receivables',
                'inv'   => 'inventory',
                'pay'   => 'payables',
                'wc'    => 'working_capital',
                'dwc'   => 'change_in_wc',
                'capex' => 'capital_expenditures',
                'debt'  => 'debt_end_year',
                'ddebt' => 'debt_change',
                'fcf'   => 'free_cash_flow',
            ];

            // ----------------------------------------------------------
            // 4. Determine whether this is a single-field or full save
            // ----------------------------------------------------------
            $singleField = $request->input('field');
            $singleValue = $request->input('value');

            DB::beginTransaction();

            $rows = [];

            for ($i = 0; $i < 4; $i++) {
                $year = $valuationYear + $yearOffsets[$i];

                $record = FinancialProjection::firstOrNew([
                    'company_id'     => $id,
                    'valuation_date' => $valuationDateFull,
                    'year'           => $year,
                ]);

                // // Currency guard: once a currency is stored it must not change
                // if ($record->exists && $record->currency && $record->currency !== $requestedCurrency) {
                //     DB::rollBack();
                //     return response()->json([
                //         'success' => false,
                //         'message' => __('admin.You can not add more than one currency per year'),
                //     ], 422);
                // }

                $record->company_id     = $id;
                $record->user_id        = $user->id;
                $record->valuation_date = $valuationDateFull;
                $record->year           = $year;
                $record->currency       = $requestedCurrency; // FIXED: Always set from request

                // ── Single-field update ──────────────────────────────
                if ($singleField) {
                    if (preg_match('/^([a-z]+)_(\d)$/', $singleField, $m)) {
                        $shortName = $m[1];
                        $fieldIdx  = (int) $m[2];

                        if ($fieldIdx === $i && isset($fieldMap[$shortName])) {
                            $dbColumn          = $fieldMap[$shortName];
                            $record->$dbColumn = is_numeric($singleValue) ? (float) $singleValue : null;
                        }
                    }
                } else {
                    // ── Full-form update ────────────────────────────
                    foreach ($fieldMap as $short => $db) {
                        $key = "{$short}_{$i}";
                        if ($request->has($key)) {
                            $val          = $request->input($key);
                            $record->$db  = is_numeric($val) ? (float) $val : null;
                        }
                    }
                }

                $record->save();
                $rows[$i] = $record;
            }

            // ----------------------------------------------------------
            // 5. Server-side recalculation + persist calculated fields
            // ----------------------------------------------------------
            $recalculated = [];
            $prevWC       = 0.0;
            $prevDebt     = 0.0;

            foreach ($rows as $i => $r) {
                $rev   = (float)($r->revenues ?? 0);
                $cogs  = (float)($r->cogs ?? 0);
                $sal   = (float)($r->salaries ?? 0);
                $opex  = (float)($r->other_op_expenses ?? 0);
                $da    = (float)($r->depreciation_amortization ?? 0);
                $int   = (float)($r->interest ?? 0);
                $tax   = (float)($r->taxes ?? 0);
                $rec   = (float)($r->receivables ?? 0);
                $inv   = (float)($r->inventory ?? 0);
                $pay   = (float)($r->payables ?? 0);
                $capex = (float)($r->capital_expenditures ?? 0);
                $debt  = (float)($r->debt_end_year ?? 0);

                $ebitda    = $rev - $cogs - $sal - $opex;
                $ebit      = $ebitda - $da;
                $totalExpenses = $cogs + $sal + $opex + $da + $int + $tax;
                $netProfit = $rev - $totalExpenses;
                $wc        = $rec + $inv - $pay;
                $deltaWC   = ($i === 0) ? 0.0 : $wc - $prevWC;
                $debtChg   = ($i === 0) ? 0.0 : $debt - $prevDebt;
                $fcf       = $netProfit + $da - $capex - $deltaWC;

                $r->ebitda           = round($ebitda, 2);
                $r->ebit             = round($ebit, 2);
                $r->net_profit       = round($netProfit, 2);
                $r->working_capital  = round($wc, 2);
                $r->change_in_wc     = ($i === 0) ? null : round($deltaWC, 2);
                $r->debt_change      = ($i === 0) ? null : round($debtChg, 2);
                $r->free_cash_flow   = round($fcf, 2);
                $r->save();

                $recalculated["ebitda_{$i}"] = round($ebitda, 2);
                $recalculated["ebit_{$i}"]   = round($ebit, 2);
                $recalculated["np_{$i}"]     = round($netProfit, 2);
                $recalculated["wc_{$i}"]     = round($wc, 2);
                $recalculated["dwc_{$i}"]    = ($i === 0) ? null : round($deltaWC, 2);
                $recalculated["ddebt_{$i}"]  = ($i === 0) ? null : round($debtChg, 2);
                $recalculated["fcf_{$i}"]    = round($fcf, 2);

                $prevWC   = $wc;
                $prevDebt = $debt;
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => __('admin.financial_data_saved'),
                'recalculated' => $recalculated,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveFinancialProjection error: ' . $e->getMessage(), [
                'company_id' => $id,
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('admin.error_updating_financial_projection'),
                'error_details' => config('app.debug') ? $e->getMessage() : null, // ADDED: helpful during debugging
            ], 500);
        }
    }

    public function saveStatistics(Request $request, $id = null)
    {
        if (isset($id) && !is_null($id)) {
            $user = User::where('company_id', $id)->first();
        } else {
            $user = auth()->user();
        }

        $companyId = $user->company_id ?? null;
        if (!$companyId) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.no_company_associated'),
                ], 404);
            }
            return redirect()->back()->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
        }

        $company = Company::find($companyId);
        if (!$company) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.no_company_associated'),
                ], 404);
            }
            return redirect()->back()->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
        }

        $validator = Validator::make($request->all(), [
            'total_employees'  => 'required|integer|min:0|max:999999',
            'male_employees'   => 'nullable|integer|min:0|max:999999',
            'female_employees' => 'nullable|integer|min:0|max:999999',
        ], [
            'total_employees.required' => __('admin.this_field_is_required'),
            'total_employees.integer'  => __('admin.please_enter_a_valid_number'),
            'total_employees.min'      => __('admin.value_cannot_be_less_than_zero'),
            'total_employees.max'      => __('admin.please_enter_a_valid_number'),
            'male_employees.integer'   => __('admin.please_enter_a_valid_number'),
            'male_employees.min'       => __('admin.value_cannot_be_less_than_zero'),
            'male_employees.max'       => __('admin.please_enter_a_valid_number'),
            'female_employees.integer' => __('admin.please_enter_a_valid_number'),
            'female_employees.min'     => __('admin.value_cannot_be_less_than_zero'),
            'female_employees.max'     => __('admin.please_enter_a_valid_number'),
        ]);

        $validator->after(function ($v) use ($request) {
            $male   = $request->input('male_employees');
            $female = $request->input('female_employees');
            $total  = $request->input('total_employees');

            if ($male !== null && $male !== '' && $female !== null && $female !== '' && $total !== null && $total !== '') {
                if (((int) $male + (int) $female) !== (int) $total) {
                    $v->errors()->add('female_employees', __('admin.employee_gender_sum_mismatch'));
                }
            }
        });

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $company->total_employees  = $request->input('total_employees');
        $company->male_employees   = $request->input('male_employees');
        $company->female_employees = $request->input('female_employees');
        $company->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('admin.employee_statistics_updated'),
            ]);
        }

        return redirect()->back()->with(['msg' => __('admin.employee_statistics_updated'), 'class' => 'success']);
    }
}
