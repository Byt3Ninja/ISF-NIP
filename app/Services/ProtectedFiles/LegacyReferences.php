<?php

namespace App\Services\ProtectedFiles;

use App\Enums\ProtectedFileSet;
use Illuminate\Support\Facades\DB;

/**
 * Enumerates every place in the database that points at a confidential file,
 * set by set. Used by the audit (read-only) and by the migration, so both work
 * from exactly the same list.
 *
 * Each reference is an array:
 *   set, owner_type, owner_id, company_id, cycle_id, field, value (as stored), name (display name if known)
 */
class LegacyReferences
{
    /** @return \Generator<int, array> */
    public function each(string $set): \Generator
    {
        switch ($set) {
            case ProtectedFileSet::COMPANY_LEGAL:
                yield from $this->companyColumns($set, ['Commercial_Register', 'Tax_Card']);
                break;

            case ProtectedFileSet::COMPANY_PITCH:
                yield from $this->companyColumns($set, ['pitch_deck', 'financial_plan', 'business_plan']);
                foreach (DB::table('companies')->whereNotNull('documents')->orderBy('id')->cursor() as $company) {
                    foreach ($this->entries($company->documents) as $entry) {
                        yield $this->reference($set, 'company', $company->id, $company->id, null, 'documents', $entry);
                    }
                }
                break;

            case ProtectedFileSet::FORM_ANSWER:
                // A file answer is one given to a file-type field. Rows whose field no
                // longer exists are kept only if the value has the upload folder's prefix.
                $rows = DB::table('form_data as fd')
                    ->leftJoin('form_fields as ff', 'ff.id', '=', 'fd.field_id')
                    ->leftJoin('fields as f', 'f.id', '=', 'ff.field_id')
                    ->leftJoin('steps as s', 's.id', '=', 'fd.step_id')
                    ->whereNull('fd.deleted_at')
                    ->where(function ($q) {
                        $q->where('f.field_type', 'file')
                            ->orWhere(function ($q) {
                                $q->whereNull('f.id')->where('fd.answer', 'like', 'formsubmissions/%');
                            });
                    })
                    ->orderBy('fd.id')
                    ->select('fd.id', 'fd.company_id', 'fd.field_id', 'fd.answer', 's.cycle_id', 'fd.updated_at', 'fd.created_at');
                foreach ($rows->cursor() as $row) {
                    yield $this->reference($set, 'company', $row->company_id, $row->company_id, $row->cycle_id, 'field_' . $row->field_id, $row->answer)
                        + ['stamp' => $row->updated_at ?? $row->created_at];
                }
                break;

            case ProtectedFileSet::MILESTONE_DOCUMENT:
                foreach (DB::table('milestones')->whereNotNull('documents')->orderBy('id')->cursor() as $milestone) {
                    foreach ($this->entries($milestone->documents) as $entry) {
                        yield $this->reference($set, 'milestone', $milestone->id, $milestone->Company_id, null, 'documents', $entry);
                    }
                }
                break;

            case ProtectedFileSet::EMPLOYEE_DOCUMENT:
                foreach (DB::table('employees')->whereNotNull('documents')->orderBy('id')->cursor() as $employee) {
                    foreach ($this->entries($employee->documents) as $entry) {
                        yield $this->reference($set, 'employee', $employee->id, $employee->company_id, null, 'documents', $entry);
                    }
                }
                break;

            case ProtectedFileSet::TASK_ATTACHMENT:
                foreach (DB::table('program_tasks')->whereNotNull('attachments')->orderBy('id')->cursor() as $task) {
                    foreach ($this->entries($task->attachments) as $entry) {
                        $kind = is_array($entry) ? ($entry['status'] ?? 'attachment') : 'attachment';
                        $company = is_array($entry) && $kind === 'submission' ? ($entry['company_id'] ?? null) : null;
                        yield $this->reference($set, 'task', $task->id, $company, $task->program_id, $kind, $entry);
                    }
                }
                foreach (DB::table('program_task_assignees')->whereNotNull('attachment')->orderBy('id')->cursor() as $assignee) {
                    $cycleId = DB::table('program_tasks')->where('id', $assignee->program_task_id)->value('program_id');
                    foreach ($this->entries($assignee->attachment) as $entry) {
                        yield $this->reference($set, 'task_assignee', $assignee->id, $assignee->company_id, $cycleId, 'submission', $entry);
                    }
                }
                break;

            case ProtectedFileSet::PROGRAM_GUIDELINE:
                $columns = ['program_guideline_file', 'financial_guideline_file', 'legal_guideline_file', 'delegation_file'];
                foreach (DB::table('cycles')->orderBy('id')->cursor() as $cycle) {
                    foreach ($columns as $column) {
                        if ($cycle->{$column} !== null && $cycle->{$column} !== '') {
                            yield $this->reference($set, 'cycle', $cycle->id, null, $cycle->id, $column, $cycle->{$column});
                        }
                    }
                }
                break;
        }
    }

    protected function companyColumns(string $set, array $columns): \Generator
    {
        foreach (DB::table('companies')->orderBy('id')->cursor() as $company) {
            foreach ($columns as $column) {
                if ($company->{$column} !== null && $company->{$column} !== '') {
                    yield $this->reference($set, 'company', $company->id, $company->id, null, $column, $company->{$column});
                }
            }
        }
    }

    /**
     * Decode a JSON list column. Tolerates double-encoded values (a JSON string
     * that itself contains JSON), a single object, and plain strings.
     */
    public function entries($raw): array
    {
        $value = $raw;
        for ($i = 0; $i < 2 && is_string($value); $i++) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }
            $value = $decoded;
        }
        if (is_string($value)) {
            return $value === '' ? [] : [$value];
        }
        if (!is_array($value)) {
            return [];
        }
        // one object rather than a list of them
        if (isset($value['path']) || isset($value['url'])) {
            return [$value];
        }

        return array_values($value);
    }

    /** @param string|array $entry a path string, or {path, url, name, …} */
    protected function reference(string $set, string $ownerType, $ownerId, $companyId, $cycleId, string $field, $entry): array
    {
        $value = is_array($entry) ? ($entry['path'] ?? $entry['url'] ?? null) : $entry;

        return [
            'set' => $set,
            'owner_type' => $ownerType,
            'owner_id' => (int) $ownerId,
            'company_id' => $companyId ? (int) $companyId : null,
            'cycle_id' => $cycleId ? (int) $cycleId : null,
            'field' => $field,
            'value' => is_string($value) ? $value : null,
            'name' => is_array($entry) && isset($entry['name']) && is_string($entry['name']) ? $entry['name'] : null,
        ];
    }

    /**
     * Folders (relative to the set's old location) that hold this set's files,
     * used to find files no row points at. `only` limits by extension where a
     * folder is shared with public images.
     */
    public function legacyAreas(string $set): array
    {
        switch ($set) {
            case ProtectedFileSet::MILESTONE_DOCUMENT: return [['dir' => 'milestones']];
            case ProtectedFileSet::EMPLOYEE_DOCUMENT:  return [['dir' => 'employees'], ['dir' => 'employee_documents']];
            case ProtectedFileSet::TASK_ATTACHMENT:    return [['dir' => 'tasks/attachments']];
            case ProtectedFileSet::PROGRAM_GUIDELINE:  return [['dir' => 'programs', 'only' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt']]];
            default:                                   return [['dir' => '']];
        }
    }
}
