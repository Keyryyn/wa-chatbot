<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    public function sendTextMessage(string $to, string $message): void
    {
        $token = (string) config('services.whatsapp.token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');

        if ($token === '' || $phoneNumberId === '') {
            Log::warning('WhatsApp token/phone number ID missing; skip sending message.', [
                'to' => $to,
                'message' => $message,
            ]);

            return;
        }

        $url = sprintf('https://graph.facebook.com/v22.0/%s/messages', $phoneNumberId);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Failed to send WhatsApp message', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }
    }
}
