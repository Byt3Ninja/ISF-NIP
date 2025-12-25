<?php

namespace App\Http\Controllers;

class InvitationController extends Controller
{
    public function index()
    {
        $invitations = [
            [
                'invitee_name'      => 'Ahmed Hassan',
                'invitee_email'     => 'ahmed.hassan@example.com',
                'title'             => 'Annual Tech Conference',
                'scope'             => 'Public',
                'status'            => 'Pending',
                'message_content'   => 'You are invited to join our annual tech conference.',
                'response_message'  => 'Hello Ahmed, I am excited to join the tech conference.',
                'date_timestamp'    => '2025-01-10 10:00:00',
                'attachments'       => [],
            ],
            [
                'invitee_name'      => 'Sara Mohamed',
                'invitee_email'     => 'sara.mohamed@example.com',
                'title'             => 'Company Strategy Meeting',
                'scope'             => 'Organization',
                'status'            => 'Accepted',
                'message_content'   => 'Please attend the company strategy meeting.',
                'response_message'  => 'I will be attending the strategy meeting.',
                'date_timestamp'    => '2025-01-15 14:30:00',
                'attachments'       => ['agenda.pdf'],
            ],
            [
                'invitee_name'      => 'Omar Khaled',
                'invitee_email'     => 'omar.khaled@example.com',
                'title'             => 'Product Launch Event',
                'scope'             => 'Public',
                'status'            => 'Declined',
                'message_content'   => 'Join us for the exciting product launch event.',
                'response_message'  => 'Sorry, I can\'t make it.',
                'date_timestamp'    => '2025-01-18 09:00:00',
                'attachments'       => ['product_brochure.pdf', 'invitation_card.jpg'],
            ],
            [
                'invitee_name'      => 'Laila Mahmoud',
                'invitee_email'     => 'laila.mahmoud@example.com',
                'title'             => 'HR Policy Workshop',
                'scope'             => 'Organization',
                'status'            => 'Pending',
                'message_content'   => 'Workshop on new HR policies.',
                'response_message'  => 'I will be attending the workshop.',
                'date_timestamp'    => '2025-01-22 11:00:00',
                'attachments'       => [],
            ],
            [
                'invitee_name'      => 'Youssef Adel',
                'invitee_email'     => 'youssef.adel@example.com',
                'title'             => 'Networking Breakfast',
                'scope'             => 'Public',
                'status'            => 'Accepted',
                'message_content'   => 'Network with industry leaders over breakfast.',
                'response_message'  => 'I will be attending the networking event.',
                'date_timestamp'    => '2025-01-25 08:00:00',
                'attachments'       => ['networking_guide.pdf'],
            ],
        ];

        return view('mentors.invitations.index', compact('invitations'));
    }
}
