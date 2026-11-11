<?php

namespace Tests\Feature;

use App\EducationEntity;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * GV-10436 (ISF): the Founder Profile saves the identification field the Education Status asks
 * for. Student code and national ID are separate columns and a founder may hold both, so saving
 * under one status must never clear the other. The referral source belongs to the program
 * application and is not edited from the profile.
 */
class IsfFounderEducationProfileTest extends TestCase
{
    use SecurityActors;

    private User $founder;

    private EducationEntity $university;

    private EducationEntity $institute;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.localization_path' => 'isf']);
        $this->setUpSecurityActors();

        [, $this->founder] = $this->startup($this->program($this->organization()));
        $this->grant('registered', ['user.profile_update_basic']);

        $this->university = EducationEntity::create(['type' => EducationEntity::TYPE_UNIVERSITY, 'name_en' => 'GV10436 University', 'name_ar' => 'جامعة']);
        $this->institute = EducationEntity::create(['type' => EducationEntity::TYPE_INSTITUTE_DIRECTORATE, 'name_en' => 'GV10436 Institute', 'name_ar' => 'معهد']);

        $this->founder->forceFill([
            'academic_stat' => EducationEntity::STATUS_UNIVERSITY_STUDENT,
            'education_entity_id' => $this->university->id,
            'national_id' => '29001011234567',
            'student_code' => 'OLD-CODE-1',
            'referral_source' => 'linkedin',
        ])->save();
    }

    private function save(array $education): void
    {
        $countryId = DB::table('countries')->value('id');

        $this->actingAs($this->founder)->post('/dashboard/myProfile', array_merge([
            'first_name_en' => 'Isf',
            'last_name_en' => 'Founder',
            'title_en' => 'Founder',
            'phone' => '1012345678',
            'country_code' => '20',
            'email' => $this->founder->email,
            'country_id' => $countryId,
            'state_id' => DB::table('states')->where('country_id', $countryId)->value('id'),
            'address' => 'Cairo',
            'timezone' => 'Africa/Cairo',
            'dob' => '1990-01-01',
        ], $education))->assertSessionHasNoErrors();

        $this->founder->refresh();
    }

    public function test_saving_as_school_student_keeps_the_stored_national_id(): void
    {
        // The national ID input is hidden and disabled for a school student, so it isn't posted.
        $this->save([
            'academic_stat' => EducationEntity::STATUS_SCHOOL_STUDENT,
            'education_entity_id' => $this->institute->id,
            'student_code' => 'NEW-CODE-2',
        ]);

        $this->assertSame(EducationEntity::STATUS_SCHOOL_STUDENT, $this->founder->academic_stat);
        $this->assertSame($this->institute->id, (int) $this->founder->education_entity_id);
        $this->assertSame('NEW-CODE-2', $this->founder->student_code);
        $this->assertSame('29001011234567', $this->founder->national_id, 'the national ID must survive');
    }

    public function test_saving_as_university_student_keeps_the_stored_student_code(): void
    {
        $this->save([
            'academic_stat' => EducationEntity::STATUS_UNIVERSITY_STUDENT,
            'education_entity_id' => $this->university->id,
            'national_id' => '30001011234568',
        ]);

        $this->assertSame('30001011234568', $this->founder->national_id);
        $this->assertSame('OLD-CODE-1', $this->founder->student_code, 'the student code must survive');
    }

    public function test_a_posted_hidden_field_is_ignored(): void
    {
        // Only the field the status asks for is written, even if the other one is posted.
        $this->save([
            'academic_stat' => EducationEntity::STATUS_SCHOOL_STUDENT,
            'education_entity_id' => $this->institute->id,
            'student_code' => 'NEW-CODE-3',
            'national_id' => '11111111111111',
        ]);

        $this->assertSame('29001011234567', $this->founder->national_id);
    }

    public function test_the_profile_does_not_edit_the_referral_source(): void
    {
        $this->save([
            'academic_stat' => EducationEntity::STATUS_UNIVERSITY_STUDENT,
            'education_entity_id' => $this->university->id,
            'national_id' => '29001011234567',
            'referral_source' => 'facebook',
        ]);

        $this->assertSame('linkedin', $this->founder->referral_source);
    }
}
