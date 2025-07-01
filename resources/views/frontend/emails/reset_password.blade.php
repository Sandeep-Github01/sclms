<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; padding: 20px;">
    <div style="background: #fff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto;">
        <h2>Smart College Leave Management System</h2>
        <p>Dear {{ $user->name }},</p>
        <p>You recently requested to reset your password.</p>
        <p>Click the button below to reset your password:</p>

        <a href="{{ $resetUrl }}" style="display:inline-block; padding:10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;">
            Reset Password
        </a>

        <p>This link will expire in 60 minutes.</p>
        <p>If you didn’t request this, please ignore this email.</p>
        <br>
        <p>Regards,<br>Smart College LMS Team</p>
    </div>
</body>
</html>
