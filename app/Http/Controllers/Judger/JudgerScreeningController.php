<?php

namespace App\Http\Controllers\Judger;

use App\Company;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationResults;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class JudgerScreeningController extends Controller
{
    public function index()
    {
        $screeningData = collect([
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
            (object)[
            'status' => 'completed',
            'startupTitle' => 'Startup One',
            ],
            (object)[
            'status' => 'in-progress',
            'startupTitle' => 'Startup Two',
            ],
            (object)[
            'status' => 'not-started',
            'startupTitle' => 'Startup Three',
            ],
        ]);

        $statusData = collect([
            (object)[
                'status' => 'startups',
                'count' => $screeningData->count(),
            ],
            (object)[
                'status' => 'not-started',
                'count' => $screeningData->where('status', 'not-started')->count(),
            ],
            (object)[
                'status' => 'in-progress',
                'count' => $screeningData->where('status', 'in-progress')->count(),
            ],
            (object)[
                'status' => 'completed',
                'count' => $screeningData->where('status', 'completed')->count(),
            ],
        ]);

        $statuses = ['not-started', 'in-progress', 'completed'];

        // Add your logic here
        return view('judger.screening.index', compact('screeningData', 'statusData', 'statuses'));
    }

    public function save_evaluation_draft(Request $request)
    {
        return $this->store_evaluation('draft', $request);
    }

    public function publish_evaluation(Request $request)
    {
        return $this->store_evaluation('publish', $request);
    }

    private function store_evaluation($type, $request)
    {
        $user = auth()->user();
        $user_id = $user->id;
        if( $type == $request->type )
        {
            $company = Company::find( $request->program );
            $evaluationForm = ScreeningEvaluation::getEvaluationData( $company->step );
            if( $evaluationForm->id == $request->id )
            {
                $evaluation_id = $evaluationForm->id;
                if( $evaluationForm->status == 'Published' )
                {
                    $toCreate = [];
                    $myResponses = ScreeningEvaluationResults::where('evaluation_id', $evaluation_id)->where('user_id', $user_id)->get();
                    $have_published = $myResponses->where('status', true);
                    if( $have_published->count() > 0 ) { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'You have already published an evaluation form'], 200); }
                    else
                    {
                        $final_comment = $request->final_comment;
                        $myComment = ScreeningEvaluation::getMyFinalComment( $evaluation_id, $user_id );
                        $myComment->value = $final_comment;
                        $myComment->save();
                        foreach( $request->evaluation as $element )
                        {
                            $checkResponse = $myResponses->where('criteria_id', $element['id'])->where('user_id', $user_id)->first();
                            if( empty( $checkResponse ) )
                            {
                                $toCreate[] = [
                                    'evaluation_id' => $evaluation_id,
                                    'criteria_id' => $element['id'],
                                    'user_id' => $user_id,
                                    'score' => $element['score'],
                                    'comment' => $element['comment'],
                                    'status' => $type == 'publish' ? true : false
                                ];
                            }
                            else
                            {
                                $checkResponse->score = $element['score'];
                                $checkResponse->comment = $element['comment'];
                                $checkResponse->status = $type == 'publish' ? true : false;
                                $checkResponse->save();
                            }
                        }
                        if( count( $toCreate ) )
                        {
                            ScreeningEvaluationResults::insert( $toCreate );
                        }
                        return response()->json(['status' => true, 'title' => 'Success', 'message' => 'Evaluation form saved successfully'], 200);
                    }
                } else { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'Evaluation form is not published'], 200); }
            } else { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'Unsupported evaluation form detected'], 200); }
        } else { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'Unsupported evaluation type'], 200); }
    }
}
