<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SubmissionsExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
	protected $submissions;
	protected $labels;

	public function __construct( $submissions )
	{
		$this->submissions = $submissions;
	}

	public function collection()
	{
		$rows = [];
		$heads = $this->headings();
		$submissions = $this->submissions;

		foreach( $submissions as $submission )
		{
			foreach( $submission->answers as $answerGroup )
			{
				$tempRow = [
					'Submission Id' => $submission->submission_id,
					'Organization Name' => $submission->organization_name,
					'Program Name' => $submission->program_name,
					'Step Name' => $submission->step_name,
					'Submitted By' => $answerGroup[0]->name ?? '',
					'Submitted At' => $submission->created_at,
				];

				foreach( $answerGroup as $answer )
				{
					$value = is_array($answer->value) ? ( $answer->value['file_url'] ?? '' ) : $answer->value;
					$tempRow[$answer->label] = $value;
				}
				$orderedRow = [];
				foreach( $heads as $heading ) { $orderedRow[] = $tempRow[$heading] ?? ''; }
				$rows[] = $orderedRow;
			}
		}

		return collect($rows);
	}

	public function headings(): array
	{
		$submissions = $this->submissions;
		$this->labels = collect($submissions)->flatMap(function ($submission) {
			return collect($submission->answers)->flatten(1)->pluck('label');
		})->unique()->values()->toArray();

		$heads = [
			'Submission Id',
			'Organization Name',
			'Program Name',
			'Step Name',
			'Submitted By',
			'Submitted At',
		];

		return array_merge($heads, $this->labels);
	}

	public function title(): string
	{
		return 'Form Submissions Report';
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