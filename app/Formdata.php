<?php

namespace App;

use stdClass;
use Illuminate\Support\Facades\{DB};
use Illuminate\Database\Eloquent\Model;
use App\Services\ProtectedFiles\ProtectedFiles;
use App\Enums\ProtectedFileSet;
use App\Services\ProtectedFiles\FileAccess;

class Formdata extends Model
{
    protected $fillable = ['field_id', 'company_id', 'user_id', 'form_id','step_id', 'question', 'answer', 'screening', 'comments'];
    protected $table ='form_data';
    protected $primaryKey = ['field_id', 'company_id'];
    public $incrementing = false;
    protected $casts = [
        'screening' => 'array',
        'comments' => 'array',
    ];

    public function field()
    {
        return $this->belongsTo(Formfield::class, 'field_id', 'id');
    }
    public function step()
    {
        return $this->belongsTo(Step::class, 'step_id', 'id');
    }


    public static function formsData( $request )
    {
        $data = [];
        $user = auth()->user();
        $l = app()->getLocale();
        $forms = DB::table('form_data as FD')
        ->select([
            'FD.company_id as FD_company_id',
            'FD.user_id as FD_user_id',
            'FD.step_id as FD_step_id',
            'S.data as S_form_id',
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
        ->when(!$user->hasRole('admin') && !empty($user->organizations_ids), function ($query) use ($user) {
            return $query->whereIn('CYC.organization_id', $user->organizations_ids);
        })
        ->when($request->has('organization') && trim( $request->organization ), function ($query) use ($request) {
            return $query->where('O.name_en', 'LIKE', $request->organization);
        })
        ->when($request->has('program') && trim( $request->program ), function ($query) use ($request) {
            return $query->where('CYC.title', 'LIKE', $request->program);
        })
        ->when($request->has('step') && trim( $request->step ), function ($query) use ($request) {
            return $query->where('S.title', 'LIKE', $request->step);
        })
        ->get();

        $C = 1;
        // One submission is one startup's set of answers for a step. Grouping by
        // step alone would collapse every startup that submitted the same step into
        // a single row and hide the rest.
        $grouped = $forms->groupBy(fn($row) => $row->FD_step_id . '-' . $row->FD_company_id);
        $data = $grouped->map(function ($group, $key) use ($l, &$C) {
            $first = $group->first();

            $item = new stdClass;
            $item->submission_id = $C++;
            $item->step_id = $first->FD_step_id;
            $item->form_id = $first->S_form_id;
            $item->company_id = $first->FD_company_id;
            $item->startup_name = $first->{'C_name_' . $l};
            $item->submitted_by = $group->pluck('user_name_' . $l)->unique()->implode(', ') ?? '';
            $item->step_name = $first->S_title;
            $item->program_name = $first->CYC_title;
            $item->organization_name = $first->O_name_en;
            $item->organization_id = $first->CYC_organization_id;
            $item->created_at = $first->FD_created_at;
            $item->answers = $group->map(function ($item) use ($l) {
                $value = $item->FD_answer;

                $value = self::fileAnswer($value, $item->FD_company_id) ?? $value;

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


        $data = $data->when($request->has('search') && trim( $request->search ), function ($item) use ($request) {
            return $item->filter(function ($item) use ($request) {
                return 
                    str_contains($item->submission_id, $request->search)
                    || str_contains($item->startup_name, $request->search)
                    || str_contains($item->submitted_by, $request->search)
                    || str_contains($item->step_name, $request->search)
                    || str_contains($item->program_name, $request->search)
                    || str_contains($item->organization_name, $request->search)
                    || str_contains($item->created_at, $request->search);
            })->values();
        });

        return $data;
    }

    /**
     * If a stored answer is an uploaded file, describe it for display; otherwise null.
     *
     * A file answer is a path in the application-uploads folder. (It used to be
     * "any answer containing a slash", which turned URLs and ordinary text into
     * file links.) The link is resolved through the startup's own file record, so
     * an answer that merely names somebody else's file gets a name and no link.
     */
    public static function fileAnswer($value, $companyId): ?array
    {
        if (!is_string($value) || !str_starts_with($value, 'formsubmissions/')) {
            return null;
        }
        $files = app(ProtectedFiles::class);
        if ($files->normalize(ProtectedFileSet::FORM_ANSWER, $value) === null) {
            return null;
        }
        $pin = ['company_id' => $companyId];
        $record = $files->find(ProtectedFileSet::FORM_ANSWER, $value, $pin);

        // Offer the link only to someone the file route would let through.
        $url = $record && !app(FileAccess::class)->canView(auth()->user(), $record)
            ? null
            : $files->urlFor(ProtectedFileSet::FORM_ANSWER, $value, $pin);

        return [
            'type' => 'file',
            'path' => $value,
            'file_name' => $record->original_name ?? basename($value),
            'file_url' => $url,
        ];
    }
}
