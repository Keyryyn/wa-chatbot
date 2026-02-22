# Laravel 12 WhatsApp Cloud API Webhook Chatbot

This repository contains a Laravel 12 boilerplate configured for WhatsApp Cloud API webhook callbacks and simple FAQ auto-replies.

## Requirements

- PHP **8.2+** (project `composer.json` requires `^8.2`)
- Composer 2+
- Docker + Docker Compose (optional, recommended)

## Setup (Local / Native)

1. Copy env:
   ```bash
   cp .env.example .env
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Generate app key:
   ```bash
   php artisan key:generate
   ```
4. Run development server:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

App will be available at `http://localhost:8000`.

## Setup (Docker Compose)

1. Copy env:
   ```bash
   cp .env.example .env
   ```
2. Start containers:
   ```bash
   docker compose up --build
   ```
3. Open app:
   - `http://localhost:8000`

## WhatsApp Webhook Endpoints

- Verification (GET): `/webhook/whatsapp`
- Callback (POST): `/webhook/whatsapp`

## WhatsApp Environment Variables

Set these in `.env`:

- `WHATSAPP_TOKEN` - Meta access token for Cloud API
- `WHATSAPP_PHONE_NUMBER_ID` - WhatsApp phone number ID
- `WHATSAPP_VERIFY_TOKEN` - custom token for webhook verification

## ngrok for Webhook Testing

Expose your local server:

```bash
ngrok http 8000
```

Then configure webhook URL in Meta dashboard as:

```text
https://<your-ngrok-subdomain>.ngrok-free.app/webhook/whatsapp
```

Use the same value as `WHATSAPP_VERIFY_TOKEN` during Meta verification setup.

## FAQ Auto Reply Logic

Controller checks incoming text and replies with simple rules:

- `jam buka` / `buka jam`
- `alamat` / `lokasi`
- `harga` / `biaya`
- fallback response for other questions

## Notes

- Incoming payloads are logged using Laravel logging (`storage/logs`).
- Outgoing WhatsApp messages are sent through a service class using Laravel HTTP client.
- Optional helper packages can be added later, but current setup uses native HTTP calls for clarity.
