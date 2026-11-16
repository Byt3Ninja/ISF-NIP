<?php

namespace Tests\Feature\Pipeline;

use App\CompanyEnrollment;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-02, hackathon staff dashboard: a team's place in the hackathon is read from its
 * company's enrollment there, wherever the company's single pointer happens to be.
 */
class HackathonEnrollmentReadsTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private int $hackathon;
    private int $hackathonStep;
    private int $otherProgram;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();

        $organization = $this->organization();
        $this->hackathon = $this->program($organization, ['type' => 'Hackathon']);
        $this->hackathonStep = $this->step($this->hackathon, ['title' => 'Hack Build Step', 'stype' => 6]);
        $this->otherProgram = $this->program($organization);
    }

    /** A team whose leader's company points at $pointerCycle. @return array{0:int,1:int} team id, company id */
    private function team(?int $pointerCycle): array
    {
        [$companyId, $leader] = $this->startup($pointerCycle ?? $this->otherProgram);
        if ($pointerCycle === null) {
            DB::table('companies')->where('id', $companyId)->update(['cycle' => null]);
            DB::table('company_enrollments')->where('company_id', $companyId)->delete();
        }

        $teamId = DB::table('team_formations')->insertGetId([
            'name' => 'Team ' . $companyId, 'cycle_id' => $this->hackathon, 'step_id' => $this->hackathonStep,
            'user_id' => $leader->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('team_members')->insert([
            'TeamMember' => 'hackathon', 'Name' => 'Leader', 'Company_id' => $companyId,
            'Email' => $leader->email, 'user_id' => $leader->id, 'acceptInvition' => 'accepted',
            'isTeamLeader' => 1, 'team_formation_id' => $teamId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$teamId, $companyId];
    }

    private function enroll(int $companyId, string $status = CompanyEnrollment::STATUS_ACTIVE): void
    {
        $enrollment = new CompanyEnrollment(['step_id' => $this->hackathonStep, 'status' => $status, 'joined_at' => now()]);
        $enrollment->company_id = $companyId;
        $enrollment->cycle_id = $this->hackathon;
        $enrollment->save();
    }

    public function test_team_states_come_from_the_enrollment_in_this_hackathon(): void
    {
        // Pointer on another program, active in the hackathon as well.
        [$inBoth, $inBothCompany] = $this->team($this->otherProgram);
        $this->enroll($inBothCompany);
        // Rejected from the hackathon, still active in the other program.
        [$rejected, $rejectedCompany] = $this->team($this->otherProgram);
        $this->enroll($rejectedCompany, CompanyEnrollment::STATUS_REJECTED);
        // Dismissed before enrollments were recorded: in no program, no history.
        [$legacy] = $this->team(null);
        // Never entered the hackathon, busy elsewhere.
        [$elsewhere] = $this->team($this->otherProgram);

        $response = $this->actingAs($this->actor('admin'))
            ->get(route('hackathon.viewAdminHackaton', $this->hackathon))
            ->assertOk();

        $data = $response->viewData('data');
        $states = collect($data['all_teams'])->pluck('step_state', 'id');
        $this->assertSame('step', $states[$inBoth]);
        $this->assertSame('Hack Build Step', collect($data['all_teams'])->firstWhere('id', $inBoth)->company_step);
        $this->assertSame('disapproved', $states[$rejected]);
        $this->assertSame('disapproved', $states[$legacy]);
        $this->assertSame('none', $states[$elsewhere]);
        $this->assertSame(1, $data['count_members'], 'only the founder whose company is active in the hackathon');
    }
}
