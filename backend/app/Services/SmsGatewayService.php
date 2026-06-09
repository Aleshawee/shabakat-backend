<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    private ?string $url;
    private ?string $username;
    private ?string $password;
    private ?int $simNumber;
    private bool $enabled;

    public function __construct()
    {
        $settings = Setting::where('group', 'sms')
            ->get()
            ->pluck('value', 'key');

        $this->username = $settings->get('smsgateway_username');
        $this->password = $settings->get('smsgateway_password');
        $this->url = $settings->get('smsgateway_url') ?: 'https://api.sms-gate.app/3rdparty/v1/messages';
        $this->simNumber = $settings->get('smsgateway_sim_number') ? (int) $settings->get('smsgateway_sim_number') : null;
        $this->enabled = ($settings->get('sms_provider') === 'smsgateway')
            && ($settings->get('sms_enabled') === 'true' || $settings->get('sms_enabled') === '1')
            && $this->username
            && $this->password;
    }

    private function requestBody(string $phone, string $message): array
    {
        $body = [
            'textMessage' => ['text' => $message],
            'phoneNumbers' => [$phone],
        ];
        if ($this->simNumber !== null) {
            $body['simNumber'] = $this->simNumber;
        }
        return $body;
    }

    public function sendSms(string $phone, string $message): bool
    {
        if (!$this->enabled) {
            Log::info('SMS Gateway disabled or not configured');
            return false;
        }

        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->url, $this->requestBody($phone, $message));

            if ($response->successful()) {
                $data = $response->json();
                $this->logMessage($phone, $message, 'sent', $data['id'] ?? null);
                return true;
            }

            Log::error('SMS Gateway send failed', ['status' => $response->status(), 'body' => $response->body()]);
            $this->logMessage($phone, $message, 'failed');
            return false;
        } catch (\Exception $e) {
            Log::error("SMS Gateway send exception: {$e->getMessage()}");
            $this->logMessage($phone, $message, 'failed');
            return false;
        }
    }

    public function sendSmsWithDetails(string $phone, string $message): array
    {
        if (!$this->enabled) {
            $reason = 'SMS Gateway غير مفعّل أو لم يتم إعداد البيانات';
            if (!$this->username) $reason = 'اسم المستخدم غير موجود';
            if (!$this->password) $reason = 'كلمة المرور غير موجودة';
            return ['success' => false, 'error' => $reason];
        }

        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->url, $this->requestBody($phone, $message));

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

            Log::error("SMS Gateway send failed: {$errorMsg}");
            $this->logMessage($phone, $message, 'failed');
            return ['success' => false, 'error' => $errorMsg];
        } catch (\Exception $e) {
            Log::error("SMS Gateway send exception: {$e->getMessage()}");
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

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($phone, '00')) {
            $phone = substr($phone, 2);
        }
        if (str_starts_with($phone, '+')) {
            return $phone;
        }
        if (strlen($phone) <= 9 && preg_match('/^7\d{8}$/', $phone)) {
            $phone = '967' . $phone;
        }
        return '+' . $phone;
    }
}
