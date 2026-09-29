<?php

namespace App\Exports;

use App\Services\JudgerScreeningReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One step's worth of a judge's evaluations, laid out in the hierarchy of the
 * ticket's reference workbook: (GV-3340)
 *
 *   row 1: [startup columns] | Criteria title (merged) ... | Summary (merged)
 *   row 2:                   | Sub-criteria (merged x3) | total | ... | criteria total
 *   row 3:                   | comment | score | weighted |      | ...
 *
 * One data row per startup, holding only this judge's answers.
 */
class JudgerScreeningEvaluationSheet implements FromArray, WithEvents, WithStyles, ShouldAutoSize, WithTitle
{
    protected $group;
    protected $judgeName;
    protected $sheetTitle;

    protected $mergeRanges = [];
    protected $totalColumnIndices = [];
    protected $criteriaTotalIndices = [];
    protected $startupColumns = [];
    protected $lastColumnIndex = 1;

    public function __construct( $group, $judgeName, $sheetTitle )
    {
        $this->group = $group;
        $this->judgeName = $judgeName;
        $this->sheetTitle = $sheetTitle;
        $this->startupColumns = $this->buildStartupColumns();
    }

    /**
     * Startup identity columns. Team name is Hackathon-only per the ticket.
     */
    protected function buildStartupColumns(): array
    {
        $columns = [ __('admin.Startup Name') ];
        if( $this->group->is_hackathon ) { $columns[] = __('admin.Team Name'); }
        $columns[] = __('admin.Founder Name');
        $columns[] = __('admin.Status');
        return $columns;
    }

    public function array(): array
    {
        return array_merge(
            [ $this->buildHeaderRow1(), $this->buildHeaderRow2(), $this->buildHeaderRow3() ],
            $this->buildDataRows()
        );
    }

    private function buildHeaderRow1(): array
    {
        $row = [];
        $colIndex = 1;

        // Startup columns span rows 1-3 (merged in registerEvents).
        foreach( $this->startupColumns as $label )
        {
            $row[] = $label;
            $colIndex++;
        }

        foreach( $this->group->criteria as $criterion )
        {
            $subCount = count( $criterion->details );
            // 3 cols per sub-criteria (comment/score/weighted) + 1 sub total, then 1 criteria total.
            $span = ( $subCount * 4 ) + 1;

            $row[] = $criterion->name;
            $start = Coordinate::stringFromColumnIndex( $colIndex );
            $end = Coordinate::stringFromColumnIndex( $colIndex + $span - 1 );
            $this->mergeRanges[] = "{$start}1:{$end}1";

            for( $i = 1; $i < $span; $i++ ) { $row[] = ''; }
            $colIndex += $span;
        }

        // Summary: Final Comments, Total Score, Recommendation, Timestamp.
        $row[] = __('admin.Summary');
        $start = Coordinate::stringFromColumnIndex( $colIndex );
        $end = Coordinate::stringFromColumnIndex( $colIndex + 3 );
        $this->mergeRanges[] = "{$start}1:{$end}1";
        $row[] = '';
        $row[] = '';
        $row[] = '';

        $this->lastColumnIndex = $colIndex + 3;

        return $row;
    }

