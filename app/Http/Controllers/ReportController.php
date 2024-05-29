<?php

namespace App\Http\Controllers;

use App\MentorshipRequest;
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
                $mentorRequests =  MentorshipRequest::when(!is_null($organization_id),function ($q)use ($organization_id){
                    $q->where('organization_id',$organization_id);
                });

                $data['cycle_mentors'] = User::whereHas('mentorCycles',function ($q)use ($organization_id){
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

                $view = '_mentorshipReport';
                break;
            case 'training':
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

                $view = '_trainingSessionsReport';

                break;
        }
        $view = view('admin.reports.ajax.'.$view, $data)->render();
        return response()->json(['value' => 1, 'view' => $view]);

    }
}
