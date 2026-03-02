<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * GET: Verify Webhook (hub.verify_token)
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode && $token) {
            $verified = $this->whatsappService->verifyWebhook($token, $challenge);
            
            if ($verified) {
                Log::info("Webhook verified successfully.");
                return response($verified, 200);
            }
        }

        Log::warning("Webhook verification failed.");
        return response('Forbidden', 403);
    }

    /**
     * POST: Handle Incoming Messages
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        // Log raw payload for debugging (Important for Shared Hosting)
        Log::info("Webhook Received:", $payload);

        try {
            $this->whatsappService->handleIncomingMessage($payload);
        } catch (\Exception $e) {
            Log::error("Error handling webhook: " . $e->getMessage());
        }

        return response('EVENT_RECEIVED', 200);
    }
}
