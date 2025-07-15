<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f6f6f6; padding: 20px;">
    <div
        style="background: #ffffff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color: #333333;">Smart College Leave Management System - Admin</h2>

        <p style="font-size: 16px;">Dear {{ $admin->name }},</p>

        <p style="font-size: 16px;">
            We received a request to reset your admin password. Click the button below to proceed:
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}"
                style="display: inline-block; padding: 12px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Reset Password
            </a>
        </p>

        <p style="font-size: 14px; color: #555555;">
            This password reset link will expire in 60 minutes.
        </p>

        <p style="font-size: 14px; color: #555555;">
            If you did not request a password reset, no further action is required.
        </p>

        <br>

        <p style="font-size: 16px;">Regards,<br><strong>Smart College LMS Team</strong></p>
    </div>
</body>

</html>
