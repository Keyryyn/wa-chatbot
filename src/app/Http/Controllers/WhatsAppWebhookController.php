<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppWebhookController extends Controller
{
    public function __construct(private readonly WhatsAppCloudApiService $whatsAppCloudApiService)
    {
    }

    /**
     * Meta webhook verification endpoint (GET).
     *
     * Meta sends `hub.verify_token` and expects your app to return
     * `hub.challenge` when the token matches your configured VERIFY_TOKEN.
     */
    public function verify(Request $request): Response
    {
        $verifyToken = (string) config('services.whatsapp.verify_token');
        $mode = (string) $request->query('hub_mode', $request->query('hub.mode'));
        $token = (string) $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = (string) $request->query('hub_challenge', $request->query('hub.challenge'));

        if ($mode === 'subscribe' && hash_equals($verifyToken, $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Incoming WhatsApp webhook handler (POST).
     *
     * This method reads the payload sent by Meta, extracts simple text messages,
     * and replies to the sender with a basic echo confirmation.
     */
    public function receive(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('WhatsApp webhook payload received.', $payload);

        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];
                $messages = $value['messages'] ?? [];

                foreach ($messages as $message) {
                    $from = $message['from'] ?? null;
                    $type = $message['type'] ?? null;
                    $messageBody = $message['text']['body'] ?? null;

                    // Only reply to basic inbound text messages.
                    if ($from && $type === 'text' && $messageBody) {
                        $reply = "Thanks for your message: {$messageBody}";
                        $this->whatsAppCloudApiService->sendTextMessage($from, $reply);
                    }
                }
            }
        }

        // Return 200 quickly so Meta knows the webhook was accepted.
        return response()->json(['status' => 'ok']);
    }
}
