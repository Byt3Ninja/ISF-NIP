@extends('emails.layout.master')

@section('content')
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:20px;line-height:150%;text-align:center;mso-line-height-alt:30px;">
                    <p style="margin: 0; word-break: break-word;"><strong>{{ __('emails.change_cycle_heading') }}</strong></p>
                </div>
            </td>
        </tr>
    </table>

    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
        <tr>
            <td class="pad" style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:15px;line-height:150%;text-align:{{ app()->getLocale() === 'ar' ? 'right' : 'left' }};mso-line-height-alt:22.5px;" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                    <p style="margin: 0;">{{ __('emails.hello') }} {{ $data['owner_name_'.app()->getLocale()] ?? __('emails.user') }},</p>

                    @if(isset($data['new_step']) && isset($data['last_step']))
                        <p style="margin: 10px 0;">{!! __('emails.cycle_changed', [
                            'from' => '<strong>'.$data['last_step'].'</strong>',
                            'to' => '<strong>'.$data['new_step'].'</strong>'
                        ]) !!}</p>
                    @endif

                    @if(isset($data['organization_name_'.app()->getLocale()]))
                        <p style="margin: 10px 0;"><strong>{{ __('emails.organization') }}:</strong> {{ $data['organization_name_'.app()->getLocale()] }}</p>
                    @endif

                    <p style="margin: 10px 0 0;">{{ __('emails.thank_you') }}</p>
                    <p style="margin: 0;">{{ $org_name ?? config('app.name', 'GrowthLabs') }}</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
