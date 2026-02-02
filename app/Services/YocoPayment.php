<?php
/**
 * Yoco Payment Service
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Services;

class YocoPayment
{
    private string $publicKey;
    private string $secretKey;
    private string $endpoint;
    private bool $sandbox;

    public function __construct()
    {
        $config = config('payment.gateways.yoco');
        $this->sandbox = $config['sandbox'] ?? true;
        $this->publicKey = $config['public_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->endpoint = $this->sandbox
            ? $config['endpoints']['sandbox']
            : $config['endpoints']['live'];
    }

    /**
     * Charge a card using a token
     */
    public function charge(array $data): array
    {
        $payload = [
            'token' => $data['token'],
            'amountInCents' => $data['amountInCents'],
            'currency' => $data['currency'] ?? 'ZAR',
        ];

        if (!empty($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }

        $response = $this->request('charges', $payload);

        if (isset($response['id']) && $response['status'] === 'successful') {
            return [
                'success' => true,
                'transaction_id' => $response['id'],
                'data' => $response,
            ];
        }

        return [
            'success' => false,
            'message' => $response['displayMessage'] ?? $response['errorMessage'] ?? 'Payment failed',
            'data' => $response,
        ];
    }

    /**
     * Refund a charge
     */
    public function refund(string $chargeId, ?int $amountInCents = null): array
    {
        $payload = [];

        if ($amountInCents) {
            $payload['amountInCents'] = $amountInCents;
        }

        $response = $this->request("charges/$chargeId/refunds", $payload);

        if (isset($response['id'])) {
            return [
                'success' => true,
                'refund_id' => $response['id'],
                'data' => $response,
            ];
        }

        return [
            'success' => false,
            'message' => $response['displayMessage'] ?? 'Refund failed',
            'data' => $response,
        ];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $webhookSecret = config('payment.gateways.yoco.webhook_secret', '');

        if (empty($webhookSecret)) {
            logMessage('warning', 'Yoco webhook secret not configured');
            return true; // Allow in development
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Make API request
     */
    private function request(string $endpoint, array $data): array
    {
        $url = rtrim($this->endpoint, '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->secretKey,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            logMessage('error', 'Yoco API request failed', ['error' => $error]);
            return ['errorMessage' => 'Connection error'];
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            logMessage('error', 'Yoco API response parse error', ['response' => $response]);
            return ['errorMessage' => 'Invalid response'];
        }

        logMessage('info', 'Yoco API response', [
            'endpoint' => $endpoint,
            'http_code' => $httpCode,
            'response' => $result,
        ]);

        return $result;
    }

    /**
     * Get public key for frontend
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}
