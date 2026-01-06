<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use stdClass;

class SubmissionDetailsExport implements WithTitle, WithStyles, WithEvents, WithHeadings, FromCollection, WithCustomStartCell
{
	protected $labels;
	protected $judges;
	protected $submissions;
	protected $start_row = 4;
	protected $merges = [];

	public function __construct( $submissions )
	{
		$this->submissions = $submissions;
	}

	public function collection()
	{
		$rows = [];
		$merges = [];
		$heads = $this->headings();

		$starting_index = $this->start_row + 2;
		foreach( $this->submissions as $submission )
		{
			$merge = ['from' => $starting_index, 'to' => $starting_index];
			foreach( $submission->answers as $index => $answer )
			{
				$row = [];
				if( $index == 0 )
				{
					$row[] = $submission->submitted_by;
					$row[] = $submission->submissionDate;
					$row[] = $submission->step_name;
					$row[] = $submission->program_name;
					$row[] = $submission->organization_name;
				}
				else
				{
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = '';
					$row[] = '';
				}
				$row[] = trim( $answer->label );
				$row[] = is_array($answer->value) ? ( $answer->value['file_url'] ?? '' ) : $answer->value;
				foreach( $this->judges as $index => $judge )
				{
					$getComment = $answer->comments->where('user_id', $judge->id)->first();
					$row[] = $getComment ? $getComment->comment : '';
				}
				$rows[] = $row;
				$merge['to'] = $starting_index++;
			}
			$merges[] = $merge;
		}
		$this->merges = $merges;

		return collect($rows);
	}

	public function headings(): array
	{
		$headers = [];
		$header_1 = [
			'Submitted By',
			'Submitted At',
			'Step Name',
			'Program Name',
			'Organization Name',
			'Submissions',
			'',
		];
		$header_2 = [
			'',
			'',
			'',
			'',
			'',
			'Question',
			'Answer',
		];

		$submissions = $this->submissions;
		$this->judges = $submissions->flatMap(function ($submission) {
			$comments = $submission->answers->flatMap(function($entry) {
				return $entry->comments->map(function($c) {
					$judge = new stdClass;
					$judge->id = $c->user_id;
					$judge->name = $c->name;
					return $judge;
				});
			})->unique('id')->values();
			return $comments->flatten(1);
		})->unique('id')->values();

		$this->labels = $submissions->flatMap(function ($submission) {
			return collect($submission->answers)->flatten(1)->pluck('label')->map(function($label) { return trim($label); });
		})->unique()->values()->toArray();

		if( !$this->judges->isEmpty() )
		{
			foreach( $this->judges as $index => $judge )
			{
				if( $index == 0 ) { $header_1[] = 'Judges Comments'; }
				else { $header_1[] = ''; }
				$header_2[] = $judge->name;
			}
		}
		$headers[] = $header_1;
		$headers[] = $header_2;

		return $headers;
	}

	public function title(): string
	{
		return 'Form Submissions Report';
	}

	public function styles(Worksheet $sheet)
	{
		$next_row = $this->start_row + 1;
		$highestColumn = $sheet->getHighestColumn();
		$highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
		for( $columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++ )
		{
			$column = Coordinate::stringFromColumnIndex($columnIndex);
			$sheet->getColumnDimension($column)->setAutoSize(true);
			$sheet->getStyle($column)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		}

		$sheet->mergeCells('A' . $this->start_row . ':A' . $this->start_row + 1);
		$sheet->mergeCells('B' . $this->start_row . ':B' . $this->start_row + 1);
		$sheet->mergeCells('C' . $this->start_row . ':C' . $this->start_row + 1);
		$sheet->mergeCells('D' . $this->start_row . ':D' . $this->start_row + 1);
		$sheet->mergeCells('E' . $this->start_row . ':E' . $this->start_row + 1);
		$sheet->mergeCells('F' . $this->start_row . ':G' . $this->start_row);
		if( !$this->judges->isEmpty() )
		{
			$end = $sheet->getHighestColumn();
			$sheet->mergeCells('H' . $this->start_row . ':' . $end . $this->start_row);
		}

		$styles = [
			"2" => [
				'font' => ['bold' => true, 'size' => 15], 
				'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
			],
			"$this->start_row" => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]],
			"$next_row" => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]],
		];

		return $styles;
	}

	public function startCell(): string
	{
		return 'A' . $this->start_row;
	}

	public function registerEvents(): array
	{
		return [
			AfterSheet::class => function(AfterSheet $event) {
				$startCol = 'A';
				$sheet = $event->sheet->getDelegate();
				$highestColumn = $sheet->getHighestColumn();

				$sheet->setCellValue('A2', "Form Submission Details");
				$sheet->mergeCells('A2:' . $highestColumn . '2');
				$sheet->getRowDimension(2)->setRowHeight(25);

				foreach( $this->merges as $merge )
				{
					$sheet->mergeCells('A' . $merge['from'] . ':A' . $merge['to']);
					$sheet->mergeCells('B' . $merge['from'] . ':B' . $merge['to']);
					$sheet->mergeCells('C' . $merge['from'] . ':C' . $merge['to']);
					$sheet->mergeCells('D' . $merge['from'] . ':D' . $merge['to']);
					$sheet->mergeCells('E' . $merge['from'] . ':E' . $merge['to']);
				}

				$styleArray = [
					'font' => ['bold' => false, 'size' => 11],
					'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
				];
				$max = $sheet->getHighestRow();
				$sheet->getStyle('F6' . ':' . $highestColumn . $max)->applyFromArray($styleArray);
				$sheet->getStyle('A6' . ':E' . $max)->applyFromArray([
					'font' => ['bold' => false, 'size' => 11],
					'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
				]);
			}
		];
	}
}