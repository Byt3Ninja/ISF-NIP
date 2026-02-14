@extends('emails.layout.master')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                    <h2 style="margin: 0 0 16px; color: #1f2937; font-size: 20px; font-weight: 600;">
                        Hello {{ $name }},
                    </h2>

                    <p style="margin: 0 0 16px;">{{ $messageBody }}</p>

                    <div style="background-color: #f3f4f6; border-left: 4px solid #1aa19c; padding: 12px 16px; margin: 0 0 16px; border-radius: 4px;">
                        <p style="margin: 0; font-size: 14px;">
                            <strong>Invitation Scope:</strong> {{ $scope }}
                        </p>
                    </div>

                    @if($scope == 'Organization' && !empty($organizations))
                        <p style="margin: 0 0 8px; font-weight: 600;">Organizations:</p>
                        @foreach($organizations as $org)
                            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 8px 12px; border-radius: 4px; margin-bottom: 6px; font-size: 14px;">
                                {{ $org['name'] }}
                            </div>
                        @endforeach
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="button_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
        <tr>
            <td class="pad" style="padding: 20px 40px; text-align: center;">
                <a href="{{ env('APP_URL') }}/register?id=MentorRole" target="_blank" style="display: inline-block; padding: 14px 28px; background-color: #1aa19c; color: #ffffff; text-decoration: none; border-radius: 6px; font-family: Montserrat, Trebuchet MS, sans-serif; font-size: 15px; font-weight: 600;">
                    {{ __('admin.Accept_Invation') }}
                </a>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">Best regards,</p>
                    <p style="margin: 0; font-weight: 600;">{{ $org_name ?? config('app.name', 'GrowthLabs') }}</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
