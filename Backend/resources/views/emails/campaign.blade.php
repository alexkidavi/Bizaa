<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaignSubject }}</title>
</head>

<body
    style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f7; margin: 0; padding: 0;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f7;">
        <tr>
            <td align="center" style="padding: 20px;">
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                       style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                    <tr>
                        <td style="padding: 28px; line-height: 1.6; color: #333333; font-size: 14px;">
                            {!! nl2br(e($body)) !!}
                        </td>
                    </tr>
                    <tr>
                        <td align="center"
                            style="padding: 16px 28px; margin-top: 20px; border-top: 1px solid #e0e0e0; font-size: 11px; color: #888888;">
                            <p style="margin: 0;">Sent via Bizaa CRM &middot; {{ date('Y') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
