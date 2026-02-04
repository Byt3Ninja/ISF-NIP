<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ScreeningEvaluationExport implements FromArray, WithEvents, WithStyles, ShouldAutoSize, WithTitle
{
    protected $evaluationsData;
    protected $companies;
    protected $criteria;
    protected $type;
    protected $includeCompanyColumn;
    protected $mergeRanges = [];
    protected $companyRowRanges = [];
    protected $totalColumns = 0;
    protected $columnMap = [];

    public function __construct($evaluationsData, $companies, $criteria, $type = 'screening', $includeCompanyColumn = true)
    {
        $this->evaluationsData = $evaluationsData;
        $this->companies = $companies;
        $this->criteria = $criteria;
        $this->type = $type;
        $this->includeCompanyColumn = $includeCompanyColumn;
    }

    public function array(): array
    {
        $rows = [];

        // Build 3 header rows
        $rows[] = $this->buildHeaderRow1();
        $rows[] = $this->buildHeaderRow2();
        $rows[] = $this->buildHeaderRow3();

        // Add data rows grouped by company
        $currentRow = 4; // Data starts at row 4

        foreach ($this->companies as $company) {
            $companyData = $this->evaluationsData[$company->id] ?? null;
            if (!$companyData) continue;

            $judges = $companyData['judges'];
            $startRow = $currentRow;
            $judgeCount = is_array($judges) ? count($judges) : $judges->count();

            foreach ($judges as $index => $judge) {
                $row = $this->buildJudgeRow($judge, $companyData, $company, $index === 0);
                $rows[] = $row;
                $currentRow++;
            }

            // Track company merge range (if multiple judges and company column included)
            if ($this->includeCompanyColumn && $judgeCount > 1) {
                $endRow = $currentRow - 1;
                $this->companyRowRanges[] = "A{$startRow}:A{$endRow}";
            }
        }

        return $rows;
    }

    private function buildHeaderRow1(): array
    {
        $row = [];
        $colIndex = 1;

        // Company column (if included) - will show "Company" merged across rows 1-3
        if ($this->includeCompanyColumn) {
            $row[] = 'Company';
            $colIndex++;
        }

        // Judges column - will show "Judges" merged across rows 1-3
        $row[] = 'Judges';
        $colIndex++;

        // Main criteria headers
        foreach ($this->criteria as $criterion) {
            $subCriteriaCount = is_array($criterion->details) ? count($criterion->details) : $criterion->details->count();
            $columnsForCriteria = ($subCriteriaCount * 3) + 1; // 3 cols per sub-criteria + 1 total

            $row[] = $criterion->name;
            $startCol = Coordinate::stringFromColumnIndex($colIndex);
            $endCol = Coordinate::stringFromColumnIndex($colIndex + $columnsForCriteria - 1);
            $this->mergeRanges[] = "{$startCol}1:{$endCol}1";

            // Fill remaining cells for this criteria group with empty strings
            for ($i = 1; $i < $columnsForCriteria; $i++) {
                $row[] = '';
            }
            $colIndex += $columnsForCriteria;
        }

        // Summary header (evaluation comment + score + Final Comments + Decision = 4 columns)
        // Removed "weighted" column from evaluation
        $row[] = 'summary';
        $startCol = Coordinate::stringFromColumnIndex($colIndex);
        $endCol = Coordinate::stringFromColumnIndex($colIndex + 3);
        $this->mergeRanges[] = "{$startCol}1:{$endCol}1";
        $row[] = '';
        $row[] = '';
        $row[] = '';

        $this->totalColumns = $colIndex + 3;

        return $row;
    }

    private function buildHeaderRow2(): array
    {
        $row = [];
        $colIndex = 1;

        // Company column header (empty - merged with row 1)
        if ($this->includeCompanyColumn) {
            $row[] = '';
            $colIndex++;
        }

        // Judges column header (empty - merged with row 1)
        $row[] = '';
        $colIndex++;

        // Sub-criteria names and totals
        foreach ($this->criteria as $criterion) {
            foreach ($criterion->details as $subCriterion) {
                $row[] = $subCriterion->name;
                $startCol = Coordinate::stringFromColumnIndex($colIndex);
                $endCol = Coordinate::stringFromColumnIndex($colIndex + 2);
                $this->mergeRanges[] = "{$startCol}2:{$endCol}2";
                $row[] = '';
                $row[] = '';
                $colIndex += 3;
            }
            // Total column for main criteria
            $row[] = 'total';
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $this->mergeRanges[] = "{$col}2:{$col}3"; // Merge total vertically
            $colIndex++;
        }

        // Summary section row 2 (evaluation comment + score only, no weighted)
        $row[] = 'evaluation';
        $col = Coordinate::stringFromColumnIndex($colIndex);
        $endCol = Coordinate::stringFromColumnIndex($colIndex + 1);
        $this->mergeRanges[] = "{$col}2:{$endCol}2"; // Merge evaluation across 2 cols (comment + score)
        $row[] = '';
        $colIndex += 2;

        $row[] = 'Final Comments';
        $col = Coordinate::stringFromColumnIndex($colIndex);
        $this->mergeRanges[] = "{$col}2:{$col}3"; // Merge vertically
        $colIndex++;

        $row[] = 'Decision';
        $col = Coordinate::stringFromColumnIndex($colIndex);
        $this->mergeRanges[] = "{$col}2:{$col}3"; // Merge vertically

        return $row;
    }

    private function buildHeaderRow3(): array
    {
        $row = [];

        // Company column (empty in row 3 - merged with rows 1-2)
        if ($this->includeCompanyColumn) {
            $row[] = '';
        }

        // Judges column (empty in row 3 - merged with rows 1-2)
        $row[] = '';

        // comment/score/weighted for each sub-criteria, empty for totals
        foreach ($this->criteria as $criterion) {
            foreach ($criterion->details as $subCriterion) {
                $row[] = 'comment';
                $row[] = 'score';
                $row[] = 'weighted';
            }
            // Total column (merged with row 2, so empty here)
            $row[] = '';
        }

        // Summary section row 3 (comment + score only, no weighted)
        $row[] = 'comment';
        $row[] = 'score';
        $row[] = ''; // Final Comments (merged with row 2)
        $row[] = ''; // Decision (merged with row 2)

        return $row;
    }

    private function buildJudgeRow($judge, $companyData, $company, $isFirstJudge): array
    {
        $row = [];

        // Company column (only for all-companies export)
        if ($this->includeCompanyColumn) {
            $row[] = $isFirstJudge ? $company->name : '';
        }

        // Judge name
        $row[] = $judge->name ?? '';

        // Criteria data
        foreach ($companyData['criteria'] as $criterion) {
            foreach ($criterion->details as $subCriterion) {
                $judgeEval = null;
                $details = is_array($subCriterion->details) ? $subCriterion->details : $subCriterion->details->toArray();

                foreach ($details as $detail) {
                    $judgerId = is_object($detail)
                        ? ($detail->judger->id ?? null)
                        : ($detail['judger']['id'] ?? null);

                    if ($judgerId == $judge->id) {
                        $judgeEval = (object) $detail;
                        break;
                    }
                }

                $row[] = $judgeEval->comment ?? '';
                $row[] = $judgeEval->given_score ?? '';
                $row[] = $judgeEval ? round(($judgeEval->value ?? 0) * 100, 2) : '';
            }
            // Main criteria total
            $row[] = round(($criterion->value ?? 0) * 100, 2);
        }

        // Summary section (comment + score only, no weighted)
        $evaluation = is_object($judge->evaluation) ? $judge->evaluation : (object) $judge->evaluation;
        $row[] = $evaluation->comment ?? '';
        $row[] = round(($evaluation->value ?? 0) * 100, 2);
        $row[] = $evaluation->comment ?? ''; // Final Comments
        $row[] = ucfirst($evaluation->approval ?? 'pending'); // Decision

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Auto-size all columns
        for ($columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            // Header row 1 styling
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Header row 2 styling
            2 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Header row 3 styling
            3 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge Company column (A1:A3) if included
                if ($this->includeCompanyColumn) {
                    try {
                        $sheet->mergeCells('A1:A3');
                        // Apply same color as main criteria (#4472C4)
                        $sheet->getStyle('A1:A3')->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ]
                        ]);

                        // Merge Judges column (B1:B3)
                        $sheet->mergeCells('B1:B3');
                        // Apply same color as main criteria (#4472C4)
                        $sheet->getStyle('B1:B3')->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ]
                        ]);
                    } catch (\Exception $e) {
                        // Skip if merge fails
                    }
                } else {
                    // Single company export - merge Judges column (A1:A3)
                    try {
                        $sheet->mergeCells('A1:A3');
                        // Apply same color as main criteria (#4472C4)
                        $sheet->getStyle('A1:A3')->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ]
                        ]);
                    } catch (\Exception $e) {
                        // Skip if merge fails
                    }
                }

                // Apply other header merges
                foreach ($this->mergeRanges as $range) {
                    try {
                        $sheet->mergeCells($range);
                    } catch (\Exception $e) {
                        // Skip invalid merge ranges
                    }
                }

                // Apply company name vertical merges in data rows
                foreach ($this->companyRowRanges as $range) {
                    try {
                        $sheet->mergeCells($range);
                        // Center align merged company cells
                        $sheet->getStyle($range)->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    } catch (\Exception $e) {
                        // Skip invalid merge ranges
                    }
                }

                // Apply borders to all cells
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Center numeric columns (score, weighted, total, evaluation score)
                $numericColumns = $this->getNumericColumnIndices();
                foreach ($numericColumns as $colIndex) {
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getStyle("{$col}4:{$col}{$highestRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Set row heights for headers
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);
            }
        ];
    }

    /**
     * Get column indices that contain numeric values (score, weighted, total)
     */
    private function getNumericColumnIndices(): array
    {
        $numericColumns = [];
        $colIndex = 1;

        // Skip Company column if included
        if ($this->includeCompanyColumn) {
            $colIndex++;
        }

        // Skip Judges column
        $colIndex++;

        // For each criteria, track score, weighted, and total columns
        foreach ($this->criteria as $criterion) {
            foreach ($criterion->details as $subCriterion) {
                $colIndex++; // comment (skip)
                $numericColumns[] = $colIndex++; // score (numeric)
                $numericColumns[] = $colIndex++; // weighted (numeric)
            }
            $numericColumns[] = $colIndex++; // total (numeric)
        }

        // Summary section: evaluation score is numeric
        $colIndex++; // comment (skip)
        $numericColumns[] = $colIndex++; // score (numeric)
        // Final Comments and Decision are not numeric

        return $numericColumns;
    }

    public function title(): string
    {
        return $this->type === 'final' ? 'Final Screening Results' : 'Screening Results';
    }
}
