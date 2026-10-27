<?php

namespace App\Services;

use App\Cycle;
use App\Messenger;
use App\Notifications\ProgramCreatedNotification;
use App\Role;
use App\Support\NotificationClaim;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies the people responsible for a program's organization -- its
 * manager(s), its assigned sub-admins, and the platform's super admins --
 * that a new program was created. Shared by the web and API program
 * creation flows so both claim/notify the same way.
 */
class ProgramCreatedNotifier
{
    public function notify(Cycle $cycle): void
    {
        // Claims the cycle atomically so a retried request never
        // double-emails everyone.
        $claim = NotificationClaim::on('cycles', ['id' => $cycle->id], 'program_created_notified_at');
        if (!$claim->take()) {
            return;
        }

        $rolesUsers = Role::_users(['organizationManager', 'admin']);
        $orgManagers = $rolesUsers->filter(fn ($u) => $u->hasRole('organizationManager') && $u->organization_id == $cycle->organization_id);
        $superAdmins = $rolesUsers->filter(fn ($u) => $u->hasRole('admin'));
        $subAdmins = $cycle->organization?->assignedUsers ?? collect();
        $recipients = $orgManagers->merge($subAdmins)->merge($superAdmins)->unique('id');

        $notified = 0;
        foreach ($recipients as $recipient) {
            try {
                Notification::send([$recipient], new ProgramCreatedNotification($cycle));

                $messenger = new Messenger();
                $messenger->sender = Auth::id();
                $messenger->receiver = $recipient->id;
                $messenger->subject = 'New Program Created';
                $messenger->message = "A new Program Titled \"{$cycle->title}\" has been created.";
                $messenger->read = 0;
                $messenger->save();
                $notified++;
            } catch (\Throwable $e) {
                Log::error('ProgramCreatedNotification dispatch failed: ' . $e->getMessage(), [
                    'cycle_id' => $cycle->id,
                    'recipient_id' => $recipient->id,
                ]);
            }
        }

        // Nobody was reached (e.g. mail down): give the claim back so a retry can notify.
        // After a partial send it is kept -- retrying would duplicate for the others.
        if ($notified === 0 && $recipients->isNotEmpty()) {
            $claim->release();
        }
    }
}
