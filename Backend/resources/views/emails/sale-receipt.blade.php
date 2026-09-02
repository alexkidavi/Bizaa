<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your receipt</title>
</head>

<body
    style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; background-color: #F0F6FB; margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F0F6FB;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="width: 600px; max-width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(34, 157, 231, 0.08);">

                    <tr>
                        <td style="height: 4px; background-color: #229DE7; background-image: linear-gradient(90deg, #229DE7, #5BB8F5, #229DE7); font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 32px 24px 8px 24px;">
                            <img src="{{ $message->embed(public_path('bizaa-logo.png')) }}" alt="{{ $businessName }}"
                                 width="140" style="max-width: 140px; height: auto; display: block;">
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 8px 32px 24px 32px;">
                            <h1 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 800; color: #1e293b;">
                                Thank you for shopping with {{ $businessName }}
                            </h1>
                            <p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.6;">
                                Your receipt for <strong style="color: #1682C4;">#{{ $sale->reference }}</strong> is attached to this email as a PDF.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 0 32px 8px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color: #F5FAFF; border: 1px solid #D9EBF7; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 18px 22px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 12.5px; color: #64748b; font-weight: 600; padding: 4px 0;">Date</td>
                                                <td align="right" style="font-size: 13.5px; color: #1e293b; font-weight: 600; padding: 4px 0;">{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 12.5px; color: #64748b; font-weight: 600; padding: 4px 0;">Total</td>
                                                <td align="right" style="font-size: 13.5px; color: #1e293b; font-weight: 700; padding: 4px 0;">KES {{ number_format((float) $sale->total, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 20px 24px; background-color: #F5FAFF; border-top: 1px solid #EEF5FB;">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                &copy; {{ date('Y') }} {{ $businessName }} · Sent via Bizaa POS.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
