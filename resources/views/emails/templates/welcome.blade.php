<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-content {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .welcome-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .welcome-title {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .welcome-subtitle {
            font-size: 16px;
            color: #666;
        }
        .user-info {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .user-info p {
            margin: 5px 0;
        }
        .user-info strong {
            color: #1a1a1a;
        }
        .email-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-content">
            <div class="welcome-header">
                <div class="welcome-icon">👋</div>
                <h1 class="welcome-title">Welcome!</h1>
                <p class="welcome-subtitle">Thank you for joining us</p>
            </div>

            <div class="user-info">
                <p><strong>Name:</strong> {{ $name ?? 'User' }}</p>
                <p><strong>Email:</strong> {{ $email ?? '' }}</p>
                @if(isset($username))
                <p><strong>Username:</strong> {{ $username }}</p>
                @endif
            </div>

            <p>We're excited to have you on board. Get started by exploring our platform.</p>

            <div class="email-footer">
                <p>This email was sent via CrowEmail API</p>
            </div>
        </div>
    </div>
</body>
</html>
