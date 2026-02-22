<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// WhatsApp webhook endpoint used by Meta for both verification (GET) and events (POST).
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'receive'])
    ->withoutMiddleware(VerifyCsrfToken::class);
