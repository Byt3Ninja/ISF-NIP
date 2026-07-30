@extends('emails.layout.master')

@section('content')
@php
    $mainColor = '#4F46E5';
    $bgColor = '#F3F4F6';
    $isRtl = app()->getLocale() === 'ar';
    $dirAttr = $isRtl ? 'rtl' : 'ltr';
    $alignStart = $isRtl ? 'right' : 'left';
    $alignEnd = $isRtl ? 'left' : 'right';

    $managerName = trim(($user->first_name_en ?? '') . ' ' . ($user->last_name_en ?? '')) ?: ($user->name ?? '');
    $organizationName = $organization->name ?? $org_name;
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: {{ $bgColor }}; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 40px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                <tr>
                    <td height="6" style="background-color: {{ $mainColor }};"></td>
                </tr>
                <tr>
                    <td style="padding: 40px 40px 20px 40px; text-align: {{ $alignStart }}; direction: {{ $dirAttr }};">

                        <p style="margin: 0 0 10px 0; color: #6B7280; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">{{ __('email.org_assigned.preheader') }}</p>
                        <h1 style="margin: 0 0 20px 0; font-size: 24px; color: #111827; font-weight: bold;">{{ __('email.hello') . ' ' . $managerName }},</h1>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.org_assigned.intro', ['organization_name' => $organizationName, 'platform_name' => $platformName]) }}</p>

                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #111827; font-weight: 600;">{{ __('email.org_assigned.details_title') }}</p>

                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F9FAFB; border-radius: 6px; border: 1px solid #E5E7EB; margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 20px; direction: {{ $dirAttr }}; text-align: {{ $alignStart }};">
                                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding-bottom: {{ !empty($assignedByName) ? '10px' : '0px' }}; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.org_assigned.label_organization') }}:</td>
                                            <td style="padding-bottom: {{ !empty($assignedByName) ? '10px' : '0px' }}; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $organizationName }}</td>
                                        </tr>
                                        @if( !empty($assignedByName) )
                                            <tr>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #6B7280; width: 150px;">{{ __('email.org_assigned.label_assigned_by') }}:</td>
                                                <td style="padding-bottom: 0px; font-size: 14px; color: #111827; font-weight: 600; text-align: {{ $alignEnd }};">{{ $assignedByName }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.org_assigned.access_note') }}</p>

                        @isset($dashboardUrl)
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 20px 0;">
                                <tr>
                                    <td align="center" style="background-color: {{ $mainColor }}; border-radius: 6px;">
                                        <a href="{{ $dashboardUrl }}" style="display: inline-block; padding: 12px 28px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none;">{{ __('email.org_assigned.dashboard_button') }}</a>
                                    </td>
                                </tr>
                            </table>
                        @endisset

                        <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 150%; color: #374151;">{{ __('email.org_assigned.unexpected') }}</p>

                        <p style="margin-top: 30px; font-size: 14px; color: #6B7280;">
                            {{ __('email.regards') }}<br>
                            <strong>{{ __('email.team', ['platform_name' => $platformName]) }}</strong>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
