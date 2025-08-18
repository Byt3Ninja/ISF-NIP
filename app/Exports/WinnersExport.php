<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class WinnersExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $winnersData;

    public function __construct($winnersData)
    {
        $this->winnersData = $winnersData;
    }

    public function collection()
    {
        $winnersData = $this->winnersData;
        foreach( $winnersData as $key => $winner )
        {
            $judges = '';
            foreach( $winner['judges'] as $judge ) { $judges .= "( $judge->score ) $judge->name , "; }
            $judges = trim( $judges, ', ' );
            $row = [
                __('admin.Project Name') => $winner['project'],
                __('admin.team_leader') => $winner['leader']?->name ?? '',
                __('admin.Email') => $winner['leader']?->email ?? '',
                __('admin.Submission Step') => $winner['step'],
                __('admin.Judges') => $judges,
                __('admin.Score') => $winner['score']
            ];
            $rows[] = $row;
        }

        return collect( $rows );
    }

    public function headings(): array
    {
        return [
            __('admin.Project Name'),
            __('admin.team_leader'),
            __('admin.Email'),
            __('admin.Submission Step'),
            __('admin.Judges'),
            __('admin.Score')
        ];
    }

    public function title(): string
    {
        return 'Winners Report';
    }

    public function styles(Worksheet $sheet)
    {
		$highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
        for( $columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++ ) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            // $sheet->getStyle($column)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

		$styles = [
			1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center', 'vertical' => 'center']],
		];

        return $styles;
    }
}