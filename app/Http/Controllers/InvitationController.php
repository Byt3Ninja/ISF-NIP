<?php

namespace App\Http\Controllers;

class InvitationController extends Controller
{
    public function index()
    {
        $invitations = [
            [
                'invitee_name'      => 'Ahmed Hassan',
                'title'  => 'Annual Tech Conference',
                'scope'  => 'Public',
                'status' => 'Pending',
                'date'   => '2025-01-10',
            ],
            [
                'invitee_name'      => 'Sara Mohamed',
                'title'  => 'Company Strategy Meeting',
                'scope'  => 'Organization',
                'status' => 'Accepted',
                'date'   => '2025-01-15',
            ],
            [
                'invitee_name'      => 'Omar Khaled',
                'title'  => 'Product Launch Event',
                'scope'  => 'Public',
                'status' => 'Declined',
                'date'   => '2025-01-18',
            ],
            [
                'invitee_name'      => 'Laila Mahmoud',
                'title'  => 'HR Policy Workshop',
                'scope'  => 'Organization',
                'status' => 'Pending',
                'date'   => '2025-01-22',
            ],
            [
                'invitee_name'      => 'Youssef Adel',
                'title'  => 'Networking Breakfast',
                'scope'  => 'Public',
                'status' => 'Accepted',
                'date'   => '2025-01-25',
            ],
        ];

        return view('mentors.invitations.index', compact('invitations'));
    }
}
