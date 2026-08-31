<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New business registered on Bizaa</title>
</head>

<body
    style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; background-color: #F0F6FB; margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F0F6FB;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="width: 600px; max-width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(34, 157, 231, 0.08);">

                    <!-- Accent bar -->
                    <tr>
                        <td style="height: 4px; background-color: #229DE7; background-image: linear-gradient(90deg, #229DE7, #5BB8F5, #229DE7); font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding: 32px 24px 8px 24px;">
                            <img src="{{ $message->embed(public_path('bizaa-logo.png')) }}" alt="Bizaa"
                                 width="150" style="max-width: 150px; height: auto; display: block;">
                        </td>
                    </tr>

                    <!-- Hero -->
                    <tr>
                        <td align="center" style="padding: 8px 32px 24px 32px;">
                            <p style="margin: 0 0 8px 0; display: inline-block; background-color: #EAF5FD; color: #1682C4; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; padding: 4px 12px; border-radius: 100px;">
                                New signup
                            </p>
                            <h1 style="margin: 8px 0 0 0; font-size: 20px; font-weight: 800; color: #1e293b;">
                                {{ $business->business_name }} just registered
                            </h1>
                        </td>
                    </tr>

                    <!-- Business details -->
                    <tr>
                        <td style="padding: 0 32px 8px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color: #F5FAFF; border: 1px solid #D9EBF7; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0 0 14px 0; font-size: 12px; font-weight: 700; color: #1682C4; text-transform: uppercase; letter-spacing: 0.06em;">
                                            Business details
                                        </p>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            @foreach ([
                                                ['Business name', $business->business_name],
                                                ['Industry', $business->business_industry],
                                                ['Location', $business->business_location],
                                                ['Business email', $business->business_email],
                                                ['Business phone', $business->business_phone],
                                                ['Owner name', $owner->name],
                                                ['Owner email', $owner->email],
                                                ['Owner phone', $owner->phone],
                                                ['Registered via', $business->source ? ucfirst($business->source) : 'Unknown'],
                                                ['Registered at', $business->created_at?->format('d M Y, H:i')],
                                            ] as [$label, $value])
                                                @if($value)
                                                    <tr>
                                                        <td width="140" valign="top" style="padding: 6px 0; font-size: 12.5px; color: #64748b; font-weight: 600;">
                                                            {{ $label }}
                                                        </td>
                                                        <td style="padding: 6px 0; font-size: 13.5px; color: #1e293b; font-weight: 600;">
                                                            {{ $value }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                        <td align="center" style="padding: 28px 32px;">
                            <a href="{{ config('app.url') }}/superadmin/businesses"
                               style="display: inline-block; background-color: #229DE7; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(34, 157, 231, 0.35);">
                                View in Super Admin
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 24px; background-color: #F5FAFF; border-top: 1px solid #EEF5FB;">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                &copy; {{ date('Y') }} Bizaa by Brimsoft Solutions. All rights reserved.<br>
                                You're receiving this because you're listed as a super-admin contact on Bizaa.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
