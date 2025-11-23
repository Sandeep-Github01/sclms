<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Decision</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f6f6; padding: 20px;">
    <div style="background: #ffffff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color: #333333;">{{ config('app.name', 'SCLMS') }}</h2>
        <p style="font-size: 16px;">Dear {{ $leave->user->name }},</p>
        <p style="font-size: 16px;">
            Your leave request has been
            <strong style="color: {{ $leave->status == 'approved' ? '#28a745' : '#dc3545' }}">
                {{ ucfirst($leave->status) }}
            </strong>.
        </p>
        <p style="font-size: 16px;">
            <strong>Leave Type:</strong> {{ $leave->leaveType->name }}<br>
            <strong>Date Range:</strong> {{ $leave->start_date->format('Y-m-d') }} to {{ $leave->end_date->format('Y-m-d') }}<br>
        </p>
        @if($leave->status == 'rejected' && $leave->status_note)
        <p style="font-size: 16px; color:#dc3545;">
            <strong>Reason for Rejection:</strong><br>
            {{ $leave->status_note }}
        </p>
        @endif
        <p style="font-size: 16px;">Regards,<br><strong>{{ config('app.name', 'SCLMS') }} Team</strong></p>
    </div>
</body>
</html>