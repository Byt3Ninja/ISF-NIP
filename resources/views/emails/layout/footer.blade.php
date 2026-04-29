                                            @if(!empty($org_website) || !empty($org_linkedin) || !empty($org_twitter) || !empty($org_facebook) || !empty($org_instagram))
                                            <table border="0" cellpadding="0" cellspacing="0" class="social_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                                                <tr>
                                                    <td class="pad" style="padding: 15px 40px 5px; text-align: center;">
                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="display: inline-table; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                            <tr>
                                                                @if(!empty($org_website))
                                                                <td style="padding: 0 8px;">
                                                                    <a href="{{ $org_website }}" target="_blank" style="display: inline-block;">
                                                                        <img src="{{ asset('images/email-icons/website.png') }}" alt="Website" width="24" height="24" style="border: 0; display: block;"/>
                                                                    </a>
                                                                </td>
                                                                @endif
                                                                @if(!empty($org_linkedin))
                                                                <td style="padding: 0 8px;">
                                                                    <a href="{{ $org_linkedin }}" target="_blank" style="display: inline-block;">
                                                                        <img src="{{ asset('images/email-icons/linkedin.png') }}" alt="LinkedIn" width="24" height="24" style="border: 0; display: block;"/>
                                                                    </a>
                                                                </td>
                                                                @endif
                                                                @if(!empty($org_facebook))
                                                                <td style="padding: 0 8px;">
                                                                    <a href="{{ $org_facebook }}" target="_blank" style="display: inline-block;">
                                                                        <img src="{{ asset('images/email-icons/facebook.png') }}" alt="Facebook" width="24" height="24" style="border: 0; display: block;"/>
                                                                    </a>
                                                                </td>
                                                                @endif
                                                                @if(!empty($org_twitter))
                                                                <td style="padding: 0 8px;">
                                                                    <a href="{{ str_starts_with($org_twitter, 'http') ? $org_twitter : 'https://x.com/' . ltrim($org_twitter, '@') }}" target="_blank" style="display: inline-block;">
                                                                        <img src="{{ asset('images/email-icons/twitter.png') }}" alt="X / Twitter" width="24" height="24" style="border: 0; display: block;"/>
                                                                    </a>
                                                                </td>
                                                                @endif
                                                                @if(!empty($org_instagram))
                                                                <td style="padding: 0 8px;">
                                                                    <a href="{{ $org_instagram }}" target="_blank" style="display: inline-block;">
                                                                        <img src="{{ asset('images/email-icons/instagram.png') }}" alt="Instagram" width="24" height="24" style="border: 0; display: block;"/>
                                                                    </a>
                                                                </td>
                                                                @endif
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                            @endif

                                            @if(!empty($org_address))
                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                                <tr>
                                                    <td class="pad" style="padding: 5px 40px;">
                                                        <div style="color:#95979c;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:11px;line-height:140%;text-align:center;">
                                                            <p style="margin: 0; word-break: break-word;">{{ $org_address }}</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                            @endif

                                            <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
                                                <tr>
                                                    <td class="pad" style="padding-bottom:30px;padding-left:40px;padding-right:40px;padding-top:20px;">
                                                        <div style="color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:12px;line-height:120%;text-align:center;mso-line-height-alt:14.399999999999999px;">
                                                            <p style="margin: 0; word-break: break-word;"><span style="color: #95979c;">{{ $platformName }} &copy; {{ date('Y') }}</span></p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table><!-- End -->
</body>
</html>
