<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Services\PaystackService;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    //
    protected $paystackService;

    public function __construct(PaystackService $paystackService){
        $this->paystackService = $paystackService;
    }

    public function link_account(Request $request){
        
         try {
            $request->validate([
                'bank_code' => 'required|string',
                'account_number' => 'required|string|max:10',
            ]);
        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors(),
            ], 422); // Unprocessable Entity status code
        }

        $client = new Client();
        $response = $client->request('GET', 'https://api.paystack.co/bank/resolve', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Accept' => 'application/json',
            ],
            'query' => [
                'account_number' => $request->account_number,
                'bank_code' => $request->bank_code,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        if ($body['status']) {
            // Assuming you have a BankAccount model to save the verified account data

            $bankAccount = BankAccount::create([
                'bank_name' => $body['data']['bank_name'],
                'account_number' => $request->account_number,
                'account_holder_name' => $body['data']['account_name'],
            ]);

            return response()->json(['message' => 'Bank account linked successfully!', 'data' => $bankAccount], 201);
        }

        return response()->json(['message' => 'Bank account verification failed.'], 422);

    }
    public function getBanks()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.paystack.co/bank', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        return response()->json($body['data']);
    }


    public function verifyAccount(Request $request)
    {
       
         try {
            $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|string|max:10',
        ]);
        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors(),
            ], 422); // Unprocessable Entity status code
        }

        try {
            $data = $this->paystackService->resolveAccount(
                $request->bank_code,
                $request->account_number
            );

            return response()->json([
                'success' => true,
                'message' => 'Account verified successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

}
