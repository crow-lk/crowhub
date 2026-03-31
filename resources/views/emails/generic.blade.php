<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .email-subject {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }
        .email-body {
            font-size: 16px;
            color: #4a4a4a;
            white-space: pre-wrap;
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
        <div class="email-header">
            <h1 class="email-subject">{{ $subject }}</h1>
        </div>
        <div class="email-body">
            {!! nl2br(e($body)) !!}
        </div>
        <div class="email-footer">
            <p>This email was sent via CrowEmail API</p>
        </div>
    </div>
</body>
</html>
