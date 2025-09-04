<?php

return [
    'hackathon_invite' => [
        'whatsapp' => 'Du wurdest von :leader in das Hackathon-Team ":team" eingeladen. Prüfe deine E-Mails für Details.',
        'broadcast' => 'Du wurdest in das Hackathon-Team ":team" eingeladen.',
    ],

    'startup_join' => [
        'whatsapp' => 'Glückwunsch! Du bist dem :cycle beigetreten. Willkommen an Bord von :app.',
        'broadcast' => 'Du bist dem :cycle beigetreten. Willkommen an Bord!',
    ],

    'welcome_judger' => [
        'subject'   => 'Willkommen als Juror!',
        'database'  => 'Willkommen auf unserer Plattform als Juror!',
        'broadcast' => 'Willkommen auf unserer Plattform als Juror! Wir freuen uns, dass du dabei bist.',
        'whatsapp'  => 'Willkommen, :name! Dein Juror-Konto wurde erfolgreich eingerichtet.',
    ],

    'welcome' => [
        'subject'   => 'Willkommen!',
        'database'  => 'Willkommen auf unserer Plattform!',
        'broadcast' => 'Willkommen auf unserer Plattform! Wir freuen uns, dass du dabei bist.',
        'whatsapp'  => 'Willkommen, :name! Dein Konto wurde erfolgreich eingerichtet.',
    ],

    'mentorship_request' => [
        'whatsapp'  => 'Du hast eine neue Mentoring-Anfrage von :startup.',
        'broadcast' => 'Neue Mentoring-Anfrage von :startup erhalten.',
    ],

    'change_cycle' => [
        'subject'   => 'Aktualisierung deines Startup-Fortschritts – Phasenwechsel',
        'database'  => 'Die Phase für :company wurde von :from zu :to geändert durch :org.',
        'broadcast' => ':company wurde in die Phase :to verschoben.',
        'whatsapp'  => 'Aktualisierung: :company wurde von :from zu :to verschoben.',
    ],

    'new_training' => [
        'broadcast' => 'Neues Training ":title" wurde veröffentlicht.',
        'whatsapp'  => 'Neues Training verfügbar: :title.',
    ],

    'task' => [
        'broadcast' => 'Aufgaben-Update: :message',
        'whatsapp'  => 'Aufgaben-Update: :message',
        'subject'   => 'Aufgaben-Update',
    ],

    'mentorship_session' => [
        'broadcast' => 'Mentoring-Sitzung: :title',
        'whatsapp'  => ':greeting :line',
    ],

    'confirm_email_update' => [
        'subject'   => 'E-Mail-Aktualisierung bestätigen',
        'broadcast' => 'Bitte bestätige deine E-Mail-Aktualisierung.',
        'whatsapp'  => 'Bestätige deine E-Mail-Aktualisierung über diesen Link: :url',
        'database'  => 'Bestätigung zur E-Mail-Aktualisierung gesendet.',
    ],

    'user_invitation' => [
        'subject' => 'Einladung zum Beitritt zur Organisation',
    ],

    'organization_created' => [
        'subject'   => 'Neue Organisation wurde erstellt',
        'broadcast' => 'Eine neue Organisation (:organization) wurde erstellt.',
        'whatsapp'  => 'Deine Organisation (:organization) wurde erstellt. Login-E-Mail: :email, temporäres Passwort: :password',
    ],

    'program_created' => [
        'subject' => 'Ein neues Programm wurde erstellt',
    ],

    'export' => [
        'subject' => 'Deine Exportdatei',
    ],

    'custom_email' => [
        'subject' => 'Zugangsdaten für das VI-Portal',
    ],
];
