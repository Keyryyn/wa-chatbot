<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    /**
     * Send a plain text message using WhatsApp Cloud API.
     */
    public function sendTextMessage(string $to, string $body): void
    {
        $token = (string) config('services.whatsapp.token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');

        if ($token === '' || $phoneNumberId === '') {
            Log::warning('WhatsApp config is missing (token or phone_number_id).');

            return;
        }

        $url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/messages";

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $body,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('WhatsApp Cloud API send failed.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }
    }
}
