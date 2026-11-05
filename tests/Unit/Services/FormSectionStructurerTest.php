<?php

namespace Tests\Unit\Services;

use App\Services\FormSectionStructurer;
use PHPUnit\Framework\TestCase;

/**
 * GV-10420: a section is identified by its stored position, not its title, and
 * its "After this section" target is read from `section_logic`. Rows saved
 * before that keep the old title grouping and first-question logic.
 */
class FormSectionStructurerTest extends TestCase
{
    private function field(int $id, array $options): object
    {
        return (object) ['id' => $id, 'options' => json_decode(json_encode($options))];
    }

    private function question(string $section, int $index, string $sectionLogic = 'continue', array $extra = []): array
    {
        return ['label' => 'Q', 'type' => 'short_answer', 'section' => $section, 'section_index' => $index, 'section_logic' => $sectionLogic, 'logic' => []] + $extra;
    }

    public function test_sections_with_the_same_title_stay_apart(): void
    {
        $sections = FormSectionStructurer::structure(collect([
            $this->field(1, $this->question('Details', 0)),
            $this->field(2, $this->question('Details', 1)),
            $this->field(3, $this->question('Details', 1)),
        ]));

        $this->assertCount(2, $sections);
        $this->assertSame([1], array_column($sections[0]['questions'], 'id'));
        $this->assertSame([2, 3], array_column($sections[1]['questions'], 'id'));
        $this->assertSame('Details', $sections[1]['title']);
    }

    public function test_section_jump_is_read_from_section_logic(): void
    {
        $sections = FormSectionStructurer::structure(collect([
            $this->field(1, $this->question('A', 0, 'section-2')),
            $this->field(2, $this->question('B', 1, 'submit')),
            $this->field(3, $this->question('C', 2)),
        ]));

        $this->assertSame(['section-2', 'submit', 'continue'], array_column($sections, 'logic'));
        // A skips B: the server-side path agrees with the wizard.
        $this->assertSame([0, 2], FormSectionStructurer::resolveVisitedPath($sections, fn () => null));
    }

    public function test_rows_saved_before_gv10420_keep_title_grouping_and_first_question_logic(): void
    {
        $sections = FormSectionStructurer::structure(collect([
            $this->field(1, ['label' => 'Q1', 'section' => 'A', 'logic' => ['section' => 'section-1']]),
            $this->field(2, ['label' => 'Q2', 'section' => 'A', 'logic' => []]),
            $this->field(3, ['label' => 'Q3', 'section' => 'B', 'logic' => 'submit']),
        ]));

        $this->assertSame(['A', 'B'], array_column($sections, 'title'));
        $this->assertSame([1, 2], array_column($sections[0]['questions'], 'id'));
        $this->assertSame(['section-1', 'submit'], array_column($sections, 'logic'));
    }

    public function test_unrecognised_section_logic_falls_back_to_continue(): void
    {
        $this->assertSame('continue', FormSectionStructurer::sectionLogic(['section_logic' => '']));
        $this->assertSame('continue', FormSectionStructurer::sectionLogic(['section_logic' => 'section-x']));
        $this->assertSame('continue', FormSectionStructurer::sectionLogic([]));
        $this->assertSame('section-3', FormSectionStructurer::sectionLogic(['section_logic' => ' section-3 ']));
    }

    public function test_section_key_accepts_arrays_and_objects(): void
    {
        $this->assertSame('index:1', FormSectionStructurer::sectionKey(['section' => 'X', 'section_index' => 1]));
        $this->assertSame('index:1', FormSectionStructurer::sectionKey((object) ['section' => 'X', 'section_index' => 1]));
        $this->assertSame('title:X', FormSectionStructurer::sectionKey((object) ['section' => ' X ']));
    }
}
