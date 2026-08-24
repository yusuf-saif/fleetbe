<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome, {{ $driver->name }}</title>
</head>
<body>
    <h2>Hello {{ $driver->name }},</h2>
    <p>Welcome onboard! Your driver account has been created successfully.</p>
    <p><strong>Email:</strong> {{ $driver->email }}</p>
    <p><strong>Temporary Password:</strong> {{ $password }}</p>
    <p>Please log in and change your password immediately on first login.</p>
    <br>
    <p>Best Regards,<br>The Team</p>
</body>
</html>
