<!DOCTYPE html>
<html>
<body>
    <h2>Your Leave Has Been Rejected</h2>

    <p>Hi {{ $user->name }},</p>

    <p>Your provisional medical leave (ID #{{ $leave->id }}) has been marked as <strong>abused</strong> because:</p>

    <ul>
        <li>You did not submit the required medical proof before the deadline.</li>
    </ul>

    <p>This leave has now been marked as <strong>rejected (abused)</strong> and penalty points may have been applied to your account.</p>

    <p>If you believe this was a mistake, please contact the admin.</p>

    <p>Regards,<br>Leave Management System</p>
</body>
</html>
