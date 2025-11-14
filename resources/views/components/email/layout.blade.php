@props(['config', 'emailSubject'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
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
            padding: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .banner-image {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
        }
        .email-content {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        @if($config['banner_image_url'] ?? null)
            <img src="{{ $config['banner_image_url'] }}" alt="Banner" class="banner-image">
        @endif

        <div class="email-content">
            <x-email.header :config="$config" />

            {{ $slot }}

            <x-email.footer :config="$config" />
        </div>
    </div>
</body>
</html>
