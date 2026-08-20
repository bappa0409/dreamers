<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#334155;">
    <div style="max-width:640px;margin:30px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;">
            <h2 style="margin:0;font-size:20px;color:#0f172a;">
                {{ setting('organization_name','Association') }}
            </h2>
        </div>

        <div style="padding:24px;font-size:14px;line-height:1.7;">
            {!! nl2br(e($body)) !!}
        </div>

        <div style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
            This is an automated email from
            {{ setting('organization_name','Association') }}.
        </div>
    </div>
</body>
</html>