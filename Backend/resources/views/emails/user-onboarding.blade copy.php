<!DOCTYPE html>
<html>
<body>
    <h2>Welcome, {{ $user->name }}</h2>

    <p>Your account has been created successfully.</p>

    <p><strong>Login Email:</strong> {{ $user->email }}</p>
    <p><strong>Temporary Password:</strong> {{ $plainPassword }}</p>

    <p>Please log in and change your password immediately.</p>

    <br>
    <p>Regards,<br>Support Team</p>
</body>
</html>
