<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <title></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:PixelsPerInch>96</o:PixelsPerInch>
            <o:AllowPNG/>
        </o:OfficeDocumentSettings>
    </xml><![endif]--><!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900"
          rel="stylesheet" type="text/css"/><!--<![endif]-->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
        }

        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: inherit !important;
        }

        #MessageViewBody a {
            color: inherit;
            text-decoration: none;
        }

        p {
            line-height: inherit
        }

        .desktop_hide,
        .desktop_hide table {
            mso-hide: all;
            display: none;
            max-height: 0px;
            overflow: hidden;
        }

        .image_block img + div {
            display: none;
        }

        @media (max-width: 660px) {
            .desktop_hide table.icons-inner {
                display: inline-block !important;
            }

            .icons-inner {
                text-align: center;
            }

            .icons-inner td {
                margin: 0 auto;
            }

            .mobile_hide {
                display: none;
            }

            .row-content {
                width: 100% !important;
            }

            .stack .column {
                width: 100%;
                display: block;
            }

            .mobile_hide {
                min-height: 0;
                max-height: 0;
                max-width: 0;
                overflow: hidden;
                font-size: 0px;
            }

            .desktop_hide,
            .desktop_hide table {
                display: table !important;
                max-height: none !important;
            }

            .email-container {
                width: 100% !important;
                padding: 20px 10px !important;
            }

            .content-wrapper {
                padding: 30px 20px !important;
            }

            .cta-button {
                padding: 14px 24px !important;
                font-size: 15px !important;
            }
        }
    </style>
</head>

<body class="body"
      style="background-color: #f8f8f9; margin: 0; padding: 0; -webkit-text-size-adjust: none; text-size-adjust: none; font-family: 'Montserrat', Arial, sans-serif;">
    
    <!-- Email Container -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f8f9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Content Card -->
                <table class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07); overflow: hidden; max-width: 600px;">
                    
                    <!-- Header with Brand Color -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 40px 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">
                                {{ config('app.name') }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td class="content-wrapper" style="padding: 40px 50px;">
                            
                            <!-- Greeting -->
                            <h2 style="margin: 0 0 24px; color: #1f2937; font-size: 24px; font-weight: 600; line-height: 1.3;">
                                Hello {{ $name }},
                            </h2>

                            <!-- Message Body -->
                            <p style="margin: 0 0 24px; color: #4b5563; font-size: 16px; line-height: 1.6;">
                                {{ $messageBody }}
                            </p>

                            <!-- Invitation Scope -->
                            <div style="background-color: #f3f4f6; border-left: 4px solid #16a34a; padding: 16px 20px; margin: 0 0 24px; border-radius: 4px;">
                                <p style="margin: 0; color: #374151; font-size: 15px; line-height: 1.5;">
                                    <strong style="color: #1f2937; font-weight: 600;">Invitation Scope:</strong> 
                                    <span style="color: #16a34a; font-weight: 500;">{{ $scope }}</span>
                                </p>
                            </div>

                            <!-- Organizations List (Conditional) -->
                            @if($scope == 'Organization' && !empty($organizations))
                                <div style="margin: 0 0 32px;">
                                    <p style="margin: 0 0 12px; color: #1f2937; font-size: 16px; font-weight: 600;">
                                        Organizations:
                                    </p>
                                    <ul style="margin: 0; padding: 0; list-style: none;">
                                        @foreach($organizations as $org)
                                            <li style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 12px 16px; margin-bottom: 8px; border-radius: 6px; color: #374151; font-size: 15px;">
                                                {{ $org['name'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Call to Action Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 32px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ env('APP_URL') }}/register?id=MentorRole"
                                           class="cta-button"
                                           style="display: inline-block; padding: 16px 32px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 6px rgba(22, 163, 74, 0.25); transition: all 0.3s ease;">
                                            {{ __('admin.Accept_Invation') }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Signature -->
                            <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                                <p style="margin: 0 0 4px; color: #6b7280; font-size: 15px; line-height: 1.5;">
                                    Best regards,
                                </p>
                                <p style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 600; line-height: 1.5;">
                                    {{ config('app.name') }}
                                </p>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #9ca3af; font-size: 13px; line-height: 1.5;">
                                This is an automated message. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>