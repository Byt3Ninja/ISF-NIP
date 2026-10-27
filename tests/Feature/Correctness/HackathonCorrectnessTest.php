<?php

namespace Tests\Feature\Correctness;

use App\Conversation;
use App\Notifications\TemplateEmail;
use App\Team_member;
use App\TeamFormation;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Correctness audit, hackathon cluster: C10, C11, C12, C24, Plausible-1 (null stype-6 step).
 */
class HackathonCorrectnessTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('registered', [
            'hackathon.createTeam', 'hackathon.acceptInvitation', 'hackathon.rejectInvitation',
            'hackathon.ApprovedRequestJoinTeam', 'messages.send_team_message',
        ]);
    }

    /** A hackathon program; with $teamSize a stype-6 (team formation) step limits team size. @return array{0:int,1:int} */
    private function hackathon(?int $teamSize): array
    {
        $cycleId = $this->program($this->organization());
        $stepId = $this->step($cycleId, ['stype' => 1]);
        if ($teamSize !== null) {
            $this->step($cycleId, ['stype' => 6, 'arr' => 2, 'count_team_number' => $teamSize]);
        }

        return [$cycleId, $stepId];
    }

    /** Hackathon participants each own a startup (team_members.Company_id is a foreign key). */
    private function participant(): User
    {
        $companyId = DB::table('companies')->insertGetId([
            'name_en' => 'Audit Participant ' . Str::random(6), 'name_ar' => 'Audit Participant',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $this->actor('registered', ['company_id' => $companyId, 'HaveCompany' => 1]);
    }

    private function team(int $cycleId, int $stepId, User $leader): int
    {
        $teamId = DB::table('team_formations')->insertGetId([
            'name' => 'Audit Team ' . Str::random(8), 'cycle_id' => $cycleId, 'step_id' => $stepId,
            'user_id' => $leader->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->member($teamId, $leader, 'accepted', 1);

        return $teamId;
    }

    private function member(int $teamId, User $user, string $status, int $leader = 0): int
    {
        return DB::table('team_members')->insertGetId([
            'TeamMember' => 'hackathon', 'Name' => 'Member', 'Company_id' => $user->company_id,
            'Email' => $user->email, 'user_id' => $user->id, 'acceptInvition' => $status,
            'isTeamLeader' => $leader, 'team_formation_id' => $teamId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function inviteStatus(int $memberId): ?string
    {
        return DB::table('team_members')->where('id', $memberId)->value('acceptInvition');
    }

    public function test_c10_declining_into_a_full_team_rejects_open_seats_instead_of_crashing(): void
    {
        [$cycle, $step] = $this->hackathon(1);
        $team = $this->team($cycle, $step, $this->participant());
        $invitee = $this->participant();
        $invite = $this->member($team, $invitee, 'pending');

        $this->actingAs($invitee)->patch("/dashboard/hackathon/rejectInvitation/{$invite}")
            ->assertOk()->assertJson(['success' => false]);

        $this->assertSame('rejected', $this->inviteStatus($invite));
    }

    public function test_c10_approving_a_join_into_a_full_team_rejects_open_requests_instead_of_crashing(): void
    {
        [$cycle, $step] = $this->hackathon(1);
        $leader = $this->participant();
        $team = $this->team($cycle, $step, $leader);
        $this->member($team, $this->participant(), 'accepted');
        $request = $this->member($team, $this->participant(), 'join');

        $this->actingAs($leader)->post('/dashboard/hackathon/ApprovedRequestJoin', ['id' => $request])
            ->assertOk()->assertJson(['success' => false]);

        $this->assertSame('rejected', $this->inviteStatus($request));
    }

    public function test_c11_team_message_email_renders_with_its_subject_and_body(): void
    {
        $sender = $this->actor('registered');
        $receiver = $this->actor('registered');

        $this->actingAs($sender)->post('/dashboard/sendteammessage', [
            'receiver' => $receiver->id, 'subject' => 'Hello', 'message' => 'Team note',
        ])->assertOk();

        Notification::assertSentTo($receiver, TemplateEmail::class, function (TemplateEmail $notification) use ($receiver) {
            $mail = $notification->toMail($receiver);

            return $mail->subject === 'New message received - VI portal'
                && str_contains(implode(' ', $mail->introLines), 'You have a new message from')
                && is_string($notification->toDatabase($receiver)['message']);
        });
    }

    public function test_c12_accepting_one_hackathon_invite_keeps_invites_to_other_hackathons(): void
    {
        [$cycleA, $stepA] = $this->hackathon(5);
        [$cycleB, $stepB] = $this->hackathon(5);
        $teamA = $this->team($cycleA, $stepA, $this->participant());
        $teamB = $this->team($cycleB, $stepB, $this->participant());
        $invitee = $this->participant();
        $inviteA = $this->member($teamA, $invitee, 'pending');
        $inviteB = $this->member($teamB, $invitee, 'pending');

        $this->actingAs($invitee)->patch("/dashboard/hackathon/acceptInvitation/{$inviteA}");

        $this->assertSame('accepted', $this->inviteStatus($inviteA));
        $this->assertSame('pending', $this->inviteStatus($inviteB));
    }

    public function test_c12_approving_a_join_keeps_join_requests_to_other_hackathons(): void
    {
        [$cycleA, $stepA] = $this->hackathon(5);
        [$cycleB, $stepB] = $this->hackathon(5);
        $leaderA = $this->participant();
        $teamA = $this->team($cycleA, $stepA, $leaderA);
        $teamB = $this->team($cycleB, $stepB, $this->participant());
        $applicant = $this->participant();
        $joinA = $this->member($teamA, $applicant, 'join');
        $joinB = $this->member($teamB, $applicant, 'join');

        $this->actingAs($leaderA)->post('/dashboard/hackathon/ApprovedRequestJoin', ['id' => $joinA]);

        $this->assertSame('accepted', $this->inviteStatus($joinA));
        $this->assertSame('join', $this->inviteStatus($joinB));
    }

    public function test_c24_create_team_leaves_nothing_behind_when_a_later_write_fails(): void
    {
        [$cycle, $step] = $this->hackathon(5);
        $founder = $this->actor('registered');
        $teamName = 'Audit Atomic ' . Str::random(8);
        Conversation::creating(function () {
            throw new RuntimeException('simulated failure while creating the team chat');
        });

        $this->actingAs($founder)->post('/dashboard/hackathon/createTeam', [
            'team_name' => $teamName, 'cycle_id' => $cycle, 'step_id' => $step,
        ]);

        $this->assertFalse(TeamFormation::where('name', $teamName)->exists());
        $this->assertFalse(Team_member::where('user_id', $founder->id)->exists());
        $this->assertNull($founder->fresh()->company_id);
    }

    public function test_c24_founder_without_a_startup_can_create_a_team(): void
    {
        [$cycle, $step] = $this->hackathon(5);
        $founder = $this->actor('registered');
        $teamName = 'Audit Fresh ' . Str::random(8);

        $this->actingAs($founder)->post('/dashboard/hackathon/createTeam', [
            'team_name' => $teamName, 'cycle_id' => $cycle, 'step_id' => $step,
        ])->assertRedirect();

        $team = TeamFormation::where('name', $teamName)->firstOrFail();
        $companyId = $founder->fresh()->company_id;
        $this->assertNotNull($companyId);
        $this->assertSame((int) $companyId, (int) Team_member::where('team_formation_id', $team->id)->where('isTeamLeader', 1)->value('Company_id'));
        $this->assertNotNull($team->group_id_chat);
    }

    public function test_plausible1_accept_and_decline_work_in_a_hackathon_without_a_team_step(): void
    {
        [$cycle, $step] = $this->hackathon(null);
        $team = $this->team($cycle, $step, $this->participant());
        $accepter = $this->participant();
        $decliner = $this->participant();
        $toAccept = $this->member($team, $accepter, 'pending');
        $toDecline = $this->member($team, $decliner, 'pending');

        $this->actingAs($accepter)->patch("/dashboard/hackathon/acceptInvitation/{$toAccept}")->assertRedirect();
        $this->actingAs($decliner)->patch("/dashboard/hackathon/rejectInvitation/{$toDecline}")->assertRedirect();

        $this->assertSame('accepted', $this->inviteStatus($toAccept));
        $this->assertSame('rejected', $this->inviteStatus($toDecline));
    }
}
