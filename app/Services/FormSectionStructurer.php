<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Groups a flat collection of form-field rows (Formfield/BuilderField, both of
 * which store per-question `options` JSON with a `section` title and a
 * `logic` map/string) into ordered sections, and simulates the same
 * "continue / section-N / submit" traversal the wizard UI performs so the
 * server can independently recompute which sections a submission actually
 * visited instead of trusting client-supplied state.
 */
class FormSectionStructurer
{
    /**
     * Which section a question belongs to. The builder stores the section's
     * position (`section_index`, GV-10420), so two sections sharing a title
     * stay apart; rows saved before that only carry the title.
     *
     * @param array|object $options a question's options JSON
     */
    public static function sectionKey($options): string
    {
        $options = (array) $options;

        return array_key_exists('section_index', $options)
            ? 'index:' . (int) $options['section_index']
            : 'title:' . trim((string) ($options['section'] ?? ''));
    }

    /**
     * The section's "after this section" target, stored on each of its
     * questions as `section_logic` (GV-10420). Older rows never had it saved;
     * for them keep reading the first question's logic, as before.
     *
     * @param array|object $options a question's options JSON
     */
    public static function sectionLogic($options): string
    {
        $options = (array) $options;

        if (array_key_exists('section_logic', $options)) {
            return static::normalizeLogic($options['section_logic']);
        }

        $logic = $options['logic'] ?? 'continue';
        if (is_object($logic)) {
            $logic = (array) $logic;
        }

        return static::normalizeLogic(is_array($logic) ? ($logic['section'] ?? 'continue') : $logic);
    }

    public static function structure(Collection $fields): array
    {
        $sections = [];
        $sectionIndexByKey = [];

        foreach ($fields as $field) {
            $options = $field->options;
            $key = static::sectionKey($options);

            if (!array_key_exists($key, $sectionIndexByKey)) {
                $sectionIndexByKey[$key] = count($sections);
                $sections[] = [
                    'index' => count($sections),
                    'title' => trim((string) ($options->section ?? '')),
                    'description' => $options->description ?? '',
                    'logic' => static::sectionLogic($options),
                    'questions' => [],
                ];
            }

            $sectionIndex = $sectionIndexByKey[$key];

            $optionValues = form_field_options($options->values ?? null);

            $logic = $options->logic ?? 'continue';
            if (is_object($logic)) {
                $logic = (array) $logic;
            }

            $sections[$sectionIndex]['questions'][] = [
                'id' => $field->id,
                'field_name' => 'field_' . $field->id,
                'label' => $options->label ?? '',
                'type' => $options->type ?? 'short_answer',
                'options' => $optionValues,
                'raw_options' => $options,
                'is_required' => isset($options->validation->required) && $options->validation->required == 1,
                'logic' => $logic,
            ];
        }

        return $sections;
    }

    /**
     * @param array $sections output of structure()
     * @param callable $answerResolver fn(string $fieldName): mixed — returns the
     *   submitted answer for a field name, or null if absent.
     * @return int[] ordered list of visited section indexes
     */
    public static function resolveVisitedPath(array $sections, callable $answerResolver): array
    {
        if (empty($sections)) {
            return [];
        }

        $history = [0];
        $current = 0;
        $guard = count($sections) + 1;

        while ($guard-- > 0) {
            if (!isset($sections[$current])) {
                break;
            }

            $logic = static::nextLogicForSection($sections[$current], $answerResolver);

            if ($logic === 'submit') {
                break;
            }

            $next = null;
            if (is_string($logic) && str_starts_with($logic, 'section-')) {
                $target = (int) substr($logic, strlen('section-'));
                // `!in_array(..., $history)` is the cycle guard on its own —
                // matching the wizard's visited-set in getActivePath(). An extra
                // `$target > $current` test would additionally reject a valid
                // backward jump the founder really did take, dropping that
                // section's answers server-side.
                if (!in_array($target, $history, true) && isset($sections[$target])) {
                    $next = $target;
                } elseif (isset($sections[$current + 1])) {
                    $next = $current + 1;
                }
            } elseif (isset($sections[$current + 1])) {
                $next = $current + 1;
            }

            if ($next === null) {
                break;
            }

            if (!in_array($next, $history, true)) {
                $history[] = $next;
            }
            $current = $next;
        }

        return $history;
    }

    /**
     * @param int[] $visitedSectionIndexes
     */
    public static function visitedFieldIds(array $sections, array $visitedSectionIndexes): array
    {
        $ids = [];
        foreach ($visitedSectionIndexes as $index) {
            foreach ($sections[$index]['questions'] ?? [] as $question) {
                $ids[] = $question['id'];
            }
        }

        return $ids;
    }

    private static function nextLogicForSection(array $section, callable $answerResolver): string
    {
        $logic = 'continue';

        foreach ($section['questions'] as $question) {
            if (!is_array($question['logic'])) {
                continue;
            }

            $answer = $answerResolver($question['field_name']);
            $answers = is_array($answer) ? $answer : [$answer];

            foreach ($answers as $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $optionLogic = static::normalizeLogic($question['logic'][$value] ?? 'continue');
                if ($optionLogic !== 'continue') {
                    $logic = $optionLogic;
                }
            }
        }

        if ($logic === 'continue') {
            $logic = static::normalizeLogic($section['logic'] ?? 'continue');
        }

        return $logic;
    }

    /**
     * A stored jump target is only meaningful as 'submit' or 'section-<n>'.
     * Older builder saves could persist an empty string when a target was lost
     * (GV-8351); anything unrecognised must fall through to 'continue' rather
     * than be mistaken for a jump.
     */
    public static function normalizeLogic($value): string
    {
        $logic = is_string($value) ? trim($value) : '';

        if ($logic === 'submit' || preg_match('/^section-\d+$/', $logic)) {
            return $logic;
        }

        return 'continue';
    }
}
