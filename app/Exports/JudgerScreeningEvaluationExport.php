<?php

namespace App\Exports;

use App\Services\JudgerScreeningReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Judge-scoped screening evaluation workbook. (GV-3340)
 *
 * The criteria hierarchy belongs to a step's evaluation form, so a selection
 * spanning several steps gets one sheet per step rather than one flat table.
 */
class JudgerScreeningEvaluationExport implements WithMultipleSheets
{
    use Exportable;

    protected $groups;
    protected $judgeName;

    public function __construct( Collection $groups, $judgeName = '' )
    {
        $this->groups = $groups;
        $this->judgeName = $judgeName;
    }

    public function sheets(): array
    {
        $sheets = [];
        $used = [];

        foreach( $this->groups as $index => $group )
        {
            // Excel sheet titles are capped at 31 chars and must be unique.
            $title = preg_replace('/[\\\\\\/\\*\\?\\:\\[\\]]/', ' ', (string) ( $group->step->name ?? 'Step' ) );
            $title = trim( mb_substr( $title, 0, 28 ) ) ?: 'Step';
            $candidate = $title;
            $suffix = 2;
            while( in_array( $candidate, $used, true ) )
            {
                $candidate = mb_substr( $title, 0, 26 ) . ' ' . $suffix;
                $suffix++;
            }
            $used[] = $candidate;

            $sheets[] = new JudgerScreeningEvaluationSheet( $group, $this->judgeName, $candidate );
        }

        return $sheets;
    }
}
