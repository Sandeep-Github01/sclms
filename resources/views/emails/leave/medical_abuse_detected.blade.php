<!DOCTYPE html>
<html>
<body>
    <h2>⚠️ Medical Leave Abuse Detected</h2>

    <p>Dear Admin,</p>

    <p>The system detected a medical leave abuse for the following user:</p>

    <ul>
        <li><strong>User:</strong> {{ $user->name }} ({{ $user->email }})</li>
        <li><strong>Leave ID:</strong> #{{ $leave->id }}</li>
        <li><strong>Date:</strong> {{ $leave->start_date }} → {{ $leave->end_date }}</li>
        <li><strong>Reason Code:</strong> medical_provisional_abuse</li>
    </ul>

    <p>The user did not upload the required medical document before the system deadline.</p>

    <p>
        You may review this leave request and take additional action if required.
    </p>

    <p>Regards,<br>Leave Management System</p>
</body>
</html>
