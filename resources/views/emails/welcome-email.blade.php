@php
    $setting = App\Settings::first();
    $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
@endphp

@extends('emails.layout.master')
@section('content')

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{__('email.welcome_email.dear')}} {{ $user->first_name_en . ' ' . $user->last_name_en }},</p>
                    <p style="margin: 0;"> {{__('email.welcome_email.m1')}}</p>
                    <p style="margin: 0;"> {{__('email.welcome_email.m2')}} <strong>{{ $user->email }}</strong></p>
                    <p style="margin: 0 0 10px;">{{__('email.welcome_email.m3')}}</p>
                    <p style="margin: 0;">{{__('email.welcome_email.best_regards')}}</p>
                    <p style="margin: 0;">{{ $platformName }}</p>
                    </div>
            </td>
        </tr>
    </table>


@endsection