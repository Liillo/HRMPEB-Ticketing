<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $environment;
    private $baseUrl;

    public function __construct()
    {
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->environment = config('mpesa.environment');
        $this->baseUrl = $this->environment === 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke' 
            : 'https://api.safaricom.co.ke';
    }

    public function getAccessToken()
    {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get($url);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        Log::error('M-Pesa Access Token Error', $response->json());
        throw new \Exception('Failed to get M-Pesa access token');
    }

    public function stkPush($phone, $amount, $accountReference, $transactionDesc)
    {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $phone = $this->formatPhoneNumber($phone);

        $response = Http::withToken($this->getAccessToken())
            ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => config('mpesa.callback_url'),
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('M-Pesa STK Push Error', $response->json());
        throw new \Exception('Failed to initiate M-Pesa payment');
    }

    public function queryTransaction($checkoutRequestId)
    {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($this->getAccessToken())
            ->post($this->baseUrl . '/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('M-Pesa Query Error', $response->json());
        throw new \Exception('Failed to query M-Pesa transaction');
    }

    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/\s+/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) === '+') {
            $phone = substr($phone, 1);
        } elseif (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
}
