@props(['config'])

<div class="footer" style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px; font-size: 14px; color: #6b7280; text-align: center;">
    <p>
        This email was sent from <span class="app-name" style="font-weight: bold; color: {{ $config['primary_color'] }};">{{ $config['header_text'] ?? config('app.name', 'Laravel') }}</span>
    </p>
    @if($config['footer_text'])
        <p style="margin-top: 10px; font-size: 12px;">
            {{ $config['footer_text'] }}
        </p>
    @endif
    @if($config['support_email'])
        <p style="margin-top: 10px; font-size: 12px;">
            Questions? Contact us at <a href="mailto:{{ $config['support_email'] }}" style="color: {{ $config['primary_color'] }};">{{ $config['support_email'] }}</a>
        </p>
    @endif
</div>
