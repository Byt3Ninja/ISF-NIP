<?php

namespace Tests\Feature\Security\Audit;

use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * N10: deletes and approvals ran on GET, so a link or <img> on any page could trigger
 * them for a logged-in admin (no CSRF on GET). They now need DELETE/POST + CSRF token.
 */
class StateChangingGetTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    public static function stateChangingGets(): array
    {
        return [
            ['/dashboard/calendar/1/delete'],
            ['/dashboard/delete_slide/1'],
            ['/dashboard/delete_menu/1'],
            ['/dashboard/deletepost/1'],
            ['/dashboard/deletepage/1'],
            ['/dashboard/deleteevent/1'],
            ['/dashboard/deletemsg/1'],
            ['/dashboard/deleteImages/1'],
            ['/dashboard/bookingApprove/1/1'],
        ];
    }

    /** @dataProvider stateChangingGets */
    public function test_get_no_longer_changes_state(string $uri): void
    {
        $this->actingAs($this->actor('admin'))->get($uri)->assertStatus(405);
    }

    public function test_confirm_action_partial_renders_a_csrf_delete_form_behind_the_alert(): void
    {
        $html = view('partials.forms.confirm-action', [
            'formId' => 'delete-slide-7', 'action' => '/dashboard/delete_slide/7', 'label' => '<i class="ti ti-trash"></i>',
        ])->render();

        $this->assertStringContainsString('data-alert-form="delete-slide-7"', $html);
        $this->assertStringContainsString('<form id="delete-slide-7" action="/dashboard/delete_slide/7" method="POST"', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringContainsString('name="_token"', $html);
    }

    public function test_slide_is_deleted_with_delete_verb(): void
    {
        $this->grant('admin', ['slides.delete']);
        $id = DB::table('sliders')->insertGetId(['img_path' => 'x.png', 'title' => 'Audit', 'ar_title' => 'Audit', 'slideorder' => 1]);

        $this->actingAs($this->actor('admin'))->delete('/dashboard/delete_slide/' . $id)->assertRedirect();

        $this->assertFalse(DB::table('sliders')->where('id', $id)->exists());
    }

    public function test_booking_is_approved_with_post_verb(): void
    {
        $this->grant('admin', ['bookings.approve']);
        $admin = $this->actor('admin');
        $roomId = DB::table('rooms')->insertGetId([
            'name_en' => 'Audit Room', 'name_ar' => 'Audit Room', 'type_trans' => 'room', 'price' => 0, 'discount' => 0,
            'desc_en' => '', 'desc_ar' => '', 'video_link' => '',
        ]);
        $id = DB::table('room_bookings')->insertGetId(['room_id' => $roomId, 'user_id' => $admin->id, 'approved' => 'Reviewing']);

        $this->actingAs($admin)->post('/dashboard/bookingApprove/' . $id . '/1')->assertRedirect();

        $this->assertSame('Approved', DB::table('room_bookings')->where('id', $id)->value('approved'));
    }
}
