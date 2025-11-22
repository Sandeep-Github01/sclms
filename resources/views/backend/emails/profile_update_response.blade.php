<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Update Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; padding: 20px;">
    <div style="background: #ffffff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color: #333333;">{{ config('app.name', 'Smart College LMS') }}</h2>
        <p style="font-size: 16px;">Dear {{ $user->name }},</p>
        <p style="font-size: 16px;">
            This is to inform you that your recent profile update has been
            <strong style="color: {{ $approved ? '#28a745' : '#dc3545' }};">
                {{ $approved ? 'approved' : 'rejected' }}
            </strong>
            by the admin.
        </p>
        <p style="font-size: 16px;">
            Reason for declining: {{ nl2br(e($messageContent)) }}
        </p>
        @if (!$approved)
            <p style="font-size: 16px;">You may log in and re-submit the required changes.</p>
        @endif
        <br>
        <p style="font-size: 16px;">Regards,<br><strong>{{ config('app.name', 'Smart College LMS Team') }}</strong></p>
    </div>
</body>
</html>