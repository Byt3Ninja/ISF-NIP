<?php

return [
    'hackathon_invite' => [
        'whatsapp' => 'You have been invited to join the ":team" hackathon team by :leader. Check your email for details.',
        'broadcast' => 'You have been invited to join the ":team" hackathon team.',
    ],

    'hackathon_status' => [
        'accepted' => ':member has accepted your invitation to join the ":team" hackathon team.',
        'rejected' => ':member has declined your invitation to join the ":team" hackathon team.',
        'cancelled' => 'The invitation to join the ":team" hackathon team has been cancelled.',
    ],

    'startup_join' => [
        'whatsapp' => 'Congratulations! You have joined the :cycle. Welcome aboard from :app.',
        'broadcast' => 'You have joined the :cycle. Welcome aboard!',
    ],

    'welcome_judger' => [
        'subject'   => 'Welcome as a Judger!',
        'database'  => 'Welcome to our platform as a Judger!',
        'broadcast' => 'Welcome to our platform as a Judger! We are excited to have you.',
        'whatsapp'  => 'Welcome, :name! Your Judger account has been set up successfully.',
    ],

    'welcome' => [
        'subject'   => 'Welcome!',
        'database'  => 'Welcome to our platform!',
        'broadcast' => 'Welcome to our platform! We are excited to have you.',
        'whatsapp'  => 'Welcome, :name! Your account has been set up successfully.',
    ],

    'organization_manager_welcome' => [
        'database'  => 'Welcome! Your account has been created as Organization Manager of :organization_name.',
        'broadcast' => 'Welcome! Your account has been created as Organization Manager of :organization_name.',
    ],

    'organization_assigned' => [
        'database'  => 'You have been assigned to :organization_name.',
        'broadcast' => 'You have been assigned to :organization_name.',
    ],

    'mentorship_request' => [
        'whatsapp'  => 'You have a new mentorship request from :startup.',
        'broadcast' => 'New mentorship request received from :startup.',
        'database' => ':startup_name has requested a new mentorship session with :mentor_name on :session_date from :start_time – :end_time . Please approve or reject this request.',
    ],

    'change_cycle' => [
        'subject'   => 'Your Startup Progress Update - Stage Transition',
        'database'  => 'The step has changed for :company from :from to :to by :org.',
        'broadcast' => ':company moved to :to stage.',
        'whatsapp'  => 'Update: :company moved from :from to :to.',
    ],

    'new_training' => [
        'broadcast' => 'New training ":title" has been published.',
        'whatsapp'  => 'New training available: :title.',
    ],

    'task' => [
        'broadcast'          => 'Task added: :message',
        'whatsapp'           => 'Task added: :message',
        'subject'            => 'Task Added',
        'subject_add'        => 'Task Added',
        'subject_update'     => 'Task Updated',
        'subject_archive'    => 'Task Archived',
        'subject_reopen'     => 'Task Reopened',
        'subject_feedback'   => 'Task Review Update',
    ],

    'mentorship_session' => [
        'broadcast' => 'Mentorship session: :title',
        'whatsapp'  => ':greeting :line',
        'database' => 'New mentorship session notification',
        'subject' => 'Mentorship Session Update',
        'founder_booking' => 'You have a new mentorship session with :mentor_name on :session_date at :start_time – :end_time (AM/PM).',
    ],

    'confirm_email_update' => [
        'subject'   => 'Confirm Email Update',
        'broadcast' => 'Please confirm your email update.',
        'whatsapp'  => 'Confirm your email update using this link: :url',
        'database'  => 'Email update confirmation sent.',
    ],

    'user_invitation' => [
        'subject' => 'Invitation to Join Organization',
    ],

    'organization_created' => [
        'subject'   => 'New Organization Created',
        'broadcast' => 'A new organization (:organization) has been created.',
        'whatsapp'  => "Your organization (:organization) was created. Login email: :email, temp password: :password",
    ],

    'program_created' => [
        'subject' => 'A New Program has been Created',
    ],

    'export' => [
        'subject' => 'Your Export File',
    ],

    'custom_email' => [
        'subject' => 'Login details for VI Portal',
    ],

    'task_assigned' => [
        'subject' => 'Task Assigned',
        'database' => 'A new task has been assigned to you',
        'broadcast' => 'New task assigned: :title',
        'whatsapp' => 'New task assigned: :title',
    ],

    'task_updated' => [
        'subject' => 'Task Updated',
        'database' => 'A task has been updated',
        'broadcast' => 'Task updated: :title',
        'whatsapp' => 'Task updated: :title',
    ],
];
