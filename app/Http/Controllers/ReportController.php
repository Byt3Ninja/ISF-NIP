<?php

namespace App\Http\Controllers;

use App\Company;
use App\FinalData;
use App\Finalscreening;
use App\MentorshipRequest;
use App\Screening;
use App\TrainingSession;
use App\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(){
        return view('admin.reports.index');
    }
    public function View($type){
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
            case 'judge':
                $data = $this->judgeReport($organization_id);
                $view = '_judgeSessionReport';
                break;

        }
        $view = view('admin.reports.ajax.'.$view, $data)->render();
        return response()->json(['value' => 1, 'view' => $view]);

    }

    // =============> ajax report functions <=============

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

        $data['mentors_count'] = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->count();
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
        $sessions = TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->when(!is_null($organization_id),function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            });
        });
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
        })->where('type','online')->count();

        return $data;
    }

    protected function judgeReport($organization_id){
        $data['judgers_count'] = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->count();
        $screenings =Company::whereHas('stepdata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            })->where('stype', 2);
        })->get();
        $final_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            })->where('stype', 5);
        })->get();
        $final_pending_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            })->where('stype', 5);
        })->doesntHave('finalData')->get();
        $final_done_screenings= Company::whereHas('stepdata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            })->where('stype', 5);
        })->whereHas('finalData')->get();

        $screening_pending_sessions =  Company::whereHas('stepdata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            })->where('stype', 2)->whereHas('formData',function ($q){
                $q->where('screening','{}');
            });
        })->get();
        $screening_done_sessions =  Company::whereHas('stepdata',function ($q)use ($organization_id){
            $q->when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->whereHas('cycle',function ($query) use ($organization_id){
                    $query->where('organization_id',$organization_id);
                });
            })->where('stype', 2)->whereHas('formData',function ($q){
                $q->where('screening','<>','{}');
            });
        })->get();

        $data['screening_sessions_count'] = count($screenings);
        $data['screening_pending_sessions_count'] = count($screening_pending_sessions);
        $data['screening_done_sessions_count'] = count($screening_done_sessions);
        $data['final_screening_count']=count($final_screenings);
        $data['final_pending_screening_count']=count($final_pending_screenings);
        $data['final_done_screening_count']=count($final_done_screenings);
        $data['screening_companies']= $screening_pending_sessions;
        $data['final_screening_companies']= $final_pending_screenings;
        return $data;
    }
}
