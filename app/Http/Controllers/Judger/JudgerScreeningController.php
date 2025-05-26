<?php

namespace App\Http\Controllers\Judger;

use App\Company;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationResults;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class JudgerScreeningController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user_id = $user->id;

        $companies_q = "SELECT
                cm.id AS `company_id`,
                cm.name_en AS `company_name_en`,
                cm.name_ar AS `company_name_ar`,
                c.id AS `program_id`,
                c.title AS `program_title`,
                s.id AS `step_id`,
                s.title AS `step_title`,
                s.stype AS `step_type`
            FROM
                companies cm
            LEFT JOIN
                cycles c ON cm.cycle = c.id
            LEFT JOIN
                steps s ON s.id = cm.step
            WHERE
                cm.deleted_at IS NULL
                AND s.id IN (
                    SELECT
                        step_id
                    FROM
                        screenings scr
                    WHERE
                        scr.deleted_at IS NULL
                        AND scr.user_id = ? 
                )
        ";

        $l = app()->getLocale();
        $companies = collect( DB::select($companies_q, [$user_id]) );
        $companies = $companies->map(function( $c ) use ( $l ) {
            $company = new stdClass;
            $company->id = $c->company_id;
            $company->name = $c->{'company_name_' . $l};
            $company->program = new stdClass;
            $company->program->id = $c->program_id;
            $company->program->name = $c->program_title;
            $company->step = new stdClass;
            $company->step->id = $c->step_id;
            $company->step->name = $c->step_title;
            $company->step->type = $c->step_type == 2 ? 'initial' : ( $c->step_type == 5 ? 'final' : 'other' );
            return $company;
        });

        $steps_ids = $companies->map( function( $c ) { return $c->step->id; } )->toArray();
        $evaluations = ScreeningEvaluation::getEvaluationData( $steps_ids );
        $evaluation_ids = $evaluations->pluck('step', 'id')->toArray();

        $myResponses = ScreeningEvaluationResults::whereIn('evaluation_id', array_keys( $evaluation_ids ))->where('user_id', $user_id)->get();
        $myResponses = $myResponses->map( function( $response ) use ( $evaluation_ids ) {
            $response->step_id = $evaluation_ids[$response->evaluation_id] ?? null;
            return $response;
        });
        $companies = $companies->map(function( $company ) use ( $myResponses ) {
            $step_id = $company->step->id;
            $status = 'not-started';
            if( $myResponses->where('step_id', $step_id)->count() > 0 ) { $status = 'in-progress'; }
            if( $myResponses->where('step_id', $step_id)->where('status', true)->count() > 0 ) { $status = 'completed'; }
            $company->status = $status;
            $company->icon = $status == 'in-progress' ? 'ti ti-file-text' : ($status == 'completed' ? 'ti ti-circle-check' : 'ti ti-gavel');
            return $company;
        });

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

        /* $statusData = collect([
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
        ]); */

        $statusCounts = $companies->groupBy('status')->map->count();
        $statusData = [
            (object) ['status' => 'startups', 'count' => $companies->count()],
            (object) ['status' => 'not-started', 'count' => $statusCounts['not-started'] ?? 0],
            (object) ['status' => 'in-progress', 'count' => $statusCounts['in-progress'] ?? 0],
            (object) ['status' => 'completed', 'count' => $statusCounts['completed'] ?? 0],
        ];
        $statuses = ['not-started', 'in-progress', 'completed'];

        // Add your logic here
        return view('judger.screening.index', compact('companies', 'screeningData', 'statusData', 'statuses'));
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
