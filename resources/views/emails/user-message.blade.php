<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }
        .greeting {
            font-size: 18px;
            color: #374151;
            margin-bottom: 20px;
        }
        .message-body {
            font-size: 16px;
            color: #4b5563;
            white-space: pre-wrap;
            margin-bottom: 30px;
        }
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .app-name {
            font-weight: bold;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
        </div>

        <div class="greeting">
            Hello {{ $user->name }},
        </div>

        <div class="message-body">{{ $messageBody }}</div>

        <div class="footer">
            <p>
                This email was sent from <span class="app-name">{{ config('app.name', 'Laravel') }}</span>
            </p>
            <p style="margin-top: 10px; font-size: 12px;">
                If you have any questions, please contact your administrator.
            </p>
        </div>
    </div>
</body>
</html>
