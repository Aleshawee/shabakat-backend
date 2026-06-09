<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?string $provider;
    protected ?string $apiKey;
    protected ?string $apiSecret;
    protected ?string $senderName;
    protected bool $enabled;
    protected ?string $networkName;

    public function __construct()
    {
        try {
            $settings = Setting::where('group', 'sms')
                ->get()
                ->pluck('value', 'key');

            $this->provider = $settings->get('sms_provider');
            $this->apiKey = $settings->get('sms_api_key');
            $this->apiSecret = $settings->get('sms_api_secret');
            $this->senderName = $settings->get('sms_sender_name');
            $this->enabled = $settings->get('sms_enabled') === 'true' || $settings->get('sms_enabled') === '1';
        } catch (\Exception $e) {
            Log::warning("SmsService: failed to load settings: {$e->getMessage()}");
            $this->provider = null;
            $this->apiKey = null;
            $this->apiSecret = null;
            $this->senderName = null;
            $this->enabled = false;
        }
        $this->networkName = tenancy()->initialized ? tenancy()->tenant->name : '';
    }

    public function send(string $phone, string $message): bool
    {
        $message = "شبكة {$this->networkName}: {$message}";

        if (!$this->enabled || !$this->provider) {
            Log::info("SMS disabled or not configured. To {$phone}: {$message}");
            return true;
        }

        return match ($this->provider) {
            'twilio' => $this->sendViaTwilio($phone, $message),
            'textbee' => $this->sendViaTextbee($phone, $message),
            'smsgateway' => $this->sendViaSmsGateway($phone, $message),
            default => $this->sendViaTwilio($phone, $message),
        };
    }

    public function sendOtp(string $phone, string $code): bool
    {
        Log::info("OTP for {$phone}: {$code}");

        if (!$this->enabled || !$this->provider) {
            return false;
        }

        $message = "شبكة {$this->networkName}: {$code}";

        return match ($this->provider) {
            'twilio' => $this->sendViaTwilio($phone, $message),
            'textbee' => $this->sendViaTextbee($phone, $message),
            'smsgateway' => $this->sendViaSmsGateway($phone, $message),
            default => $this->sendViaTwilio($phone, $message),
        };
    }

    protected function sendViaSmsGateway(string $phone, string $message): bool
    {
        $gateway = new SmsGatewayService();
        return $gateway->sendSms($phone, $message);
    }

    protected function sendViaTextbee(string $phone, string $message): bool
    {
        $textbee = new TextbeeService();
        return $textbee->sendSms($phone, $message);
    }

    protected function sendViaTwilio(string $phone, string $message): bool
    {
        try {
            $twilio = new \Twilio\Rest\Client($this->apiKey, $this->apiSecret);
            $twilio->messages->create($phone, [
                'from' => $this->senderName,
                'body' => $message,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error("Twilio SMS failed: {$e->getMessage()}");
            return false;
        }
    }
}
