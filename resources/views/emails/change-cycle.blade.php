<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ __('emails.change_cycle_subject') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
        .rtl { direction: rtl; text-align: right; }
        .ltr { direction: ltr; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ __('emails.change_cycle_heading') }}</h2>
        </div>
        
        <div class="content {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
            <p>{{ __('emails.hello') }} {{ $data['owner_name_'.app()->getLocale()] ?? __('emails.user') }},</p>
            
            @if(isset($data['new_step']) && isset($data['last_step']))
                <p>{!! __('emails.cycle_changed', [
                    'from' => '<strong>'.$data['last_step'].'</strong>',
                    'to' => '<strong>'.$data['new_step'].'</strong>'
                ]) !!}</p>
            @endif
            
            @if(isset($data['organization_name_'.app()->getLocale()]))
                <p><strong>{{ __('emails.organization') }}:</strong> {{ $data['organization_name_'.app()->getLocale()] }}</p>
            @endif
        </div>
        
        <div class="footer">
            <p>{{ __('emails.thank_you') }}</p>
            <p>{{ $data['organization_name_'.app()->getLocale()] ?? config('app.name') }}</p>
            <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
