<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextbeeService
{
    private ?string $apiKey;
    private ?string $deviceId;
    private bool $enabled;
    private string $baseUrl = 'https://api.textbee.dev/api/v1/gateway/devices';

    public function __construct()
    {
        $settings = Setting::where('group', 'sms')
            ->get()
            ->pluck('value', 'key');

        $this->apiKey = $settings->get('textbee_api_key');
        $this->deviceId = $settings->get('textbee_device_id');
        $this->enabled = ($settings->get('sms_provider') === 'textbee')
            && ($settings->get('sms_enabled') === 'true' || $settings->get('sms_enabled') === '1')
            && $this->apiKey
            && $this->deviceId;
    }

    public function sendSms(string $phone, string $message): bool
    {
        if (!$this->enabled) {
            Log::info('Textbee disabled or not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$this->deviceId}/send-sms", [
                'recipients' => [$phone],
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->logMessage($phone, $message, 'sent', $data['id'] ?? null);
                return true;
            }

            Log::error('Textbee send failed', ['status' => $response->status(), 'body' => $response->body()]);
            $this->logMessage($phone, $message, 'failed');
            return false;
        } catch (\Exception $e) {
            Log::error("Textbee send exception: {$e->getMessage()}");
            $this->logMessage($phone, $message, 'failed');
            return false;
        }
    }

    public function sendSmsWithDetails(string $phone, string $message): array
    {
        if (!$this->enabled) {
            $reason = 'Textbee غير مفعّل أو لم يتم إعداد المفتاح والجهاز';
            if (!$this->apiKey) $reason = 'مفتاح API الخاص بـ Textbee غير موجود';
            if (!$this->deviceId) $reason = 'Device ID الخاص بـ Textbee غير موجود';
            return ['success' => false, 'error' => $reason];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$this->deviceId}/send-sms", [
                'recipients' => [$phone],
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->logMessage($phone, $message, 'sent', $data['id'] ?? null);
                return ['success' => true, 'error' => null];
            }

            $body = $response->body();
            $errorMsg = "HTTP {$response->status()}";
            $json = $response->json();
            if ($json && isset($json['error'])) {
                $errorMsg .= ': ' . $json['error'];
            } elseif ($json && isset($json['message'])) {
                $errorMsg .= ': ' . $json['message'];
            } else {
                $errorMsg .= ': ' . substr($body, 0, 200);
            }

            Log::error("Textbee send failed: {$errorMsg}");
            $this->logMessage($phone, $message, 'failed');
            return ['success' => false, 'error' => $errorMsg];
        } catch (\Exception $e) {
            Log::error("Textbee send exception: {$e->getMessage()}");
            $this->logMessage($phone, $message, 'failed');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $phones, string $message): array
    {
        $results = [];
        foreach ($phones as $phone) {
            $results[$phone] = $this->sendSms($phone, $message);
        }
        return $results;
    }

    public function fetchReceivedSms(): array
    {
        if (!$this->enabled) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("{$this->baseUrl}/{$this->deviceId}/get-received-sms", [
                'page' => 1,
                'limit' => 100,
            ]);

            if ($response->successful()) {
                $body = $response->json() ?? [];
                $messages = $body['data'] ?? $body;
                $saved = [];

                foreach ($messages as $msg) {
                    $refId = $msg['_id'] ?? $msg['id'] ?? null;
                    $sender = $msg['sender'] ?? null;
                    $text = $msg['message'] ?? '';
                    $receivedAt = $msg['receivedAt'] ?? $msg['received_at'] ?? now();

                    if ($refId && SmsMessage::where('reference_id', $refId)->exists()) {
                        continue;
                    }

                    SmsMessage::create([
                        'phone' => $sender,
                        'message' => $text,
                        'status' => 'received',
                        'direction' => 'incoming',
                        'reference_id' => $refId,
                        'sender' => $sender,
                        'received_at' => $receivedAt,
                        'sent_at' => now(),
                    ]);

                    $saved[] = $msg;
                }

                return $saved;
            }

            Log::error('Textbee fetch received failed', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error("Textbee fetch exception: {$e->getMessage()}");
            return [];
        }
    }

    private function logMessage(string $phone, string $message, string $status, ?string $referenceId = null): void
    {
        SmsMessage::create([
            'phone' => $phone,
            'message' => $message,
            'status' => $status,
            'direction' => 'outgoing',
            'reference_id' => $referenceId,
            'sent_at' => now(),
        ]);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
