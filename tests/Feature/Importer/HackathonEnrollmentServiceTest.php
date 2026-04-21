<?php

namespace Tests\Feature\Importer;

use App\Company;
use App\Cycle;
use App\Organization;
use App\Services\HackathonEnrollmentService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $cycle = Cycle::create([
            'title' => 'Hack1',
            'type' => 'Hackathon',
            'start' => '2026-05-01',
            'end' => '2026-05-03',
            'organization_id' => $org->id,
        ]);

        $company = new Company();
        $company->name_en = 'Team Acme ' . uniqid();
        $company->name_ar = $company->name_en;
        $company->address_en = ' ';
        $company->address_ar = ' ';
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
