<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use stdClass;
use App\Cycle;
use App\Almuni;
use App\Company;
use App\FinalData;
use App\Screening;
use Carbon\Carbon;
use App\SurveyScopes;
use App\Finalscreening;
use App\SurveyResponses;
use App\TrainingSession;
use App\MentorshipRequest;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ScreeningEvaluationResults;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ScreeningReportExport;
use App\Exports\FinalScreeningReportExport;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->get();
        $companies = Company::whereHas('cycledata',function ($q)use ($orgIds, $user){
            $q->when(!empty($orgIds) && !$user->hasRole('admin'),function ($query)use($orgIds){
                $query->whereIn('organization_id',$orgIds);
            });
        })->get();

        $data['mentorRequests'] =  MentorshipRequest::all();
        $available_surveys = SurveyResponses::whereIn('user_id', $data['mentorRequests']->pluck('mentor_id'))->whereIn('triggered_at', $data['mentorRequests']->pluck('id'))->where('triggered_by', 'session')->get();

        return view('admin.reports.index',compact('companies','cycles','available_surveys'));
    }
    public function View($type,Request $request){
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $program_id = $request->get('program_id', null); // Can be null for "All Programs"

        switch ($type){
            case 'mentorship':
                $data = $this->mentorshipReport($orgIds, $user, $program_id,$request);
                $view = '_mentorshipReport';
                break;
            case 'training':
                $data = $this->trainingReport($orgIds, $user, $program_id);
                $view = '_trainingSessionsReport';
                break;
            case 'finalScreening':
                $data = $this->finalScreeningReport($orgIds, $user, $program_id);
                $view = '_finalScreeningReport';
                break;
            case 'screening':
                $data = $this->screeningReport($orgIds, $user, $program_id);
                $view = '_screeningReport';
                break;
            case 'cycles':
                $data = $this->cyclesReport($orgIds, $user, $request->id, $program_id);
                $view = '_cyclesReport';
                break;
            case 'companies':
                $data = $this->companiesReport($orgIds, $user, $request->id, $program_id);
                $view = '_companyReport';
                break;

            case 'mentorSurveys':
                $data = $this->mentorSurveyReport($orgIds, $user, $request->get('status', 'all'));
                $view = '_mentorSurvey';
                break;

            case 'startupSurveys':
                $data = $this->startupSurveysReport($orgIds, $user);
                $view = '_startupSurveyReport';
                break;

        }
        $view = view('admin.reports.ajax.'.$view, $data)->render();
        return response()->json(['value' => 1, 'view' => $view]);

    }

    // =============> ajax report functions <=============

    // =============> ajax report StartupSurveys <=============
    public function surveyReport(Request $request)
    {
        $surveyId = $request->id;
        $available_surveys = SurveyResponses::with(['user', 'cycle.companies', 'survey.organization'])
        ->where('id', $surveyId)->first();

       return view('admin.reports.show-survey', [
        'available_surveys' => $available_surveys,
        'organizationName' => $available_surveys->survey->organization->{'name_' . app()->getLocale()} ?? '-',
        'programName' => $available_surveys->cycle->title ?? '-',
        'startupName' => $available_surveys->cycle->companies->first()?->{'name_' . app()->getLocale()} ?? '-',
        'submitterName' => $available_surveys->user->first_name_en . ' ' . $available_surveys->user->last_name_en ?? '-',
        'submitDate' => $available_surveys->created_at?->format('Y-m-d H:i:s') ?? '-',
      ]);

    }

    protected function startupSurveysReport($orgIds, $user){
        $mentorRequestsQuery = MentorshipRequest::query()
        ->whereHas('user', function($q) use ($orgIds, $user) {
            $q->when(!empty($orgIds) && !$user->hasRole('admin'), function ($query) use ($orgIds) {
                $query->whereIn('organization_id', $orgIds);
            });
        });
        $data['mentorRequests'] =  $mentorRequestsQuery->get();
        $data['mentorRequests'] = $data['mentorRequests']->filter(function ($request) {
            return SurveyResponses::where('user_id', $request->user_id)
                ->where('triggered_at', $request->id)
                ->where('triggered_by', 'session')
                ->exists();
        });
        $data['cycle_mentors'] = User::whereHas('mentorSessions')->get();

        $data['available_surveys'] = SurveyResponses::whereIn('user_id', $data['mentorRequests']->pluck('user_id'))->whereIn('triggered_at', $data['mentorRequests']->pluck('id'))->where('triggered_by', 'session')->get();
        // ==============> counts <=================
        $data['Total_Survey'] = $data['available_surveys']->count();
        $data['Survey_Done'] = $data['available_surveys']->whereNotNull('responses')->count();
        $data['Survey_NotDone'] = $data['available_surveys']->whereNull('responses')->count();
      
        return $data;
    }
 // =============> ajax report MentorSurvey <=============
    protected function mentorSurveyReport($orgIds, $user, $program_id = null){
        $mentorRequestsQuery = MentorshipRequest::query()
        ->whereHas('mentor', function($q) use ($orgIds, $user) {
            $q->when(!empty($orgIds) && !$user->hasRole('admin'), function ($query) use ($orgIds) {
                $query->whereIn('organization_id', $orgIds);
            });
        });
        $status = request('status', 'all'); 
        $data['mentorRequests'] =  $mentorRequestsQuery->get();
        $data['mentorRequests'] = $data['mentorRequests']->filter(function ($request) {
            return SurveyResponses::where('user_id', $request->mentor_id)
                ->where('triggered_at', $request->id)
                ->where('triggered_by', 'session')
                ->exists();
        });
        $data['cycle_mentors'] = User::whereHas('mentorSessions')->get();

        $surveyScope = SurveyScopes::whereIn('type', ['mentor', 'both'])->get();
        $data['available_surveys'] = SurveyResponses::whereIn('user_id', $data['mentorRequests']->pluck('mentor_id'))->whereIn('triggered_at', $data['mentorRequests']->pluck('id'))->where('triggered_by', 'session')->get();

        $surveyMap = [];
        foreach ($data['available_surveys'] as $survey) {
        $key = $survey->user_id . '-' . $survey->triggered_at;
        $surveyMap[$key] = $survey;
        }
        $data['available_surveys_map'] = $surveyMap;
        if (in_array($status, ['open', 'completed'])) {
                $data['mentorRequests'] = $data['mentorRequests']->filter(function ($request) use ($data, $status) {
                $key = $request->mentor_id . '-' . $request->id;
                $survey = $data['available_surveys_map'][$key] ?? null;

                if (!$survey) return false;

                $isCompleted = $survey->responses && count($survey->responses) > 0;

                return $status === 'completed' ? $isCompleted : !$isCompleted;
            });
        }
        // ==============> counts <=================
        $data['Total_Survey'] = $data['available_surveys']->count();
        $data['Survey_Done'] = $data['available_surveys']->whereNotNull('responses')->count();
        $data['Survey_NotDone'] = $data['available_surveys']->whereNull('responses')->count();
        
        return $data;
    }


    protected function mentorshipReport($orgIds, $user, $program_id = null,Request $request) {
        $mentorRequests =  MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            $q->where('cycle_id', $program_id);
        });

        $enrolledInProgramFilter = $request->input('enrolledInProgramFilter', '1'); // default = 1

        $data['cycle_mentors'] = User::query();

    if ($enrolledInProgramFilter == '1') {
        $data['cycle_mentors'] = $data['cycle_mentors']->has('mentorCycles');
    } elseif ($enrolledInProgramFilter == '0') {
        $data['cycle_mentors'] = $data['cycle_mentors']->doesntHave('mentorCycles');
    }
    $data['cycle_mentors'] = $data['cycle_mentors']->get();


        
        // Get all sessions with relationships
        $data['sessions'] = MentorshipRequest::with(['user.company', 'mentor', 'cycle'])
            ->when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
                $q->whereIn('organization_id',$orgIds);
            })
            ->when(!is_null($program_id), function ($q) use ($program_id) {
                $q->where('cycle_id', $program_id);
            })
            ->orderBy('zoom_date', 'desc')
            ->get();

        // Get cycles for reference
        $data['cycles'] = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->get();
        
        // ==============> counts <=================

        // $data['mentors_count'] = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->count();
		$data['mentors_count'] = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'mentor')
			->select('users.*')
			->count();
        $data['total_sessions_count']= $mentorRequests->count();
        $data['mentorships_period'] =$this->formatMinutesToHoursMinutes($mentorRequests->sum('period'));
        $data['approved_sessions_count'] = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->where('approved','Yes')->count();
        $data['pending_sessions_count'] = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->where('approved','Yes')->where(function($q){
            $q->whereNull('done')->orWhere('done','!=',1);
        })->count();
        $data['done_sessions_count'] = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->where('approved','Yes')->whereDate('zoom_date','<',now()->format('Y-m-d'))->count();
        $data['disapproved_sessions_count'] = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->where('approved','No')->count();
        $data['new_sessions_count'] = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'),function ($q)use ($orgIds){
            $q->whereIn('organization_id',$orgIds);
        })->whereNull('approved')->count();
        $data['cycle_mentors_count'] = count($data['cycle_mentors']);
        return $data;
    }

    protected function trainingReport($orgIds, $user, $program_id = null){
        $cycles = TrainingSession::with('step.cycle')->get()->pluck('step.cycle')->unique('id');
        $data['cycles'] = $cycles->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            return $q->where('id', $program_id);
        });

        $steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereHas('cycle', function ($query) use ($orgIds) {
                $query->whereIn('organization_id', $orgIds);
            });
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            return $q->whereHas('cycle', function ($query) use ($program_id) {
                $query->where('id', $program_id);
            });
        })->where('stype', 4)->get();
        $data['steps'] = $steps;

        $sessions = TrainingSession::with('reports')->whereHas('step',function ($q)use ($orgIds, $user, $program_id){
            $q->where('stype',4)->when(!empty($orgIds) && !$user->hasRole('admin'),function ($query) use ($orgIds){
                $query->whereHas('cycle',function ($query) use ($orgIds){
                    $query->whereIn('organization_id',$orgIds);
                });
            })->when(!is_null($program_id), function ($query) use ($program_id) {
                $query->whereHas('cycle', function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            });
        })->orderBy('datetime', 'ASC');
        $data['sessions'] = $sessions->get();

        $data['total_sessions_count'] = $sessions->count();
        $totalMinutes = $sessions->sum('duration');
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $data['sessions_period'] = sprintf("%d:%02d", $hours, $minutes);
        $data['zoom_sessions_count'] = TrainingSession::whereHas('step',function ($q)use ($orgIds, $user, $program_id){
            $q->where('stype',4)->when(!empty($orgIds) && !$user->hasRole('admin'),function ($query) use ($orgIds){
                $query->whereHas('cycle',function ($query) use ($orgIds){
                    $query->whereIn('organization_id',$orgIds);
                });
            })->when(!is_null($program_id), function ($query) use ($program_id) {
                $query->whereHas('cycle', function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            });
        })->where('type','sessions')->count();

        $data['offline_sessions_count'] = TrainingSession::whereHas('step',function ($q)use ($orgIds, $user, $program_id){
            $q->where('stype',4)->when(!empty($orgIds) && !$user->hasRole('admin'),function ($query) use ($orgIds){
                $query->whereHas('cycle',function ($query) use ($orgIds){
                    $query->whereIn('organization_id',$orgIds);
                });
            })->when(!is_null($program_id), function ($query) use ($program_id) {
                $query->whereHas('cycle', function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            });
        })->where('type','offline')->count();

        $data['online_sessions_count'] = TrainingSession::whereHas('step',function ($q)use ($orgIds, $user, $program_id){
            $q->where('stype',4)->when(!empty($orgIds) && !$user->hasRole('admin'),function ($query) use ($orgIds){
                $query->whereHas('cycle',function ($query) use ($orgIds){
                    $query->whereIn('organization_id',$orgIds);
                });
            })->when(!is_null($program_id), function ($query) use ($program_id) {
                $query->whereHas('cycle', function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            });
        })->where('type','sessions')->count();


         $data['online_training_count'] = TrainingSession::whereHas('step',function ($q)use ($orgIds, $user, $program_id){
            $q->where('stype',4)->when(!empty($orgIds) && !$user->hasRole('admin'),function ($query) use ($orgIds){
                $query->whereHas('cycle',function ($query) use ($orgIds){
                    $query->whereIn('organization_id',$orgIds);
                });
            })->when(!is_null($program_id), function ($query) use ($program_id) {
                $query->whereHas('cycle', function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            });
        })->where('type','online')->count();
        
        return $data;
    }

    protected function finalScreeningReport($orgIds, $user, $program_id = null){
        // $data['judgers_count'] = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->count();
		$data['judgers_count'] = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->count();

        $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            return $q->where('id', $program_id);
        })->get();

        $steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereHas('cycle', function ($query) use ($orgIds) {
            $query->whereIn('organization_id', $orgIds);
        });
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            return $q->whereHas('cycle', function ($query) use ($program_id) {
                $query->where('id', $program_id);
            });
        })->where('stype', 5)->orderBy('from')->get();

        // Get final screening step IDs and evaluation IDs for later use
        $final_screening_steps = $steps->pluck('id');
        $evaluation_ids = ScreeningEvaluation::whereIn('step_id', $final_screening_steps)->pluck('id');

   
        $finals = DB::table('finalscreenings AS fs')
            ->select([
                'fs.id',
                'fs.company_id',
                'fs.step_id',
                'fs.judges',
                'se.id AS evaluation_id',
                'se.status AS evaluation_status',
                'sec.id AS criteria_id',
                'sec.title AS criteria_name',
                'sec.weight AS criteria_weight',
                'sec_child.id AS sub_criteria_id',
                'sec_child.title AS sub_criteria_name',
                'sec_child.weight AS sub_criteria_weight',
                'sec_child.parent_id AS sub_criteria_parent_id',
                'ser.id AS result_id',
                'ser.evaluation_id AS result_evaluation_id',
                'ser.user_id AS result_judge_id',
                'ser.company_id AS result_company_id',
                'ser.criteria_id AS result_criteria_id',
                'ser.score AS result_score',
                'ser.comment AS result_comment',
                'ser.status AS result_status'
            ])
            ->leftJoin('screening_evaluation AS se', 'se.step_id', 'fs.step_id')
            ->leftJoin('screening_evaluation_criteria AS sec', 'sec.evaluation_id', 'se.id')
            ->leftJoin('screening_evaluation_criteria AS sec_child', 'sec_child.parent_id', 'sec.id')
            ->leftJoin('screening_evaluation_results AS ser', 'ser.evaluation_id', 'se.id')
            ->when(!is_null($program_id), function ($q) use ($program_id) {
                $q->whereHas('step.cycle', function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            })
            ->get();

        $finals = $finals->map(function ($f) {
            $f->judges = collect(json_decode($f->judges))->map(function($j) { return intval($j); })->toArray();
            return $f;
        });
        $judges_ids = $finals->pluck('judges')->flatten()->unique()->values()->toArray();

        $judges = DB::table('users AS u')
            ->select([
                'u.id',
                DB::raw("CONCAT(u.first_name_en, ' ', u.last_name_en) AS user_name_en"),
                DB::raw("CONCAT(u.first_name_ar, ' ', u.last_name_ar) AS user_name_ar"),
                'u.email',
                'u.img_url'
            ])
            ->whereIn('id', $judges_ids)
            ->get();

        $judges = $judges->map(function ($j) {
            $j->img_url = User::image_handler( $j->img_url );
            return $j;
        });

        $finals = $finals->map(function ($final) use ($judges) {
            $final->judges = $judges->whereIn('id', $final->judges);
            return $final;
        });

        $finals_by_evaluation = $finals->groupBy('evaluation_id');
        $finals_by_company = $finals->where('evaluation_status', 1)->groupBy('company_id');
        

        $_companies = DB::table('companies AS c')
            ->select([
                'c.id',
                'c.name_ar',
                'c.name_en',
            ])
            ->whereIn('c.step', function($q) {
                return $q->select(['s.id'])->from('steps AS s')->where('s.stype', 5);
            })->get();

        $finals_by_evaluation = $finals_by_evaluation->filter(function($evaluation) {
            $evaluation = $evaluation->where('evaluation_status', 1);
            return !$evaluation->isEmpty();
        });
        $evaluations_structure = $finals_by_evaluation->map(function($ev) {
            $e = $ev->first();
            $evaluation = new stdClass;
            $evaluation->id = $e->evaluation_id;
            $evaluation->step = $e->step_id;
            $evaluation->status = $e->evaluation_status;
            $evaluation->criteria = $ev->groupBy('criteria_id')->map(function($groupedCriteria) {
                $c = $groupedCriteria->first();
                $criteria = new stdClass;
                $criteria->id = $c->criteria_id;
                $criteria->name = $c->criteria_name;
                $criteria->weight = $c->criteria_weight;
                $criteria->children = $groupedCriteria->groupBy('sub_criteria_id')->map(function($groupedCriteriaChild) {
                    $c = $groupedCriteriaChild->first();
                    $criteriaChild = new stdClass;
                    $criteriaChild->id = $c->sub_criteria_id;
                    $criteriaChild->name = $c->sub_criteria_name;
                    $criteriaChild->weight = $c->sub_criteria_weight;
                    return $criteriaChild;
                })->values();
                return $criteria;
            })->values();
            return $evaluation;
        })->values();
        $evaluated_companies = $finals_by_company->map(function($groupedCompany) {
            $c = $groupedCompany->first();
            $company = new stdClass;
            $company->id = $c->company_id;
            $company->step = $c->step_id;
            $company->judges = $c->judges;
            $company->evaluations = $groupedCompany->pluck('evaluation_id')->unique()->values();
            $company->results = $groupedCompany->map(function($r) {
                $result = new stdClass;
                $result->id = $r->result_id;
                $result->evaluation_id = $r->result_evaluation_id;
                $result->judge_id = $r->result_judge_id;
                $result->company_id = $r->result_company_id;
                $result->criteria_id = $r->result_criteria_id;
                $result->score = $r->result_score;
                $result->comment = $r->result_comment;
                $result->status = $r->result_status;
                return $result;
            })->unique('id')->values();
            return $company;
        })->values();

        $_companies = $_companies->map(function($company) use ($evaluated_companies, $evaluations_structure) {
            $status = 'not_evaluated';
            $evaluated_company = $evaluated_companies->where('id', $company->id)->first();

            if( !empty( $evaluated_company ) )
            {
                $judges_ids = $evaluated_company->judges->pluck('id')->unique()->toArray();
                $evaluations = $evaluations_structure->whereIn('id', $evaluated_company->evaluations->toArray())->values();
                
                $required_criteria = [];
                foreach( $evaluations as $evaluation )
                {
                    foreach( $evaluation->criteria as $criteria )
                    {
                        $required_criteria = array_merge($required_criteria, $criteria->children->pluck('id')->toArray());
                    }

                }
                $required_criteria = array_values( array_unique($required_criteria) );
                $total_required_count = count( $judges_ids ) * count( $required_criteria );
                if( $evaluated_company->results->count() > 0 )
                {
                    $published_results = $evaluated_company->results->where('status', 1)->values();
                    if( $published_results->count() == $total_required_count ) { $status = 'published'; }
                    else { $status = 'draft'; }
                }
            }
            $company->status = $status;
            return $company;
        });

        $final_screening_count = $_companies->count();
        $final_pending_screening_count = $_companies->where('status', 'not_evaluated')->values();
        $final_done_screening_count = $_companies->where('status', 'published')->values();
        $draft_screenings_count = $_companies->where('status', 'draft')->values();
        $Judges = $judges->count();

        $l = app()->getLocale();
        $steps_ids = $steps->pluck('id')->toArray();
        $metadata = collect([]);
        // Only process metadata for the filtered steps
        foreach($steps as $step) {
            $metadata->push(ScreeningEvaluationResults::metadata($step->id));
        }
        $_allSteps = collect();
        $allJudges = collect();
        $allResults = collect();
        $allCompanies = collect();
        $_allCompanies = collect();
        $_allEvaluations = collect();

        if( count( $steps_ids ) > 0 )
        {
            // $metadata = ScreeningEvaluationResults::metadataReport( $steps_ids );
            foreach( $metadata as $stepId => $data )
            {
                $allCompanies = $allCompanies->concat($data['companies']);
                $_allSteps = $_allSteps->concat($data['_steps']);
                $allJudges = $allJudges->concat($data['judges']);
                $allResults = $allResults->concat($data['results']);
                $_allCompanies = $_allCompanies->concat($data['_companies']);
                if( !is_null($data['evaluation']) ) { $_allEvaluations = $_allEvaluations->push($data['evaluation']); }
            }
        }
        // $_allCompanies = $_allCompanies->unique('id')->values();
        $_allCompanies = $_allCompanies->map(function( $c ) use ($l) {
            $c = clone $c;
            $id = $c->id;
            $output = new stdClass;
            $output->id = $c->id;
            $output->name = $c->{'name_' . $l};
            $output->score = $c->average_score;
            $output->step_id = $c->company_step_id;
            $output->cycle_id = $c->company_cycle_id;
            $output->judges = $c->judges->map(function($judge) use ($id) {
                $judge = clone $judge;
                $oneResult = $judge->companies->where('id', $id)->first();
                $judge->result = $oneResult;
                return $judge;
            });
            $statuses = $output->judges
                ->pluck('result.status')
                ->filter()
                ->unique()
                ->values();

            $output->status = $statuses->count() === 1 ? $statuses->first() : 'Draft';
            return $output;
        })->values();

        $allJudges = $allJudges->unique('id')->values();

        // Calculate KPI data for screening
        $screening_count = $_allCompanies->count();
        $pending_screenings = $_allCompanies->filter(function($company) use ($allResults) {
            return $allResults->where('company_id', $company->id)->isEmpty();
        })->values();
        $not_assigned_screenings = $_allCompanies->filter(function($company) {
            return $company->judges->count() === 0;
        })->values();
        $non_pending_companies = $_allCompanies->filter(function($company) use ($allResults) {
            return !$allResults->where('company_id', $company->id)->isEmpty();
        })->values();
        $draft_screenings = $non_pending_companies->where('status', 'Draft')->count();
        $published_screenings = $non_pending_companies->where('status', 'Published')->count();
        $not_evaluated_screenings = $non_pending_companies->where('status', 'Not Evaluated')->count();

        // Kpi Cards Data (Dont' remove)
        $kpiData = [
            [ 'value' => $allJudges?->count() ?? 0, 'label' => 'Judgers', 'icon' => 'user', 'color' => 'info'],
            [ 'value' => $_allCompanies?->count() ?? 0, 'label' => 'Total', 'icon' => 'gavel', 'color' => 'primary'],
            [ 'value' => $not_assigned_screenings->count(), 'label' => 'Not Assigned', 'icon' => 'clock', 'color' => 'warning' ],
            [ 'value' => $pending_screenings->count() + $not_evaluated_screenings, 'label' => 'Not Evaluated', 'icon' => 'trending-up-2', 'color' => 'danger'],
            [ 'value' => $draft_screenings, 'label' => 'In Progress', 'icon' => 'pencil-check', 'color' => 'secondary'],
            [ 'value' => $published_screenings, 'label' => 'Published', 'icon' => 'progress-check', 'color' => 'success'],
        ];

        // Process companies with their evaluation data
        $startups = $_allCompanies->map(function ($company) use ($allJudges, $allResults, $allCompanies, $steps, $not_assigned_screenings) {
            $scoped = $allCompanies->where('id', $company->id)->first();
            $c = clone $scoped;
            
            // Get evaluations for this company
            $companyResults = $allResults->where('company_id', $c->id);
            
            // Calculate scores and status
            $publishedResults = $companyResults->where('status', 1);
            $averageScore = $publishedResults->count() > 0 ? $publishedResults->avg('score') : 0;
            
            // Attach calculated data to company
            $c->average_score = round($averageScore, 1);
            $c->evaluation_results = $companyResults;

            $has_no_judgers = $not_assigned_screenings->where('id', $c->id)->count() > 0 ? true : false;

            $c->company_step_id = $company->step_id;
            $c->company_cycle_id = $company->cycle_id;
            $c->score = $company->score;
            $_judges = $company->judges;
            $c->status = $has_no_judgers ? 'Not Assigned' : $company->status;
            $c->stepInfo = $steps->where('id', $company->step_id)->first();
            $judges = clone $_judges;
            $companyId = $c->id;
            $judges = $judges->filter(function($judge) use ($companyId) {
                return in_array($companyId, $judge->companies->pluck('id')->unique()->values()->toArray());
            })->values();
            $c->judges = $judges;
            return $c;
        })->values();

        // Process judges with their evaluation data
        $judges = $allJudges->map(function ($judge) use ($allResults, $allCompanies) {
            $judgeResults = $allResults->where('user_id', $judge->id);
            
            $companies = $judgeResults->map(function ($result) use ($allCompanies) {
                $company = $allCompanies->where('id', $result->company_id)->first();
                if ($company) {
                    return (object) [
                        'id' => $result->company_id,
                        'status' => $result->status == 1 ? 'Published' : 'Draft',
                        'score' => $result->score
                    ];
                }
                return null;
            })->filter();
            
            $judge->companies_ids = $companies->pluck('id')->toArray();
            $judge->startups = collect($judge->companies_ids);
            $judge->companies = $companies->toArray();
            
            return $judge;
        });

        $data['cycles'] = $cycles;
        $data['steps'] = $steps;
        $data['kpiData'] = $kpiData;
        $data['startups'] = $startups;
        $data['judges'] = $judges;

        return $data;
    }

    protected function screeningReport($orgIds, $user, $program_id = null)
    {
        // First get steps based on program filter
        $stepsQuery = Step::where('stype', 2)
            ->whereNull('deleted_at')
            ->when(!empty($orgIds) && !$user->hasRole('admin'), function($q) use ($orgIds) {
                return $q->whereHas('cycle', function($query) use ($orgIds) {
                    $query->whereIn('organization_id', $orgIds);
                });
            })
            ->when(!is_null($program_id), function($q) use ($program_id) {
                return $q->where('cycle_id', $program_id);
            });

        $steps = $stepsQuery->get();
        $metadata = collect([]);
        
        // Only process metadata for the filtered steps
        foreach($steps as $step) {
            $metadata->push(ScreeningEvaluationResults::metadata($step->id));
        }

        $l = app()->getLocale();
        
        // Use the already filtered steps query and order them
        $steps = $stepsQuery->orderBy('from')->get();

        $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            return $q->where('id', $program_id);
        })->get();

        $steps_ids = $steps->pluck('id')->toArray();

        $_allSteps = collect();
        $allJudges = collect();
        $allResults = collect();
        $allCompanies = collect();
        $_allCompanies = collect();
        $_allEvaluations = collect();

        if( count( $steps_ids ) > 0 )
        {
            // $metadata = ScreeningEvaluationResults::metadataReport( $steps_ids );
            foreach( $metadata as $stepId => $data )
            {
                $allCompanies = $allCompanies->concat($data['companies']);
                $_allSteps = $_allSteps->concat($data['_steps']);
                $allJudges = $allJudges->concat($data['judges']);
                $allResults = $allResults->concat($data['results']);
                $_allCompanies = $_allCompanies->concat($data['_companies']);
                if( !is_null($data['evaluation']) ) { $_allEvaluations = $_allEvaluations->push($data['evaluation']); }
            }
        }
        // $_allCompanies = $_allCompanies->unique('id')->values();
        $_allCompanies = $_allCompanies->map(function( $c ) use ($l) {
            $c = clone $c;
            $id = $c->id;
            $output = new stdClass;
            $output->id = $c->id;
            $output->name = $c->{'name_' . $l};
            $output->score = $c->average_score;
            $output->step_id = $c->company_step_id;
            $output->cycle_id = $c->company_cycle_id;
            $output->judges = $c->judges->map(function($judge) use ($id) {
                $judge = clone $judge;
                $oneResult = $judge->companies->where('id', $id)->first();
                $judge->result = $oneResult;
                return $judge;
            });
            $statuses = $output->judges
                ->pluck('result.status')
                ->filter()
                ->unique()
                ->values();

            $output->status = $statuses->count() === 1 ? $statuses->first() : 'Draft';
            return $output;
        })->values();
        $allJudges = $allJudges->unique('id')->values();

        // Calculate KPI data for screening
        $screening_count = $_allCompanies->count();
        $pending_screenings = $_allCompanies->filter(function($company) use ($allResults) {
            return $allResults->where('company_id', $company->id)->isEmpty();
        })->values();
        $not_assigned_screenings = $_allCompanies->filter(function($company) {
            return $company->judges->count() === 0;
        })->values();
        $non_pending_companies = $_allCompanies->filter(function($company) use ($allResults) {
            return !$allResults->where('company_id', $company->id)->isEmpty();
        })->values();
        $draft_screenings = $non_pending_companies->where('status', 'Draft')->count();
        $published_screenings = $non_pending_companies->where('status', 'Published')->count();
        $not_evaluated_screenings = $non_pending_companies->where('status', 'Not Evaluated')->count();
        
        $judgers_count = $allJudges->count();

        // KPI Cards Data
        $kpiData = [
            [ 'value' => $judgers_count, 'label' => 'Judgers', 'icon' => 'user', 'color' => 'info'],
            [ 'value' => $screening_count, 'label' => 'Total', 'icon' => 'gavel', 'color' => 'primary'],
            [ 'value' => $not_assigned_screenings->count(), 'label' => 'Not Assigned', 'icon' => 'clock', 'color' => 'warning' ],
            [ 'value' => $pending_screenings->count() + $not_evaluated_screenings, 'label' => 'Not Evaluated', 'icon' => 'trending-up-2', 'color' => 'danger'],
            [ 'value' => $draft_screenings, 'label' => 'In Progress', 'icon' => 'pencil-check', 'color' => 'secondary'],
            [ 'value' => $published_screenings, 'label' => 'Published', 'icon' => 'progress-check', 'color' => 'success'],
        ];

        // Process companies with their evaluation data
        $startups = $_allCompanies->map(function ($company) use ($allJudges, $allResults, $allCompanies, $steps, $not_assigned_screenings) {
            $scoped = $allCompanies->where('id', $company->id)->first();
            $c = clone $scoped;
            
            // Get evaluations for this company
            $companyResults = $allResults->where('company_id', $c->id);
            
            // Calculate scores and status
            $publishedResults = $companyResults->where('status', 1);
            $averageScore = $publishedResults->count() > 0 ? $publishedResults->avg('score') : 0;
            
            // Attach calculated data to company
            $c->average_score = round($averageScore, 1);
            $c->evaluation_results = $companyResults;

            $has_no_judgers = $not_assigned_screenings->where('id', $c->id)->count() > 0 ? true : false;

            $c->company_step_id = $company->step_id;
            $c->company_cycle_id = $company->cycle_id;
            $c->score = $company->score;
            $_judges = $company->judges;
            $c->status = $has_no_judgers ? 'Not Assigned' : $company->status;
            $c->stepInfo = $steps->where('id', $c->company_step_id)->first();
            $judges = clone $_judges;
            $companyId = $c->id;
            $judges = $judges->filter(function($judge) use ($companyId) {
                return in_array($companyId, $judge->companies->pluck('id')->unique()->values()->toArray());
            })->values();
            $c->judges = $judges;
            return $c;
        })->values();

        // Process judges with their evaluation data
        $judges = $allJudges->map(function ($judge) use ($allResults, $allCompanies) {
            $judgeResults = $allResults->where('user_id', $judge->id);
            
            $companies = $judgeResults->map(function ($result) use ($allCompanies) {
                $company = $allCompanies->where('id', $result->company_id)->first();
                if ($company) {
                    return (object) [
                        'id' => $result->company_id,
                        'status' => $result->status == 1 ? 'Published' : 'Draft',
                        'score' => $result->score
                    ];
                }
                return null;
            })->filter();
            
            $judge->companies_ids = $companies->pluck('id')->toArray();
            $judge->startups = collect($judge->companies_ids);
            $judge->companies = $companies->toArray();
            
            return $judge;
        });

        $judgers = User::whereHas('roles', function ($query) {
			$query->where('default_for', 'judger');
		})->count();
        $data['judgers'] = $judgers;
        $data['cycles'] = $cycles;
        $data['steps'] = $steps;
        $data['kpiData'] = $kpiData;
        $data['startups'] = $startups;
        $data['judges'] = $judges;

        return $data;
    }

    protected function cyclesReport($orgIds, $user,$cycle_id, $program_id = null){
        // Keep the passed cycle (for backward-compatible views), but do not filter by it
        $cycle = Cycle::find($cycle_id);

        $ongoingCycles = Cycle::where('start', '<=', now())
            ->where('end', '>=', now())
            ->get();

        $completedCycles = Cycle::where('end', '<', now())
            ->get();

        // OVERALL (all cycles) KPIs within the organization (no cycle filter)
        $data['mentors_count'] = User::whereHas('mentorSessions', function ($q) use ($orgIds, $user, $program_id) {
            $q->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                $q->whereIn('organization_id', $orgIds);
            })->when(!is_null($program_id), function ($q) use ($program_id) {
                $q->where('cycle_id', $program_id);
            });
        })->count();

        $mentorRequests = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            $q->whereIn('organization_id', $orgIds);
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            $q->where('cycle_id', $program_id);
        });

        $totalMentorshipMinutes = (clone $mentorRequests)->sum('period');

        $training_sessions = TrainingSession::whereHas('step', function ($q) use ($orgIds, $user, $program_id) {
            $q->where('stype', 4)->whereHas('cycle', function ($query) use ($orgIds, $user, $program_id) {
                $query->when(!empty($orgIds) && !$user->hasRole('admin'), function ($query) use ($orgIds) {
                    $query->whereIn('organization_id', $orgIds);
                })->when(!is_null($program_id), function ($query) use ($program_id) {
                    $query->where('id', $program_id);
                });
            });
        });

        $startups = Company::whereHas('cycledata', function ($q) use ($orgIds, $user, $program_id) {
            $q->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                $q->whereIn('organization_id', $orgIds);
            })->when(!is_null($program_id), function ($q) use ($program_id) {
                $q->where('id', $program_id);
            });
        });

        $screenings = Company::whereHas('stepdata', function ($q) use ($orgIds, $user, $program_id) {
            return $q->whereHas('cycle', function ($query) use ($orgIds, $user, $program_id) {
                $query->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                    $q->whereIn('organization_id', $orgIds);
                })->when(!is_null($program_id), function ($q) use ($program_id) {
                    $q->where('id', $program_id);
                });
            })->where('stype', 2);
        })->count();

        $final_screenings = Company::whereHas('stepdata', function ($q) use ($orgIds, $user, $program_id) {
            return $q->whereHas('cycle', function ($query) use ($orgIds, $user, $program_id) {
                $query->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                    $q->whereIn('organization_id', $orgIds);
                })->when(!is_null($program_id), function ($q) use ($program_id) {
                    $q->where('id', $program_id);
                });
            })->where('stype', 5);
        })->count();

        $steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereHas('cycle', function ($query) use ($orgIds) {
                $query->whereIn('organization_id', $orgIds);
            });
        })->when(!is_null($program_id), function ($q) use ($program_id) {
            return $q->whereHas('cycle', function ($query) use ($program_id) {
                $query->where('id', $program_id);
            });
        })->where('stype', 4)->get();

        // All cycles within the organization
        $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            $q->whereIn('organization_id', $orgIds);
        })->get();

        // Per-cycle KPI data
        $cycles_kpis = [];
        $cycles_sessions = [];
        $cycles_screenings = [];
        $cycles_final_screenings = [];
        foreach ($cycles as $c) {
            $per_cycle_mentor_requests = MentorshipRequest::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                $q->whereIn('organization_id', $orgIds);
            })->where('cycle_id', $c->id);

            $per_cycle_sessions = MentorshipRequest::with(['mentor', 'user.company'])
                ->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                    $q->whereIn('organization_id', $orgIds);
                })
                ->where('cycle_id', $c->id)
                ->orderBy('zoom_date', 'desc')
                ->get()
                ->map(function ($session) {
                    $durationMinutes = (int) ($session->period ?? 0);
                    return [
                        'id' => $session->id,
                        'mentor' => $session->mentor->name ?? '-',
                        'startup' => optional(optional($session->user)->company)->{'name_' . app()->getLocale()} ?? '-',
                        'zoom_date' => $session->zoom_date ? Carbon::parse($session->zoom_date)->format('Y-m-d H:i') : '-',
                        'duration_minutes' => $durationMinutes,
                        'duration_formatted' => $this->formatMinutesToHoursMinutes($durationMinutes),
                    ];
                })
                ->values()
                ->all();

            $cycles_sessions[$c->id] = $per_cycle_sessions;

            $cycleMentorshipMinutes = (clone $per_cycle_mentor_requests)->sum('period');

            $per_cycle_screening_companies = Company::with(['user'])
                ->whereHas('stepdata', function ($q) use ($orgIds, $user, $c) {
                    return $q->whereHas('cycle', function ($query) use ($orgIds, $user, $c) {
                        $query->where('cycles.id', $c->id)
                            ->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                                $q->whereIn('organization_id', $orgIds);
                            });
                    })->where('stype', 2);
                })
                ->orderBy('name_en')
                ->get()
                ->map(function ($company) use ($c) {
                    $representative = $company->user;
                    return [
                        'id' => $company->id,
                        'name' => $company->{'name_' . app()->getLocale()} ?? $company->name_en,
                        'representative' => $representative ? ($representative->{'first_name_' . app()->getLocale()} . ' ' . $representative->{'last_name_' . app()->getLocale()}) : '-',
                        'email' => $representative->email ?? '-',
                        'phone' => $representative->phone ?? '-',
                        'cycle_id' => $c->id,
                    ];
                })
                ->values()
                ->all();

            $cycles_screenings[$c->id] = $per_cycle_screening_companies;

            $per_cycle_final_screening_companies = Company::with(['user'])
                ->whereHas('stepdata', function ($q) use ($orgIds, $user, $c) {
                    return $q->whereHas('cycle', function ($query) use ($orgIds, $user, $c) {
                        $query->where('cycles.id', $c->id)
                            ->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                                $q->whereIn('organization_id', $orgIds);
                            });
                    })->where('stype', 5);
                })
                ->orderBy('name_en')
                ->get()
                ->map(function ($company) use ($c) {
                    $representative = $company->user;
                    return [
                        'id' => $company->id,
                        'name' => $company->{'name_' . app()->getLocale()} ?? $company->name_en,
                        'representative' => $representative ? ($representative->{'first_name_' . app()->getLocale()} . ' ' . $representative->{'last_name_' . app()->getLocale()}) : '-',
                        'email' => $representative->email ?? '-',
                        'phone' => $representative->phone ?? '-',
                        'cycle_id' => $c->id,
                    ];
                })
                ->values()
                ->all();

            $cycles_final_screenings[$c->id] = $per_cycle_final_screening_companies;

            $per_cycle_training_sessions = TrainingSession::whereHas('step', function ($q) use ($orgIds, $user, $c) {
                $q->where('stype', 4)->whereHas('cycle', function ($query) use ($orgIds, $user, $c) {
                    $query->where('cycles.id', $c->id)
                        ->when(!empty($orgIds) && !$user->hasRole('admin'), function ($query) use ($orgIds) {
                            $query->whereIn('organization_id', $orgIds);
                        });
                });
            });

            $per_cycle_startups = Company::whereHas('cycledata', function ($q) use ($orgIds, $user, $c) {
                $q->where('cycles.id', $c->id)->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                    $q->whereIn('organization_id', $orgIds);
                });
            });

            $per_cycle_screenings = Company::whereHas('stepdata', function ($q) use ($orgIds, $user, $c) {
                return $q->whereHas('cycle', function ($query) use ($orgIds, $user, $c) {
                    $query->where('cycles.id', $c->id)->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                        $q->whereIn('organization_id', $orgIds);
                    });
                })->where('stype', 2);
            })->count();

            $per_cycle_final_screenings = Company::whereHas('stepdata', function ($q) use ($orgIds, $user, $c) {
                return $q->whereHas('cycle', function ($query) use ($orgIds, $user, $c) {
                    $query->where('cycles.id', $c->id)->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                        $q->whereIn('organization_id', $orgIds);
                    });
                })->where('stype', 5);
            })->count();

            $per_cycle_mentors_count = User::whereHas('mentorSessions', function ($q) use ($orgIds, $user, $c) {
                $q->where('cycle_id', $c->id)->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                    $q->whereIn('organization_id', $orgIds);
                });
            })->count();

            $cycles_kpis[$c->id] = [
                'cycle' => $c,
                'mentors_count' => $per_cycle_mentors_count,
                'mentorRequests_count' => $per_cycle_mentor_requests->count(),
                'mentorships_period_minutes' => $cycleMentorshipMinutes,
                'mentorships_period_formatted' => $this->formatMinutesToHoursMinutes($cycleMentorshipMinutes),
                'mentorship_attendance_rate' => $per_cycle_mentor_requests->count() ? ($per_cycle_mentor_requests->sum('done') / $per_cycle_mentor_requests->count()) * 100 : 0,
                'training_sessions_count' => $per_cycle_training_sessions->count(),
                'training_sessions_period' => number_format($per_cycle_training_sessions->sum('duration') / 60, 2),
                'startups' => $per_cycle_startups->count(),
                'screening_sessions_count' => $per_cycle_screenings,
                'final_screening_count' => $per_cycle_final_screenings,
            ];
        }

        // Overall aggregates (no cycle filter)
        $data['training_sessions_count'] = $training_sessions->count();
        $data['training_sessions'] = $training_sessions->get();
        // $totalMinutes_training_sessions_period = $training_sessions->sum('duration');
        $data['training_sessions_period'] = $this->formatMinutesToHoursMinutes($training_sessions->sum('duration'));
        $data['mentorRequests_count'] = $mentorRequests->count();
        $data['mentorships_period_minutes'] = $totalMentorshipMinutes;
        $data['mentorships_period_formatted'] = $this->formatMinutesToHoursMinutes($totalMentorshipMinutes);
        $data['mentorship_attendance_rate'] = $mentorRequests->count() ? ($mentorRequests->sum('done') / $mentorRequests->count()) * 100 : 0;

        // Pass through references
        $data['cycle'] = $cycle; // kept for compatibility
        $data['cycles'] = $cycles; // all cycles
        $data['cycles_kpis'] = $cycles_kpis; // new: per-cycle KPI data
        $data['cycles_sessions'] = $cycles_sessions;
        $data['cycles_screenings'] = $cycles_screenings;
        $data['cycles_final_screenings'] = $cycles_final_screenings;
        $data['steps'] = $steps;
        $data['startups'] = $startups->count();
        $data['screening_sessions_count'] = $screenings;
        $data['final_screening_count'] = $final_screenings;
        $data['ongoingCycles'] = $ongoingCycles;
        $data['completed_programs'] = $completedCycles;

        // Group overall KPIs for Blade looping
        $data['kpiData'] = [
            [ 'value' => $data['cycles']->count() ?? 0, 'label' => 'total_programs', 'icon' => 'components', 'color' => 'info' ],
            [ 'value' => $data['ongoingCycles']->count() ?? 0, 'label' => 'ongoing_programs', 'icon' => 'progress-bolt', 'color' => 'primary' ],
            [ 'value' => $data['completed_programs']->count() ?? 0, 'label' => 'completed_programs', 'icon' => 'progress-check', 'color' => 'success' ],
        ];

        return $data;
    }

    private function formatMinutesToHoursMinutes(int $totalMinutes): string
    {
        if ($totalMinutes <= 0) {
            return '00:00';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function companiesReport($orgIds, $user, $company_id, $program_id = null){
        $data['company'] = Company::find($company_id);
        if($data['company']){
            
           $data['company']->alumni = Almuni::where('company_id', $data['company']?->id)->exists();
        }
        
        

        $mentorRequests =  MentorshipRequest::where('user_id', $data['company']?->user?->id);
        $training_sessions = TrainingSession::whereHas('step', function ($q) use ($company_id) {
            $q->where('stype',4)->whereHas('companies',function ($q) use ($company_id) {
                $q->where('companies.id',$company_id);
            });
        });

        $data['training_sessions_count'] = $training_sessions->count();
        $data['training_sessions_period'] = $this->formatMinutesToHoursMinutes($training_sessions->sum('duration'));
        $data['mentorRequests_count']  = $mentorRequests->count();
        $data['mentorships_period'] = $this->formatMinutesToHoursMinutes($mentorRequests->sum('period'));
        if ($mentorRequests->count()){

            $data['mentorship_attendance_rate']= ($mentorRequests->sum('done')/$mentorRequests->count())*100;
        }else{
            $data['mentorship_attendance_rate']= 0;
        }
        $data['mentorship_sessions']=$mentorRequests->get();
        $data['training_sessions']=$training_sessions->get();
        
        // Adjusted Static Programs List

        $programs = [];

$groupedTrainingSessions = $data['training_sessions']
    ->groupBy('step_id');

foreach ($groupedTrainingSessions as $stepId => $trainingSessions) {

    $step = optional($trainingSessions->first())->step;
    // جلسات الإرشاد المرتبطة بنفس البرنامج
    $mentorRequests = $data['mentorship_sessions']
        ->where('cycle_id', $step?->cycle_id);
    $mentorshipCount = $mentorRequests->count();
    $attendanceRate = $mentorshipCount
        ? round(($mentorRequests->sum('done') / $mentorshipCount) * 100)
        : 0;
    $programs[] = (object)[
        'program_name' => $step?->cycle->title ?? 'N/A',
        'organization' => $data['company']->cycledata->organization->name_ar,

        'mentorship_sessions_count' => $mentorshipCount,
        'attendance_rate' => $attendanceRate,

        'training_sessions_count' => $trainingSessions->count(),

        // لو عندك score
        'average_screening_score' => round(
            $mentorRequests->avg('screening_score') ?? 0
        ),

        'average_final_screening_score' => round(
            $mentorRequests->avg('final_score') ?? 0
        ),

        'status' => $step?->status ?? 'ongoing',

        'current_step' => $step?->title ?? 'Training Phase',
    ];
}
$almunies = Almuni::where('company_id', $data['company']->id)->get();

foreach ($almunies as $almuni) {

    $programsRaw = json_decode($almuni->program ?? '[]', true);
    if (!is_array($programsRaw)) continue;

    foreach ($programsRaw as $p) {
        $p = (object)$p;
        $mentorshipData = collect(json_decode($almuni->mentorship ?? '[]', true));
        $mentorshipCount = $mentorshipData->count();
        $attendanceRate = $mentorshipCount
            ? round(($mentorshipData->where('attendance', 'Attended')->count() / $mentorshipCount) * 100)
            : 0;
        $trainingData = collect(json_decode($almuni->training ?? '[]', true));
        $finalScreeningData = collect(json_decode($almuni->final_screening ?? '[]', true));
        $screeningData = collect(json_decode($almuni->screening ?? '[]', true));
        $orgName = 'N/A';
        if (isset($p->Organization)) {
            $org = $p->Organization;
            if (is_array($org)) {
                $orgName = $org['name_ar'] ?? $org['name_en'] ?? $org['name'] ?? 'N/A';
            } elseif (is_object($org)) {
                $orgName = $org->name_ar ?? $org->name_en ?? $org->name ?? 'N/A';
            } elseif (is_string($org)) {
                $orgName = $org;
            }
        }

        $programs[] = (object)[
            'program_name' => $p->Program_Name ?? $p->title ?? 'N/A',
            'organization' => $orgName,
            'mentorship_sessions_count' => $mentorshipCount,
            'attendance_rate' => $attendanceRate,
            'training_sessions_count' => $trainingData->count(),
            'average_screening_score' => round(
                $screeningData->avg('overall_score') ?? 0
            ),
            'average_final_screening_score' => round(
                $finalScreeningData->avg('overall_score') ?? 0
            ),
            'status' => 'complete',
            'current_step' => 'Alumni',
        ];
    }
}

        // $programs = [
        //     (object)[
        //         'program_name' => 'Full Stack Development Bootcamp',
        //         'organization' => 'Tech Academy',
        //         'mentorship_sessions_count' => 12,
        //         'attendance_rate' => 92, // percentage
        //         'training_sessions_count' => 40,
        //         'average_screening_score' => 80, // int or float
        //         'average_final_screening_score' => 85, // int or float
        //         'status' => 'ongoing',
        //         'current_step' => 'Training Phase',
        //     ],
        //     (object)[
        //         'program_name' => 'Digital Marketing Training',
        //         'organization' => 'Marketing Institute',
        //         'mentorship_sessions_count' => 8,
        //         'attendance_rate' => 88,
        //         'training_sessions_count' => 25,
        //         'average_screening_score' => 75,
        //         'average_final_screening_score' => 78,
        //         'status' => 'completed',
        //         'current_step' => 'Final Evaluation',
        //     ],
        //     (object)[
        //         'program_name' => 'Project Management Mentorship',
        //         'organization' => 'PMI Center',
        //         'mentorship_sessions_count' => 10,
        //         'attendance_rate' => 95,
        //         'training_sessions_count' => 18,
        //         'average_screening_score' => 90,
        //         'average_final_screening_score' => 92,
        //         'status' => 'ongoing',
        //         'current_step' => 'initial screening',
        //     ],
        // ];

        $data['programs'] = $programs;

        // dd($data['mentorship_sessions']);
        return $data;
    }

    public function exportFinalScreening(Request $request)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $program_id = $request->input('program_id', null);
        $format = $request->input('format', 'excel');
        $filters = $request->input('filters', []);

        // Get the same data as the report view but process it for export
        $reportData = $this->finalScreeningReport($orgIds, $user, $program_id);
        $startups = $reportData['startups'];
        $judges = $reportData['judges'];
        
        // Apply filters to the data
        $filteredStartups = $this->applyExportFilters($startups, $judges, $filters);
        
        // Process data for export
        $exportData = $this->prepareExportData($filteredStartups, $judges);
        
        // Generate filename with timestamp
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "final_screening_report_{$timestamp}";
        
        if ($format === 'csv') {
            return Excel::download(new FinalScreeningReportExport($exportData, $judges), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } else {
            return Excel::download(new FinalScreeningReportExport($exportData, $judges), $filename . '.xlsx');
        }
    }
    
    private function applyExportFilters($startups, $judges, $filters)
    {
        $filtered = $startups->filter(function ($startup) use ($filters) {
            // Apply search filter
            if (!empty($filters['search'])) {
                $searchTerm = strtolower($filters['search']);
                $companyName = strtolower($startup->name ?? '');
                $userName = strtolower($startup->user->name ?? '');
                
                // Get category string
                $categories = is_string($startup->business_categories) ? 
                    json_decode($startup->business_categories, true) : $startup->business_categories;
                $categoryString = is_array($categories) ? 
                    implode(' ', array_filter($categories)) : ($startup->business_categories ?? '');
                $categoryString = strtolower(str_replace(['/', ','], ' ', $categoryString ?? ''));
                
                if (!str_contains($companyName, $searchTerm) && 
                    !str_contains($userName, $searchTerm) && 
                    !str_contains($categoryString, $searchTerm)) {
                    return false;
                }
            }
            
            // Apply cycle filter
            if (!empty($filters['cycle'])) {
                $cycleId = $startup->stepdata->cycle->id ?? ($startup->cycledata->id ?? '');
                if (strval($cycleId) !== strval($filters['cycle'])) {
                    return false;
                }
            }
            
            // Apply step filter
            if (!empty($filters['step'])) {
                $stepId = $startup->stepdata->id ?? $startup->step ?? '';
                if (strval($stepId) !== strval($filters['step'])) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Apply judge and score filters (more complex filtering)
        return $filtered->filter(function ($startup) use ($judges, $filters) {
            // Get judge evaluations for this startup
            $startupJudges = $judges->filter(function($judge) use ($startup) {
                return collect($judge->companies)->where('id', $startup->id)->isNotEmpty();
            });
            
            // Apply judge filter
            if (!empty($filters['judge'])) {
                $hasJudge = $startupJudges->contains('id', $filters['judge']);
                if (!$hasJudge) {
                    return false;
                }
            }
            
            // Calculate scores for filtering
            $totalScore = 0;
            $publishedEvaluations = 0;
            $evaluationStatuses = [];
            
            foreach ($startupJudges as $judge) {
                $scoped_company = collect($judge->companies)->where('id', $startup->id)->first();
                if ($scoped_company) {
                    $status = strtolower($scoped_company->status ?? 'not-evaluated');
                    $evaluationStatuses[] = $status;
                    
                    if ($status === 'published' && isset($scoped_company->score)) {
                        $totalScore += (float) $scoped_company->score;
                        $publishedEvaluations++;
                    }
                }
            }
            
            $averageScore = $publishedEvaluations > 0 ? $totalScore / $publishedEvaluations : 0;
            
            // Apply score filter
            if (!empty($filters['score'])) {
                switch ($filters['score']) {
                    case 'excellent':
                        if ($averageScore < 75 || $averageScore > 100) return false;
                        break;
                    case 'good':
                        if ($averageScore < 50 || $averageScore >= 75) return false;
                        break;
                    case 'average':
                        if ($averageScore < 25 || $averageScore >= 50) return false;
                        break;
                    case 'poor':
                        if ($averageScore >= 25) return false;
                        break;
                }
            }
            
            // Apply status filter
            if (!empty($filters['status'])) {
                $primaryStatus = 'not-evaluated';
                $hasPublished = in_array('published', $evaluationStatuses);
                $hasDraft = in_array('draft', $evaluationStatuses);
                $hasNotEvaluated = in_array('not-evaluated', $evaluationStatuses) || empty($evaluationStatuses);
                
                if ($hasPublished && !$hasDraft && !$hasNotEvaluated) {
                    $primaryStatus = 'published';
                } elseif ($hasDraft) {
                    $primaryStatus = 'draft';
                }
                
                switch ($filters['status']) {
                    case 'published':
                        if ($primaryStatus !== 'published' && !$hasPublished) return false;
                        break;
                    case 'draft':
                        if ($primaryStatus !== 'draft' && !$hasDraft) return false;
                        break;
                    case 'not-evaluated':
                        if ($primaryStatus !== 'not-evaluated' && !$hasNotEvaluated) return false;
                        break;
                }
            }
            
            return true;
        });
    }
    
    private function prepareExportData($startups, $judges)
    {
        return $startups->map(function ($startup) use ($judges) {
            // Get judges for this startup
            $startupJudges = $judges->filter(function($judge) use ($startup) {
                return collect($judge->companies)->where('id', $startup->id)->isNotEmpty();
            });
            
            // Calculate average score and collect judge data
            $totalScore = 0;
            $publishedEvaluations = 0;
            $judgeScores = [];
            $judgeNames = [];
            $evaluationStatuses = [];
            
            foreach ($judges as $judge) {
                $scoped_company = collect($judge->companies)->where('id', $startup->id)->first();
                if ($scoped_company) {
                    $judgeNames[] = $judge->name;
                    $status = strtolower($scoped_company->status ?? 'not-evaluated');
                    $evaluationStatuses[] = ucfirst($status);
                    
                    if (isset($scoped_company->score)) {
                        $judgeScores[$judge->id] = $scoped_company->score;
                        if ($status === 'published') {
                            $totalScore += (float) $scoped_company->score;
                            $publishedEvaluations++;
                        }
                    } else {
                        $judgeScores[$judge->id] = 'N/A';
                    }
                } else {
                    $judgeScores[$judge->id] = 'N/A';
                }
            }
            
            $averageScore = $publishedEvaluations > 0 ? round($totalScore / $publishedEvaluations, 1) : 0;
            
            // Determine overall evaluation status
            $overallStatus = 'Not Evaluated';
            if (in_array('Published', $evaluationStatuses)) {
                $overallStatus = 'Published';
            } elseif (in_array('Draft', $evaluationStatuses)) {
                $overallStatus = 'Draft';
            }
            
            // Add calculated fields to startup object
            $startup->average_score = $averageScore;
            $startup->judges_names = implode(', ', array_unique($judgeNames));
            $startup->evaluation_status = $overallStatus;
            $startup->judge_scores = $judgeScores;
            
            return $startup;
        });
    }

    public function exportScreening(Request $request)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $program_id = $request->input('program_id', null);
        $format = $request->input('format', 'excel');
        $filters = $request->input('filters', []);

        // Get the same data as the report view but process it for export
        $reportData = $this->screeningReport($orgIds, $user, $program_id);
        $startups = $reportData['startups'];
        $judges = $reportData['judges'];
        
        // Apply filters to the data
        $filteredStartups = $this->applyScreeningExportFilters($startups, $judges, $filters);
        
        // Process data for export
        $exportData = $this->prepareScreeningExportData($filteredStartups, $judges);
        
        // Generate filename with timestamp
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "screening_report_{$timestamp}";
        
        if ($format === 'csv') {
            return Excel::download(new ScreeningReportExport($exportData, $judges), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        } else {
            return Excel::download(new ScreeningReportExport($exportData, $judges), $filename . '.xlsx');
        }
    }
    
    private function applyScreeningExportFilters($startups, $judges, $filters)
    {
        $filtered = $startups->filter(function ($startup) use ($filters) {
            // Apply search filter
            if (!empty($filters['search'])) {
                $searchTerm = strtolower($filters['search']);
                $companyName = strtolower($startup->name ?? '');
                $userName = strtolower($startup->user->name ?? '');
                
                // Get category string
                $categories = is_string($startup->business_categories) ? 
                    json_decode($startup->business_categories, true) : $startup->business_categories;
                $categoryString = is_array($categories) ? 
                    implode(' ', array_filter($categories)) : ($startup->business_categories ?? '');
                $categoryString = strtolower(str_replace(['/', ','], ' ', $categoryString ?? ''));
                
                if (!str_contains($companyName, $searchTerm) && 
                    !str_contains($userName, $searchTerm) && 
                    !str_contains($categoryString, $searchTerm)) {
                    return false;
                }
            }
            
            // Apply cycle filter
            if (!empty($filters['cycle'])) {
                $cycleId = $startup->stepdata->cycle->id ?? ($startup->cycledata->id ?? '');
                if (strval($cycleId) !== strval($filters['cycle'])) {
                    return false;
                }
            }
            
            // Apply step filter
            if (!empty($filters['step'])) {
                $stepId = $startup->stepdata->id ?? $startup->step ?? '';
                if (strval($stepId) !== strval($filters['step'])) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Apply judge and score filters (more complex filtering)
        return $filtered->filter(function ($startup) use ($judges, $filters) {
            // Get judge evaluations for this startup
            $startupJudges = $judges->filter(function($judge) use ($startup) {
                return collect($judge->companies)->where('id', $startup->id)->isNotEmpty();
            });
            
            // Apply judge filter
            if (!empty($filters['judge'])) {
                $hasJudge = $startupJudges->contains('id', $filters['judge']);
                if (!$hasJudge) {
                    return false;
                }
            }
            
            // Calculate scores for filtering
            $totalScore = 0;
            $publishedEvaluations = 0;
            $evaluationStatuses = [];
            
            foreach ($startupJudges as $judge) {
                $scoped_company = collect($judge->companies)->where('id', $startup->id)->first();
                if ($scoped_company) {
                    $status = strtolower($scoped_company->status ?? 'not-evaluated');
                    $evaluationStatuses[] = $status;
                    
                    if ($status === 'published' && isset($scoped_company->score)) {
                        $totalScore += (float) $scoped_company->score;
                        $publishedEvaluations++;
                    }
                }
            }
            
            $averageScore = $publishedEvaluations > 0 ? $totalScore / $publishedEvaluations : 0;
            
            // Apply score filter
            if (!empty($filters['score'])) {
                switch ($filters['score']) {
                    case 'excellent':
                        if ($averageScore < 75 || $averageScore > 100) return false;
                        break;
                    case 'good':
                        if ($averageScore < 50 || $averageScore >= 75) return false;
                        break;
                    case 'average':
                        if ($averageScore < 25 || $averageScore >= 50) return false;
                        break;
                    case 'poor':
                        if ($averageScore >= 25) return false;
                        break;
                }
            }
            
            // Apply status filter
            if (!empty($filters['status'])) {
                $primaryStatus = 'not-evaluated';
                $hasPublished = in_array('published', $evaluationStatuses);
                $hasDraft = in_array('draft', $evaluationStatuses);
                $hasNotEvaluated = in_array('not-evaluated', $evaluationStatuses) || empty($evaluationStatuses);
                
                if ($hasPublished && !$hasDraft && !$hasNotEvaluated) {
                    $primaryStatus = 'published';
                } elseif ($hasDraft) {
                    $primaryStatus = 'draft';
                }
                
                switch ($filters['status']) {
                    case 'published':
                        if ($primaryStatus !== 'published' && !$hasPublished) return false;
                        break;
                    case 'draft':
                        if ($primaryStatus !== 'draft' && !$hasDraft) return false;
                        break;
                    case 'not-evaluated':
                        if ($primaryStatus !== 'not-evaluated' && !$hasNotEvaluated) return false;
                        break;
                }
            }
            
            return true;
        });
    }
    
    private function prepareScreeningExportData($startups, $judges)
    {
        return $startups->map(function ($startup) use ($judges) {
            // Get judges for this startup
            $startupJudges = $judges->filter(function($judge) use ($startup) {
                return collect($judge->companies)->where('id', $startup->id)->isNotEmpty();
            });
            
            // Calculate average score and collect judge data
            $totalScore = 0;
            $publishedEvaluations = 0;
            $judgeScores = [];
            $judgeNames = [];
            $evaluationStatuses = [];
            
            foreach ($judges as $judge) {
                $scoped_company = collect($judge->companies)->where('id', $startup->id)->first();
                if ($scoped_company) {
                    $judgeNames[] = $judge->name;
                    $status = strtolower($scoped_company->status ?? 'not-evaluated');
                    $evaluationStatuses[] = ucfirst($status);
                    
                    if (isset($scoped_company->score)) {
                        $judgeScores[$judge->id] = $scoped_company->score;
                        if ($status === 'published') {
                            $totalScore += (float) $scoped_company->score;
                            $publishedEvaluations++;
                        }
                    } else {
                        $judgeScores[$judge->id] = 'N/A';
                    }
                } else {
                    $judgeScores[$judge->id] = 'N/A';
                }
            }
            
            $averageScore = $publishedEvaluations > 0 ? round($totalScore / $publishedEvaluations, 1) : 0;
            
            // Determine overall evaluation status
            $overallStatus = 'Not Evaluated';
            if (in_array('Published', $evaluationStatuses)) {
                $overallStatus = 'Published';
            } elseif (in_array('Draft', $evaluationStatuses)) {
                $overallStatus = 'Draft';
            }
            
            // Add calculated fields to startup object
            $startup->average_score = $averageScore;
            $startup->judges_names = implode(', ', array_unique($judgeNames));
            $startup->evaluation_status = $overallStatus;
            $startup->judge_scores = $judgeScores;
            
            return $startup;
        });
    }
}
