<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Bizaa</title>
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
                            <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 800; color: #1e293b;">
                                Welcome to Bizaa, {{ $user->name }} 🎉
                            </h1>
                            <p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.6;">
                                <strong style="color: #1682C4;">{{ $businessName }}</strong> is all set up, and your
                                14-day free trial has started — no card required.
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 0 32px 8px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color: #F5FAFF; border: 1px solid #D9EBF7; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0 0 12px 0; font-size: 12px; font-weight: 700; color: #1682C4; text-transform: uppercase; letter-spacing: 0.06em;">
                                            What you can do right away
                                        </p>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            @foreach ([
                                                'Ring up sales fast from the POS screen — cash, M-Pesa, card, split payments',
                                                'Track stock across every branch, with low-stock and out-of-stock alerts',
                                                'Manage customers, suppliers, purchases, invoices and quotations in one place',
                                                'See daily sales, profit and stock reports the moment you need them',
                                            ] as $item)
                                                <tr>
                                                    <td width="20" valign="top" style="padding: 4px 0;">
                                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background-color: #229DE7; margin-top: 7px;"></span>
                                                    </td>
                                                    <td style="padding: 4px 0; font-size: 13.5px; color: #334155; line-height: 1.6;">
                                                        {{ $item }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Setup steps -->
                    <tr>
                        <td style="padding: 16px 32px 8px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color: #FFFBF3; border: 1px solid #FBEACB; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0 0 14px 0; font-size: 12px; font-weight: 700; color: #D98C0F; text-transform: uppercase; letter-spacing: 0.06em;">
                                            Get set up in 5 steps
                                        </p>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            @foreach ([
                                                'Complete your business profile' => 'Add your logo, location and tax details under Settings → Business.',
                                                'Add your branch(es)' => 'Running more than one location? Set them up so stock and sales stay separate per branch.',
                                                'Set up categories & units' => 'Create the product categories and units (pcs, kg, litre…) you sell in.',
                                                'Add your products' => 'Add products one by one, or import your whole catalogue at once via CSV.',
                                                'Invite your staff' => 'Add your cashiers and managers, and give each one only the access they need.',
                                            ] as $title => $desc)
                                                <tr>
                                                    <td width="28" valign="top" style="padding: 6px 0;">
                                                        <span style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: #F5A623; color: #ffffff; font-size: 11px; font-weight: 800; text-align: center; line-height: 20px;">{{ $loop->iteration }}</span>
                                                    </td>
                                                    <td style="padding: 6px 0 6px 8px;">
                                                        <p style="margin: 0; font-size: 13.5px; font-weight: 700; color: #1e293b;">{{ $title }}</p>
                                                        <p style="margin: 2px 0 0 0; font-size: 12.5px; color: #64748b; line-height: 1.5;">{{ $desc }}</p>
                                                    </td>
                                                </tr>
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
                            <a href="https://bizaa.africa/login"
                               style="display: inline-block; background-color: #229DE7; color: #ffffff; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(34, 157, 231, 0.35);">
                                Log In to Bizaa
                            </a>
                            <p style="margin: 12px 0 0 0; font-size: 12px; color: #94a3b8;">
                                Log in with the email and password you just created — {{ $user->email }}
                            </p>
                        </td>
                    </tr>

                    <!-- Support -->
                    <tr>
                        <td style="padding: 0 32px 32px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="border-top: 1px solid #EEF5FB; padding-top: 20px;">
                                <tr><td style="padding-top: 20px; font-size: 13px; color: #64748b; text-align: center; line-height: 1.7;">
                                    Need a hand getting started? We're a real team, not a ticket queue.<br>
                                    <a href="https://wa.me/254705030613" style="color: #229DE7; text-decoration: none; font-weight: 600;">WhatsApp us</a>
                                    &nbsp;·&nbsp;
                                    <a href="mailto:brimsoftsolutions@gmail.com" style="color: #229DE7; text-decoration: none; font-weight: 600;">brimsoftsolutions@gmail.com</a>
                                    &nbsp;·&nbsp;
                                    <a href="tel:+254705030613" style="color: #229DE7; text-decoration: none; font-weight: 600;">+254 705 030 613</a>
                                </td></tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 24px; background-color: #F5FAFF; border-top: 1px solid #EEF5FB;">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                &copy; {{ date('Y') }} Bizaa by Brimsoft Solutions. All rights reserved.<br>
                                You're receiving this because {{ $businessName }} was just registered on Bizaa.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
