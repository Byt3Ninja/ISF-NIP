<?php

namespace Tests\Feature;

use App\EducationEntity;
use App\Exports\IsfApplicationExport;
use App\User;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use stdClass;
use Tests\TestCase;

/**
 * GV-10452 / GV-10456 / GV-10460 / GV-10464 (ISF): the Application phase export follows
 * the "Form Data Template": one row per applicant, profile details first, then each
 * question of the application form in its own column, and no step / program /
 * organization columns.
 */
class IsfApplicationExportTest extends TestCase
{
    private EducationEntity $university;

    private EducationEntity $institute;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.localization_path' => 'isf']);
        app()->setLocale('en');
        $this->university = EducationEntity::create(['type' => EducationEntity::TYPE_UNIVERSITY, 'name_en' => 'GV10452 University', 'name_ar' => 'جامعة GV10452']);
        $this->institute = EducationEntity::create(['type' => EducationEntity::TYPE_INSTITUTE_DIRECTORATE, 'name_en' => 'GV10452 Institute', 'name_ar' => 'معهد GV10452']);
    }

    private function applicant(array $attributes): User
    {
        return User::factory()->create($attributes + ['country_code' => '20']);
    }

    private function answer(int $id, User $user, string $label, $value): stdClass
    {
        $answer = new stdClass;
        $answer->id = $id;
        $answer->user_id = $user->id;
        $answer->label = $label;
        $answer->value = $value;
        $answer->created_at = '2026-11-09 10:15:00';

        return $answer;
    }

    /** A Formdata::formsData() submission: one startup's answers, grouped by submitter. */
    private function submission(array ...$answersByUser): stdClass
    {
        $submission = new stdClass;
        $submission->step_name = 'Application';
        $submission->program_name = 'Program';
        $submission->organization_name = 'Organization';
        $submission->answers = collect($answersByUser)->map(fn ($answers) => collect($answers));

        return $submission;
    }

    /** @test */
    public function it_puts_one_applicant_per_row_with_profile_columns_then_the_form_questions()
    {
        $student = $this->applicant([
            'first_name_en' => 'Reneh', 'last_name_en' => 'Hanna', 'email' => 'gv10452-a@example.test', 'phone' => '1208687009',
            'academic_stat' => EducationEntity::STATUS_UNIVERSITY_STUDENT, 'national_id' => '05056341658468', 'education_entity_id' => $this->university->id,
        ]);
        $pupil = $this->applicant([
            'first_name_en' => 'Omar', 'last_name_en' => 'Ali', 'email' => 'gv10452-b@example.test', 'phone' => '01001234567',
            'academic_stat' => EducationEntity::STATUS_SCHOOL_STUDENT, 'student_code' => 'SC-77', 'education_entity_id' => $this->institute->id,
        ]);

        // Saved out of order on purpose: columns follow the form (answer id) order.
        $export = new IsfApplicationExport(collect([
            $this->submission([
                $this->answer(12, $student, 'Why join?', 'To learn'),
                $this->answer(11, $student, 'University', 'Cairo'),
            ]),
            $this->submission([
                $this->answer(21, $pupil, 'University', 'None'),
                $this->answer(22, $pupil, 'Skills', '["Design","Speaking"]'),
            ]),
        ]));

        $this->assertSame([
            'Timestamp', 'First Name', 'Last Name', 'Email Address', 'Mobile Number', 'National ID / Student Code', 'Entity',
            'University', 'Why join?', 'Skills',
        ], $export->headings());
        foreach (['Step Name', 'Program Name', 'Organization Name', 'Submission Id', 'Submitted By'] as $removed) {
            $this->assertNotContains($removed, $export->headings());
        }

        [$first, $second] = $export->array();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $first[0]);
        $this->assertSame(['Reneh', 'Hanna', 'gv10452-a@example.test', '+20 1208687009', '05056341658468', 'GV10452 University', 'Cairo', 'To learn', ''], array_slice($first, 1));
        $this->assertSame(['Omar', 'Ali', 'gv10452-b@example.test', '+20 01001234567', 'SC-77', 'GV10452 Institute', 'None', '', 'Design, Speaking'], array_slice($second, 1));
    }

    /** @test */
    public function team_members_get_a_row_each_and_repeated_questions_keep_every_answer()
    {
        $lead = $this->applicant(['first_name_en' => 'Lead', 'email' => 'gv10452-lead@example.test']);
        $member = $this->applicant(['first_name_en' => 'Member', 'email' => 'gv10452-member@example.test']);

        $export = new IsfApplicationExport(collect([$this->submission(
            [$this->answer(1, $lead, 'Describe', 'first'), $this->answer(2, $lead, 'Describe', 'second')],
            [$this->answer(3, $member, 'Describe', 'only')],
        )]));

        $this->assertSame(['Describe', 'Describe (2)'], array_slice($export->headings(), 7));
        $rows = $export->array();
        $this->assertSame(['Lead', 'first', 'second'], [$rows[0][1], $rows[0][7], $rows[0][8]]);
        $this->assertSame(['Member', 'only', ''], [$rows[1][1], $rows[1][7], $rows[1][8]]);
    }

    /** @test */
    public function headers_follow_the_interface_language()
    {
        app()->setLocale('ar');
        $user = $this->applicant(['first_name_en' => 'Sara', 'first_name_ar' => 'سارة', 'education_entity_id' => $this->university->id]);

        $export = new IsfApplicationExport(collect([$this->submission([$this->answer(1, $user, 'سؤال', 'إجابة')])]));

        $this->assertSame('الاسم الأول', $export->headings()[1]);
        $this->assertSame('الجهة', $export->headings()[6]);
        $this->assertSame(['سارة', 'جامعة GV10452'], [$export->array()[0][1], $export->array()[0][6]]);
    }

    /** @test */
    public function files_are_links_and_ids_stay_text_in_the_spreadsheet()
    {
        $user = $this->applicant(['phone' => '01208687009', 'national_id' => '05056341658468', 'academic_stat' => EducationEntity::STATUS_RESEARCHER]);
        $file = ['type' => 'file', 'path' => 'formsubmissions/cv.pdf', 'file_name' => 'cv.pdf', 'file_url' => 'https://example.test/files/cv.pdf'];

        $export = new IsfApplicationExport(collect([$this->submission([$this->answer(1, $user, 'CV', $file)])]));
        $this->assertSame('https://example.test/files/cv.pdf', $export->array()[0][7]);

        $path = tempnam(sys_get_temp_dir(), 'isf_export_');
        try {
            file_put_contents($path, Excel::raw($export, ExcelFormat::XLSX));
            $sheet = IOFactory::load($path)->getActiveSheet();
        } finally {
            @unlink($path);
        }

        $this->assertSame('https://example.test/files/cv.pdf', $sheet->getCell('H2')->getHyperlink()->getUrl());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('F2')->getDataType());
        $this->assertSame('05056341658468', $sheet->getCell('F2')->getValue());
        $this->assertSame('A2', $sheet->getFreezePane());
    }
}
