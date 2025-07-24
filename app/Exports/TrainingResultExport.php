<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TrainingResultExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $compaines;

    public function __construct($compaines)
    {
        $this->compaines = $compaines;
    }

    public function collection()
    {
        $rows = [];
        $compaines = $this->compaines;
        foreach( $compaines as $key => $company )
        {
            $users = $company->users->pluck('name')->toArray();
            $row = [
                'ID' => $company->id,
                'Company' => $company->name,
                'Title' => $company->program->name,
                'User' => implode(', ', $users),
                'Completed' => $company->completed,
                'Current Step' => $company->step->name,
                'Time' => $company->created_at,
            ];
            $rows[] = $row;
        }
        return collect( $rows );
    }

    public function headings(): array
    {
        return [
            'ID',
            'Company',
            'Title',
            'User',
            'Completed',
            'Current Step',
            'Time',
        ];
    }

    public function title(): string
    {
        return 'Training Results Report';
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