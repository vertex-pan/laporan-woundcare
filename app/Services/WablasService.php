<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WablasService
{
    /**
     * Send a WhatsApp message via Wablas.
     *
     * @param string $target Phone number (e.g. 081234567890 or 628123456789)
     * @param string $message Text message content
     * @return array
     */
    public function send(string $target, string $message): array
    {
        $host = env('WABLAS_API_HOST', 'https://solo.wablas.com');
        $token = env('WABLAS_TOKEN');
        $secretKey = env('WABLAS_SECRET_KEY');

        if (empty($token)) {
            Log::warning('Wablas API Token is not configured. WhatsApp message not sent.', [
                'target' => $target,
                'message' => $message,
            ]);
            return [
                'status' => false,
                'reason' => 'Wablas token not configured'
            ];
        }

        // Format phone number to international format (62...)
        if (str_starts_with($target, '08')) {
            $target = '62' . substr($target, 1);
        }

        // Remove any non-numeric characters (plus, spaces, dashes)
        $target = preg_replace('/[^0-9]/', '', $target);

        // API Endpoint
        $endpoint = rtrim($host, '/') . '/api/send-message';

        try {
            $headers = [];
            $cleanSecret = trim($secretKey ?? '');
            if (!empty($cleanSecret) && strcasecmp($cleanSecret, 'No Secret Key Available') !== 0 && strcasecmp($cleanSecret, 'none') !== 0) {
                $headers['Authorization'] = $token . '.' . $cleanSecret;
            } else {
                $headers['Authorization'] = $token;
            }

            $response = Http::withHeaders($headers)
                ->asForm()
                ->post($endpoint, [
                    'phone' => $target,
                    'message' => $message,
                ]);

            $result = $response->json();

            Log::info('Wablas API response', [
                'target' => $target,
                'response' => $result,
                'status_code' => $response->status()
            ]);

            return [
                'status' => $response->successful() && ($result['status'] ?? false),
                'response' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Wablas API error: ' . $e->getMessage(), [
                'target' => $target,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'reason' => $e->getMessage()
            ];
        }
    }
}
