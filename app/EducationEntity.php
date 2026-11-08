<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * GV-10436 / GV-10440 (ISF): an educational institution a founder belongs to.
 *
 * The founder's Education Status (users.academic_stat) decides which types of entity they
 * may pick, so a university is entered once and offered to both University Students and
 * Researchers. Everything that lists, validates or shows these choices reads the constants
 * below.
 */
class EducationEntity extends Model
{
    protected $table = 'education_entities';

    protected $fillable = ['type', 'name_en', 'name_ar'];

    public const TYPE_UNIVERSITY = 'university';

    public const TYPE_INSTITUTE_DIRECTORATE = 'institute_directorate';

    public const TYPE_RESEARCH_CENTER = 'research_center';

    public const TYPES = [self::TYPE_UNIVERSITY, self::TYPE_INSTITUTE_DIRECTORATE, self::TYPE_RESEARCH_CENTER];

    public const STATUS_UNIVERSITY_STUDENT = 'university_student';

    public const STATUS_SCHOOL_STUDENT = 'school_student';

    public const STATUS_RESEARCHER = 'researcher';

    // Education Status => entity types it may pick from.
    public const STATUS_TYPES = [
        self::STATUS_UNIVERSITY_STUDENT => [self::TYPE_UNIVERSITY],
        self::STATUS_SCHOOL_STUDENT => [self::TYPE_INSTITUTE_DIRECTORATE],
        self::STATUS_RESEARCHER => [self::TYPE_RESEARCH_CENTER, self::TYPE_UNIVERSITY],
    ];

    // "How did you hear about the program?" options.
    public const REFERRAL_SOURCES = [
        'linkedin', 'facebook', 'instagram', 'ambassador', 'isf_website', 'university_coordinator', 'friend',
    ];

    public static function statuses(): array
    {
        return array_keys(self::STATUS_TYPES);
    }

    /**
     * School students identify with a student code; everyone else with a national ID.
     */
    public static function identificationField(?string $status): ?string
    {
        if (! in_array($status, self::statuses(), true)) {
            return null;
        }

        return $status === self::STATUS_SCHOOL_STUDENT ? 'student_code' : 'national_id';
    }

    /**
     * Server rules for the education fields, shared by the Founder Profile and the program
     * landing-page application so the two can never disagree.
     */
    public static function validationRules(?string $status, bool $referralRequired): array
    {
        $rules = [
            'academic_stat' => ['required', Rule::in(self::statuses())],
            'education_entity_id' => [
                'required', 'integer',
                Rule::exists('education_entities', 'id')->whereIn('type', self::STATUS_TYPES[$status] ?? []),
            ],
            'referral_source' => [$referralRequired ? 'required' : 'nullable', Rule::in(self::REFERRAL_SOURCES)],
        ];

        if (self::identificationField($status) === 'student_code') {
            $rules['student_code'] = ['required', 'alpha_dash', 'max:50'];
        } else {
            $rules['national_id'] = ['required', 'digits:14']; // Egyptian national ID
        }

        return $rules;
    }

    /**
     * @param  string  $entityRequiredKey  the profile and the application word the Entity message differently
     */
    public static function validationMessages(string $entityRequiredKey): array
    {
        return [
            'academic_stat.required' => __('admin.education_status_required_msg'),
            'academic_stat.in' => __('admin.education_status_required_msg'),
            'education_entity_id.required' => __($entityRequiredKey),
            'education_entity_id.integer' => __($entityRequiredKey),
            'education_entity_id.exists' => __('admin.entity_not_for_status_msg'),
            'national_id.required' => __('admin.national_id_enter_msg'),
            'national_id.digits' => __('admin.national_id_14_digits_msg'),
            'student_code.required' => __('admin.student_code_required_msg'),
            'student_code.alpha_dash' => __('admin.student_code_invalid_msg'),
            'student_code.max' => __('admin.student_code_invalid_msg'),
            'referral_source.required' => __('admin.referral_source_required_msg'),
            'referral_source.in' => __('admin.referral_source_required_msg'),
        ];
    }

    /**
     * Everything the shared education-fields partial needs (statuses, which identification
     * field each one asks for, referral options and the Entity options per status).
     */
    public static function formConfig(): array
    {
        return [
            'statuses' => self::statuses(),
            'identification' => collect(self::statuses())->mapWithKeys(fn ($status) => [$status => self::identificationField($status)])->all(),
            'referral_sources' => self::REFERRAL_SOURCES,
            'entities' => self::optionsByStatus(),
        ];
    }

    /**
     * Every status with its entity options, for the dependent dropdowns:
     * [status => [['id' => 1, 'name' => '...'], ...]].
     */
    public static function optionsByStatus(): array
    {
        $byType = self::query()->orderBy('name_'.(app()->getLocale() === 'ar' ? 'ar' : 'en'))->get()->groupBy('type');

        return collect(self::STATUS_TYPES)->map(function ($types) use ($byType) {
            return collect($types)
                ->flatMap(fn ($type) => $byType->get($type, collect()))
                ->map(fn (self $entity) => ['id' => $entity->id, 'name' => $entity->localized_name])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        })->all();
    }

    public function getLocalizedNameAttribute(): string
    {
        return (string) (app()->getLocale() === 'ar' ? ($this->name_ar ?: $this->name_en) : ($this->name_en ?: $this->name_ar));
    }

    public function users()
    {
        return $this->hasMany(User::class, 'education_entity_id');
    }
}
