#  Notification System

This document explains how to add and manage notifications in the system.

Notifications can be sent via:

- **Database** (for audit/history)
- **Email** (via Laravel mailables & Blade templates)
- **WhatsApp** (via custom `WhatsAppChannel`)
- **Pusher/Broadcast** (real-time push notifications)

---

##  Code Structure

```plaintext
app/
 └── Notifications/
      └── ChangeCycle.php          # Example notification class
resources/
 └── views/
      └── emails/
           └── emailCycle.blade.php # Email Blade for notifications
lang/
 └── en/
      ├── email.php                # Email translations
      └── notification.php         # Notification translations
app/Broadcasting/
 └── WhatsAppChannel.php           # Custom channel for WhatsApp
```

---

## 🛠 Creating a New Notification

Generate a new notification class:

```bash
php artisan make:notification MyNewNotification
```

### Define Delivery Channels

```php
public function via($notifiable)
{
    $channels = ['database'];

    if (!empty($notifiable->accept_whatsapp)) {
        $channels[] = WhatsAppChannel::class;
    }

    if (!empty($notifiable->accept_pusher)) {
        $channels[] = 'broadcast';
    }

    if (!empty($notifiable->accept_email)) {
        $channels[] = 'mail';
    }

    return $channels;
}
```

### Define Each Channel Handler
- **Database (`toDatabase`)** → Saves notification content for history
- **Email (`toMail`)** → Sends email using a Blade view
- **WhatsApp (`toWhatsapp`)** → Sends text to WhatsApp API
- **Broadcast (`toBroadcast`)** → Pushes data to Pusher

---

##  Email Notifications

Email templates are stored in `resources/views/emails/`.

**Example: `emailCycle.blade.php`**

```blade
@extends('emails.layout')

@section('content')
<p><strong>{{ __('email.cycle_email.title') }}</strong></p>
<p>{{ __('email.cycle_email.dear') }} {{ $owner_name_en }},</p>

<p>{{ __('email.cycle_email.in_body_1', [
    'company_name' => $company_name_en,
    'last_step' => $last_step,
    'new_step' => $new_step
]) }}</p>

<p>{{ __('email.cycle_email.regards') }}<br>{{ $organization_name_en }}</p>
@endsection
```

---

##  Translations

All messages are **translatable**.

### Email Translations → `lang/en/email.php`

```php
'cycle_email' => [
    'subject' => 'Startup Cycle Update',
    'title' => 'Startup Progress Notification',
    'dear' => 'Dear',
    'in_body_1' => 'We are pleased to inform you that your startup :company_name moved from :last_step to :new_step.',
    'regards' => 'Best regards,',
],
```

### Notification Translations → `lang/en/notification.php`

```php
'change_cycle' => [
    'subject'   => 'Your Startup Progress Update',
    'database'  => 'The step has changed for :company from :from to :to by :org.',
    'broadcast' => ':company moved to :to stage.',
    'whatsapp'  => 'Update: :company moved from :from to :to.',
],
```

---

##  Sending Notifications

Use the `notify()` method on a `User` (or any notifiable model):

```php
$user->notify(new ChangeCycle([
    'company_name_en' => 'Tech Startup',
    'last_step' => 'Incubation',
    'new_step' => 'Acceleration',
    'organization_name_en' => 'Startup Hub'
]));
```

---
