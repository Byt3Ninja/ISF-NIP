@php
    $setting = App\Settings::first();
    $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
@endphp

@extends('emails.layout.master')

@section('content')
    <tr>
        <td class="content-block" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
            <h1 style="font-family: 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 20px 0 30px; padding: 0;">
                Reset Your Password
            </h1>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                Hello {{ $user->name ?? 'there' }},
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 16px; line-height: 1.6; margin: 0 0 20px; padding: 0;">
                You are receiving this email because we received a password reset request for your account.
            </p>
            <table border="0" cellpadding="0" cellspacing="0" style="margin: 20px 0 30px;">
                <tr>
                    <td align="center" style="border-radius: 3px;" bgcolor="#1aa19c">
                        <a href="{{ $resetUrl }}" target="_blank" style="font-size: 15px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; padding: 12px 25px; border: 1px solid #1aa19c; display: inline-block; font-weight: 500;">
                            Reset Password
                        </a>
                    </td>
                </tr>
            </table>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0; color: #666;">
                This password reset link will expire in {{ $expire }} minutes.
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0; color: #666;">
                If you did not request a password reset, no further action is required.
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 20px; padding: 0; color: #666;">
                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; line-height: 1.6; margin: 0 0 20px; padding: 10px; background: #f5f5f5; border-radius: 3px; word-break: break-all;">
                {{ $resetUrl }}
            </p>
            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
                Thank you,<br>
                {{ $platformName }}
            </p>
        </td>
    </tr>
@endsection
