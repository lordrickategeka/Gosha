<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to GarageHQ</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f5f7; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background-color: #2C72B3; padding: 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .header p { color: rgba(255,255,255,0.8); margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px; }
        .body h2 { margin-top: 0; font-size: 20px; }
        .credentials { background: #f4f5f7; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .credentials p { margin: 6px 0; font-size: 14px; }
        .credentials .label { color: #666; }
        .credentials .value { font-weight: 600; color: #333; }
        .btn { display: inline-block; background-color: #2C72B3; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; margin: 20px 0; border-radius: 0 6px 6px 0; font-size: 13px; }
        .footer { padding: 24px 32px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GarageHQ</h1>
            <p>Garage Management Platform</p>
        </div>
        <div class="body">
            <h2>Welcome, {{ $owner->name }}!</h2>
            <p>Your organization <strong>{{ $vendor->name }}</strong> has been set up on GarageHQ. Below are your login credentials.</p>

            <div class="credentials">
                <p><span class="label">Login URL:</span> <span class="value">{{ url('/login') }}</span></p>
                <p><span class="label">Email:</span> <span class="value">{{ $owner->email }}</span></p>
                <p><span class="label">Temporary Password:</span> <span class="value">{{ $temporaryPassword }}</span></p>
            </div>

            <div class="warning">
                <strong>Important:</strong> Please change your password immediately after your first login. This temporary password will expire once changed.
            </div>

            <p style="text-align: center; margin-top: 28px;">
                <a href="{{ url('/login') }}" class="btn">Sign In Now</a>
            </p>

            <p style="margin-top: 24px; font-size: 14px;">
                Your account comes with a <strong>14-day free trial</strong>. If you have any questions, feel free to reach out to our support team.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} GarageHQ. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
