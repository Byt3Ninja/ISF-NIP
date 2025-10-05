<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Cycle;
use App\Company;
use App\FinalData;
use App\Screening;
use App\SurveyScopes;
use App\Finalscreening;
use App\SurveyResponses;
use App\TrainingSession;
use App\MentorshipRequest;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use App\ScreeningEvaluationResults;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\FinalScreeningReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(){
        $organization_id = auth()->user()->organization_id;
        $cycles = Cycle::when(!is_null($organization_id),function ($q)use($organization_id){
            $q->where('organization_id',$organization_id);
        })->get();
        $companies = Company::whereHas('cycledata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($query)use($organization_id){
                $query->where('organization_id',$organization_id);
            });
        })->get();


        $data['mentorRequests'] =  MentorshipRequest::all();
        $available_surveys = SurveyResponses::whereIn('user_id', $data['mentorRequests']->pluck('mentor_id'))->whereIn('triggered_at', $data['mentorRequests']->pluck('id'))->where('triggered_by', 'session')->get();


        return view('admin.reports.index',compact('companies','cycles','available_surveys'));
    }
    public function View($type,Request $request){
        $organization_id = auth()->user()->organization_id;
        switch ($type){
            case 'mentorship':
                $data = $this->mentorshipReport($organization_id);
                $view = '_mentorshipReport';
                break;
            case 'training':
                $data = $this->trainingReport($organization_id);
                $view = '_trainingSessionsReport';
                break;
            case 'finalScreening':
                $data = $this->finalScreeningReport($organization_id);
                $view = '_finalScreeningReport';
                break;
            case 'cycles':
                $data = $this->cyclesReport($organization_id,$request->id);
                $view = '_cyclesReport';
                break;
            case 'companies':
                $data = $this->companiesReport($organization_id,$request->id);
                $view = '_companyReport';
                break;
                
            case 'mentorSurveys':
                $data = $this->mentorSurveyReport($request->get('status', 'all'));
                $view = '_mentorSurvey';
                break;

            case 'startupSurveys':
                $data = $this->startupSurveysReport();
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

    protected function startupSurveysReport(){
        $data['mentorRequests'] =  MentorshipRequest::all();
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
    protected function mentorSurveyReport(){
        $status = request('status', 'all'); 
        $data['mentorRequests'] =  MentorshipRequest::all();
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


    protected function mentorshipReport($organization_id){
        $mentorRequests =  MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
            $q->where('organization_id',$organization_id);
        });

        $data['cycle_mentors'] = User::whereHas('mentorSessions',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($query)use($organization_id){
                $query->where('organization_id',$organization_id);
            });
        })->get();
        // ==============> counts <=================

        // $data['mentors_count'] = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->count();
		$data['mentors_count'] = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'mentor')
			->select('users.*')
			->count();
        $data['total_sessions_count']= $mentorRequests->count();
        $data['mentorships_period'] =$mentorRequests->sum('period')/60;
        $data['approved_sessions_count'] = $mentorRequests->whereNotNull('start_url')->count();
        $data['pending_sessions_count'] = MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->whereNotNull('start_url')->whereDate('zoom_date','>=',now()->format('Y-m-d'))->count();
        $data['done_sessions_count'] = MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->where('approved','Yes')->whereDate('zoom_date','<',now()->format('Y-m-d'))->count();
        $data['disapproved_sessions_count'] = MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->where('approved','No')->count();
        $data['new_sessions_count'] = MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->whereNull('approved')->count();
        $data['cycle_mentors_count'] =count($data['cycle_mentors']);
        return $data;
    }

    protected function trainingReport($organization_id){
        $cycles = TrainingSession::with('step.cycle')->get()->pluck('step.cycle')->unique('id');
        $data['cycles'] = $cycles->when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->where('organization_id', $organization_id);
        });

        $steps = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->whereHas('cycle', function ($query) use ($organization_id) {
                $query->where('organization_id', $organization_id);
            });
        })->where('stype', 4)->get();
        $data['steps'] = $steps;
        
        $sessions = TrainingSession::with('reports')->whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->when(!is_null($organization_id),function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            });
        })->orderBy('datetime', 'ASC');
        $data['sessions'] = $sessions->get();

        $data['total_sessions_count'] = $sessions->count();
        $data['sessions_period'] = $sessions->sum('duration')/60;
        $data['zoom_sessions_count'] = TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->when(!is_null($organization_id),function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            });
        })->where('type','sessions')->count();

        $data['offline_sessions_count'] = TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->when(!is_null($organization_id),function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            });
        })->where('type','offline')->count();

        $data['online_sessions_count'] = TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->when(!is_null($organization_id),function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            });
        })->where('type','sessions')->count();


         $data['online_training_count'] = TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->when(!is_null($organization_id),function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            });
        })->where('type','online')->count();
        return $data;
    }

    protected function finalScreeningReport($organization_id){
        // $data['judgers_count'] = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->count();
		$data['judgers_count'] = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->count();

        $cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->where('organization_id', $organization_id);
        })->get();

        $steps = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->whereHas('cycle', function ($query) use ($organization_id) {
            $query->where('organization_id', $organization_id);
        });
        })->where('stype', 5)->orderBy('from')->get();

        // Get final screening step IDs and evaluation IDs for later use
        $final_screening_steps = $steps->pluck('id');
        $evaluation_ids = ScreeningEvaluation::whereIn('step_id', $final_screening_steps)->pluck('id');

        // Get real data for final screenings
        $final_screenings = Company::whereHas('stepdata', function ($q) use ($organization_id) {
            $q->when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('stype', 5);
        })->get();

        $final_pending_screenings = Company::whereHas('stepdata', function ($q) use ($organization_id) {
            $q->when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('stype', 5);
        })->doesntHave('finalData')->get();

        $final_done_screenings = Company::whereHas('stepdata', function ($q) use ($organization_id) {
            $q->when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('stype', 5);
        })->whereHas('finalData')->get();

        // Calculate draft count (companies with screening evaluations in draft status)
        $draft_screenings_count = Company::whereHas('stepdata', function ($q) use ($organization_id) {
            $q->when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('stype', 5);
        })->whereExists(function ($query) use ($evaluation_ids) {
            $query->select(DB::raw(1))
                ->from('screening_evaluation_results')
                ->whereIn('evaluation_id', $evaluation_ids)
                ->whereRaw('screening_evaluation_results.company_id = companies.id')
                ->where('status', 0); // 0 = draft, 1 = published
        })->count();

        $final_screening_count = count($final_screenings);
        $final_pending_screening_count = count($final_pending_screenings);
        $final_done_screening_count = count($final_done_screenings);

        // Kpi Cards Data (Dont' remove)
        $kpiData = [
            [ 'value' => $data['judgers_count'] ?? 0, 'label' => 'Judgers', 'icon' => 'user', 'color' => 'info'],
            [ 'value' => $final_screening_count ?? 0, 'label' => 'Total', 'icon' => 'gavel', 'color' => 'primary'],
            [ 'value' => $final_pending_screening_count ?? 0, 'label' => 'Not Evaluated', 'icon' => 'trending-up-2', 'color' => 'danger'],
            [ 'value' => $draft_screenings_count ?? 0, 'label' => 'Draft', 'icon' => 'pencil-check', 'color' => 'secondary'],
            [ 'value' => $final_done_screening_count ?? 0, 'label' => 'Published', 'icon' => 'progress-check', 'color' => 'success'],
        ];

        // Get real startups data from database
        $startups = Company::with(['user', 'stepdata.cycle.organization'])
            ->whereHas('stepdata', function ($q) use ($organization_id) {
                $q->when(!is_null($organization_id), function ($q) use ($organization_id) {
                    return $q->whereHas('cycle', function ($query) use ($organization_id) {
                        $query->where('organization_id', $organization_id);
                    });
                })->where('stype', 5); // Final screening steps
            })->get();

        // Get real judges data with their evaluations
        $judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.default_for', 'judger')
            ->select('users.*')
            ->get()
            ->map(function ($judge) use ($final_screening_steps, $evaluation_ids) {
                // Get companies this judge evaluates through Finalscreening table
                $finalScreenings = Finalscreening::whereIn('step_id', $final_screening_steps)
                    ->whereJsonContains('judges', (string) $judge->id)
                    ->get();
                
                $companies = $finalScreenings->map(function ($screening) use ($judge, $evaluation_ids) {
                    // Get the screening evaluation results for this company and judge
                    $results = ScreeningEvaluationResults::whereIn('evaluation_id', $evaluation_ids)
                        ->where('company_id', $screening->company_id)
                        ->where('user_id', $judge->id)
                        ->get();
                    
                    $status = 'Not Evaluated';
                    $score = 0;
                    
                    if ($results->isNotEmpty()) {
                        $status = $results->contains('status', 0) ? 'Draft' : 'Published';
                        $score = $results->sum('score');
                    }
                    
                    return (object) [
                        'id' => $screening->company_id,
                        'status' => $status,
                        'score' => $score
                    ];
                });

                $judge->companies_ids = $companies->pluck('id')->toArray();
                $judge->startups = $judge->companies_ids;
                $judge->companies = $companies->toArray();

                return $judge;
            });

        $data['cycles'] = $cycles;
        $data['steps'] = $steps;
        $data['kpiData'] = $kpiData;
        $data['startups'] = $startups;
        $data['judges'] = $judges;

        // $metadata = ScreeningEvaluationResults::metadataReport($steps->pluck('id')->toArray());

        // $allCompanies = collect();
        // foreach ($metadata as $stepId => $dataSet) {
        //     $allCompanies = $allCompanies->concat($dataSet['companies']);
        // }
        //  $allCompanies = $allCompanies->unique('id')->values();

        // $data['screening_sessions_count'] = $allCompanies->count();
        // $final_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id){
        //     $q->when(!is_null($organization_id),function ($q) use($organization_id){
        //         return $q->whereHas('cycle',function ($query) use ($organization_id){
        //             $query->where('organization_id',$organization_id);
        //         });
        //     })->where('stype', 5);
        // })->get();
        // $final_pending_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id){
        //     $q->when(!is_null($organization_id),function ($q) use($organization_id){
        //         return $q->whereHas('cycle',function ($query) use ($organization_id){
        //             $query->where('organization_id',$organization_id);
        //         });
        //     })->where('stype', 5);
        // })->doesntHave('finalData')->get();
        // $final_done_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id){
        //     $q->when(!is_null($organization_id),function ($q) use($organization_id){
        //         return $q->whereHas('cycle',function ($query) use ($organization_id){
        //             $query->where('organization_id',$organization_id);
        //         });
        //     })->where('stype', 5);
        // })->whereHas('finalData')->get();

        // $screening_pending_sessions =  Company::whereHas('stepdata',function ($q)use ($organization_id){
        //     $q->when(!is_null($organization_id),function ($q) use($organization_id){
        //         return $q->whereHas('cycle',function ($query) use ($organization_id){
        //             $query->where('organization_id',$organization_id);
        //         });
        //     })->where('stype', 2)->whereHas('formData',function ($q){
        //         $q->where('screening','{}');
        //     });
        // })->get();
        // $screening_done_sessions =  Company::whereHas('stepdata',function ($q)use ($organization_id){
        //     $q->when(!is_null($organization_id),function ($q) use($organization_id){
        //         return $q->whereHas('cycle',function ($query) use ($organization_id){
        //             $query->where('organization_id',$organization_id);
        //         });
        //     })->where('stype', 2)->whereHas('formData',function ($q){
        //         $q->where('screening','<>','{}');
        //     });
        // })->get();

        // $data['screening_pending_sessions_count'] = count($screening_pending_sessions);
        // $data['screening_done_sessions_count'] = count($screening_done_sessions);
        // $data['final_screening_count']=count($final_screenings);
        // $data['final_pending_screening_count']=count($final_pending_screenings);
        // $data['final_done_screening_count']=count($final_done_screenings);
        // $data['screening_companies']= $screening_pending_sessions;
        // $data['final_screening_companies']= $final_pending_screenings;
        return $data;
    }

    protected function cyclesReport($organization_id,$cycle_id){
        $cycle = Cycle::find($cycle_id);
        $data['mentors_count'] = User::whereHas('mentorSessions',function ($q)use ($organization_id,$cycle_id){
            $q->where('cycle_id',$cycle_id)->when(!is_null($organization_id),function ($q)use ($organization_id){
                $q->where('organization_id',$organization_id);
            });
        })->count();
        $mentorRequests =  MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->where('cycle_id',$cycle_id);

        $training_sessions = TrainingSession::whereHas('step',function ($q)use ($organization_id,$cycle_id){
            $q->where('stype',4)->whereHas('cycle',function ($query) use ($organization_id,$cycle_id){
                    $query->where('cycles.id',$cycle_id)->when(!is_null($organization_id),function ($query) use ($organization_id){
                        $query->where('organization_id',$organization_id);
                    });
            });
        });
        $startups = Company::whereHas('cycledata',function ($q) use ($organization_id,$cycle_id){
            $q->where('cycles.id',$cycle_id)->when(!is_null($organization_id),function ($q)use ($organization_id){
                 $q->where('organization_id',$organization_id);
            });
        });
        $screenings =Company::whereHas('stepdata',function ($q)use ($organization_id,$cycle_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id,$cycle_id){
                    $query->where('cycles.id',$cycle_id)->when(!is_null($organization_id),function ($q)use ($organization_id){
                        $q->where('organization_id',$organization_id);
                    });
            })->where('stype', 2);
        })->count();
        $final_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id,$cycle_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id,$cycle_id){
                    $query->where('cycles.id',$cycle_id)->when(!is_null($organization_id),function ($q) use($organization_id){
                        $q->where('organization_id',$organization_id);
                    });
            })->where('stype', 5);
        })->count();
        $data['training_sessions_count'] = $training_sessions->count();
        $data['training_sessions_period'] = $training_sessions->sum('duration')/60;
        $data['mentorRequests_count']  = $mentorRequests->count();
        $data['mentorships_period']= $mentorRequests->sum('period')/60;
        $data['mentorship_attendance_rate']= $mentorRequests->count()?($mentorRequests->sum('done')/$mentorRequests->count())*100:0;
        $data['cycle']=$cycle;
        $data['startups']=$startups->count();
        $data['screening_sessions_count']=$screenings;
        $data['final_screening_count']=$final_screenings;

        return $data;
    }

    protected function companiesReport($organization_id,$company_id){
        $data['company']= Company::find($company_id);
        $mentorRequests =  MentorshipRequest::where('user_id',$data['company']->user->id);
        $training_sessions = TrainingSession::whereHas('step',function ($q)use ($organization_id,$company_id){
            $q->where('stype',4)->whereHas('companies',function ($q)use ($company_id){
             $q->where('companies.id',$company_id);
            });
        });

        $data['training_sessions_count'] = $training_sessions->count();
        $data['training_sessions_period'] = $training_sessions->sum('duration')/60;
        $data['mentorRequests_count']  = $mentorRequests->count();
        $data['mentorships_period']= $mentorRequests->sum('period')/60;
        if ($mentorRequests->count()){

            $data['mentorship_attendance_rate']= ($mentorRequests->sum('done')/$mentorRequests->count())*100;
        }else{
            $data['mentorship_attendance_rate']= 0;
        }
        $data['mentorship_sessions']=$mentorRequests->get();
        $data['training_sessions']=$training_sessions->get();
        return $data;
    }

    public function exportFinalScreening(Request $request)
    {
        $organization_id = auth()->user()->organization_id;
        $format = $request->input('format', 'excel');
        $filters = $request->input('filters', []);
        
        // Get the same data as the report view but process it for export
        $reportData = $this->finalScreeningReport($organization_id);
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
}
