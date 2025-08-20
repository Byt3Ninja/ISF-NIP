@extends('emails.layout.master')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3"
            role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
            width="100%">
        <tr>
            <td class="pad"
                style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
                    <p style="margin: 0; word-break: break-word;"><span
                                style="word-break: break-word; color: #000000;"><strong>{{__('email.welcome_email.subject')}}</strong></span>
                    </p>
                </div>
            </td>
        </tr>
    </table>
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4"
            role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
            width="100%">
        <tr>
            <td class="pad"
                style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:left;mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{__('email.welcome_email.dear')}} {{ $data['name'] }},</p>
                    <p style="margin: 0;"> </p>
                    <p style="margin: 0;">{{__('email.welcome_email.m1')}}</p>
                    <p style="margin: 0;"> </p>
                    
                    <p style="margin: 0;">{{__('email.welcome_email.m2')}} {{ $data['email'] }}</p>
                    <p style="margin: 0;">
                    {{__('email.welcome_email.m4')}}
                        <a href="{{ $data['reset_url'] }}">{{__('email.welcome_email.m5')}}</a>
                    </p>
                    
                    <p style="margin: 0;"> </p>
                    <p style="margin: 0;">{{__('email.welcome_email.best_regards')}}</p>
                    {{-- <p style="margin: 0;">{{ $organization_name_en }}</p> --}}
                </div>
            </td>
        </tr>
        
    </table>
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-5"
            role="presentation"
            style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
            width="100%">
    
        <tr>
            <td class="pad"
                style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:right;mso-line-height-alt:22.5px;">
                    <p style="margin: 0;">{{__('email.welcome_email.dear')}} {{ $data['name'] }},</p>
                    <p style="margin: 0;"> </p>
                    <p style="margin: 0;">{{__('email.welcome_email.m1')}}</p>
                    <p style="margin: 0;"> </p>
                    
                    <p style="margin: 0;">{{__('email.welcome_email.m2')}} {{ $data['email'] }}</p>
                    <p style="margin: 0;">
                        {{__('email.welcome_email.m4')}}
                    <a href="{{ $data['reset_url'] }}">{{__('email.welcome_email.m5')}}</a>
                    </p>
                    
                    <p style="margin: 0;"> </p>
                    <p style="margin: 0;"> {{__('email.welcome_email.best_regards')}}</p>
                    {{-- <p style="margin: 0;">{{ $organization_name_ar }}</p> --}}
                </div>
            </td>
        </tr>
    

    </table>

@endsection

