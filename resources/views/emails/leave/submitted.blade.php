<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Submitted</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f6f6f6; padding: 20px;">
    <div
        style="background: #ffffff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);">

        <h2 style="color: #333333;">Smart College Leave Management System</h2>

        <p style="font-size: 16px;">Dear {{ $leave->user->name }},</p>

        <p style="font-size: 16px;">
            Your leave request has been successfully submitted and is currently under review.
        </p>

        <p style="font-size: 16px;">
            <strong>Leave Type:</strong> {{ $leave->leaveType->name }}<br>
            <strong>Dates:</strong> {{ $leave->start_date }} to {{ $leave->end_date }}
        </p>

        <p style="font-size: 16px;">You will be notified when a decision is made.</p>

        <p style="font-size: 16px;">Regards,<br><strong>Smart College LMS Team</strong></p>

    </div>
</body>

</html>
