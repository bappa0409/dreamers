<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Set Your Password</title>
</head>

<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#334155;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 15px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px;background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;">

                    <tr>
                        <td style="background:#0d3b66;padding:24px;text-align:center;color:#ffffff;">
                            @if(setting('site_logo'))
                                <img src="{{ asset('storage/'.setting('site_logo')) }}" alt="{{ setting('organization_name', 'Dreamers Association') }}" style="max-height:40px;margin-bottom:8px;">
                            @endif
                            <h2 style="margin:0;font-size:22px;">{{ setting('organization_name', 'Dreamers Association') }}</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;">

                            <h3 style="margin-top:0;color:#1e293b;">
                                Welcome, {{ $user->name }}
                            </h3>

                            <p style="font-size:14px;line-height:1.7;">
                                Your {{ setting('organization_name', 'Dreamers Association') }} membership has been approved.
                            </p>

                            <p style="font-size:14px;line-height:1.7;">
                                Please set your account password using the button below.
                            </p>

                            <div style="text-align:center;margin:30px 0;">
                                <a href="{{ $setupUrl }}"
                                    style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;font-weight:bold;font-size:14px;">
                                    Set My Password
                                </a>
                            </div>

                            <p style="font-size:13px;color:#64748b;">
                                This link will expire in 24 hours.
                            </p>

                            <p style="font-size:13px;color:#64748b;">
                                If you did not expect this email, please contact {{ setting('organization_name', 'Dreamers Association') }} administration.
                            </p>

                            <hr style="border:none;border-top:1px solid #e2e8f0;margin:25px 0;">

                            <p style="font-size:11px;color:#94a3b8;word-break:break-all;">
                                {{ $setupUrl }}
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>