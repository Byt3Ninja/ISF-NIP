<?php

namespace App\Exports;

use App\EducationEntity;
use App\Services\TimezoneService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * GV-10452 / GV-10456 / GV-10460 / GV-10464 (ISF): the Application phase export in the
 * "Form Data Template" layout. One row per applicant: the applicant's profile details,
 * then every question of the custom application form as its own column. No step,
 * program or organization columns.
 *
 * Takes the submissions built by Formdata::formsData(), so every ISF export button
 * (Forms > Submissions and a phase's submission details page) produces the same sheet.
 * Every cell is written as text, so national IDs and phone numbers keep their digits
 * and leading zeros.
 */
class IsfApplicationExport extends StringValueBinder implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents, WithCustomValueBinder, ShouldAutoSize
{
    private const FIXED_COLUMNS = ['timestamp', 'first_name', 'last_name', 'email', 'mobile', 'identification', 'entity'];

    private array $headings = [];

    private array $rows = [];

    // [row number => [column number => url]] for file answers, linked after the sheet is written.
    private array $links = [];

    public function __construct(Collection $submissions)
    {
        $locale = app()->getLocale();

        // One application per applicant per phase, its answers in the order they were saved
        // (the order of the form).
        $applications = $submissions->flatMap(function ($submission) {
            return collect($submission->answers)->map(fn ($answers) => collect($answers)->sortBy('id')->values());
        })->filter(fn ($answers) => $answers->isNotEmpty())->values();

        $users = User::with('educationEntity')
            ->whereIn('id', $applications->map(fn ($answers) => $answers->first()->user_id)->filter()->unique())
            ->get()
            ->keyBy('id');

        // A question is a column. Two questions with the same wording in one form get one
        // column each ("Question", "Question (2)"), so no answer overwrites another.
        $questionColumns = [];
        $answerRows = $applications->map(function ($answers) use (&$questionColumns) {
            $seen = [];
            $row = [];
            foreach ($answers as $answer) {
                $label = trim((string) $answer->label);
                $occurrence = $seen[$label] = ($seen[$label] ?? 0) + 1;
                $key = $label . "\0" . $occurrence;
                $questionColumns[$key] ??= $occurrence > 1 ? "{$label} ({$occurrence})" : $label;
                $row[$key] = $answer->value;
            }

            return $row;
        });

        $this->headings = array_merge(
            array_map(fn ($column) => __('forms.isf_export.' . $column), self::FIXED_COLUMNS),
            array_values($questionColumns)
        );

        foreach ($applications as $index => $answers) {
            $rowNumber = $index + 2;
            $user = $users->get($answers->first()->user_id);
            $row = $this->profileCells($user, $answers->min('created_at'), $locale);

            foreach (array_keys($questionColumns) as $columnIndex => $key) {
                $value = $answerRows[$index][$key] ?? '';
                if (is_array($value)) {
                    // An uploaded file: its link, or its name when the viewer may not open it.
                    if (! empty($value['file_url'])) {
                        $this->links[$rowNumber][count(self::FIXED_COLUMNS) + $columnIndex + 1] = $value['file_url'];
                    }
                    $value = $value['file_url'] ?? $value['file_name'] ?? '';
                }
                $row[] = $this->plainAnswer($value);
            }
            $this->rows[] = $row;
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Form Submissions';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');
                foreach ($this->links as $rowNumber => $columns) {
                    foreach ($columns as $columnNumber => $url) {
                        $sheet->getCell(Coordinate::stringFromColumnIndex($columnNumber) . $rowNumber)->getHyperlink()->setUrl($url);
                    }
                }
            },
        ];
    }

    private function profileCells(?User $user, $submittedAt, string $locale): array
    {
        $timestamp = $submittedAt
            ? TimezoneService::forViewer(Carbon::parse($submittedAt, TimezoneService::storage()))->format('Y-m-d H:i:s')
            : '';

        if (! $user) {
            return [$timestamp, '', '', '', '', '', ''];
        }

        $other = $locale === 'ar' ? 'en' : 'ar';
        $inLocale = fn ($field) => $user->{"{$field}_{$locale}"} ?: $user->{"{$field}_{$other}"};

        // School students identify with a student code, everyone else with a national ID.
        $identification = EducationEntity::identificationField($user->academic_stat) === 'student_code'
            ? ($user->student_code ?: $user->national_id)
            : ($user->national_id ?: $user->student_code);

        $entity = $user->educationEntity;

        return [
            $timestamp,
            $inLocale('first_name'),
            $inLocale('last_name'),
            $user->email,
            $this->mobile($user),
            $identification,
            $entity ? ($entity->{"name_{$locale}"} ?: $entity->{"name_{$other}"}) : '',
        ];
    }

    // "+20 120 147 4101": the country code is stored without its "+".
    private function mobile(User $user): string
    {
        if (! $user->phone) {
            return '';
        }
        $code = ltrim((string) $user->country_code, '+ ');

        return $code !== '' ? "+{$code} {$user->phone}" : (string) $user->phone;
    }

    /**
     * A multiple-choice answer is stored as a JSON list (or, from older forms, a comma
     * string); show it as "a, b".
     */
    private function plainAnswer($value): string
    {
        if (is_string($value) && str_starts_with(ltrim($value), '[')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item, JSON_UNESCAPED_UNICODE), $decoded));
            }
        }

        return is_scalar($value) ? (string) $value : '';
    }
}
