@props(['config'])

<div class="header" style="border-bottom: 3px solid {{ $config['primary_color'] }}; padding-bottom: 20px; margin-bottom: 30px;">
    @if($config['logo_url'])
        <div style="margin-bottom: 15px;">
            <img src="{{ $config['logo_url'] }}" alt="{{ $config['header_text'] }}" style="max-height: 60px; height: auto;">
        </div>
    @endif
    <h1 style="margin: 0; font-size: 24px; color: #1f2937;">
        {{ $config['header_text'] ?? config('app.name', 'Laravel') }}
    </h1>
</div>
