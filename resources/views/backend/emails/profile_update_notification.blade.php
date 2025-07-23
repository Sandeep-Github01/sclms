<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile Update Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            color: #333;
            text-align: left;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .details td {
            padding: 8px 0;
        }

        .details td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff !important;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>New User Profile Update</h2>
        <p>Hello Admin,</p>
        <p>A user has updated their profile and is awaiting your approval. Please review the details below:</p>

        <table class="details">
            <tr>
                <td>Name:</td>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td>Role:</td>
                <td>{{ ucfirst($user->role) }}</td>
            </tr>
            <tr>
                <td>Department:</td>
                <td>{{ $user->dept_name }}</td>
            </tr>
        </table>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('admin.user.profileReview', $user->id) }}" class="button">Review Profile</a>
        </div>

        <div class="footer">
            <small>&copy; {{ date('Y') }} SCLMS Admin Panel - All rights reserved.</small>
        </div>
    </div>
</body>

</html>
