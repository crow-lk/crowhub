<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Email' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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
        }
        .data-field {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .data-field label {
            font-weight: 600;
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
            <div class="email-header">
                <h1 class="email-subject">{{ $subject ?? 'Email' }}</h1>
            </div>
            <div class="email-body">
                {{-- Display all passed data as key-value pairs --}}
                @if(isset($data) && is_array($data))
                    @foreach($data as $key => $value)
                        @if(!is_array($value))
                        <div class="data-field">
                            <label>{{ ucfirst(str_replace('_', ' ', $key)) }}:</label> {{ $value }}
                        </div>
                        @endif
                    @endforeach
                @elseif(isset($content))
                    {{ $content }}
                @elseif(isset($message))
                    {{ $message }}
                @else
                    <p>No content provided.</p>
                @endif
            </div>
            <div class="email-footer">
                <p>This email was sent via CrowEmail API</p>
            </div>
        </div>
    </div>
</body>
</html>
