<x-email.layout :config="$tenantConfig" :emailSubject="$emailSubject">
    <div class="greeting" style="font-size: 18px; color: #374151; margin-bottom: 20px;">
        Hello {{ $user->name }},
    </div>

    <div class="message-body" style="font-size: 16px; color: #4b5563; white-space: pre-wrap; margin-bottom: 30px;">
        {{ $messageBody }}
    </div>
</x-email.layout>
