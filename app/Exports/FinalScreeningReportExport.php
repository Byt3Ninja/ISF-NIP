<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FinalScreeningReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $judges;
    protected $exportTimestamp;

    public function __construct($data, $judges)
    {
        $this->data = $data;
        $this->judges = $judges;
        $this->exportTimestamp = Carbon::now()->format('Y-m-d H:i:s');
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        $headers = [
            'Export Timestamp',
            'Startup Name',
            'Startup User',
            'Program Title',
            'Step Name',
            'Average Score (%)',
            'Judges Names',
            'Evaluation Status'
        ];

        // Add individual judge score columns
        foreach ($this->judges as $judge) {
            $headers[] = $judge->name . ' Score (%)';
        }

        return $headers;
    }

    public function map($startup): array
    {
        $row = [
            $this->exportTimestamp,
            $startup->name ?? 'N/A',
            $startup->user->name ?? 'N/A',
            $startup->stepdata->cycle->title ?? ($startup->cycledata->title ?? 'N/A'),
            $startup->stepdata->title ?? 'N/A',
            $startup->average_score ?? 0,
            $startup->judges_names ?? 'N/A',
            $startup->evaluation_status ?? 'Not Evaluated'
        ];

        // Add individual judge scores
        foreach ($this->judges as $judge) {
            $judgeScore = 'N/A';
            if (isset($startup->judge_scores) && isset($startup->judge_scores[$judge->id])) {
                $judgeScore = $startup->judge_scores[$judge->id];
            }
            $row[] = $judgeScore;
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Final Screening Report';
    }
}