<?php

namespace Tests\Feature\Security\Audit;

use App\Messenger;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H9: the `messages*` exemption let every role (incl. service providers) reach the
 * Messenger and the platform contact-us inbox, and the send endpoints accepted any
 * receiver id. Messaging is now permission-checked and receivers must be on the
 * list the compose screen offers (App\Services\MessagingRecipients).
 */
class MessagingAccessTest extends TestCase
{
    use SecurityActors;

    private const MESSENGER_ROLES = ['admin', 'organizationmanager', 'subadmin', 'mentor', 'judger', 'registered'];

    private int $orgId;
    private int $cycleId;
    private User $founder;
    private int $companyId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        // Mirror the JSON defaults (the deploy seeder applies them; tests grant inline).
        foreach (self::MESSENGER_ROLES as $role) {
            $this->grant($role, ['messages.list', 'messages.send', 'messages.save', 'messages.reply', 'messages.send_direct', 'messages.recipients_list_by_program']);
        }
        $this->grant('admin', ['contact_messages.list']);

        $this->orgId = $this->organization();
        $this->cycleId = $this->program($this->orgId);
        [$this->companyId, $this->founder] = $this->startup($this->cycleId, ['approved' => 1]);
    }

    /** A founder of a startup in another organization's program. */
    private function outsider(): User
    {
        [, $founder] = $this->startup($this->program($this->organization()), ['approved' => 1]);

        return $founder;
    }

    private function messagesFrom(User $sender): int
    {
        return Messenger::where('sender', $sender->id)->count();
    }

    public function test_service_provider_cannot_open_the_messenger(): void
    {
        $this->actingAs($this->actor('serviceProvider'))->get('/dashboard/messages')->assertStatus(401);
    }

    public function test_messenger_roles_can_still_open_it(): void
    {
        $this->actingAs($this->founder)->get('/dashboard/messages')->assertOk();
        $this->actingAs($this->actor('mentor'))->get('/dashboard/messages')->assertOk();
        $this->actingAs($this->manager($this->orgId))->get('/dashboard/messages')->assertOk();
        $this->actingAs($this->actor('judger'))->get('/dashboard/messages')->assertOk();
    }

    public function test_org_manager_compose_screen_renders(): void
    {
        $this->actingAs($this->manager($this->orgId))->get('/dashboard/sendmessage')->assertOk();
    }

    public function test_contact_us_inbox_is_admin_only(): void
    {
        $this->actingAs($this->manager($this->orgId))->get('/dashboard/mymsg')->assertStatus(401);
        $this->actingAs($this->actor('admin'))->get('/dashboard/mymsg')->assertOk();
    }

    public function test_founder_cannot_direct_message_an_unrelated_user(): void
    {
        $outsider = $this->outsider();

        $this->actingAs($this->founder)->post('/dashboard/send_message', ['receiver' => $outsider->id, 'subject' => 's', 'message' => 'm']);

        $this->assertSame(0, $this->messagesFrom($this->founder));
    }

    public function test_founder_can_reply_to_someone_who_wrote_to_them(): void
    {
        $outsider = $this->outsider();
        Messenger::create(['sender' => $outsider->id, 'receiver' => $this->founder->id, 'subject' => 'hi', 'message' => 'hello']);

        $this->actingAs($this->founder)->post('/dashboard/send_message', ['receiver' => $outsider->id, 'subject' => 's', 'message' => 'm']);

        $this->assertSame(1, $this->messagesFrom($this->founder));
    }

    public function test_founder_cannot_compose_to_another_programs_startup(): void
    {
        $outsider = $this->outsider();

        $this->actingAs($this->founder)->post('/dashboard/sendmessage', ['type' => 1, 'users' => [$outsider->id], 'subject' => 's', 'message' => 'm']);

        $this->assertSame(0, $this->messagesFrom($this->founder));
    }

    public function test_founder_can_compose_to_a_mentor_of_their_program(): void
    {
        $mentor = $this->actor('mentor');
        DB::table('cycles_mentors')->insert(['cycle_id' => $this->cycleId, 'user_id' => $mentor->id, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->founder)->post('/dashboard/sendmessage', ['type' => 2, 'mentors' => [$mentor->id], 'subject' => 's', 'message' => 'm']);

        $this->assertSame(1, Messenger::where('sender', $this->founder->id)->where('receiver', $mentor->id)->count());
    }

    public function test_org_manager_cannot_broadcast_to_another_organization(): void
    {
        $foreignOrg = $this->organization();
        $this->manager($foreignOrg);
        $manager = $this->manager($this->orgId);

        $this->actingAs($manager)->post('/dashboard/sendmessage', ['type' => 3, 'organizations' => [$foreignOrg], 'subject' => 's', 'message' => 'm']);

        $this->assertSame(0, $this->messagesFrom($manager));
    }

    public function test_reply_endpoint_cannot_target_an_unrelated_user(): void
    {
        $outsider = $this->outsider();

        $this->actingAs($this->founder)->postJson('/dashboard/sendReplymessage', ['message_receiver' => $outsider->id, 'message_message' => 'm']);

        $this->assertSame(0, $this->messagesFrom($this->founder));
    }

    public function test_recipient_list_is_limited_to_the_managers_programs(): void
    {
        $foreignCycle = $this->program($this->organization());
        $this->startup($foreignCycle, ['approved' => 1]);

        $response = $this->actingAs($this->manager($this->orgId))->getJson('/dashboard/get-users-by-cycle?cycle_id=' . $foreignCycle);

        $this->assertSame([], $response->json('users') ?? []);
    }

    public function test_recipient_list_returns_the_managers_own_program_founders(): void
    {
        $response = $this->actingAs($this->manager($this->orgId))->getJson('/dashboard/get-users-by-cycle?cycle_id=' . $this->cycleId);

        $this->assertContains($this->founder->id, collect($response->json('users'))->pluck('id')->all());
    }

    public function test_messaging_route_names_are_unique_and_seeded_for_the_six_roles(): void
    {
        $names = collect(Route::getRoutes()->getRoutes())->map->getName()->filter(fn ($n) => $n && (str_starts_with($n, 'messages.') || str_starts_with($n, 'contact_messages.')));
        $this->assertSame($names->count(), $names->unique()->count(), 'duplicate messaging route names: ' . $names->duplicates()->implode(', '));

        $json = collect(json_decode(file_get_contents(base_path('database/dbFiles/rolesWithDefaultPermissions.json')), true))
            ->flatMap(fn ($group) => $group['permissions'])->keyBy('name');
        $this->assertEqualsCanonicalizing(self::MESSENGER_ROLES, $json['messages.list']['default_for']);
        $this->assertSame(['admin'], $json['contact_messages.list']['default_for']);
    }
}
