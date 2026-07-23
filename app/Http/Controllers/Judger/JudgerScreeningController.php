<?php

namespace App\Http\Controllers\Judger;

use App\Company;
use App\ScreeningEvaluation;
use App\FormDataJudgesComments;
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

        $screenings = \App\Screening::where('user_id', $user_id)->get();
        $assignedCompanyIds = $screenings->pluck('users')->flatten()->filter()->unique()->toArray();
        $screenedStepIds = $screenings->pluck('step_id')->unique()->toArray();

        if (empty($assignedCompanyIds)) {
            $companies = collect();
        } else {
            $placeholders = implode(',', array_fill(0, count($assignedCompanyIds), '?'));
            $stepPlaceholders = implode(',', array_fill(0, count($screenedStepIds), '?'));
            
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
                    AND cm.id IN ($placeholders)
                    AND s.id IN ($stepPlaceholders)
            ";

            $bindings = array_merge($assignedCompanyIds, $screenedStepIds);
            $companies = collect(DB::select($companies_q, $bindings));
        }

        // Keep only startups the judge is actually assigned to AT their CURRENT
        // step. The query above matches any assigned company whose current step
        // is one the judge screens *somewhere*, which leaked startups the judge
        // screened in an earlier step but no longer judges after they advanced
        // (e.g. screened in Initial, moved to Final under different judges). For
        // an initial step (stype 2) the judge must be in screenings.users for
        // that exact step; for a final step (stype 5) the judge must be in the
        // finalscreenings.judges array for (step, company). This mirrors the
        // judger dashboard, which already scopes by current-step assignment.
        $screeningsByStep = $screenings->groupBy('step_id');
        $finalAssignedByStep = \App\Finalscreening::whereIn('step_id', $companies->pluck('step_id')->unique()->filter()->toArray())
            ->get()
            ->filter(function ($f) use ($user_id) {
                $judges = is_array($f->judges) ? $f->judges : (json_decode($f->judges, true) ?: []);
                return in_array((int) $user_id, array_map('intval', $judges), true);
            })
            ->groupBy('step_id');

        $companies = $companies->filter(function ($c) use ($screeningsByStep, $finalAssignedByStep, $user_id) {
            $stepId = (int) $c->step_id;
            if ((int) $c->step_type === 2) {
                $assignedIds = ($screeningsByStep->get($stepId) ?? collect())
                    ->pluck('users')
                    ->map(fn ($u) => is_array($u) ? $u : (json_decode($u, true) ?: []))
                    ->flatten()->map(fn ($i) => (int) $i)->all();
                return in_array((int) $c->company_id, $assignedIds, true);
            }
            if ((int) $c->step_type === 5) {
                $assignedCompanyIds = ($finalAssignedByStep->get($stepId) ?? collect())
                    ->pluck('company_id')->map(fn ($i) => (int) $i)->all();
                return in_array((int) $c->company_id, $assignedCompanyIds, true);
            }
            return false;
        })->values();

        $l = app()->getLocale();
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

        $evaluations = collect();
        if( !empty( $steps_ids ) ) { $evaluations = ScreeningEvaluation::getEvaluationData( $steps_ids ); }
        $evaluation_ids = $evaluations->pluck('step_id', 'id')->toArray();

        $myResponses = ScreeningEvaluationResults::whereIn('evaluation_id', array_keys( $evaluation_ids ))->where('user_id', $user_id)->get();
        $myResponses = $myResponses->map( function( $response ) use ( $evaluation_ids ) {
            $response->step_id = $evaluation_ids[$response->evaluation_id] ?? null;
            return $response;
        });
        $companies = $companies->map(function( $company ) use ( $myResponses ) {
            $step_id = $company->step->id;
            $status = 'not-started';
            if( $myResponses->where('step_id', $step_id)->where('company_id', $company->id)->count() > 0 ) { $status = 'in-progress'; }
            if( $myResponses->where('step_id', $step_id)->where('company_id', $company->id)->where('status', true)->count() > 0 ) { $status = 'completed'; }
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

        // Show EVERY assigned startup regardless of the evaluation form state.
        // A startup is "evaluatable" only when its evaluation form is published
        // AND has at least one criterion configured. When it is not evaluatable
        // (no evaluation record, still draft, or zero criteria) we keep the
        // startup in the list but swap the gavel for a warning icon, which opens
        // a popup explaining that evaluation cannot be performed yet. (GV-3786)
        $companies = $companies->map(function( $company ) {
            $step_id = $company->step->id;
            $evaluation = ScreeningEvaluation::getEvaluationData( $step_id );
            $company->evaluation = $evaluation;

            $status = trim( strtolower( $evaluation?->status ?? 'draft' ) );
            $criteriaCount = $evaluation?->categories ? $evaluation->categories->count() : 0;
            $company->evaluatable = ( $status == 'published' && $criteriaCount > 0 );

            if( ! $company->evaluatable ) { $company->icon = 'ti ti-alert-triangle'; }

            return $company;
        })->values();

        $statusCounts = $companies->groupBy('status')->map->count();
        $statusData = [
            (object) ['status' => 'Startups', 'count' => $companies->count()],
            (object) ['status' => 'NotStarted', 'count' => $statusCounts['not-started'] ?? 0],
            (object) ['status' => 'In_Progress', 'count' => $statusCounts['in-progress'] ?? 0],
            (object) ['status' => 'Completed', 'count' => $statusCounts['completed'] ?? 0],
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

        $successMsg = $type === 'publish' ? __('admin.Evaluation published successfully') : __('admin.Evaluation saved successfully');
        if( $type == $request->type )
        {
            $company = Company::find( $request->program );
            $form = Company::form( $company );
            $evaluationForm = ScreeningEvaluation::getEvaluationData( $company->step );
            if( $evaluationForm->id == $request->id )
            {
                $evaluation_id = $evaluationForm->id;
                if( $evaluationForm->status == 'Published' )
                {
                    $toCreate = [];
                    $commentToCreate = [];
                    $myComments = collect([]);
                    $myResponses = ScreeningEvaluationResults::where('company_id', $company->id)->where('evaluation_id', $evaluation_id)->where('user_id', $user_id)->get();
                    if( !empty( $form ) )
                    {
                        $formData = $form->formData;
                        if( $formData->count() > 0 )
                        {
                            $ids = $formData->pluck('id')->toArray();
                            $myComments = FormDataJudgesComments::where('user_id', $user_id)->whereIn('form_data_id', $ids)->get();
                        }
                    }
                    $have_published = $myResponses->where('status', true);
                    if( $have_published->count() > 0 ) { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'You have already published an evaluation form'], 200); }
                    else
                    {
                        $final_comment = $request->final_comment;
                        $is_approved = $request->has('is_approved') ? (
                            intval( $request->input('is_approved', '0') ) == 1 ? true : ( ( $request->input('is_approved', '0') === null || trim($request->input('is_approved', '0')) === '' ) ? null : false )
                        ) : null;
                        $myComment = ScreeningEvaluation::getMyFinalComment( $evaluation_id, $user_id, $company->id );
                        $myComment->value = $final_comment;
                        $myComment->company_id = $company->id;
                        $myComment->is_approved = $is_approved;
                        $myComment->save();

                        $comments = collect( $request->comments );
                        foreach( $comments as $oneComment )
                        {
                            $checkCommentResponse = $myComments->where('form_data_id', $oneComment['id'])->where('user_id', $user_id)->first();
                            if( empty( $checkCommentResponse ) )
                            {
                                $commentToCreate[] = [
                                    'form_data_id' => $oneComment['id'],
                                    'user_id' => $user_id,
                                    'comment' => $oneComment['comment'],
                                ];
                            }
                            else
                            {
                                $checkCommentResponse->form_data_id = $oneComment['id'];
                                $checkCommentResponse->comment = $oneComment['comment'];
                                $checkCommentResponse->save();
                            }
                        }
                        $evaluations = collect( $request->evaluation );
                        foreach( $evaluations as $element )
                        {
                            $checkResponse = $myResponses->where('criteria_id', $element['id'])->where('user_id', $user_id)->first();
                            if( empty( $checkResponse ) )
                            {
                                $toCreate[] = [
                                    'evaluation_id' => $evaluation_id,
                                    'criteria_id' => $element['id'],
                                    'user_id' => $user_id,
                                    'company_id' => $company->id,
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
                        if( count( $toCreate ) ) { ScreeningEvaluationResults::insert( $toCreate ); }
                        if( count( $commentToCreate ) ) { FormDataJudgesComments::insert( $commentToCreate ); }
                        return response()->json(['status' => true, 'title' => __('admin.success'), 'message' => $successMsg], 200);
                    }
                } else { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'Evaluation form is not published'], 200); }
            } else { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'Unsupported evaluation form detected'], 200); }
        } else { return response()->json(['status' => false, 'title' => 'Error', 'message' => 'Unsupported evaluation type'], 200); }
    }
}
