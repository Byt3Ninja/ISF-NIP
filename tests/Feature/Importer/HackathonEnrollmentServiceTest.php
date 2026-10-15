<?php

namespace Tests\Feature\Importer;

use App\Company;
use App\Cycle;
use App\Organization;
use App\Services\HackathonEnrollmentService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HackathonEnrollmentServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_enroll_as_team_leader_creates_or_reuses_formation(): void
    {
        $admin = User::factory()->create();
        $admin->allotRole('Admin');
        $org = Organization::create([
            'name_en' => 'Org',
            'name_ar' => 'م',
            'user_id' => $admin->id,
        ]);
        $cycle = Cycle::forceCreate([
            'title' => 'Hack1',
            'type' => 'Hackathon',
            'start' => '2026-05-01',
            'end' => '2026-05-03',
            'organization_id' => $org->id,
        ]);

        // Hackathon cycles must have at least one step for the importer to enroll into.
        DB::table('steps')->insert([
            'cycle_id'   => $cycle->id,
            'title'      => 'Idea Submission',
            'stype'      => 0,
            'arr'        => 0,
            'is_public'  => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $company = new Company();
        $company->name_en = 'Team Acme ' . uniqid();
        $company->name_ar = $company->name_en;
        $company->save();

        $user = User::factory()->create();
        $user->company_id = $company->id;
        $user->save();

        $svc = app(HackathonEnrollmentService::class);
        $tf = $svc->enrollAsTeamLeader($cycle, $user, $company);
        $this->assertNotNull($tf->id);
        $this->assertSame($cycle->id, $tf->cycle_id);
        $this->assertSame($user->id, $tf->user_id);

        // Idempotent
        $tf2 = $svc->enrollAsTeamLeader($cycle, $user, $company);
        $this->assertSame($tf->id, $tf2->id);
    }
}
