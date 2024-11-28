<?php

// app/Services/PaystackService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected $paystackBaseUrl;
    protected $secretKey; 

    public function __construct()
    {
        $this->paystackBaseUrl = 'https://api.paystack.co';
        $this->secretKey =  env('PAYSTACK_SECRET_KEY');
    }

    public function initiateAccountLinking($email, $amount, $callback_url)
    {
        $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        'Content-Type' => 'application/json',
        ])->post("{$this->paystackBaseUrl}/transaction/initialize", [
            'email' => $email,
            'amount' => $amount * 100, // Paystack expects amount in kobo
            'callback_url' => $callback_url
        ]);

        if ($response->successful()) {
            return $response->json()['data']['authorization_url'];
        }

        throw new \Exception('Error initiating account linking: ' . $response->body());
    }

    public function verifyTransaction($reference)
    {
        $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY')
        ])->get("{$this->paystackBaseUrl}/transaction/verify/{$reference}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Transaction verification failed: ' . $response->body());
    }

     public function resolveAccount(string $bankCode, string $accountNumber): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->paystackBaseUrl}/bank/resolve", [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ]);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        throw new \Exception($response->json()['message'] ?? 'Unable to verify account. Please try again.');
    }
}
