<?php

namespace Tests\Feature\Correctness;

use App\Room;
use App\Room_booking;
use App\Rooms_inclusive;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Correctness audit, booking/rooms cluster: C4 (a booker could self-approve),
 * C19 (room inclusives saved against room_id = 1).
 */
class BookingRoomsCorrectnessTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('admin', ['booking.store', 'rooms.update']);
    }

    private function room(): Room
    {
        return Room::forceCreate([
            'name_en' => 'Audit Room', 'name_ar' => 'Audit Room', 'type_trans' => 'meeting', 'price' => 10,
            'discount' => 0, 'desc_en' => 'Room', 'desc_ar' => 'Room', 'video_link' => '', 'published' => 1,
        ]);
    }

    public function test_c4_a_new_booking_always_starts_in_review(): void
    {
        $booker = $this->actor('admin');
        $room = $this->room();

        $this->actingAs($booker)->post('/dashboard/booking', [
            'room_id' => $room->id, 'fromDate' => now()->addDay()->toDateTimeString(), 'toDate' => now()->addDays(2)->toDateTimeString(),
            'approved' => 'Approved', 'user_id' => 1, 'user_added' => 1,
        ])->assertRedirect();

        $booking = Room_booking::where('room_id', $room->id)->firstOrFail();
        $this->assertSame('Reviewing', $booking->approved);
        $this->assertSame($booker->id, (int) $booking->user_id);
        $this->assertNull($booking->user_added);
    }

    public function test_c19_room_inclusives_are_saved_against_the_edited_room(): void
    {
        $admin = $this->actor('admin');
        $room = $this->room();
        $inclusiveId = DB::table('inclusives')->insertGetId(['en_title' => 'Projector', 'ar_title' => 'Projector', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->put("/dashboard/rooms/{$room->id}", [
            'name_en' => 'Audit Room', 'name_ar' => 'Audit Room', 'type_trans' => 'meeting', 'price' => 10,
            'discount' => 0, 'desc_en' => 'Room', 'desc_ar' => 'Room', 'video_link' => 'https://example.invalid/tour', 'published' => 1,
            'inclusives' => [$inclusiveId],
        ])->assertRedirect();

        $this->assertSame([$room->id], Rooms_inclusive::where('inclusive_id', $inclusiveId)->pluck('room_id')->map('intval')->all());
    }
}
