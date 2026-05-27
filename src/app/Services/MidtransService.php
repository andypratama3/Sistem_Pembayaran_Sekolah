<?php

namespace App\Services;

use App\Events\PaymentCompleted;
use App\Models\Charge;
use App\Models\Payment;
use Exception;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    protected bool $configured = false;

    public function __construct()
    {
        if (! class_exists(Config::class)) {
            logger()->warning('Midtrans SDK not installed. Run: composer require midtrans/midtrans-php');

            return;
        }

        Config::$serverKey = \get_system_config('midtrans_server_key', config('midtrans.server_key'));
        Config::$clientKey = \get_system_config('midtrans_client_key', config('midtrans.client_key'));
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
        $this->configured = true;
    }

    /**
     * Create transaction and get payment URL
     */
    public function createTransaction(Payment $payment): array
    {
        if (! $this->configured) {
            return ['success' => false, 'message' => 'Midtrans SDK not installed'];
        }

        try {
            $orderId = 'PAY-'.$payment->id.'-'.time();

            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => (int) $payment->gross_amount,
            ];

            $billingAddress = [
                'first_name' => $payment->student->name ?? 'Student',
                'last_name' => $payment->student->guardian_name ?? '',
                'email' => $payment->student->user?->email ?? 'student@school.edu',
                'phone' => $payment->student->phone ?? '',
            ];

            $customerDetails = [
                'first_name' => $payment->student->name ?? 'Student',
                'last_name' => $payment->student->guardian_name ?? '',
                'email' => $payment->student->user?->email ?? 'student@school.edu',
                'phone' => $payment->student->phone ?? '',
                'billing_address' => $billingAddress,
            ];

            $item_details = [];
            $titleName = $payment->paymentTitle->name ?: 'Pembayaran';
            $item_details[] = [
                'id' => $payment->payment_title_id,
                'price' => (int) $payment->gross_amount,
                'quantity' => 1,
                'name' => $titleName,
            ];

            $transaction = [
                'payment_type' => 'bank_transfer',
                'bank_transfer' => [
                    'bank' => 'bca',
                ],
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $item_details,
                'callbacks' => [
                    'finish' => route('payment.success', ['id' => $payment->id]),
                    'unfinish' => route('payment.unfinish', ['id' => $payment->id]),
                    'error' => route('payment.error', ['id' => $payment->id]),
                ],
            ];

            $snapToken = Snap::getSnapToken($transaction);

            // Store charge record
            Charge::create([
                'payment_id' => $payment->id,
                'order_id' => $orderId,
                'snap_token' => $snapToken,
                'gross_amount' => $payment->gross_amount,
                'transaction_status' => 'pending',
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
                'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/'.$snapToken,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create transaction: '.$e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $status = Transaction::status($orderId);

            return [
                'success' => true,
                'data' => $status,
                'transaction_status' => $status->transaction_status,
                'fraud_status' => $status->fraud_status ?? 'unknown',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get transaction status: '.$e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Verify Midtrans webhook signature
     */
    public function verifySignature(array $data, string $signature): bool
    {
        try {
            // Build signature string
            $orderIdStatus = $data['order_id'].$data['status_code'].$data['gross_amount'].config('midtrans.server_key');
            $hash = hash('sha512', $orderIdStatus);

            return hash_equals($hash, $signature);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Process payment callback from Midtrans
     */
    public function processCallback(array $data): array
    {
        try {
            $orderId = $data['order_id'] ?? null;
            $statusCode = $data['status_code'] ?? null;
            $transactionStatus = $data['transaction_status'] ?? null;
            $signatureKey = $data['signature_key'] ?? null;

            // Verify signature
            if (! $this->verifySignatureFromCallback($data, $signatureKey)) {
                throw new Exception('Invalid signature key');
            }

            // Extract payment ID from order ID (format: PAY-{payment_id}-{timestamp})
            $parts = explode('-', $orderId);
            if (count($parts) >= 3) {
                $paymentId = implode('-', array_slice($parts, 1, -1));
            } else {
                $paymentId = null;
            }

            if (! $paymentId) {
                throw new Exception('Invalid order ID format');
            }

            $payment = Payment::find($paymentId);
            if (! $payment) {
                throw new Exception('Payment not found: '.$paymentId);
            }
            $charge = Charge::where('order_id', $orderId)->first();

            if (! $charge) {
                throw new Exception('Charge record not found');
            }

            // Update charge status
            $chargeStatus = match ($transactionStatus) {
                'capture' => 'success',
                'settlement' => 'success',
                'pending' => 'pending',
                'deny' => 'failed',
                'cancel' => 'cancelled',
                'expire' => 'expired',
                default => 'unknown'
            };

            $charge->update([
                'transaction_status' => $chargeStatus,
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_type' => $data['payment_type'] ?? null,
            ]);

            // Update payment status based on transaction status
            if ($chargeStatus === 'success') {
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'paid_at' => now(),
                ]);

                // Dispatch event
                event(new PaymentCompleted($payment));
            } elseif ($chargeStatus === 'failed' || $chargeStatus === 'cancelled') {
                $payment->update([
                    'status' => 'failed',
                ]);
            } elseif ($chargeStatus === 'expired') {
                $payment->update([
                    'status' => 'expired',
                ]);
            }

            return [
                'success' => true,
                'message' => 'Callback processed successfully',
                'payment_id' => $paymentId,
                'status' => $chargeStatus,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Callback processing failed: '.$e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Verify signature from Midtrans callback
     */
    public function verifySignatureFromCallback(array $data, string $signature): bool
    {
        try {
            $orderId = $data['order_id'] ?? '';
            $statusCode = $data['status_code'] ?? '';
            $grossAmount = $data['gross_amount'] ?? '';
            $serverKey = \get_system_config('midtrans_server_key', config('midtrans.server_key'));

            $signableString = $orderId.$statusCode.$grossAmount.$serverKey;
            $hash = hash('sha512', $signableString);

            return hash_equals($hash, $signature);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction(string $orderId): array
    {
        try {
            Transaction::cancel($orderId);

            $charge = Charge::where('order_id', $orderId)->first();
            if ($charge) {
                $charge->update(['transaction_status' => 'cancelled']);
            }

            return [
                'success' => true,
                'message' => 'Transaction cancelled successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel transaction: '.$e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Refund transaction
     */
    public function refundTransaction(string $orderId, ?int $refundAmount = null): array
    {
        try {
            $refundKey = 'refund_'.$orderId.'_'.time();

            if ($refundAmount) {
                Transaction::refund($orderId, ['refund_key' => $refundKey, 'amount' => $refundAmount]);
            } else {
                Transaction::refund($orderId, ['refund_key' => $refundKey]);
            }

            $charge = Charge::where('order_id', $orderId)->first();
            if ($charge) {
                $charge->update(['transaction_status' => 'refunded']);
            }

            return [
                'success' => true,
                'message' => 'Refund processed successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to process refund: '.$e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(): array
    {
        return [
            'bank_transfers' => ['BCA', 'Mandiri', 'BNI', 'CIMB Niaga', 'BTN'],
            'e_wallets' => ['GoPay', 'OVO', 'DANA', 'LinkAja'],
            'buy_now_pay_later' => ['Kredivo', 'Akulaku', 'Kredivossi'],
            'credit_cards' => ['Visa', 'Mastercard', 'JCB'],
            'retail' => ['Indomaret', 'Alfamart'],
        ];
    }

    /**
     * Format currency for Midtrans
     */
    public function formatAmount($amount): int
    {
        return (int) round($amount);
    }
}
