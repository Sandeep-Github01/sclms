<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Document Uploaded</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f6f6f6; padding: 20px;">
    <div style="background: #ffffff; padding: 30px; border-radius: 5px; max-width: 600px; margin: auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color: #333333;">{{ config('app.name', 'SCLMS') }}</h2>

        <p style="font-size: 16px;">Dear Admin,</p>

        <p style="font-size: 16px;">
            <strong>{{ $leave->user->name }}</strong> has uploaded the medical document for
            provisional leave #{{ $leave->id }}.
        </p>

        <p style="font-size: 16px;">
            <strong>Leave Type:</strong> {{ $leave->leaveType->name }}<br>
            <strong>Dates:</strong>
            {{ \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') }} to
            {{ \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}<br>
            <strong>Uploaded at:</strong> {{ now()->format('Y-m-d H:i') }}
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('admin.leaves.show', $leave->id) }}" style="display: inline-block; padding: 12px 25px; background-color: #4CAF50; color: #ffffff;
                      text-decoration: none; border-radius: 4px; font-weight: bold;">
                Review Document
            </a>
        </p>

        <p style="font-size: 16px;">Regards,<br><strong>{{ config('app.name', 'SCLMS') }} Team</strong></p>
    </div>
</body>

</html>