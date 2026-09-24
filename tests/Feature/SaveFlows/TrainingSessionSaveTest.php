<?php

namespace Tests\Feature\SaveFlows;

use App\Notifications\NewTrainingNotification;
use App\Notifications\TrainerEmail;
use App\Services\ZoomService;
use App\TrainingSession;
use Carbon\Carbon;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * GV-9566 save flow: an admin creates / edits a training session.
 *
 * Run under the reported server condition: APP_TIMEZONE=UTC, admins in Cairo.
 * The typed wall-clock must be stored as the Cairo instant (it used to be read
 * in the server zone, 3h off), survive an unchanged edit, reach Zoom as Cairo
 * wall-clock, and reach each founder in their own timezone.
 */
class TrainingSessionSaveTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    private string $originalZone;
    private array $program;
    private \App\User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        $this->setStorageZone('UTC');
        $this->setPlatformZone('Africa/Cairo');
        $this->fakeOutboundServices();

        $this->program = $this->makeFinalScreeningProgram();
        // Training lives on its own step (stype 4) of the same program; the founder sits on it.
        $this->program['trainingStep'] = \DB::table('steps')->insertGetId([
            'cycle_id' => $this->program['cycle'], 'title' => 'GV-9566 Training', 'stype' => 4, 'arr' => 2,
            'from' => '2026-09-01', 'to' => '2026-10-31', 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('companies')->where('id', $this->program['company'])->update(['step' => $this->program['trainingStep']]);

        $this->admin = $this->makeUser('admin', null);
        Carbon::setTestNow(Carbon::parse('2026-09-25 09:00', 'Africa/Cairo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->setStorageZone($this->originalZone);

        parent::tearDown();
    }

    private function create(string $type, array $extra = []): TrainingSession
    {
        $this->actingAs($this->admin)->post('/dashboard/training/addsession', array_merge([
            'type' => $type,
            'step_id' => $this->program['trainingStep'],
            'cycle_id' => $this->program['cycle'],
            'title' => 'GV9566 Training ' . $type,
            'dsc' => 'Dummy',
            'datetime' => '2026-10-01',
            'starttime' => '10:00',
            'endtime' => '11:00',
            'trainer_name' => 'Dummy Trainer',
            'trainer_email' => 'gv9566-trainer@example.invalid',
            'training_Address' => 'Dummy address',
            'training_location' => 'https://example.invalid/map',
        ], $extra))->assertSessionHas('class', 'success');

        return TrainingSession::where('title', 'GV9566 Training ' . $type)->latest('id')->firstOrFail();
    }

    public function test_typed_time_is_stored_as_the_platform_instant_not_the_server_zone(): void
    {
        $session = $this->create('offline');

        $this->assertSame(Carbon::parse('2026-10-01 10:00', 'Africa/Cairo')->timestamp, (int) $session->getRawOriginal('datetime'));
        $this->assertSame(Carbon::parse('2026-10-01 11:00', 'Africa/Cairo')->timestamp, (int) $session->getRawOriginal('enddatetime'));
    }

    public function test_the_edit_form_shows_the_typed_time_and_an_unchanged_edit_round_trips(): void
    {
        $session = $this->create('offline');
        $stored = (int) $session->getRawOriginal('datetime');

        $form = $this->actingAs($this->admin)->post('/dashboard/training/session', ['id' => $session->id])->assertOk()->json();
        $this->assertSame('2026-10-01', $form['daterange']);
        $this->assertSame('10:00 AM', $form['fromtime']);
        $this->assertSame('11:00 AM', $form['endtime']);

        // Post the form back exactly as shown.
        $this->actingAs($this->admin)->put('/dashboard/training/editsession/' . $session->id, [
            'sid' => $session->id, 'type' => 'offline', 'step_id' => $this->program['trainingStep'],
            'title' => $session->title, 'dsc' => 'Dummy', 'datetime' => $form['daterange'],
            'starttime' => $form['fromtime'], 'endtime' => $form['endtime'],
            'trainer_name' => 'Dummy Trainer', 'trainer_email' => 'gv9566-trainer@example.invalid',
            'training_Address' => 'Dummy address', 'training_location' => 'https://example.invalid/map',
        ])->assertSessionHas('class', 'success');

        $this->assertSame($stored, (int) $session->fresh()->getRawOriginal('datetime'));
    }

    public function test_zoom_receives_the_platform_wall_clock_and_zone(): void
    {
        $this->create('sessions');

        $payload = $this->meetings->created()[0] ?? null;
        $this->assertNotNull($payload, 'No meeting was created for a "sessions" training.');
        $this->assertSame('Africa/Cairo', $payload['timezone']);

        $zoom = (new \ReflectionClass(ZoomService::class))->newInstanceWithoutConstructor();
        $this->assertSame('2026-10-01T10:00:00', $zoom->toZoomTimeFormat($payload['start_time'], $payload['timezone']));
    }

    public function test_each_founder_is_emailed_in_their_own_timezone_and_the_trainer_in_platform_time(): void
    {
        $this->program['founder']->forceFill(['timezone' => 'Asia/Kolkata'])->save();

        $this->create('sessions');

        Notification::assertSentTo($this->program['founder'], NewTrainingNotification::class, function ($notification) {
            $data = (new \ReflectionProperty($notification, 'data'))->getValue($notification);
            // 10:00 Cairo = 12:30 Kolkata
            return str_contains($data['date'], '2026-10-01 12:30 PM')
                && $data['start_time'] === '12:30 PM' && $data['end_time'] === '01:30 PM';
        });

        Notification::assertSentOnDemand(TrainerEmail::class, function ($notification, $channels, AnonymousNotifiable $notifiable) {
            $mail = (new \ReflectionProperty($notification, 'data'))->getValue($notification);
            return $mail['session_date'] === '2026-10-01' && $mail['start_time'] === '10:00 AM' && $mail['end_time'] === '11:00 AM';
        });
    }
}