    private function buildHeaderRow2(): array
    {
        $row = [];
        $colIndex = 1;

        foreach( $this->startupColumns as $label )
        {
            $row[] = '';
            $colIndex++;
        }

        foreach( $this->group->criteria as $criterion )
        {
            foreach( $criterion->details as $sub )
            {
                $row[] = $sub->name;
                $start = Coordinate::stringFromColumnIndex( $colIndex );
                $end = Coordinate::stringFromColumnIndex( $colIndex + 2 );
                $this->mergeRanges[] = "{$start}2:{$end}2";
                $row[] = '';
                $row[] = '';
                $colIndex += 3;

                $row[] = __('admin.Sub Criteria Total');
                $col = Coordinate::stringFromColumnIndex( $colIndex );
                $this->mergeRanges[] = "{$col}2:{$col}3";
                $this->totalColumnIndices[] = $colIndex;
                $colIndex++;
            }

            $row[] = __('admin.Criteria Total');
            $col = Coordinate::stringFromColumnIndex( $colIndex );
            $this->mergeRanges[] = "{$col}2:{$col}3";
            $this->totalColumnIndices[] = $colIndex;
            $this->criteriaTotalIndices[] = $colIndex;
            $colIndex++;
        }

        foreach( [ __('admin.Final Comments'), __('admin.Total Score'), __('admin.Recommendation'), __('admin.Timestamp') ] as $label )
        {
            $row[] = $label;
            $col = Coordinate::stringFromColumnIndex( $colIndex );
            $this->mergeRanges[] = "{$col}2:{$col}3";
            $colIndex++;
        }

        return $row;
    }

    private function buildHeaderRow3(): array
    {
        $row = [];

        foreach( $this->startupColumns as $label ) { $row[] = ''; }

        foreach( $this->group->criteria as $criterion )
        {
            foreach( $criterion->details as $sub )
            {
                $row[] = __('admin.comment');
                $row[] = __('admin.score');
                $row[] = __('admin.weighted');
                $row[] = ''; // sub-criteria total (merged with row 2)
            }
            $row[] = ''; // criteria total (merged with row 2)
        }

        // Summary labels all live in row 2, merged down into row 3.
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';

        return $row;
    }

    private function buildDataRows(): array
    {
        $rows = [];

        foreach( $this->group->rows as $item )
        {
            $row = [ $item->startup ];
            if( $this->group->is_hackathon ) { $row[] = $item->team_name ?? ''; }
            $row[] = $item->founder;
            $row[] = __('admin.' . $item->status);

            foreach( $item->criteria as $criterion )
            {
                foreach( $criterion->details as $detail )
                {
                    $row[] = $detail->comment ?? '';
                    $row[] = $detail->answered ? $detail->score : '';
                    $row[] = $detail->answered ? $this->percent( $detail->weighted ) : '';
                    $row[] = $detail->answered ? $this->percent( $detail->total ) : '';
                }
                $row[] = $criterion->total !== null ? $this->percent( $criterion->total ) : '';
            }

            $row[] = $item->comment ?? '';
            $row[] = $this->percent( $item->total );
            $row[] = JudgerScreeningReport::recommendationLabel( $item->approval );
            $row[] = $item->timestamp ? (string) $item->timestamp : '';

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * results() stores weights as fractions of 1; the reference workbook shows
     * them as percentages.
     */
    private function percent( $value ): string
    {
        return round( floatval( $value ) * 100, 2 ) . '%';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E2F3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function( AfterSheet $event ) {
                $sheet = $event->sheet->getDelegate();

                // Startup identity columns span the three header rows.
                foreach( array_keys( $this->startupColumns ) as $i )
                {
                    $col = Coordinate::stringFromColumnIndex( $i + 1 );
                    $this->safeMerge( $sheet, "{$col}1:{$col}3" );
                    $sheet->getStyle("{$col}1:{$col}3")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }

                foreach( $this->mergeRanges as $range ) { $this->safeMerge( $sheet, $range ); }

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);

                // Wrap the free-text columns so long comments stay readable.
                $sheet->getStyle("A4:{$highestColumn}{$highestRow}")->getAlignment()
                    ->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);

                foreach( $this->criteriaTotalIndices as $colIdx )
                {
                    $col = Coordinate::stringFromColumnIndex( $colIdx );
                    $sheet->getStyle("{$col}2:{$col}3")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    ]);
                }

                // Repeat the header rows when the sheet is printed.
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);

                $sheet->freezePane('A4');
            },
        ];
    }

    private function safeMerge( Worksheet $sheet, string $range ): void
    {
        try { $sheet->mergeCells( $range ); }
        catch( \Exception $e ) { /* skip invalid merge ranges */ }
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
