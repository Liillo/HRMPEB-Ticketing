<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ticket;
use App\Mail\TicketPurchased;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MpesaCallbackController extends Controller
{
    public function callback(Request $request)
    {
        Log::info('========== M-PESA CALLBACK RECEIVED ==========');
        Log::info('Full Request Data:', $request->all());

        $data = $request->all();
        
        // Validate callback structure
        if (!isset($data['Body']['stkCallback'])) {
            Log::error('Invalid callback structure - Missing Body.stkCallback', [
                'received_data' => $data
            ]);
            return response()->json([
                'ResultCode' => 1, 
                'ResultDesc' => 'Invalid callback data'
            ], 400);
        }

        $callback = $data['Body']['stkCallback'];
        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $merchantRequestId = $callback['MerchantRequestID'] ?? null;
        $resultCode = $callback['ResultCode'] ?? null;
        $resultDesc = $callback['ResultDesc'] ?? 'No description';

        Log::info('Callback Details', [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);

        // Find payment record
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            Log::error('Payment Record Not Found', [
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId
            ]);
            return response()->json([
                'ResultCode' => 1, 
                'ResultDesc' => 'Payment record not found'
            ], 404);
        }

        Log::info('Payment Record Found', [
            'payment_id' => $payment->id,
            'ticket_id' => $payment->ticket_id,
            'current_status' => $payment->status
        ]);

        // Process successful payment
        if ($resultCode == 0) {
            Log::info('Processing Successful Payment');
            
            // Extract callback metadata
            $mpesaReceipt = null;
            $amount = null;
            $phoneNumber = null;
            $transactionDate = null;

            if (isset($callback['CallbackMetadata']['Item'])) {
                $callbackMetadata = $callback['CallbackMetadata']['Item'];
                
                Log::info('Callback Metadata Items:', ['items' => $callbackMetadata]);
                
                foreach ($callbackMetadata as $item) {
                    if (!isset($item['Name']) || !isset($item['Value'])) {
                        continue;
                    }
                    
                    switch ($item['Name']) {
                        case 'MpesaReceiptNumber':
                            $mpesaReceipt = $item['Value'];
                            break;
                        case 'Amount':
                            $amount = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $phoneNumber = $item['Value'];
                            break;
                        case 'TransactionDate':
                            $transactionDate = $item['Value'];
                            break;
                    }
                }
            } else {
                Log::warning('No CallbackMetadata found in successful payment');
            }

            Log::info('Extracted Payment Details', [
                'mpesa_receipt' => $mpesaReceipt,
                'amount' => $amount,
                'phone_number' => $phoneNumber,
                'transaction_date' => $transactionDate
            ]);

            // Update payment record
            try {
                $payment->update([
                    'status' => 'success',
                    'mpesa_receipt' => $mpesaReceipt,
                    'response_description' => $resultDesc,
                ]);

                Log::info('Payment Updated to Success', [
                    'payment_id' => $payment->id,
                    'mpesa_receipt' => $mpesaReceipt
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update payment', [
                    'error' => $e->getMessage(),
                    'payment_id' => $payment->id
                ]);
            }

            // Update ticket status
            try {
                $ticket = $payment->ticket;
                $ticket->update(['status' => 'paid']);

                Log::info('Ticket Updated to Paid', [
                    'ticket_id' => $ticket->id,
                    'ticket_uuid' => $ticket->uuid
                ]);

                // Generate QR code if not exists
                if (!$ticket->qr_code || !file_exists(storage_path('app/public/qrcodes/' . $ticket->uuid . '.svg'))) {
                    $ticketService = new \App\Services\TicketService();
                    $ticketService->generateQrCode($ticket);
                    
                    Log::info('QR Code Generated', ['ticket_uuid' => $ticket->uuid]);
                }

                // Send confirmation email
                $this->sendTicketEmail($ticket);

            } catch (\Exception $e) {
                Log::error('Failed to update ticket or send email', [
                    'error' => $e->getMessage(),
                    'ticket_id' => $payment->ticket_id
                ]);
            }

            Log::info('========== PAYMENT PROCESSING COMPLETE - SUCCESS ==========');

        } else {
            // Payment failed
            Log::warning('Processing Failed Payment', [
                'result_code' => $resultCode,
                'result_desc' => $resultDesc
            ]);

            try {
                $payment->update([
                    'status' => 'failed',
                    'response_description' => $resultDesc,
                ]);

                $payment->ticket->update([
                    'status' => 'failed',
                ]);

                Log::info('Payment and Ticket Updated to Failed', [
                    'payment_id' => $payment->id,
                    'ticket_id' => $payment->ticket_id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update payment/ticket to failed status', [
                    'error' => $e->getMessage(),
                    'payment_id' => $payment->id
                ]);
            }

            Log::info('========== PAYMENT PROCESSING COMPLETE - FAILED ==========');
        }

        // Acknowledge M-Pesa
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }

    public function timeout(Request $request)
    {
        Log::warning('========== M-PESA TIMEOUT RECEIVED ==========');
        Log::warning('Timeout Request Data:', $request->all());
        
        $data = $request->all();
        
        $checkoutRequestId = $data['CheckoutRequestID'] ?? null;
        $merchantRequestId = $data['MerchantRequestID'] ?? null;

        if (!$checkoutRequestId) {
            Log::error('Timeout callback missing CheckoutRequestID');
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Missing CheckoutRequestID'
            ], 400);
        }

        Log::info('Processing Timeout', [
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $merchantRequestId
        ]);

        // Find and update payment
        $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();
        
        if ($payment && $payment->status === 'pending') {
            try {
                $payment->update([
                    'status' => 'failed',
                    'response_description' => 'Request timeout - Customer did not complete payment'
                ]);

                $payment->ticket->update([
                    'status' => 'failed'
                ]);

                Log::info('Timeout Processed - Payment and Ticket Failed', [
                    'payment_id' => $payment->id,
                    'ticket_id' => $payment->ticket_id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to process timeout', [
                    'error' => $e->getMessage(),
                    'payment_id' => $payment->id
                ]);
            }
        } else {
            Log::warning('Timeout - Payment not found or already processed', [
                'checkout_request_id' => $checkoutRequestId,
                'payment_found' => $payment ? 'yes' : 'no',
                'payment_status' => $payment ? $payment->status : 'N/A'
            ]);
        }

        Log::info('========== TIMEOUT PROCESSING COMPLETE ==========');

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Timeout processed'
        ]);
    }

    /*Send ticket confirmation email*/
    private function sendTicketEmail(Ticket $ticket)
    {
        try {
            $email = $ticket->type === 'individual' 
                ? $ticket->email 
                : $ticket->company_email;

            if (!$email) {
                Log::warning('No email address found for ticket', [
                    'ticket_id' => $ticket->id,
                    'type' => $ticket->type
                ]);
                return;
            }

            Log::info('Attempting to send ticket email', [
                'ticket_id' => $ticket->id,
                'email' => $email
            ]);

            Mail::to($email)->send(new TicketPurchased($ticket));
            
            Log::info('Ticket email sent successfully', [
                'ticket_id' => $ticket->id,
                'email' => $email,
                'event' => $ticket->event->name
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send ticket email', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw - email failure shouldn't stop payment processing
        }
    }
}