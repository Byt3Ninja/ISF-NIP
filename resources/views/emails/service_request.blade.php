@extends('emails.layout.master')

@section('content')
@php
    $organization = null;
    if( isset( $service->serviceCategory ) && $service->serviceCategory->organization )
    {
        $organization = $service->serviceCategory->organization;
    }
@endphp
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3"
                                                   role="presentation"
                                                   style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;"
                                                   width="100%">
                                                <tr>

                                                    <td class="pad"
                                                        style="padding-bottom:10px;padding-left:40px;padding-right:40px;padding-top:10px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:30px;line-height:150%;text-align:center;mso-line-height-alt:45px;">
                                                            <p style="margin: 0; word-break: break-word;"><span
                                                                        style="word-break: break-word; color: #000000;"><strong></strong></span>
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
                                                            <p style="margin: 0;">Dear {{ $service->vendor_name }},</p>
                                                            <p style="margin: 0;">A new service request has been submitted for your service: **{{ $service->name_en }}**.
                                                            </p>
                                                            <p style="margin: 0;">Request Details: -</p>
                                                            <p style="margin: 0;">Requested by: {{ $requester->first_name_en }} {{ $requester->last_name_en }}</p>
                                                            <p style="margin: 0;">Email: {{ $requester->email }}</p>
                                                            <p style="margin: 0;">Organization: {{ !is_null( $organization ) ? $organization->name_en : '-' }}</p>
                                                            <p style="margin: 0;">Requested Date: {{ $request->date_time }}</p>
                                                            <p style="margin: 0;"> Service Description: {{ $service->description_en }}</p>
                                                            <p style="margin: 0;">Additional Notes: {{ $request->comment }}</p>
                                                            <p style="margin: 0;">Please Review the Request and Respond Accordingly.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
@endsection
