<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(private readonly WhatsAppCloudApiService $whatsAppService)
    {
    }

    /**
     * Meta Webhook Verification (GET).
     */
    public function verify(Request $request): Response
    {
        $verifyToken = (string) config('services.whatsapp.verify_token');
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        if ($mode === 'subscribe' && hash_equals($verifyToken, (string) $token)) {
            return response((string) $challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Invalid verification token.', 403);
    }

    /**
     * Incoming webhook callback (POST).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('WhatsApp webhook payload received', $payload);

        $messages = Arr::get($payload, 'entry.0.changes.0.value.messages', []);

        foreach ($messages as $message) {
            if (($message['type'] ?? null) !== 'text') {
                continue;
            }

            $from = (string) ($message['from'] ?? '');
            $text = mb_strtolower(trim((string) Arr::get($message, 'text.body', '')));

            if ($from === '' || $text === '') {
                continue;
            }

            $reply = $this->buildFaqReply($text);
            $this->whatsAppService->sendTextMessage($from, $reply);
        }

        return response()->json(['status' => 'ok']);
    }

    private function buildFaqReply(string $text): string
    {
        return match (true) {
            str_contains($text, 'jam buka'), str_contains($text, 'buka jam') =>
                'Jam buka kami Senin - Jumat, 09.00 - 17.00 WIB.',
            str_contains($text, 'alamat'), str_contains($text, 'lokasi') =>
                'Alamat kami di Jl. Contoh No. 123, Jakarta.',
            str_contains($text, 'harga'), str_contains($text, 'biaya') =>
                'Untuk informasi harga terbaru, mohon sebutkan produk yang Anda butuhkan.',
            default =>
                "Terima kasih sudah menghubungi kami.\n".
                "Pertanyaan yang bisa dijawab cepat:\n".
                "- Jam buka\n- Alamat\n- Harga",
        };
    }
}
