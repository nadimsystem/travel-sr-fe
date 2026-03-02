<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $token;
    protected string $phoneId;
    protected string $verifyToken;

    public function __construct()
    {
        $this->token = env('WHATSAPP_TOKEN');
        $this->phoneId = env('PHONE_NUMBER_ID');
        $this->verifyToken = env('VERIFY_TOKEN');
    }

    /**
     * Verify the webhook via token.
     */
    public function verifyWebhook(string $token, string $challenge): string|bool
    {
        if ($token === $this->verifyToken) {
            return $challenge;
        }
        return false;
    }

    /**
     * Process incoming webhook payload.
     */
    public function handleIncomingMessage(array $payload): void
    {
        // Check if there is a message
        if (empty($payload['entry'][0]['changes'][0]['value']['messages'][0])) {
            return;
        }

        $message = $payload['entry'][0]['changes'][0]['value']['messages'][0];
        $from = $message['from']; // Phone number
        $type = $message['type'];

        Log::info("Incoming WhatsApp Message from $from: " . json_encode($message));

        // Logic Handler
        if ($type === 'text') {
            $body = strtolower(trim($message['text']['body']));
            $this->handleTextLogic($from, $body);
        } elseif ($type === 'image') {
            $this->sendMessage($from, "Wow, gambar yang bagus! Tapi saya belum bisa melihatnya.");
        } elseif ($type === 'location') {
            $this->sendMessage($from, "Terima kasih telah membagikan lokasi Anda.");
        } else {
            $this->sendMessage($from, "Maaf, format pesan ini belum didukung.");
        }
    }

    /**
     * Simple keyword-based logic handler.
     */
    protected function handleTextLogic(string $to, string $text): void
    {
        switch ($text) {
            case 'info':
                $response = "🤖 *Bot Info*\n\nIni adalah bot demo Laravel 11 untuk WhatsApp API.\nKetik *harga* untuk cek harga layanan.";
                break;
            case 'harga':
                $response = "💰 *Daftar Harga*\n\n1. Paket Basic: Rp 50.000\n2. Paket Premium: Rp 100.000\n3. Paket Sultan: Rp 500.000";
                break;
            case 'pesan':
                $response = "📝 Silakan balas dengan format:\nNama - Paket - Alamat";
                break;
            default:
                $response = "👋 Halo! Saya bot otomatis.\n\nKetik:\n- *info* untuk bantuan\n- *harga* untuk pricelist\n- *pesan* untuk order";
                break;
        }

        $this->sendMessage($to, $response);
    }

    /**
     * Send a text message via WhatsApp Business API.
     */
    public function sendMessage(string $to, string $text): void
    {
        $url = "https://graph.facebook.com/v21.0/{$this->phoneId}/messages";

        $response = Http::withToken($this->token)->post($url, [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'text',
            'text'              => [
                'preview_url' => false,
                'body'        => $text
            ]
        ]);

        if ($response->successful()) {
            Log::info("Message sent to $to");
        } else {
            Log::error("Failed to send message: " . $response->body());
        }
    }
}
