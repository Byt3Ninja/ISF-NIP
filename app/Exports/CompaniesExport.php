<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CompaniesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['Company Name', 'Logo', 'Sector', 'Stage', 'Organization', 'Program(s)', 'Exported At'];
    }

    public function map($row): array
    {
        return [
            $row['Company Name'] ?? '',
            $row['Logo'] ?? '',
            $row['Sector'] ?? '',
            $row['Stage'] ?? '',
            $row['Organization'] ?? '',
            $row['Program(s)'] ?? '',
            now()->format('d-m-Y H:i'),
        ];
    }
}
